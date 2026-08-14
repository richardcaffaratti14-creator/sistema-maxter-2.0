# Maxter Server — Arquitectura Fase 2 (Producto instalable)

**Objetivo general:** convertir Maxter Server en un producto **instalable y profesional**.
**Prioridad:** Fase **2.1 — Instalador**.
**Estado:** documento de arquitectura para aprobar. **Sin código todavía.**

---

## 0. Principio rector de la Fase 2

Separar de forma estricta lo **inmutable** (binarios, que se reemplazan en cada update) de lo **mutable** (base de datos, config generada, logs, que deben sobrevivir a los updates). Este único principio ordena la estructura de carpetas, la estrategia de actualización y la desinstalación.

```
BINARIOS  (read-only)      ->  C:\Program Files\Maxter Server\
DATOS     (read-write)     ->  C:\ProgramData\Maxter Server\
```

Windows no permite que una app escriba en `Program Files` sin privilegios de admin; por eso el datadir de MariaDB, la config generada y los logs **no pueden** vivir ahí. Van a `ProgramData`. Esto es lo que hace posible "actualizar sin perder datos".

---

## 1. Decisiones tecnológicas (a confirmar)

| Tema | Recomendación | Por qué |
|---|---|---|
| **Motor de instalador** | **Inno Setup 6** | Script-driven, gratis, ideal para empaquetar apps con binarios grandes (Apache/PHP/MariaDB ~200–300 MB). Pascal scripting para acciones custom (detección VC++, firewall, servicio). Genera un único `MaxterServerSetup.exe`. Curva baja, muy usado en productos reales. |
| Alternativa enterprise | WiX Toolset (MSI) | MSI/GPO, ACLs, `ServiceInstall` nativo, Burn para prerequisitos. Más potente pero curva alta (XML) y pensado para despliegue corporativo. Se puede migrar en el futuro si hace falta MSI. |
| **Tipo de instalación** | Per-machine (requiere admin) | Servicio, firewall y `Program Files` necesitan elevación. Una PC = servidor. |
| **VC++ Redistributable** | Detectar + instalar VC++ 2012 (VC11) x64 en silencio | Apache/PHP VC11 lo requieren. |
| **Servicio de Windows** | Incluirlo en 2.1 como **componente opcional** (arranque automático al boot) | El caso de uso es "una PC actúa como servidor": conviene que arranque sin que nadie inicie sesión. Se mantiene también el modo consola para debug. |
| **Ubicación de datos** | `C:\ProgramData\Maxter Server\` | Mutable, sobrevive updates, no requiere permisos especiales. |

> Inno Setup no instala servicios de forma nativa como MSI; el servicio se registra invocando `sc.exe` o un comando propio del `MaxterServer.exe` (`--install-service`) desde la sección `[Run]` del instalador. Se detalla en la Task 2.1.8.

---

## 2. Estructura final de carpetas

### 2.1 Binarios — `C:\Program Files\Maxter Server\` (read-only)

```
Maxter Server\
├── MaxterServer.exe                 ← supervisor (self-contained)
├── runtime\
│   ├── manifest.json
│   ├── web\   (Apache 2.4.25 VC11 + mod_php)
│   ├── php\   (PHP 5.6.40 VC11 TS + ext\)
│   └── db\    (MariaDB 10.6 bin + seed\maxter.sql)
├── app\                             ← sistema Maxter (archivos PHP, read-only)
├── redist\
│   └── vcredist_x64.exe             ← VC++ 2012 (VC11) para instalar si falta
├── LICENSE.txt  /  VERSION           ← metadatos del producto
└── uninstall\                       ← generado por Inno Setup (unins000.exe/.dat)
```

### 2.2 Datos — `C:\ProgramData\Maxter Server\` (read-write, persiste updates)

```
Maxter Server\
├── config\
│   ├── maxter.config                ← editable por el operador (puerto, etc.)
│   ├── httpd.generated.conf         ← generado en cada arranque
│   ├── my.generated.ini             ← generado en cada arranque
│   └── php.ini                      ← generado en cada arranque
├── db\
│   └── data\                        ← datadir MariaDB (base "maxter")  ⟵ NUNCA se borra en update
└── logs\
    ├── maxter-server.log  web.log  db.log  apache-error.log
