    {{-- COLUMNA IZQUIERDA                                                         --}}
    {{-- ========================================================================= --}}
    <div class="space-y-5">

        {{-- 1. INFORMACIÓN CLÍNICA --}}
        <div class="rm-clinical-card space-y-3.5">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-identification-card text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Información clínica</span>
                    <span class="sr-only">Información Clínica Relevante</span>
                </h3>
                @if(!$consultaEnfermeria)
<button type="button" @click="drawerExpediente = true"
                   class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <i class="ph-bold ph-pencil-simple text-xs"></i>
                    <span>Editar</span>
                </button>
@endif
            </div>

            <div class="space-y-3 text-xs">
                {{-- Diagnósticos activos --}}
                <div>
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-stethoscope text-[var(--rm-action-primary)]"></i>
                        <span>Diagnósticos activos</span>
                    </span>
                    @if($diagnosticosActivos->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($diagnosticosActivos as $diag)
                                <span class="inline-flex items-center rounded-lg border border-[var(--rm-info)]/25 bg-[var(--rm-info-soft)] px-2.5 py-1 text-xs font-semibold text-[var(--rm-info-strong)]">
                                    {{ $diag }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)] italic">Sin diagnósticos activos registrados.</p>
                    @endif
                </div>

                {{-- Alergias --}}
                <div class="border-t border-[var(--rm-border-soft)] pt-2.5">
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-warning-circle text-[var(--rm-danger-strong)]"></i>
                        <span>Alergias</span>
                    </span>
                    @if($alergiasConocidas->isNotEmpty())
                        <div class="flex items-center gap-1.5 rounded-xl border border-[var(--rm-danger)]/25 bg-[var(--rm-danger-soft)] p-2 text-xs font-semibold text-[var(--rm-danger-strong)]">
                            <i class="ph-bold ph-warning text-[var(--rm-danger-strong)] text-sm shrink-0"></i>
                            <span>{{ $alergiasTexto }}</span>
                        </div>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)]">Sin alergias conocidas reportadas.</p>
                    @endif
                </div>

                {{-- Antecedentes relevantes --}}
                <div class="border-t border-[var(--rm-border-soft)] pt-2.5">
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-info-strong)]"></i>
                        <span>Antecedentes relevantes</span>
                    </span>
                    @if($antecedentesRelevantes->isNotEmpty())
                        <ul class="space-y-1.5 rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-alt)] p-2.5 text-xs text-[var(--rm-text-body)]">
                            @foreach($antecedentesRelevantes->take(3) as $antecedente)
                                <li class="flex items-start gap-1.5 leading-relaxed">
                                    <i class="ph-fill ph-dot-outline mt-0.5 text-[var(--rm-action-primary)]"></i>
                                    <span>{{ $antecedente }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)] italic">Sin antecedentes patológicos relevantes registrados.</p>
                    @endif
                </div>

                {{-- Grupo sanguíneo y Seguro de salud --}}
                <div class="grid grid-cols-2 gap-2 border-t border-[var(--rm-border-soft)] pt-2.5">
                    <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Grupo sanguíneo</span>
                        <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block">
                            {{ $grupoSanguineo ?: 'No registrado' }}
                        </span>
                    </div>
                    <div class="p-2 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Seguro de salud</span>
                        <span class="text-xs font-bold text-[var(--rm-text-title)] mt-0.5 block truncate" title="{{ $seguroActivo?->entidad ?: 'No registrado' }}">
                            {{ $seguroActivo?->entidad ?: 'No registrado' }}
                        </span>
                    </div>
                </div>

                {{-- Contacto de emergencia --}}
                <div class="border-t border-[var(--rm-border-soft)] pt-2.5">
                    <span class="text-[11px] font-bold text-[var(--rm-text-title)] flex items-center gap-1.5 mb-1.5">
                        <i class="ph-bold ph-phone-call text-[var(--rm-success-strong)]"></i>
                        <span>Contacto de emergencia</span>
                    </span>
                    @if($contactoEmergencia)
                        <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] flex items-center justify-between">
                            <div class="min-w-0 pr-2">
                                <span class="font-bold text-[var(--rm-text-title)] block truncate">{{ $contactoEmergencia->nombre_completo }}</span>
                                <span class="text-[10px] text-[var(--rm-text-muted)] block">{{ $contactoEmergencia->pivot?->parentesco ?: 'Contacto' }}</span>
                            </div>
                            <span class="flex shrink-0 items-center gap-1 text-xs font-semibold text-[var(--rm-success-strong)]">
                                <i class="ph-bold ph-phone"></i> {{ $contactoEmergencia->celular ?: ($contactoEmergencia->telefono ?: 'No registrado') }}
                            </span>
                        </div>
                    @else
                        <p class="text-[11px] text-[var(--rm-text-muted)] italic">Sin contacto de emergencia registrado.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- 2. ÚLTIMA EVOLUCIÓN --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-note-pencil text-[var(--rm-action-primary)] text-sm"></i>
                    <span>Última evolución</span>
                </h3>
                <button type="button"
                        @click="activeTab = 'seguimiento'; $wire.cambiarTab('seguimiento')"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver más</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                    <span class="flex items-center gap-1"><i class="ph-bold ph-calendar"></i> {{ $ultSeg?->fecha_hora?->format('d/m/Y H:i') ?? 'Sin evolución registrada' }}</span>
                    <span class="font-medium text-[var(--rm-text-title)]">{{ $ultSeg?->personalSaliente?->usuario?->name ?? 'Responsable no registrado' }}</span>
                </div>
                @if($ultSeg)
                    <p class="text-[var(--rm-text-body)] bg-[var(--rm-surface-alt)] p-3 rounded-xl border border-[var(--rm-border-soft)] leading-relaxed">
                        {{ $ultSeg->resumen ?: 'Sin resumen clínico registrado.' }}
                    </p>
                @else
                    <p class="text-[var(--rm-text-muted)] bg-[var(--rm-surface-alt)] p-3 rounded-xl border border-[var(--rm-border-soft)] leading-relaxed italic">
                        No existen pases de turno registrados para este residente.
                    </p>
                @endif
            </div>
        </div>

        {{-- 3. ALERTAS ACTIVAS --}}
        <div class="rm-clinical-card space-y-3">
            <div class="rm-clinical-card__header">
                <h3 class="rm-clinical-card__title">
                    <i class="ph-bold ph-warning-octagon text-[var(--rm-danger-strong)] text-sm"></i>
                    <span>Alertas activas</span>
                </h3>
                <button type="button"
                        @click="activeTab = 'alertas'; $wire.cambiarTab('alertas')"
                        class="rm-btn-secondary h-7 px-2.5 text-[11px] rounded-lg font-semibold inline-flex items-center gap-1 transition">
                    <span>Ver todas</span>
                    <i class="ph-bold ph-arrow-right text-[10px]"></i>
                </button>
            </div>

            <div class="space-y-2 text-xs">
                @if($alertasAct->count() > 0)
                    @foreach($alertasAct->take(2) as $al)
                        <div class="rm-action-item border-[var(--rm-danger)]/25 bg-[var(--rm-danger-soft)]">
                            <div class="min-w-0 pr-1">
                                <span class="block truncate font-bold text-[var(--rm-danger-strong)]">{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($al->tipo_alerta ?? $al->motivo ?? $al->tipo) }}</span>
                                <span class="block text-[10px] text-[var(--rm-danger-strong)]">{{ $al->origen ?: 'Origen no registrado' }}</span>
                            </div>
                            <x-ui.status-badge :estado="$al->nivel ?? $al->prioridad" class="shrink-0" />
                        </div>
                    @endforeach
                @else
                    <div class="flex items-center gap-2 rounded-xl border border-[var(--rm-success)]/25 bg-[var(--rm-success-soft)] p-3 text-xs text-[var(--rm-success-strong)]">
                        <i class="ph-bold ph-shield-check text-[var(--rm-success-strong)] text-base shrink-0"></i>
                        <span>Sin alertas clínicas activas registradas.</span>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
