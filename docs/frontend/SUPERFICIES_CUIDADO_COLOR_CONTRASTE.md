# Superficies de cuidado: composición y contraste

Fecha: 13/10/2026. Alcance: Mi turno, Mis residentes y superficies compartidas de captura clínica en modo claro.

## Dirección solicitada

La propietaria pidió café grisáceo terroso, cards más claras que el fondo, sin superficies blancas dominantes, con relieve y combinación de colores funcionales. Las referencias evolucionaron de #E7DED6 / #DED9D5 / #E6E3D9 a #C2BCB6; la última corrección pide un punto medio, sin demasiado oscuro ni blanco. La implementación utiliza #D0CAC5 para las cards, #BBB3AB para el canvas, #C5BDB6 para superficies secundarias y #D8D3CD para campos.

## Implementación compartida

Los valores físicos viven en `tokens/primitives.css`; los aliases `--rm-capture-*` en `tokens/colors.css`. `patterns/care-surfaces.css` conecta los consumidores existentes, incluidos botones secundarios. No se crea una paleta por rol ni se duplican las vistas. Los cambios previos presentes en esta capa se conservaron y ajustaron al pedido vigente.

Texto principal carbón con un matiz petróleo, texto secundario café profundo, borde visible y elevación suave. Menta: cuidado/acciones; azul: agenda, información y evolución; lavanda: conducta. Los indicadores, selecciones y alertas siguen sus reglas existentes. Se excluye la card de intensidad de Dolor de la normalización genérica para conservar sus estados aprobados. No se cambian layout, rutas, permisos, reglas clínicas, datos ni BDD. Los overrides de esta capa excluyen modo oscuro.

## Verificación

- Build PASS.
- Render de Mi turno usando la prueba existente y BDD sintética en memoria: 1 PASS / 7 assertions; sin escrituras en la base operativa.
- CSS de producción en muestras de nueve familias (Conducta, Signos, Dolor, Ingesta, Hidratación, Eliminación, Movilidad, Heridas y formulario compartido): superficie de card #D0CAC5, campos sin blanco puro.
- Mi turno renderizado: 11 cards normales con la base compartida. Luminancia de la card mayor que la del fondo; contraste principal medido 5.76:1.
- Conducta: harness de Blade/Alpine real con transporte sintético, cinco viewports exactos PASS; selecciones con contraste ≥4.5:1; popup, foco, captura y cierre conservados.
- Evidencia local: `%TEMP%/remembermind-care-surfaces-qa/` y `%TEMP%/remembermind-conducta-qa/superficies-final/`.

Estas verificaciones prueban presentación y propagación CSS; no equivalen a recorrer las sesiones operativas de todos los roles ni a una certificación de accesibilidad. Stage, commit y push: NONE.

## Bienvenida y botones: ajuste posterior

La propietaria pidió el mismo contraste en la bienvenida y permitió beige claro y pequeños toques de blanco, con más presencia de verde. El header pasa a la superficie de card #D0CAC5, por encima del canvas #BBB3AB; la transición hacia la fotografía utiliza ese mismo fondo. La fecha tiene una superficie neutral aclarada, la hora azul y el turno/badge menta con tinta más profunda. Se conservan composición, contenido y fotografía.

Los botones primarios usan verde profundo y los secundarios menta claro con borde verde; «Ver evolución» conserva azul informativo. Los declaradores antiguos de botones se normalizan en este workspace mediante los tokens canónicos, sin introducir otra capa de aliases legacy. Los botones de peligro y los estados clínicos no se recolorean.

Build PASS. Header renderizado de Mi turno con fondo esperado y muestra de nueve familias PASS. Harness de Conducta en los cinco viewports PASS; contraste medido de los secundarios 5.35:1 y de «Ver evolución» 5.53:1. Evidencias: `%TEMP%/remembermind-care-surfaces-qa/bienvenida.png` y `%TEMP%/remembermind-conducta-qa/bienvenida-botones/`. Sin commit ni push.

## Aclarado, trama y transparencia moderada

Pedido posterior: fondo más claro con trama fina, cards algo translúcidas y mejor separación cromática en los formularios. Valores vigentes: canvas #C9C1B8, card sólida de referencia #DED8D2, campos #E8E3DD y superficie secundaria #D5CDC5. Las cards usan un fondo al 90 % de opacidad; no se aplica opacity al contenedor, por lo que texto, botones y estados permanecen nítidos. Se aplica también a las cards de residentes del directorio.

