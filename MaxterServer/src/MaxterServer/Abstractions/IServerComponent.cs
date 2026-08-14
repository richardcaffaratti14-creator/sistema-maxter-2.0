namespace MaxterServer.Abstractions;

/// <summary>
/// Contrato comun de un motor gestionado por el Runtime. El resto del Runtime depende
/// de esta abstraccion, nunca del nombre concreto del motor (Apache, MariaDB, etc.).
/// </summary>
public interface IServerComponent
{
    /// <summary>Id logico del componente ("web", "db").</summary>
    string Id { get; }

    /// <summary>Nombre para logs/UI (p.ej. "servidor web").</summary>
    string DisplayName { get; }

    /// <summary>Genera/prepara la configuracion necesaria antes de arrancar el proceso.</summary>
    void PrepareConfiguration();

    /// <summary>Devuelve como lanzar el proceso (exe, argumentos, working dir).</summary>
    ProcessStartSpec BuildStartSpec();

    /// <summary>Espera activa hasta que el motor responde de verdad (no solo "proceso vivo").</summary>
    Task<bool> WaitUntilReadyAsync(TimeSpan timeout);

    /// <summary>Solicita un apagado ordenado del motor (best-effort).</summary>
    void RequestGracefulStop();

    /// <summary>
    /// Ejecuta y registra un diagnostico de arranque del motor (best-effort). Se invoca
    /// cuando el componente no queda listo, para volcar al log la causa exacta.
    /// Default: no-op; cada motor puede sobreescribirlo.
    /// </summary>
    void LogStartupDiagnostics() { }
}
