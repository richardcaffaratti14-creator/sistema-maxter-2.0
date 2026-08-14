using System.Net;
using System.Net.Sockets;
using MaxterServer.Health;
using Xunit;

namespace MaxterServer.Tests;

public class ReadinessCheckTests
{
    [Fact]
    public async Task Detects_open_port()
    {
        var listener = new TcpListener(IPAddress.Loopback, 0);
        listener.Start();
        int port = ((IPEndPoint)listener.LocalEndpoint).Port;
        try
        {
            var ok = await ReadinessCheck.WaitTcpAsync("127.0.0.1", port, TimeSpan.FromSeconds(2));
            Assert.True(ok);
        }
        finally
        {
            listener.Stop();
        }
    }

    [Fact]
    public async Task Times_out_on_closed_port()
    {
        // Puerto 1 no deberia estar escuchando; el sondeo debe agotarse rapido.
        var ok = await ReadinessCheck.WaitTcpAsync("127.0.0.1", 1, TimeSpan.FromMilliseconds(800));
        Assert.False(ok);
    }
}
