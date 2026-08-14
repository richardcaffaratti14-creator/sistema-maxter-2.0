namespace MaxterServer.Config;

/// <summary>
/// Motor de plantillas trivial: reemplaza tokens {{CLAVE}} por valores. Se usa para generar
/// httpd.conf y my.ini a partir de plantillas, inyectando rutas absolutas resueltas en ejecucion.
/// </summary>
public static class TemplateEngine
{
    public static string Render(string template, IReadOnlyDictionary<string, string> tokens)
    {
        foreach (var kv in tokens)
            template = template.Replace("{{" + kv.Key + "}}", kv.Value);
        return template;
    }
}
