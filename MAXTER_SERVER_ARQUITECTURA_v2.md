# Maxter Server — Arquitectura del Runtime (v2)

> **v2 reemplaza a v1.** Cambia el encuadre: Maxter Server **no** es un reemplazo de XAMPP, es el **Runtime oficial de Maxter**, un producto independiente y autocontenido. Apache, PHP y MySQL son **componentes internos** del Runtime; el usuario nunca sabe que existen.

**Objetivo del documento:** definir la arquitectura del Runtime **antes** de escribir código.
**Alcance de Fase 1:** iniciar el servidor web, PHP y MySQL; publicar la carpeta del sistema; permitir acceso por LAN. Nada más (sin GUI, sin instalador, sin servicios Windows, sin backups/monitoreo).

---

## 1. Principios de producto

Maxter Server se diseña como un **runtime propio**, no como una adaptación de un stack existente. Cinco principios lo gobiernan:

1. **Autocontenido.** Todos los binarios (servidor web, PHP, base de datos) viven **dentro** del árbol de Maxter Server. No se depende de ninguna instalación previa (XAMPP, WAMP, PHP de sistema, MySQL de sistema).
2. **Binarios propios y versionados.** El Runtime **provee** sus propios binarios, con versiones fijadas y verificables. No se "toman prestados" de otra instalación; se vendorizan como parte del producto.
3. **Configuración propia.** `httpd.conf`, `php.ini`, `my.ini` y sus rutas son **de Maxter**, generados/mantenidos por el Runtime, apuntando a rutas relativas internas. No se heredan configuraciones ajenas.
4. **Encapsulación total.** El usuario interactúa con **`MaxterServer.exe`** y con la aplicación web. Nunca ve, nombra ni administra "Apache", "PHP" o "MySQL". Esos nombres son detalle de implementación interno.
5. **Componentes intercambiables.** Como son piezas internas, pueden actualizarse o sustituirse (p. ej. subir la versión de PHP) sin cambiar la identidad del producto, siempre que el contrato externo (web PHP en LAN) se mantenga.

Estos principios convierten a Apache/PHP/MySQL en **motores internos** del Runtime, análogos a como un navegador embebe un motor de render sin exponerlo.

---

## 2. Restricciones actuales del proyecto (heredadas del código)

Dos condicionantes reales, detectados en el código, definen la **configuración interna** del Runtime en su primera versión.

### 2.1 PHP 5.6 — restricción **temporal** de compatibilidad

La capa de acceso a datos principal del framework, `sypfw/includes/SypDatabase.php`, usa la extensión **`mysql_*`** (`mysql_connect`, `mysql_query`, `mysql_fetch_*`… 53 usos):

```php
$this->_connection = @mysql_connect($this->_host.":".$this->_port, $this->_user, $this->_password);
$cursor = mysql_query($sql, $this->_connection);
```

Esa extensión fue **eliminada en PHP 7.0**. Por lo tanto, **hoy** el Runtime debe embeber **PHP 5.6**.

> **Esto NO es una decisión permanente.** Se documenta explícitamente como una **restricción temporal de compatibilidad** derivada del uso de `mysql_*` en SYPFW. En una versión futura del proyecto se migrará la capa de acceso a datos a **`mysqli` o PDO**, lo que permitirá actualizar el motor PHP interno del Runtime a una versión moderna (7.x/8.x) sin cambiar el producto. Ver la hoja de ruta en la sección 6.

`sypfw/helpers/Updater.php` **ya usa `mysqli`**, lo que confirma que la migración es viable y parcial­mente iniciada.

### 2.2 Ruteo por `.htaccess` + `mod_rewrite`

El front controller reescribe todas las URLs hacia `index.php` mediante `mod_rewrite`, en 3 archivos `.htaccess` (raíz, `admin/`, `admin/ckeditor/`):

```apache
RewriteEngine on
RewriteRule ^(.*)$ index.php?params=$1&%1 [L]
```

Solo un servidor que **procese `.htaccess` nativamente** ejecuta este ruteo sin reescribir reglas. Esto orienta la elección del motor web interno hacia **Apache** (ver sección 4).

### 2.3 Otros datos del entorno

| Dato | Valor |
|---|---|
| Base de datos | MySQL/MariaDB, base `maxter`, `root` sin contraseña |
| Conexión | `127.0.0.1:3306` |
| Front controller | `index.php` → `configuration.php` → `overseer.php` → SYPFW |
| Sesiones | `session_start()` (PHP nativo) |
| Archivos PHP | ~381 |

