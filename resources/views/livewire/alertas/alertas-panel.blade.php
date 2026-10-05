<div class="rm-pilot-alertas rm-page-layout font-sans">
 {{-- Encabezado Institucional Canónico --}}
 <x-ui.page-header
  title="Alertas clínicas y cuidados"
  subtitle="Monitoreo continuo de alertas clínicas, signos vitales, administración de fármacos y eventos asistenciales en Los Almendros."
  overline="Centro de control asistencial"
  icon="ph-shield-warning"
  color="bg-[var(--rm-danger-action)]">
  @can('alertas.gestionar')
  <x-ui.action-button
   variant="accent"
   size="sm"
   icono="ph-plus-circle"
   wire:click="abrirCrear"
   class="cursor-pointer">
   Registrar alerta
  </x-ui.action-button>

  <x-ui.action-button
   variant="secondary"
   size="sm"
   wire:click="detectarAlertas"
   wire:loading.attr="disabled"
   class="cursor-pointer">
   <i class="ph ph-arrows-clockwise text-lg" wire:loading.class="animate-spin" wire:target="detectarAlertas"></i>
   <span wire:loading.remove wire:target="detectarAlertas">Detectar pendientes</span>
   <span wire:loading wire:target="detectarAlertas">Analizando...</span>
  </x-ui.action-button>
  @endcan

  <x-ui.action-button
   variant="icon"
   size="sm"
   wire:click="$refresh"
   title="Actualizar datos"
   aria-label="Actualizar datos"
   class="cursor-pointer">
   <i class="ph ph-arrow-clockwise text-lg" wire:loading.class="animate-spin" wire:target="$refresh"></i>
  </x-ui.action-button>
 </x-ui.page-header>

 {{-- Notificación Flash Institucional --}}
 @if (session()->has('mensaje'))
 <x-ui.callout variant="success" title="Operación completada">
  {{ session('mensaje') }}
 </x-ui.callout>
 @endif

 <x-validation-errors class="mb-2" />

 {{-- TARJETAS KPI: BASE CÁLIDA (var(--rm-surface)) + MODO OSCURO --}}
 <section class="rm-kpi-grid">
 {{-- Card 1: Total Alertas --}}
 <div wire:click="limpiarFiltros"
  class="rm-card-metric rm-card-interactive cursor-pointer border-l-[3px] border-l-[var(--rm-action-primary)]">
  <div class="flex items-center justify-between">
  <span class="rm-metric-label">Total Registro</span>
  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/20">
   <i class="ph ph-bell text-lg"></i>
  </span>
  </div>
  <div class="mt-1 flex items-baseline justify-between gap-2">
  <span class="rm-metric-value">{{ $conteos['total'] ?? 0 }}</span>
  <div class="w-20 h-7 shrink-0 pointer-events-none">
   <x-ui.sparkline :id="'spark-kpi-total'" :data="[max(1, ($conteos['total'] ?? 0) - 3), max(1, ($conteos['total'] ?? 0) - 1), max(1, ($conteos['total'] ?? 0) - 2), max(1, ($conteos['total'] ?? 0) - 1), ($conteos['total'] ?? 0)]" color="primary" height="28px" />
  </div>
  </div>
  <div class="rm-metric-meta justify-between">
  <span>Alertas en sistema</span>
  <span class="font-semibold underline underline-offset-2">Ver todas</span>
  </div>
 </div>

 {{-- Card 2: Críticas y Altas --}}
 <div wire:click="setFiltroRapido('filtroNivel', 'CRITICO')"
  class="rm-card-metric rm-card-interactive cursor-pointer border-l-[3px] border-l-[var(--rm-danger)] {{ $filtroNivel === 'CRITICO' ? 'ring-1 ring-[var(--rm-danger)]/40 bg-[var(--rm-danger-soft)]/20' : '' }}">
  <div class="flex items-center justify-between">
  <div class="flex items-center gap-1.5">
   <span class="relative flex h-2.5 w-2.5">
   <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[var(--rm-danger-action)] opacity-75"></span>
   <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[var(--rm-danger-action)]"></span>
   </span>
   <span class="rm-metric-label text-[var(--rm-danger-action)]">Críticas y Altas</span>
  </div>
  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/20">
   <i class="ph ph-warning-octagon text-lg"></i>
  </span>
  </div>
  <div class="mt-1 flex items-baseline justify-between gap-2">
  <span class="rm-metric-value text-[var(--rm-danger-action)]">
   {{ ($conteos['criticas'] ?? 0) + ($conteos['altas'] ?? 0) }}
  </span>
  <div class="w-20 h-7 shrink-0 pointer-events-none">
   <x-ui.sparkline :id="'spark-kpi-criticas'" :data="[max(0, ($conteos['criticas'] ?? 0) - 2), max(0, ($conteos['criticas'] ?? 0) + 1), max(0, ($conteos['criticas'] ?? 0) - 1), ($conteos['criticas'] ?? 0)]" color="danger" height="28px" />
  </div>
  </div>
  <div class="rm-metric-meta justify-between text-[var(--rm-text-soft)]">
  <span class="font-medium text-[var(--rm-danger-action)]">{{ $conteos['criticas'] ?? 0 }} críticas</span>
  <span>{{ $conteos['altas'] ?? 0 }} altas</span>
  </div>
 </div>

 {{-- Card 3: Por Atender --}}
 <div wire:click="setFiltroRapido('filtroEstado', 'ABIERTA')"
  class="rm-card-metric rm-card-interactive cursor-pointer border-l-[3px] border-l-[var(--rm-warning)] {{ $filtroEstado === 'ABIERTA' ? 'ring-1 ring-[var(--rm-warning)]/40 bg-[var(--rm-warning-soft)]/20' : '' }}">
  <div class="flex items-center justify-between">
  <span class="rm-metric-label text-[var(--rm-warning)]">Por Atender</span>
  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-warning-soft)] text-[var(--rm-warning)] border border-[var(--rm-warning)]/20">
   <i class="ph ph-clock-countdown text-lg"></i>
  </span>
  </div>
  <div class="mt-1 flex items-baseline justify-between gap-2">
  <span class="rm-metric-value text-[var(--rm-warning)]">{{ $conteos['abiertas'] ?? 0 }}</span>
  <div class="w-20 h-7 shrink-0 pointer-events-none">
   <x-ui.sparkline :id="'spark-kpi-abiertas'" :data="[max(0, ($conteos['abiertas'] ?? 0) + 2), max(0, ($conteos['abiertas'] ?? 0) + 1), max(0, ($conteos['abiertas'] ?? 0)), ($conteos['abiertas'] ?? 0)]" color="warning" height="28px" />
  </div>
  </div>
  <div class="rm-metric-meta text-[var(--rm-text-soft)]">
  <i class="ph ph-hourglass-high"></i>
  <span>Abiertas sin atención</span>
  </div>
 </div>

 {{-- Card 4: En Atención --}}
 <div wire:click="setFiltroRapido('filtroEstado', 'EN_ATENCION')"
  class="rm-card-metric rm-card-interactive cursor-pointer border-l-[3px] border-l-[var(--rm-info)] {{ $filtroEstado === 'EN_ATENCION' ? 'ring-1 ring-[var(--rm-info)]/40 bg-[var(--rm-info-soft)]/20' : '' }}">
  <div class="flex items-center justify-between">
  <span class="rm-metric-label text-[var(--rm-info-action)]">En Atención</span>
  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-info-soft)] text-[var(--rm-info)] border border-[var(--rm-info)]/20">
   <i class="ph ph-first-aid text-lg"></i>
  </span>
  </div>
  <div class="mt-1 flex items-baseline justify-between gap-2">
  <span class="rm-metric-value text-[var(--rm-info-action)]">{{ $conteos['en_atencion'] ?? 0 }}</span>
  <div class="w-20 h-7 shrink-0 pointer-events-none">
   <x-ui.sparkline :id="'spark-kpi-atencion'" :data="[max(0, ($conteos['en_atencion'] ?? 0) - 1), max(0, ($conteos['en_atencion'] ?? 0) + 1), max(0, ($conteos['en_atencion'] ?? 0)), ($conteos['en_atencion'] ?? 0)]" color="info" height="28px" />
  </div>
  </div>
  <div class="rm-metric-meta text-[var(--rm-text-soft)]">
  <i class="ph ph-arrows-clockwise"></i>
  <span>Protocolo en curso</span>
  </div>
 </div>

 {{-- Card 5: Resueltas --}}
 <div wire:click="setFiltroRapido('filtroEstado', 'CERRADA')"
  class="rm-card-metric rm-card-interactive cursor-pointer border-l-[3px] border-l-[var(--rm-success)] {{ $filtroEstado === 'CERRADA' ? 'ring-1 ring-[var(--rm-success)]/40 bg-[var(--rm-success-soft)]/20' : '' }}">
  <div class="flex items-center justify-between">
  <span class="rm-metric-label text-[var(--rm-success-action)]">Resueltas</span>
  <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--rm-success-soft)] text-[var(--rm-success)] border border-[var(--rm-success)]/20">
   <i class="ph ph-check-circle text-lg"></i>
  </span>
  </div>
  <div class="mt-1 flex items-baseline justify-between gap-2">
  <span class="rm-metric-value text-[var(--rm-success-action)]">{{ $conteos['cerradas'] ?? 0 }}</span>
  <div class="w-20 h-7 shrink-0 pointer-events-none">
   <x-ui.sparkline :id="'spark-kpi-cerradas'" :data="[max(0, ($conteos['cerradas'] ?? 0) - 3), max(0, ($conteos['cerradas'] ?? 0) - 2), max(0, ($conteos['cerradas'] ?? 0) - 1), ($conteos['cerradas'] ?? 0)]" color="success" height="28px" />
  </div>
  </div>
  <div class="rm-metric-meta text-[var(--rm-text-soft)]">
  <i class="ph ph-archive-box"></i>
  <span>Historial conservado</span>
  </div>
 </div>
 </section>

 {{-- Script Chart.js — RememberMind Chart Design System --}}
