<x-ui.section-card title="Casos sintéticos" subtitle="Entradas del catálogo real de escenarios. Los estados se muestran solo después de ejecutar.">
    <div class="rm-lab-filters">
        <div><label for="lab-buscar-casos">Buscar casos</label><input id="lab-buscar-casos" type="search" wire:model.live.debounce.300ms="buscarCasos" class="rm-input w-full" maxlength="120"></div>
        <div><label for="lab-tipo">Tipo de escenario</label><select id="lab-tipo" wire:model.live="tipoCaso" class="rm-input w-full"><option value="">Todos</option><option value="petersen">Petersen</option><option value="insuficiente">Insuficiente</option><option value="contradictorio">Contradictorio</option><option value="negativo">Negativo</option><option value="no interpretable">No interpretable</option></select></div>
        <div><label for="lab-orden">Orden por descripción</label><select id="lab-orden" wire:model.live="ordenCasos" class="rm-input w-full"><option value="asc">A–Z</option><option value="desc">Z–A</option></select></div>
    </div>
    <table class="rm-lab-table mt-4">
        <caption class="sr-only">Casos ficticios disponibles para ejecutar el motor experimental</caption>
        <thead><tr><th scope="col">Caso</th><th scope="col">Corte y versión</th><th scope="col">Tipo</th><th scope="col">Última ejecución en sesión</th><th scope="col">Acciones</th></tr></thead>
        <tbody>
            @forelse($casos as $c)
                <tr wire:key="caso-{{ $c['id'] }}">
                    <td data-label="Caso"><strong>{{ $c['nombre'] }}</strong><p class="text-sm text-rm-secondary">{{ $c['id'] }}</p></td>
                    <td data-label="Corte y versión">{{ $c['corte'] }}<p class="text-sm">{{ $c['version'] }}</p></td>
                    <td data-label="Tipo">{{ ucfirst($c['tipo']) }}</td>
                    <td data-label="Última ejecución en sesión">{{ $c['ultima']['fecha'] ?? 'Sin ejecutar en esta sesión' }}</td>
                    <td data-label="Acciones"><button type="button" class="rm-btn rm-btn-secondary" wire:click="elegirCaso('{{ $c['id'] }}')" aria-pressed="{{ $escenarioSeleccionado === $c['id'] ? 'true' : 'false' }}">{{ $escenarioSeleccionado === $c['id'] ? 'Seleccionado' : 'Seleccionar' }}</button></td>
                </tr>
            @empty
                <tr><td colspan="5"><x-ui.empty-state compact title="Sin coincidencias" description="Cambie la búsqueda o el filtro de escenario." /></td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $casos->links() }}
    <div class="mt-5 border-t border-rm pt-4">
        <h3 class="font-semibold mb-3">Detalle y ejecución del caso seleccionado</h3>
        <p class="mb-4">{{ $escenariosPerfil[$escenarioSeleccionado] ?? 'Seleccione un caso disponible' }}. Su entrada procede del catálogo de escenarios; la selección no supone que se haya evaluado.</p>
        @include('livewire.superadministrador.sistema-experto.ejecutar')
    </div>
</x-ui.section-card>
