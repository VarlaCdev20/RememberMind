---
title: "Propuesta de galería de fotografías del centro"
status: IMPLEMENTADA_QA_SESION_PENDIENTE
date: 2026-10-17
scope: "Banco de imágenes institucionales del lugar"
implemented: true
---

# Galería de fotografías del centro

Aprobada el 17/10/2026 mediante «dale incorpora esta parte sin romper nada respetando la fn4». [Contrato estructural aprobado](../base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md). Los apartados de propuesta que siguen documentan la revisión previa; esta decisión gobierna el alcance de implementación.

Implementada y aplicada en la base local el 17/10/2026. [Informe de verificación y límites](GALERIA_INSTITUCIONAL_2026_10_17.md). Inventario vigente: 72 tablas operativas; las referencias a 71 en el análisis previo describen el estado anterior a esta extensión.

## Alcance corregido por la propietaria

La aclaración vigente es «necesito que sea como una galeria de imagenes del lugar». La entidad principal será la fotografía institucional independiente. Esta revisión sustituye la propuesta inicial de publicaciones con fotografías hijas, que nunca fue aprobada ni implementada. No se crean publicaciones, álbumes ni categorías sin una necesidad posterior definida.

Objetivo: Gerencia y Administración suben fotografías del centro, las consultan en una galería y pueden reutilizar el mismo recurso en los destinos internos autorizados. Esta galería no gestiona retratos de cuentas/residentes ni documentación clínica.

## SOURCE OF TRUTH