La trama combina puntos pequeños y líneas diagonales de 6 % / 2 % de tinta, mediante tokens compartidos y CSS estático, sin assets nuevos, animación ni filtros costosos sobre los campos. Aparece en el canvas y los paneles de captura. La integración de la fotografía conserva un inicio de degradado opaco para evitar cortes visibles en el borde de la imagen. Se mantienen los botones menta/azul, las secciones lavanda y los estados clínicos existentes.

Build PASS; muestra CSS de nueve familias y 11 cards de Mi turno PASS, opacidad calculada 0.9. Contraste de texto principal medido sobre el fondo compuesto (card + canvas): 6.48:1; cards más claras que el fondo. Conducta en 1440/1280/1024/768/390 PASS: sin overflow, contraste de selecciones/botones ≥4.5:1, popup/foco/captura intactos. Capturas sintéticas revisadas en `%TEMP%/remembermind-conducta-qa/trama-glass/` y `%TEMP%/remembermind-care-surfaces-qa/`. Sin navegación acreditada de todas las sesiones operativas, sin certificación WCAG, sin commit ni push.

### Trama del workspace más visible

Se refuerza solo la trama del fondo de Mi turno/Mis residentes: puntos al 11 % y dos direcciones diagonales al 3.5 % / 2.5 %, con aspecto de tejido fino. La trama de los paneles de captura mantiene su intensidad anterior. El selector contempla `main`/`data-rm-main` y ambos shells de Enfermería (incluida identidad contextual nursing). Build y render sintético con el atributo real `data-rm-main`: PASS; background-image presente y contraste compuesto principal 6.48:1. Sin JS, listeners, overlays ni cambios de interacción.

## Recuadros de formularios más claros — 13/10/2026

Solicitud: mejorar color y contraste de todos los recuadros de captura. Se añaden cuatro aliases compartidos para superficie de sección, campo y sus bordes; mezclan primitives existentes de tierra clara y menta. El canvas/dashboard y las fichas de residentes conservan sus colores de las tandas anteriores.

Secciones con fondo cálido más claro, campos con matiz menta muy discreto y bordes distinguibles, labels de mayor peso. Consumidores: Signos, Dolor, Ingesta, Hidratación, Eliminación, Movilidad, Heridas/curaciones, Conducta, Cognición y wrappers clínicos de Seguimiento, Sueño, Procedimiento y medicación. Se respeta la estructura, dimensión y comportamiento de cada formulario.

Signos solo retoca sus cuadros/campos neutrales; conserva estados clínicos, foco y errores. La card de intensidad EVA, los colores de selección y los bordes laterales de las secciones conservan su semántica. Sin cambios de backend, validación, datos, permisos ni modo oscuro.

Build PASS; 43 pruebas JS relacionadas PASS. Muestras de componentes con CSS de producto de 12 familias en 1440/1280/1024/768/390: sin overflow; contraste mínimo labels/fondo 8.39:1 y bordes de campo 3.27:1. Comparación CSS antes/después de warning/crítico y EVA intermedio/intenso: colores conservados. Conducta y Cognición: harness Blade/Alpine en cinco viewports PASS; Cognición requirió regenerar su fixture para corresponder a la navegación de secciones vigente, sin editar esos cambios previos.

Evidencia sintética: `%TEMP%/remembermind-clinical-boxes-qa/`, `%TEMP%/remembermind-conducta-qa/recuadros-contraste/` y `%TEMP%/remembermind-cognicion-qa/evidence/`. La muestra de doce familias verifica CSS/componentes; no afirma una sesión operativa end-to-end de cada formulario. Sin commit, stage ni push.

## Tipografía, formas y jerarquía clínica compartida — 13/10/2026

Pedido posterior: reducir diferencias entre formularios y dar más carácter profesional a la composición. Se detectó Inter en Cognición frente a Nunito Sans en Conducta y otros formularios, además de radios y títulos independientes. La familia de captura usa ahora Nunito Sans mediante el alias `--rm-capture-font`, con títulos de modal de 21 px/800, títulos de sección de 15 px/800, campos y labels de 14 px, y ayudas de 12 px. Se conservan tamaños específicos de valores clínicos y metadata compacta.

