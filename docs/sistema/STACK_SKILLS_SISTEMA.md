---
title: "Stack de skills de sistema de RememberMind"
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

> Infraestructura/registro de skills; no aprobación clínica ni certificación de producto. Fase documental actual: [portal](../README.md).

# Stack de skills de sistema de RememberMind

Skills institucionales en [`.agents/skills/`](../../.agents/skills); documentación en [`docs/sistema/`](.). Los conteos siguientes describen esta entrega de skills, no el inventario BDD: 14 nuevas, 4 evolucionadas, 15 responsabilidades solicitadas.

## Entradas

- [Auditoría y decisiones sobre las 43 skills locales previas](AUDITORIA_SKILLS_SISTEMA.md).
- [Mapa de fuentes y conflictos](../../.agents/skills/remembermind-source-of-truth/references/source-map.md).
- [Mapa funcional completo y actores](MAPA_DOMINIO_FUNCIONAL.md).
- [Contratos de prueba](../../.agents/skills/remembermind-system-testing/references/test-contract.md).
- [Conocimiento, explicación y validación experta](../../.agents/skills/remembermind-expert-system-engineering/references/knowledge-contract.md).
- [Verificación de esta entrega](VERIFICACION_SKILLS_SISTEMA.md).

## Responsabilidades de desarrollo

| Skill/responsabilidad | Cuándo se selecciona |
|---|---|
| [remembermind-source-of-truth](../../.agents/skills/remembermind-source-of-truth/SKILL.md) | Resolver las fuentes, versiones y contratos vigentes de RememberMind antes de diseñar o modificar un flujo; clasificar historia, propuestas y conflictos. Usar al iniciar un módulo, auditar documentación o encontrar fuentes contradictorias; no para dictar reglas nuevas. |
| [remembermind-system-guardrails](../../.agents/skills/remembermind-system-guardrails/SKILL.md) | Detectar y aislar cambios que romperían contratos institucionales, BDD congelada, autorización o historia clínica de RememberMind. Usar ante un DOMAIN GAP o decisiones transversales; no sustituye diseño de dominio ni gates finales. |
| [remembermind-domain-architect](../../.agents/skills/remembermind-domain-architect/SKILL.md) | Diseñar casos de uso y responsabilidades Laravel de RememberMind: Policy, Action, Service, Models, entrada, transacciones y eventos. Usar para flujos nuevos o responsabilidades mal ubicadas; no para elegir estilo visual ni imponer otra arquitectura. |
| [remembermind-geriatric-workflows](../../.agents/skills/remembermind-geriatric-workflows/SKILL.md) | Reconstruir procesos geriátricos institucionales de RememberMind por actores, estados y continuidad: admisión, turno, cuidado, medicación y trabajo interdisciplinario. Usar antes de implementar o reorganizar un proceso; no inventa protocolos clínicos. |
| [remembermind-authorization-guardian](../../.agents/skills/remembermind-authorization-guardian/SKILL.md) | Diseñar e implementar autorización contextual de RememberMind durante desarrollo: cuenta activa, permiso, Policy, estado, vínculo y competencia. Usar en mutaciones clínicas, permisos, familia, archivos o exportaciones; security-review conserva el gate final independiente. |
| [remembermind-database-integrity](../../.agents/skills/remembermind-database-integrity/SKILL.md) | Diseñar y corregir la persistencia del flujo cambiado de RememberMind conforme al baseline congelado y extensiones aprobadas: relaciones, tipos, grano, atomicidad e invariantes cruzadas. Usar durante implementación; database-audit realiza el gate independiente. |
| [remembermind-clinical-record-integrity](../../.agents/skills/remembermind-clinical-record-integrity/SKILL.md) | Preservar autoría, fecha clínica, contexto y longitudinalidad en registros clínicos de RememberMind. Usar al crear, corregir, anular o consultar historia; no define interpretación clínica ni sustituye la integridad relacional general. |
| [remembermind-care-continuity](../../.agents/skills/remembermind-care-continuity/SKILL.md) | Conectar registros, intervenciones, pendientes y pase de turno de RememberMind con el siguiente punto operativo autorizado. Usar cuando información clínica queda aislada o un flujo atraviesa profesionales/turnos; no crea historia ni agenda paralelas. |
| [remembermind-alert-engine](../../.agents/skills/remembermind-alert-engine/SKILL.md) | Diseñar e implementar detección y ciclo de alertas de RememberMind con reglas aprobadas, eventos e idempotencia aplicable. Usar al integrar un registro confirmado con alerta/seguimiento; no inventa severidad ni interpreta preview como persistencia. |
| [remembermind-system-testing](../../.agents/skills/remembermind-system-testing/SKILL.md) | Diseñar y ejecutar pruebas de RememberMind por invariantes y efectos reales: unitarias, feature, integración, autorización, workflow y regresión. Usar para cambios de dominio/persistencia o brechas de cobertura; no equipara SQLite, build o mocks con prueba clínica/integración. |
| [remembermind-observability-audit](../../.agents/skills/remembermind-observability-audit/SKILL.md) | Instrumentar y revisar trazabilidad técnica y auditoría aprobada de RememberMind con Activitylog, errores y contexto mínimo. Usar en operaciones relevantes, diagnóstico y eventos de negocio; no reemplaza procedencia clínica ni añade otra tabla corporativa o servicio externo. |
| [remembermind-performance-guardian](../../.agents/skills/remembermind-performance-guardian/SKILL.md) | Medir y corregir consultas/payloads lentos de RememberMind preservando permisos, datos y reglas. Usar ante N+1, reportes, históricos o regresión medible; no prescribe cache/índices sin evidencia ni convierte cada tarea en optimización global. |
| [remembermind-expert-system-engineering](../../.agents/skills/remembermind-expert-system-engineering/SKILL.md) | Diseñar conocimiento, inferencia multicriterio y explicación del futuro sistema experto cognitivo de RememberMind mediante fases Buchanan. Usar en formalización o integración aprobada; no convierte propuestas en protocolo, diagnostica ni elige pesos/umbrales clínicos. |
| [remembermind-expert-validation](../../.agents/skills/remembermind-expert-validation/SKILL.md) | Validar independientemente conocimiento, reglas, normalización, pesos, agregación, inferencia y explicaciones del experto de RememberMind contra metodología y casos aprobados. Usar para aceptar/rechazar un motor o cambio; no inventa muestra, métricas ni aprobación clínica. |
| remembermind-module-architect → remembermind-module-delivery | EVOLVE + MERGE: diseño/plan/entrega transversal; no se crea skill con el nombre module-architect. |

