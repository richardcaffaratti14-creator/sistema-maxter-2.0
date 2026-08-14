using MaxterServer;
using MaxterServer.Abstractions;
using MaxterServer.Databases;
using MaxterServer.Logging;
using MaxterServer.Processes;
using MaxterServer.WebServers;

// ============================================================================
//  Maxter Server - supervisor del Runtime (Fase 1 / MVP)
//  Secuencia: validar -> (instalar DB si es 1er arranque) -> DB -> web -> publicar
//             -> esperar -> cierre ordenado. Anti-huerfanos via Job Object.
// ============================================================================

var paths = new RuntimePaths();
var log = new FileLog(paths.LogsDir, "maxter-server");
var dbLog = new FileLog(paths.LogsDir, "db");
var webLog = new FileLog(paths.LogsDir, "web");

log.Info("== Maxter Server: iniciando Runtime ==");
log.Info($"Runtime root: {paths.Root}");

int exitCode = 0;
var done = new ManualResetEventSlim(false);
using var supervisor = new ProcessSupervisor(log);

IDatabaseServer? database = null;
IWebServer? web = null;
var shutdownOnce = new object();
var alreadyShuttingDown = false;

void Shutdown(string reason)
{
    lock (shutdownOnce)
    {
        if (alreadyShuttingDown) return;
        alreadyShuttingDown = true;
    }

    log.Info($"Cerrando Runtime ({reason})...");
    supervisor.ShutdownAll(child =>
    {
        if (child.Name == "web") web?.RequestGracefulStop();
        else if (child.Name == "db") database?.RequestGracefulStop();
    });
    done.Set();
}

// Cierre por Ctrl+C o cierre de la ventana de consola.
ConsoleControl.OnShutdown(() => Shutdown("solicitado por el usuario"));

// Si un componente se cae solo, es fatal: apagamos todo de forma ordenada.
supervisor.UnexpectedExit += _ => { exitCode = 1; Shutdown("caida de un componente"); };

try
{
    // Configuracion de alto nivel + manifiesto de componentes.
    var cfg = MaxterConfig.Load(paths.MaxterConfigFile);
    var manifest = Manifest.Load(paths.ManifestFile);
    log.Info($"{manifest.RuntimeName} v{manifest.RuntimeVersion}");

    // (0) Validacion previa: todos los componentes presentes e integros ANTES de arrancar nada.
    var validation = new RuntimeValidator(paths, manifest, log).Validate();
    if (!validation.Ok)
        throw new InvalidOperationException("La validacion del Runtime fallo. Revise los problemas listados arriba.");

    // (0b) La carpeta de la aplicacion a publicar debe existir y contener index.php.
    //      Falla temprano y claro, en vez del error criptico de Apache (DocumentRoot).
    var appDir = paths.AppDir(cfg);
    if (!Directory.Exists(appDir))
        throw new InvalidOperationException(
            $"La carpeta de la aplicacion no existe: {appDir}. " +
            "Copie el sistema Maxter alli, o ajuste 'appFolder' en config/maxter.config (acepta ruta relativa o absoluta).");
    if (!File.Exists(Path.Combine(appDir, "index.php")))
        throw new InvalidOperationException(
            $"La carpeta de la aplicacion no contiene index.php: {appDir}. " +
            "Verifique que 'appFolder' en config/maxter.config apunte al sistema Maxter.");
    log.Info($"Aplicacion a publicar: {appDir}");

    // Instanciacion de motores detras de las interfaces (el resto no depende de Apache/MariaDB).
    database = new MariaDbServer(paths, manifest, dbLog);
    web = new ApacheWebServer(paths, cfg, manifest, webLog);

    // (1) Instalacion inicial de la base (solo primer arranque).
    if (database.NeedsInitialization())
        database.Initialize();

    // (2) Iniciar la base de datos.
    log.Info("Iniciando base de datos...");
    database.PrepareConfiguration();
    supervisor.Launch(database.Id, database.BuildStartSpec(), dbLog);
    if (!await database.WaitUntilReadyAsync(TimeSpan.FromSeconds(cfg.DbStartTimeoutSeconds)))
        throw new InvalidOperationException("La base de datos no quedo lista a tiempo.");
    log.Info("Base de datos lista.");

    // (3) Iniciar el servidor web + PHP (publica la carpeta de la aplicacion).
    log.Info("Iniciando servidor web...");
    web.PrepareConfiguration();
    supervisor.Launch(web.Id, web.BuildStartSpec(), webLog);
    if (!await web.WaitUntilReadyAsync(TimeSpan.FromSeconds(cfg.WebStartTimeoutSeconds)))
    {
        web.LogStartupDiagnostics(); // corre 'httpd -t' y vuelca el diagnostico exacto de Apache
        throw new InvalidOperationException("El servidor web no quedo listo a tiempo.");
    }
    log.Info("Servidor web listo.");

    // (4) Publicar los accesos.
    log.Info("=====================================================");
    log.Info("  Maxter Server EN LINEA");
    log.Info($"  Local:  {web.LocalUrl}");
    log.Info($"  LAN:    {web.LanUrl}");
    log.Info("  (Ctrl+C para detener)");
    log.Info("=====================================================");
}
catch (Exception ex)
{
    log.Error("Fallo de arranque: " + ex.Message);
    exitCode = 1;
    Shutdown("error de arranque");
}

// Bloquea hasta Ctrl+C, cierre de consola o caida de un componente.
done.Wait();

supervisor.Dispose(); // Job Object: red final anti-huerfanos.
log.Info("Runtime detenido.");
return exitCode;
