using System.Runtime.InteropServices;
using System.Runtime.Versioning;

namespace MaxterServer;

/// <summary>
/// Captura Ctrl+C y el cierre de la ventana de consola para disparar un apagado ordenado
/// del Runtime. Mantiene una referencia al delegate para que el GC no lo recolecte.
/// </summary>
[SupportedOSPlatform("windows")]
public static class ConsoleControl
{
    private static Action? _onExit;
    private static HandlerRoutine? _handler;

    public static void OnShutdown(Action onExit)
    {
        _onExit = onExit;

        Console.CancelKeyPress += (_, e) =>
        {
            e.Cancel = true; // evitamos el kill abrupto; hacemos apagado ordenado
            _onExit?.Invoke();
        };

        _handler = _ =>
        {
            _onExit?.Invoke();
            return true;
        };
        SetConsoleCtrlHandler(_handler, add: true);
    }

    private delegate bool HandlerRoutine(int ctrlType);

    [DllImport("kernel32.dll", SetLastError = true)]
    private static extern bool SetConsoleCtrlHandler(HandlerRoutine handler, bool add);
}
