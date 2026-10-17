    {{-- 2. CABECERA OPERATIVA EXACTA                                              --}}
    {{-- ========================================================================= --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-2xs space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)] shadow-2xs">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-bold tracking-tight text-[var(--rm-text-title)] uppercase">
                            MEDICACIÓN
                        </h2>
                        {{-- Badges de contexto institucional --}}
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-info-action)] animate-pulse"></span>
                            Medicación Activa Prescrita
                        </span>
                        <span class="text-[11px] font-bold text-[var(--rm-info-strong)] bg-[var(--rm-info-soft)] px-2 py-0.5 rounded-md border border-[var(--rm-info-strong)]">
                            Plan de medicación
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-[var(--rm-text-muted)] mt-0.5">
                        Administración segura, a tiempo, para su bienestar
                    </p>
                </div>
            </div>

            {{-- Bloque derecho con fecha y turno exactos --}}
            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                @if(app(\App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService::class)
                    ->tieneRol(Auth::user(), ['MEDICO GENERAL/GERIATRA']) && Auth::user()?->can('prescripciones.crear'))
                @if(!$consultaEnfermeria)
<button type="button"
                        @click="$dispatch('abrirModalMedicacion', { cod_residente: '{{ $adultoMayor->cod_residente }}' })"
                        class="rm-btn-primary h-9 px-3.5 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition cursor-pointer">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    <span>Nueva Prescripción</span>
                </button>
