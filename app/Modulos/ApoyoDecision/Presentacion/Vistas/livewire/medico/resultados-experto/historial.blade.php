<x-ui.section-card title="Historial de valoraciones" subtitle="Consultar una evaluación anterior sin modificar sus resultados" icon="ph-clock-counter-clockwise" id="historial-cognitivo">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @forelse($informe['historial'] as $h)
            <button type="button" wire:key="historial-{{ $h['id'] }}" wire:click="seleccionarEvaluacion(@js($h['id']))" wire:loading.attr="disabled" class="rm-expert-criterion text-left min-w-0 {{ $datos['evaluacion']['id'] === $h['id'] ? 'is-selected' : '' }}" aria-pressed="{{ $datos['evaluacion']['id'] === $h['id'] ? 'true' : 'false' }}">
                <span class="block font-semibold">{{ $h['fecha'] ?? 'Fecha no disponible' }}</span>
                @foreach($h['criterios'] as $c)
                    <span class="block text-sm mt-2">{{ $c['nombre'] }}</span>
                @endforeach
                <span class="block text-sm mt-3">Consultar resultados y fundamento conservados</span>
            </button>
        @empty
            <p class="text-sm text-rm-secondary">Todavía no hay valoraciones disponibles para consulta.</p>
        @endforelse
    </div>
    @if($datos['historial']->hasPages())<div class="mt-4">{{ $datos['historial']->links() }}</div>@endif
</x-ui.section-card>
