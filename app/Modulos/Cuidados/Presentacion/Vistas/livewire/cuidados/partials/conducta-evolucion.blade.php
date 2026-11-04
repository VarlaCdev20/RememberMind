<section class="rm-conducta__card rm-conducta__evolution">
    <header><div><h3>Cantidad de registros</h3><p>Registros de conducta confirmados por día</p></div><span class="rm-conducta__chart-total"><strong>{{ $contexto['resumen']['total'] }}</strong> {{ $contexto['resumen']['total'] === 1 ? 'registro' : 'registros' }}</span></header>
    <p class="rm-conducta__chart-purpose"><i class="ph-bold ph-info" aria-hidden="true"></i>Este conteo no mide intensidad, gravedad ni mejoría de la conducta.</p>
    <x-ui.field label="Periodo" for="conducta-periodo"><select id="conducta-periodo" wire:model.live="dias" class="rm-select"><option value="7">Últimos 7 días</option><option value="14">Últimos 14 días</option><option value="30">Últimos 30 días</option></select></x-ui.field>
    @if(empty($contexto['series']))
        <div class="rm-conducta__empty"><i class="ph-bold ph-chart-line" aria-hidden="true"></i><strong>Sin datos en este periodo</strong><p>No se representa una tendencia sin registros.</p></div>
    @else
        <div x-data="rmConductaChart(@js($contexto['series']))" wire:key="conducta-chart-{{ $dias }}-{{ $contexto['resumen']['total'] }}">
            <p x-show="!points.length" x-cloak class="rm-conducta__help">No hay datos válidos para representar. Puedes consultar los registros en el historial.</p>
            <div x-show="points.length" class="rm-conducta__line-chart" aria-hidden="true">
                <div class="rm-conducta__line-scale"><span x-text="upper"></span><span x-text="upper / 2"></span><span>0</span></div>
                <div class="rm-conducta__line-plot">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" focusable="false">
                        <polygon x-show="area" :points="area" class="rm-conducta__line-area"></polygon>
                        <polyline x-show="line" :points="line" class="rm-conducta__line-path"></polyline>
                    </svg>
                    <template x-for="(row, index) in points" :key="row.date"><span class="rm-conducta__line-dot" :class="{ 'is-active': active === index }" :style="'left:' + row.x + '%;top:' + row.y + '%'" @mouseenter="active = index" @click="active = index" :title="row.label + ': ' + row.value"><span x-show="points.length <= 6 || active === index" x-text="row.value"></span></span></template>
                    <div class="rm-conducta__line-dates"><template x-for="(row, index) in ticks" :key="row.date"><span :class="{ 'is-last': ticks.length > 1 && index === ticks.length - 1, 'is-single': ticks.length === 1 }" x-text="row.label"></span></template></div>
                </div>
            </div>
            <div x-show="points.length" class="rm-conducta__line-legend"><span aria-hidden="true"></span> Registros guardados <small x-show="points.length === 1">Un registro aislado; sin tendencia.</small></div>
            <div x-show="points.length" class="rm-conducta__chart-readout" aria-live="polite">
                <p x-show="!selected">Selecciona un punto o una fecha para consultar sus registros.</p>
                <div x-show="selected" x-cloak role="tooltip" id="conducta-chart-detail"><strong x-text="selected ? selected.date + ' · ' + selected.value + (selected.value === 1 ? ' registro' : ' registros') : ''"></strong><span x-text="selected?.observed ?? ''"></span></div>
            </div>
            <details x-show="points.length" class="rm-conducta__chart-dates"><summary>Consultar fechas y valores <span x-text="'(' + points.length + ')'"></span><i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary><div class="rm-conducta__date-grid" role="group" aria-label="Registros confirmados por fecha">
                <template x-for="(row, index) in points" :key="row.date"><button type="button" class="rm-conducta__chart-point" @click="active = index" @mouseenter="active = index" @focus="active = index" :class="{ 'is-active': active === index }" :aria-pressed="active === index" :aria-label="row.date + ': ' + row.value + ' registros. ' + row.observed" :aria-describedby="active === index ? 'conducta-chart-detail' : null"><span x-text="row.label"></span><strong x-text="row.value + (row.value === 1 ? ' registro' : ' registros')"></strong></button></template>
            </div></details>
        </div>
        <p class="rm-conducta__help">Solo fechas registradas. La línea une los puntos disponibles; los intervalos sin datos no equivalen a cero.</p>
    @endif
</section>
