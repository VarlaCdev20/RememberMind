# Galería del centro — entrega del 17/10/2026

Estado: implementada y aplicada en la base local; aceptación de navegador con sesión pendiente. Alcance: galería institucional independiente para GERENTE y ADMINISTRADOR. [Decisión y contrato de 4FN](../base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md).

## Implementado e integración

- `/admin/galeria`, enlazada en Organización de Gerencia y Operación diaria de Administración. Encabezado y filtros compartidos; tokens cálidos existentes, sin otra paleta ni indicadores decorativos.
- Carga múltiple, previsualización, título, descripción, fecha de fotografía y descripción accesible. Los datos del lote pueden editarse individualmente después. Búsqueda, paginación, activas/archivadas, edición, archivo y reactivación.
- JPG/PNG/WebP de hasta 2 MB y 12 megapíxeles; 10 fotos y 6 MB por lote. Validación de tipo/contenido y límites en backend; límites de tamaño también antes de subir en el navegador. Resumen real por archivo y conservación de los fallidos para reintento.
- WebP normalizado de hasta 2560 px y miniatura de hasta 480 px en `storage/app/private/galeria-institucional`. Orientación EXIF y recodificación sin conservar esa cabecera. No se almacena el original como recurso público.
- Duplicados por hash del contenido normalizado: una fotografía, autor/metadatos previos conservados y limpieza de los archivos nuevos, incluso ante dos cargas simultáneas.
- La opción de bienvenida alimenta el rotador de Gerencia y Administración. Sin fotografías elegibles se conservan las imágenes existentes. No se amplía su lectura a Familia, Enfermería u otros destinos.
- Dirty state en cancelar, nueva carga y cambio de fotografía; errores, progreso y resultado por archivo. Archivar conserva bytes e historia, bloquea descargas y retira la fotografía del conjunto de bienvenida.

## Persistencia y seguridad

Una tabla nueva de 17 columnas, PK `cod_fotografia`, FK `cod_usuario`, índices/UNIQUE/CHECK y casts correspondientes; inventario operativo aprobado 71 → 72. Las rutas derivadas son atributos de una fotografía, sin colecciones independientes, JSON/EAV o listas de destinos. No se altera la estructura de las tablas anteriores.

Autorización en Livewire, acción y descarga: sesión, cuenta activa, rol y permiso explícito, Policy y estado. Autor obtenido del backend y vuelto a consultar; identidad, bytes y autor no se sobrescriben al editar. Preview no escribe. SUPERADMINISTRADOR conserva lectura global y no adquiere gestión por ese rol. Las rutas privadas vuelven a autorizar cada descarga y usan `private, no-store` y `nosniff`.

Auditoría con Activitylog y transacción SQL por fotografía. El fallo SQL/auditoría compensa archivos nuevos; un fallo de limpieza se reporta. El lote no simula atomicidad global: informa lo realmente guardado y lo pendiente.

La comprobación posterior a migrar detectó que el observer general concedía los permisos nuevos al superadministrador. Se corrigió la creación de estos permisos en la migración para concederlos explícitamente y se retiraron exclusivamente esas dos asignaciones nuevas de la base local. Se añadió una prueba de migración con roles ya existentes; Policy impedía escritura por el rol SUPERADMINISTRADOR también antes de esta corrección.

## Aplicación local y respaldo

Base comprobada: PostgreSQL local `remembermind_dev`. Antes de aplicar se generó respaldo custom de 1.588.016 bytes, con índice legible mediante `pg_restore --list`:

`C:\Users\CARLAENCINAS\AppData\Local\RememberMind\backups\galeria-20261017-150428\remembermind_dev.dump`

El respaldo y los conteos anteriores/posteriores permanecen fuera del repositorio. No se ha probado restaurar este respaldo; leer su índice no equivale a una restauración verificada.

Única migración aplicada:

```powershell
php artisan migrate --path=database/migrations/2026_10_17_150000_create_fotografias_institucionales_table.php --force
```

Resultado comprobado: 17 columnas, galería vacía, una migración agregada, dos permisos nuevos y cuatro asignaciones finales, solo GERENTE/ADMINISTRADOR. Sin cambios en los conteos de las 101 tablas existentes revisadas; sesiones/cachés/colas y tablas técnicas modificadas por la migración se excluyeron de esa comparación. No se ejecutaron migraciones ajenas ni reset de la base local. No se añadieron fotografías sintéticas a la base operativa.

GD y EXIF declaradas en Composer, lock actualizado sin cambiar versiones de paquetes. EXIF habilitada en el PHP instalado, conservando copia previa de `php.ini`; servidor local reiniciado en 8000. Para otras instalaciones se requieren ambas extensiones y WebP en GD.

## Evidencias de verificación

| Verificación | Resultado y alcance |
|---|---|
| PostgreSQL desechable | PASS: 50 pruebas, 743 aserciones, incluyendo galería, dos workers concurrentes, dashboard, rotador, códigos e inventario |
| SQLite en memoria | PASS: 17 pruebas, 117 aserciones, con validación, autorización, rollback, archivos, estados y renderizado Livewire |
| Diseño offline | PASS: siete estados × cinco anchos (1440/1280/1024/768/390) × dos temas = 70 combinaciones; reflow, controles de 44 px, labels, foco, imágenes, límites previos a carga y confirmación de cambios |
| Seguridad y BDD | Revisión técnica separada de implementación y negativos automatizados sin hallazgos abiertos en este alcance; no constituye auditoría externa independiente |
| Dependencias | `composer check-platform-reqs`: PASS |
| Build | `npm run build`: PASS |
| Legacy | Sin dependencias V1 nuevas en el flujo de galería. Legacy previo de otros módulos fuera de alcance |
| Navegador con sesión | PENDIENTE: navegador local deshabilitado en Work; sin afirmar carga HTTP completa desde sesión autenticada |

