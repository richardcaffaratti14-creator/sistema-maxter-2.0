using MaxterServer;
using Xunit;

namespace MaxterServer.Tests;

public class MaxterConfigTests
{
    [Fact]
    public void Loads_defaults_when_fields_missing()
    {
        var cfg = MaxterConfig.FromJson("{}");
        Assert.Equal(80, cfg.Port);
        Assert.Equal("app", cfg.AppFolder);
        Assert.Equal(30, cfg.DbStartTimeoutSeconds);
        Assert.Equal(20, cfg.WebStartTimeoutSeconds);
    }

    [Fact]
    public void Reads_overrides()
    {
        var cfg = MaxterConfig.FromJson("{\"port\":8080,\"appFolder\":\"sistema\",\"dbStartTimeoutSeconds\":45}");
        Assert.Equal(8080, cfg.Port);
        Assert.Equal("sistema", cfg.AppFolder);
        Assert.Equal(45, cfg.DbStartTimeoutSeconds);
    }
}