La capa compartida aplica radios canónicos de 16 px a las cards, 14 px a campos/botones y 18 px a paneles. Los iconos de cabecera usan un marco de 40 px; los pasos un marco de 32 px con radio de 10 px. Avatares y controles circulares conservan su función. Las secciones tienen elevación suave y cabeceras con banda tintada: menta en cuidado, azul en controles y lavanda en conducta/cognición. Los formularios conservan su distribución propia, contexto, contenido, comportamiento, colores de selección y estados clínicos. Rojo no se introduce como decoración. La capa excluye el modo oscuro.

Verificación: build PASS (advertencia preexistente de chunk JS mayor a 500 kB); 43 tests JS de Signos/Conducta/Cognición PASS. Muestras de 12 familias × cinco tamaños exactos (1440/1280/1024/768/390): Nunito Sans en campos/labels, radios de card de 16 px, sin overflow; contraste de labels ≥8.39:1 y bordes de campo ≥3.27:1. Colores de warning/crítico y EVA conservados. Harnesses Blade/Alpine de Conducta y Cognición en los mismos cinco tamaños PASS, incluyendo selecciones, historial, popup, captura y movimiento reducido; capturas de escritorio y móvil revisadas.

Evidencia: `%TEMP%/remembermind-clinical-boxes-qa/`, `%TEMP%/remembermind-conducta-qa/tipografia-formas/` y `%TEMP%/remembermind-cognicion-qa/tipografia-formas/`. Las muestras de los otros formularios prueban propagación de CSS, no una sesión operativa de cada flujo. Backend/BDD/permisos sin cambios en esta tanda. Se conserva el árbol previo con trabajo mixto sin stage, commit ni push.

## Flat y glass moderado — 13/10/2026

Solicitud vigente: aplicar flat design con un poco más de glassmorphism a la familia de formularios. Las secciones de captura pasan a sombras nulas; los botones primarios/secundarios pierden sombra decorativa conservando foco y selección. El panel flotante mantiene una elevación media para distinguirlo del entorno. Se conserva la tipografía, geometría, bandas de sección, acentos y estados clínicos anteriores.

El panel usa superficie cálida al 92 % de opacidad. Cabeceras y pies usan una superficie menta/cálida al 76 % y blur de 14 px, con aliases compartidos. No se aplica blur al panel que contiene los popups, a inputs ni a listas largas; se conserva el popup independiente. El fallback sin soporte de backdrop-filter es opaco. No se introducen degradados ni cambios de dominio. Alcance: formularios; las fichas y dashboard mantienen sus superficies.

Se detectó durante QA que el compilador retenía únicamente la propiedad prefijada cuando se declaraba al final; se invirtió el orden y se comprobó `backdrop-filter: blur(14px)` en Chrome con CSS de producción. Build PASS (advertencia de tamaño JS preexistente). 43 pruebas JS relacionadas PASS. Muestras de 12 familias × cinco viewports PASS: secciones sin sombra, tipografía/radios conservados, contraste labels ≥8.39:1 y bordes de campo ≥3.27:1, colores clínicos conservados. Conducta y Cognición: harness Blade/Alpine en 1440/1280/1024/768/390 PASS, incluidos popup, selección y movimiento reducido; capturas de escritorio/móvil revisadas.

Evidencia sintética: `%TEMP%/remembermind-clinical-boxes-qa/flat-glass.json`, `%TEMP%/remembermind-conducta-qa/flat-glass/` y `%TEMP%/remembermind-cognicion-qa/flat-glass/`. No se afirma verificación operativa de todos los formularios. Backend/BDD/permisos sin cambios. Stage/commit/push NONE; se preserva trabajo previo mezclado.

## Cabecera y contexto clínico más legibles — 13/10/2026

Referencia: captura de la propietaria de Signos vitales (`codex-clipboard-4752b5bc-2f1f-4d40-9f01-2fa741c83ba5.png`). Análisis previo: dos franjas horizontales, primera de aproximadamente 90 px con volver/título/subtítulo y mover/cerrar; segunda con card del residente de aproximadamente 105 px y tres zonas (identidad, profesional, jornada). Se conservan esas dos franjas y los controles existentes. El pedido es mejorar la referencia actual, no reproducir sus tamaños pequeños; en móvil se conservan los disclosures de contexto donde ya existen.

