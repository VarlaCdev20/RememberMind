<div>
    <!-- Tarjeta de Historial Reciente de Tomas -->
    <div class="rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-[#2F3E5C]">Últimas Administraciones</h3>
                <p class="text-sm text-[#2F3E5C]/60">Registro reciente de tomas u omisiones.</p>
            </div>
            <!-- Botón opcional si se quisiera registrar sin estar en la medicación -->
        </div>

        @if(isset($administracionList) && $administracionList->isNotEmpty())
            <div class="mt-4 space-y-3">
                @foreach($administracionList as $admin)
                    <div class="rounded-xl border border-[#C7B5A3] bg-white p-3 shadow-sm flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center {{ $admin->administrado ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                <i class="ph-bold {{ $admin->administrado ? 'ph-check-circle' : 'ph-x-circle' }} text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-[#2F3E5C] text-sm">{{ $admin->medicacion?->nombre_medicamento ?? 'Medicación Desconocida' }}</h4>
                                <p class="text-xs font-bold text-[#2F3E5C]/60">
                                    {{ $admin->fecha->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($admin->hora_real ?? $admin->hora_programada)->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        @if(!$admin->administrado)
                            <div class="text-right">
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-md text-[10px] font-black uppercase">Omitido</span>
                                <p class="text-[10px] text-[#2F3E5C]/60 mt-1 max-w-[120px] truncate" title="{{ $admin->motivo_omision }}">{{ $admin->motivo_omision }}</p>
                            </div>
                        @else
                            <div class="text-right">
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-md text-[10px] font-black uppercase">Administrado</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-4 text-center border-2 border-dashed border-[#C7B5A3] rounded-xl p-4 bg-white/50">
                <p class="text-sm font-bold text-[#2F3E5C]/60">No hay registros de administración recientes.</p>
            </div>
        @endif
    </div>

    <x-ui.modal-livewire wire:model="showModal" title="Registro de Administración" maxWidth="2xl">
        <x-slot name="icon">
            <i class="ph-bold ph-calendar-check text-terracota"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formAdministracion">
            <div class="mb-4 rounded-xl border border-terracota/30 bg-terracota/10 px-4 py-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-terracota">Medicamento</span>
                <p class="text-sm font-bold text-[#2F3E5C]">{{ $medicamento_nombre }}</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Tipo de Registro -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">1. ¿Se administró el medicamento?</h4>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model.live="administrado" value="1" class="h-4 w-4 border-[#C7B5A3] text-terracota focus:ring-terracota">
                            <span class="text-sm font-bold text-[#2F3E5C]">Sí, Administrado</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-red-600">
                            <input type="radio" wire:model.live="administrado" value="0" class="h-4 w-4 border-[#C7B5A3] text-red-600 focus:ring-red-600">
                            <span class="text-sm font-bold text-red-600">No (Omisión)</span>
                        </label>
                    </div>
                </div>

                <!-- Detalles de Fecha y Hora -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">2. Fecha y Hora</h4>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha *</label>
                            <input type="date" wire:model="fecha"
                                class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('fecha') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora Prog. *</label>
                            <input type="time" wire:model="hora_programada"
                                class="w-full rounded-xl border {{ $errors->has('hora_programada') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('hora_programada') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        @if($administrado)
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora Real *</label>
                            <input type="time" wire:model="hora_real"
                                class="w-full rounded-xl border {{ $errors->has('hora_real') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('hora_real') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Motivo de Omisión -->
                @if(!$administrado)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-red-700">3. Motivo de Omisión *</h4>
                    <textarea wire:model="motivo_omision" rows="2" placeholder="Ej. El paciente se negó, dormido, ausente..."
                        class="w-full rounded-xl border {{ $errors->has('motivo_omision') ? 'border-red-500' : 'border-red-300' }} bg-white px-3 py-2 text-sm text-red-900 focus:border-red-500 focus:ring-red-500"></textarea>
                    @error('motivo_omision') <span class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                </div>
                @endif

                <!-- Observaciones Adicionales -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">{{ $administrado ? '3. Observaciones' : '4. Observaciones' }}</h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        @if($administrado)
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Efecto Observado</label>
                            <input type="text" wire:model="efecto_observado" placeholder="Ej. Sin reacciones adversas"
                                class="w-full rounded-xl border border-[#C7B5A3] bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('efecto_observado') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        <div class="{{ $administrado ? '' : 'md:col-span-2' }}">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Nota Adicional</label>
                            <input type="text" wire:model="observacion" placeholder="Detalle extra"
                                class="w-full rounded-xl border border-[#C7B5A3] bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('observacion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] transition hover:bg-[#F8F2EC]">
                Cancelar
            </button>
            <button type="submit" form="formAdministracion" class="inline-flex items-center justify-center gap-2 rounded-xl {{ $administrado ? 'bg-terracota hover:bg-[#C45F4B]' : 'bg-red-600 hover:bg-red-700' }} px-4 py-2 text-sm font-bold text-white transition active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $administrado ? 'Registrar Administración' : 'Registrar Omisión' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
