---
title: "Gerencia: carga, edición y privacidad de Galería"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: STATIC_GALLERY_FORM_STORAGE_AND_HISTORY_REVIEW
runtime_verified: false
---

# Carga y mantenimiento de fotografías

## 1. Formulario de nueva carga

Editor dentro de [Galería](29-GALERIA-GERENCIA.md). Mantener seleccionados/previews y formulario visibles al fallar; no vaciar todo el lote para resolver un archivo incorrecto. La [imagen propuesta](32-DISENO-IMAGENES-GALERIA.md) coloca previsualización a la izquierda y datos a la derecha; al reducir ancho se apilan.

| Campo | Entrada/validación existente | Ayuda visible |
|---|---|---|
| Fotografías | JPG/JPEG, PNG o WebP, contenido real de imagen | Hasta 2 MB y 12 megapíxeles por foto; hasta diez fotos y 6 MB por lote |
| Título descriptivo | Requerido, string hasta 160 | Se aplica al lote; después se edita cada fotografía |
| Fecha de fotografía | Opcional, fecha válida Y-m-d | Momento de toma conocido; dejar vacía si no se conoce |
| Descripción | Opcional hasta 2000 | Explicar espacio/actividad sin datos clínicos o identificaciones innecesarias |
| Descripción accesible | Opcional hasta 255 | Descripción útil; si falta se usa título, no «imagen» genérico |
| Ofrecer en bienvenida | Booleano explícito, no publicación externa | Solo conjunto interno Gerencia/Administración ya autorizado |

Autor, fecha técnica, estado inicial y rutas se derivan del backend; no se eligen desde formulario. Los límites proceden de la [decisión aprobada de Galería](../../../base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md), no de una restricción nueva añadida por esta tarea.

La vista previa se etiqueta «Sin guardar». Guardar no se habilita hasta que la carga temporal necesaria pueda validarse. El cliente ayuda a detectar límites, pero el backend verifica MIME/contenido, dimensiones y peso. No se aceptan SVG, ejecutables o archivos falsamente renombrados por tener extensión permitida.

## 2. Edición y preservación

Editar permite título, descripción, fecha conocida, descripción accesible y elegibilidad de bienvenida. No reemplaza archivo, miniatura, hash, autor o fecha de registro. Si se necesita otra imagen, se realiza una carga independiente; no sobrescribir historia del recurso original. [FotografiaInstitucional](../../../../app/Models/FotografiaInstitucional.php) protege atributos de identidad/contenido al actualizar.

Archivar conserva bytes e historia y retira imagen/miniatura de las rutas de consulta y del conjunto de bienvenida. Reactivar restablece disponibilidad conforme al permiso y al booleano conservado: no debe sorprender que una fotografía vuelva a ser elegible si ya tenía la opción marcada; explicar el efecto en la confirmación propuesta. Archivar no es borrado físico ni una acción sobre historia clínica.

Confirmación propuesta: nombre/título, efecto, Cancelar y Archivar fotografía o Reactivar fotografía. Usar componentes existentes, foco y loading; una autorización revocada entre abrir y confirmar produce denegación backend. No atribuir a SQL un control de versión de formulario que aún no existe.

## 3. Atomicidad por imagen y cargas parciales

[GestionarFotografiasInstitucionales](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarFotografiasInstitucionales.php) y [AlmacenamientoFotografias](../../../../app/Modulos/Institucion/Infraestructura/Fotografias/AlmacenamientoFotografias.php) separan SQL y archivos. Cada fotografía válida se guarda en transacción con su auditoría; fallo SQL/auditoría compensa únicamente archivos nuevos. No llamar transacción global a todo el lote.

Resultado por archivo: Guardada, Ya estaba en la galería (estado conservado), o No guardada con causa/reintento. El resumen suma guardadas, existentes y pendientes; si falla una de tres, no mostrar «Todo guardado». Los fallidos siguen disponibles para reintentar cuando sea técnicamente posible.

Duplicación por SHA-256 del contenido WebP **normalizado**, no identificación facial, similitud visual ni hash del original temporal. Repetir la carga no cambia autor/metadatos previos ni reactiva un recurso archivado automáticamente. Conflicto UNIQUE concurrente requiere recuperar el recurso existente y limpiar solo los archivos nuevos. Un fallo de limpieza se reporta para recuperación, nunca se oculta como integridad demostrada.

## 4. Almacenamiento privado y continuidad

La implementación normaliza WebP principal de hasta 2560 px y miniatura de hasta 480 px, conserva proporción/orientación admitida y elimina cabeceras EXIF al recodificar. Son derivados del recurso institucional, no fotografías clínicas ni originales forenses. No afirmar conservación byte a byte de la toma original.

Disco local privado y rutas protegidas por autenticación, cuenta, rol, permiso, Policy, estado y variante permitida. No `asset(storage/...)` público como sustitución. No hashes/rutas privadas en tarjetas o logs innecesarios. No transmitir imágenes a renderizadores/CDN externos para consultar o generar miniaturas.

El [rotador de bienvenida](../../../../app/Modulos/Reportes/Presentacion/DashboardPhotoRotation.php) usa fotografías activas elegibles dentro del contexto aprobado y mantiene fallback cuando no hay ninguna. Marcar elegibilidad no garantiza aparición inmediata o posición fija: no inventar orden editorial o nueva rotación. Archivar una foto usada exige retirada de selección sin romper la bienvenida.

## 5. Personas, acceso y ausencia de publicación

El alcance aprobado es imágenes del lugar, no retratos ni documentación clínica. Las propuestas usan espacios ficticios vacíos. Si una fotografía real incluye personas identificables, revisar finalidad y autorización institucional aplicables antes de usarla; esta ficha no inventa un formulario legal de consentimiento, una casilla de «aprobación» ni un permiso para publicar imágenes. No guardar esa autorización en título/descripcion como sustituto de un proceso definido.

Gerencia y Administración gestionan dentro de su autorización existente. Superadministración consulta, sin facultad automática de gestión; otros roles no adquieren Galería porque vean imágenes propias de sus dashboards. La galería no administra foto de avatar, foto de residente, heridas, estudios o expediente.

## 6. Estados UX

Carga temporal, validación, guardado, resultado parcial, duplicado, vacío, búsqueda sin coincidencias, archivo faltante y permiso revocado se comunican por separado. Las etiquetas no deben imprimir valores técnicos sin traducción. Cancelar/nueva carga/cambio de fotografía con dirty state ofrecen continuar editando o descartar; no borran contenido guardado. Verificar también salida por navegación y cierre del overlay si se incorpora.

Nombre de archivo en carga es dato no confiable: escapar en frontend, no ejecutarlo ni reutilizarlo como ruta de almacenamiento. Fecha ausente se muestra como «Fecha de fotografía sin registrar». Imagen no disponible tiene fallback seguro y texto; no repetir otra imagen como si fuera esa fotografía.

## 7. Estado de esta ficha

Mecanismos existentes localizados y ajustes UX especificados. No hubo carga de imágenes de ejemplo al módulo, cambio de permisos/DDL ni ejecución de pruebas de producto en esta tarea. Ver [aceptación](31-ACEPTACION-GALERIA-GERENCIA.md) y [matriz del rol](33-MATRIZ-COBERTURA-GERENCIA.md).
