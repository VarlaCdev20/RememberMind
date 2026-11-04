<div class="rm-cognicion__evolution" wire:key="cognicion-series-{{ md5(json_encode([$contexto['series'] ?? [], $contexto['ultima'] ?? null, $dias, $desde, $hasta, $errorConsulta])) }}">
    <div class="rm-cognicion__filters">
        <label for="cognicion-domain">Dominio<select id="cognicion-domain" class="rm-select" :value="domain" @change="chooseDomain($event.target.value)">@foreach($dominios as $clave => $label)<option value="{{ $clave }}">{{ $label }}</option>@endforeach</select></label>
        <label for="cognicion-indicator">Indicador<select id="cognicion-indicator" class="rm-select" x-model="indicator" @change="selectedRecord = null"><template x-for="key in domainFields" :key="key"><option :value="key" x-text="fields[key][0]"></option></template></select></label>
        <label for="cognicion-period">Periodo<select id="cognicion-period" class="rm-select" wire:model.live="dias"><option value="7">Últimos 7 días</option><option value="30">Últimos 30 días</option><option value="90">Últimos 90 días</option><option value="0">Personalizado</option></select></label>
    </div>
    @if($dias === 0)<div class="rm-cognicion__filters"><x-ui.field label="Desde" for="cognicion-from" error="desde"><input id="cognicion-from" type="date" class="rm-input" wire:model.live="desde" max="{{ today()->toDateString() }}"></x-ui.field><x-ui.field label="Hasta" for="cognicion-to" error="hasta"><input id="cognicion-to" type="date" class="rm-input" wire:model.live="hasta" max="{{ today()->toDateString() }}"></x-ui.field></div>@endif
    <p wire:loading role="status">Consultando registros…</p>
    @if($errorConsulta)
        <x-ui.callout variant="info" title="Consulta no disponible">{{ $errorConsulta }} <button type="button" class="rm-btn-secondary" wire:click="reintentarConsulta">Reintentar</button></x-ui.callout>
    @else
        <div class="rm-cognicion__comparison">
            <article class="rm-cognicion__card"><span class="rm-cognicion__record-label"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i> Historial</span><h4>Última guardada</h4>@if($contexto['ultima'])<strong x-text="response(indicator, @js($contexto['ultima']['respuestas'])[indicator])"></strong><p>{{ $contexto['ultima']['fecha'] }} · {{ $contexto['ultima']['hora'] }}</p><small>{{ $contexto['ultima']['profesional'] }} · {{ $contexto['ultima']['estado'] }}</small>@else<p>Sin observaciones previas</p>@endif</article>
            <article class="rm-cognicion__card"><span class="rm-cognicion__record-label"><i class="ph-bold ph-pencil-simple" aria-hidden="true"></i> {{ $resultado === [] ? 'Vista previa' : 'Registro confirmado' }}</span><h4>{{ $resultado === [] ? 'Actual · Sin guardar' : 'Captura confirmada' }}</h4><strong x-text="response(indicator, $wire.datos[indicator])"></strong><p>{{ $resultado === [] ? 'No forma parte del historial hasta guardar.' : 'Datos del registro recién guardado' }}</p></article>
        </div>
        <p class="rm-help" x-text="comparison(@js($contexto['ultima']['respuestas'] ?? [])[indicator])"></p>
        <h4>Secuencia de respuestas</h4><p class="rm-help">Selecciona una observación para consultar su fecha y autor. Cada punto es un registro guardado, no un puntaje.</p>
        @if(empty($contexto['series']))<p class="rm-cognicion__empty">{{ $contexto['hayHistoria'] ? 'No se encontraron registros vigentes para el periodo seleccionado.' : 'Todavía no existen observaciones cognitivas registradas para este residente.' }}</p>@else
            <ol class="rm-cognicion__timeline" aria-label="Evolución categórica">
                @foreach($contexto['series'] as $registro)
                    <li><time datetime="{{ $registro['instante'] }}">{{ $registro['fecha'] }}<small>{{ $registro['hora'] }}</small></time><button type="button" class="rm-cognicion__point" :data-unobserved="@js($registro['respuestas'])[indicator] === null" :aria-pressed="selectedRecord?.codigo === @js($registro['codigo'])" @click="selectedRecord = @js($registro)" @focus="selectedRecord = @js($registro)" :aria-label="@js($registro['fecha'].' '.$registro['hora'].' · ').concat(fields[indicator][0], ': ', response(indicator, @js($registro['respuestas'])[indicator]))"><i class="ph-bold ph-circle" aria-hidden="true"></i><span x-text="response(indicator, @js($registro['respuestas'])[indicator])"></span></button></li>
                @endforeach
            </ol>
            @if(count($contexto['series']) === 1)<p class="rm-help">Un registro guardado. No hay una tendencia para calcular.</p>@endif
            @if($contexto['graficaLimitada'])<p class="rm-help">Se muestran los últimos 200 registros vigentes del periodo. El historial dispone de paginación.</p>@endif
        @endif
        @if($resultado === [])<div class="rm-cognicion__preview" x-show="$wire.datos[indicator] !== null && $wire.datos[indicator] !== ''"><b>Actual · Sin guardar</b><span x-text="response(indicator, $wire.datos[indicator])"></span></div>@endif
        <div class="rm-cognicion__card" x-show="selectedRecord" x-cloak role="status" aria-live="polite"><strong x-text="fields[indicator][0]"></strong><p x-text="selectedRecord ? response(indicator, selectedRecord.respuestas[indicator]) : ''"></p><small x-text="selectedRecord ? selectedRecord.fecha + ' ' + selectedRecord.hora + ' · ' + selectedRecord.profesional + ' · ' + selectedRecord.estado + ' · Guardado' : ''"></small></div>
        <details class="rm-cognicion__domain-disclosure"><summary>Resumen de otros dominios <i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary><div class="rm-cognicion__domain-history">@foreach($dominios as $clave => $label)<button type="button" class="rm-btn-secondary" @click="chooseDomain(@js($clave))" :aria-pressed="domain === @js($clave)"><span>{{ $label }}</span><small>{{ $contexto['resumenDominios'][$clave]['resumen'] ?? 'Sin datos en los registros mostrados' }}</small>@if($contexto['resumenDominios'][$clave])<small>Última respuesta mostrada · {{ $contexto['resumenDominios'][$clave]['fecha'] }}</small>@endif</button>@endforeach</div></details>
        <button type="button" class="rm-btn-secondary" @click="openHistory($event.currentTarget)">Ver historial</button>
    @endif
</div>
