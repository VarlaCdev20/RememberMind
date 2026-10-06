# Auditoría de skills de sistema de RememberMind

Fecha de corte: 2026-10-06. Alcance: instrucciones, referencias y documentación; sin modificaciones de producto. Fuente de alcance: tarea maestra adjunta de la propietaria. Revisión de dominio/seguridad, BDD/fuentes y experto/pruebas por tres agentes de solo lectura; plan consolidado e implementación por un responsable.

## Ubicación e inventario inicial

| Carpeta | Contenido encontrado |
|---|---|
| `.agents/skills/` del repositorio | 43 skills locales antes de esta tarea; constituyen el alcance auditado de integración institucional. |
| `C:/Users/CARLAENCINAS/.codex/skills/` | 11 skills, incluyendo `.system`: zoom-out, ui-ux-pro-max, design-engineer, caveman, skill-installer, impeccable, skill-creator, review-agent, humanizer, openai-docs, imagegen. |
| `C:/Users/CARLAENCINAS/.agents/skills/` | find-skills y humanizer. |
| `C:/Users/CARLAENCINAS/.codex/plugins/cache/` | Skills de plugins por proveedor/versión; se descubren desde el catálogo de la sesión. No son fuentes del dominio ni se modifican aquí. |
| `docs/` | Documentación del proyecto: índices, arquitectura, BDD, UX, producción, propuestas expertas e historia. AGENTS por ámbito complementan sus contratos. |

Las copias globales de caveman, humanizer y ui-ux-pro-max se solapan con locales. Se conserva su instalación; las locales especializadas y los contratos de RememberMind gobiernan tareas institucionales. Esta auditoría evalúa la responsabilidad de sistema de las skills locales; la calidad visual está documentada en [la auditoría UX](../frontend/AUDITORIA_SKILLS_UX_UI.md).

## Matriz anterior a la creación

