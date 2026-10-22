<div class="rm-expert-record" wire:key="dato-{{ $version ?? 'experimental' }}-{{ $fichaDato['tabla'] }}-{{ $fichaDato['id'] }}" x-data="{ pestana: 'relaciones' }">
    <div class="rm-expert-record__identity">
        <span class="rm-expert-record__icon"><i class="ph {{ $fichaDato['icono'] }}" aria-hidden="true"></i></span>
        <div><p class="rm-expert-eyebrow">{{ $fichaDato['categoria'] }}</p><h3>{{ $fichaDato['nombre'] }}</h3><p>{{ $fichaDato['id'] }}</p></div>
    </div>
    <div class="rm-expert-record__origin"><i class="ph ph-stack" aria-hidden="true"></i><span>{{ $version ? 'Versión almacenada: '.$version : 'Paquete experimental · sin publicación clínica' }}</span></div>
    <nav class="rm-expert-record__trail" aria-label="Recorrido de consulta del conocimiento">
        <button type="button" wire:click="volverDato" @disabled(count($recorridoDatos) < 2) class="rm-btn rm-btn-secondary"><i class="ph ph-arrow-left" aria-hidden="true"></i>Volver al dato anterior</button>
        <span>Paso {{ count($recorridoDatos) }} · {{ count($fichaDato['enlaces']) }} referencias documentadas</span>
    </nav>
    <p class="rm-expert-record__description">{{ $fichaDato['campos']['definicion'] ?? $fichaDato['campos']['descripcion'] ?? $fichaDato['campos']['observacion'] ?? 'Explore sus referencias para conocer dónde se utiliza y de qué registros depende.' }}</p>
    <div class="rm-expert-local-tabs" role="group" aria-label="Contenido del registro">
        <button type="button" @click="pestana = 'relaciones'" :aria-pressed="pestana === 'relaciones'">Conexiones <span>{{ count($fichaDato['enlaces']) }}</span></button>
        <button type="button" @click="pestana = 'campos'" :aria-pressed="pestana === 'campos'">Todos los campos <span>{{ count($fichaDato['campos']) }}</span></button>
    </div>
    <section x-show="pestana === 'relaciones'" aria-label="Relaciones del registro">
        @if($fichaDato['enlaces'])
            <p class="rm-expert-guide"><i class="ph ph-cursor-click" aria-hidden="true"></i>Seleccione una conexión para abrir ese dato. La flecha sigue la referencia almacenada.</p>
            <div x-data="rmExpertKnowledgeGraph(@js($fichaDato['grafo']), 'actual')" class="rm-expert-record__map">
                <div class="rm-lab-actions"><button type="button" @click="zoom(-.15)" class="rm-btn rm-btn-secondary" aria-label="Reducir conexiones">−</button><button type="button" @click="zoom(.15)" class="rm-btn rm-btn-secondary" aria-label="Ampliar conexiones">+</button><button type="button" @click="centrar()" class="rm-btn rm-btn-secondary">Ajustar conexiones</button></div>
                <x-ui.expert-graph-canvas class="rm-knowledge-canvas" label="Mapa de referencias del registro" node-attribute="data-expert-record-node" select-action="$wire.abrirDato(nodo.tabla, nodo.registro)" />
                @if(count($fichaDato['enlaces']) > 18)<p class="rm-caption">El mapa muestra las primeras 18 referencias; todas se pueden consultar en la lista inferior.</p>@endif
            </div>
            <div class="rm-expert-record__links">
                @foreach($fichaDato['enlaces'] as $enlace)
                    <button type="button" data-expert-related wire:click="abrirDato(@js($enlace['tabla']), @js($enlace['id']))"><span><small>{{ $enlace['direccion'] }} · {{ $enlace['categoria'] }}</small><strong>{{ $enlace['nombre'] }}</strong><small>{{ $enlace['id'] }} · {{ str_replace('_', ' ', $enlace['campo']) }}</small></span><i class="ph ph-arrow-up-right" aria-hidden="true"></i></button>
                @endforeach
            </div>
        @else
            <x-ui.empty-state compact title="Sin referencias en esta versión" description="Este registro no tiene vínculos explícitos con otros datos del paquete. Puede consultar todos sus campos." icon="ph-link-break" />
        @endif
    </section>
    <section x-show="pestana === 'campos'" x-cloak aria-label="Campos originales del registro">
        <p class="rm-expert-guide">Contenido original de la instantánea seleccionada. Los identificadores permiten comprobar cada referencia.</p>
        @include('livewire.superadministrador.sistema-experto.datos', ['datos' => $fichaDato['campos']])
    </section>
</div>
