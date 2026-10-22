@if($narrativa['experimental'] && !($simulacion ?? false))
    <aside class="rm-expert-test-note" role="note"><strong>{{ $narrativa['advertencia'] }}</strong></aside>
@endif
<section class="rm-card rm-expert-clinical-conclusion" aria-labelledby="resultado-principal-titulo" aria-live="polite">
    <p class="rm-expert-eyebrow">Resultado principal</p>
    <h2 id="resultado-principal-titulo" class="rm-expert-conclusion">{{ $narrativa['conclusion'] }}</h2>
    <p class="rm-expert-prose mt-3">{{ $narrativa['resumen'] }}</p>
</section>
@if($narrativa['experimental'])
<section class="rm-card rm-expert-clinical-block" aria-labelledby="requisitos-conservados-titulo">
    <h2 id="requisitos-conservados-titulo" class="rm-expert-section-title">Requisitos considerados en la evaluación</h2>
    @if(isset($narrativa['fecha_corte']))
        <p class="text-sm mt-2">Información considerada hasta el {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($narrativa['fecha_corte']) }}.</p>
    @endif
    <dl class="space-y-4 mt-4">
        @foreach($narrativa['requisitos'] as $requisito)
            <div><dt class="font-semibold">{{ $requisito['nombre'] }} · {{ $requisito['estado'] }}</dt>
                <dd class="rm-expert-prose mt-2">{{ $requisito['razon'] }}</dd>
                @foreach($requisito['fuentes'] as $fuenteRequisito)<dd class="text-sm text-rm-secondary mt-1">{{ $fuenteRequisito }}</dd>@endforeach
            </div>
        @endforeach
    </dl>
</section>
@endif
<section class="rm-card rm-expert-clinical-block" aria-labelledby="funciones-titulo">
    <h2 id="funciones-titulo" class="rm-expert-section-title">Valoración de las funciones cognitivas</h2>
    <div class="rm-expert-area-grid">
        @foreach($narrativa['funciones'] as $codigo => $funcion)
            @if(!$narrativa['experimental'])
            <button type="button" wire:key="funcion-{{ $codigo }}" wire:click="seleccionarCriterio(@js($codigo))" wire:loading.attr="disabled"
                aria-pressed="{{ $criterio === $codigo ? 'true' : 'false' }}" aria-controls="fuentes-valoracion"
                class="rm-expert-criterion text-left min-w-0 {{ $criterio === $codigo ? 'is-selected' : '' }}">
                @else<article class="rm-expert-criterion min-w-0">@endif
                <span class="rm-expert-criterion-heading"><i class="ph {{ $informe['areas'][$codigo]['icono'] }}" aria-hidden="true"></i><strong>{{ $funcion['nombre'] }}</strong></span>
                <span class="rm-expert-criterion-description">{{ $funcion['frase'] }}</span>
                @if(isset($funcion['fuentes_legibles']))
                    <p class="text-sm font-semibold mt-4">Información utilizada</p>
                    <ul class="space-y-3 mt-3 list-none">
                        @forelse($funcion['fuentes_legibles'] as $fuenteLegible)
                            <li class="rm-expert-source text-sm">
                                <h3 class="font-semibold">{{ $fuenteLegible['titulo'] }}</h3>
                                <p class="mt-2"><strong>Fecha:</strong> <time datetime="{{ $fuenteLegible['fecha_iso'] }}">{{ $fuenteLegible['fecha'] }}</time></p>
                                <ul class="list-disc pl-5 space-y-2 mt-3">
                                    @foreach($fuenteLegible['datos'] as $datoLegible)<li>{{ $datoLegible }}</li>@endforeach
                                </ul>
                                <p class="rm-expert-prose mt-3 text-rm-secondary">{{ $fuenteLegible['uso'] }}</p>
                            </li>
                        @empty
                            <li>No hay información suficiente para valorar esta función.</li>
                        @endforelse
                    </ul>
                @else
                    @foreach($funcion['evidencia'] as $evidenciaFuncion)<span class="rm-expert-criterion-source">{{ $evidenciaFuncion }}</span>@endforeach
                @endif
            @if(!$narrativa['experimental'])</button>@else</article>@endif
        @endforeach
    </div>
