# Medicación — composición operativa, 15/10/2026

## Alcance y fuentes

Solicitud: mejorar la vista de Medicación a partir del diseño compartido aprobado. Entrada principal: `/admin/enfermeria/medicacion`, `SaludAdministracionMedicacionPanel`; la misma vista tiene consumidores clínicos existentes. Fuentes: AGENTS, contrato visual compartido, contrato del módulo de Medicación y componentes reales. No se recibió una nueva referencia estética para esta vista; se conserva la paleta greige/salvia, las superficies claras, tipografías y escala compacta del sistema.

## Antes y flujo aplicado

La cabecera mezclaba título pequeño, nombre institucional fijo y seis chips. La navegación duplicaba Kardex/Historial con tres accesos adicionales. Los seis filtros principales comprimían búsqueda y vías, y las tablas resultaban difíciles de consultar en móvil.

Flujo: identificar contexto/fecha → revisar resumen de dosis → seleccionar Kardex/Próximas/Omisiones/Historial → filtrar → abrir el detalle existente. La consulta fuera de turno mantiene su indicación de solo lectura. Las operaciones clínicas y permisos permanecen en los mecanismos existentes.

## Implementado

- Cabecera con `collection-header`, título de 26 px, icono de 48 px, fecha y contexto actual; se elimina el nombre institucional fijo de esta vista.
- Cuatro `metric-card` compartidas: pendientes, retrasadas, administradas y omitidas. Número junto al texto, icono visible sin superposición; singular/plural según el dato.
- Una sola navegación con cuatro vistas y `aria-current` en la selección.
- `filter-bar` canónica: búsqueda, estado, residente y «Más filtros». Horario, vía, tipo de indicación y fecha siguen disponibles en el área expandible. Labels asociados, `aria-expanded` y `aria-controls`.
- Los filtros activos reutilizan `active-filter-chip` y `clear-all-filters`, incluyen todos los criterios del Kardex y permiten retirarlos individualmente. Se corrige la posición heredada del botón de limpieza que podía interceptar clics de otros controles.
- `status-badge` compartido para estados de dosis; etiquetas del backend conservadas. No se añaden umbrales ni nuevas severidades.
- Tabla en escritorio y tarjetas con labels en móvil para Kardex, próximas dosis y omisiones. Identidad y medicamento preceden a horario/ubicación, dosis/vía y estado/acción. Targets de 44 px y foco visible.
- Historial conserva los registros y la estructura longitudinal; recibe los estilos del contenedor y nombres accesibles para sus selectores/campo de medicamento.
- Nuevo patrón `medication-workspace.css` consume tokens existentes y se limita a esta superficie. El parcial `kardex-filtros` queda registrado en el mapa modular.

## Corrección relacionada de consulta

La agenda de hoy recibía `today()` como instante (00:00), excluyendo órdenes emitidas después de medianoche mediante el filtro `fecha_hora_prescripcion <= instante`. Se reproduce el defecto con una prescripción de las 07:00, consulta de las 09:00 y horarios 08:00/20:00; inicialmente el conteo esperado fallaba. La consulta usa `now()` cuando la fecha seleccionada es hoy. Las fechas distintas conservan su comportamiento previo. El caso final confirma una dosis vencida, una pendiente y cero administraciones creadas. No se cambia el servicio de agenda, la prescripción ni su persistencia.

## Verificación

| Evidencia | Resultado y alcance |
|---|---|
| PHP relacionado | **21 PASS / 179 assertions**: FiltrosMedicacion, TabsMedicacionFlujo, AdministracionMedicacionEnfermeria, ModalesMedicacionSeguridadClinica, AgendaMedicacion y regresión de orden del mismo día |
| Cabecera final | **1 PASS / 12 assertions** después del ajuste de singular/plural |
| JS compartido | **4 PASS**, consistencia de shell |
| Build final | **PASS**, aviso general existente de bundle >500 kB |
| QA claro | **30 PASS**: seis estados × 1440/1280/1024/768/390 px |
| QA oscuro | **12 PASS**: mismos estados × 1440/390 px |
| Controles | Filtros, navegación y botones de tabla ≥44 px; expansión/cierre de «Más filtros» ejercitados |
| Contenido | Sin overflow horizontal ni errores JS; contraste de textos/estados muestreado ≥4.5:1; sin NaN/undefined/Infinity/[object Object] visibles |
| Navegador conectado | Vista nueva cargada; navegación Kardex/Historial operativa, sin overflow ni formularios guardados |

