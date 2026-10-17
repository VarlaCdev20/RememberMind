# Superficies clínicas claras — 14/10/2026

## Revisión posterior de la propietaria: glass sin bisel

La indicación posterior sustituye el acabado elevado de botones: botones lisos, sin gradiente, sombra interior ni desplazamiento al pulsar; se conserva el foco visible. Las cards compartidas utilizan una superficie al 84% y las acciones menta una superficie translúcida. Las sombras de cards y paneles vuelven a elevaciones exteriores suaves, sin el escalón de relieve. La regla final de paleta consume estos tokens para impedir que reaparezca el bisel.

Build PASS y ficha renderizada en los cinco anchos PASS, sin errores JS ni overflow y con contraste muestreado mínimo de 4.5:1. En la vista conectada de Enfermería se comprobó sombra de botón `none` y alpha de card `0.84`. Evidencia sintética en `storage/app/qa/glass-sin-bisel/`. Este ajuste es CSS; no se vuelve a ejecutar ni se atribuye una nueva suite PHP/JS.

## Implementado

La instrucción vigente de la propietaria pide superficies claras y una composición menos plana. Se evolucionan los tokens compartidos `rm-capture-*`, sin otra paleta por formulario:

- Fondo café grisáceo claro con la trama existente; cards más claras y ligeramente translúcidas.
- Separación mediante espacio, sombra suave y relieve interior; texto con tinta contrastada.
- Acciones principales en menta clara y texto verde legible.
- Ficha: contexto elevado, bloques internos redondeados y encabezados diferenciados en menta, azul y lavanda. Se retiran las franjas laterales decorativas.
- Selector PA/FC/SpO₂/Temp con estado `aria-pressed` y selección menta clara.
- Evolución: altura inicial móvil proporcional al viewport para que encabezado y herramientas dejen espacio útil al contenido. Se conserva la geometría compartida, límites, arrastre y redimensionamiento.

## Integración

`tokens/colors.css`, `patterns/care-surfaces.css` y `patterns/clinical-detail.css` abastecen a los consumidores clínicos actuales: Signos, Dolor, Ingesta, Hidratación, Eliminación, Movilidad, Cognición, Conducta, Heridas, Medicación y capturas genéricas compatibles. La ficha reutiliza estos mismos tokens. No se introducen campos, reglas clínicas, persistencia ni permisos.

La geometría de evolución sigue en el componente existente de Signos, reutilizado por otros formularios. La prueba responsive de esa geometría se actualiza junto al nuevo tamaño inicial; conserva los gates de confinamiento, arrastre, límites y datos.

## Verificación

- `npm test`: **192 PASS**, sin pruebas omitidas.
- `php artisan test --filter='FichaResumenClinicoTest|FichaPacienteBotonesTest'`: **9 PASS / 49 assertions**.
- `npm run build`: **PASS**. Permanece el aviso general de bundle mayor a 500 kB.
- Ficha sintética: **5 anchos** (1440, 1280, 1024, 768 y 390 px), sin overflow horizontal ni errores JS. Texto muestreado sobre la superficie compuesta con contraste mínimo de 4.5:1; superficies ordinarias muestreadas claras.
- Capturas/consultas compartidas: **60 casos** en claro, 12 vistas en los cinco anchos; apertura, cierre, conservación de captura y distribución dentro del viewport.
- Medicación: **50 casos** en claro, 10 estados en los cinco anchos; los pares de texto muestreados pasan 4.5:1.
- Comprobación de la ficha conectada: los estilos compartidos se ejecutan, la acción principal tiene fondo menta y no hay overflow horizontal.

La evidencia reproducible usa Blade renderizado por tests con datos sintéticos, CSS/JS del build y transporte Livewire simulado. No acredita un guardado clínico desde el navegador operativo, auditoría completa de accesibilidad ni certificación clínica. Este lote no modifica el backend clínico ni la BDD.

## Entrega

Capturas, resultados y logs locales: `storage/app/qa/superficies-claras/` (ignorado por Git).

Sin nueva dependencia, cambio de schema ni legacy ejecutable nuevo. Sin commit/push de este lote: el checkout conserva numerosos cambios previos mezclados, incluidos los archivos compartidos modificados aquí.
