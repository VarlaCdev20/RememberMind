# Mapa de módulos y matriz de refactorización

Recomiendo 12 módulos técnicos. Las especialidades asistenciales comparten evaluación y cuidado, sin perder sus datos y permisos específicos. Un módulo técnico no equivale a una sección del menú ni a una profesión.

| Módulo | Alcance funcional | Propietario de escritura | Dependencias permitidas |
|---|---|---|---|
| Identity | Personas, usuarios, profesionales, RBAC | Identity Actions | Institution para asignación organizativa |
| Institution | Institución, áreas, cargos, habitaciones, camas y turnos | Institution | Identity para personal asignado |
| Residents | Identificación de residente, contactos, consentimiento, expediente compuesto | Residents | Identity; Queries clínicas para lectura |
| Admissions | Caso de ingreso, estancia, egreso, asignación de cama | Admissions | Residents, Institution |
| Clinical | Notas, diagnósticos, alergias, signos | Clinical | Residents, Identity |
| Medication | Prescripciones y administraciones | Medication | Residents, Clinical como contexto |
| Assessment | VGI, cognición, afectividad, funcionalidad, movilidad y nutrición evaluada | Assessment | Residents, Identity |
| Care | Planes, tareas, turno, observaciones, sesiones y planes nutricionales | Care | Residents, Assessment, lectura Medication |
| Social | Familia/seguimiento social, actividades, visitas, voluntariado | Social | Residents/Identity; contactos pertenecen a Residents |
| Safety | Incidentes, caídas, alertas y acciones | Safety | Residents; referencias a fuentes clínicas |
| ExpertSystem | Ejecuciones y revisión de inferencias | ExpertSystem | Queries/snapshots de Clinical/Assessment/Care |
| Reports | Indicadores y exportación autorizada | Sin escritura clínica | Queries de módulos; Documents |

Documentos es un servicio transversal pequeño (Support/Documents) con tablas propias y políticas según propietario; agenda inicial se compone de turnos, visitas, actividades y sesiones. Si se validan citas generales independientes, añadir appointments justificadamente. No simular una agenda clínica completa con un campo JSON sin FK.

## Matriz por componente funcional actual

| Componente | Estado actual / problemas | Reutilizable | Decisión | Destino |
|---|---|---|---|---|
| Fortify/Jetstream/Sanctum | Login personalizado; controles de estado parciales | Sesión, hashing, limitadores, 2FA | CONSERVAR CON AJUSTES | Identity |
| Usuarios y PersonalInstitucional | Dos flujos de datos, submodelos por rol, UI muy extensa | Validaciones, documentos y horarios como requisitos | REFACTORIZAR | Identity + Institution |
| RolesPermisos | Spatie y nombres ligados a profesiones | Tablas y paquete | CONSERVAR CON AJUSTES | Identity |
| Áreas | CRUD/reportes y datos institucionales | Catálogo y tokens | CONSERVAR CON AJUSTES | Institution |
| Turnos/asignaciones | Horarios por tipo y generador grande | Reglas de cobertura que se validen | REFACTORIZAR | Institution + Care |
| AdultosMayores | CRUD, modal y expediente con responsabilidades mezcladas | Identidad y relaciones reconciliadas | REFACTORIZAR | Residents |
| Admisiones | Wizard/casos con creación múltiple y documentos | Flujo por etapas y estado de pendientes | REFACTORIZAR | Admissions |
| Médico | Ficha, decisión, notas, Barthel y signos | Registro de notas/signos | REFACTORIZAR | Clinical + Assessment + Admissions |
| SaludSeguimiento | Duplica entradas a ficha, medicamentos y signos | Consultas/gráficos revisados | REFACTORIZAR | Clinical + Medication + Assessment |
| Medicación | Orden plana, receta documental, administración separada | Datos originales y vínculo de receta | REFACTORIZAR | Medication |
| Enfermería | Dashboard, ficha, cama, valoración, tareas, pase | Flujos operativos y planes | REFACTORIZAR | Care; camas a Institution/Admissions |
| Psicología | Evaluación por área; enlaces aún placeholder | Instrumentos y tendencias como experiencia | REFACTORIZAR | Assessment + Care |
| Movilidad/fisioterapia | Principalmente alias de dashboard | Requerimientos, no funcionalidad presumida | REESCRIBIR | Assessment + Care |
| Nutrición | Principalmente alias de dashboard | Peso/signos disponibles | REESCRIBIR | Assessment + Care |
| FamiliaSocial | Árbol y vínculos, varios datos de contacto | Vínculos, visualización SVG revisada | REFACTORIZAR | Social + Residents |
| Actividades | CRUD y participación | Catálogos y datos | CONSERVAR CON AJUSTES | Social |
| Voluntariado | Asistencia, disponibilidad y asignación | Flujos no clínicos | CONSERVAR CON AJUSTES | Social + Institution |
| Planes/cuidados | Tablas ya separadas plan/tarea/seguimiento | Estructura de proceso | REFACTORIZAR | Care |
| Alertas | Acciones y estados presentes | Alerta/acción | CONSERVAR CON AJUSTES | Safety |
| Portal familiar | Alias y permisos generales | Ningún supuesto de protección por menú | REESCRIBIR | Presentation familiar + Residents Queries |
| Reportes y bitácora | Servicios y exportadores, lógica repetida | Plantillas PDF y biblioteca Excel | REFACTORIZAR | Reports + Documents |
| Sistema experto | No implementación identificada | Datos clínicos futuros reconciliados | REESCRIBIR (desarrollo nuevo) | ExpertSystem |
| Alias/placeholder y duplicados huérfanos | No representan procesos completos | Nada tras comprobar referencias | ELIMINAR después del reemplazo | Rutas canónicas |

«REESCRIBIR» en un placeholder significa implementar una capacidad ausente; no eliminar información. Las carpetas solo se crean cuando un corte mueve código probado. No separar Psychology, Mobility y Nutrition como tres módulos de infraestructura idéntica al inicio; separarlos después si desarrollan invariantes propias suficientes.
