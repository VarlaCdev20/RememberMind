# Reportes de Enfermería por áreas y secciones

Entrega del 27/10/2026, America/La_Paz. Amplía el [primer resumen por período y residente](REPORTES_ENFERMERIA_PERIODO_2026_10_27.md). Su alcance es Enfermería; no declara implementadas exportaciones nuevas para todas las especialidades de RememberMind. No modifica tablas, columnas, FK, permisos ni reglas clínicas. No usa subagentes.

## Análisis y decisiones de implementación

El reporte anterior reunía cinco fuentes, tenía una tabla cronológica y descargaba PDF. Faltaban los restantes cuidados, el acceso desde cada área, CSV, impresión y una estructura que permitiera elegir una sección sin revisar todo el expediente.

Se encontró `ConsultaCentralCuidados`, que ya prepara los campos nominales de once fuentes. Se extrajo su formateo a `camposPresentacion`, reutilizado por el reporte, sin crear otro historial ni una segunda interpretación clínica. Este método recibe modelos previamente autorizados; no consulta ni concede permisos. Continúan los resúmenes operacionales propios de movilidad y eliminación.

Se mantiene una consulta común para pantalla, vista previa, PDF y CSV. «Generar reporte» abre una pestaña con el área y residente disponibles, conservando los filtros de fecha cuando la pantalla de origen los ofrece. Abrirla en otra pestaña permite conservar un formulario clínico que todavía esté en edición; el reporte solo incluye registros guardados.

## Secciones y fuentes exactas

| Sección visible | Fuente | Fecha y contenido |
|---|---|---|
| Cuidados | `ejecuciones_cuidado` + intervención | Fecha de ejecución o programada; intervención, estado, observación, omisión |
| Controles de signos vitales | `signos_vitales` | Fecha del control; mediciones con unidades y observación |
| Administración de medicación | `administraciones_medicacion` + prescripción + medicamento | Administración o programación; medicamento, dosis administrada cuando consta, resultado, motivo, efecto y reacción |
| Incidentes | `incidentes` | Fecha del incidente; tipo, descripción y estado |
| Alertas | `alertas` | Fecha de alerta; título, descripción, estado, responsable |
| Dolor | `valoraciones_dolor` | Fecha de valoración; intensidad, ubicación, duración, frecuencia, desencadenantes, alivio, intervención y respuesta conservados |
| Alimentación | `registros_ingesta` | Fecha del registro; comida, porcentaje, apetito, tolerancia, deglución y observación |
| Hidratación | `registros_hidratacion` | Fecha del aporte; cantidad medida, líquido, tolerancia y observación |
| Eliminación | `registros_eliminacion` | Fecha; resumen operacional nominal urinario/intestinal y observación |
| Movilidad | `registros_movilidad` | Fecha; actividad, apoyos, distancia y demás campos del resumen operacional |
| Sueño | `registros_sueno` | Fecha sin hora inventada; horas, despertares, insomnio, somnolencia, agitación y calidad |
| Conducta | `registros_conductuales` | Fecha; ánimo, participación, indicadores observados, descripción, intervención y respuesta |
| Observación cognitiva | `controles_cognitivos` | Fecha; observaciones originales y cambios registrados; no equivale a diagnóstico ni clasificación experta |
| Heridas identificadas | `heridas` | Fecha de identificación; tipo, ubicación, causa, clasificación, observación y cierre cuando consta |
| Curaciones | `curaciones_herida` → `heridas` | Fecha de curación; herida, medidas, tejido, exudado, dolor, procedimiento, materiales y respuesta |
| Dispositivos clínicos | `dispositivos_clinicos` | Retiro o, si no consta, colocación; tipo, ubicación, descripción y fechas originales |
| Seguimiento diario | `atenciones` con `tipo_atencion = SEGUIMIENTO_DIARIO` | Fecha de atención; motivo y nota registrada. No incorpora atenciones médicas de otros tipos |
| Pase de turno | `pases_turno` | Fecha de entrega; resumen, pendientes, vigilancia, recomendación, saliente, entrante y recepción |