---

## 3. Comparación de motores web internos

La pregunta no es "¿qué servidor uso en vez de XAMPP?", sino **"¿qué motor web embebe Maxter Server internamente?"**. Se evalúan las alternativas sobre los criterios pedidos.

| Criterio | Apache + mod_php | Nginx + FastCGI | Caddy + FastCGI | PHP `-S` | FrankenPHP |
|---|:--:|:--:|:--:|:--:|:--:|
| Dificultad de integrar como componente interno | ★★★★★ | ★★☆☆☆ | ★★★☆☆ | ★★★★☆ | — |
| Consumo de memoria | ★★★☆☆ | ★★★★☆ | ★★★★☆ | ★★★★★ | — |
| Rendimiento en LAN multiusuario | ★★★★☆ | ★★★★☆ | ★★★★☆ | ★☆☆☆☆ | — |
| Compat. PHP 5.6 + ext. `mysql` | ★★★★★ | ★★★☆☆ | ★★★☆☆ | ★★★★★ | ✗ (exige PHP 8.2+) |
| Compat. SYPFW (`.htaccess`) sin tocar app | ★★★★★ | ★★☆☆☆ | ★★☆☆☆ | ★★☆☆☆ | ✗ |
| Facilidad de empaquetar/distribuir | ★★★☆☆ | ★★★☆☆ | ★★★★☆ | ★★★★★ | — |
| Mantenimiento | ★★★★★ | ★★☆☆☆ | ★★★☆☆ | ★★★☆☆ | — |
| Riesgo de romper la aplicación | **Mínimo** | Alto | Alto | Alto | N/A |

**Notas por opción (resumen):**

- **Apache + `mod_php`:** procesa `.htaccess` nativamente y carga PHP 5.6 con la extensión `mysql` directamente. Cero cambios en la app. Es el motor probado del sistema, ahora **empaquetado como componente propio** (no reutilizado de XAMPP).
- **Nginx / Caddy:** obligan a **traducir y mantener** las reglas `.htaccess` en su propia sintaxis, y a montar un backend `php-cgi` FastCGI de PHP 5.6 **sin PHP-FPM en Windows** (frágil y no estándar). Más complejidad, más riesgo, sin beneficio en LAN.
- **PHP `-S` (servidor embebido):** **monohilo** → inadecuado para varias PCs simultáneas; además **ignora `.htaccess`** (requeriría un router script, es decir, agregar infraestructura). Descartado para multiusuario.
- **FrankenPHP / stacks modernos:** requieren **PHP 8.2+** → incompatibles con `mysql_*`. Descartados por la restricción 2.1 (viables recién tras la migración de la capa DB).

---

## 4. Motor interno recomendado: **Apache 2.4 + `mod_php` (PHP 5.6) + MariaDB/MySQL**, todo propio y embebido

Es la **única** alternativa que, **hoy**, satisface simultáneamente las dos restricciones heredadas (PHP 5.6 con `mysql`, y `.htaccess`/`mod_rewrite`) **sin modificar la aplicación ni SYPFW**, y con riesgo mínimo por ser el motor ya probado — pero ahora **vendorizado como componente interno de Maxter Server**, no tomado de una instalación XAMPP.

Justificación técnica:

1. **`.htaccess` intacto.** Solo Apache lo procesa de forma nativa → el ruteo del front controller funciona sin traducir reglas y sin tocar rutas.
2. **PHP 5.6 + `mysql` de fábrica.** `mod_php` carga el motor requerido directamente; se evita el FastCGI sin FPM que penaliza a Nginx/Caddy en Windows.
3. **Riesgo mínimo.** Es el mismo trío de motores que hoy funciona; se elimina la *dependencia del producto XAMPP*, no el motor. El comportamiento externo es idéntico.
4. **Cumple el diagrama de destino** `MaxterServer.exe → Servidor Web → PHP → MySQL → index.php → SYPFW → Aplicación`, sin capas nuevas ni cambios en bootstrap, `index.php` o `configuration.php`.
5. **Encapsulable.** Apache/PHP/MySQL quedan bajo el árbol de Maxter Server, gobernados por `MaxterServer.exe`; el usuario nunca los ve.

---

## 5. Estructura del Runtime (Maxter Server como producto)

Propuesta de árbol autocontenido. Todo vive bajo una raíz de Maxter Server; **nada** fuera de ella.

