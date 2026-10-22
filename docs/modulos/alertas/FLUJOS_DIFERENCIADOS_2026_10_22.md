# Flujos de Alertas por rol — 22/10/2026

## UX FLOW previo a implementación

Objetivo: que la bandeja de Alertas sirva a la siguiente tarea real de cada rol, con distinta jerarquía, contexto y acción principal dentro de la identidad institucional compartida.

Entrada común: `/admin/alertas-clinicas`, rol autenticado o previsualización de Superadministración. Cuenta activa, permiso y ámbito de `BandejaAlertas`; la previsualización conserva su prohibición de escritura. Familia sigue en su portal autorizado.

| Usuario | Recorrido y prioridad | Contexto visible | Salida y siguiente acción |
|---|---|---|---|
| Enfermería | Pendientes del turno → intervención → seguimiento | Situación, ubicación, responsable y últimos eventos del caso | Registro de intervención y continuidad del cuidado; pase de turno existente |
| Médico/Geriatra | Alerta → revisión clínica → evolución médica | Última nota vigente del residente, tipo y contenido con fecha/autor | Abrir evolución médica, respaldada por atención propia |
| Psicología | Observación → valoración → continuidad psicológica | Ánimo/afecto y conducta/relación separados de conclusión/recomendación | Abrir valoración psicológica propia |
| Nutrición | Situación alimentaria → apoyo/hidratación → valoración | Última valoración con apetito, deglución, asistencia, restricciones y requerimiento hídrico en mL/día | Abrir valoración nutricional propia |
| Fisioterapia | Movilidad → autonomía diaria → seguimiento funcional | Matriz de autonomía separada de capacidad motora y conclusión | Abrir valoración funcional propia |
| Pedagogía | Actividad → participación → observación de cambios | Atención, comprensión, ejecución, participación y continuidad | Abrir seguimiento pedagógico |
| Administración | Avisos sin responsable → coordinación → seguimiento operativo | Cola de asignación y límites de fecha ya guardados | Reconocer/asignar/coordinar en el detalle, según permisos existentes |
| Gerencia | Pendientes del equipo → distribución por responsable → supervisión | Agregación real de alertas activas por responsable, con filtro aplicable a la bandeja | Consultar la carga registrada; asignar según permiso, sin registrar actos clínicos |
| Superadministración | Fuentes → relación del caso → eventos | Distribución por origen/generación y trazabilidad persistida | Inspeccionar el registro y su historial; entrar en previsualización si corresponde |
| Familia | Información compartida del residente vinculado | Portal autorizado | Contacto institucional; no se publica expediente ni alertas clínicas automáticamente |

La última nota/valoración se presenta como **contexto del residente**, no como origen comprobado de la alerta ni como evaluación vigente de hoy. Se exige permiso específico de consulta de esa fuente. Su ausencia se muestra sin inventar datos; una falta de permiso impide recuperar el contenido. Fecha, autor y estado se conservan.

Información compartida que permanece: residente, prioridad del dominio, situación guardada y acceso a todas las alertas del ámbito. La especialidad no filtra por palabras, no recalcula gravedad ni oculta avisos de otras fuentes.

Composición: Enfermería prioriza cola de cuidado; Medicina revisión de nota; Psicología observación/continuidad; Nutrición lectura alimentaria/hídrica; Fisioterapia matriz funcional; Pedagogía proceso de actividad; Administración coordinación; Gerencia equipo; Superadministración fuentes/grafo. La lista y los filtros son primitivas comunes, no la composición completa de cada puesto.

En móvil los paneles pasan a una columna; seleccionar un caso permite llegar al contexto y volver a la bandeja. Ninguna acción esencial depende de hover, color o desplazamiento horizontal. Los formularios, validación y protección de cambios pendientes ya implementados siguen siendo los mecanismos de registro.

Excepciones: sin alertas, filtros sin resultados, sin fuente profesional vigente, sin permiso de esa fuente, contexto seleccionado fuera de la consulta, validación, fallo de conexión y previsualización sin escritura. Recuperación mediante filtros, regreso a la lista o consulta del detalle, conservando la captura cuando corresponde.

Fuentes: contrato de Alertas; baseline de roles y aprobación de lectura para tres especialidades de esta conversación; `BandejaAlertas`, `ExperienciaAlertas`, `CamposRegistroEspecialidad`, registradores de Clínica/Valoraciones y Design System actual. No requiere cambiar esquema, permisos ni protocolos clínicos.

