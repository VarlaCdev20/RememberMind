<div class="rm-staff-schedule space-y-5">
 @if($modalFinalizarAbierto)
 <section id="staff-schedule-finalizar" class="rm-staff-action__form" aria-label="Finalizar asignación">
  <x-ui.section-header title="Finalizar asignación" icon="ph-flag-checkered" />
  <div class="rm-staff-action__context"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i><div><p class="rm-help">Trabajador</p><strong>{{ $nombrePersonal }}</strong></div></div>
  @php
   $asignacionAFinalizar = collect($asignaciones)->firstWhere('cod_asignacion', $codAsignacionFinalizar);
  @endphp
  @if($asignacionAFinalizar)
   <p><strong>{{ $asignacionAFinalizar['turno_nombre'] }}</strong> · {{ $asignacionAFinalizar['area_nombre'] }} · {{ $asignacionAFinalizar['hora_inicio'] }}–{{ $asignacionAFinalizar['hora_fin'] }}</p>
  @endif
  <form wire:submit="solicitarFinalizar" class="rm-staff-action__form" x-data x-init="$nextTick(() => $refs.fechaFin.focus())" novalidate>
   <p class="rm-staff-action__note"><i class="ph-bold ph-info" aria-hidden="true"></i><span>Las jornadas anteriores se conservan. Las ocurrencias seleccionadas desde la fecha indicada quedarán inactivas.</span></p>
   <x-ui.field label="Cancelar desde la fecha" for="staff-schedule-fecha-fin" error="f_fecha_fin_finalizar" required>
    <input id="staff-schedule-fecha-fin" x-ref="fechaFin" type="date" wire:model="f_fecha_fin_finalizar" class="rm-input" aria-invalid="{{ $errors->has('f_fecha_fin_finalizar') ? 'true' : 'false' }}" @if($errors->has('f_fecha_fin_finalizar')) aria-describedby="staff-schedule-fecha-fin-error" @endif required>
   </x-ui.field>
   <div class="rm-staff-action__actions">
    <x-ui.action-button variant="secondary" wire:click="cancelarFinalizar">Cancelar</x-ui.action-button>
    <x-ui.action-button tipo="submit" loading="solicitarFinalizar,confirmarFinalizar" icono="ph-flag-checkered">Confirmar finalización</x-ui.action-button>
   </div>
  </form>
 </section>
 @else

 {{-- ══ SIN CLASIFICACIÓN ══ --}}
 @if($tipoPersonal === 'ninguno')
 <div class="flex flex-col items-center justify-center p-10 text-apoyo border-2 border-dashed border-borde rounded-2xl bg-[var(--rm-surface-soft)]">
  <i class="ph-fill ph-calendar-slash text-5xl mb-4 opacity-40"></i>
  <h4 class="text-lg font-bold text-titulo">Sin clasificación operativa</h4>
  <p class="text-sm font-semibold mt-2 text-center max-w-md text-apoyo">
  Este trabajador no tiene una clasificación compatible con la asignación de horarios. Revisa su rol institucional y su registro laboral.
  </p>
 </div>

 @else

 <div class="rm-staff-action__context">
  <i class="ph-bold ph-identification-badge" aria-hidden="true"></i>
  <div><p class="rm-help">Trabajador</p><strong>{{ $nombrePersonal }}</strong></div>
  <x-ui.status-badge :estado="$estadoUsuario" />
 </div>
 @if(!$formAbierto)
 <div class="rm-staff-schedule__heading">
  <x-ui.section-header title="Asignaciones de turno" icon="ph-calendar-check" />
   <x-ui.action-button wire:click="abrirFormNuevo" loading="abrirFormNuevo" icono="ph-plus"
    :disabled="$sinRol || in_array(strtoupper($estadoUsuario), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true)">Nueva asignación</x-ui.action-button>
 </div>
 @endif
 @if($sinRol)
  <p class="rm-staff-action__note"><i class="ph-bold ph-info" aria-hidden="true"></i><span>Este usuario no tiene un rol institucional. La creación y edición de horarios permanecerá bloqueada hasta asignarle uno.</span></p>
 @elseif(in_array(strtoupper($estadoUsuario), ['INACTIVO', 'SUSPENDIDO', 'RETIRADO', '0'], true))
  <p class="rm-error" role="status">Usuario {{ strtoupper($estadoUsuario) }}. No se permiten nuevas asignaciones ni cambios de horario.</p>
 @elseif(!$formAbierto && collect($asignaciones)->contains('estado', 'ACTIVA'))
  <p class="rm-staff-action__note"><i class="ph-bold ph-info" aria-hidden="true"></i><span>La edición conserva las jornadas anteriores y registra una nueva versión del horario.</span></p>
 @endif

 @if($formAbierto)
 <form wire:submit="solicitarGuardar" class="rm-staff-action__form" novalidate>
  <div class="rm-schedule-editor">
  <div class="rm-schedule-editor__fields">
  <x-ui.form-section :title="$modoEdicion ? 'Editar asignación' : 'Nueva asignación de turno'" :icon="$modoEdicion ? 'ph-pencil-simple' : 'ph-calendar-plus'" :columns="2">
   <x-ui.field label="Área" for="staff-schedule-f_cod_area" error="f_cod_area" required>
    <select id="staff-schedule-f_cod_area" wire:model="f_cod_area" class="rm-input" aria-invalid="{{ $errors->has('f_cod_area') ? 'true' : 'false' }}" @if($errors->has('f_cod_area')) aria-describedby="staff-schedule-f_cod_area-error" @endif required>
     <option value="">SELECCIONAR ÁREA</option>
     @foreach($areas as $area)<option value="{{ $area['cod_area'] }}">{{ mb_strtoupper($area['nombre'], 'UTF-8') }}</option>@endforeach
    </select>
   </x-ui.field>
   <x-ui.field label="Tipo de asignación" for="staff-schedule-f_tipo_asignacion" error="f_tipo_asignacion" required>
    <select id="staff-schedule-f_tipo_asignacion" wire:model.live="f_tipo_asignacion" class="rm-input" aria-invalid="{{ $errors->has('f_tipo_asignacion') ? 'true' : 'false' }}" @if($errors->has('f_tipo_asignacion')) aria-describedby="staff-schedule-f_tipo_asignacion-error" @endif required>
     <option value="FIJO">FIJO · HORARIO SEMANAL</option><option value="ROTATIVO">ROTATIVO · TURNOS ROTATIVOS</option><option value="TEMPORAL">TEMPORAL · VIGENCIA LIMITADA</option><option value="EVENTUAL">EVENTUAL · COBERTURA PUNTUAL</option>
    </select>
   </x-ui.field>
   @if(!$esRotacionAutomatica)
   <x-ui.field label="Turno" for="staff-schedule-f_cod_turno" error="f_cod_turno" required>
    <select id="staff-schedule-f_cod_turno" wire:model.live="f_cod_turno" class="rm-input" aria-invalid="{{ $errors->has('f_cod_turno') ? 'true' : 'false' }}" @if($errors->has('f_cod_turno')) aria-describedby="staff-schedule-f_cod_turno-error" @endif required>
     <option value="">SELECCIONAR TURNO</option>
     @foreach($turnos as $turno)
      <option value="{{ $turno['cod_turno'] }}">{{ mb_strtoupper($turno['nombre'], 'UTF-8') }} @if($turno['hora_inicio'] && $turno['hora_fin']) ({{ \Carbon\Carbon::parse($turno['hora_inicio'])->format('H:i') }}–{{ \Carbon\Carbon::parse($turno['hora_fin'])->format('H:i') }}) @endif</option>
     @endforeach
    </select>
    @php
     $turnoSeleccionado = collect($turnos)->firstWhere('cod_turno', $f_cod_turno);
    @endphp
    @if($turnoSeleccionado)
     <p class="rm-help"><i class="ph-bold ph-clock" aria-hidden="true"></i> Horario institucional: {{ $turnoSeleccionado['hora_inicio'] ? \Carbon\Carbon::parse($turnoSeleccionado['hora_inicio'])->format('H:i') : 'Sin hora' }}–{{ $turnoSeleccionado['hora_fin'] ? \Carbon\Carbon::parse($turnoSeleccionado['hora_fin'])->format('H:i') : 'Sin hora' }}</p>
    @endif
   </x-ui.field>
   @else
    <fieldset class="rm-schedule-shifts rm-staff-action__wide" @if($errors->has('f_turno_inicial')) aria-describedby="staff-schedule-f_turno_inicial-error" @endif>
     <legend class="rm-label rm-label-required">Turno inicial</legend>
     <div class="rm-schedule-shifts__options">
      @foreach($turnosRotacion as $codigo => $definicion)
       <label>
        <input class="sr-only" type="radio" wire:model.live="f_turno_inicial" value="{{ $codigo }}" required>
        <i class="ph-bold {{ ['MANANA'=>'ph-sun','TARDE'=>'ph-sun-horizon','NOCHE'=>'ph-moon'][$codigo] }}" aria-hidden="true"></i>
        <strong>{{ mb_strtoupper($definicion['nombre']) }}</strong><span>{{ $definicion['inicio'] }}–{{ $definicion['fin'] }}</span>
       </label>
      @endforeach
     </div>
     @error('f_turno_inicial')<p id="staff-schedule-f_turno_inicial-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
    </fieldset>
   @endif
   @if($esEnfermeria && !$modoEdicion && $f_tipo_asignacion === 'ROTATIVO')
    <div class="rm-staff-action__wide">
     <label class="rm-label min-h-11 flex items-center gap-2" for="staff-schedule-rotacion">
      <input id="staff-schedule-rotacion" type="checkbox" wire:model.live="f_rotacion_automatica" aria-invalid="{{ $errors->has('f_rotacion_automatica') ? 'true' : 'false' }}" @if($errors->has('f_rotacion_automatica')) aria-describedby="staff-schedule-rotacion-error" @endif> Rotación automática de ocho horas
     </label>
     @error('f_rotacion_automatica')<p id="staff-schedule-rotacion-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
     @if($esRotacionAutomatica)
      <p class="rm-help">Mañana → tarde → noche → 24 horas de descanso → mañana.</p>
      @can('turnos.gestionar')
       <x-ui.action-button variant="secondary" wire:click="solicitarPrepararTurnos" icono="ph-clock" loading="solicitarPrepararTurnos,prepararTurnosRotacion">Preparar turnos de 8 horas</x-ui.action-button>
      @endcan
     @endif
    </div>
   @endif
   <x-ui.field label="Fecha de inicio" for="staff-schedule-f_fecha_inicio" error="f_fecha_inicio" required>
    <input id="staff-schedule-f_fecha_inicio" type="date" wire:model.live="f_fecha_inicio" class="rm-input" aria-invalid="{{ $errors->has('f_fecha_inicio') ? 'true' : 'false' }}" @if($errors->has('f_fecha_inicio')) aria-describedby="staff-schedule-f_fecha_inicio-error" @endif required>
   </x-ui.field>
   @if($permiteRepetir || $esRotacionAutomatica)
    <x-ui.field :label="$esRotacionAutomatica ? 'Programar hasta' : 'Repetir hasta (opcional)'" for="staff-schedule-f_fecha_fin" error="f_fecha_fin" :required="$esRotacionAutomatica">
     <input id="staff-schedule-f_fecha_fin" type="date" wire:model.live="f_fecha_fin" @if($esRotacionAutomatica) required @endif class="rm-input" aria-invalid="{{ $errors->has('f_fecha_fin') ? 'true' : 'false' }}" aria-describedby="staff-schedule-repeat-help{{ $errors->has('f_fecha_fin') ? ' staff-schedule-f_fecha_fin-error' : '' }}">
     <p id="staff-schedule-repeat-help" class="rm-help">Hasta un año.@if(!$esRotacionAutomatica) Deja la fecha vacía para asignar solo la primera semana.@endif</p>
    </x-ui.field>
   @endif
   <x-ui.field label="Observaciones (opcional)" for="staff-schedule-f_observaciones" error="f_observaciones" class="rm-staff-action__wide">
    <textarea id="staff-schedule-f_observaciones" wire:model="f_observaciones" class="rm-input" rows="2" maxlength="1000" placeholder="Ej. cobertura temporal o reemplazo" aria-invalid="{{ $errors->has('f_observaciones') ? 'true' : 'false' }}" @if($errors->has('f_observaciones')) aria-describedby="staff-schedule-f_observaciones-error" @endif></textarea>
   </x-ui.field>
  </x-ui.form-section>
  @if(!$esRotacionAutomatica)
  <fieldset class="rm-staff-action__days" @if($errors->has('f_dias_semana')) aria-describedby="staff-schedule-dias-error" @endif>
   <legend class="rm-label rm-label-required">Días laborales</legend>
   <div class="rm-schedule-day-shortcuts" role="group" aria-label="Selección rápida de días">
    <x-ui.action-button variant="ghost" size="sm" wire:click="seleccionarDias('laborables')">Lunes a viernes</x-ui.action-button>
    <x-ui.action-button variant="ghost" size="sm" wire:click="seleccionarDias('todos')">Toda la semana</x-ui.action-button>
    <x-ui.action-button variant="ghost" size="sm" wire:click="seleccionarDias('ninguno')">Limpiar</x-ui.action-button>
   </div>
   <div class="rm-staff-action__day-options">
    @foreach($diasSemana as $dia)
     <label><input class="sr-only" type="checkbox" wire:model.live="f_dias_semana" value="{{ $dia }}"><span><i class="ph-bold ph-check" aria-hidden="true"></i>{{ ['MIERCOLES'=>'MIÉRCOLES','SABADO'=>'SÁBADO'][$dia] ?? $dia }}</span></label>
    @endforeach
   </div>
   @error('f_dias_semana')<p id="staff-schedule-dias-error" class="rm-error" role="alert">{{ $message }}</p>@enderror
   @foreach($errors->get('f_dias_semana.*') as $mensajes) @foreach($mensajes as $mensaje)<p class="rm-error" role="alert">{{ $mensaje }}</p>@endforeach @endforeach
  </fieldset>
  @endif
  <p class="rm-staff-action__note"><i class="ph-bold ph-info" aria-hidden="true"></i><span>
   @if($esRotacionAutomatica)
    Al terminar la noche a las 07:00, descansa hasta las 07:00 del día siguiente. Revisa las fechas antes de confirmar.
   @elseif($permiteRepetir)
    El turno se asignará cada semana en los días elegidos, hasta la fecha indicada inclusive.
   @else
    Se guardará una jornada por cada día elegido dentro de los siete días desde el inicio.
   @endif
   @if($modoEdicion)Las jornadas anteriores se conservan; se registrará una nueva versión.@endif
  </span></p>
  </div>
  <aside class="rm-schedule-preview" aria-labelledby="staff-schedule-preview-title">
   <div class="rm-schedule-preview__heading">
    <h3 id="staff-schedule-preview-title"><i class="ph-bold ph-calendar-check" aria-hidden="true"></i> Vista previa</h3>
    <x-ui.action-button variant="secondary" size="sm" wire:click="previsualizarHorario" loading="previsualizarHorario" icono="ph-arrows-clockwise">Ver calendario</x-ui.action-button>
   </div>
   @if($vistaPreviaHorario)
    <p class="rm-help" role="status">{{ count(array_filter($vistaPreviaHorario, fn ($dia) => !$dia['descanso'])) }} turnos · {{ count(array_filter($vistaPreviaHorario, fn ($dia) => $dia['descanso'])) }} descansos · Sin guardar</p>
    <ol class="rm-schedule-preview__dates">
     @foreach(array_slice($vistaPreviaHorario, ($paginaVistaPrevia - 1) * 7, 7) as $dia)
      <li class="{{ $dia['descanso'] ? 'is-rest' : '' }}"><time datetime="{{ $dia['fecha'] }}">{{ $dia['dia'] }}</time><div><strong>{{ $dia['nombre'] }}</strong><span>{{ $dia['horario'] }}{{ $dia['cruza_dia'] ? ' (+1 día)' : '' }}</span></div></li>
     @endforeach
    </ol>
    <nav class="rm-schedule-preview__navigation" aria-label="Semanas de la vista previa">
     <x-ui.action-button variant="icon" wire:click="moverVistaPrevia(-1)" aria-label="Fechas anteriores" icono="ph-caret-left" :disabled="$paginaVistaPrevia === 1" />
     <span>{{ $paginaVistaPrevia }} / {{ (int) ceil(count($vistaPreviaHorario) / 7) }}</span>
     <x-ui.action-button variant="icon" wire:click="moverVistaPrevia(1)" aria-label="Fechas siguientes" icono="ph-caret-right" :disabled="$paginaVistaPrevia * 7 >= count($vistaPreviaHorario)" />
    </nav>
   @else
    <div class="rm-schedule-preview__empty"><i class="ph-bold ph-calendar-blank" aria-hidden="true"></i><p>Completa el horario y pulsa «Ver calendario» para revisar las fechas.</p></div>
   @endif
  </aside>
  </div>
  <div class="rm-staff-action__actions">
   <x-ui.action-button variant="secondary" wire:click="cancelarFormulario">Cancelar</x-ui.action-button>
   <x-ui.action-button tipo="submit" loading="solicitarGuardar,guardar" icono="ph-check">{{ $modoEdicion ? 'Guardar cambios' : 'Crear asignación' }}</x-ui.action-button>
  </div>
 </form>
 @endif

 {{-- ══ TABLA DE ASIGNACIONES ══ --}}
 @if(!$formAbierto)
 @if(count($asignaciones) > 0)
 <div class="rm-table-surface rm-schedule-table-wrap">
  <table class="rm-table rm-schedule-table">
   <caption class="sr-only">Jornadas y horarios registrados del trabajador</caption>
   <thead><tr><th scope="col">Fecha</th><th scope="col">Turno y área</th><th scope="col">Horario</th><th scope="col">Situación</th><th scope="col">Acciones</th></tr></thead>
   <tbody>
    @foreach($asignaciones as $asig)
     <tr wire:key="asig-{{ $asig['cod_asignacion'] }}">
      <td data-label="Fecha"><strong>{{ $asig['fecha_inicio'] }}</strong>@if($asig['fecha_fin'] && $asig['fecha_fin'] !== $asig['fecha_inicio'])<span class="rm-help">Hasta {{ $asig['fecha_fin'] }}</span>@endif</td>
      <td data-label="Turno y área"><strong>{{ $asig['turno_nombre'] }}</strong><span class="rm-help">{{ $asig['area_nombre'] }}</span></td>
      <td data-label="Horario"><span class="rm-schedule-table__hours"><i class="ph-bold ph-clock" aria-hidden="true"></i>{{ $asig['hora_inicio'] }}–{{ $asig['hora_fin'] }}</span>@if($asig['hora_fin'] <= $asig['hora_inicio'])<small class="rm-help">Termina al día siguiente</small>@endif</td>
      <td data-label="Situación"><x-ui.status-badge :estado="$asig['estado']" /><span class="rm-help">{{ $asig['tipo_asignacion'] }}</span></td>
      <td data-label="Acciones"><div class="rm-schedule-table__actions">
       <x-ui.action-button variant="icon" wire:click="verDetalle('{{ $asig['cod_asignacion'] }}')" icono="ph-eye" aria-label="Ver detalle" title="Ver detalle" />
       @if($asig['estado'] === 'ACTIVA')
        <x-ui.action-button variant="icon" wire:click="editarAsignacion('{{ $asig['cod_asignacion'] }}')" icono="ph-pencil-simple" aria-label="Editar asignación" title="Editar asignación" />
        <x-ui.action-button variant="icon" wire:click="prepararFinalizar('{{ $asig['cod_asignacion'] }}')" icono="ph-flag-checkered" aria-label="Finalizar asignación" title="Finalizar asignación" />
       @endif
      </div></td>
     </tr>
    @endforeach
   </tbody>
  </table>
 </div>
 @else
 <div class="flex flex-col items-center justify-center py-10 text-apoyo border border-dashed border-borde rounded-2xl bg-[var(--rm-surface-soft)]">
  <i class="ph-fill ph-calendar-x text-4xl mb-2 opacity-40"></i>
  <p class="text-sm font-semibold">No hay asignaciones de turno registradas.</p>
  <p class="text-xs mt-1">Haz clic en «Nueva asignación» para comenzar.</p>
 </div>
 @endif

 @endif {{-- fin de la lista mientras el formulario está cerrado --}}
 @endif {{-- fin @if tipoPersonal !== ninguno --}}




 {{-- ══ MODAL: DETALLE ══ --}}
 @if($modalDetalleAbierto && !empty($detalleAsignacion))
 <div class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
 <div class="w-full max-w-md bg-[var(--rm-surface-soft)] rounded-2xl shadow-2xl border border-borde overflow-hidden">
  <div class="flex items-center justify-between px-5 py-4 border-b border-borde">
  <div class="flex items-center gap-3">
   <div class="h-10 w-10 rounded-xl bg-boton-acento/10 flex items-center justify-center text-boton-acento">
   <i class="ph-bold ph-calendar-check text-xl"></i>
   </div>
   <div>
   <h5 class="font-black text-titulo text-sm">Detalle de Asignación</h5>
   <p class="text-xs text-apoyo">{{ $detalleAsignacion['tipo_asignacion'] }} · {{ $detalleAsignacion['area'] }}</p>
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
    'ACTIVA' => 'text-estado-exito',
    'FINALIZADA' => 'text-estado-info',
    default => 'text-estado-peligro',
    };
   @endphp
   <span class="text-sm font-bold {{ $cls }}">{{ $detalleAsignacion['estado'] }}</span>
   </div>
  </div>

  <div class="p-3 bg-fondo rounded-xl border border-borde">
   <span class="block text-[10px] font-bold uppercase tracking-wider text-apoyo mb-2">Das laborales</span>
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
   @if($detalleAsignacion['created_at'])
   Registrado por <strong>{{ $detalleAsignacion['creado_por'] ?: 'Autor no disponible' }}</strong> el {{ $detalleAsignacion['created_at'] }}
   @else
   Autor y fecha original de registro no constan.
   @endif
   <details><summary>Historia conservada de esta ocurrencia</summary>@forelse($detalleAsignacion['historia'] ?? [] as $acto)<article class="rm-card p-3 mt-2"><strong>{{ ['ALTA'=>'Registro de asignación','FINALIZACION'=>'Finalización registrada','CANCELACION'=>'Cancelación registrada','RECTIFICACION'=>'Corrección registrada'][$acto['tipo_evento']] ?? 'Acto registrado' }}</strong><p>{{ $acto['motivo'] }}</p><p class="rm-help">Efecto: {{ \Carbon\Carbon::parse($acto['fecha_hora_efecto'])->format('d/m/Y H:i') }}. Registrado: {{ \Carbon\Carbon::parse($acto['fecha_hora_registro'])->format('d/m/Y H:i') }}. {{ trim(($acto['nombres'] ?? '').' '.($acto['apellido_paterno'] ?? '')) ?: 'Nombre del autor no disponible' }}.</p></article>@empty<p class="rm-help">No consta historia nominal anterior. No se infiere fecha o autor desde observaciones.</p>@endforelse</details>
  </div>
  </div>
 </div>
 </div>
 @endif

 @endif
</div>

@script
<script>

{!! file_get_contents(resource_path('frontend/scripts/modules/livewire-identidad-personal-institucional-horarios.js')) !!}
</script>
@endscript
