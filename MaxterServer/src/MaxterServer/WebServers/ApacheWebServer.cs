using System.Runtime.Versioning;
using MaxterServer.Abstractions;
using MaxterServer.Config;
using MaxterServer.Health;
using MaxterServer.Logging;
using MaxterServer.Net;

namespace MaxterServer.WebServers;

/// <summary>
/// Implementacion de IWebServer sobre Apache httpd + mod_php. Detalle interno del Runtime:
/// el resto del sistema solo ve IWebServer. Genera httpd.conf desde plantilla con rutas
/// absolutas (ServerRoot, DocumentRoot=app/, PHP, Listen 0.0.0.0:puerto) y deja intacto
/// el .htaccess de la aplicacion (AllowOverride All en la plantilla).
/// </summary>
[SupportedOSPlatform("windows")]
public sealed class ApacheWebServer : IWebServer
{
    private readonly RuntimePaths _paths;
    private readonly MaxterConfig _cfg;
    private readonly ComponentInfo _web;
    private readonly ComponentInfo _php;
    private readonly FileLog _log;

    public ApacheWebServer(RuntimePaths paths, MaxterConfig cfg, Manifest manifest, FileLog log)
    {
        _paths = paths;
        _cfg = cfg;
        _web = manifest.Component("web");
        _php = manifest.Component("php");
        _log = log;
    }

    public string Id => "web";
    public string DisplayName => "servidor web";

    public string LocalUrl => $"http://localhost{PortSuffix}";
    public string LanUrl => $"http://{LanAddress.BestGuess()}{PortSuffix}";
    private string PortSuffix => _cfg.Port == 80 ? "" : ":" + _cfg.Port;

    private string TemplatePath => _paths.ComponentFile(_web, "conf/httpd.conf.tmpl");

    public void PrepareConfiguration()
    {
        Directory.CreateDirectory(_paths.ConfigDir);
        Directory.CreateDirectory(_paths.LogsDir); // Apache no crea la carpeta del ErrorLog

        // 1) php.ini del Runtime: base vendorizada + bloque Maxter (extension_dir absoluto + extensiones MySQL).
        GeneratePhpIni();

        // 2) httpd.conf
        var tokens = new Dictionary<string, string>
        {
            ["SERVER_ROOT"] = Slash(_paths.ComponentDir(_web)),
            ["DOC_ROOT"]    = Slash(_paths.AppDir(_cfg)),
            ["PORT"]        = _cfg.Port.ToString(),
            ["PHP_DIR"]     = Slash(_paths.ComponentDir(_php)),
            ["PHP_INI_DIR"] = Slash(_paths.ConfigDir),
            ["ERROR_LOG"]   = Slash(_paths.ApacheErrorLog),
        };

        var rendered = TemplateEngine.Render(File.ReadAllText(TemplatePath), tokens);
        File.WriteAllText(_paths.HttpdConfGenerated, rendered);
        _log.Info("Configuracion del servidor web generada.");
    }

    /// <summary>
    /// Genera config/php.ini = php.ini vendorizado + bloque Maxter al final (ultima asignacion gana):
    /// extension_dir absoluto (portable, calculado en runtime) y las extensiones MySQL que exige la app
    /// (php_mysql para SypDatabase, php_mysqli para el Updater). No modifica el php.ini vendorizado.
    /// </summary>
    private void GeneratePhpIni()
    {
        var vendored = _paths.ComponentFile(_php, "php.ini");
        var extDir = Slash(_paths.ComponentFile(_php, "ext"));
        var baseIni = File.Exists(vendored) ? File.ReadAllText(vendored) : "";

        var block = string.Join(Environment.NewLine, new[]
        {
            "",
            "; ===== Maxter Server: configuracion inyectada (no editar) =====",
            $"extension_dir = \"{extDir}\"",
            "extension=php_mysql.dll",
            "extension=php_mysqli.dll",
            // SYPFW usa etiquetas cortas <? ?>. PHP 5.6 trae short_open_tag=Off por defecto;
            // sin esto, PHP emite el codigo fuente como texto en vez de ejecutarlo.
            "short_open_tag = On",
            ""
        });

        File.WriteAllText(_paths.PhpIniGenerated, baseIni + block);
        _log.Info($"php.ini del Runtime generado (extension_dir=\"{extDir}\", mysql+mysqli).");
    }

    public ProcessStartSpec BuildStartSpec() => new(
        FileName: _paths.ComponentExecutable(_web),
        Arguments: $"-f \"{_paths.HttpdConfGenerated}\" -d \"{_paths.ComponentDir(_web)}\"",
        WorkingDirectory: _paths.ComponentDir(_web));

    public Task<bool> WaitUntilReadyAsync(TimeSpan timeout) =>
        ReadinessCheck.WaitHttpAsync($"http://127.0.0.1:{_cfg.Port}/", timeout);

    public void RequestGracefulStop()
    {
        // httpd -k stop: apagado ordenado del servidor web
        RunShort(_paths.ComponentExecutable(_web), $"-f \"{_paths.HttpdConfGenerated}\" -k stop");
    }

