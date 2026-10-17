    {{-- COLUMNA CENTRAL                                                           --}}
    {{-- ========================================================================= --}}
    <div class="space-y-5">

        {{-- 1. ESTADO ACTUAL --}}
        <div class="rm-clinical-card space-y-3.5">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-activity text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Estado actual</span>
                    <span class="sr-only">Estado Clínico Actual</span>
                </h3>
                <x-ui.status-badge :estado="$ultSeg ? 'REGISTRADO' : 'SIN_REGISTRO'" :label="$ultSeg ? 'Con evolución' : 'Sin registro'" />
            </div>

            {{-- 8 Indicadores Clínicos --}}
            <div class="grid grid-cols-2 gap-2 text-xs">
                {{-- Estado general --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Estado general</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                        {{ $ultSeg?->estado_general ?: 'No registrado' }}
                    </span>
                </div>

                {{-- Nivel de cuidado --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Nivel de cuidado</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $asignacionActiva?->nivel_supervision ?: 'No registrado' }}
                    </span>
                </div>

                {{-- Dependencia (Barthel) --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Dependencia</span>
                    <span class="mt-0.5 block text-xs font-bold text-[var(--rm-info-strong)]">
                        {{ $ultFunc?->indice_barthel !== null ? $ultFunc->indice_barthel.' / 100' : 'No registrado' }}
                    </span>
                </div>

                {{-- Riesgo de caídas --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Riesgo de caídas</span>
                    <span class="mt-0.5 block text-xs font-bold text-[var(--rm-warning-strong)]">
                        {{ $movilidadHoy?->riesgo_caida ?: 'No registrado' }}
                    </span>
                </div>

                {{-- Riesgo de UPP --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Supervisión funcional</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                        {{ $ultFunc ? ($ultFunc->necesita_supervision ? 'Requerida' : 'No requerida') : 'No registrado' }}
                    </span>
                </div>

                {{-- Estado cognitivo --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Estado cognitivo</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                        {{ $ultControlCognitivo ? ($ultControlCognitivo->cambio_cognitivo ? 'Cambio observado' : 'Sin cambio observado') : 'No registrado' }}
                    </span>
                </div>

                {{-- Estado nutricional --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Estado nutricional</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $ingestaHoy?->apetito ?: ($ingestaHoy?->tolerancia ?: 'No registrado') }}
                    </span>
                </div>

                {{-- Dolor actual --}}
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Dolor actual</span>
                    <span class="mt-0.5 block text-xs font-bold text-[var(--rm-success-strong)]">
                        {{ $ultDolor?->intensidad !== null ? $ultDolor->intensidad.'/10' : 'No registrado' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- 2. PLAN DE CUIDADOS --}}
        <div class="rm-clinical-card space-y-3.5">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-hand-heart text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Plan de cuidados</span>
                </h3>
                <button type="button"
                        @click="activeTab = 'cuidados'; $wire.cambiarTab('cuidados')"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver plan</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="flex items-center gap-4 text-xs">
                {{-- Indicador circular de progreso tipo 3/5 --}}
                <div class="relative flex items-center justify-center shrink-0 w-16 h-16">
                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                        <path class="text-[var(--rm-border-soft)]" stroke-width="3.5" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="text-[var(--rm-action-primary)] transition-all duration-500" stroke-dasharray="{{ $totalTareasHoy > 0 ? round(($tareasCompletadasHoy / $totalTareasHoy) * 100) : 0 }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                              d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center">
                        <span class="text-xs font-bold text-[var(--rm-text-title)]">{{ $tareasCompletadasHoy }}/{{ $totalTareasHoy }}</span>
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase">Hoy</span>
                    </div>
                </div>

                {{-- Lista de objetivos/cuidados --}}
                <div class="min-w-0 space-y-1.5 flex-1">
                    <p class="font-bold text-[var(--rm-text-title)] truncate">
                        {{ $plan?->nombre ?: 'Sin plan de cuidados activo' }}
                    </p>
                    <p class="text-[11px] text-[var(--rm-text-body)] line-clamp-2">
                        {{ $plan?->objetivo_general ?: 'No se registró un objetivo de cuidado activo.' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- 3. RESUMEN DEL DÍA --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-sun text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Resumen del día</span>
                </h3>
                <span class="text-[10px] font-semibold text-[var(--rm-text-muted)]">Hoy</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Alimentación</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $ingestaHoy ? (($ingestaHoy->porcentaje_consumido !== null ? $ingestaHoy->porcentaje_consumido.'% · ' : '').($ingestaHoy->tolerancia ?: ($ingestaHoy->apetito ?: 'Registrada'))) : 'No registrada' }}
                    </span>
                </div>
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Hidratación</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $hidratacionHoy->isNotEmpty() ? number_format($hidratacionHoyMl, 0, ',', '.').' mL' : 'No registrada' }}
                    </span>
                </div>
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Movilidad</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $movilidadHoy?->marcha ?: ($movilidadHoy?->tipo_apoyo ?: 'No registrada') }}
                    </span>
                </div>
                <div class="rm-data-tile">
                    <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Autonomía en higiene</span>
                    <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate">
                        {{ $ultFunc?->higiene_autonoma ?: 'No registrada' }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
