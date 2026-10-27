# Inventario documental de RememberMind

**Fecha:** 25/10/2026, America/La_Paz. **Tipo:** inventario de rutas y metadatos, no revisión ni aprobación de todos los contenidos.

Se localizaron **458 archivos Markdown**, incluidos este índice, **5 AGENTS.md**, **57 SKILL.md** y sus referencias. Antes de crear este índice había 457 archivos en este alcance.

Se utilizó rg --files --hidden sobre el árbol local: incluye archivos sin seguimiento visibles para rg. Excluye dependencias, .git, storage, build, output, work y carpetas temporales/de resultados; respeta las reglas de exclusión de Git. No es un inventario de todos los Markdown del equipo. Los archivos externos de skills no pertenecen a este repositorio.

El estado de las tablas siguientes es el declarado en front matter; CURRENT no significa que todas sus afirmaciones estén actualizadas o aprobadas. Sin metadatos se indica No declarado. Un nombre que contiene PROPUESTA no basta para determinar si después fue aprobado.

## AGENTS aplicables

| Archivo | Ámbito |
|---|---|
| [AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/AGENTS.md>) | Todo el repositorio |
| [app/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/AGENTS.md>) | app/ y responsabilidades indicadas en el archivo |
| [database/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/database/AGENTS.md>) | database/ y responsabilidades indicadas en el archivo |
| [resources/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/AGENTS.md>) | resources/ y responsabilidades indicadas en el archivo |
| [tests/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/AGENTS.md>) | tests/ y responsabilidades indicadas en el archivo |

No se encontraron AGENTS propios dentro de los doce módulos. Los README de cada módulo no sustituyen a app/AGENTS.md.

## Distribución

| Ubicación | Markdown |
|---|---:|
| .agents/skills | 70 |
| .github | 1 |
| app | 20 |
| contratos | 2 |
| database | 4 |
| docs (raíz) | 13 |
| docs/administrador | 11 |
| docs/architecture-audit | 25 |
| docs/arquitectura | 14 |
| docs/auditorias | 18 |
| docs/base-de-datos | 30 |
| docs/frontend | 68 |
| docs/historico | 2 |
| docs/modulos | 20 |
| docs/produccion | 1 |
| docs/refactorizacion-total | 15 |
| docs/seguridad | 3 |
| docs/sistema | 18 |
| docs/sistema-experto | 111 |
| motor-experto | 1 |
| prototipos | 1 |
| Raíz | 3 |
| resources | 5 |
| tests | 2 |

## Contratos de módulo ya localizados

Los doce módulos disponen de app/Modulos/{modulo}/README.md. Son resúmenes arquitectónicos: no se consideran doce especificaciones funcionales completas. docs/modulos agrupa siete procesos: ingreso-institucional, residentes-alojamiento, jornadas-asignaciones, medicacion, signos-vitales, alertas y pase-turno. Su cobertura no es uno a uno con los doce propietarios.

Plantilla disponible: [docs/modulos/_PLANTILLA_CONTRATO_MODULO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/_PLANTILLA_CONTRATO_MODULO.md>). Mapa transversal: [docs/sistema/MAPA_DOMINIO_FUNCIONAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/MAPA_DOMINIO_FUNCIONAL.md>).

## Discrepancias ya comprobadas para la siguiente revisión

- AGENTS.md:27 y tests/AGENTS.md:152 conservan el inventario de 104 tablas del 21/10. database/AGENTS.md:17 y el baseline recogen la consolidación posterior del 22/10: 100 tablas de negocio y 16 técnicas. Debe distinguirse el corte histórico del inventario actual.
- docs/sistema/MAPA_DOMINIO_FUNCIONAL.md conserva referencias a app/Backend y app/Http/Controllers anteriores a la arquitectura híbrida. Esas rutas deben conciliarse con sus consumidores actuales.
- El mapa de fuentes de la skill source-of-truth declara revisión del 08/10 y conserva estados anteriores del experto. Sirve para localizar, pero requiere contraste con decisiones posteriores.
- La presencia de README, contrato, propuesta o informe de entrega no acredita por sí misma implementación ni pruebas satisfactorias.

Solo se creó este inventario y su evidencia. No se modificaron los cinco AGENTS ni los contratos existentes.

## Catálogo completo por ubicación

### .agents/skills

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [.agents/skills/banner-design/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/banner-design/SKILL.md>) | Banner Design - Multi-Format Creative Banner System | No declarado |
| [.agents/skills/brand/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/brand/SKILL.md>) | Brand | No declarado |
| [.agents/skills/cavecrew/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/cavecrew/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman-commit/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-commit/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman-compress/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-compress/SKILL.md>) | Caveman Compress | No declarado |
| [.agents/skills/caveman-discover/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-discover/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman-evidence-review/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-evidence-review/SKILL.md>) | Review Caveman evidence | No declarado |
| [.agents/skills/caveman-explore/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-explore/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman-help/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-help/SKILL.md>) | Caveman Help | No declarado |
| [.agents/skills/caveman-learn/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-learn/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman-manage/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-manage/SKILL.md>) | Manage eval-gated experiments | No declarado |
| [.agents/skills/caveman-optimize/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-optimize/SKILL.md>) | Evaluate an optimization observation | No declarado |
| [.agents/skills/caveman-review/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-review/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman-setup/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-setup/SKILL.md>) | OpenAI-protocol wiring: | No declarado |
| [.agents/skills/caveman-stats/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman-stats/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/caveman/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/caveman/SKILL.md>) | SKILL.md | No declarado |
| [.agents/skills/design-system/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/design-system/SKILL.md>) | Design System | No declarado |
| [.agents/skills/design/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/design/SKILL.md>) | Design | No declarado |
| [.agents/skills/humanizer/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/humanizer/SKILL.md>) | Humanizer: remove AI writing patterns | No declarado |
| [.agents/skills/investigate-first/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/investigate-first/SKILL.md>) | Investigate first | No declarado |
| [.agents/skills/lean-build/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/lean-build/SKILL.md>) | Lean build | No declarado |
| [.agents/skills/migration/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/migration/SKILL.md>) | Migration | No declarado |
| [.agents/skills/remembermind-alert-engine/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-alert-engine/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-authorization-guardian/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-authorization-guardian/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-care-continuity/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-care-continuity/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-clinical-form-ux/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-clinical-form-ux/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-clinical-form-ux/references/clinical-interaction.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-clinical-form-ux/references/clinical-interaction.md>) | Matriz de interacción clínica | No declarado |
| [.agents/skills/remembermind-clinical-record-integrity/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-clinical-record-integrity/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-data-visualization-ux/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-data-visualization-ux/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-data-visualization-ux/references/visualization-contract.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-data-visualization-ux/references/visualization-contract.md>) | Contrato de visualización y datos | No declarado |
| [.agents/skills/remembermind-database-audit/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-database-audit/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-database-integrity/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-database-integrity/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-design-system/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-design-system/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-design-system/references/component-contracts.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-design-system/references/component-contracts.md>) | Catálogo conceptual y contrato de componentes | No declarado |
| [.agents/skills/remembermind-domain-architect/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-domain-architect/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-expert-system-engineering/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-expert-system-engineering/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-expert-system-engineering/references/knowledge-contract.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-expert-system-engineering/references/knowledge-contract.md>) | Contratos de ingeniería del conocimiento y validación | No declarado |
| [.agents/skills/remembermind-expert-validation/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-expert-validation/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-geriatric-workflows/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-geriatric-workflows/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-module-delivery/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-module-delivery/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-motion-microinteractions/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-motion-microinteractions/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-motion-microinteractions/references/motion-map.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-motion-microinteractions/references/motion-map.md>) | Mapa de microinteracciones | No declarado |
| [.agents/skills/remembermind-observability-audit/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-observability-audit/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-performance-guardian/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-performance-guardian/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-reference-fidelity/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-reference-fidelity/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-reference-fidelity/references/reference-analysis.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-reference-fidelity/references/reference-analysis.md>) | Ficha de fidelidad | No declarado |
| [.agents/skills/remembermind-release-check/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-release-check/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-responsive-accessibility/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-responsive-accessibility/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-responsive-accessibility/references/responsive-matrix.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-responsive-accessibility/references/responsive-matrix.md>) | Matriz de adaptación y acceso | No declarado |
| [.agents/skills/remembermind-security-review/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-security-review/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-source-of-truth/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-source-of-truth/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-source-of-truth/references/source-map.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-source-of-truth/references/source-map.md>) | Mapa de fuentes y conflictos | No declarado |
| [.agents/skills/remembermind-system-guardrails/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-system-guardrails/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-system-testing/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-system-testing/SKILL.md>) | Purpose | No declarado |
| [.agents/skills/remembermind-system-testing/references/test-contract.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-system-testing/references/test-contract.md>) | Contratos de prueba por riesgo | No declarado |
| [.agents/skills/remembermind-ui-review/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-ui-review/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-ux-flow-architect/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-ux-flow-architect/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-ux-flow-architect/references/ux-flow.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-ux-flow-architect/references/ux-flow.md>) | Especificación de flujo por rol | No declarado |
| [.agents/skills/remembermind-ux-writing/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-ux-writing/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-ux-writing/references/writing-patterns.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-ux-writing/references/writing-patterns.md>) | Patrones de copy operativo | No declarado |
| [.agents/skills/remembermind-visual-functional-qa/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-visual-functional-qa/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-visual-functional-qa/references/qa-acceptance.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-visual-functional-qa/references/qa-acceptance.md>) | Matriz de aceptación visual y funcional | No declarado |
| [.agents/skills/remembermind-warm-geriatric-art-direction/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-warm-geriatric-art-direction/SKILL.md>) | Propósito | No declarado |
| [.agents/skills/remembermind-warm-geriatric-art-direction/references/art-direction.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/remembermind-warm-geriatric-art-direction/references/art-direction.md>) | Brief artístico de una superficie | No declarado |
| [.agents/skills/safe-refactor/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/safe-refactor/SKILL.md>) | Safe refactor | No declarado |
| [.agents/skills/slides/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/slides/SKILL.md>) | Slides | No declarado |
| [.agents/skills/surgical-patch/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/surgical-patch/SKILL.md>) | Surgical patch | No declarado |
| [.agents/skills/ui-styling/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/ui-styling/SKILL.md>) | UI Styling Skill | No declarado |
| [.agents/skills/ui-ux-pro-max/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/ui-ux-pro-max/SKILL.md>) | ui-ux-pro-max | No declarado |
| [.agents/skills/verify-and-stop/SKILL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.agents/skills/verify-and-stop/SKILL.md>) | Verify and stop | No declarado |

### .github

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [.github/pull_request_template.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/.github/pull_request_template.md>) | pull_request_template.md | No declarado |