    /// <summary>
    /// Corre "httpd -t" (test de configuracion) capturando stdout y stderr, y los vuelca al
    /// log (logs/web.log + consola). Da el diagnostico exacto de Apache cuando el arranque falla,
    /// sin depender solo del codigo de salida.
    /// </summary>
    public void LogStartupDiagnostics()
    {
        _log.Info("Diagnostico de Apache: ejecutando 'httpd -t'...");
        var exe = _paths.ComponentExecutable(_web);
        var args = $"-t -f \"{_paths.HttpdConfGenerated}\" -d \"{_paths.ComponentDir(_web)}\"";
        var (code, _, _) = RunCaptured(exe, args);
        _log.Info($"'httpd -t' exit code: {code}");

        // Apache escribe los errores de arranque/bind en su ErrorLog, NO en stdout.
        DumpApacheErrorLog();

        // Probe HTTP real: que devuelve el servidor (status + body). Revela errores de la app (500, etc.).
        LogHttpProbeAsync().GetAwaiter().GetResult();
    }

    /// <summary>Hace un GET real y loguea status HTTP + primeros 500 chars del body (o la excepcion).</summary>
    private async Task LogHttpProbeAsync()
    {
        var url = $"http://127.0.0.1:{_cfg.Port}/";
        _log.Info($"Probe HTTP: GET {url}");
        try
        {
            using var handler = new System.Net.Http.HttpClientHandler { UseProxy = false, AllowAutoRedirect = false };
            using var http = new System.Net.Http.HttpClient(handler) { Timeout = TimeSpan.FromSeconds(8) };
            using var resp = await http.GetAsync(url);
            var body = await resp.Content.ReadAsStringAsync();
            var snippet = body.Length > 500 ? body.Substring(0, 500) : body;
            _log.Info($"Probe HTTP status: {(int)resp.StatusCode} {resp.StatusCode}");
            _log.Info($"Probe HTTP body (primeros 500):{Environment.NewLine}{snippet}");
        }
        catch (Exception ex)
        {
            _log.Error($"Probe HTTP fallo: {ex.GetType().Name}: {ex.Message}");
        }
    }

    /// <summary>Vuelca las ultimas lineas del error_log de Apache al log del Runtime.</summary>
    private void DumpApacheErrorLog(int lastLines = 40)
    {
        var path = _paths.ApacheErrorLog;
        if (!File.Exists(path))
        {
            _log.Info($"(no hay apache-error.log en {path})");
            return;
        }

        try
        {
            // FileShare.ReadWrite: Apache puede tener el archivo abierto para escritura.
            using var fs = new FileStream(path, FileMode.Open, FileAccess.Read, FileShare.ReadWrite);
            using var sr = new StreamReader(fs);
            var all = sr.ReadToEnd().Replace("\r\n", "\n").Split('\n');
            var tail = string.Join(Environment.NewLine, all.Skip(Math.Max(0, all.Length - lastLines)));
            _log.Error($"apache-error.log (ultimas {lastLines} lineas):{Environment.NewLine}{tail}");
        }
        catch (Exception ex)
        {
            _log.Error($"No se pudo leer apache-error.log: {ex.Message}");
        }
    }

    /// <summary>
    /// Ejecuta httpd capturando stdout y stderr (lectura async para evitar deadlock) y los loguea.
    /// </summary>
    private (int exitCode, string stdout, string stderr) RunCaptured(string exe, string args)
    {
        _log.Info($"CMD: \"{exe}\" {args}   (cwd: {_paths.ComponentDir(_web)})");

        using var p = new System.Diagnostics.Process
        {
            StartInfo = new System.Diagnostics.ProcessStartInfo(exe, args)
            {
                UseShellExecute = false,
                CreateNoWindow = true,
                WorkingDirectory = _paths.ComponentDir(_web),
                RedirectStandardOutput = true,
                RedirectStandardError = true,
            }
        };

        if (!p.Start())
            throw new InvalidOperationException($"No se pudo iniciar {Path.GetFileName(exe)}.");

        var outTask = p.StandardOutput.ReadToEndAsync();
        var errTask = p.StandardError.ReadToEndAsync();
        p.WaitForExit();
        var outp = outTask.GetAwaiter().GetResult();
        var err = errTask.GetAwaiter().GetResult();

        var tool = Path.GetFileName(exe);
        if (!string.IsNullOrWhiteSpace(outp)) _log.Info($"[{tool}] stdout:{Environment.NewLine}{outp.TrimEnd()}");
        if (!string.IsNullOrWhiteSpace(err)) _log.Error($"[{tool}] stderr:{Environment.NewLine}{err.TrimEnd()}");
        return (p.ExitCode, outp, err);
    }

    private void RunShort(string exe, string args)
    {
        try
        {
            var psi = new System.Diagnostics.ProcessStartInfo(exe, args)
            {
                UseShellExecute = false,
                CreateNoWindow = true,
                WorkingDirectory = _paths.ComponentDir(_web),
            };
            System.Diagnostics.Process.Start(psi);
        }
        catch (Exception ex)
        {
            _log.Error($"No se pudo enviar el apagado ordenado al servidor web: {ex.Message}");
        }
    }

    private static string Slash(string path) => path.Replace('\\', '/');
}
