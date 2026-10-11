---
title: "Eliminación V2 — resultado de implementación y verificación"
status: ACCEPTED
version: "1.0"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verification_scope: ELIMINACION_V2_WORKTREE_TESTS_AND_VERIFIED_RUNTIME
runtime_verified: true
---

# ELIMINACIÓN V2 — RESULTADO

Alcance: Enfermería → Mis residentes → Eliminación. Rama `FORMULARIOS`, base `7a6758e8`, árbol de trabajo sin commit. Los cientos de cambios previos no pertenecen a este lote. **ACCEPTED / VERIFIED_RUNTIME**, exclusivamente en este alcance y árbol de trabajo; no certifica producción ni otros módulos.

## Schema

- Table: `registros_eliminacion`.
- New tables: **NONE**. Inventario operacional: 71, sin cambio.
- Added columns: `cantidad_cualitativa`, `volumen_ml`, `color_orina`, `aspecto_orina`, `olor_orina`, `tipo_miccion`, `tipo_bristol`, `color_heces`, `esfuerzo_defecacion`, `presencia_sangre`, `presencia_moco`, `molestia_eliminacion`, `descripcion_molestia`.
- Removed columns: **NONE**.
- Legacy preserved: `cantidad`, `caracteristica`; no backfill ni unidad inferida.
- Other tables modified: **NONE**.

Autoridad: [decisión aprobada](../base-de-datos/DECISION_ELIMINACION_V2.md), [auditoría CURRENT](../base-de-datos/AUDITORIA_FORMULARIO_ELIMINACION.md). Cada confirmación agrega un evento longitudinal independiente; no sobrescribe historia. No interpretación clínica ni alerta automática.

## Migration

| Gate | Resultado |
| --- | --- |
| SQLite migrate / rollback / reapply | PASS |
| PostgreSQL migrate / rollback / reapply | PASS |
| Columnas anteriores y fila legacy conservadas | PASS |
| Inventario sin nuevas tablas | PASS |

Aplicada en la base aislada de runtime `remembermind_experto_test_20261009_dolorv2` y, tras confirmar nombre/motor y estado Pending, exclusivamente esta migración aditiva en la base local configurada `remembermind_dev`. Fresh/seed únicamente en la base nueva desechable `remembermind_eliminacion_fresh_20261009`. No se ejecutó fresh sobre la base de desarrollo del propietario ni se aplicaron otras migraciones pendientes.

## Urinaria

Cantidad cualitativa, volumen, color, aspecto, olor, tipo de micción, sangre y molestia: **PASS** en pruebas de contrato/backend. Volumen opcional 0–999999.99, dos decimales; cero y NULL distintos. Catálogos compartidos entre modelo, servicio y presentación. Rechazo de datos intestinales no NULL, incluyendo false/0.

## Intestinal

Cantidad cualitativa, Bristol, color, esfuerzo, sangre, moco y molestia: **PASS** en pruebas de contrato/backend. Bristol entero opcional 1–7, SVG propios y descripciones de forma; sin diagnóstico. Rechazo de datos urinarios no NULL. Booleanos true/false/NULL conservados. Descripción opcional 250; observación opcional trim/5000.

## Continencia

**PASS.** Opciones compatibles por tipo; no se representa incontinencia como error. Cambio de rama confirmado limpia específicos e incompatibles y conserva comunes/observaciones. Escape cancela el cambio y preserva captura.

## History

| Criterio | Resultado |
| --- | --- |
| Legacy literal con «Dato de registro anterior» | PASS |
| Nuevos campos estructurados y 0.00 mL | PASS |
| Filtros Todos/Urinaria/Intestinal | PASS |
| Grupos Hoy/Ayer/fecha | PASS backend/JS; runtime Hoy |
| Último global del mismo tipo, independiente del límite 40 | PASS |
| Conteos reales de toda la jornada | PASS |
| Seis recientes / cuarenta en ventana, límite comunicado | PASS |
| Otro residente / futuro / anulado excluidos | PASS |
| Sin permiso de lectura, sin historia expuesta | PASS |
| Historial vacío: sin gráfica ni tendencia ficticia | PASS runtime |

No se proyecta la captura como evento guardado. Un histórico previo con cantidad `350` aparece sin mL inventados. Sin literales null/NaN/undefined/Infinity/[object Object] visibles en los estados comprobados.

## Popup

**PASS runtime:** abrir, cerrar, filtros, mover por flechas, ampliar, restablecer, Escape y devolución de foco a «Ver historial». Volumen/observación conservados. Ventana longitudinal sin gráfica numérica. La ventana compartida acepta un label opcional «historial»; los demás formularios conservan el label por defecto «gráfica».

