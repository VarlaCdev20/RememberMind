# Galería institucional — extensión aprobada

Fecha: 17/10/2026. Aprobación de la propietaria: «dale incorpora esta parte sin romper nada respetando la fn4», después de revisar los 17 campos de una galería independiente. Sustituye la propuesta inicial de publicaciones, nunca aprobada ni implementada.

## Contrato estructural

Una nueva tabla `fotografias_institucionales`: inventario operativo 71 → 72. Se conservan todas las tablas y relaciones existentes. Campos, tipos y nulabilidad de la [especificación revisada](../frontend/PROPUESTA_GALERIA_INSTITUCIONAL_2026_10_17.md).

PK `cod_fotografia VARCHAR(20)` no nula, prefijo aprobado `FGI` mediante el generador correlativo existente. FK indexada `cod_usuario → usuarios.cod_usuario` con igual tipo/longitud y UPDATE/DELETE RESTRICT. Autor derivado de sesión.

UNIQUE en rutas de imagen/miniatura y SHA-256. CHECK de título no vacío/longitud, rutas/hash, peso/dimensiones positivos y estados ACTIVA/ARCHIVADA. Índice `(estado, fecha_registro, cod_fotografia)`. Fechas explícitas, sin timestamps ni soft deletes añadidos a tablas congeladas.

## 4FN

Una fila identifica una fotografía con atributos atómicos. La miniatura es una derivación técnica uno a uno, no una colección independiente. Las claves candidatas determinan los restantes atributos; no se introducen dependencias multivaluadas independientes. Sin arrays, listas de pantallas, JSON/EAV o agrupaciones editoriales dentro de una columna.

`usar_en_bienvenida` es una pertenencia booleana a un conjunto compartido. Destinos/álbumes independientes futuros necesitan relaciones normalizadas y aprobación. La carga múltiple es entrada temporal, no persistencia multivaluada.

## Permisos, almacenamiento y operación

GERENTE/ADMINISTRADOR reciben únicamente `fotografias_institucionales.ver` y `.gestionar`. SUPERADMINISTRADOR conserva su lectura global, sin gestión automática. Otros roles conservan sus accesos. Preview no escribe.

Archivos normalizados WebP y miniatura en disco local privado; ruta protegida con autorización y estado. El original temporal no se publica. Límites acordes al PHP instalado: JPG/PNG/WebP, 2 MB y 12 megapíxeles por foto, hasta 10 fotos y 6 MB por lote. Límites comprobados también en backend; no son columnas adicionales.

La atomicidad es por fotografía. SQL no revierte archivos: si falla SQL/auditoría se compensan solo archivos nuevos, reportando fallos de limpieza para recuperación. Duplicados exactos conservan recurso, autor y metadatos anteriores. Archivar bloquea la URL y la selección de bienvenida, conservando historia y bytes. Reactivar restablece disponibilidad. No se sobrescriben identidad, bytes ni autor de una foto guardada.

## Aplicación y reversión

Aplicar exclusivamente la nueva migración en la base local verificada, después de pruebas desechables SQLite/PostgreSQL y respaldo privado. No ejecutar migraciones ajenas pendientes ni reset de la base operativa. La asignación de los dos permisos nuevos es idempotente y utiliza los roles existentes, sin crear usuarios/personas como fallback.

Antes de rollback, preservar archivos y metadatos si existen cargas reales. `down()` elimina solo la tabla y los dos permisos nuevos y no es limpieza rutinaria de contenido. Para retirar imágenes se usan estados.

El informe de entrega registrará comandos, resultados y límites de verificación. Esta decisión autoriza la extensión; no acredita ejecución, QA ni restauración de respaldo por su mera existencia.
