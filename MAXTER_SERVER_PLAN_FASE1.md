# Maxter Server — Plan de Implementación Fase 1 (MVP)

**Goal:** Al ejecutar `MaxterServer.exe`, el Runtime arranca sus motores internos (base de datos → PHP/web), publica la carpeta de la aplicación y la deja accesible en `http://localhost` y `http://IP_DEL_SERVIDOR` de la LAN, cerrándose de forma limpia sin dejar procesos huérfanos.

**Filosofía:** el Runtime se adapta al sistema existente, nunca al revés. No se toca la app, SYPFW, bootstrap, `index.php` ni `configuration.php`. Apache/PHP/MySQL son componentes internos e invisibles.

**Regla de simplicidad:** MVP sin sobreingeniería. Nada de GUI, tray, servicio Windows, instalador, updates, backups, monitoreo, panel ni seguridad extra. Solo lo necesario para demostrar que el Runtime funciona.

---

## 0. Ajustes aprobados — revisión 2

Cambios sobre el plan base, ya integrados en las secciones y tareas:

1. **Versiones por manifiesto, no hardcodeadas.** Las versiones y rutas de los componentes (web, php, db) viven en `runtime/manifest.json`. El Runtime las lee en ejecución. Actualizar un componente = editar el manifiesto + reemplazar binarios, sin recompilar. → nueva `Manifest.cs` + Task 2b.
2. **Abstracción por interfaces.** El resto del Runtime depende de `IWebServer` e `IDatabaseServer`, no de "Apache"/"MariaDB". Implementaciones concretas: `ApacheWebServer`, `MariaDbServer`. Sustituir el motor en el futuro no toca la orquestación. → nuevas `Abstractions/` + Task 7 reorganizada.
3. **Etapa de validación previa.** Antes de arrancar procesos, `RuntimeValidator` verifica existencia e integridad (tamaño/checksum del manifiesto) de todos los componentes declarados. Si falta o no coincide algo, se aborta con mensaje claro. → nueva `RuntimeValidator.cs` + Task 6b, invocada al inicio de `Program.cs`.
4. **`config/` y `logs/` separados.** Toda la configuración (de alto nivel y generada) va en `config/`; los logs van en `logs/` con **un archivo por componente** (`maxter-server.log`, `web.log`, `db.log`). → `RuntimePaths` ajustado.
5. **Un solo mecanismo de migración.** El dump inicial (`runtime/db/seed/maxter.sql`, exportado de la base oficial) se usa **solo para la instalación inicial**. Las evoluciones posteriores del esquema las sigue manejando el **`Updater` existente del sistema** (que `index.php` ya ejecuta). El Runtime **no** implementa migraciones. → Task 8b acotada a instalación inicial.

Árbol de distribución actualizado:

```
MaxterServer/
├── MaxterServer.exe
├── config/
│   ├── maxter.config           ← config de alto nivel (puerto, ruta de app)
│   ├── httpd.generated.conf    ← generado al arrancar
│   └── my.generated.ini        ← generado al arrancar
├── runtime/
│   ├── manifest.json           ← versiones + rutas + integridad de componentes
│   ├── web/  (Apache)   php/   db/ (+ seed/maxter.sql)
├── app/                        ← sistema Maxter (DocumentRoot)
└── logs/  web.log  db.log  maxter-server.log
```

---

## 1. Decisiones técnicas (resumen para aprobar)

| Tema | Decisión | Por qué |
|---|---|---|
| **Proyecto** | Aplicación de **consola .NET 8 (LTS)**, C#. Nombre del ejecutable: `MaxterServer.exe`. | Consola = supervisor sin ventana propia; base para futuras fases (tray/servicio) sin rehacer. |
| **Distribución** | **Self-contained, single-file** (`win-x64`). Un solo `.exe` que **incluye el runtime .NET**. | Encapsulación total: el usuario no instala .NET ni ve dependencias. (Native AOT queda como optimización futura, no MVP.) |
| **Librerías** | **Solo la BCL de .NET** + P/Invoke a Win32. Para tests, **xUnit**. **Sin paquetes NuGet de terceros.** | Máxima robustez y cero dependencias externas. `System.Diagnostics.Process`, `System.Net.Sockets`, `System.Net.Http`, `System.Net.NetworkInformation`, `System.Text.Json`. |
| **Arranque de procesos** | `System.Diagnostics.Process`, con `stdout`/`stderr` redirigidos a `logs/`. | API estándar, control total del ciclo de vida. |
| **Control de procesos hijos** | **Windows Job Object** con `JOB_OBJECT_LIMIT_KILL_ON_JOB_CLOSE` (vía P/Invoke). Todos los hijos se asignan al Job. | Garantiza que **si el supervisor muere (incluso por crash o kill), Windows mata TODO el árbol de procesos**. Es la forma profesional y a prueba de huérfanos. |
| **Detección de muerte** | Evento `Process.Exited` + bucle monitor. Salida inesperada de un hijo = fatal → apagado ordenado. | Detecta si Apache o MySQL se caen y reacciona. |
| **Verificar que levantaron** | Sondeo **TCP** a `127.0.0.1:3306` (MySQL) y **HTTP GET** a `127.0.0.1:<puerto>` (Apache), con timeout. | "Proceso vivo" ≠ "servicio listo"; se confirma que el puerto responde de verdad. |
| **Cierre correcto** | Handler de `Ctrl+C` y de cierre de consola → apagado ordenado (`mysqladmin shutdown`, `httpd -k stop`) y luego `Dispose` del Job Object. | Cierre limpio de la DB + red de seguridad del Job. |
| **Publicar la app** | Generar `httpd.conf` al inicio desde una plantilla, con `ServerRoot` absoluto, `DocumentRoot` = `app/`, `Listen 0.0.0.0:<puerto>`. | La app se sirve sin tocarla; `.htaccess`/`mod_rewrite` siguen intactos. |