### app

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [app/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/AGENTS.md>) | app/AGENTS.md — RememberMind backend | No declarado |
| [app/Modulos/Admisiones/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Admisiones/README.md>) | Admisiones | No declarado |
| [app/Modulos/Alertas/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Alertas/README.md>) | Alertas | No declarado |
| [app/Modulos/ApoyoDecision/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/ApoyoDecision/README.md>) | ApoyoDecision | No declarado |
| [app/Modulos/Clinica/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Clinica/README.md>) | Clinica | No declarado |
| [app/Modulos/Cuidados/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Cuidados/README.md>) | Cuidados | No declarado |
| [app/Modulos/Identidad/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Identidad/README.md>) | Identidad | No declarado |
| [app/Modulos/Institucion/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Institucion/README.md>) | Institucion | No declarado |
| [app/Modulos/Medicacion/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Medicacion/README.md>) | Medicacion | No declarado |
| [app/Modulos/Reportes/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Reportes/README.md>) | Reportes | No declarado |
| [app/Modulos/Residentes/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Residentes/README.md>) | Residentes | No declarado |
| [app/Modulos/Social/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Social/README.md>) | Social | No declarado |
| [app/Modulos/Valoraciones/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/Modulos/Valoraciones/README.md>) | Valoraciones | No declarado |
| [app/SistemaExperto/Infrastructure/DECISION_PERSISTENCIA_IADL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/Infrastructure/DECISION_PERSISTENCIA_IADL.md>) | Antecedente de conservación e IADL | No declarado |
| [app/SistemaExperto/Infrastructure/PROPUESTA_CONSERVACION_V1.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/Infrastructure/PROPUESTA_CONSERVACION_V1.md>) | Diccionario propuesto: conservación exacta de memoria, cognición y DCL | No declarado |
| [app/SistemaExperto/Infrastructure/PROPUESTA_REUTILIZACION_D137_V2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/Infrastructure/PROPUESTA_REUTILIZACION_D137_V2.md>) | Conservación con reutilización de D-137: propuesta de nueve tablas adicionales | No declarado |
| [app/SistemaExperto/Infrastructure/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/Infrastructure/README.md>) | Infrastructure de O.R.I.O.N. | No declarado |
| [app/SistemaExperto/Infrastructure/REVISION_REUTILIZACION_D137.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/Infrastructure/REVISION_REUTILIZACION_D137.md>) | Revisión de reutilización de D-137 antes de ampliar conservación | No declarado |
| [app/SistemaExperto/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/README.md>) | O.R.I.O.N. — módulo experimental aislado | No declarado |
| [app/SistemaExperto/VERIFICACION_FASES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/app/SistemaExperto/VERIFICACION_FASES.md>) | Verificación de las fases — 17/10/2026 | No declarado |

### contratos

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [contratos/evaluacion-experta/v1/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/contratos/evaluacion-experta/v1/README.md>) | Solicitud tecnica v1 | No declarado |
| [contratos/eventos/v1/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/contratos/eventos/v1/README.md>) | EvaluacionTecnicaSolicitada v1 | No declarado |

### database

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [database/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/database/AGENTS.md>) | database/AGENTS.md — RememberMind DB V2.1 | No declarado |
| [database/seeders/LaPaz/FUENTES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/database/seeders/LaPaz/FUENTES.md>) | Fuentes y criterio de construcción | No declarado |
| [database/seeders/LaPaz/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/database/seeders/LaPaz/README.md>) | Historias residenciales de La Paz, 2025–2026 | No declarado |
| [database/seeders/LaPaz/VERIFICACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/database/seeders/LaPaz/VERIFICACION.md>) | Verificación de la cohorte La Paz | No declarado |

### docs (raíz)

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/AUDITORIA_DOCUMENTAL_FASE_1.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/AUDITORIA_DOCUMENTAL_FASE_1.md>) | DOCUMENT AUDIT | CURRENT |
| [docs/CHANGELOG.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/CHANGELOG.md>) | Changelog documental | CURRENT |
| [docs/DECISIONES_PENDIENTES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/DECISIONES_PENDIENTES.md>) | Decisiones pendientes | CURRENT |
| [docs/DEUDA_TECNICA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/DEUDA_TECNICA.md>) | Deuda técnica | CURRENT |
| [docs/ESTADO_ACTUAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/ESTADO_ACTUAL.md>) | Estado actual de RememberMind | CURRENT |
| [docs/GLOSARIO_DOMINIO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/GLOSARIO_DOMINIO.md>) | Glosario de dominio | CURRENT |
| [docs/GOBERNANZA_DOCUMENTAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/GOBERNANZA_DOCUMENTAL.md>) | Gobernanza documental | CURRENT |
| [docs/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/README.md>) | RememberMind Documentation | CURRENT |
| [docs/ROADMAP.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/ROADMAP.md>) | Roadmap | CURRENT |
| [docs/TRAZABILIDAD.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/TRAZABILIDAD.md>) | Trazabilidad de Fases 2 a 4 | CURRENT |
| [docs/VERIFICACION_DOCUMENTAL_FASE_1.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/VERIFICACION_DOCUMENTAL_FASE_1.md>) | Verificación documental de Fase 1 | CURRENT |
| [docs/auditoria.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditoria.md>) | Auditoría de Base de Datos - RememberMind | HISTORICAL |
| [docs/auditoria_roles_permisos.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditoria_roles_permisos.md>) | Auditoría de Roles y Permisos - RememberMind | HISTORICAL |

### docs/administrador

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/administrador/01-ADMISIONES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/01-ADMISIONES.md>) | Admisiones: funcionamiento, mejoras y verificación | No declarado |
| [docs/administrador/02-COMPONENTES-COMPARTIDOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/02-COMPONENTES-COMPARTIDOS.md>) | Componentes compartidos: listado y navegación | No declarado |
| [docs/administrador/03-RESIDENTES-Y-ALOJAMIENTO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/03-RESIDENTES-Y-ALOJAMIENTO.md>) | Residentes y alojamiento | No declarado |
| [docs/administrador/04-HABITACIONES-Y-CAMAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/04-HABITACIONES-Y-CAMAS.md>) | Habitaciones y camas: exploración visual | No declarado |
| [docs/administrador/05-OCUPACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/05-OCUPACION.md>) | Ocupación: mapa y alojamiento | No declarado |
| [docs/administrador/06-COORDINACION-DIARIA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/06-COORDINACION-DIARIA.md>) | Coordinación diaria del administrador | No declarado |
| [docs/administrador/07-EXPEDIENTE-ADMINISTRATIVO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/07-EXPEDIENTE-ADMINISTRATIVO.md>) | Expediente administrativo: consulta y vínculos | No declarado |
| [docs/administrador/08-SEGUIMIENTO-Y-REPORTES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/08-SEGUIMIENTO-Y-REPORTES.md>) | Seguimiento administrativo y reportes | No declarado |
| [docs/administrador/09-GRAFICOS-Y-VISTAS-POR-PROCESO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/09-GRAFICOS-Y-VISTAS-POR-PROCESO.md>) | Gráficos y vistas según el proceso | No declarado |
| [docs/administrador/10-CALENDARIOS-Y-FICHAS-LOCALES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/10-CALENDARIOS-Y-FICHAS-LOCALES.md>) | Calendarios y fichas en la misma ventana | No declarado |
| [docs/administrador/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/administrador/README.md>) | Administrador: trabajo gradual y documentación | No declarado |

### docs/architecture-audit

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/architecture-audit/00_RESUMEN_EJECUTIVO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/00_RESUMEN_EJECUTIVO.md>) | Auditoría arquitectónica de RememberMind | No declarado |
| [docs/architecture-audit/01_AS_IS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/01_AS_IS.md>) | Arquitectura AS-IS | No declarado |
| [docs/architecture-audit/02_REQUERIMIENTOS_CLIENTE_SIMULADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/02_REQUERIMIENTOS_CLIENTE_SIMULADO.md>) | Requerimientos del cliente simulado | No declarado |
| [docs/architecture-audit/03_GAP_ANALYSIS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/03_GAP_ANALYSIS.md>) | Brechas y prioridades | No declarado |
| [docs/architecture-audit/04_ARQUITECTURA_TO_BE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/04_ARQUITECTURA_TO_BE.md>) | Arquitectura objetivo propuesta | No declarado |
| [docs/architecture-audit/05_CAPAS_Y_RESPONSABILIDADES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/05_CAPAS_Y_RESPONSABILIDADES.md>) | Capas, casos de uso y convenciones | No declarado |
| [docs/architecture-audit/06_MAPA_MODULOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/06_MAPA_MODULOS.md>) | Mapa de módulos y matriz de refactorización | No declarado |
| [docs/architecture-audit/07_ROLES_PERMISOS_PROFESIONES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/07_ROLES_PERMISOS_PROFESIONES.md>) | Identidad, profesiones y autorización | No declarado |
| [docs/architecture-audit/08_MAPA_VISTAS_DASHBOARDS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/08_MAPA_VISTAS_DASHBOARDS.md>) | Vistas, dashboards y expediente 360 | No declarado |
| [docs/architecture-audit/09_BD_AS_IS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/09_BD_AS_IS.md>) | Base de datos AS-IS | No declarado |
| [docs/architecture-audit/10_BD_TO_BE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/10_BD_TO_BE.md>) | Modelo de datos objetivo | No declarado |
| [docs/architecture-audit/11_ERD_TO_BE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/11_ERD_TO_BE.md>) | ERD lógico TO-BE | No declarado |
| [docs/architecture-audit/12_FRONTEND_AS_IS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/12_FRONTEND_AS_IS.md>) | Frontend actual | No declarado |
| [docs/architecture-audit/13_FRONTEND_VUE_TO_BE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/13_FRONTEND_VUE_TO_BE.md>) | Vue + TypeScript + Inertia: propuesta | No declarado |
| [docs/architecture-audit/14_PLAN_MIGRACION_FRONTEND.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/14_PLAN_MIGRACION_FRONTEND.md>) | Convivencia Livewire → Vue | No declarado |
| [docs/architecture-audit/15_PLAN_REFACTORIZACION_BACKEND.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/15_PLAN_REFACTORIZACION_BACKEND.md>) | Plan de refactorización backend | No declarado |
| [docs/architecture-audit/16_TESTING_STRATEGY.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/16_TESTING_STRATEGY.md>) | Estrategia de pruebas | No declarado |
| [docs/architecture-audit/17_SEGURIDAD.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/17_SEGURIDAD.md>) | Seguridad y privacidad | No declarado |
| [docs/architecture-audit/18_SISTEMA_EXPERTO_INTEGRACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/18_SISTEMA_EXPERTO_INTEGRACION.md>) | Integración futura del sistema experto | No declarado |
| [docs/architecture-audit/19_ROADMAP.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/19_ROADMAP.md>) | Roadmap incremental para una desarrolladora | No declarado |
| [docs/architecture-audit/20_DECISIONES_ARQUITECTONICAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/20_DECISIONES_ARQUITECTONICAS.md>) | Registro de decisiones arquitectónicas | No declarado |
| [docs/architecture-audit/21_PAQUETE_REVISION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/21_PAQUETE_REVISION.md>) | 1. DICTAMEN GENERAL | No declarado |
| [docs/architecture-audit/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/README.md>) | architecture-audit | HISTORICAL |
| [docs/architecture-audit/RESUMEN_ARQUITECTURA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/RESUMEN_ARQUITECTURA.md>) | Resumen histórico de arquitectura | No declarado |
| [docs/architecture-audit/WALKTHROUGH_ALERTAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/architecture-audit/WALKTHROUGH_ALERTAS.md>) | Resumen de Mejoras: Alertas, Notificaciones, Paneles Laterales (Drawers) y Unificación de Diseño | No declarado |

