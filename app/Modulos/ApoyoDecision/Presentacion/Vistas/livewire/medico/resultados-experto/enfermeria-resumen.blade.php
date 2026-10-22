<nav class="rm-expert-reading-nav" aria-label="Consulta del residente asignado"><a href="#expert-nursing-today" class="rm-btn rm-btn-secondary"><i class="ph ph-sun" aria-hidden="true"></i>Resumen</a><a href="#expert-nursing-records" class="rm-btn rm-btn-secondary"><i class="ph ph-notepad" aria-hidden="true"></i>Registros</a><a href="#expert-nursing-care" class="rm-btn rm-btn-secondary"><i class="ph ph-heart" aria-hidden="true"></i>Cuidados</a></nav>
<div class="rm-expert-nursing-grid">
    <section class="rm-card rm-orion-panel" id="expert-nursing-today" aria-labelledby="expert-nursing-today-title"><p class="rm-expert-eyebrow">Información disponible</p><h2 id="expert-nursing-today-title" class="rm-expert-section-title">{{ $consultaEnfermeria['titulo'] ?? 'Sin evaluación experta disponible' }}</h2><p class="rm-expert-prose">{{ $consultaEnfermeria['resumen'] ?? 'No hay un resultado experto verificable para esta consulta. Puede volver al expediente autorizado o reintentar la lectura.' }}</p><p class="rm-orion-note">Este resumen consulta información guardada. No describe por sí mismo el estado general de hoy ni genera indicaciones de cuidado.</p>@if($consultaEnfermeria)<button type="button" class="rm-btn rm-btn-secondary mt-3" wire:click="abrirConsulta('registro')">Consultar fuentes y fundamento</button>@endif</section>
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
        <a class="rm-btn rm-btn-secondary mt-3" href="{{ route('admin.enfermeria.pacientes', ['residente' => $codResidente]) }}">Abrir ficha del residente</a>
    </section>
</div>
