---
title: "RememberMind Documentation"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-08
owner: RememberMind
source_of_truth: true
source_of_truth_scope: [document_governance, source_routing]
verified_against_commit: null
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# RememberMind Documentation

Dolor V2 (09/10/2026): [resultado y gates](frontend/FORMULARIO_DOLOR_V2_RESULTADO.md),
[extensión estructural aprobada](base-de-datos/DECISION_DOLOR_V2.md).

## Estado del proyecto

Sistema residencial geriátrico con operación institucional y seguimiento clínico, cognitivo, funcional e interdisciplinario. Hay código/tests por varias áreas y desarrollo activo; no se certifica completitud global ni producción. [Estado actual](ESTADO_ACTUAL.md) describe evidencia; [roadmap](ROADMAP.md) separa planes e ideas.

Evidencia estática de Fase 1: V2.1 + extensión V2.2 aprobada = 71 tablas operativas declaradas. Aquella fase no comprobó una BDD ejecutada. La instalación autorizada del sistema experto del 2026-10-08 verificó en PostgreSQL esas 71 tablas y añadió 23 expertas vacías, conservando los datos; evidencia acotada en [implementación V1](sistema-experto/IMPLEMENTACION_V1.md). Ninguna cifra equivale a versión/release global del producto. La cadena estructural está en [BDD](base-de-datos/README.md).

## Cómo usar esta documentación

Leer estado/metadatos y alcance antes de usar una afirmación. CURRENT no convierte todo párrafo en regla aprobada; propuestas/conflictos parciales se señalan. Plan, archivo FINAL, skill o test existente no demuestra implementación validada.

Este portal es autoridad sobre gobernanza documental y routing de fuentes. Su `source_of_truth: true` no reemplaza el baseline BDD, los contratos clínicos, la matriz de permisos ni las decisiones aprobadas específicas. `verified_against_commit` identifica la base del contraste estático cuando tiene valor; `null` corresponde aquí al árbol de trabajo sin commit. No certifica runtime. Fase 1 no ejecutó `migrate:fresh`, seed, suites, build ni comprobación de una BDD real.

Jerarquía: instrucción actual de propietaria → baseline/diccionario y decisiones aprobadas aplicables → contrato funcional vigente → código V2 → tests → auxiliares → historia. Extensión aprobada modifica solo su alcance. [Convención](GOBERNANZA_DOCUMENTAL.md) define metadatos y evidencia.

## Fuentes de verdad

