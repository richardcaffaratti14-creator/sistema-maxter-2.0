using MaxterServer;
using Xunit;

namespace MaxterServer.Tests;

public class RuntimePathsTests
{
    private static string MakeRuntimeTree(out string nested)
    {
        // root/runtime/manifest.json  +  root/bin/Debug/net8.0 (simula output)
        var root = Path.Combine(Path.GetTempPath(), "mxtest_" + Guid.NewGuid().ToString("N"));
        Directory.CreateDirectory(Path.Combine(root, "runtime"));
        File.WriteAllText(Path.Combine(root, "runtime", "manifest.json"), "{}");
        nested = Path.Combine(root, "bin", "Debug", "net8.0");
        Directory.CreateDirectory(nested);
        return root;
    }

    [Fact]
    public void Walks_up_to_find_runtime_root()
    {
        var root = MakeRuntimeTree(out var nested);
        try
        {
            var resolved = RuntimePaths.ResolveRoot(nested, envOverride: null);
            Assert.Equal(root, resolved);
        }
        finally { Directory.Delete(root, recursive: true); }
    }

    [Fact]
    public void Finds_runtime_next_to_executable_first_iteration()
    {
        var root = MakeRuntimeTree(out _);
        try
        {
            // baseDir == root (produccion: runtime/ junto al .exe)
            var resolved = RuntimePaths.ResolveRoot(root, envOverride: null);
            Assert.Equal(root, resolved);
        }
        finally { Directory.Delete(root, recursive: true); }
    }

    [Fact]
    public void Env_override_wins_when_valid()
    {
        var root = MakeRuntimeTree(out var nested);
        try
        {
            var resolved = RuntimePaths.ResolveRoot(nested, envOverride: root);
            Assert.Equal(Path.GetFullPath(root), resolved);
        }
        finally { Directory.Delete(root, recursive: true); }
    }

    [Fact]
    public void Skips_build_output_dir_and_finds_real_root()
    {
        // root/runtime/manifest.json  (raiz real)
        // + root/src/App/bin/Debug/net8.0/runtime/manifest.json  (copia parasita en bin)
        var root = Path.Combine(Path.GetTempPath(), "mxbin_" + Guid.NewGuid().ToString("N"));
        Directory.CreateDirectory(Path.Combine(root, "runtime"));
        File.WriteAllText(Path.Combine(root, "runtime", "manifest.json"), "{}");

        var outDir = Path.Combine(root, "src", "App", "bin", "Debug", "net8.0");
        Directory.CreateDirectory(Path.Combine(outDir, "runtime"));
        File.WriteAllText(Path.Combine(outDir, "runtime", "manifest.json"), "{}");
        try
        {
            // Debe IGNORAR la copia dentro de bin y subir hasta la raiz real.
            var resolved = RuntimePaths.ResolveRoot(outDir, envOverride: null);
            Assert.Equal(root, resolved);
        }
        finally { Directory.Delete(root, recursive: true); }
    }

    [Fact]
    public void Falls_back_to_basedir_when_not_found()
    {
        var lonely = Path.Combine(Path.GetTempPath(), "mxlonely_" + Guid.NewGuid().ToString("N"));
        Directory.CreateDirectory(lonely);
        try
        {
            var resolved = RuntimePaths.ResolveRoot(lonely, envOverride: null);
            Assert.Equal(lonely, resolved);
        }
        finally { Directory.Delete(lonely, recursive: true); }
    }
}
