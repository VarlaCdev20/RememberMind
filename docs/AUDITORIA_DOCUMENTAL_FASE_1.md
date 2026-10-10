---
title: "Auditoría documental de Fase 1"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: ["README.md","ESTADO_ACTUAL.md","DECISIONES_PENDIENTES.md"]
related_modules: []
---

# DOCUMENT AUDIT

## Alcance y método

91 documentos existentes inventariados y clasificados: 80 en docs, tres Markdown raíz, cuatro AGENTS internos, dos referencias de Design System y dos plantillas legales. Inspección de títulos/estado/índices y lectura dirigida de secciones importantes; no se afirma lectura clínica exhaustiva de cada párrafo del diccionario o de todos los históricos. Skills consultadas como contexto de autoridad/routing, fuera de este conteo.

Localización independiente con caveman-explore y revisión estática de BDD y dominio. Read/Glob/Grep/Haiku de los metadatos de ese skill no están disponibles en este host; lectura/búsqueda de archivos fue el fallback. Caveman Cloud sin MCP/CLI: costos, traces y telemetría NO VERIFICADOS. caveman-evidence-review no se presenta como revisión de repositorio ni como resultado Cloud; el contraste documental siguiente usa evidencia local explícita.

Base: commit `8e9e20325519c5da5a6b25f6fb26568cad78efe1` y árbol de trabajo con cambios previos de producto y guía UX. No se ejecutaron suites PHP/JS, DB/migraciones o build, ni se verificó producción.

## Evidencia y estados

Estado encontrado es clasificación de auditoría, no aprobación nueva. CURRENT/APPROVED/PROPOSED/DRAFT/DEPRECATED/HISTORICAL son metadatos; CONFLICTING/UNKNOWN son hallazgos que pueden afectar solo parte del documento.

Afirmaciones se etiquetan: CONFIRMADO POR DOCUMENTACIÓN VIGENTE; CONFIRMADO POR CÓDIGO; CONFIRMADO POR TESTS (definición localizada, ejecución indicada por separado); HISTÓRICO; PROPUESTO; CONFLICTIVO; NO VERIFICADO.

