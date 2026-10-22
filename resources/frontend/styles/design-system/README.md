# RememberMind Design System

Fuente canónica de tokens, componentes compartidos y patrones visuales.
La paleta oficial se centraliza en `tokens/colors.css` y se expone en
`tailwind.config.js`. La entrada de estilos es
`resources/frontend/styles/app.css`, que importa `design-system/index.css`.

## Arquitectura

1. **Paleta**: `--rm-palette-*` contiene los valores oficiales, definidos una sola vez.
2. **Semántica**: `--rm-bg-app`, `--rm-primary`, `--rm-info`, etc. representan usos.
3. **Componentes**: `--rm-sidebar-*`, `--rm-button-*`, `--rm-input-*`, etc. consumen esos usos.

Los nombres de la paleta identifican una función, nunca una escala como brown-1.
Los alias existentes `--color-*` y las familias numéricas de Tailwind consumen
el mismo contrato durante la migración progresiva. No introducir otra paleta por rol.

Paleta final aprobada el 14/10/2026 y corrección posterior: superficies y controles sólidos, relieve con bordes/sombras y conservación de los degradados tipográficos. Reemplaza las propuestas anteriores. Ver [implementación y verificación](../../../../docs/frontend/PALETA_FINAL_SOLIDA_Y_RELIEVE.md).

## Paleta oficial en modo claro

| Token semántico | Valor | Uso |
|---|---|---|
| `--rm-bg-app` | #E7E0D9 | Fondo de la aplicación |
| `--rm-bg-sidebar` | #D6C6B9 | Estructura y sidebar |
| `--rm-surface` | Mezcla 20% de #A69E96 con el fondo | Capuchino suave para cards, formularios, menús y modales |
| `--rm-surface-soft` | Mezcla 30% de #A69E96 con el fondo | Hover y subdivisiones |
| `--rm-surface-muted` | Mezcla 50% de #A69E96 con el fondo | Superficie atenuada |
| `--rm-text-primary` | #59524B | Títulos, datos, navegación e iconos |
| `--rm-text-secondary` | #665F57 | Descripciones y metadatos |
| `--rm-primary` | #8DA18A | Verde principal salvia |
| `--rm-primary-ink` | #56604F | Texto, iconos y foco contrastados sobre fondos claros |
| `--rm-primary-soft` | #C9CDC5 | Salvia suave para acentos |
| `--rm-selected-bg` | #D6D9D3 | Selección suave con texto #56604F |
| `--rm-sage` | #A8BAA3 | Acento de cuidado y seguimiento |
| `--rm-sage-soft` | #C2CEBE | Grupo abierto y estado positivo suave |
| `--rm-info` | #8699AC | Acento clínico, información y datos |
| `--rm-info-soft` | #C2CCD5 | Fondo informativo |
| `--rm-psychology` | #CEB0B0 | Acento de bienestar emocional |
| `--rm-nutrition` | #C4C4A0 | Acento de alimentación y nutrición |
| `--rm-warning` | #C69245 | Advertencia real |
| `--rm-danger` | #B85C58 | Error, riesgo o acción destructiva |
| `--rm-border` | rgba(74,53,47,.14) | Separación discreta |
| `--rm-border-strong` | rgba(74,53,47,.24) | Separación reforzada |

Las variantes `*-strong` y `*-action` se derivan con `color-mix()`:
los acentos suaves no se utilizan directamente como texto pequeño.
El modo oscuro adapta superficies y contraste manteniendo los mismos contratos.

## Utilidades Tailwind

| Clase | Token |
|---|---|
| `bg-rm-app` | `--rm-bg-app` |
| `bg-rm-sidebar` | `--rm-bg-sidebar` |
| `bg-rm-surface`, `bg-rm-surface-soft` | Superficies |
| `text-rm-primary` | `--rm-text-primary` (texto café) |
| `text-rm-secondary` | `--rm-text-secondary` |
| `bg-rm-primary-action` | `--rm-primary` (salvia con texto café) |
| `text-rm-primary-ink` | `--rm-primary-ink` (texto verde contrastado) |
| `bg-rm-primary-soft` | Fondo seleccionado |
| `bg-rm-sage-soft` | Grupo abierto |
| `text-rm-info-strong`, `text-rm-danger-strong` | Texto semántico accesible |
| `border-rm`, `border-rm-border-strong` | Bordes |
| `outline-rm-focus` | Foco institucional |

Se admiten modificadores de opacidad, por ejemplo `bg-rm-surface/80`.
No escribir HEX ni crear colores locales en Blade o Livewire.

## Sidebar de referencia

La referencia histórica autorizada es
`SUPERADMINISTRADOR:resources/views/components/layout/barra-lateral-sistema.blade.php`.
Se conservan marca, grupos, jerarquía, iconografía, submenús, badges,
colapsado desktop, flyouts y drawer móvil.
En el shell autenticado, sidebar y barra superior usan capuchino `#D7CEC7`;
el lienzo usa el fondo aclarado `#D7CEC7` (petróleo en modo oscuro). El sidebar abierto mide
`224px` y el colapsado `80px`. El hover de los iconos superiores consume la
misma superficie salvia translúcida que la selección del sidebar.

