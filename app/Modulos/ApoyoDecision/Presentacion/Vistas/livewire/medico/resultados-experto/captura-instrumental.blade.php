<x-ui.section-card title="Mediciones instrumentales del expediente" subtitle="Consulta de las fuentes actuales del expediente" icon="ph-clipboard-text">
    <p class="text-sm text-rm-secondary">Fecha de consulta: {{ $capturaInstrumental['fecha_corte'] }}. Las evidencias de una evaluación histórica se consultan en su informe conservado.</p>
    @forelse($capturaInstrumental['capturas'] as $captura)
        <details class="rounded-xl border border-rm bg-rm-surface-soft p-4 mt-3 min-w-0" wire:key="captura-{{ $captura['cod_aplicacion'] }}">
            <summary class="cursor-pointer min-h-11 font-semibold break-words">{{ $captura['nombre_instrumento'] }} · {{ $captura['version_instrumento'] ?: 'Versión no registrada' }}</summary>
            <p class="text-sm mt-3 break-words">Fecha original: {{ $captura['fecha_hora'] }} · Profesional: {{ $captura['autor'] }} · Estado registrado: {{ $captura['estado_original'] }}</p>
            @if($captura['extraccion'])
                @foreach($captura['extraccion']['normalizacion']['componentes'] as $componente => $calculo)
                    @foreach($calculo['medidas'] ?? [$calculo] as $medida)
                        <p class="text-sm mt-3 break-words"><strong>{{ $componente }}{{ isset($medida['medida']) ? ' · '.$medida['medida'] : '' }}</strong>: {{ $medida['estado'] }}
                            @if($medida['estado'] === 'NORMALIZADO')
                                · Medición: {{ $medida['puntuacion_bruta'] }}
                                @if($medida['z'] !== null) · Referencia: {{ $medida['esperado'] }} · z: {{ $medida['z'] }} @endif
                                @if(isset($medida['percentil_intervalo']))
                                    · Intervalo percentilar aproximado:
                                    @if($medida['percentil_intervalo']['inferior'] === null) por debajo de P{{ $medida['percentil_intervalo']['superior'] }}.
                                    @elseif($medida['percentil_intervalo']['superior'] === null) por encima de P{{ $medida['percentil_intervalo']['inferior'] }}.
                                    @elseif($medida['percentil_intervalo']['inferior'] === $medida['percentil_intervalo']['superior']) en torno a P{{ $medida['percentil_intervalo']['inferior'] }}.
                                    @else entre P{{ $medida['percentil_intervalo']['inferior'] }} y P{{ $medida['percentil_intervalo']['superior'] }}. @endif
                                @endif
                                @if(($medida['direccion'] ?? null) === 'MENOR_ES_MEJOR') · Menor tiempo indica mejor rendimiento; z positivo indica más lentitud respecto de la referencia. @endif
                            @endif
                        </p>
                    @endforeach
                @endforeach
                <p class="text-sm text-rm-secondary mt-3">La normalización compara la medición con su referencia publicada. La interpretación del dominio requiere sus condiciones y soportes.</p>
            @else
                <p class="text-sm text-rm-secondary mt-3">No hay cálculo instrumental disponible para esta aplicación. Se conservan sus respuestas originales.</p>
            @endif
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4">
                @foreach($captura['respuestas_originales'] as $respuesta)
                    <div class="min-w-0"><dt class="text-sm text-rm-secondary break-words">{{ $respuesta['pregunta'] ?: $respuesta['codigo_pregunta'] }}</dt>
                        <dd class="text-sm font-semibold break-words whitespace-pre-line">{{ $respuesta['valor_texto'] ?? $respuesta['valor_numero'] ?? ($respuesta['valor_logico'] !== null ? ($respuesta['valor_logico'] ? 'Sí' : 'No') : ($respuesta['opcion_actual']['nombre'] ?? $respuesta['cod_opcion'] ?? 'Sin dato')) }}</dd>
                        @if($respuesta['observacion'])<dd class="text-sm break-words whitespace-pre-line mt-1">Observación registrada: {{ $respuesta['observacion'] }}</dd>@endif
                    </div>
                @endforeach
            </dl>
            @if($captura['aplicacion_actual']['observacion'])<p class="text-sm break-words whitespace-pre-line mt-3">Observación de la aplicación: {{ $captura['aplicacion_actual']['observacion'] }}</p>@endif
            <details class="mt-3"><summary class="cursor-pointer min-h-11 text-sm">Procedencia y condiciones de cálculo</summary>
                <p class="text-xs break-words">Aplicación {{ $captura['cod_aplicacion'] }} · Instrumento {{ $captura['cod_instrumento'] }} · Autor {{ $captura['cod_personal'] }}</p>
                @foreach(array_merge($captura['motivos'], $captura['extraccion']['motivos_captura'] ?? []) as $motivo)<p class="text-xs break-words mt-2">{{ $motivo }}</p>@endforeach
                @foreach($captura['extraccion']['normalizacion']['componentes'] ?? [] as $componente => $calculo)
                    @foreach($calculo['medidas'] ?? [$calculo] as $medida)
                        @foreach($medida['motivos'] as $motivo)<p class="text-xs break-words mt-2">{{ $componente }} · {{ $medida['medida'] ?? '' }} · {{ $motivo }}</p>@endforeach
                    @endforeach
                @endforeach
            </details>
        </details>
    @empty
        <p class="text-sm text-rm-secondary">No hay aplicaciones instrumentales registradas hasta la fecha de consulta.</p>
    @endforelse
</x-ui.section-card>
