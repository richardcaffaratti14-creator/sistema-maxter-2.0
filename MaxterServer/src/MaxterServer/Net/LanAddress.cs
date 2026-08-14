using System.Net;
using System.Net.NetworkInformation;
using System.Net.Sockets;

namespace MaxterServer.Net;

/// <summary>Detecta la mejor IPv4 de LAN para mostrar la URL de acceso desde otras PCs.</summary>
public static class LanAddress
{
    public static string BestGuess()
    {
        foreach (var ni in NetworkInterface.GetAllNetworkInterfaces())
        {
            if (ni.OperationalStatus != OperationalStatus.Up) continue;
            if (ni.NetworkInterfaceType is NetworkInterfaceType.Loopback or NetworkInterfaceType.Tunnel) continue;

            foreach (var addr in ni.GetIPProperties().UnicastAddresses)
            {
                if (addr.Address.AddressFamily == AddressFamily.InterNetwork &&
                    !IPAddress.IsLoopback(addr.Address))
                {
                    return addr.Address.ToString();
                }
            }
        }
        return "127.0.0.1";
    }
}