## Guardado, limpieza y seguridad

- **PASS:** autor, jornada, residente, fecha/hora y estado del servidor; cuenta/personal activos, permiso explícito, asignación/contexto vigente, reautorización y competencia. Sin escritura clínica por rol administrativo.
- **PASS runtime:** selección de tipo sola activa descarte; «Seguir editando» conserva captura; limpieza confirmada vacía campos/tipo y devuelve foco al selector, manteniendo contexto/historia.
- **PASS runtime PostgreSQL:** bloqueo SHARE controlado en la base aislada + statement_timeout de 2 s. Error seguro, formulario abierto, volumen 123.45/observación intactos, ningún éxito falso y COUNT sin fila nueva. Tras liberar bloqueo, reintento crea exactamente un evento y muestra resultado real. «Registrar otro evento» empieza vacío.
- **PASS SQLite:** trigger real de fallo de INSERT y recuperación. No se usa mock de éxito ni se modifica BDD real para provocar el error.

## Responsive

| CSS viewport real | Geometría / reflow / overflow | Captura visual completa |
| --- | --- | --- |
| 1440 × 700 | PASS | PASS, componente completo |
| 1280 × 700 exactos | PASS | PASS |
| 1024 × 700 | PASS | PASS |
| 768 × 700 | PASS | PASS |
| 390 × 700 | PASS | PASS |

`innerWidth` y `scrollWidth` coinciden en los cinco tamaños. Modal/popup dentro del viewport. Captura/continuidad aproximadamente 65/35 cuando la ventana está cerrada; una columna al abrir la ventana y en móvil. Bristol 7/4/3 columnas según espacio. Móvil: popup inferior de 280 px, controles y texto conservados.

**Problema de herramienta resuelto:** los intentos iniciales de 900 px de alto excedían el área capturable. Se verificaron los anchos CSS exactos compensando la escala, se esperó al render en una llamada separada y se usó una altura real de 700 px, no impuesta por el contrato. La captura nativa preserva modal, controles y footer. En 1440 se revisó el componente completo; el margen de fondo derecho de la página excede el área de captura, sin ocultar controles. Las capturas iniciales recortadas no se utilizan como aceptación. Chrome fue autorizado, pero siguió no disponible incluso tras reiniciar las herramientas; se cerró el gate con IAB.

Evidencia final ignorada: `storage/app/qa/eliminacion-1440-final.jpg`, `eliminacion-1280-700.jpg`, `eliminacion-1024-final.jpg`, `eliminacion-768-final.jpg`, `eliminacion-390-final.jpg`, `eliminacion-390-popup.jpg`, `eliminacion-390-texto.jpg`. Además de los viewports finales, se comprobaron geométricamente 1440/1280/1024 a 900 px de alto, tablet 768×1024 y móvil 390×844.

## Accessibility

**PASS en lo comprobado:** labels, grupos, aria-pressed, foco visible, retorno de foco, Escape, confirmación de cambio/discard y captura usable con `prefers-reduced-motion: reduce` realmente activo en Windows. Layout sin animación y transiciones 0 s. Botones de captura ≥44 CSS px (43.998 por redondeo geométrico). Indicadores acompañados de texto, no solo color.

Contraste calculado de estilos efectivos claros: texto de card 8.47:1; selección verde 5.01:1; tipo intestinal 4.86:1; urinario 5.70:1. No se afirma certificación WCAG. Revisados en móvil: Bristol, texto largo de 5000 caracteres, descripción de 242/250, cero, error de volumen negativo, popup vacío con filtros y footer; sin overflow ni pérdida de captura.

## Tests

| Gate | Resultado |
| --- | --- |
| Eliminación SQLite final | PASS: 15 pruebas / 209 aserciones |
| Eliminación PostgreSQL | PASS: 14 pruebas / 198 aserciones; 1 skip específico del trigger SQLite, sustituido por fallo real PG runtime |
| Regresión Signos/Dolor/Ingesta/Hidratación/residentes/turnos/seguridad | PASS: 311 pruebas / 2888 aserciones |
| JS completo final | PASS: 139 pruebas |
| JS Eliminación | PASS: 10 pruebas |
| Build final / Blade cache | PASS |
| PostgreSQL fresh seed | PASS, base desechable nombrada arriba |
| PHP full final | PASS: 976 pruebas / 19603 aserciones; 15 skips por condiciones de motor/entorno |
| Visual QA | PASS en el alcance documentado; cinco anchos, estados, popup, teclado y movimiento reducido real |

