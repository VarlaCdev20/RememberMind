<x-ui.clinical-trend-window id="movilidad-historial-popup" title="Historial de movilidad" window-label="historial" :resident="$detalleResidente['nombre_completo']">
    <p class="rm-help">Eventos vigentes del residente · Más recientes primero. La captura no forma parte del historial.</p>
    <div class="rm-movilidad__filters" role="group" aria-label="Filtrar historial de movilidad">
        @foreach(['TODOS' => 'Todos', 'DEAMBULACION' => 'Deambulación', 'TRANSFERENCIAS' => 'Transferencias', 'OTROS' => 'Otros'] as $value => $label)
            <button type="button" :aria-pressed="historyFilter === @js($value)" @click="historyFilter = @js($value)">{{ $label }}</button>
        @endforeach
    </div>
    <p class="rm-help" x-show="!filteredHistory().length">Sin registros para este filtro.</p>
    <template x-for="group in groups()" :key="group.label"><section class="rm-movilidad__history-group"><h6 x-text="group.label"></h6>
        <template x-for="row in group.rows" :key="row.codigo"><details class="rm-movilidad__event"><summary><time x-text="row.hora"></time><strong x-text="row.actividad"></strong><i class="ph-bold ph-caret-down" aria-hidden="true"></i></summary>
            <dl><template x-for="field in row.campos" :key="field.nombre"><div><dt x-text="field.nombre"></dt><dd x-text="field.valor"></dd></div></template></dl>
            <p x-show="row.observacion" x-text="row.observacion"></p>
        </details></template>
    </section></template>
    @if($movHayMasHistorial)<button type="button" class="rm-btn-secondary" wire:click="cargarMasHistorialMovilidad" wire:loading.attr="disabled" wire:target="cargarMasHistorialMovilidad">Cargar más registros</button>@endif
</x-ui.clinical-trend-window>
