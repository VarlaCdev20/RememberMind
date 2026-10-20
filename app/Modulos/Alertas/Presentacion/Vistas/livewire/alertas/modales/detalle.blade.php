@if($modalDetalle && $detalle)
<x-ui.drawer-livewire wire:model="modalDetalle" :title="str_replace('_', ' ', $detalle->tipo)" :subtitle="$detalle->residente?->nombre_completo" badge="Alerta registrada · consulta y trayectoria" icon="ph-bell-ringing" size="xl" closeMethod="solicitarCerrarModales" :dismissOnBackdrop="false">
    <div class="space-y-5">
        <div class="flex flex-wrap gap-2"><span class="rm-badge">{{ $detalle->getKey() }}</span><span class="rm-badge rm-alert-severity {{ in_array($detalle->prioridad,['CRITICO','CRITICA']) ? 'is-critical' : (in_array($detalle->prioridad,['ALTO','ALTA']) ? 'is-high' : '') }}">Prioridad {{ $detalle->prioridad }}</span><span class="rm-badge">{{ str_replace('_',' ',$detalle->estado) }}</span></div>
        <p>{{ \App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($detalle->descripcion) }}</p>
        @if(!in_array($detalle->estado, [...\App\Models\Alerta::ESTADOS_ACTIVOS, 'ATENDIDA', ...\App\Models\Alerta::ESTADOS_TERMINALES], true))
            <x-ui.callout variant="info" title="Registro anterior conservado">Esta alerta mantiene el estado {{ $detalle->estado }}. Puedes revisar sus datos y eventos; ese estado no se reclasifica ni habilita actuaciones del ciclo actual automáticamente.</x-ui.callout>
        @endif
        <dl class="rm-alert-case__facts">
            <div><dt>Registrada</dt><dd>{{ $detalle->fecha_hora?->format('d/m/Y H:i') }}</dd></div>
            <div><dt>Origen</dt><dd>{{ $detalle->modulo ?: 'Sin origen registrado' }}</dd></div>
            <div><dt>Responsable</dt><dd>{{ $detalle->responsable ? trim($detalle->responsable->nombres.' '.$detalle->responsable->apellido_paterno) : 'Sin responsable asignado' }}</dd></div>
            <div><dt>Ubicación actual</dt><dd>{{ $detalle->residente?->cama?->habitacion?->codigo ?: 'Habitación no registrada' }} · {{ $detalle->residente?->cama?->codigo ?: 'Cama no registrada' }}</dd></div>
        </dl>
        @if($signoOrigen)
            <section><h3>Registro que originó la alerta</h3><p>{{ $signoOrigen->fecha_hora?->format('d/m/Y H:i') }} · {{ $signoOrigen->cod_signo }}</p>
                <dl class="rm-alert-case__facts">@foreach(['presion_sistolica'=>['Presión sistólica','mmHg'],'presion_diastolica'=>['Presión diastólica','mmHg'],'frecuencia_cardiaca'=>['Frecuencia cardíaca','lpm'],'frecuencia_respiratoria'=>['Frecuencia respiratoria','rpm'],'temperatura'=>['Temperatura','°C'],'saturacion_oxigeno'=>['Saturación de oxígeno','%']] as $campo=>[$label,$unidad])@if($signoOrigen->$campo !== null)<div><dt>{{ $label }}</dt><dd>{{ $signoOrigen->$campo }} {{ $unidad }}</dd></div>@endif @endforeach</dl>
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
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.expediente.index', ['residente'=>$detalle->cod_residente]) }}">Consultar expediente y procesos autorizados</a>
        @endif
        @if($puedeReconocer && $detalle->estado === 'ABIERTA')
            <button type="button" class="rm-btn rm-btn-secondary" wire:click="reconocer('{{ $detalle->getKey() }}')" wire:loading.attr="disabled">Reconocer aviso · continúa pendiente</button>
        @endif
        @if($puedeAsignar && in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true))
            @if($usuarios->isEmpty())<p class="rm-help">No hay profesionales activos con acceso a este residente para asignar. Revisa las asignaciones y los permisos vigentes con coordinación.</p>@else
            <form wire:submit="asignarResponsable" class="space-y-2">
                <x-ui.field label="Responsable profesional" for="alerta-asignar" error="responsableId" help="Selecciona al profesional autorizado que dará seguimiento a este caso."><x-ui.selector id="alerta-asignar" label="Responsable profesional" wire:model="responsableId" teleport="#drawer-modaldetalle-shell" :aria-invalid="$errors->has('responsableId') ? 'true' : 'false'" aria-describedby="alerta-asignar-error"><option value="">Seleccionar personal autorizado</option>@foreach($usuarios as $usuario)<option value="{{ $usuario->getKey() }}">{{ $usuario->name }}</option>@endforeach</x-ui.selector></x-ui.field>
                <button type="submit" class="rm-btn rm-btn-secondary" wire:loading.attr="disabled">Asignar responsable</button>
            </form>
            @endif
        @endif
        <section aria-label="Historial de eventos"><h3>Historial de eventos · {{ $detalle->eventos->count() }}</h3>
            @php($eventos = $detalle->eventos->sortBy('fecha_hora')->map(fn($e)=>['id'=>$e->getKey(),'actor'=>$e->usuario?->name ?: 'Autor no disponible','tipo'=>$e->tipo_evento,'estado'=>$e->estado_anterior && $e->estado_anterior !== $e->estado_nuevo ? $e->estado_anterior.' → '.$e->estado_nuevo : $e->estado_nuevo,'fecha'=>$e->fecha_hora?->format('d/m/Y H:i'),'descripcion'=>\App\Compartido\Aplicacion\Formato\TextoVisible::sinCodigosInternos($e->descripcion),'variant'=>$e->tipo_evento === 'CIERRE' ? 'success' : 'info'])->values()->all())
            <x-patterns.timeline :items="$eventos" emptyMessage="Este registro aún no tiene eventos de seguimiento." />
        </section>
        @if(in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true) && ($puedeCoordinar || ($puedeIntervenir && $detalle->estado === 'EN_ATENCION')))
            <form wire:submit="guardarAccion" class="space-y-2">
                <x-ui.field :label="$puedeIntervenir ? 'Seguimiento de la atención en curso' : 'Seguimiento de coordinación'" for="alerta-seguimiento" error="accion" help="Describe hechos y acciones realmente realizados. El seguimiento de coordinación no documenta atención clínica."><textarea id="alerta-seguimiento" class="rm-input" rows="4" minlength="5" maxlength="10000" wire:model="accion" aria-describedby="alerta-seguimiento-help {{ $errors->has('accion') ? 'alerta-seguimiento-error' : '' }}" aria-invalid="{{ $errors->has('accion') ? 'true' : 'false' }}"></textarea></x-ui.field>
                <button type="submit" class="rm-btn rm-btn-primary" wire:loading.attr="disabled">{{ $puedeIntervenir ? 'Guardar seguimiento' : 'Registrar coordinación' }}</button>
            </form>
        @endif
        @if(!$puedeIntervenir && !$puedeCoordinar)<p class="rm-help">{{ $perfil['foco'] === 'turno' ? 'Consulta disponible. Para registrar acciones necesitas un turno vigente y asignación al residente.' : 'Consulta autorizada. Las decisiones profesionales se documentan en los procesos de tu competencia.' }}</p>@endif
    </div>
    <x-slot:footer><div class="space-y-3">
        @include('livewire.alertas.modales.confirmar-descarte')
        <div class="flex flex-wrap gap-2">
        @if($puedeIntervenir && in_array($detalle->estado, \App\Models\Alerta::ESTADOS_ACTIVOS, true))<button type="button" class="rm-btn rm-btn-primary" wire:click="atenderAlerta('{{ $detalle->getKey() }}')">Registrar intervención</button>@endif
        @if($puedeCerrar && $detalle->puedeCerrarse())<button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarAlerta('{{ $detalle->getKey() }}')">{{ $puedeIntervenir ? 'Preparar cierre justificado' : 'Preparar cierre de coordinación' }}</button>@endif
        <button type="button" class="rm-btn rm-btn-secondary" wire:click="solicitarCerrarModales">Volver a la bandeja</button>
    </div></div></x-slot:footer>
</x-ui.drawer-livewire>
@endif
