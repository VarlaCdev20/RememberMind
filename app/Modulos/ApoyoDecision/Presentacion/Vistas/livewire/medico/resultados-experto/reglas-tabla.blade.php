<div class="rm-orion-rules" aria-label="Reglas y evidencias de la evaluación histórica">
    @forelse($exploracion['inferencia']['reglas'] as $regla)
        <details class="rm-orion-accordion"><summary><strong>{{ $regla['nombre'] }}</strong><span>{{ $regla['estado'] }}</span></summary>
            <div class="rm-orion-accordion-body">
                <p>SI todas las condiciones exigidas están respaldadas, ENTONCES esta regla aporta la consecuencia declarada.</p>
                <div class="rm-orion-table-wrap"><table class="rm-orion-table"><caption>Construcción completa de {{ $regla['nombre'] }}</caption>
                    <thead><tr><th scope="col">Condición exigida</th><th scope="col">Dato utilizado, fuente y fecha</th><th scope="col">Cumplimiento</th><th scope="col">Consecuencia declarada</th></tr></thead>
                    <tbody>@foreach($regla['filas'] as $fila)<tr><td><button type="button" class="rm-orion-text-button" wire:click="abrirNodoGrafo('inferencia', @js($fila['id']))">{{ $fila['exigida'] }}</button></td>
                        <td>@forelse($fila['datos'] as $dato)<p>{{ $dato['nombre'] }} · {{ $dato['valor'] ?? 'No registrado' }} · {{ $dato['fuente'] }}.</p><p>Incorporación: {{ $dato['fecha'] ?? 'No conservada' }}. Valor y fecha originales: no conservados en esta evaluación.</p>@empty Sin evidencia de soporte consultable. @endforelse</td>
                        <td>{{ $fila['estado'] }}</td><td>{{ implode(', ', $regla['consecuencias']) ?: 'Ninguna registrada' }}. {{ $regla['estado'] === 'CUMPLE' ? 'Aportada según traza; sujeta a integridad y publicación.' : 'No aportada al resultado.' }}</td></tr>@endforeach</tbody>
                </table></div>
            </div>
        </details>
    @empty<p class="rm-orion-empty">No hay reglas evaluadas conservadas para esta área. Una regla del catálogo no acredita ejecución.</p>@endforelse
</div>