### docs/arquitectura

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/arquitectura/ADR_ARQUITECTURA_HIBRIDA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/ADR_ARQUITECTURA_HIBRIDA.md>) | Arquitectura hibrida aprobada — 2026-10-10 | No declarado |
| [docs/arquitectura/ARQUITECTURA_VIGENTE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/ARQUITECTURA_VIGENTE.md>) | Mapa de arquitectura vigente | CURRENT |
| [docs/arquitectura/AUDITORIA_LIMPIEZA_2026_10_10.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/AUDITORIA_LIMPIEZA_2026_10_10.md>) | Auditoría de arquitectura y limpieza — 10/10/2026 | CURRENT |
| [docs/arquitectura/DASHBOARDS_POR_ROL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/DASHBOARDS_POR_ROL.md>) | Dashboards por rol: fuentes y límites | CURRENT |
| [docs/arquitectura/DESACOPLAMIENTO_2026_10_11.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/DESACOPLAMIENTO_2026_10_11.md>) | Desacoplamiento — 11/10/2026 | CURRENT |
| [docs/arquitectura/INFORME_MIGRACION_HIBRIDA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/INFORME_MIGRACION_HIBRIDA.md>) | Migración integral de RememberMind | No declarado |
| [docs/arquitectura/INTEGRACION_RAMA_FORMULARIOS_2026_10_10.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/INTEGRACION_RAMA_FORMULARIOS_2026_10_10.md>) | Integración de FORMULARIOS — 10/10/2026 | No declarado |
| [docs/arquitectura/LIMITES_VERIFICADOS_2026_10_11.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/LIMITES_VERIFICADOS_2026_10_11.md>) | Límites de capas verificados — 2026-10-11 | No declarado |
| [docs/arquitectura/LIMPIEZA_CARPETAS_2026_10_10.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/LIMPIEZA_CARPETAS_2026_10_10.md>) | Limpieza segura de archivos y carpetas | CURRENT |
| [docs/arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md>) | Permisos temporales del superadministrador | CURRENT |
| [docs/arquitectura/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/README.md>) | Arquitectura vigente de RememberMind | CURRENT |
| [docs/arquitectura/REMEMBERMIND_ARQUITECTURA_FRONTEND.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/REMEMBERMIND_ARQUITECTURA_FRONTEND.md>) | RememberMind — Arquitectura Frontend Canónica | DEPRECATED |
| [docs/arquitectura/REMEMBERMIND_BASELINE_TECNICO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/REMEMBERMIND_BASELINE_TECNICO.md>) | RememberMind — Baseline técnico y entorno de ejecución | CURRENT |
| [docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md>) | RememberMind — Baseline funcional congelado de roles | CURRENT |

### docs/auditorias

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/auditorias/2026-10-21/CIERRE_POR_PROFESION_Y_VALIDACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/CIERRE_POR_PROFESION_Y_VALIDACION.md>) | Cierre por profesión y validación de O.R.I.O.N. | No declarado |
| [docs/auditorias/2026-10-21/EVIDENCIA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/EVIDENCIA.md>) | Evidencia y límites de la auditoría | No declarado |
| [docs/auditorias/2026-10-21/INVENTARIO_DISENO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/INVENTARIO_DISENO.md>) | Inventario estático de reutilización UI — 21/10/2026 | No declarado |
| [docs/auditorias/2026-10-21/PARTE_1_ADMINISTRATIVA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/PARTE_1_ADMINISTRATIVA.md>) | Parte 1 — Auditoría administrativa, operativa y asistencial | No declarado |
| [docs/auditorias/2026-10-21/PARTE_2_SISTEMA_EXPERTO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/PARTE_2_SISTEMA_EXPERTO.md>) | Parte 2 — Auditoría del sistema experto O.R.I.O.N. | No declarado |
| [docs/auditorias/2026-10-21/PLAN_CIERRE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/PLAN_CIERRE.md>) | Plan de cierre derivado de la auditoría | No declarado |
| [docs/auditorias/2026-10-21/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/README.md>) | Auditoría integral de RememberMind — 21/10/2026 | No declarado |
| [docs/auditorias/2026-10-21/RED_SEMANTICA_DETALLE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/RED_SEMANTICA_DETALLE.md>) | Red semántica: estructura, participación e inferencia | No declarado |
| [docs/auditorias/2026-10-21/REVISION_BDD_SEGURIDAD.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/REVISION_BDD_SEGURIDAD.md>) | Revisión independiente de BDD, autorización y arquitectura | No declarado |
| [docs/auditorias/2026-10-21/REVISION_EXPERTO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/REVISION_EXPERTO.md>) | Auditoría integral de O.R.I.O.N. — 21/10/2026 | No declarado |
| [docs/auditorias/2026-10-21/REVISION_OPERATIVA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-21/REVISION_OPERATIVA.md>) | Auditoría operativa y administrativa — 21/10/2026 | No declarado |
| [docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md>) | Auditoría integral de RememberMind y su sistema experto | No declarado |
| [docs/auditorias/2026-10-25/INVENTARIO_DOCUMENTAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/2026-10-25/INVENTARIO_DOCUMENTAL.md>) | Inventario documental de RememberMind | No declarado |
| [docs/auditorias/cohorte-aplicacion-local-2026-10-22/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/cohorte-aplicacion-local-2026-10-22/README.md>) | Acta: 30 residentes de entrenamiento visibles en la aplicación | No declarado |
| [docs/auditorias/cohorte-investigacion-2026-10-22/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/cohorte-investigacion-2026-10-22/README.md>) | Acta técnica: cohorte cognitiva investigada | No declarado |
| [docs/auditorias/cohorte-nombres-2026-10-22/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/cohorte-nombres-2026-10-22/README.md>) | Nombres naturales para los treinta residentes de entrenamiento | No declarado |
| [docs/auditorias/observaciones-sinteticas-2026-10-22/OBSERVACIONES_SINTETICAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/observaciones-sinteticas-2026-10-22/OBSERVACIONES_SINTETICAS.md>) | 20 observaciones sintéticas con fechas y situaciones variadas | No declarado |
| [docs/auditorias/observaciones-sinteticas-2026-10-22/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/auditorias/observaciones-sinteticas-2026-10-22/README.md>) | Observaciones variadas para probar la captura | No declarado |

### docs/base-de-datos

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/base-de-datos/AUDITORIA_FORMULARIO_ALIMENTACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_ALIMENTACION.md>) | Auditoría: cuidado de alimentación en Nuevo registro | CURRENT |
| [docs/base-de-datos/AUDITORIA_FORMULARIO_DOLOR.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_DOLOR.md>) | Auditoría Dolor V2 | CURRENT |
| [docs/base-de-datos/AUDITORIA_FORMULARIO_ELIMINACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_ELIMINACION.md>) | Auditoría del formulario Eliminación V2 | CURRENT |
| [docs/base-de-datos/AUDITORIA_FORMULARIO_HIDRATACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_HIDRATACION.md>) | Hidratación V2 — contrato vigente | CURRENT |
| [docs/base-de-datos/AUDITORIA_FORMULARIO_MEDICACION_PROGRAMADA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_MEDICACION_PROGRAMADA.md>) | Auditoría: Nuevo registro de Enfermería / Medicación programada | CURRENT |
| [docs/base-de-datos/AUDITORIA_FORMULARIO_MOVILIDAD.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_MOVILIDAD.md>) | Auditoría: cuidado de movilidad en Nuevo registro | CURRENT |
| [docs/base-de-datos/AUDITORIA_FORMULARIO_SIGNOS_VITALES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/AUDITORIA_FORMULARIO_SIGNOS_VITALES.md>) | Auditoría: Nuevo registro de Enfermería / Signos vitales | CURRENT |
| [docs/base-de-datos/CODIGOS_CORRELATIVOS_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/CODIGOS_CORRELATIVOS_RESULTADO.md>) | Códigos correlativos: resultado | No declarado |
| [docs/base-de-datos/DECISION_ARQUITECTURA_BDD_V2_1.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_ARQUITECTURA_BDD_V2_1.md>) | Decisión de Arquitectura — BDD Operativa V2.1 | APPROVED |
| [docs/base-de-datos/DECISION_CODIGOS_CORRELATIVOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_CODIGOS_CORRELATIVOS.md>) | Códigos correlativos — decisión aprobada | No declarado |
| [docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_CONSERVACION_EXPERTA_V2_2026_10_17.md>) | Conservación experta V2: nueve tablas y reutilización de D-137 | No declarado |
| [docs/base-de-datos/DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_CONSOLIDACION_BLOQUES_EXPERTOS_2026_10_22.md>) | Consolidación de cuatro bloques expertos — 22/10/2026 | No declarado |
| [docs/base-de-datos/DECISION_DOLOR_V2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_DOLOR_V2.md>) | Decisión Dolor V2 — 09/10/2026 | CURRENT |
| [docs/base-de-datos/DECISION_ELIMINACION_V2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_ELIMINACION_V2.md>) | Eliminación V2 — extensión aditiva aprobada | APPROVED |
| [docs/base-de-datos/DECISION_FOTOGRAFIAS_HERIDAS_V2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_FOTOGRAFIAS_HERIDAS_V2.md>) | Fotografías de heridas y curaciones V2 | APPROVED |
| [docs/base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md>) | Galería institucional — extensión aprobada | No declarado |
| [docs/base-de-datos/DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_IADL_FUENTE_EXPERTA_2026_10_17.md>) | Decisión de la propietaria — 17/10/2026 | APPROVED |
| [docs/base-de-datos/DECISION_MOVILIDAD_V2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_MOVILIDAD_V2.md>) | Movilidad V2 — extensión aditiva aprobada | APPROVED |
| [docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md>) | Extensión clínica V2.2 — objetivos individuales de signos vitales | APPROVED |
| [docs/base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_REDUCCION_EXPERTA_2026_10_20.md>) | Reducción aprobada de cuatro tablas expertas — 20/10/2026 | No declarado |
| [docs/base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_REVISIONES_EXPERTAS_2026_10_17.md>) | Revisiones profesionales separadas de la valoración experta | No declarado |
| [docs/base-de-datos/DECISION_UNIDAD_TALLA_CM.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/DECISION_UNIDAD_TALLA_CM.md>) | Decisión de Arquitectura (ADR) — Unidad Canónica de Talla en Centímetros | APPROVED |
| [docs/base-de-datos/INVENTARIO_MIGRACION_APLICACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/INVENTARIO_MIGRACION_APLICACION.md>) | Inventario inicial de migración de aplicación | HISTORICAL |
| [docs/base-de-datos/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/README.md>) | Base de datos de RememberMind | CURRENT |
| [docs/base-de-datos/REMEMBERMIND_BDD_69_TABLAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/REMEMBERMIND_BDD_69_TABLAS.md>) | RememberMind — HISTÓRICO — BDD Operativa V2.0: 69 tablas | HISTORICAL |
| [docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md>) | RememberMind — BDD Operativa V2.1: 70 tablas | CURRENT |
| [docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md>) | RememberMind — Baseline Rector de la BDD Operativa | CURRENT |
| [docs/base-de-datos/RESULTADO_MIGRACION_APLICACION_V2.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/RESULTADO_MIGRACION_APLICACION_V2.md>) | Resultado de migración de la aplicación a BDD V2 | HISTORICAL |
| [docs/base-de-datos/SEEDERS_LOCALES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/SEEDERS_LOCALES.md>) | Datos sintéticos para desarrollo local | No declarado |
| [docs/base-de-datos/SEED_HISTORICO_2026.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/base-de-datos/SEED_HISTORICO_2026.md>) | Carga sintética histórica 2026 | CURRENT |

