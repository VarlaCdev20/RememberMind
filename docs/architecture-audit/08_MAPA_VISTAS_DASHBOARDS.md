# Vistas, dashboards y expediente 360

Una aplicación y seis composiciones, no once aplicaciones. El perfil determina prioridades; las Policies determinan acceso. El menú se construye desde capacidades efectivas del servidor, sin confiar en él como protección.

| Composición | Perfiles | Prioridad | Compartido |
|---|---|---|---|
| Dirección | Dirección | Ocupación, calidad, riesgos agregados, alertas críticas | Indicadores/periodos y Reports |
| Operaciones | Administración y Trabajo Social parametrizado | Admisiones, camas, agenda, personal; social: contactos/visitas | Agenda, tablas, filtros |
| Clínico | Médico, Psicología, Fisioterapia, Nutrición | Pacientes prioritarios, evaluaciones pendientes, tendencias de disciplina | Cola clínica y expediente |
| Turno/cuidados | Enfermería y Cuidador | Mis residentes, dosis, tareas, signos, incidentes, pase | Lista de turno; cuidador sin prescripción |
| Familiar | Familiar autorizado | Resumen publicado y visitas/documentos autorizados | Componentes visuales; Query propia mínima |
| Técnico | Superadmin | Jobs, salud operativa, accesos y fallos | Diseño general, sin datos clínicos rutinarios |

El usuario con varios perfiles puede cambiar contexto visible entre composiciones habilitadas; no se usa roles.first() como decisión de autoridad. Dirección necesita una vista agregada distinta, familiar requiere un contrato de datos propio y técnico requiere una vista operativa separada. Las cuatro disciplinas clínicas comparten plantilla.

## Expediente único

Ruta conceptual /residents/{resident}; ResidentHeader con nombre, identificador, estancia/ubicación vigente y alertas pertinentes. Secciones: Resumen, Datos personales, Admisión, Historia, Medicina, Medicamentos, Enfermería, Cognición, Funcionalidad, Movilidad, Nutrición, Social, Actividades, Cuidados, Incidentes, Documentos y Experto. No mostrar 18 pestañas simultáneas en tablet: agrupar identificación, salud, cuidado, red social e historia, con subsecciones y acceso directo.

Cada sección tiene Query y capacidades propias. Ocultar la pestaña también omite sus props y consultas; no enviar toda la historia para esconderla con v-if. Timeline cruza registros autorizados, conserva tipo/id origen y paginación por cursor. Filtros fecha/disciplina/tipo no cambian hechos. Comparaciones usan mismo instrumento y versión compatible, o explican la no comparabilidad.

## UX y accesibilidad

Sidebar consistente, breadcrumbs desde ruta, búsqueda global filtrada por permiso y alcance antes de paginar. Formularios cortos, errores asociados a campos y resumen de errores, confirmación de anulación con motivo. Estado vacío distingue sin registros, sin permiso y error de carga. Guardado muestra progreso y evita doble envío, pero la idempotencia se garantiza en servidor.

Tablas con filtros en URL, paginación servidor y columnas adaptadas a tablet; detalles secundarios fuera de la fila. Modal con nombre accesible, foco contenido y retorno al disparador. Gráficos acompañados por tabla/resumen; alerta con texto/icono, no solo color. Revisar teclado, zoom 200–400%, contraste de ambos temas y movimiento reducido.

La referencia es WCAG 2.2 AA; incluye foco no oculto y tamaño mínimo de objetivos de 24×24 CSS px con las excepciones del estándar. Preferir 44 px en acciones de turno por usabilidad. Esto es un objetivo de diseño, no una declaración de conformidad del frontend existente. [WCAG 2.2](https://www.w3.org/TR/WCAG22/).

## Antiguas → compartidas

admin/adultos-mayores/show + médico/FichaClinicaIntegradaPanel + enfermería/FichaPaciente → Residents/Show con vista inicial por contexto. DashboardMedico y DashboardPsicologo → Dashboard/Clinical con widgets autorizados. SaludSignosPanel y registros médicos de signos → Clinical/VitalSigns. Instrumentos y Barthel convergen en Assessment, conservando el formulario específico donde aporta comprensión.