| Estado | Tratamiento |
|---|---|
| Normal | Transparente y texto primario |
| Hover | Salvia translúcida, 160 ms ease-out |
| Grupo abierto | Tierra-ceniza suave, sin marcador de página |
| Página actual | Verde suave, texto/icono institucional y marcador |
| Focus | Outline verde visible |
| Movimiento reducido | Transiciones reducidas y sin desplazamientos decorativos |

Solo el destino que mejor corresponde a la ruta actual se marca como página.
El padre abierto tiene otro tratamiento. El acordeón anima la altura con
Alpine Collapse (incluido en Livewire), evitando escalar las letras.

## Componentes compartidos

- Botones: usar `rm-btn` y una variante existente; principal y éxito usan verde,
  acciones destructivas usan danger-action y el texto conserva contraste.
- Formularios: superficies neutras, labels visibles, placeholder legible,
  error con texto y foco institucional. La validación sigue perteneciendo al backend.
- Cards y tablas: superficies neutras, bordes suaves y sombras cálidas.
  Los acentos funcionales son pequeños; la selección sí usa verde suave.
- Badges y alertas: color derivado para texto, además de texto/icono.
  Warning y danger únicamente para significados reales determinados por el dominio.
- Modales: superficie canónica, elevación cálida y overlay compartido.
- Gráficos: series basadas en tokens, leyendas/etiquetas y colores semánticos explícitos.
  El conversor resuelve funciones CSS a RGB antes de entregar colores a Chart.js.

La proporción orientativa es 65% neutros, 25% verde y como máximo 10% acentos.
No colorear una pantalla completa según el rol o el área funcional.

## Tipografía y accesibilidad

Las fuentes locales y la escala oficial se centralizan en `resources/css/app.css`.
La entrada Vite existente importa ese archivo; no hay cargas de fuentes por CDN.

| Uso | Fuente | Peso |
| --- | --- | --- |
| Marca, título de página y KPI | Outfit | 700/800 |
| Secciones, módulos y navegación principal | Plus Jakarta Sans | 600/700 |
| Títulos de cards, badges y alertas | Nunito Sans | 600/700 |
| Texto, formularios, tablas y metadatos | Inter | 400/500/600 |

Consumir `rm-brand`, `rm-page-title`, `rm-section-title`, `rm-card-title`,
`rm-body`, `rm-label`, `rm-caption`, `rm-metric`, `rm-nav-section`,
`rm-nav-group`, `rm-nav-item`, `rm-table-head`, `rm-table-cell`, `rm-badge`,
`rm-alert-title` y `rm-alert-description`. Tailwind expone `font-brand`,
`font-heading`, `font-friendly` y `font-body` con los mismos tokens.
Los alias de tamaño anteriores conservan la densidad de vistas aún no migradas;
no deben usarse para nuevos componentes. Las referencias de esta fase son
sidebar, metric-card, form-section, tabla-bitacora-dashboard y callout.

El foco de teclado debe permanecer visible. Respetar movimiento reducido,
alto contraste y colores forzados. Las combinaciones con superficie-muted
requieren revisión: no asumir que un acento suave o texto secundario tiene
contraste suficiente sobre cualquier fondo.

## Aplicación progresiva y verificación

Esta fase conecta tokens y utilidades globales, sidebar y componentes compartidos.
Las pantallas que ya consumen el contrato se actualizan automáticamente.
Todavía existen estilos históricos y colores locales fuera de esta capa:
su migración debe hacerse por pantalla, revisando el significado de cada estado,
sin reemplazos masivos por color ni cambios de comportamiento.

Para revisar el sidebar, abrir `/admin/usuarios` con una cuenta autorizada.
También se comparte en `/dashboard` y `/admin/administracion/dashboard`.

Verificación proporcional:

```bash
npm run build
npm test
php artisan test --filter=SidebarServiceTest
php artisan test --filter=FrontendArchitectureTest
```

La revisión de contraste de esta fase utiliza componentes renderizados con el CSS
compilado, en modo claro y oscuro, y contempla ancho móvil, colapsado y movimiento
reducido. No sustituye la aprobación visual de cada pantalla institucional.

### Escala café aprobada

`#59524B · #665F57 · #746B62 · #82786E · #8F857B · #9B9288 · #A69E96`.
Los tonos intermedios sirven para bordes y profundidad; el texto pequeño usa
los dos tonos oscuros sobre superficies aclaradas para mantener contraste.

### Paleta final y relieve

La salvia institucional es #689D7C y el fondo general #D7CEC7. La serie de gráficas es salvia, petróleo, coral, oro, menta y lavanda. Las superficies y rellenos de gráficas son uniformes. El degradado de letras de la cabecera se conserva. Los tokens rm-relief-card-shadow y rm-relief-panel-shadow centralizan el relieve.

### Familia earth y superficies

`--rm-earth-900` a `--rm-earth-300` definen los siete tonos tierra.
Los nombres `--rm-palette-brown-*` son alias de esta familia, sin duplicar HEX.
Tailwind expone `text-rm-earth-900` a `text-rm-earth-300`, `text-rm-icon`,
`text-rm-muted`, `bg-rm-capuchino` y `bg-rm-selection`.