Explicabilidad permanece dentro de expert-system-engineering; no skill duplicada. Module-architect se absorbe en [module-delivery](../../.agents/skills/remembermind-module-delivery/SKILL.md). Guardian diseña/implementa; revisión final valida independientemente.

## Gates y auxiliares

| Skill existente | Función conservada/evolucionada |
|---|---|
| [database-audit](../../.agents/skills/remembermind-database-audit/SKILL.md) | Gate independiente BDD. PASS/FAIL/BLOQUEADO por capa/motor, sin implementar. |
| [security-review](../../.agents/skills/remembermind-security-review/SKILL.md) | Gate independiente seguridad. Hallazgos y negativos, sin implementar. |
| [release-check](../../.agents/skills/remembermind-release-check/SKILL.md) | Consolidar aceptación/evidencia y commit local. No push. |
| investigate-first | Diagnóstico por hipótesis si el fallo/causa es ambiguo. En feature nueva empieza con fuentes y vacíos, no inventa incidente. |
| surgical-patch / safe-refactor | Cambio localizado / preservación de conducta. Mantienen contratos pertinentes; no necesitan plan completo de módulo. |
| migration / lean-build | Auxiliares condicionales subordinados a AGENTS, arquitectura y congelado. Compatibilidad temporal no justifica V1 permanente. |
| verify-and-stop | Comprobar resultado existente sin expansión. No repetir todo tras release si no queda riesgo. |
| Caveman/otros auxiliares | Estilo/localización/telemetría según invocación y disponibilidad, nunca autoridad de dominio o permiso automático para enviar datos. |

## Pipeline proporcional

Contrato/fuentes → guardrails → dominio/workflow → autorización → integridad → UX aplicable → implementación → pruebas → auditoría/gates → QA visual aplicable → release.

La selección depende del riesgo, no de cargar todas las skills. Una vez resuelto un contrato, reutilizar su informe; no releer todo el baseline en cada etapa. Consultar secciones afectadas y cambios posteriores.

