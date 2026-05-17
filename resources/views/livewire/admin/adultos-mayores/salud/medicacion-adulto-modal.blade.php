<div>
    <!-- Tarjeta Principal y Botón -->
    <div class="rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-xl font-black text-[#2F3E5C]">Medicación Activa</h3>
                <p class="text-sm text-[#2F3E5C]/60">Prescripciones y control de administración.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="abrirModalMedicacion('{{ $cod_am }}')" class="inline-flex items-center gap-2 rounded-xl border border-[#C45F4B] bg-white px-4 py-2 text-sm font-bold text-[#C45F4B] shadow-sm hover:bg-[#C45F4B]/10 active:scale-95 transition">
                    <i class="ph-bold ph-plus"></i> Nueva Receta
                </button>
            </div>
        </div>

        <!-- Lista de Medicación -->
        @if($medicacionList->isNotEmpty())
            <div class="mt-4 space-y-3">
                @foreach($medicacionList as $med)
                    <div class="rounded-xl border border-[#C7B5A3] bg-white p-4 shadow-sm flex flex-col lg:flex-row justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-black text-[#2F3E5C] text-md">{{ $med->nombre_medicamento }}</h4>
                                <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $med->estado === 'ACTIVO' ? 'bg-green-100 text-green-700' : ($med->estado === 'SUSPENDIDO' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $med->estado }}
                                </span>
                            </div>
                            <p class="text-xs font-bold text-[#2F3E5C]/70">
                                {{ $med->dosis }} · {{ $med->via_administracion }} · {{ $med->frecuencia }} 
                                <br>
                                <i class="ph-bold ph-clock"></i> {{ \Carbon\Carbon::parse($med->hora_programada)->format('H:i') }} | 
                                <i class="ph-bold ph-calendar"></i> {{ $med->fecha_inicio->format('d/m/Y') }} {{ $med->fecha_fin ? ' al '.$med->fecha_fin->format('d/m/Y') : '(Indefinido)' }}
                            </p>
                            @if($med->observacion)
                                <p class="text-xs text-[#2F3E5C]/50 mt-1 italic"><i class="ph-bold ph-info"></i> {{ $med->observacion }}</p>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2 items-end shrink-0">
                            <div class="flex items-center gap-2">
                                <button wire:click="abrirModalMedicacion('{{ $cod_am }}', {{ $med->cod_med_adulto }})" class="text-[#2F3E5C] bg-[#F8F2EC] hover:bg-[#D5C7B9] px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition">
                                    <i class="ph-bold ph-pencil-simple"></i> Editar
                                </button>
                                @if($med->estado === 'ACTIVO')
                                    <button wire:click="cambiarEstado({{ $med->cod_med_adulto }}, 'SUSPENDIDO')" wire:confirm="¿Seguro de suspender esta medicación?" class="text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition">
                                        <i class="ph-bold ph-stop-circle"></i> Suspender
                                    </button>
                                @elseif($med->estado === 'SUSPENDIDO')
                                    <button wire:click="cambiarEstado({{ $med->cod_med_adulto }}, 'ACTIVO')" wire:confirm="¿Reanudar medicación?" class="text-green-700 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition">
                                        <i class="ph-bold ph-play-circle"></i> Reanudar
                                    </button>
                                @endif
                                <button wire:click="cambiarEstado({{ $med->cod_med_adulto }}, 'FINALIZADO')" wire:confirm="¿Marcar como finalizado?" class="text-[#617453] bg-[#8EA17D]/20 hover:bg-[#8EA17D]/40 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition">
                                    <i class="ph-bold ph-check-circle"></i> Finalizar
                                </button>
                            </div>
                            <button wire:click="$dispatch('abrirModalAdministracion', { cod_am: '{{ $cod_am }}', cod_med_adulto: {{ $med->cod_med_adulto }} })" class="inline-flex items-center gap-2 rounded-xl bg-terracota px-4 py-2 text-xs font-black text-white shadow-sm hover:bg-[#C45F4B] transition w-full justify-center">
                                <i class="ph-bold ph-check-square"></i> Registrar Toma
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-4 text-center border-2 border-dashed border-[#C7B5A3] rounded-xl p-4 bg-white/50">
                <p class="text-sm font-bold text-[#2F3E5C]/60">No hay medicación activa.</p>
            </div>
        @endif
    </div>

    <x-ui.modal-livewire wire:model="showModal" title="{{ $isEditing ? 'Editar Medicación' : 'Registrar Medicación' }}" maxWidth="3xl">
        <x-slot name="icon">
            <i class="ph-bold ph-pill text-terracota"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formMedicacion">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Datos del Medicamento -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">1. Datos del Medicamento</h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Nombre del Medicamento *</label>
                            <input type="text" wire:model="nombre_medicamento" placeholder="Ej. Losartán"
                                class="w-full rounded-xl border {{ $errors->has('nombre_medicamento') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('nombre_medicamento') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Dosis *</label>
                            <input type="text" wire:model="dosis" placeholder="Ej. 50mg"
                                class="w-full rounded-xl border {{ $errors->has('dosis') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('dosis') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Vía de Adm. *</label>
                            <select wire:model="via_administracion" class="w-full rounded-xl border {{ $errors->has('via_administracion') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                                <option value="">Seleccione vía</option>
                                <option value="ORAL">Oral</option>
                                <option value="SUBLINGUAL">Sublingual</option>
                                <option value="TOPICA">Tópica</option>
                                <option value="OFTALMICA">Oftálmica</option>
                                <option value="OTICA">Ótica</option>
                                <option value="INHALATORIA">Inhalatoria</option>
                                <option value="INTRAVENOSA">Intravenosa</option>
                                <option value="INTRAMUSCULAR">Intramuscular</option>
                                <option value="SUBCUTANEA">Subcutánea</option>
                                <option value="OTRA">Otra</option>
                            </select>
                            @error('via_administracion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Horario y Frecuencia -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">2. Horario y Tratamiento</h4>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Frecuencia *</label>
                            <input type="text" wire:model="frecuencia" placeholder="Ej. Cada 12 horas"
                                class="w-full rounded-xl border {{ $errors->has('frecuencia') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('frecuencia') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Hora Inicial *</label>
                            <input type="time" wire:model="hora_programada"
                                class="w-full rounded-xl border {{ $errors->has('hora_programada') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('hora_programada') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha Inicio *</label>
                            <input type="date" wire:model="fecha_inicio"
                                class="w-full rounded-xl border {{ $errors->has('fecha_inicio') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('fecha_inicio') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Fecha Fin (Opcional)</label>
                            <input type="date" wire:model="fecha_fin"
                                class="w-full rounded-xl border {{ $errors->has('fecha_fin') ? 'border-terracota' : 'border-[#C7B5A3]' }} bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('fecha_fin') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Médico Indincante</label>
                            <input type="text" wire:model="medico_indica" placeholder="Ej. Dr. Pérez"
                                class="w-full rounded-xl border border-[#C7B5A3] bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota">
                            @error('medico_indica') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="rounded-xl border border-[#C7B5A3] bg-white/50 p-4 shadow-sm md:col-span-2">
                    <h4 class="mb-3 text-sm font-black uppercase text-[#2F3E5C]">3. Observación</h4>
                    <textarea wire:model="observacion" rows="2" placeholder="Detalles, recomendaciones, toma con comida..."
                        class="w-full rounded-xl border border-[#C7B5A3] bg-[#F8F2EC] px-3 py-2 text-sm text-[#2F3E5C] focus:border-terracota focus:ring-terracota"></textarea>
                    @error('observacion') <span class="mt-1 text-xs text-terracota font-bold">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-[#C7B5A3] bg-white px-4 py-2 text-sm font-bold text-[#2F3E5C] transition hover:bg-[#F8F2EC]">
                Cancelar
            </button>
            <button type="submit" form="formMedicacion" class="inline-flex items-center justify-center gap-2 rounded-xl bg-terracota px-4 py-2 text-sm font-bold text-white transition hover:bg-[#C45F4B] active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $isEditing ? 'Actualizar Medicación' : 'Guardar Medicación' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