Contrato compartido: títulos 24 px en escritorio / 20 px en móvil, nombre del residente 22/18 px, metadata principal y nombres profesionales de 14 px, etiquetas auxiliares de 12 px. Icono del formulario en marco de 44/36 px, avatar de 48 px, controles de al menos 44 px. Cabecera con altura automática y mínimo de 80 px; contexto con altura automática, mínimo de 88 px, superficie ligeramente menta y acento lateral de cuidado. Padding de 12/16 px en escritorio y 8 px en móvil. No se trunca la identidad ni se fijan máximos de altura que corten contenido.

Se reutilizan el modal y las clases de contexto actuales de Signos, captura compartida (Dolor/Ingesta/Hidratación/Eliminación/Movilidad y registros comunes), Conducta, Cognición y Heridas. Los grids, permisos y datos propios de cada flujo se mantienen. El estilo flat/glass y la jerarquía clínica anterior permanecen; no se añaden datos ni interacciones clínicas.

Build PASS, 43 tests JS relacionados PASS. Conducta/Cognición con Blade y Alpine reales, transporte sintético: cinco viewports exactos PASS, popup/foco/selecciones/captura conservados. Muestras de contexto de Signos, formulario compartido y Heridas con nombres sintéticos largos: 3 familias × 5 tamaños sin overflow y tamaños calculados del título/nombre correctos. Capturas de escritorio y móvil revisadas. Evidencia en `%TEMP%/remembermind-clinical-masthead-qa/`, `%TEMP%/remembermind-conducta-qa/cabecera-contexto/` y `%TEMP%/remembermind-cognicion-qa/cabecera-contexto/`.

Alcance de la prueba: las muestras de contexto verifican presentación CSS, no una sesión clínica operativa completa de todos los formularios. Backend/BDD/validación/permisos sin cambios. Stage/commit/push NONE por árbol previo mezclado.

## Popups clínicos unificados — 13/10/2026

Referencia de la propietaria: `codex-clipboard-3351be68-aaf9-49aa-85db-351970029a54.png`, ventana de evolución de pulso con cabecera, body desplazable, comparación guardado/actual, gráfica y herramientas inferiores. Se conserva esa estructura, el native popover y su geometría de arrastre/resize; se mejora el material, la jerarquía y los controles.

La clase compartida `rm-signos__graph-popup`, reutilizada por Signos y `clinical-trend-window` (Dolor, Ingesta, Hidratación, Eliminación, Movilidad, Cognición) y por Conducta, usa ahora fondo de captura claro, Nunito Sans, radio de modal canónico, elevación media y borde compartido. Cabecera/pie usan el glass de los formularios con blur de 14 px y fallback opaco. Títulos de ventana de 20 px en escritorio / 18 px en móvil, subtítulo de 14 px, controles de 44 px con radio de botón y hover menta. Body con padding uniforme; comparación y detalle de Signos adoptan las superficies flat de captura, conservando estados y paleta del gráfico.

Los historiales de Conducta, Cognición y Heridas adoptan el mismo material, título, cabecera y controles. No se crean popups donde no existían ni se modifican fotografías, reglas clínicas, historial, datos, permisos o backend. Los bordes invisibles de resize y el foco se conservan.

QA detectó recorte inferior del gráfico de Conducta a 390 px después de ampliar cabecera/pie. Se compactó el espaciado móvil y la altura del plot a 160 px sin alterar datos/escala. La prueba que exige plot visible queda PASS. Build PASS, 43 pruebas JS relacionadas PASS, Conducta/Cognición en 1440/1280/1024/768/390 PASS con popup, teclado, mover/resize, captura, historial y movimiento reducido. Capturas de popups e historiales revisadas. La galería de 12 familias conserva contraste y colores clínicos. El build mantiene la advertencia preexistente de tamaño de chunk JS.

Evidencia sintética: `%TEMP%/remembermind-conducta-qa/popups-unificados/`, `%TEMP%/remembermind-cognicion-qa/popups-unificados/` y `%TEMP%/remembermind-clinical-boxes-qa/`. Verificación de los otros consumidores por reutilización CSS/componentes; no se afirma navegación operativa completa de todos ellos. Stage/commit/push NONE.

## Composición y ritmo de lectura de los popups — 13/10/2026

Pedido posterior: mejorar distribución, tamaños de letra, color, composición y espacios. Se mantiene el marco compartido; se añaden separaciones de 12 px entre filtros/comparaciones, filtros agrupados sobre azul tenue y cards comparables con padding uniforme. Guardado usa azul suave; captura actual usa lavanda. Estos acentos identifican procedencia, no normalidad/severidad. Los badges y estados clínicos conservan su autoridad y sus colores.

