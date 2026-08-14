# Maxter Server — Informe de Arquitectura (Fase 1)

**Objetivo del documento:** definir la arquitectura del servidor que reemplazará a XAMPP, **antes** de escribir código. Comparación técnica de alternativas y recomendación única justificada.

**Alcance de Fase 1:** iniciar servidor web, PHP y MySQL; publicar la carpeta del sistema; permitir acceso por LAN. Nada más.

---

## 1. Restricciones reales detectadas en el código

El análisis del proyecto arroja dos condicionantes **duros** que determinan la decisión. No son preferencias: son requisitos de compatibilidad.

### 1.1 El sistema requiere PHP 5.x (extensión `mysql` legacy)

La capa de base de datos principal del framework, `sypfw/includes/SypDatabase.php`, usa la extensión **`mysql_*`** (`mysql_connect`, `mysql_query`, `mysql_fetch_*`… **53 usos** en el proyecto):

```php
// sypfw/includes/SypDatabase.php
$this->_connection = @mysql_connect($this->_host.":".$this->_port, $this->_user, $this->_password);
...
$cursor = mysql_query($sql, $this->_connection);
```

Esa extensión fue **deprecada en PHP 5.5 y eliminada por completo en PHP 7.0**. Por lo tanto:

- **El sistema solo funciona en PHP 5.x** (en la práctica, **PHP 5.6.x**, la última rama que la incluye).
- Subir a PHP 7/8 **rompería la aplicación** salvo reescribir toda la capa DB → **fuera del alcance** ("no modificar SYPFW").
- Únicamente `sypfw/helpers/Updater.php` usa `mysqli` (moderno). Es la excepción, no la regla.

> **Consecuencia:** cualquier arquitectura elegida debe entregar **PHP 5.6** con la extensión `mysql` habilitada. Esto descarta stacks modernos que asumen PHP 8.

### 1.2 El ruteo depende de `.htaccess` + `mod_rewrite` (Apache)

El front controller reescribe todas las URLs hacia `index.php` mediante reglas `mod_rewrite` en **3 archivos `.htaccess`** (raíz, `admin/`, `admin/ckeditor/`):

```apache
# .htaccess (raíz)
RewriteEngine on
RewriteCond %{QUERY_STRING} ^(.*)$
RewriteRule ^(.*)$ index.php?params=$1&%1 [L]
```

`SITE_ROOT` se construye a partir de `$_SERVER['SERVER_NAME']` y `$_SERVER['PHP_SELF']`, con un *fix* explícito para "cuando corre en el root de server Windows". El sistema **depende de que el servidor procese `.htaccess`**.

> **Consecuencia:** solo **Apache** lee `.htaccess` de forma nativa. Nginx, Caddy o el servidor embebido de PHP **ignoran** esos archivos: habría que **traducir manualmente** las reglas de reescritura a su propia sintaxis y mantenerlas sincronizadas. Eso viola el espíritu de "no modificar rutas / total compatibilidad".

### 1.3 Otros datos del entorno

| Dato | Valor |
|---|---|
| Base de datos | MySQL/MariaDB, base `maxter`, usuario `root`, sin contraseña (`configuration.php`) |
| Puerto DB | 3306 (host `127.0.0.1`) |
| Front controller | `index.php` → `configuration.php` → `overseer.php` → SYPFW |
| Archivos PHP | ~381 |
| Sesiones | `session_start()` en `overseer.php` y `admin/index.php` |
| Encoding | `utf8_encode()` (deprecado en PHP 8.2 — otro motivo más para quedarse en 5.6) |

**Estas dos restricciones (PHP 5.6 + `.htaccess`) son el eje de toda la decisión que sigue.**

---

## 2. Alternativas evaluadas

Se analizan las cuatro solicitadas más dos adicionales pertinentes. Cada una se puntúa sobre los nueve criterios pedidos.

### Opción A — Apache HTTP Server portable + `mod_php` (PHP 5.6)

Apache 2.4 en modo portable (carpeta autocontenida, sin instalación en Windows), con PHP 5.6 cargado como **módulo** (`php5apache2_4.dll`) y MariaDB/MySQL portable. Es, esencialmente, **el mismo motor que ya usa XAMPP**, pero desacoplado de XAMPP y arrancado por `MaxterServer.exe`.

