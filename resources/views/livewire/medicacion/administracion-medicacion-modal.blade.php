<div>
    <x-ui.modal-livewire id="modalAdministracionMedicacion" wire:model="showModal" maxWidth="2xl" closeMethod="cerrarModal">
        <x-slot name="icon">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-principal text-inverso shadow-sm">
                <i class="ph-bold ph-calendar-check text-lg"></i>
            </span>
        </x-slot>

        <x-slot name="title">
            <div class="flex items-center gap-2">
                <span>Registrar Administraci?n / Toma</span>
                <span class="rounded-md bg-boton-principal/15 px-2 py-0.5 text-[10px] font-bold text-boton-principal uppercase">
                    Control Asistencial
                </span>
            </div>
        </x-slot>

        <form wire:submit="guardar" id="formAdministracion" class="space-y-5">
            <!-- Resumen de Medicaci?n -->
            <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-boton-principal">Medicamento a Administrar</span>
                    <h3 class="mt-1 text-lg font-extrabold text-parrafo">{{ $medicamento_nombre ?: 'Sin Seleccionar' }}</h3>
                    <div class="mt-1 flex items-center gap-2 text-xs font-bold text-apoyo">
                        <i class="ph-bold ph-clock text-boton-principal"></i> Programado para las: <span class="text-parrafo">{{ $hora_programada }}</span>
                    </div>
                </div>
                <div class="h-12 w-12 flex items-center justify-center rounded-2xl bg-boton-principal/15 text-boton-principal shadow-sm">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </div>
            </div>

            <!-- 1. Estado de Administraci?n -->
            <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm space-y-3">
                <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-parrafo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-fondo-app text-[11px] font-extrabold text-apoyo">1</span>
                    Estado de la Toma
                </h4>
                
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="administrado" value="1" class="peer sr-only">
                        <div class="rounded-xl border-2 border-borde bg-fondo-app p-4 text-center transition-all peer-checked:border-estado-exitoBorde peer-checked:bg-estado-exitoBg peer-checked:text-estado-exito hover:bg-fondo-hover">
                            <i class="ph-bold ph-check-circle text-2xl mb-1 block"></i>
                            <div class="text-xs font-black uppercase tracking-wider">Administrado</div>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" wire:model.live="administrado" value="0" class="peer sr-only">
                        <div class="rounded-xl border-2 border-borde bg-fondo-app p-4 text-center transition-all peer-checked:border-estado-peligroBorde peer-checked:bg-estado-peligroBg peer-checked:text-estado-peligro hover:bg-fondo-hover">
                            <i class="ph-bold ph-x-circle text-2xl mb-1 block"></i>
                            <div class="text-xs font-black uppercase tracking-wider">Omitido / Rechazado</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. Detalle del Registro -->
            <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
                <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-parrafo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-fondo-app text-[11px] font-extrabold text-apoyo">2</span>
                    Detalle del Registro
                </h4>
                
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Fecha de Toma *</label>
                        <input type="date" wire:model="fecha"
                            class="w-full rounded-xl border {{ $errors->has('fecha') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('fecha') <span class="mt-1 block text-[10px] text-estado-peligro font-bold">{{ $message }}</span> @enderror
                    </div>
                    
                    @if($administrado)
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Hora Real de Toma *</label>
                            <input type="time" wire:model="hora_real"
                                class="w-full rounded-xl border {{ $errors->has('hora_real') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                            @error('hora_real') <span class="mt-1 block text-[10px] text-estado-peligro font-bold">{{ $message }}</span> @enderror
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Motivo de omisión (si no fue administrado) -->
            @if(!$administrado)
                <div class="rounded-[1.4rem] border border-estado-peligroBorde bg-estado-peligroBg p-5 shadow-sm space-y-3">
                    <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-estado-peligro">
                        <span class="flex h-5 w-5 items-center justify-center rounded-md bg-fondo-app text-[11px] font-extrabold text-estado-peligro">3</span>
                        Motivo de omisión / rechazo *
                    </h4>
                    <textarea wire:model="motivo_omision" rows="2" placeholder="Ej.: El residente se negó a recibirlo o presentó náuseas."
                        class="w-full rounded-xl border {{ $errors->has('motivo_omision') ? 'border-estado-peligroBorde' : 'border-borde' }} bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20"></textarea>
                    @error('motivo_omision') <span class="mt-1 block text-[10px] text-estado-peligro font-bold">{{ $message }}</span> @enderror
                </div>
            @endif

            <!-- 4. Observaciones Adicionales -->
            <div class="rounded-[1.4rem] border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
                <h4 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-parrafo">
                    <span class="flex h-5 w-5 items-center justify-center rounded-md bg-fondo-app text-[11px] font-extrabold text-apoyo">{{ $administrado ? '3' : '4' }}</span>
                    Observaciones Adicionales
                </h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    @if($administrado)
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Efecto Observado</label>
                            <input type="text" wire:model="efecto_observado" placeholder="Ej. Tolerancia adecuada, sin reacciones"
                                class="w-full rounded-xl border border-borde bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                            @error('efecto_observado') <span class="mt-1 block text-[10px] text-estado-peligro font-bold">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="{{ $administrado ? '' : 'sm:col-span-2' }}">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-parrafo/70">Nota de Turno</label>
                        <input type="text" wire:model="observacion" placeholder="Detalle complementario de enfermería"
                            class="w-full rounded-xl border border-borde bg-fondo-app px-3.5 py-2.5 text-xs font-bold text-parrafo outline-none transition focus:border-boton-principal focus:ring-2 focus:ring-boton-principal/20">
                        @error('observacion') <span class="mt-1 block text-[10px] text-estado-peligro font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-borde bg-fondo-app px-5 py-2.5 text-xs font-bold text-apoyo transition hover:bg-fondo-panel hover:text-parrafo active:scale-95 cursor-pointer">
                Cancelar
            </button>
            <button type="submit" form="formAdministracion" class="inline-flex items-center justify-center gap-2 rounded-xl {{ $administrado ? 'bg-boton-principal hover:bg-boton-principalHover' : 'bg-boton-acento hover:bg-boton-acentoHover' }} px-6 py-2.5 text-xs font-black uppercase tracking-wider text-inverso shadow-sm transition hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 cursor-pointer" wire:loading.attr="disabled">
                <i wire:loading wire:target="guardar" class="ph-bold ph-spinner animate-spin"></i>
                <i wire:loading.remove wire:target="guardar" class="ph-bold {{ $administrado ? 'ph-check-circle' : 'ph-x-circle' }}"></i>
                <span>{{ $administrado ? 'Confirmar toma' : 'Registrar omisión' }}</span>
            </button>
        </x-slot>
    </x-ui.modal-livewire>
</div>