Estados sintéticos: Kardex con tres dosis, próximas, omisiones, historial confirmado, búsqueda sin coincidencias y solo consulta. HTML/snapshot se exportan desde Livewire real contra SQLite en memoria; los registros del escenario se crean mediante el formulario existente. El harness visual carga Blade/bundle reales con transporte Livewire simulado, por lo que no se presenta como prueba de escritura desde navegador. Capturas revisadas en los cinco anchos y temas. Evidencia local ignorada por Git: `storage/app/qa/medicacion-workspace-2026-10-15/`.

## Límites y entrega

Sin cambios BDD, catálogos, permisos, prescripción, historia, formularios de administración/omisión ni sus modales. Las etiquetas de estado se representan sin cambiar reglas clínicas. Se conserva deuda previa de los parciales de lectura fuera del alcance de esta composición; no se declara una auditoría integral del backend ni certificación de accesibilidad/producción. PostgreSQL y la suite PHP completa no se ejecutaron para esta entrega acotada. Archivos compartidos contienen trabajo previo mezclado: no se crea commit ni se hace push.

## Ajuste de color y título solicitado

Título visible: **MEDICACIÓN**. Las cuatro cards de métricas utilizan `--rm-surface-main` (greige canónico `#ECE9E6`) y la sombra compartida suave, conservando sus acentos de estado. Sustituye la superficie elevada anterior; no cambia la paleta global. Verificación de estilos computados en la pestaña conectada: cards `rgb(236, 233, 230)`, fondo `rgb(216, 210, 205)`, título en mayúsculas. Build PASS, prueba de cabecera PASS / 12 assertions, cinco anchos claros y dos oscuros PASS; en claro se verifica explícitamente el color de las cuatro cards. Evidencia adicional en `color-titulo/` dentro del directorio de QA de esta entrega.

## Actualización: gráficas de agenda y retirada de indicadores superiores

Solicitud vigente: quitar las cuatro cards numéricas superiores y mejorar el listado incorporando gráficas.

- Se retiran las cards y sus reglas CSS exclusivas. Se conserva el título MEDICACIÓN.
- La tabla prioriza residente y medicamento con tipografía de cuerpo legible, acciones de 44 px y presentación por fichas en móvil.
- Debajo del Kardex se añaden distribución por estado (aro) y cantidad por hora programada (barras), con RMCharts y los tokens compartidos.
- Ambas reciben un DTO de la misma colección autorizada y filtrada que la tabla. Fecha, denominador y unidad están visibles; no se añaden consultas ni cambios de BDD.
- Los colores del aro coinciden con los estados del listado: pendientes/ámbar, próximas/azul, retrasadas/coral, administradas/salvia, omitidas/neutro.
- Cero dosis muestra un estado vacío sin canvas. Un horario muestra una única barra. Las cantidades están disponibles como texto, también para teclado.
- Se conserva la autorización contextual y el registro existente. Las gráficas no representan eficacia clínica ni hora real de administración.

### Verificación del lote

| Comprobación | Resultado |
|---|---|
| PHP relacionado | PASS: 22 tests / 188 assertions |
| JS de charts y consistencia del shell | PASS: 11 tests |
| Vite build | PASS; aviso existente de bundle mayor de 500 kB |
| QA claro | PASS: 30 escenarios, seis estados en 1440/1280/1024/768/390 CSS px |
| QA oscuro | PASS: 12 escenarios en 1440/390 CSS px |
| Reactividad de charts | PASS en navegador sintético: datos iniciales, cambio a un horario, vaciado y recuperación |
| Navegador conectado | PASS: pendientes reduce a una dosis; búsqueda sin coincidencias quita las gráficas; limpiar filtros restaura dos dosis |
| Accesibilidad muestreada | Sin overflow horizontal; contraste textual muestreado ≥4.5:1; foco y controles de 44 px; movimiento reducido activo en el harness |

Los escenarios visuales usan HTML/snapshot de Livewire real exportados desde SQLite en memoria y transporte Livewire simulado en Chrome. La comprobación conectada ejercita lectura/filtros reales sobre PostgreSQL, sin guardar registros clínicos. No se ejecutó una suite global ni se certifica producción.

Evidencia local ignorada: storage/app/qa/medicacion-workspace-2026-10-15/graficas/.
Commit: ninguno; este lote depende de la composición anterior aún sin commit y el árbol contiene trabajo previo de varios módulos. No se agrupa ese estado en un commit de esta modificación.
- Revisión final de tablet: la tabla pasa a fichas según el espacio disponible del contenedor (≤860 px), para evitar que la acción recorte el estado. Build y 30/12 escenarios visuales repetidos tras este ajuste: PASS.

## Ajuste de distribución y realce