---

## 2. Componentes internos: cómo se incluyen Apache, PHP y MySQL

Los binarios se **vendorizan** (viven dentro del árbol del Runtime, versionados como parte del producto). El supervisor **no** depende de ninguna instalación del sistema.

**Árbol del producto distribuido:**

```
MaxterServer/
├── MaxterServer.exe            ← único ejecutable visible (self-contained)
├── maxter.config               ← config de alto nivel (puerto, ruta de app)
├── runtime/                    ← componentes internos (invisibles)
│   ├── web/                    ← Apache 2.4 vendorizado
│   │   ├── bin/httpd.exe
│   │   ├── modules/            ← incl. mod_php
│   │   └── conf/httpd.conf.tmpl   ← PLANTILLA (el .conf real se genera al arrancar)
│   ├── php/
│   │   ├── php.ini             ← con extensión mysql habilitada
│   │   └── ext/
│   └── db/                     ← MariaDB/MySQL vendorizado
│       ├── bin/mysqld.exe
│       ├── bin/mysqladmin.exe
│       ├── my.ini.tmpl         ← PLANTILLA
│       └── data/               ← datadir del Runtime (base "maxter")
├── app/                        ← carpeta del sistema Maxter (DocumentRoot)
└── logs/                       ← salida de web, php y db
```

**Cómo encajan las piezas sin tocar la app:**

- **Rutas absolutas al arrancar.** Apache exige `ServerRoot` absoluto. El supervisor resuelve la ruta real del Runtime en tiempo de ejecución y **genera** `httpd.conf`/`my.ini` desde sus plantillas sustituyendo tokens (`{{SERVER_ROOT}}`, `{{DOC_ROOT}}`, `{{PORT}}`, `{{PHP_DIR}}`, `{{DATA_DIR}}`). Así el producto es **portable** (se mueve de carpeta sin reconfigurar).
- **PHP como módulo.** `httpd.conf` carga `mod_php` apuntando a `runtime/php` y a `php.ini` (con la extensión `mysql` activa, requerida por SYPFW). Cero FastCGI.
- **DB propia.** `mysqld` usa `--defaults-file` = `my.ini` generado, con `datadir` = `runtime/db/data`. En primer arranque, si el `data/` no está inicializado, el supervisor lo inicializa e importa la base `maxter` (ver Task 8b); en arranques siguientes solo levanta.

### 2.1 Binarios internos fijados (versiones oficiales del Runtime)

Decisión: el Runtime **provee y fija** estos binarios (Windows x64). La coherencia **VC11 + Thread-Safe + x64** entre Apache y PHP es obligatoria: `mod_php` de PHP 5.6 (`php5apache2_4.dll`) está compilado con VC11, por lo que Apache **debe** ser el build VC11.

| Componente | Versión fijada | Origen | Nota de compatibilidad |
|---|---|---|---|
| Servidor web | **Apache httpd 2.4.25 VC11 x64** | Apache Lounge (último build **VC11**) | Debe ser VC11 para que cargue `mod_php` de PHP 5.6. |
| PHP | **PHP 5.6.40 VC11 x64 Thread-Safe** | windows.php.net (archives) | TS obligatorio para módulo de Apache. Incluye `php_mysql.dll` (ext. `mysql` legacy que exige SYPFW). |
| Base de datos | **MariaDB 10.6.x x64 (ZIP)** | mariadb.org | Usa `mysql_native_password` por defecto → compatible con el cliente `ext/mysql` antiguo de PHP 5.6. |
| Runtime C++ | **Visual C++ 2012 (VC11) Redistributable x64** | Microsoft | Requerido por Apache y PHP VC11. Se vendoriza o se verifica su presencia al arrancar. |

> El `root` de la DB debe autenticar con `mysql_native_password` (default en MariaDB 10.6) para que `mysql_connect()` de SYPFW funcione. No usar esquemas de auth modernos (`ed25519`, `caching_sha2`).

---

## 3. Estructura del proyecto (código fuente)

```
MaxterServer.sln
src/MaxterServer/
├── MaxterServer.csproj
├── Program.cs                  ← orquestación del arranque (secuencia 1–8)
├── MaxterConfig.cs             ← lee maxter.config (JSON)
├── RuntimePaths.cs             ← resuelve rutas internas a partir del .exe
├── Logging/FileLog.cs          ← log simple a archivo/consola
├── Processes/JobObject.cs      ← wrapper P/Invoke del Job Object
├── Processes/ManagedProcess.cs ← arranque + eventos de un proceso hijo
├── Processes/ProcessSupervisor.cs ← arranca/detiene/monitorea todos los hijos
├── Config/ConfigWriter.cs      ← genera httpd.conf / my.ini desde plantillas
├── Config/DbInitializer.cs     ← inicializa datadir + importa maxter.sql en 1er arranque
├── Health/ReadinessCheck.cs    ← sondeo TCP/HTTP de readiness
└── Net/LanAddress.cs           ← detecta la IP LAN para mostrar la URL
tests/MaxterServer.Tests/
├── MaxterServer.Tests.csproj
├── ConfigWriterTests.cs        ← sustitución de tokens de plantilla
├── MaxterConfigTests.cs        ← parseo/valores por defecto
└── ReadinessCheckTests.cs      ← readiness contra un listener de prueba
```

