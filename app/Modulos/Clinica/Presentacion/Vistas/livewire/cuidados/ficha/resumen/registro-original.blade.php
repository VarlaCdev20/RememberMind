<details class="py-2" id="{{ $idOriginal ?? $evidencia['ancla'] }}" wire:key="original-{{ $contexto }}-{{ $evidencia['codigo'] }}">
    <summary class="min-h-11 cursor-pointer py-2 text-sm leading-relaxed focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--rm-focus-ring-color)]">
        <span class="font-semibold">{{ $evidencia['origen'] }} · {{ $evidencia['fecha'] }}</span>
        <span class="block pl-4 text-[var(--rm-text-primary)]">Consultar registro original · {{ $evidencia['codigo'] }} · {{ $evidencia['estado'] }}</span>
    </summary>
    <dl class="space-y-3 py-3 text-sm leading-relaxed">
        <div><dt class="font-semibold">Responsable</dt><dd>{{ $evidencia['responsable'] }} · {{ $evidencia['codigo_responsable'] ?? 'Código no registrado' }}</dd></div>
        @foreach($evidencia['campos'] as $etiqueta => $valor)
            <div><dt class="font-semibold">{{ $etiqueta }}</dt><dd class="whitespace-pre-line [overflow-wrap:anywhere]">{{ $valor === null || $valor === '' ? 'No registrado' : $valor }}</dd></div>
        @endforeach
    </dl>
    @isset($evidencia['clasificacion'])
        <div class="space-y-3 border-t border-[var(--rm-border)] py-3 text-sm">
            <p><span class="inline-flex rounded-lg border px-2 py-1 font-semibold {{ $evidencia['clasificacion']['estado_badge'] }}">{{ $evidencia['clasificacion']['label'] }}</span></p>
            @forelse($evidencia['clasificacion']['resultados'] as $resultado)
                <div class="space-y-1 leading-relaxed">
                    <p class="font-semibold">{{ $resultado['valor'] }} {{ $resultado['unidad'] }} · {{ $resultado['severidad'] ?? 'Sin clasificación automática' }}</p>
                    <p>{{ $resultado['explicacion'] }}</p>
                    <p>{{ $resultado['referencia_utilizada'] ?? 'Sin referencia aplicable' }} · {{ $resultado['rango_o_umbral'] ?? 'Sin rango aplicable' }}</p>
                    <p>Regla de origen: {{ $resultado['codigo_regla'] }}</p>
                </div>
            @empty
                <p>No hay mediciones con una regla de interpretación aplicable en este registro.</p>
            @endforelse
        </div>
    @endisset
</details>
