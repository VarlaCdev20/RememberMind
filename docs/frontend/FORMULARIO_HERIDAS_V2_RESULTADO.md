---
title: "Heridas / Curaciones V2 — resultado y verificación"
status: CURRENT
version: "1.3"
last_reviewed: 2026-10-13
owner: RememberMind
source_of_truth: false
verification_scope: SCOPED_AUTOMATED_TESTS_AND_ISOLATED_VISUAL_RUNTIME
runtime_verified: true
---

# HERIDAS / CURACIONES V2 — RESULTADO

Estado: correcciones backend de cierre y transporte fotográfico verificadas el 13/10/2026, según el cierre al final. La prueba HTTP del límite de 8 MB ya aprobó; revisión visual de los últimos desplegables e ilustraciones pendiente por acceso al navegador. El lote visual de referencia anterior corresponde al 12/10/2026. Rama conservada: `feature/institucion-asignaciones-planificadas`. Commit: **NONE**. No se hizo stage, push, PR nuevo ni merge para esta entrega. Los apartados originales siguientes registran evidencias históricas y no sustituyen el resultado actualizado del cierre.

## SOURCE OF TRUTH

- Área: evidencia fotográfica de heridas y curaciones, sin interpretación clínica automática.
- Autoridad: instrucción explícita de la propietaria del 11/10/2026; [extensión aprobada](../base-de-datos/DECISION_FOTOGRAFIAS_HERIDAS_V2.md).
- Vigentes: [baseline](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [roles](../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md), [contrato visual](CONTRATO_VISUAL_UX_UI.md), AGENTS de app/database/resources/tests.
- Código: `DocumentoClinico`, `Herida`, `CuracionHerida`, `RegistrarCuidadosDeTurno`, `LesionesEnfermeriaService`, `GestionarCuidadosHttp`, `RegistrosEnfermeria`, `DocumentoHeridaPolicy`, `FotografiaHeridaController` y contrato `ArchivosAplicacion`.
- Conflicto resuelto: el baseline no tenía relaciones documentales para heridas. La autorización actual modifica solo dos columnas y sus FK/índices; no autoriza otras tablas, columnas o permisos.
- Historia: no se reutilizan arquitectura Backend/Frontend, campos V1, esquemas corporales ni fotografías ficticias en la instalación operativa.
- Referencia visual del lote original: no se adjuntó imagen. El lote del 12/10/2026 sí revisó la referencia recibida, según el análisis enlazado al final.

## Schema

New tables: **NONE**. `documentos_clinicos` añade únicamente `cod_herida` y `cod_curacion`, nullable `string(20)`, indexadas, FK RESTRICT. No hay cambios estructurales en heridas o curaciones. Documentos generales anteriores conservados, ambas referencias nulas.

## Migration

SQLite: **PASS**. PostgreSQL: **PASS**. Rollback y reaplicación: **PASS**, sobre bases desechables. Se verifican columnas exactas, inventario estable, índices, FK físicas y preservación de documentos generales. Migración local aplicada exclusivamente por su ruta; no se ejecutó rollback local ni reset de datos reales.

## Storage

Private: **PASS**. Public URL: **NONE** para evidencia nueva. Las pruebas verifican disco público vacío, nombres UUID y entrega autorizada con headers privados. Una fila por fotografía; no BLOB/JSON ni lista de rutas en una fila. El servidor calcula hash SHA-256, tamaño, formato, fecha y autor.

## Upload

JPG/JPEG: **PASS**. PNG: **PASS**. WEBP: **PASS**. SVG/GIF/PDF y extensión falsa: **RECHAZADOS**. Invalid MIME: **PASS**. Oversize: **PASS** en validación backend. Cantidades 1, 2 y 5: **PASS**. Seis archivos: **RECHAZADOS**. Se verifican compensación por fallo parcial de almacenamiento y metadata, fallo SQL real de FK después de almacenar, rollback de registros/documentos/auditoría y ausencia de archivos persistentes huérfanos en esos escenarios. Los tres fallos aprueban en SQLite; el fallo SQL también aprueba en PostgreSQL.

Carga HTTP de 8 MB: pendiente en el lote original; **PASS el 13/10/2026** mediante transporte HTTP real, según el cierre al final. El límite global de PHP conserva 2M por archivo y 8M por POST; el arranque local del proyecto ahora utiliza límites por proceso de 8M/45M. No se acredita el transporte mediante `UploadedFile::fake()`.

## Relations

Wound: **PASS**. Curation: **PASS**. Mutual exclusion: **PASS** en aplicación/modelo. Same resident: **PASS**. Referencias inexistentes: **RECHAZADAS**. FK restrictivas: **PASS** en SQLite y PostgreSQL. No se afirma un CHECK SQL de exclusión o residente.

## Gallery

Current/history: **PASS** en relaciones y renderización Livewire; los documentos anteriores permanecen intactos. No se filtra contenido de otro residente. Las URLs contienen el código de documento y residente, nunca la ruta física.

Viewer/compare: **IMPLEMENTADOS; QA VISUAL PENDIENTE**. Visor privado con imagen completa, metadatos, anterior/siguiente, Escape/flechas, confinamiento y devolución de foco. Comparación disponible desde dos fotografías, lado a lado en escritorio y apilada en móvil. No emite diagnóstico ni recomendación.

## Security

Authorization: **PASS**. Scope: **PASS**. Private stream/download: **PASS**. Cuenta inactiva, permiso ausente, personal inactivo, asignación ausente, herida cerrada, residente cruzado y competencia insuficiente rechazan escritura sin registros ni archivos. Familia/administración, documento anulado y residente fuera de alcance no acceden al contenido. Superadministración consulta, pero el rol aislado no concede adjuntar evidencia. El inicio de carga temporal también verifica Policy y contexto antes de emitir URL de subida.

## UX, dirección visual y continuidad

Se reutilizan `ui.modal-livewire`, `ui.field`, `ui.section-card`, estilos de formularios, botones y tokens existentes. Layout objetivo 68/32, una columna hasta 768 px; identidad terracotta acotada a heridas, acciones sage existentes y superficies neutrales. Las dimensiones usan controles de 46 px, step 0.01 y cm. El área es derivada, no persistida; la gráfica usa solo medidas históricas válidas.

Selector de heridas, master de solo lectura, nueva herida con fotos, valoración, evidencia, procedimiento, materiales, respuesta, observación, último registro e historial están conectados a la persistencia actual. Se preserva el cierre documentado. Sin esquema corporal, borrador ficticio, reemplazo de historia ni interpretación automática.

Las cargas muestran progreso real y recuperación; archivos temporales se previsualizan mediante URLs locales del navegador. Reemplazar una selección fallida conserva la anterior. Campos y fotografías forman parte del estado sin guardar. X, Cancelar, Escape, backdrop, navegación y salida protegen captura pendiente. Los mensajes de éxito proceden exclusivamente de persistencia confirmada. Las pruebas JavaScript cubren fallos, retorno de foco, límites de galería, salida pendiente y valores derivados finitos.

Motion: se reutiliza el modal con tokens existentes; no se añaden animaciones decorativas ni tiempos nuevos. Visor y comparación permanecen operables sin depender de animación. La verificación real con movimiento reducido queda pendiente junto con la revisión visual.

## Responsive

1440: **PENDIENTE**. 1280: **PENDIENTE**. 1024: **PENDIENTE**. 768: **PENDIENTE**. 390: **PENDIENTE**.

Los breakpoints están implementados, pero clases y build no prueban ausencia de overflow, contraste, foco visible o lectura en cada ancho. Dictamen `remembermind-visual-functional-qa`: **FAIL por evidencia requerida incompleta**, sin atribuir defectos visuales no observados. Lectura local de la sección Heridas verificada sin error 500; esa comprobación no cubre formulario con fotografías.

La revisión automática rechazó preparar y arrancar un servidor QA con datos sintéticos y solo indicó `blocked by policy`. No se eludió ese rechazo ni se crearon registros clínicos reales para suplir QA. No hay capturas sintéticas nuevas de esta entrega.

## Tests y build

- Regresión focalizada SQLite: 69 pruebas, 1028 aserciones aprobadas; controles, historial, seguridad, incidentes y frontera de aplicación.
- Negativos y lotes adicionales SQLite: 9 pruebas, 34 aserciones aprobadas.
- PostgreSQL final: 37 pruebas, 229 aserciones aprobadas; fotografías, relaciones, autorización, almacenamiento, migración, reversión y selección contextual.
- Fallo SQL posterior a almacenamiento: una prueba adicional PostgreSQL con cinco aserciones aprobadas. Los tres escenarios de compensación aprobaron en SQLite con 15 aserciones.
- Componente final SQLite: 3 pruebas, 29 aserciones aprobadas; captura ante error, lotes y cambio de herida. Tras consolidar la autorización de galería para evitar consultas por fotografía, 12 pruebas de acceso/renderización aprobaron con 112 aserciones.
- Frontend final: 159 pruebas aprobadas, incluido el caso de área finita.
- Pint: aplicado a los archivos PHP afectados.
- Build final: aprobado, Vite 8.3.1, 3.01 segundos.
- Suite completa: 1138 pruebas ejecutadas y 32368 aserciones, con 15 omitidas según perfil; la primera corrida terminó con dos contratos obsoletos y un error de archivo simulado. El contrato de rutas pasó de 296 a 297 con una única entrada nueva; la comparación íntegra de las anteriores se mantiene y su repetición aprueba. El test de rollback experto ahora retira cuatro extensiones posteriores antes de las mismas 23 migraciones expertas; su repetición aprueba con 60 aserciones. El error histórico `Archivo clínico ausente o alterado` coincidió con pruebas concurrentes que compartían `Storage::fake('local')`; la repetición de los tres grupos con almacenamiento aislado aprobó: **15 pruebas y 4416 aserciones**, sin errores ni fallos. No se presenta la primera corrida integral como verde ni se afirma una segunda corrida integral; se acredita la repetición de todos sus grupos fallidos y las baterías finales del cambio.

Evidencias locales de esta sesión: `wound-full-suite.xml`, `wound-failed-groups-rerun.xml`, `wound-pg-final.xml`, `wound-pg-db-failure.xml`, `wound-frontend-final.log` y `wound-build-final.log`, en el directorio de outputs de la conversación; contienen datos sintéticos. El clúster PostgreSQL desechable quedó detenido. La instalación operativa no recibió fotografías ni heridas de prueba.

Fuentes técnicas contrastadas: [Livewire 4 — uploads](https://livewire.laravel.com/docs/4.x/uploads), [Laravel — almacenamiento](https://laravel.com/framework/docs/filesystem) y código instalado. No son fuentes de reglas clínicas.

## Commit

### Ajuste solicitado: formulario emergente

Nueva herida, curación y cierre reutilizan `x-ui.modal-livewire`, con cabecera, contexto del residente/profesional, cuerpo desplazable y acciones en el pie. El título identifica la operación. Nueva herida y cierre ocupan una columna; la curación conserva la continuidad lateral. La entrada inicial recibe foco sin competir con el foco automático del componente compartido; los demás modales conservan su comportamiento predeterminado. La captura abierta no se reinicia al repetir su disparador y el cierre mantiene la protección de cambios y cargas pendientes.

Verificación local de esta corrección: 161 pruebas frontend aprobadas; después de ajustar el alcance del foco a `$root`, las ocho pruebas del módulo aprobaron nuevamente. Tres pruebas Livewire con 29 aserciones y build final aprobado. En el navegador se comprobó Nueva herida: apertura emergente, título, campo inicial enfocado, continuidad ajena oculta, cierre mediante Cancelar y Escape, y foco devuelto al disparador. Solo se abrió y cerró el formulario vacío; no se guardó información clínica operativa. Evidencia recortada a la cabecera, sin datos personales: `heridas-modal-cabecera.jpg` en outputs de la conversación. No sustituye la QA pendiente de fotografías, comparación ni de los cinco anchos.

**NONE**, por instrucción explícita. Cambios permanecen sin commit en la rama actual. No se continúa con Medicación antes de revisión.

### Corrección de los accesos de Registrar

La comprobación anterior solo acreditaba el modal dentro de la página de registros. El botón «Heridas / Curaciones» del selector del directorio seguía siendo un enlace a esa página y el mismo botón de la ficha clínica solo cambiaba de pestaña. Ambos accesos ahora montan el mismo componente de heridas en modalidad emergente, dentro de la pantalla actual. El componente autoriza residente, competencia y permiso antes de abrir; selecciona una herida activa para curación o la identificación de nueva herida si no existe ninguna activa. Permite identificar otra herida con confirmación de descarte. Cancelar devuelve al selector y el éxito permanece visible en el pop-up hasta cerrarlo. Se conserva el formulario y la persistencia únicos, sin duplicar operaciones clínicas.

La propietaria confirmó que el acceso concreto es el directorio con un residente seleccionado. Tras pedir el mismo comportamiento de Signos vitales, Heridas utiliza también `x-ui.quick-register-modal`: ventana centrada del registro rápido, cabecera con icono, opción Volver al selector, contexto, cuerpo desplazable y pie de acciones. Su apertura usa el estado Livewire `mostrarFormularioHerida`, activado al montar el formulario autorizado y sincronizado al abrir, cerrar, descartar o confirmar. El componente compartido conserva los valores predeterminados de los otros formularios; solo acepta cierres y regreso Alpine cuando un formulario necesita proteger fotografías y captura pendientes.

Verificación de esta unificación: 10 pruebas de selector, permisos, heridas y ficha aprobaron con 125 aserciones; 163 pruebas frontend, los diez casos de interacción de heridas, Pint y build aprobaron. La revisión del grupo amplio detectó una expectativa obsoleta de enlace y la pérdida de un permiso adicional de visibilidad al cambiar ese enlace por botón. Se actualizó la expectativa al nuevo acceso y se restituyó `atenciones.ver` en el selector; las pruebas de permisos y alerta crítica siguen aprobando. La sesión de navegador controlable permanece en supervisión, por lo que no se afirma una comprobación visual de clic en Registrar con Enfermería ni se modifica la autenticación para forzarla.

Verificación de esta corrección: 17 pruebas de navegación y formularios aprobadas, con 88 aserciones; 163 pruebas frontend y build aprobados. La corrida anterior de fotografías, navegación y botones de ficha aprobó 57 casos y falló en una expectativa de enlace que correspondía al acceso sustituido por el botón; esa expectativa se actualizó al nuevo flujo y el grupo completo de navegación aprobó en la repetición. Vistas compiladas limpiadas para servir la versión actual. En el navegador disponible, la sesión de supervisión no ofrece Registrar en el directorio; no se cambió su rol ni se añadieron asignaciones para obtener una captura. La apertura desde los selectores queda acreditada por pruebas Livewire y de interacción JavaScript, pendiente de comprobación visual con una sesión clínica autorizada. No se reutiliza la captura de la cabecera anterior como prueba de estos nuevos accesos.

## Cierre del lote visual de referencia — 12/10/2026

### Acceso sin heridas activas

Comprobado el acceso real desde «Mis residentes → Registrar → Heridas / Curaciones» en el servidor local operativo. La apertura depende del estado real: una herida activa abre curación; sin heridas activas abre identificación inicial. Este segundo formulario no muestra valoración de curación, evolución ni historial ficticios. Se añadió un aviso informativo que explica el siguiente paso «Registrar primera curación», disponible tras identificar y guardar una herida real. No se crearon registros operativos para mostrar el diseño. Regresión de las dos aperturas, regreso al selector y transición a primera curación: 3 pruebas, 34 aserciones aprobadas; build aprobado.

### Ayudas de captura y aviso reforzado — 12/10/2026

Por solicitud explícita, el aviso informativo incorpora mayor contraste azul, borde lateral, icono destacado y dos pasos. No representa una alerta clínica. Tipo, ubicación, clasificación y causa usan el componente reutilizable `x-ui.free-text-select`: 13 tipos, 38 ubicaciones, siete descripciones de clasificación y 15 causas como ayudas de escritura, más «Otro / escribir» en cada campo. Los opcionales pueden quedar sin indicar. «Otro» despliega un campo de texto con el límite original y foco automático; el centinela de UI nunca se envía como dato. Se conservan texto previo, validación backend, permisos, autoría y estado pendiente. El grupo inicial se deshabilita durante curación para que sus controles requeridos ocultos no bloqueen esa operación.

No se añaden catálogos institucionales, tablas, campos, reglas, inferencias ni clasificación automática. La selección corresponde al profesional. Los estadios de presión se identifican expresamente y no se presentan como clasificación universal: referencia de terminología [NHS — categorías de lesiones por presión](https://www.cntw.nhs.uk/resources/pressure-ulcers-patient-information-leaflet). Tipos de heridas contrastados con [NHS — guía de cuidados de heridas](https://doclibrary-rcht.cornwall.nhs.uk/DocumentsLibrary/RoyalCornwallHospitalsTrust/Clinical/CorporateClinical/WoundCareClinicalGuideline.pdf). Estas fuentes no definen un protocolo institucional nuevo.

JavaScript: 170 pruebas aprobadas, tres nuevas sobre selección/escritura, recuperación y reinicio. Heridas: 44 pruebas y 279 aserciones aprobadas. Build final aprobado. La nueva revisión visual e interacción en navegador está pendiente: el control de navegador rechazó acceder a la pestaña por restricción de protocolo; se solicitó captura a la propietaria sin guardar registros. La propietaria confirmó la actualización, pero todavía no se recibió una captura adjunta para esta revisión. El PASS visual anterior no se atribuye a esta última revisión de desplegables. Sin stage, commit ni push.

### Corrección de Confirmar y registrar — 12/10/2026

BUG_PRODUCT: al identificar una nueva herida, `x-show` ocultaba la valoración y el procedimiento de curación, pero sus campos `required` seguían participando en la validación nativa. El navegador podía impedir el envío antes de invocar Livewire. Las tres secciones de curación ahora son grupos `fieldset` deshabilitados durante identificación; al pasar a curación se habilitan y mantienen sus requisitos. El grupo de identificación conserva la exclusión inversa. Se mantienen los estilos, el formulario único y la validación backend, sin cambiar permisos, reglas ni persistencia.

Regresión del contrato HTML compilado: solo participan los obligatorios de la operación seleccionada; no se presenta como ejecución del navegador. Regresión Livewire: rechaza tipo/ubicación vacíos sin fila ni evento de éxito, conserva el formulario y permite reintentar con esos datos sin exigir ni crear una curación. Verificación: `php artisan test tests/Feature/FotografiasHeridasTest.php` PASS (46 pruebas, 296 aserciones), `npm test` PASS (170), `npm run build` PASS y Pint PASS. Vistas compiladas limpiadas. Comprobación de clic real pendiente por el bloqueo del control de navegador descrito arriba. No se guardaron registros en la instalación operativa. Sin stage, commit ni push.

### Pulido de Identificar la herida — 12/10/2026

Ajuste visual solicitado exclusivamente para la identificación inicial: título y paso con mayor jerarquía, tipo/ubicación al principio, separación visible de información complementaria opcional, controles claros de 48 px, iconos menta y azul, texto con mayor contraste, campo «Otro» con superficie informativa y observación a todo el ancho. Se reutilizan `free-text-select` y `field`, con estilos del patrón de heridas y tokens existentes. El badge de registro inicial se oculta en móvil para priorizar el título. Se mantienen una columna hasta 768 px, errores visibles y la exclusión de campos de curación durante identificación. No se cambian opciones, validaciones, permisos, BDD ni los otros formularios.

Verificación de este ajuste: cuatro pruebas PHP (41 aserciones), 17 pruebas frontend y build aprobados; vistas compiladas limpiadas. La prueba de pantalla en navegador sigue pendiente por la restricción de acceso ya documentada; no se atribuye el PASS visual histórico al ajuste actual. Sin stage, commit ni push.

### Opciones ilustradas de identificación — 12/10/2026

Tipo y ubicación mantienen los desplegables y los mismos valores de captura, y añaden un panel nativo desplegable «Ver opciones ilustradas»: 13 pictogramas de tipos y 38 esquemas corporales con marcador, vista frontal/posterior y etiqueta completa. Son esquemas de orientación, no fotografías, imágenes de lesiones reales ni criterios diagnósticos. No se representan estadios ni se deduce una clasificación. SVG propios y locales; sin datos clínicos ni conexiones externas. La ilustración pertenece a Presentación de Cuidados y se registra en el mapa de vistas existente; el contenedor, búsqueda y selección reutilizan `free-text-select` mediante un slot opcional. Los otros consumidores conservan su presentación.

La búsqueda tolera acentos y varios términos, sin cambiar la captura. Elegir una tarjeta conserva el texto canónico, marca cambios pendientes, cierra el panel y devuelve foco al desplegable; «Otro / escribir» enfoca la descripción libre. Escape cierra el panel sin salir del formulario. No se reemplaza la validación nativa/backend. Se conserva el registro de herida separado de la curación, y los campos ocultos permanecen deshabilitados. Panel con desplazamiento y tarjetas de una columna en móvil, sin portales ni overlays adicionales.

Verificación: cinco pruebas PHP PASS (52 aserciones), `npm test` PASS (173), build PASS y Pint PASS. La primera comprobación de render detectó que faltaba registrar la vista ilustrada en `vistas_modulares`; se corrigió y las pruebas se repitieron satisfactoriamente. QA visual/runtime pendiente por el bloqueo de navegador documentado; no se declara PASS visual nuevo. Sin cambios BDD, permisos ni reglas clínicas; sin stage, commit ni push.

**Visual Fidelity: PASS** para modal, header, contexto del residente, resumen, valoración, foto, procedimiento, columna lateral, gráfica, historial y footer. Análisis, adaptaciones, capturas y límites en [HERIDAS_CURACIONES_REFERENCE_ANALYSIS.md](HERIDAS_CURACIONES_REFERENCE_ANALYSIS.md).

- Composición A/B/C y seguimiento lateral; 66/34 en 1440, 64/36 en 1280; cuerpo desplazable y footer visible. Texto libre preservado; chips como ayudas de escritura. No se alteran los demás formularios ni el sidebar.
- Corregidos selector que marcaba cambios falsos, error de carga antiguo tras reintento válido, estilos globales que ampliaban filas, contexto profesional comprimido en tablet/móvil y overlays de historial/fotos que quedaban detrás del formulario. Los diálogos anidados mantienen foco dentro del contexto activo.
- Historial y visor privados, comparación, captura pendiente, confirmación de descarte, EVA por teclado, retorno de foco y movimiento reducido real comprobados en navegador sobre una base QA aislada. Cero/un histórico sin tendencia inventada; comparación inicial/última con divisiones seguras. Sin literales null/NaN/undefined/Infinity/[object Object] visibles.
- **Responsive: PASS** en 1440×900, 1280×900, 1024×900, 768×900 y 390×844 reales en CSS px. Sin desbordamiento horizontal del cuerpo; historial apilado en móvil.
- **Accessibility: PASS en el alcance revisado**: teclado, foco, labels, controles, reflow, contraste medido y movimiento reducido. No certificación WCAG ni de producción.
- **Body diagram: ABSENT**. Sin piel perilesional/signos de infección estructurados, siguiente curación sugerida, borrador ficticio, diagnóstico, recomendación ni estado automático de mejoría. Identidad sin imagen disponible usa iniciales, no una fotografía inventada.
- **Clinical rules invented: NONE. Database changes: NONE en este lote**. La extensión fotográfica previa aprobada se conserva sin modificar. La variación del área solo se calcula para presentación.
- **Tests:** Heridas 44 PASS/276 aserciones; proyección 3 PASS/13; repetición final de proyección y tres regresiones Livewire 6 PASS/37. La corrida previa de Heridas y navegación pasó 55/324. JavaScript 167 PASS. Pint de proyección/test PASS y `git diff --check` sin errores de whitespace.
- **Build: PASS**. Evidencias ignoradas en `storage/app/qa/wound-reference/`. El guardado sintético no modifica la instalación operativa. No se repitió la suite PHP global ni PostgreSQL para este lote de presentación; los resultados históricos anteriores no se presentan como nuevas ejecuciones.
- **Pendiente anterior resuelto por el lote backend del 13/10/2026:** prueba HTTP de 8 MB, detallada abajo. Este cierre visual histórico no la sustituye.
- **Caveman Cloud:** herramientas/CLI no disponibles, sin métricas atribuidas.
- **Commit: NONE**. Sin stage ni push. Medicación permanece fuera del alcance.

## Cierre backend de Heridas / Curaciones — 13/10/2026

**Resultado de las correcciones solicitadas: VERIFIED_RUNTIME.** Alcance: cierre del formulario, concurrencia sobre la misma herida, transporte real de fotografías al límite y regresión del flujo. No equivale a aceptación clínica de catálogos/reglas, certificación de producción ni aceptación visual de las últimas ilustraciones.

### Cierre único y conservación de historia

`RegistrarCuidadosDeTurno::cerrarLesion`, invocado por `RegistrosEnfermeria`, ahora bloquea la herida dentro de `DB::transaction`. Filtra por residente, reautoriza cuenta/permiso/turno/asignación después de obtener el bloqueo y comprueba que continúe ACTIVA. Solo entonces añade el cierre a la observación existente y registra estado/fecha. Un cierre repetido devuelve un error de validación en `resultadoCierreLesion`, conserva la captura y no emite éxito. Se mantiene `atenciones.crear`, conforme al contrato vigente de continuidad; no se inventa un permiso nuevo. Curaciones anteriores y evidencia permanecen intactas.

Prueba PostgreSQL con dos procesos independientes y barrera: ambas conexiones se observaron esperando el lock de la misma fila, luego una cerró y la otra fue rechazada. Resultado único, fecha conservada, una sola anotación de cierre y texto inicial intacto. La prueba ejercita la acción real del formulario, a diferencia del test histórico del otro servicio.

### Transporte HTTP y límites locales

Prueba automatizada con servidor PHP HTTP aislado, SQLite temporal propia y PNG sintéticos válidos de 8.388.608 bytes: cinco archivos enviados en una petición multipart al endpoint firmado real de Livewire, con sesión y CSRF reales. Verifica las cinco referencias temporales firmadas y pasa los archivos recibidos a la acción real de curación; esta fase de persistencia utiliza la DB de prueba del runner y discos privados de prueba. Comprueba tamaños, hashes, metadatos, vínculo a curación y ausencia de archivos públicos. No presenta una llamada directa a la acción como confirmación HTTP del formulario completo. Se rechazan CSRF ausente (419), firma ausente (401) y archivo de 8 MB + un byte (422); no se agrega evidencia al rechazarlo. No se retiró middleware ni se desactivó CSRF para aprobar.

`composer dev` utiliza `php scripts/servir-local.php`: servidor loopback, límites por proceso `upload_max_filesize=8M` y `post_max_size=45M`. También admite puerto explícito: `php scripts/servir-local.php 8000`. No modifica `.env`, el `php.ini` global ni las dependencias. Se reinició exclusivamente el proceso PHP verificado del proyecto en 8000; `/up` respondió 200 y se verificaron ambos flags del proceso activo. Otros despliegues deben configurar sus propios límites PHP/proxy. No se cargaron fotografías ni se crearon registros en la DB operativa.

### Gates ejecutados

- SQLite: `php artisan test tests/Feature/FotografiasHeridasTest.php tests/Feature/SeguridadCriticaEnfermeriaTest.php tests/Feature/EnfermeriaV2CorreccionCriticaTest.php` — **80 PASS / 1.166 aserciones**.
- PostgreSQL desechable: `scripts/probar-postgres.ps1 -Filtro 'FotografiasHeridasTest|CierreHeridaConcurrenciaPostgresTest'` — **51 PASS / 391 aserciones**. Cluster detenido y eliminado por el runner seguro. Incluye la prueba de concurrencia real y la de transporte HTTP aislado.
- Prueba HTTP inicial aislada: **1 PASS / 44 aserciones**; forma parte de ambas corridas anteriores. Los intentos iniciales fallaron por sesión/CSRF temporal y por no extraer la firma de la referencia Livewire; se corrigió únicamente el harness manteniendo las protecciones.
- Pint, sintaxis de helpers/launcher y `composer validate --strict --no-check-publish`: **PASS**.
- Frontend/build: sin cambios en este lote backend. Gates anteriores de ilustraciones: 173 JS PASS y build PASS; no se contabilizan como una nueva ejecución.

Evidencias ignoradas: `storage/app/qa/wound-reference/backend-closure-php.txt`, `backend-closure-pg.txt`, `backend-closure-pg.xml`, `http8mb.txt` y `local-server-limits-proof.json`. BDD/FK/migraciones/permisos/reglas clínicas: sin cambios en este cierre. Sin stage, commit ni push, por instrucción vigente. La revisión visual/runtime de los últimos paneles ilustrados sigue pendiente por bloqueo del control de navegador; el cierre backend no le atribuye un PASS.
