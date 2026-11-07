# Documentación pendiente dentro del expediente personal

## Alcance y fuentes

Instrucción de la propietaria del 06/11/2026: ver dentro de cada carpeta los documentos que faltan entregar y su plazo, marcados como pendientes. Se conserva el diseño neutral y compacto aprobado para las carpetas por trabajador.

Contrato vigente: decisión `DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md`, solicitudes documentales persistidas y proyección `PlazoExpedienteDocumental`. El catálogo de tipos no crea obligaciones automáticamente. La fecha vigente procede del registro original y de su concesión cuando corresponde; no se recalcula desde la fecha de consulta. Los informes anteriores conservan su corte.

## Integración

- Documentación → Archivos del personal → carpeta del trabajador: sección «Pendientes de entrega» con documento solicitado, fecha límite vigente, aviso existente y «Ver seguimiento».
- La lista reutiliza `ConsultarDocumentacionPersonal::situacion`, incluyendo correcciones, anulaciones y conflictos de plazo. Una entrega CARGADO/PENDIENTE de revisión o VALIDADO no se presenta como faltante.
- El seguimiento conserva el expediente abierto y permite volver a él. La lista tiene paginación independiente de los archivos y no queda oculta por filtros de estado/formato de archivos.
- La raíz incluye titulares con solicitudes consultables aunque tengan cero archivos. Los filtros de persona, rol y búsqueda conservan su alcance; estado/formato siguen describiendo archivos existentes.
- Sin pendientes se informa «No hay solicitudes pendientes de entrega», sin afirmar que todo el expediente esté validado. Sin la ampliación instalada se informa la indisponibilidad del seguimiento y siguen disponibles los archivos originales.

## Seguridad y persistencia

Solo lectura adicional: no se crean obligaciones, entregas, fechas, prórrogas ni auditorías al consultar. Sin cambios de BDD, permisos, rutas o dependencias. Se conserva el almacenamiento privado y la autorización de archivos existente.

Las solicitudes de carpetas se restringen al titular autorizado de la biblioteca: trabajador propio, Gerencia en su alcance y Superadministración según su lectura existente. Se verifica de nuevo la carpeta al abrirla; el botón de seguimiento comprueba que la solicitud pertenezca a ese expediente. Los identificadores de selección permanecen bloqueados en Livewire.

## Límites

Los pendientes representan solicitudes registradas; no se deducen obligaciones históricas por ausencia de archivos. La proyección de pendientes carga las solicitudes del trabajador seleccionado con sus entregas y prepara las concesiones en lote antes de paginar la presentación, evitando consultas por cada fila.

## Verificación del cambio

- SQLite y PostgreSQL desechable: **34 pruebas / 314 aserciones aprobadas por motor**, conjunto `DocumentacionPersonalArchivosTest|GerenciaSolicitudesDocumentalesTest`.
- Nuevos casos: carpeta sin archivos, fecha vigente, prórroga compartida sin duplicar su acto, separación entre entrega y revisión, correcciones/anulaciones/reemplazos, seguimiento dentro de la carpeta y rechazo de solicitudes ajenas/cuentas mixtas fuera de alcance. Las consultas no crean documentos ni entregas.
- E2E aprobado en 1440/1280/1024/768/390px: pendiente visible con fecha, recibido excluido de faltantes, expediente por persona, miniatura privada, visor, filtros y retorno de foco. Sin errores JavaScript ni desbordamiento horizontal. Capturas actuales inspeccionadas; aceptación acotada a la sección añadida y al recorrido existente ejercitado, no una auditoría global de accesibilidad o rediseño de la tabla.
- Build Vite aprobado. No se ejecutó CI completa ni se modificó el esquema operativo.

Evidencia local: `output/documentacion-pendientes-{sqlite,postgres,build,e2e}-20261106.txt`; capturas sintéticas `output/documentacion-carpetas-20261106/expediente-{1440,1280,1024,768,390}.png`. Revisión acotada de seguridad sin hallazgos en las nuevas entradas: alcance, identidad del titular y ausencia de escrituras comprobados por los negativos descritos. Se reutilizan la Policy y el visor privados existentes.

## Ajuste posterior: notas adhesivas

La propietaria pide que los pendientes se distingan como notas adhesivas. Se sustituyen las filas por notas compactas (máximo 300px), fondo salvia claro, cinta superior y esquina doblada, conservando nombre, estado, fecha y seguimiento. Se reutilizan los tokens existentes; no cambian consultas, plazos ni permisos.

Build aprobado; tres pruebas focales / 25 aserciones aprobadas y E2E aprobado en los cinco anchos, incluyendo comprobación de la nota y ausencia de desbordamiento. Capturas actuales de escritorio y móvil inspeccionadas. Evidencia: `output/documentacion-notas-{build,focal,e2e}-20261106.txt`. La verificación PostgreSQL anterior sigue correspondiendo al mismo backend, que este ajuste no modifica.

## Ajuste posterior: distribución interna del expediente

