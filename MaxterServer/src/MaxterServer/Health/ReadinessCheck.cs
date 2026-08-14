using System.Diagnostics;
using System.Net.Http;
using System.Net.Sockets;

namespace MaxterServer.Health;

/// <summary>
/// Verifica que un servicio realmente responde ("proceso vivo" != "servicio listo").
/// TCP para la base de datos, HTTP para el servidor web.
/// </summary>
public static class ReadinessCheck
{
    /// <summary>Sondea un puerto TCP hasta que acepta conexion o vence el timeout.</summary>
    public static async Task<bool> WaitTcpAsync(string host, int port, TimeSpan timeout)
    {
        var sw = Stopwatch.StartNew();
        while (sw.Elapsed < timeout)
        {
            try
            {
                using var client = new TcpClient();
                await client.ConnectAsync(host, port);
                if (client.Connected) return true;
            }
            catch
            {
                await Task.Delay(250);
            }
        }
        return false;
    }

    /// <summary>
    /// Hace GET a una URL hasta obtener CUALQUIER respuesta HTTP o vence el timeout.
    /// Cualquier codigo (incluido 5xx) significa "servidor web arriba y atendiendo": un 500
    /// es un error de la aplicacion, no del servidor. Sin proxy, para descartar interferencias.
    /// </summary>
    public static async Task<bool> WaitHttpAsync(string url, TimeSpan timeout)
    {
        var sw = Stopwatch.StartNew();
        using var handler = new HttpClientHandler { UseProxy = false, AllowAutoRedirect = false };
        using var http = new HttpClient(handler) { Timeout = TimeSpan.FromSeconds(5) };
        while (sw.Elapsed < timeout)
        {
            try
            {
                using var resp = await http.GetAsync(url, HttpCompletionOption.ResponseHeadersRead);
                return true; // hubo respuesta HTTP => servidor arriba
            }
            catch
            {
                await Task.Delay(300);
            }
        }
        return false;
    }
}
