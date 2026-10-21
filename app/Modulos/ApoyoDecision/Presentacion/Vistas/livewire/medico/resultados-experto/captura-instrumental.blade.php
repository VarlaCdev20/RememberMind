<x-ui.section-card title="Aplicaciones y observaciones del expediente" subtitle="Fuentes registradas para este residente" icon="ph-clipboard-text">
    <p class="text-sm text-rm-secondary">Fecha de consulta: {{ $capturaInstrumental['fecha_corte'] }}. Las evidencias de una evaluación histórica se consultan en su informe conservado.</p>
    @forelse($capturaInstrumental['capturas'] as $captura)
        <details class="rounded-xl border border-rm bg-rm-surface-soft p-4 mt-3 min-w-0" wire:key="captura-{{ $captura['cod_aplicacion'] }}">
            <summary class="cursor-pointer min-h-11 font-semibold break-words">{{ ($captura['instrumento_actual']['tipo'] ?? null) === 'OBSERVACIONAL' ? 'Observación clínica' : 'Aplicación instrumental' }} · {{ $captura['nombre_instrumento'] }} · {{ $captura['version_instrumento'] ?: 'Versión no registrada' }}</summary>
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
            @elseif(($captura['instrumento_actual']['tipo'] ?? null) === 'OBSERVACIONAL')
                <p class="text-sm text-rm-secondary mt-3">Esta observación clínica se presenta sin puntuación instrumental. Sus respuestas registradas figuran a continuación.</p>
            @else
                <p class="text-sm text-rm-secondary mt-3">Esta aplicación no tiene un cálculo instrumental admisible. Consulte los motivos y las respuestas registradas.</p>
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
                @if(isset($captura['procedencia']))
                    <p class="text-sm break-words mt-2">Cuenta de registro: {{ $captura['procedencia']['cod_usuario_registro'] ?? 'No conservada en este registro' }} · Fecha de registro: {{ $captura['procedencia']['registrado_en'] ?? 'No conservada' }}</p>
                    @if($captura['procedencia']['cod_documento_clinico'])
                        <p class="text-sm break-words mt-2">Documento original: {{ $captura['procedencia']['cod_documento_clinico'] }} · Localizador: {{ $captura['procedencia']['localizador_fuente'] }} · {{ $captura['procedencia']['estado_documento'] === 'VERIFICADO' ? 'Archivo y huella verificados' : 'Original no verificable' }}</p>
                    @else
                        <p class="text-sm mt-2">Sin vínculo documental conservado.</p>
                    @endif
                    @if($captura['procedencia']['cod_aplicacion_anterior'])<p class="text-sm mt-2">Rectifica la aplicación {{ $captura['procedencia']['cod_aplicacion_anterior'] }}; el original permanece en el historial.</p>@endif
                @endif
                @foreach(array_merge($captura['motivos'], $captura['extraccion']['motivos_captura'] ?? []) as $motivo)<p class="text-sm break-words mt-2">{{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto::condicionInstrumental($motivo) }}</p>@endforeach
                @foreach($captura['extraccion']['normalizacion']['componentes'] ?? [] as $componente => $calculo)
                    @foreach($calculo['medidas'] ?? [$calculo] as $medida)
                        @foreach($medida['motivos'] as $motivo)<p class="text-sm break-words mt-2">{{ $componente }} · {{ \App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto::condicionInstrumental($motivo) }}</p>@endforeach
                    @endforeach
                @endforeach
            </details>
        </details>
    @empty
        <p class="text-sm text-rm-secondary">No hay aplicaciones ni observaciones de instrumento registradas para este residente hasta la fecha de consulta.</p>
    @endforelse
</x-ui.section-card>
