    {{-- 2. PRIMERA FILA — 4 KPIS MÉTRICOS + 1 CARD CONTEXTUAL (ÚLTIMO ESTUDIO)    --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        {{-- KPI 1: Total Estudios --}}
        <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">
                    Estudios registrados
                </span>
                <i class="ph-bold ph-folder-simple text-[var(--rm-action-primary)] text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-[var(--rm-text-title)] tracking-tight font-mono">
                {{ $metricas['total'] }}
            </div>
            <p class="text-[11px] text-[var(--rm-text-muted)]">Historial acumulado</p>
        </div>

        {{-- KPI 2: Normales --}}
        <div class="rounded-2xl border border-[var(--rm-success-strong)] dark:border-[var(--rm-success-strong)] bg-[var(--rm-success-soft)] dark:bg-[var(--rm-success-soft)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)] uppercase tracking-wider">
                    Resultados normales
                </span>
                <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)] text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)] tracking-tight font-mono">
                {{ $metricas['normales'] }} <span class="text-xs font-normal text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)]">({{ $metricas['normales_pct'] }}%)</span>
            </div>
            <p class="text-[11px] text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)]">Valores dentro de rango</p>
        </div>

        {{-- KPI 3: Fuera de rango --}}
        <div class="rounded-2xl border border-[var(--rm-danger-strong)] dark:border-[var(--rm-danger-strong)] bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] uppercase tracking-wider">
                    Fuera de rango
                </span>
                <i class="ph-bold ph-warning-octagon text-[var(--rm-danger-strong)] text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] tracking-tight font-mono">
                {{ $metricas['fuera_rango'] }}
            </div>
            <p class="text-[11px] text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)]">Alto o bajo respecto al corte</p>
        </div>

        {{-- KPI 4: Requieren seguimiento --}}
        <div class="rounded-2xl border border-[var(--rm-warning-strong)] dark:border-[var(--rm-warning-strong)] bg-[var(--rm-warning-soft)] dark:bg-[var(--rm-warning-soft)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] uppercase tracking-wider">
                    Requieren seguimiento
                </span>
                <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-warning-strong)] text-sm"></i>
            </div>
            <div class="text-xl sm:text-2xl font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)] tracking-tight font-mono">
                {{ $metricas['seguimiento'] }}
            </div>
            <p class="text-[11px] text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-strong)]">Atención médica prioritaria</p>
        </div>

        {{-- KPI 5: Último estudio --}}
        <div class="col-span-2 sm:col-span-1 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3.5 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">
                    Último estudio
                </span>
                <i class="ph-bold ph-calendar text-[var(--rm-action-primary)] text-sm"></i>
            </div>
            <div class="text-xs font-bold text-[var(--rm-text-title)] truncate mt-0.5" title="{{ $metricas['ultimo_estudio']['tipo_texto'] ?? 'Estudio clínico' }}">
                {{ $metricas['ultimo_estudio']['tipo_texto'] ?? 'Estudio clínico' }}
            </div>
            <div class="flex items-center justify-between text-[10.5px] text-[var(--rm-text-muted)] font-mono">
                <span>{{ $metricas['ultimo_estudio']['fecha'] ?? '—' }}</span>
                <span class="px-1.5 py-0.2 rounded-md font-bold text-[9.5px] {{ $metricas['ultimo_estudio']['estado_color'] ?? '' }}">
                    {{ $metricas['ultimo_estudio']['estado'] ?? '' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