```
MaxterServer/
├── MaxterServer.exe            ← supervisor/lanzador (única cara visible al usuario)
├── maxter.config               ← config de alto nivel del Runtime (puerto, ruta de la app, etc.)
│
├── runtime/                    ← componentes internos (invisibles para el usuario)
│   ├── web/                    ← motor web propio (Apache 2.4 vendorizado)
│   │   ├── bin/                ← httpd.exe y módulos (incl. mod_php)
│   │   └── conf/httpd.conf     ← config PROPIA de Maxter, rutas relativas internas
│   ├── php/                    ← motor PHP propio (5.6 hoy; intercambiable a futuro)
│   │   ├── php.ini             ← config PROPIA de Maxter (extensión mysql activada)
│   │   └── ext/
│   └── db/                     ← motor de base de datos propio (MariaDB/MySQL)
│       ├── bin/                ← mysqld.exe
│       ├── conf/my.ini         ← config PROPIA de Maxter
│       └── data/               ← datadir del Runtime (base "maxter")
│
├── app/                        ← la carpeta del sistema Maxter que se publica (DocumentRoot)
│
└── logs/                       ← logs del Runtime (web, php, db)
```

**Notas de diseño:**

- **`MaxterServer.exe`** es el **único** proceso que el usuario ejecuta. En Fase 1 es un **supervisor**: arranca `httpd.exe` y `mysqld.exe` con las configuraciones internas, publica `app/` como DocumentRoot, escucha en `0.0.0.0:80` (accesible como `http://localhost`, `http://192.168.x.x`, `http://NOMBRE-PC`) y detiene todo al cerrar.
- **Rutas relativas internas.** Las configuraciones (`httpd.conf`, `php.ini`, `my.ini`) usan rutas relativas a la raíz del Runtime → el producto es **portable** (se puede mover de carpeta sin reconfigurar) y no colisiona con software del sistema.
- **Nomenclatura de marca.** En logs, mensajes y config de alto nivel se habla de "Maxter Server / Runtime", no de "Apache/PHP/MySQL". Los nombres técnicos quedan confinados al interior de `runtime/`.
- **Puerto y aislamiento.** El Runtime usa sus propios puertos; conviene verificar que el 80 esté libre (IIS/otros) o permitir configurarlo en `maxter.config`.

> Fase 1 implementa únicamente el supervisor y la estructura. GUI, instalador, servicio Windows, backups, monitoreo y auto-update son fases posteriores.

---

## 6. Versionado del Runtime y hoja de ruta de migración

El motor PHP interno se trata como **componente versionado e intercambiable**, no como una atadura permanente.

| Fase | Motor PHP interno | Capa DB de SYPFW | Estado |
|---|---|---|---|
| **Runtime v1 (actual)** | **PHP 5.6** | `mysql_*` (legacy) | Restricción **temporal** de compatibilidad. Único camino sin tocar la app. |
| **Transición** | PHP 5.6 | Migración progresiva `mysql_*` → `mysqli`/PDO en `SypDatabase.php` (el `Updater` ya usa `mysqli`) | Trabajo futuro sobre SYPFW, fuera del alcance de Fase 1. |
| **Runtime v2 (futuro)** | **PHP moderno (7.x/8.x)** | `mysqli`/PDO | Se sube el motor PHP interno **sin cambiar el producto**; el contrato externo (web PHP en LAN) se mantiene. |

**Punto clave:** como PHP es un componente interno del Runtime, migrar de 5.6 a una versión moderna es una **actualización de componente**, no un rediseño del producto. La migración de la capa DB de SYPFW es el prerrequisito que la habilita, y se abordará como proyecto propio más adelante.

---

## 7. Definiciones a confirmar antes de codificar Fase 1

1. **Versiones a vendorizar** del motor web (Apache 2.4.x), PHP (5.6.x) y DB (MariaDB/MySQL x.y) — a fijar y verificar como binarios propios del Runtime.
2. **Puerto de publicación** (80 por defecto; configurable en `maxter.config`; verificar que esté libre).
3. **Ubicación/manejo de `app/`**: si la carpeta del sistema se copia dentro del Runtime o se referencia por ruta configurable como DocumentRoot.
4. **Modo de arranque de `MaxterServer.exe`** en Fase 1: consola en primer plano vs. proceso en segundo plano (sin llegar aún a servicio Windows).
5. **Migración de datos**: cómo se inicializa/importa la base `maxter` en el `data/` propio del Runtime la primera vez.

Con estas definiciones se puede implementar el supervisor de Fase 1 sin tocar SYPFW, `index.php` ni `configuration.php`.