## Implementación y contrato de los grafos

- `ExperienciaAlertas` define título, icono, composición, acción de selección e indicadores por rol. La identidad administrativa prevalece cuando una cuenta acumula varios roles; una previsualización explícita sigue determinando el puesto mostrado.
- `ConsultarContextoEspecialidadAlerta` consulta una sola fuente por caso, después del ámbito de Alertas y del permiso de lectura de esa fuente. Conserva fecha, autor y campos guardados. No consulta la tabla de la especialidad cuando falta permiso. La nota clínica se etiqueta como tal y conserva su autor; no se atribuye automáticamente a Medicina.
- `ConsultarResumenEquipoAlertas` agrega solo responsables para Gerencia y origen/generación para Superadministración. Devuelve hasta ocho grupos. Las demás especialidades no ejecutan estas agrupaciones. Los recuentos explican período/ámbito; no valoran desempeño.
- Administración filtra alertas activas sin responsable o con límite registrado superado. Gerencia puede seleccionar un responsable y volver a todas las alertas activas del equipo. Ninguno de estos filtros modifica estados o plazos.
- `ConsultarTrazaAlerta` conserva los vínculos comprobados y añade, cuando existe permiso y registro vigente, la relación **residente → contexto profesional → autor / atención y área / grupos de campos**. Valida que el contexto pertenezca al mismo residente y rol. Las secciones son agrupaciones de campos del mismo registro, no entidades de la base.
- El origen verificable de una alerta sigue separado del contexto longitudinal. Una valoración consultada no se representa como causa de la alerta. La línea discontinua identifica contexto; el inspector nombra cada relación con sus dos extremos. Si falta origen verificable se informa expresamente.
- El componente de grafo está visible en cada puesto autorizado, con propósito específico, selección de nodo, capas de caso/área/eventos, zoom, enfoque, retorno a vista completa, navegación de teclado y alternativa en tarjetas. El inspector muestra todos los campos del grupo seleccionado y permite saltar a su contenido sin recorrer un SVG largo. La selección del grafo es local y de consulta: no persiste coordenadas, relaciones ni registros.
- Al cambiar filtros, el contexto seleccionado debe seguir perteneciendo a la consulta. Si deja de coincidir, se consulta el primer caso de esa lista; si no hay resultados, se conserva la composición profesional sin inventar valores.

## Consulta con edición bloqueada

Los formularios profesionales conservan sus campos visibles dentro de un `fieldset disabled` cuando la previsualización, los permisos o el contexto impiden registrar. También se muestran tipo y motivo de la atención aunque el selector de atención esté deshabilitado. El botón de guardado permanece visible y bloqueado, con explicación.

El detalle de Enfermería y los roles de coordinación/supervisión muestra los campos propios en consulta cuando su edición está bloqueada. Las actuaciones ya realizadas se leen en el historial; los campos deshabilitados no se rellenan con actuaciones ficticias. La prohibición de escritura se verifica en servidor, además del bloqueo HTML. La previsualización rechaza la intervención y el alta antes de validar contenido, y el registro de especialidad conserva su rechazo previo existente. Los puestos que registran avisos pueden abrir su formulario en consulta: se ven origen, residente, prioridad, tipo y motivo, pero la selección y el guardado quedan deshabilitados y no se genera una solicitud de alta.

Esto no habilita lectura a una cuenta inactiva ni publica contenido clínico para Familia. «Ver todos los campos» se limita a las etiquetas del formulario y a los datos que la cuenta tenga autorización para consultar.

## Verificación y límites

Se usan únicamente fixtures sintéticos en pruebas aisladas; no se crea un residente ni una cuenta en la base institucional. La comprobación institucional fue una transacción PostgreSQL `READ ONLY` revertida: la cuenta solicitada tiene Superadministración, su perfil es auditoría y su ámbito contiene 33 alertas en ese momento. El caso señalado en la captura referencia un incidente del mismo residente; la consulta actual verifica ese origen y muestra dos nodos útiles: hecho y medida inmediata. La falta de fuente mostrada anteriormente procedía de una proyección que solo resolvía signos, no de una referencia inexistente. Este resultado no acredita inicio de sesión en el navegador.

