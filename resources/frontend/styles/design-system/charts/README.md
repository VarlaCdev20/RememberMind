# Chart Design System de RememberMind

## Fuente de verdad

- `tokens/chart-colors.css`: colores semánticos, superficies, tipografía, opacidades, grosor, radios y estados. Sus variables sirven tanto a CSS como a Chart.js.
- `components/charts.css`: card, encabezado, área de dibujo, leyenda y filtro.
- `charts/chart-theme.js`: lectura de tokens, colores, defaults de Chart.js y modo oscuro.
- `charts/chart-presets.js`: configuraciones reutilizables por tipo de gráfica.
- `charts/chart-livewire.js`: ciclo de vida de instancias y refresco de tema.
- `charts/chart-motion.js`: entrada única en viewport de las cards, también después de un morph de Livewire.
- `tokens/motion.css`: duraciones y curvas compartidas; `chart-colors.css` contiene los aliases de movimiento para gráficas.

El entrypoint es `resources/frontend/scripts/app.js`, que expone `window.RMCharts`. No hace falta instalar otra librería ni cargar una CDN.

## Semántica

| Tono | Uso |
| --- | --- |
| `care` | cuidado, estabilidad, cumplimiento |
| `clinical` | información clínica, operación, disponibilidad |
| `alert` | riesgo, alerta o incidencia real |
| `neutral` | comparación o categoría neutra |
| `cognitive` | cognición y ámbito psicosocial |
| `rehab` | movilidad y rehabilitación |
| `reference` | histórico, meta o línea base |

Los aliases de dominio `residents`, `beds`, `alerts`, `medication`, `cognitive`, `rehab`, `staff` y `activities` tienen variantes `primary` y `secondary`. Un tono no determina la gravedad clínica; esta procede del dato autorizado. Para donuts con más de dos categorías, se deben pasar colores semánticos explícitos en `customOptions.colors`.

## Uso

```js
const api = window.RMCharts;
const config = api.presets.semantic('line', 'clinical', fechas, valores);
api.init('residente-presion', canvas, config);
```

Para dos series, pasar datasets etiquetados:

```js
api.presets.semantic('area', 'residents', fechas, [
    { label: 'Admisiones', data: admisiones },
    { label: 'Altas', data: altas },
]);
```

Para categorías con significado propio:

```js
api.presets.doughnut(
    ['Activos', 'Disponibles'],
    [activos, disponibles],
    [api.color('care'), api.color('clinical')],
);
```

Los presets disponibles son `line`, `area`, `bar`, `stackedBar`, `barHorizontal`, `doughnut`, `pie`, `radar`, `scatter`, `gauge` y `sparkline`. El factory semántico admite `line`, `area`, `bar`, `stackedBar`, `barHorizontal`, `donut`, `radar` y `sparkline`.

`api.color(tone)`, `api.getCss('--rm-chart-*')` y `api.number('--rm-*-width', fallback)` resuelven los tokens. Evitar HEX, opacidades, tipografías y radios locales.

## Componente visual

```html
<section class="rm-chart-card">
    <header class="rm-chart-header">
        <div class="rm-chart-heading">
            <h3 class="rm-chart-title">Evolución clínica</h3>
            <p class="rm-chart-subtitle">Últimos 30 días</p>
        </div>
    </header>
    <div class="rm-chart-body is-md">
        <canvas aria-label="Evolución clínica de los últimos 30 días" role="img"></canvas>
    </div>
</section>
```

Las gráficas deben acompañarse de etiquetas y valores legibles. Un canvas por sí solo no debe ser la única forma de acceder a un dato operativo esencial. El sistema respeta movimiento reducido y tokens de modo oscuro. Si una pantalla crea configuraciones al cambiar de tema, registrar su renderizador con `api.onThemeChange(render)`.

`api.init(key, canvas, config)` conserva la instancia y la visibilidad de series cuando coinciden la clave, el canvas y el tipo. Actualiza los valores con interpolación de `--rm-chart-motion-update-duration`; solo reemplaza la instancia cuando cambia el canvas o el tipo. La primera entrada de cada card se observa una vez con `IntersectionObserver`; los gráficos fuera de pantalla no reciben efectos repetidos al volver a hacer scroll. En cambios de datos de Livewire se debe llamar otra vez a `api.init` con la misma clave.

## Datos

Los tokens solo cambian la presentación. Reutilizar Models/Services/queries existentes y documentar para cada gráfica fuente, período, denominador y estados incluidos. No inventar métricas, gravedad ni columnas para completar una visualización.