Son **18 fuentes/secciones**, además de la opción «Reporte general». «Heridas y curaciones» abre ambas fuentes; «Curaciones» permite consultar solo procedimientos. El resumen cuenta cada fuente por separado para no duplicar lesiones.

Los registros anulados quedan fuera. Los valores ausentes no se convierten en cero ni en «No»; un cero o un falso realmente registrado sí se conserva. Se mantienen los estados pendientes. La cantidad de registros no se interpreta como gravedad ni como eventos clínicos únicos. No se copian fotos, adjuntos privados, contraseñas, códigos de PK/FK ni documentos de otras áreas al reporte.

Un dispositivo sin fecha de colocación ni retiro no se atribuye artificialmente a un período. El criterio temporal es el último de los dos momentos disponible: se exportan ambas fechas en el detalle, sin crear un historial de eventos que no exista en la fuente.

## Recorrido de uso

1. En el área correspondiente, pulsar **Generar reporte**. También se puede entrar desde **Reportes de enfermería**.
2. Elegir **Desde**, **Hasta**, **Residente** y **Sección**. El panel lateral permite cambiar de sección; en móvil se presenta en una cuadrícula desplazable. Las fechas incluyen ambos días completos.
3. Pulsar **Aplicar filtros**. El resumen y los enlaces de salida se basan en los filtros aplicados, no en cambios todavía pendientes de aplicar.
4. Consultar cantidades, desglose por sección, distribución diaria y detalle paginado de 20 filas.
5. Usar **Vista previa**, **Exportar PDF**, **Exportar CSV** o **Imprimir**. Los documentos incluyen todas las filas que coinciden, no solamente la página visible.

La vista previa tiene su propia barra de imprimir/PDF/CSV/modificar filtros. «Imprimir» abre una vista con los filtros y solicita el diálogo del navegador; sus controles se ocultan al imprimir. No guarda una firma ni envía el documento por correo.

## Diseño y componentes reutilizados

- Cabecera institucional `x-ui.collection-header`, tarjetas, campos, botones y tabla compartidos.
- Componente nuevo `x-ui.nursing-report-link`: un solo punto para los enlaces de las diferentes áreas.
- Selector de secciones separado de los filtros. Tres cifras principales: registros, secciones con registros y días con registros.
- Acciones de salida juntas, con icono y texto. Distribución diaria desplegable con alternativa numérica accesible; no introduce gráficas clínicas sin datos comparables.
- Desktop: navegación lateral de 220 px y contenido flexible. Tablet: navegación sobre el contenido. Móvil: dos columnas de secciones con desplazamiento y contenido en una columna; la tabla conserva desplazamiento horizontal dentro de su región.
- Colores y tipografías del Design System; variante oscura con superficie semántica corregida. Campos de al menos 44 px, foco visible y labels conectados. No hay animación decorativa nueva.
- PDF A4 horizontal, resumen vertical y tablas separadas por sección. Cabeceras de tabla repetidas y filas sin corte entre páginas cuando caben. Contenido textual completo, sin recortes a la página del navegador.

## Integración: ubicación de los botones

| Entrada | Ubicación |
|---|---|
| Central de cuidados | Cabecera general y cada tarjeta de cuidado, con residente y período actuales |
| Mis residentes / directorio por cuidado | Cabecera; formulario/detalle de un residente con el tipo seleccionado |
| Cognición y conducta | Barra de consulta de sus registros originales |
| Heridas | Sección de heridas del residente |
| Registros de cuidados y dispositivos | Cabecera, conservando la sección y residente seleccionados |
| Seguimiento diario | Cabecera; cambia a cognición/conducta cuando corresponde al filtro de esa pantalla |
| Tareas | Cabecera de cuidados programados |
| Medicación | Cabecera de administración |
| Incidentes | Cabecera, conservando las fechas de su filtro |
| Alertas | Cabecera de la entrada de Enfermería |
| Pase de turno | Cabecera junto a las vistas de transferencia e historial |

