<section class="rm-card rm-resident-expert" aria-labelledby="resident-expert-title">
    <header class="rm-resident-expert__identity">
        <span class="rm-expert-avatar" aria-hidden="true"><i class="ph ph-user"></i></span>
        <div><p class="rm-expert-eyebrow">Sistema experto · Este residente</p><h2 id="resident-expert-title">{{ $residente->nombre_completo }}</h2><p>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }} · {{ $residente->ubicacion_formateada }}</p></div>
        <div class="rm-resident-expert__scope"><span><i class="ph ph-folder-user" aria-hidden="true"></i>Consulta del expediente actual</span><p>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($conexionesResidente['fecha'] ?? null, 'Corte no disponible') }}</p></div>
    </header>

    <div class="rm-resident-expert__intro"><div><h3>Cómo se conecta su información</h3><p>Elija un área o requisito y siga sus soportes hasta la valoración original. Las flechas representan vínculos documentados, no intensidad ni gravedad.</p></div><button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Consultar registros</button></div>

    @if($errorInstrumental)
        <div class="rm-resident-expert__empty" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i><h3>No se pudieron verificar las conexiones</h3><p>Vuelva a consultar las fuentes. No se representa el fallo como ausencia de información.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Reintentar consulta</button></div>
    @elseif(! ($conexionesResidente['disponible'] ?? false))
        <div class="rm-resident-expert__empty" role="status"><i class="ph ph-git-branch" aria-hidden="true"></i><h3>Las conexiones de valoración aún no están disponibles</h3><p>Puede consultar los registros existentes más abajo. No se crean vínculos a partir del nombre de un instrumento ni se trasladan conexiones de otro residente.</p></div>
    @else
        <div wire:key="conexiones-{{ $codResidente }}-{{ md5(json_encode($conexionesResidente['elementos'])) }}" x-data="rmExpertResidentWorkspace(@js($conexionesResidente['elementos']))" class="rm-resident-expert__workspace">
            <div class="rm-resident-expert__toolbar">
                <div role="group" aria-label="Explorar áreas o requisitos" class="rm-resident-expert__switch">
                    <button type="button" @click="cambiarGrupo('areas')" :aria-pressed="grupo === 'areas'" :disabled="!elementos.some(e => e.grupo === 'areas')">Cinco áreas cognitivas</button>
                    <button type="button" @click="cambiarGrupo('requisitos')" :aria-pressed="grupo === 'requisitos'" :disabled="!elementos.some(e => e.grupo === 'requisitos')">Requisitos de valoración</button>
                    <button type="button" @click="cambiarGrupo('fuentes')" :aria-pressed="grupo === 'fuentes'" :disabled="!elementos.some(e => e.grupo === 'fuentes')">Registros del expediente</button>
                </div>
                <p>{{ $conexionesResidente['valoraciones'] }} valoraciones originales · {{ $conexionesResidente['soportes'] }} soportes distintos</p>
            </div>
            <div class="rm-resident-expert__choices" role="group" aria-label="Seleccionar elemento del residente">
                <template x-for="elemento in opciones" :key="elemento.id">
                    <button type="button" @click="seleccionar(elemento.id)" :aria-pressed="seleccionado === elemento.id" :data-state="elemento.codigoEstado">
                        <i :class="'ph ' + elemento.icono" aria-hidden="true"></i><strong x-text="elemento.nombre"></strong><span x-text="elemento.estado"></span><small x-text="elemento.cantidadVisible"></small>
                    </button>
                </template>
            </div>
            <template x-for="area in (actual ? [actual] : [])" :key="area.id">
                <section class="rm-resident-expert__exploration" x-data="rmExpertResidentGraph(area.grafo)" :aria-label="'Conexiones de ' + area.nombre">
                    <header class="rm-resident-expert__graph-heading"><div><p class="rm-expert-eyebrow">Exploración de la consulta actual</p><h3 x-text="area.nombre"></h3><p x-text="area.estado"></p></div>
                        <div role="group" aria-label="Representación de las conexiones" class="rm-resident-expert__switch"><button type="button" @click="cambiarVista('mapa')" :aria-pressed="vista === 'mapa'">Mapa</button><button type="button" @click="cambiarVista('lista')" :aria-pressed="vista === 'lista'">Lista</button></div>
                    </header>
                    <div class="rm-resident-expert__steps" role="group" aria-label="Recorrer la procedencia">
                        <button type="button" @click="explorarEtapa('Soporte')" :disabled="!disponible('Soporte')" :aria-pressed="etapa === 'Soporte'"><span>1</span> Fuentes originales <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" @click="explorarEtapa('Valoración')" :disabled="!disponible('Valoración')" :aria-pressed="etapa === 'Valoración'"><span>2</span> Valoraciones <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" @click="explorarEtapa('Elemento')" :aria-pressed="etapa === 'Elemento'"><span>3</span> Elemento consultado</button>
                    </div>
                    <nav class="rm-resident-expert__path" aria-label="Camino explorado en este expediente">
                        <span>Camino explorado</span>
                        <ol><template x-for="(paso, indice) in pasosRecorrido" :key="paso.id"><li><i x-show="indice > 0" class="ph ph-caret-right" aria-hidden="true"></i><button type="button" @click="volverPaso(paso.id)" :aria-current="paso.id === seleccionado ? 'step' : null" x-text="paso.nombre"></button></li></template></ol>
                        <span class="sr-only" role="status" x-text="'Paso ' + pasosRecorrido.length + ': ' + (actual?.nombre ?? 'Seleccione un nodo')"></span>
                    </nav>
                    <div class="rm-resident-expert__graph-layout">
                        <div class="rm-resident-expert__graph-main">
                            <div class="rm-resident-expert__graph-tools" x-show="vista === 'mapa'" role="group" aria-label="Controles del mapa del residente"><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(-.15)" aria-label="Reducir mapa">−</button><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(.15)" aria-label="Ampliar mapa">+</button><button type="button" class="rm-btn rm-btn-secondary" @click="centrar()">Ajustar mapa</button><button type="button" class="rm-btn rm-btn-secondary" @click="enfocarConexiones()" :aria-pressed="focoVecindario">Enfocar conexión</button><span role="status" x-text="cantidadVisible + ' nodos · ' + Math.round(escala * 100) + '%'"></span></div>
                            <div class="rm-resident-expert__canvas" x-show="vista === 'mapa'" x-ref="area">
                                <svg :view-box.camel="`0 0 ${viewportAncho} ${viewportAlto}`" role="group" aria-label="Soportes, valoraciones y conexiones del elemento seleccionado" @pointerdown="iniciar($event)" @pointermove="mover($event)" @pointerup="terminar()" @pointercancel="terminar()">
                                    <defs><marker :id="'resident-arrow-' + area.id" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0 0 L8 4 L0 8" class="rm-orion-arrow"/></marker></defs>
                                    <g :transform="`translate(${dx} ${dy}) scale(${escala})`">
                                        <template x-for="arista in aristas" :key="arista.origen + '>' + arista.destino"><path x-show="aristaVisible(arista)" :d="ruta(arista)" class="rm-orion-edge" :class="{ 'is-connected': vinculada(arista), 'is-path': enRecorrido(arista) }" :marker-end="'url(#resident-arrow-' + area.id + ')'"><title x-text="arista.relacion"></title></path></template>
                                        <template x-for="nodo in nodos" :key="nodo.id">
                                            <g data-orion-node data-resident-node x-show="esVisible(nodo.id)" role="button" tabindex="0" :aria-label="nodo.tipo + ': ' + nodo.nombre + '. ' + nodo.estado" :aria-pressed="seleccionado === nodo.id" :transform="`translate(${nodo.x} ${nodo.y})`" @click="seleccionar(nodo.id)" @keydown.enter.prevent="seleccionar(nodo.id)" @keydown.space.prevent="seleccionar(nodo.id)" @focus="enfocar(nodo.id)" class="rm-orion-node" :class="{ 'is-selected': seleccionado === nodo.id }">
                                                <rect width="190" height="86" rx="10"/><text x="12" y="20" class="rm-orion-node-type" x-text="nodo.tipo"></text><text x="12" y="43" x-text="nodo.nombre.length > 24 ? nodo.nombre.slice(0, 22) + '…' : nodo.nombre"></text><text x="12" y="67" class="rm-orion-node-state" x-text="nodo.estado.length > 26 ? nodo.estado.slice(0, 24) + '…' : nodo.estado"></text><title x-text="nodo.nombre + ' · ' + nodo.estado"></title>
                                            </g>
                                        </template>
                                    </g>
                                </svg>
                            </div>
                            <div x-show="vista === 'lista'" class="rm-resident-expert__node-list" role="group" aria-label="Lista de fuentes y valoraciones"><template x-for="nodo in nodos" :key="nodo.id"><button type="button" @click="seleccionar(nodo.id)" :aria-pressed="seleccionado === nodo.id"><span x-text="nodo.tipo"></span><strong x-text="nodo.nombre"></strong><small x-text="nodo.estado"></small></button></template></div>
                            <p class="rm-resident-expert__map-help" x-show="vista === 'mapa'">Arrastre el fondo para desplazarse. Tab y Enter permiten seleccionar cada nodo; la vista Lista conserva los mismos datos.</p>
                            <div x-show="aristas.length === 0" class="rm-resident-expert__no-links" role="status"><i class="ph ph-info" aria-hidden="true"></i><p>Sin soportes ni valoraciones originales vinculados a este elemento. Su estado no se interpreta como rendimiento esperado.</p></div>
                        </div>
                        <aside class="rm-resident-expert__inspector" aria-label="Detalle de la conexión del residente" aria-live="polite">
                            <template x-if="actual"><div><p class="rm-expert-eyebrow" x-text="actual.tipo + ' seleccionado'"></p><h4 x-text="actual.nombre"></h4><span class="rm-resident-expert__state" x-text="actual.estado"></span><dl><template x-for="(valor, nombre) in actual.datos" :key="nombre"><div><dt x-text="nombre"></dt><dd x-text="valor"></dd></div></template></dl>
                                <h5>Conexiones documentadas</h5><template x-if="conexionesActuales.length === 0"><p>Este elemento no tiene vínculos documentados en la consulta actual.</p></template><ul><template x-for="conexion in conexionesActuales" :key="conexion.origen + '>' + conexion.destino"><li><span x-text="conexion.relacion"></span><button type="button" @click="seguirConexion(conexion.origen === seleccionado ? conexion.destino : conexion.origen)" x-text="conexion.nombre"></button></li></template></ul>
                            </div></template>
                        </aside>
                    </div>
                    <details class="rm-resident-expert__reasons" x-show="area.motivos.length"><summary>Motivos y límites de este elemento</summary><ul><template x-for="(motivo, indice) in area.motivos" :key="indice"><li x-text="motivo"></li></template></ul></details>
                </section>
            </template>
            <p class="rm-resident-expert__boundary"><i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>Este mapa consulta fuentes actuales. La evaluación histórica conserva su fecha, su versión y su traza en el detalle inferior. Explorar el mapa no guarda ni modifica registros.</p>
        </div>
    @endif
</section>