1. **Entrada:** alcance/aceptación y git status/diff; investigate-first si causa incierta, source-of-truth para contrato.
2. **Diseño:** guardrails y domain-architect para comportamiento entre capas; geriatric-workflows cuando hay proceso/actores/transiciones.
3. **Contrato sensible:** authorization-guardian; database-integrity/clinical-record-integrity si hay persistencia/historia; care-continuity/alert-engine si hay seguimiento/alertas.
4. **UI:** remembermind-ui-review selecciona especialidades pertinentes. Sistema determina permisos/reglas/datos; UX representa contrato, no elige clínica.
5. **Implementación:** un responsable, reutilización V2, scope y criterios explícitos. Module-delivery coordina módulos; surgical-patch/safe-refactor para tareas pequeñas.
6. **Prueba:** system-testing; observability-audit para acción/audit afectados; expert-validation si motor/conocimiento; performance-guardian si hay riesgo medible.
7. **Gates:** security-review y database-audit cuando riesgo/encargo lo justifiquen; no auditoría global por defecto. Revisor emite hallazgos, implementador corrige en fase aparte y revisor comprueba.
8. **Aceptación:** QA visual/funcional real si cambia UI; release-check reúne evidencia, stage solo propio y commit local; verify-and-stop cuando se solicita comprobar o queda una aceptación pendiente.

## Selección por tarea

| Encargo | Especialidades mínimas a considerar | Límites de aceptación |
|---|---|---|
| CSS puntual sin conducta | Contrato vigente + ui-styling/QA UX relevante | No plan institucional completo ni testing PHP por reflejar CSS |
| Bug ambiguo clínico | investigate-first → fuente → surgical-patch + guardian/integridad del riesgo → prueba regresión | Resolver causa antes de editar; gate sensible según hallazgo |
| Admisión formal | módulo + fuentes/guardrails/dominio/workflow/autorización/BDD/testing | Transacción, cama, estados resueltos; carrera PostgreSQL no inferida de SQLite |
| Signos vitales | fuente/guardrails/dominio/autorización/historia/alertas + integridad si persistencia | Preview puro, backend interpreta, origen/eventos; objetivo aprobado según decisión vigente |
| Pase de turno | workflow/continuidad/autorización + persistencia/historia pertinente | Pendientes reales al siguiente profesional con scope |
| Reporte/archivo familiar | fuente/autorización + performance si medido + security-review | Vínculo no habilita contenido completo; fuente intacta y descarga reautorizada |
| Motor cognitivo futuro | fuente/guardrails/expert-engineering/dominio/autorización + expert-validation/testing | Diseño técnico ≠ aprobación ni validación clínica; falta de método se aísla |
| Documentación o skills | Fuentes, skill-creator y verificación de formato/enlaces/escenarios | Sin cambios funcionales; suites/build no aplican por mera creación de instrucciones |

## Integración con UX

La [guía UX existente](../frontend/STACK_SKILLS_UX_UI.md), [contrato visual](../frontend/CONTRATO_VISUAL_UX_UI.md) y remembermind-ui-review siguen siendo sus autoridades específicas. En Fase 1 documental se separó el pegado de la solicitud de sistema a [archivo histórico](../historico/SOLICITUD_STACK_SKILLS_SISTEMA.md), conservándolo íntegro; no es aprobación clínica.

Selección especializada: flujo → fidelidad solo con imagen aprobada → dirección artística si falta → tokens/componentes → interacción clínica/datos cuando aplican → motion funcional → responsive/accessibility → writing → QA. No cargar diez etapas para cambiar un label. QA visual emite evidencia/PASS/FAIL, no diseña ni corrige durante revisión.

## Protocolo de agentes para trabajo complejo

La tarea maestra autoriza este protocolo: orquestador → reviewers de lectura de dominio/seguridad/BDD y UX si corresponde → plan consolidado → un implementador → agentes de pruebas/revisión → aceptación.

No dos agentes escribiendo mismos archivos ni reviewer corrigiendo mientras audita. Cada revisión devuelve ámbito, fuentes, evidencia, conflictos y propuesta; el orquestador conserva las decisiones institucionales pendientes. Delegación y herramientas solo si están disponibles y autorizadas; presets de otro host no bastan.

## Mantenimiento

Al aprobar una extensión o método, actualizar sus fuentes/decisiones y los ejemplos afectados, no fijar un nuevo número eterno en frontmatter. Cambiar una skill conserva su responsabilidad o registra transición explícita en auditoría. Nuevas skills solo para responsabilidad distinta; no duplicar gates/UX ni reintroducir V1 por documentos históricos.
