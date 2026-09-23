@if ($modalAbierto)
            <div class="fixed inset-0 z-[70] flex items-center justify-center bg-titulo/40 p-4 backdrop-blur-sm">
                <div class="max-h-[86vh] w-full max-w-4xl flex flex-col overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-panel shadow-2xl">
                    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-borde-suave p-5">
                        <div>
                            <h2 class="text-lg font-black text-titulo">Nueva asignación institucional</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">Seleccione el personal. El registro definitivo se conectará al flujo de asignación correspondiente.</p>
                        </div>
                        <button type="button" wire:click="cerrarModal"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-fondo-card text-titulo transition hover:bg-boton-acento hover:text-inverso">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>

                    <div class="p-5 overflow-y-auto">
                        @if ($usuarioSeleccionadoData)
                            <div class="rounded-2xl border border-borde-suave bg-fondo-card/35 p-4">
                                <div class="flex flex-col gap-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Personal seleccionado</p>
                                            <p class="mt-2 text-base font-black text-titulo">{{ $usuarioSeleccionadoData->nombres }} {{ $usuarioSeleccionadoData->ap_paterno }}</p>
                                            <p class="text-xs font-bold text-apoyo">{{ $usuarioSeleccionadoData->correo }}</p>
                                        </div>
                                        <button type="button" wire:click="$set('usuarioSeleccionado', null)" class="text-[11px] font-bold text-boton-acento hover:text-boton-acento/80 transition">
                                            Cambiar personal
                                        </button>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <livewire:identidad.personal-institucional-horarios 
                                            :usuario-id="$usuarioSeleccionadoData->cod_usu" 
                                            :abrir-formulario-inicial="true"
                                            wire:key="horario-modal-{{ $usuarioSeleccionadoData->cod_usu }}" 
                                        />
                                    </div>

                                    <div class="mt-2 flex justify-end border-t border-borde-suave pt-4">
                                        <button type="button" wire:click="cerrarModal" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black uppercase tracking-wide text-inverso transition hover:bg-boton-principal/90">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="relative">
                                <i class="ph-bold ph-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-apoyo"></i>
                                <input type="search" wire:model.live.debounce.300ms="busquedaModal"
                                    class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/40 pl-9 pr-3 text-xs font-bold text-titulo outline-none transition focus:border-borde-focus"
                                    placeholder="Buscar personal para asignar...">
                            </div>

                            <div class="mt-4 max-h-[420px] space-y-2 overflow-y-auto pr-1">
                                @forelse ($personalModal as $usuario)
                                    <button type="button" wire:click="seleccionarUsuario('{{ $usuario->cod_usu }}')"
                                        class="w-full rounded-2xl border border-borde-suave bg-fondo-card/35 p-3 text-left transition hover:border-borde-focus hover:bg-fondo-card">
                                        <p class="text-sm font-black text-titulo">{{ $usuario->nombres }} {{ $usuario->ap_paterno }}</p>
                                        <p class="text-xs font-bold text-apoyo">{{ $usuario->correo }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach ($usuario->roles as $rol)
                                                <span class="rounded-full bg-fondo-panel px-2 py-0.5 text-[9px] font-black text-apoyo">{{ $rol->name }}</span>
                                            @endforeach
                                        </div>
                                    </button>
                                @empty
                                    <p class="rounded-2xl border border-dashed border-borde-suave p-8 text-center text-sm font-bold text-apoyo">No se encontraron usuarios disponibles.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
