<x-ui.clinical-trend-window id="hidratacion-grafica-popup" title="Evolución de hidratación" :resident="$detalleResidente['nombre_completo']">
    <p class="rm-clinical-form__note">Aportes de líquido registrados · volumen en mL.</p>
    <p class="rm-dolor__empty" x-show="!history.length">Aún no hay aportes anteriores para representar.</p>
    <p class="rm-clinical-form__note" x-show="history.length === 1">Un aporte guardado; no se infiere una tendencia.</p>
    <template x-if="rows().length">
        <div class="rm-dolor__chart">
            <svg viewBox="0 0 380 230" role="group" :aria-label="'Volumen en mililitros, escala de 0 a ' + axisMaximum()">
                @for($tickIndex = 0; $tickIndex < 5; $tickIndex++)
                    <g x-data="{ tick: null }" x-effect="tick = ticks()[{{ $tickIndex }}] ?? null" x-show="tick" x-cloak>
                        <line class="rm-dolor__chart-grid" x1="48" x2="344" :y1="tick?.y ?? 0" :y2="tick?.y ?? 0" />
                        <text class="rm-dolor__chart-axis" x="38" :y="(tick?.y ?? 0) + 4" text-anchor="end" x-text="tick?.value ?? ''"></text>
                    </g>
                @endfor
                <text class="rm-dolor__chart-axis" x="48" y="16">Volumen · mL</text>
                <polyline class="rm-dolor__chart-line" fill="none" :points="historyLine()" />
                <polyline class="rm-dolor__chart-line rm-dolor__chart-line--preview" fill="none" :points="previewLine()" />
                @for($index = 0; $index < 7; $index++)
                    <g x-data="{ row: null }" x-effect="row = markers()[{{ $index }}] ?? null" x-show="row" x-cloak>
                        <circle class="rm-dolor__chart-hit" :cx="row?.x ?? 0" :cy="row?.y ?? 0" r="22" role="button" :tabindex="row ? 0 : -1" :aria-label="row?.label ?? ''" @mouseenter="selectedPoint = row" @focus="selectedPoint = row" @click="selectedPoint = row" @keydown.enter.prevent="selectedPoint = row" @keydown.space.prevent="selectedPoint = row"><title x-text="row?.label ?? ''"></title></circle>
                        <circle class="rm-dolor__chart-dot" :class="{ 'is-preview': row?.preview }" :cx="row?.x ?? 0" :cy="row?.y ?? 0" :r="row?.preview ? 7 : 5" aria-hidden="true" />
                    </g>
                @endfor
                <text class="rm-dolor__chart-axis" x="48" y="205" x-text="rows()[0]?.date ?? ''" />
                <text class="rm-dolor__chart-axis" x="344" y="222" text-anchor="end" x-text="rows().at(-1)?.date ?? ''" />
            </svg>
            <p class="rm-dolor__tooltip" x-show="selectedMarker()" x-cloak aria-live="polite" x-text="selectedMarker()?.label ?? ''"></p>
            <div class="rm-dolor__legend"><span>● Guardado</span><span x-show="!persisted">◯ Sin guardar · conexión discontinua</span></div>
        </div>
    </template>
    <div class="rm-dolor__history">
        <h6>Últimos aportes registrados</h6>
        <table x-show="history.length" class="rm-hidratacion__history-table">
            <caption class="sr-only">Historial de hidratación del residente</caption>
            <thead><tr><th scope="col">Fecha y hora</th><th scope="col">Tipo</th><th scope="col">Volumen</th><th scope="col">Tolerancia</th></tr></thead>
            <tbody><template x-for="(row, index) in [...history].reverse()" :key="row.code || index"><tr><td data-label="Fecha y hora" x-text="row.date"></td><td data-label="Tipo" x-text="row.liquid"></td><td data-label="Volumen" x-text="row.value + ' mL'"></td><td data-label="Tolerancia" x-text="row.tolerance"></td></tr></template></tbody>
        </table>
    </div>
</x-ui.clinical-trend-window>