Comando final PostgreSQL:

```powershell
.\scripts\probar-postgres.ps1 -Filtro 'GaleriaInstitucionalTest|GaleriaInstitucionalConcurrenciaPostgresTest|GerenciaDashboardTest|DashboardPhotoRotationTest|CodigosCorrelativosTest|BddOperativaV2Test'
```

Log: `%TEMP%\remembermind-pg-b707bd2e8ab94883afa1d08b07f50022.log`; informe JUnit con el mismo identificador. El runner crea y elimina su propio cluster; no usa la base operativa. Una ejecución combinada previa detectó contaminación de fixtures por los workers; se corrigió el estado de RefreshDatabase al terminar la prueba concurrente y la ejecución combinada final pasó.

Reproducción del QA offline, sin sesión de la aplicación:

```powershell
$env:RM_GALLERY_QA_DIR=Join-Path $env:TEMP 'remembermind-gallery-qa'
php artisan test --filter=GaleriaInstitucionalTest
$env:RM_GALLERY_EVIDENCE_DIR='C:\Users\CARLAENCINAS\.codex\visualizations\2026\10\17\01a14ae7-aa47-7b33-85ee-85377b04d172\gallery'
node --test tests/Frontend/institutional-gallery.test.js
```

Los fixtures proceden del HTML real de Livewire, CSS compilado e imágenes sintéticas. Se aíslan los atributos de hidratación y se simula el enlace `$wire`; por eso prueban presentación/Alpine y no sustituyen una carga en navegador contra el servidor. Se corrigió una altura excesiva causada por la dirección flex heredada de los filtros y se añadió una comprobación de regresión. Capturas de los siete estados en 390/1440 px dentro del directorio de evidencia indicado.

## Importación del archivo existente — 17/10/2026

Petición posterior de la propietaria: «MUEVE LAS FOTOGRAFIAS QUE TENEMO A ESE APARTADO DE GALERIA DEL CENTRO». Se incorporaron las 32 fotografías JPG de `public/images/FOTOS CENTRO DE ADULTOS MAYORES` mediante la acción existente, con las mismas validaciones, Policy, normalización, deduplicación y transacción por archivo. Sin cambios de código funcional, esquema ni permisos.

Resultado: 32 fotografías nuevas ACTIVA, 64 archivos privados WebP (imagen y miniatura), 7.728.216 bytes derivados y tres páginas de 12 elementos. Títulos iniciales `Fotografía del centro 01` a `32`, editables; fecha de fotografía desconocida conservada como nula. Siete fotografías ya empleadas por el inicio se ofrecen al conjunto de bienvenida; las restantes quedan disponibles en la galería para selección posterior.

La operación CLI se ejecutó bajo la cuenta GERENTE indicada expresamente en esta conversación, comprobando cuenta activa y Policy y sin modificar su sesión de navegador. No se atribuye a esa cuenta la toma original de las fotos, sino su importación. Cada evento de subida registra archivo/hash de origen y lote en Activitylog. El manifiesto origen → código/hash normalizado y las comprobaciones permanecen fuera del repositorio, junto al respaldo privado.

Respaldo previo custom (1.593.424 bytes, índice legible): `C:\Users\CARLAENCINAS\AppData\Local\RememberMind\backups\importacion-galeria-20261017-151641\remembermind_dev.dump`. No se afirma restauración verificada.

Verificación posterior: hashes de los 32 originales intactos; 64 archivos WebP legibles y 64 respuestas autorizadas del controlador comprobadas directamente en PHP; 32 auditorías con actor y procedencia correctos; selección de bienvenida de Gerencia desde la galería; sin cambios en los conteos de tablas ajenas. No equivale a una prueba HTTP de navegador. Las rutas originales se conservan porque aún alimentan bienvenida pública y otros roles; archivar su copia de galería solo afecta los destinos privados integrados. Esta importación no amplía el control editorial de los otros destinos.

Pruebas focales existentes ejecutadas: 4 PASS / 44 aserciones (carga de ambos roles, duplicados, archivo/descarga y rollback de auditoría). Build no repetido: esta operación cambia datos y documentación, sin modificar frontend. Sin datos reales nuevos en tests, seeders, capturas o Git.

## Pendientes y Git

QA global: FAIL por evidencia de sesión incompleta, sin defecto visual abierto en la matriz offline. Falta recorrer desde la sesión autenticada: menú → carga → recarga → bienvenida → archivo → URL bloqueada → reactivación, además de comprobar el conjunto con sidebar/topbar reales y teclado.

No se amplía esta entrega a portadas públicas, álbumes, categorías, retratos ni fotografías clínicas. Otros destinos requieren contrato de visibilidad antes de integrarse.

Cierre anterior sin commit ni push por trabajo previo mezclado y aceptación de sesión pendiente. Posteriormente la propietaria pidió commit y push y confirmó expresamente «Todos los cambios pendientes del proyecto». Este corte se publica como avance técnico; mantiene pendiente la aceptación de sesión y no publica almacenamiento privado ni base de datos. Véase [informe de publicación](../sistema/PUBLICACION_AVANCES_2026_10_17.md).