### docs/frontend

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/frontend/ACTUALIZACION_ENFERMERIA_ACCESO_PSICOLOGIA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/ACTUALIZACION_ENFERMERIA_ACCESO_PSICOLOGIA_2026_10_22.md>) | Resultados de Enfermería y acceso desde Psicología | No declarado |
| [docs/frontend/ADMINISTRACION_MEDICACION_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/ADMINISTRACION_MEDICACION_RESULTADO.md>) | Administración de medicación — entrega del 14/10/2026 | No declarado |
| [docs/frontend/AUDITORIA_FORMULARIOS_ENFERMERIA_2026_10_14.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/AUDITORIA_FORMULARIOS_ENFERMERIA_2026_10_14.md>) | Auditoría de formularios de Enfermería — 14/10/2026 | No declarado |
| [docs/frontend/AUDITORIA_SKILLS_UX_UI.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/AUDITORIA_SKILLS_UX_UI.md>) | Auditoría del stack de skills UX/UI | CURRENT |
| [docs/frontend/CENTRAL_CUIDADOS_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CENTRAL_CUIDADOS_RESULTADO.md>) | Centro de cuidados de Enfermería | No declarado |
| [docs/frontend/COGNICION_REDISENO_UI_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/COGNICION_REDISENO_UI_RESULTADO.md>) | Cognición: distribución, color e interacción | No declarado |
| [docs/frontend/COMPOSICION_GLOBAL_2026_10_14.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/COMPOSICION_GLOBAL_2026_10_14.md>) | Composición global — 14/10/2026 | No declarado |
| [docs/frontend/CONDUCTA_V2_REFERENCE_ANALYSIS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CONDUCTA_V2_REFERENCE_ANALYSIS.md>) | Conducta V2 — referencia y contrato | No declarado |
| [docs/frontend/CONDUCTA_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CONDUCTA_V2_RESULTADO.md>) | CONDUCTA V2 — RESULTADO | No declarado |
| [docs/frontend/CONSULTAS_FORMULARIOS_UNIFICADAS_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CONSULTAS_FORMULARIOS_UNIFICADAS_RESULTADO.md>) | Consultas de formularios — unificación del 14/10/2026 | No declarado |
| [docs/frontend/CONTRATO_VISUAL_UX_UI.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CONTRATO_VISUAL_UX_UI.md>) | Contrato compartido de UX/UI de RememberMind | CURRENT |
| [docs/frontend/CORRECCIONES_FORMULARIOS_UX_2026_10_14.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CORRECCIONES_FORMULARIOS_UX_2026_10_14.md>) | Correcciones seleccionadas de formularios — 14/10/2026 | No declarado |
| [docs/frontend/CORRECCION_NAVEGACION_DETALLE_COGNITIVO_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/CORRECCION_NAVEGACION_DETALLE_COGNITIVO_2026_10_22.md>) | Corrección de navegación al detalle cognitivo — 22/10/2026 | No declarado |
| [docs/frontend/DIRECTORIO_RESIDENTES_COMPOSICION_2026_10_14.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/DIRECTORIO_RESIDENTES_COMPOSICION_2026_10_14.md>) | Mis residentes — composición y tipografía, 14/10/2026 | No declarado |
| [docs/frontend/DIRECTORIO_RESIDENTES_FILTROS_LOGICA_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/DIRECTORIO_RESIDENTES_FILTROS_LOGICA_2026_10_15.md>) | Mis residentes — lógica de filtros, 15/10/2026 | No declarado |
| [docs/frontend/EMERGENCIAS_CARDS_2026_10_16.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/EMERGENCIAS_CARDS_2026_10_16.md>) | Cards de emergencias y alertas — 16/10/2026 | No declarado |
| [docs/frontend/EVALUACION_COGNITIVA_ENFERMERIA_ENTREGA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/EVALUACION_COGNITIVA_ENFERMERIA_ENTREGA_2026_10_20.md>) | Evaluación cognitiva y cuidados — entrega integrada de Enfermería | No declarado |
| [docs/frontend/EVALUACION_COGNITIVA_RESPONSIVE_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/EVALUACION_COGNITIVA_RESPONSIVE_2026_10_21.md>) | Ajuste responsive de la consulta cognitiva — 21/10/2026 | No declarado |
| [docs/frontend/EXPERTO_CONEXIONES_POR_RESIDENTE_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/EXPERTO_CONEXIONES_POR_RESIDENTE_2026_10_22.md>) | Sistema experto: conexiones y recorridos por residente | No declarado |
| [docs/frontend/FASE_11_INTEGRACION_VISUAL_DASHBOARD.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FASE_11_INTEGRACION_VISUAL_DASHBOARD.md>) | Fase 11 — Integración visual del dashboard de Enfermería | HISTORICAL |
| [docs/frontend/FASE_3_RESUMEN_COGNITIVO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FASE_3_RESUMEN_COGNITIVO.md>) | Fase 3 — Memoria y estado cognitivo | No declarado |
| [docs/frontend/FASE_4_INFORME_COGNITIVO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FASE_4_INFORME_COGNITIVO.md>) | Fase 4 — Informe médico de resultados expertos | No declarado |
| [docs/frontend/FASE_9B_TAREAS_UBICACION_NOTAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FASE_9B_TAREAS_UBICACION_NOTAS.md>) | Fase 9B: tareas, ubicación y observaciones de Enfermería | HISTORICAL |
| [docs/frontend/FASE_9_DISTRIBUCION_Y_AGENDA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FASE_9_DISTRIBUCION_Y_AGENDA.md>) | Fase 9: distribución asistencial y agenda de Enfermería | HISTORICAL |
| [docs/frontend/FORMULARIOS_COMPOSICION_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIOS_COMPOSICION_2026_10_15.md>) | Formularios: composición compartida — 15/10/2026 | No declarado |
| [docs/frontend/FORMULARIO_DOLOR_PATTERN_V1.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_DOLOR_PATTERN_V1.md>) | DOLOR REDISEÑADO — RESULTADO | HISTORICAL |
| [docs/frontend/FORMULARIO_DOLOR_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_DOLOR_V2_RESULTADO.md>) | DOLOR V2 — RESULTADO | CURRENT |
| [docs/frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md>) | ELIMINACIÓN V2 — RESULTADO | ACCEPTED |
| [docs/frontend/FORMULARIO_HERIDAS_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_HERIDAS_V2_RESULTADO.md>) | HERIDAS / CURACIONES V2 — RESULTADO | CURRENT |
| [docs/frontend/FORMULARIO_HIDRATACION_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_HIDRATACION_V2_RESULTADO.md>) | HIDRATACIÓN V2 — RESULTADO | ACCEPTED |
| [docs/frontend/FORMULARIO_INGESTA_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_INGESTA_V2_RESULTADO.md>) | INGESTA V2 — RESULTADO | IMPLEMENTED_ACCEPTANCE_PENDING |
| [docs/frontend/FORMULARIO_MOVILIDAD_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/FORMULARIO_MOVILIDAD_V2_RESULTADO.md>) | MOVILIDAD V2 — RESULTADO | ACCEPTED |
| [docs/frontend/GALERIA_INSTITUCIONAL_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/GALERIA_INSTITUCIONAL_2026_10_17.md>) | Galería del centro — entrega del 17/10/2026 | No declarado |
| [docs/frontend/GERENCIA_INICIO_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/GERENCIA_INICIO_2026_10_17.md>) | Gerencia — primer corte del inicio institucional | No declarado |
| [docs/frontend/GRAFICAS_MI_TURNO_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/GRAFICAS_MI_TURNO_2026_10_15.md>) | Gráficas de Mi turno — 15/10/2026 | No declarado |
| [docs/frontend/HERIDAS_CURACIONES_REFERENCE_ANALYSIS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/HERIDAS_CURACIONES_REFERENCE_ANALYSIS.md>) | Heridas / curaciones: análisis de referencia | No declarado |
| [docs/frontend/IDENTIFICADORES_INTERNOS_2026_10_16.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/IDENTIFICADORES_INTERNOS_2026_10_16.md>) | Identificadores internos en la interfaz | No declarado |
| [docs/frontend/INCIDENTES_UX_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/INCIDENTES_UX_2026_10_15.md>) | Incidentes: consulta y seguimiento | No declarado |
| [docs/frontend/LIMPIEZA_Y_DESCARTE_FORMULARIOS_ENFERMERIA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/LIMPIEZA_Y_DESCARTE_FORMULARIOS_ENFERMERIA.md>) | Limpieza y descarte de captura — Enfermería | No declarado |
| [docs/frontend/MEDICACION_IMAGEN_Y_ACCIONES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/MEDICACION_IMAGEN_Y_ACCIONES.md>) | Medicación: imagen, distribución y acciones | No declarado |
| [docs/frontend/MEDICACION_VISTA_COMPOSICION_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/MEDICACION_VISTA_COMPOSICION_2026_10_15.md>) | Medicación — composición operativa, 15/10/2026 | No declarado |
| [docs/frontend/MEJORA_FICHA_PSICOLOGICA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/MEJORA_FICHA_PSICOLOGICA_2026_10_22.md>) | Ficha psicológica del residente — mejora de presentación | No declarado |
| [docs/frontend/NUEVA_HERIDA_REDISENO_V2_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/NUEVA_HERIDA_REDISENO_V2_RESULTADO.md>) | Nueva herida — rediseño V2 | No declarado |
| [docs/frontend/OBSERVACION_COGNITIVA_IMPLEMENTACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/OBSERVACION_COGNITIVA_IMPLEMENTACION.md>) | Observación cognitiva — Enfermería | No declarado |
| [docs/frontend/OPTIMIZACION_BUNDLE_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/OPTIMIZACION_BUNDLE_2026_10_17.md>) | Reducción de JavaScript compartido | No declarado |
| [docs/frontend/ORION_EXPLICABILIDAD_ENTREGA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/ORION_EXPLICABILIDAD_ENTREGA_2026_10_20.md>) | O.R.I.O.N. — entrega de interfaz, explicabilidad y trazabilidad | No declarado |
| [docs/frontend/PALETA_CALIDA_COMPOSICION_2026_10_14.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PALETA_CALIDA_COMPOSICION_2026_10_14.md>) | Paleta cálida y composición — 14/10/2026 | No declarado |
| [docs/frontend/PALETA_FINAL_SOLIDA_Y_RELIEVE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PALETA_FINAL_SOLIDA_Y_RELIEVE.md>) | Paleta final: superficies sólidas y relieve | CURRENT |
| [docs/frontend/PENDIENTE_BADGES_ESTADO_RESIDENTE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PENDIENTE_BADGES_ESTADO_RESIDENTE.md>) | Pendiente: etiquetas de estado en «Mis residentes» | CURRENT |
| [docs/frontend/PERSONAL_RESPONSIVE_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PERSONAL_RESPONSIVE_2026_10_17.md>) | Personal institucional — adaptación de la vista de Gerencia | No declarado |
| [docs/frontend/PLAN_UNIFICACION_VISUAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PLAN_UNIFICACION_VISUAL.md>) | Plan de unificación visual de RememberMind | HISTORICAL |
| [docs/frontend/PREADMISIONES_INTERACTIVAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PREADMISIONES_INTERACTIVAS.md>) | Preadmisiones: interfaz y controles institucionales | No declarado |
| [docs/frontend/PROPUESTA_GALERIA_INSTITUCIONAL_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PROPUESTA_GALERIA_INSTITUCIONAL_2026_10_17.md>) | Galería de fotografías del centro | IMPLEMENTADA_QA_SESION_PENDIENTE |
| [docs/frontend/PROPUESTA_VISTA_RESULTADOS_PSICOLOGIA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/PROPUESTA_VISTA_RESULTADOS_PSICOLOGIA_2026_10_22.md>) | Vista de evaluación cognitiva y funcional para Psicología | No declarado |
| [docs/frontend/REFACTOR_BLADE_FICHA_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/REFACTOR_BLADE_FICHA_2026_10_17.md>) | Organización de Blade en la ficha clínica | No declarado |
| [docs/frontend/REFACTOR_DASHBOARD_ENFERMERIA_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/REFACTOR_DASHBOARD_ENFERMERIA_2026_10_17.md>) | Organización del Dashboard de Enfermería | No declarado |
| [docs/frontend/REFACTOR_DIRECTORIO_ENFERMERIA_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/REFACTOR_DIRECTORIO_ENFERMERIA_2026_10_17.md>) | Estructura del directorio de residentes de Enfermería | No declarado |
| [docs/frontend/RELIEVE_MICROINTERACCIONES_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/RELIEVE_MICROINTERACCIONES_2026_10_15.md>) | Relieve y microinteracciones compartidas — 15/10/2026 | No declarado |
| [docs/frontend/RESULTADOS_COGNITIVOS_REFERENCIA_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/RESULTADOS_COGNITIVOS_REFERENCIA_2026_10_21.md>) | Informe cognitivo y funcional: adaptación de la referencia | No declarado |
| [docs/frontend/REVISION_PSICOLOGIA_AVATAR_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/REVISION_PSICOLOGIA_AVATAR_2026_10_22.md>) | Revisión de Psicología, avatar y acceso a los cambios — 22/10/2026 | No declarado |
| [docs/frontend/SELECTOR_REGISTROS_ENFERMERIA_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/SELECTOR_REGISTROS_ENFERMERIA_RESULTADO.md>) | Selector de registros de enfermería | No declarado |
| [docs/frontend/SINCRONIZACION_VISTA_EXPERTO_LOCAL_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/SINCRONIZACION_VISTA_EXPERTO_LOCAL_2026_10_22.md>) | Vista general del sistema experto: sincronización local | No declarado |
| [docs/frontend/SISTEMA_EXPERTO_REFERENCIAS_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/SISTEMA_EXPERTO_REFERENCIAS_2026_10_22.md>) | Sistema experto: siete referencias, 22/10/2026 | No declarado |
| [docs/frontend/STACK_SKILLS_UX_UI.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/STACK_SKILLS_UX_UI.md>) | Stack de skills UX/UI de RememberMind | CURRENT |
| [docs/frontend/SUPERADMIN_EXPLORACION_CONECTADA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/SUPERADMIN_EXPLORACION_CONECTADA_2026_10_22.md>) | Superadministración: exploración conectada del sistema experto | No declarado |
| [docs/frontend/SUPERFICIES_CLINICAS_CLARAS_RESULTADO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/SUPERFICIES_CLINICAS_CLARAS_RESULTADO.md>) | Superficies clínicas claras — 14/10/2026 | No declarado |
| [docs/frontend/SUPERFICIES_CUIDADO_COLOR_CONTRASTE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/SUPERFICIES_CUIDADO_COLOR_CONTRASTE.md>) | Superficies de cuidado: composición y contraste | No declarado |
| [docs/frontend/VALORACIONES_INTERACTIVAS_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/frontend/VALORACIONES_INTERACTIVAS_2026_10_22.md>) | Valoraciones interactivas — implementación del 22/10/2026 | No declarado |

