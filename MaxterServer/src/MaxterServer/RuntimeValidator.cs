using System.Security.Cryptography;
using MaxterServer.Logging;

namespace MaxterServer;

/// <summary>Resultado de la validacion previa del Runtime.</summary>
public sealed class ValidationResult
{
    public List<string> Problems { get; } = new();
    public bool Ok => Problems.Count == 0;
}

/// <summary>
/// Etapa de validacion previa: antes de arrancar ningun proceso, verifica que todos los
/// componentes declarados en el manifiesto existan y (si el manifiesto trae checksum)
/// que su integridad coincida. Si algo falta o no coincide, el Runtime aborta con un
/// mensaje claro en vez de intentar arrancar binarios rotos o incompletos.
/// </summary>
public sealed class RuntimeValidator
{
    private readonly RuntimePaths _paths;
    private readonly Manifest _manifest;
    private readonly FileLog _log;

    public RuntimeValidator(RuntimePaths paths, Manifest manifest, FileLog log)
    {
        _paths = paths;
        _manifest = manifest;
        _log = log;
    }

    public ValidationResult Validate()
    {
        var result = new ValidationResult();
        _log.Info("Validando componentes del Runtime...");

        foreach (var component in _manifest.Components)
        {
            var dir = _paths.ComponentDir(component);
            if (!Directory.Exists(dir))
            {
                result.Problems.Add($"[{component.Id}] falta la carpeta del componente: {dir}");
                continue;
            }

            // Ejecutable principal
            var exe = _paths.ComponentExecutable(component);
            if (!string.IsNullOrEmpty(component.Executable) && !File.Exists(exe))
                result.Problems.Add($"[{component.Id}] falta el ejecutable: {exe}");

            // Archivos declarados para integridad
            foreach (var (relative, expectedHash) in component.Integrity)
            {
                var file = _paths.ComponentFile(component, relative);
                if (!File.Exists(file))
                {
                    result.Problems.Add($"[{component.Id}] falta el archivo: {file}");
                    continue;
                }

                if (!string.IsNullOrWhiteSpace(expectedHash))
                {
                    var actual = Sha256(file);
                    if (!string.Equals(actual, expectedHash, StringComparison.OrdinalIgnoreCase))
                        result.Problems.Add(
                            $"[{component.Id}] integridad incorrecta en {relative}: esperado {expectedHash}, obtenido {actual}");
                }
            }

            if (result.Ok)
                _log.Info($"  OK: {component.Name} ({component.Version})");
        }

        if (result.Ok)
            _log.Info("Validacion completa: todos los componentes estan presentes e integros.");
        else
            foreach (var p in result.Problems) _log.Error("  " + p);

        return result;
    }

    private static string Sha256(string file)
    {
        using var sha = SHA256.Create();
        using var stream = File.OpenRead(file);
        return Convert.ToHexString(sha.ComputeHash(stream));
    }
}
