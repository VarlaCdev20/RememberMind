---
title: "Estados y ciclos de vida"
status: CURRENT
version: "2.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [lifecycle_inventory]
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: ["../../docs/README.md", "../../docs/TRAZABILIDAD.md"]
related_modules: []
---

# Estados y ciclos de vida

Contraste estático del commit base y árbol con cambios previos sin commit. No se ejecutaron migraciones, seed, suites, build ni BDD real. CURRENT no aprueba reglas nuevas. `verified_against_commit` identifica base de lectura; no certifica runtime.

Fuentes rectoras: [AGENTS](../../AGENTS.md), [baseline](../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [extensión aprobada V2.2](../../docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md), [roles](../../docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Inventario 70+1=71 es evidencia estática del repositorio, no BDD instalada comprobada.

## Cómo interpretar el inventario

**OBSERVED IMPLEMENTATION** salvo fila expresamente APPROVED CONTRACT. Strings, constantes, Enums, validators y lectores están dispersos; no existe un Enum universal de estado. Una lista admitida por un Request no demuestra todas las transiciones permitidas. No se homogeneizan ACTIVO/ACTIVA/VIGENTE, ni se crea catálogo nuevo. **OPEN DECISION:** DEC-OPEN-001 (estado institucional), DEC-OPEN-002 (catálogos), DEC-OPEN-003 (etiquetas clínicas), en [registro](../../docs/DECISIONES_PENDIENTES.md).

## Inventario por entidad y entrada

| Entidad / fuente | Estado y significado observado | Transición observada o límite | Actor / precondición | Efecto |
|---|---|---|---|---|
| preadmisiones — [Controller](../../app/Http/Controllers/Admisiones/PreadmisionController.php) | PENDIENTE: por revisar; APROBADA/RECHAZADA: revisión decidida | PENDIENTE → APROBADA o RECHAZADA; Action cambia APROBADA → ADMITIDA | Revisor permitido por entrada; revisión solo pendiente, rechazo con motivo | Guarda revisión/autor/fecha; aprobar no crea residente |
| residentes — [Action](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php), [Model](../../app/Models/Residente.php) | ADMITIDO: creación en Action; ACTIVO/EST_001 aceptados en controles | No se acredita catálogo completo de altas/egresos | Formalización con cama; autor autorizado por entrada | Residente + historial; **CONFLICT** ACTIVO físico vs ADMITIDO, DEC-OPEN-001 |
| residente, entrada heredada — [Modal](../../app/Frontend/Livewire/Admisiones/DecisionAdmisionModal.php) | DECISION_ADMISION → PENDIENTE_ASIGNACION o DERIVADO | Flujo sobre residente ya creado; no ingreso canónico | Roles médicos admitidos por servicio temporal | Update + historial + activity; **CONFLICT**, TECH-006 |
| admisiones — [Action](../../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php) | ACTIVA: formalización registrada | Creación; cierre/cancelación NOT VERIFIED aquí | Preadmisión APROBADA y sin admisión previa | Guarda vínculo con preadmisión y residente |
| ocupaciones_cama — [Model](../../app/Models/OcupacionCama.php) | ACTIVA; ACTIVO aceptado por guardia | Alta activa; liberación tiene fecha/motivo en esquema, ciclo completo NOT VERIFIED | Sin otra ocupación activa por cama/residente | Ocupación; no se infiere liberación automática |
| camas / habitaciones — [Cama](../../app/Models/Cama.php), [Panel](../../app/Frontend/Livewire/Admisiones/HabitacionesPanel.php) | DISPONIBLE, OCUPADA, MANTENIMIENTO, BLOQUEADA en panel; Action formaliza con cama ACTIVA | **CONFLICT** disponibilidad Model DISPONIBLE vs entrada Action ACTIVA | Operador con permisos; ocupado no puede liberarse por editar etiqueta | Panel bloquea mantenimiento habitación ocupada y sincroniza presentación; catálogo físico pendiente DEC-OPEN-001 |
| turnos — [Model](../../app/Models/Turno.php) | ACTIVO/ACTIVA leído: catálogo horario | No equivale a jornada abierta | Planificación institucional | Horario/orden, sin creación clínica |
| jornadas — [HTTP](../../app/Http/Controllers/Identidad/InstitucionalController.php), [Pase](../../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php) | ABIERTA creado HTTP; ACTIVA creado panel; PLANIFICADA/EN_CURSO leídos | Requisitos de ventana/fecha por operación; cierre integral no acreditado | Operación/planificación y contexto laboral | Define contexto por fecha y turno; no catálogo aprobado deducido del filtro |
| asignaciones_personal / asignaciones_residente_jornada — [Panel](../../app/Frontend/Livewire/Administracion/Identidad/TurnosAsignacionesPanel.php), [HTTP](../../app/Http/Controllers/Cuidados/CuidadoController.php) | ACTIVA/ACTIVO vigentes; ANULADA en desvinculación laboral | Reasignación de plaza anula anterior; relación residente creada explícitamente | Cuenta/permiso, personal/jornada activos | Scope laboral y asistencial distintos |
| prescripciones — [HTTP](../../app/Http/Controllers/Medicacion/MedicacionController.php), [Model](../../app/Models/Prescripcion.php) | ACTIVA en HTTP, ACTIVO default Model; SUSPENDIDA | Activa → suspendida con motivo, autor y fecha | Médico autorizado, atención/residente propios | No modifica administraciones históricas |
| horarios_prescripcion — [Agenda](../../app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php) | ACTIVO: horario programable | Lector excluye otros valores; edición/cierre completo NOT VERIFIED | Orden activa, fecha/día válidos | Genera ocurrencia derivada; no administración ficticia |
| administraciones_medicacion — [Writer](../../app/Backend/Modulos/Medicacion/Servicios/RegistrarAdministracionMedicacionService.php), [Model](../../app/Models/AdministracionMedicacion.php) | estado REGISTRADA en Service / FINALIZADO default Model; resultado ADMINISTRADA/OMITIDA | Nuevo evento; no transición automática PENDIENTE → ADMINISTRADA en estado | Enfermería asignada y orden/horario coherentes; omisión con motivo | Fecha programada, fecha real si administrada, autor/jornada |
| agenda de medicación — [Proyección](../../app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php) | PENDIENTE/PROXIMA/VENCIDA/ADMINISTRADA/OMITIDA | Derivado de horario, reloj y registro existente | Lectura de residentes permitidos | **No estado persistido de administración** |
| planes_cuidado / intervenciones / programaciones — [HTTP](../../app/Http/Controllers/Cuidados/CuidadoController.php), [Plan](../../app/Models/PlanCuidado.php) | ACTIVO/ACTIVA creados y VIGENTE aceptado para actividad | Creación separada; ciclo de suspensión/cierre uniforme no acreditado | Profesión/permiso/área; plan activo | Planificación, no ejecución automática; DEC-OPEN-002 |
| ejecuciones_cuidado — [Writer](../../app/Http/Controllers/Cuidados/CuidadoController.php), [Model](../../app/Models/EjecucionCuidado.php) | HTTP PENDIENTE/EJECUTADA/OMITIDA/CANCELADA; Model reconoce EN_PROCESO; Pase busca REALIZADA/NO_REALIZADA | Validator de creación, no máquina de transición exhaustiva | Enfermería, plan/intervención activos, motivo si omisión | Registro nuevo; **CONFLICT lector/writer**, TECH-008 |
| alertas — [Controller](../../app/Http/Controllers/Alertas/AlertaController.php) | ABIERTA, RECONOCIDA, ASIGNADA, EN_ATENCION, ATENDIDA, CERRADA, ANULADA | Tabla siguiente | Enfermería contextual o Administración por acción; bloqueo y estado válido | Estado + CAMBIO_ESTADO, no necesariamente cambio de responsable |
| alertas — [Model](../../app/Models/Alerta.php), [Panel](../../app/Frontend/Livewire/Compartido/Alertas/AlertasPanel.php) | ABIERTA/EN_ATENCION mutables; CERRADA terminal | ABIERTA → EN_ATENCION → CERRADA; asignar responsable no cambia estado | Permiso específico o gestionar, scope según entrada | Eventos de asignación/intervención/seguimiento/cierre; evento inicial ausente TECH-002 |
| signos_vitales — [Service](../../app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php) | ACTIVO/VIGENTE válidos; RECTIFICADO anterior; ANULADO en método Controller | Rectificación: original RECTIFICADO + nueva medición; no sobrescribir mediciones originales | Contexto clínico; método anular carece de contexto visible, alcance enrutado no acreditado | Historia conservada; sin FK nueva entre correcciones |
| objetivos_signos_vitales — decisión V2.2 y [Action](../../app/Backend/Modulos/Clinica/Acciones/DefinirObjetivoSignoVitalAction.php) | **APPROVED CONTRACT** VIGENTE/REEMPLAZADO/ANULADO | Nueva versión reemplaza vigente; anulación lógica | Médico activo con permiso y autor propio | Mantiene versiones; no rebaja crítico general |
| documentos — [HTTP](../../app/Http/Controllers/Documentos/DocumentoController.php) | PENDIENTE al subir | Aprobación/archivo por todas las entradas NOT VERIFIED | Permiso gestionar y view residente | Metadata/hash/ruta; no certifica validez legal |
| documentos_clinicos / informes_estudio — [HTTP](../../app/Http/Controllers/Clinica/EstudioClinicoController.php) | VIGENTE al crear | No se fuerza transición uniforme con documento administrativo | Competencia médica y relación correcta | Archivo/informe conservado |
| consentimientos — [Model](../../app/Models/Consentimiento.php), [HTTP](../../app/Http/Controllers/Residentes/RelacionResidenteController.php) | VIGENTE creado | Catálogo completo/revocación no acreditados; DEC-OPEN-002 | Firma residente XOR contacto del mismo residente | Consentimiento vinculado, no regla legal inventada |
| incidentes — [Service](../../app/Backend/Modulos/Enfermeria/Servicios/IncidentesEnfermeriaService.php) | ABIERTO; EN_SEGUIMIENTO; CERRADO; REPORTADO aceptado | Abierto/reportado/seguimiento → seguimiento o cerrado | Enfermería asignada; texto de intervención/cierre obligatorio | Append texto en observación con lock, no tabla nueva de eventos |
| heridas — [Service](../../app/Backend/Modulos/Enfermeria/Servicios/LesionesEnfermeriaService.php) | ACTIVA identificación; CERRADA cierre | Seguimiento/curación separados; catálogo DEC-OPEN-002 | Autor clínico competente | Conserva herida/curaciones; no borrado ordinario |
| pases_turno — [Model](../../app/Models/PaseTurno.php), [Service](../../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php) | BORRADOR, ENTREGADO, RECIBIDO, ANULADO; GENERADO/EMITIDO/PENDIENTE compatibilidad | Panel borrador → entregado → recibido; generar escribe GENERADO; HTTP EMITIDO; anular sin guardia de origen | Emisor/receptor de jornadas; controles incompletos TECH-009 | Resumen/pendientes textuales y recepción fechada |
| estudios_clinicos — [HTTP](../../app/Http/Controllers/Clinica/EstudioClinicoController.php) | SOLICITADO; EN_PROCESO leído; REALIZADO; INFORMADO aceptado | Resultados válidos → REALIZADO; informe creado no actualiza a INFORMADO | Médico y componentes del tipo propio | Resultados/informe por entidad |
| instrumentos / preguntas / opciones / aplicaciones — [HTTP](../../app/Http/Controllers/Instrumentos/InstrumentoController.php) | ACTIVO / ACTIVA / ACTIVO; aplicación COMPLETA | Aplicar solo activos y crear respuestas propias | Permiso + profesión, atención/residente coherentes | Puntaje calculado; estado no acredita validez metodológica |
| atenciones / notas / controles / registros / valoraciones — [Cuidado](../../app/Http/Controllers/Cuidados/CuidadoController.php), [Valoraciones](../../app/Http/Controllers/Valoraciones/ValoracionProfesionalController.php), [Atención](../../app/Models/Atencion.php) | FINALIZADA en atención, VIGENTE en registros/valoraciones | Nuevos eventos; no ciclo genérico de edición | Autor propio, profesión y contexto según caso | Historia clínica; corrección no universalmente implementada |
| usuarios / personal / áreas / vínculos / seguros — [User](../../app/Models/User.php), [Relaciones](../../app/Http/Controllers/Residentes/RelacionResidenteController.php) | ACTIVO cuenta/personal/vínculo; ACTIVA área; ACTIVO seguro en admisión | Solo cuenta ACTIVO autentica; cambios completos por todas las entidades NOT VERIFIED | Gestión institucional y permiso concreto | Habilita contexto, no competencia clínica automática |
| visitas — [HTTP](../../app/Http/Controllers/Residentes/RelacionResidenteController.php) | PROGRAMADA/AUTORIZADA/EN_CURSO/FINALIZADA/CANCELADA | Lista de input; no grafo completo aprobado deducido | Permiso y residente permitido | Registro social; no ingreso clínico |
| actividades / participantes — [Model](../../app/Models/Actividad.php), [Panel](../../app/Frontend/Livewire/Administracion/Actividades/ParticipacionPanel.php) | PROGRAMADA; CANCELADA observados; normalizador acepta REALIZADA/COMPLETADA/FINALIZADA | Alias para presentación no equivalen a catálogo institucional | Personal/área propios y permisos | Programación/asistencia, no reemplazo de valoración |

## Transiciones HTTP de alertas

**OBSERVED IMPLEMENTATION**, AlertaController::cambiarEstado; catálogo pendiente DEC-OPEN-002.

| Origen | Destinos permitidos por esa entrada |
|---|---|
| ABIERTA | RECONOCIDA, ASIGNADA, EN_ATENCION, ATENDIDA, CERRADA, ANULADA |
| RECONOCIDA | ASIGNADA, EN_ATENCION, ATENDIDA, CERRADA, ANULADA |
| ASIGNADA | EN_ATENCION, ATENDIDA, CERRADA, ANULADA |
| EN_ATENCION | ATENDIDA, CERRADA, ANULADA |
| ATENDIDA | CERRADA |
| CERRADA / ANULADA | Ninguno |

## Enums clínicos: otro significado

[SeveridadClinica](../../app/Backend/Modulos/Clinica/SignosVitales/Tipos/SeveridadClinica.php) tiene NORMAL/OBJETIVO_PERSONALIZADO/ADVERTENCIA/ALTO/CRITICO; [ComportamientoAlerta](../../app/Backend/Modulos/Clinica/SignosVitales/Tipos/ComportamientoAlerta.php) NINGUNA/SUGERIR/AUTOMATICA_AL_CONFIRMAR. Son resultados de evaluación, no estado institucional del residente. Valor ausente no equivale a NORMAL ni a Estable. DEC-OPEN-003 sigue abierta.

## Límites de exhaustividad

Inventario de entidades que condicionan los flujos núcleo y sus dependencias observadas; no declara un catálogo cerrado global ni transiciones de módulos no investigados completamente. Fechas de vencimiento, prioridades, resultados y aliases de presentación no se convierten en estados aprobados. Toda transición no localizada se marca NOT VERIFIED antes de implementarla.