### docs/historico

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/historico/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/historico/README.md>) | historico | HISTORICAL |
| [docs/historico/SOLICITUD_STACK_SKILLS_SISTEMA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/historico/SOLICITUD_STACK_SKILLS_SISTEMA.md>) | Solicitud preservada | HISTORICAL |

### docs/modulos

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/modulos/_PLANTILLA_CONTRATO_MODULO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/_PLANTILLA_CONTRATO_MODULO.md>) | Plantilla de contrato de módulo | CURRENT |
| [docs/modulos/alertas/ALERTAS_POR_ESPECIALIDAD_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/ALERTAS_POR_ESPECIALIDAD_2026_10_21.md>) | Alertas por especialidad — entrega local del 21/10/2026 | No declarado |
| [docs/modulos/alertas/FLUJOS_DIFERENCIADOS_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/FLUJOS_DIFERENCIADOS_2026_10_22.md>) | Flujos de Alertas por rol — 22/10/2026 | No declarado |
| [docs/modulos/alertas/MAPAS_AJUSTABLES_POR_ROL_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/MAPAS_AJUSTABLES_POR_ROL_2026_10_22.md>) | Vistas de relaciones ajustables por rol | No declarado |
| [docs/modulos/alertas/PUBLICACION_E_INTEGRACION_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/PUBLICACION_E_INTEGRACION_2026_10_22.md>) | Publicación e integración de Alertas | No declarado |
| [docs/modulos/alertas/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/README.md>) | Alertas | CURRENT |
| [docs/modulos/alertas/REDISENO_INTERACTIVO_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/REDISENO_INTERACTIVO_2026_10_21.md>) | Alertas clínicas y cuidados: entrega local del 21/10/2026 | No declarado |
| [docs/modulos/alertas/REGISTRO_ENFERMERIA_Y_ENTRADA_EXPERTA_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/REGISTRO_ENFERMERIA_Y_ENTRADA_EXPERTA_2026_10_21.md>) | Intervención de Enfermería y entrada al sistema experto | No declarado |
| [docs/modulos/alertas/REPRESENTACIONES_FUNCIONALES_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/REPRESENTACIONES_FUNCIONALES_2026_10_22.md>) | Representaciones funcionales distintas por rol | No declarado |
| [docs/modulos/alertas/REVISION_ENFERMERIA_Y_CANALES_2026_10_15.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/REVISION_ENFERMERIA_Y_CANALES_2026_10_15.md>) | Alertas de Enfermería: revisión funcional y canales | No declarado |
| [docs/modulos/alertas/REVISION_INTEGRAL_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/REVISION_INTEGRAL_2026_10_20.md>) | Alertas: corrección y experiencia por responsabilidades | No declarado |
| [docs/modulos/alertas/VINCULOS_POR_ESPECIALIDAD_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/alertas/VINCULOS_POR_ESPECIALIDAD_2026_10_22.md>) | Alertas identificables por especialidad y área de origen | No declarado |
| [docs/modulos/ingreso-institucional/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/ingreso-institucional/README.md>) | Ingreso institucional | CURRENT |
| [docs/modulos/jornadas-asignaciones/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/jornadas-asignaciones/README.md>) | Jornadas y asignaciones | CURRENT |
| [docs/modulos/medicacion/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/medicacion/README.md>) | Medicación | CURRENT |
| [docs/modulos/pase-turno/CIERRE_PASE_TURNO_2026_10_16.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/pase-turno/CIERRE_PASE_TURNO_2026_10_16.md>) | Pase de turno — cierre funcional del 16–17/10/2026 | No declarado |
| [docs/modulos/pase-turno/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/pase-turno/README.md>) | Pase de turno y continuidad | CURRENT |
| [docs/modulos/residentes-alojamiento/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/residentes-alojamiento/README.md>) | Residentes y alojamiento | CURRENT |
| [docs/modulos/signos-vitales/INTERPRETACION_UNIFICADA_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/signos-vitales/INTERPRETACION_UNIFICADA_2026_10_17.md>) | Interpretación compartida de signos vitales — 17/10/2026 | No declarado |
| [docs/modulos/signos-vitales/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/modulos/signos-vitales/README.md>) | Signos vitales | CURRENT |

### docs/produccion

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/produccion/DESPLIEGUE_SEGURO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/produccion/DESPLIEGUE_SEGURO.md>) | Despliegue seguro de RememberMind | CURRENT |

### docs/refactorizacion-total

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/refactorizacion-total/00_RESUMEN.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/00_RESUMEN.md>) | Diseño técnico de refactorización total — RememberMind | No declarado |
| [docs/refactorizacion-total/01_INVENTARIO_FUNCIONAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/01_INVENTARIO_FUNCIONAL.md>) | Inventario funcional AS-IS | No declarado |
| [docs/refactorizacion-total/02_BD_AS_IS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/02_BD_AS_IS.md>) | Base de datos AS-IS | No declarado |
| [docs/refactorizacion-total/03_BD_TO_BE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/03_BD_TO_BE.md>) | Base de datos TO-BE y ERD completo | No declarado |
| [docs/refactorizacion-total/04_MAPA_MIGRACION_BD.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/04_MAPA_MIGRACION_BD.md>) | Mapa de migración AS-IS → TO-BE | No declarado |
| [docs/refactorizacion-total/05_MODULOS_TO_BE.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/05_MODULOS_TO_BE.md>) | Módulos TO-BE | No declarado |
| [docs/refactorizacion-total/06_CRUDS_Y_VISTAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/06_CRUDS_Y_VISTAS.md>) | CRUDs, estados y vistas Blade/Livewire | No declarado |
| [docs/refactorizacion-total/07_ROLES_PERMISOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/07_ROLES_PERMISOS.md>) | Roles, permisos y autorización contextual | No declarado |
| [docs/refactorizacion-total/08_ESTRUCTURA_CODIGO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/08_ESTRUCTURA_CODIGO.md>) | Estructura de código propuesta | No declarado |
| [docs/refactorizacion-total/09_PLAN_REFACTORIZACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/09_PLAN_REFACTORIZACION.md>) | Plan de refactorización total (no ejecutado) | No declarado |
| [docs/refactorizacion-total/10_DECISIONES_PENDIENTES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/10_DECISIONES_PENDIENTES.md>) | Decisiones pendientes de aprobación | No declarado |
| [docs/refactorizacion-total/12_REORGANIZACION_CODIGO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/12_REORGANIZACION_CODIGO.md>) | Reorganización del código | No declarado |
| [docs/refactorizacion-total/13_AUDITORIA_VENTANAS_FORMULARIOS_Y_ACCIONES.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/13_AUDITORIA_VENTANAS_FORMULARIOS_Y_ACCIONES.md>) | Auditoría de ventanas, formularios, botones y pantallas | No declarado |
| [docs/refactorizacion-total/14_UNIFICACION_FRONTEND.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/14_UNIFICACION_FRONTEND.md>) | Unificación del frontend | No declarado |
| [docs/refactorizacion-total/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/refactorizacion-total/README.md>) | refactorizacion-total | HISTORICAL |

