<section class="rm-card rm-resident-expert" aria-labelledby="resident-expert-title">
<<<<<<< HEAD
    <header class="rm-resident-expert__identity">
        <span class="rm-expert-avatar" aria-hidden="true"><i class="ph ph-user"></i></span>
        <div><p class="rm-expert-eyebrow">Sistema experto · Este residente</p><h2 id="resident-expert-title">{{ $residente->nombre_completo }}</h2><p>{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no disponible' }} · {{ $residente->ubicacion_formateada }}</p></div>
        <div class="rm-resident-expert__scope"><span><i class="ph ph-folder-user" aria-hidden="true"></i>Consulta del expediente actual</span><p>{{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($conexionesResidente['fecha'] ?? null, 'Corte no disponible') }}</p></div>
    </header>

    <div class="rm-resident-expert__intro"><div><h3>Cómo se conecta su información</h3><p>Elija un área o requisito y siga sus soportes hasta la valoración original. Las flechas representan vínculos documentados, no intensidad ni gravedad.</p></div><button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Consultar registros</button></div>
=======
    <div class="rm-resident-expert__intro"><div><h2 id="resident-expert-title">Conexiones del expediente</h2><p>Seleccione un registro del mapa para ver su fecha, autor y relaciones.</p></div><button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Consultar registros</button></div>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo

    @if($errorInstrumental)
        <div class="rm-resident-expert__empty" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i><h3>No se pudieron verificar las conexiones</h3><p>Vuelva a consultar las fuentes. No se representa el fallo como ausencia de información.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Reintentar consulta</button></div>
    @elseif(! ($conexionesResidente['disponible'] ?? false))
        <div class="rm-resident-expert__empty" role="status"><i class="ph ph-git-branch" aria-hidden="true"></i><h3>Las conexiones de valoración aún no están disponibles</h3><p>Puede consultar los registros existentes más abajo. No se crean vínculos a partir del nombre de un instrumento ni se trasladan conexiones de otro residente.</p></div>
    @else
        <div wire:key="conexiones-{{ $codResidente }}-{{ md5(json_encode($conexionesResidente['elementos'])) }}" x-data="rmExpertResidentWorkspace(@js($conexionesResidente['elementos']))" class="rm-resident-expert__workspace">
            <div class="rm-resident-expert__toolbar">
                <div role="group" aria-label="Explorar áreas o requisitos" class="rm-resident-expert__switch">
<<<<<<< HEAD
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
            @foreach($conexionesResidente['elementos'] as $elemento)
                <template x-if="seleccionado === @js($elemento['id'])">
                <section class="rm-resident-expert__exploration" x-data="Object.assign(rmExpertResidentGraph(@js($elemento['grafo'])), { area: @js($elemento) })" aria-label="Conexiones de {{ $elemento['nombre'] }}">
                    <header class="rm-resident-expert__graph-heading"><div><p class="rm-expert-eyebrow">Exploración de la consulta actual</p><h3 x-text="area.nombre"></h3><p x-text="area.estado"></p></div>
=======
                    <button type="button" x-show="elementos.some(e => e.grupo === 'areas')" @click="cambiarGrupo('areas')" :aria-pressed="grupo === 'areas'" :disabled="!elementos.some(e => e.grupo === 'areas')">Cinco áreas cognitivas</button>
                    <button type="button" x-show="elementos.some(e => e.grupo === 'requisitos')" @click="cambiarGrupo('requisitos')" :aria-pressed="grupo === 'requisitos'" :disabled="!elementos.some(e => e.grupo === 'requisitos')">Requisitos de valoración</button>
                    <button type="button" x-show="elementos.some(e => e.grupo === 'fuentes')" @click="cambiarGrupo('fuentes')" :aria-pressed="grupo === 'fuentes'" :disabled="!elementos.some(e => e.grupo === 'fuentes')">Registros del expediente</button>
                </div>
                <p>Consulta del {{ \App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible::mostrar($conexionesResidente['fecha'] ?? null, 'corte disponible') }}</p>
            </div>
            <div class="rm-resident-expert__choices" x-show="opciones.length > 1" role="group" aria-label="Seleccionar elemento del residente">
                <template x-for="elemento in opciones" :key="elemento.id">
                    <button type="button" @click="seleccionar(elemento.id)" :aria-pressed="seleccionado === elemento.id" :data-state="elemento.codigoEstado">
                        <i :class="'ph ' + elemento.icono" aria-hidden="true"></i><strong x-text="elemento.nombre"></strong><small x-text="elemento.cantidadVisible"></small>
                    </button>
                </template>
            </div>
            <template x-for="area in (actual ? [actual] : [])" :key="area.id">
                <section class="rm-resident-expert__exploration" x-data="rmExpertResidentGraph(area.grafo)" :aria-label="'Conexiones de ' + area.nombre">
                    <header class="rm-resident-expert__graph-heading"><div><h3 x-text="area.nombre"></h3></div>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                        <div role="group" aria-label="Representación de las conexiones" class="rm-resident-expert__switch"><button type="button" @click="cambiarVista('mapa')" :aria-pressed="vista === 'mapa'">Mapa</button><button type="button" @click="cambiarVista('lista')" :aria-pressed="vista === 'lista'">Lista</button></div>
                    </header>
                    <div class="rm-resident-expert__steps" role="group" aria-label="Recorrer la procedencia">
                        <button type="button" @click="explorarEtapa('Soporte')" :disabled="!disponible('Soporte')" :aria-pressed="etapa === 'Soporte'"><span>1</span> Fuentes originales <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" @click="explorarEtapa('Valoración')" :disabled="!disponible('Valoración')" :aria-pressed="etapa === 'Valoración'"><span>2</span> Valoraciones <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" @click="explorarEtapa('Elemento')" :aria-pressed="etapa === 'Elemento'"><span>3</span> Elemento consultado</button>
                    </div>
                    <nav class="rm-resident-expert__path" aria-label="Camino explorado en este expediente">
<<<<<<< HEAD
                        <span>Camino explorado</span>
=======
                        <span><i class="ph ph-footprints" aria-hidden="true"></i> Su recorrido</span>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                        <ol><template x-for="(paso, indice) in pasosRecorrido" :key="paso.id"><li><i x-show="indice > 0" class="ph ph-caret-right" aria-hidden="true"></i><button type="button" @click="volverPaso(paso.id)" :aria-current="paso.id === seleccionado ? 'step' : null" x-text="paso.nombre"></button></li></template></ol>
                        <span class="sr-only" role="status" x-text="'Paso ' + pasosRecorrido.length + ': ' + (actual?.nombre ?? 'Seleccione un nodo')"></span>
                    </nav>
                    <div class="rm-resident-expert__graph-layout">
                        <div class="rm-resident-expert__graph-main">
<<<<<<< HEAD
                            <div class="rm-resident-expert__graph-tools" x-show="vista === 'mapa'" role="group" aria-label="Controles del mapa del residente"><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(-.15)" aria-label="Reducir mapa">−</button><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(.15)" aria-label="Ampliar mapa">+</button><button type="button" class="rm-btn rm-btn-secondary" @click="centrar()">Ajustar mapa</button><button type="button" class="rm-btn rm-btn-secondary" @click="enfocarConexiones()" :aria-pressed="focoVecindario">Enfocar conexión</button><span role="status" x-text="cantidadVisible + ' nodos · ' + Math.round(escala * 100) + '%'"></span></div>
                            <div class="rm-resident-expert__canvas" x-show="vista === 'mapa'" x-ref="area">
                                <svg :view-box.camel="`0 0 ${viewportAncho} ${viewportAlto}`" role="group" aria-label="Soportes, valoraciones y conexiones del elemento seleccionado" @pointerdown="iniciar($event)" @pointermove="mover($event)" @pointerup="terminar()" @pointercancel="terminar()">
                                    <defs><marker :id="'resident-arrow-' + area.id" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0 0 L8 4 L0 8" class="rm-orion-arrow"/></marker></defs>
                                    <g :transform="`translate(${dx} ${dy}) scale(${escala})`">
                                        @foreach($elemento['grafo']['aristas'] as $arista)
                                            <path x-show="aristaVisible(@js($arista))" :d="ruta(@js($arista))" class="rm-orion-edge" :class="{ 'is-connected': vinculada(@js($arista)), 'is-path': enRecorrido(@js($arista)) }" :marker-end="'url(#resident-arrow-' + area.id + ')'"><title>{{ $arista['relacion'] }}</title></path>
                                        @endforeach
                                        @foreach($elemento['grafo']['nodos'] as $nodo)
                                            <g data-orion-node data-resident-node x-show="esVisible(@js($nodo['id']))" role="button" tabindex="0" aria-label="{{ $nodo['tipo'].': '.$nodo['nombre'].'. '.$nodo['estado'] }}" :aria-pressed="seleccionado === @js($nodo['id'])" :transform="`translate(${punto(@js($nodo['id'])).x} ${punto(@js($nodo['id'])).y})`" @click="seleccionar(@js($nodo['id']))" @keydown.enter.prevent="seleccionar(@js($nodo['id']))" @keydown.space.prevent="seleccionar(@js($nodo['id']))" @focus="enfocar(@js($nodo['id']))" class="rm-orion-node" :class="{ 'is-selected': seleccionado === @js($nodo['id']) }">
                                                <rect width="190" height="86" rx="10"/><text x="12" y="20" class="rm-orion-node-type">{{ $nodo['tipo'] }}</text><text x="12" y="43">{{ \Illuminate\Support\Str::limit($nodo['nombre'], 24) }}</text><text x="12" y="67" class="rm-orion-node-state">{{ \Illuminate\Support\Str::limit($nodo['estado'], 26) }}</text><title>{{ $nodo['nombre'].' · '.$nodo['estado'] }}</title>
                                            </g>
                                        @endforeach
                                    </g>
                                </svg>
                            </div>
                            <div x-show="vista === 'lista'" class="rm-resident-expert__node-list" role="group" aria-label="Lista de fuentes y valoraciones"><template x-for="nodo in nodos" :key="nodo.id"><button type="button" @click="seleccionar(nodo.id)" :aria-pressed="seleccionado === nodo.id"><span x-text="nodo.tipo"></span><strong x-text="nodo.nombre"></strong><small x-text="nodo.estado"></small></button></template></div>
                            <p class="rm-resident-expert__map-help" x-show="vista === 'mapa'">Arrastre el fondo para desplazarse. Tab y Enter permiten seleccionar cada nodo; la vista Lista conserva los mismos datos.</p>
=======
                            <div class="rm-resident-expert__graph-tools" x-show="vista === 'mapa'" role="group" aria-label="Controles del mapa del residente"><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(-.15)" aria-label="Reducir mapa">−</button><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(.15)" aria-label="Ampliar mapa">+</button><button type="button" class="rm-btn rm-btn-secondary" @click="centrar()">Ajustar mapa</button><button type="button" class="rm-btn rm-btn-secondary" @click="enfocarConexiones()" :aria-pressed="focoVecindario">Enfocar conexión</button><span role="status" x-text="cantidadVisible + ' de ' + nodos.length + ' nodos · ' + Math.round(escala * 100) + '%'"></span></div>
                            <nav class="rm-resident-expert__graph-tools" x-show="paginarFuentes && vista === 'mapa'" aria-label="Páginas de fuentes del mapa"><button type="button" class="rm-btn rm-btn-secondary" @click="cambiarPaginaFuente(paginaFuentes - 1)" :disabled="paginaFuentes <= 1">Fuentes anteriores</button><span role="status" x-text="'Página ' + paginaFuentes + ' de ' + paginasFuentes"></span><button type="button" class="rm-btn rm-btn-secondary" @click="cambiarPaginaFuente(paginaFuentes + 1)" :disabled="paginaFuentes >= paginasFuentes">Siguientes fuentes</button></nav>
                            <x-ui.expert-graph-canvas class="rm-resident-expert__canvas" x-show="vista === 'mapa'" label="Soportes, valoraciones y conexiones del elemento seleccionado" node-attribute="data-resident-node" :path-aware="true" />
                            <div x-show="vista === 'lista'" class="rm-resident-expert__node-list" role="group" aria-label="Lista de fuentes y valoraciones"><template x-for="nodo in nodos" :key="nodo.id"><button type="button" @click="seleccionar(nodo.id)" :aria-pressed="seleccionado === nodo.id"><span x-text="nodo.tipo"></span><strong x-text="nodo.nombre"></strong><small x-text="nodo.estado"></small></button></template></div>
                            <p class="rm-resident-expert__map-help" x-show="vista === 'mapa'">Arrastre para desplazarse · Seleccione un nodo · Use + para ampliar.</p>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                            <div x-show="aristas.length === 0" class="rm-resident-expert__no-links" role="status"><i class="ph ph-info" aria-hidden="true"></i><p>Sin soportes ni valoraciones originales vinculados a este elemento. Su estado no se interpreta como rendimiento esperado.</p></div>
                        </div>
                        <aside class="rm-resident-expert__inspector" aria-label="Detalle de la conexión del residente" aria-live="polite">
                            <template x-if="actual"><div><p class="rm-expert-eyebrow" x-text="actual.tipo + ' seleccionado'"></p><h4 x-text="actual.nombre"></h4><span class="rm-resident-expert__state" x-text="actual.estado"></span><dl><template x-for="(valor, nombre) in actual.datos" :key="nombre"><div><dt x-text="nombre"></dt><dd x-text="valor"></dd></div></template></dl>
<<<<<<< HEAD
                                <h5>Conexiones documentadas</h5><template x-if="conexionesActuales.length === 0"><p>Este elemento no tiene vínculos documentados en la consulta actual.</p></template><ul><template x-for="conexion in conexionesActuales" :key="conexion.origen + '>' + conexion.destino"><li><span x-text="conexion.relacion"></span><button type="button" @click="seguirConexion(conexion.origen === seleccionado ? conexion.destino : conexion.origen)" x-text="conexion.nombre"></button></li></template></ul>
=======
                                <h5>Seguir una conexión</h5><template x-if="conexionesActuales.length === 0"><p>Este elemento no tiene vínculos documentados en la consulta actual.</p></template><ul><template x-for="conexion in conexionesActuales" :key="conexion.origen + '>' + conexion.destino"><li><small x-text="punto(conexion.origen === seleccionado ? conexion.destino : conexion.origen)?.datos?.Fecha ?? conexion.relacion"></small><button type="button" @click="seguirConexion(conexion.origen === seleccionado ? conexion.destino : conexion.origen)" x-text="conexion.nombre"></button></li></template></ul>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
                            </div></template>
                        </aside>
                    </div>
                    <details class="rm-resident-expert__reasons" x-show="area.motivos.length"><summary>Motivos y límites de este elemento</summary><ul><template x-for="(motivo, indice) in area.motivos" :key="indice"><li x-text="motivo"></li></template></ul></details>
                </section>
<<<<<<< HEAD
                </template>
            @endforeach
            <p class="rm-resident-expert__boundary"><i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>Este mapa consulta fuentes actuales. La evaluación histórica conserva su fecha, su versión y su traza en el detalle inferior. Explorar el mapa no guarda ni modifica registros.</p>
=======
            </template>
            <details class="rm-resident-expert__reasons"><summary>Acerca de este mapa</summary><p>Representa vínculos documentados del expediente actual. Explorar no guarda ni ejecuta una evaluación; la historia conserva su fecha y versión.</p></details>
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        </div>
    @endif
</section>
