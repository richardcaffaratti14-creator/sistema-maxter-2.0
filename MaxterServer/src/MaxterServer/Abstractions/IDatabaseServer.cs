namespace MaxterServer.Abstractions;

/// <summary>Motor de base de datos. Implementacion actual: MariaDB (invisible al usuario).</summary>
public interface IDatabaseServer : IServerComponent
{
    /// <summary>True si es el primer arranque y el datadir aun no fue inicializado.</summary>
    bool NeedsInitialization();

    /// <summary>
    /// Instalacion inicial (solo primer arranque): inicializa el datadir e importa el dump
    /// oficial (seed). Las evoluciones posteriores del esquema las maneja el Updater del
    /// sistema, no el Runtime (un unico mecanismo de migracion).
    /// </summary>
    void Initialize();
}
