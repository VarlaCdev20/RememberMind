<x-ui.section-card icon="ph-tree-structure" title="Fundamento del resultado">
    <details wire:ignore.self @if($abierto ?? false) open @endif>
        <summary class="min-h-11 py-3 font-semibold cursor-pointer">Ver fundamento completo</summary>
        <div class="space-y-4 text-sm mt-3">
            <p>Esta consulta muestra únicamente la traza guardada. No ejecuta reglas ni vuelve a interpretar fuentes.</p>
            @if($detalle['traza'])
                <p class="break-words">Traza {{ $detalle['traza']['id'] }} · Estado registrado: {{ $detalle['traza']['estado'] }} · {{ $detalle['traza']['inicio'] ?? 'Sin fecha' }}</p>
                @foreach($detalle['reglas'] as $regla)
                    <article class="rounded-xl border border-rm p-4 space-y-3 min-w-0">
                        <h3 class="font-semibold break-words">{{ $regla['nombre'] }} · {{ $regla['estado'] }}</h3>
                        <p class="text-xs text-rm-secondary break-all">Regla: {{ $regla['id'] }}</p>
                        @foreach($regla['condiciones'] as $condicion)
                            <div class="rounded-lg bg-rm-surface-soft p-3 space-y-2 break-words">
                                <p>Condición {{ $condicion['id'] }} · {{ $condicion['estado'] }}</p>
                                <p>Variable exigida: {{ $condicion['variable'] }} · Valor exigido: {{ $condicion['valor'] }}</p>
                                @forelse($condicion['soportes'] as $sid)
                                    @php($soporte = $detalle['evidencias'][$sid])
                                    <p>Registro fuente {{ $soporte['tabla'] }} / {{ $soporte['registro'] }} → mapeo de variable {{ $soporte['mapeo'] }} → {{ $soporte['nombre'] }} ({{ $sid }}) → variable {{ $soporte['variable'] }} / valor semántico {{ $soporte['valor'] ?? 'No disponible' }} → condición {{ $condicion['id'] }} → regla {{ $regla['id'] }}.</p>
                                @empty
                                    <p>No se conserva evidencia de soporte para esta condición.</p>
                                @endforelse
                            </div>
                        @endforeach
                        <p>Consecuencias declaradas para este criterio: {{ implode(', ', $regla['consecuencias']) ?: 'Ninguna registrada' }}.</p>
                    </article>
                @endforeach
            @else
                <p>No hay una traza guardada para esta área.</p>
            @endif
            <dl class="rounded-xl bg-rm-surface-soft p-4 space-y-3">
                <div><dt class="font-semibold">Valor original usado durante la inferencia</dt><dd>No preservado en la traza.</dd></div>
                <div><dt class="font-semibold">Mapeo exacto del valor original</dt><dd>No preservado en la traza.</dd></div>
                <div><dt class="font-semibold">Fecha original y corte de las fuentes utilizadas</dt><dd>No preservados en la traza.</dd></div>
                <div><dt class="font-semibold">Comprobaciones originales del componente, condiciones de aplicación y factores que limitan la interpretación</dt><dd>No preservadas en la traza. No se reconstruyen a partir de respuestas actuales.</dd></div>
            </dl>
            <p class="text-rm-secondary">La consulta del dato fuente actual no completa ni sustituye estos datos históricos faltantes.</p>
        </div>
    </details>
</x-ui.section-card>
