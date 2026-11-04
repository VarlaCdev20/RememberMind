---
title: "Gerencia: Galería del centro"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: OWNER_DECISION_AND_STATIC_GALLERY_REVIEW
runtime_verified: false
---

# Galería del centro

## 1. Fuentes y alcance

**27B** mantiene una entrada directa «Galería» en el menú de Gerencia, sin ampliar permisos o privacidad. La [decisión del 17/10](../../../base-de-datos/DECISION_GALERIA_INSTITUCIONAL_2026_10_17.md) ya aprobó galería independiente, gestión para Gerencia/Administración, almacenamiento privado, archivo y reactivación. [Decisiones del rol](../../roles/GERENCIA_CUESTIONARIO_PENDIENTE.md), [baseline BDD](../../../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [contrato revisado](../../../frontend/PROPUESTA_GALERIA_INSTITUCIONAL_2026_10_17.md) gobiernan su alcance.

La entidad es una fotografía institucional del lugar, no publicación, álbum, retrato de cuenta/residente o documento clínico. No hay likes, comentarios, muro público, categorías o destinos múltiples por añadir esos controles al diseño. Los conteos 71→72 del documento del 17/10 son su corte histórico, no el inventario actual de toda la BDD.

El [informe del 17/10](../../../frontend/GALERIA_INSTITUCIONAL_2026_10_17.md) acredita implementación según ese corte y deja QA de sesión pendiente. El código actual conserva galería/acción/archivos/Policy/pruebas. Esta entrega documenta y propone ajustes de presentación: **no está construyendo nuevamente el módulo ni revalidando en ejecución aquel informe**.

## 2. UX FLOW y ubicación

Gerencia → Galería → buscar por título/descripción o elegir Activas/Archivadas → consultar fotografía activa → editar metadatos o archivar si autorizado → confirmación real → volver conservando búsqueda. Subir fotografías abre el editor existente, permite revisar archivos/datos y solo después guardar. La gestión usa permiso explícito y Policy en backend; ocultar acciones no basta.

Ruta actual `admin.galeria.index`, definida en [Institucion](../../../../app/Modulos/Institucion/Presentacion/Rutas/web.php), con archivos mediante `admin.galeria.archivo`. No se crea otra galería por rol. La opción «Ofrecer en la bienvenida de Gerencia y Administración» reutiliza el conjunto aprobado; no publica en internet ni concede acceso a familiares u otros profesionales.

## 3. Composición de pantalla

| Zona | Contenido | Interacción |
|---|---|---|
| Cabecera | «Galería del centro» y descripción breve | Subir fotografías si puede gestionar |
| Filtros | Buscar título/descripción; Mostrar Activas/Archivadas | Filtrar consulta y reiniciar página; Limpiar como mejora de interacción |
| Resultado | Cantidad del filtro y estado consultado | No confundir fotografías con residentes/actividades |
| Rejilla | Miniatura, título, descripción breve, fecha y elegibilidad de bienvenida | Ver fotografía activa, Editar datos y Archivar/Reactivar según permiso |
| Editor | [Formulario de carga/edición](30-CARGA-PRIVACIDAD-GALERIA.md) | Carga temporal distinguida de fotografía guardada |
| Pie | Rango/página y navegación | Mantener filtros; no ocultar que existen más resultados |

Miniatura con proporción uniforme sin deformar; fotografía completa conserva proporción al abrirse. Descripción extensa se abre/expande con acceso, no ocupa toda la tarjeta. Nombres de archivos temporales ayudan a revisar una carga; PK, rutas privadas y hash no son títulos visibles. No mostrar carruseles automáticos, gráficas o indicadores decorativos en el directorio.

## 4. Datos y procedencia

| ID | Dato visible | Fuente y significado |
|---|---|---|
| GL01 | Fotografía seleccionada | Archivo temporal validado en carga, distinto del recurso guardado |
| GL02 | Título descriptivo | fotografias_institucionales.titulo, requerido, máximo 160 caracteres |
| GL03 | Descripción | descripcion, opcional, máximo 2000; texto institucional, no historia clínica |
| GL04 | Fecha de fotografía | fecha_fotografia, opcional; ausencia no se rellena con fecha de subida |
| GL05 | Descripción accesible | texto_alternativo, opcional, máximo 255; fallback al título sin inventar identidad de personas |
| GL06 | Elegibilidad de bienvenida | usar_en_bienvenida booleano, conjunto compartido Gerencia/Administración; estado activo necesario |
| GL07 | Estado | ACTIVA/ARCHIVADA; archivo conservado, sin borrado ordinario |
| GL08 | Autor de carga | cod_usuario → usuarios, derivado de sesión; edición posterior no cambia autor original |
| GL09 | Registro/actualización | fecha_registro y fecha_actualizacion, distintas de fecha de fotografía |
| GL10 | Previsualización/miniatura | temporal local en carga o ruta privada autorizada en guardado; sin enlace público de storage |
| GL11 | Tamaño/formato/dimensiones | Archivo elegido y resultado normalizado tienen medidas distintas; no confundir peso original con WebP guardado |
| GL12 | Resultado por archivo | Guardada / Ya existente / Pendiente con causa, derivado del resultado real de cada operación |

Estos doce datos no son doce columnas nuevas. El [modelo existente](../../../../app/Models/FotografiaInstitucional.php) conserva los 17 atributos aprobados; imágenes, miniaturas y auditoría reutilizan las relaciones actuales. No introducir JSON/EAV, listas de categorías o datos de consentimiento en descripción para evitar una decisión estructural.

## 5. Botones y efectos

| ID | Acción | Efecto y límites |
|---|---|---|
| GL-A01 | Subir fotografías | Abre editor vacío con autorización; no guarda ni descarta cambios silenciosamente |
| GL-A02 | Buscar | Consulta título/descripción, con loading y reset de página; no modifica imágenes |
| GL-A03 | Activas/Archivadas | Cambia conjunto; archivadas muestran ficha/placeholder, no imagen privada accesible |
| GL-A04 | Limpiar | Restablece búsqueda/estado de consulta; no elimina archivos ni metadatos |
| GL-A05 | Ver fotografía | Solo activa y autorizada; abre imagen completa sin convertirla en publicación pública |
| GL-A06 | Editar datos | Editor de metadatos existentes; no reemplaza bytes/autor/identidad |
| GL-A07 | Archivar | Cambia estado conservando bytes/historia; bloquea URL y elegibilidad efectiva de bienvenida |
| GL-A08 | Reactivar | Restablece ACTIVA según autorización, no crea duplicado ni nueva autoría |
| GL-A09 | Seleccionar fotografías | Carga temporal con límites y feedback; selección no es guardado |
| GL-A10 | Guardar fotografías/cambios | Ejecuta Action y comunica resultado exacto por archivo o edición |
| GL-A11 | Cancelar | Si hay cambios, ofrece conservar edición o descartarla; nada se guarda por cerrar |
| GL-A12 | Anterior/Siguiente | Paginación de fotografías del mismo filtro, foco/contexto conservados |

Limpiar, una presentación más compacta, el detalle visual y las confirmaciones de archivo son propuestas UX; no se declaran implementadas por figurar aquí. La vista actual abre fotografía activa en nueva pestaña protegida. Un futuro visor modal reutiliza el overlay compartido y la misma ruta/Policy, sin inventar permisos, navegación a archivadas o descarga pública.

## 6. Código actual y límites por comprobar

- [GaleriaInstitucionalPanel](../../../../app/Modulos/Institucion/Presentacion/Livewire/Paginas/GaleriaInstitucionalPanel.php) consulta autor renovado, valida estado, pagina 12 y conserva fallidos para reintento. La búsqueda `like` debe comprobar acentos/caso y comportamiento por motor; no afirmar equivalencia por usar una sola base.
- [Policy](../../../../app/Modulos/Institucion/Presentacion/Policies/FotografiaInstitucionalPolicy.php): Gerencia/Administración con permisos explícitos; Superadministración lectura global sin gestión automática; preview no escribe. La ruta también exige permiso de lectura: verificar grant efectivo, no sortear la combinación por el nombre de rol.
- [Action](../../../../app/Modulos/Institucion/Aplicacion/Acciones/GestionarFotografiasInstitucionales.php) reautoriza, bloquea filas para edición/estado y audita. El bloqueo SQL no demuestra detección de dos formularios antiguos: no se observa versión esperada en la entrada; diseñar/verificar conflicto antes de prometer que una edición obsoleta nunca sobrescribe otra.
- [Vista actual](../../../../app/Modulos/Institucion/Presentacion/Vistas/livewire/galeria-institucional-panel.blade.php) usa confirmación nativa para cancelar/reemplazar editor con cambios; otras salidas, navegación y mutaciones mientras está abierto requieren revisión. Archivar/reactivar actuales ejecutan directamente: una confirmación contextual es propuesta de claridad, no mecanismo de seguridad suficiente.
- [Controlador privado](../../../../app/Modulos/Institucion/Presentacion/Http/Controllers/FotografiaInstitucionalController.php) autoriza cuenta/estado y recurso, devuelve private/no-store/nosniff, no imagen archivada. Un 404 por archivo faltante es distinto de 403 por permiso: ofrecer error seguro sin inventar contenido o filtrar rutas internas.

No se modificaron esos archivos. Los [casos de aceptación](31-ACEPTACION-GALERIA-GERENCIA.md) y [propuestas visuales](32-DISENO-IMAGENES-GALERIA.md) delimitan la comprobación posterior.
