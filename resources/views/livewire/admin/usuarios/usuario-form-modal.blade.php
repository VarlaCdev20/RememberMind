<div>
    @if($mostrar)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="w-full max-w-2xl rounded-2xl bg-fondo-card shadow-2xl relative">
            {{-- Header --}}
            <div class="border-b border-borde-suave bg-fondo-panel px-6 py-4 rounded-t-2xl flex items-center justify-between">
                <h3 class="text-xl font-black text-parrafo">
                    {{ $isEdit ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h3>
                <button type="button" wire:click="cerrar" class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-panel text-parrafo transition hover:bg-boton-principal hover:text-inverso active:scale-95">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
        <div class="grid gap-4 md:grid-cols-2 p-1">
            <div class="md:col-span-2">
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Nombres *</label>
                <input type="text" wire:model="nombres"
                       class="w-full rounded-xl border {{ $errors->has('nombres') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                @error('nombres') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Apellido Paterno</label>
                <input type="text" wire:model="ap_paterno"
                       class="w-full rounded-xl border {{ $errors->has('ap_paterno') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                @error('ap_paterno') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Apellido Materno</label>
                <input type="text" wire:model="ap_materno"
                       class="w-full rounded-xl border {{ $errors->has('ap_materno') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                @error('ap_materno') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Correo Electrónico *</label>
                <input type="email" wire:model="correo"
                       class="w-full rounded-xl border {{ $errors->has('correo') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                @error('correo') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Teléfono</label>
                <input type="text" wire:model="telefono"
                       class="w-full rounded-xl border {{ $errors->has('telefono') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                @error('telefono') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Rol de Usuario *</label>
                <select wire:model="rol"
                        class="w-full rounded-xl border {{ $errors->has('rol') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                    <option value="">Seleccionar rol</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ strtoupper(str_replace('_', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
                @error('rol') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Estado *</label>
                <select wire:model="estado"
                        class="w-full rounded-xl border {{ $errors->has('estado') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
                @error('estado') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2 pt-2 border-t border-borde-suave mt-2">
                <p class="text-xs font-black text-parrafo mb-3"><i class="ph-bold ph-lock-key mr-1"></i> Seguridad</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Contraseña {{ $isEdit ? '(Opcional)' : '*' }}</label>
                        <input type="password" wire:model="password"
                               class="w-full rounded-xl border {{ $errors->has('password') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                        @error('password') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-apoyo">Confirmar Contraseña</label>
                        <input type="password" wire:model="password_confirmation"
                               class="w-full rounded-xl border {{ $errors->has('password_confirmation') ? 'border-borde-focus ring-4 ring-[#E27D60]/10 bg-estado-peligroBg' : 'border-borde bg-fondo-panel' }} px-3.5 py-2.5 text-sm font-bold text-parrafo outline-none transition focus:border-borde-focus focus:bg-fondo-app focus:ring-4 focus:ring-[#E27D60]/10">
                        @error('password_confirmation') <span class="mt-1 text-xs font-black text-boton-acento">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

            </div>

            {{-- Footer --}}
            <div class="border-t border-borde-suave bg-fondo-panel px-6 py-4 rounded-b-2xl flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="button" wire:click="cerrar" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-fondo-app px-4 py-2.5 text-xs font-black text-parrafo transition hover:bg-fondo-panel active:scale-95">
                    Cancelar
                </button>
                <button type="button" wire:click="guardar" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-6 py-2.5 text-xs font-black text-inverso shadow-[0_8px_18px_rgba(233,122,95,0.25)] transition hover:bg-fondo-panel hover:-translate-y-0.5 active:scale-95">
                    <span wire:loading.remove wire:target="guardar">
                        <i class="ph-bold ph-floppy-disk"></i> Guardar Usuario
                    </span>
                    <span wire:loading wire:target="guardar">
                        <i class="ph-bold ph-spinner animate-spin"></i> Guardando...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
