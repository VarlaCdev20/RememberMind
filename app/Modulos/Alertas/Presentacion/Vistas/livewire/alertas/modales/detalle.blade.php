@if($modalDetalle && $detalle)
<x-ui.drawer-livewire class="rm-alert-dialog" wire:model="modalDetalle" :title="\App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::tipo($detalle->tipo)" :subtitle="$detalle->residente?->nombre_completo" badge="Alerta registrada · consulta y trayectoria" icon="ph-bell-ringing" size="xl" closeMethod="solicitarCerrarModales" :dismissOnBackdrop="false">
    <div class="rm-alert-detail space-y-5">
        <section class="rm-alert-detail__signal" data-priority="{{ $detalle->prioridad }}" aria-label="Identificación de la alerta"><span class="rm-alert-detail__icon"><i class="ph-bold ph-bell-ringing" aria-hidden="true"></i></span><div><p class="rm-alert-detail__label">Alerta registrada</p><div class="flex flex-wrap gap-2"><span class="rm-badge rm-alert-severity {{ in_array($detalle->prioridad,['CRITICO','CRITICA']) ? 'is-critical' : (in_array($detalle->prioridad,['ALTO','ALTA']) ? 'is-high' : '') }}">{{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::prioridad($detalle->prioridad) }}</span> <span class="rm-badge">{{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::estado($detalle->estado) }}</span></div>
        <p class="rm-alert-case__resident-label">Nombre del residente</p><h3 class="rm-alert-detail__resident">{{ $detalle->residente?->nombre_completo }}</h3></div></section>
        <section class="rm-alert-detail__description"><h3>Situación registrada</h3><p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($detalle->descripcion) }}</p></section>
        @if(!in_array($detalle->estado, [...\App\Models\Alerta::ESTADOS_ACTIVOS, 'ATENDIDA', ...\App\Models\Alerta::ESTADOS_TERMINALES], true))
            <x-ui.callout variant="info" title="Registro anterior conservado">Esta alerta mantiene el estado {{ $detalle->estado }}. Puedes revisar sus datos y eventos; ese estado no se reclasifica ni habilita actuaciones del ciclo actual automáticamente.</x-ui.callout>
        @endif
        <dl class="rm-alert-case__facts">
            <div><dt>Registrada</dt><dd>{{ $detalle->fecha_hora?->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Origen</dt><dd>{{ $detalle->modulo ?: 'Sin origen registrado' }}</dd></div>
            <div><dt>Responsable del seguimiento</dt><dd>{{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::responsable($detalle->responsable) }}</dd></div>
            <div><dt>Ubicación actual</dt><dd>{{ $detalle->residente?->cama?->habitacion?->codigo ?: 'Habitación no registrada' }} · {{ $detalle->residente?->cama?->codigo ?: 'Cama no registrada' }}</dd></div>
        </dl>
        @if($detalle->modulo && !$signoOrigen && !$origenDetalle['disponible'])<p class="rm-help">Registro de origen no disponible en esta consulta. Se conserva el origen declarado; no se reconstruyen sus datos.</p>@endif
        @if($origenDetalle['disponible'])<section class="space-y-4" aria-label="Hecho y cuidado que referencia la alerta"><h3>Registro de origen y cuidado relacionado</h3>@foreach($origenDetalle['nodos'] as $nodoOrigen)<article class="rm-alert-desk__group"><h4>{{ $nodoOrigen['label'] }}</h4><p>{{ $nodoOrigen['texto'] }}</p><dl class="rm-alert-desk__fields">@foreach($nodoOrigen['detalles'] as $campoOrigen)<div><dt>{{ $campoOrigen['etiqueta'] }}</dt><dd>{{ $campoOrigen['valor'] }}</dd></div>@endforeach</dl></article>@endforeach</section>@endif
        <p class="rm-help">El responsable da continuidad a la alerta. El historial identifica quién realizó cada actuación. La asignación no concede competencias ni permisos adicionales.</p>
        <p class="rm-help">Turno de origen: no consta un vínculo de turno en este registro.</p>
        @if($signoOrigen)
            <section><h3>Registro que originó la alerta</h3><p>{{ $signoOrigen->fecha_hora?->format('d/m/Y H:i') }}</p>
                <dl class="rm-alert-case__facts">@foreach(['presion_sistolica'=>['Presión sistólica','mmHg'],'presion_diastolica'=>['Presión diastólica','mmHg'],'frecuencia_cardiaca'=>['Frecuencia cardíaca','lpm'],'frecuencia_respiratoria'=>['Frecuencia respiratoria','rpm'],'temperatura'=>['Temperatura','°C'],'saturacion_oxigeno'=>['Saturación de oxígeno','%'],'glucemia'=>['Glucemia','mg/dL']] as $campo=>[$label,$unidad])@if($signoOrigen->$campo !== null)<div><dt>{{ $label }}</dt><dd>{{ $signoOrigen->$campo }} {{ $unidad }}</dd></div>@endif @endforeach</dl>
                @if($puedeRegistrarNuevaMedicion)<a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes', ['residente'=>$detalle->cod_residente, 'registrar'=>'signos']) }}">Registrar nueva medición</a>@endif
            </section>
        @endif
        @if($signoOrigen && $evolucionSignos->isNotEmpty())
            <section aria-label="Mediciones posteriores al origen" class="space-y-3">
                <h3>Evolución desde la alerta</h3>
                <p class="rm-help">Mediciones registradas desde la apertura, con fecha y unidad de cada dato.</p>
                @foreach($evolucionSignos as $medicion)
                    <article class="space-y-2 rounded-xl border border-[var(--rm-border-soft)] p-3">
                        <h4 class="font-semibold">{{ $medicion->fecha_hora?->format('d/m/Y H:i') }}</h4>
                        <dl class="rm-alert-case__facts">
                            @foreach(['presion_sistolica'=>['Presión sistólica','mmHg'],'presion_diastolica'=>['Presión diastólica','mmHg'],'frecuencia_cardiaca'=>['Frecuencia cardíaca','lpm'],'frecuencia_respiratoria'=>['Frecuencia respiratoria','rpm'],'temperatura'=>['Temperatura','°C'],'saturacion_oxigeno'=>['Saturación de oxígeno','%'],'glucemia'=>['Glucemia','mg/dL']] as $campo=>[$label,$unidad])
                                @if($medicion->$campo !== null)<div><dt>{{ $label }}</dt><dd>{{ $medicion->$campo }} {{ $unidad }}</dd></div>@endif
                            @endforeach
                        </dl>
                    </article>
                @endforeach
            </section>
        @endif
        @if($perfil['foco'] === 'turno' && auth()->user()->can('enfermeria.ver_ficha_paciente'))<a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto'=>$detalle->cod_residente]) }}">Continuar en el expediente del residente</a>@endif
        @if(in_array($perfil['foco'], ['clinico','psicologia']) && auth()->user()->can('atenciones.ver') && \Illuminate\Support\Facades\Gate::allows('view', $detalle->residente))
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.adultos-mayores.show', ['adulto_mayor'=>$detalle->cod_residente, 'tab'=>'salud']) }}">Consultar expediente y procesos autorizados</a>
        @endif
        @if($puedeReconocer && $detalle->estado === 'ABIERTA')
            <button type="button" class="rm-btn rm-btn-secondary" wire:click="reconocer('{{ $detalle->getKey() }}')" wire:loading.attr="disabled">Reconocer aviso · continúa pendiente</button>
        @endif
        @if($puedeAsignar && in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true))
            <x-ui.field label="Buscar responsable del seguimiento" for="alerta-responsable-busqueda"><input type="search" class="rm-input" id="alerta-responsable-busqueda" wire:model.live.debounce.300ms="buscarResponsable" placeholder="Nombre o apellido del profesional"></x-ui.field>
            @if($usuarios->isEmpty())<p class="rm-help">No hay profesionales activos con acceso a este residente para asignar. Revisa las asignaciones y los permisos vigentes con coordinación.</p>@else
            <form wire:submit="asignarResponsable" class="space-y-2">
                <fieldset class="rm-field"><legend class="rm-label">Responsable del seguimiento</legend><div class="rm-alert-choice-grid">
                    @foreach($usuarios->take(12) as $usuario)<x-ui.choice-card name="responsableId" :value="$usuario->getKey()" :label="$usuario->name" :description="$usuario->personal?->profesion ?: 'Profesión no registrada'" icon="ph-user-check" wire:model.live="responsableId" />@endforeach
                </div><p class="rm-help">Hasta 12 resultados autorizados. Refina la búsqueda por nombre para localizar al profesional.</p>@error('responsableId')<p class="rm-error" role="alert">{{ $message }}</p>@enderror</fieldset>
                <button type="submit" class="rm-btn rm-btn-secondary" wire:loading.attr="disabled">Asignar responsable</button>
            </form>
            @endif
        @endif
        <section class="rm-alert-detail__history" aria-label="Historial de eventos"><h3>Historial de eventos · {{ $detalle->eventos->count() }}</h3>
            @php($eventos = $detalle->eventos->sortBy('fecha_hora')->map(fn($e)=>['id'=>$e->getKey(),'actor'=>$e->usuario?->name ?: 'Autor no disponible','tipo'=>$e->tipo_evento,'estado'=>$e->estado_anterior && $e->estado_anterior !== $e->estado_nuevo ? $e->estado_anterior.' → '.$e->estado_nuevo : $e->estado_nuevo,'fecha'=>$e->fecha_hora?->format('d/m/Y H:i'),'descripcion'=>\App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($e->descripcion),'variant'=>$e->tipo_evento === 'CIERRE' ? 'success' : 'info'])->values()->all())
            <x-patterns.timeline :items="$eventos" emptyMessage="Este registro aún no tiene eventos de seguimiento." />
        </section>
        @if(in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true) && ($puedeCoordinar || ($puedeIntervenir && $detalle->estado === 'EN_ATENCION')))
            <form wire:submit="guardarAccion" class="space-y-2">
                <x-ui.field :label="$puedeIntervenir ? 'Seguimiento de la atención en curso' : 'Seguimiento de coordinación'" for="alerta-seguimiento" error="accion" help="Describe hechos y acciones realmente realizados. El seguimiento de coordinación no documenta atención clínica."><textarea id="alerta-seguimiento" class="rm-input" rows="4" minlength="5" maxlength="10000" wire:model="accion" aria-describedby="alerta-seguimiento-help {{ $errors->has('accion') ? 'alerta-seguimiento-error' : '' }}" aria-invalid="{{ $errors->has('accion') ? 'true' : 'false' }}"></textarea></x-ui.field>
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">{{ $puedeIntervenir ? 'Guardar seguimiento' : 'Registrar coordinación' }}</button>
            </form>
        @endif
        @if(in_array($perfil['foco'], ['turno','coordinacion','supervision','auditoria'], true) && ($preview || !in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true) || ($perfil['foco'] === 'turno' && !$puedeIntervenir) || ($perfil['foco'] === 'supervision' && !$puedeAsignar) || (in_array($perfil['foco'], ['coordinacion','auditoria'],true) && !$puedeCoordinar)))
            <section class="rm-alert-desk__group" aria-label="Campos del rol en solo lectura"><h3><i class="ph-bold ph-lock-simple" aria-hidden="true"></i> Campos disponibles para consulta</h3><p class="rm-help">Los controles de edición están bloqueados por la previsualización, el estado de la alerta o los permisos y contexto de tu cuenta. Puedes consultar las etiquetas y los datos guardados del historial.</p>
                <fieldset disabled class="space-y-4" data-readonly-role-fields="{{ $perfil['foco'] }}"><legend class="rm-label">Formulario del rol · solo lectura</legend>
                    <x-ui.field label="Responsable del seguimiento" for="responsable-alerta-consulta"><input id="responsable-alerta-consulta" class="rm-input" type="text" value="{{ \App\Modulos\Alertas\Aplicacion\Formato\LenguajeAlerta::responsable($detalle->responsable) }}"><button type="button" class="rm-btn rm-btn-secondary" disabled>Asignar responsable</button></x-ui.field>
                    @if($perfil['foco'] === 'turno')<x-ui.field label="Nota de intervención" for="intervencion-alerta-consulta" help="Observación y momento del hecho, actuación, respuesta, comunicación y pendientes. Los registros guardados se consultan en el historial."><textarea id="intervencion-alerta-consulta" class="rm-textarea" rows="4" disabled></textarea><button type="button" class="rm-btn rm-btn-primary" disabled>Registrar intervención</button></x-ui.field>@endif
                    @if($perfil['foco'] !== 'supervision')<x-ui.field :label="$perfil['foco'] === 'turno' ? 'Seguimiento de la atención en curso' : 'Seguimiento de coordinación'" for="seguimiento-alerta-consulta"><textarea id="seguimiento-alerta-consulta" class="rm-textarea" rows="3" disabled></textarea><button type="button" class="rm-btn rm-btn-secondary" disabled>Guardar seguimiento</button></x-ui.field><x-ui.field label="Justificación del cierre" for="cierre-alerta-consulta"><textarea id="cierre-alerta-consulta" class="rm-textarea" rows="3" disabled></textarea><button type="button" class="rm-btn rm-btn-secondary" disabled>Preparar cierre justificado</button></x-ui.field>@endif
                </fieldset>
            </section>
        @endif
        @if(!$puedeIntervenir && !$puedeCoordinar)<p class="rm-help">{{ $perfil['foco'] === 'turno' ? 'Consulta disponible. Para registrar acciones necesitas un turno vigente y asignación al residente.' : 'Consulta autorizada. Las decisiones profesionales se documentan en los procesos de tu competencia.' }}</p>@endif
    </div>
    <x-slot:footer><div class="space-y-3">
        @include('livewire.alertas.modales.confirmar-descarte')
        <div class="flex flex-wrap gap-2">
        @if($definicionEspecialidad)<button type="button" class="rm-btn rm-btn-primary" wire:click="abrirRegistroEspecialidad('{{ $detalle->getKey() }}')">{{ $preview ? 'Ver campos: ' : 'Registrar: ' }}{{ mb_strtolower($definicionEspecialidad['titulo']) }}</button>@endif
        @if($puedeIntervenir && in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true))<button type="button" class="rm-btn rm-btn-primary" wire:click="atenderAlerta('{{ $detalle->getKey() }}')">Registrar intervención</button>@endif
        @if($puedeCerrar && $detalle->puedeCerrarse())<button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarAlerta('{{ $detalle->getKey() }}')">{{ $puedeIntervenir ? 'Preparar cierre justificado' : 'Preparar cierre de coordinación' }}</button>@endif
        @can('signos_vitales.ver')<button type="button" class="rm-btn rm-btn-secondary" wire:click="verGraficos('{{ $detalle->cod_residente }}')">Ver evolución</button>@endcan
        <button type="button" class="rm-btn rm-btn-secondary" wire:click="verUbicacion('{{ $detalle->cod_residente }}')">Ver ubicación</button>
        <button type="button" class="rm-btn rm-btn-secondary" wire:click="solicitarCerrarModales">Volver a la bandeja</button>
    </div></div></x-slot:footer>
</x-ui.drawer-livewire>
@endif
