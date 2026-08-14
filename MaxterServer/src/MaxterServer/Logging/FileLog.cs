namespace MaxterServer.Logging;

/// <summary>
/// Log minimo, thread-safe: escribe a consola y a un archivo por componente en logs/.
/// Sin dependencias externas (requisito de encapsulacion del Runtime).
/// </summary>
public sealed class FileLog
{
    private readonly object _gate = new();
    private readonly string _file;
    private readonly string _tag;

    /// <param name="logsDir">Carpeta logs/ del Runtime.</param>
    /// <param name="name">Nombre del componente: crea logs/{name}.log (p.ej. "web", "db").</param>
    public FileLog(string logsDir, string name)
    {
        Directory.CreateDirectory(logsDir);
        _file = Path.Combine(logsDir, name + ".log");
        _tag = name;
    }

    public void Info(string message) => Write("INFO", message);
    public void Error(string message) => Write("ERROR", message);

    private void Write(string level, string message)
    {
        var line = $"{DateTime.Now:yyyy-MM-dd HH:mm:ss} [{level}] [{_tag}] {message}";
        lock (_gate)
        {
            Console.WriteLine(line);
            try { File.AppendAllText(_file, line + Environment.NewLine); }
            catch { /* el log nunca debe tumbar el Runtime */ }
        }
    }
}
