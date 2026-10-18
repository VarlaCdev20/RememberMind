<div class="space-y-3 mt-4">
    @php($fuentesArea = array_filter($narrativa['fuentes'], fn ($e) => $e['area'] === $criterio))
    @forelse($fuentesArea as $e)
        <article class="rm-expert-source" wire:key="evidencia-{{ $e['id'] }}">
            <h3 class="font-semibold">{{ $e['nombre'] }}</h3>
            <p class="rm-expert-prose mt-2">{{ $e['uso'] }}</p>
            <p class="text-sm text-rm-secondary mt-2">{{ $e['fuente'] }} · Incorporada a la evaluación: {{ $e['fecha'] ?? 'Fecha no disponible' }}</p>
            <button type="button" wire:click="verEvidencia(@js($e['id']))" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary mt-3" aria-expanded="{{ $evidencia === $e['id'] ? 'true' : 'false' }}">{{ $evidencia === $e['id'] ? 'Cerrar fuente' : 'Consultar fuente original' }}</button>
            @if($evidencia === $e['id'] && $fuente)
                <div class="mt-3" role="status"><p>{{ $fuente['estado'] }}</p>
                    @if(isset($fuente['fecha']))<p class="mt-2">Fecha del registro actual: {{ $fuente['fecha'] }}</p>@endif
                    @if(isset($fuente['valor']))<p class="mt-2">Dato registrado: {{ $fuente['valor'] }}</p>@endif
                    @if(isset($fuente['instrumento']))<p class="mt-2">Instrumento: {{ $fuente['instrumento'] }}</p>@endif
                </div>
            @endif
        </article>
    @empty<p class="text-sm text-rm-secondary">No hay evidencias conservadas para esta área.</p>@endforelse
</div>
