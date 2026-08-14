using System.Diagnostics;
using System.Runtime.Versioning;
using MaxterServer.Abstractions;
using MaxterServer.Config;
using MaxterServer.Health;
using MaxterServer.Logging;

namespace MaxterServer.Databases;

/// <summary>
/// Implementacion de IDatabaseServer sobre MariaDB. Detalle interno del Runtime.
/// Genera my.ini desde plantilla (datadir/puerto), maneja la instalacion inicial
/// (primer arranque: crear datadir + importar el dump oficial 'maxter') y el arranque normal.
/// Las evoluciones de esquema posteriores las maneja el Updater del sistema, no el Runtime.
/// </summary>
[SupportedOSPlatform("windows")]
public sealed class MariaDbServer : IDatabaseServer
{
    private const int Port = 3306;

    private readonly RuntimePaths _paths;
    private readonly ComponentInfo _db;
    private readonly FileLog _log;

    public MariaDbServer(RuntimePaths paths, Manifest manifest, FileLog log)
    {
        _paths = paths;
        _db = manifest.Component("db");
        _log = log;
    }

    public string Id => "db";
    public string DisplayName => "base de datos";

    // Rutas internas del motor
    private string BinDir => _paths.ComponentFile(_db, "bin");
    private string DataDir => _paths.ComponentFile(_db, "data");
    private string SeedFile => _paths.ComponentFile(_db, "seed/maxter.sql");
    private string TemplatePath => _paths.ComponentFile(_db, "my.ini.tmpl");
    private string Tool(string exe) => Path.Combine(BinDir, exe);

    // Nombres de herramientas de MariaDB en Windows (localizados aqui; no se filtran al resto).
    private string MysqldExe => Tool("mysqld.exe");
    private string InstallDbExe => Tool("mysql_install_db.exe");
    private string MysqlExe => Tool("mysql.exe");
    private string MysqlAdminExe => Tool("mysqladmin.exe");

    public bool NeedsInitialization() =>
        !Directory.Exists(Path.Combine(DataDir, "mysql"));

    public void PrepareConfiguration()
    {
        var tokens = new Dictionary<string, string>
        {
            ["DATA_DIR"] = Slash(DataDir),
            ["BASE_DIR"] = Slash(_paths.ComponentDir(_db)),
            ["PORT"]     = Port.ToString(),
        };
        var rendered = TemplateEngine.Render(File.ReadAllText(TemplatePath), tokens);
        Directory.CreateDirectory(_paths.ConfigDir);
        File.WriteAllText(_paths.MyIniGenerated, rendered);
        _log.Info("Configuracion de la base de datos generada.");
    }

    public ProcessStartSpec BuildStartSpec() => new(
        FileName: MysqldExe,
        Arguments: $"--defaults-file=\"{_paths.MyIniGenerated}\"",
        WorkingDirectory: _paths.ComponentDir(_db));

    public Task<bool> WaitUntilReadyAsync(TimeSpan timeout) =>
        ReadinessCheck.WaitTcpAsync("127.0.0.1", Port, timeout);

    public void RequestGracefulStop() =>
        RunTool(MysqlAdminExe, $"--host=127.0.0.1 --port={Port} -u root shutdown", waitMs: 8000);

    // ---------------- Instalacion inicial (solo primer arranque) ----------------

    public void Initialize()
    {
        _log.Info("Primer arranque: instalando la base de datos...");
        Directory.CreateDirectory(DataDir);

        // 1) Crear el datadir del sistema (MariaDB 10.6 Windows).
        //    Opciones validas de mysql_install_db.exe: --datadir, --service, --password,
        //    --port, --socket, --default-user, --allow-remote-root-access, --skip-networking...
        //    NO existe --auth-root-authentication-method (eso es de MySQL). En Windows,
        //    MariaDB crea 'root' sin password y con mysql_native_password por defecto.
        RunToolChecked(InstallDbExe, $"--datadir=\"{DataDir}\"");

        // 2) Levantar un mysqld temporal para poder crear la base e importar el seed.
        var temp = StartTemporaryServer();
        try
        {
            if (!ReadinessCheck.WaitTcpAsync("127.0.0.1", Port, TimeSpan.FromSeconds(30)).GetAwaiter().GetResult())
                throw new InvalidOperationException("El motor temporal no respondio durante la instalacion.");

            // 3) Crear la base 'maxter'.
            RunToolChecked(MysqlExe, "--host=127.0.0.1 -u root -e \"CREATE DATABASE IF NOT EXISTS maxter CHARACTER SET utf8;\"");

            // 4) Importar el dump oficial, si esta presente.
            if (File.Exists(SeedFile))
            {
                _log.Info("Importando el dump oficial de la base 'maxter'...");
                RunToolWithInput(MysqlExe, "--host=127.0.0.1 -u root maxter", File.ReadAllText(SeedFile));
            }
            else
            {
                _log.Info($"No se encontro el dump inicial ({SeedFile}); se crea la base vacia.");
            }

            // 5) Asegurar que 'root' entra sin password y con mysql_native_password,
            //    tanto por 'localhost' como por '127.0.0.1' (SypDatabase conecta a 127.0.0.1).
            const string sql =
                "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING '';" +
                "CREATE USER IF NOT EXISTS 'root'@'127.0.0.1' IDENTIFIED VIA mysql_native_password USING '';" +
                "GRANT ALL PRIVILEGES ON *.* TO 'root'@'127.0.0.1' WITH GRANT OPTION;" +
                "FLUSH PRIVILEGES;";
            RunToolChecked(MysqlExe, $"--host=127.0.0.1 -u root -e \"{sql}\"");
        }
        finally
        {
            // 6) Detener el mysqld temporal de forma ordenada.
            RunTool(MysqlAdminExe, $"--host=127.0.0.1 --port={Port} -u root shutdown", waitMs: 8000);
            try { if (!temp.WaitForExit(10000) && !temp.HasExited) temp.Kill(true); } catch { }
        }

        _log.Info("Base de datos instalada.");
    }

