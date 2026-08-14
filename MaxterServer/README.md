# Maxter Server — Runtime (Fase 1 / MVP)

Runtime oficial de Maxter. Al ejecutar `MaxterServer.exe`, arranca los motores internos
(base de datos → PHP/web), publica la carpeta de la aplicación y la deja accesible en
`http://localhost` y `http://IP_DEL_SERVIDOR` de la LAN. Apache, PHP y MySQL son
componentes internos e invisibles: el usuario solo conoce **Maxter Server**.

No modifica la aplicación, SYPFW, el bootstrap, `index.php` ni `configuration.php`.

## Estructura del producto distribuido

```
MaxterServer/
├── MaxterServer.exe            ← único ejecutable visible (self-contained, no requiere .NET)
├── config/
│   └── maxter.config           ← puerto, carpeta de la app, timeouts
├── runtime/
│   ├── manifest.json           ← versiones + rutas + integridad de los componentes
│   ├── web/  (Apache + mod_php, incl. conf/httpd.conf.tmpl)
│   ├── php/  (PHP 5.6 VC11 x64 TS)
│   └── db/   (MariaDB; my.ini.tmpl; seed/maxter.sql; data/)
├── app/                        ← sistema Maxter (DocumentRoot)
└── logs/   maxter-server.log · web.log · db.log
```

## Qué hay que aportar (no incluido en el código fuente)

Los binarios pesados y el dump se distribuyen aparte (ver `.gitignore`) y deben colocarse
según el `manifest.json`:

| Ubicación | Contenido |
|---|---|
| `runtime/web/` | Apache **httpd 2.4.25 VC11 x64** (`bin/httpd.exe`, `modules/`) |
| `runtime/php/` | PHP **5.6.40 VC11 x64 Thread-Safe** (`php5apache2_4.dll`, `php.ini` con ext. `mysql`) |
| `runtime/db/` | MariaDB **10.6.x** (`bin/mysqld.exe`, herramientas) |
| `runtime/db/seed/maxter.sql` | Dump oficial de la base `maxter` (para la instalación inicial) |
| `app/` | Contenido del sistema Maxter |

Coherencia obligatoria: Apache y PHP deben ser **VC11 + x64 + Thread-Safe** (mod_php de PHP 5.6
está compilado con VC11). Requiere el **Visual C++ 2012 (VC11) Redistributable x64**.

## Compilar / publicar

```bash
# Compilar y testear (portable)
dotnet build
dotnet test

# Publicar el ejecutable único self-contained (Windows x64)
dotnet publish src/MaxterServer/MaxterServer.csproj -c Release -r win-x64 \
    --self-contained true -p:PublishSingleFile=true -o dist
```

## Cómo funciona el arranque

1. Carga `config/maxter.config` y `runtime/manifest.json`.
2. **Valida** existencia e integridad de todos los componentes (aborta si falta algo).
3. Si es el **primer arranque**, instala la base: crea el datadir e importa `seed/maxter.sql`.
   Las evoluciones posteriores del esquema las maneja el **Updater** del sistema.
4. Arranca la base de datos y espera a que responda (TCP 3306).
5. Arranca el servidor web + PHP y espera a que responda (HTTP).
6. Publica los accesos local y de LAN.
7. `Ctrl+C` o cierre → apagado ordenado. Un **Job Object** garantiza que no queden
   procesos huérfanos aunque el supervisor muera de forma abrupta.

## Fuera de alcance (Fase 1)

GUI, tray, servicio Windows, instalador, updates, backups, monitoreo, panel, seguridad extra.
