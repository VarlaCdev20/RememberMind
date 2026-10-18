@php($evolucion = $this->evolucionComparada)

<section id="evolucion-residente" class="mb-5 min-w-0 space-y-4 text-[var(--rm-text-primary)]" aria-labelledby="evolucion-residente-titulo"
    wire:key="evolucion-{{ $adultoMayor->getKey() }}-{{ $periodoEstado }}"
    x-data="evolucionResidente()" @click="abrirOriginal($event)">
    <div class="rm-clinical-card space-y-4">
        <div class="space-y-1">
            <h2 id="evolucion-residente-titulo" class="rm-clinical-card__title"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i>Evolución del residente</h2>
            <p class="text-sm leading-relaxed">Comparación descriptiva de registros. Se utiliza el período seleccionado en “Estado y evolución del residente”.</p>
        </div>
        <div class="grid min-w-0 gap-4 md:grid-cols-2">
            <div class="min-w-0 space-y-2 border-l-2 border-[var(--rm-info-strong)] pl-3 text-sm">
                <h3 class="font-semibold">Período actual · {{ $evolucion['dias'] }} días</h3>
                <p>{{ $evolucion['actual']['desde'] }} — {{ $evolucion['actual']['hasta'] }}</p>
                <p>{{ trans_choice('{1} :count incidente|[0,*] :count incidentes', $evolucion['actual']['resumen']['total_incidentes']) }} · {{ trans_choice('{1} :count control|[0,*] :count controles', $evolucion['actual']['resumen']['total_signos']) }} · {{ trans_choice('{1} :count alerta|[0,*] :count alertas', $evolucion['actual']['resumen']['total_alertas']) }} · {{ trans_choice('{1} :count nota|[0,*] :count notas', $evolucion['actual']['resumen']['total_observaciones']) }}.</p>
            </div>
            <div class="min-w-0 space-y-2 border-l-2 border-[var(--rm-border-strong)] pl-3 text-sm">
                <h3 class="font-semibold">Período anterior · {{ $evolucion['dias'] }} días</h3>
                <p>{{ $evolucion['anterior']['desde'] }} — antes del {{ $evolucion['anterior']['hasta'] }}</p>
                <p>{{ trans_choice('{1} :count incidente|[0,*] :count incidentes', $evolucion['anterior']['resumen']['total_incidentes']) }} · {{ trans_choice('{1} :count control|[0,*] :count controles', $evolucion['anterior']['resumen']['total_signos']) }} · {{ trans_choice('{1} :count alerta|[0,*] :count alertas', $evolucion['anterior']['resumen']['total_alertas']) }} · {{ trans_choice('{1} :count nota|[0,*] :count notas', $evolucion['anterior']['resumen']['total_observaciones']) }}.</p>
            </div>
        </div>
        <p class="text-sm leading-relaxed">Ambas ventanas tienen la misma duración. El instante compartido pertenece al período actual y se excluye del anterior. Las fechas siguen la zona horaria institucional.</p>
    </div>

    <div class="grid min-w-0 items-start gap-4 lg:grid-cols-2">
        @foreach($evolucion['comparaciones'] as $comparacion)
            <article class="rm-clinical-card min-w-0 space-y-3 break-words">
                <h3 class="rm-clinical-card__title">{{ $comparacion['categoria'] }}</h3>
                <p class="text-sm font-semibold leading-relaxed">{{ $comparacion['titulo'] }}</p>
                <p class="text-sm leading-relaxed">{{ $comparacion['descripcion'] }}</p>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt>Actual</dt><dd class="font-semibold">{{ trans_choice('{1} :count registro|[0,*] :count registros', $comparacion['actual']) }}</dd></div>
                    <div><dt>Anterior</dt><dd class="font-semibold">{{ trans_choice('{1} :count registro|[0,*] :count registros', $comparacion['anterior']) }}</dd></div>
                </dl>
                <details class="border-t border-[var(--rm-border)]">
                    <summary class="min-h-11 cursor-pointer py-3 text-sm font-semibold">¿Cómo se llegó a este resultado?</summary>
                    <p class="mb-3 text-sm leading-relaxed">{{ $comparacion['fundamento'] }}</p>
                    <div class="space-y-3 text-sm">
                        <div>
                            <h4 class="font-semibold">Actual: {{ $evolucion['actual']['desde'] }} — {{ $evolucion['actual']['hasta'] }}</h4>
                            @forelse($comparacion['registros_actuales'] as $registro)
                                <a class="flex min-h-11 items-center break-words py-2 underline" href="#evolucion-{{ $registro['ancla'] }}" data-original-evolucion>{{ $registro['fecha'] }} · {{ $registro['codigo'] }} · Consultar original</a>
                            @empty
                                <p class="py-2">Sin registros de esta categoría en el período actual.</p>
                            @endforelse
                        </div>
                        <div>
                            <h4 class="font-semibold">Anterior: {{ $evolucion['anterior']['desde'] }} — antes del {{ $evolucion['anterior']['hasta'] }}</h4>
                            @forelse($comparacion['registros_anteriores'] as $registro)
                                <a class="flex min-h-11 items-center break-words py-2 underline" href="#evolucion-{{ $registro['ancla'] }}" data-original-evolucion>{{ $registro['fecha'] }} · {{ $registro['codigo'] }} · Consultar original</a>
                            @empty
                                <p class="py-2">Sin registros de esta categoría en el período anterior.</p>
                            @endforelse
                        </div>
                    </div>
                </details>
            </article>
        @endforeach
    </div>

    @if($evolucion['incidentes_por_tipo'])
        <details class="rm-clinical-card">
            <summary class="min-h-11 cursor-pointer py-2 text-sm font-semibold">Cambios documentados por tipo de incidente</summary>
            <dl class="mt-3 divide-y divide-[var(--rm-border)] text-sm">
                @foreach($evolucion['incidentes_por_tipo'] as $tipo)
                    <div class="flex flex-wrap justify-between gap-2 py-3"><dt class="font-semibold break-words">{{ $tipo['tipo'] }}</dt><dd>Actual: {{ $tipo['actual'] }} · Anterior: {{ $tipo['anterior'] }}</dd></div>
                @endforeach
            </dl>
        </details>
    @endif

    <div class="rm-chart-card min-w-0" x-data="evolucionResidenteChart(@js($evolucion['presion']), @js('evolucion-pa-'.$adultoMayor->getKey().'-'.$periodoEstado), @js($evolucion['rango_presion']))">
        <header class="rm-chart-header">
            <div class="min-w-0 space-y-1"><h3 class="rm-chart-title">Mediciones originales de presión arterial</h3><p class="text-sm">Sistólica y diastólica en mmHg · ambos períodos, en sus fechas reales.</p></div>
        </header>
        @if($evolucion['presion'])
            <div class="rm-chart-body is-md min-w-0" wire:ignore><canvas x-ref="canvas" role="img" aria-label="Puntos originales de presión arterial sistólica y diastólica. Los valores y sus clasificaciones están disponibles en la cronología."></canvas></div>
            <p class="px-4 pb-4 text-sm leading-relaxed">Cada punto es una medición registrada. No se interpolan ausencias ni se dibujan umbrales nuevos. Consulte la fecha, el período y la clasificación en el punto o en el registro original de la cronología.</p>
            <p x-cloak x-show="error" class="px-4 pb-4 text-sm" role="status">La gráfica no está disponible. Los valores originales siguen disponibles en la cronología.</p>
        @else
            <p class="p-4 text-sm">No se registraron mediciones de presión arterial en los dos períodos consultados.</p>
        @endif
    </div>

    <article class="rm-clinical-card min-w-0 space-y-3">
        <h3 class="rm-clinical-card__title">Comparación funcional</h3>
        @if(!$evolucion['funcional']['autorizada'])
            <p class="text-sm">Su cuenta no tiene permiso para consultar valoraciones funcionales.</p>
        @else
            <p class="text-sm leading-relaxed">Se muestran los campos originales de la última valoración de cada período. No se deduce mejoría ni deterioro a partir de diferencias literales.</p>
            @if(!$evolucion['funcional']['comparable'])
                <p class="text-sm font-semibold">No se dispone de valoraciones funcionales con campos comparables suficientes en ambos períodos.</p>
            @endif
            <div class="grid gap-3 md:grid-cols-2 text-sm">
                <p><strong>Actual:</strong> {{ $evolucion['funcional']['actual']['fecha'] ?? 'Sin valoración en este período' }} · {{ $evolucion['funcional']['actual']['codigo'] ?? 'Sin registro' }}</p>
                <p><strong>Anterior:</strong> {{ $evolucion['funcional']['anterior']['fecha'] ?? 'Sin valoración en este período' }} · {{ $evolucion['funcional']['anterior']['codigo'] ?? 'Sin registro' }}</p>
            </div>
            <dl class="divide-y divide-[var(--rm-border)] text-sm">
                @foreach($evolucion['funcional']['campos'] as $campo)
                    <div class="grid gap-2 py-3 md:grid-cols-3"><dt class="font-semibold">{{ $campo['etiqueta'] }}</dt><dd>Actual: {{ $campo['actual'] }}<br>Anterior: {{ $campo['anterior'] }}</dd><dd>{{ $campo['comparacion'] }}</dd></div>
                @endforeach
            </dl>
        @endif
    </article>

    <article class="rm-clinical-card min-w-0 space-y-3">
        <h3 class="rm-clinical-card__title">Evaluaciones por instrumento y evolución cognitiva</h3>
        @if(!$evolucion['instrumentos']['autorizada'])
            <p class="text-sm">Su cuenta no tiene permiso para consultar aplicaciones de instrumentos.</p>
        @else
            <p class="text-sm font-semibold leading-relaxed">{{ $evolucion['mensaje_cognitivo'] }}</p>
            <p class="text-sm leading-relaxed">{{ $evolucion['limite_cognitivo'] }}</p>
            @forelse($evolucion['instrumentos']['grupos'] as $instrumento)
                <div class="space-y-2 border-t border-[var(--rm-border)] pt-3 text-sm">
                    <h4 class="font-semibold">{{ $instrumento['nombre'] }} · {{ $instrumento['codigo'] }}</h4>
                    <p>Tipo registrado: {{ $instrumento['tipo'] ?? 'No registrado' }} · Versión del catálogo actual: {{ $instrumento['version_catalogo'] ?? 'No registrada' }}</p>
                    <p>Actual: {{ $instrumento['actual']['campos']['Puntaje registrado'] ?? 'Sin puntaje' }} / {{ $instrumento['actual']['campos']['Máximo registrado'] ?? 'Sin máximo' }} · {{ $instrumento['actual']['fecha'] ?? 'Sin aplicación en el período' }} · {{ $instrumento['actual']['codigo'] ?? 'Sin registro' }}</p>
                    <p>Anterior: {{ $instrumento['anterior']['campos']['Puntaje registrado'] ?? 'Sin puntaje' }} / {{ $instrumento['anterior']['campos']['Máximo registrado'] ?? 'Sin máximo' }} · {{ $instrumento['anterior']['fecha'] ?? 'Sin aplicación en el período' }} · {{ $instrumento['anterior']['codigo'] ?? 'Sin registro' }}</p>
                    @if($instrumento['comparable'])
                        <p class="font-semibold">Diferencia aritmética entre puntajes registrados: {{ $instrumento['diferencia'] }} puntos.</p>
                        <p>Mismo instrumento y mismo máximo positivo registrado. Esta diferencia descriptiva no determina evolución clínica.</p>
                    @else
                        <p>Sin puntajes comparables: falta una aplicación, un puntaje o un máximo positivo coincidente entre los períodos.</p>
                    @endif
                </div>
            @empty
                <p class="text-sm">No se registraron aplicaciones de instrumentos en los dos períodos consultados.</p>
            @endforelse
        @endif
    </article>

    <details class="rm-clinical-card min-w-0" x-ref="cronologia" data-cronologia-evolucion>
        <summary class="min-h-11 cursor-pointer py-2 text-sm font-semibold">Cronología y registros originales · {{ count($evolucion['cronologia']) }} registros distintos</summary>
        <p class="my-3 text-sm">Orden por fecha del registro. Cada código aparece una sola vez, aunque fundamente varias comparaciones.</p>
        <div class="divide-y divide-[var(--rm-border)]">
            @forelse($evolucion['cronologia'] as $evidencia)
                <div class="min-w-0 break-words py-2">
                    <p class="text-sm font-semibold">{{ $evidencia['periodo'] === 'actual' ? 'Período actual' : 'Período anterior' }}</p>
                    @include('clinica::livewire.cuidados.ficha.resumen.registro-original', ['contexto' => 'evolucion-'.$evidencia['periodo'], 'idOriginal' => 'evolucion-'.$evidencia['ancla']])
                </div>
            @empty
                <p class="py-3 text-sm">No se registraron datos autorizados en ninguno de los períodos.</p>
            @endforelse
        </div>
    </details>
</section>
