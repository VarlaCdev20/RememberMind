@props(['label' => 'Datos y conexiones', 'nodeAttribute' => 'data-expert-canvas-node', 'selectAction' => 'seleccionar(nodo.id)', 'pathAware' => false])
{{-- x-for debe vivir en HTML: los template dentro de SVG no tienen content. --}}
<div {{ $attributes->class(['rm-expert-graph-canvas']) }} x-ref="area" x-id="['edge-arrow']" role="group" aria-label="{{ $label }}" @pointerdown="iniciar($event)" @pointermove="mover($event)" @pointerup="terminar()" @pointercancel="terminar()">
    <div class="rm-expert-graph-stage" :style="`transform: translate(${dx}px, ${dy}px) scale(${escala})`">
        <template x-for="(arista, indice) in aristas" :key="arista.origen + '>' + arista.destino + ':' + indice">
            <svg class="rm-expert-graph-edge-svg" aria-hidden="true" width="1" height="1" x-show="aristaVisible(arista)">
                <defs><marker :id="$id('edge-arrow', indice)" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0 0 L8 4 L0 8" class="rm-orion-arrow"/></marker></defs>
                <path :d="ruta(arista)" class="rm-orion-edge" :class="{ 'is-connected': vinculada(arista), 'is-path': {{ $pathAware ? 'enRecorrido(arista)' : 'false' }} }" :marker-end="`url(#${$id('edge-arrow', indice)})`" />
                <text class="rm-knowledge-edge-label" x-show="vinculada(arista) && numeroConexiones <= 6" :x="etiqueta(arista).x" :y="etiqueta(arista).y" x-text="arista.relacion.replaceAll('_', ' ').slice(0, 32)"></text>
            </svg>
        </template>
        <template x-for="nodo in nodos" :key="nodo.id">
            <svg class="rm-expert-graph-node-svg" width="190" height="86" :style="`left: ${nodo.x}px; top: ${nodo.y}px; display: ${esVisible(nodo.id) ? 'block' : 'none'}`">
                <g {{ $nodeAttribute }} role="button" tabindex="0" class="rm-orion-node" :data-type="nodo.tipo" :class="{ 'is-selected': seleccionado === nodo.id, 'is-muted': !esVecino(nodo.id) }" :aria-label="nodo.tipo + ': ' + nodo.nombre + '. ' + nodo.estado + (nodo.datos?.Fecha ? '. ' + nodo.datos.Fecha : '')" :aria-pressed="seleccionado === nodo.id" @focus="enfocar(nodo.id)" @pointerdown.stop="iniciarNodo($event, nodo.id)" @click="{{ $selectAction }}" @keydown.enter.prevent="{{ $selectAction }}" @keydown.space.prevent="{{ $selectAction }}">
                    <rect x="2" y="2" width="186" height="82" rx="10"/><text x="12" y="20" class="rm-orion-node-type" x-text="nodo.tipo"></text><text x="12" y="43" x-text="nodo.nombre.length > 24 ? nodo.nombre.slice(0,22) + '…' : nodo.nombre"></text><text x="12" y="67" class="rm-orion-node-state" x-text="nodo.datos?.Fecha ?? (nodo.estado.length > 26 ? nodo.estado.slice(0,24) + '…' : nodo.estado)"></text><title x-text="nodo.nombre + ' · ' + nodo.estado + (nodo.datos?.Fecha ? ' · ' + nodo.datos.Fecha : '')"></title>
                </g>
            </svg>
        </template>
    </div>
</div>
