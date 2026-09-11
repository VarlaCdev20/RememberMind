@if($modalDetalle && $detalle)
@php
    $estadoColor = match($detalle->estado) {
        'ABIERTA' => 'bg-estado-avisoBg text-estado-aviso border border-estado-avisoBorde',
        'EN_ATENCION' => 'bg-estado-infoBg text-estado-info border border-estado-infoBorde',
        'CERRADA' => 'bg-estado-exitoBg text-estado-exito border border-estado-exitoBorde',
        default => 'bg-fondo-app text-apoyo border border-borde'
    };
    $estadoTexto = match($detalle->estado) {
        'ABIERTA' => 'Alerta Abierta',
        'EN_ATENCION' => 'En Atenci?n Activa',
        'CERRADA' => 'Alerta Resuelta / Cerrada',
        default => $detalle->estado
    };
@endphp
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     x-data
     x-on:keydown.escape.window="$wire.cerrarModales()">
    <!-- Fondo clicable -->
    <div class="fixed inset-0" wire:click="cerrarModales"></div>

    <!-- Tarjeta del Modal Flotante Unificado -->
    <div class="modal-institucional relative w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-borde bg-fondo-panel shadow-2xl z-10 font-sans">
        <!-- Encabezado -->
        <div class="flex items-center justify-between border-b border-borde bg-fondo-app/60 px-6 py-4">
            <div class="flex items-center gap-3 text-titulo">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-app text-boton-principal border border-borde shadow-sm">
                    <i class="ph-bold ph-file-text text-xl"></i>
                </span>
                <div>
                    <h3 class="text-base font-black text-titulo">Expediente de Alerta Cl?nica</h3>
                    <span class="text-xs font-bold text-apoyo font-mono">ID: {{ $detalle->cod_alerta }}</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider {{ $estadoColor }}">
                    {{ $estadoTexto }}
                </span>
                <button type="button"
                    wire:click="cerrarModales"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-apoyo hover:bg-fondo-hover hover:text-titulo transition cursor-pointer">
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>
        </div>

        <!-- Contenido desplazable -->
        <div class="flex-1 overflow-y-auto p-6 space-y-5 bg-fondo-panel text-parrafo">
            <!-- Datos del Adulto Mayor y Ubicaci?n -->
            <div class="p-4 rounded-xl bg-fondo-app border border-borde">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-boton-principal text-inverso font-black text-sm shadow-sm">
                            {{ substr($detalle->adultoMayor->nombres ?? 'A', 0, 1) }}{{ substr($detalle->adultoMayor->ap_paterno ?? 'M', 0, 1) }}
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-titulo">
                                {{ $detalle->adultoMayor->nombres ?? 'Sin Datos' }} {{ $detalle->adultoMayor->ap_paterno ?? '' }} {{ $detalle->adultoMayor->ap_materno ?? '' }}
                            </h4>
                            <p class="text-xs text-apoyo font-medium">
                                C?digo: <span class="font-mono font-bold text-titulo">{{ $detalle->cod_am }}</span>
                                @if($detalle->adultoMayor?->edad)
                                    · {{ $detalle->adultoMayor->edad }} años
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-xs font-bold text-parrafo bg-fondo-panel px-3 py-1.5 rounded-lg border border-borde inline-flex items-center gap-1.5 self-start sm:self-auto shadow-sm">
                        <i class="ph-bold ph-door text-apoyo"></i>
                        <span>Hab. {{ $detalle->adultoMayor?->habitacion?->nombre ?? ($detalle->adultoMayor?->habitacion?->codigo ?? 'S/A') }}</span>
                        <span class="text-borde">?</span>
                        <span>Cama {{ $detalle->adultoMayor?->cama?->codigo ?? 'S/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Acciones Directas Unificadas: Gr?ficos de Evoluci?n y Ubicaci?n -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-xl bg-fondo-app border border-borde">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-panel text-boton-principal border border-borde shadow-sm">
                        <i class="ph-bold ph-chart-line-up text-lg"></i>
                    </span>
                    <div>
                        <h5 class="text-xs font-black text-titulo">Curvas y Gr?ficos Cl?nicos</h5>
                        <p class="text-[11px] text-apoyo font-medium">Evoluci?n de presi?n arterial, pulso, saturaci?n O2 y temperatura</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button"
                       wire:click="verGraficos('{{ $detalle->cod_am }}')"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-inverso bg-boton-principal hover:bg-boton-principalHover shadow-sm transition active:scale-95 cursor-pointer">
                        <i class="ph-bold ph-chart-line-up text-sm"></i>
                        <span>Ver Gr?ficos Cl?nicos</span>
                    </button>
                    <button type="button"
                       wire:click="verUbicacion('{{ $detalle->cod_am }}')"
                       class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-parrafo bg-fondo-panel border border-borde hover:bg-fondo-hover transition active:scale-95 cursor-pointer shadow-sm">
                        <i class="ph-bold ph-bed text-sm text-boton-acento"></i>
                        <span>Ubicaci?n y Ficha</span>
                    </button>
                </div>
            </div>

            <!-- Datos Cl?nicos de la Alerta -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-fondo-app border border-borde space-y-1">
                    <span class="font-bold text-apoyo uppercase tracking-wider text-[10px]">Origen</span>
                    <p class="font-bold text-titulo">{{ $detalle->origen }}</p>
                </div>
                <div class="p-3 rounded-xl bg-fondo-app border border-borde space-y-1">
                    <span class="font-bold text-apoyo uppercase tracking-wider text-[10px]">Severidad</span>
                    <p class="font-bold text-titulo">{{ $detalle->tipoAlerta?->severidad ?? 'MEDIA' }}</p>
                </div>
                <div class="p-3 rounded-xl bg-fondo-app border border-borde space-y-1">
                    <span class="font-bold text-apoyo uppercase tracking-wider text-[10px]">Atenci?n</span>
                    <p class="font-bold text-titulo">{{ $detalle->fecha_atencion?->translatedFormat('d M Y, H:i') ?? 'Sin atender' }}</p>
                    <p class="text-apoyo text-[11px]">{{ $detalle->atendidoPor?->name ?? 'Pendiente' }}</p>
                </div>
                <div class="p-3 rounded-xl bg-fondo-app border border-borde space-y-1">
                    <span class="font-bold text-apoyo uppercase tracking-wider text-[10px]">Resoluci?n</span>
                    <p class="font-bold text-titulo">{{ $detalle->fecha_cierre?->translatedFormat('d M Y, H:i') ?? 'Activa' }}</p>
                    <p class="text-apoyo text-[11px]">{{ $detalle->cerradoPor?->name ?? 'En seguimiento' }}</p>
                </div>
            </div>

            @if($detalle->observacion_cierre)
                <div class="p-4 rounded-xl bg-estado-exitoBg border border-estado-exitoBorde text-xs text-estado-exito space-y-1 shadow-sm">
                    <span class="font-bold uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-check-circle text-base"></i>
                        Observaci?n de Cierre y Resoluci?n
                    </span>
                    <p class="leading-relaxed text-parrafo">{{ $detalle->observacion_cierre }}</p>
                </div>
            @endif

            <!-- Historial de Intervenciones y Acciones Registradas -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-apoyo flex items-center gap-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-base text-boton-principal"></i>
                        Historial de Intervenciones Cl?nicas ({{ $detalle->acciones->count() }})
                    </span>
                </div>

                @if($detalle->acciones->isEmpty())
                    <div class="text-center py-6 border border-dashed border-borde rounded-xl text-apoyo text-xs font-medium bg-fondo-app/40">
                        No se han asentado acciones a?n en esta alerta.
                    </div>
                @else
                    <div class="space-y-2 max-h-52 overflow-y-auto pr-1">
                        @foreach($detalle->acciones as $acc)
                            <div class="p-3 rounded-xl bg-fondo-app border border-borde text-xs space-y-1 shadow-sm">
                                <div class="flex items-center justify-between text-apoyo text-[11px]">
                                    <span class="font-bold text-titulo flex items-center gap-1">
                                        <i class="ph-bold ph-user"></i>
                                        {{ $acc->responsable?->name ?? $acc->responsable_id }}
                                    </span>
                                    <span>{{ $acc->fecha_accion?->translatedFormat('d M, H:i') }} ({{ $acc->fecha_accion?->diffForHumans() }})</span>
                                </div>
                                <p class="text-parrafo font-medium leading-relaxed">{{ $acc->accion }}</p>
                                @if($acc->observacion)
                                    <p class="text-apoyo text-[11px] italic">{{ $acc->observacion }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Registrar Nueva Acci?n / Intervenci?n (si la alerta no est? cerrada) -->
            @if($detalle->puedeCerrarse())
                @canany(['alertas.atender','alertas.gestionar','salud.alertas.gestionar'])
                    <div class="p-4 rounded-xl bg-fondo-app border border-borde space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-titulo">
                            Agregar Nota de Evoluci?n o Intervenci?n
                        </label>
                        <div class="flex gap-2">
                            <input type="text"
                                wire:model="accion"
                                placeholder="Describa la acci?n realizada en este turno (m?nimo 5 caracteres)..."
                                class="flex-1 rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-xs text-parrafo placeholder-apoyo focus:border-boton-principal focus:ring-1 focus:ring-boton-principal outline-none transition" />
                            <button type="button"
                                wire:click="guardarAccion"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-inverso bg-boton-principal hover:bg-boton-principalHover shadow-md transition active:scale-95 cursor-pointer disabled:opacity-60 flex items-center gap-1">
                                <i class="ph-bold ph-plus-circle text-sm"></i>
                                <span wire:loading.remove wire:target="guardarAccion">Registrar</span>
                                <span wire:loading wire:target="guardarAccion">...</span>
                            </button>
                        </div>
                    </div>
                @endcanany
            @endif
        </div>

        <!-- Pie del Modal -->
        <div class="flex items-center justify-between border-t border-borde bg-fondo-app/60 px-6 py-4">
            <div>
                @if($detalle && $detalle->puedeCerrarse())
                    @canany(['alertas.cerrar','alertas.gestionar','salud.alertas.gestionar'])
                        <button type="button"
                            wire:click="cerrarAlerta('{{ $detalle->cod_alerta }}')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold bg-estado-exitoBg text-estado-exito border border-estado-exitoBorde hover:brightness-95 transition cursor-pointer shadow-sm">
                            <i class="ph-bold ph-archive-box text-sm"></i>
                            <span>Finalizar / Cerrar Alerta</span>
                        </button>
                    @endcanany
                @endif
            </div>

            <div class="flex items-center gap-2">
                <button type="button"
                    wire:click="cerrarModales"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-apoyo border border-borde bg-fondo-panel hover:bg-fondo-hover hover:text-titulo transition cursor-pointer">
                    Cerrar Ventana
                </button>

                @if($detalle && $detalle->estado === 'ABIERTA')
                    @canany(['alertas.atender','alertas.gestionar','salud.alertas.gestionar'])
                        <button type="button"
                            wire:click="atenderAlerta('{{ $detalle->cod_alerta }}')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-inverso bg-boton-principal hover:bg-boton-principalHover shadow-md transition active:scale-95 cursor-pointer">
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
