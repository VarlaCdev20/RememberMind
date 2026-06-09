<div class="space-y-5">

    {{-- ══ SIN CLASIFICACIÓN ══ --}}
    @if($tipoPersonal === 'ninguno')
        <div class="flex flex-col items-center justify-center p-10 text-apoyo border-2 border-dashed border-borde rounded-2xl bg-white">
            <i class="ph-fill ph-calendar-slash text-5xl mb-4 opacity-40"></i>
            <h4 class="text-lg font-bold text-titulo">Sin clasificación operativa</h4>
            <p class="text-sm font-semibold mt-2 text-center max-w-md text-apoyo">
                Este usuario no está registrado como Personal de Salud ni Administrativo. Completa la clasificación en la pestaña «Información Base» antes de asignar horarios.
            </p>
        </div>

    @else

        {{-- ══ HEADER ══ --}}
        <div class="flex items-center justify-between">
            <div>
                <h4 class="text-sm font-black text-titulo flex items-center gap-2">
                    <i class="ph-fill ph-calendar-check text-boton-acento"></i>
                    Asignaciones de Turno
                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                        {{ $tipoPersonal === 'salud' && $esEnfermeria
                            ? 'bg-estado-infoBg text-estado-info border border-estado-infoBorde'
                            : 'bg-boton-acento/10 text-boton-acento border border-boton-acento/20' }}">
                        {{ $tipoPersonal === 'salud' ? ($subtipoSalud ?: 'Salud') : 'Administrativo' }}
                    </span>
                </h4>
                <p class="text-xs text-apoyo font-semibold mt-0.5">
                    {{ $tipoPersonal === 'salud' && $esEnfermeria
                        ? 'Enfermería: validación estricta de solapamientos. Tipo ROTATIVO recomendado.'
                        : 'Gestiona turnos, áreas y días laborales del personal.' }}
                </p>
            </div>
            @if(!$formAbierto)
                <button type="button" wire:click="abrirFormNuevo" wire:loading.attr="disabled"
                    @disabled($sinRol || in_array(strtoupper($estadoUsuario), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true))
                    class="rm-btn-primary h-9 gap-1.5 rounded-xl px-4 text-xs flex items-center disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="ph-bold ph-plus text-base"></i> Nueva asignación
                </button>
            @endif
        </div>

        @if($sinRol)
            <div class="flex items-start gap-2 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-3 py-2 text-xs font-semibold text-estado-advertencia">
                <i class="ph-bold ph-warning-circle mt-0.5 text-base"></i>
                <span>Este usuario no tiene un rol institucional. La creación y edición de horarios permanecerá bloqueada hasta asignarle uno.</span>
            </div>
        @elseif(in_array(strtoupper($estadoUsuario), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true))
            <div class="flex items-start gap-2 rounded-xl border border-estado-peligroBorde bg-estado-peligroBg px-3 py-2 text-xs font-semibold text-estado-peligro">
                <i class="ph-bold ph-prohibit mt-0.5 text-base"></i>
                <span>Usuario {{ strtoupper($estadoUsuario) }}. No se permiten nuevas asignaciones ni cambios de horario.</span>
            </div>
        @elseif(collect($asignaciones)->contains('estado', 'ACTIVA'))
            <div class="flex items-start gap-2 rounded-xl border border-estado-infoBorde bg-estado-infoBg px-3 py-2 text-xs font-semibold text-estado-info">
                <i class="ph-bold ph-info mt-0.5 text-base"></i>
                <span>El usuario ya tiene horario activo. Puede editarlo para finalizar el registro anterior y crear una nueva versión, o finalizarlo manualmente.</span>
            </div>
        @endif

        {{-- ══ FORMULARIO CREAR / EDITAR ══ --}}
        @if($formAbierto)
        <div class="border border-boton-acento/30 rounded-2xl bg-boton-acento/5 p-5 space-y-4 animate-fade-in">
            <div class="flex items-center justify-between mb-1">
                <h5 class="text-sm font-bold text-titulo flex items-center gap-2">
                    <i class="ph-bold {{ $modoEdicion ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-boton-acento"></i>
                    {{ $modoEdicion ? 'Editar Asignación (se creará historial)' : 'Nueva Asignación de Turno' }}
                </h5>
                <button type="button" wire:click="cancelarFormulario" class="h-7 w-7 flex items-center justify-center rounded-lg text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition-colors">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            @if($modoEdicion)
            <div class="p-3 bg-estado-advertenciaBg border border-estado-advertenciaBorde rounded-xl flex gap-2 text-xs text-estado-advertencia font-semibold">
                <i class="ph-bold ph-warning-circle text-base flex-shrink-0 mt-0.5"></i>
                <span>Al guardar, la asignación actual quedará como <strong>FINALIZADA</strong> y se creará una nueva asignación con los datos modificados.</span>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Área --}}
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">
                        Área <span class="text-estado-peligro">*</span>
                    </label>
                    <select wire:model="f_cod_area" class="w-full rounded-xl border-input-borde bg-white text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                        <option value="">Seleccione un área...</option>
                        @foreach($areas as $area)
                            <option value="{{ $area['cod_area'] }}">{{ $area['nombre'] }}{{ $area['tipo_area'] ? ' ('.$area['tipo_area'].')' : '' }}</option>
                        @endforeach
                    </select>
                    @error('f_cod_area') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Turno --}}
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">
                        Turno <span class="text-estado-peligro">*</span>
                    </label>
                    <select wire:model.live="f_cod_turno" class="w-full rounded-xl border-input-borde bg-white text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                        <option value="">Seleccione un turno...</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno['cod_turno'] }}">
                                {{ $turno['nombre'] }}
                                @if($turno['hora_inicio'] && $turno['hora_fin'])
                                    ({{ \Carbon\Carbon::parse($turno['hora_inicio'])->format('H:i') }} – {{ \Carbon\Carbon::parse($turno['hora_fin'])->format('H:i') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @php
                        $turnoSeleccionado = collect($turnos)->firstWhere('cod_turno', $f_cod_turno);
                    @endphp
                    @if($turnoSeleccionado)
                        <div class="mt-1.5 flex items-center gap-2 rounded-lg border border-borde bg-fondo px-2.5 py-1.5 text-[11px] font-semibold text-apoyo">
                            <i class="ph-bold ph-clock text-boton-acento"></i>
                            <span>Horario institucional:</span>
                            <strong class="text-titulo">
                                {{ $turnoSeleccionado['hora_inicio'] ? \Carbon\Carbon::parse($turnoSeleccionado['hora_inicio'])->format('H:i') : 'Sin hora' }}
                                -
                                {{ $turnoSeleccionado['hora_fin'] ? \Carbon\Carbon::parse($turnoSeleccionado['hora_fin'])->format('H:i') : 'Sin hora' }}
                            </strong>
                        </div>
                    @endif
                    @error('f_cod_turno') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Tipo de asignación --}}
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">
                        Tipo de Asignación <span class="text-estado-peligro">*</span>
                    </label>
                    <select wire:model="f_tipo_asignacion" class="w-full rounded-xl border-input-borde bg-white text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                        <option value="FIJO">FIJO – Horario fijo semanal</option>
                        <option value="ROTATIVO">ROTATIVO – Turnos rotativos</option>
                        <option value="TEMPORAL">TEMPORAL – Vigencia limitada</option>
                        <option value="EVENTUAL">EVENTUAL – Cobertura puntual</option>
                    </select>
                    @error('f_tipo_asignacion') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Fecha inicio --}}
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">
                        Fecha Inicio <span class="text-estado-peligro">*</span>
                    </label>
                    <input type="date" wire:model="f_fecha_inicio" class="w-full rounded-xl border-input-borde bg-white text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                    @error('f_fecha_inicio') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Fecha fin (opcional) --}}
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">
                        Fecha Fin <span class="text-xs text-apoyo font-normal">(opcional)</span>
                    </label>
                    <input type="date" wire:model="f_fecha_fin" class="w-full rounded-xl border-input-borde bg-white text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                    @error('f_fecha_fin') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Observaciones --}}
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Observaciones</label>
                    <input type="text" wire:model="f_observaciones" placeholder="Ej: Cobertura temporal, reemplazo..." class="w-full rounded-xl border-input-borde bg-white text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                </div>
            </div>

            {{-- Días de la semana --}}
            <div>
                <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-2">
                    Días Laborales <span class="text-estado-peligro">*</span>
                    @if($tipoPersonal === 'salud' && $esEnfermeria)
                        <span class="ml-2 text-estado-info text-[10px] font-bold normal-case">Enfermería: sin solapamiento en ningún día</span>
                    @endif
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach($diasSemana as $dia)
                        <label class="relative cursor-pointer select-none">
                            <input type="checkbox" wire:model="f_dias_semana" value="{{ $dia }}" class="sr-only peer">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold border-2 transition-all
                                peer-checked:bg-boton-acento peer-checked:border-boton-acento peer-checked:text-white
                                border-borde text-apoyo hover:border-boton-acento/50 bg-white">
                                {{ $dia }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('f_dias_semana') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Botones --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-borde/60">
                <button type="button" wire:click="cancelarFormulario"
                    class="px-4 py-2 text-sm font-bold text-apoyo hover:text-titulo transition-colors rounded-xl hover:bg-fondo-hover">
                    Cancelar
                </button>
                <button type="button" wire:click="solicitarGuardar"
                    wire:loading.attr="disabled"
                    wire:target="solicitarGuardar,guardar"
                    class="rm-btn-primary px-5 py-2 text-sm rounded-xl flex items-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="solicitarGuardar,guardar">
                        <i class="ph-bold {{ $modoEdicion ? 'ph-arrows-clockwise' : 'ph-check' }} text-sm"></i>
                        {{ $modoEdicion ? 'Guardar cambios' : 'Crear asignación' }}
                    </span>
                    <span wire:loading wire:target="solicitarGuardar,guardar" class="flex items-center gap-2">
                        <i class="ph-bold ph-spinner animate-spin"></i> Guardando...
                    </span>
                </button>
            </div>
        </div>
        @endif

        {{-- ══ TABLA DE ASIGNACIONES ══ --}}
        @if(count($asignaciones) > 0)
        <div class="bg-white rounded-2xl border border-borde overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-fondo-tabla text-apoyo uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Turno / Área</th>
                        <th class="px-4 py-3">Días</th>
                        <th class="px-4 py-3 text-center">Tipo</th>
                        <th class="px-4 py-3 text-center">Vigencia</th>
                        <th class="px-4 py-3 text-center">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($asignaciones as $asig)
                    <tr wire:key="asig-{{ $asig['cod_asignacion'] }}"
                        class="hover:bg-fondo-hover/50 transition-colors {{ $asig['estado'] !== 'ACTIVA' ? 'opacity-60' : '' }}">

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($asig['turno_color'])
                                    <span class="h-3 w-3 rounded-full flex-shrink-0" style="background-color: {{ $asig['turno_color'] }}"></span>
                                @endif
                                <div>
                                    <p class="font-bold text-titulo text-xs">{{ $asig['turno_nombre'] }}</p>
                                    <p class="text-[11px] text-apoyo">{{ $asig['area_nombre'] }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1 max-w-[180px]">
                                @foreach($asig['dias_semana'] as $dia)
                                    <span class="inline-block px-1.5 py-0.5 bg-boton-acento/10 text-boton-acento text-[10px] font-bold rounded">
                                        {{ mb_substr($dia, 0, 3) }}
                                    </span>
                                @endforeach
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                {{ $asig['tipo_asignacion'] === 'ROTATIVO'
                                    ? 'bg-estado-infoBg text-estado-info border border-estado-infoBorde'
                                    : 'bg-fondo-tabla text-apoyo border border-borde' }}">
                                {{ $asig['tipo_asignacion'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-center text-[11px] text-apoyo font-semibold whitespace-nowrap">
                            {{ $asig['fecha_inicio'] }}
                            @if($asig['fecha_fin'])
                                <br><span class="text-[10px]">→ {{ $asig['fecha_fin'] }}</span>
                            @else
                                <br><span class="text-[10px] text-estado-exito">Indefinido</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $estadoClasses = match($asig['estado']) {
                                    'ACTIVA'     => 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde',
                                    'FINALIZADA' => 'bg-estado-infoBg text-estado-info border-estado-infoBorde',
                                    default      => 'bg-estado-peligroBg text-estado-peligro border-estado-peligroBorde',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $estadoClasses }}">
                                {{ $asig['estado'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                {{-- Ver detalle --}}
                                <button type="button" wire:click="verDetalle('{{ $asig['cod_asignacion'] }}')"
                                    title="Ver detalle"
                                    class="h-7 w-7 rounded-lg border border-borde text-apoyo hover:bg-fondo-hover hover:text-titulo transition-colors inline-flex items-center justify-center">
                                    <i class="ph-bold ph-eye text-xs"></i>
                                </button>

                                @if($asig['estado'] === 'ACTIVA')
                                    {{-- Editar --}}
                                    <button type="button" wire:click="editarAsignacion('{{ $asig['cod_asignacion'] }}')"
                                        title="Editar asignación"
                                        class="h-7 w-7 rounded-lg border border-borde text-apoyo hover:bg-boton-acento/10 hover:text-boton-acento hover:border-boton-acento/30 transition-colors inline-flex items-center justify-center">
                                        <i class="ph-bold ph-pencil-simple text-xs"></i>
                                    </button>
                                    {{-- Finalizar --}}
                                    <button type="button" wire:click="prepararFinalizar('{{ $asig['cod_asignacion'] }}')"
                                        title="Finalizar asignación"
                                        class="h-7 w-7 rounded-lg border border-borde text-apoyo hover:bg-estado-advertenciaBg hover:text-estado-advertencia hover:border-estado-advertenciaBorde transition-colors inline-flex items-center justify-center">
                                        <i class="ph-bold ph-flag-checkered text-xs"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-apoyo border border-dashed border-borde rounded-2xl bg-white">
            <i class="ph-fill ph-calendar-x text-4xl mb-2 opacity-40"></i>
            <p class="text-sm font-semibold">No hay asignaciones de turno registradas.</p>
            <p class="text-xs mt-1">Haz clic en «Nueva asignación» para comenzar.</p>
        </div>
        @endif

    @endif {{-- fin @if tipoPersonal !== ninguno --}}


    {{-- ══ MODAL: FINALIZAR ══ --}}
    @if($modalFinalizarAbierto)
    <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-borde overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-4 border-b border-borde bg-estado-advertenciaBg">
                <div class="h-10 w-10 rounded-xl bg-estado-advertencia/20 flex items-center justify-center text-estado-advertencia">
                    <i class="ph-bold ph-flag-checkered text-xl"></i>
                </div>
                <div>
                    <h5 class="font-black text-titulo text-sm">Finalizar Asignación</h5>
                    <p class="text-xs text-apoyo font-semibold">{{ $codAsignacionFinalizar }}</p>
                </div>
            </div>
            <div class="p-5 space-y-4">
                <p class="text-sm text-parrafo">Ingrese la fecha en que finaliza esta asignación. El registro quedará en estado <strong>FINALIZADA</strong>.</p>
                <div>
                    <label class="block text-xs font-bold text-apoyo uppercase tracking-wider mb-1">Fecha de Fin <span class="text-estado-peligro">*</span></label>
                    <input type="date" wire:model="f_fecha_fin_finalizar" class="w-full rounded-xl border-input-borde bg-input-bg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus">
                    @error('f_fecha_fin_finalizar') <span class="text-xs text-estado-peligro mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-borde bg-fondo-hover">
                <button type="button" wire:click="cancelarFinalizar"
                    class="px-4 py-2 text-sm font-bold text-apoyo hover:text-titulo transition-colors rounded-xl hover:bg-fondo-hover">
                    Cancelar
                </button>
                <button type="button" wire:click="solicitarFinalizar"
                    wire:loading.attr="disabled"
                    wire:target="solicitarFinalizar,confirmarFinalizar"
                    class="px-5 py-2 text-sm font-bold bg-estado-advertencia text-white rounded-xl hover:bg-estado-advertencia/90 transition-colors flex items-center gap-2 disabled:opacity-60">
                    <span wire:loading.remove wire:target="solicitarFinalizar,confirmarFinalizar">
                        <i class="ph-bold ph-flag-checkered"></i> Confirmar finalización
                    </span>
                    <span wire:loading wire:target="solicitarFinalizar,confirmarFinalizar" class="flex items-center gap-2">
                        <i class="ph-bold ph-spinner animate-spin"></i> Procesando...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif


    {{-- ══ MODAL: DETALLE ══ --}}
    @if($modalDetalleAbierto && !empty($detalleAsignacion))
    <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl border border-borde overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-borde">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-boton-acento/10 flex items-center justify-center text-boton-acento">
                        <i class="ph-bold ph-calendar-check text-xl"></i>
                    </div>
                    <div>
                        <h5 class="font-black text-titulo text-sm">Detalle de Asignación</h5>
                        <p class="text-xs text-apoyo font-mono">{{ $detalleAsignacion['cod_asignacion'] }}</p>
                    </div>
                </div>
                <button type="button" wire:click="cerrarDetalle"
                    class="h-8 w-8 flex items-center justify-center rounded-lg text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition-colors">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <div class="p-5 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-fondo rounded-xl border border-borde">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Turno</span>
                        <span class="text-sm font-bold text-titulo">{{ $detalleAsignacion['turno'] }}</span>
                        <span class="text-xs text-apoyo block">{{ $detalleAsignacion['hora_inicio'] }} – {{ $detalleAsignacion['hora_fin'] }}</span>
                    </div>
                    <div class="p-3 bg-fondo rounded-xl border border-borde">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Área</span>
                        <span class="text-sm font-bold text-titulo">{{ $detalleAsignacion['area'] }}</span>
                    </div>
                    <div class="p-3 bg-fondo rounded-xl border border-borde">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Tipo</span>
                        <span class="text-sm font-bold text-titulo">{{ $detalleAsignacion['tipo_asignacion'] }}</span>
                    </div>
                    <div class="p-3 bg-fondo rounded-xl border border-borde">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo">Estado</span>
                        @php
                            $cls = match($detalleAsignacion['estado']) {
                                'ACTIVA'     => 'text-estado-exito',
                                'FINALIZADA' => 'text-estado-info',
                                default      => 'text-estado-peligro',
                            };
                        @endphp
                        <span class="text-sm font-bold {{ $cls }}">{{ $detalleAsignacion['estado'] }}</span>
                    </div>
                </div>

                <div class="p-3 bg-fondo rounded-xl border border-borde">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo mb-2">Días laborales</span>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($detalleAsignacion['dias_semana'] as $dia)
                            <span class="px-2 py-0.5 bg-boton-acento/10 text-boton-acento text-xs font-bold rounded-lg">{{ $dia }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs text-apoyo font-semibold">
                    <div><span class="font-bold text-titulo block">Inicio:</span> {{ $detalleAsignacion['fecha_inicio'] }}</div>
                    <div><span class="font-bold text-titulo block">Fin:</span> {{ $detalleAsignacion['fecha_fin'] }}</div>
                </div>

                @if($detalleAsignacion['observaciones'] !== '—')
                <div class="p-3 bg-estado-advertenciaBg border border-estado-advertenciaBorde rounded-xl text-xs text-estado-advertencia font-semibold">
                    <i class="ph-bold ph-note"></i> {{ $detalleAsignacion['observaciones'] }}
                </div>
                @endif

                <div class="text-[10px] text-apoyo pt-1 border-t border-borde">
                    Registrado por <strong>{{ $detalleAsignacion['creado_por'] }}</strong> el {{ $detalleAsignacion['created_at'] }}
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@script
<script>
    $wire.on('confirmarGuardadoAsignacion', (event) => {
        const data = event[0] ?? event;

        Swal.fire({
            title: data.title ?? 'Guardar asignación',
            text: data.message ?? 'Se registrará el horario seleccionado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3F7D5A',
            cancelButtonColor: '#78716C',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Revisar datos',
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('guardar');
            }
        });
    });

    $wire.on('confirmarFinalizacionAsignacion', () => {
        Swal.fire({
            title: 'Finalizar asignación',
            text: 'El horario quedará como FINALIZADO y se conservará en el historial.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#C06B4E',
            cancelButtonColor: '#78716C',
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $wire.call('confirmarFinalizar');
            }
        });
    });
</script>
@endscript
