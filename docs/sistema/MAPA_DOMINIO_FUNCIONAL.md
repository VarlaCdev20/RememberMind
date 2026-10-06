# Mapa funcional y límites de RememberMind

Fecha: 2026-10-06. Mapa para localizar contratos y consumidores; no certificación de completitud de módulos. Fuentes: [AGENTS](../../AGENTS.md), [arquitectura](../arquitectura/README.md), [baseline BDD](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Consultar secciones concretas antes de implementar.

## Áreas y recorridos

| Área | Entidades/proceso | Ubicaciones y evidencia a descubrir | Invariantes relevantes |
|---|---|---|---|
| Institucional | Postulante → preadmisión → revisión → aprobación/rechazo → admisión formal → residente | Admisiones/Acciones/FormalizarAdmision; Policies/Requests/controladores; CasosPreadmisionTest | Aprobación no crea residente; no CRUD directo. Estados físicos según contrato resuelto. |
| Residencia | Habitaciones, camas, ocupaciones, estado e historial | Models Cama/OcupacionCama/Residente; OcupacionCamaIntegrityTest; migraciones hardening | Una ocupación activa por cama y residente, transacción y bloqueo; historial preservado. |
| Identidad y operación laboral | Usuarios, personal, profesiones, áreas, jornadas, asignaciones | Identidad/Servicios/ContextoLaboralService; Policies; AsignacionesIntegrityTest | Usuario ≠ personal; cuenta activa; jornada/área/competencia según acción aprobada. |
| Vínculos y documentación | Contactos, consentimientos, seguros, documentos | Documentos/Servicios; Models Consentimiento/ResidenteContacto; controladores | Firmante vinculado al mismo residente; descarga autorizada y almacenamiento privado. |
| Clínica general | Atenciones, notas, antecedentes, diagnósticos, alergias, dispositivos, derivaciones, indicaciones | Models/controladores Clínica/Residentes; servicios clínicos; RegistrosClinicosIntegrityTest | Autor profesional real; atención del mismo residente; evento nuevo no sobrescribe trayectoria. |
| Medicación | Catálogo → prescripción → horario → administración/omisión | Medicacion/Servicios; PrescripcionPolicy; AdministracionMedicacionIntegrityTest | Enfermería no prescribe; residente y horario coherentes; no modificar orden por administración. |
| Controles y cuidado | Signos vitales, dolor, antropometría, cognición, conducta, sueño, alimentación, hidratación, eliminación, higiene, movilidad, lesiones | Clinica/Acciones y SignosVitales; Enfermeria/Servicios; pruebas específicas | Preview sin escritura; significado backend aprobado; unidades/fecha/autor e historial. |
| Planes de cuidado | Plan → intervención → programación → ejecución/resultado | Models PlanCuidado/IntervencionCuidado/ProgramacionCuidado/EjecucionCuidado; CuidadosIntegrityTest | Reutilizar grano y estado vigentes; no nueva entidad “tarea” por conveniencia. |
| Continuidad | Turno, pendientes, incidentes, alertas, pase, próximo profesional | Enfermeria/Servicios/PaseTurnoService; MiTurnoService; PaseTurnoIntegridadTest | Información disponible en próximo punto operativo con permisos; no crear historial paralelo. |
| Interdisciplinaria | Medicina/geriatría, enfermería, psicología, nutrición, fisioterapia y pedagogía | Valoraciones/atenciones/planes/instrumentos; UI compartida o del rol real | Competencia explícita y alcance; compartir información no otorga escritura a todas las profesiones. |
| Instrumentos | Instrumento versionado → preguntas/opciones → aplicación → respuestas/resultados | InstrumentoController; Models Instrumento/AplicacionInstrumento/RespuestaInstrumento; BddOperativaV2Test | Pregunta/opción correctas y versión aplicable; contenido y método legalmente autorizados. |
| Estudios | Tipo → componentes → estudio → resultados → informe/documento | EstudioClinicoController; Models correspondientes; BddOperativaV2Test | No tabla por examen; componente del tipo correcto; duplicados según contrato; archivo privado con metadata/hash. |
| Operación social | Actividades, participación/asistencia, visitas, documentación residencial | Actividades/Visitas; Models/controladores; ActividadesIntegrityTest | Operación social no se confunde con atención clínica ni concede acceso general a familia. |
| Alertas | Condición → alerta → eventos de atención/seguimiento/cierre | Alertas/Servicios/AlertasService y DeteccionAlertasService; AlertasFlujoTest/AlertasHistorialFeatureTest | Estado actual + trayectoria; evento ≠ notificación ≠ auditoría. No inventar ciclo/umbrales. |
| Reportes/búsquedas | Consultas, históricos, exportaciones por ámbito | Reportes/Servicios; Exports; Policies/rutas | Permisos antes de serializar/exportar; filtros y fechas reproducibles; paginar; no alterar fuente. |
| Auditoría y soporte técnico | Actividad aprobada, errores, jobs, diagnóstico | Activitylog/config/modelos/providers; tests audit | Proveniencia clínica en dominio; actor técnico separado; logs mínimos, sin payload clínico completo. |
| Experto cognitivo futuro | Apoyo a detección temprana y revisión profesional | docs/sistema-experto; aún propuesta sin módulo cognitivo ejecutable acreditado | No diagnóstico/tratamiento autónomo; métodos, derechos, reglas, integración y persistencia necesitan decisión aplicable. |

El evaluador operativo de signos vitales no demuestra un sistema experto cognitivo. Los controladores con nombres AdultoMayor son evidencia de legacy, no nuevas dependencias permitidas.

## Actores y límites

| Actor | Contrato a proteger |
|---|---|
| Superadministrador | Lectura global según alcance; rol por sí solo no concede escritura clínica. Excepción local/testing solo conforme documento temporal y configuración vigente. |
| Gerente | Dirección institucional, personal y planificación; no competencia clínica automática. |
| Administrador | Operación diaria; no gestión de cuentas/RRHH ni escritura clínica por rol. Resolver README contradictorio siguiendo AGENTS/baseline roles. |
| Médico general/geriatra | Prescripción ordinaria y actividad clínica dentro de permisos, Policy y competencia. |
| Enfermería | Administración/documentación del cuidado; no prescribir ni editar orden médica. |
| Psicología/nutrición/fisioterapia/pedagogía | Operaciones de su competencia y alcance autorizados; no extrapolar privilegios. |
| Familiar | Información autorizada de residentes vinculados; sin acceso por código arbitrario. |
| Otras funciones, voluntariado | VOLUNTARIO está fuera del alcance vigente según baseline roles. Roles futuros no justifican permisos ni flujos ejecutables. |

## Hallazgos que no son decisiones nuevas

- Admisión: condición institucional y valores físicos divergentes en fuentes; registrar conflicto por transición.
- Contexto clínico: validación general de jornada actual puede divergir de interconsultas/actos fuera de turno permitidos por Policy. No inventar obligación universal de turno.
- Coherencia instrumento/pregunta y consentimiento/contacto: distinguir validación Eloquent, entrada y restricción SQL real.
- PostgreSQL: configuración de CI o locks en código no demuestra carreras/rollback ejecutados.
- Responsables de áreas: el contrato actual es mostrarlos desde asignaciones; no añadir columna ni permitir una edición ficticia.
- Catálogos/reglas abiertas e idempotencia PRN requieren decisión específica; ninguna skill las fija.