## Autorización, privacidad e integridad

Se mantienen los permisos instalados. El servidor comprueba sesión/cuenta ACTIVO, personal activo salvo lectura global de Superadministración, `enfermeria.ver_dashboard`, permiso de lectura de la fuente y residente dentro del alcance de `TurnoEnfermeriaService`.

Un reporte específico exige sus fuentes; no exige permisos de otra sección independiente. El reporte general exige las 18 fuentes: si falta permiso, se rechaza antes de presentar un documento parcial como completo. Heridas/curaciones exigen ambas lecturas. Los pases se acotan además con `PaseTurnoService::acotarConsultaPases`, sin ampliar sus reglas históricas.

PDF, CSV y la acción explícita Imprimir conservan el permiso de exportación ya utilizado: `enfermeria.ver_ficha_paciente`. La entrada `imprimir=1` se reautoriza en servidor. La consulta por residente enviado fuera del alcance devuelve denegación; no se convierte en «todos». Los parámetros manipulados como arrays se rechazan en la entrada del panel. La cuenta y los permisos se revalidan en cada solicitud de salida. Esto no pretende impedir la función de impresión propia del navegador de quien ya puede leer una página.

Los textos HTML se escapan con Blade. CSV usa UTF-8 con BOM, separador punto y coma y escape normal de comillas/saltos de línea. Los campos que podrían interpretarse como fórmula por comenzar con espacios y `=`, `+`, `-` o `@` reciben apóstrofo de protección. La descarga no ejecuta fórmulas ni transforma observaciones en contenido HTML.

Respuestas privadas `no-store`. Activitylog registra exportaciones exitosas y filtros técnicos; no copia notas o mediciones. No hay una nueva tabla de auditoría. El reporte y la impresión no modifican los registros originales ni habilitan prescripción o firma profesional.

## Verificación

Todas las pruebas usan datos sintéticos, SQLite de pruebas o un clúster PostgreSQL propio, con runner dedicado, puerto efímero y eliminación únicamente de su directorio temporal verificado. No se migró ni modificó la base institucional.

| Ejecución | Resultado real |
|---|---|
| Reporte + central de cuidados + seguimiento + módulo integral, SQLite | 36 pruebas correctas, 311 aserciones |
| Repetición final del reporte tras reforzar autorización de impresión | 17 pruebas correctas, 217 aserciones |
| Reporte + central de cuidados, PostgreSQL 18 desechable | 27 pruebas correctas, 264 aserciones |
| Repetición final de permiso de exportación/impresión y sesión en PostgreSQL | 1 prueba correcta, 12 aserciones; se solapa con la anterior, no se suma como un caso distinto |
| Navegador real con SQLite temporal | 1 recorrido correcto en 1440, 1280, 1024, 768 y 390 px, claro/oscuro |
| `npm run build` | Correcto; mantiene advertencia de chunks mayores de 500 kB |
| Pint sobre las clases PHP modificadas y `git diff --check` | Correctos |

Comandos reproducibles:

```powershell
php artisan test --filter='ReporteEnfermeriaPeriodoTest|CentralCuidadosTest|SeguimientoDiarioPersistenciaTest|ModuloEnfermeriaIntegralTest' --compact
php artisan test --filter=ReporteEnfermeriaPeriodoTest --compact
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'ReporteEnfermeriaPeriodoTest|CentralCuidadosTest' -Informe output/reporte-enfermeria-20261027/areas-postgres.junit.xml -Log output/reporte-enfermeria-20261027/areas-postgres.log
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'test_exportacion_revalida_su_permiso_y_no_admite_sesion_anonima' -Informe output/reporte-enfermeria-20261027/areas-permisos-postgres.junit.xml -Log output/reporte-enfermeria-20261027/areas-permisos-postgres.log
node --test tests/Frontend/reporte-enfermeria-e2e.test.js
npm run build
```

