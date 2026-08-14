using System.Runtime.Versioning;
using MaxterServer.Abstractions;
using MaxterServer.Logging;

namespace MaxterServer.Processes;

/// <summary>
/// Arranca, vigila y detiene los procesos hijos del Runtime. Cada hijo se asigna a un
/// Job Object (anti-huerfanos). Si un hijo muere de forma inesperada, notifica para que
/// el Runtime haga un apagado ordenado.
/// </summary>
[SupportedOSPlatform("windows")]
public sealed class ProcessSupervisor : IDisposable
{
    private readonly JobObject _job = new();
    private readonly List<ManagedProcess> _children = new();
    private readonly FileLog _log;
    private volatile bool _shuttingDown;

    /// <summary>Se dispara cuando un hijo termina sin que se haya pedido el apagado.</summary>
    public event Action<ManagedProcess>? UnexpectedExit;

    public ProcessSupervisor(FileLog log) => _log = log;

    /// <summary>Lanza un proceso descripto por <paramref name="spec"/> y lo pone bajo supervision.</summary>
    public ManagedProcess Launch(string name, ProcessStartSpec spec, FileLog componentLog)
    {
        var mp = new ManagedProcess(name, spec, componentLog);
        mp.Exited += m =>
        {
            if (!_shuttingDown)
            {
                _log.Error($"El componente '{m.Name}' termino inesperadamente.");
                UnexpectedExit?.Invoke(m);
            }
        };

        mp.Start();
        _job.Add(mp.Proc); // si el supervisor muere, Windows mata a este hijo
        _children.Add(mp);
        _log.Info($"Componente '{name}' iniciado (PID {mp.Proc.Id}).");
        return mp;
    }

    /// <summary>
    /// Apagado ordenado: para cada hijo ejecuta <paramref name="gracefulStop"/> y espera;
    /// si no cierra en el tiempo dado, lo mata. Luego el Dispose del job es la red final.
    /// </summary>
    public void ShutdownAll(Action<ManagedProcess> gracefulStop, int gracefulTimeoutMs = 8000)
    {
        _shuttingDown = true;
        foreach (var c in _children)
        {
            try
            {
                if (!c.IsAlive) continue;
                gracefulStop(c);
                if (!c.Proc.WaitForExit(gracefulTimeoutMs) && c.IsAlive)
                {
                    _log.Error($"'{c.Name}' no cerro a tiempo; forzando cierre.");
                    c.Proc.Kill(entireProcessTree: true);
                }
            }
            catch (Exception ex)
            {
                _log.Error($"Error deteniendo '{c.Name}': {ex.Message}");
            }
        }
    }

    public void Dispose() => _job.Dispose();
}