- Navegación alineada en cuatro columnas, con selección salvia más visible y dos columnas solo en móvil.
- Cabecera del listado simplificada: fecha, prioridad y cantidad; horarios diferenciados y botones con más presencia.
- Iconos de sección, leyenda con realce de los estados presentes y rellenos de gráfica más definidos usando los mismos tokens semánticos.
- Ejes de las barras a 12 px. En tablet las dosis ocupan dos fichas por fila; en móvil una.
- Corregido durante QA el contraste del texto seleccionado en modo oscuro; se usa el token de texto principal.
- Backend, permisos y estructura de datos: sin cambios en este ajuste.
- Verificación: PHP 12 PASS / 102 assertions; JS 11 PASS; build PASS (aviso existente >500 kB); QA claro 30 PASS y oscuro 12 PASS. Contraste textual muestreado ≥4.5:1, sin overflow horizontal, reactividad de gráficas preservada.
- Evidencia sintética: storage/app/qa/medicacion-workspace-2026-10-15/realce/. La página conectada carga los encabezados y controles nuevos.
- Commit: ninguno; se conserva el trabajo previo mezclado y sus dependencias sin agruparlo.

## Gráficas compactas y proporcionales

- Área de dibujo: 180 → 144 px; grosor máximo de barras: 36 → 28 px.
- Columnas iguales según el espacio real del contenedor; una columna por debajo de 740 px.
- Menos padding, separación y altura de encabezados/leyenda; se conserva el texto legible y el color anterior.
- La explicación de horarios se agrupa en su desplegable. Expandirlo no estira la gráfica de estados.
- Verificación: exportación Blade/Livewire sintética PASS; JS de charts 7 PASS; build PASS (aviso existente >500 kB).
- QA: 30 escenarios claros y 12 oscuros PASS, sin overflow; canvas ≤145 px y cards cerradas ≤310 px; dos columnas cuando caben; detalle operable con Enter y sin estirar la card vecina.
- Página conectada actualizada, sin registrar datos. Backend/BDD/permisos sin cambios.
- Evidencia: storage/app/qa/medicacion-workspace-2026-10-15/proporciones/. Sin commit por dependencias previas mezcladas.

## Referencia: columna derecha de gráficas

Referencia aprobada: codex-clipboard-d1fd09f0-2cdd-4806-a52b-54746a24a1c3.png. Se analizó antes de editar: columna lateral estrecha, cards apiladas, aro a la izquierda de la leyenda, radios suaves y separación compacta. Se traslada esa composición a las dos gráficas existentes; no se copian cifras ni se añaden widgets ajenos al encargo de colocación.

- Kardex: filtros a todo el ancho; agenda a la izquierda y gráficas apiladas en un aside derecho de 320 px cuando el contenedor dispone de al menos 900 px.
- La adaptación de tabla a fichas usa ahora el ancho de su propia columna, conservando acceso a todos los campos.
- Cards laterales alineadas arriba con la agenda. El encabezado general de las gráficas sigue accesible para lectores de pantalla; en modo estrecho vuelve a mostrarse.
- Aro lateral de 120 px, sin padding interno que desplace el centro. Leyenda, etiquetas, datos y colores conservados.
- En tamaños estrechos las gráficas quedan debajo de la agenda; dos columnas si caben y una en móvil.
- Verificación: PHP 12 PASS / 102 assertions; JS de charts 7 PASS; build PASS con aviso existente >500 kB.
- QA: 30 escenarios claros y 12 oscuros PASS, con posición real del aside comprobada, charts apilados a la derecha, sin overflow, controles de 44 px y contraste textual muestreado ≥4.5:1.
- Navegador conectado: vista actualizada y columna derecha visible; aro con dimensiones DOM 120 × 120 px. No se guardaron datos.
- Backend, BDD y permisos sin cambios. Evidencia sintética: storage/app/qa/medicacion-workspace-2026-10-15/lateral-derecho/.
- Commit: ninguno; se conserva el trabajo previo mezclado y sus dependencias.

## Dosis programadas: contraste y adaptación de filas

Referencias revisadas: codex-clipboard-e5e1974b-8021-4bf4-b1a1-24171b46f964.png (tabla) y codex-clipboard-7ecdbd9e-c394-44b2-a85b-c1b2f375ee17.png (fichas). Se observaban fondos uniformes, etiquetas repetidas y demasiado espacio vertical en fichas.

