<x-ui.section-card title="Evidencias conservadas" subtitle="La fecha mostrada es la incorporación a la evaluación, no la fecha original de medición." icon="ph-files">
    @if($detalle['evidencias'])
        <table class="rm-expert-evidence-table text-sm">
            <caption class="sr-only">Evidencias, procedencia, admisibilidad y participación conservadas para el criterio seleccionado</caption>
            <thead><tr><th scope="col">Evidencia</th><th scope="col">Fuente y fecha</th><th scope="col">Admisibilidad</th><th scope="col">Participación</th><th scope="col">Dato fuente</th></tr></thead>
            <tbody>
                @foreach($detalle['evidencias'] as $e)
                    <tr wire:key="evidencia-{{ $e['id'] }}">
                        <td data-label="Evidencia"><strong>{{ $e['nombre'] }}</strong><p class="text-xs text-rm-secondary mt-1">{{ $e['papel'] }}</p><span class="block text-xs text-rm-secondary mt-1 break-all">{{ $e['id'] }}</span></td>
                        <td data-label="Fuente y fecha">{{ $e['fuente'] }}<p class="text-xs text-rm-secondary mt-1">{{ $e['fecha'] ?? 'No disponible' }}</p></td>
                        <td data-label="Admisibilidad"><span class="rm-expert-status mt-0">{{ \App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto::etiqueta($e['admisibilidad']) }}</span><p class="text-xs text-rm-secondary mt-1">Representación: {{ \App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto::etiqueta($e['representacion']) }}</p>@if($e['motivo'])<p class="mt-2">{{ $e['motivo'] }}</p>@endif</td>
                        <td data-label="Participación">{{ \App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto::etiqueta($e['rol']) }}<p class="text-xs text-rm-secondary mt-1">{{ \App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto::etiqueta($e['participacion']) }}</p>@if($e['justificacion'])<p class="mt-2">{{ $e['justificacion'] }}</p>@endif</td>
                        <td><button type="button" wire:click="verEvidencia(@js($e['id']))" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary" aria-expanded="{{ $evidencia === $e['id'] ? 'true' : 'false' }}">{{ $evidencia === $e['id'] ? 'Cerrar fuente' : 'Consultar fuente' }}</button></td>
                    </tr>
                    @if($evidencia === $e['id'] && $fuente)
                        <tr><td colspan="5"><div class="rounded-xl bg-rm-surface-soft p-4" role="status">
                            <p class="font-semibold">{{ $fuente['estado'] }}</p>
                            @if(isset($fuente['fecha']))<p class="mt-2">Fecha del registro fuente actual: {{ $fuente['fecha'] }}</p>@endif
                            @if(isset($fuente['valor']))<p class="mt-2">Dato fuente actual: {{ $fuente['valor'] }}</p>@endif
                            @if(isset($fuente['instrumento']))<p class="mt-2">Instrumento: {{ $fuente['instrumento'] }}</p>@endif
                            @if(isset($fuente['respuestas']))
                                <p class="text-sm mt-2">{{ $fuente['version'] }} · {{ $fuente['aplicacion'] }} · Autor sintético: {{ $fuente['autor'] }}</p>
                                <dl class="space-y-3 mt-4">
                                    @foreach($fuente['respuestas'] as $respuesta)
                                        <div><dt class="font-medium">{{ $respuesta['pregunta'] }}</dt><dd class="mt-1">{{ $respuesta['valor'] }}</dd></div>
                                    @endforeach
                                </dl>
                            @endif
                        </div></td></tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-sm text-rm-secondary">No hay evidencias conservadas para esta área.</p>
    @endif
</x-ui.section-card>
