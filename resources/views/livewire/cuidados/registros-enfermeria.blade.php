<div class="rm-pilot-enfermeria rm-page-layout font-sans space-y-5">
    <header class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--rm-accent)]">{{ $esSuperAdmin ? 'Supervisión asistencial' : 'Registro asistencial' }}</p>
                <h1 class="mt-1 text-2xl font-black text-[var(--rm-text-title)]">Cuidados, dispositivos e incidentes</h1>
                <p class="mt-1 text-sm text-[var(--rm-text-muted)]">Datos estructurados, firmados y vinculados al residente y al turno.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">Agenda</a>
                <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis pacientes' }}</a>
            </div>
        </div>
    </header>

    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm">
        <label class="text-xs font-bold text-[var(--rm-text-title)]" for="residente-registro">Residente</label>
        <select id="residente-registro" wire:model.live="codAm" class="mt-2 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-4 py-2.5 text-sm text-[var(--rm-text-body)] outline-none focus:border-[var(--rm-border)]-focus">
            <option value="">{{ $esSuperAdmin ? 'Seleccione cualquier residente' : 'Seleccione un residente asignado' }}</option>
            @foreach($pacientes as $paciente)
                <option value="{{ $paciente->cod_am }}">{{ $paciente->ap_paterno }} {{ $paciente->ap_materno }}, {{ $paciente->nombres }} · HC {{ $paciente->cod_am }}</option>
            @endforeach
        </select>
        @error('codAm') <p class="mt-1 text-xs font-bold text-estado-peligro">{{ $message }}</p> @enderror
    </section>

    @if($adulto)
        @if($errors->any())
            <div class="rounded-xl border border-estado-peligro bg-estado-peligroBg p-4 text-sm text-estado-peligro">
                <ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase text-[var(--rm-accent)]">Identificación segura</p>
                    <h2 class="text-lg font-black text-[var(--rm-text-title)]">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</h2>
                    <p class="text-xs text-[var(--rm-text-muted)]">Estado: {{ str_replace('_', ' ', $adulto->estado_operativo ?: 'EN_CENTRO') }}</p>
                </div>
                <a href="{{ route('admin.enfermeria.pacientes.ficha', $adulto->cod_am) }}" class="rm-btn-primary px-4 py-2 text-xs font-bold">Abrir Ficha 360°</a>
            </div>
            @if($adulto->dispositivosActivos->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($adulto->dispositivosActivos as $dispositivo)
                        <span class="rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-2.5 py-1 text-xs font-bold text-[var(--rm-text-body)]">{{ str_replace('_', ' ', $dispositivo->tipo) }}{{ $dispositivo->ubicacion ? ' · '.$dispositivo->ubicacion : '' }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        <nav class="flex flex-wrap gap-2 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-3 shadow-sm" aria-label="Tipos de registro">
            @foreach(['CUIDADOS' => 'Cuidados diarios', 'DOLOR' => 'Dolor', 'ESTADO' => 'Estado operativo', 'DISPOSITIVOS' => 'Dispositivos', 'INCIDENTES' => 'Incidentes y lesiones', 'HISTORIAL' => 'Historial'] as $clave => $etiqueta)
                <button wire:click="$set('seccion', '{{ $clave }}')" class="rounded-xl px-4 py-2 text-xs font-bold transition {{ $seccion === $clave ? 'bg-[var(--rm-primary)] text-inverso' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)]' }}">{{ $etiqueta }}</button>
            @endforeach
        </nav>

        @if($seccion === 'CUIDADOS')
            <form wire:submit="guardarCuidado" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                <h2 class="text-lg font-black text-[var(--rm-text-title)]">Nuevo cuidado</h2>
                <p class="mt-1 text-xs text-[var(--rm-text-muted)]">El registro se firma al guardar. Una corrección posterior debe crear una rectificación.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Tipo
                        <select wire:model.live="tipo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">
                            @foreach(['ALIMENTACION'=>'Alimentación','HIDRATACION'=>'Hidratación','ELIMINACION'=>'Eliminación','HIGIENE'=>'Higiene','MOVILIDAD'=>'Movilidad','SUENO'=>'Sueño','VALORACION_RAPIDA'=>'Valoración rápida','PROCEDIMIENTO'=>'Procedimiento'] as $v=>$e)<option value="{{ $v }}">{{ $e }}</option>@endforeach
                        </select>
                    </label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Detalle del cuidado
                        <input wire:model="subtipo" type="text" placeholder="Ej. Desayuno, baño, deambulación" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">
                        @error('subtipo') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Cambio respecto al basal
                        <select wire:model="cambioBasal" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option>SIN_CAMBIOS</option><option>MEJOR</option><option>PEOR</option><option>NO_EVALUABLE</option></select>
                    </label>
                    @if($tipo === 'VALORACION_RAPIDA')
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Estado general<select wire:model="estadoGeneral" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option>SIN_CAMBIOS</option><option>MEJOR</option><option>CON_CAMBIOS</option><option>NO_EVALUABLE</option></select>@error('estadoGeneral')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Conciencia<select wire:model="conciencia" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Sin cambio</option><option>ALERTA</option><option>SOMNOLENCIA</option><option>RESPUESTA_DISMINUIDA</option></select></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Cognición<select wire:model="cognicion" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Sin cambio</option><option>ORIENTADO</option><option>CONFUSION_NUEVA</option><option>DESORIENTACION</option></select></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Conducta<select wire:model="conducta" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Sin cambio</option><option>TRANQUILO</option><option>AGITADO</option><option>APATICO</option><option>AGRESIVO</option></select></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Respiración<select wire:model="respiracion" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Sin cambio</option><option>NORMAL</option><option>DISNEA</option><option>TAQUIPNEA</option><option>SECRECIONES</option></select></label>
                    @endif
                    @if($tipo === 'ALIMENTACION')
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Porcentaje consumido
                            <select wire:model="porcentaje" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option value="">Seleccione</option>@foreach([0,25,50,75,100] as $p)<option value="{{ $p }}">{{ $p }}%</option>@endforeach</select>
                        </label>
                    @endif
                    @if($tipo === 'HIDRATACION')
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Cantidad en ml<input wire:model="cantidadMl" type="number" min="1" max="10000" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">@error('cantidadMl')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Tolerancia<select wire:model="tolerancia" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">No registrada</option><option>ADECUADA</option><option>PARCIAL</option><option>RECHAZO</option><option>NAUSEAS</option></select></label>
                    @endif
                    @if($tipo === 'ELIMINACION')
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Consistencia<input wire:model="consistencia" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                        <div class="flex flex-wrap items-center gap-4 md:col-span-2 text-xs font-bold text-[var(--rm-text-title)]"><label><input wire:model="esContinente" type="checkbox"> Continente</label><label><input wire:model="presentaDificultad" type="checkbox"> Dificultad</label><label><input wire:model="presentaDolor" type="checkbox"> Dolor</label><label><input wire:model="usaDispositivo" type="checkbox"> Usa dispositivo</label></div>
                    @endif
                    @if(in_array($tipo, ['ALIMENTACION','MOVILIDAD','HIGIENE']))
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Nivel de ayuda
                            <select wire:model="nivelAyuda" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option value="">No aplica</option><option>INDEPENDIENTE</option><option>SUPERVISION</option><option>PARCIAL</option><option>COMPLETA</option><option>UNA_PERSONA</option><option>DOS_PERSONAS</option></select>
                        </label>
                    @endif
                    @if($tipo === 'MOVILIDAD')
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Ayuda técnica<input wire:model="ayudaTecnica" placeholder="Andador, bastón, silla" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                    @endif
                    @if(in_array($tipo, ['HIGIENE','PROCEDIMIENTO']))
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Resultado<select wire:model="resultado" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option value="">Seleccione</option><option>REALIZADO</option><option>PARCIAL</option><option>NO_REALIZADO</option><option>CANCELADO</option></select></label>
                    @endif
                    @if($tipo === 'SUENO')
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Inicio<input wire:model="horaInicio" type="time" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Fin<input wire:model="horaFin" type="time" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">@error('horaFin')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Despertares<input wire:model="cantidadDespertares" type="number" min="0" max="30" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Calidad<select wire:model="calidad" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">No registrada</option><option>BUENA</option><option>FRAGMENTADA</option><option>INSOMNIO</option></select></label>
                        <div class="flex items-center gap-4 text-xs font-bold text-[var(--rm-text-title)]"><label><input wire:model="deambulacionNocturna" type="checkbox"> Deambulación</label><label><input wire:model="agitacion" type="checkbox"> Agitación</label></div>
                    @endif
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Dolor 0–10<input wire:model="dolor" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2">Motivo o incidencia<input wire:model="motivo" type="text" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">@error('motivo')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2 lg:col-span-3">Observación complementaria<textarea wire:model="observacion" rows="3" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></textarea></label>
                </div>
                <div class="mt-5 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Firmar registro</button></div>
            </form>
            <form wire:submit="rectificarCuidado" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                <h2 class="text-lg font-black text-[var(--rm-text-title)]">Rectificar un registro firmado</h2>
                <p class="mt-1 text-xs text-[var(--rm-text-muted)]">Complete arriba los valores correctos. Se creará un registro vinculado y el original permanecerá en el historial.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Registro original<select wire:model="registroRectificarId" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->registrosCuidados->where('estado','FIRMADO') as $r)<option value="{{ $r->cod_registro_cuidado }}">{{ $r->fecha_hora_evento->format('d/m H:i') }} · {{ $r->tipo }} · {{ $r->subtipo }}</option>@endforeach</select></label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Motivo de rectificación<textarea wire:model="motivoRectificacion" rows="2" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></textarea></label>
                </div>
                <div class="mt-4 flex justify-end"><button class="rm-btn-secondary px-5 py-2.5 text-xs font-bold">Registrar rectificación</button></div>
            </form>
        @elseif($seccion === 'DOLOR')
            <form wire:submit="guardarDolor" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                <h2 class="text-lg font-black text-[var(--rm-text-title)]">Valoración y seguimiento del dolor</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Etapa<select wire:model.live="faseDolor" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option>VALORACION</option><option>INTERVENCION</option><option>REEVALUACION</option></select></label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Intensidad 0–10<input wire:model="intensidadDolor" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                    @if($faseDolor !== 'VALORACION')<label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2">Valoración inicial<select wire:model="valoracionDolorId" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->registrosCuidados->where('tipo','DOLOR')->where('fase_dolor','VALORACION') as $r)<option value="{{ $r->cod_registro_cuidado }}">{{ $r->fecha_hora_evento->format('d/m H:i') }} · dolor {{ $r->dolor }}/10</option>@endforeach</select></label>@endif
                    <label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2">{{ $faseDolor === 'INTERVENCION' ? 'Intervención realizada' : ($faseDolor === 'REEVALUACION' ? 'Hallazgos de reevaluación' : 'Características y localización') }}<textarea wire:model="detalleDolor" rows="3" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></textarea></label>
                    @if($faseDolor === 'REEVALUACION')<label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2">Resultado<textarea wire:model="resultadoDolor" rows="2" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></textarea></label>@endif
                </div>
                <div class="mt-4 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Firmar etapa</button></div>
            </form>
        @elseif($seccion === 'ESTADO')
            <form wire:submit="cambiarEstadoOperativo" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                <h2 class="text-lg font-black text-[var(--rm-text-title)]">Disponibilidad institucional</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Estado<select wire:model="estadoOperativo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option>EN_CENTRO</option><option>SALIDA_TEMPORAL</option><option>HOSPITALIZADO</option><option>EGRESADO</option><option>FALLECIDO</option></select></label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Motivo<textarea wire:model="motivoEstado" rows="3" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></textarea>@error('motivoEstado')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                </div>
                <div class="mt-4 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Registrar cambio</button></div>
            </form>
        @elseif($seccion === 'DISPOSITIVOS')
            <div class="grid gap-5 lg:grid-cols-2">
                <form wire:submit="guardarDispositivo" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                    <h2 class="text-lg font-black text-[var(--rm-text-title)]">Registrar dispositivo</h2>
                    <label class="mt-4 block text-xs font-bold text-[var(--rm-text-title)]">Tipo<select wire:model="tipoDispositivo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option>OXIGENO</option><option>SONDA_URINARIA</option><option>OSTOMIA</option><option>ALIMENTACION_ENTERAL</option><option>OTRO</option></select></label>
                    <label class="mt-3 block text-xs font-bold text-[var(--rm-text-title)]">Ubicación<input wire:model="ubicacionDispositivo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                    <label class="mt-3 block text-xs font-bold text-[var(--rm-text-title)]">Indicación<textarea wire:model="indicacionDispositivo" rows="3" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></textarea>@error('indicacionDispositivo')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <button class="rm-btn-primary mt-4 px-5 py-2.5 text-xs font-bold">Guardar dispositivo</button>
                </form>
                <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-black text-[var(--rm-text-title)]">Dispositivos activos</h2><label class="mt-3 block text-xs font-bold text-[var(--rm-text-title)]">Motivo del retiro<input wire:model="motivoRetiroDispositivo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label><div class="mt-4 space-y-2">@forelse($adulto->dispositivosActivos as $d)<div class="flex items-center justify-between rounded-xl bg-[var(--rm-surface-alt)] p-3"><div><p class="text-sm font-bold text-[var(--rm-text-title)]">{{ str_replace('_',' ',$d->tipo) }}</p><p class="text-xs text-[var(--rm-text-muted)]">{{ $d->indicacion }}</p></div><button wire:click="retirarDispositivo('{{ $d->cod_dispositivo }}')" class="text-xs font-bold text-[var(--rm-accent)]">Registrar retiro</button></div>@empty<p class="text-sm text-[var(--rm-text-muted)]">No hay dispositivos activos.</p>@endforelse</div></div>
            </div>
        @elseif($seccion === 'INCIDENTES')
            <form wire:submit="guardarIncidente" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                <h2 class="text-lg font-black text-[var(--rm-text-title)]">Registrar incidente o caída</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Tipo<select wire:model.live="tipoIncidente" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"><option>CAIDA</option><option>GOLPE</option><option>ERROR_MEDICACION</option><option>LESION</option><option>CAMBIO_CLINICO</option><option>OTRO</option></select></label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Lugar<input wire:model="lugarIncidente" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">@error('lugarIncidente')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Actividad previa<input wire:model="actividadPrevia" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--rm-text-title)]"><input wire:model.live="presenciado" type="checkbox"> Fue presenciado</label>
                    @if($presenciado)<label class="text-xs font-bold text-[var(--rm-text-title)]">Testigo<input wire:model="testigo" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">@error('testigo')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>@endif
                    <label class="text-xs font-bold text-[var(--rm-text-title)]">Dolor 0–10<input wire:model="dolorIncidente" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-[var(--rm-text-title)]"><input wire:model.live="hayLesion" type="checkbox"> Presenta lesión</label>
                    @if($hayLesion)
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Tipo de lesión<input wire:model="tipoLesion" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Zona corporal<input wire:model="zonaLesion" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm">@error('zonaLesion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    @endif
                    <label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2 lg:col-span-3">Descripción completa<textarea wire:model="descripcionIncidente" rows="4" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5 text-sm"></textarea>@error('descripcionIncidente')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <div class="flex flex-wrap gap-4 md:col-span-2 lg:col-span-3"><label class="text-xs font-bold text-[var(--rm-text-title)]"><input wire:model="cambioCognitivo" type="checkbox"> Cambio cognitivo</label><label class="text-xs font-bold text-[var(--rm-text-title)]"><input wire:model="medicoInformado" type="checkbox"> Médico informado</label><label class="text-xs font-bold text-[var(--rm-text-title)]"><input wire:model="familiarInformado" type="checkbox"> Familiar informado</label><label class="text-xs font-bold text-[var(--rm-text-title)]"><input wire:model="requiereSeguimiento" type="checkbox"> Requiere seguimiento</label></div>
                </div>
                <div class="mt-5 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Registrar y generar alerta</button></div>
            </form>

            @if($adulto->incidentes->whereIn('estado', ['ABIERTO','EN_SEGUIMIENTO'])->isNotEmpty())
                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <form wire:submit="seguimientoIncidente" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                        <h2 class="text-lg font-black text-[var(--rm-text-title)]">Seguimiento del incidente</h2>
                        <select wire:model="incidenteId" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->incidentes->whereIn('estado',['ABIERTO','EN_SEGUIMIENTO']) as $i)<option value="{{ $i->cod_incidente }}">{{ $i->tipo }} · {{ $i->fecha_hora_evento->format('d/m H:i') }}</option>@endforeach</select>
                        <textarea wire:model="seguimientoIncidente" rows="3" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5" placeholder="Acción realizada, respuesta y pendientes"></textarea>
                        <button class="rm-btn-primary mt-3 px-5 py-2.5 text-xs font-bold">Pasar a seguimiento</button>
                    </form>
                    <form wire:submit="cerrarIncidente" class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                        <h2 class="text-lg font-black text-[var(--rm-text-title)]">Cerrar incidente</h2>
                        <select wire:model="incidenteId" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->incidentes->whereIn('estado',['ABIERTO','EN_SEGUIMIENTO']) as $i)<option value="{{ $i->cod_incidente }}">{{ $i->tipo }} · {{ $i->estado }}</option>@endforeach</select>
                        <textarea wire:model="evaluacionFinalIncidente" rows="2" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5" placeholder="Evaluación final"></textarea>
                        <textarea wire:model="resultadoIncidente" rows="2" class="mt-3 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5" placeholder="Resultado y condición al cierre"></textarea>
                        <button class="rm-btn-secondary mt-3 px-5 py-2.5 text-xs font-bold">Cerrar con resultado</button>
                    </form>
                </div>
            @endif

            @if($adulto->lesiones->where('estado', 'ACTIVA')->isNotEmpty())
                <form wire:submit="guardarSeguimientoLesion" class="mt-5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm">
                    <h2 class="text-lg font-black text-[var(--rm-text-title)]">Seguimiento de lesión</h2>
                    <p class="mt-1 text-xs text-[var(--rm-text-muted)]">Cada control crea una medición nueva y conserva todas las anteriores.</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <label class="text-xs font-bold text-[var(--rm-text-title)] lg:col-span-2">Lesión activa<select wire:model="lesionId" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->lesiones->where('estado','ACTIVA') as $lesion)<option value="{{ $lesion->cod_lesion }}">{{ $lesion->tipo }} · {{ $lesion->zona_corporal }}</option>@endforeach</select></label>
                        <label class="flex items-center gap-2 pt-6 text-xs font-bold text-[var(--rm-text-title)]"><input wire:model.live="lesionMedible" type="checkbox"> La lesión es medible</label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Dolor 0–10<input wire:model="dolorLesion" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                        @if($lesionMedible)
                            <label class="text-xs font-bold text-[var(--rm-text-title)]">Largo cm<input wire:model="largoLesion" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                            <label class="text-xs font-bold text-[var(--rm-text-title)]">Ancho cm<input wire:model="anchoLesion" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                            <label class="text-xs font-bold text-[var(--rm-text-title)]">Profundidad cm<input wire:model="profundidadLesion" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                        @endif
                        <label class="text-xs font-bold text-[var(--rm-text-title)]">Exudado<input wire:model="exudadoLesion" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2">Aspecto actual<textarea wire:model="aspectoLesion" rows="2" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></textarea>@error('aspectoLesion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-[var(--rm-text-title)] md:col-span-2">Acción realizada<textarea wire:model="accionLesion" rows="2" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5"></textarea>@error('accionLesion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    </div>
                    <div class="mt-4 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Guardar seguimiento</button></div>
                </form>
                <section class="mt-5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-black text-[var(--rm-text-title)]">Cierre de lesión</h2><div class="mt-3 grid gap-3 md:grid-cols-2"><textarea wire:model="resultadoCierreLesion" rows="2" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5" placeholder="Resultado clínico al cierre"></textarea><textarea wire:model="motivoCierreLesion" rows="2" class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2.5" placeholder="Motivo del cierre"></textarea></div><div class="mt-3 flex flex-wrap gap-2">@foreach($adulto->lesiones->where('estado','ACTIVA') as $lesion)<button type="button" wire:click="cerrarLesion('{{ $lesion->cod_lesion }}')" class="rm-btn-secondary px-3 py-2 text-xs font-bold">Cerrar {{ $lesion->zona_corporal }}</button>@endforeach</div></section>
            @endif
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-black text-[var(--rm-text-title)]">Cuidados firmados</h2><div class="mt-4 space-y-2">@forelse($adulto->registrosCuidados as $r)<article class="rounded-xl bg-[var(--rm-surface-alt)] p-3"><div class="flex justify-between gap-2"><p class="text-xs font-black text-[var(--rm-text-title)]">{{ str_replace('_',' ',$r->tipo) }} · {{ $r->subtipo }}</p><time class="text-[11px] text-[var(--rm-text-muted)]">{{ $r->fecha_hora_evento->format('d/m H:i') }}</time></div><p class="mt-1 text-xs text-[var(--rm-text-body)]">{{ $r->observacion ?: $r->motivo ?: 'Sin observación adicional' }}</p></article>@empty<p class="text-sm text-[var(--rm-text-muted)]">Sin registros estructurados.</p>@endforelse</div></section>
                <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm"><h2 class="text-lg font-black text-[var(--rm-text-title)]">Incidentes</h2><div class="mt-4 space-y-2">@forelse($adulto->incidentes as $i)<article class="rounded-xl bg-[var(--rm-surface-alt)] p-3"><div class="flex justify-between"><p class="text-xs font-black text-[var(--rm-text-title)]">{{ str_replace('_',' ',$i->tipo) }}</p><span class="text-[11px] font-bold text-[var(--rm-accent)]">{{ $i->estado }}</span></div><p class="mt-1 text-xs text-[var(--rm-text-body)]">{{ $i->descripcion }}</p></article>@empty<p class="text-sm text-[var(--rm-text-muted)]">Sin incidentes registrados.</p>@endforelse</div></section>
            </div>
        @endif
    @endif
</div>