Labels de comparación de 14 px, cifras de Signos de 30 px, encabezados internos de 18 px y ayudas/metadata de 13 px. El detalle del punto usa menta informativa con padding de 12 px y texto de 14 px. En Conducta se mueve el readout después de la gráfica/leyenda: primero conteo y periodo, después gráfica y detalle. Se conservan IDs, tooltip, selección por ratón/teclado y datos. El body permanece desplazable cuando el contenido supera la ventana; no se oculta información ni se fuerza un alto fijo al contenido.

Build PASS; 43 tests JS relacionados PASS. Prueba PHP de gráfico Conducta cero/uno/conteos factual PASS (1 test, 9 assertions), utilizada también para regenerar los fixtures desde la vista vigente. Conducta y Cognición en 1440/1280/1024/768/390 PASS con transporte sintético; capturas desktop/mobile de popup revisadas. Evidencia: `%TEMP%/remembermind-conducta-qa/composicion-popup/` y `%TEMP%/remembermind-cognicion-qa/composicion-popup/`. Sin cambios de BDD, permisos, reglas ni backend. Sin stage, commit ni push.

## Retirar franjas y dar más glass/profundidad — 13/10/2026

Nueva dirección de la propietaria: quitar las franjas de borde señaladas en `codex-clipboard-a171cb34-1cc5-45ba-af32-7399e6471fd9.png` y dar más presencia a los recuadros mediante glass/sombra. Sustituye el acabado sin sombra de las secciones de las tandas anteriores; conserva composición y tipografía.

Se retiran las franjas laterales menta/azul/lavanda de contexto, secciones y comparaciones. En cards clínicas de Signos, EVA y avisos, el borde lateral grueso se sustituye por un rim claro de 1 px; se conservan fondo, texto, badge e icono de cada estado. Se retiran también acentos laterales de timeline de Eliminación y resumen de dominios. El token de acento de cards métricas queda a cero dentro del workspace compartido, sin modificar navegación ni barras activas del sidebar.

Secciones neutrales y contexto usan la superficie de captura al 88 % con rim claro y sombra canónica media. Comparaciones y cuadros clínicos reciben la misma elevación, preservando sus tonos. Campos/foco/error/selecciones conservan su contrato. No se aplica opacity a contenedores ni blur a cientos de campos; el frosted glass de cabecera/pie mantiene 14 px. La intensidad EVA conserva su fondo/color aprobado. Modo oscuro fuera de esta tanda.

Build PASS (advertencia JS preexistente), 43 pruebas JS relacionadas PASS. Conducta/Cognición: cinco tamaños exactos PASS; capturas revisadas en `%TEMP%/remembermind-conducta-qa/glass-sin-franjas/` y `%TEMP%/remembermind-cognicion-qa/glass-sin-franjas/`. Galería sintética de 12 familias × 5 tamaños con sombras presentes, bordes uniformes, sin overflow; warning/crítico y fondo/texto de EVA conservados. Casos de card crítica y warning de Signos y EVA comprueban borde lateral de 1 px. Campos mantienen contraste de borde ≥3.27:1. Evidencia en `%TEMP%/remembermind-clinical-boxes-qa/`. No se afirma navegación operativa de todos los formularios ni certificación. BDD/backend/permisos sin cambios; stage/commit/push NONE.

### Aclarado y relieve posterior

Solicitud posterior: cuadros aún más claros, con más relieve y glass. El material de sección mezcla la superficie institucional clara al 62 % con taupe claro; los campos combinan esa superficie al 80 % con menta suave. La transparencia de las secciones pasa a 84 % de opacidad, sin aplicar opacity al texto. `--rm-capture-box-shadow` reutiliza la elevación canónica grande y añade un reflejo superior fino. El cambio se propaga a secciones, contexto y comparaciones de la familia clínica; el canvas y las fichas del directorio mantienen sus tonos. No reaparecen franjas laterales ni se añade blur a cada campo.

Build y 43 tests JS relacionados PASS. Conducta/Cognición en cinco tamaños exactos PASS, capturas desktop/mobile revisadas. Galería CSS de 12 familias × 5 tamaños PASS: sin overflow, shadows presentes, bordes laterales uniformes de 1 px en las cards clínicas, fondo/texto de EVA y badges warning/crítico conservados; contraste de borde de campos ≥3.32:1. Evidencia en `%TEMP%/remembermind-conducta-qa/glass-claro-relieve/`, `%TEMP%/remembermind-cognicion-qa/glass-claro-relieve/` y `%TEMP%/remembermind-clinical-boxes-qa/`. Verificación sintética, sin afirmar recorrido operativo completo de todos los formularios. Backend/BDD/permisos sin cambios; sin stage/commit/push.