    private Process StartTemporaryServer()
    {
        var psi = new ProcessStartInfo(MysqldExe,
            $"--datadir=\"{DataDir}\" --port={Port} --skip-name-resolve")
        {
            UseShellExecute = false,
            CreateNoWindow = true,
            WorkingDirectory = _paths.ComponentDir(_db),
        };
        return Process.Start(psi)
               ?? throw new InvalidOperationException("No se pudo iniciar el motor temporal de base de datos.");
    }

    // ---------------- Helpers de ejecucion de herramientas ----------------

    private void RunTool(string exe, string args, int waitMs)
    {
        try
        {
            using var p = Process.Start(new ProcessStartInfo(exe, args)
            { UseShellExecute = false, CreateNoWindow = true, WorkingDirectory = BinDir });
            p?.WaitForExit(waitMs);
        }
        catch (Exception ex)
        {
            _log.Error($"Fallo ejecutando {Path.GetFileName(exe)}: {ex.Message}");
        }
    }

    private void RunToolChecked(string exe, string args)
    {
        var (code, outp, err) = RunCaptured(exe, args, stdin: null);
        if (code != 0)
            throw new InvalidOperationException(FailDetail(exe, args, code, outp, err));
    }

    private void RunToolWithInput(string exe, string args, string stdin)
    {
        var (code, outp, err) = RunCaptured(exe, args, stdin);
        if (code != 0)
            throw new InvalidOperationException(FailDetail(exe, args, code, outp, err));
    }

    /// <summary>
    /// Ejecuta una herramienta capturando stdout y stderr. Registra el comando completo y
    /// ambas salidas en el log (logs/db.log + consola). Lectura async para evitar deadlock
    /// cuando los buffers de salida se llenan.
    /// </summary>
    private (int exitCode, string stdout, string stderr) RunCaptured(string exe, string args, string? stdin)
    {
        _log.Info($"CMD: \"{exe}\" {args}   (cwd: {BinDir}){(stdin != null ? $"  (stdin: {stdin.Length} bytes)" : "")}");

        using var p = new Process
        {
            StartInfo = new ProcessStartInfo(exe, args)
            {
                UseShellExecute = false,
                CreateNoWindow = true,
                WorkingDirectory = BinDir,
                RedirectStandardOutput = true,
                RedirectStandardError = true,
                RedirectStandardInput = stdin != null,
            }
        };

        if (!p.Start())
            throw new InvalidOperationException($"No se pudo iniciar {Path.GetFileName(exe)}.");

        var outTask = p.StandardOutput.ReadToEndAsync();
        var errTask = p.StandardError.ReadToEndAsync();

        if (stdin != null)
        {
            p.StandardInput.Write(stdin);
            p.StandardInput.Close();
        }

        p.WaitForExit();
        var outp = outTask.GetAwaiter().GetResult();
        var err = errTask.GetAwaiter().GetResult();

        var tool = Path.GetFileName(exe);
        if (!string.IsNullOrWhiteSpace(outp)) _log.Info($"[{tool}] stdout:{Environment.NewLine}{outp.TrimEnd()}");
        if (!string.IsNullOrWhiteSpace(err)) _log.Info($"[{tool}] stderr:{Environment.NewLine}{err.TrimEnd()}");
        _log.Info($"[{tool}] exit code: {p.ExitCode}");

        return (p.ExitCode, outp, err);
    }

    /// <summary>Arma el mensaje de fallo con codigo + stdout + stderr, y lo vuelca al log/consola.</summary>
    private string FailDetail(string exe, string args, int code, string stdout, string stderr)
    {
        var tool = Path.GetFileName(exe);
        var msg =
            $"{tool} salio con codigo {code}." + Environment.NewLine +
            $"  Comando: \"{exe}\" {args}" + Environment.NewLine +
            $"  stdout: {(string.IsNullOrWhiteSpace(stdout) ? "(vacio)" : Environment.NewLine + stdout.TrimEnd())}" + Environment.NewLine +
            $"  stderr: {(string.IsNullOrWhiteSpace(stderr) ? "(vacio)" : Environment.NewLine + stderr.TrimEnd())}";
        _log.Error(msg);   // consola + logs/db.log al fallar
        return msg;
    }

    private static string Slash(string path) => path.Replace('\\', '/');
}
