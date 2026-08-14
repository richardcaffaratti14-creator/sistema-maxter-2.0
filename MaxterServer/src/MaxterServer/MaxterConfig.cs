using System.Text.Json;

namespace MaxterServer;

/// <summary>
/// Configuracion de alto nivel del Runtime (config/maxter.config, formato JSON).
/// Solo lo que el operador podria querer cambiar. Todo tiene valor por defecto sensato.
/// </summary>
public sealed class MaxterConfig
{
    /// <summary>Puerto TCP donde se publica la aplicacion. Default 80.</summary>
    public int Port { get; init; } = 80;

    /// <summary>Carpeta de la aplicacion a publicar (DocumentRoot), relativa a la raiz. Default "app".</summary>
    public string AppFolder { get; init; } = "app";

    /// <summary>Segundos maximos de espera a que la base de datos quede lista.</summary>
    public int DbStartTimeoutSeconds { get; init; } = 30;

    /// <summary>Segundos maximos de espera a que el servidor web quede listo.</summary>
    public int WebStartTimeoutSeconds { get; init; } = 20;

    public static MaxterConfig FromJson(string json)
    {
        using var doc = JsonDocument.Parse(json);
        var r = doc.RootElement;
        return new MaxterConfig
        {
            Port = r.TryGetProperty("port", out var p) ? p.GetInt32() : 80,
            AppFolder = r.TryGetProperty("appFolder", out var a) ? (a.GetString() ?? "app") : "app",
            DbStartTimeoutSeconds = r.TryGetProperty("dbStartTimeoutSeconds", out var d) ? d.GetInt32() : 30,
            WebStartTimeoutSeconds = r.TryGetProperty("webStartTimeoutSeconds", out var w) ? w.GetInt32() : 20,
        };
    }

    public static MaxterConfig Load(string path) =>
        File.Exists(path) ? FromJson(File.ReadAllText(path)) : new MaxterConfig();
}
