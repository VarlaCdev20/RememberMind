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
