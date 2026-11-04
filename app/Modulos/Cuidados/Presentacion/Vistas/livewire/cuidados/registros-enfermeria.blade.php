<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-5 {{ in_array($cuidado, ['hidratacion', 'sueno', 'heridas'], true) || $seccion === 'HERIDAS' ? 'rm-clinical-workspace' : '' }}" x-data="rmClinicalFormFeedback()" @clinical-capture-changed="clinicalFormDirty = $event.detail.dirty" @swal="if (confirmWorkspaceFeedback($event.detail)) $event.stopPropagation()">
    @if($emergente)
        @include('livewire.cuidados.partials.heridas-curaciones')
    @else
    <x-ui.page-header
    :title="$cuidado !== '' ? 'Cuidados · '.\App\Modulos\Cuidados\Presentacion\Navegacion\NavegacionCuidadosService::opcion($cuidado)['label'] : 'Cuidados, dispositivos e incidentes'"
    :subtitle="$cuidado === 'sueno' ? 'Consulta el descanso y las observaciones de sueño del residente.' : ($cuidado === 'hidratacion' ? 'Consulta los aportes de líquido y registra la hidratación del residente.' : 'Datos estructurados, firmados y vinculados al residente y al turno.')"
    :overline="$esSuperAdmin ? 'Supervisión asistencial' : 'Registro asistencial'"
    icon="ph-activity"
    :date="now()">
    <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn rm-btn-secondary">Agenda</a>
    <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn rm-btn-secondary">{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis residentes' }}</a>
