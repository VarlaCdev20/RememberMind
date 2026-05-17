<div>
    <!-- Tarjeta Principal y Botón -->
    <div class="rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-[#2F3E5C]">Valoración Funcional</h3>
                <p class="text-sm text-[#2F3E5C]/60">Nivel de dependencia y autonomía del adulto mayor.</p>
            </div>
            <button wire:click="abrirModalValoracion('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl bg-[#8EA17D] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#617453] active:scale-95 transition">
                <i class="ph-bold ph-wheelchair"></i> Nueva Valoración
            </button>
        </div>

        @if(isset($valoracionList) && $valoracionList->isNotEmpty())
            <div class="mt-4 space-y-3">
                @foreach($valoracionList as $val)
                    <div class="rounded-xl border border-[#C7B5A3] bg-white p-4 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <div>
                            <span class="text-xs font-black uppercase text-[#2F3E5C]/55">
                                Valoración del {{ $val->fecha_valoracion->format('d/m/Y') }}
                            </span>
                            <div class="mt-1">
                                <span class="px-2 py-1 rounded-md text-xs font-black uppercase 
                                    {{ $val->nivel_dependencia === 'INDEPENDIENTE' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $val->nivel_dependencia === 'LEVE' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $val->nivel_dependencia === 'MODERADO' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $val->nivel_dependencia === 'SEVERO' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ $val->nivel_dependencia }}
                                </span>
                            </div>
                            @if($val->observacion)
                                <p class="text-xs text-[#2F3E5C]/60 mt-2 line-clamp-1" title="{{ $val->observacion }}">{{ $val->observacion }}</p>
                            @endif
                        </div>
                        <button wire:click="abrirModalValoracion('{{ $cod_am }}', {{ $val->cod_val_func }})" class="text-[#2F3E5C] bg-[#F8F2EC] hover:bg-[#D5C7B9] px-3 py-1.5 rounded-lg text-xs font-bold transition shrink-0">
                            <i class="ph-bold ph-pencil-simple mr-1"></i> Ver Detalles
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-4 text-center border-2 border-dashed border-[#C7B5A3] rounded-xl p-4 bg-white/50">
                <p class="text-sm font-bold text-[#2F3E5C]/60">No hay valoraciones funcionales registradas.</p>
            </div>
        @endif
    </div>

    <x-ui.modal-livewire wire:model="showModal" title="{{ $isEditing ? 'Editar Valoración Funcional' : 'Registrar Valoración Funcional' }}" maxWidth="3xl">
        <x-slot name="icon">
            <i class="ph-bold ph-wheelchair text-terracota"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formValoracion">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Fecha y Nivel -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <div class="grid gap-4 md:grid-cols-2 items-center">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha de Valoración *</label>
                            <input type="date" wire:model="fecha_valoracion"
                                class="w-full rounded-xl border {{ $errors->has('fecha_valoracion') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('fecha_valoracion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div class="text-right">
                            <span class="block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Nivel de Dependencia Calculado</span>
                            <div class="mt-1 inline-flex items-center rounded-xl border border-[#C7B5A3] bg-[#D5C7B9]/30 px-4 py-2">
                                <span class="text-lg font-black text-[#2F3E5C]">{{ $nivel_dependencia }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. Actividades Básicas -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">1. Actividades de la Vida Diaria</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="come_solo" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Come solo/a</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="se_bana_solo" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Se baña solo/a</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="se_viste_solo" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Se viste solo/a</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="va_bano_solo" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Va al baño solo/a</span>
                        </label>
                    </div>
                </div>

                <!-- 2. Movilidad y Apoyo -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">2. Movilidad y Apoyos</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="camina_solo" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Camina solo/a</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="usa_baston" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Usa bastón</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="usa_andador" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Usa andador</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="usa_silla_ruedas" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Usa silla de ruedas</span>
                        </label>
                    </div>
                </div>

                <!-- 3. Comunicación y Sensibilidad -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">3. Comunicación y Sentidos</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="baja_vision" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Baja visión</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="baja_audicion" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Baja audición</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="dificultad_hablar" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Dificultad para hablar</span>
                        </label>
                    </div>
                </div>

                <!-- 4. Cognitivo y Ambiental -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">4. Cognitivo y Ambiental</h4>
                    <div class="space-y-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="molestia_luz" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Molestia a la luz</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="molestia_ruido" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Molestia al ruido</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model.live="se_asusta_facil" class="h-4 w-4 rounded border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Se asusta fácil</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-terracota">
                            <input type="checkbox" wire:model.live="necesita_supervision" class="h-4 w-4 rounded border-terracota text-terracota focus:ring-terracota">
                            <span class="text-sm font-black text-terracota">Necesita Supervisión Constante</span>
                        </label>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">5. Observación Adicional</h4>
                    <textarea wire:model="observacion" rows="3" placeholder="Detalles de la valoración, comportamiento observado..."
                        class="w-full rounded-xl border border-[#C7B5A3] bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota"></textarea>
                    @error('observacion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] transition hover:bg-[#F8F2EC]">
                Cancelar
            </button>
            <button type="submit" form="formValoracion" class="inline-flex items-center justify-center gap-2 rounded-xl bg-terracota px-4 py-2 text-sm font-bold text-white transition hover:bg-[#C45F4B] active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $isEditing ? 'Actualizar Valoración' : 'Guardar Valoración' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
