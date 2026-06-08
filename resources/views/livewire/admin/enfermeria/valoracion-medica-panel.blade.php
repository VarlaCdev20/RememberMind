<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-[1.5rem] border border-borde/70 bg-fondo-panel p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Medicina</span>
            <h2 class="mt-1 text-xl font-extrabold text-parrafo">Valoraciones Médicas de Admisión</h2>
            <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/60">Vista adaptada a ficha_medica_adulto.</p>
        </div>
        <button wire:click="abrirCrear" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition active:scale-95">
            <i class="ph-bold ph-plus"></i>
            Nueva valoración
        </button>
    </div>

    <div class="grid gap-4 rounded-[1.5rem] border border-borde/70 bg-fondo-panel p-5 shadow-sm sm:grid-cols-2">
        <label class="block">
            <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-parrafo/60">Buscar paciente</span>
            <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-xl border border-borde/70 bg-fondo-panel px-4 py-2.5 text-xs font-bold text-parrafo outline-none" placeholder="Nombre o apellido...">
        </label>
        <label class="block">
            <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-parrafo/60">Estado</span>
            <select wire:model.live="filtroEstado" class="w-full rounded-xl border border-borde/70 bg-fondo-panel px-4 py-2.5 text-xs font-bold text-parrafo outline-none">
                <option value="">TODOS</option>
                <option value="ACTIVO">ACTIVO</option>
                <option value="BORRADOR">BORRADOR</option>
            </select>
        </label>
    </div>

    <div class="overflow-hidden rounded-[1.5rem] border border-borde/70 bg-fondo-panel shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-parrafo">
                <thead class="bg-fondo-app text-[10px] font-bold uppercase tracking-widest text-parrafo/60">
                    <tr>
                        <th class="px-5 py-4">Adulto Mayor</th>
                        <th class="px-5 py-4">Registro</th>
                        <th class="px-5 py-4">Estado</th>
                        <th class="px-5 py-4">Observación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/40">
                    @forelse($valoraciones as $val)
                        <tr class="transition hover:bg-fondo-app/50">
                            <td class="px-5 py-3 font-bold">{{ $val->adultoMayor->nombres ?? 'S/D' }} {{ $val->adultoMayor->ap_paterno ?? '' }}</td>
                            <td class="px-5 py-3">{{ optional($val->created_at)->format('d/m/Y H:i') ?? 'S/D' }}</td>
                            <td class="px-5 py-3"><span class="rounded-full bg-estado-exitoBg px-2.5 py-0.5 text-[10px] font-bold uppercase text-estado-exito">{{ $val->estado ?? 'ACTIVO' }}</span></td>
                            <td class="max-w-md truncate px-5 py-3">{{ $val->observacion_medica ?? 'Sin observación' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-xs font-bold text-parrafo/60">No hay valoraciones médicas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($valoraciones->hasPages())
            <div class="border-t border-borde/70 bg-fondo-panel px-5 py-3">{{ $valoraciones->links() }}</div>
        @endif
    </div>

    @if($modalForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl overflow-hidden rounded-[1.5rem] bg-fondo-panel shadow-2xl">
                <div class="flex items-center justify-between border-b border-borde/70 bg-fondo-app px-6 py-4">
                    <h3 class="text-lg font-extrabold text-parrafo">Registro de Valoración Médica</h3>
                    <button wire:click="cerrarModales" class="text-parrafo/60 transition hover:text-boton-acento"><i class="ph-bold ph-x text-xl"></i></button>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-bold text-parrafo">Paciente</span>
                        <select wire:model="codAm" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
                            <option value="">Seleccione...</option>
                            @foreach($adultos as $adulto)
                                <option value="{{ $adulto->cod_am }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>
                            @endforeach
                        </select>
                        @error('codAm') <span class="text-[10px] text-boton-acento">{{ $message }}</span> @enderror
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold text-parrafo">Fecha</span>
                        <input type="date" wire:model="fecha" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
                    </label>
                    <label class="block">
                        <span class="text-xs font-bold text-parrafo">Resultado</span>
                        <select wire:model="resultadoAdmision" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
                            <option value="">Seleccione...</option>
                            <option value="ADMITIDO">ADMITIDO</option>
                            <option value="NO_ADMITIDO">NO ADMITIDO</option>
                            <option value="DERIVADO">DERIVADO</option>
                            <option value="OBSERVADO">OBSERVADO</option>
                            <option value="CANCELADO">CANCELADO</option>
                        </select>
                    </label>
                    <label class="block sm:col-span-2">
                        <span class="text-xs font-bold text-parrafo">Motivo / decisión</span>
                        <textarea wire:model="motivoDecision" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs"></textarea>
                        @error('motivoDecision') <span class="text-[10px] text-boton-acento">{{ $message }}</span> @enderror
                    </label>
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-borde/70 bg-fondo-app px-6 py-4">
                    <button wire:click="cerrarModales" class="rounded-xl border border-borde/70 px-4 py-2 text-xs font-bold text-parrafo">Cancelar</button>
                    <button wire:click="guardar" class="rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso">Guardar</button>
                </div>
            </div>
        </div>
    @endif
</div>
