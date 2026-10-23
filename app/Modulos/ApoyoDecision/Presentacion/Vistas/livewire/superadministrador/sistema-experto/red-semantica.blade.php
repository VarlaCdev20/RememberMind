<x-ui.section-card title="Explorador semántico" subtitle="Conocimiento declarado por versión, independiente de la traza de un residente." icon="ph-graph">
    <div class="rm-knowledge-filters">
        <div><label for="expert-network-search">Buscar código o nombre</label><input id="expert-network-search" wire:model.live.debounce.300ms="buscarRed" type="search" maxlength="120" class="rm-input w-full"></div>
        <div><label for="lab-dominio-red">Criterio y entorno próximo</label><select id="lab-dominio-red" wire:model.live="dominioRed" class="rm-input w-full"><option value="">Todos</option>@foreach($red['criterios'] as $n)<option value="{{ $n['codigo_semantico'] }}">{{ $n['nombre'] }}</option>@endforeach</select></div>
        <div><label for="expert-node-type">Tipo de nodo</label><select id="expert-node-type" wire:model.live="tipoRed" class="rm-input w-full"><option value="">Todos</option>@foreach($red['tipos'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
        <div><label for="expert-edge-type">Relación</label><select id="expert-edge-type" wire:model.live="relacionRed" class="rm-input w-full"><option value="">Todas</option>@foreach($red['predicados'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
        <div><label for="expert-node-state">Estado declarado</label><select id="expert-node-state" wire:model.live="estadoRed" class="rm-input w-full"><option value="">Todos</option>@foreach($red['estados'] as $t)<option>{{ $t }}</option>@endforeach</select></div>
        <button type="button" class="rm-btn rm-btn-secondary" wire:click="limpiarRed">Limpiar filtros</button>
    </div>
    <p class="rm-orion-note mt-3">{{ $red['aviso'] }} Se muestran {{ count($red['nodos']) }} de {{ $red['total'] }} nodos coincidentes. Las flechas unen nodos de esta página; el inspector conserva todas sus relaciones.</p>
    <div wire:key="knowledge-network-{{ md5(json_encode([$version, $buscarRed, $tipoRed, $relacionRed, $estadoRed, $dominioRed, $red['pagina']])) }}" x-data="rmExpertKnowledgeGraph(@js($red), @js($nodo))">
        @if($red['nodos'])
            <div class="rm-lab-actions mt-3 mb-3" role="group" aria-label="Controles del grafo de conocimiento">
                <button type="button" class="rm-btn rm-btn-secondary" @click="zoom(-.15)" aria-label="Reducir grafo">−</button><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(.15)" aria-label="Ampliar grafo">+</button><button type="button" class="rm-btn rm-btn-secondary" @click="centrar()">Ajustar grafo</button><button type="button" class="rm-btn rm-btn-secondary" @click="restablecer()">Restablecer vista</button><span role="status" x-text="Math.round(escala * 100) + '%'">100%</span>
            </div>
            <div class="rm-knowledge-distribution" role="group" aria-label="Distribución y enfoque del grafo">
                <button type="button" class="rm-btn rm-btn-secondary" @click="distribuir('compacta')" :aria-pressed="distribucion === 'compacta'"><i class="ph ph-squares-four" aria-hidden="true"></i>Distribuir en cuadrícula</button>
                <button type="button" class="rm-btn rm-btn-secondary" @click="distribuir('niveles')" :aria-pressed="distribucion === 'niveles'"><i class="ph ph-tree-structure" aria-hidden="true"></i>Organizar por niveles</button>
                <button type="button" class="rm-btn rm-btn-secondary" @click="enfocarConexiones()" :disabled="!seleccionado" :aria-pressed="focoVecindario"><i class="ph ph-crosshair" aria-hidden="true"></i>Enfocar conexiones</button>
                <span role="status" x-text="cantidadVisible + ' de ' + nodos.length + ' nodos de esta página'"></span>
            </div>
            <p class="text-sm text-rm-secondary mb-3">Arrastre el fondo para desplazar o un nodo para reorganizar. Tab y Enter seleccionan. En grafos grandes, seleccione un nodo y enfoque sus conexiones. La lista accesible conserva el contenido completo.</p>
            <div x-show="actual" x-cloak class="rm-knowledge-selection mb-3" role="status"><i class="ph ph-cursor-click" aria-hidden="true"></i><strong x-text="actual?.nombre"></strong><span x-text="numeroConexiones + ' conexiones de esta página'"></span><button type="button" class="rm-btn rm-btn-secondary" @click="limpiarSeleccion()">Ver toda la página</button></div>
            <div class="rm-knowledge-graph-layout">
                <div class="rm-orion-graph-canvas rm-knowledge-canvas" x-ref="area">
                    <svg :view-box.camel="`0 0 ${viewportAncho} ${viewportAlto}`" role="group" aria-label="Conceptos y relaciones declaradas" @pointerdown="iniciar($event)" @pointermove="mover($event)" @pointerup="terminar()" @pointercancel="terminar()">
                        <defs><marker id="expert-knowledge-arrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0 0 L8 4 L0 8" class="rm-orion-arrow"/></marker></defs>
                        <g :transform="`translate(${dx} ${dy}) scale(${escala})`">
                            @foreach($red['aristas'] as $a)<g x-show="aristaVisible(@js($a))"><path :d="ruta(@js($a))" class="rm-orion-edge" :class="{ 'is-connected': vinculada(@js($a)) }" marker-end="url(#expert-knowledge-arrow)"><title>{{ $a['relacion'] }}</title></path><text class="rm-knowledge-edge-label" x-show="aristas.length <= 12 || vinculada(@js($a))" :x="etiqueta(@js($a)).x" :y="etiqueta(@js($a)).y" :class="{ 'is-connected': vinculada(@js($a)) }">{{ str_replace('_', ' ', $a['relacion']) }}</text></g>@endforeach
                            @foreach($red['nodos'] as $n)
                                <g data-expert-node x-show="esVisible(@js($n['id']))" role="button" tabindex="0" class="rm-orion-node" data-type="{{ $n['tipo'] }}" :class="{ 'is-selected': seleccionado === @js($n['id']), 'is-muted': !esVecino(@js($n['id'])) }" aria-label="{{ $n['tipo'].': '.$n['nombre'].'. '.$n['estado'] }}" :aria-pressed="seleccionado === @js($n['id'])" :transform="`translate(${punto(@js($n['id'])).x} ${punto(@js($n['id'])).y})`" @focus="enfocar(@js($n['id']))" @pointerdown.stop="iniciarNodo($event, @js($n['id']))" @click="seleccionar(@js($n['id'])); $wire.abrirNodo(@js($n['id']))" @keydown.enter.prevent="seleccionar(@js($n['id'])); $wire.abrirNodo(@js($n['id']))" @keydown.space.prevent="seleccionar(@js($n['id'])); $wire.abrirNodo(@js($n['id']))">
                                    <rect width="190" height="86" rx="10"/><text x="12" y="20" class="rm-orion-node-type">{{ $n['tipo'] }}</text><text x="12" y="43">{{ \Illuminate\Support\Str::limit($n['nombre'], 24) }}</text><text x="12" y="67" class="rm-orion-node-state">{{ $n['estado'] }}</text><title>{{ $n['nombre'] }}</title>
                                </g>
                            @endforeach
                        </g>
                    </svg>
                </div>
                <aside class="rm-knowledge-inspector" aria-label="Detalle del nodo" aria-live="polite">
                    @if($nodo && isset($conocimiento['nodos'][$nodo]))
                        @php($actual = $conocimiento['nodos'][$nodo])
                        <p class="rm-expert-eyebrow">Nodo seleccionado</p><h3 class="rm-expert-section-title">{{ $actual['nombre'] }}</h3><p>{{ $actual['tipo_nodo'] }} · {{ $actual['estado'] }}</p>
<<<<<<< HEAD
=======
                        <button type="button" data-expert-detail wire:click="abrirDato('nodos_semanticos', @js($nodo))" class="rm-btn rm-btn-primary mt-3"><i class="ph ph-arrows-out" aria-hidden="true"></i>Ver ficha y todas sus conexiones</button>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                        <dl class="rm-lab-data"><div><dt>Código</dt><dd>{{ $actual['codigo_semantico'] }}</dd></div><div><dt>Descripción</dt><dd>{{ $actual['definicion'] ?? 'No declarada' }}</dd></div><div><dt>Versión</dt><dd>{{ $actual['cod_version_modelo'] }}</dd></div><div><dt>Fuentes declaradas</dt><dd>@forelse($fuentesNodo as $fuenteNodo)<p>{{ $fuenteNodo['fuente'] }} · {{ $fuenteNodo['codigo'] }}<small class="block">Mapeo: {{ $fuenteNodo['mapeo'] }}</small></p>@empty Sin vínculo directo de fuente declarado. @endforelse</dd></div></dl>
                        <h4 class="font-semibold mt-3">Relaciones de entrada y salida</h4><ul class="rm-knowledge-neighbors">
                            @forelse(array_filter($conocimiento['relaciones'], fn ($r) => $r['cod_nodo_origen'] === $nodo || $r['cod_nodo_destino'] === $nodo) as $r)
                                @php($otro = $r['cod_nodo_origen'] === $nodo ? $r['cod_nodo_destino'] : $r['cod_nodo_origen'])
                                <li><p>{{ $r['cod_nodo_origen'] === $nodo ? 'Salida' : 'Entrada' }} · {{ str_replace('_', ' ', $r['tipo_relacion']) }} · {{ $r['estado'] }}</p><button type="button" class="rm-btn rm-btn-secondary" wire:click="abrirNodo(@js($otro))">Explorar nodo relacionado<span class="sr-only"> {{ $conocimiento['nodos'][$otro]['nombre'] }}</span></button><p>{{ $conocimiento['nodos'][$otro]['nombre'] }}</p></li>
                            @empty<li>Sin relaciones declaradas en esta versión.</li>@endforelse
                        </ul>
                    @else
                        <i class="ph ph-cursor-click rm-knowledge-inspector__icon" aria-hidden="true"></i><h3 class="rm-expert-section-title">Explore una conexión</h3><p>Seleccione un nodo con clic o teclado. Aquí encontrará su definición, versión y relaciones reales.</p>
                    @endif
                </aside>
            </div>
            <details class="rm-expert-source mt-4"><summary>Lista accesible de nodos y relaciones · {{ count($red['nodos']) }} nodos</summary><ul class="rm-knowledge-node-list">
                @foreach($red['nodos'] as $n)<li><button type="button" class="rm-btn rm-btn-secondary" @click="seleccionar(@js($n['id'])); $wire.abrirNodo(@js($n['id']))">{{ $n['nombre'] }}</button><p>{{ $n['datos']['Código'] }} · {{ $n['tipo'] }} · {{ $n['estado'] }} · Versión {{ $n['version'] }}</p><p>{{ $n['datos']['Descripción'] }}</p><ul>@foreach($conocimiento['relaciones'] as $r)@if($r['cod_nodo_origen'] === $n['id'] || $r['cod_nodo_destino'] === $n['id'])<li>{{ $r['cod_nodo_origen'] === $n['id'] ? 'Salida' : 'Entrada' }} · {{ $r['tipo_relacion'] }} → {{ $conocimiento['nodos'][$r['cod_nodo_origen'] === $n['id'] ? $r['cod_nodo_destino'] : $r['cod_nodo_origen']]['nombre'] }}</li>@endif @endforeach</ul></li>@endforeach
            </ul></details>
        @else
            <x-ui.empty-state title="Sin nodos que coincidan" description="Limpie los filtros o seleccione otra versión del conocimiento." icon="ph-graph" />
        @endif
        <nav class="rm-lab-actions mt-4" aria-label="Páginas de la red"><button type="button" wire:click="cambiarPaginaRed({{ $red['pagina'] - 1 }})" @disabled($red['pagina'] <= 1) class="rm-btn rm-btn-secondary">Anterior</button><span>Página {{ $red['pagina'] }} de {{ $red['paginas'] }}</span><button type="button" wire:click="cambiarPaginaRed({{ $red['pagina'] + 1 }})" @disabled($red['pagina'] >= $red['paginas']) class="rm-btn rm-btn-secondary">Siguiente</button></nav>
    </div>
</x-ui.section-card>
