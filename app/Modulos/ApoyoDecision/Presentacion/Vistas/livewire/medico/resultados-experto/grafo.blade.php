<section class="rm-orion-graph" wire:key="grafo-{{ $tipo }}-{{ $criterio }}-{{ $datos['evaluacion']['id'] ?? 'documental' }}"
    x-data="rmOrionGraph(@js($grafo))" aria-label="{{ $titulo }}">
    <h3 class="rm-expert-section-title">{{ $titulo }}</h3>
    <p class="rm-expert-prose text-sm">{{ $grafo['aviso'] }} Versión: {{ $grafo['version'] ?? 'No disponible' }}.</p>
    @if($grafo['nodos'])
        <p class="rm-orion-note"><strong>Tipos de nodo:</strong> {{ implode(' · ', array_values(array_unique(array_column($grafo['nodos'], 'tipo')))) }}. El texto de cada nodo indica su tipo y estado; las flechas conservan la dirección de la relación.</p>
        <div class="rm-orion-toolbar" role="group" aria-label="Controles del grafo">
            <button type="button" class="rm-btn rm-btn-secondary" @click="zoom(-.15)" aria-label="Reducir grafo">−</button>
            <button type="button" class="rm-btn rm-btn-secondary" @click="zoom(.15)" aria-label="Ampliar grafo">+</button>
            <button type="button" class="rm-btn rm-btn-secondary" @click="centrar()"><i class="ph ph-crosshair" aria-hidden="true"></i>Centrar grafo</button>
            <button type="button" class="rm-btn rm-btn-secondary" @click="restablecer()">Restablecer vista</button>
            <span class="text-sm">Arrastra el fondo para desplazar. Selecciona un nodo para seguir sus conexiones.</span>
        </div>
        <div class="rm-orion-graph-workspace">
        <div class="rm-orion-graph-canvas" x-ref="area">
            <svg :view-box.camel="`0 0 ${viewportAncho} ${viewportAlto}`" role="group" aria-label="Nodos y conexiones. También disponibles en la lista inferior."
                @pointerdown="iniciar($event)" @pointermove="mover($event)" @pointerup="terminar()" @pointercancel="terminar()">
                <defs><marker id="flecha-{{ $tipo }}" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0 0 L8 4 L0 8" class="rm-orion-arrow"/></marker></defs>
                <g :transform="`translate(${dx} ${dy}) scale(${escala})`">
                    @foreach($grafo['aristas'] as $a)<path :d="ruta(@js($a))" class="rm-orion-edge" :class="{ 'is-connected': vinculada(@js($a)) }" marker-end="url(#flecha-{{ $tipo }})"><title>{{ $a['relacion'] }}</title></path>@endforeach
                    @foreach($grafo['nodos'] as $n)
                        <g data-orion-node role="button" tabindex="0" aria-label="{{ $n['tipo'].': '.$n['nombre'].'. '.$n['estado'] }}" :aria-pressed="seleccionado === @js($n['id'])"
                            :transform="`translate(${punto(@js($n['id'])).x} ${punto(@js($n['id'])).y})`" @focus="enfocar(@js($n['id']))" @click="seleccionar(@js($n['id']))" @keydown.enter.prevent="seleccionar(@js($n['id']))" @keydown.space.prevent="seleccionar(@js($n['id']))"
                            class="rm-orion-node" :class="{ 'is-selected': seleccionado === @js($n['id']) }" data-type="{{ $n['tipo'] }}" data-state="{{ $n['estado'] }}">
                            <rect width="190" height="86" rx="10"/>
                            <text x="12" y="20" class="rm-orion-node-type">{{ $n['tipo'] }}</text>
                            <text x="12" y="43">{{ \Illuminate\Support\Str::limit($n['nombre'], 25) }}</text>
                            <text x="12" y="67" class="rm-orion-node-state">{{ \Illuminate\Support\Str::limit($n['estado'], 28) }}</text>
                            <title>{{ $n['nombre'] }}</title>
                        </g>
                    @endforeach
                </g>
            </svg>
        </div>
        <aside class="rm-orion-graph-detail" aria-label="Detalle del nodo seleccionado" aria-live="polite">
            <div x-show="!actual"><h4>Explora el razonamiento</h4><p class="rm-orion-note">Selecciona un nodo para consultar su tipo, estado, versión y datos conservados. Las conexiones relacionadas quedarán resaltadas.</p></div>
            <template x-if="actual"><div><h4 class="font-semibold" x-text="actual.nombre"></h4><p x-text="`${actual.tipo} · ${actual.estado} · ${actual.version}`"></p>
                <dl class="rm-orion-definition"><template x-for="(valor, nombre) in actual.datos" :key="nombre"><div><dt x-text="nombre"></dt><dd x-text="valor"></dd></div></template></dl>
                <template x-if="reglaActual"><div class="rm-orion-rule-construction">
                    <h5 class="font-semibold">Condiciones exigidas</h5>
                    <template x-for="condicion in reglaActual.filas" :key="condicion.id"><article class="rm-orion-note"><strong x-text="condicion.exigida"></strong><p x-text="condicion.estado"></p>
                        <template x-for="dato in condicion.datos" :key="dato.id"><p><span>Evidencia conservada: </span><span x-text="dato.nombre"></span></p></template>
                    </article></template>
                    <h5 class="font-semibold">Consecuencia declarada</h5><ul><template x-for="(consecuencia, indice) in reglaActual.consecuencias" :key="indice"><li x-text="consecuencia"></li></template></ul>
                </div></template>
                <button type="button" class="rm-btn rm-btn-secondary" @click="$wire.abrirNodoGrafo(@js($tipo), seleccionado)">Abrir detalle del nodo</button></div></template>
        </aside>
        </div>
        <details class="rm-expert-source mt-3"><summary>Lista accesible de nodos y relaciones ({{ count($grafo['nodos']) }})</summary>
            <ul class="rm-orion-node-list">@foreach($grafo['nodos'] as $n)<li>
                <button type="button" class="rm-btn rm-btn-secondary" @click="seleccionar(@js($n['id']))">{{ $n['tipo'] }} · {{ $n['nombre'] }}</button>
                <p>{{ $n['estado'] }} · Versión {{ $n['version'] }}</p>
                <ul>@foreach($grafo['aristas'] as $a)@if($a['origen'] === $n['id'] || $a['destino'] === $n['id'])
                    <li>{{ $a['origen'] === $n['id'] ? 'Salida' : 'Entrada' }} · {{ $a['relacion'] }} → {{ collect($grafo['nodos'])->firstWhere('id', $a['origen'] === $n['id'] ? $a['destino'] : $a['origen'])['nombre'] ?? 'No disponible' }}</li>
                @endif @endforeach</ul>
            </li>@endforeach</ul>
        </details>
    @else
        <p class="rm-orion-empty">No hay nodos verificables disponibles para esta selección.</p>
    @endif
</section>
