using System.Text.Json;
using System.Text.Json.Serialization;

namespace MaxterServer;

/// <summary>
/// Describe un componente interno del Runtime (servidor web, php o base de datos).
/// Las versiones y rutas NO estan hardcodeadas: provienen de runtime/manifest.json,
/// de modo que actualizar un componente sea editar el manifiesto + reemplazar binarios.
/// </summary>
public sealed class ComponentInfo
{
    /// <summary>Identificador logico: "web", "php", "db".</summary>
    [JsonPropertyName("id")] public string Id { get; init; } = "";

    /// <summary>Nombre para mostrar/log: p.ej. "Apache httpd".</summary>
    [JsonPropertyName("name")] public string Name { get; init; } = "";

    /// <summary>Version fijada: p.ej. "2.4.25-VC11-x64".</summary>
    [JsonPropertyName("version")] public string Version { get; init; } = "";

    /// <summary>Carpeta del componente, relativa a runtime/: p.ej. "web".</summary>
    [JsonPropertyName("path")] public string Path { get; init; } = "";

    /// <summary>Ejecutable principal, relativo a <see cref="Path"/>: p.ej. "bin/httpd.exe".</summary>
    [JsonPropertyName("executable")] public string Executable { get; init; } = "";

    /// <summary>
    /// Archivos cuya existencia (y opcionalmente checksum) valida el Runtime antes de arrancar.
    /// Clave = ruta relativa a <see cref="Path"/>; valor = SHA-256 en hex (o "" para solo-existencia).
    /// </summary>
    [JsonPropertyName("integrity")] public Dictionary<string, string> Integrity { get; init; } = new();
}

/// <summary>
/// Manifiesto del Runtime. Fuente unica de verdad sobre que componentes existen,
/// que version tienen y donde estan. Se carga en ejecucion desde runtime/manifest.json.
/// </summary>
public sealed class Manifest
{
    [JsonPropertyName("runtimeName")] public string RuntimeName { get; init; } = "Maxter Server";
    [JsonPropertyName("runtimeVersion")] public string RuntimeVersion { get; init; } = "1.0.0";
    [JsonPropertyName("components")] public List<ComponentInfo> Components { get; init; } = new();

    private static readonly JsonSerializerOptions Options = new()
    {
        PropertyNameCaseInsensitive = true,
        ReadCommentHandling = JsonCommentHandling.Skip,
        AllowTrailingCommas = true,
    };

    public static Manifest FromJson(string json)
    {
        var m = JsonSerializer.Deserialize<Manifest>(json, Options)
                ?? throw new InvalidDataException("El manifiesto esta vacio o es invalido.");
        return m;
    }

    public static Manifest Load(string path)
    {
        if (!File.Exists(path))
            throw new FileNotFoundException($"No se encontro el manifiesto del Runtime: {path}");
        return FromJson(File.ReadAllText(path));
    }

    /// <summary>Devuelve un componente por id logico, o lanza si no existe.</summary>
    public ComponentInfo Component(string id) =>
        Components.FirstOrDefault(c => string.Equals(c.Id, id, StringComparison.OrdinalIgnoreCase))
        ?? throw new KeyNotFoundException($"El manifiesto no declara el componente '{id}'.");
}
