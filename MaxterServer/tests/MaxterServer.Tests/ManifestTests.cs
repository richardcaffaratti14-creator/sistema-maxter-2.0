using MaxterServer;
using Xunit;

namespace MaxterServer.Tests;

public class ManifestTests
{
    private const string Json = """
    {
      "runtimeName": "Maxter Server",
      "runtimeVersion": "1.0.0",
      "components": [
        { "id": "web", "name": "Web", "version": "2.4.25", "path": "web", "executable": "bin/httpd.exe",
          "integrity": { "bin/httpd.exe": "" } },
        { "id": "db", "name": "DB", "version": "10.6", "path": "db", "executable": "bin/mysqld.exe" }
      ]
    }
    """;

    [Fact]
    public void Parses_components()
    {
        var m = Manifest.FromJson(Json);
        Assert.Equal("Maxter Server", m.RuntimeName);
        Assert.Equal(2, m.Components.Count);
        Assert.Equal("bin/httpd.exe", m.Component("web").Executable);
        Assert.Equal("2.4.25", m.Component("web").Version);
    }

    [Fact]
    public void Throws_on_unknown_component()
    {
        var m = Manifest.FromJson(Json);
        Assert.Throws<KeyNotFoundException>(() => m.Component("php"));
    }
}
