<div class="rm-pilot-alertas rm-emergency-workspace rm-page-layout font-sans" @unless(app(\App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService::class)->isActive(auth()->user())) wire:poll.30s @endunless>
 {{-- Encabezado Institucional Canónico --}}
 <x-ui.page-header
  title="Alertas clínicas y cuidados"
  subtitle="Revisa primero las prioridades y documenta cada intervención. Actualización de la lista cada 30 segundos mientras la pantalla está activa."
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


    @if($prioritarias->isNotEmpty())
    <section class="rm-alert-priority" aria-labelledby="alert-priority-title">
        <header><i class="ph-bold ph-warning-octagon" aria-hidden="true"></i><div><h2 id="alert-priority-title">Atención prioritaria</h2><p>{{ ($conteos['criticas'] ?? 0) }} críticas · {{ ($conteos['altas'] ?? 0) }} altas activas. Se muestran antes de los filtros.</p></div></header>
        @foreach($prioritarias as $prioritaria)<button type="button" wire:click="verDetalle('{{ $prioritaria->cod_alerta }}')"><strong>{{ $prioritaria->prioridad }} · {{ $prioritaria->residente?->nombre_completo }}</strong><span>{{ $prioritaria->tipo }} · {{ $prioritaria->fecha_hora?->format('d/m/Y H:i') }}</span><span>Revisar alerta →</span></button>@endforeach
    </section>
    @endif
    <section class="rm-alert-kpis" aria-label="Resumen de alertas">
        @foreach([['Total de alertas','total','',''],['Críticas y altas','priority','filtroNivel','CRITICO'],['Por atender','abiertas','filtroEstado','ABIERTA'],['En atención','en_atencion','filtroEstado','EN_ATENCION'],['Resueltas','cerradas','filtroEstado','CERRADA']] as [$label,$key,$campo,$valor])
        <button type="button" class="rm-alert-kpi {{ $key === 'priority' ? 'is-priority' : '' }}" wire:click="{{ $key === 'total' ? 'limpiarFiltros' : ($key === 'priority' ? 'verPrioritarias' : "setFiltroRapido('$campo', '$valor')") }}">
            <strong>{{ $key === 'priority' ? ($conteos['criticas'] + $conteos['altas']) : ($conteos[$key] ?? 0) }}</strong>
            <span class="rm-alert-kpi__copy"><span>{{ $label }}</span><small>{{ $key === 'priority' ? 'Ver prioridades' : 'Ver registros' }}</small></span>
            <i class="ph-bold {{ match($key) { 'priority' => 'ph-warning-octagon', 'abiertas' => 'ph-clock-countdown', 'en_atencion' => 'ph-first-aid', 'cerradas' => 'ph-check-circle', default => 'ph-bell' } }}" aria-hidden="true"></i>
        </button>
        @endforeach
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
<details class="rm-alert-analytics" @toggle="if ($event.target.open) window.dispatchEvent(new Event('resize'))"><summary>Ver análisis y distribución de alertas</summary>    <section class="grid grid-cols-1 lg:grid-cols-12 gap-4" x-data="alertasDashboardComponent(@js($chartData), @js($conteos))" x-init="initAllCharts()">
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
                <canvas wire:ignore id="chartAlertasNivel"></canvas>
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
                <canvas wire:ignore id="chartAlertasOrigen"></canvas>
            </div>
        </div>

        {{-- Gráfico 3: Estado de Gestión (Donut Grueso con % de Resueltas) --}}
        <div class="lg:col-span-4 rm-chart-card rm-chart-glass">
            <div class="rm-chart-header">
                <div>
                    <h3 class="rm-chart-title">Estado de Gestión</h3>
                    <p class="rm-chart-subtitle">Estados de los registros consultados</p>
                </div>
                <span class="rm-chart-kpi-badge">
                    {{ ($conteos['total'] ?? 0) > 0 ? round((($conteos['cerradas'] ?? 0) / $conteos['total']) * 100) : 0 }}% Resueltas
                </span>
            </div>
            <div class="relative rm-chart-body is-md" style="height: 220px; width: 100%;">
                <canvas wire:ignore id="chartAlertasEstado"></canvas>
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
    </section></details>

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
                    aria-label="Buscar alertas por residente, tipo o motivo" placeholder="Buscar por residente, tipo o motivo..."
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
                <select wire:model.live="filtroEstado" aria-label="Estado de la alerta" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="ABIERTA">Abierta / Pendiente (Inicial)</option>
                    <option value="EN_ATENCION">En Atención</option>
                    <option value="CERRADA">Cerrada / Resuelta</option>
                    <option value="">Todos los estados</option>
                </select>
            </div>

            {{-- Filtro Nivel --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroNivel" aria-label="Prioridad de la alerta" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los niveles</option>
                    <option value="CRITICO">Crítico</option>
                    <option value="ALTO">Alto</option>
                    <option value="MEDIO">Medio</option>
                    <option value="BAJO">Bajo</option>
                </select>
            </div>

            {{-- Filtro Origen --}}
            <div class="lg:col-span-2">
                <select wire:model.live="filtroOrigen" aria-label="Origen de la alerta" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
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
                <select wire:model.live="filtroAdulto" aria-label="Residente" class="w-full rounded-xl border border-[var(--rm-border)]  bg-[var(--rm-input-bg)]  py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los residentes</option>
                    @foreach($adultos as $ad)
                        <option value="{{ $ad->cod_residente }}">
                            {{ $ad->apellido_paterno }} {{ $ad->nombres }}
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
                            <span>Residente: {{ $adFiltrado ? $adFiltrado->apellido_paterno : 'Filtrado' }}</span>
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


    <section class="rm-alert-board" aria-label="Alertas ordenadas por prioridad">
        <header class="rm-alert-board__heading"><h2>Cola de atención</h2><span>{{ $alertas->total() }} resultados · Prioridad primero, después antigüedad</span></header>
        <div class="rm-alert-board__list">
        @forelse($alertas as $alerta)
            @php
                $prioridad = strtoupper($alerta->prioridad ?? '');
                $activa = in_array($alerta->estado, ['ABIERTA','EN_ATENCION','RECONOCIDA','ASIGNADA','PENDIENTE']);
                $critica = $activa && in_array($prioridad, ['CRITICO','CRITICA']);
                $alta = $activa && in_array($prioridad, ['ALTO','ALTA']);
            @endphp
            <article class="rm-alert-case {{ $critica ? 'is-critical' : ($alta ? 'is-high' : '') }}" wire:key="alert-case-{{ $alerta->cod_alerta }}" aria-labelledby="alert-case-{{ $alerta->cod_alerta }}-title">
                <header class="rm-alert-case__header">
                    <div><span class="rm-alert-case__priority"><i class="ph-bold {{ $critica || $alta ? 'ph-warning-octagon' : 'ph-bell' }}" aria-hidden="true"></i>{{ $critica ? 'CRÍTICA · ATENCIÓN PRIORITARIA' : 'PRIORIDAD '.($prioridad ?: 'NO REGISTRADA') }}</span><h3 id="alert-case-{{ $alerta->cod_alerta }}-title">{{ $alerta->tipo }}</h3></div>
                    <x-ui.status-badge :estado="$alerta->estado" />
                </header>
                <div class="rm-alert-case__resident">
                    <span class="rm-alert-case__avatar" aria-hidden="true"><i class="ph-bold ph-user"></i></span>
                    <div><strong>{{ $alerta->residente?->nombre_completo ?? 'Residente no disponible' }}</strong><span><i class="ph-bold ph-bed" aria-hidden="true"></i>{{ $alerta->residente?->ubicacion_formateada ?? 'Sin ubicación asignada' }}</span></div>
                </div>
                <div class="rm-alert-case__body">
                    <div class="rm-alert-case__reason"><h4>Motivo de la alerta</h4><p class="rm-alert-case__description">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($alerta->descripcion) }}</p></div>
                    <dl class="rm-alert-case__facts">
                        <div><dt><i class="ph-bold ph-clock" aria-hidden="true"></i>Registrada</dt><dd><time datetime="{{ $alerta->fecha_hora?->toIso8601String() }}">{{ $alerta->fecha_hora?->timezone(config('app.timezone'))->format('d/m/Y · H:i') ?? 'Sin fecha' }}</time></dd></div>
                        <div><dt><i class="ph-bold ph-tag" aria-hidden="true"></i>Origen</dt><dd>{{ str_replace('_', ' ', $alerta->modulo) }}</dd></div>
                        <div><dt><i class="ph-bold ph-user-circle" aria-hidden="true"></i>Responsable</dt><dd>{{ $alerta->responsable?->nombre_completo ?? ($alerta->responsable ? trim($alerta->responsable->nombres.' '.$alerta->responsable->apellido_paterno) : 'Sin asignar') }}</dd></div>
                        @if($alerta->fecha_hora_limite)<div><dt><i class="ph-bold ph-hourglass" aria-hidden="true"></i>Límite registrado</dt><dd>{{ $alerta->fecha_hora_limite->timezone(config('app.timezone'))->format('d/m/Y · H:i') }}</dd></div>@endif
                    </dl>
                </div>
                <footer class="rm-alert-case__actions">
                    <div class="rm-alert-case__primary-actions">
                        @if($activa && auth()->user()?->canAny(['alertas.seguimiento','alertas.gestionar']))<x-ui.action-button :variant="$critica ? 'danger' : 'primary'" icono="ph-first-aid" wire:click="atenderAlerta('{{ $alerta->cod_alerta }}')" :loading="'atenderAlerta(\''.$alerta->cod_alerta.'\')'">Registrar atención</x-ui.action-button>@endif
                        <x-ui.action-button variant="secondary" icono="ph-eye" wire:click="verDetalle('{{ $alerta->cod_alerta }}')" :loading="'verDetalle(\''.$alerta->cod_alerta.'\')'">Detalle e historial</x-ui.action-button>
                    </div>
                    <div class="rm-alert-case__support-actions" aria-label="Contexto y cierre de la alerta">
                        <x-ui.action-button variant="ghost" icono="ph-chart-line" wire:click="verGraficos('{{ $alerta->cod_residente }}')">Signos y evolución</x-ui.action-button>
                        <x-ui.action-button variant="ghost" icono="ph-map-pin" wire:click="verUbicacion('{{ $alerta->cod_residente }}')">Ubicación</x-ui.action-button>
                        @if($activa && auth()->user()?->canAny(['alertas.cerrar','alertas.gestionar']))<x-ui.action-button variant="ghost" icono="ph-check-circle" wire:click="cerrarAlerta('{{ $alerta->cod_alerta }}')">Cerrar con resultado</x-ui.action-button>@endif
                    </div>
                </footer>
            </article>
        @empty
            <x-ui.empty-state icono="ph-shield-check" titulo="Sin alertas en esta consulta" texto="Revisa los filtros. Esto no sustituye la valoración del residente ni confirma ausencia de riesgo." />
        @endforelse
        </div>
        <div class="rm-alert-board__pagination">{{ $alertas->links() }}</div>
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
