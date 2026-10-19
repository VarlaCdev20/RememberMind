<x-ui.section-card title="Red semántica" subtitle="Relaciones existentes del paquete seleccionado. Seleccione un nodo para consultar su entorno inmediato.">
    <div class="rm-lab-filters">
        <div><label for="lab-dominio-red">Punto de entrada por dominio</label><select id="lab-dominio-red" wire:model.live="dominioRed" class="rm-input w-full"><option value="">Todos los dominios</option>@foreach($dominios as $id => $nombre)<option value="{{ $id }}">{{ $nombre }}</option>@endforeach</select></div>
    </div>
    <div class="rm-lab-network mt-4" x-data="{ zoom: 1 }" :style="'--lab-zoom: ' + zoom">
        <div class="rm-lab-actions mb-4"><button type="button" class="rm-btn rm-btn-secondary" @click="zoom = Math.max(1, zoom - 0.1)" :disabled="zoom <= 1" aria-label="Reducir tamaño de los nodos">Reducir</button><button type="button" class="rm-btn rm-btn-secondary" @click="zoom = Math.min(1.5, zoom + 0.1)" :disabled="zoom >= 1.5" aria-label="Ampliar tamaño de los nodos">Ampliar</button><span role="status" x-text="Math.round(zoom * 100) + '%'">100%</span></div>
        <div class="rm-lab-network-nodes">
            @forelse(array_filter($conocimiento['nodos'], fn ($n) => $n['tipo_nodo'] === 'CRITERIO' && ($dominioRed === '' || $n['codigo_semantico'] === $dominioRed)) as $id => $n)
                <button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirNodo(@js($id))" aria-pressed="{{ $nodo === $id ? 'true' : 'false' }}">{{ $dominios[$n['codigo_semantico']] ?? $n['nombre'] ?? $n['codigo_semantico'] }}<span class="text-sm">{{ $n['estado'] }}</span></button>
            @empty
                <p>No hay nodos de dominio que coincidan con el filtro.</p>
            @endforelse
        </div>
        @if($nodo && isset($conocimiento['nodos'][$nodo]))
            <div class="rm-lab-selected-node mt-5"><h3 class="font-semibold">Nodo seleccionado: {{ $conocimiento['nodos'][$nodo]['nombre'] ?? $conocimiento['nodos'][$nodo]['codigo_semantico'] }}</h3>
                @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $conocimiento['nodos'][$nodo]])
            </div>
            <ul class="rm-lab-edges mt-4">
                @forelse(array_filter($conocimiento['relaciones'], fn ($r) => $r['cod_nodo_origen'] === $nodo || $r['cod_nodo_destino'] === $nodo) as $r)
                    <li><div><span>{{ $conocimiento['nodos'][$r['cod_nodo_origen']]['codigo_semantico'] ?? $r['cod_nodo_origen'] }}</span><span aria-hidden="true"> → </span><strong>{{ str_replace('_', ' ', $r['tipo_relacion']) }}</strong><span aria-hidden="true"> → </span><span>{{ $conocimiento['nodos'][$r['cod_nodo_destino']]['codigo_semantico'] ?? $r['cod_nodo_destino'] }}</span><p class="text-sm">{{ $r['estado'] }} · {{ $r['significado'] ?? 'Procedencia: paquete seleccionado' }}</p></div>
                        <button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirNodo(@js($r['cod_nodo_origen'] === $nodo ? $r['cod_nodo_destino'] : $r['cod_nodo_origen']))">Explorar nodo relacionado</button>
                    </li>
                @empty
                    <li>Este nodo no tiene relaciones en la versión seleccionada.</li>
                @endforelse
            </ul>
        @else
            <p class="mt-4 text-rm-secondary">Seleccione un dominio. Se mostrarán solo sus relaciones inmediatas para conservar la legibilidad; puede recorrer la red nodo por nodo.</p>
        @endif
    </div>
</x-ui.section-card>
