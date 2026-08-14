namespace MaxterServer.Abstractions;

/// <summary>
/// Descripcion neutral de como arrancar un proceso hijo. La producen los componentes
/// (IWebServer / IDatabaseServer) y la consume el ProcessSupervisor, que no necesita
/// saber si detras hay Apache, MariaDB u otro motor.
/// </summary>
public sealed record ProcessStartSpec(string FileName, string Arguments, string WorkingDirectory);