| SKILL | RESPONSABILIDAD ACTUAL | SOLAPAMIENTO | CALIDAD | DECISIÓN |
|---|---|---|---|---|
| remembermind-database-audit | Auditar esquema/persistencia | Integridad durante implementación | Útil; descripción fija baseline anterior | EVOLVE: gate independiente, fuente dinámica |
| remembermind-security-review | Revisión de autorización | Guardian de diseño; actualmente permite corregir en gate | Útil; separar revisión y ejecución | EVOLVE: gate de lectura |
| remembermind-module-delivery | Entrega completa de módulo | module-architect propuesto | Buena base; faltan contratos y selección condicional | EVOLVE + MERGE: arquitectura de módulo aquí |
| remembermind-release-check | Verificación y commit local | Testing y revisión especializada | Buena base; coordinar evidencia sin repetir todos los gates | EVOLVE |
| investigate-first | Diagnóstico por hipótesis | Source-of-truth consulta fuentes | Claro para fallos ambiguos | KEEP: diagnóstico cuando causa incierta |
| surgical-patch | Corrección localizada | Entrega de módulo | Acotado | KEEP: cambio pequeño con regresión |
| safe-refactor | Reorganización sin cambiar conducta | Domain-architect | Acotado | KEEP: refactor con prueba de preservación |
| verify-and-stop | Validación sin expansión | Release coordina aceptación | Acotado | KEEP: comprobar resultado existente |
| migration | Transición reversible | Database-integrity | Genérico; compatibilidad debe ser temporal | AUXILIARY: sujeto a gobernanza BDD y prohibición V1 permanente |
| lean-build | Control de alcance | module-delivery | Referencias Native Core/Core safety sin contrato local | AUXILIARY: no autoridad institucional |
| remembermind-ui-review | Seleccionar etapas UX | Coordinación transversal | Específico; selección proporcional | KEEP: único coordinador UX |
| remembermind-ux-flow-architect | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-reference-fidelity | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-warm-geriatric-art-direction | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-design-system | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-clinical-form-ux | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-data-visualization-ux | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-motion-microinteractions | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-responsive-accessibility | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-ux-writing | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| remembermind-visual-functional-qa | Especialidad UX del nombre | Sistema entrega contrato, UX lo representa | Especializada; auditada en stack UX | KEEP: sin copiarla en skills de sistema |
| design | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| design-system | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| ui-styling | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| ui-ux-pro-max | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| brand | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| banner-design | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| slides | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| humanizer | Diseño, comunicación o estilo auxiliar | Stack UX especializado | Adaptada en auditoría UX previa | AUXILIARY: conserva alcance allí definido |
| cavecrew | Delegación a presets | Protocolo de agentes | Presets/herramientas de otro entorno; disponibilidad no acreditada | AUXILIARY: no activar por defecto |
| caveman-explore | Localización de código | Investigación/fuentes | Metadatos de otro host; no criterio de dominio | AUXILIARY |
| caveman-review | Formato breve de hallazgos | Gates de seguridad/BDD | Formato, sin evaluación institucional completa | AUXILIARY |
| caveman | Estilo, memoria o información de uso | Sin responsabilidad de sistema | Depende de invocación/host; no prueba calidad ni ahorro | AUXILIARY |
| caveman-commit | Estilo, memoria o información de uso | Sin responsabilidad de sistema | Depende de invocación/host; no prueba calidad ni ahorro | AUXILIARY |
| caveman-compress | Estilo, memoria o información de uso | Sin responsabilidad de sistema | Depende de invocación/host; no prueba calidad ni ahorro | AUXILIARY |
| caveman-help | Estilo, memoria o información de uso | Sin responsabilidad de sistema | Depende de invocación/host; no prueba calidad ni ahorro | AUXILIARY |
| caveman-stats | Estilo, memoria o información de uso | Sin responsabilidad de sistema | Depende de invocación/host; no prueba calidad ni ahorro | AUXILIARY |
| caveman-discover | Observabilidad/experimentos de Caveman Cloud | No equivale a auditoría de RememberMind | Exige disponibilidad y autorización propia | AUXILIARY: no enviar datos clínicos por inferencia |
| caveman-evidence-review | Observabilidad/experimentos de Caveman Cloud | No equivale a auditoría de RememberMind | Exige disponibilidad y autorización propia | AUXILIARY: no enviar datos clínicos por inferencia |
| caveman-learn | Observabilidad/experimentos de Caveman Cloud | No equivale a auditoría de RememberMind | Exige disponibilidad y autorización propia | AUXILIARY: no enviar datos clínicos por inferencia |
| caveman-manage | Observabilidad/experimentos de Caveman Cloud | No equivale a auditoría de RememberMind | Exige disponibilidad y autorización propia | AUXILIARY: no enviar datos clínicos por inferencia |
| caveman-optimize | Observabilidad/experimentos de Caveman Cloud | No equivale a auditoría de RememberMind | Exige disponibilidad y autorización propia | AUXILIARY: no enviar datos clínicos por inferencia |
| caveman-setup | Observabilidad/experimentos de Caveman Cloud | No equivale a auditoría de RememberMind | Exige disponibilidad y autorización propia | AUXILIARY: no enviar datos clínicos por inferencia |

No se elimina ninguna skill. KEEP conserva su función; EVOLVE ajusta límites; MERGE absorbe una responsabilidad solicitada; AUXILIARY conserva apoyo condicional; DEPRECATE LATER no se aplica sin una decisión y transición documentadas.

## Plan consolidado

1. Resolver autoridad, versiones, estado documental y conflictos con source-of-truth.
2. Crear guardrails, diseño de dominio/workflows, guardian de autorización e integridad durante desarrollo.
3. Separar continuidad, alertas y observabilidad para no confundir dato clínico, evento operativo, notificación y audit log.
4. Evolucionar module-delivery para cubrir module-architect. No crear una segunda entrada competidora.
5. Crear testing/performance y dos especialidades expertas; explicación dentro de expert-system-engineering.
6. Evolucionar database-audit/security-review como revisiones independientes; release coordina evidencias y commit.
7. Integrar selector y documentación. Validar formato, enlaces, selección por escenarios y cambios versionados.

Resultado previsto: 14 skills nuevas + 4 evolucionadas, cubriendo las 15 responsabilidades solicitadas y manteniendo gates existentes. Las skills de sistema consultan baseline y extensiones aprobadas; no congelan un número eterno de tablas.

## Hallazgos de fuentes y producto

Esta tarea los registra; la autorización actual excluye corregir código funcional.