```

### 2.3 Accesos directos — Menú Inicio y Escritorio

```
Menú Inicio\Programas\Maxter Server\
├── Maxter Server (iniciar)          → MaxterServer.exe   (o gestiona el servicio)
├── Abrir Maxter (navegador)         → http://localhost
├── Carpeta de datos                 → C:\ProgramData\Maxter Server
└── Desinstalar Maxter Server        → unins000.exe
```

> **Nota sobre `app\`:** los archivos PHP del sistema son read-only en `Program Files`. Las carpetas donde la app escribe (uploads, `img_cache`, etc.) se resuelven por `getSiteInfo('filespath')` hacia una ruta externa configurable (fuera de `Program Files`). Se valida en la Task 2.1.11 que ninguna escritura de la app caiga dentro de `Program Files`.

---

## 3. Cambios requeridos en el Runtime (prerequisito del instalador)

El Runtime hoy resuelve **una sola raíz** (todo relativo al `.exe`). Para el producto instalado hay que separar **raíz de instalación** (binarios, read-only) de **raíz de datos** (mutable). Esto es la base de todo lo demás.

- **`InstallRoot`** = carpeta del `.exe` (`Program Files\Maxter Server`). Contiene `runtime\`, `app\`, `redist\`.
- **`DataRoot`** = carpeta de datos mutable. Resolución por orden:
  1. variable/entrada de config `MAXTER_DATA` si está definida;
  2. si el `.exe` corre desde `Program Files` → `C:\ProgramData\Maxter Server`;
  3. en desarrollo (corriendo desde `bin\...`) → igual que hoy (junto al proyecto), para no romper el flujo actual.
- `config\`, `db\data\`, `logs\` pasan a colgar de `DataRoot`. `runtime\`, `app\`, `manifest.json` cuelgan de `InstallRoot`.

Esto es una refactor acotada de `RuntimePaths` (Task 2.1.2). El resto del código (validador, motores, orquestación) ya usa `RuntimePaths`, así que el cambio queda contenido.

---

## 4. Flujo completo de instalación

```
1.  Usuario ejecuta MaxterServerSetup.exe
2.  UAC: solicita elevación (admin)
3.  Pantalla de bienvenida + licencia + carpeta destino (default C:\Program Files\Maxter Server)
4.  (Opcional) Selección de componentes: [x] Servicio de Windows (arranque automático)
5.  Verifica requisitos:
       - VC++ 2012 (VC11) x64 instalado?  -> si no, instala redist\vcredist_x64.exe /install /quiet /norestart
       - Puerto 80 libre?                 -> si está ocupado, avisa y ofrece continuar / configurar otro puerto
6.  Copia binarios a Program Files\Maxter Server\  (exe, runtime\, app\, redist\)
7.  Crea DataRoot en ProgramData\Maxter Server\  (config\, db\, logs\)  [solo si no existe]
8.  Escribe config\maxter.config inicial (si no existe)  [preserva el existente en updates]
9.  Primer arranque de datos: inicializa MariaDB en ProgramData\...\db\data e importa seed\maxter.sql
       (reutiliza el DbInitializer de Fase 1; solo corre si el datadir está vacío)
10. Firewall: crea regla de entrada TCP puerto 80 "Maxter Server"
11. (Si se eligió servicio) Registra el servicio de Windows con arranque automático y lo inicia
       (Si no) Deja MaxterServer.exe listo para iniciar manualmente