<script>
    window.alertasDashboardComponent = function(chartData, conteos) {
        return {
            chartData: chartData,
            conteos: conteos,

            initAllCharts() {
                const initCharts = () => {
                    if (typeof Chart === 'undefined' || typeof window.RMCharts === 'undefined' || !window.RMCharts.presets) {
                        setTimeout(initCharts, 80);
                        return;
                    }
                    try { this.renderGraficoNivel(); } catch (e) { console.warn('[RM Charts] Error en gráfico nivel:', e); }
                    try { this.renderGraficoOrigen(); } catch (e) { console.warn('[RM Charts] Error en gráfico origen:', e); }
                    try { this.renderGraficoEstado(); } catch (e) { console.warn('[RM Charts] Error en gráfico estado:', e); }
                };

                this.$nextTick(initCharts);

                // Reactividad automática con Livewire
                if (typeof this.$wire !== 'undefined' && this.$wire) {
                    window.RMCharts.watchLivewire(this.$wire, 'chartData', (newData) => {
                        this.chartData = newData;
                        this.updateCharts();
                    });
                }

                // Observador del dark mode real de RememberMind (sin recargar página)
                window.RMCharts.onThemeChange(() => {
                    this.renderGraficoNivel();
                    this.renderGraficoOrigen();
                    this.renderGraficoEstado();
                }, 'alertas-panel');
            },

            // --- Gráfico 1: Nivel de Severidad (Doughnut semántico) ---
            renderGraficoNivel() {
                const niveles = this.chartData.niveles || {};
                const sem = window.RMCharts.semanticColors();

                const config = window.RMCharts.presets.doughnut(
                    ['Crítico', 'Alto', 'Medio', 'Bajo'],
                    [
                        parseInt(niveles.CRITICO) || 0,
                        parseInt(niveles.ALTO) || 0,
                        parseInt(niveles.MEDIO) || 0,
                        parseInt(niveles.BAJO) || 0
                    ],
                    [sem.danger, sem.warningHigh, sem.info, sem.success]
                );

                window.RMCharts.init('alertas_nivel', 'chartAlertasNivel', config, () => this.renderGraficoNivel());
            },

            // --- Gráfico 2: Origen de Alertas (Barras horizontales con mapping ESTABLE origen -> color) ---
            renderGraficoOrigen() {
                const origenes = this.chartData.origenes || {};
                const originKeys = Object.keys(origenes);

                // Labels amigables y colores fijos por origen clínico (nunca varían con el orden)
                const labels = originKeys.map(k => window.RMCharts.getOriginLabel(k));
                const dataValues = originKeys.map(k => parseInt(origenes[k]) || 0);
                const barColors = originKeys.map(k => window.RMCharts.getOriginColor(k));

                const maxVal = Math.max(...(dataValues.length ? dataValues : [0]), 1);
                const suggestedMax = maxVal + Math.ceil(maxVal * 0.35) + 1;

                const config = window.RMCharts.presets.barHorizontal(
                    labels,
                    dataValues,
                    barColors,
                    {
                        _datasetLabel: 'Alertas por Origen',
                    }
                );

                window.RMCharts.init('alertas_origen', 'chartAlertasOrigen', config, () => this.renderGraficoOrigen());
            },

            // --- Gráfico 3: Ciclo de Vida / Estado (Doughnut semántico) ---
            renderGraficoEstado() {
                const estados = this.chartData.estados || {};
                const sem = window.RMCharts.semanticColors();

                const config = window.RMCharts.presets.doughnut(
                    ['Abiertas', 'En Atención', 'Resueltas'],
                    [
                        parseInt(estados.ABIERTA) || 0,
                        parseInt(estados.EN_ATENCION) || 0,
                        parseInt(estados.CERRADA) || 0
                    ],
                    [sem.info, sem.primary, sem.success]
                );

                window.RMCharts.init('alertas_estado', 'chartAlertasEstado', config, () => this.renderGraficoEstado());
            },

            // --- Actualización reactiva sin duplicar instancias ---
            updateCharts() {
                if (!window.RMCharts.has('alertas_nivel') || !window.RMCharts.has('alertas_origen') || !window.RMCharts.has('alertas_estado')) {
                    this.initAllCharts();
                    return;
                }
                try {
                    // Nivel
                    if (this.chartData.niveles) {
                        const n = this.chartData.niveles;
                        window.RMCharts.update('alertas_nivel', [
                            parseInt(n.CRITICO) || 0, parseInt(n.ALTO) || 0, parseInt(n.MEDIO) || 0, parseInt(n.BAJO) || 0
                        ]);
                    }
                    // Origen
                    if (this.chartData.origenes) {
                        const origenes = this.chartData.origenes;
                        const originKeys = Object.keys(origenes);
                        const labels = originKeys.map(k => window.RMCharts.getOriginLabel(k));
                        const dataValues = originKeys.map(k => parseInt(origenes[k]) || 0);
                        window.RMCharts.update('alertas_origen', dataValues, labels);
                    }
                    // Estado
                    if (this.chartData.estados) {
                        const e = this.chartData.estados;
                        window.RMCharts.update('alertas_estado', [
                            parseInt(e.ABIERTA) || 0, parseInt(e.EN_ATENCION) || 0, parseInt(e.CERRADA) || 0
                        ]);
                    }
                } catch (err) {
                    console.warn('[RM Charts] Error en actualización, reinicializando:', err);
                    this.initAllCharts();
                }
            }
        };
    };
