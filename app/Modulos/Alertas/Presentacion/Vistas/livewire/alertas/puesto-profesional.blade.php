<section class="rm-alert-desk" data-desk="{{ $perfil['foco'] }}" aria-label="{{ $perfil['seleccionar'] }}">
    <header class="rm-alert-desk__heading"><span class="rm-alert-desk__icon"><i class="ph-bold {{ $perfil['icono'] }}" aria-hidden="true"></i></span><div><p class="rm-help">{{ $perfil['seleccionar'] }}</p><h2>{{ $traza['residente'] ?? 'Selecciona un caso de la bandeja' }}</h2></div></header>
    @if($definicionEspecialidad)
        @if($contextoEspecialidad && $contextoEspecialidad['hayRegistro'])<p class="rm-alert-desk__provenance">Último registro vigente del residente · <time>{{ $contextoEspecialidad['fecha'] }}</time></p>@endif
        @if($contextoEspecialidad && !$contextoEspecialidad['autorizado'])
            <x-ui.callout variant="info" title="La fuente requiere permiso de consulta">Puedes revisar la alerta. El contenido de {{ mb_strtolower($contextoEspecialidad['titulo']) }} requiere el permiso de esa especialidad.</x-ui.callout>
        @else
            @php($contextoTrabajo = $contextoEspecialidad ?? ['campos' => collect($definicionEspecialidad['campos'])->mapWithKeys(fn($dato, $campo) => [$campo => ['etiqueta'=>$dato[0], 'valor'=>$traza ? 'Sin contexto vinculado a esta especialidad' : 'Sin caso seleccionado']])->all(), 'secciones'=>$definicionEspecialidad['secciones']])
            @if(!$contextoEspecialidad)
                <x-ui.callout variant="info" :title="$traza ? 'Este caso no tiene vínculo verificable con tu especialidad' : 'Elige un caso para comenzar'">Consulta las alertas vinculadas a tu especialidad para recorrer su contexto profesional. Una valoración reciente del residente no demuestra que haya originado esta alerta.</x-ui.callout>
            @elseif(!$contextoEspecialidad['hayRegistro'])
            <x-ui.empty-state :icono="$perfil['icono']" :titulo="'Sin '.mb_strtolower($contextoEspecialidad['titulo']).' vigente'" texto="No hay un registro guardado para mostrar como contexto. La alerta se conserva y puedes consultar su detalle." />
            @endif
            @switch($perfil['foco'])
                @case('clinico')
                    <article class="rm-alert-desk__note"><span class="rm-badge">{{ $contextoTrabajo['campos']['tipo_nota']['valor'] }}</span><h3>Contenido de la última nota clínica</h3><p>{{ $contextoTrabajo['campos']['contenido']['valor'] }}</p></article>
                    @break
                @case('psicologia')
                    <div class="rm-alert-desk__observations">
                        @foreach($contextoTrabajo['secciones'] as $tituloGrupo=>$camposGrupo)<section class="rm-alert-desk__group"><h3>{{ $tituloGrupo }}</h3>@include('livewire.alertas.contexto-campos', ['camposContexto'=>$camposGrupo])</section>@endforeach
                    </div>
                    @break
                @case('nutricion')
                    <section class="rm-alert-desk__hydration"><i class="ph-bold ph-drop" aria-hidden="true"></i><div><h3>Requerimiento hídrico registrado</h3><strong>{{ $contextoTrabajo['campos']['requerimiento_hidrico']['valor'] }}</strong><p>Valor de la valoración profesional; no es consumo medido ni una recomendación calculada.</p></div></section>
                    <section class="rm-alert-desk__group"><h3>Alimentación y apoyo</h3>@include('livewire.alertas.contexto-campos', ['camposContexto'=>['estado_nutricional','apetito','deglucion','riesgo_desnutricion','necesidad_asistencia','restricciones_alimentarias']])</section>
                    <section class="rm-alert-desk__group"><h3>Conclusión y continuidad nutricional</h3>@include('livewire.alertas.contexto-campos', ['camposContexto'=>['conclusion','recomendacion']])</section>
                    @break
                @case('funcional')
                    <section class="rm-alert-desk__group"><h3>Autonomía en el cuidado diario</h3><dl class="rm-alert-desk__autonomy">@foreach(['alimentacion_autonoma','bano_autonomo','vestido_autonomo','higiene_autonoma','continencia','movilidad_autonoma'] as $campo)<div><dt>{{ $contextoTrabajo['campos'][$campo]['etiqueta'] }}</dt><dd>{{ $contextoTrabajo['campos'][$campo]['valor'] }}</dd></div>@endforeach</dl></section>
                    <section class="rm-alert-desk__group"><h3>Capacidad motora y supervisión</h3>@include('livewire.alertas.contexto-campos', ['camposContexto'=>['marcha','equilibrio','traslado','fuerza_funcional','resistencia','necesita_supervision','nivel_dependencia']])</section>
                    <section class="rm-alert-desk__group"><h3>Continuidad funcional</h3>@include('livewire.alertas.contexto-campos', ['camposContexto'=>['conclusion','recomendacion']])</section>
                    @break
                @case('pedagogia')
                    <ol class="rm-alert-desk__activity">
                        @foreach(['Atención y comprensión'=>['atencion','comprension_instrucciones','reconocimiento','orientacion'],'Ejecución y participación'=>['ejecucion_tarea','participacion','interaccion'],'Cambio y continuidad'=>['cambio_desempeno','observacion']] as $tituloGrupo=>$camposGrupo)<li><span class="rm-alert-desk__step">{{ $loop->iteration }}</span><section><h3>{{ $tituloGrupo }}</h3>@include('livewire.alertas.contexto-campos', ['camposContexto'=>$camposGrupo])</section></li>@endforeach
                    </ol>
                    @break
            @endswitch
            @if($contextoEspecialidad && $contextoEspecialidad['hayRegistro'])<p class="rm-alert-desk__provenance"><i class="ph-bold ph-user-check" aria-hidden="true"></i>{{ $contextoEspecialidad['autor'] }}</p>@endif
        @endif
        <p class="rm-help">Contexto del residente. Este registro no confirma por sí solo el origen ni la resolución de la alerta.</p>
        @if($traza && $definicionEspecialidad)<x-ui.action-button :variant="$preview || !auth()->user()->can($definicionEspecialidad['permiso']) ? 'secondary' : 'primary'" wire:click="abrirRegistroEspecialidad('{{ $traza['codigo'] }}')" loading="abrirRegistroEspecialidad" :icono="$perfil['icono']">{{ $preview || !auth()->user()->can($definicionEspecialidad['permiso']) ? 'Consultar formulario' : 'Registrar '.mb_strtolower($definicionEspecialidad['titulo']) }}</x-ui.action-button>@endif
    @elseif($perfil['foco'] === 'turno')
        <h3>Situación y continuidad del cuidado</h3><p>Revisa el momento del hecho y la actuación anterior antes de documentar la intervención.</p>
        @if($traza)
            <ol class="rm-alert-desk__handover">@forelse(array_slice($traza['movimientos'],0,3) as $movimiento)<li><strong>{{ $movimiento['tipo'] }}</strong><p>{{ $movimiento['texto'] }}</p></li>@empty<li>No hay eventos guardados para este caso.</li>@endforelse</ol>
            @if($puedeIntervenir)<x-ui.action-button wire:click="verDetalle('{{ $traza['codigo'] }}')" loading="verDetalle" icono="ph-first-aid">Revisar intervención y seguimiento</x-ui.action-button>@else<p class="rm-help">{{ $preview ? 'La previsualización permite consultar. La intervención requiere una sesión profesional y una asignación vigente.' : 'El registro del cuidado requiere personal activo, permiso y asignación vigente.' }}</p>@endif
        @else<p class="rm-help">Selecciona una alerta para consultar la continuidad guardada.</p>@endif
    @elseif($perfil['foco'] === 'coordinacion')
        <h3>Resolver la coordinación del aviso</h3><ol class="rm-alert-desk__activity"><li><span class="rm-alert-desk__step">1</span><p>Reconocer el aviso y revisar quién está a cargo.</p></li><li><span class="rm-alert-desk__step">2</span><p>Asignar responsable cuando corresponda y documentar la coordinación.</p></li><li><span class="rm-alert-desk__step">3</span><p>Revisar el seguimiento y justificar el cierre operativo.</p></li></ol>
        @if($traza)<x-ui.action-button variant="secondary" wire:click="verDetalle('{{ $traza['codigo'] }}')" loading="verDetalle" icono="ph-clipboard-text">{{ $preview ? 'Consultar coordinación' : 'Abrir coordinación del aviso' }}</x-ui.action-button>@endif
    @elseif($perfil['foco'] === 'supervision')
        <h3>Supervisión del caso</h3><p>Consulta quién está a cargo y los eventos de gestión. La asignación conserva sus comprobaciones de permiso y ámbito.</p>@if($traza)<x-ui.action-button variant="secondary" wire:click="verDetalle('{{ $traza['codigo'] }}')" loading="verDetalle" icono="ph-users-three">Consultar responsabilidad e historial</x-ui.action-button>@endif
    @else
        <h3>Inspección del registro</h3><p>Selecciona una alerta para revisar sus relaciones verificables, autoría y eventos conservados.</p>@if($traza)<x-ui.action-button variant="secondary" wire:click="verDetalle('{{ $traza['codigo'] }}')" loading="verDetalle" icono="ph-magnifying-glass">Inspeccionar detalle</x-ui.action-button>@endif
    @endif
</section>