**Nota de testing (pragmática, sin sobreingeniería):** se hacen tests unitarios solo de la lógica pura y determinista (generación de config, parseo, readiness contra un socket de prueba). La orquestación de procesos reales (Apache/MySQL) se valida con una **verificación end-to-end manual** (Task 10), porque testear binarios externos reales no aporta valor en un MVP.

---

## 4. Tareas

> Cada tarea produce un cambio autocontenido y termina en commit. Las tareas con lógica pura llevan test; las de integración llevan verificación manual explícita.

### Task 1 — Solución y esqueleto de proyectos

**Files:** `MaxterServer.sln`, `src/MaxterServer/MaxterServer.csproj`, `src/MaxterServer/Program.cs`, `tests/MaxterServer.Tests/MaxterServer.Tests.csproj`

- [ ] **Step 1:** Crear solución y proyectos.
```bash
dotnet new sln -n MaxterServer
dotnet new console -n MaxterServer -o src/MaxterServer -f net8.0
dotnet new xunit -n MaxterServer.Tests -o tests/MaxterServer.Tests -f net8.0
dotnet sln add src/MaxterServer tests/MaxterServer.Tests
dotnet add tests/MaxterServer.Tests reference src/MaxterServer
```
- [ ] **Step 2:** Fijar `MaxterServer.csproj` para Windows y single-file self-contained.
```xml
<PropertyGroup>
  <OutputType>Exe</OutputType>
  <TargetFramework>net8.0</TargetFramework>
  <RuntimeIdentifier>win-x64</RuntimeIdentifier>
  <SelfContained>true</SelfContained>
  <PublishSingleFile>true</PublishSingleFile>
  <IncludeNativeLibrariesForSelfExtract>true</IncludeNativeLibrariesForSelfExtract>
  <AssemblyName>MaxterServer</AssemblyName>
  <Nullable>enable</Nullable>
  <InvariantGlobalization>true</InvariantGlobalization>
</PropertyGroup>
```
- [ ] **Step 3:** Compilar en vacío. Run: `dotnet build`. Expected: build OK.
- [ ] **Step 4:** Commit — `chore: scaffold MaxterServer solution and projects`.

### Task 2 — `RuntimePaths` y `MaxterConfig`

**Files:** Create `src/MaxterServer/RuntimePaths.cs`, `src/MaxterServer/MaxterConfig.cs`; Test `tests/MaxterServer.Tests/MaxterConfigTests.cs`

- [ ] **Step 1 (test primero):** parseo de config con defaults.
```csharp
[Fact]
public void Loads_defaults_when_fields_missing() {
    var cfg = MaxterConfig.FromJson("{}");
    Assert.Equal(80, cfg.Port);
    Assert.Equal("app", cfg.AppFolder);
}
[Fact]
public void Reads_overrides() {
    var cfg = MaxterConfig.FromJson("{\"port\":8080,\"appFolder\":\"sistema\"}");
    Assert.Equal(8080, cfg.Port);
    Assert.Equal("sistema", cfg.AppFolder);
}
```
- [ ] **Step 2:** verificar que falla. Run: `dotnet test`. Expected: FAIL (no compila / no existe `MaxterConfig`).
- [ ] **Step 3:** implementar.
```csharp
// MaxterConfig.cs
using System.Text.Json;
public sealed class MaxterConfig {
    public int Port { get; init; } = 80;
    public string AppFolder { get; init; } = "app";
    public static MaxterConfig FromJson(string json) {
        using var doc = JsonDocument.Parse(json);
        var r = doc.RootElement;
        return new MaxterConfig {
            Port = r.TryGetProperty("port", out var p) ? p.GetInt32() : 80,
            AppFolder = r.TryGetProperty("appFolder", out var a) ? a.GetString()! : "app",
        };
    }
    public static MaxterConfig Load(string path) =>
        File.Exists(path) ? FromJson(File.ReadAllText(path)) : new MaxterConfig();
}
```
```csharp
// RuntimePaths.cs — todas las rutas derivan de la ubicación del .exe (portátil)
public sealed class RuntimePaths {
    public string Root { get; }
    public RuntimePaths(string? root = null) => Root = root ?? AppContext.BaseDirectory;
    public string Web       => Path.Combine(Root, "runtime", "web");
    public string HttpdExe  => Path.Combine(Web, "bin", "httpd.exe");
    public string HttpdTmpl => Path.Combine(Web, "conf", "httpd.conf.tmpl");
    public string HttpdConf => Path.Combine(Web, "conf", "httpd.generated.conf");
    public string Php       => Path.Combine(Root, "runtime", "php");
    public string Db        => Path.Combine(Root, "runtime", "db");
    public string MysqldExe => Path.Combine(Db, "bin", "mysqld.exe");
    public string DataDir   => Path.Combine(Db, "data");
    public string Logs      => Path.Combine(Root, "logs");
    public string AppDir(MaxterConfig c) => Path.Combine(Root, c.AppFolder);
}
```
- [ ] **Step 4:** Run `dotnet test`. Expected: PASS.
- [ ] **Step 5:** Commit — `feat: runtime path resolution and config loader`.