| Área | Entrada | Alcance |
|---|---|---|
| Producto | [README raíz](../README.md), [Estado](ESTADO_ACTUAL.md), [Roadmap](ROADMAP.md) | Qué es, qué está observado y qué se propone; no mezclar |
| Dominio | [Glosario](GLOSARIO_DOMINIO.md), [Mapa funcional](sistema/MAPA_DOMINIO_FUNCIONAL.md), [AGENTS](../AGENTS.md) | Identidades, actores y flujo; mapa no certifica completitud |
| Arquitectura | [Organización vigente](arquitectura/README.md), [baseline técnico](arquitectura/REMEMBERMIND_BASELINE_TECNICO.md), [app/AGENTS](../app/AGENTS.md) | Carpetas/capas y motores; locks para versiones |
| BDD | [Índice BDD](base-de-datos/README.md) | Núcleo, extensiones, estructura y conflictos; histórico 69 excluido |
| Seguridad | [Roles](arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [excepción temporal](arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md), [app/AGENTS](../app/AGENTS.md) | Autenticación/permiso/Policy/competencia/scope; excepción no permanente |
| Clínica | [Baseline](base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), decisiones y auditorías localizadas [BDD](base-de-datos/README.md) | Autoría, historial y reglas respaldadas; propuestas no se implementan por leerlas |
| UX | [resources/AGENTS](../resources/AGENTS.md), [contrato visual](frontend/CONTRATO_VISUAL_UX_UI.md), [stack UX](frontend/STACK_SKILLS_UX_UI.md), [DS](../resources/frontend/styles/design-system/README.md) | Dirección aprobada frente a tokens implementados; no inventa clínica |
| Testing | [tests/AGENTS](../tests/AGENTS.md), [contratos de prueba](../.agents/skills/remembermind-system-testing/references/test-contract.md) | Riesgo y efectos; test definido no significa ejecutado |
| Sistema experto | [Implementación técnica y fuentes](sistema-experto/README.md) | O.R.I.O.N.; núcleo y esquema verificados con datos sintéticos; activación clínica pendiente |
| Producción | [Despliegue seguro](produccion/DESPLIEGUE_SEGURO.md) | Procedimiento; operación desplegada NO VERIFICADA |
| Agentes/Codex | [CODEX_SETUP](../CODEX_SETUP.md), [stack sistema](sistema/STACK_SKILLS_SISTEMA.md), [mapa fuentes](../.agents/skills/remembermind-source-of-truth/references/source-map.md) | Selección proporcional y guardrails; no autoridad clínica nueva |

## Documentación vigente

- [Eliminación V2: implementación, gates y límites de QA](frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md), [extensión aprobada](base-de-datos/DECISION_ELIMINACION_V2.md).
- [Administrador: etapas, funcionamiento y mejoras](administrador/README.md)
- [Estado actual](ESTADO_ACTUAL.md), [Glosario](GLOSARIO_DOMINIO.md), [Gobernanza](GOBERNANZA_DOCUMENTAL.md).
- [Auditoría Fase 1](AUDITORIA_DOCUMENTAL_FASE_1.md), [Decisiones](DECISIONES_PENDIENTES.md), [Deuda técnica](DEUDA_TECNICA.md).
- [Arquitectura](arquitectura/README.md), [roles](arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [dashboards por rol](arquitectura/DASHBOARDS_POR_ROL.md).
- [BDD](base-de-datos/README.md), [UX](frontend/STACK_SKILLS_UX_UI.md), [skills sistema](sistema/STACK_SKILLS_SISTEMA.md).
- [Changelog documental](CHANGELOG.md), [verificación Fase 1](VERIFICACION_DOCUMENTAL_FASE_1.md).
- [Arquitectura contrastada Fase 2](arquitectura/ARQUITECTURA_VIGENTE.md), [flujos](sistema/FLUJOS_GERIATRICOS.md), [ciclos de vida](sistema/ESTADOS_Y_CICLOS_DE_VIDA.md).
- [Modelo de autorización](seguridad/MODELO_AUTORIZACION.md), [matriz de roles y entradas](seguridad/MATRIZ_ROLES_PERMISOS.md), [invariantes](sistema/INVARIANTES_NEGOCIO.md), [continuidad asistencial](sistema/CONTINUIDAD_ASISTENCIAL.md).
- [Plantilla de contrato](modulos/_PLANTILLA_CONTRATO_MODULO.md), [ingreso](modulos/ingreso-institucional/README.md), [residentes/alojamiento](modulos/residentes-alojamiento/README.md), [jornadas/asignaciones](modulos/jornadas-asignaciones/README.md), [medicación](modulos/medicacion/README.md), [signos vitales](modulos/signos-vitales/README.md), [alertas](modulos/alertas/README.md), [pase de turno](modulos/pase-turno/README.md).
- [Trazabilidad y cobertura estática Fase 2](TRAZABILIDAD.md).

El flujo institucional se localiza en AGENTS/baseline y FormalizarAdmision: preadmisión → revisión → APROBADA/RECHAZADA → admisión formal con cama → residente admitido. Aprobar no crea residente. Valores físicos conflictivos se registran en DEC-OPEN-001.

## Documentación histórica

**HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH.**

- [architecture-audit](architecture-audit/README.md): propuestas/análisis anteriores.
- [refactorizacion-total](refactorizacion-total/README.md): inventario/roadmap de otra etapa.
- [Auditoría V1](auditoria.md), [roles/permisos V1](auditoria_roles_permisos.md).
- [Diccionario V2.0](base-de-datos/REMEMBERMIND_BDD_69_TABLAS.md), [inventario migración](base-de-datos/INVENTARIO_MIGRACION_APLICACION.md), [resultado inicial](base-de-datos/RESULTADO_MIGRACION_APLICACION_V2.md).
- [Arquitectura frontend anterior](arquitectura/REMEMBERMIND_ARQUITECTURA_FRONTEND.md): DEPRECATED como norma; consultar fuentes sustitutas.
- [Archivo de solicitudes históricas](historico/README.md) y snapshots UX señalados como HISTORICAL.

No se mueven masivamente archivos; sus enlaces se conservan. Contratos canónicos vigentes pueden referenciar historia para contexto explícito, sin otorgarle autoridad.

## Documentación en construcción

- Índice de [sistema experto](sistema-experto/README.md): CURRENT; enlaza la implementación técnica V1 y O.R.I.O.N. Los tres diseños de árboles siguen PROPOSED; no hay activación clínica.
- Cierre y cobertura de Fase 2: [trazabilidad](TRAZABILIDAD.md); otros módulos aún requieren contrato detallado según [mapa funcional](sistema/MAPA_DOMINIO_FUNCIONAL.md).
- Plantillas legales de resources/markdown: DRAFT sin contenido institucional acreditado; no prueba de cumplimiento.
- [Etiquetas clínicas pendientes](frontend/PENDIENTE_BADGES_ESTADO_RESIDENTE.md): no aprobadas.

## Decisiones pendientes

[DEC-OPEN-001..006](DECISIONES_PENDIENTES.md): DEC-OPEN-001 estados institucionales; DEC-OPEN-002 catálogos; DEC-OPEN-003 etiquetas clínicas; DEC-OPEN-004 PRN/reintentos; DEC-OPEN-005 metodología/derechos del sistema experto; DEC-OPEN-006 publicación clínica familiar. Lo técnico evidenciado se registra separado en [TECH-001..011](DEUDA_TECNICA.md); las ideas en [roadmap](ROADMAP.md).

## Orden recomendado de lectura

### Para un desarrollador nuevo

1. README raíz y ESTADO_ACTUAL.
2. GLOSARIO_DOMINIO y arquitectura/README + AGENTS del ámbito.
3. DECISIONES_PENDIENTES/DEUDA_TECNICA y contrato del proceso antes del código.

### Para trabajar en Base de Datos

1. database/AGENTS e índice BDD.
2. Baseline, diccionario núcleo y decisiones/extensiones aprobadas aplicables.
3. Migraciones reales, modelos/tests pertinentes y conflictos abiertos; sin reset desconocido.

### Para trabajar en un módulo clínico

1. AGENTS/app, roles y contrato del proceso.
2. Glosario/BDD, autoría/longitudinalidad y decisiones clínicas aplicables.
3. Código/tests/continuidad y especialistas de sistema; UI representa el backend aprobado.

### Para trabajar en UX

1. resources/AGENTS y contrato visual.
2. remembermind-ui-review/stack UX y referencia aprobada cuando existe.
3. Componentes/tokens reales y QA relevante; no deducir verificación visual de PHPUnit/build.

### Para trabajar en el sistema experto

1. sistema-experto/README y DEC-OPEN-005.
2. Propuestas con fuentes/método/derechos/estado explícitos y arquitectura vigente.
3. Skills expert-engineering/validation y casos aprobados; diseño sintético no acredita clínica.
