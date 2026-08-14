# MASTER_DOCUMENTATION.md

## Sistema Maxter 2.0

### Versión 0.2 -- Base de Conocimiento de Arquitectura

# Índice

1.  Objetivo
2.  Visión General
3.  Arquitectura General
4.  Componentes Principales
5.  Flujo de Inicialización
6.  Flujo de Ejecución
7.  Estructura Física
8.  Framework SYPFW
9.  Configuración
10. Base de Datos
11. Panel Administrativo
12. Seguridad
13. Grafo de Dependencias
14. Componentes Críticos
15. Puntos de Extensión
16. Riesgos
17. Estado del Proyecto
18. Información Pendiente

# 1. Objetivo

Este documento constituye la base de conocimiento inicial del proyecto.
No pretende reemplazar la documentación detallada por archivo, sino
servir como mapa arquitectónico para comprender cómo interactúan los
principales componentes.

Toda la información aquí incluida proviene del análisis realizado sobre
el proyecto y únicamente se documentan aspectos verificados.

# 2. Visión General

Sistema Maxter 2.0 es una aplicación web PHP basada en un framework
propietario denominado **SYPFW**. La arquitectura combina un núcleo
propio con un subsistema administrativo heredado y una base de datos
MySQL.

Los pilares identificados son:

-   Bootstrap
-   Configuración
-   Framework
-   Aplicaciones
-   Administración
-   Base de datos
-   Recursos públicos
-   Migraciones

# 3. Arquitectura General

    Cliente
       │
       ▼
    Apache
       │
       ▼
    index.php
       │
       ├── configuration.php
       ├── Updater::runUpdate()
       ├── site_configuration.php
       └── sypfw/includes/overseer.php
                        │
                        ▼
                   Framework SYPFW
                        │
              ┌─────────┴─────────┐
              ▼                   ▼
          Aplicación          Infraestructura
              │
              ▼
          Base de datos
              │
              ▼
          Respuesta HTML

# 4. Componentes Principales

  Componente               Función                  Criticidad
  ------------------------ ------------------------ ------------
  index.php                Punto de entrada         ★★★★★
  configuration.php        Configuración global     ★★★★★
  site_configuration.php   Configuración dinámica   ★★★★★
  SYPFW                    Framework                ★★★★★
  admin                    Gestión administrativa   ★★★★★
  apps                     Lógica de negocio        ★★★★★
  static                   Recursos públicos        ★★☆☆☆
  \_db_updates             Migraciones              ★★★★☆

# 5. Flujo de Inicialización

1.  El navegador solicita la aplicación.
2.  Apache entrega la petición a `index.php`.
3.  Se carga `configuration.php`.
4.  Se ejecuta `Updater::runUpdate()`.
5.  Se completa la configuración mediante `site_configuration.php`.
6.  Se inicializa `overseer.php`.
7.  El framework determina aplicación, módulo y acción.
8.  Se carga la lógica correspondiente.
9.  Se consulta MySQL si es necesario.
10. Se genera la respuesta.

# 6. Flujo de Ejecución

    Usuario
        ↓
    Front Controller
        ↓
    Configuración
        ↓
    Framework
        ↓
    Aplicación
        ↓
    Modelo / Base de datos
        ↓
    Vista
        ↓
    Respuesta

# 7. Estructura Física

-   admin/: administración y autenticación.
-   apps/: lógica de negocio.
-   sypfw/: infraestructura reutilizable.
-   static/: CSS, JS, imágenes y recursos.
-   mpdf/: dependencia externa.
-   \_db_updates/: scripts de actualización.

# 8. Framework SYPFW

Responsabilidades verificadas:

-   Inicialización.
-   Helpers compartidos.
-   Modelos.
-   Layouts.
-   Resolución de aplicaciones.

Dependencias:

-   configuration.php
-   site_configuration.php

Punto de entrada:

-   sypfw/includes/overseer.php

# 9. Configuración

La configuración está dividida en dos niveles:

**Estática**

-   Constantes.
-   Rutas.
-   Base de datos.

**Dinámica**

Obtenida mediante `getSiteInfo()` desde MySQL.

Se utiliza para parámetros multimedia y comportamiento del sitio.

# 10. Base de Datos

Motor detectado:

-   MySQL.

Características:

-   Configuración centralizada.
-   Migraciones automáticas.
-   Scripts versionados.
-   Parámetros dinámicos.

Pendiente:

-   Modelo entidad-relación.
-   Tablas.
-   Índices.
-   Relaciones.

# 11. Panel Administrativo

Flujo observado:

    admin/index.php
          │
    session_start()
          │
    Conexión
          │
    cAdvancedSecurity
          │
    AutoLogin()
          │
    LoadUserLevel()
          │
    Dashboard

# 12. Seguridad

Confirmado:

-   Sesiones PHP.
-   Control de permisos.
-   AutoLogin.
-   AdvancedSecurity.

Pendiente:

-   CSRF.
-   XSS.
-   Sanitización.
-   Hashes.
-   Cookies.

# 13. Grafo de Dependencias

    index.php
       │
    configuration.php
       │
    Framework
       │
    Apps
       │
    MySQL

    admin/index.php
       │
    EW Security
       │
    Dashboard

# 14. Componentes Críticos

  Archivo                  Motivo
  ------------------------ ------------------------
  index.php                Bootstrap
  configuration.php        Configuración global
  site_configuration.php   Configuración dinámica
  overseer.php             Núcleo del framework
  admin/index.php          Acceso administrativo

Modificar estos archivos requiere comprender completamente su flujo.

# 15. Puntos de Extensión

Se identifican como zonas de crecimiento:

-   apps/
-   helpers/
-   models/
-   layouts/
-   \_db_updates/
-   static/

# 16. Riesgos

-   Arquitectura híbrida.
-   Dependencia fuerte del bootstrap.
-   Configuración global compartida.
-   Configuración dinámica almacenada en la BD.
-   Framework propietario sin documentación original.

# 17. Estado del Proyecto

Confirmado:

-   Arquitectura.
-   Bootstrap.
-   Framework.
-   Seguridad inicial.
-   Dependencias.
-   Configuración.

Pendiente:

-   Ingeniería inversa profunda del framework.
-   Módulos internos.
-   SQL.
-   JavaScript.
-   CSS.
-   Relaciones entre archivos.

# 18. Información Pendiente

La siguiente etapa deberá documentar:

-   Todos los archivos.
-   Todas las clases.
-   Todas las funciones.
-   Todas las consultas SQL.
-   Todas las dependencias.
-   Todos los módulos.

Este documento constituye el punto de partida para esa etapa.
