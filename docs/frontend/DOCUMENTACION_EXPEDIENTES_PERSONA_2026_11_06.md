# Documentación: carpetas por trabajador y expediente interno — 06/11/2026

La propietaria pidió carpetas más anchas, identificadas por el nombre del usuario, con la documentación dentro como un file. La presentación anterior de una carpeta por documento se sustituye por una carpeta por trabajador con archivos autorizados.

## Implementado

- Carpetas neutras, de ancho mínimo330px cuando hay espacio, pestaña «Expediente personal», nombre completo y número de documentos. En móvil ocupan el ancho disponible.
- Abrir una carpeta muestra el expediente de esa persona en la tabla compartida, con miniaturas, estados y acceso al visor original. El encabezado identifica al trabajador y permite «Volver a carpetas». Los filtros internos se centran en documentos; no muestran selectores para cambiar de persona/rol desde el expediente.
- La tabla general sigue disponible desde la raíz. La paginación de personas usa `carpetasPage`; la de archivos usa `archivosPage`. Se agrupa en SQL antes de paginar: trece documentos de la misma persona no generan carpetas repetidas ni conteos parciales de doce.
- Los filtros de documentos también determinan las carpetas y sus conteos. Cuando hay filtros, el número indica «en esta consulta». Volver conserva filtros y paginación de carpetas, con retorno de foco al origen o al encabezado si dejó de existir por la selección aplicada.

## Integridad y seguridad

Se reutiliza `ConsultarDocumentacionPersonal::archivos`: el subquery de agrupación conserva exactamente su alcance autorizado. No se listan carpetas de cuentas sin archivos autorizados; no se crean expedientes vacíos por defecto. No hay nuevas tablas, columnas, relaciones, permisos, rutas ni almacenamiento alternativo. Las carpetas son organización visual; los archivos físicos y su historia permanecen intactos.

El código del usuario seleccionado está bloqueado en Livewire. Abrir una carpeta exige documentos consultables para ese titular; cada render revalida su acceso. El visor de una carpeta rechaza archivos de otro titular aunque Gerencia pueda consultarlos desde la tabla general. Familiar, cuenta suspendida y trabajador ajeno conservan sus restricciones. No se escribe documentación al consultar, volver o paginar; no se envían correos.

## Pruebas

- SQLite:12 pruebas y128 aserciones aprobadas.
- PostgreSQL desechable:12 pruebas y128 aserciones aprobadas.
- Casos nuevos: agrupación de13 documentos de una persona, paginación interna sin mezcla, conteo filtrado, otro titular, revocación de cuenta y documento ajeno al expediente abierto.
- E2E de carpeta por persona y expediente interno: cinco anchos1440/1280/1024/768/390px. Comprueba tres personas con cuatro archivos (dos pertenecen a la misma persona), entrada/salida, encabezado y foco, miniatura autorizada, visor, tabla global y filtros. Sin errores JavaScript. Se inspeccionan capturas de carpetas y file en escritorio y móvil.
- E2E anterior adaptado a la tabla general: filtros/visor y Solicitudes aprobado. Build Vite aprobado. No se ejecuta CI completa ni una auditoría global de accesibilidad.

Evidencia local: `output/documentacion-expedientes-*20261106*` y capturas `usuarios-*`/`expediente-*` en `output/documentacion-carpetas-20261106/`. Todas las personas de las capturas son sintéticas.
