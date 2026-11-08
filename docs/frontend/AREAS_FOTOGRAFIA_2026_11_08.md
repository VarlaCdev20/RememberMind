# Áreas: formulario completo y fotografía principal

Fecha: 08/11/2026. Alcance: registro/edición de áreas de Gerencia y Superadmin.
La [decisión expresa de la propietaria](../base-de-datos/DECISION_FOTOGRAFIA_AREA_2026_11_08.md)
autoriza una columna opcional y su FK/index, sin tablas ni permisos nuevos.

## Implementación e integración

El formulario conserva la composición compartida con Personal: modal centrado,
cabecera verde suave, café grisáceo, Nunito, campos legibles y controles redondeados.
Presenta código automático/de solo lectura, nombre, descripción, estado y fotografía
principal. No añade información sin respaldo en el modelo, jefaturas permanentes
ni personal duplicado: el equipo continúa derivándose de asignaciones por jornada.

La fotografía se elige desde Galería con búsqueda/paginación o se carga en el
formulario con vista previa y texto alternativo opcional. La carga definitiva
ocurre al guardar el área. SQL, fotografía y auditoría participan en la transacción;
un fallo posterior compensa únicamente los archivos nuevos. Una fotografía
duplicada conserva autor, contenido y metadatos anteriores. Quitar/reemplazar el
vínculo no borra la imagen. La tarjeta usa la ruta protegida, sin publicar rutas
físicas. Archivar impide servir/seleccionar la fotografía; el vínculo anterior se
conserva y la edición de texto sigue permitida.

El estado inicial del formulario se conserva en una propiedad bloqueada para que
las actualizaciones al seleccionar en Galería no anulen la advertencia de descarte.

## Seguridad y persistencia

- Las escrituras revalidan cuenta activa, permiso de áreas y restricciones existentes.
- Seleccionar requiere lectura autorizada de Galería; subir requiere su gestión.
  Superadmin conserva selección/lectura, sin recibir gestión automática.
- Se reutilizan Policy, almacenamiento privado, normalización WebP, miniatura,
  control de duplicados y límites JPG/PNG/WebP, 2 MB y 12 megapíxeles.
- La relación usa `VARCHAR(20) NULL`, índice y UPDATE/DELETE RESTRICT.
  El rollback rechaza vínculos poblados. No hay JSON/EAV ni nuevas tablas.
- La auditoría conserva los códigos de fotografía anterior y posterior.

Instalación local PostgreSQL completada mediante
`php scripts/instalar-fotografia-areas.php --instalar-fotografia-area-aprobada`.
Se confirmó un respaldo privado con catálogo legible y exclusión de Git. Las
18 áreas previas conservan exactamente sus cuatro atributos originales; la nueva
columna quedó NULL en todas, sin asignaciones supuestas. Permanecen 122 tablas
físicas y los permisos existentes. Un segundo intento confirmó la instalación
sin volver a aplicarla. No se ejecutaron migraciones ajenas ni reset operativo.

## Verificación

| Alcance | Evidencia |
|---|---|
| Nuevos contratos, SQLite desechable | `AreasFotografiaTest`: 13 pruebas, 86 aserciones; pasada posterior de auditoría: 3 pruebas, 28 aserciones. |
| Regresión de Áreas, SQLite | Tres clases existentes de Áreas: 28 pruebas, 130 aserciones. |
| Integración PostgreSQL desechable | Nuevos contratos y tres clases existentes: 41 pruebas, 216 aserciones. Auditoría posterior del reemplazo: 1 prueba, 9 aserciones. |
| Galería | `GaleriaInstitucionalTest`: 19 pruebas, 140 aserciones. |
| UI/flujo | `RM_AREAS_FOTOGRAFIA_E2E=1 node --test tests/Frontend/areas-fotografia-e2e.test.js`: PASS. Carga real sintética, selección, descarte, guardado, recarga y desvinculación sin borrar/duplicar Galería. |
| Adaptación | 1440, 1280, 1024, 768 y 390 px; también 390 oscuro. Modal dentro del viewport, sin desborde horizontal, controles ≥44 px, Nunito, foco de teclado y ausencia de errores JS/HTTP o avisos nativos. |
| Build | `npm run build`: PASS. |

La comprobación visual usa un servidor aislado y datos sintéticos, sin operar la
sesión del navegador de la propietaria. Revisión de autorización y FK/rollback:
PASS dentro de este alcance y los motores citados. No se añade dependencia V1.

## Pendiente ajeno localizado

La pasada ampliada `GaleriaInstitucionalTest|BddOperativaV2Test` obtuvo 37 pruebas
correctas y una fallida: `BddOperativaV2Test:82` aún exige cinco columnas de
`usuarios`, omitiendo `remember_token`, aprobado desde el 26/10/2026. La misma
aserción está en HEAD anterior a esta tarea. No se modifican usuarios ni se
oculta ese fallo; queda fuera del registro de Áreas. La suite general no se
declara completamente verde.