@endif
                @endif
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)]">
                    <i class="ph-bold ph-calendar text-[var(--rm-info-strong)]"></i>
                    <span>Hoy, {{ today()->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-success-strong)] bg-[var(--rm-success-soft)] dark:bg-[var(--rm-success-soft)] text-xs font-bold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)] shadow-2xs">
                    <i class="ph-bold ph-desktop text-[var(--rm-success-strong)] animate-pulse"></i>
                    <span>Hora actual PC: <strong x-text="relojPC.hora12" class="font-mono font-bold text-[var(--rm-success-strong)] dark:text-[var(--rm-success-strong)]"></strong></span>
                </div>

                <button type="button"
                        @click="modalIndicaciones = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-title)] transition cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-info text-[var(--rm-info-strong)]"></i>
                    <span>Ver indicaciones generales</span>
                </button>
            </div>
        </div>

        {{-- Recordatorio funcional de enfermería --}}
        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs text-[var(--rm-text-body)]">
            <i class="ph-bold ph-shield-check text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
            <div class="leading-relaxed">
                <span class="font-bold text-[var(--rm-text-title)]">Enfermería administra y registra medicación prescrita.</span>
                <span class="text-[var(--rm-text-muted)] ml-1">La prescripción y modificaciones corresponden al personal médico. No modificar dosis ni pautas médicas.</span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. KPIS NUEVOS EXACTOS (REEMPLAZO COMPLETO DE LOS ANTERIORES)              --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
        {{-- KPI 1: POR ADMINISTRAR AHORA (ROJO si existen dosis vencidas/actuales) --}}
        <div class="p-3.5 rounded-2xl border bg-[var(--rm-danger-soft)] border-[var(--rm-danger-strong)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-danger-strong)] uppercase tracking-wider">POR ADMINISTRAR AHORA</span>
                <i class="ph-bold ph-warning-circle text-[var(--rm-danger-strong)] text-base animate-pulse"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-[var(--rm-danger-strong)] font-mono">{{ $conteoPrioritarias }}</span>
                <span class="text-[10px] font-bold text-[var(--rm-danger-strong)]">dosis prioritarias</span>
            </div>
            <span class="text-[10px] text-[var(--rm-danger-strong)] font-semibold block">Atrasadas o pendientes ahora</span>
        </div>

        {{-- KPI 2: PRÓXIMAS DOSIS (En las próximas 2 horas) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-warning-strong)] bg-[var(--rm-warning-soft)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-warning-strong)] uppercase tracking-wider">PRÓXIMAS DOSIS</span>
                <i class="ph-bold ph-clock-countdown text-[var(--rm-warning-strong)] text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-[var(--rm-warning-strong)] font-mono">{{ $conteoProximas }}</span>
                <span class="text-[10px] font-bold text-[var(--rm-warning-strong)]">próximas</span>
            </div>
            <span class="text-[10px] text-[var(--rm-warning-strong)] font-medium block">Ventana de administración</span>
        </div>

        {{-- KPI 3: ADMINISTRADAS HOY (4 de 6 programadas) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">ADMINISTRADAS HOY</span>
                <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)] text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-[var(--rm-success-strong)] font-mono">{{ $conteoAdministradas }} de {{ $totalProgramadas }}</span>
                <span class="text-[10px] text-[var(--rm-text-muted)]">programadas</span>
            </div>
            <span class="text-[10px] text-[var(--rm-success-strong)] font-semibold block">Registros confirmados</span>
        </div>

        {{-- KPI 4: OMITIDAS / ATRASADAS (1 Requiere atención) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">OMITIDAS / ATRASADAS</span>
                <i class="ph-bold ph-bell-ringing text-[var(--rm-danger-strong)] text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-[var(--rm-danger-strong)] font-mono">{{ $conteoOmitidas + $listaOrdenada->where('estadoHoy', 'Atrasada')->count() }}</span>
                <span class="text-[10px] font-bold text-[var(--rm-danger-strong)]">requiere atención</span>
            </div>
            <span class="text-[10px] text-[var(--rm-text-muted)] font-medium block">Según registros clínicos</span>
        </div>

        {{-- KPI 5: ADHERENCIA HOY (89% 8 de 9 administradas) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">ADHERENCIA HOY</span>
                <i class="ph-bold ph-chart-donut text-[var(--rm-info-strong)] text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-[var(--rm-action-primary)] font-mono">{{ $adherencia }}%</span>
                <span class="text-[10px] text-[var(--rm-text-muted)]">{{ $conteoAdministradas }} de {{ $totalProgramadas }} administradas</span>
            </div>
            <div class="w-full bg-[var(--rm-surface-soft)] rounded-full h-1.5 mt-1 overflow-hidden">
                <div class="bg-[var(--rm-action-primary)] h-1.5 rounded-full" style="width: {{ $adherencia }}%"></div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 7. FILTROS PRIORITARIOS (FOCO EN 'POR ADMINISTRAR')                       --}}
    {{-- ========================================================================= --}}
    <x-ui.filter-bar class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="rm-filter-pills flex-1">
            {{-- Por administrar --}}
            <button type="button"
                    @click="filtroPrioritario = 'por_administrar'"
                    :class="filtroPrioritario === 'por_administrar' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-bell-ringing text-xs"></i>
                <span>Por administrar</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'por_administrar' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)]'">{{ $conteoPrioritarias }}</span>
            </button>

            {{-- Próximas --}}
            <button type="button"
                    @click="filtroPrioritario = 'proximas'"
                    :class="filtroPrioritario === 'proximas' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-clock text-xs"></i>
                <span>Próximas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'proximas' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-warning-soft)] text-[var(--rm-status-high)]'">{{ $conteoProximas }}</span>
            </button>

            {{-- Administradas --}}
            <button type="button"
                    @click="filtroPrioritario = 'administradas'"
                    :class="filtroPrioritario === 'administradas' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-check-circle text-xs"></i>
                <span>Administradas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'administradas' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]'">{{ $conteoAdministradas }}</span>
            </button>

            {{-- PRN --}}
            <button type="button"
                    @click="filtroPrioritario = 'prn'"
                    :class="filtroPrioritario === 'prn' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-first-aid text-xs"></i>
                <span>PRN</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'prn' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical-strong)]'">{{ $conteoPrn }}</span>
            </button>

            {{-- Suspendidas --}}
            <button type="button"
                    @click="filtroPrioritario = 'suspendidas'"
                    :class="filtroPrioritario === 'suspendidas' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-prohibit text-xs"></i>
                <span>Suspendidas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'suspendidas' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-surface)] text-[var(--rm-text-secondary)]'">{{ $conteoSuspendidas }}</span>
            </button>
        </div>

        {{-- Selector / indicador de contexto --}}
        <div class="text-xs text-[var(--rm-text-muted)] font-medium flex items-center gap-1.5">
            <i class="ph-bold ph-funnel text-[var(--rm-action-primary)]"></i>
            <span>Orden clínico: URGENCIA ASISTENCIAL</span>
        </div>
    </x-ui.filter-bar>

    {{-- ========================================================================= --}}