| Criterio | Evaluación |
|---|---|
| **Dificultad de implementación** | **Muy baja.** El stack ya funciona hoy bajo XAMPP; se reutiliza `httpd.conf` y el binario de PHP existentes. `MaxterServer.exe` solo lanza `httpd.exe` y `mysqld.exe`. |
| **Consumo de memoria** | Moderado (~40–90 MB Apache + PHP según workers). Aceptable para un servidor LAN dedicado. |
| **Rendimiento** | Suficiente y probado para la carga real (pocas PCs en LAN). `mod_php` evita el sobrecosto de FastCGI. |
| **Compatibilidad con PHP** | **Total.** `mod_php` soporta PHP 5.6 con la extensión `mysql` nativamente. |
| **Compatibilidad con SYPFW** | **Total y sin cambios.** Lee `.htaccess`/`mod_rewrite` tal cual. Cero modificaciones a la app. |
| **Facilidad de distribución** | Buena: carpeta autocontenida. Algo pesada (Apache+PHP+MySQL ≈ 150–250 MB). |
| **Mantenimiento** | Bajo: es tecnología conocida, idéntica a la actual; enorme documentación. |
| **Ventajas** | Compatibilidad 100%; riesgo mínimo; reutiliza lo existente; `.htaccess` intacto; camino más corto a Fase 1. |
| **Desventajas** | Apache/PHP 5.6 sin soporte oficial (igual que hoy); binarios algo grandes; requiere empaquetar bien. |

### Opción B — Nginx + PHP-CGI/FastCGI (PHP 5.6)

Nginx como servidor web, PHP 5.6 servido vía FastCGI. **PHP-FPM no existe en Windows**, así que habría que usar `php-cgi.exe` bajo un supervisor (o `mod_fastcgi`).

| Criterio | Evaluación |
|---|---|
| **Dificultad de implementación** | **Alta.** Hay que configurar Nginx + pool de `php-cgi` en Windows (frágil, sin FPM), y **reescribir las reglas `.htaccess` en sintaxis Nginx**. |
| **Consumo de memoria** | Bajo en Nginx, pero cada worker `php-cgi` suma; gestión de procesos manual. |
| **Rendimiento** | Muy bueno para estáticos; para PHP dinámico el FastCGI en Windows con PHP 5.6 es menos robusto. |
| **Compatibilidad con PHP** | Parcial/frágil: PHP 5.6 CGI corre, pero sin FPM la administración de procesos en Windows es problemática. |
| **Compatibilidad con SYPFW** | **Requiere trabajo.** No lee `.htaccess`; hay que **portar y mantener** las reglas de reescritura → riesgo de romper el ruteo. |
| **Facilidad de distribución** | Media: Nginx es liviano, pero el conjunto (Nginx + php-cgi supervisor + MySQL) es más complejo de empaquetar. |
| **Mantenimiento** | Alto: dos configuraciones de reescritura que mantener sincronizadas; setup no estándar en Windows. |
| **Ventajas** | Ligero; excelente sirviendo estáticos; muy usado. |
| **Desventajas** | Traducción de `.htaccess`; sin PHP-FPM en Windows; mayor complejidad para un beneficio nulo en este contexto LAN. |

### Opción C — Caddy + PHP-CGI/FastCGI (PHP 5.6)

Caddy 2 (un solo binario, HTTPS automático) con PHP vía FastCGI (directiva `php_fastcgi`, que también necesita un backend `php-cgi`/FPM).

| Criterio | Evaluación |
|---|---|
| **Dificultad de implementación** | **Media-alta.** Caddy en sí es simple (un `.exe` + `Caddyfile`), pero **igual necesita un backend FastCGI de PHP 5.6** (sin FPM en Windows) y **traducir las reglas de reescritura** al `Caddyfile`. |
| **Consumo de memoria** | Bajo-moderado; Caddy es eficiente. |
| **Rendimiento** | Muy bueno; comparable a Nginx. |
| **Compatibilidad con PHP** | Igual limitación que Nginx: depende de un `php-cgi` externo; Caddy está pensado para PHP-FPM moderno. |
| **Compatibilidad con SYPFW** | **Requiere trabajo.** No usa `.htaccess`; reescritura manual en `Caddyfile`. |
| **Facilidad de distribución** | **Excelente para el servidor web** (binario único), pero el backend PHP y MySQL siguen sumando complejidad. |
| **Mantenimiento** | Medio: `Caddyfile` es legible, pero se mantiene aparte del `.htaccess` real. HTTPS automático es irrelevante en LAN interna. |
| **Ventajas** | Binario único; config sencilla; TLS automático. |
| **Desventajas** | Mismo problema de `.htaccess` y de FastCGI+PHP 5.6 en Windows; TLS automático no aporta en LAN. |