Las pruebas comprueban consulta después del cierre de turno, rango inclusivo, NULL/cero/falso, todas las nuevas fuentes, lesiones ajenas o anuladas, medicación PRN, separación de notas médicas, selección contextual desde cada área, escape HTML, CSV con fórmulas/comillas/saltos de línea, usuario/personal inactivos, permisos revocados, residente ajeno y denegación sin auditoría falsa de exportación.

El recorrido de navegador aplica filtros, comprueba ausencia de desbordamiento global, labels, foco y campos de 44 px, mide contraste de texto/superficie, abre el reporte desde Hidratación en la central, descarga CSV/PDF, comprueba la vista previa completa y conecta tanto el botón de impresión como la solicitud automática del diálogo. El modo print oculta la barra de acciones. No se mandan datos clínicos a servicios externos durante las pruebas.

La prueba de volumen conserva **1.200 filas** de signos en el CSV y realiza **14 consultas SQL**, sin consultas por cada fila. La medición local SQLite del ensayo fue aproximadamente **0,21 s** para consultar y producir el CSV; el pico de memoria del proceso de pruebas fue unos 146 MiB, incluyendo Laravel y PHPUnit, no una medición aislada del reporte. Los tiempos son evidencia local y no un SLA. El test también pasó en PostgreSQL, sin equiparar métricas de un motor con las del otro.

Capturas/documentos: `output/reporte-enfermeria-20261027/`. Se inspeccionaron móvil/escritorio y las páginas inicial/final renderizadas del PDF general sintético de 26 filas, con secciones separadas y sueño sin hora ficticia. El caso de controles conserva sus 23 filas al exportar. La revisión es acotada a esta pantalla/documento; no certifica WCAG integral ni todos los formularios clínicos compartidos.

### Batería ampliada y hallazgos fuera del reporte

Se ejecutó además:

```powershell
php artisan test --filter='ReporteEnfermeriaPeriodoTest|CentralCuidadosTest|RegistroConductaTest|IncidentesEnfermeriaTest|MisPacientesRedisenadaTest|PaseTurnoReconstruidoTest|TabMedicacionEnfermeriaTest' --compact
```

Resultado: **287 correctas y 5 fallidas, 2.472 aserciones**, en 626,40 s. No se presenta esa batería como aprobada. Fue una ejecución anterior al último refuerzo de impresión y a la prueba de volumen, repetidos después en las suites acotadas.

| Fallo | Evidencia |
|---|---|
| `IncidentesEnfermeriaTest::test_solicitar_valoracion_medica_crea_alerta_vinculada_y_evento` | El test espera alerta ACTIVA; la operación crea ABIERTA. No se alteró la regla ni la prueba para ocultarlo |
| `MisPacientesRedisenadaTest::test_ficha_rapida_muestra_acciones_en_footer_y_un_solo_drawer` | Espera el literal de trap `x-trap.noscroll="show"` ausente en el HTML compartido |
| `MisPacientesRedisenadaTest::test_modal_de_registro_declara_medidas_responsivas_y_foco_accesible` | Mismo literal de trap esperado; requiere revisar el contrato del modal compartido |
| `TabMedicacionEnfermeriaTest::test_omision_conserva_motivo_en_trazabilidad` | La operación exige un horario concreto/vigente de prescripción |
| `TabMedicacionEnfermeriaTest::test_dosis_administrada_no_sobrescribe_dosis_prescrita` | Mismo requisito de horario |

Los fallos pertenecen a operaciones de alertas/administración o al contrato de modales compartidos, cuya lógica no se cambió para crear reportes. No se ha realizado aquí una comparación aislada con una revisión anterior para atribuirles origen. No se declara todo Enfermería terminado. Los dos fallos de agenda del informe anterior tampoco se dan por resueltos ni se suman como si se hubieran reejecutado en esta batería.