</x-ui.page-header>

    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm">
        <label class="text-xs font-bold text-[var(--rm-text-title)]" for="residente-registro">Residente</label>
        <select id="residente-registro" wire:model.live="codResidente" :disabled="clinicalFormOpen" class="mt-2 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-4 py-2.5 text-sm text-[var(--rm-text-body)] outline-none focus:border-[var(--rm-border)]-focus">
            <option value="">{{ $esSuperAdmin ? 'Seleccione cualquier residente' : 'Seleccione un residente asignado' }}</option>
            @foreach($pacientes as $paciente)
                <option value="{{ $paciente->cod_residente }}">{{ $paciente->apellido_paterno ?? $paciente->ap_paterno }} {{ $paciente->apellido_materno ?? $paciente->ap_materno }}, {{ $paciente->nombres }}</option>
            @endforeach
        </select>
        @error('codResidente') <p class="mt-1 text-xs font-bold text-[var(--rm-danger)]">{{ $message }}</p> @enderror
    </section>

    @if($modoConsulta)
        <p role="status" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-3 text-sm font-semibold">Consulta fuera de turno · Solo lectura.</p>
    @endif
    @if($cuidado === 'hidratacion')
        @include('livewire.cuidados.partials.hidratacion-clinica')
    @elseif($cuidado === 'sueno')
        @include('livewire.cuidados.partials.sueno-consulta')
    @elseif($cuidado === 'heridas')
        @if($adulto)
            <x-ui.section-card :title="trim($adulto->nombres.' '.$adulto->ap_paterno.' '.$adulto->ap_materno)" subtitle="Heridas · Identificación del residente" icon="ph-user-circle" />
            @include('livewire.cuidados.partials.heridas-curaciones')
        @else
            <x-ui.empty-state icon="ph-user-circle" title="Selecciona un residente" description="Elige un residente de tu alcance para consultar sus heridas y curaciones." />
        @endif
    @else
    @if($adulto)
        @if(in_array($cuidado, ['sueno', 'hidratacion'], true))
            <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5">
                <h2 class="text-lg font-bold text-[var(--rm-text-title)]">Últimos registros guardados</h2>
                @forelse($registrosCuidado as $registro)
                    <p class="mt-2 text-sm">{{ ($cuidado === 'sueno' ? $registro->fecha : $registro->fecha_hora)->format('d/m/Y H:i') }} · {{ $cuidado === 'sueno' ? ($registro->calidad ?? 'Sin calidad registrada') : $registro->cantidad_ml.' ml' }} · {{ $registro->estado }} · {{ $registro->observacion }}</p>
                @empty
                    <p class="mt-2 text-sm text-[var(--rm-text-muted)]">Sin registros guardados para este residente.</p>
                @endforelse
            </section>
        @endif
        @if($errors->any())
            <div class="rounded-xl border border-[var(--rm-danger-border)] bg-[var(--rm-danger-soft)] p-4 text-sm text-[var(--rm-danger)]">
                <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase text-[var(--rm-accent)]">Identificación segura</p>
                    <h2 class="text-lg font-bold text-[var(--rm-text-title)]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</h2>
                    <p class="text-xs text-[var(--rm-text-muted)]">Estado institucional: {{ $adulto->estado }}</p>
                </div>
                <a href="{{ route('admin.enfermeria.pacientes.ficha', $adulto->cod_residente) }}" class="rm-btn-primary px-4 py-2 text-xs font-bold">Abrir Ficha 360°</a>
            </div>
            @if($adulto->dispositivosActivos->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($adulto->dispositivosActivos as $dispositivo)
                        <span class="rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-2.5 py-1 text-xs font-bold text-[var(--rm-text-body)]">{{ str_replace('_', ' ', $dispositivo->tipo) }}{{ $dispositivo->ubicacion ? ' · '.$dispositivo->ubicacion : '' }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        <nav class="flex flex-wrap gap-2 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3 shadow-sm" aria-label="Tipos de registro">
            @foreach(['CUIDADOS' => 'Cuidados diarios', 'DOLOR' => 'Dolor', 'DISPOSITIVOS' => 'Dispositivos', 'INCIDENTES' => 'Incidentes y lesiones', 'HERIDAS' => 'Heridas y curaciones', 'HISTORIAL' => 'Historial'] as $clave => $etiqueta)
                <button wire:click="$set('seccion', '{{ $clave }}')" class="rounded-xl px-4 py-2 text-xs font-bold transition {{ $seccion === $clave ? 'bg-[var(--rm-primary)] text-inverso' : 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)]' }}">{{ $etiqueta }}</button>
            @endforeach
        </nav>

        @if($seccion === 'HERIDAS')
            @include('livewire.cuidados.partials.heridas-curaciones')
        @elseif($seccion === 'CUIDADOS')
            @if($cuidado === 'sueno' && ! $this->puedeMutarRegistro('registros_sueno.crear'))
                <p class="text-sm text-[var(--rm-text-muted)]">Consulta de sueño. Tu cuenta no tiene permiso para registrar sueño desde esta pantalla.</p>
            @elseif($cuidado === 'hidratacion' && ! $this->puedeMutarRegistro('registros_hidratacion.crear'))
                <p class="text-sm text-[var(--rm-text-muted)]">Consulta de hidratación. El registro requiere permiso y una jornada vigente.</p>
            @else
            @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="guardarCuidado" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                <x-ui.section-header :level="2" title="Nuevo cuidado" icon="ph-clipboard-text" />
                <p class="mt-1 text-xs text-[var(--rm-text-muted)]">El registro se firma al guardar. Una corrección posterior debe crear una rectificación.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="rm-label">Tipo
                        <select wire:model.live="tipo" class="rm-select">
                            @foreach(['ALIMENTACION'=>'Alimentación','HIDRATACION'=>'Hidratación','ELIMINACION'=>'Eliminación','HIGIENE'=>'Higiene','MOVILIDAD'=>'Movilidad','SUENO'=>'Sueño','VALORACION_RAPIDA'=>'Valoración rápida','PROCEDIMIENTO'=>'Procedimiento'] as $v=>$e)<option value="{{ $v }}">{{ $e }}</option>@endforeach
                        </select>
                    </label>
                    <label class="rm-label">{{ $tipo === 'MOVILIDAD' ? 'Movilidad observada' : 'Detalle del cuidado' }}
                        @if($tipo === 'MOVILIDAD')
                            <select wire:model="subtipo" class="rm-select"><option value="">Selecciona una opción</option>@foreach(\App\Models\RegistroMovilidad::catalogos()['marcha'] as $option)<option value="{{ $option }}">{{ \App\Models\RegistroMovilidad::TEXTOS[$option] }}</option>@endforeach</select>
                        @else
                            <input wire:model="subtipo" type="text" placeholder="Ej. Desayuno, baño, deambulación" class="rm-input">
                        @endif
                        @error('subtipo') <span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span> @enderror
                    </label>
                    <label class="rm-label">Cambio respecto al basal
                        <select wire:model="cambioBasal" class="rm-select"><option>SIN_CAMBIOS</option><option>MEJOR</option><option>PEOR</option><option>NO_EVALUABLE</option></select>
                    </label>
                    @if(!in_array($tipo, ['ALIMENTACION','HIDRATACION','ELIMINACION','MOVILIDAD','SUENO']))
                        <label class="rm-label md:col-span-2">Intervención del plan de cuidados
                            <select wire:model="intervencionId" class="rm-select">
                                <option value="">Seleccione intervención vigente</option>
                                @foreach($intervencionesActivas as $int)
                                    <option value="{{ $int->cod_intervencion }}">{{ $int->nombre }} — {{ \Illuminate\Support\Str::limit($int->descripcion, 40) }}</option>
                                @endforeach
                            </select>
                            @error('intervencionId') <span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span> @enderror
                        </label>
                    @endif
                    @if($tipo === 'VALORACION_RAPIDA')
                        <label class="rm-label">Estado general<select wire:model="estadoGeneral" class="rm-select"><option>SIN_CAMBIOS</option><option>MEJOR</option><option>CON_CAMBIOS</option><option>NO_EVALUABLE</option></select>@error('estadoGeneral')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                        <label class="rm-label">Conciencia<select wire:model="conciencia" class="rm-select"><option value="">Sin cambio</option><option>ALERTA</option><option>SOMNOLENCIA</option><option>RESPUESTA_DISMINUIDA</option></select></label>
                        <label class="rm-label">Cognición<select wire:model="cognicion" class="rm-select"><option value="">Sin cambio</option><option>ORIENTADO</option><option>CONFUSION_NUEVA</option><option>DESORIENTACION</option></select></label>
                        <label class="rm-label">Conducta<select wire:model="conducta" class="rm-select"><option value="">Sin cambio</option><option>TRANQUILO</option><option>AGITADO</option><option>APATICO</option><option>AGRESIVO</option></select></label>
                        <label class="rm-label">Respiración<select wire:model="respiracion" class="rm-select"><option value="">Sin cambio</option><option>NORMAL</option><option>DISNEA</option><option>TAQUIPNEA</option><option>SECRECIONES</option></select></label>
                    @endif
                    @if($tipo === 'ALIMENTACION')
                        <label class="rm-label">Porcentaje consumido
                            <select wire:model="porcentaje" class="rm-select"><option value="">Seleccione</option>@foreach([0,25,50,75,100] as $p)<option value="{{ $p }}">{{ $p }}%</option>@endforeach</select>
                        </label>
                    @endif
                    @if($tipo === 'HIDRATACION')
                        <label class="rm-label">Cantidad en ml<input wire:model="cantidadMl" type="number" min="1" max="10000" class="rm-input">@error('cantidadMl')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                        <label class="rm-label">Tolerancia<select wire:model="tolerancia" class="rm-select"><option value="">No registrada</option><option>ADECUADA</option><option>PARCIAL</option><option>RECHAZO</option><option>NAUSEAS</option></select></label>
                    @endif
                    @if($tipo === 'ELIMINACION')
                        <div class="md:col-span-2"><p class="rm-help">La eliminación se registra ahora con campos estructurados desde Mis residentes.</p><a class="rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente, 'cuidado' => 'eliminacion']) }}">Abrir Eliminación V2</a></div>
                    @endif
                    @if(in_array($tipo, ['ALIMENTACION','MOVILIDAD','HIGIENE']))
                        <label class="rm-label">Nivel de ayuda
                            <select wire:model="nivelAyuda" class="rm-select"><option value="">No aplica</option><option>INDEPENDIENTE</option><option>SUPERVISION</option><option>PARCIAL</option><option>COMPLETA</option><option>UNA_PERSONA</option><option>DOS_PERSONAS</option></select>
                        </label>
                    @endif
                    @if($tipo === 'MOVILIDAD')
                        <label class="rm-label">Dispositivo<select wire:model.live="ayudaTecnica" class="rm-select"><option value="">Sin registrar</option>@foreach(\App\Models\RegistroMovilidad::catalogos()['dispositivo'] as $option)<option value="{{ $option }}">{{ \App\Models\RegistroMovilidad::TEXTOS[$option] }}</option>@endforeach</select></label>
                        @if($ayudaTecnica === 'OTRO')
                            <x-ui.field for="movilidad-generico-dispositivo-otro" label="Especifica el otro dispositivo" error="dispositivoOtro" required help="Hasta 500 caracteres. Se conserva en las observaciones del registro."><input id="movilidad-generico-dispositivo-otro" class="rm-input" wire:model="dispositivoOtro" maxlength="500" required aria-invalid="{{ $errors->has('dispositivoOtro') ? 'true' : 'false' }}" aria-describedby="movilidad-generico-dispositivo-otro-help @error('dispositivoOtro') movilidad-generico-dispositivo-otro-error @enderror"></x-ui.field>
                        @endif
                    @endif
                    @if(in_array($tipo, ['HIGIENE','PROCEDIMIENTO']))
                        <label class="rm-label">Resultado<select wire:model="resultado" class="rm-select"><option value="">Seleccione</option><option>REALIZADO</option><option>PARCIAL</option><option>NO_REALIZADO</option><option>CANCELADO</option></select></label>
                    @endif
                    @if($tipo === 'SUENO')
                        <label class="rm-label">Inicio<input wire:model="horaInicio" type="time" class="rm-input"></label>
                        <label class="rm-label">Fin<input wire:model="horaFin" type="time" class="rm-input">@error('horaFin')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                        <label class="rm-label">Despertares<input wire:model="cantidadDespertares" type="number" min="0" max="30" class="rm-input"></label>
                        <label class="rm-label">Calidad<select wire:model="calidad" class="rm-select"><option value="">No registrada</option><option>BUENA</option><option>FRAGMENTADA</option><option>INSOMNIO</option></select></label>
                        <div class="flex items-center gap-4 text-xs font-bold text-[var(--rm-text-title)]"><label><input wire:model="deambulacionNocturna" type="checkbox"> Deambulación</label><label><input wire:model="agitacion" type="checkbox"> Agitación</label></div>
                    @endif
                    @if($tipo !== 'MOVILIDAD')<label class="rm-label">Dolor 0–10<input wire:model="dolor" type="number" min="0" max="10" class="rm-input"></label>@endif
                    <label class="rm-label md:col-span-2">Motivo o incidencia<input wire:model="motivo" type="text" class="rm-input">@error('motivo')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                    <label class="rm-label md:col-span-2 lg:col-span-3">Observación complementaria<textarea wire:model="observacion" rows="3" class="rm-textarea"></textarea></label>
                </div>
                <div class="mt-5 flex justify-end"><button class="rm-btn-primary" @if($tipo === 'ELIMINACION') disabled @endif>Firmar registro</button></div>
            <x-ui.clinical-clear-action /></x-ui.card></form>
@endif
            @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="rectificarCuidado" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                <x-ui.section-header :level="2" title="Rectificar un registro firmado" icon="ph-clipboard-text" />
                <p class="mt-1 text-xs text-[var(--rm-text-muted)]">Complete arriba los valores correctos. Se creará un registro vinculado y el original permanecerá en el historial.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="rm-label">Registro original<select wire:model="registroRectificarId" class="rm-select"><option value="">Seleccione</option>@foreach($adulto->ejecucionesCuidado as $r)<option value="{{ $r->cod_ejecucion }}">{{ $r->fecha_hora_programada?->format('d/m H:i') ?? 'N/A' }} — {{ $r->resultado ?? $r->estado }} — {{ \Illuminate\Support\Str::limit($r->observacion, 30) }}</option>@endforeach</select></label>
                    <label class="rm-label">Motivo de rectificación<textarea wire:model="motivoRectificacion" rows="2" class="rm-textarea"></textarea></label>
                </div>
                <div class="mt-4 flex justify-end"><button class="rm-btn-secondary px-5 py-2.5 text-xs font-bold">Registrar rectificación</button></div>
            <x-ui.clinical-clear-action /></x-ui.card></form>
@endif
            @endif
        @elseif($seccion === 'DOLOR')
            @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="guardarDolor" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                <x-ui.section-header :level="2" title="Valoración y seguimiento del dolor" icon="ph-clipboard-text" />
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="rm-label">Etapa<select wire:model.live="faseDolor" class="rm-select"><option>VALORACION</option><option>INTERVENCION</option><option>REEVALUACION</option></select></label>
                    <label class="rm-label">Intensidad 0–10<input wire:model="intensidadDolor" type="number" min="0" max="10" class="rm-input"></label>
                    @if($faseDolor !== 'VALORACION')<label class="rm-label md:col-span-2">Valoración inicial<select wire:model="valoracionDolorId" class="rm-select"><option value="">Seleccione</option>@foreach($adulto->valoracionesDolor as $r)<option value="{{ $r->cod_valoracion_dolor }}">{{ $r->fecha_hora?->format('d/m H:i') ?? 'N/A' }} — dolor {{ $r->intensidad }}/10</option>@endforeach</select></label>@endif
                    <label class="rm-label md:col-span-2">{{ $faseDolor === 'INTERVENCION' ? 'Intervención realizada' : ($faseDolor === 'REEVALUACION' ? 'Hallazgos de reevaluación' : 'Características y localización') }}<textarea wire:model="detalleDolor" rows="3" class="rm-textarea"></textarea></label>
                    @if($faseDolor === 'REEVALUACION')<label class="rm-label md:col-span-2">Resultado<textarea wire:model="resultadoDolor" rows="2" class="rm-textarea"></textarea></label>@endif
                </div>
                <div class="mt-4 flex justify-end"><button class="rm-btn-primary">Firmar etapa</button></div>
            <x-ui.clinical-clear-action /></x-ui.card></form>
@endif
        
        @elseif($seccion === 'DISPOSITIVOS')
            <div class="grid gap-5 lg:grid-cols-2">
                @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="guardarDispositivo" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                    <x-ui.section-header :level="2" title="Registrar dispositivo" icon="ph-clipboard-text" />
                    <label class="mt-4 block rm-label">Tipo<select wire:model="tipoDispositivo" class="rm-select"><option>OXIGENO</option><option>SONDA_URINARIA</option><option>OSTOMIA</option><option>ALIMENTACION_ENTERAL</option><option>OTRO</option></select></label>
                    <label class="mt-3 block rm-label">Ubicación<input wire:model="ubicacionDispositivo" class="rm-input"></label>
                    <label class="mt-3 block rm-label">Indicación<textarea wire:model="indicacionDispositivo" rows="3" class="rm-textarea"></textarea>@error('indicacionDispositivo')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                    <button class="rm-btn-primary mt-4">Guardar dispositivo</button>
                <x-ui.clinical-clear-action /></x-ui.card></form>
@endif
                <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-bold text-[var(--rm-text-title)]">Dispositivos activos</h2>@if(!$modoConsulta)<label class="mt-3 block text-xs font-bold text-[var(--rm-text-title)]">Motivo del retiro<input wire:model="motivoRetiroDispositivo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2.5"></label>@endif<div class="mt-4 space-y-2">@forelse($adulto->dispositivosActivos as $d)<div class="flex items-center justify-between rounded-xl bg-[var(--rm-surface-soft)] p-3"><div><p class="text-sm font-bold text-[var(--rm-text-title)]">{{ str_replace('_',' ',$d->tipo) }}</p><p class="text-xs text-[var(--rm-text-muted)]">{{ $d->indicacion }}</p></div>@if(!$modoConsulta)<button wire:click="retirarDispositivo('{{ $d->cod_dispositivo }}')" class="text-xs font-bold text-[var(--rm-accent)]">Registrar retiro</button>@endif</div>@empty<p class="text-sm text-[var(--rm-text-muted)]">No hay dispositivos activos.</p>@endforelse</div></div>
            </div>
        @elseif($seccion === 'INCIDENTES')
            @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="guardarIncidente" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                <x-ui.section-header :level="2" title="Registrar incidente o caída" icon="ph-clipboard-text" />
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="rm-label">Tipo<select wire:model.live="tipoIncidente" class="rm-select"><option>CAIDA</option><option>GOLPE</option><option>ERROR_MEDICACION</option><option>LESION</option><option>CAMBIO_CLINICO</option><option>OTRO</option></select></label>
                    <label class="rm-label">Lugar<input wire:model="lugarIncidente" class="rm-input">@error('lugarIncidente')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                    <label class="rm-label">Actividad previa<input wire:model="actividadPrevia" class="rm-input"></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--rm-text-title)]"><input wire:model.live="presenciado" type="checkbox"> Fue presenciado</label>
                    @if($presenciado)<label class="rm-label">Testigo<input wire:model="testigo" class="rm-input">@error('testigo')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>@endif
                    <label class="rm-label">Dolor 0–10<input wire:model="dolorIncidente" type="number" min="0" max="10" class="rm-input"></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--rm-text-title)]"><input wire:model.live="hayLesion" type="checkbox"> Presenta lesión</label>
                    @if($hayLesion)
                        <label class="rm-label">Tipo de lesión<input wire:model="tipoLesion" class="rm-input"></label>
                        <label class="rm-label">Zona corporal<input wire:model="zonaLesion" class="rm-input">@error('zonaLesion')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                    @endif
                    <label class="rm-label md:col-span-2 lg:col-span-3">Descripción completa<textarea wire:model="descripcionIncidente" rows="4" class="rm-textarea"></textarea>@error('descripcionIncidente')<span class="text-xs text-[var(--rm-danger)]">{{ $message }}</span>@enderror</label>
                    <div class="flex flex-wrap gap-4 md:col-span-2 lg:col-span-3"><label class="rm-label"><input wire:model="cambioCognitivo" type="checkbox"> Cambio cognitivo</label><label class="rm-label"><input wire:model="medicoInformado" type="checkbox"> Médico informado</label><label class="rm-label"><input wire:model="familiarInformado" type="checkbox"> Familiar informado</label><label class="rm-label"><input wire:model="requiereSeguimiento" type="checkbox"> Requiere seguimiento</label></div>
                </div>
                <div class="mt-5 flex justify-end"><button class="rm-btn-primary">Registrar y generar alerta</button></div>
            <x-ui.clinical-clear-action /></x-ui.card></form>