### Opción D — Servidor web embebido de PHP (`php -S`)

El servidor interno de PHP (`php -S 0.0.0.0:80 -t .`). Sin dependencias externas de servidor web.

| Criterio | Evaluación |
|---|---|
| **Dificultad de implementación** | **Muy baja** para arrancar… pero engañosa. |
| **Consumo de memoria** | Mínimo. |
| **Rendimiento** | **Insuficiente:** es **monoproceso / monohilo**. Atiende una petición a la vez → inaceptable para "varias PCs en LAN". |
| **Compatibilidad con PHP** | Total (es el propio PHP 5.6). |
| **Compatibilidad con SYPFW** | **Problemática.** **No procesa `.htaccess`** → el ruteo `mod_rewrite` no funciona; habría que emular el front controller con un *router script*, es decir, **agregar código de infraestructura** y arriesgar el comportamiento actual. |
| **Facilidad de distribución** | Excelente (solo PHP), pero inservible por lo anterior. |
| **Mantenimiento** | Bajo en apariencia, pero el *router script* es un punto frágil y no estándar. |
| **Ventajas** | Cero componentes extra; ideal para desarrollo de un solo usuario. |
| **Desventajas** | **Oficialmente desaconsejado para producción**; monousuario; sin `.htaccess`. **Descartado para uso multiusuario.** |

### Opción E — FrankenPHP / servidores "todo en uno" modernos

FrankenPHP (Caddy + PHP embebido en un binario).

| Criterio | Evaluación |
|---|---|
| **Compatibilidad con PHP** | **Incompatible.** FrankenPHP requiere **PHP 8.2+**. No soporta PHP 5.6 ni la extensión `mysql`. **Descartado de entrada** por la restricción 1.1. |

### Opción F — Reempaquetar el stack actual (WAMP/XAMPP "headless")

Tomar los binarios Apache+PHP+MySQL que **ya vienen con el XAMPP en uso**, extraerlos a una carpeta propia y gobernarlos con `MaxterServer.exe`. Es una **variante concreta y pragmática de la Opción A**: en lugar de descargar Apache/PHP nuevos, se reutilizan exactamente los binarios ya validados con el sistema.

| Criterio | Evaluación |
|---|---|
| **Dificultad de implementación** | **Mínima.** Los binarios ya funcionan con Maxter hoy; solo se elimina la "cáscara" de XAMPP. |
| **Compatibilidad** | **Idéntica a la actual por definición** (mismos binarios, mismas extensiones, mismo `.htaccess`). Riesgo prácticamente nulo. |
| **Ventajas** | Máxima fidelidad al entorno probado; sin sorpresas de versión. |
| **Desventajas** | Se hereda la versión concreta de PHP/Apache del XAMPP; conviene fijar/anotar esas versiones. |

---

## 3. Cuadro comparativo resumido

| Criterio | A. Apache+mod_php | B. Nginx | C. Caddy | D. `php -S` | E. FrankenPHP |
|---|:--:|:--:|:--:|:--:|:--:|
| Dificultad implementación | ★★★★★ | ★★☆☆☆ | ★★★☆☆ | ★★★★☆ | — |
| Consumo de memoria | ★★★☆☆ | ★★★★☆ | ★★★★☆ | ★★★★★ | — |
| Rendimiento (LAN) | ★★★★☆ | ★★★★☆ | ★★★★☆ | ★☆☆☆☆ | — |
| Compat. PHP 5.6 + ext mysql | ★★★★★ | ★★★☆☆ | ★★★☆☆ | ★★★★★ | ✗ |
| Compat. SYPFW (`.htaccess`) | ★★★★★ | ★★☆☆☆ | ★★☆☆☆ | ★★☆☆☆ | ✗ |
| Facilidad de distribución | ★★★☆☆ | ★★★☆☆ | ★★★★☆ | ★★★★★ | — |
| Mantenimiento | ★★★★★ | ★★☆☆☆ | ★★★☆☆ | ★★★☆☆ | — |
| **Riesgo de romper la app** | **Mínimo** | Alto | Alto | Alto | N/A |