| Documento | Propósito/área | Estado encontrado | Última relevancia | Conflicto/duplicación | Decisión recomendada |
|---|---|---|---|---|---|
| `docs/architecture-audit/00_RESUMEN_EJECUTIVO.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/01_AS_IS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/02_REQUERIMIENTOS_CLIENTE_SIMULADO.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/03_GAP_ANALYSIS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/04_ARQUITECTURA_TO_BE.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/05_CAPAS_Y_RESPONSABILIDADES.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/06_MAPA_MODULOS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/07_ROLES_PERMISOS_PROFESIONES.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/08_MAPA_VISTAS_DASHBOARDS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/09_BD_AS_IS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/10_BD_TO_BE.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/11_ERD_TO_BE.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/12_FRONTEND_AS_IS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/13_FRONTEND_VUE_TO_BE.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/14_PLAN_MIGRACION_FRONTEND.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/15_PLAN_REFACTORIZACION_BACKEND.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/16_TESTING_STRATEGY.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/17_SEGURIDAD.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/18_SISTEMA_EXPERTO_INTEGRACION.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/19_ROADMAP.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/20_DECISIONES_ARQUITECTONICAS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/21_PAQUETE_REVISION.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/RESUMEN_ARQUITECTURA.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/architecture-audit/WALKTHROUGH_ALERTAS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/arquitectura/DASHBOARDS_POR_ROL.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/arquitectura/PERMISOS_TEMPORALES_SUPERADMIN.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/arquitectura/README.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/arquitectura/REMEMBERMIND_ARQUITECTURA_FRONTEND.md` | Norma anterior + arquitectura objetivo | CONFLICTING | Revisión dirigida 2026-10-06 | Features/Pages y visual divergentes | DEPRECATED como norma; fuentes sustitutas |
| `docs/arquitectura/REMEMBERMIND_BASELINE_TECNICO.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/auditoria.md` | Inventario/auditoría de V1 o transición V2.0 | HISTORICAL | Migración anterior, no estado actual | Conteos/nombres anteriores | Cabecera histórica y enlaces actuales |
| `docs/auditoria_roles_permisos.md` | Inventario/auditoría de V1 o transición V2.0 | HISTORICAL | Migración anterior, no estado actual | Conteos/nombres anteriores | Cabecera histórica y enlaces actuales |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_ALIMENTACION.md` | Auditoría localizada con partes propuestas | CURRENT | Revisión dirigida 2026-10-06 | Propuestas no autorizan reglas o columnas | Aviso de alcance/estado parcial; preservar |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_DOLOR.md` | Auditoría localizada con partes propuestas | CURRENT | Revisión dirigida 2026-10-06 | Propuestas no autorizan reglas o columnas | Aviso de alcance/estado parcial; preservar |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_ELIMINACION.md` | Auditoría localizada con partes propuestas | CURRENT | Revisión dirigida 2026-10-06 | Propuestas no autorizan reglas o columnas | Aviso de alcance/estado parcial; preservar |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_MEDICACION_PROGRAMADA.md` | Auditoría localizada con partes propuestas | CURRENT | Revisión dirigida 2026-10-06 | Propuestas no autorizan reglas o columnas | Aviso de alcance/estado parcial; preservar |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_MOVILIDAD.md` | Auditoría localizada con partes propuestas | CURRENT | Revisión dirigida 2026-10-06 | Propuestas no autorizan reglas o columnas | Aviso de alcance/estado parcial; preservar |
| `docs/base-de-datos/AUDITORIA_FORMULARIO_SIGNOS_VITALES.md` | Auditoría localizada con partes propuestas | CURRENT | Revisión dirigida 2026-10-06 | Objetivo propuesto anterior superado por V2.2 | Aviso de alcance/estado parcial; preservar |
| `docs/base-de-datos/DECISION_ARQUITECTURA_BDD_V2_1.md` | Decisión explícita acotada | APPROVED | Revisión dirigida 2026-10-06 | No anula núcleo fuera de su alcance | Conservar contenido aprobado; metadata y enlace |
| `docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md` | Decisión explícita acotada | APPROVED | Revisión dirigida 2026-10-06 | No anula núcleo fuera de su alcance | Conservar contenido aprobado; metadata y enlace |
| `docs/base-de-datos/DECISION_UNIDAD_TALLA_CM.md` | Decisión explícita acotada | APPROVED | Revisión dirigida 2026-10-06 | No anula núcleo fuera de su alcance | Conservar contenido aprobado; metadata y enlace |
| `docs/base-de-datos/INVENTARIO_MIGRACION_APLICACION.md` | Inventario/auditoría de V1 o transición V2.0 | HISTORICAL | Migración anterior, no estado actual | Conteos/nombres anteriores | Cabecera histórica y enlaces actuales |
| `docs/base-de-datos/README.md` | Navegación BDD y evolución | CONFLICTING | Revisión dirigida 2026-10-06 | 4 migraciones inexistentes; prioridad no distingue extensión | Enlazar rutas presentes y secuencia aprobada |
| `docs/base-de-datos/REMEMBERMIND_BDD_69_TABLAS.md` | Inventario/auditoría de V1 o transición V2.0 | HISTORICAL | Migración anterior, no estado actual | Conteos/nombres anteriores | Cabecera histórica y enlaces actuales |
| `docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md` | Diccionario físico del núcleo V2.1 | CURRENT | Revisión dirigida 2026-10-06 | Resumen de autoría y cardinalidad aplicada no equivalen a garantía SQL | Preservar especificación; avisar conflictos localizados |
| `docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md` | Contrato rector congelado | CURRENT | Revisión dirigida 2026-10-06 | Estados, resultado/estado y trazabilidad puntuales | Conservar reglas; aviso y decisiones/deuda separadas |
| `docs/base-de-datos/RESULTADO_MIGRACION_APLICACION_V2.md` | Inventario/auditoría de V1 o transición V2.0 | HISTORICAL | Migración anterior, no estado actual | Conteos/nombres anteriores | Cabecera histórica y enlaces actuales |
| `docs/base-de-datos/SEEDERS_LOCALES.md` | Procedimiento de carga sintética local | CURRENT | Revisión dirigida 2026-10-06 | Referencias al núcleo, no prueba de extensión/ejecución | Preservar alcance; no ejecutar |
| `docs/frontend/AUDITORIA_SKILLS_UX_UI.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/frontend/CONTRATO_VISUAL_UX_UI.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `docs/frontend/FASE_11_INTEGRACION_VISUAL_DASHBOARD.md` | Plan/resultado fechado de UX | HISTORICAL | Revisión dirigida 2026-10-06 | No prueba estado actual de pantallas | Marcar snapshot; conservar |
| `docs/frontend/FASE_9_DISTRIBUCION_Y_AGENDA.md` | Plan/resultado fechado de UX | HISTORICAL | Revisión dirigida 2026-10-06 | No prueba estado actual de pantallas | Marcar snapshot; conservar |
| `docs/frontend/FASE_9B_TAREAS_UBICACION_NOTAS.md` | Plan/resultado fechado de UX | HISTORICAL | Revisión dirigida 2026-10-06 | No prueba estado actual de pantallas | Marcar snapshot; conservar |
| `docs/frontend/PENDIENTE_BADGES_ESTADO_RESIDENTE.md` | Restricción mientras no hay criterio aprobado | CURRENT | Revisión dirigida 2026-10-06 | Regla clínica aún no definida | Conservar; DEC-OPEN-003 |
| `docs/frontend/PLAN_UNIFICACION_VISUAL.md` | Plan/resultado fechado de UX | HISTORICAL | Revisión dirigida 2026-10-06 | No prueba estado actual de pantallas | Marcar snapshot; conservar |
| `docs/frontend/STACK_SKILLS_UX_UI.md` | Selector UX con tarea de sistema insertada | CONFLICTING | Revisión dirigida 2026-10-06 | Solicitud mezclada con guía | Extraer solicitud íntegra a histórico; restituir guía |
| `docs/produccion/DESPLIEGUE_SEGURO.md` | Procedimiento de liberación y reversión | CURRENT | Revisión dirigida 2026-10-06 | Inventario anterior; despliegue no verificado | Actualizar alcance BDD; separar procedimiento/evidencia |
| `docs/README.md` | Portal documental | CURRENT | Revisión dirigida 2026-10-06 | Faltan documentos maestros | Reorganizar entrada y fuentes |
| `docs/refactorizacion-total/00_RESUMEN.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/01_INVENTARIO_FUNCIONAL.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/02_BD_AS_IS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/03_BD_TO_BE.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/04_MAPA_MIGRACION_BD.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/05_MODULOS_TO_BE.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/06_CRUDS_Y_VISTAS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/07_ROLES_PERMISOS.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/08_ESTRUCTURA_CODIGO.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/09_PLAN_REFACTORIZACION.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/10_DECISIONES_PENDIENTES.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/12_REORGANIZACION_CODIGO.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/13_AUDITORIA_VENTANAS_FORMULARIOS_Y_ACCIONES.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/refactorizacion-total/14_UNIFICACION_FRONTEND.md` | Análisis/propuesta de una etapa anterior | HISTORICAL | Snapshot anterior; sin vigencia operativa | No gobierna arquitectura o modelo actuales | Conservar ruta y contexto; README histórico |
| `docs/sistema/AUDITORIA_SKILLS_SISTEMA.md` | Infraestructura de skills o evidencia de su entrega | CURRENT | Revisión dirigida 2026-10-06 | No certifica módulos | Conservar selector; evidencia fechada sin elevar a producto |
| `docs/sistema/MAPA_DOMINIO_FUNCIONAL.md` | Infraestructura de skills o evidencia de su entrega | CURRENT | Revisión dirigida 2026-10-06 | No certifica módulos | Conservar selector; evidencia fechada sin elevar a producto |
| `docs/sistema/STACK_SKILLS_SISTEMA.md` | Infraestructura de skills o evidencia de su entrega | CURRENT | Revisión dirigida 2026-10-06 | No certifica módulos | Conservar selector; evidencia fechada sin elevar a producto |
| `docs/sistema/VERIFICACION_SKILLS_SISTEMA.md` | Infraestructura de skills o evidencia de su entrega | CURRENT | Revisión dirigida 2026-10-06 | No certifica módulos | Conservar selector; evidencia fechada sin elevar a producto |
| `docs/sistema-experto/ARBOLES_DECISION_AREA_MEDICINA.md` | Diseño experto futuro | PROPOSED | Diseño futuro, no implementación | Método/reglas no aprobados | Metadata PROPOSED; no ejecutar ni elevar a norma |
| `docs/sistema-experto/ARQUITECTURA_COMPLETA_SISTEMA_EXPERTO.md` | Diseño experto futuro | PROPOSED | Diseño futuro, no implementación | Método/reglas no aprobados; ubicación propuesta divergente | Metadata PROPOSED; no ejecutar ni elevar a norma |
| `docs/sistema-experto/DISENO_ARBOLES_DECISION_TODAS_AREAS.md` | Diseño experto futuro | PROPOSED | Diseño futuro, no implementación | Método/reglas no aprobados | Metadata PROPOSED; no ejecutar ni elevar a norma |
| `docs/sistema-experto/README.md` | Estado y límites de propuestas expertas | CURRENT | Revisión dirigida 2026-10-06 | No demuestra validación clínica | Conservar; enlazar desde fuente contextual |
| `README.md` | Entrada del proyecto e instalación | CONFLICTING | Revisión dirigida 2026-10-06 | 70 como inventario vigente; RRHH Admin; suite 100%; Node genérico | Actualizar solo afirmaciones/enlaces |
| `CODEX_SETUP.md` | Guía del pack original de agentes | DEPRECATED | Revisión dirigida 2026-10-06 | Cinco skills y baseline anterior; script ofrecido ausente del árbol | Actualizar inventario por enlaces y retirar instrucción del script inexistente |
| `AGENTS.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `app/AGENTS.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `database/AGENTS.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `resources/AGENTS.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `tests/AGENTS.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `resources/frontend/styles/design-system/README.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `resources/frontend/styles/design-system/charts/README.md` | Contrato o evidencia especializada | CURRENT | Revisión dirigida 2026-10-06 | Sin conflicto localizado en su alcance | Conservar; enlazar desde fuente contextual |
| `resources/markdown/terms.md` | Placeholder de texto legal de Jetstream | DRAFT | No verificado como documento institucional | No hay contenido institucional | No publicar como obligación legal aprobada |
| `resources/markdown/policy.md` | Placeholder de texto legal de Jetstream | DRAFT | No verificado como documento institucional | No hay contenido institucional | No publicar como obligación legal aprobada |

