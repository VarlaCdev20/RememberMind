---
title: "Fotografía principal del área"
status: CURRENT
last_reviewed: 2026-11-08
owner: RememberMind
source_of_truth: true
---

# Alcance autorizado

La propietaria respondió «Sí, autorizo el vínculo con Galería» el 08/11/2026 a
la consulta que especificaba columna `cod_fotografia`, clave foránea e índice,
sin tablas ni permisos nuevos. Se autoriza esta ampliación y su integración en
el registro/edición de Áreas. La instalación y las pruebas se acreditan aparte.

La propietaria solicita completar el formulario de Áreas, incluida su imagen.
La inspección de solo lectura de PostgreSQL del 08/11/2026 confirma exactamente
cuatro columnas en `areas`: `cod_area VARCHAR(20)` PK, `nombre VARCHAR(80)` UNIQUE,
`descripcion TEXT NULL` y `estado VARCHAR(20)`. Coinciden con el
[diccionario vigente del núcleo](REMEMBERMIND_BDD_70_TABLAS.md).
En ese corte previo no existía una fotografía vinculada al área.

## Cambio concreto aprobado

Agregar únicamente `areas.cod_fotografia VARCHAR(20) NULL`, con índice y FK
a `fotografias_institucionales.cod_fotografia`, UPDATE/DELETE RESTRICT.
No crear tablas ni permisos. Las áreas anteriores conservarían NULL;
no se asignarían imágenes por coincidencia de nombres ni por suposición.

Cada área tendría como máximo una fotografía principal. Una fotografía existente
podría ser elegida por más de un área. El grano continúa siendo una fila por área;
no se duplican rutas, bytes ni metadatos de la fotografía. Este vínculo escalar no
introduce dependencias multivaluadas independientes y conserva 4FN en su alcance.
No se usarían `descripcion`, JSON, listas ni convenciones de archivos para suplir
la relación faltante.

## Formulario e integración

- Código automático al guardar y de solo lectura al editar.
- Nombre obligatorio, único, en mayúsculas y de hasta 80 caracteres.
- Descripción funcional opcional.
- Estado según los controles vigentes: Gerencia conserva el estado al editar;
  Superadmin mantiene el selector y las restricciones para desactivar.
- Fotografía opcional: seleccionar en Galería o subir con su autorización actual,
  previsualizar, reemplazar o quitar el vínculo sin borrar el archivo.
- Mostrar la fotografía autorizada en la tarjeta del área. Una imagen archivada
  no se serviría ni podría elegirse para un vínculo nuevo; se conservaría el
  vínculo previo y se ofrecería reemplazarlo.
- El personal y los responsables siguen derivándose de asignaciones por jornada;
  no se convierten en columnas ni jefaturas permanentes del área.

Se reutilizarían almacenamiento privado, validación JPG/PNG/WebP de hasta 2 MB,
normalización, miniaturas, control de duplicados, Policy y ruta protegida de la
[Galería aprobada](DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md).
Superadmin conserva lectura y selección autorizada, sin obtener gestión de
Galería por esta ampliación. La carga requiere la autorización de Galería existente.

## Verificación e instalación previstas

Tras aprobación: validar autorización, fotografía existente/activa, alta/edición,
cancelación, fallos de archivo/SQL, auditoría, FK y lectura posterior; comprobar
SQLite desechable y PostgreSQL de integración, build y los cinco anchos de pantalla.
Instalación explícita, sin migraciones ajenas ni reset. La reversión debe rechazar
la pérdida de vínculos poblados hasta contar con una conservación y decisión expresa.

**Instalación y verificación posteriores:** completadas localmente el 08/11/2026;
18 áreas previas conservadas, 122 tablas físicas y permisos sin cambios. Véase el
[informe de implementación y pruebas](../frontend/AREAS_FOTOGRAFIA_2026_11_08.md)
para separar autorización, instalación, evidencia por motor y pendiente general.
