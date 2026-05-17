<div>
    @if($mostrar)
    <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl relative">
            {{-- Header --}}
            <div class="border-b border-[#D5C7B9] bg-[#E6DDD3]/50 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                <h3 class="text-xl font-black text-[#2F3E5C]">
                    {{ $isEdit ? 'Editar Usuario' : 'Nuevo Usuario' }}
                </h3>
                <button type="button" wire:click="cerrar" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#D5C7B9]/50 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white active:scale-95">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
        <div class="grid gap-4 md:grid-cols-2 p-1">
            <div class="md:col-span-2">
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Nombres *</label>
                <input type="text" wire:model="nombres"
                       class="w-full rounded-xl border {{ $errors->has('nombres') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                @error('nombres') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Apellido Paterno</label>
                <input type="text" wire:model="ap_paterno"
                       class="w-full rounded-xl border {{ $errors->has('ap_paterno') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                @error('ap_paterno') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Apellido Materno</label>
                <input type="text" wire:model="ap_materno"
                       class="w-full rounded-xl border {{ $errors->has('ap_materno') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                @error('ap_materno') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Correo Electrónico *</label>
                <input type="email" wire:model="correo"
                       class="w-full rounded-xl border {{ $errors->has('correo') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                @error('correo') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Teléfono</label>
                <input type="text" wire:model="telefono"
                       class="w-full rounded-xl border {{ $errors->has('telefono') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                @error('telefono') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Rol de Usuario *</label>
                <select wire:model="rol"
                        class="w-full rounded-xl border {{ $errors->has('rol') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                    <option value="">Seleccionar rol</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}">{{ strtoupper(str_replace('_', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
                @error('rol') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Estado *</label>
                <select wire:model="estado"
                        class="w-full rounded-xl border {{ $errors->has('estado') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
                @error('estado') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2 pt-2 border-t border-[#C7B5A3]/30 mt-2">
                <p class="text-xs font-black text-[#2F3E5C] mb-3"><i class="ph-bold ph-lock-key mr-1"></i> Seguridad</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Contraseña {{ $isEdit ? '(Opcional)' : '*' }}</label>
                        <input type="password" wire:model="password"
                               class="w-full rounded-xl border {{ $errors->has('password') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                        @error('password') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-[11px] font-black uppercase tracking-widest text-[#2F3E5C]/60">Confirmar Contraseña</label>
                        <input type="password" wire:model="password_confirmation"
                               class="w-full rounded-xl border {{ $errors->has('password_confirmation') ? 'border-[#E27D60] ring-4 ring-[#E27D60]/10 bg-[#E27D60]/5' : 'border-[#C7B5A3] bg-[#D5C7B9]/70' }} px-3.5 py-2.5 text-sm font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:bg-[#E6DDD3] focus:ring-4 focus:ring-[#E27D60]/10">
                        @error('password_confirmation') <span class="mt-1 text-xs font-black text-[#E27D60]">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

            </div>

            {{-- Footer --}}
            <div class="border-t border-[#D5C7B9] bg-[#E6DDD3]/30 px-6 py-4 rounded-b-2xl flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="button" wire:click="cerrar" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D5C7B9] px-4 py-2.5 text-xs font-black text-[#2F3E5C] transition hover:bg-[#C7B5A3] active:scale-95">
                    Cancelar
                </button>
                <button type="button" wire:click="guardar" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-6 py-2.5 text-xs font-black text-white shadow-[0_8px_18px_rgba(233,122,95,0.25)] transition hover:bg-[#D96F58] hover:-translate-y-0.5 active:scale-95">
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
