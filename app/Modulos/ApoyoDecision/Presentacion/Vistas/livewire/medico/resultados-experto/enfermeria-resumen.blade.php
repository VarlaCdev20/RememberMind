@include('apoyodecision::livewire.medico.resultados-experto.enfermeria-informe')
<details class="rm-card rm-expert-report__foundation"><summary><i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i><span>Evaluaciones guardadas<small>Historial de resultados conservados</small></span><i class="ph ph-caret-down" aria-hidden="true"></i></summary>
    @include('apoyodecision::livewire.medico.resultados-experto.historia-institucional')
</details>
<details class="rm-card rm-expert-report__foundation" id="nursing-followup">
    <summary><i class="ph ph-hand-heart" aria-hidden="true"></i><span>Seguimiento y cuidados<small>Controles originales y continuidad del turno</small></span><i class="ph ph-caret-down" aria-hidden="true"></i></summary>
<div class="rm-expert-nursing-grid">
    <section class="rm-card rm-decision-panel rm-expert-nursing-observations" aria-labelledby="expert-nursing-observations-title">
        <div class="rm-decision-section-heading"><h2 id="expert-nursing-observations-title" class="rm-expert-section-title">Hallazgos del seguimiento cognitivo</h2><i class="ph ph-clipboard-text" aria-hidden="true"></i></div>
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
                <p class="rm-decision-note mt-3">{{ $observacionesEnfermeria['titulo'] }}</p>
            @endif
            <p class="rm-decision-note mt-3">{{ $observacionesEnfermeria['limite'] }}</p>
        @else
            <p>No hay un análisis observacional verificable en esta consulta.</p>
        @endif
    </section>
    <section class="rm-card rm-decision-panel" id="expert-nursing-records" aria-labelledby="expert-nursing-records-title"><div class="rm-decision-section-heading"><h2 id="expert-nursing-records-title" class="rm-expert-section-title">Últimos registros cognitivos</h2><i class="ph ph-notepad" aria-hidden="true"></i></div>
        @forelse($datos['registros_cognitivos'] ?? [] as $registro)
            <article class="rm-expert-operational-record"><p><strong>{{ $registro['fecha'] }}</strong></p><p>{{ $registro['observacion'] ?: 'Sin observación textual registrada.' }}</p><small>Autor: {{ $registro['autor'] }}</small></article>
        @empty
            <p class="rm-decision-note">No hay controles recientes disponibles en este resumen. Las fuentes históricas se consultan por separado.</p>
        @endforelse
        @if($consultaEnfermeria)<button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="abrirConsulta('registro')">Ver registros y procedencia</button>@endif
    </section>
    <section class="rm-card rm-decision-panel" id="expert-nursing-care" aria-labelledby="expert-nursing-care-title"><div class="rm-decision-section-heading"><h2 id="expert-nursing-care-title" class="rm-expert-section-title">Continuidad de cuidados</h2><i class="ph ph-hand-heart" aria-hidden="true"></i></div>
        @if(! $puedeVerCuidados)<p>Su acceso a esta consulta no incluye el detalle de los planes de cuidado.</p>
        @else
            @forelse($cuidadosPendientesHoy->take(4) as $cuidado)<article class="rm-expert-operational-record"><strong>{{ $cuidado['intervencion']->nombre }}</strong><p>{{ $cuidado['hora'] }} · {{ $cuidado['estado'] }}</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="verCuidado(@js($cuidado['clave']))">Consultar cuidado</button></article>
            @empty<p class="rm-decision-note">No hay cuidados pendientes para hoy en la consulta autorizada. Esto no acredita ausencia de necesidades de cuidado.</p>@endforelse
        @endif
        <div class="rm-decision-toolbar">
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente]) }}">Abrir ficha del residente</a>
            <a class="rm-btn rm-btn-secondary" href="{{ route('admin.enfermeria.agenda') }}">Ir a mi agenda de turno</a>
        </div>
    </section>
</div>

</details>
