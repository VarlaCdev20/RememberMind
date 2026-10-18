<div class="space-y-4 mt-3 text-sm">
    <p>Valores utilizados de la instantánea original. Los cambios posteriores de las fuentes no modifican esta consulta.</p>
    @foreach($datos['simulacion_local']['hechos'] as $hecho)
        <dl class="break-words"><dt class="font-semibold">{{ $hecho['concepto'] }}</dt>
            <dd>Valor: {{ $hecho['valor'] === null ? 'NULL · no registrado' : (is_bool($hecho['valor']) ? ($hecho['valor'] ? 'true · marcado' : 'false · no marcado') : (string) $hecho['valor']) }}</dd>
            <dd>{{ $hecho['origen'] }} · {{ $hecho['fecha'] }}</dd>
        </dl>
    @endforeach
    <p class="font-semibold">Referencias artificiales de {{ $informe['actual']['nombre'] }}</p>
    @foreach($detalle['evidencias'] as $e)
        <dl class="break-words"><dt>{{ $e['nombre'] }} · {{ $e['valor'] ?? 'NULL' }}</dt><dd>{{ $e['origen'] }} · {{ $e['fecha'] }}</dd>
            @foreach($e['referencia'] as $campo => $valor)<dd>{{ $campo }}: {{ $valor }}</dd>@endforeach
        </dl>
    @endforeach
    <p>Las referencias 03A se declaran por separado para probar la clasificación. Un control descriptivo o una caída no las sustituye.</p>
</div>