12. Crea accesos directos (Menú Inicio + opcional Escritorio)
13. Registra el desinstalador (Inno Setup lo hace) y la entrada en "Aplicaciones instaladas"
14. Finaliza; opción "Abrir Maxter ahora" -> arranca + abre http://localhost
```

---

## 5. Instalación de Apache, PHP y MariaDB embebidos

No hay "instalación" de estos motores en el sentido clásico: **se copian** como parte de `runtime\` (ya vendorizados y validados en Fase 1). El instalador solo:

1. **Copia** `runtime\web`, `runtime\php`, `runtime\db` (con `seed\maxter.sql`) a `Program Files\Maxter Server\runtime\`.
2. Garantiza el **VC++ 2012 Redistributable** (del que dependen Apache y PHP VC11) — ver Task 2.1.6.
3. En el **primer arranque**, el propio Runtime genera `httpd.conf`/`my.ini`/`php.ini` en `DataRoot\config` e **inicializa la base** en `DataRoot\db\data` (lógica ya existente en Fase 1, solo apuntando a `DataRoot`).

Ventaja: los motores quedan encapsulados e invisibles; el instalador no toca el Apache/MySQL del sistema (si existiera).

---

## 6. VC++ Redistributable (detección + instalación automática)

- **Detección** (registro): `HKLM\SOFTWARE\Wow6432Node\Microsoft\VisualStudio\11.0\VC\Runtimes\x64`, valor `Installed = 1`.
- **Si falta:** ejecutar `redist\vcredist_x64.exe /install /quiet /norestart` (bundleado en el instalador).
- Se corre **antes** de intentar arrancar Apache/PHP. Si el redist pide reinicio (`/norestart` lo evita normalmente), se documenta.

---

## 7. Firewall

- **Instalación:** `netsh advfirewall firewall add rule name="Maxter Server (HTTP 80)" dir=in action=allow protocol=TCP localport=80`.
- Si el puerto es configurable, la regla usa el puerto elegido en `maxter.config`.
- **Desinstalación:** `netsh advfirewall firewall delete rule name="Maxter Server (HTTP 80)"`.
- Solo entrada (inbound). No se abre saliente (no hace falta).

---

## 8. Servicio de Windows (opcional en 2.1)

- **Recomendación:** ofrecerlo como componente marcado por defecto, porque el caso de uso es una PC-servidor que debe atender aunque nadie inicie sesión.
- **Enfoque:** `MaxterServer.exe` soporta correr como servicio (integración `Microsoft.Extensions.Hosting.WindowsServices` en .NET 8) además del modo consola actual. El mismo `.exe` sirve para ambos.
- **Registro:** el instalador registra el servicio con arranque automático (vía `sc.exe create` o comando propio `MaxterServer.exe --install-service`) y lo inicia.
- **Modo consola** se conserva para debug (lo que usamos en Fase 1).
- Si preferís, esto se puede **diferir a Fase 2.2** y dejar 2.1 con arranque manual + acceso directo. Queda como decisión a confirmar.

---

## 9. Desinstalación limpia

El desinstalador (Inno Setup) hace:

1. Detiene y **elimina el servicio** (si estaba instalado).
2. Mata cualquier `MaxterServer.exe`/`httpd.exe`/`mysqld.exe` residual.
3. **Elimina la regla de Firewall**.
4. Borra `Program Files\Maxter Server\` (binarios) y los accesos directos.
5. **Datos (`ProgramData\Maxter Server`):** pregunta al usuario:
   - **Conservar** (default, seguro) — para reinstalar sin perder la base.
   - **Eliminar todo** (checkbox explícito "borrar también la base de datos y la configuración").

Nunca borra la base de datos en silencio.

---

## 10. Estrategia de actualización sin perder datos

- El instalador de una versión nueva **detecta la instalación previa** (mismo AppId de Inno Setup) y corre en modo update:
  1. Detiene el servicio / proceso.
  2. **Reemplaza solo los binarios** en `Program Files\Maxter Server\` (exe, `runtime\`, `app\`).
  3. **No toca** `ProgramData\Maxter Server\` (base, config, logs) → los datos persisten.
  4. Reinicia el servicio / proceso.
- **Esquema de la base:** las evoluciones las sigue aplicando el **`Updater` del sistema** (que `index.php` ejecuta al arrancar) — un único mecanismo de migración, como se definió en Fase 1. El instalador **no** corre migraciones.
- **Guardas:** control de versión (VERSION / AppVersion de Inno) para impedir downgrades accidentales; el `seed\maxter.sql` solo se importa si el datadir está vacío (nunca sobre datos existentes).
- **Config:** `maxter.config` se preserva si ya existe (no se pisa en updates).

---

## 11. División en tareas (Fase 2.1) — con dependencias y orden

> Orden pensado para avanzar como en Fase 1: cada tarea deja algo verificable. Las tareas 2.1.1–2.1.3 son prerequisito del instalador; 2.1.4+ son el instalador en sí.

| # | Tarea | Depende de | Entregable / verificación |
|---|---|---|---|
| **2.1.1** | Definir layout final Install/Data + naming | — | Documento/constantes acordadas (esta arquitectura). |
| **2.1.2** | Refactor `RuntimePaths`: `InstallRoot` + `DataRoot` | 2.1.1 | Runtime corre en dev igual que hoy; y si `DataRoot` apunta a otra carpeta, genera config/db/logs ahí. Tests unitarios de resolución. |
| **2.1.3** | Modo servicio de Windows en `MaxterServer.exe` (+ `--install/uninstall-service`) | 2.1.2 | El exe corre como servicio y como consola; arranca/para vía `services.msc`. *(Diferible a 2.2 si se decide.)* |
| **2.1.4** | Script base Inno Setup: metadatos, carpeta destino, copia de binarios | 2.1.2 | Setup instala binarios en `Program Files\Maxter Server` y desinstala. |
| **2.1.5** | Creación de `DataRoot` + `maxter.config` inicial (preservando si existe) | 2.1.4 | Tras instalar, existe `ProgramData\Maxter Server\` con config; en reinstalación no se pisa. |
| **2.1.6** | Detección + instalación silenciosa de VC++ 2012 x64 | 2.1.4 | En una VM limpia sin VC11, el setup lo instala; si ya está, lo omite. |
| **2.1.7** | Regla de Firewall (alta en install / baja en uninstall) | 2.1.4 | Acceso desde otra PC de la LAN sin tocar el firewall a mano. |
| **2.1.8** | Registro/inicio del servicio desde el instalador (componente opcional) | 2.1.3, 2.1.4 | Servicio "Maxter Server" con arranque automático; inicia tras instalar. |
| **2.1.9** | Accesos directos (Menú Inicio + navegador + carpeta datos) | 2.1.4 | Accesos funcionan; "Abrir Maxter" abre `http://localhost`. |
| **2.1.10** | Primer arranque post-install: init DB en `DataRoot` + verificación EN LINEA | 2.1.2, 2.1.5 | Tras instalar y arrancar, `http://localhost` sirve el sistema (como Fase 1, pero instalado). |
| **2.1.11** | Validación de escrituras de la app fuera de `Program Files` | 2.1.10 | Confirmar que uploads/cache/logs de la app no intentan escribir en `Program Files`; documentar `filespath`. |
| **2.1.12** | Flujo de actualización (detección de instalación previa, preservar datos) | 2.1.4, 2.1.5 | Instalar v1 → cargar datos → instalar v2 → datos intactos. |
| **2.1.13** | Desinstalación limpia (servicio, firewall, binarios, accesos; datos opcional) | 2.1.4–2.1.9 | Tras desinstalar no quedan servicio, regla de firewall ni procesos; `ProgramData` se conserva o borra según elección. |
| **2.1.14** | Prueba end-to-end en Windows limpio (VM) | todas | Instalar desde cero en una VM sin XAMPP/VC11 → arranca y sirve en LAN → update → desinstala. |

