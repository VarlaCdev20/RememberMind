@php($petersenEnfermeria = $perfilInstrumental['petersen_institucional'] ?? null)
<nav class="rm-expert-reading-nav" aria-label="Consulta del residente asignado"><a href="#expert-nursing-today" class="rm-btn rm-btn-secondary"><i class="ph ph-sun" aria-hidden="true"></i>Resultado</a><a href="#expert-nursing-records" class="rm-btn rm-btn-secondary"><i class="ph ph-notepad" aria-hidden="true"></i>Registros</a><a href="#expert-nursing-care" class="rm-btn rm-btn-secondary"><i class="ph ph-heart" aria-hidden="true"></i>Cuidados</a></nav>
@if($petersenEnfermeria)
    @include('apoyodecision::livewire.medico.resultados-experto.enfermeria-perfil-petersen', ['petersen' => $petersenEnfermeria])
@endif
<div class="rm-expert-nursing-grid">
    @unless($petersenEnfermeria)
    <section class="rm-card rm-orion-panel rm-expert-nursing-result" id="expert-nursing-today" aria-labelledby="expert-nursing-today-title">
        <div class="rm-orion-section-heading">
            <p class="rm-expert-eyebrow">Resultado de la evaluación clínica</p>
            @if($consultaEnfermeria)<span class="rm-orion-state">{{ $consultaEnfermeria['estado_principal'] }}</span>@endif
        </div>
        <h2 id="expert-nursing-today-title">{{ $consultaEnfermeria['titulo'] ?? 'Sin evaluación experta disponible' }}</h2>
        <p class="rm-expert-prose">{{ $consultaEnfermeria['resumen'] ?? 'No hay un resultado experto verificable para esta consulta. Puede volver al expediente autorizado o reintentar la lectura.' }}</p>
        <p class="rm-orion-note">Aquí solo aparece una conclusión cognitiva cuando existe una evaluación clínica conservada y publicable. Los controles de seguimiento se muestran por separado; ninguno genera indicaciones de cuidado por sí solo.</p>
        @if($consultaEnfermeria)
            <div class="rm-orion-toolbar">
                <button type="button" class="rm-btn rm-btn-primary" wire:click="abrirSignificado" wire:loading.attr="disabled" wire:target="abrirSignificado">¿Qué significa este resultado?</button>
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirConsulta('registro')" wire:loading.attr="disabled" wire:target="abrirConsulta">Consultar fuentes y fundamento</button>
            </div>
        @endif
    </section>
    @endunless
    <section class="rm-card rm-orion-panel rm-expert-nursing-observations" aria-labelledby="expert-nursing-observations-title">
        <div class="rm-orion-section-heading"><h2 id="expert-nursing-observations-title" class="rm-expert-section-title">Hallazgos del seguimiento cognitivo</h2><i class="ph ph-clipboard-text" aria-hidden="true"></i></div>
        @if($errorObservacional)
            <p role="alert">No fue posible verificar los controles cognitivos. Reintente la consulta; esto no significa que no existan registros.</p>
            <button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="reintentar">Reintentar</button>
        @elseif($observacionesEnfermeria)
            <p class="rm-expert-prose">{{ $observacionesEnfermeria['alcance'] }} Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($observacionesEnfermeria['fecha']) }}.</p>
            @if($observacionesEnfermeria['items'] && $hallazgosObservacionales['registros']->total() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 mt-3">
                    @foreach($observacionesEnfermeria['items'] as $item)
                        <div class="rm-expert-operational-record"><strong>{{ $item['nombre'] }}</strong><p>{{ $item['estado'] }}</p>@if($item['hallazgo'])<p>{{ $item['hallazgo'] }}</p>@endif</div>
                    @endforeach
                </div>
            @else
                <p class="rm-orion-note mt-3">{{ $observacionesEnfermeria['titulo'] }}</p>
            @endif
            <p class="rm-orion-note mt-3">{{ $observacionesEnfermeria['limite'] }}</p>
        @else
            <p>No hay un análisis observacional verificable en esta consulta.</p>
        @endif
    </section>
    <section class="rm-card rm-orion-panel" id="expert-nursing-records" aria-labelledby="expert-nursing-records-title"><div class="rm-orion-section-heading"><h2 id="expert-nursing-records-title" class="rm-expert-section-title">Últimos registros cognitivos</h2><i class="ph ph-notepad" aria-hidden="true"></i></div>
        @forelse($datos['registros_cognitivos'] ?? [] as $registro)
            <article class="rm-expert-operational-record"><p><strong>{{ $registro['fecha'] }}</strong></p><p>{{ $registro['observacion'] ?: 'Sin observación textual registrada.' }}</p><small>Autor: {{ $registro['autor'] }}</small></article>
        @empty
            <p class="rm-orion-note">No hay controles recientes disponibles en este resumen. Las fuentes históricas se consultan por separado.</p>
        @endforelse
        @if($consultaEnfermeria)<button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="abrirConsulta('registro')">Ver registros y procedencia</button>@endif
    </section>
    <section class="rm-card rm-orion-panel" id="expert-nursing-care" aria-labelledby="expert-nursing-care-title"><div class="rm-orion-section-heading"><h2 id="expert-nursing-care-title" class="rm-expert-section-title">Continuidad de cuidados</h2><i class="ph ph-hand-heart" aria-hidden="true"></i></div>
        @if(! $puedeVerCuidados)<p>Su acceso a esta consulta no incluye el detalle de los planes de cuidado.</p>
        @else
            @forelse($cuidadosPendientesHoy->take(4) as $cuidado)<article class="rm-expert-operational-record"><strong>{{ $cuidado['intervencion']->nombre }}</strong><p>{{ $cuidado['hora'] }} · {{ $cuidado['estado'] }}</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="verCuidado(@js($cuidado['clave']))">Consultar cuidado</button></article>
            @empty<p class="rm-orion-note">No hay cuidados pendientes para hoy en la consulta autorizada. Esto no acredita ausencia de necesidades de cuidado.</p>@endforelse
        @endif
        <div class="rm-orion-toolbar">
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente]) }}">Abrir ficha del residente</a>
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.agenda') }}">Ir a mi agenda de turno</a>
        </div>
    </section>
</div>