El encabezado identifica al trabajador una vez, con «Expediente personal» y retorno a carpetas. Se separan «Archivos entregados» y «Pendientes de entrega», cada uno con su conteo existente. La tabla interna omite la columna redundante de trabajador; la tabla general la conserva. Los filtros de archivos permanecen dentro de su sección y la búsqueda aclara que corresponde al expediente abierto.

Con al menos 950px de espacio útil, las notas ocupan una columna lateral de 310px y los archivos la zona principal. Por debajo se apilan, conservando primero los pendientes. El panel de archivos tiene su propio contenedor responsive: tabla con espacio suficiente, fichas compactas en tamaños intermedios y estado/fecha agrupados en móvil. Los botones mantienen textos completos y objetivos táctiles. Se retira el paginador de pendientes cuando solo hay una página. No cambia la persistencia, el alcance de lectura ni los plazos.

Verificación: 15 pruebas / 153 aserciones aprobadas de `DocumentacionPersonalArchivosTest`; build aprobado y E2E de cierre aprobado en 1440/1280/1024/768/390px, sin errores JavaScript ni desbordamiento horizontal. Se comprueban título de archivos, ausencia de trabajador repetido, columna lateral en escritorio, notas, visor y retorno de foco. Capturas de los cinco anchos revisadas, con revisión adicional de 1280px tras ajustar el corte de distribución. Evidencia: `output/documentacion-interior-focal-20261106.txt`, `output/documentacion-interior-build-cierre-20261106.txt` y `output/documentacion-interior-e2e-cierre-20261106.txt`. Sin cambios de backend ni nueva ejecución PostgreSQL para este ajuste visual.

## Ajuste posterior: compactar cada documento

Por petición de la propietaria se eliminan los rótulos repetidos «Acciones», «Estado» y «Carga» de las fichas internas; se conservan el estado y la fecha, esta última con nombre accesible. La tabla interna usa «Fecha» y mantiene un encabezado de acciones solo para lectores de pantalla. Se retira el texto explicativo «La carga no equivale…» únicamente dentro del expediente, conservándolo en la biblioteca general.

Los botones «Ver archivo» y «Abrir original» se agrupan a la derecha, con el mismo sistema de botones y áreas táctiles de 44px. Se reducen los espacios internos y se mantienen el visor y la apertura autorizada del original. Sin cambios de backend, permisos o persistencia.

Build aprobado, tres pruebas focales / 25 aserciones aprobadas y E2E aprobado en cinco anchos, incluyendo ausencia de rótulos y texto redundante, visor y retorno de foco. Capturas actuales de escritorio y móvil inspeccionadas. Evidencia: `output/documentacion-botones-build-final-20261106.txt`, `output/documentacion-botones-focal-20261106.txt`, `output/documentacion-botones-e2e-final-20261106.txt`.

## Ajuste posterior: encabezado y espacios señalados

Las capturas anotadas por la propietaria muestran un encabezado alto por el apilamiento del retorno, etiqueta, nombre y subtítulo, una fila adicional de botones bajo cada archivo y una columna reservada con cero pendientes. Se conserva la composición cálida, Nunito, miniaturas, botones y notas aprobados; se corrige la geometría señalada.

El encabezado reúne icono, etiqueta y nombre a la izquierda y retorno a la derecha, suprimiendo el subtítulo redundante. En fichas de ancho intermedio, documento, estado/fecha y botones comparten una fila, sin fila inferior reservada a acciones; en móvil se apilan sin truncar los nombres. Cuando no hay pendientes, la información se presenta como franja breve y el listado utiliza todo el ancho, sin columna lateral vacía. Esto no afirma que los archivos estén validados.

Verificación: 15 pruebas / 153 aserciones aprobadas; build y E2E aprobados en cinco anchos, incluyendo expediente con y sin pendientes, encabezado inferior a 100px en escritorio, ancho completo sin pendientes, filtros, visor y retorno de foco. Capturas actuales de los cinco tamaños revisadas y comparación adicional de ambos estados en escritorio y móvil. Sin cambios de backend, BDD o permisos. Evidencia: `output/documentacion-espacios-{focal,build,e2e}-20261106.txt`; capturas `expediente-sin-pendientes-*` y `expediente-*` del directorio de QA ya indicado.

## Corrección de preferencia: conservar el espacio de pendientes

La propietaria aclara que el espacio de los documentos faltantes estaba bien. Se conserva de nuevo la columna lateral de pendientes en escritorio aunque el conteo sea cero, y su sección apilada en pantallas pequeñas. Se retira únicamente la variante que expandía los archivos cuando no había pendientes. El encabezado compacto y los botones en la misma fila permanecen. Esta indicación sustituye el ajuste anterior de ancho completo sin pendientes.

Build y E2E aprobados en los cinco anchos, comprobando que se conserva la columna lateral sin pendientes en escritorio, el visor y la navegación. Captura de escritorio sin pendientes inspeccionada. Evidencia: `output/documentacion-espacio-pendientes-{build,e2e}-20261106.txt`. Sin cambios de backend, BDD o permisos.