@endif

            @if($adulto->incidentesClinicos->whereIn('estado', ['ABIERTO','EN_SEGUIMIENTO'])->isNotEmpty())
                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="seguimientoIncidente" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                        <x-ui.section-header :level="2" title="Seguimiento del incidente" icon="ph-clipboard-text" />
                        <select wire:model="incidenteId" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->incidentesClinicos->whereIn('estado',['ABIERTO','EN_SEGUIMIENTO']) as $i)<option value="{{ $i->cod_incidente }}">{{ $i->tipo }} · {{ $i->fecha_hora_evento->format('d/m H:i') }}</option>@endforeach</select>
                        <textarea wire:model="seguimientoIncidente" rows="3" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2.5" placeholder="Acción realizada, respuesta y pendientes"></textarea>
                        <button class="rm-btn-primary mt-3 px-5 py-2.5 text-xs font-bold">Pasar a seguimiento</button>
                    <x-ui.clinical-clear-action /></x-ui.card></form>
@endif
                    @if(!$modoConsulta)
<form x-data="rmClinicalCapture()" wire:submit="cerrarIncidente" class="rm-clinical-form rm-clinical-form--generic"><x-ui.card class="rm-clinical-form__section">
                        <x-ui.section-header :level="2" title="Cerrar incidente" icon="ph-clipboard-text" />
                        <select wire:model="incidenteId" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->incidentesClinicos->whereIn('estado',['ABIERTO','EN_SEGUIMIENTO']) as $i)<option value="{{ $i->cod_incidente }}">{{ $i->tipo }} · {{ $i->estado }}</option>@endforeach</select>
                        <textarea wire:model="evaluacionFinalIncidente" rows="2" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2.5" placeholder="Evaluación final"></textarea>
                        <textarea wire:model="resultadoIncidente" rows="2" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2.5" placeholder="Resultado y condición al cierre"></textarea>
                        <button class="rm-btn-secondary mt-3 px-5 py-2.5 text-xs font-bold">Cerrar con resultado</button>
                    <x-ui.clinical-clear-action /></x-ui.card></form>