### Task 3 — `FileLog` (log mínimo)

**Files:** Create `src/MaxterServer/Logging/FileLog.cs`

- [ ] **Step 1:** implementar log simple (consola + archivo por componente). Sin librerías externas.
```csharp
public sealed class FileLog {
    private readonly object _gate = new();
    private readonly string _file;
    public FileLog(string logsDir, string name) {
        Directory.CreateDirectory(logsDir);
        _file = Path.Combine(logsDir, name + ".log");
    }
    public void Info(string msg) => Write("INFO", msg);
    public void Error(string msg) => Write("ERROR", msg);
    private void Write(string lvl, string msg) {
        var line = $"{DateTime.Now:yyyy-MM-dd HH:mm:ss} [{lvl}] {msg}";
        lock (_gate) { Console.WriteLine(line); File.AppendAllText(_file, line + Environment.NewLine); }
    }
}
```
- [ ] **Step 2:** `dotnet build`. Expected: OK.
- [ ] **Step 3:** Commit — `feat: minimal file logger`.

### Task 4 — `JobObject` (control del árbol de procesos)

**Files:** Create `src/MaxterServer/Processes/JobObject.cs`

- [ ] **Step 1:** wrapper P/Invoke con `KILL_ON_JOB_CLOSE`.
```csharp
using System.Diagnostics;
using System.Runtime.InteropServices;
internal sealed class JobObject : IDisposable {
    private IntPtr _handle;
    public JobObject() {
        _handle = CreateJobObject(IntPtr.Zero, null);
        if (_handle == IntPtr.Zero) throw new InvalidOperationException("CreateJobObject failed");
        var info = new JOBOBJECT_EXTENDED_LIMIT_INFORMATION {
            BasicLimitInformation = new JOBOBJECT_BASIC_LIMIT_INFORMATION { LimitFlags = JOB_OBJECT_LIMIT_KILL_ON_JOB_CLOSE }
        };
        int len = Marshal.SizeOf(info);
        IntPtr ptr = Marshal.AllocHGlobal(len);
        try {
            Marshal.StructureToPtr(info, ptr, false);
            if (!SetInformationJobObject(_handle, JobObjectExtendedLimitInformation, ptr, (uint)len))
                throw new InvalidOperationException("SetInformationJobObject failed");
        } finally { Marshal.FreeHGlobal(ptr); }
    }
    public void Add(Process p) {
        if (!AssignProcessToJobObject(_handle, p.Handle))
            throw new InvalidOperationException("AssignProcessToJobObject failed");
    }
    public void Dispose() { if (_handle != IntPtr.Zero) { CloseHandle(_handle); _handle = IntPtr.Zero; } }

    const int  JobObjectExtendedLimitInformation = 9;
    const uint JOB_OBJECT_LIMIT_KILL_ON_JOB_CLOSE = 0x2000;
    [DllImport("kernel32.dll", CharSet = CharSet.Unicode)] static extern IntPtr CreateJobObject(IntPtr a, string? name);
    [DllImport("kernel32.dll")] static extern bool SetInformationJobObject(IntPtr h, int t, IntPtr info, uint len);
    [DllImport("kernel32.dll")] static extern bool AssignProcessToJobObject(IntPtr h, IntPtr proc);
    [DllImport("kernel32.dll")] static extern bool CloseHandle(IntPtr h);
    [StructLayout(LayoutKind.Sequential)] struct JOBOBJECT_BASIC_LIMIT_INFORMATION {
        public long PerProcessUserTimeLimit, PerJobUserTimeLimit; public uint LimitFlags;
        public UIntPtr MinWorkingSet, MaxWorkingSet; public uint ActiveProcessLimit;
        public UIntPtr Affinity; public uint PriorityClass, SchedulingClass; }
    [StructLayout(LayoutKind.Sequential)] struct IO_COUNTERS {
        public ulong r, w, o, rt, wt, ot; }
    [StructLayout(LayoutKind.Sequential)] struct JOBOBJECT_EXTENDED_LIMIT_INFORMATION {
        public JOBOBJECT_BASIC_LIMIT_INFORMATION BasicLimitInformation; public IO_COUNTERS IoInfo;
        public UIntPtr ProcessMemoryLimit, JobMemoryLimit, PeakProcessMemoryUsed, PeakJobMemoryUsed; }
}
```
- [ ] **Step 2:** `dotnet build`. Expected: OK.
- [ ] **Step 3:** Commit — `feat: windows job object for guaranteed child cleanup`.

### Task 5 — `ConfigWriter` (generar httpd.conf / my.ini)

**Files:** Create `src/MaxterServer/Config/ConfigWriter.cs`; plantilla `runtime/web/conf/httpd.conf.tmpl`; Test `tests/MaxterServer.Tests/ConfigWriterTests.cs`