## Composición con niveles de profundidad — 14/10/2026

Pedido vigente: que los formularios dejen de verse planos. El body de captura adquiere un canvas gris cálido con matiz azul, separado de las secciones claras translúcidas; las sombras existentes se perciben sobre ese canvas. Las cabeceras de sección refuerzan su tinte funcional, cambian la línea inferior por profundidad suave y tienen títulos de 16 px en los formularios especializados. Los legends y encabezados equivalentes de captura común reciben una superficie tintada compacta. Grupos interiores neutrales y marcadores de paso tienen elevación secundaria; el espaciado entre secciones se normaliza a 16 px.

Se conservan colores planos, glass moderado, tipografía común, ausencia de franjas laterales, campos y controles originales. No se añaden gráficos decorativos, animaciones, nuevos flujos ni reglas clínicas. Las selecciones/errores/alertas mantienen sus colores y foco. El canvas nuevo queda acotado al body de formularios, sin recolorear el dashboard/directorio ni cambiar posición de popups.

Build PASS (advertencia preexistente de chunk JS), 43 tests JS relacionados PASS. Conducta/Cognición con Blade/Alpine reales y transporte sintético: 1440/1280/1024/768/390 PASS, sin overflow, popup/historial/selecciones/teclado/movimiento reducido conservados. Capturas de escritorio y móvil revisadas. Galería de 12 familias × 5 tamaños PASS, colores de warning/crítico/EVA conservados y borde de campo ≥3.32:1. Evidencia: `%TEMP%/remembermind-conducta-qa/composicion-capas/`, `%TEMP%/remembermind-cognicion-qa/composicion-capas/`, `%TEMP%/remembermind-clinical-boxes-qa/`. Las muestras de las demás familias verifican CSS, no navegación operativa completa. Backend/BDD/permisos sin cambios; sin stage/commit/push.

## Menos blanco y selecciones más suaves — 14/10/2026

La propietaria pide reducir el blanco y limpiar las selecciones/microinteracciones. La sección pasa a mezcla taupe claro 72 % / tierra clara 28 %, propagada a campos, panel y glass mediante los aliases existentes. Conserva elevación, transparencia, composición, acentos clínicos y ausencia de franjas.

Mapa de transiciones de opciones de captura (radios segmentados, chips, choices, Bristol y escala EVA):

| Trigger | Feedback | Timing / foco |
| --- | --- | --- |
| Hover con ratón | Sombra discreta, sin desplazamiento | 180 ms, easing estándar |
| Seleccionar | Un borde de 1 px y sombra XS; mantiene el color de la opción | Fondo/borde 220 ms, texto/sombra 180 ms |
| Pulsar | Sombra interior leve, sin escala ni traslación | Transición de pintura, sin cambiar layout |
| Sin respuesta | Superficie neutra y borde uniforme; sin doble contorno | Mantiene estado vacío explícito |
| Teclado | Conserva outline de foco del componente | No se elimina ni se depende de animationend |
| Reduced motion | Tokens/globales existentes suprimen duración | Selección y texto permanecen visibles |

No se animan dimensiones, pesos de texto, valores clínicos ni geometría de popups. No hay librerías/listeners nuevos. Los fondos/tintas seleccionados, swatches, badges y errores conservan sus reglas; la captura no depende de completar la transición.

Build PASS (advertencia JS previa), 43 pruebas JS relacionadas PASS. Conducta/Cognición con transporte sintético en cinco viewports PASS con reduced motion. También se ejecutaron ambos harnesses con `no-preference`: duración computada 220/180 ms verificada, interacción/popup/foco conservados; en Conducta se espera el fin real de las transiciones antes de medir contraste de selección (≥4.5:1). Galería de 12 familias PASS, sin overflow, warning/crítico/EVA conservados y borde de campo ≥3.21:1. Capturas de selección desktop/mobile revisadas. Evidencia: `%TEMP%/remembermind-conducta-qa/seleccion-suave/`, `%TEMP%/remembermind-conducta-qa/seleccion-suave-motion/`, y equivalentes de Cognición. Verificación de otras familias por muestras CSS, sin afirmar recorrido operativo completo. Backend/BDD/permisos sin cambios. Stage/commit/push NONE.

