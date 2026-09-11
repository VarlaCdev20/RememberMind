<div class="space-y-5">
    <header class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-boton-acento">{{ $esSuperAdmin ? 'Supervisión asistencial' : 'Registro asistencial' }}</p>
                <h1 class="mt-1 text-2xl font-black text-titulo">Cuidados, dispositivos e incidentes</h1>
                <p class="mt-1 text-sm text-apoyo">Datos estructurados, firmados y vinculados al residente y al turno.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.enfermeria.agenda') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">Agenda</a>
                <a href="{{ route('admin.enfermeria.pacientes') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">{{ $esSuperAdmin ? 'Todos los residentes' : 'Mis pacientes' }}</a>
            </div>
        </div>
    </header>

    <section class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
        <label class="text-xs font-bold text-titulo" for="residente-registro">Residente</label>
        <select id="residente-registro" wire:model.live="codAm" class="mt-2 w-full rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm text-parrafo outline-none focus:border-borde-focus">
            <option value="">{{ $esSuperAdmin ? 'Seleccione cualquier residente' : 'Seleccione un residente asignado' }}</option>
            @foreach($pacientes as $paciente)
                <option value="{{ $paciente->cod_am }}">{{ $paciente->ap_paterno }} {{ $paciente->ap_materno }}, {{ $paciente->nombres }} · HC {{ $paciente->cod_am }}</option>
            @endforeach
        </select>
        @error('codAm') <p class="mt-1 text-xs font-bold text-estado-peligro">{{ $message }}</p> @enderror
    </section>

    @if($adulto)
        <section class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase text-boton-acento">Identificación segura</p>
                    <h2 class="text-lg font-black text-titulo">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</h2>
                    <p class="text-xs text-apoyo">HC {{ $adulto->cod_am }} · Estado: {{ str_replace('_', ' ', $adulto->estado_operativo ?: 'EN_CENTRO') }}</p>
                </div>
                <a href="{{ route('admin.enfermeria.pacientes.ficha', $adulto->cod_am) }}" class="rm-btn-primary px-4 py-2 text-xs font-bold">Abrir Ficha 360°</a>
            </div>
            @if($adulto->dispositivosActivos->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($adulto->dispositivosActivos as $dispositivo)
                        <span class="rounded-lg border border-borde bg-fondo-card px-2.5 py-1 text-xs font-bold text-parrafo">{{ str_replace('_', ' ', $dispositivo->tipo) }}{{ $dispositivo->ubicacion ? ' · '.$dispositivo->ubicacion : '' }}</span>
                    @endforeach
                </div>
            @endif
        </section>

        <nav class="flex flex-wrap gap-2 rounded-2xl border border-borde bg-fondo-panel p-3 shadow-sm" aria-label="Tipos de registro">
            @foreach(['CUIDADOS' => 'Cuidados diarios', 'ESTADO' => 'Estado operativo', 'DISPOSITIVOS' => 'Dispositivos', 'INCIDENTES' => 'Incidentes y caídas', 'HISTORIAL' => 'Historial'] as $clave => $etiqueta)
                <button wire:click="$set('seccion', '{{ $clave }}')" class="rounded-xl px-4 py-2 text-xs font-bold transition {{ $seccion === $clave ? 'bg-boton-principal text-inverso' : 'bg-fondo-card text-parrafo hover:text-titulo' }}">{{ $etiqueta }}</button>
            @endforeach
        </nav>

        @if($seccion === 'CUIDADOS')
            <form wire:submit="guardarCuidado" class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
                <h2 class="text-lg font-black text-titulo">Nuevo cuidado</h2>
                <p class="mt-1 text-xs text-apoyo">El registro se firma al guardar. Una corrección posterior debe crear una rectificación.</p>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="text-xs font-bold text-titulo">Tipo
                        <select wire:model.live="tipo" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">
                            @foreach(['ALIMENTACION'=>'Alimentación','HIDRATACION'=>'Hidratación','ELIMINACION'=>'Eliminación','HIGIENE'=>'Higiene','MOVILIDAD'=>'Movilidad','SUENO'=>'Sueño','VALORACION_RAPIDA'=>'Valoración rápida','PROCEDIMIENTO'=>'Procedimiento'] as $v=>$e)<option value="{{ $v }}">{{ $e }}</option>@endforeach
                        </select>
                    </label>
                    <label class="text-xs font-bold text-titulo">Detalle del cuidado
                        <input wire:model="subtipo" type="text" placeholder="Ej. Desayuno, baño, deambulación" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">
                        @error('subtipo') <span class="text-xs text-estado-peligro">{{ $message }}</span> @enderror
                    </label>
                    <label class="text-xs font-bold text-titulo">Cambio respecto al basal
                        <select wire:model="cambioBasal" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option>SIN_CAMBIOS</option><option>MEJOR</option><option>PEOR</option><option>NO_EVALUABLE</option></select>
                    </label>
                    @if($tipo === 'VALORACION_RAPIDA')
                        <label class="text-xs font-bold text-titulo">Estado general<select wire:model="estadoGeneral" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option>SIN_CAMBIOS</option><option>MEJOR</option><option>CON_CAMBIOS</option><option>NO_EVALUABLE</option></select>@error('estadoGeneral')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-titulo">Conciencia<select wire:model="conciencia" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">Sin cambio</option><option>ALERTA</option><option>SOMNOLENCIA</option><option>RESPUESTA_DISMINUIDA</option></select></label>
                        <label class="text-xs font-bold text-titulo">Cognición<select wire:model="cognicion" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">Sin cambio</option><option>ORIENTADO</option><option>CONFUSION_NUEVA</option><option>DESORIENTACION</option></select></label>
                        <label class="text-xs font-bold text-titulo">Conducta<select wire:model="conducta" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">Sin cambio</option><option>TRANQUILO</option><option>AGITADO</option><option>APATICO</option><option>AGRESIVO</option></select></label>
                        <label class="text-xs font-bold text-titulo">Respiración<select wire:model="respiracion" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">Sin cambio</option><option>NORMAL</option><option>DISNEA</option><option>TAQUIPNEA</option><option>SECRECIONES</option></select></label>
                    @endif
                    @if($tipo === 'ALIMENTACION')
                        <label class="text-xs font-bold text-titulo">Porcentaje consumido
                            <select wire:model="porcentaje" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option value="">Seleccione</option>@foreach([0,25,50,75,100] as $p)<option value="{{ $p }}">{{ $p }}%</option>@endforeach</select>
                        </label>
                    @endif
                    @if($tipo === 'HIDRATACION')
                        <label class="text-xs font-bold text-titulo">Cantidad en ml<input wire:model="cantidadMl" type="number" min="1" max="10000" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">@error('cantidadMl')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-titulo">Tolerancia<select wire:model="tolerancia" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">No registrada</option><option>ADECUADA</option><option>PARCIAL</option><option>RECHAZO</option><option>NAUSEAS</option></select></label>
                    @endif
                    @if($tipo === 'ELIMINACION')
                        <label class="text-xs font-bold text-titulo">Consistencia<input wire:model="consistencia" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                        <div class="flex flex-wrap items-center gap-4 md:col-span-2 text-xs font-bold text-titulo"><label><input wire:model="esContinente" type="checkbox"> Continente</label><label><input wire:model="presentaDificultad" type="checkbox"> Dificultad</label><label><input wire:model="presentaDolor" type="checkbox"> Dolor</label><label><input wire:model="usaDispositivo" type="checkbox"> Usa dispositivo</label></div>
                    @endif
                    @if(in_array($tipo, ['ALIMENTACION','MOVILIDAD','HIGIENE']))
                        <label class="text-xs font-bold text-titulo">Nivel de ayuda
                            <select wire:model="nivelAyuda" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option value="">No aplica</option><option>INDEPENDIENTE</option><option>SUPERVISION</option><option>PARCIAL</option><option>COMPLETA</option><option>UNA_PERSONA</option><option>DOS_PERSONAS</option></select>
                        </label>
                    @endif
                    @if($tipo === 'MOVILIDAD')
                        <label class="text-xs font-bold text-titulo">Ayuda técnica<input wire:model="ayudaTecnica" placeholder="Andador, bastón, silla" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                    @endif
                    @if(in_array($tipo, ['HIGIENE','PROCEDIMIENTO']))
                        <label class="text-xs font-bold text-titulo">Resultado<select wire:model="resultado" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option value="">Seleccione</option><option>REALIZADO</option><option>PARCIAL</option><option>NO_REALIZADO</option><option>CANCELADO</option></select></label>
                    @endif
                    @if($tipo === 'SUENO')
                        <label class="text-xs font-bold text-titulo">Inicio<input wire:model="horaInicio" type="time" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                        <label class="text-xs font-bold text-titulo">Fin<input wire:model="horaFin" type="time" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">@error('horaFin')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-titulo">Despertares<input wire:model="cantidadDespertares" type="number" min="0" max="30" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                        <label class="text-xs font-bold text-titulo">Calidad<select wire:model="calidad" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">No registrada</option><option>BUENA</option><option>FRAGMENTADA</option><option>INSOMNIO</option></select></label>
                        <div class="flex items-center gap-4 text-xs font-bold text-titulo"><label><input wire:model="deambulacionNocturna" type="checkbox"> Deambulación</label><label><input wire:model="agitacion" type="checkbox"> Agitación</label></div>
                    @endif
                    <label class="text-xs font-bold text-titulo">Dolor 0–10<input wire:model="dolor" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                    <label class="text-xs font-bold text-titulo md:col-span-2">Motivo o incidencia<input wire:model="motivo" type="text" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">@error('motivo')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <label class="text-xs font-bold text-titulo md:col-span-2 lg:col-span-3">Observación complementaria<textarea wire:model="observacion" rows="3" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></textarea></label>
                </div>
                <div class="mt-5 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Firmar registro</button></div>
            </form>
        @elseif($seccion === 'ESTADO')
            <form wire:submit="cambiarEstadoOperativo" class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
                <h2 class="text-lg font-black text-titulo">Disponibilidad institucional</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <label class="text-xs font-bold text-titulo">Estado<select wire:model="estadoOperativo" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option>EN_CENTRO</option><option>SALIDA_TEMPORAL</option><option>HOSPITALIZADO</option><option>EGRESADO</option><option>FALLECIDO</option></select></label>
                    <label class="text-xs font-bold text-titulo">Motivo<textarea wire:model="motivoEstado" rows="3" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></textarea>@error('motivoEstado')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                </div>
                <div class="mt-4 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Registrar cambio</button></div>
            </form>
        @elseif($seccion === 'DISPOSITIVOS')
            <div class="grid gap-5 lg:grid-cols-2">
                <form wire:submit="guardarDispositivo" class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
                    <h2 class="text-lg font-black text-titulo">Registrar dispositivo</h2>
                    <label class="mt-4 block text-xs font-bold text-titulo">Tipo<select wire:model="tipoDispositivo" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option>OXIGENO</option><option>SONDA_URINARIA</option><option>OSTOMIA</option><option>ALIMENTACION_ENTERAL</option><option>OTRO</option></select></label>
                    <label class="mt-3 block text-xs font-bold text-titulo">Ubicación<input wire:model="ubicacionDispositivo" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                    <label class="mt-3 block text-xs font-bold text-titulo">Indicación<textarea wire:model="indicacionDispositivo" rows="3" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></textarea>@error('indicacionDispositivo')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <button class="rm-btn-primary mt-4 px-5 py-2.5 text-xs font-bold">Guardar dispositivo</button>
                </form>
                <div class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm"><h2 class="text-lg font-black text-titulo">Dispositivos activos</h2><div class="mt-4 space-y-2">@forelse($adulto->dispositivosActivos as $d)<div class="flex items-center justify-between rounded-xl bg-fondo-card p-3"><div><p class="text-sm font-bold text-titulo">{{ str_replace('_',' ',$d->tipo) }}</p><p class="text-xs text-apoyo">{{ $d->indicacion }}</p></div><button wire:click="retirarDispositivo('{{ $d->cod_dispositivo }}')" class="text-xs font-bold text-boton-acento">Registrar retiro</button></div>@empty<p class="text-sm text-apoyo">No hay dispositivos activos.</p>@endforelse</div></div>
            </div>
        @elseif($seccion === 'INCIDENTES')
            <form wire:submit="guardarIncidente" class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
                <h2 class="text-lg font-black text-titulo">Registrar incidente o caída</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <label class="text-xs font-bold text-titulo">Tipo<select wire:model.live="tipoIncidente" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"><option>CAIDA</option><option>GOLPE</option><option>ERROR_MEDICACION</option><option>LESION</option><option>CAMBIO_CLINICO</option><option>OTRO</option></select></label>
                    <label class="text-xs font-bold text-titulo">Lugar<input wire:model="lugarIncidente" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">@error('lugarIncidente')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <label class="text-xs font-bold text-titulo">Actividad previa<input wire:model="actividadPrevia" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-titulo"><input wire:model.live="presenciado" type="checkbox"> Fue presenciado</label>
                    @if($presenciado)<label class="text-xs font-bold text-titulo">Testigo<input wire:model="testigo" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">@error('testigo')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>@endif
                    <label class="text-xs font-bold text-titulo">Dolor 0–10<input wire:model="dolorIncidente" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                    <label class="flex items-center gap-2 text-xs font-bold text-titulo"><input wire:model.live="hayLesion" type="checkbox"> Presenta lesión</label>
                    @if($hayLesion)
                        <label class="text-xs font-bold text-titulo">Tipo de lesión<input wire:model="tipoLesion" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></label>
                        <label class="text-xs font-bold text-titulo">Zona corporal<input wire:model="zonaLesion" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm">@error('zonaLesion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    @endif
                    <label class="text-xs font-bold text-titulo md:col-span-2 lg:col-span-3">Descripción completa<textarea wire:model="descripcionIncidente" rows="4" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5 text-sm"></textarea>@error('descripcionIncidente')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    <div class="flex flex-wrap gap-4 md:col-span-2 lg:col-span-3"><label class="text-xs font-bold text-titulo"><input wire:model="cambioCognitivo" type="checkbox"> Cambio cognitivo</label><label class="text-xs font-bold text-titulo"><input wire:model="medicoInformado" type="checkbox"> Médico informado</label><label class="text-xs font-bold text-titulo"><input wire:model="familiarInformado" type="checkbox"> Familiar informado</label><label class="text-xs font-bold text-titulo"><input wire:model="requiereSeguimiento" type="checkbox"> Requiere seguimiento</label></div>
                </div>
                <div class="mt-5 flex justify-end"><button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Registrar y generar alerta</button></div>
            </form>

            @if($adulto->lesiones->where('estado', 'ACTIVA')->isNotEmpty())
                <form wire:submit="guardarSeguimientoLesion" class="mt-5 rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm">
                    <h2 class="text-lg font-black text-titulo">Seguimiento de lesión</h2>
                    <p class="mt-1 text-xs text-apoyo">Cada control crea una medición nueva y conserva todas las anteriores.</p>
                    <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <label class="text-xs font-bold text-titulo lg:col-span-2">Lesión activa<select wire:model="lesionId" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"><option value="">Seleccione</option>@foreach($adulto->lesiones->where('estado','ACTIVA') as $lesion)<option value="{{ $lesion->cod_lesion }}">{{ $lesion->tipo }} · {{ $lesion->zona_corporal }}</option>@endforeach</select></label>
                        <label class="flex items-center gap-2 pt-6 text-xs font-bold text-titulo"><input wire:model.live="lesionMedible" type="checkbox"> La lesión es medible</label>
                        <label class="text-xs font-bold text-titulo">Dolor 0–10<input wire:model="dolorLesion" type="number" min="0" max="10" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                        @if($lesionMedible)
                            <label class="text-xs font-bold text-titulo">Largo cm<input wire:model="largoLesion" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                            <label class="text-xs font-bold text-titulo">Ancho cm<input wire:model="anchoLesion" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                            <label class="text-xs font-bold text-titulo">Profundidad cm<input wire:model="profundidadLesion" type="number" step="0.01" min="0" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                        @endif
                        <label class="text-xs font-bold text-titulo">Exudado<input wire:model="exudadoLesion" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></label>
                        <label class="text-xs font-bold text-titulo md:col-span-2">Aspecto actual<textarea wire:model="aspectoLesion" rows="2" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></textarea>@error('aspectoLesion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                        <label class="text-xs font-bold text-titulo md:col-span-2">Acción realizada<textarea wire:model="accionLesion" rows="2" class="mt-1 w-full rounded-xl border border-borde bg-fondo-card px-3 py-2.5"></textarea>@error('accionLesion')<span class="text-xs text-estado-peligro">{{ $message }}</span>@enderror</label>
                    </div>
                    <div class="mt-4 flex flex-wrap justify-end gap-2">@foreach($adulto->lesiones->where('estado','ACTIVA') as $lesion)<button type="button" wire:click="cerrarLesion('{{ $lesion->cod_lesion }}')" class="rm-btn-secondary px-3 py-2 text-xs font-bold">Cerrar {{ $lesion->zona_corporal }}</button>@endforeach<button class="rm-btn-primary px-5 py-2.5 text-xs font-bold">Guardar seguimiento</button></div>
                </form>
            @endif
        @else
            <div class="grid gap-5 lg:grid-cols-2">
                <section class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm"><h2 class="text-lg font-black text-titulo">Cuidados firmados</h2><div class="mt-4 space-y-2">@forelse($adulto->registrosCuidados as $r)<article class="rounded-xl bg-fondo-card p-3"><div class="flex justify-between gap-2"><p class="text-xs font-black text-titulo">{{ str_replace('_',' ',$r->tipo) }} · {{ $r->subtipo }}</p><time class="text-[11px] text-apoyo">{{ $r->fecha_hora_evento->format('d/m H:i') }}</time></div><p class="mt-1 text-xs text-parrafo">{{ $r->observacion ?: $r->motivo ?: 'Sin observación adicional' }}</p></article>@empty<p class="text-sm text-apoyo">Sin registros estructurados.</p>@endforelse</div></section>
                <section class="rounded-2xl border border-borde bg-fondo-panel p-5 shadow-sm"><h2 class="text-lg font-black text-titulo">Incidentes</h2><div class="mt-4 space-y-2">@forelse($adulto->incidentes as $i)<article class="rounded-xl bg-fondo-card p-3"><div class="flex justify-between"><p class="text-xs font-black text-titulo">{{ str_replace('_',' ',$i->tipo) }}</p><span class="text-[11px] font-bold text-boton-acento">{{ $i->estado }}</span></div><p class="mt-1 text-xs text-parrafo">{{ $i->descripcion }}</p></article>@empty<p class="text-sm text-apoyo">Sin incidentes registrados.</p>@endforelse</div></section>
            </div>
        @endif
    @endif
</div>