### docs/seguridad

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/seguridad/MATRIZ_ROLES_PERMISOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/seguridad/MATRIZ_ROLES_PERMISOS.md>) | Matriz de roles, permisos y entradas | CURRENT |
| [docs/seguridad/MODELO_AUTORIZACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/seguridad/MODELO_AUTORIZACION.md>) | Modelo de autorización | CURRENT |
| [docs/seguridad/RECHAZOS_ASINCRONOS_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/seguridad/RECHAZOS_ASINCRONOS_2026_10_17.md>) | Rechazos asíncronos de acceso — 17/10/2026 | No declarado |

### docs/sistema

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/sistema/AUDITORIA_SKILLS_SISTEMA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/AUDITORIA_SKILLS_SISTEMA.md>) | Auditoría de skills de sistema de RememberMind | CURRENT |
| [docs/sistema/CARGA_LA_PAZ_LOCAL_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/CARGA_LA_PAZ_LOCAL_2026_10_21.md>) | Integración y carga local de la cohorte La Paz | No declarado |
| [docs/sistema/CONTINUIDAD_ASISTENCIAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/CONTINUIDAD_ASISTENCIAL.md>) | Continuidad asistencial | CURRENT |
| [docs/sistema/CORREO_SMTP_CONFIGURACION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/CORREO_SMTP_CONFIGURACION.md>) | Conexión del correo saliente | No declarado |
| [docs/sistema/DEPENDENCIAS_DESARROLLO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/DEPENDENCIAS_DESARROLLO.md>) | Dependencias frontend — revisión 2026-10-11 | No declarado |
| [docs/sistema/DESARROLLO_SEGURO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/DESARROLLO_SEGURO.md>) | Desarrollo y pruebas seguras | No declarado |
| [docs/sistema/ESTADOS_Y_CICLOS_DE_VIDA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/ESTADOS_Y_CICLOS_DE_VIDA.md>) | Estados y ciclos de vida | CURRENT |
| [docs/sistema/FLUJOS_GERIATRICOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/FLUJOS_GERIATRICOS.md>) | Flujos geriátricos | CURRENT |
| [docs/sistema/GIT_FLOW.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/GIT_FLOW.md>) | Flujo Git de RememberMind | No declarado |
| [docs/sistema/HISTORIALES_SUPERADMINISTRADOR_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/HISTORIALES_SUPERADMINISTRADOR_2026_10_20.md>) | Historiales con Superadministrador | CURRENT |
| [docs/sistema/INVARIANTES_NEGOCIO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/INVARIANTES_NEGOCIO.md>) | Invariantes de negocio | CURRENT |
| [docs/sistema/MAPA_DOMINIO_FUNCIONAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/MAPA_DOMINIO_FUNCIONAL.md>) | Mapa funcional y límites de RememberMind | CURRENT |
| [docs/sistema/PREPARACION_DESARROLLO_2026_10_11.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/PREPARACION_DESARROLLO_2026_10_11.md>) | Preparación para continuar desarrollo — 11/10/2026 | CURRENT |
| [docs/sistema/PUBLICACION_AVANCES_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/PUBLICACION_AVANCES_2026_10_17.md>) | Publicación de avances — 17/10/2026 | No declarado |
| [docs/sistema/PUBLICACION_CONJUNTA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/PUBLICACION_CONJUNTA_2026_10_22.md>) | Publicación conjunta para sincronización del equipo — 22/10/2026 | No declarado |
| [docs/sistema/REPARACION_CONFLICTOS_INTEGRACION_2026_10_23.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/REPARACION_CONFLICTOS_INTEGRACION_2026_10_23.md>) | Reparación de conflictos guardados en la integración — 23/10/2026 | No declarado |
| [docs/sistema/STACK_SKILLS_SISTEMA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/STACK_SKILLS_SISTEMA.md>) | Stack de skills de sistema de RememberMind | CURRENT |
| [docs/sistema/VERIFICACION_SKILLS_SISTEMA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema/VERIFICACION_SKILLS_SISTEMA.md>) | Verificación del stack de skills de sistema | CURRENT |

