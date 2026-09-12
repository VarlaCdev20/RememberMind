@php $detalle = $detalle ?? $alertaActiva; @endphp
@if($modalDetalle && $detalle)
@php
    $badgeEstadoClass = match($detalle->estado) {
        'ABIERTA' => 'rm-badge-warning',
        'EN_ATENCION' => 'rm-badge-info',
        'CERRADA' => 'rm-badge-success',
        default => 'rm-badge-neutral'
    };
    $estadoTexto = match($detalle->estado) {
        'ABIERTA' => 'Por Atender',
        'EN_ATENCION' => 'En Atención',
        'CERRADA' => 'Resuelta / Archivada',
        default => $detalle->estado
    };

    $badgeNivelClass = match($detalle->nivel ?? $detalle->tipoAlerta?->severidad) {
        'CRITICO' => 'rm-badge-danger',
        'ALTO' => 'rm-badge-warning',
        'MEDIO' => 'rm-badge-info',
        'BAJO' => 'rm-badge-neutral',
        default => 'rm-badge-neutral'
    };
@endphp
<div class="rm-modal-backdrop"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-detalle-title"
     x-data
     x-on:keydown.escape.window="$wire.cerrarModales()">
    <!-- Backdrop dismiss -->
    <div class="fixed inset-0" wire:click="cerrarModales"></div>

    <!-- Modal Expediente Clínico LG / XL -->
    <div class="rm-modal rm-modal-lg z-10 font-sans" @click.stop>
        <!-- Header (#F7F0E9) -->
        <div class="rm-modal-header shrink-0">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--rm-surface-alt)] text-[var(--rm-primary)] border border-[var(--rm-border)] shadow-xs">
                    <i class="ph-bold ph-clipboard-text text-xl"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-200 text-slate-800 border border-slate-300 shadow-xs flex items-center gap-1">
                            <i class="ph-bold ph-folder-open"></i> Dossier de Expediente Completo
                        </span>
                    </div>
                    <h3 id="modal-detalle-title" class="rm-modal-title text-base font-bold text-[var(--rm-text-title)]">
                            Detalle de la alerta
                        </h3>
                        <span class="rm-badge {{ $badgeEstadoClass }}">
                            {{ $estadoTexto }}
                        </span>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                        Registro clínico asistencial y trazabilidad médica
                    </p>
                </div>
            </div>
            <button type="button"
                wire:click="cerrarModales"
                aria-label="Cerrar expediente"
                class="rm-btn-icon text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Body (#FBF7F2) -->
        <div class="rm-modal-body flex-1 min-h-0 overflow-y-auto space-y-3.5 text-xs">
            @if (session()->has('mensaje'))
                <div class="rm-alert rm-alert-success flex items-center justify-between">
                    <span>{{ session('mensaje') }}</span>
                </div>
            @endif

            <!-- 1. Datos del Residente y Ubicación Física -->
            <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] shadow-xs flex-wrap gap-2.5">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--rm-primary)] text-white font-bold text-sm shadow-xs shrink-0">
                        {{ substr($detalle->adultoMayor?->nombres ?? 'A', 0, 1) }}{{ substr($detalle->adultoMayor?->ap_paterno ?? 'M', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight">
                            {{ $detalle->adultoMayor?->ap_paterno }} {{ $detalle->adultoMayor?->ap_materno }} {{ $detalle->adultoMayor?->nombres }}
                        </h4>
                        <div class="flex items-center gap-2 text-[11px] text-[var(--rm-text-muted)] mt-0.5">
                            <span>{{ $detalle->adultoMayor?->edad_texto ?? 'Edad no registrada' }}</span>
                            <span>•</span>
                            <span class="inline-flex items-center gap-1">
                                <i class="ph ph-map-pin text-[var(--rm-primary)]"></i>
                                <span>{{ $detalle->adultoMayor?->ubicacion_texto ?? 'Sin ubicación asignada' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                        wire:click="verGraficos('{{ $detalle->cod_am }}')"
                        class="rm-btn rm-btn-sm rm-btn-secondary text-xs cursor-pointer shadow-xs">
                        <i class="ph ph-chart-line-up"></i>
                        <span>Evolución</span>
                    </button>
                    <button type="button"
                        wire:click="verUbicacion('{{ $detalle->cod_am }}')"
                        class="rm-btn rm-btn-sm rm-btn-secondary text-xs cursor-pointer shadow-xs">
                        <i class="ph ph-bed"></i>
                        <span>Ubicación</span>
                    </button>
                </div>
            </div>

            <!-- 2. Resumen Clínico Estructurado -->
            <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] space-y-2">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div class="space-y-0.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Severidad</span>
                        <span class="rm-badge {{ $badgeNivelClass }}">
                            {{ $detalle->nivel }}
                        </span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Canal / Origen</span>
                        <span class="rm-badge rm-badge-neutral text-[10px]">
                            {{ $detalle->origen }}
                        </span>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Responsable</span>
                        <p class="font-bold text-[var(--rm-text-title)] text-xs truncate">
                            {{ $detalle->responsable?->name ?? 'Sistema Automático' }}
                        </p>
                        <p class="text-[10px] text-[var(--rm-text-muted)]">{{ $detalle->turno?->nombre ?? 'Guardia General' }}</p>
                    </div>
                    <div class="space-y-0.5">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">Detección</span>
                        <p class="font-bold text-[var(--rm-text-title)] text-xs">
                            {{ $detalle->created_at?->translatedFormat('d M, H:i') }}
                        </p>
                        <p class="text-[10px] text-[var(--rm-text-muted)] font-mono">{{ $detalle->created_at?->diffForHumans() }}</p>
                    </div>
                </div>

                <!-- Motivo clínico -->
                <div class="pt-2 border-t border-[var(--rm-border-soft)]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)] block mb-0.5">
                        Motivo y Diagnóstico Clínico
                    </span>
                    <p class="p-2.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] text-xs text-[var(--rm-text-body)] leading-relaxed">
                        <strong class="text-[var(--rm-text-title)]">{{ $detalle->tipo_alerta }}:</strong> {{ $detalle->motivo }}
                    </p>
                </div>
            </div>

            @if($detalle->observacion_cierre)
                <div class="p-3 rounded-xl bg-[var(--rm-state-success-bg)] border border-[var(--rm-state-success-border)] text-xs text-[var(--rm-state-success-text)] space-y-1">
                    <span class="font-bold uppercase tracking-wider flex items-center gap-1.5 text-[var(--rm-success-action)]">
                        <i class="ph ph-check-circle text-base"></i>
                        Resolución y Justificación de Cierre
                    </span>
                    <p class="leading-relaxed text-[var(--rm-text-body)]">{{ $detalle->observacion_cierre }}</p>
                    <p class="text-[10.5px] text-[var(--rm-text-muted)] font-mono mt-1">
                        Cerrado por: {{ $detalle->cerradoPor?->name ?? 'Equipo asistencial' }} el {{ $detalle->fecha_cierre?->translatedFormat('d/m/Y H:i') }}
                    </p>
                </div>
            @endif

            <!-- 3. Historial de Intervenciones Clínicas (Timeline) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-base text-[var(--rm-primary)]"></i>
                        Historial de Intervenciones y Evolución ({{ $detalle->acciones->count() }})
                    </span>
                    <span class="text-[11px] text-[var(--rm-text-muted)] font-mono">Orden cronológico</span>
                </div>

                @if($detalle->acciones->isEmpty())
                    <div class="text-center py-6 border border-dashed border-[var(--rm-border)] rounded-xl text-[var(--rm-text-muted)] text-xs font-medium bg-[var(--rm-surface-alt)]">
                        No se han registrado intervenciones aún en esta alerta.
                    </div>
                @else
                    <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                        @foreach($detalle->acciones as $acc)
                            <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                                        <i class="ph ph-user-circle text-sm text-[var(--rm-primary)]"></i>
                                        <span>{{ $acc->responsable?->name ?? 'Profesional Clínico' }}</span>
                                        <span class="text-[var(--rm-text-muted)] font-normal">· {{ $acc->responsable?->roles?->first()?->name ?? 'Enfermería' }}</span>
                                    </span>
                                    <span class="text-[var(--rm-text-muted)] font-mono">
                                        {{ $acc->fecha_accion?->format('H:i') }} ({{ $acc->fecha_accion?->format('d/m/Y') }})
                                    </span>
                                </div>
                                <p class="text-[var(--rm-text-body)] font-medium leading-relaxed pl-5">
                                    {{ $acc->accion }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- 4. Formulario para Registrar Nueva Intervención (si la alerta no está cerrada) -->
            @if($detalle->puedeCerrarse())
                @canany(['alertas.atender','alertas.gestionar','salud.alertas.gestionar'])
                    <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)] space-y-2">
                        <label for="nuevaIntervencionInput" class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                            Agregar Nota de Evolución o Intervención
                        </label>
                        <div class="flex gap-2">
                            <input type="text"
                                id="nuevaIntervencionInput"
                                wire:model="accion"
                                placeholder="Describa la evolución o acción realizada (mínimo 5 caracteres)..."
                                class="rm-input flex-1 text-xs" />
                            <button type="button"
                                wire:click="guardarAccion"
                                wire:loading.attr="disabled"
                                class="rm-btn rm-btn-accent cursor-pointer disabled:opacity-60 flex items-center gap-1.5 flex-shrink-0">
                                <i class="ph-bold ph-plus-circle text-base"></i>
                                <span wire:loading.remove wire:target="guardarAccion">Registrar</span>
                                <span wire:loading wire:target="guardarAccion">Guardando...</span>
                            </button>
                        </div>
                        @error('accion')
                            <span class="text-xs text-[var(--rm-danger-action)] font-medium block">{{ $message }}</span>
                        @enderror
                    </div>
                @endcanany
            @endif
        </div>

        <!-- Footer (#F7F0E9) -->
        <div class="rm-modal-footer shrink-0 justify-between">
            <div>
                @if($detalle && $detalle->puedeCerrarse())
                    @canany(['alertas.cerrar','alertas.gestionar','salud.alertas.gestionar'])
                        <button type="button"
                            wire:click="cerrarAlerta('{{ $detalle->cod_alerta }}')"
                            class="rm-btn rm-btn-sm rm-btn-success cursor-pointer shadow-xs">
                            <i class="ph ph-archive-box text-base"></i>
                            <span>Cerrar y Archivar Alerta</span>
                        </button>
                    @endcanany
                @endif
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                    wire:click="cerrarModales"
                    class="rm-btn rm-btn-ghost cursor-pointer">
                    Cerrar Ventana
                </button>

                @if($detalle && $detalle->estado === 'ABIERTA')
                    @canany(['alertas.atender','alertas.gestionar','salud.alertas.gestionar'])
                        <button type="button"
                            wire:click="atenderAlerta('{{ $detalle->cod_alerta }}')"
                            class="rm-btn rm-btn-accent cursor-pointer shadow-xs">
                            <i class="ph-bold ph-stethoscope text-base"></i>
                            <span>Atender Alerta</span>
                        </button>
                    @endcanany
                @endif
            </div>
        </div>
    </div>
</div>
@endif