- Filas con superficie más clara, horarios azulados, iniciales salvia e iconos de apoyo. Estados con borde y fondo semánticos más definidos; el texto permanece explícito.
- Fichas: residente y horario arriba; medicamento en bloque azul suave; habitación; dosis y vía; estado y acción al pie.
- Se suprimen etiquetas visuales redundantes en fichas, conservando encabezados de tabla y nombres accesibles.
- «Ver» en tabla y «Ver detalle» en fichas, con nombre accesible específico para residente/medicamento/horario. Se mantiene la misma acción autorizada.
- Se retiran las antiguas clases de opacidad, bordes laterales y la utilidad inválida duplicada. Alerta clínica real y selección siguen representadas.
- Tipografía legible y reflow de nombres; geometría adaptada al ancho de la columna. Las gráficas laterales conservan su posición.
- Verificación: PHP relacionado 12 PASS / 102 assertions; exportación sintética Livewire final PASS; build PASS con aviso existente >500 kB.
- QA final: 30 escenarios claros y 12 oscuros PASS. Cinco tamaños CSS, sin scroll horizontal en documento ni contenedor de tabla, sin horarios/acciones fuera de las fichas, controles ≥44 px y contraste de textos/estados muestreado ≥4.5:1.
- Navegador conectado actualizado; no se registraron datos clínicos. Backend, permisos y BDD sin cambios.
- Evidencia: storage/app/qa/medicacion-workspace-2026-10-15/dosis-responsive/. Sin commit por dependencias de trabajo previo aún mezclado.
## Detalle de dosis y ficha en primer plano

Solicitud: mejorar la composición del detalle y evitar que la ficha del medicamento quede detrás de su backdrop. Causa comprobada: el popup propio usaba z-index 50 y el drawer canónico 500.

- Se reutiliza `ui.modal-livewire` para la ficha, en la capa modal 1000. En escritorio desde 1024 px queda a 16 px del drawer; en tamaños menores aparece centrada en primer plano. Conserva scroll, cierre, teclado y foco.
- El detalle separa residente, alergias, medicamento, prescripción de solo lectura, seguridad y observación. Tipografía legible, superficies cálidas y acciones visibles. Las alergias aparecen antes de la orden; los cinco correctos son referencias, sin marcas de verificación ficticias.
- Abrir/cerrar la ficha conserva la observación. Escape cierra solo la ficha y devuelve el foco a «Ver ficha». Cerrar el detalle con observación requiere confirmar descarte, sin justificativo. Cerrar el padre también cierra la ficha.
- Lectura protegida por cuenta activa, Policy existente y alcance de residentes asignados. Se rechaza contexto de residente incoherente. Se corrige la consulta de alergias para incluir los estados existentes ACTIVA/ACTIVO; el DTO incorpora únicamente un booleano de presentación.
- Sin cambios de esquema, permisos, prescripción ni reglas clínicas. No se guardaron administraciones/omisiones reales durante QA.

### Evidencia y límites

| Comprobación | Resultado |
|---|---|
| PHP relacionado | PASS: 15 tests / 138 assertions |
| JS de medicación y shell | PASS: 9 tests |
| Build | PASS; aviso existente de bundle >500 kB |
| Exportación sintética Blade/Livewire | PASS: 1 test / 5 assertions |
| QA visual claro | PASS: 15 casos en 1440/1280/1024/768/390 px |
| QA visual oscuro | PASS: 6 casos en 1440/390 px |
| Navegador conectado | PASS: ficha al frente, separación 16 px, Escape conserva detalle y devuelve foco |

QA sintética con transporte Livewire simulado: sin overflow horizontal, controles de 44 px, contraste textual muestreado ≥4.5:1, foco confinado y movimiento reducido. Capturas revisadas en los cinco anchos. La comprobación conectada verificó lectura e interacción reales, sin escrituras clínicas. No se ejecutó la suite global ni se afirma certificación clínica, WCAG o de producción.

Evidencia local ignorada: `storage/app/qa/medicacion-workspace-2026-10-15/detalle-ficha/`.
Commit: ninguno; hay cambios previos mezclados y dependencias aún sin commit. Sin push.

## Refinamiento visual de los dos paneles