- Estructura vigente: [baseline congelado](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario](../base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md) y [gobernanza BDD](../../database/AGENTS.md). Inventario actual: 71 tablas operativas. Esta propuesta añadiría una, resultando en 72, solo tras aprobación.
- Identificadores: [decisión de códigos correlativos](../base-de-datos/DECISION_CODIGOS_CORRELATIVOS.md). Se mantiene PK/FK string de 20 caracteres, sin `MAX+1`, IDs numéricos ni modificación de entidades existentes.
- Actores: [roles vigentes](../arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Nuevas facultades se acotan a la galería; no se heredan desde permisos de cuentas o clínicos.
- Implementación: [DashboardPhotoRotation](../../app/Modulos/Reportes/Presentacion/DashboardPhotoRotation.php) selecciona archivos fijos, no contenido administrado desde una galería. Los campos `usuarios.foto` y `residentes.foto` representan retratos y no se reutilizan para este banco de imágenes.
- Infraestructura reutilizable: Laravel Storage y [ArchivosLaravel](../../app/Compartido/Infraestructura/Documentos/ArchivosLaravel.php). El contrato de documentos de usuario y su destino público no definen la propiedad ni la visibilidad de una fotografía institucional.
- Versiones observadas en `composer.lock`: Laravel v13.33.0, Livewire v4.4.6 y Activitylog 4.12.3. [Arquitectura híbrida vigente](../arquitectura/ADR_ARQUITECTURA_HIBRIDA.md); no crear raíces históricas Backend/Frontend.

## Grano y normalización

Una fila de `fotografias_institucionales` representa una imagen normalizada, almacenada de forma duradera y con identidad propia. Su miniatura es una derivación técnica de la misma imagen, no otra fotografía editorial.

`usuarios (1) → (N) fotografias_institucionales` identifica a quien hizo la subida. `cod_usuario` no cambia al editar posteriormente la descripción; los demás actores quedan en Activitylog.

Dependencia principal: `cod_fotografia → atributos de la fotografía`. Las rutas únicas y la huella única son claves alternativas propuestas. No se duplican nombre, correo o rol del usuario. No se almacenan listas de fotografías, pantallas o categorías en JSON/texto. No se guarda un contador de usos ni un campo portada de publicación inexistente. Este contrato permite un diseño en 3FN; no demuestra por sí solo integridad física ni almacenamiento correcto.

## Campos propuestos

Tipos lógicos para formalizar después en el DDL de PostgreSQL y SQLite. Ninguna columna se ha creado.

| Campo | Tipo propuesto | Nulo/default | Procedencia y significado |
|---|---|---|---|
| `cod_fotografia` | `varchar(20)` | PK, no nulo | Generador correlativo existente; prefijo propuesto `FGI`, sujeto a aprobación e incorporación al catálogo técnico. |
| `cod_usuario` | `varchar(20)` | No nulo, FK | Usuario autenticado que subió la foto; no se acepta autor elegido por el cliente. |
| `titulo` | `varchar(160)` | No nulo | Texto descriptivo de la fotografía, editable por la persona autorizada. Debe quedar no vacío después de trim. |
| `descripcion` | `text` | Sí | Contexto opcional en texto plano. Límite de entrada propuesto: 2000 caracteres. |
| `fecha_fotografia` | `date` | Sí | Fecha conocida de la toma/actividad. No se inventa a partir de la fecha de subida. |
| `texto_alternativo` | `varchar(255)` | Sí | Descripción accesible específica; cuando falte, el consumidor utiliza el título descriptivo. |
| `ruta_archivo` | `varchar(500)` | No nulo, UNIQUE | Ruta relativa generada por servidor de la imagen normalizada; nunca una ruta proporcionada por el cliente. |
| `ruta_miniatura` | `varchar(500)` | No nulo, UNIQUE | Ruta relativa de la miniatura preparada antes de confirmar el registro. |
| `tipo_archivo` | `varchar(80)` | No nulo | MIME real de la imagen almacenada después del procesamiento, no del nombre o encabezado del navegador. |
| `tamanio_bytes` | `bigint` | No nulo | Bytes reales del archivo normalizado; positivo. No es el peso del original temporal. |
| `ancho` | `integer` | No nulo | Ancho en píxeles del archivo normalizado; positivo. |
| `alto` | `integer` | No nulo | Alto en píxeles del archivo normalizado; positivo. |
| `hash_archivo` | `char(64)` | No nulo, UNIQUE | SHA-256 de los bytes normalizados. Detecta duplicados exactos del contenido almacenado, no similitud visual. |
| `estado` | `varchar(20)` | No nulo, ACTIVA | Valores propuestos ACTIVA/ARCHIVADA. El cambio de estado conserva archivo y metadatos. |
| `usar_en_bienvenida` | `boolean` | No nulo, false | Candidata a la bienvenida interna de Gerencia/Administración; solo tiene efecto si está ACTIVA. No identifica una ubicación individual. |
| `fecha_registro` | `datetime` | No nulo | Fecha técnica de persistencia, asignada por servidor según el contrato horario vigente de la aplicación. |
| `fecha_actualizacion` | `datetime` | No nulo | Última modificación del registro, asignada por servidor. |

Se proponen fechas explícitas, coherentes con la base operativa cuyo modelo base tiene `timestamps = false`; no añadir `created_at`, `updated_at` o `deleted_at` automáticamente. No se infiere que el DDL esté aprobado por especificarlo aquí.

### Qué completa la persona

Título; descripción y fecha si se conocen; texto alternativo si necesita diferir del título; decisión de ofrecerla en bienvenida. Puede editar los textos y archivar/reactivar según el permiso aprobado. Las rutas, MIME, tamaño, hash, dimensiones, autor y fechas técnicas son responsabilidad exclusiva del servidor.

Una imagen persistida no se sobrescribe con otros bytes conservando su código. Para cambiar la fotografía se sube otro recurso y se retira el anterior de los consumidores que correspondan. Así, el código siempre identifica el mismo contenido y los enlaces/caché no cambian silenciosamente de imagen.

## Restricciones e índices propuestos

- PK: `cod_fotografia`. FK indexada `cod_usuario → usuarios.cod_usuario`, con UPDATE/DELETE restrictivo. No autor nulo ni eliminación en cascada.
- UNIQUE de `ruta_archivo`, `ruta_miniatura` y `hash_archivo`; no índices duplicados sobre esas mismas columnas.
- CHECK de estado ACTIVA/ARCHIVADA, `tamanio_bytes > 0`, `ancho > 0`, `alto > 0` y longitud de hash 64. El backend valida además formato hexadecimal y recalcula el hash; comprobar el dato contra el archivo requiere acceso al almacenamiento.
- Índice compuesto propuesto `(estado, fecha_registro, cod_fotografia)` para galería paginada por estado y fecha, con desempate estable. No añadir índices de todos los campos ni búsquedas avanzadas sin evidencia de necesidad.
- La BDD no valida existencia física, capacidad de decodificar, máximo de píxeles, autorización o huella real. Action/Policy/procesador deben garantizarlo y probarlo.
- Las longitudes de `varchar` se validan también en la entrada; SQLite no acredita los mismos límites físicos solo por declarar un tipo lógico. Las restricciones efectivas se comprobarán por motor antes de dar el cambio por verificado.
- Si dos subidas concurrentes producen el mismo hash, la restricción UNIQUE decide la unicidad. La operación informa que la foto ya existe sin sobrescribir sus textos/autor ni confundir el resultado con una nueva inserción; solo compensa sus archivos nuevos.

## Estados, archivos y consistencia

Estados propuestos:

- ACTIVA: visible en la galería autorizada; podrá aparecer en bienvenida si además está seleccionada para ese uso.
- ARCHIVADA: excluida de galería ordinaria y bienvenida; consultable en el filtro de archivadas por los gestores autorizados. La lectura del archivo también comprueba el contexto/estado para no mantener acceso por una URL vieja.

No hace falta un estado BORRADOR en la tabla para una selección temporal que todavía no se guarda. Cada imagen se valida y prepara primero. Solo se confirma su fila cuando imagen normalizada y miniatura estén listas.

La atomicidad es por fotografía, no por publicación. Una carga múltiple informa cuántas fotos quedaron guardadas, cuáles ya existían y cuáles fallaron; puede reintentar las fallidas. Una transacción SQL no revierte archivos. Si falla SQL o la auditoría requerida, compensar exclusivamente los archivos nuevos de esa operación. Los errores de limpieza se registran y deben poder recuperarse; no ocultarlos.

Archivo privado persistente gestionado por Storage, nombres generados, imágenes recodificadas y orientadas, EXIF eliminado, límites de bytes/píxeles/cantidad y validación real de JPG/PNG/WebP. La lectura usa ruta autenticada y Policy; no publicar automáticamente en `public/storage`. Disco/configuración es responsabilidad de despliegue, no una opción de cada registro. Respaldar y restaurar BDD y archivos en conjunto.

Archivar no elimina físicamente. Retención y eliminación física quedan pendientes de decisión; esta propuesta no crea un plazo automático ni una segunda tabla de auditoría.

## Reutilización y límite del modelo

Una fotografía puede mostrarse en múltiples pantallas consultando su mismo código/URL autorizado; no se duplica archivo ni fila. La primera integración propuesta de bienvenida usa un conjunto de candidatas ACTIVA + `usar_en_bienvenida`, dentro de los dos roles gestores indicados.

Este campo booleano NO resuelve asignar una fotografía exacta a cada posición/pantalla. Si se requiere administrar destinos independientes —por ejemplo una portada particular para cada rol— debe diseñarse una relación de ubicaciones y fotografías, con aprobación de su tabla y catálogo. No guardar `pantallas = "inicio,medico,..."`, ni convertirlo en un JSON que sustituya esa relación.

Álbumes, clasificación por categorías y asignaciones a espacios específicos se incorporarán solo al confirmar la necesidad. No vincular fotografías editoriales automáticamente a residentes, actos clínicos o publicaciones inexistentes.

## Casos de uso, permisos y arquitectura propuestos

- Institución → Galería del centro → buscar/filtrar → subir varias fotos → confirmar resultados por archivo → consultar/editar textos → seleccionar bienvenida o archivar.
- GERENTE y ADMINISTRADOR: permisos propuestos `fotografias_institucionales.ver` y `fotografias_institucionales.gestionar`, cuenta ACTIVA, Policy y contexto. No modificar permisos de cuentas, residentes o clínica para permitirlo.
- Otros roles, pantallas familiares y acceso público: sin nuevas facultades ni exposición automática; su audiencia se define antes de habilitar nuevos destinos.
- Propietario funcional `Institucion` según arquitectura híbrida; Livewire/HTTP/Policy/vistas en Presentacion, operaciones explícitas en Aplicacion, reutilización de infraestructura compartida cuando corresponda. No crear capas históricas.
- Activitylog registra alta, edición de metadatos, archivo/reactivación y cambios de uso, con actor y recurso; no guarda bytes ni EXIF sensible.

## Pruebas previstas y decisión pendiente

Probar persistencia y recarga real; autor derivado de sesión; rechazo de cuenta inactiva/sin permiso; acceso directo/manipulación de códigos/rutas; formatos falsos y límites; archivo/miniatura/hash/dimensiones coherentes; fallos de almacenamiento/SQL/auditoría; duplicados concurrentes; carga múltiple con resultados parciales; archivado y URL antigua; candidatos de bienvenida y fallback; restauración de backups; PostgreSQL y SQLite por separado; UI/teclado en los cinco anchos del proyecto.

Antes de migrar se necesita aprobar esta extensión de UNA tabla, columnas/constraints/índices, prefijo de código y matriz de permisos/destinos descritos. Revisar campos no constituye autorización para modificar el modelo congelado.

Entrega actual: revisión documental y especificación propuesta, sin migraciones, cambios de permisos ni subidas reales. Se verifican enlaces/formato y coherencia documental; no se ejecuta build ni suites como prueba de una BDD inexistente. Falta aprobación estructural y verificación ejecutable de una futura implementación.

Referencias complementarias: [OWASP File Upload](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html), [Laravel 13 — File Storage](https://laravel.com/framework/docs/13.x/filesystem). Sustentan medidas técnicas, no autorizan nuevas reglas ni estructura institucional.
