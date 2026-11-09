@php($tiposDeGrafica = ['auto' => 'Recomendado', 'dona' => 'Dona', 'torta' => 'Torta', 'radar' => 'Radar · estrella', 'barras' => 'Barras'])

@if($selectorGraficaCompacto ?? false)
    <x-dropdown align="right" content-classes="rm-report-chart-choice__menu">
        <x-slot:trigger>
            <x-ui.action-button variant="secondary" icono="ph-chart-pie" class="rm-report-chart-choice" aria-label="Diseño de las gráficas"
                aria-controls="inicio-gerencia-tipos-grafica" x-bind:aria-expanded="open" x-bind:disabled="cargandoGraficas" />
        </x-slot:trigger>
        <x-slot:content>
            <div id="inicio-gerencia-tipos-grafica" class="rm-choice__options" role="group" aria-label="Diseño de las gráficas">
                @foreach($tiposDeGrafica as $tipo => $nombre)
                    <button type="button" value="{{ $tipo }}" :class="{ 'is-selected': tipoGraficas === $el.value }"
                        :aria-pressed="tipoGraficas === $el.value"
                        @click="tipoGraficas = $el.value; $nextTick(() => $refs.trigger.querySelector('button').focus())">
                        {{ $nombre }}
                        <i class="ph-bold ph-check" aria-hidden="true" x-show="tipoGraficas === $el.parentElement.value"></i>
                    </button>
                @endforeach
            </div>
        </x-slot:content>
    </x-dropdown>
@else
    <label class="rm-field">
        <span>Diseño de las gráficas</span>
        <select class="rm-input" x-model="tipoGraficas" :disabled="cargandoGraficas || $data.descargandoExportacion === true">
            @foreach($tiposDeGrafica as $tipo => $nombre)
                <option value="{{ $tipo }}">{{ $nombre }}</option>
            @endforeach
        </select>
    </label>
@endif
