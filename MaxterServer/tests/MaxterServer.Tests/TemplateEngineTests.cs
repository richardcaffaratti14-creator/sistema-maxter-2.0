using MaxterServer.Config;
using Xunit;

namespace MaxterServer.Tests;

public class TemplateEngineTests
{
    [Fact]
    public void Replaces_all_tokens()
    {
        var tmpl = "Root={{SERVER_ROOT}} Doc={{DOC_ROOT}} Port={{PORT}} Php={{PHP_DIR}}";
        var outp = TemplateEngine.Render(tmpl, new Dictionary<string, string>
        {
            ["SERVER_ROOT"] = "C:/mx/web",
            ["DOC_ROOT"] = "C:/mx/app",
            ["PORT"] = "80",
            ["PHP_DIR"] = "C:/mx/php",
        });

        Assert.Equal("Root=C:/mx/web Doc=C:/mx/app Port=80 Php=C:/mx/php", outp);
        Assert.DoesNotContain("{{", outp);
    }

    [Fact]
    public void Leaves_unknown_tokens_untouched()
    {
        var outp = TemplateEngine.Render("A={{A}} B={{B}}", new Dictionary<string, string> { ["A"] = "1" });
        Assert.Equal("A=1 B={{B}}", outp);
    }
}
