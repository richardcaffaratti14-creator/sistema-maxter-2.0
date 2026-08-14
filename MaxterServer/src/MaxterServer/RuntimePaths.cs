namespace MaxterServer;

/// <summary>
/// Resuelve todas las rutas del Runtime a partir de una raiz LOCALIZADA (no asumida).
///
/// La raiz es la carpeta que contiene "runtime/manifest.json". Se busca en este orden:
///   1) variable de entorno MAXTER_HOME (override explicito para deploy/avanzado);
///   2) subiendo por el arbol de carpetas desde la ubicacion del ejecutable hasta
///      encontrar "runtime/manifest.json".
///
/// Asi funciona igual en produccion (runtime/ junto al .exe: se encuentra en la 1a
/// iteracion) y en desarrollo (el .exe queda en bin/Debug/net8.0 y runtime/ esta en la
/// raiz del proyecto: se encuentra subiendo). No hace falta copiar el runtime al output.
///
/// Al usar rutas relativas a esa raiz, el producto es portable. Estructura:
/// config/  runtime/{web,php,db}  app/  logs/.
/// </summary>
public sealed class RuntimePaths
{
    public string Root { get; }

    public RuntimePaths(string? root = null) =>
        Root = root ?? ResolveRoot(AppContext.BaseDirectory,
                                   Environment.GetEnvironmentVariable("MAXTER_HOME"));

    /// <summary>
    /// Localiza la raiz del Runtime. Expuesto internal para tests.
    /// Devuelve <paramref name="baseDir"/> como fallback si no encuentra nada (la
    /// validacion posterior reportara el problema con un mensaje claro).
    /// </summary>
    internal static string ResolveRoot(string baseDir, string? envOverride)
    {
        // 1) Override explicito.
        if (!string.IsNullOrWhiteSpace(envOverride) && HasRuntime(envOverride))
            return Path.GetFullPath(envOverride);

        // 2) Subir por el arbol hasta hallar runtime/manifest.json, IGNORANDO carpetas de
        //    salida de compilacion (bin/obj). Invariante: la raiz del Runtime NUNCA es una
        //    carpeta de build. Asi una copia parasita del runtime dentro de bin/ (p.ej.
        //    copiada a mano) no gana: se salta y el walk-up llega a la raiz real del proyecto.
        for (var dir = new DirectoryInfo(baseDir); dir != null; dir = dir.Parent)
        {
            if (IsBuildOutputPath(dir.FullName))
                continue;
            if (HasRuntime(dir.FullName))
                return dir.FullName;
        }

        return baseDir;
    }

    /// <summary>True si <paramref name="root"/> contiene runtime/manifest.json.</summary>
    internal static bool HasRuntime(string root) =>
        File.Exists(Path.Combine(root, "runtime", "manifest.json"));

    /// <summary>True si algun segmento de la ruta es 'bin' u 'obj' (salida de compilacion .NET).</summary>
    internal static bool IsBuildOutputPath(string path)
    {
        var parts = path.Split(Path.DirectorySeparatorChar, Path.AltDirectorySeparatorChar);
        foreach (var p in parts)
            if (p.Equals("bin", StringComparison.OrdinalIgnoreCase) ||
                p.Equals("obj", StringComparison.OrdinalIgnoreCase))
                return true;
        return false;
    }

    // --- Carpetas de primer nivel ---
    public string ConfigDir => Combine(Root, "config");
    public string RuntimeDir => Combine(Root, "runtime");
    public string LogsDir => Combine(Root, "logs");

    // --- Configuracion (config/) ---
    public string MaxterConfigFile => Combine(ConfigDir, "maxter.config");
    public string ApacheErrorLog => Combine(LogsDir, "apache-error.log");
    public string HttpdConfGenerated => Combine(ConfigDir, "httpd.generated.conf");
    public string MyIniGenerated => Combine(ConfigDir, "my.generated.ini");
    public string PhpIniGenerated => Combine(ConfigDir, "php.ini"); // PHP busca un archivo llamado exactamente "php.ini"

    // --- Manifiesto ---
    public string ManifestFile => Combine(RuntimeDir, "manifest.json");

    // --- Componentes (resueltos via manifiesto) ---
    public string ComponentDir(ComponentInfo c) => Combine(RuntimeDir, c.Path);
    public string ComponentExecutable(ComponentInfo c) => Combine(ComponentDir(c), c.Executable);
    public string ComponentFile(ComponentInfo c, string relative) => Combine(ComponentDir(c), relative);

    // --- Aplicacion publicada ---
    // appFolder puede ser relativa (p.ej. "app" o ".." para el sistema en la carpeta padre)
    // o una ruta absoluta. GetFullPath normaliza (resuelve ".." y separadores) para que
    // Apache reciba siempre un DocumentRoot absoluto y valido.
    public string AppDir(MaxterConfig cfg) => Path.GetFullPath(Combine(Root, cfg.AppFolder));

    private static string Combine(params string[] parts) => Path.Combine(parts);
}