@endif
                </div>
            @endif

            @include('livewire.cuidados.partials.heridas-curaciones')
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-bold text-[var(--rm-text-title)]">Cuidados firmados</h2><div class="mt-4 space-y-2">@forelse($adulto->ejecucionesCuidado as $r)<article class="rounded-xl bg-[var(--rm-surface-soft)] p-3"><div class="flex justify-between gap-2"><p class="text-xs font-bold text-[var(--rm-text-title)]">{{ str_replace('_',' ',$r->tipo) }} · {{ $r->subtipo }}</p><time class="text-[11px] text-[var(--rm-text-muted)]">{{ $r->fecha_hora_evento->format('d/m H:i') }}</time></div><p class="mt-1 text-xs text-[var(--rm-text-body)]">{{ $r->observacion ?: $r->motivo ?: 'Sin observación adicional' }}</p></article>@empty<p class="text-sm text-[var(--rm-text-muted)]">Sin registros estructurados.</p>@endforelse</div></section>
                <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-bold text-[var(--rm-text-title)]">Incidentes</h2><div class="mt-4 space-y-2">@forelse($adulto->incidentesClinicos as $i)<article class="rounded-xl bg-[var(--rm-surface-soft)] p-3"><div class="flex justify-between"><p class="text-xs font-bold text-[var(--rm-text-title)]">{{ str_replace('_',' ',$i->tipo) }}</p><span class="text-[11px] font-bold text-[var(--rm-accent)]">{{ $i->estado }}</span></div><p class="mt-1 text-xs text-[var(--rm-text-body)]">{{ $i->descripcion }}</p></article>@empty<p class="text-sm text-[var(--rm-text-muted)]">Sin incidentes registrados.</p>@endforelse</div></section>
            </div>
        @endif
    @endif
    @endif
    @endif
</div>

