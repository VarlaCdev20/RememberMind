<div class="min-h-screen py-8 font-sans antialiased text-[#2F3E5C]">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        {{-- ENCABEZADO Y VOLVER --}}
        <div class="mb-8 flex flex-col justify-between gap-4 border-b border-[#C7B5A3]/50 pb-6 sm:flex-row sm:items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.salud-seguimiento.resumen', $adulto) }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm transition-all hover:bg-terracota hover:text-white border border-[#C7B5A3]/40">
                    <i class="ph-bold ph-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight text-azul-profundo">
                        Signos Vitales
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        {{ $adulto->nombres }} {{ $adulto->ap_paterno }} • {{ $adulto->cod_am }}
                    </p>
                </div>
            </div>
            
            @can('salud.signos.crear')
                <button wire:click="openModal" class="inline-flex items-center gap-2 rounded-xl bg-terracota px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-md transition-all hover:bg-terracota-dark hover:scale-105 active:scale-95">
                    <i class="ph-bold ph-plus"></i>
                    Registrar Control
                </button>
            @endcan
        </div>

        {{-- HISTORIAL DE SIGNOS VITALES --}}
        <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden mb-8">
            <div class="bg-azul-profundo px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                    <i class="ph-bold ph-activity text-terracota text-lg"></i>
                    Historial de Controles
                </h2>
            </div>
            <div class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-azul-profundo">
                        <thead class="bg-[#F7F5F2] text-[10px] font-black uppercase text-azul-profundo/60">
                            <tr>
                                <th class="px-6 py-3">Fecha y Hora</th>
                                <th class="px-6 py-3">P. Arterial</th>
                                <th class="px-6 py-3">F. Cardíaca</th>
                                <th class="px-6 py-3">Sat. O2</th>
                                <th class="px-6 py-3">Temperatura</th>
                                <th class="px-6 py-3">Peso</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#C7B5A3]/20">
                            @forelse($historialSignos as $signo)
                                @php
                                    $alertaSat = $signo->saturacion_oxigeno < 92 && $signo->saturacion_oxigeno !== null;
                                    $alertaTemp = $signo->temperatura > 37.8 && $signo->temperatura !== null;
                                @endphp
                                <tr class="hover:bg-[#F7F5F2]/50 transition-colors">
                                    <td class="px-6 py-4 font-bold">
                                        {{ \Carbon\Carbon::parse($signo->fecha)->format('d/m/Y') }}<br>
                                        <span class="text-[10px] text-terracota">{{ \Carbon\Carbon::parse($signo->hora)->format('H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4">{{ $signo->presion_sistolica }}/{{ $signo->presion_diastolica }} <span class="text-[9px]">mmHg</span></td>
                                    <td class="px-6 py-4">{{ $signo->frecuencia_cardiaca }} <span class="text-[9px]">bpm</span></td>
                                    <td class="px-6 py-4 font-bold {{ $alertaSat ? 'text-rose-600' : '' }}">
                                        {{ $signo->saturacion_oxigeno }}%
                                        @if($alertaSat) <i class="ph-fill ph-warning-circle ml-1" title="Valor fuera de rango"></i> @endif
                                    </td>
                                    <td class="px-6 py-4 font-bold {{ $alertaTemp ? 'text-amber-600' : '' }}">
                                        {{ $signo->temperatura }}°C
                                        @if($alertaTemp) <i class="ph-fill ph-warning-circle ml-1" title="Valor fuera de rango"></i> @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $signo->peso }} <span class="text-[9px]">kg</span></td>
                                    <td class="px-6 py-4 text-right">
                                        @can('salud.signos.anular')
                                            <button wire:click="anularRegistro({{ $signo->id_signos ?? $signo->cod_signos ?? $signo->id }})" wire:confirm="¿Estás seguro de anular este registro?" class="text-rose-500 hover:text-rose-700 transition-colors p-1" title="Anular">
                                                <i class="ph-bold ph-trash text-lg"></i>
                                            </button>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-xs font-bold text-azul-profundo/50 italic">
                                        No hay registros de signos vitales.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REGISTRAR SIGNOS --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl" @click.stop>
                <div class="flex items-center justify-between border-b border-[#C7B5A3]/30 bg-[#F7F5F2] px-6 py-4">
                    <h2 class="text-lg font-black text-azul-profundo flex items-center gap-2">
                        <i class="ph-bold ph-activity text-terracota"></i> Registrar Signos Vitales
                    </h2>
                    <button wire:click="closeModal" class="rounded-full p-2 text-azul-profundo/50 hover:bg-rose-50 hover:text-rose-500 transition-colors">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <form wire:submit.prevent="save">
                        <div class="grid gap-4 sm:grid-cols-2 mb-4 border-b border-[#C7B5A3]/20 pb-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Fecha</label>
                                <input type="date" wire:model="fecha" class="w-full rounded-xl border-[#C7B5A3]/40 bg-[#F7F5F2] text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota" required>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Hora</label>
                                <input type="time" wire:model="hora" class="w-full rounded-xl border-[#C7B5A3]/40 bg-[#F7F5F2] text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota" required>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 mb-6">
                            <div class="flex gap-2">
                                <div class="w-1/2">
                                    <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Sistólica (mmHg)</label>
                                    <input type="number" wire:model="presion_sistolica" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Diastólica (mmHg)</label>
                                    <input type="number" wire:model="presion_diastolica" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Frecuencia Cardíaca (bpm)</label>
                                <input type="number" wire:model="frecuencia_cardiaca" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Saturación O2 (%)</label>
                                <input type="number" wire:model="saturacion_oxigeno" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Temperatura (°C)</label>
                                <input type="number" step="0.1" wire:model="temperatura" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Peso (kg)</label>
                                <input type="number" step="0.1" wire:model="peso" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-black uppercase text-azul-profundo mb-1">Observaciones</label>
                                <textarea wire:model="observaciones" rows="2" class="w-full rounded-xl border-[#C7B5A3]/40 bg-white text-sm font-bold text-azul-profundo focus:border-terracota focus:ring-terracota"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-[#C7B5A3]/20">
                            <button type="button" wire:click="closeModal" class="rounded-xl px-5 py-2.5 text-xs font-black uppercase text-azul-profundo hover:bg-[#F7F5F2] transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-xl bg-terracota px-6 py-2.5 text-xs font-black uppercase text-white shadow-md hover:bg-terracota-dark transition-colors">
                                Guardar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