## Propagación de los cuatro criterios a la captura de Enfermería — 14/10/2026

Se revisaron consumidores actuales y se localizaron brechas: el drawer de Seguimiento no diferenciaba su canvas, secciones de registro común no usaban la superficie elevada, cabeceras directas de Dolor/Signos no recibían la banda compartida y los patterns conservaban gaps distintos. Además existe un formulario independiente de Medicación con cards propias, incorporado a esta capa sin editar su trabajo funcional previo.

La capa común aplica canvas a body de modal/drawer, superficie glass y elevación a registro compartido/Medicación, encabezados de 16 px con tintes funcionales a sus equivalentes y separación de 20 px entre secciones en escritorio / 16 px en móvil. Grids de campos usan 16 px; se mantienen columnas y reflow de cada formulario. La card de contexto de Medicación comparte material/relieve; sus inputs y los del registro común reciben Nunito Sans y contraste de campo. Los overrides excluyen inválidos, foco, disabled y readonly donde corresponde. Las cards clínicas activas mantienen sus fondos/badges, sin devolver las franjas laterales.

Consumidores comprobados: Signos, Dolor, Ingesta, Hidratación, Eliminación, Movilidad, Conducta, Cognición, Heridas, Seguimiento/drawer, registros comunes (incluido Procedimiento) y administración de Medicación. No se crean secciones de dominio, campos ni formularios para completar el diseño.

Build PASS (advertencia previa de tamaño JS). 46 tests JS de Signos/Conducta/Cognición/Medicación PASS. `AdministracionMedicacionFormularioTest`: 13 PASS / 89 assertions. Galería de 14 familias × 5 viewports PASS (sin overflow, fuente/radio/sombra/contraste y colores clínicos preservados; borde de campo ≥3.21:1). Conducta/Cognición Blade/Alpine con transporte sintético en 1440/1280/1024/768/390 PASS; capturas revisadas. Evidencia: `%TEMP%/remembermind-conducta-qa/todos-formularios/`, `%TEMP%/remembermind-cognicion-qa/todos-formularios/`, `%TEMP%/remembermind-clinical-boxes-qa/`. Las demás familias se verificaron mediante CSS/componentes y pruebas pertinentes, no recorrido operativo completo. Backend/BDD/permisos sin cambios en esta tanda. Stage/commit/push NONE por trabajo previo mezclado.

## Café grisáceo claro con trama y contraste — 14/10/2026

La propietaria pide volver cálido el canvas gris, añadir trama, mejorar legibilidad y espacios, usando la implementación compartida. `--rm-capture-workspace` mezcla taupe 100 al 65 % con taupe 150; los bodies de modal/drawer reciben la trama CSS fina existente (puntos y diagonales). Se mantienen cards más claras y su glass/relieve. La tinta principal pasa a café 950 con matiz petróleo y la secundaria a café profundo; «letras más claras» se implementa como mayor legibilidad, no como texto pálido de bajo contraste.

Se centralizan `--rm-capture-section-gap` (24 px escritorio) y `--rm-capture-field-gap` (16 px campos/móvil) en la escala canónica. Los layouts existentes consumen los aliases, preservando columnas y contenidos. `form-section`, campos, modal/drawer y ventana clínica existentes comparten la capa visual; se conservan los componentes específicos de interacción clínica que no son intercambiables. No se crea otra biblioteca ni se afirma que todos los formularios tengan idéntico markup.

Build PASS (warning de tamaño JS existente), 46 tests JS relacionados PASS. Galería de 14 familias × 5 tamaños PASS; estados warning/crítico/EVA conservados, bordes de campo ≥3.21:1. Conducta/Cognición con Blade/Alpine reales y transporte sintético en 1440/1280/1024/768/390 PASS, capturas revisadas. Conducta también con movimiento normal: contraste de selección ≥4.5:1 al terminar las transiciones, trama presente y gap computado 24/16 px comprobados. Evidencia: `%TEMP%/remembermind-conducta-qa/cafe-trama-contraste/`, `%TEMP%/remembermind-conducta-qa/cafe-trama-contraste-motion/`, `%TEMP%/remembermind-cognicion-qa/cafe-trama-contraste/` y galería compartida. Sin navegación operativa completa acreditada de los otros formularios. Backend/BDD/permisos sin cambios; sin stage/commit/push.
