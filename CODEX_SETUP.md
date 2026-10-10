---
title: "Codex en RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# Codex en RememberMind

Entrada documental: [docs/README](docs/README.md). Instrucciones por alcance y skills locales en .agents/skills; inventario/selección actuales en [stack sistema](docs/sistema/STACK_SKILLS_SISTEMA.md) y [stack UX](docs/frontend/STACK_SKILLS_UX_UI.md). No limitar el catálogo al pack original de cinco entradas.

## Autoridad y estructura

- [AGENTS raíz](AGENTS.md).
- [app/AGENTS](app/AGENTS.md), [database/AGENTS](database/AGENTS.md), [resources/AGENTS](resources/AGENTS.md), [tests/AGENTS](tests/AGENTS.md).
- [Estado actual](docs/ESTADO_ACTUAL.md), [glosario](docs/GLOSARIO_DOMINIO.md) y [decisiones pendientes](docs/DECISIONES_PENDIENTES.md).
- [BDD](docs/base-de-datos/README.md): baseline congelado y extensiones aprobadas; el número del nombre del diccionario no fija inventario eterno.

Las skills guían trabajo reutilizable por nombre/tarea. Una skill no aprueba reglas clínicas, permisos o esquema; las fuentes de dominio y la instrucción actual prevalecen.

## Comprobar instrucciones disponibles

La guía anterior mencionaba `scripts/setup-codex.ps1`, pero ese archivo no está presente en el árbol inspeccionado. No usarlo como comprobación disponible ni inventar su resultado.

Verificar las rutas de AGENTS y los selectores enlazados arriba. Esta fase valida documentación con las herramientas existentes; no crea un script o instala otro servicio para compensar el archivo ausente.

## Selección proporcional

```text
$remembermind-source-of-truth reconstruye contrato y conflictos del flujo.
$remembermind-database-integrity revisa persistencia durante implementación.
$remembermind-database-audit realiza gate independiente contra fuentes actuales.
$remembermind-authorization-guardian diseña controles contextuales.
$remembermind-security-review realiza revisión independiente de seguridad.
$remembermind-ui-review selecciona especialistas UX aplicables.
$remembermind-module-delivery coordina entrega y arquitectura de módulo.
```

Guardianes guían implementación; gates revisan sin editar; corrección en fase aparte. Ui-ux-pro-max es auxiliar cuando aporta a una duda concreta. No cargar todas las especialidades en una tarea puntual.

## Verificación y autonomía

Contrato → guardrails/dominio/workflow → autorización/integridad → UX cuando aplica → implementación → tests/gates → QA pertinente → release. Código/test presente no demuestra ejecución. PostgreSQL acredita garantías específicas solo tras prueba; SQLite :memory: rápido no basta para carreras.

Datos sintéticos; no reset de DB desconocida ni uso de datos clínicos reales para capturas. Secretos/configuración funcional no forman parte de tareas documentales. Commit/push se rigen por instrucción actual: **esta Fase 1 no autoriza commit**.

## Historia

El pack inicial estaba preparado para REFAC_BDD y V2.0. Sus conteos/ejemplos son antecedentes; [historia](docs/README.md#documentación-histórica) no gobierna cambios nuevos.
