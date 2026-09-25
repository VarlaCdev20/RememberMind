# RememberMind Chart Design System

## Arquitectura

```
resources/frontend/styles/design-system/
+-- tokens/
|   +-- chart-colors.css          <- Variables CSS para charts
+-- components/
|   +-- charts.css                <- Estilos de cards y contenedores
+-- charts/
|   +-- chart-theme.js            <- Motor central (paleta, gradientes, opciones)
|   +-- chart-presets.js          <- Presets por tipo de grafico
|   +-- chart-livewire.js         <- Helpers para Livewire (destroy/create)
|   +-- README.md                 <- Este archivo
+-- examples/
    +-- example-chart-card.blade.php
```

## Uso Rapido

### 1. Doughnut con colores semanticos

```js
const sem = rmChartSemanticColors();
const config = rmDoughnutChartConfig(
    ['Critico', 'Alto', 'Medio', 'Bajo'],
    [critico, alto, medio, bajo],
    [sem.danger, sem.warningHigh, sem.warning, sem.info]
);
rmInitChart('canvas-id', config, this.charts, 'nivel');
```

### 2. Barra horizontal con paleta categorica

```js
const palette = rmChartPalette();
const config = rmBarHorizontalChartConfig(
    labels,
    values,
    labels.map((_, i) => palette[i % 10])
);
rmInitChart('canvas-id', config, this.charts, 'origen');
```

### 3. Grafico de area

```js
const config = rmAreaChartConfig(
    fechas,
    [{ label: 'Presion Sistolica', data: sistolica, color: '#DC2626' }]
);
rmInitChart('canvas-id', config, this.charts, 'presion');
```

## Colores

### Categoricos (10 colores)
Para categorias clinicas, NO representan gravedad:
- `--rm-chart-1` a `--rm-chart-10`
- Acceso JS: `rmChartPalette()`

### Semanticos
Solo cuando el backend ya determino un estado:
- `danger` -> Critico
- `warningHigh` -> Alto
- `warning` -> Medio/Pendiente
- `success` -> Resuelto
- `info` -> En atencion / Bajo
- Acceso JS: `rmChartSemanticColors()`

## Dark Mode

Los graficos se actualizan automaticamente al cambiar tema:

```js
rmObserveThemeChanges(this.charts, () => this.initAllCharts());
```

Detecta `html.dark` o `data-theme="dark"` via el evento
`remembermind:theme-changed` del sistema.

## Livewire

**IMPORTANTE:** Siempre destruir antes de crear:

```js
rmInitChart(canvasId, config, store, key);
// Internamente: if (store[key]) store[key].destroy();
```

Observar cambios reactivos:

```js
rmWatchLivewireData(this.$wire, 'chartData', (newData) => {
    this.chartData = newData;
    this.updateCharts();
});
```

## Reglas de Transparencia

| Tipo | Fill Alpha |
|------|-----------|
| Area (arriba) | ~38% |
| Area (centro) | ~18% |
| Area (abajo) | ~2% |
| Doughnut/Pie | ~85-90% |
| Radar | ~15-20% |
| Barras | ~72-92% gradiente |

## Alturas Estandar

| Clase | Altura |
|-------|--------|
| `.is-sm` | 210px |
| `.is-md` | 250px |
| `.is-lg` | 300px |

## Cards

```html
<section class="rm-chart-card rm-chart-glass">
    <header class="rm-chart-header">
        <div class="rm-chart-heading">
            <h3 class="rm-chart-title">Titulo</h3>
            <p class="rm-chart-subtitle">Subtitulo</p>
        </div>
    </header>
    <div class="rm-chart-body is-md">
        <canvas id="chart-id"></canvas>
    </div>
</section>
```

## NO Hacer

- NO hardcodear `backgroundColor: '#...'` en vistas
- NO crear charts-v2.js o similares
- NO instalar plugins sin auditar package.json
- NO decidir umbrales medicos desde el design system