**Camino crítico:** 2.1.1 → 2.1.2 → 2.1.4 → 2.1.10. El servicio (2.1.3/2.1.8) puede ir en paralelo o diferirse a 2.2.

---

## 12. Fuera de alcance de la Fase 2.1

GUI de administración, bandeja del sistema (tray), auto-update (descarga automática de nuevas versiones), backups programados, monitoreo, panel web de admin. Se abordan en fases posteriores (2.2+). La migración de la capa de datos `mysql_*` → `mysqli`/PDO (para subir PHP) sigue siendo un proyecto aparte.

---

## 13. Decisiones (cerradas)

1. **Motor de instalador:** **Inno Setup 6.** ✔
2. **Servicio de Windows:** **incluido en Fase 2.1** (arranque automático), con modo consola conservado para debug. ✔
3. **Naming:** **"Maxter Server"** → `Program Files\Maxter Server` y `ProgramData\Maxter Server`. ✔ (default)
4. **Puerto:** **configurable en el instalador, default 80.** ✔ (default)
5. **Desinstalación:** **conserva los datos por defecto**, con checkbox explícito para borrarlos. ✔ (default)

Todo definido. Próximo paso al aprobar: arrancar el camino crítico escribiendo código — **2.1.1 → 2.1.2** (refactor `RuntimePaths` a `InstallRoot` + `DataRoot`).
