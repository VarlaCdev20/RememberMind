<x-ui.clinical-trend-window id="dolor-grafica-popup" title="Evolución del dolor" :resident="$detalleResidente['nombre_completo']">
    @if($dolorCodOrigen)<p class="rm-clinical-form__note">Episodio iniciado · {{ $dolorContextoOrigen['fecha'] }}. Se muestran los últimos registros de este episodio.</p>@endif
    <p class="rm-clinical-form__note" x-show="!history.length">Sin valoraciones anteriores. La intensidad actual es una vista previa sin guardar.</p>
    <p class="rm-clinical-form__note" x-show="history.length === 1">Una valoración guardada; no se infiere una tendencia.</p>
    <template x-if="rows().length">
        <div class="rm-dolor__chart">
            <svg viewBox="0 0 380 230" role="group" aria-label="Evolución de la intensidad EVA, escala fija de 0 a 10">
                @foreach([0, 2, 4, 6, 8, 10] as $tick)
                    <line class="rm-dolor__chart-grid" x1="44" x2="344" y1="{{ 170 - $tick * 14 }}" y2="{{ 170 - $tick * 14 }}" />
                    <text class="rm-dolor__chart-axis" x="34" y="{{ 174 - $tick * 14 }}" text-anchor="end">{{ $tick }}</text>
                @endforeach
                <text class="rm-dolor__chart-axis" x="44" y="16">EVA / 10</text>
                <polyline class="rm-dolor__chart-line" fill="none" :points="historyLine()" />
                <polyline class="rm-dolor__chart-line rm-dolor__chart-line--preview" fill="none" :points="previewLine()" />
                @for($index = 0; $index < 7; $index++)
                    <g x-data="{ row: null }" x-effect="row = markers()[{{ $index }}] ?? null" x-show="row" x-cloak>
                        <circle class="rm-dolor__chart-hit" :cx="row?.x ?? 0" :cy="row?.y ?? 0" r="22" role="button" tabindex="0" :aria-label="row?.label ?? ''" @mouseenter="selectedPoint = row" @focus="selectedPoint = row" @click="selectedPoint = row" @keydown.enter.prevent="selectedPoint = row" @keydown.space.prevent="selectedPoint = row"><title x-text="row?.label ?? ''"></title></circle>
                        <circle class="rm-dolor__chart-dot" :class="{ 'is-preview': row?.preview }" :cx="row?.x ?? 0" :cy="row?.y ?? 0" :r="row?.preview ? 7 : 5" aria-hidden="true" />
                    </g>
                @endfor
                <text class="rm-dolor__chart-axis" x="44" y="205" x-text="rows()[0]?.date ?? ''" />
                <text class="rm-dolor__chart-axis" x="344" y="222" text-anchor="end" x-text="rows().at(-1)?.date ?? ''" />
            </svg>
            <p class="rm-dolor__tooltip" x-show="selectedMarker()" x-cloak aria-live="polite" x-text="selectedMarker()?.label ?? ''"></p>
            <div class="rm-dolor__legend"><span>● Guardada</span><span x-show="!persisted">◯ Sin guardar · conexión discontinua</span></div>
        </div>
    </template>
    <p class="rm-dolor__empty" x-show="!rows().length">Selecciona una intensidad para ver la lectura actual.</p>
    <div class="rm-dolor__comparison rm-dolor__comparison--three" aria-live="polite" x-show="!persisted">
        <div><span>Anterior</span><strong x-text="previous() ? previous().value + ' / 10' : '—'"></strong></div>
        <div><span>Actual · Sin guardar</span><strong x-text="current() === null ? '—' : current() + ' / 10'"></strong></div>
        <div><span>Diferencia</span><strong x-text="comparison()"></strong></div>
    </div>
    <details class="rm-dolor__history" open>
        <summary>Últimos registros guardados</summary>
        <p x-show="!history.length">Sin valoraciones anteriores.</p>
        <ol>
            <template x-for="(row, index) in [...(origin ? episode : history)].reverse()" :key="row.code || index">
                <li><time x-text="row.date"></time><strong x-text="'EVA ' + row.value + ' / 10'"></strong>
                    <span x-text="row.origin ? 'Reevaluación' : 'Valoración inicial'"></span>
                    <span x-text="row.location || 'Localización no registrada'"></span>
                    <span x-show="row.frequency" x-text="'Frecuencia: ' + row.frequency"></span>
                    <span x-show="row.relief" x-text="'Alivio: ' + row.relief"></span>
                    <span x-show="row.intervention" x-text="'Intervención: ' + row.intervention"></span>
                    <span x-show="row.response" x-text="'Respuesta: ' + row.response"></span>
                    @if(!$esModoConsulta)
                        @can('valoraciones_dolor.crear')<button class="rm-btn-secondary" type="button" x-show="row.code" @click="$wire.abrirReevaluacionDolor(row.code)" wire:loading.attr="disabled" wire:target="abrirReevaluacionDolor">Reevaluar dolor</button>@endcan
                    @endif
                </li>
            </template>
        </ol>
        @can('enfermeria.ver_ficha_paciente')<a class="rm-clinical-form__history-link" href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $detalleResidente['cod_residente']]) }}" target="_blank" rel="noopener">Consultar ficha clínica <span class="sr-only">(abre otra pestaña)</span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a>@endcan
    </details>
</x-ui.clinical-trend-window>
