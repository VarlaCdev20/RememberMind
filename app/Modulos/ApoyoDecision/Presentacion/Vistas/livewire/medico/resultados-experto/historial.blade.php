<x-ui.section-card title="Historial de evaluaciones" subtitle="Se conserva la versión utilizada en cada evaluación. La consulta no recalcula resultados ni compara progresión." icon="ph-clock-counter-clockwise">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @forelse($datos['historial'] as $h)
            <button type="button" wire:key="historial-{{ $h['id'] }}" wire:click="seleccionarEvaluacion(@js($h['id']))" wire:loading.attr="disabled" class="rm-expert-criterion text-left min-w-0 {{ $datos['evaluacion']['id'] === $h['id'] ? 'is-selected' : '' }}" aria-pressed="{{ $datos['evaluacion']['id'] === $h['id'] ? 'true' : 'false' }}">
                <span class="block font-semibold">{{ $h['fecha'] ?? 'Fecha no disponible' }} · {{ $h['version'] ?? 'Versión no disponible' }}</span>
                <span class="block text-sm text-rm-secondary mt-1 break-all">{{ $h['id'] }}</span>
                @foreach($h['criterios'] as $c)
                    <span class="block text-sm font-semibold mt-2">{{ $c['nombre'] }}: {{ $c['estado'] }}</span>
                    <span class="block text-xs text-rm-secondary mt-1">{{ $c['evaluabilidad'] }}</span>
                @endforeach
                <span class="block text-sm mt-3">Consultar resultados y fundamento conservados</span>
            </button>
        @empty
            <p class="text-sm text-rm-secondary">Todavía no hay evaluaciones clínicas disponibles. Las ejecuciones de prueba no se incluyen en este historial.</p>
        @endforelse
    </div>
    @if($datos['historial']->hasPages())<div class="mt-4">{{ $datos['historial']->links() }}</div>@endif
</x-ui.section-card>
