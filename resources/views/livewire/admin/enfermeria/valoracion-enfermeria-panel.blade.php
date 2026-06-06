<div class="space-y-6">
    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-[1.5rem] border border-borde/70 bg-fondo-panel p-5 shadow-sm">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Enfermería</span>
            <h2 class="mt-1 text-xl font-extrabold text-parrafo">Valoraciones de Admisión</h2>
            <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/60">
                Gestione las valoraciones iniciales de enfermería para los nuevos residentes.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="abrirCrear" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
                <i class="ph-bold ph-plus"></i>
                Nueva Valoración
            </button>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="grid gap-4 sm:grid-cols-2 rounded-[1.5rem] border border-borde/70 bg-fondo-panel p-5 shadow-sm">
        <label class="relative block">
            <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-parrafo/60">Buscar paciente</span>
            <i class="ph-bold ph-magnifying-glass absolute bottom-3.5 left-3.5 text-meta"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre o apellido..." class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition placeholder:text-meta focus:border-borde-focus">
        </label>
        
        <label class="relative block">
            <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-parrafo/60">Estado</span>
            <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-borde/70 bg-fondo-panel py-2.5 px-4 text-xs font-bold text-parrafo outline-none transition focus:border-borde-focus">
                <option value="">TODOS</option>
                <option value="BORRADOR">BORRADOR</option>
                <option value="COMPLETADA">COMPLETADA</option>
            </select>
        </label>
    </div>

    {{-- TABLA --}}
    <div class="overflow-hidden rounded-[1.5rem] border border-borde/70 bg-fondo-panel shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-parrafo">
                <thead class="bg-fondo-app text-[10px] font-bold uppercase tracking-widest text-parrafo/60">
                    <tr>
                        <th class="px-5 py-4">Adulto Mayor</th>
                        <th class="px-5 py-4">Fecha/Hora</th>
                        <th class="px-5 py-4">Estado Gral.</th>
                        <th class="px-5 py-4">Nivel Conciencia</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/40">
                    @forelse($valoraciones as $val)
                        <tr class="transition hover:bg-fondo-app/50">
                            <td class="px-5 py-3 font-bold">
                                {{ $val->adultoMayor->nombres ?? 'S/D' }} {{ $val->adultoMayor->ap_paterno ?? '' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ \Carbon\Carbon::parse($val->fecha)->format('d/m/Y') }} <br>
                                <span class="text-xs text-parrafo/60">{{ \Carbon\Carbon::parse($val->hora)->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-3 font-bold">
                                {{ $val->estado_general ?? 'S/D' }}
                            </td>
                            <td class="px-5 py-3">
                                {{ $val->nivel_conciencia ?? 'S/D' }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $val->estado === 'COMPLETADA' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
                                    {{ $val->estado }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="abrirVer({{ $val->id }})" class="inline-flex items-center justify-center rounded-lg bg-fondo-panel p-2 text-boton-acento shadow-sm border border-borde/70 transition hover:bg-boton-acento hover:text-inverso">
                                    <i class="ph-bold ph-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center">
                                <i class="ph-bold ph-file-dashed text-4xl text-parrafo/30"></i>
                                <p class="mt-2 text-xs font-bold text-parrafo/60">No hay valoraciones registradas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($valoraciones->hasPages())
            <div class="border-t border-borde/70 bg-fondo-panel px-5 py-3">
                {{ $valoraciones->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL DE FORMULARIO --}}
    @if($modalForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl overflow-hidden rounded-[1.5rem] bg-fondo-panel shadow-2xl">
                <div class="flex items-center justify-between border-b border-borde/70 bg-fondo-app px-6 py-4">
                    <h3 class="text-lg font-extrabold text-parrafo">Registro de Valoración</h3>
                    <button wire:click="cerrarModales" class="text-parrafo/60 transition hover:text-boton-acento">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>
                <div class="p-6">
                    <p class="text-xs font-bold text-parrafo/60 mb-4">Esta vista es una estructura base simplificada. Amplíe los campos según el modelo.</p>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold text-parrafo">Paciente</span>
                            <select wire:model="codAm" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
                                <option value="">Seleccione...</option>
                                @foreach($adultos as $adulto)
                                    <option value="{{ $adulto->cod_am }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>
                                @endforeach
                            </select>
                            @error('codAm') <span class="text-[10px] text-boton-acento">{{ $message }}</span> @enderror
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="block">
                                <span class="text-xs font-bold text-parrafo">Fecha</span>
                                <input type="date" wire:model="fecha" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
                            </label>
                            <label class="block">
                                <span class="text-xs font-bold text-parrafo">Hora</span>
                                <input type="time" wire:model="hora" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-borde/70 bg-fondo-app px-6 py-4">
                    <button wire:click="cerrarModales" class="rounded-xl border border-borde/70 px-4 py-2 text-xs font-bold text-parrafo transition hover:bg-borde/40">
                        Cancelar
                    </button>
                    <button wire:click="guardar" class="rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso transition hover:bg-fondo-panel">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VER DETALLE --}}
    @if($modalVer && $detalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl overflow-hidden rounded-[1.5rem] bg-fondo-panel shadow-2xl">
                <div class="flex items-center justify-between border-b border-borde/70 bg-fondo-app px-6 py-4">
                    <h3 class="text-lg font-extrabold text-parrafo">Detalle de Valoración</h3>
                    <button wire:click="cerrarModales" class="text-parrafo/60 transition hover:text-boton-acento">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div class="rounded-xl border border-borde/70 p-4">
                        <p class="text-xs font-bold text-parrafo/60 uppercase">Paciente</p>
                        <p class="text-base font-extrabold text-parrafo">{{ $detalle->adultoMayor->nombres ?? 'S/D' }} {{ $detalle->adultoMayor->ap_paterno ?? '' }}</p>
                        <p class="text-xs font-bold mt-1">Registrado por: {{ $detalle->registradoPor->nombres ?? 'Desconocido' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-[10px] font-bold text-parrafo/60 uppercase">Estado General</p>
                            <p class="text-sm font-bold text-parrafo">{{ $detalle->estado_general ?? 'S/D' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-parrafo/60 uppercase">Nivel Conciencia</p>
                            <p class="text-sm font-bold text-parrafo">{{ $detalle->nivel_conciencia ?? 'S/D' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-parrafo/60 uppercase">Riesgo Caída</p>
                            <p class="text-sm font-bold text-parrafo">{{ $detalle->riesgo_caida ?? 'S/D' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-parrafo/60 uppercase">Piel</p>
                            <p class="text-sm font-bold text-parrafo">{{ $detalle->piel_estado ?? 'S/D' }}</p>
                        </div>
                    </div>
                    @if($detalle->observacion)
                    <div>
                        <p class="text-[10px] font-bold text-parrafo/60 uppercase">Observación</p>
                        <p class="text-sm text-parrafo">{{ $detalle->observacion }}</p>
                    </div>
                    @endif
                </div>
                <div class="border-t border-borde/70 bg-fondo-app px-6 py-4 text-right">
                    <button wire:click="cerrarModales" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-bold text-inverso transition hover:bg-fondo-panel">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