- [ ] **Step 1 (test primero):** sustitución de tokens.
```csharp
[Fact]
public void Replaces_all_tokens() {
    var tmpl = "Root={{SERVER_ROOT}} Doc={{DOC_ROOT}} Port={{PORT}} Php={{PHP_DIR}}";
    var outp = ConfigWriter.Render(tmpl, new() {
        ["SERVER_ROOT"]="C:/mx/web", ["DOC_ROOT"]="C:/mx/app", ["PORT"]="80", ["PHP_DIR"]="C:/mx/php" });
    Assert.Equal("Root=C:/mx/web Doc=C:/mx/app Port=80 Php=C:/mx/php", outp);
    Assert.DoesNotContain("{{", outp);
}
```
- [ ] **Step 2:** `dotnet test` → FAIL.
- [ ] **Step 3:** implementar `Render` + `WriteHttpdConf`.
```csharp
public static class ConfigWriter {
    public static string Render(string tmpl, Dictionary<string,string> tokens) {
        foreach (var kv in tokens) tmpl = tmpl.Replace("{{"+kv.Key+"}}", kv.Value);
        return tmpl;
    }
    public static void WriteHttpdConf(RuntimePaths p, MaxterConfig cfg) {
        string slash(string s) => s.Replace('\\','/');
        var tokens = new Dictionary<string,string> {
            ["SERVER_ROOT"] = slash(p.Web),
            ["DOC_ROOT"]    = slash(p.AppDir(cfg)),
            ["PORT"]        = cfg.Port.ToString(),
            ["PHP_DIR"]     = slash(p.Php),
        };
        File.WriteAllText(p.HttpdConf, Render(File.ReadAllText(p.HttpdTmpl), tokens));
    }
}
```
- [ ] **Step 4:** crear la plantilla `httpd.conf.tmpl` (extracto clave): `ServerRoot "{{SERVER_ROOT}}"`, `Listen 0.0.0.0:{{PORT}}`, carga de `mod_php` desde `{{PHP_DIR}}`, `DocumentRoot "{{DOC_ROOT}}"`, `<Directory "{{DOC_ROOT}}"> AllowOverride All Require all granted </Directory>` (AllowOverride All = `.htaccess` activo).
- [ ] **Step 5:** `dotnet test` → PASS.
- [ ] **Step 6:** Commit — `feat: generate apache config from template at startup`.

### Task 6 — `ReadinessCheck` (TCP + HTTP)

**Files:** Create `src/MaxterServer/Health/ReadinessCheck.cs`; Test `tests/MaxterServer.Tests/ReadinessCheckTests.cs`

- [ ] **Step 1 (test primero):** TCP contra un listener de prueba.
```csharp
[Fact]
public async Task Detects_open_port() {
    var l = new TcpListener(IPAddress.Loopback, 0); l.Start();
    int port = ((IPEndPoint)l.LocalEndpoint).Port;
    var ok = await ReadinessCheck.WaitTcpAsync("127.0.0.1", port, TimeSpan.FromSeconds(2));
    l.Stop();
    Assert.True(ok);
}
[Fact]
public async Task Times_out_on_closed_port() {
    var ok = await ReadinessCheck.WaitTcpAsync("127.0.0.1", 1, TimeSpan.FromMilliseconds(600));
    Assert.False(ok);
}
```
- [ ] **Step 2:** `dotnet test` → FAIL.
- [ ] **Step 3:** implementar.
```csharp
using System.Net.Sockets;
using System.Net.Http;
using System.Diagnostics;
public static class ReadinessCheck {
    public static async Task<bool> WaitTcpAsync(string host, int port, TimeSpan timeout) {
        var sw = Stopwatch.StartNew();
        while (sw.Elapsed < timeout) {
            try { using var c = new TcpClient(); await c.ConnectAsync(host, port); if (c.Connected) return true; }
            catch { await Task.Delay(250); }
        }
        return false;
    }
    public static async Task<bool> WaitHttpAsync(string url, TimeSpan timeout) {
        var sw = Stopwatch.StartNew();
        using var http = new HttpClient { Timeout = TimeSpan.FromSeconds(3) };
        while (sw.Elapsed < timeout) {
            try { var r = await http.GetAsync(url); if ((int)r.StatusCode < 500) return true; }
            catch { await Task.Delay(300); }
        }
        return false;
    }
}
```
- [ ] **Step 4:** `dotnet test` → PASS.
- [ ] **Step 5:** Commit — `feat: tcp/http readiness checks`.

### Task 7 — `ManagedProcess` + `ProcessSupervisor`

**Files:** Create `src/MaxterServer/Processes/ManagedProcess.cs`, `src/MaxterServer/Processes/ProcessSupervisor.cs`