Logs y helpers permanecen ignorados en `storage/logs/eliminacion-*` y `storage/app/qa/`, solo con datos sintéticos.

## Drift y revisión

1. **CONTRACT_DRIFT / BUG_PRODUCT preexistente:** escritores genéricos usaban atributos inexistentes. Se consolidó el servicio canónico y se retiró la captura obsoleta de la pantalla antigua.
2. **CONTRACT_DRIFT aprobado:** seis tests antiguos y el dirty mapping exigían cantidad/característica legacy. Se actualizaron al contrato estructurado autorizado, manteniendo negativos y límites de autorización.
3. **CONTRACT_DRIFT / BUG_TEST introducido por la nueva migración:** rollback experto suponía una sola migración posterior. Ahora retira Dolor + Eliminación antes de las 23 expertas; conserva todas las comprobaciones anteriores y añade ausencia/reaplicación de volumen_ml. Targeted: PASS, 55 aserciones.
4. **BUG_TEST ambiental:** cursor glow suponía animaciones activas pese a Windows reduced motion. La fixture establece no-preference para comprobar el estado animado y luego verifica explícitamente reduce; ninguna expectativa se elimina.
5. **CONTRACT_DRIFT / BUG_TEST preexistente:** footer público esperado petroleum-950, mientras CSS CURRENT usa --rm-bg-app. El test comprueba la superficie compartida efectiva del shell, con ambos selectores oscuros; producto público intacto.

Hallazgos corregidos durante revisión: último global fuera de la lista truncada, dirty baseline después del remount, foco/Escape en confirmación de cambio, limpieza de tipo pendiente, label de ventana histórica y target de «Sin registrar» Bristol. Correcciones verificadas con pruebas focalizadas y runtime pertinente.

Revisión local limitada al lote; no acepta el árbol preexistente. Caveman Cloud evidence-review: **UNAVAILABLE**, sin herramientas/CLI disponibles; no se infiere organización/proyecto ni se leen payloads clínicos.

## Commit

**NONE.** Sin stage, commit ni push. No se continúa con Movilidad.

## Ajuste visual solicitado — 2026-10-10

- Captura neutral con bordes/jerarquía más definidos; continuidad diferenciada por superficie, separador y conteos azul/coral.
- Urinaria conserva azul clínico e Intestinal coral en filtros, etiquetas, líneas del historial y botón «Ver historial». Selecciones específicas usan el acento del tipo; «Sin registrar»/«No valorado» permanecen neutrales. No se asigna gravedad clínica por color.
- Popup con etiquetas + icono + hora y filas separadas para distinguir atributo y valor. Datos, reglas, permisos, schema y guardado intactos.
- QA visual del ajuste en 1440/1280/1024/768/390 CSS px sin overflow horizontal; filtros ejercitados con historia sintética, targets de 44 px y captura conservada al cerrar. Evidencia: `storage/app/qa/eliminacion-ui-color-1280.jpg`.
- Verificación acotada: 10 JS PASS; prueba PHP historia/continuidad/filtros PASS (8 aserciones); build y Blade cache PASS. El PHP full de la sección anterior pertenece al cierre anterior; no se repitió por este ajuste de presentación.
- Para revisar el día actual se añadieron jornada/asignaciones sintéticas únicamente en la base aislada de QA. No se reescribió historia clínica ni se insertaron eventos para este ajuste.

### Corrección de semántica cromática — 2026-10-10

La propietaria corrigió el uso de coral para Intestinal: se sustituye por tonos tierra neutrales en captura, historial y filtros. Rojo queda reservado a alertas/errores; la opción literal «Rojizo» muestra su propia muestra de color observado, sin clasificar gravedad. «Color de heces» usa muestras marrón, marrón claro, marrón oscuro, rojizo y negruzco; selección con borde/fondo y tinta contrastada del mismo tono. Otro/Sin registrar conservan neutralidad. Verificados los cinco cambios de selección y composición a 1280/390 px con datos sintéticos, sin guardar eventos. Evidencia: `storage/app/qa/eliminacion-heces-colores.png`. Sustituye la asignación coral del ajuste anterior, sin cambios de datos o reglas clínicas.

Bristol: ilustraciones en café claro cálido; elección marcada con fondo café suave, borde interior de 2 px y badge «✓ Elegido». Espacio del badge reservado para evitar saltos al seleccionar. Mantiene `aria-pressed` y no atribuye gravedad. Cambio Tipo 4 → Tipo 1 verificado con selección única en 1280/390 px; sin overflow ni persistencia. JS focalizado: 10 PASS; build: PASS. Evidencia: `storage/app/qa/eliminacion-bristol-cafe.png`.
