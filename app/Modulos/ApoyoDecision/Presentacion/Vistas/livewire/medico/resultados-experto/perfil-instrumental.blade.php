<x-ui.section-card title="Perfil de mediciones por dominio" subtitle="Cinco dominios y diecinueve componentes de la red cognitiva" icon="ph-tree-structure">
    <p class="text-sm text-rm-secondary">Las mediciones disponibles se presentan con su método y procedencia. La interpretación clínica de los dominios requiere un contrato específico de integración.</p>
    <div class="space-y-3 mt-4">
        @foreach($perfilInstrumental['dominios'] as $codigo => $dominio)
            <details class="rounded-xl border border-rm bg-rm-surface-soft p-4 min-w-0" wire:key="perfil-instrumental-{{ $codigo }}">
                <summary class="cursor-pointer min-h-11 font-semibold break-words">{{ $dominio['nombre'] }} · {{ count($dominio['componentes']) }} componentes</summary>
                <p class="text-sm text-rm-secondary mt-3">Interpretación del dominio pendiente de método de integración.</p>
                @foreach($dominio['componentes'] as $id => $componente)
                    <div class="border-t border-rm mt-4 pt-3 min-w-0">
                        <h3 class="font-semibold break-words">{{ $componente['nombre'] }}</h3>
                        <p class="text-sm mt-2">{{ $componente['estado_medicion'] === 'MEDICIONES_DISPONIBLES' ? 'Hay mediciones normalizadas disponibles.' : 'Sin medición normalizada vinculada a este componente.' }}</p>
                        @foreach($componente['mediciones'] as $medicion)
                            <p class="text-sm break-words mt-2">Aplicación {{ $medicion['aplicacion'] }} · {{ $medicion['fecha'] }} · Método {{ $medicion['metodo'] }}</p>
                        @endforeach
                        <details class="mt-2"><summary class="cursor-pointer min-h-11 text-sm">Definición y límites del componente</summary>
                            <p class="text-sm break-words">{{ $componente['concepto']['definicion'] }}</p>
                            <p class="text-sm text-rm-secondary break-words mt-2">{{ $componente['concepto']['limites'] }}</p>
                        </details>
                    </div>
                @endforeach
            </details>
        @endforeach
    </div>
</x-ui.section-card>