- [ ] **Step 1:** `ManagedProcess` — arranque con logs redirigidos y evento de salida.
```csharp
using System.Diagnostics;
public sealed class ManagedProcess {
    public string Name { get; }
    public Process Proc { get; }
    public event Action<ManagedProcess>? Exited;
    public ManagedProcess(string name, string exe, string args, string workDir, FileLog log) {
        Name = name;
        Proc = new Process { EnableRaisingEvents = true, StartInfo = new ProcessStartInfo {
            FileName = exe, Arguments = args, WorkingDirectory = workDir,
            UseShellExecute = false, RedirectStandardOutput = true, RedirectStandardError = true,
            CreateNoWindow = true } };
        Proc.OutputDataReceived += (_, e) => { if (e.Data != null) log.Info(e.Data); };
        Proc.ErrorDataReceived  += (_, e) => { if (e.Data != null) log.Error(e.Data); };
        Proc.Exited += (_, __) => Exited?.Invoke(this);
    }
    public void Start() { Proc.Start(); Proc.BeginOutputReadLine(); Proc.BeginErrorReadLine(); }
    public bool IsAlive => !Proc.HasExited;
}
```
- [ ] **Step 2:** `ProcessSupervisor` — arranca hijos, los mete al Job Object, monitorea y detiene.
```csharp
public sealed class ProcessSupervisor : IDisposable {
    private readonly JobObject _job = new();
    private readonly List<ManagedProcess> _children = new();
    private readonly FileLog _log;
    private volatile bool _shuttingDown;
    public event Action<ManagedProcess>? UnexpectedExit;
    public ProcessSupervisor(FileLog log) => _log = log;
    public ManagedProcess Launch(string name, string exe, string args, string workDir, FileLog log) {
        var mp = new ManagedProcess(name, exe, args, workDir, log);
        mp.Exited += m => { if (!_shuttingDown) UnexpectedExit?.Invoke(m); };
        mp.Start();
        _job.Add(mp.Proc);       // ← si el supervisor muere, Windows mata a este hijo
        _children.Add(mp);
        return mp;
    }
    public void ShutdownAll(Action<ManagedProcess> gracefulStop) {
        _shuttingDown = true;
        foreach (var c in _children) {
            try { if (c.IsAlive) { gracefulStop(c); if (!c.Proc.WaitForExit(5000) && c.IsAlive) c.Proc.Kill(true); } }
            catch (Exception ex) { _log.Error($"stop {c.Name}: {ex.Message}"); }
        }
    }
    public void Dispose() => _job.Dispose();   // red de seguridad final
}
```
- [ ] **Step 3:** `dotnet build`. Expected: OK.
- [ ] **Step 4:** Commit — `feat: process supervisor with monitoring and job-object binding`.

### Task 8 — `ConsoleControl` + `LanAddress`

**Files:** Create `src/MaxterServer/ConsoleControl.cs`, `src/MaxterServer/Net/LanAddress.cs`

- [ ] **Step 1:** handler de `Ctrl+C` y cierre de consola que dispara el apagado ordenado.
```csharp
using System.Runtime.InteropServices;
public static class ConsoleControl {
    private static Action? _onExit;
    private static HandlerRoutine? _handler;   // evitar GC del delegate
    public static void OnShutdown(Action onExit) {
        _onExit = onExit;
        Console.CancelKeyPress += (_, e) => { e.Cancel = true; _onExit?.Invoke(); };
        _handler = _ => { _onExit?.Invoke(); return true; };
        SetConsoleCtrlHandler(_handler, true);
    }
    private delegate bool HandlerRoutine(int ctrlType);
    [DllImport("kernel32.dll")] private static extern bool SetConsoleCtrlHandler(HandlerRoutine handler, bool add);
}
```
- [ ] **Step 2:** `LanAddress` — primera IPv4 no-loopback para mostrar la URL de acceso.
```csharp
using System.Net;
using System.Net.NetworkInformation;
using System.Net.Sockets;
public static class LanAddress {
    public static string BestGuess() {
        foreach (var ni in NetworkInterface.GetAllNetworkInterfaces()) {
            if (ni.OperationalStatus != OperationalStatus.Up) continue;
            if (ni.NetworkInterfaceType is NetworkInterfaceType.Loopback or NetworkInterfaceType.Tunnel) continue;
            foreach (var a in ni.GetIPProperties().UnicastAddresses)
                if (a.Address.AddressFamily == AddressFamily.InterNetwork && !IPAddress.IsLoopback(a.Address))
                    return a.Address.ToString();
        }
        return "127.0.0.1";
    }
}
```
- [ ] **Step 3:** `dotnet build`. Expected: OK.
- [ ] **Step 4:** Commit — `feat: console shutdown handler and lan ip detection`.

### Task 8b — `DbInitializer` (init + import en primer arranque)

**Files:** Create `src/MaxterServer/Config/DbInitializer.cs`; recurso `runtime/db/seed/maxter.sql` (dump inicial de la base `maxter`)

Estrategia de primer arranque: si `runtime/db/data` está vacío → (1) inicializar el datadir de MariaDB, (2) levantar `mysqld` temporalmente, (3) crear la base `maxter` e importar `seed/maxter.sql`, (4) asegurar que `root` use `mysql_native_password` sin contraseña (como espera `configuration.php`), (5) detener el `mysqld` temporal. En arranques siguientes, `NeedsInit` es `false` y no hace nada.