### docs/sistema-experto

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [docs/sistema-experto/ACEPTACION_INTEGRAL_ORION_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ACEPTACION_INTEGRAL_ORION_2026_10_21.md>) | O.R.I.O.N. — corte de aceptación integral | No declarado |
| [docs/sistema-experto/ANALISIS_REFACTOR_REDUCCION_MANTENIBLE_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ANALISIS_REFACTOR_REDUCCION_MANTENIBLE_2026_10_22.md>) | Refactor de tablas expertas: conteo y límites verificables | No declarado |
| [docs/sistema-experto/APORTE_INTERDISCIPLINARIO_GENERAL_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/APORTE_INTERDISCIPLINARIO_GENERAL_2026_10_20.md>) | Aporte interdisciplinario general para O.R.I.O.N. | No declarado |
| [docs/sistema-experto/AVANCE_CIERRE_FASES_6_A_8_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/AVANCE_CIERRE_FASES_6_A_8_2026_10_20.md>) | Cierre de fases 6–8: contrato vigente y avance verificable | No declarado |
| [docs/sistema-experto/CAPTURA_INSTRUMENTAL_FORMULARIO_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CAPTURA_INSTRUMENTAL_FORMULARIO_2026_10_19.md>) | Captura instrumental conectada — 19/10/2026 | No declarado |
| [docs/sistema-experto/CIERRE_OPERATIVO_1_A_8_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CIERRE_OPERATIVO_1_A_8_2026_10_18.md>) | Cierre operativo de los entregables 1–8 | No declarado |
| [docs/sistema-experto/CIERRE_REFACTOR_BLOQUES_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CIERRE_REFACTOR_BLOQUES_2026_10_22.md>) | Cierre de la refactorización de cuatro bloques expertos | No declarado |
| [docs/sistema-experto/CIERRE_TECNICO_FASE_3_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CIERRE_TECNICO_FASE_3_2026_10_19.md>) | O.R.I.O.N. — Cierre técnico de los 19 componentes instrumentales | No declarado |
| [docs/sistema-experto/CIERRE_TRAZABILIDAD_Y_REPRODUCCION_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CIERRE_TRAZABILIDAD_Y_REPRODUCCION_2026_10_19.md>) | Cierre de pendientes técnicos y fuentes verificadas | CURRENT |
| [docs/sistema-experto/COHORTE_INFERENCIA_30_Y_PILOTO_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/COHORTE_INFERENCIA_30_Y_PILOTO_2026_10_22.md>) | Cohorte de 30 casos e inventario del residente piloto | No declarado |
| [docs/sistema-experto/COHORTE_INVESTIGACION_SEEDER_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/COHORTE_INVESTIGACION_SEEDER_2026_10_22.md>) | Cohorte cognitiva de investigación: seeder y retroalimentación verificable | No declarado |
| [docs/sistema-experto/COHORTE_VISIBLE_APLICACION_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/COHORTE_VISIBLE_APLICACION_2026_10_22.md>) | Cohorte de entrenamiento disponible en la aplicación local | No declarado |
| [docs/sistema-experto/CONCILIACION_TRES_PERFILES_DCL_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONCILIACION_TRES_PERFILES_DCL_2026_10_22.md>) | Conciliación de tres perfiles DCL de prueba con fuentes originales | No declarado |
| [docs/sistema-experto/CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONSERVACION_INSTRUMENTAL_ANALISIS_2026_10_19.md>) | Conservación instrumental institucional — análisis de brecha | No declarado |
| [docs/sistema-experto/CONSERVACION_LECTURA_Y_REPLAY_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONSERVACION_LECTURA_Y_REPLAY_2026_10_22.md>) | Conservación institucional: lectura íntegra y replay separados | No declarado |
| [docs/sistema-experto/CONSOLIDACION_PUNTO_1_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONSOLIDACION_PUNTO_1_2026_10_19.md>) | Punto 1 — Consolidación del sistema experto | No declarado |
| [docs/sistema-experto/CONSULTA_OPERATIVA_SIN_SIMULACIONES_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONSULTA_OPERATIVA_SIN_SIMULACIONES_2026_10_17.md>) | Valoración operativa sin simulaciones | No declarado |
| [docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md>) | Contrato funcional de Petersen institucional: C01–C05 | No declarado |
| [docs/sistema-experto/CONTRATO_INFORME_COGNITIVO.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONTRATO_INFORME_COGNITIVO.md>) | Contrato de salida — Fase 4 | CURRENT |
| [docs/sistema-experto/CONTRATO_OBSERVACIONAL_VERSIONADO_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CONTRATO_OBSERVACIONAL_VERSIONADO_2026_10_18.md>) | Ejecución observacional versionada | No declarado |
| [docs/sistema-experto/CORRECCIONES_POR_PUNTOS_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/CORRECCIONES_POR_PUNTOS_2026_10_18.md>) | Correcciones por puntos — 18/10/2026 | No declarado |
| [docs/sistema-experto/DCL_PETERSEN_EXPERIMENTAL_GUIA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DCL_PETERSEN_EXPERIMENTAL_GUIA.md>) | O.R.I.O.N. — Clasificación Petersen experimental DCL | No declarado |
| [docs/sistema-experto/DECISION_MAXIMO_104_TABLAS_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DECISION_MAXIMO_104_TABLAS_2026_10_20.md>) | O.R.I.O.N. — máximo vigente de 104 tablas de negocio | No declarado |
| [docs/sistema-experto/DECISION_MAXIMO_110_TABLAS_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DECISION_MAXIMO_110_TABLAS_2026_10_21.md>) | Decisión de capacidad para la conservación de O.R.I.O.N. | No declarado |
| [docs/sistema-experto/DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md>) | Diccionario de adquisición instrumental O.R.I.O.N. | No declarado |
| [docs/sistema-experto/DICTAMEN_CIERRE_FASE_2_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DICTAMEN_CIERRE_FASE_2_2026_10_19.md>) | Dictamen de cierre de la fase 2: fuentes originales | No declarado |
| [docs/sistema-experto/DICTAMEN_CONSERVACION_INSTITUCIONAL_110_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DICTAMEN_CONSERVACION_INSTITUCIONAL_110_2026_10_21.md>) | O.R.I.O.N. — gate relacional de conservación institucional bajo 110 tablas | No declarado |
| [docs/sistema-experto/DICTAMEN_FACTIBILIDAD_CONSERVACION_104_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DICTAMEN_FACTIBILIDAD_CONSERVACION_104_2026_10_21.md>) | O.R.I.O.N. — dictamen de factibilidad de conservación completa bajo 104 tablas | No declarado |
| [docs/sistema-experto/DICTAMEN_LIMITE_100_TABLAS_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DICTAMEN_LIMITE_100_TABLAS_2026_10_20.md>) | O.R.I.O.N.: límite de 100 tablas de negocio | No declarado |
| [docs/sistema-experto/DICTAMEN_PROPUESTA_INSTRUMENTAL_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/DICTAMEN_PROPUESTA_INSTRUMENTAL_2026_10_20.md>) | Dictamen de la propuesta instrumental para O.R.I.O.N. | No declarado |
| [docs/sistema-experto/EJECUCION_COMPUESTA_INTEGRADA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/EJECUCION_COMPUESTA_INTEGRADA_2026_10_22.md>) | Ejecución compuesta integrada en la pantalla de resultados | No declarado |
| [docs/sistema-experto/ENSAYO_CINCO_RELACIONES_104_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENSAYO_CINCO_RELACIONES_104_2026_10_21.md>) | O.R.I.O.N. — ensayo de cinco relaciones bajo 104 tablas | No declarado |
| [docs/sistema-experto/ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENSAYO_CONSERVACION_REDUCIDA_FASE_6_2026_10_20.md>) | Ensayo de conservación institucional reducida | No declarado |
| [docs/sistema-experto/ENSAYO_CONSOLIDACION_RESULTADO_TRAZA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENSAYO_CONSOLIDACION_RESULTADO_TRAZA_2026_10_20.md>) | Ensayo de consolidación de resultado y traza por criterio | No declarado |
| [docs/sistema-experto/ENSAYO_NOMINAL_CONSERVACION_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENSAYO_NOMINAL_CONSERVACION_2026_10_22.md>) | Conservación institucional: escritor nominal, lector y replay | No declarado |
| [docs/sistema-experto/ENSAYO_UNION_VINCULOS_EVIDENCIA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENSAYO_UNION_VINCULOS_EVIDENCIA_2026_10_20.md>) | Ensayo de unión tipada de vínculos de evidencia | No declarado |
| [docs/sistema-experto/ENTREGA_INTEGRAL_ORION_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENTREGA_INTEGRAL_ORION_2026_10_20.md>) | O.R.I.O.N. — expediente de entrega integral | No declarado |
| [docs/sistema-experto/ENTREGA_REVISION_PROFESIONAL_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ENTREGA_REVISION_PROFESIONAL_2026_10_17.md>) | Entrega de revisión profesional — 17/10/2026 | No declarado |
| [docs/sistema-experto/ESCENARIOS_DESARROLLO_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ESCENARIOS_DESARROLLO_2026_10_17.md>) | Datos cargados y alcance comprobado | CURRENT |
| [docs/sistema-experto/ESPECIFICACION_FORMULARIOS_ADQUISICION_ORION_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ESPECIFICACION_FORMULARIOS_ADQUISICION_ORION_2026_10_21.md>) | O.R.I.O.N. — Especificación de formularios para adquirir evidencia profesional | No declarado |
| [docs/sistema-experto/EVIDENCIAS_COGNITIVAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/EVIDENCIAS_COGNITIVAS.md>) | Evidencias cognitivas: contrato de lectura | CURRENT |
| [docs/sistema-experto/EVIDENCIA_BIBLIOGRAFICA_Y_ACEPTACION_TECNICA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/EVIDENCIA_BIBLIOGRAFICA_Y_ACEPTACION_TECNICA_2026_10_22.md>) | Evidencia bibliográfica y aceptación técnica | No declarado |
| [docs/sistema-experto/FASE01_ACEPTACION_INTEGRAL_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE01_ACEPTACION_INTEGRAL_2026_10_18.md>) | Fase 01 — aceptación técnica y trazabilidad integral | No declarado |
| [docs/sistema-experto/FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md>) | Sistema experto — auditoría y evidencia de Fase 01 | No declarado |
| [docs/sistema-experto/FASE01_CATALOGO_FUENTES_Y_HECHOS_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE01_CATALOGO_FUENTES_Y_HECHOS_2026_10_18.md>) | Sistema experto — catálogo de fuentes y hechos del incremento | No declarado |
| [docs/sistema-experto/FASE01_CATALOGO_REGLAS_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE01_CATALOGO_REGLAS_2026_10_18.md>) | Fase 01 — catálogo de conocimiento ejecutable y conceptual | No declarado |
| [docs/sistema-experto/FASE01_COTEJO_DOCUMENTAL_Y_VT_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE01_COTEJO_DOCUMENTAL_Y_VT_2026_10_18.md>) | Fase 01 — cotejo documental completo y matriz VT | No declarado |
| [docs/sistema-experto/FASE01_MANUAL_Y_TRAZABILIDAD_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE01_MANUAL_Y_TRAZABILIDAD_2026_10_18.md>) | Sistema experto — laboratorio integrado y trazabilidad de Fase 01 | No declarado |
| [docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_0_1_CONSERVACION_PENDIENTE_2026_10_20.md>) | Fase 0.1: conservación compuesta pendiente de autorización estructural | No declarado |
| [docs/sistema-experto/FASE_0_1_ESTABILIZACION_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_0_1_ESTABILIZACION_2026_10_20.md>) | O.R.I.O.N. — Fase 0.1: estabilización | No declarado |
| [docs/sistema-experto/FASE_2_COMPROBACION_ADQUISICION_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_2_COMPROBACION_ADQUISICION_2026_10_19.md>) | Fase 2 — Comprobación de adquisición y métricas 60+ | No declarado |
| [docs/sistema-experto/FASE_2_FUENTES_ORIGINALES_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_2_FUENTES_ORIGINALES_2026_10_19.md>) | Fase 2 — Adquisición y procedencia de fuentes originales | No declarado |
| [docs/sistema-experto/FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_2_MATRIZ_MAESTRA_INFERENCIA_2026_10_20.md>) | O.R.I.O.N. — matriz maestra de conocimiento e inferencia | No declarado |
| [docs/sistema-experto/FASE_3_COMPONENTES_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_3_COMPONENTES_2026_10_19.md>) | Fase 3 — Objetivo activo: completar los 19 componentes | No declarado |
| [docs/sistema-experto/FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_4_CONTRATO_AD_INTEGRADO_2026_10_19.md>) | Fase 4 — Integración del contrato institucional A–D | No declarado |
| [docs/sistema-experto/FASE_4_FUENTES_Y_METRICAS_60_MAS_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_4_FUENTES_Y_METRICAS_60_MAS_2026_10_19.md>) | Fuentes profesionales y aplicación técnica en mayores de 60 | CURRENT |
| [docs/sistema-experto/FASE_4_PUENTE_NOMINAL_Y_SUCESION_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_4_PUENTE_NOMINAL_Y_SUCESION_2026_10_19.md>) | Fase 4: fuentes nominales y sucesión del conocimiento | No declarado |
| [docs/sistema-experto/FASE_4_RED_Y_REGLAS_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_4_RED_Y_REGLAS_2026_10_19.md>) | Fase 4: integración técnica de red y reglas | CURRENT |
| [docs/sistema-experto/FASE_4_REGISTRO_Y_VERIFICACION_UI_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_4_REGISTRO_Y_VERIFICACION_UI_2026_10_19.md>) | Fase 4 — Registro institucional y verificación en interfaz | No declarado |
| [docs/sistema-experto/FASE_5_CAPTURA_PETERSEN_Y_REPRODUCCION_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_5_CAPTURA_PETERSEN_Y_REPRODUCCION_2026_10_19.md>) | Petersen institucional: captura original, cálculo y reproducción instrumental | No declarado |
| [docs/sistema-experto/FASE_6_PREPARACION_COMPUESTA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FASE_6_PREPARACION_COMPUESTA_2026_10_20.md>) | Fase 6: preparación compuesta y reproducción | No declarado |
| [docs/sistema-experto/FORMALIZACION_FUNCIONAL_V0_1_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FORMALIZACION_FUNCIONAL_V0_1_2026_10_17.md>) | Formalización incremental del sistema experto funcional | No declarado |
| [docs/sistema-experto/FRONTEND_INTEGRAL_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/FRONTEND_INTEGRAL_2026_10_18.md>) | Sistema experto — implementación frontend y verificación | No declarado |
| [docs/sistema-experto/G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/G08_AUDITORIA_INSTRUMENTAL_EXPERIMENTAL.md>) | O.R.I.O.N. — G08: auditoría instrumental de los 15 subcomponentes (expediente experimental) | No declarado |
| [docs/sistema-experto/G08_CONTRATO_CAPTURA_Y_COMPARABILIDAD_MUESTRA.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/G08_CONTRATO_CAPTURA_Y_COMPARABILIDAD_MUESTRA.md>) | O.R.I.O.N. — G08: especificación de captura cruda y comparabilidad de muestra | No declarado |
| [docs/sistema-experto/G08_FICHAS_CANDIDATAS_PUBLICACIONES_Y_BRECHAS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/G08_FICHAS_CANDIDATAS_PUBLICACIONES_Y_BRECHAS.md>) | O.R.I.O.N. — G08: fichas bibliográficas, pertinencia y controles de aplicabilidad | No declarado |
| [docs/sistema-experto/G08_LOCALIZADORES_BOLIVIA_PUBLICACIONES_2015.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/G08_LOCALIZADORES_BOLIVIA_PUBLICACIONES_2015.md>) | O.R.I.O.N. — G08: localizadores exactos de anexos normativos para Bolivia | No declarado |
| [docs/sistema-experto/G09_EXPERIMENTAL_GUIA_EJECUCION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/G09_EXPERIMENTAL_GUIA_EJECUCION.md>) | O.R.I.O.N. — Guía de ejecución del prototipo experimental G08/G09 | No declarado |
| [docs/sistema-experto/GOBIERNO_PETERSEN_Y_QA_CAPTURA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/GOBIERNO_PETERSEN_Y_QA_CAPTURA_2026_10_20.md>) | Gobierno Petersen y verificación de captura original | No declarado |
| [docs/sistema-experto/INFORME_INSTRUMENTAL_Y_NORMALIZACION_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INFORME_INSTRUMENTAL_Y_NORMALIZACION_2026_10_18.md>) | Evaluación cognitiva y métodos instrumentales para RememberMind | No declarado |
| [docs/sistema-experto/INTEGRACION_CINCO_DOMINIOS_EXPERIMENTAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INTEGRACION_CINCO_DOMINIOS_EXPERIMENTAL.md>) | O.R.I.O.N. — Perfil experimental de cinco dominios y compuerta Petersen | No declarado |
| [docs/sistema-experto/INTEGRACION_F02_SOBRE_ESQUEMA_REAL_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INTEGRACION_F02_SOBRE_ESQUEMA_REAL_2026_10_21.md>) | O.R.I.O.N. — integración F02 sobre las migraciones institucionales | No declarado |
| [docs/sistema-experto/INTEGRACION_LABORATORIO_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INTEGRACION_LABORATORIO_2026_10_19.md>) | Integración versionada del laboratorio experto | No declarado |
| [docs/sistema-experto/INTERFAZ_MEDICA_RESULTADOS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INTERFAZ_MEDICA_RESULTADOS.md>) | Interfaz médica de resultados y resumen cognitivo | CURRENT |
| [docs/sistema-experto/INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md>) | O.R.I.O.N. — Investigación de los 19 componentes cognitivos | No declarado |
| [docs/sistema-experto/INVESTIGACION_RECURRENCIA_OBSERVACIONAL_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/INVESTIGACION_RECURRENCIA_OBSERVACIONAL_2026_10_18.md>) | Recurrencia observacional — investigación y recomendación técnica | No declarado |
| [docs/sistema-experto/LECTURA_ENFERMERIA_RESULTADO_PETERSEN_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/LECTURA_ENFERMERIA_RESULTADO_PETERSEN_2026_10_22.md>) | Lectura del resultado Petersen en Enfermería — 22/10/2026 | No declarado |
| [docs/sistema-experto/MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md>) | O.R.I.O.N. — Manual técnico investigado para población mayor de 60 años | No declarado |
| [docs/sistema-experto/MATRIZ_INTERPRETACION_OPERATIVA_PARA_REVISION.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/MATRIZ_INTERPRETACION_OPERATIVA_PARA_REVISION.md>) | Matriz de interpretación operativa para Psicología y Medicina | No declarado |
| [docs/sistema-experto/MEMBRESIAS_HISTORICAS_SQL_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/MEMBRESIAS_HISTORICAS_SQL_2026_10_22.md>) | Membresías históricas de Petersen: captura y conservación relacional | No declarado |
| [docs/sistema-experto/METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md>) | Métodos y campos de evaluación cognitiva — V2 | No declarado |
| [docs/sistema-experto/NORMALIZACIONES_REFERENCIA_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/NORMALIZACIONES_REFERENCIA_2026_10_19.md>) | O.R.I.O.N. — Normalizaciones de referencia implementadas | No declarado |
| [docs/sistema-experto/ORION_FUENTES_ORIGINALES_E_INVESTIGACION_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/ORION_FUENTES_ORIGINALES_E_INVESTIGACION_2026_10_19.md>) | O.R.I.O.N. — Fuentes originales y adquisición instrumental | No declarado |
| [docs/sistema-experto/PETERSEN_REVISION_Y_CONSERVACION_ESTADO_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PETERSEN_REVISION_Y_CONSERVACION_ESTADO_2026_10_21.md>) | Petersen y revisión posterior: estado de persistencia y decisión pendiente | No declarado |
| [docs/sistema-experto/PLAN_APOYO_INSTITUCIONAL_Y_VALIDACION_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PLAN_APOYO_INSTITUCIONAL_Y_VALIDACION_2026_10_18.md>) | Sistema experto — transición a apoyo institucional y validación | No declarado |
| [docs/sistema-experto/PLAN_CIERRE_INTEGRAL_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PLAN_CIERRE_INTEGRAL_2026_10_19.md>) | Plan de trabajo para terminar el sistema experto | No declarado |
| [docs/sistema-experto/PROMPT_MAESTRO_01_CHECKLIST_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROMPT_MAESTRO_01_CHECKLIST_2026_10_18.md>) | Sistema experto — Fase 01: memoria de alcance, entregables y checklist | No declarado |
| [docs/sistema-experto/PROMPT_MAESTRO_01_FUENTE_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROMPT_MAESTRO_01_FUENTE_2026_10_18.md>) | PROMPT MAESTRO 01 — IMPLEMENTACIÓN INTEGRAL DEL SISTEMA EXPERTO | No declarado |
| [docs/sistema-experto/PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_CAPTURA_PETERSEN_ORIGINAL_2026_10_19.md>) | Captura original de Petersen: extensión concreta propuesta | No declarado |
| [docs/sistema-experto/PROPUESTA_CONSERVACION_COMPUESTA_FASE_6_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_CONSERVACION_COMPUESTA_FASE_6_2026_10_20.md>) | Propuesta de conservación compuesta — fase 6 | No declarado |
| [docs/sistema-experto/PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_CONSERVACION_OBSERVACIONAL_V1.md>) | Conservación observacional V1 — Decisión aprobada | No declarado |
| [docs/sistema-experto/PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md>) | Decisión de contenido institucional para la fase 4 | No declarado |
| [docs/sistema-experto/PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_FUENTES_ORIGINALES_FASE_2_2026_10_19.md>) | Fase 2 — Procedencia de aplicaciones y transcripción histórica | No declarado |
| [docs/sistema-experto/PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_GOBIERNO_REGLAS_2026_10_18.md>) | Gobierno de reglas: diccionario mínimo | No declarado |
| [docs/sistema-experto/PROPUESTA_LECTURA_IADL_PETERSEN_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_LECTURA_IADL_PETERSEN_2026_10_19.md>) | Lectura IADL para preparar Petersen | No declarado |
| [docs/sistema-experto/PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md>) | Petersen institucional: contrato de acreditación propuesto | No declarado |
| [docs/sistema-experto/PROPUESTA_REDUCCION_CUATRO_TABLAS_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_REDUCCION_CUATRO_TABLAS_2026_10_20.md>) | Propuesta concreta: consolidar cuatro tablas expertas | No declarado |
| [docs/sistema-experto/PROPUESTA_VALORACION_PROFESIONAL_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROPUESTA_VALORACION_PROFESIONAL_2026_10_17.md>) | Valoración del sistema experto y revisión profesional | No declarado |
| [docs/sistema-experto/PROTOCOLOS_CAPTURA_COGNITIVA_V1_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PROTOCOLOS_CAPTURA_COGNITIVA_V1_2026_10_18.md>) | Protocolos y fichas de captura cognitiva — V1 | No declarado |
| [docs/sistema-experto/PUBLICACION_INTEGRADA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PUBLICACION_INTEGRADA_2026_10_22.md>) | Publicación integrada del sistema experto y cohorte local | No declarado |
| [docs/sistema-experto/PUENTE_REFERENCIAS_G08_A_P_DCL_EXPERIMENTAL.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PUENTE_REFERENCIAS_G08_A_P_DCL_EXPERIMENTAL.md>) | O.R.I.O.N. — Contrato y ejecución del puente G08 → A/P/U/I/X → Petersen | No declarado |
| [docs/sistema-experto/PUNTO_1_EVIDENCIA_COGNITIVA_ORIGINAL_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/PUNTO_1_EVIDENCIA_COGNITIVA_ORIGINAL_2026_10_21.md>) | Punto 1 — Evidencia cognitiva original | No declarado |
| [docs/sistema-experto/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/README.md>) | Consulta cognitiva y sistema experto | No declarado |
| [docs/sistema-experto/RECUPERACION_SQL_COMPLETA_REFERENCIA_2026_10_22.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/RECUPERACION_SQL_COMPLETA_REFERENCIA_2026_10_22.md>) | Recuperación SQL completa: resultado comprobado y límite institucional | No declarado |
| [docs/sistema-experto/REDISENO_INFORME_NEUROCOGNITIVO_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/REDISENO_INFORME_NEUROCOGNITIVO_2026_10_17.md>) | Informe neurocognitivo de O.R.I.O.N. | No declarado |
| [docs/sistema-experto/REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md>) | O.R.I.O.N. — Cinco referencias geriátricas adicionales | No declarado |
| [docs/sistema-experto/RESOLUCION_RUTAS_CONSERVACION_110_2026_10_21.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/RESOLUCION_RUTAS_CONSERVACION_110_2026_10_21.md>) | O.R.I.O.N. — resolución verificable de rutas de conservación | No declarado |
| [docs/sistema-experto/REUTILIZACION_Y_REDUCCION_EXPERTA_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/REUTILIZACION_Y_REDUCCION_EXPERTA_2026_10_20.md>) | Reutilización y reducción del sistema experto | No declarado |
| [docs/sistema-experto/REVISION_EXPEDIENTE_G09_RECIBIDO_2026_10_20.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/REVISION_EXPEDIENTE_G09_RECIBIDO_2026_10_20.md>) | Revisión del expediente G09 recibido | No declarado |
| [docs/sistema-experto/RFC_CONSERVACION_PERFIL_EXPERIMENTAL_2026_10_18.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/RFC_CONSERVACION_PERFIL_EXPERIMENTAL_2026_10_18.md>) | Sistema experto — RFC de conservación del perfil experimental | No declarado |
| [docs/sistema-experto/SIMULACION_LOCAL_2026_10_17.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/docs/sistema-experto/SIMULACION_LOCAL_2026_10_17.md>) | Integración local de O.R.I.O.N. — 17/10/2026 | No declarado |