</section>
<section class="rm-card rm-expert-clinical-block" aria-labelledby="fundamentos-titulo">
    <h2 id="fundamentos-titulo" class="rm-expert-section-title">¿Por qué llegamos a este resultado?</h2>
    <ol class="rm-expert-reasons">
        @foreach($narrativa['fundamentos'] as $fundamento)<li><h3 class="font-semibold">{{ $fundamento['titulo'] }}</h3><p class="rm-expert-prose mt-2">{{ $fundamento['texto'] }}</p>
            @foreach($fundamento['fuentes'] ?? [] as $razonFuente)<p class="rm-expert-prose text-sm text-rm-secondary mt-2">{{ $razonFuente }}</p>@endforeach
        </li>@endforeach
    </ol>
    @if(!$narrativa['experimental'])
    <details id="fuentes-valoracion" class="rm-expert-supplement mt-4" wire:key="fuentes-{{ $criterio }}-{{ $datos['evaluacion']['id'] ?? 'vacia' }}">
        <summary>Ver en qué se basa · {{ $informe['areas'][$criterio]['nombre'] }}</summary>
        @include('livewire.medico.resultados-experto.evidencias')
        @if($datos['registros_cognitivos'])
            <h3 class="font-semibold mt-5">Observaciones del expediente</h3>
            <p class="text-sm mt-2">Estos controles describen observaciones; no sustituyen una evaluación objetiva de memoria.</p>
            @foreach($datos['registros_cognitivos'] as $registro)
                <details class="rm-expert-source mt-3"><summary>{{ $registro['fecha'] }} · {{ $registro['autor'] }}</summary>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">@foreach($registro['mediciones'] as $medicion)<div><dt class="text-sm text-rm-secondary">{{ $medicion['nombre'] }}</dt><dd>{{ $medicion['valor'] }}</dd></div>@endforeach</dl>
                    @if($registro['observacion'])<p class="rm-expert-prose mt-3"><strong>Observación registrada:</strong> {{ $registro['observacion'] }}</p>@endif
                </details>
            @endforeach
        @endif
        <p class="text-sm text-rm-secondary mt-4">{{ $informe['limite_fuente'] }}</p>
    </details>
    @endif
</section>
<section class="rm-card rm-expert-clinical-block" aria-labelledby="autonomia-titulo">
    <h2 id="autonomia-titulo" class="rm-expert-section-title">Autonomía en las actividades cotidianas</h2>
    <p class="rm-expert-prose">{{ $narrativa['autonomia']['resumen'] }}</p>
    @foreach($narrativa['autonomia']['actividades'] as $actividad)
        <details class="rm-expert-source mt-3"><summary>{{ $actividad['nombre'] }}</summary><p class="rm-expert-prose mt-2">{{ $actividad['texto'] }}</p>
            <p class="text-sm text-rm-secondary mt-2">{{ $actividad['fecha'] }} · {{ $actividad['autor'] }}</p>
            @if($actividad['observacion'])<p class="rm-expert-prose mt-2"><strong>Observación registrada:</strong> {{ $actividad['observacion'] }}</p>@endif
            @foreach($actividad['variantes'] ?? [] as $variante)
                <div class="rm-expert-source mt-3"><p class="font-semibold">{{ $variante['fecha'] }} · {{ $variante['autor'] }}</p><p class="rm-expert-prose mt-2">{{ $variante['texto'] }}</p>
                    @if($variante['observacion'])<p class="rm-expert-prose mt-2">{{ $variante['observacion'] }}</p>@endif
                </div>
            @endforeach
        </details>
    @endforeach
    <p class="text-sm text-rm-secondary mt-4">{{ $narrativa['autonomia']['limite'] }}</p>
</section>
<section class="rm-card rm-expert-clinical-block" aria-labelledby="faltantes-titulo">
    <h2 id="faltantes-titulo" class="rm-expert-section-title">¿Qué información nos falta?</h2>
    <p class="rm-expert-prose">{{ $narrativa['completa'] ? 'La información requerida para este análisis está completa.' : ($narrativa['contradiccion'] ? 'Encontramos resultados que no coinciden entre sí y deben revisarse antes de llegar a una conclusión integrada.' : 'Todavía no contamos con información suficiente para establecer una conclusión completa.') }}</p>
    <ul class="rm-expert-reasons mt-4">@foreach($narrativa['faltantes'] as $faltante)<li><h3 class="font-semibold">{{ $faltante['titulo'] }}</h3><p class="rm-expert-prose mt-2">{{ $faltante['texto'] }}</p></li>@endforeach</ul>
</section>
<section class="rm-card rm-expert-clinical-block" aria-labelledby="significado-titulo">
    <h2 id="significado-titulo" class="rm-expert-section-title">¿Qué significa el resultado?</h2><p class="rm-expert-prose">{{ $narrativa['significado'] }}</p>
</section>
<section class="rm-card rm-expert-clinical-block" aria-labelledby="revisar-titulo">
    <h2 id="revisar-titulo" class="rm-expert-section-title">¿Qué corresponde revisar?</h2>
    <p class="rm-expert-prose">El profesional responsable debe contrastar los hallazgos con sus fuentes y revisar los siguientes puntos antes de interpretar el perfil en conjunto.</p>
    <ul class="list-disc pl-5 space-y-2 mt-3">@foreach($narrativa['revision'] as $punto)<li>{{ $punto }}</li>@endforeach</ul>
</section>
