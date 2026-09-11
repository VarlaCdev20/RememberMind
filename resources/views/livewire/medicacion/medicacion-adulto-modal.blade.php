<div>
    <x-ui.modal-livewire id="modalMedicacionAdulto" wire:model="showModal" maxWidth="3xl" closeMethod="cerrarModal">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-sm">
                <i class="ph-bold ph-pill text-lg"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div class="flex items-center gap-2">
                <span>{{ $isEditing ? 'Editar medicación' : 'Registrar medicación' }}</span>
                <span class="rounded-md bg-boton-principal/15 px-2 py-0.5 text-[10px] font-bold text-boton-principal uppercase">
                    {{ $isEditing ? 'Modificación' : 'Nuevo fármaco' }}
                </span>
            </div>
        </x-slot>

        <form wire:submit="guardar" id="formMedicacion" class="space-y-5">
            <!-- 1. Paciente Asignado -->
            @if(empty($cod_am))
                <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm space-y-3">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">
                        Adulto Mayor / Residente <span class="text-estado-peligro">*</span>
                    </label>
                    <div class="relative">
                        <i class="ph-bold ph-user absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                        <select wire:model="cod_am" class="w-full rounded-xl border {{ $errors->has('cod_am') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                            <option value="">-- Seleccione un residente --</option>
                            @foreach($adultosDisponibles as $ad)
                                <option value="{{ $ad->cod_am }}">{{ $ad->nombres }} {{ $ad->ap_paterno }} ({{ $ad->cod_am }})</option>
                            @endforeach
                        </select>
                    </div>
                    @error('cod_am') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- 2. Fármaco y vía -->
            <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
                <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-parrafo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-fondo-app text-[11px] font-extrabold text-apoyo">1</span>
                    Datos del Medicamento
                </h4>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-3">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Nombre del Medicamento / Principio Activo *</label>
                        <div class="relative">
                            <i class="ph-bold ph-pill absolute left-3.5 top-1/2 -translate-y-1/2 text-meta"></i>
                            <input type="text" wire:model="nombre_medicamento" placeholder="Ej: Enalapril, Paracetamol, Metformina"
                                class="w-full rounded-xl border {{ $errors->has('nombre_medicamento') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app py-2.5 pl-10 pr-4 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        </div>
                        @error('nombre_medicamento') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Dosis *</label>
                        <input type="text" wire:model="dosis" placeholder="Ej: 50mg, 1 comp."
                            class="w-full rounded-xl border {{ $errors->has('dosis') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('dosis') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Vía de administración *</label>
                        <select wire:model="via_administracion" class="w-full rounded-xl border {{ $errors->has('via_administracion') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
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
                        @error('via_administracion') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Estado del Tratamiento</label>
                        <select wire:model="estado" class="w-full rounded-xl border {{ $errors->has('estado') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition appearance-none focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                            <option value="ACTIVO">Activo</option>
                            <option value="PAUSADO">Pausado</option>
                            <option value="EN REVISION">En revisión</option>
                            <option value="SUSPENDIDO">Suspendido</option>
                            <option value="FINALIZADO">Finalizado</option>
                        </select>
                        @error('estado') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- 3. Frecuencia y Horario -->
            <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
                <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-parrafo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-fondo-app text-[11px] font-extrabold text-apoyo">2</span>
                    Frecuencia y Horarios
                </h4>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Frecuencia *</label>
                        <input type="text" wire:model="frecuencia" placeholder="Ej.: Cada 12 horas, una vez al día con desayuno"
                            class="w-full rounded-xl border {{ $errors->has('frecuencia') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('frecuencia') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Hora Programada *</label>
                        <input type="time" wire:model="hora_programada"
                            class="w-full rounded-xl border {{ $errors->has('hora_programada') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('hora_programada') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Fecha Inicio *</label>
                        <input type="date" wire:model="fecha_inicio"
                            class="w-full rounded-xl border {{ $errors->has('fecha_inicio') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('fecha_inicio') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Fecha Fin (Opcional)</label>
                        <input type="date" wire:model="fecha_fin"
                            class="w-full rounded-xl border {{ $errors->has('fecha_fin') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('fecha_fin') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Médico prescriptor</label>
                        <input type="text" wire:model="medico_indica" placeholder="Ej: Dr. Roberto Mendoza"
                            class="w-full rounded-xl border border-borde bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('medico_indica') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Indicaciones Especiales / Observaciones</label>
                    <textarea wire:model="observacion" rows="2" placeholder="Ej.: Administrar con abundante agua después de las comidas. Monitorear pulso."
                        class="w-full rounded-xl border border-borde bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20"></textarea>
                    @error('observacion') <span class="mt-1 block text-[10px] font-bold text-estado-peligro">{{ $message }}</span> @enderror
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde bg-fondo-app px-5 py-2.5 text-xs font-bold text-apoyo transition hover:bg-fondo-panel hover:text-parrafo active:scale-95 cursor-pointer">
                Cancelar
            </button>
            <button type="submit" form="formMedicacion" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-6 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-sm transition hover:bg-boton-principalHover hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 cursor-pointer" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <i wire:loading.remove wire:target="guardar" class="ph-bold ph-check-circle"></i>
                <span>{{ $isEditing ? 'Actualizar prescripción' : 'Guardar prescripción' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
