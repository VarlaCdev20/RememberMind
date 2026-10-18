@php($sim = $datos['simulacion_local'])
<section class="rm-card rm-expert-clinical-block" aria-labelledby="simulacion-titulo">
    <h2 id="simulacion-titulo" class="rm-expert-section-title">Simulación local · {{ $sim['caso'] }}</h2>
    <p role="note" class="font-semibold text-sm mt-2">{{ $sim['advertencia'] }}</p>
    <p class="text-sm mt-2">Residente ficticio y referencias artificiales. Esta ejecución permite comprobar el sistema; no constituye un diagnóstico ni una valoración profesional.</p>
    @if($sim['conservacion_completa'])
        <p class="text-sm mt-2">Fecha de corte conservada: {{ $sim['fecha_corte'] }}</p>
        <p class="font-semibold mt-3">{{ $sim['patron'] ? 'Patrón candidato experimental: '.$sim['patron'] : 'Sin patrón candidato experimental' }}</p>
        <p class="text-sm mt-2">{{ $sim['explicacion'] }}</p>
        @foreach($sim['motivos'] as $motivo)<p class="text-sm mt-2 break-words">{{ $motivo }}</p>@endforeach
        <details class="rm-expert-supplement mt-3">
            <summary>Consultar los cinco requisitos conservados</summary>
            <dl class="mt-3 space-y-3 text-sm">
                @foreach($sim['criterios'] as $c)
                    <div><dt class="font-semibold">{{ $c['codigo'] }} · {{ $c['estado'] }}</dt><dd>{{ $c['razon'] }}</dd>
                    <dd class="break-words">{{ $c['soportes'] ? implode(' · ', $c['soportes']) : 'Sin soporte positivo: requisito pendiente.' }}</dd></div>
                @endforeach
            </dl>
        </details>
    @else
        @foreach($sim['integridad'] as $mensaje)<p role="alert" class="text-sm mt-3">{{ $mensaje }}</p>@endforeach
    @endif
</section>