Los duplicados conceptuales se separan por autoridad: diccionario físico y baseline rector son complementarios; snapshots no sustituyen contratos; estado actual no duplica roadmap; skill no gobierna por encima de fuente de dominio. Historia permanece en sus rutas para conservar referencias.

## Afirmaciones relevantes contrastadas

| Afirmación | Evidencia | Clasificación | Límite |
|---|---|---|---|
| RememberMind es residencial geriátrico e interdisciplinario | AGENTS Mission; baseline Ámbito | CONFIRMADO POR DOCUMENTACIÓN VIGENTE | No demuestra entrega completa |
| V2.0 de 69 → núcleo V2.1 de 70 → extensión V2.2 de 71 actuales | Baseline apertura, decisiones V2.1/V2.2, AGENTS/database | CONFIRMADO POR DOCUMENTACIÓN VIGENTE | Extensión no reemplaza las 70 tablas ni las reglas no modificadas |
| 71 creaciones operativas únicas están declaradas | 69 migraciones individuales, normalización y objetivos | CONFIRMADO POR CÓDIGO | Conteo estático, no introspección física |
| Test exige 71 y excluye tablas técnicas | BddOperativaV2Test método de inventario | CONFIRMADO POR TESTS, NO EJECUTADOS | No prueba resultado actual/DB instalada |
| Aprobación separada de admisión; transacción y locks presentes | FormalizarAdmision y tests BDD/preadmisión | DOCUMENTACIÓN + CÓDIGO + TESTS DEFINIDOS | Carreras/rollback PostgreSQL no ejecutados |
| RRHH pertenece a Gerente, no al Administrador por rol | AGENTS y baseline roles | CONFIRMADO POR DOCUMENTACIÓN VIGENTE | Se corrige README; no permisos |
| Experto cognitivo entregado | Diseños PROPOSED; no módulo cognitivo localizado | PROPUESTO / NO VERIFICADO como producto | Signos vitales e instrumentos no acreditan motor experto |
| Suite global aprobada al 100% | README antiguo | NO VERIFICADO | Se retira afirmación; no se inventa porcentaje |
| Despliegue, backups/restauración y QA exhaustivo | Guías/checklists y archivos presentes | NO VERIFICADO en esta fase | Procedimiento o vista no es prueba de operación |
| Históricos Vue, otras tablas/rutas/algoritmos | Carpetas de análisis anteriores | HISTÓRICO | No fuentes vigentes |

## Conflictos localizados y preservación

- Estados de preadmisión/residente y catálogos: [DECISIONES_PENDIENTES](DECISIONES_PENDIENTES.md).
- Resultado frente a estado de administración, atribución de garantía SQL/test antiguo, rutas obsoletas: [DEUDA_TECNICA](DEUDA_TECNICA.md).
- Arquitectura frontend antigua: normativa sustituida por arquitectura canónica, resources/AGENTS y contrato visual; contenido conservado como referencia de transición.
- Solicitud de sistema en guía UX: se separa íntegra a histórico, sin destruir contenido previo. No aprueba protocolo clínico.
- Cuatro enlaces Markdown rotos detectados antes de editar, todos en índice BDD: migraciones agrupadas inexistentes. Se reemplazan por rutas presentes.
- Búsqueda de FINAL/FINAL2/FINAL-DEFINITIVO/ACTUALIZADO2 no identificó documentos maestros con esos nombres. TODO/FIXME encontrados en instrucciones/checklists no se convierten automáticamente en bugs.

Una futura migración a docs/historico necesita inventario de enlaces entrantes y actualización atómica. Esta fase solo añade avisos a carpetas históricas y archiva una solicitud mezclada en la guía; no mueve masivamente documentos.