- [ ] **Step 1:** implementar detección + inicialización.
```csharp
using System.Diagnostics;
public sealed class DbInitializer {
    private readonly RuntimePaths _p; private readonly FileLog _log;
    public DbInitializer(RuntimePaths p, FileLog log) { _p = p; _log = log; }
    public bool NeedsInit() =>
        !Directory.Exists(Path.Combine(_p.DataDir, "mysql")); // datadir sin sistema = no inicializado

    public void Initialize() {
        _log.Info("Primer arranque: inicializando base de datos...");
        Directory.CreateDirectory(_p.DataDir);
        // 1) crear datadir (MariaDB)
        Run(Path.Combine(_p.Db, "bin", "mariadb-install-db.exe"),
            $"--datadir=\"{_p.DataDir}\" --auth-root-authentication-method=normal");
        // 2) mysqld temporal
        var temp = StartTemp();
        try {
            if (!ReadinessCheck.WaitTcpAsync("127.0.0.1", 3306, TimeSpan.FromSeconds(30)).Result)
                throw new Exception("mysqld temporal no respondió durante init.");
            // 3) crear DB + importar seed + fijar auth de root
            var seed = Path.Combine(_p.Db, "seed", "maxter.sql");
            var mysql = Path.Combine(_p.Db, "bin", "mariadb.exe");
            Run(mysql, "-u root -e \"CREATE DATABASE IF NOT EXISTS maxter CHARACTER SET utf8;\"");
            if (File.Exists(seed))
                RunWithInput(mysql, "-u root maxter", File.ReadAllText(seed));
            Run(mysql, "-u root -e \"ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING ''; FLUSH PRIVILEGES;\"");
        }
        finally {
            // 5) detener mysqld temporal
            try { Run(Path.Combine(_p.Db, "bin", "mariadb-admin.exe"), "-u root shutdown"); } catch {}
            if (!temp.WaitForExit(8000)) temp.Kill(true);
        }
        _log.Info("Base de datos inicializada.");
    }

    private Process StartTemp() {
        var psi = new ProcessStartInfo(_p.MysqldExe,
            $"--datadir=\"{_p.DataDir}\" --skip-grant-tables=0 --port=3306") {
            UseShellExecute = false, CreateNoWindow = true };
        return Process.Start(psi)!;
    }
    private void Run(string exe, string args) {
        using var pr = Process.Start(new ProcessStartInfo(exe, args){UseShellExecute=false})!;
        pr.WaitForExit();
        if (pr.ExitCode != 0) throw new Exception($"{Path.GetFileName(exe)} salió con código {pr.ExitCode}");
    }
    private void RunWithInput(string exe, string args, string stdin) {
        using var pr = Process.Start(new ProcessStartInfo(exe, args){UseShellExecute=false, RedirectStandardInput=true})!;
        pr.StandardInput.Write(stdin); pr.StandardInput.Close(); pr.WaitForExit();
        if (pr.ExitCode != 0) throw new Exception($"Import de seed falló (código {pr.ExitCode})");
    }
}
```
> Los nombres de binarios (`mariadb-install-db.exe`, `mariadb.exe`, `mariadb-admin.exe`) corresponden a MariaDB 10.6; se confirman al vendorizar y se ajustan si el paquete usa los alias `mysql*`.
- [ ] **Step 2:** `dotnet build`. Expected: OK.
- [ ] **Step 3:** Commit — `feat: first-run database initialization and seed import`.

### Task 9 — `Program.cs` (orquestación: la secuencia 1–8)

**Files:** Modify `src/MaxterServer/Program.cs`

- [ ] **Step 1:** implementar el arranque completo, en orden DB → web, con verificación y apagado ordenado.
```csharp
var paths  = new RuntimePaths();
var cfg    = MaxterConfig.Load(Path.Combine(paths.Root, "maxter.config"));
var log    = new FileLog(paths.Logs, "maxter-server");
var dbLog  = new FileLog(paths.Logs, "db");
var webLog = new FileLog(paths.Logs, "web");

log.Info("== Maxter Server: iniciando Runtime ==");
using var sup = new ProcessSupervisor(log);
var done = new ManualResetEventSlim(false);
int exitCode = 0;

void Shutdown(string reason) {
    log.Info($"Cerrando Runtime ({reason})...");
    sup.ShutdownAll(c => {
        if (c.Name == "db")  Process.Start(new ProcessStartInfo(paths.MysqldExe.Replace("mysqld","mysqladmin"),
                               "-u root shutdown") { UseShellExecute = false });
        if (c.Name == "web") Process.Start(new ProcessStartInfo(paths.HttpdExe,
                               $"-f \"{paths.HttpdConf}\" -k stop") { UseShellExecute = false });
    });
    done.Set();
}
sup.UnexpectedExit += m => { log.Error($"Componente '{m.Name}' se detuvo inesperadamente."); exitCode = 1; Shutdown($"caída de {m.Name}"); };
ConsoleControl.OnShutdown(() => Shutdown("solicitado por el usuario"));

try {
    // (1) Generar configuración interna
    ConfigWriter.WriteHttpdConf(paths, cfg);

    // (2) Inicializar la DB si es el primer arranque, luego iniciar el motor
    var dbInit = new DbInitializer(paths, dbLog);
    if (dbInit.NeedsInit()) dbInit.Initialize();
    log.Info("Iniciando motor de base de datos...");
    sup.Launch("db", paths.MysqldExe, $"--defaults-file=\"{Path.Combine(paths.Db, "my.ini")}\"", paths.Db, dbLog);
    if (!await ReadinessCheck.WaitTcpAsync("127.0.0.1", 3306, TimeSpan.FromSeconds(30)))
        throw new Exception("La base de datos no respondió en el puerto 3306.");
    log.Info("Base de datos lista.");

    // (3) Iniciar servidor web + PHP (publica app/)
    log.Info("Iniciando servidor web...");
    sup.Launch("web", paths.HttpdExe, $"-f \"{paths.HttpdConf}\" -d \"{paths.Web}\"", paths.Web, webLog);
    if (!await ReadinessCheck.WaitHttpAsync($"http://127.0.0.1:{cfg.Port}/", TimeSpan.FromSeconds(20)))
        throw new Exception($"El servidor web no respondió en el puerto {cfg.Port}.");
    log.Info("Servidor web listo.");

    // (4) Publicar accesos
    var lan = LanAddress.BestGuess();
    log.Info("=====================================================");
    log.Info("  Maxter Server EN LÍNEA");
    log.Info($"  Local:  http://localhost{(cfg.Port==80?"":":"+cfg.Port)}");
    log.Info($"  LAN:    http://{lan}{(cfg.Port==80?"":":"+cfg.Port)}");
    log.Info("  (Ctrl+C para detener)");
    log.Info("=====================================================");
}
catch (Exception ex) {
    log.Error("Fallo de arranque: " + ex.Message);
    exitCode = 1;
    Shutdown("error de arranque");
}

done.Wait();                 // bloquea hasta Ctrl+C o caída
sup.Dispose();               // Job Object: garantiza que no queden huérfanos
log.Info("Runtime detenido.");
return exitCode;
```
- [ ] **Step 2:** `dotnet build`. Expected: OK.
- [ ] **Step 3:** Commit — `feat: runtime boot orchestration (db -> web) with readiness and clean shutdown`.