- Ficha: cabecera de identidad con miniatura de 100 px, estado visible y explicación de la ilustración. Datos agrupados en «Presentación y uso», valores alineados a la izquierda y observación diferenciada. Se conserva toda la información del catálogo.
- Detalle: residente sobre superficie neutra, medicamento destacado, dosis y horario agrupados, iconos de sección consistentes y seguridad con tinte salvia. Captura diferenciada de consulta. Encabezados con glass compartido y fallback sólido; sin biseles ni nueva paleta.
- Se mantienen la separación lateral de 16 px desde 1024 px, el modal frontal en tamaños menores, las acciones existentes y la conservación de observaciones/foco. No se modifica backend, BDD ni permisos en este refinamiento.
- QA final PASS: 15 casos claros en 1440/1280/1024/768/390 px y 6 oscuros en 1440/390 px. Sin overflow, controles ≥44 px, contraste textual muestreado ≥4.5:1, Escape/foco/descarte preservados y movimiento reducido. Se corrigió durante implementación un contraste de cabecera oscuro detectado por el harness; la verificación posterior pasó.
- Exportación Blade/Livewire sintética PASS (1 test / 5 assertions), JS relacionado PASS (9 tests), build PASS (aviso existente >500 kB), diff-check PASS. QA sintética usa transporte simulado. Pestaña conectada actualizada y ambos paneles inspeccionados visualmente, sin guardar datos clínicos.
- Evidencia ignorada: `storage/app/qa/medicacion-workspace-2026-10-15/rediseno-detalle-ficha/`. Sin suite global adicional: el alcance es visual. Commit ninguno por dependencias previas mezcladas; sin push.

### Nombre en mayúsculas y horario destacado

Nombre genérico/comercial en mayúsculas mediante presentación CSS, sin alterar datos. Hora programada a 25 px, cifras tabulares y superficie informativa más clara. Build y exportación sintética PASS; QA de overlays repetida: 15 casos claros en cinco anchos y 6 oscuros PASS, sin overflow ni regresión de foco. Pestaña conectada confirma mayúsculas y hora 09:00 a 25 px. Evidencia: `storage/app/qa/medicacion-workspace-2026-10-15/nombre-horario/`. Sin cambios backend/BDD; continúa sin commit por dependencias previas mezcladas.

### Todos los horarios, indicación y prescriptor

El detalle mostraba exclusivamente la hora de la dosis seleccionada. Ahora incluye todos los horarios ACTIVO de la misma prescripción, ordenados y con sus días, señalando la dosis consultada. La pestaña conectada confirma 09:00 y 19:00 para la orden observada. No se generan horas desde texto libre ni se cambia la programación persistida; ausencia de horarios se explica explícitamente. Se retira el fallback que tomaba el primer horario cuando no coincidía con el solicitado.

«¿Para qué se indicó?» utiliza solamente `prescripciones.indicacion`; una observación no sustituye ese dato. La frecuencia ausente tampoco recibe una pauta inventada. Se muestra autor completo desde `cod_personal`, profesión y especialidad registradas. El modelo vigente carece de un campo cargo específico: la etiqueta «Cargo / profesión registrada» representa `personal.profesion`, sin inferir competencias desde roles. Estos datos profesionales son los actuales de Personal, no una instantánea histórica inexistente. Consulta de solo lectura, mismas Policy y restricciones; sin cambios de esquema, permisos u órdenes reales.

Verificación: PHP relacionado 16 PASS / 155 assertions, incluidas dos regresiones nuevas; se prueba exclusión de horarios inactivos, preservación de días, autor distinto del usuario que consulta, indicación ausente sin sustituciones y ausencia de escrituras. Exportación sintética 1 PASS / 5 assertions; build PASS (aviso previo >500 kB). QA: 15 casos claros en cinco anchos y 6 oscuros PASS, teclado/foco/contraste/reduced motion y sin overflow. Fixture sintética con dos horarios y profesión/especialidad verificadas en DOM; transporte Livewire simulado. Revisión conectada de lectura también confirma indicación y datos profesionales reales, sin guardar. Sin suite global adicional ni nueva validación PostgreSQL de escritura: no se cambia persistencia.

Evidencia ignorada: `storage/app/qa/medicacion-workspace-2026-10-15/horarios-indicacion-prescriptor/`. Commit ninguno por dependencias previas mezcladas; sin push.

### Aviso del catálogo en lenguaje claro

Se muestra «Dosis y horarios»: «La dosis, la frecuencia y los horarios se consultan en la prescripción médica». La observación original permanece íntegra en «Ver notas y referencias del catálogo», disponible con teclado cuando existe. Sin modificar datos del catálogo, backend o prescripción.

Build PASS; exportación sintética PASS (1 test / 5 assertions); QA sintética PASS (15 casos claros en cinco anchos y 6 oscuros), incluyendo apertura/cierre del desplegable con Enter, preservación literal de notas, foco, contraste y reflow. Comprobación conectada confirma el aviso actualizado. Evidencia local ignorada: `storage/app/qa/medicacion-workspace-2026-10-15/aviso-claro/`. Transporte QA simulado; sin suite global adicional. Sin commit por dependencias previas mezcladas.