(★ = mejor. "—" = descartada por incompatibilidad previa.)

---

## 4. Recomendación

### Arquitectura recomendada: **Apache 2.4 portable + `mod_php` (PHP 5.6) + MariaDB/MySQL portable, orquestados por `MaxterServer.exe`** (Opción A, materializada con los binarios ya validados — Opción F).

```
Windows
   ↓
MaxterServer.exe        ← supervisor/lanzador propio (arranca y detiene el stack)
   ↓
Apache 2.4 (portable)   ← lee .htaccess / mod_rewrite SIN cambios
   ↓
mod_php (PHP 5.6)       ← extensión mysql legacy disponible
   ↓
MySQL / MariaDB         ← base "maxter" en 127.0.0.1:3306
   ↓
index.php → SYPFW → Aplicación
```

`MaxterServer.exe` en Fase 1 es simplemente un **proceso supervisor**: levanta `httpd.exe` y `mysqld.exe` apuntando a las configuraciones portables, publica la carpeta del sistema como *DocumentRoot*, escucha en `0.0.0.0:80` (accesible como `http://localhost`, `http://192.168.x.x` o `http://NOMBRE-PC`) y los detiene al cerrar. Sin GUI, sin servicios de Windows, sin instalador — todo eso queda para fases posteriores.

### Por qué es la mejor opción (justificación técnica)

1. **Es la única que preserva el ruteo sin tocar el código.** El sistema depende de `.htaccess` + `mod_rewrite`, y **solo Apache los procesa nativamente**. Nginx y Caddy obligarían a traducir y mantener reglas de reescritura en paralelo — precisamente lo que las restricciones prohíben ("no modificar rutas", "total compatibilidad").

2. **Es la única cómoda con PHP 5.6 + extensión `mysql`.** `mod_php` carga PHP 5.6 directamente. Nginx/Caddy dependerían de `php-cgi` FastCGI, y **PHP-FPM no existe en Windows**, lo que hace ese backend frágil y no estándar. FrankenPHP queda descartado porque exige PHP 8.2+.

3. **Riesgo mínimo: es el mismo motor que ya funciona.** XAMPP hoy es Apache + PHP + MySQL. Se elimina la *dependencia de XAMPP* como producto, no el motor. Reutilizando los binarios ya validados (Opción F), el comportamiento es **idéntico por construcción**: la app "sigue funcionando exactamente igual", que es el objetivo declarado.

4. **Cumple el diagrama de destino pedido** (`MaxterServer.exe → Servidor Web → PHP → MySQL → index.php → SYPFW → Aplicación`) sin introducir capas nuevas ni cambiar el bootstrap, `index.php` ni `configuration.php`.

5. **Mantenimiento y rendimiento adecuados al escenario.** Para una LAN con pocas PCs, Apache + `mod_php` rinde de sobra; su ecosistema y documentación son enormes; y no añade piezas experimentales.

### Descartes en una línea

- **`php -S`:** monohilo → no sirve para varias PCs; además ignora `.htaccess`.
- **Nginx / Caddy:** obligan a traducir `.htaccess` y a montar un FastCGI de PHP 5.6 sin FPM en Windows → más trabajo, más riesgo, cero beneficio real en LAN.
- **FrankenPHP y stacks modernos:** requieren PHP 8+, incompatible con la extensión `mysql` del framework.

---

## 5. Definiciones a fijar antes de codificar (siguiente paso)

Para arrancar la implementación de Fase 1 conviene confirmar:

1. **Versión exacta de PHP/Apache/MySQL** del XAMPP actualmente en uso (para reutilizar esos binarios). Un `php -v`, `httpd -v` y `mysql --version` en la PC servidor bastan.
2. **Puerto de publicación** (80 por defecto; verificar que no lo ocupe otro servicio como IIS/Skype).
3. **Ubicación de la carpeta del sistema** a publicar como *DocumentRoot* (`D:\web\SISTEMA maxter 2.0`).
4. **Forma de arranque de `MaxterServer.exe`** en Fase 1: lanzador simple en consola vs. proceso en segundo plano (sin llegar a servicio Windows, que es fase posterior).

Con esas cuatro definiciones se puede escribir el supervisor de Fase 1 sin tocar SYPFW, `index.php` ni `configuration.php`.
