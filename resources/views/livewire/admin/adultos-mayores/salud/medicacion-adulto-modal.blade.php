<div>
    <x-ui.modal-livewire wire:model="showModal" title="{{ $isEditing ? 'Editar Medicación' : 'Registrar Medicación' }}" maxWidth="3xl">
        <x-slot name="icon">
            <i class="ph-bold ph-pill text-boton-acento"></i>
        </x-slot>

        <form wire:submit.prevent="guardar" id="formMedicacion">
            <div class="grid gap-6 md:grid-cols-2">

                <!-- 1. Adulto Mayor -->
                <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">1</span>
                        Adulto Mayor
                    </h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Seleccionar Adulto Mayor *</label>
                            <div class="relative">
                                <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                <select wire:model.live="cod_am" class="w-full rounded-xl border {{ $errors->has('cod_am') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card py-2.5 pl-10 pr-4 text-xs font-bold text-titulo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15" {{ $isEditing ? 'disabled' : '' }}>
                                    <option value="">Seleccione a quién asignar la medicación</option>
                                    @foreach($adultosDisponibles as $ad)
                                        <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }} ({{ $ad->cod_am }})</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('cod_am') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        @if($adultoSeleccionado)
                        <div class="rounded-xl border border-borde-suave bg-fondo-card/60 p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-titulo">
                                <i class="ph-bold ph-info text-boton-acento"></i>
                                El paciente tiene actualmente <span class="text-estado-exito font-black mx-1">{{ $adultoSeleccionado->medicaciones()->where('estado', 'ACTIVO')->count() ?? 0 }}</span> medicamentos activos.
                            </div>
                            <a href="#" @click.prevent="$dispatch('cerrarModal'); $dispatch('cambiarAdulto', { cod_am: '{{ $adultoSeleccionado->cod_am }}' })" class="text-[10px] font-black uppercase tracking-wider text-boton-acento hover:underline">Ver historial</a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 2. Datos del Medicamento -->
                <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">2</span>
                        Datos del Medicamento
                    </h4>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Nombre del Medicamento *</label>
                            <div class="relative">
                                <i class="ph-bold ph-pill absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                <input type="text" wire:model="nombre_medicamento" placeholder="Ej. Losartán 50mg"
                                    class="w-full rounded-xl border {{ $errors->has('nombre_medicamento') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card py-2.5 pl-10 pr-4 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            </div>
                            @error('nombre_medicamento') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Dosis *</label>
                            <input type="text" wire:model="dosis" placeholder="Ej. 1 tableta, 5ml, 2 gotas"
                                class="w-full rounded-xl border {{ $errors->has('dosis') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('dosis') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Vía de Adm. *</label>
                            <select wire:model="via_administracion" class="w-full rounded-xl border {{ $errors->has('via_administracion') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="">Seleccione vía</option>
                                <option value="ORAL">Oral</option>
                                <option value="SUBLINGUAL">Sublingual</option>
                                <option value="TOPICA">Tópica</option>
                                <option value="OFTALMICA">Oftálmica</option>
                                <option value="OTICA">Ótica</option>
                                <option value="NASAL">Nasal</option>
                                <option value="INHALATORIA">Inhalatoria</option>
                                <option value="INTRAVENOSA">Intravenosa</option>
                                <option value="INTRAMUSCULAR">Intramuscular</option>
                                <option value="SUBCUTANEA">Subcutánea</option>
                                <option value="RECTAL">Rectal</option>
                                <option value="OTRA">Otra</option>
                            </select>
                            @error('via_administracion') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- 3. Horario y Tratamiento -->
                <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">3</span>
                        Frecuencia y Horarios
                    </h4>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Frecuencia *</label>
                            <input type="text" wire:model="frecuencia" placeholder="Ej. Cada 8 horas, 1 vez al día"
                                class="w-full rounded-xl border {{ $errors->has('frecuencia') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('frecuencia') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Hora Inicial *</label>
                            <input type="time" wire:model="hora_programada"
                                class="w-full rounded-xl border {{ $errors->has('hora_programada') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('hora_programada') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Fecha Inicio *</label>
                            <input type="date" wire:model="fecha_inicio"
                                class="w-full rounded-xl border {{ $errors->has('fecha_inicio') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('fecha_inicio') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Fecha Fin (Opcional)</label>
                            <input type="date" wire:model="fecha_fin"
                                class="w-full rounded-xl border {{ $errors->has('fecha_fin') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            @error('fecha_fin') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Estado del Tratamiento</label>
                            <select wire:model="estado" class="w-full rounded-xl border {{ $errors->has('estado') ? 'border-red-500' : 'border-borde-suave' }} bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition appearance-none focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                                <option value="ACTIVO">Activo</option>
                                <option value="PAUSADO">Pausado</option>
                                <option value="EN REVISION">En Revisión</option>
                                <option value="SUSPENDIDO">Suspendido</option>
                                <option value="FINALIZADO">Finalizado</option>
                            </select>
                            @error('estado') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- 4. Prescripción y Observación -->
                <div class="rounded-[1.4rem] border border-borde-suave bg-fondo-panel p-5 shadow-sm md:col-span-2 backdrop-blur-xl">
                    <h4 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-titulo">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-fondo-panel text-titulo">4</span>
                        Prescripción y Detalles
                    </h4>
                    <div class="grid gap-4">
                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Médico que indica / Prescripción</label>
                            <div class="relative">
                                <i class="ph-bold ph-stethoscope absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                <input type="text" wire:model="medico_indica" placeholder="Ej. Dr. Pérez - Control Cardiológico"
                                    class="w-full rounded-xl border border-borde-suave bg-fondo-card py-2.5 pl-10 pr-4 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15">
                            </div>
                            @error('medico_indica') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Observaciones Propias de la Medicación</label>
                            <textarea wire:model="observacion" rows="2" placeholder="Ej. Tomar después de las comidas, controlar presión antes de administrar..."
                                class="w-full rounded-xl border border-borde-suave bg-fondo-card px-3 py-2.5 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus focus:ring-2 focus:ring-[#E27D60]/15"></textarea>
                            @error('observacion') <span class="mt-1 text-xs text-red-500 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde-suave bg-fondo-panel px-5 py-2.5 text-xs font-black uppercase tracking-wider text-titulo transition hover:bg-fondo-panel active:scale-95">
                Cancelar
            </button>
            <button type="submit" form="formMedicacion" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-5 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-[0_8px_16px_rgba(226,125,96,0.2)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95 disabled:opacity-50" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <span>{{ $isEditing ? 'Actualizar Medicación' : 'Guardar Medicación' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