## Origen útil y cadena de cuidados

La captura de la propietaria mostró un grafo excesivamente general. Se incorporó `ConsultarOrigenOperativoAlerta` para recuperar referencias originales, con comprobación de residente y permiso por fuente. Normaliza únicamente la comparación del nombre de módulo; no modifica valores en la base:

- `INCIDENTE` / `INCIDENTES`, incluidas mayúsculas/minúsculas: incidente original, descripción, fecha/lugar, gravedad registrada, situación, autor y medida inmediata. Se conservan los indicadores guardados de necesidad de médico/derivación; no prueban que una evaluación o derivación se haya realizado.
- `SOLICITUD_MEDICA` y el tipo exacto `INCIDENTE EN SEGUIMIENTO` del detector: atención referenciada, con motivo, tipo persistido, fecha, área, autor, observación y estado. No se deduce una relación con una atención a partir de palabras de la descripción.
- `PLAN`: ejecución referenciada según el productor vigente; desde ella se recorre su intervención y su plan, con permisos separados. La intervención solo se incorpora si el plan pertenece al mismo residente. Se muestran programación, ejecución documentada, resultado, omisión, objetivo específico y objetivo general. Un cuidado programado u omitido nunca se presenta como ejecutado.
- `SIGNOS` / `SIGNOS_VITALES`: medición referenciada con valores guardados y unidades, conservando sistólica y diastólica separadas. No se añaden umbrales.

El grafo arranca seleccionando el origen útil cuando existe. Los eventos llevan nombres comprensibles y cada nodo añade un resumen del dato; el inspector permite leerlo completo. La nueva capa «Hecho y cuidado de origen» permite explorar esa cadena. El detalle de la alerta consulta la misma proyección, evitando que anuncie una fuente ausente cuando el grafo ya la verificó.

No se vincula el incidente con cualquier plan del residente por proximidad temporal ni por coincidencia de palabras. La relación incidente → plan no consta en el modelo revisado. El plan solo aparece en la cadena de una ejecución que lo referencia a través de su intervención. Los valores ausentes se muestran como tales.

### Evidencia técnica

| Comprobación | Resultado |
|---|---|
| SQLite: origen útil, flujos por rol, bloqueos y transporte de previsualización | 7 pruebas / 245 aserciones, PASS (`origen-util-sqlite.xml`). |
| PostgreSQL aislado: incidente referenciado, permisos, residente correcto y plan → intervención → ejecución omitida | 2 pruebas / 26 aserciones, PASS (`origen-util-postgres.xml`); clúster temporal detenido al finalizar. |
| SQLite: regresiones previas de especialidades, roles y grafos | 10 pruebas / 434 aserciones, PASS (`grafos-final-sqlite.xml`), anterior a la ampliación de orígenes. |
| PostgreSQL aislado: flujos y consulta bloqueada | 4 pruebas / 199 aserciones, PASS (`grafos-final-postgres.xml`), anterior a la ampliación de orígenes. |
| JavaScript: capas, enfoque, consulta y ausencia de mutación | 8 pruebas, PASS. |
| Build del origen útil | PASS, 110 módulos / 8,89 s (`build-origen-util.log`). |

Las evidencias se conservan en `output/alertas-20261021/`. La selección acotada evita una batería masiva y distingue qué corte cubre cada ejecución. La consulta real de la referencia comprobó origen y residente sin publicar nombres ni contenido clínico en el reporte.

El navegador local sigue desactivado en Work. Ante la nueva solicitud de entrar como Superadministración se intentó acceder al tab y el proveedor devolvió esa limitación. No se sustituyó el acceso por un bypass de autenticación ni se afirma que se haya abierto una sesión. QA visual del último corte: **FAIL por evidencia incompleta**, sin defecto visual afirmado por suposición. Pendientes los anchos 1440/1280/1024/768/390, los temas, contraste, foco y operación de SVG/formularios en navegador. Las pruebas y build no sustituyen esa revisión.

Sin migraciones, dependencias, cambios de permisos adicionales, escrituras clínicas institucionales ni operaciones de staging/commit/push. Se preservó el trabajo local previo. La deuda de accesores históricos de `Atencion` se aisló en los lectores afectados usando el valor persistido de `tipo_atencion`, evitando tratar un objeto de compatibilidad como texto visible.
