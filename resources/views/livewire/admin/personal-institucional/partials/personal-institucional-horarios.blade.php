<div class="space-y-6">
    @if($tipoPersonal === 'ninguno')
        <div class="flex flex-col items-center justify-center p-10 text-apoyo border-2 border-dashed border-borde rounded-2xl">
            <i class="ph-fill ph-calendar-slash text-5xl mb-4 opacity-50"></i>
            <h4 class="text-lg font-bold text-titulo">No requiere horarios</h4>
            <p class="text-sm font-semibold mt-2 text-center max-w-md">Este usuario no está clasificado como Personal de Salud ni como Personal Administrativo, por lo tanto no maneja carga horaria operativa.</p>
        </div>
    @else
        <!-- Formulario para agregar horario -->
        <div class="bg-fondo-hover p-4 rounded-2xl border border-borde">
            <h4 class="text-sm font-bold text-titulo mb-3 flex items-center gap-2">
                <i class="ph-bold ph-calendar-plus"></i> Programar Carga Horaria
            </h4>
            <form wire:submit.prevent="agregarHorario" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Día de Semana</label>
                    <select wire:model="dia_semana" class="w-full rounded-xl border-input-borde bg-white text-sm" required>
                        <option value="">Seleccione...</option>
                        @foreach($dias as $dia)
                            <option value="{{ $dia }}">{{ $dia }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Hora Inicio</label>
                    <input type="time" wire:model="hora_inicio" class="w-full rounded-xl border-input-borde bg-white text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Hora Fin</label>
                    <input type="time" wire:model="hora_fin" class="w-full rounded-xl border-input-borde bg-white text-sm" required>
                </div>
                @if($tipoPersonal === 'admin')
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Turno Institucional</label>
                    <select wire:model="cod_turno_inst" class="w-full rounded-xl border-input-borde bg-white text-sm">
                        <option value="">Ninguno</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno->cod_turno_inst }}">{{ $turno->nombre_turno }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="{{ $tipoPersonal === 'admin' ? '' : 'col-span-2' }}">
                    <button type="submit" class="rm-btn-primary w-full h-[38px] flex items-center justify-center gap-2">
                        <i class="ph-bold ph-plus text-lg"></i>
                        <span>Agregar</span>
                    </button>
                </div>
            </form>
            @error('hora_fin') <span class="text-xs text-estado-peligro block mt-2">{{ $message }}</span> @enderror
        </div>

        <!-- Lista de Horarios -->
        <div>
            <h4 class="text-sm font-bold text-titulo mb-3 flex items-center gap-2">
                <i class="ph-bold ph-calendar-check"></i> Cronograma Semanal
            </h4>

            @if(count($horarios) > 0)
            <div class="bg-white rounded-2xl border border-borde overflow-hidden">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-fondo-tabla text-apoyo uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Día</th>
                            <th class="px-5 py-3 text-center">Horario</th>
                            @if($tipoPersonal === 'admin')
                            <th class="px-5 py-3 text-center">Turno</th>
                            @endif
                            <th class="px-5 py-3 text-center">Estado</th>
                            <th class="px-5 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde/50">
                        @foreach($horarios as $horario)
                            <tr class="hover:bg-fondo-hover transition-colors">
                                <td class="px-5 py-3 font-bold text-titulo">
                                    {{ $horario->dia_semana }}
                                </td>
                                <td class="px-5 py-3 text-center text-apoyo font-semibold">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-fondo-tabla rounded-lg border border-borde">
                                        <i class="ph-bold ph-clock"></i>
                                        <span>{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}</span>
                                    </div>
                                </td>
                                @if($tipoPersonal === 'admin')
                                <td class="px-5 py-3 text-center">
                                    @if($horario->turno)
                                        <span class="text-xs font-bold text-titulo">{{ $horario->turno->nombre_turno }}</span>
                                    @else
                                        <span class="text-xs text-apoyo">Horario Libre</span>
                                    @endif
                                </td>
                                @endif
                                <td class="px-5 py-3 text-center">
                                    @if($horario->estado === 'ACTIVO')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-estado-exitoBg text-estado-exito border border-estado-exitoBorde">
                                            ACTIVO
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-estado-peligroBg text-estado-peligro border border-estado-peligroBorde">
                                            INACTIVO
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button wire:click="eliminarHorario({{ $horario->getKey() }})" class="h-8 w-8 rounded-lg border border-borde text-estado-peligro hover:bg-estado-peligro hover:text-white transition-colors inline-flex items-center justify-center">
                                        <i class="ph-bold ph-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-apoyo border border-dashed border-borde rounded-2xl bg-white">
                <i class="ph-fill ph-calendar-x text-4xl mb-2 opacity-50"></i>
                <p class="text-sm font-semibold">No se han registrado horarios operativos para este personal.</p>
            </div>
            @endif
        </div>
    @endif
</div>
