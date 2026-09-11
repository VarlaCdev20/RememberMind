@if ($modalAsignarPlazaAbierto)
            <div class="fixed inset-0 z-[80] flex items-center justify-center bg-titulo/40 p-4 backdrop-blur-sm">
                <div class="w-full max-w-lg overflow-hidden rounded-[1.5rem] border border-borde-suave bg-fondo-panel shadow-2xl">
                    <div class="flex items-center justify-between border-b border-borde-suave p-5">
                        <div>
                            <h2 class="text-lg font-black text-titulo">Asignar Enfermero a Plaza</h2>
                            <p class="mt-1 text-xs font-bold text-apoyo">Gestión de la plaza {{ $plazaSeleccionada }}</p>
                        </div>
                        <button type="button" wire:click="$set('modalAsignarPlazaAbierto', false)"
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-fondo-card text-titulo transition hover:bg-boton-acento hover:text-inverso">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="guardarAsignacionPlaza" class="p-5 space-y-4">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-apoyo">Plaza Operativa</label>
                            <input type="text" value="{{ $plazaSeleccionada }}" disabled
                                class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/30 px-3 text-xs font-bold text-apoyo outline-none">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-apoyo">Tipo de Asignación</label>
                            <select wire:model.live="tipoAsignacion"
                                class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                <option value="TITULAR">Titular regular (Permanente)</option>
                                <option value="REEMPLAZO">Reemplazo temporal (Por fecha)</option>
                                <option value="APOYO">Apoyo / Volante (Por fecha)</option>
                                <option value="DESCANSO">Descanso programado (Por fecha)</option>
                            </select>
                        </div>

                        @if ($tipoAsignacion !== 'DESCANSO')
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-apoyo">Seleccionar Enfermero</label>
                                <select wire:model="enfermeroSeleccionado"
                                    class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/40 px-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus">
                                    <option value="">Seleccione un enfermero...</option>
                                    @foreach (\App\Models\User::role('ENFERMEROS')->where('estado', 'ACTIVO')->orderBy('nombres')->get() as $nurse)
                                        <option value="{{ $nurse->cod_usu }}">{{ $nurse->name }} ({{ $nurse->cod_usu }})</option>
                                    @endforeach
                                </select>
                                @error('enfermeroSeleccionado')
                                    <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        @if ($tipoAsignacion !== 'TITULAR')
                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-apoyo">Fecha del Turno</label>
                                <input type="text" value="{{ \Carbon\Carbon::parse($fechaSeleccionadaPlaza)->format('d/m/Y') }}" disabled
                                    class="h-10 w-full rounded-xl border border-borde-suave bg-fondo-card/30 px-3 text-xs font-bold text-apoyo outline-none">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-apoyo">Motivo / Observación</label>
                                <textarea wire:model="motivoAsignacion" rows="3" placeholder="Ej. cobertura por baja médica, refuerzo de fin de semana..."
                                    class="w-full rounded-xl border border-borde-suave bg-fondo-card/40 p-3 text-xs font-bold text-titulo outline-none focus:border-borde-focus"></textarea>
                                @error('motivoAsignacion')
                                    <span class="mt-1 block text-xs font-bold text-estado-peligro">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 border-t border-borde-suave pt-4 shrink-0">
                            <button type="button" wire:click="$set('modalAsignarPlazaAbierto', false)"
                                class="rounded-xl border border-borde-suave bg-fondo-card/45 px-4 py-2 text-xs font-black uppercase tracking-wider text-titulo hover:bg-fondo-card">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="rounded-xl bg-boton-acento px-4 py-2 text-xs font-black uppercase tracking-wider text-inverso hover:bg-boton-acento/90">
                                Guardar Asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
