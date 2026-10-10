<x-ui.clinical-trend-window id="eliminacion-historial-popup" title="Historial de eliminación" window-label="historial" :resident="$detalleResidente['nombre_completo']">
    <p class="rm-help">Últimos 40 eventos vigentes · Se conserva la captura al cerrar.</p>
    <div class="rm-eliminacion__history-tabs" role="group" aria-label="Filtrar historial por tipo">
        @foreach(['TODOS' => 'Todos', 'URINARIA' => 'Urinaria', 'INTESTINAL' => 'Intestinal'] as $value => $label)
            <button type="button" data-type="{{ $value }}" :aria-pressed="historyFilter === @js($value)" @click="historyFilter = @js($value)"><i class="ph-bold {{ $value === 'URINARIA' ? 'ph-drop' : ($value === 'INTESTINAL' ? 'ph-wave-sine' : 'ph-list-bullets') }}" aria-hidden="true"></i>{{ $label }}</button>
        @endforeach
    </div>
    <p class="rm-help" x-show="!filteredHistory().length">Sin registros para este filtro.</p>
    <div class="rm-eliminacion__timeline">
        <template x-for="group in groups()" :key="group.label">
            <section><h6 x-text="group.label"></h6><ol>
                <template x-for="row in group.rows" :key="row.codigo"><li :data-type="row.tipo">
                    <div class="rm-eliminacion__event-heading"><strong class="rm-eliminacion__type-badge" :data-type="row.tipo"><i class="ph-bold" :class="row.tipo === 'URINARIA' ? 'ph-drop' : 'ph-wave-sine'" aria-hidden="true"></i><span x-text="row.tipo_label"></span></strong><time x-text="row.hora"></time></div>
                    <span class="rm-eliminacion__legacy" x-show="row.legacy">Dato de registro anterior</span>
                    <dl><template x-for="field in row.campos" :key="field.nombre"><div><dt x-text="field.nombre"></dt><dd x-text="field.valor"></dd></div></template></dl>
                    <p x-show="row.observacion" x-text="row.observacion"></p>
                </li></template>
            </ol></section>
        </template>
    </div>
</x-ui.clinical-trend-window>
