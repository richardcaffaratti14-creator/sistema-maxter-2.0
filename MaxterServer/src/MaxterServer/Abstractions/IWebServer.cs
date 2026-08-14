namespace MaxterServer.Abstractions;

/// <summary>Motor web que publica la aplicacion. Implementacion actual: Apache (invisible al usuario).</summary>
public interface IWebServer : IServerComponent
{
    /// <summary>URL de acceso local, p.ej. http://localhost.</summary>
    string LocalUrl { get; }

    /// <summary>URL de acceso desde la LAN, p.ej. http://192.168.0.10.</summary>
    string LanUrl { get; }
}