## Archivos de implementación

- `app/Modulos/Reportes/Aplicacion/Consultas/ConsultarReporteEnfermeria.php`: dieciocho fuentes, permisos, rango, scope y filas comunes.
- `app/Modulos/Cuidados/Aplicacion/Consultas/ConsultaCentralCuidados.php`: extracción reutilizable del formato nominal de registros.
- `app/Modulos/Reportes/Presentacion/Livewire/Paginas/ReporteEnfermeria.php`: entrada contextual, filtros aplicados y selección de sección.
- `app/Modulos/Reportes/Presentacion/Http/Controllers/ReporteEnfermeriaController.php`: vista previa, PDF, CSV y autorización de impresión.
- `app/Modulos/Reportes/Presentacion/Rutas/web.php`: ruta `admin.enfermeria.reportes.csv`, `/admin/enfermeria/reportes/csv`; conserva las rutas anteriores.
- `app/Modulos/Reportes/Presentacion/Vistas/livewire/cuidados/reporte-enfermeria.blade.php`: nueva distribución de la pantalla.
- `app/Modulos/Reportes/Presentacion/Vistas/reportes/enfermeria-resumen.blade.php`: documento agrupado y barra de impresión/exportación.
- `app/Modulos/Reportes/Presentacion/Vistas/reportes/enfermeria-registros.blade.php`: tabla compartida, fecha sin hora ficticia.
- `resources/views/components/ui/nursing-report-link.blade.php`: botón contextual reutilizable.
- `resources/frontend/styles/design-system/patterns/nursing-reports.css`: composición responsive y estados de sección.
- `resources/frontend/scripts/modules/nursing-report-print.js` y `vite.config.js`: entrada ligera e independiente para imprimir la vista previa, sin cargar el bundle clínico completo.
- Botones en las vistas de Cuidados: `central-cuidados`, `directorio/cabecera`, `directorio/registro`, `observacion-cognitiva`, `registro-conducta`, `registros-enfermeria`, `seguimiento-diario-panel`, `tareas-plan-panel`, `pase-turno-panel` y `partials/heridas-curaciones`, bajo `app/Modulos/Cuidados/Presentacion/Vistas/livewire/cuidados/`.
- `app/Modulos/Medicacion/Presentacion/Vistas/livewire/medicacion/salud-administracion-medicacion.blade.php`: acceso de Medicación.
- `app/Modulos/Alertas/Presentacion/Vistas/livewire/cuidados/partials/incidentes-listado.blade.php` y `app/Modulos/Alertas/Presentacion/Vistas/livewire/alertas/alertas-panel.blade.php`: accesos de Incidentes/Alertas.
- `tests/Feature/ReporteEnfermeriaPeriodoTest.php`, `tests/Frontend/reporte-enfermeria-e2e.test.js`, `tests/Support/ServidorReporteEnfermeriaSintetico.php`: pruebas y entorno aislado.

Las modificaciones simultáneas de administración, avisos, correo o estructura detectadas en el árbol no forman parte de esta entrega y se conservaron.

## Límites que se conservan

- Pantalla, vista previa, CSV y PDF consultan datos al solicitarse; si cambia un registro entre solicitudes puede cambiar el contenido. No constituyen una instantánea histórica firmada.
- El CSV representa una fila por registro y conserva sus campos nominales legibles en «Detalle»; no es un volcado SQL ni incluye PK/FK. La hora de sueño se deja vacía.
- La exportación materializa filas y tiene coste proporcional al período. Una prueba de volumen acotada no es una garantía de capacidad ilimitada ni sustituye una exportación masiva asíncrona.
- No se extiende esta entrega a reportes de Medicina, Psicología o Gerencia. Tampoco cambia reglas de asignación o permisos clínicos para resolver fallos ajenos al reporte.
- No se ejecutaron commit, push, cambio de rama ni despliegue en esta tarea. Se preserva el trabajo simultáneo del árbol.