Texto principal: earth-900; secundario: earth-800; muted: earth-700;
iconos neutros: earth-600. Los iconos acompañados de texto son decorativos.
Earth-400 y earth-300 son tonos de divisores y superficies, no texto importante.
Los bordes semánticos son rgba(89, 82, 75, .14) y rgba(89, 82, 75, .24).

El sidebar estructural conserva cacao #D7CEC7. Los submenús usan una
superficie lino aclarada para permitir texto earth-800; los captions pequeños
conservan earth-800 cuando earth-700 no alcanza 4.5:1 sobre su superficie.
El fondo general #D7CEC7 y las cards aclaradas conservan la separación visual.
El tema oscuro reinterpreta texto, iconos y superficies sin alterar las primitivas.

Corrección del 14 de octubre: el fondo general usa café grisáceo #D7CEC7; navbar y sidebar conservan capuchino #D7CEC7. Solo el lienzo tiene una trama discreta de puntos menudos: radio de 0.6 px y separación de 16 px, adaptada a ambos temas. Conserva la profundidad interior. Los tokens rm-canvas-relief-* están en tokens/shadows.css; tarjetas y controles siguen sólidos.

El listado de residentes conserva su sección, contador y estados, sin superficie, borde ni sombra envolvente. Las tarjetas individuales mantienen su relieve.

Superficies cálidas unificadas: cabeceras, filtros, cards, paneles, modales y menús usan #E6DED6; los campos #D6CCC3. Header reutiliza la superficie principal; soft y elevated tienen tonos cálidos propios para distinguir profundidad. Las utilidades blancas heredadas del shell reciben la superficie semántica del tema. El relieve distingue elevación sin aclarar los paneles hasta blanco.

Dirección vigente: neutros arena/cacao de intensidad media, superficies de apoyo #D2C5BB y elevadas #EBE3DC. Acentos salvia, petróleo, coral, oro, menta y lavanda más presentes en sus funciones existentes. Relieve con bordes y sombras cálidas; el color de estado sigue procediendo del dominio. No se cambian layout ni reglas clínicas.

Corrección vigente: sidebar y navbar se aclaran al mismo café grisáceo #D7CEC7 del fondo. El token capuchino reutiliza el de página, sin duplicar color; acentos y relieve de Inicio se conservan.

## Composición global vigente (14/10/2026)

La actualización de legibilidad comparte Nunito Sans en títulos funcionales e Inter en texto: página 28 px, sección 18 px, card 16 px, cuerpo 15 px, tablas 14 px y labels 13 px. Outfit conserva la marca y el saludo destacado. Los contenedores usan separación de 20 px, grids de 16 px y filtros de 44 px de alto, con selección sin bisel. La base cálida y los acentos de estado conservan su significado. Ver [alcance y evidencia](../../../../docs/frontend/COMPOSICION_GLOBAL_2026_10_14.md).

Iconografía compartida: `--rm-icon-md` para iconos de tarjetas/acciones con texto (20 px), `--rm-icon-lg` para navegación y controles de solo icono (24 px). El soporte conserva espacio suficiente y las tintas se verifican sobre la superficie compuesta en claro/oscuro. Los estados conservan sus variantes semánticas. Véase la sección de iconos del informe de composición global.

Escala vigente tras la petición de compactar (15/10/2026): títulos de página 26 px, dashboard 28 px, sección 17 px, card 15 px y cuerpo 14 px. Espacios principales 14/18/22 px y card base 14 px. Se conservan iconos legibles y targets de acción. Esta actualización sustituye los tamaños superiores de la sección de composición global; ver su informe para evidencia.

## Consulta del Sistema experto (22/10/2026)

`patterns/expert-laboratory.css` incorpora las composiciones `rm-knowledge-summary`, `rm-knowledge-overview`, `rm-knowledge-graph-layout`, filtros, inspector y catálogos. Consumen exclusivamente los tokens existentes y `rm-btn`/`section-card`/`drawer-livewire`; no añaden paleta, fuentes ni controles paralelos. Su consumidor es `ApoyoDecision/…/sistema-experto/conocimiento-panel` y sus parciales. `rm-knowledge-workspace` evita colisionar con `rm-expert-workspace`, que ya pertenece a la lectura del residente.

`patterns/expert-results.css` añade la matriz documental de dominios, navegación de lectura y resumen operacional de Enfermería. Los cinco dominios representan disponibilidad, nunca una escala de deterioro. El grafo administrativo reutiliza el visor SVG local mediante `rmExpertKnowledgeGraph`; los tipos de nodo tienen etiqueta textual, el inspector conserva entradas/salidas y las fuentes proceden de mapeos declarados. El lienzo muestra como máximo 36 nodos por página y ofrece el mismo contenido en lista accesible. El grafo histórico mantiene su contrato y permisos propios. Referencias, adaptaciones y QA: [informe de entrega](../../../../docs/frontend/SISTEMA_EXPERTO_REFERENCIAS_2026_10_22.md).
