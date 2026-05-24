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
                        Ficha Médica Base
                    </h1>
                    <p class="mt-1 text-sm font-bold text-azul-profundo/60">
                        {{ $adulto->nombres }} {{ $adulto->ap_paterno }} • {{ $adulto->cod_am }}
                    </p>
                </div>
            </div>
            
            @can('salud.ficha.crear')
                <button wire:click="openModal" class="inline-flex items-center gap-2 rounded-xl bg-terracota px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-md transition-all hover:bg-terracota-dark hover:scale-105 active:scale-95">
                    <i class="ph-bold ph-pencil-simple"></i>
                    {{ $fichaActiva ? 'Editar Ficha' : 'Crear Ficha' }}
                </button>
            @endcan
        </div>

        @if($fichaActiva)
            <div class="rounded-3xl border border-[#C7B5A3]/40 bg-white shadow-sm overflow-hidden mb-8">
                <div class="bg-emerald-600 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-sm font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <i class="ph-bold ph-check-circle text-lg"></i>
                        Ficha Activa
                    </h2>
                    @can('salud.ficha.archivar')
                        <button wire:click="archivarFicha" wire:confirm="¿Estás seguro de archivar esta ficha? Se moverá al historial." class="rounded-lg bg-white/10 px-3 py-1 text-xs font-black text-white hover:bg-white/20 transition-colors">
                            Archivar Ficha
                        </button>
                    @endcan
                </div>
                <div class="p-6 grid gap-6 md:grid-cols-2">
                    <div>
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-3 border-b border-[#C7B5A3]/20 pb-2">Patologías Registradas</h3>
                        <div class="flex flex-wrap gap-2">
                            @if($fichaActiva->hipertension) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Hipertensión</span> @endif
                            @if($fichaActiva->diabetes) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Diabetes</span> @endif
                            @if($fichaActiva->problemas_cardiacos) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Problemas Cardíacos</span> @endif
                            @if($fichaActiva->acv) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">ACV</span> @endif
                            @if($fichaActiva->parkinson) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Parkinson</span> @endif
                            @if($fichaActiva->epilepsia) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Epilepsia</span> @endif
                            @if($fichaActiva->alzheimer_diagnosticado) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Alzheimer</span> @endif
                            @if($fichaActiva->depresion) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Depresión</span> @endif
                            @if($fichaActiva->ansiedad) <span class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Ansiedad</span> @endif
                        </div>
                    </div>
                    <div>
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-3 border-b border-[#C7B5A3]/20 pb-2">Alergias y Restricciones</h3>
                        <div class="space-y-4">
                            <div>
                                <span class="block text-[10px] font-bold text-azul-profundo/60 mb-1">Alergias</span>
                                <p class="text-sm font-bold text-azul-profundo">{{ $fichaActiva->alergias ?: 'Ninguna' }}</p>
                            </div>
                            <div>
                                <span class="block text-[10px] font-bold text-azul-profundo/60 mb-1">Restricciones Alimentarias</span>
                                <p class="text-sm font-bold text-azul-profundo">{{ $fichaActiva->restricciones_alimentarias ?: 'Ninguna' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-azul-profundo/50 mb-3 border-b border-[#C7B5A3]/20 pb-2">Observaciones Generales</h3>
                        <p class="text-sm text-azul-profundo">{{ $fichaActiva->observacion_medica ?: 'Sin observaciones.' }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-[#C7B5A3]/60 bg-white py-8 text-center shadow-sm mb-8">
                <i class="ph-fill ph-file-dashed text-3xl text-[#C7B5A3]/60 mb-4"></i>
                <h3 class="text-lg font-black text-azul-profundo">No hay ficha médica activa</h3>
                <p class="mt-1 text-sm font-bold text-[#2F3E5C]/50 mb-6">Debes registrar la ficha para habilitar el seguimiento clínico.</p>
                @can('salud.ficha.crear')
                    <button wire:click="openModal" class="rounded-xl bg-terracota px-6 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md transition-all hover:bg-terracota-dark hover:scale-105">
                        Crear Ficha Médica
                    </button>
                @endcan
            </div>
        @endif

        {{-- HISTORIAL --}}
        @if(count($historialFichas) > 0)
            <h2 class="text-xs font-black uppercase tracking-widest text-azul-profundo mb-4 flex items-center gap-2">
                <i class="ph-bold ph-clock-counter-clockwise text-terracota"></i> Historial de Fichas
            </h2>
            <div class="space-y-3">
                @foreach($historialFichas as $hist)
                    <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#F7F5F2] p-4 flex items-center justify-between">
                        <div>
                            <span class="rounded-full bg-azul-profundo/10 px-2.5 py-1 text-[10px] font-black uppercase text-azul-profundo mr-2">{{ $hist->estado }}</span>
                            <span class="text-sm font-bold text-azul-profundo">Actualizada el {{ \Carbon\Carbon::parse($hist->updated_at)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- MODAL CREAR/EDITAR FICHA --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
            <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto rounded-3xl bg-white shadow-2xl" @click.stop>
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-[#C7B5A3]/30 bg-white/90 backdrop-blur-md px-6 py-4">
                    <h2 class="text-lg font-black text-azul-profundo">
                        {{ $fichaActiva ? 'Editar Ficha Médica' : 'Nueva Ficha Médica' }}
                    </h2>
                    <button wire:click="closeModal" class="rounded-full p-2 text-azul-profundo/50 hover:bg-rose-50 hover:text-rose-500 transition-colors">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>

                <div class="p-6">
                    <form wire:submit.prevent="save">
                        <div class="grid gap-6 md:grid-cols-3 mb-6">
                            <div class="md:col-span-3">
                                <h3 class="text-xs font-black uppercase text-azul-profundo/50 mb-3 border-b border-[#C7B5A3]/20 pb-2">Patologías Crónicas</h3>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 hover:border-terracota transition-colors">
                                        <input type="checkbox" wire:model="hipertension" class="rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                                        <span class="text-sm font-bold text-azul-profundo">Hipertensión</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 hover:border-terracota transition-colors">
                                        <input type="checkbox" wire:model="diabetes" class="rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                                        <span class="text-sm font-bold text-azul-profundo">Diabetes</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 hover:border-terracota transition-colors">
                                        <input type="checkbox" wire:model="problemas_cardiacos" class="rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                                        <span class="text-sm font-bold text-azul-profundo">P. Cardíacos</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 hover:border-terracota transition-colors">
                                        <input type="checkbox" wire:model="acv" class="rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                                        <span class="text-sm font-bold text-azul-profundo">ACV</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 hover:border-terracota transition-colors">
                                        <input type="checkbox" wire:model="alzheimer_diagnosticado" class="rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                                        <span class="text-sm font-bold text-azul-profundo">Alzheimer</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer bg-[#F7F5F2] p-3 rounded-xl border border-[#C7B5A3]/30 hover:border-terracota transition-colors">
                                        <input type="checkbox" wire:model="depresion" class="rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                                        <span class="text-sm font-bold text-azul-profundo">Depresión</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2 mb-6">
                            <div>
                                <label class="block text-xs font-black uppercase text-azul-profundo mb-2">Alergias</label>
                                <textarea wire:model="alergias" rows="2" class="w-full rounded-xl border-[#C7B5A3]/40 bg-[#F7F5F2] focus:border-terracota focus:ring-terracota text-sm font-bold text-azul-profundo"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-azul-profundo mb-2">Restricciones Alimentarias</label>
                                <textarea wire:model="restricciones_alimentarias" rows="2" class="w-full rounded-xl border-[#C7B5A3]/40 bg-[#F7F5F2] focus:border-terracota focus:ring-terracota text-sm font-bold text-azul-profundo"></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black uppercase text-azul-profundo mb-2">Observaciones Médicas</label>
                                <textarea wire:model="observacion_medica" rows="3" class="w-full rounded-xl border-[#C7B5A3]/40 bg-[#F7F5F2] focus:border-terracota focus:ring-terracota text-sm font-bold text-azul-profundo"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-[#C7B5A3]/20">
                            <button type="button" wire:click="closeModal" class="rounded-xl px-5 py-2.5 text-xs font-black uppercase text-azul-profundo hover:bg-[#F7F5F2] transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-xl bg-terracota px-6 py-2.5 text-xs font-black uppercase text-white shadow-md hover:bg-terracota-dark transition-colors">
                                Guardar Ficha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
