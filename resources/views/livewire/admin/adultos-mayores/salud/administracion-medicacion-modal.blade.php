<div>
    <x-ui.modal-livewire wire:model="showModal" title="Registrar Administración de Medicación" maxWidth="2xl">
        <x-slot name="icon">
            <i class="ph-bold ph-calendar-check text-[#E27D60]"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formAdministracion">
            
            <!-- Resumen de Medicación -->
            <div class="mb-6 rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-[#E27D60]">Medicamento a Administrar</span>
                    <h3 class="mt-1 text-lg font-black text-[#2F3E5C]">{{ $medicamento_nombre ?: 'Sin Seleccionar' }}</h3>
                    <div class="mt-1 flex items-center gap-2 text-xs font-bold text-[#2F3E5C]/60">
                        <i class="ph-bold ph-clock"></i> Programado para las: {{ $hora_programada }}
                    </div>
                </div>
                <div class="h-12 w-12 flex items-center justify-center rounded-full bg-[#E27D60]/10 text-[#E27D60]">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- 1. Estado de Administración -->
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-white p-5 shadow-sm md:col-span-2 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full {{ $administrado ? 'bg-[#8DA280]' : 'bg-[#E27D60]' }} transition-colors duration-300"></div>
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C]">1</span>
                        Estado de la Toma
                    </h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="administrado" value="1" class="peer sr-only">
                            <div class="rounded-xl border-2 border-[#C7B5A3]/40 bg-[#F3ECE4]/40 p-4 text-center transition-all peer-checked:border-[#8DA280] peer-checked:bg-[#8DA280]/10 peer-checked:text-[#63775B] hover:bg-[#F3ECE4]">
                                <i class="ph-bold ph-check-circle text-2xl mb-1"></i>
                                <div class="text-xs font-black uppercase tracking-wider">Administrado</div>
                            </div>
                        </label>

                        <label class="cursor-pointer">
                            <input type="radio" wire:model.live="administrado" value="0" class="peer sr-only">
                            <div class="rounded-xl border-2 border-[#C7B5A3]/40 bg-[#F3ECE4]/40 p-4 text-center transition-all peer-checked:border-[#E27D60] peer-checked:bg-[#E27D60]/10 peer-checked:text-[#E27D60] hover:bg-[#F3ECE4]">
                                <i class="ph-bold ph-x-circle text-2xl mb-1"></i>
                                <div class="text-xs font-black uppercase tracking-wider">Omitido / Rechazado</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2. Fecha y Hora -->
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C]">2</span>
                        Detalle del Registro
                    </h4>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha de Toma *</label>
                            <input type="date" wire:model="fecha"
                                class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('fecha') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                        
                        @if($administrado)
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora Real de Toma *</label>
                            <input type="time" wire:model="hora_real"
                                class="w-full rounded-xl border {{ $errors->has('hora_real') ? 'border-red-500' : 'border-[#C7B5A3]/70' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora_real') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 3. Motivo de Omisión (Si no fue administrado) -->
                @if(!$administrado)
                <div class="rounded-[1.4rem] border border-[#E27D60]/30 bg-[#E27D60]/5 p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-[#E27D60]">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#E27D60]/20 text-[#E27D60]">3</span>
                        Motivo de Omisión / Rechazo *
                    </h4>
                    <textarea wire:model="motivo_omision" rows="2" placeholder="Ej. El paciente se negó a tomarlo, estaba dormido, etc."
                        class="w-full rounded-xl border {{ $errors->has('motivo_omision') ? 'border-red-500' : 'border-[#E27D60]/30' }} bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/20"></textarea>
                    @error('motivo_omision') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                </div>
                @endif

                <!-- 4. Observaciones Adicionales -->
                <div class="rounded-[1.4rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-[#2F3E5C]">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-[#2F3E5C]/10 text-[#2F3E5C]">{{ $administrado ? '3' : '4' }}</span>
                        Observaciones Adicionales
                    </h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        @if($administrado)
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Efecto Observado</label>
                            <input type="text" wire:model="efecto_observado" placeholder="Ej. Tolerancia adecuada"
                                class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('efecto_observado') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div class="{{ $administrado ? '' : 'md:col-span-2' }}">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Nota / Responsable</label>
                            <input type="text" wire:model="observacion" placeholder="Detalle extra"
                                class="w-full rounded-xl border border-[#C7B5A3]/70 bg-white px-3 py-2.5 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('observacion') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80 active:scale-95">
                Cancelar
            </button>
            <button type="submit" form="formAdministracion" class="inline-flex items-center justify-center gap-2 rounded-xl {{ $administrado ? 'bg-[#8DA280] shadow-[0_8px_16px_rgba(141,162,128,0.2)] hover:bg-[#63775B]' : 'bg-[#E27D60] shadow-[0_8px_16px_rgba(226,125,96,0.2)] hover:bg-[#D96F58]' }} px-5 py-2.5 text-xs font-black uppercase tracking-wider text-white transition hover:-translate-y-0.5 active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $administrado ? 'Confirmar Administración' : 'Registrar Omisión' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
