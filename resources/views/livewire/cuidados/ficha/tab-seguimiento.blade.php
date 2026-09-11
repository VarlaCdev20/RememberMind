{{-- TAB 5: SEGUIMIENTO (HISTORIAL DE EVOLUCIÓN Y SEGUIMIENTOS DIARIOS) --}}
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-borde pb-3">
        <div>
            <h2 class="text-sm font-bold text-titulo flex items-center gap-2">
                <i class="ph-bold ph-note-pencil text-blue-600 text-base"></i>
                <span>Seguimiento Diario y Evolución del Residente</span>
            </h2>
            <p class="text-xs text-apoyo mt-0.5">Registro cronológico de comportamiento, ingesta, movilidad y novedades clínicas.</p>
        </div>

        {{-- ÚNICA ACCIÓN: REGISTRAR SEGUIMIENTO --}}
        <button type="button"
                wire:click="abrirModalSeguimiento"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-sky-700 transition self-start sm:self-auto">
            <i class="ph-bold ph-plus text-sm"></i>
            <span>Registrar seguimiento</span>
        </button>
    </div>

    <div class="space-y-4">
        @forelse($adultoMayor->seguimientosDiarios as $seg)
            <div class="rounded-3xl border border-borde bg-fondo-panel p-5 shadow-sm space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-borde pb-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold border {{ $seg->incidente ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $seg->incidente ? 'bg-rose-500' : 'bg-blue-500' }}"></span>
                            Estado: {{ ucfirst(strtolower(str_replace('_', ' ', $seg->estado_general ?? 'Estable'))) }}
                        </span>
                        <span class="text-xs font-bold text-titulo">
                            {{ $seg->fecha ? \Carbon\Carbon::parse($seg->fecha)->format('d/m/Y') : '' }}
                            @if($seg->hora_inicio)
                                · {{ substr($seg->hora_inicio, 0, 5) }} {{ $seg->hora_fin ? 'a ' . substr($seg->hora_fin, 0, 5) : '' }}
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center gap-3 text-xs text-apoyo">
                        @if($seg->incidente)
                            <span class="inline-flex items-center gap-1 font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                <i class="ph-bold ph-warning"></i> Incidente registrado
                            </span>
                        @endif
                        @if($seg->requiere_atencion_medica)
                            <span class="inline-flex items-center gap-1 font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                <i class="ph-bold ph-stethoscope"></i> Solicitud revisión médica
                            </span>
                        @endif
                        <span>Responsable: <strong class="text-parrafo">{{ $seg->turno?->enfermero?->name ?? 'Enfermería' }}</strong></span>
                    </div>
                </div>

                {{-- Matriz de Parámetros de la Evolución --}}
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 text-xs">
                    <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Alimentación / Hidratación</span>
                        <span class="font-bold text-titulo mt-0.5 block">
                            {{ $seg->alimentacion ? ucfirst(strtolower($seg->alimentacion)) : 'Sin reporte' }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Movilidad Funcional</span>
                        <span class="font-bold text-titulo mt-0.5 block">
                            {{ $seg->movilidad ? ucfirst(strtolower(str_replace('_', ' ', $seg->movilidad))) : 'Sin reporte' }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Orientación y Conducta</span>
                        <span class="font-bold text-titulo mt-0.5 block">
                            {{ $seg->patron_sueno ? 'Sueño: ' . ucfirst(strtolower($seg->patron_sueno)) : 'Tranquilo y orientado' }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-fondo-card p-2.5 border border-borde">
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Dolor / Molestias</span>
                        <span class="font-bold text-titulo mt-0.5 block">
                            {{ $seg->nivel_dolor !== null ? $seg->nivel_dolor . '/10 EVA' : 'Sin dolor activo' }}
                        </span>
                    </div>
                </div>

                {{-- Observaciones Clínicas Detalladas --}}
                @if($seg->observaciones)
                    <div class="rounded-xl bg-fondo-card p-3 border border-borde text-xs text-parrafo leading-relaxed">
                        <span class="font-bold text-titulo block mb-1">Observaciones asistenciales:</span>
                        {{ $seg->observaciones }}
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-3xl border border-borde bg-fondo-panel p-8 text-center">
                <i class="ph-bold ph-note-pencil text-3xl text-apoyo mb-2 block"></i>
                <p class="text-sm font-bold text-titulo">No existen registros de seguimiento diario.</p>
                <p class="text-xs text-apoyo mt-1">Haga clic en "Registrar seguimiento" para ingresar la primera anotación de enfermería.</p>
            </div>
        @endforelse
    </div>
</div>
