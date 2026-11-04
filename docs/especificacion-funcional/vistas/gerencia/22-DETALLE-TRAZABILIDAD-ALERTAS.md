---
title: "Gerencia: detalle e historia de Alertas"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: STATIC_ALERT_DETAIL_AND_SOURCE_BOUNDARIES
runtime_verified: false
---

# Detalle de alerta y continuidad

## 1. Ubicación y objetivo

Drawer de lectura abierto desde «Ver detalle» en [Alertas del equipo](21-ALERTAS-GERENCIA.md). Mantiene filtros, página y fila de origen. En móvil ocupa el ancho disponible, con desplazamiento interno y footer accesible. Una apertura no añade un evento asistencial ni confirma que alguien haya actuado.

| Zona | Contenido | Requisito |
|---|---|---|
| Cabecera | Detalle de alerta, título y cerrar X | Título único con aria-labelledby; cierre con nombre accesible |
| Contexto | Nombre del residente y ubicación autorizada | Ubicación actual identificada como tal; no reconstruir ubicación histórica ausente |
| Estado | Prioridad y situación registradas | Etiquetas humanas; colores acompañados de texto/iconos |
| Metadatos | Fecha de registro, responsable, fecha límite y origen | Fechas locales, NULL explícito; sin ID, ISO técnico ni nombres de tablas |
| Aviso contextual | Sin responsable / límite guardado superado / fuente no disponible, según datos | Sin transformar aviso de supervisión en orden clínica |
| Resumen | Qué consta en el registro, texto autorizado breve | No revelar una descripción restringida mediante un resumen automático |
| Historial | Eventos, fechas, autores y contenido autorizado | Orden estable y completo mediante paginación si procede; no eventos inventados |
| Origen | Módulo y registro verificables, acceso contextual | Permiso de alertas no sustituye permiso del registro fuente |
| Footer | Cerrar detalle / Consultar origen si autorizado | «Cerrar detalle» nunca equivale a «Cerrar alerta» |

Tabs Resumen/Historial/Origen son propuesta de organización, no nuevos estados del dominio. La miniatura de diseño muestra un extracto del historial dentro de Resumen; el tab Historial permite el recorrido completo autorizado. Si no hay eventos, mostrar «No hay eventos registrados» y la limitación; no fabricar una creación o firma a partir de la fila actual.

## 2. Trayectoria y persistencia existentes

[Alerta](../../../../app/Models/Alerta.php) conserva estado y responsable actuales. [EventoAlerta](../../../../app/Models/EventoAlerta.php) conserva hechos de la trayectoria y su autor usuario. El responsable es personal, mientras el autor de evento es usuario: no son relaciones intercambiables. Una reasignación posterior no convierte al nuevo responsable en autor de hechos anteriores.

[AlertasService](../../../../app/Modulos/Alertas/Aplicacion/Acciones/AlertasService.php) y las operaciones autorizadas del módulo gestionan cambios e historia. El frontend no escribe eventos artificiales para rellenar la línea de tiempo. El evento de dominio, la notificación y Activitylog tienen responsabilidades distintas; no crear otra tabla de auditoría.

Origen `modulo + cod_registro` requiere resolución específica y comprobación de residente. Si una referencia no existe, apunta a otro residente, está anulada o el permiso fue revocado, el detalle explica «No se puede consultar el registro de origen» según motivo permitido, sin SQL/stack trace y sin inventar un enlace válido. La alerta no se elimina por ese fallo.

## 3. Datos actuales frente a históricos

La lista presenta estado actual de alerta. El historial muestra los eventos conservados, no una recreación con los datos del profesional, vínculo o habitación actuales. Los [vínculos profesionales](../../../../app/Modulos/Alertas/Aplicacion/Consultas/VinculosProfesionalesAlertas.php) usan roles actuales: no acreditan por sí solos la competencia histórica al momento del evento.

Para cada fecha, indicar qué significa: momento registrado de alerta, evento, límite o consulta. No usar «Atendida» si únicamente existe lectura o reconocimiento. «Atendida · por cerrar» se distingue de «Cerrada». Estados históricos desconocidos quedan legibles como registro anterior y fuera de una reclasificación automática.

## 4. Gráficas y formularios

Gerencia dispone de barras de **cantidades de alertas** por responsable/estado cuando la fuente está autorizada y el alcance es explícito. La alternativa tabular incluye grupo y cantidad. No se muestran tendencias de signos vitales, radar cognitivo, riesgo de DCL ni ranking profesional con un simple permiso de alertas. Abrir gráficas clínicas requiere permisos propios y finalidad del rol competente; no se ofrece como función gerencial por esta ficha.

No hay formulario de creación, intervención o cierre gerencial en este alcance. Los filtros no requieren Guardar. Un posible formulario de coordinación/gestión requerirá autorización funcional específica, validación de transición y estado concurrente; no se construye ni aprueba aquí por aprovechar un modal existente.

## 5. Estados UX y recuperación

- Cargando: skeleton y estado anunciado, controles de acción temporalmente deshabilitados con razón; no mantener el detalle anterior como si perteneciera al seleccionado.
- Sin datos o sin origen: mensaje concreto, continuidad hacia lista; no ocultar una alerta persistida.
- Permiso revocado/cuenta inactiva: denegar consulta y limpiar payload sensible de la sesión del componente conforme al flujo de seguridad.
- Error inesperado: mensaje seguro y opción de reintento; conservar filtros, reportar con contexto técnico mínimo.
- Escape, cerrar X y Cerrar detalle: mismo efecto de lectura, foco a la fila/botón que abrió el drawer. Sin pérdida silenciosa de captura: aquí no hay campos editables.
- Nueva información de otro actor: Actualizar relee estado actual; el frontend no muestra confirmación de una mutación que no realizó.

Reutilizar [drawer-livewire](../../../../resources/views/components/ui/drawer-livewire.blade.php), [callout](../../../../resources/views/components/ui/callout.blade.php), [status-badge](../../../../resources/views/components/ui/status-badge.blade.php) y la composición existente de [detalle](../../../../app/Modulos/Alertas/Presentacion/Vistas/livewire/alertas/modales/detalle.blade.php) solo para partes apropiadas a lectura. No copiar botones clínicos escondiéndolos únicamente con CSS.

## 6. Estado

Contrato de lectura y propuesta visual documentados. No se modifica aplicación, tablas, permisos ni datos. Las ilustraciones de [24](24-DISENO-IMAGENES-ALERTAS.md) contienen ejemplos ficticios; no acreditan eventos, autorías o atención real. Las [pruebas de aceptación](23-ACEPTACION-ALERTAS-GERENCIA.md) siguen pendientes de ejecución.