</script>

    {{-- SECCIÓN DE GRÁFICOS ANALÍTICOS: ANILLOS Y BARRAS GRUESAS CON SOPORTE MODO OSCURO --}}
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-4" wire:ignore x-data="alertasDashboardComponent(@js($chartData), @js($conteos))" x-init="initAllCharts()">
        {{-- Gráfico 1: Severidad (Donut Grueso con Métrica Central) --}}
        <div class="lg:col-span-4 rm-chart-card rm-chart-glass">
            <div class="rm-chart-header">
                <div>
                    <h3 class="rm-chart-title">Severidad de Alertas</h3>
                    <p class="rm-chart-subtitle">Distribución por nivel de criticidad</p>
                </div>
                <span class="rm-chart-kpi-badge">
                    {{ ($conteos['criticas'] ?? 0) }} Críticas
                </span>
            </div>
            <div class="relative rm-chart-body is-md" style="height: 220px; width: 100%;">
                <canvas id="chartAlertasNivel"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xl font-extrabold text-[var(--rm-text-title)] dark:text-slate-100">
                        {{ $conteos['total'] ?? 0 }}
                    </span>
                    <span class="text-[10px] font-semibold text-[var(--rm-text-muted)] dark:text-slate-400 uppercase tracking-wider">
                        Alertas
                    </span>
                </div>
            </div>
        </div>

        {{-- Gráfico 2: Origen de Alertas (Barras Horizontales Gruesas y Redondeadas) --}}
        <div class="lg:col-span-4 rm-chart-card rm-chart-glass">
            <div class="rm-chart-header">
                <div>
                    <h3 class="rm-chart-title">Alertas por Origen</h3>
                    <p class="rm-chart-subtitle">Eventos según categoría asistencial</p>
                </div>
                <span class="rm-chart-kpi-badge">
                    {{ count($chartData['origenes'] ?? []) }} orígenes
                </span>
            </div>
            <div class="rm-chart-body is-md" style="height: 220px; width: 100%;">
                <canvas id="chartAlertasOrigen"></canvas>
            </div>
        </div>

        {{-- Gráfico 3: Estado de Gestión (Donut Grueso con % de Resueltas) --}}
        <div class="lg:col-span-4 rm-chart-card rm-chart-glass">
            <div class="rm-chart-header">
                <div>
                    <h3 class="rm-chart-title">Estado de Gestión</h3>
                    <p class="rm-chart-subtitle">Evolución y resolución del turno</p>
                </div>
                <span class="rm-chart-kpi-badge">
                    {{ ($conteos['total'] ?? 0) > 0 ? round((($conteos['cerradas'] ?? 0) / $conteos['total']) * 100) : 0 }}% Resueltas
                </span>
            </div>
            <div class="relative rm-chart-body is-md" style="height: 220px; width: 100%;">
                <canvas id="chartAlertasEstado"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-xl font-extrabold text-[var(--rm-success)]">
                        {{ ($conteos['total'] ?? 0) > 0 ? round((($conteos['cerradas'] ?? 0) / $conteos['total']) * 100) : 0 }}%
                    </span>
                    <span class="text-[10px] font-semibold text-[var(--rm-text-muted)] dark:text-slate-400 uppercase tracking-wider">
                        Resueltas
                    </span>
                </div>
            </div>
        </div>
    </section>

        {{-- BARRA DE FILTROS UNIFICADA: ESTRUCTURA DE EMERGENCIAS + VISTA DE INCIDENTES --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
            {{-- Búsqueda textual --}}
            <div class="lg:col-span-4 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por residente, diagnóstico o motivo..."
                    class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if($search !== '')
                    <button type="button"
                        wire:click="limpiarFiltro('search')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Filtro Estado --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="ABIERTA">Abierta / Pendiente (Inicial)</option>
                    <option value="EN_ATENCION">En Atención</option>
                    <option value="CERRADA">Cerrada / Resuelta</option>
                    <option value="">Todos los estados</option>
                </select>
            </div>

            {{-- Filtro Nivel --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroNivel" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los niveles</option>
                    <option value="CRITICO">Crítico</option>
                    <option value="ALTO">Alto</option>
                    <option value="MEDIO">Medio</option>
                    <option value="BAJO">Bajo</option>
                </select>
            </div>

            {{-- Filtro Origen --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroOrigen" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los orígenes</option>
                    <option value="SIGNOS">Signos Vitales</option>
                    <option value="MEDICACION">Medicación</option>
                    <option value="INCIDENTE">Incidente Asistencial</option>
                    <option value="PLAN">Plan de Cuidado</option>
                    <option value="SEGUIMIENTO">Seguimiento Clínico</option>
                    <option value="SOLICITUD_MEDICA">Solicitud Médica</option>
                    <option value="MANUAL">Registro Manual</option>
                    <option value="FICHA">Ficha Clínica</option>
                    <option value="VALORACION">Valoración</option>
                </select>
            </div>

            {{-- Filtro Residente --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroAdulto" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los residentes</option>
                    @foreach($adultos as $ad)
                        <option value="{{ $ad->cod_residente }}">
                            {{ $ad->ap_paterno }} {{ $ad->nombres }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Fila de chips de filtros activos (desplazable y con colorcitos) --}}
        @php
            $hasFiltrosActivos = !empty($search) || ($filtroEstado !== 'ABIERTA') || !empty($filtroNivel) || !empty($filtroOrigen) || !empty($filtroAdulto);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="rm-filter-scroll">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>

                    @if(!empty($search))
                        <span class="rm-filter-chip rm-filter-chip--search">
                            <i class="ph-bold ph-magnifying-glass text-xs"></i>
                            <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="limpiarFiltro('search')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if($filtroEstado !== 'ABIERTA')
                        <span class="rm-filter-chip {{ $filtroEstado === 'EN_ATENCION' ? 'rm-filter-chip--warning' : ($filtroEstado === 'CERRADA' ? 'rm-filter-chip--success' : 'rm-filter-chip--danger') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $filtroEstado === 'EN_ATENCION' ? 'bg-[var(--rm-status-high)]' : ($filtroEstado === 'CERRADA' ? 'bg-[var(--rm-action-primary)]' : 'bg-[var(--rm-danger)]') }}"></span>
                            <span>Estado: {{ $filtroEstado === 'EN_ATENCION' ? 'En Atención' : ($filtroEstado === 'CERRADA' ? 'Cerrada' : 'Todos') }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroEstado')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if(!empty($filtroNivel))
                        <span class="rm-filter-chip {{ in_array($filtroNivel, ['CRITICO', 'ALTO']) ? 'rm-filter-chip--danger' : 'rm-filter-chip--warning' }}">
                            <i class="ph-bold ph-warning-circle text-xs"></i>
                            <span>Nivel: {{ $filtroNivel }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroNivel')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if(!empty($filtroOrigen))
                        <span class="rm-filter-chip rm-filter-chip--clinical">
                            <i class="ph-bold ph-tag text-xs"></i>
                            <span>Origen: {{ $filtroOrigen }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroOrigen')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif

                    @if(!empty($filtroAdulto))
                        @php $adFiltrado = $adultos->firstWhere('cod_residente', $filtroAdulto); @endphp
                        <span class="rm-filter-chip rm-filter-chip--info">
                            <i class="ph-bold ph-user text-xs"></i>
                            <span>Residente: {{ $adFiltrado ? $adFiltrado->ap_paterno : 'Filtrado' }}</span>
                            <button type="button" wire:click="limpiarFiltro('filtroAdulto')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[var(--rm-surface-alt)] text-[var(--rm-text-secondary)] border border-[var(--rm-border-soft)]">
                        {{ $alertas->total() }} coincidentes
                    </span>

                    <button type="button"
                        wire:click="limpiarFiltros"
                        class="rm-filter-bar__clear-btn">
                        <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                        <span>Limpiar filtros</span>
                    </button>
                </div>
            </div>
        @endif
    </x-ui.filter-bar>

    {{-- TABLA ASISTENCIAL MEDIANA + MODO OSCURO + PIE DE PAGINACIÓN INSTITUCIONAL --}}
    <section class="rm-table-wrapper">
        <div class="w-full overflow-x-auto overscroll-x-contain">
            <table class="rm-data-table rm-data-table--actions rm-table w-full min-w-[980px] table-fixed">
                <thead class="rm-table-header">
                    <tr>
                        <th class="w-[23%] text-left font-bold">Residente y Ubicación</th>
                        <th class="w-[14%] text-left font-bold">Clasificación</th>
                        <th class="w-[28%] text-left font-bold">Motivo Clínico</th>
                        <th class="w-[15%] text-left font-bold">Responsable</th>
                        <th class="w-[8%] text-center font-bold">Estado</th>
                        <th class="w-[12%] text-right font-bold">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @forelse($alertas as $alerta)
                        @php
                            $nivelTexto = match($alerta->prioridad) {
                                'CRITICO' => 'Crítico',
                                'ALTO' => 'Alto',
                                'MEDIO' => 'Medio',
                                'BAJO' => 'Bajo',
                                default => $alerta->prioridad
                            };
                            $estadoTexto = match($alerta->estado) {
                                'ABIERTA' => 'Por Atender',
                                'EN_ATENCION' => 'En Atención',
                                'CERRADA' => 'Resuelta',
                                default => $alerta->estado
                            };
                        @endphp
                        <tr class="rm-table-row">
                            {{-- Columna 1: Residente y Ubicación --}}
                            <td class="px-2.5 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[var(--rm-surface-alt)] dark:bg-slate-800 border border-[var(--rm-border)]  text-[var(--rm-text-title)] dark:text-slate-200 font-extrabold text-[11px] shadow-2xs">
                                        {{ substr($alerta->adultoMayor?->nombres ?? 'R', 0, 1) }}{{ substr($alerta->adultoMayor?->ap_paterno ?? 'M', 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-bold text-[var(--rm-text-title)] dark:text-slate-100 truncate text-xs">
                                            {{ $alerta->adultoMayor?->ap_paterno }} {{ $alerta->adultoMayor?->ap_materno }} {{ $alerta->adultoMayor?->nombres }}
                                        </div>
                                        <div class="text-[10.5px] text-[var(--rm-text-muted)] dark:text-slate-400 flex items-center gap-1 mt-0.5 truncate">
                                            <i class="ph ph-map-pin text-[var(--rm-primary)] shrink-0"></i>
                                            <span class="truncate">{{ $alerta->adultoMayor?->ubicacion_texto ?? 'Sin ubicación asignada' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Columna 2: Clasificación y Nivel --}}
                            <td class="px-2 py-2">
                                <div class="flex flex-col gap-1 items-start">
                                    <x-ui.status-badge :estado="$alerta->prioridad" :label="$nivelTexto" />
                                    <x-ui.status-badge :estado="$alerta->modulo" variant="clinical" class="text-[9.5px]" />
                                </div>
                            </td>

                            {{-- Columna 3: Motivo y Diagnóstico Clínico --}}
                            <td class="px-2.5 py-2">
                                <div class="font-bold text-[var(--rm-text-title)] dark:text-slate-100 truncate text-xs">
                                    {{ $alerta->tipo }}
                                </div>
                                <div class="text-[10.5px] text-[var(--rm-text-muted)] dark:text-slate-400 line-clamp-1 mt-0.5">
                                    {{ $alerta->descripcion }}
                                </div>
                                <div class="text-[9.5px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">
                                    {{ $alerta->fecha_alerta?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                </div>
                            </td>

                            {{-- Columna 4: Responsable / Turno --}}
                            <td class="px-2 py-2">
                                <div class="font-medium text-[var(--rm-text-title)] dark:text-slate-200 truncate text-xs">
                                    {{ $alerta->responsable ? $alerta->responsable->nombres . ' ' . $alerta->responsable->ap_paterno : 'Sin asignar' }}
                                </div>
                                <div class="text-[10px] text-[var(--rm-text-muted)] dark:text-slate-400 font-mono mt-0.5 truncate">
                                    Turno: {{ $alerta->turno?->tipo_turno ?? 'N/A' }}
                                </div>
                            </td>

                            {{-- Columna 5: Estado (Badge chiquito con reborde sutil y dot de pulso) --}}
                            <td class="px-1.5 py-2 text-center whitespace-nowrap">
                                <x-ui.status-badge :estado="$alerta->estado" :label="$estadoTexto" />
                            </td>

                            {{-- Columna 6: Acciones (Principales + Menú Secundario) --}}
                            <td class="px-3 py-1.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-end">
                                    {{-- 1. Atender Alerta (Visible principal) --}}
                                    @if($alerta->estado !== 'CERRADA')
                                        <button type="button"
                                            wire:click="atenderAlerta('{{ $alerta->cod_alerta }}')"
                                            class="rm-action-btn rm-action-btn-atender"
                                            title="Iniciar / Registrar Atención Asistencial">
                                            <i class="ph ph-first-aid"></i>
                                        </button>
                                    @endif

                                    {{-- 2. Ver Gráficos Clínicos (Drawer Lateral) --}}
                                    <button type="button"
                                        wire:click="verGraficos('{{ $alerta->cod_residente }}')"
                                        class="rm-action-btn rm-action-btn-drawer"
                                        title="[Panel Lateral] Gráficos y Constantes">
                                        <i class="ph ph-chart-line-up"></i>
                                    </button>

                                    {{-- 3. Ver Ubicación / Cama (Drawer Lateral) --}}
                                    <button type="button"
                                        wire:click="verUbicacion('{{ $alerta->cod_residente }}')"
                                        class="rm-action-btn rm-action-btn-ubicacion"
                                        title="[Panel Lateral] Ubicación y Cama">
                                        <i class="ph ph-bed"></i>
                                    </button>

                                    {{-- 4. Menú Secundario (...) --}}
                                    <div x-data="{ open: false }" class="relative inline-block text-left" @click.outside="open = false">
                                        <button type="button"
                                            @click="open = !open"
                                            class="rm-action-btn rm-action-btn-more"
                                            title="Más opciones de la alerta">
                                            <i class="ph ph-dots-three-vertical"></i>
                                        </button>

                                        <div x-show="open"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="absolute right-0 z-50 mt-1 w-48 origin-top-right rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-surface)]  shadow-xl py-1 text-xs focus:outline-none"
                                             style="display: none;">

                                            {{-- Ver Expediente / Detalle --}}
                                            <button type="button"
                                                wire:click="verDetalle('{{ $alerta->cod_alerta }}'); open = false;"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-slate-700 dark:text-slate-200 hover:bg-[var(--rm-surface-soft)] dark:hover:bg-slate-700/70 text-left font-medium transition cursor-pointer">
                                                <i class="ph ph-file-text text-sm text-slate-500 dark:text-slate-400"></i>
                                                <span>Ver expediente clínico</span>
                                            </button>

                                            {{-- Finalizar / Cerrar Alerta --}}
                                            @if($alerta->estado !== 'CERRADA')
                                                <button type="button"
                                                    wire:click="cerrarAlerta('{{ $alerta->cod_alerta }}'); open = false;"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 text-left font-medium transition cursor-pointer border-t border-slate-100 /60">
                                                    <i class="ph ph-check-circle text-sm text-[var(--rm-success)]"></i>
                                                    <span>Finalizar / Cerrar alerta</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[var(--rm-surface-soft)] dark:bg-slate-800 text-slate-400 text-2xl">
                                        <i class="ph ph-bell-slash"></i>
                                    </span>
                                    <div class="font-bold text-slate-700 dark:text-slate-300 text-sm">
                                        No se encontraron alertas clínicas
                                    </div>
                                    <div class="text-xs text-slate-400 max-w-sm">
                                        No hay alertas registradas que coincidan con los filtros seleccionados en este momento.
                                    </div>
                                    @if($search || $filtroEstado || $filtroNivel || $filtroOrigen || $filtroAdulto)
                                        <button type="button"
                                            wire:click="limpiarFiltros"
                                            class="mt-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-[var(--rm-text-primary)] hover:bg-[var(--rm-text-body)] transition cursor-pointer">
                                            Restablecer filtros
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PIE DE TABLA: RESUMEN, SELECTOR POR PÁGINA Y NAVEGACIÓN INSTITUCIONAL --}}
        <div class="rm-pagination-bar px-3.5 py-2 border-t border-[var(--rm-border-soft)] /60 bg-[var(--rm-surface-soft)]/90 dark:bg-slate-900/90 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs">
            {{-- Lado Izquierdo: Resumen numérico y selector por página --}}
            <div class="flex flex-wrap items-center gap-2.5 text-[var(--rm-text-muted)] dark:text-slate-400">
                <div class="text-[11.5px]">
                    Mostrando
                    <span class="font-bold text-[var(--rm-text-title)] dark:text-slate-200">{{ $alertas->firstItem() ?? 0 }}</span>
                    a
                    <span class="font-bold text-[var(--rm-text-title)] dark:text-slate-200">{{ $alertas->lastItem() ?? 0 }}</span>
                    de
                    <span class="font-bold text-[var(--rm-text-title)] dark:text-slate-200">{{ $alertas->total() }}</span>
                    alertas
                </div>

                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">|</span>

                <div class="flex items-center gap-1.5 text-[11.5px]">
                    <span>Mostrar</span>
                    <select wire:model.live="perPage" class="h-7 py-0 px-2 text-xs font-semibold rounded-md bg-[var(--rm-input-bg)]  border border-[var(--rm-border)]  text-slate-700 dark:text-slate-200 cursor-pointer outline-none">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>por pág.</span>
                </div>
            </div>

            {{-- Lado Derecho: Controles de Paginación --}}
            @if($alertas->hasPages())
                <nav role="navigation" aria-label="Paginación" class="flex items-center gap-1">
                    {{-- Botón Anterior --}}
                    @if ($alertas->onFirstPage())
                        <span class="h-7 px-2 rounded-md border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 bg-[var(--rm-surface-soft)]/40 dark:bg-slate-900/30 cursor-not-allowed flex items-center gap-1 font-medium text-[11px]">
                            <i class="ph ph-caret-left text-xs"></i>
                            <span class="hidden md:inline">Anterior</span>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="h-7 px-2 rounded-md border border-[var(--rm-border)]  text-slate-700 dark:text-slate-200 bg-[var(--rm-surface)]  hover:bg-[var(--rm-surface-alt)] dark:hover:bg-slate-700 transition flex items-center gap-1 font-medium text-[11px] cursor-pointer">
                            <i class="ph ph-caret-left text-xs"></i>
                            <span class="hidden md:inline">Anterior</span>
                        </button>
                    @endif

                    {{-- Enlaces Numéricos --}}
                    <div class="flex items-center gap-1">
                        @foreach ($alertas->getUrlRange(max(1, $alertas->currentPage() - 2), min($alertas->lastPage(), $alertas->currentPage() + 2)) as $page => $url)
                            @if ($page == $alertas->currentPage())
                                <span class="min-w-[28px] h-7 px-1.5 flex items-center justify-center rounded-md bg-[var(--rm-primary)] text-white font-bold text-xs shadow-2xs">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }})" class="min-w-[28px] h-7 px-1.5 flex items-center justify-center rounded-md border border-[var(--rm-border)]  bg-[var(--rm-surface)]  text-slate-700 dark:text-slate-200 hover:bg-[var(--rm-surface-alt)] dark:hover:bg-slate-700 font-medium text-xs transition cursor-pointer">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    {{-- Botón Siguiente --}}
                    @if ($alertas->hasMorePages())
                        <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="h-7 px-2 rounded-md border border-[var(--rm-border)]  text-slate-700 dark:text-slate-200 bg-[var(--rm-surface)]  hover:bg-[var(--rm-surface-alt)] dark:hover:bg-slate-700 transition flex items-center gap-1 font-medium text-[11px] cursor-pointer">
                            <span class="hidden md:inline">Siguiente</span>
                            <i class="ph ph-caret-right text-xs"></i>
                        </button>
                    @else
                        <span class="h-7 px-2 rounded-md border border-slate-200/80 dark:border-slate-800 text-slate-400 dark:text-slate-600 bg-[var(--rm-surface-soft)]/40 dark:bg-slate-900/30 cursor-not-allowed flex items-center gap-1 font-medium text-[11px]">
                            <span class="hidden md:inline">Siguiente</span>
                            <i class="ph ph-caret-right text-xs"></i>
                        </span>
                    @endif
                </nav>
            @endif
        </div>
    </section>

    {{-- Modales de Gestión Clínica Canónicos --}}
    @include('livewire.alertas.modales.crear')
    @include('livewire.alertas.modales.drawer-graficos')
    @include('livewire.alertas.modales.drawer-ubicacion')
    @include('livewire.alertas.modales.atender')
    @include('livewire.alertas.modales.cerrar')
    @include('livewire.alertas.modales.detalle')
    @include('livewire.alertas.modales.resultado-cierre')
</div>
