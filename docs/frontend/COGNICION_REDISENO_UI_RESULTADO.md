# Cognición: distribución, color e interacción

Fecha: 2026-10-13. Alcance: captura de Observación cognitiva y ventana de Evolución cognitiva existentes.

## Antes y flujo

El resumen ocupaba una franja amplia antes de los campos; las ayudas y las tarjetas
tenían poca separación visual, y el acceso a evolución quedaba al final en móvil.
Se conserva el flujo residente/autor/jornada → observación → validación → guardado
→ historial. La captura y las observaciones guardadas mantienen sus responsabilidades.

## Implementación

- Cabecera de captura compacta, contador factual de respuestas y resumen por dominio desplegable.
- Accesos Ver evolución y Ver historial junto al resumen, disponibles también en móvil.
- Navegación A–D: abre la sección y lleva el foco a su cabecera, sin modificar respuestas.
- Distribución de captura y apoyo contextual en dos columnas; una columna en móvil/tablet compacta.
- Superficies neutrales, campos más claros y acentos azul/lavanda de los tokens compartidos.
  La ausencia de observación usa una selección neutral; las respuestas elegidas usan el mismo
  acento informativo para Sí y No. Los colores no atribuyen gravedad clínica.
- Ayuda general desplegable; los recordatorios contextuales existentes siguen visibles.
- Evolución: Historial y Vista previa identificados, selección de registros por clic/foco,
  fecha/autor conservados y resumen de otros dominios desplegable.

Archivos del cambio: `observacion-cognitiva.blade.php`, `cognicion-evolucion.blade.php`,
`observacion-cognitiva.css`, `observacion-cognitiva.js` y `cognicion-visual-qa.mjs`.
Se reutilizan los componentes de modal, ventana de evolución, campos y botones.
No se cambian el backend, el vocabulario de respuestas, validaciones, permisos,
interpretación, consultas, persistencia ni esquema. Se mantienen NULL, false y true.

## Verificación

- `php vendor/bin/phpunit tests/Feature/ObservacionCognitivaTest.php`: 24 PASS, 121 aserciones,
  SQLite en memoria. Incluye autorización, persistencia, reenvíos, errores y continuidad.
- `node --test tests/Frontend/observacion-cognitiva.test.js`: 4 PASS.
- `node tests/Support/cognicion-visual-qa.mjs`: PASS en 1440, 1280, 1024, 768 y 390 px,
  cero errores JS, sin desbordamiento horizontal. Se verifican navegación A/D, apertura de
  sección cerrada, foco, movimiento reducido y conservación de texto y booleanos.
  También se conservan las pruebas de ventana, historial, descarte y contraste de selección.
- `npm run build`: PASS. Aviso previo del bundle JavaScript superior a 500 kB.
- Aplicación local: apertura real, navegación a Observaciones, apertura/cierre de Evolución,
  retorno del foco al disparador y formulario conservado. Sin guardar registros clínicos.
- QA visual final: PASS para el alcance de presentación e interacción. Las capturas
  sintéticas de los cinco anchos están en el directorio `cognitive-redesign` de este chat.

## Integración y límites

Cambios aplicados al checkout compartido, preservando sus otros trabajos.
Los cinco archivos base del módulo cognitivo ya eran archivos sin seguimiento de Git.
Se guardó una copia anterior y un parche exclusivo del rediseño en
`C:/Users/CARLAENCINAS/.codex/cognitive-ui-redesign.patch`; comprobación inversa
`git apply --reverse --check` aprobada. No se incorporan al commit cambios anteriores
del módulo ni de Conducta, Heridas, sidebar, permisos o sistema experto.
El commit de código queda pendiente de integrar la base previa del módulo.
No se ejecutó PostgreSQL en esta tarea de presentación; las pruebas se informan
por su entorno real. No hay cambios nuevos de dominio ni dependencia funcional
pendiente para usar el rediseño local. Sin push ni despliegue.