### Task 10 — Publicación y verificación end-to-end (MVP cumplido)

**Files:** none (empaquetado + prueba real). Requiere los binarios vendorizados en `runtime/` y la app en `app/`.

- [ ] **Step 1:** publicar el ejecutable único.
```bash
dotnet publish src/MaxterServer -c Release -r win-x64 --self-contained true -p:PublishSingleFile=true -o dist
```
Expected: `dist/MaxterServer.exe` (un solo archivo, sin instalar .NET).
- [ ] **Step 2:** montar el árbol de distribución: copiar `MaxterServer.exe`, `maxter.config`, `runtime/` (web+php+db vendorizados) y `app/` (contenido de `SISTEMA maxter 2.0`) a una carpeta limpia.
- [ ] **Step 3:** ejecutar `MaxterServer.exe`. Expected en consola: "Base de datos lista." → "Servidor web listo." → banner con URLs local y LAN.
- [ ] **Step 4 (verificación readiness):** abrir `http://localhost` en el navegador de la PC servidor → carga el sistema Maxter. Abrir `http://IP_DEL_SERVIDOR` desde otra PC de la LAN → carga igual.
- [ ] **Step 5 (verificación de cierre limpio):** `Ctrl+C` en la consola → mensajes de cierre. Confirmar en el Administrador de tareas que **no quedan** `httpd.exe` ni `mysqld.exe`.
- [ ] **Step 6 (verificación anti-huérfanos):** relanzar, luego **matar `MaxterServer.exe`** desde el Administrador de tareas → confirmar que Windows (Job Object) también cerró `httpd.exe` y `mysqld.exe`.
- [ ] **Step 7 (verificación de error):** renombrar temporalmente `runtime/db/bin/mysqld.exe`, ejecutar → debe reportar "Fallo de arranque" y salir sin dejar procesos. Restaurar el nombre.
- [ ] **Step 8:** Commit — `chore: fase 1 MVP end-to-end verified`.

---

## 5. Cómo cada requisito tuyo queda cubierto

| Requisito | Dónde se resuelve |
|---|---|
| Inicializar el Runtime | Task 9, paso 1 (config interna) |
| Iniciar Apache / PHP | Task 9 (web + mod_php) · Task 5 (config) |
| Iniciar MySQL | Task 9 (db) |
| Publicar la carpeta de la app | Task 5 (`DocumentRoot=app/`, `AllowOverride All`) |
| Acceso `http://localhost` y `http://IP` LAN | Task 5 (`Listen 0.0.0.0`) · Task 8 (IP LAN) · Task 10 (verif.) |
| Iniciar procesos desde `MaxterServer.exe` | Task 7 + Task 9 |
| Detener todo al cerrar | Task 8 (handler) · Task 9 (Shutdown) · Task 4 (Job Object) |
| Detectar errores de inicio | Task 9 (try/catch + timeouts de readiness) |
| Verificar que Apache/MySQL levantaron | Task 6 (TCP/HTTP) usado en Task 9 |
| No tocar app/SYPFW/index/config | Todo el Runtime es externo; solo lee `app/` y genera SUS propios `.conf` |
| Apache/PHP/MySQL invisibles | Encapsulados en `runtime/`; el usuario solo ve `MaxterServer.exe` |

## 6. Fuera de alcance (Fase 1) — confirmado

GUI, tray, servicio Windows, instalador, updates, backups, monitoreo, logs avanzados, seguridad extra, configuración automática, panel. **Nada de eso se implementa ahora.**

## 7. Decisiones cerradas y supuestos restantes

**Cerradas (esta ronda):**

1. **Lenguaje:** C# / .NET 8, single-file self-contained. ✔
2. **Binarios:** los fija el Runtime — matriz de la sección 2.1 (Apache 2.4.25 VC11 x64 · PHP 5.6.40 VC11 x64 TS · MariaDB 10.6.x · VC11 Redist). ✔
3. **DB:** inicialización + import en primer arranque (Task 8b). Requiere un dump `runtime/db/seed/maxter.sql`. ✔

**Supuestos restantes a validar:**

4. **Dump de la base `maxter`**: para el import de primer arranque hace falta un `maxter.sql`. ¿Lo exportás vos desde la DB actual, o el Runtime arranca con una base vacía/mínima en esta fase?
5. **Puerto 80 libre** en la PC servidor (que no lo tome IIS u otro). Configurable en `maxter.config`.
6. **Firewall LAN**: el acceso desde otras PCs requiere permitir la conexión entrante al puerto (no se automatiza en Fase 1; se documenta).
