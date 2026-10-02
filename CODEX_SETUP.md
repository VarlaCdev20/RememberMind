# Codex en RememberMind

Este repositorio usa instrucciones por alcance y cinco skills locales. La fuente de verdad de la base de datos es la **BDD Operativa V2.1 de 70 tablas**, documentada en `docs/base-de-datos/`.

## Estructura

```text
AGENTS.md
app/AGENTS.md
database/AGENTS.md
resources/AGENTS.md
tests/AGENTS.md
.agents/skills/remembermind-module-delivery/SKILL.md
.agents/skills/remembermind-ui-review/SKILL.md
.agents/skills/remembermind-database-audit/SKILL.md
.agents/skills/remembermind-security-review/SKILL.md
.agents/skills/remembermind-release-check/SKILL.md
scripts/setup-codex.ps1
CODEX_SETUP.md
```

El `AGENTS.md` de la raíz contiene reglas generales. Los archivos de `app/`, `database/`, `resources/` y `tests/` detallan las reglas de cada área. Los skills describen flujos reutilizables y se activan por su nombre o por tareas afines. Reinicia o abre una nueva sesión de Codex para cargar instrucciones y skills recién agregados.

## Comprobar la instalación

Desde la raíz del repositorio:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup-codex.ps1
```

El script comprueba la presencia de los archivos, el baseline V2.1 y un skill UI/UX Pro Max opcional. No instala software, cambia la base de datos, crea commits ni hace push. La estructura funciona igual si después mueves o clonas el repositorio a `C:\laragon\www\RememberMind`.

## Uso de los skills

Puedes pedirlos de forma explícita:

```text
$remembermind-database-audit revisa la BDD V2.1 sin modificar su estructura.
$remembermind-security-review revisa permisos y acceso a un residente.
$remembermind-ui-review revisa el formulario de admisión.
$remembermind-module-delivery completa un flujo institucional.
$remembermind-release-check revisa los cambios antes de un commit.
```

UI/UX Pro Max es opcional y puede estar instalado en el perfil de usuario; no hace falta copiarlo al repositorio ni instalar una CLI global para que funcionen los cinco skills de RememberMind.

## Verificación del proyecto

Para cambios de código, usa las pruebas relevantes y `npm run build` cuando cambie el frontend. `composer test` ejecuta la suite PHP. Usa PostgreSQL para integración/CI; SQLite `:memory:` solo para pruebas rápidas configuradas. Ejecuta `php artisan migrate:fresh --seed` únicamente sobre una base de datos de desarrollo o pruebas confirmada como desechable.

El pack original se preparó para `REFAC_BDD` y 69 tablas. Estas instrucciones se adaptaron a la rama y al baseline vigentes; los documentos históricos de 69 tablas no gobiernan cambios nuevos.
