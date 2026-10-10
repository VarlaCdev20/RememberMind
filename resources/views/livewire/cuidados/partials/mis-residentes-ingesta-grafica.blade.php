<x-ui.clinical-trend-window id="ingesta-grafica-popup" title="Evolución de ingesta" :resident="$detalleResidente['nombre_completo']">
    <p class="rm-clinical-form__note" x-show="!history.length">Sin registros anteriores. No hay una tendencia guardada.</p>
    <p class="rm-clinical-form__note" x-show="history.filter(row => row.value !== null).length === 1">Un porcentaje guardado; no se infiere una tendencia.</p>
    <p class="rm-clinical-form__note">Últimos registros · valores descriptivos, sin interpretación clínica automática.</p>
    <template x-if="rows().length">
        <div class="rm-dolor__chart">
            <svg viewBox="0 0 380 230" role="group" aria-label="Porcentaje consumido, escala fija de 0 a 100">
                @foreach([0, 25, 50, 75, 100] as $tick)
                    <line class="rm-dolor__chart-grid" x1="44" x2="344" y1="{{ 170 - $tick * 1.4 }}" y2="{{ 170 - $tick * 1.4 }}" />
                    <text class="rm-dolor__chart-axis" x="34" y="{{ 174 - $tick * 1.4 }}" text-anchor="end">{{ $tick }}</text>
                @endforeach
                <text class="rm-dolor__chart-axis" x="44" y="16">Consumido · %</text>
                <polyline class="rm-dolor__chart-line" fill="none" :points="historyLine()" />
                <polyline class="rm-dolor__chart-line rm-dolor__chart-line--preview" fill="none" :points="previewLine()" />
                @for($index = 0; $index < 11; $index++)
                    <g x-data="{ row: null }" x-effect="row = markers()[{{ $index }}] ?? null" x-show="row" x-cloak>
                        <circle class="rm-dolor__chart-hit" :cx="row?.x ?? 0" :cy="row?.y ?? 0" r="22" role="button" tabindex="0" :aria-label="row?.label ?? ''" @mouseenter="selectedPoint = row" @focus="selectedPoint = row" @click="selectedPoint = row" @keydown.enter.prevent="selectedPoint = row" @keydown.space.prevent="selectedPoint = row"><title x-text="row?.label ?? ''"></title></circle>
                        <circle class="rm-dolor__chart-dot" :class="{ 'is-preview': row?.preview }" :cx="row?.x ?? 0" :cy="row?.y ?? 0" :r="row?.preview ? 7 : 5" aria-hidden="true" />
                    </g>
                @endfor
                <text class="rm-dolor__chart-axis" x="44" y="205" x-text="rows()[0]?.date ?? ''" />
                <text class="rm-dolor__chart-axis" x="344" y="222" text-anchor="end" x-text="rows().at(-1)?.date ?? ''" />
            </svg>
            <p class="rm-dolor__tooltip" x-show="selectedMarker()" x-cloak aria-live="polite" x-text="selectedMarker()?.label ?? ''"></p>
            <div class="rm-dolor__legend"><span>● Guardado</span><span x-show="!persisted">◯ Sin guardar · conexión discontinua</span></div>
        </div>
    </template>
    <p class="rm-dolor__empty" x-show="!rows().length">Sin porcentajes disponibles. Los registros sin porcentaje permanecen en el historial.</p>
    <div class="rm-dolor__history rm-ingesta__history">
        <h6>Últimas ingestas registradas</h6>
        <p x-show="!history.length">Sin registros de ingesta anteriores.</p>
        <table x-show="history.length" class="rm-ingesta__history-table">
            <caption class="sr-only">Últimas ingestas registradas del residente</caption>
            <thead><tr><th scope="col">Fecha y hora</th><th scope="col">Comida</th><th scope="col">Porcentaje</th><th scope="col">Tolerancia</th><th scope="col">Deglución</th></tr></thead>
            <tbody><template x-for="(row, index) in [...history].reverse()" :key="row.code || index">
                <tr><td data-label="Fecha y hora" x-text="row.date"></td><td data-label="Comida" x-text="row.meal"></td><td data-label="Porcentaje" x-text="row.value === null ? 'Sin registrar' : row.value + ' %'"></td><td data-label="Tolerancia" x-text="row.tolerance"></td><td data-label="Deglución" x-text="row.swallowing"></td></tr>
            </template></tbody>
        </table>
    </div>
</x-ui.clinical-trend-window>
