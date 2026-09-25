{{-- RememberMind Chart Design System - Ejemplo de Referencia --}}
<section class="rm-chart-card rm-chart-glass">
    <header class="rm-chart-header">
        <div class="rm-chart-heading">
            <h3 class="rm-chart-title">
                <i class="ph ph-shield-warning text-sm text-[var(--rm-danger)]"></i>
                Nivel de Severidad
            </h3>
            <p class="rm-chart-subtitle">Distribucion por gravedad clinica</p>
        </div>
        <span class="rm-chart-kpi-badge">0 total</span>
    </header>
    <div class="rm-chart-body is-md">
        <canvas id="chart-ejemplo-nivel"></canvas>
    </div>
</section>

<section class="rm-chart-card rm-chart-glass">
    <header class="rm-chart-header">
        <div class="rm-chart-heading">
            <h3 class="rm-chart-title">
                <i class="ph ph-funnel text-sm text-[var(--rm-primary)]"></i>
                Canales y Origenes
            </h3>
            <p class="rm-chart-subtitle">Alertas por canal de deteccion</p>
        </div>
    </header>
    <div class="rm-chart-body is-md">
        <canvas id="chart-ejemplo-origen"></canvas>
    </div>
</section>

<section class="rm-chart-card rm-chart-glass">
    <header class="rm-chart-header">
        <div class="rm-chart-heading">
            <h3 class="rm-chart-title">
                <i class="ph ph-chart-line text-sm text-[var(--rm-accent)]"></i>
                Evolucion Clinica
            </h3>
            <p class="rm-chart-subtitle">Tendencias de los ultimos controles</p>
        </div>
    </header>
    <div class="rm-chart-body is-lg">
        <canvas id="chart-ejemplo-evolucion"></canvas>
    </div>
</section>
{{-- Micrográfico Sparkline de Tendencia para KPIs --}}
<section class="rm-card-metric rm-kpi-card-sparkline">
    <div class="flex items-center justify-between">
        <span class="rm-metric-label text-[var(--rm-primary)]">Tendencia Semanal</span>
        <span class="rm-badge rm-badge-success text-[10px]">+14%</span>
    </div>
    <div class="flex items-baseline justify-between mt-1">
        <span class="rm-metric-value">128</span>
        <span class="text-xs text-[var(--rm-text-soft)]">controles</span>
    </div>
    <div class="rm-sparkline is-sm mt-1">
        <canvas id="chart-ejemplo-sparkline"></canvas>
    </div>
</section>
