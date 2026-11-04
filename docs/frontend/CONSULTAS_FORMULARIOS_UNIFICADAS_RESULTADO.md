# Consultas de formularios — unificación del 14/10/2026

## Alcance entregado

Se unifican la ubicación y composición de las consultas existentes en los formularios clínicos de Enfermería. No se agregan consultas, prescripciones, campos persistidos, permisos ni reglas clínicas para completar el diseño.

| Formulario | Accesos superiores | Contenido utilizado |
|---|---|---|
| Medicación | Ver historial / Ver evolución | Ventanas y consultas autorizadas existentes |
| Signos vitales | Ver historial / Ver evolución | Registros y gráfica del parámetro seleccionado |
| Dolor | Ver historial / Ver evolución | Episodio, registros y gráfica existentes |
| Ingesta | Ver historial / Ver evolución | Registros y gráfica existentes |
| Hidratación | Ver historial / Ver evolución | Aportes y gráfica existentes |
| Eliminación | Ver historial | Historial estructurado existente |
| Movilidad | Ver historial | Historial estructurado existente |
| Observación cognitiva | Ver historial / Ver evolución | Consultas existentes, sin puntaje clínico nuevo |
| Conducta | Ver historial / Ver evolución | Registros y conteos factuales existentes |
| Heridas / curaciones | Ver historial / Ver evolución | Curaciones y evolución de la herida seleccionada |

En Signos, Dolor, Ingesta e Hidratación ambas acciones utilizan la ventana existente: Historial abre/enfoca su bloque de registros; Evolución muestra la gráfica. No se presenta el subconjunto cargado como una historia exhaustiva. Los accesos a ficha clínica se conservan.

Las pantallas genéricas de Seguimiento y Registros que no tienen una consulta contextual independiente mantienen sus flujos existentes; no se introducen botones ficticios ni gráficas nuevas. La composición compartida se aplica automáticamente a las ventanas clínicas compatibles.

## Componentes e integración

- `ui.clinical-consultation-bar`: misma zona superior, orden Historial → Evolución, controles existentes y targets de al menos 44 px. En capturas con Alpine local se ubica al principio de la captura, después de la fecha; en los modales con contexto propio queda debajo de la identidad, como Medicación.
- `clinical-consultations.js` y `clinical-consultations.css`: distribución compartida para `clinical-trend-window` y los historiales nativos. Se reserva el lado disponible a partir de 1280 px. Si no hay 600 px legibles para la captura, se conserva la consulta superpuesta. El arrastre manual existente permanece disponible.
- El coordinador usa los límites reales de la ventana, observa cambios de tamaño y restaura la composición original al cerrar. No modifica datos Livewire ni envía operaciones clínicas.
- Se retiran las fórmulas locales de ancho de Signos, Dolor, Ingesta, Hidratación, Eliminación, Cognición, Conducta y Medicación. La variante de curación conserva su ancho original solamente cuando no hay consulta lateral.
- Cognición, Conducta y Heridas usan popovers de historial en lugar de un overlay que cubre la captura. En pantallas pequeñas conservan confinamiento de foco; el modo se actualiza también al redimensionar una consulta abierta.
- Heridas reutiliza su panel de evolución dentro de una ventana independiente. Las fotografías suspenden temporalmente la consulta y vuelven a ella al cerrar. Cambiar de herida, iniciar una identificación nueva o volver al residente cierra las consultas anteriores. La consulta de historial de solo lectura permanece disponible bajo su permiso existente.

## Verificación

- PHP relacionado: **122 PASS / 792 assertions** (Cognición, Conducta, Fotografías de heridas y Administración de medicación). No suite PHP global ni PostgreSQL nuevo en este lote de presentación.
- Suite JS: **192 PASS**. Tras el último ajuste de cierre contextual de Heridas: **16 pruebas enfocadas PASS**.
- Build final: **PASS**; permanece el aviso general de bundle >500 kB.
- Harness general: **60 casos en claro** (12 vistas × 1440/1280/1024/768/390) y **24 en oscuro** (1440/390), con apertura/cierre de las consultas superiores disponibles. Se comprueba separación lateral, viewport y ausencia de overflow en captura y consulta. Cero errores JS.
- Cognición y Conducta: cinco anchos PASS, foco, conservación de captura, consulta lateral y transición 1280 → 390 → 1280 con historial abierto. Se conservan los gates previos de empty state, un registro y errores.
- Medicación: **50 casos en claro / 10 en oscuro**, cinco y dos anchos respectivamente. Historial y evolución por separado junto a la captura, ampliación/restablecimiento, cierre independiente, centrado restaurado y dosis conservada.
- Heridas, comprobación final adicional: **cinco anchos en claro / dos en oscuro**, con dos curaciones sintéticas, gráfica real de dos puntos, fotos y retorno a la consulta. La transición de tamaño se comprueba en claro. Los cambios de foco usan el retraso real de activación del trap, no una captura instantánea antes del reflow.

La evidencia visual usa Blade/Alpine renderizado por pruebas con datos sintéticos y transporte UI simulado. No acredita persistencia desde la sesión operativa ni certificación WCAG/producción. La persistencia y los permisos se cubren por las pruebas PHP indicadas; no se cambian en este lote. El harness general no acredita carga del CDN de iconos, mientras los harnesses específicos de Cognición/Conducta esperan su carga.

## Evidencias y entrega

Evidencias locales en `%TEMP%/remembermind-consultations/` y `%TEMP%/remembermind-forms-ux-qa/consultations-*`. Logs y sondas conservados bajo `storage/app/qa/consultas-compartidas/`, ignorado por Git.

Sin cambios nuevos de BDD, autorización, clasificación clínica ni consultas backend. Sin commit/push adicional: el checkout contiene trabajo previo mezclado que se conserva.