| Hallazgo | Evidencia | Tratamiento |
|---|---|---|
| Auditor BDD fijaba el inventario del núcleo anterior | Frontmatter original de database-audit; decisión V2.2 aprobada | EVOLVE: resolver baseline + decisiones vigentes. El nombre del diccionario no cambia. |
| Índices documentales presentan auditorías V1 como vigentes | [auditoria.md](../auditoria.md), [auditoria_roles_permisos.md](../auditoria_roles_permisos.md) | Clasificar HISTÓRICA y corregir el índice raíz; conservar evidencia histórica. |
| Estado físico/documentado de admisión difiere | Baseline §§6 y 29: ACTIVO; [FormalizarAdmision](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php): ADMITIDO/ADMITIDA; AGENTS: condición institucional ADMITIDO | SOURCE CONFLICT localizado. La condición institucional tiene instrucción actual; no deducir todos los valores del catálogo de esa condición. Resolver contrato específico antes de cambiar estados. |
| Lista de migraciones agrupadas obsoleta | [índice BDD](../base-de-datos/README.md), migraciones actuales desagregadas | Deuda de índice; descubrir archivos existentes, no copiar rutas ausentes. |
| Bloqueo de admisión marcado pendiente pese a Action con lockForUpdate | Baseline §§13 y 26, FormalizarAdmision | Distinguir deuda documental de prueba de concurrencia. No afirmar PostgreSQL verificado. |
| Integridad instrumento/pregunta atribuida al hardening | Baseline §§22 y 30; RespuestaInstrumento/InstrumentoController; BddOperativaV2Test | Evidencia por capa: modelo/controlador no equivalen a restricción física SQL. |
| Objetivos individuales de signos aún “PROPUESTOS” en auditoría antigua | Auditoría formulario signos; decisión V2.2 posterior aprobada | DEPRECADA solo esa afirmación; aprobación posterior resuelve el alcance. |
| Propuesta experta usa otra raíz y Repository Pattern | Arquitectura experta §8; arquitectura vigente/AGENTS | CONFLICTIVA en ubicación; aplica Backend/Modulos. No imponer repositorios. |
| Clasificaciones MoCA/MMSE en controlador heredado | [AdultoMayorEvaluacionController](../../app/Http/Controllers/Valoraciones/AdultoMayorEvaluacionController.php):54–80 | Evidencia de riesgo y legacy; no acredita método o licencia aprobados. |
| Posible exposición de expediente a familiar por permiso genérico | ReporteV2Controller:15–16 y routes/web.php:1014; DocumentoController:30 | Hallazgo de lectura para revisión posterior por recurso/contenido. Ver residente no concede expediente/descarga; no se reparó producto en esta tarea. |
| Ciclos de alerta con eventos distintos entre entradas | AlertaController:91, AlertasService:98, DeteccionAlertasService:152 frente a ServicioDecisionAlertaClinica | Riesgo a verificar por operación y contrato; creación/eventos no certificados por esta auditoría de skills. |
| Texto de la tarea de sistema insertado previamente en guía UX | Diff preexistente de STACK_SKILLS_UX_UI.md | Preservar cambio del usuario; no elevar el pegado a protocolo clínico ni extender su autoridad visual. |

Pendientes de decisiones/documentación: catálogos clínicos aún abiertos, PRN/reintentos, metodología y derechos del experto. Pendientes de evidencia: carreras/rollback con PostgreSQL y validación de extensión aprobada allí. Las skills obligan a aislar esos huecos y continuar trabajo independiente.

## Criterios de aceptación del stack

- Descripciones distinguen diseño/implementación y gates; no hacen universales todas las etapas.
- Cada skill de sistema nueva/evolucionada contiene frontmatter y las once secciones de cuerpo solicitadas, fuentes y límite de responsabilidad.
- Referencias de detalle se cargan por necesidad; no replican diccionario, matriz completa de permisos o UX.
- Lectura de fuentes distingue vigente/histórico/propuesta/conflicto/deprecación parcial/no resuelto.
- Pruebas operativas, validación clínica experta, build y revisión visual se informan por separado.
- Ninguna instrucción autoriza cambios de esquema, reglas clínicas, permisos o eliminación de datos sin la decisión aplicable.
- Revisión por escenarios y validación mecánica se registran en [el informe de verificación](VERIFICACION_SKILLS_SISTEMA.md).
