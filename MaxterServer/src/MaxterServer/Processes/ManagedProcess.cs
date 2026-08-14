using System.Diagnostics;
using MaxterServer.Abstractions;
using MaxterServer.Logging;

namespace MaxterServer.Processes;

/// <summary>
/// Un proceso hijo gestionado: lo arranca, redirige su salida al log del componente
/// y expone un evento cuando termina. No sabe de Apache/MariaDB; recibe un ProcessStartSpec.
/// </summary>
public sealed class ManagedProcess
{
    public string Name { get; }
    public Process Proc { get; }

    /// <summary>Se dispara cuando el proceso termina (por la razon que sea).</summary>
    public event Action<ManagedProcess>? Exited;

    public ManagedProcess(string name, ProcessStartSpec spec, FileLog log)
    {
        Name = name;
        Proc = new Process
        {
            EnableRaisingEvents = true,
            StartInfo = new ProcessStartInfo
            {
                FileName = spec.FileName,
                Arguments = spec.Arguments,
                WorkingDirectory = spec.WorkingDirectory,
                UseShellExecute = false,
                RedirectStandardOutput = true,
                RedirectStandardError = true,
                CreateNoWindow = true,
            }
        };

        Proc.OutputDataReceived += (_, e) => { if (e.Data != null) log.Info(e.Data); };
        Proc.ErrorDataReceived += (_, e) => { if (e.Data != null) log.Error(e.Data); };
        Proc.Exited += (_, _) => Exited?.Invoke(this);
    }

    public void Start()
    {
        Proc.Start();
        Proc.BeginOutputReadLine();
        Proc.BeginErrorReadLine();
    }

    public bool IsAlive
    {
        get { try { return !Proc.HasExited; } catch { return false; } }
    }
}
