    {{-- 2. PRIMERA FILA — KPIS + GRÁFICOS (EVENTOS POR MES + EVENTOS POR TIPO)    --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        {{-- PARTE IZQUIERDA: 4 KPIS COMPACTOS (5 cols en lg) --}}
        <div class="lg:col-span-5 grid grid-cols-2 gap-3">
            {{-- KPI 1: Eventos activos --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-[var(--rm-danger-strong)] bg-[var(--rm-danger-soft)] shadow-2xs dark:bg-[var(--rm-danger-soft)] dark:border-[var(--rm-danger-strong)] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] uppercase tracking-wider">Eventos activos</span>
                    <i class="ph-bold ph-warning-octagon text-[var(--rm-danger-strong)] text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-bold text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] font-mono tracking-tight">
                        {{ $metricas['activos'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] mt-0.5">
                        Requieren seguimiento
                    </p>
                </div>
            </div>

            {{-- KPI 2: En seguimiento --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-[var(--rm-warning-strong)] bg-[var(--rm-warning-soft)] shadow-2xs dark:bg-[var(--rm-warning-soft)] dark:border-[var(--rm-warning-strong)] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] uppercase tracking-wider">En seguimiento</span>
                    <i class="ph-bold ph-clock-countdown text-[var(--rm-warning-strong)] text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] font-mono tracking-tight">
                        {{ $metricas['en_seguimiento'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] mt-0.5">
                        En evaluación
                    </p>
                </div>
            </div>

            {{-- KPI 3: Resueltos --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-[var(--rm-success-strong)] bg-[var(--rm-success-soft)] shadow-2xs dark:bg-[var(--rm-success-soft)] dark:border-[var(--rm-success-strong)] flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)] uppercase tracking-wider">Resueltos</span>
                    <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)] text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-bold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)] font-mono tracking-tight">
                        {{ $metricas['resueltos'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)] mt-0.5">
                        Sin pendientes
                    </p>
                </div>
            </div>

            {{-- KPI 4: Críticos --}}
            <div class="p-3.5 sm:p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">Críticos</span>
                    <i class="ph-bold ph-shield-warning {{ $metricas['criticos'] > 0 ? 'text-[var(--rm-danger-strong)]' : 'text-[var(--rm-text-muted)]' }} text-lg"></i>
                </div>
                <div class="mt-2.5">
                    <span class="text-2xl sm:text-3xl font-bold {{ $metricas['criticos'] > 0 ? 'text-[var(--rm-danger-strong)]' : 'text-[var(--rm-text-title)]' }} font-mono tracking-tight">
                        {{ $metricas['criticos'] }}
                    </span>
                    <p class="text-[10.5px] font-semibold text-[var(--rm-text-muted)] mt-0.5">
                        Últimos 30 días
                    </p>
                </div>
            </div>
        </div>

        {{-- GRÁFICO 1: EVENTOS POR MES (BAR CHART - 4 cols en lg) --}}
        <div class="lg:col-span-4 rm-chart-card rm-chart-glass flex flex-col justify-between">
            <div class="rm-chart-header">
                <div>
                    <h3 class="rm-chart-title">
                        <i class="ph-bold ph-chart-bar text-[var(--rm-action-primary)] text-sm"></i>
                        <span>Eventos por mes</span>
                    </h3>
                    <p class="rm-chart-subtitle">Últimos 6 meses</p>
                </div>
                <span class="rm-chart-kpi-badge">
                    Total: {{ array_sum($porMes['data']) }}
                </span>
            </div>
            <div class="rm-chart-body h-32 w-full relative wire:ignore" style="height: 8rem; min-height: 8rem; max-height: 8rem;">
                <canvas id="chartEventosPorMesCanvas" role="img" aria-label="Eventos clínicos registrados por mes"></canvas>
            </div>
        </div>

        {{-- GRÁFICO 2: EVENTOS POR TIPO (DONUT CHART - 3 cols en lg) --}}
        <div class="lg:col-span-3 rm-chart-card rm-chart-glass flex flex-col justify-between">
            <div class="rm-chart-header">
                <div>
                    <h3 class="rm-chart-title">
                        <i class="ph-bold ph-chart-pie-slice text-[var(--rm-action-primary)] text-sm"></i>
                        <span>Eventos por tipo</span>
                    </h3>
                    <p class="rm-chart-subtitle">Distribución clínica</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                {{-- Donut canvas con centro numérico --}}
                <div class="relative flex items-center justify-center shrink-0 w-28 h-28">
                    <div class="absolute inset-0" wire:ignore>
                        <canvas id="chartEventosPorTipoCanvas" role="img" aria-label="Distribución de eventos clínicos por tipo"></canvas>
                    </div>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                        <span class="text-base font-bold text-[var(--rm-text-title)] leading-none font-mono">{{ $porTipo['total'] }}</span>
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] leading-tight mt-0.5">eventos</span>
                    </div>
                </div>

                {{-- Leyenda porcentual a la derecha --}}
                <div class="space-y-1 text-[10px] w-full min-w-0 font-medium text-[var(--rm-text-body)]">
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-[var(--rm-action-primary)] shrink-0"></span>
                            <span>Caídas</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Caídas'] ?? 0 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-[var(--rm-warning)] shrink-0"></span>
                            <span>Lesiones</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Lesiones'] ?? 0 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-[var(--rm-info)] shrink-0"></span>
                            <span>Incidentes</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Incidentes'] ?? 0 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-[var(--rm-danger)] shrink-0"></span>
                            <span>Complicac.</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Complicaciones'] ?? 0 }}%</strong>
                    </div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="flex items-center gap-1.5 truncate">
                            <span class="h-2 w-2 rounded-full bg-[var(--rm-surface-soft)] shrink-0"></span>
                            <span>Otros</span>
                        </span>
                        <strong class="font-mono text-[var(--rm-text-title)]">{{ $porTipo['percentages']['Otros'] ?? 0 }}%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