### motor-experto

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [motor-experto/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/motor-experto/README.md>) | Motor experto tecnico | No declarado |

### prototipos

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [prototipos/orion-conservacion-nominal/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/prototipos/orion-conservacion-nominal/README.md>) | Ensayo nominal de entradas institucionales | No declarado |

### Raíz

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/AGENTS.md>) | AGENTS.md — RememberMind | No declarado |
| [CODEX_SETUP.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/CODEX_SETUP.md>) | Codex en RememberMind | CURRENT |
| [README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/README.md>) | RememberMind | CURRENT |

### resources

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [resources/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/AGENTS.md>) | resources/AGENTS.md — RememberMind UI/UX | No declarado |
| [resources/frontend/styles/design-system/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/styles/design-system/README.md>) | RememberMind Design System | No declarado |
| [resources/frontend/styles/design-system/charts/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/frontend/styles/design-system/charts/README.md>) | Chart Design System de RememberMind | No declarado |
| [resources/markdown/policy.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/markdown/policy.md>) | Privacy Policy | No declarado |
| [resources/markdown/terms.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/resources/markdown/terms.md>) | Terms of Service | No declarado |

### tests

| Archivo | Título encontrado | Estado declarado |
|---|---|---|
| [tests/AGENTS.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/AGENTS.md>) | tests/AGENTS.md — RememberMind testing | No declarado |
| [tests/Unit/SistemaExperto/Domain/Services/Dcl/Fixtures/README.md](<C:/Users/CARLAENCINAS/Desktop/RememberMind/tests/Unit/SistemaExperto/Domain/Services/Dcl/Fixtures/README.md>) | Oráculo sintético 03A | No declarado |
