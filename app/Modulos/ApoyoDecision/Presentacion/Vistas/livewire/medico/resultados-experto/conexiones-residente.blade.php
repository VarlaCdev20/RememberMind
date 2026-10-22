<section class="rm-card rm-resident-expert" aria-labelledby="resident-expert-title">
    <div class="rm-resident-expert__intro"><div><h2 id="resident-expert-title">Conexiones del expediente</h2><p>Seleccione un registro del mapa para ver su fecha, autor y relaciones.</p></div><button type="button" wire:click="irASeccion('fuentes-disponibles')" class="rm-btn rm-btn-secondary"><i class="ph ph-files" aria-hidden="true"></i>Consultar registros</button></div>

    @if($errorInstrumental)
        <div class="rm-resident-expert__empty" role="alert"><i class="ph ph-warning-circle" aria-hidden="true"></i><h3>No se pudieron verificar las conexiones</h3><p>Vuelva a consultar las fuentes. No se representa el fallo como ausencia de información.</p><button type="button" wire:click="reintentar" wire:loading.attr="disabled" class="rm-btn rm-btn-secondary">Reintentar consulta</button></div>
    @elseif(! ($conexionesResidente['disponible'] ?? false))
        <div class="rm-resident-expert__empty" role="status"><i class="ph ph-git-branch" aria-hidden="true"></i><h3>Las conexiones de valoración aún no están disponibles</h3><p>Puede consultar los registros existentes más abajo. No se crean vínculos a partir del nombre de un instrumento ni se trasladan conexiones de otro residente.</p></div>
    @else
        <div wire:key="conexiones-{{ $codResidente }}-{{ md5(json_encode($conexionesResidente['elementos'])) }}" x-data="rmExpertResidentWorkspace(@js($conexionesResidente['elementos']))" class="rm-resident-expert__workspace">
            <div class="rm-resident-expert__toolbar">
                <div role="group" aria-label="Explorar áreas o requisitos" class="rm-resident-expert__switch">
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
                        <div role="group" aria-label="Representación de las conexiones" class="rm-resident-expert__switch"><button type="button" @click="cambiarVista('mapa')" :aria-pressed="vista === 'mapa'">Mapa</button><button type="button" @click="cambiarVista('lista')" :aria-pressed="vista === 'lista'">Lista</button></div>
                    </header>
                    <div class="rm-resident-expert__steps" role="group" aria-label="Recorrer la procedencia">
                        <button type="button" @click="explorarEtapa('Soporte')" :disabled="!disponible('Soporte')" :aria-pressed="etapa === 'Soporte'"><span>1</span> Fuentes originales <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" @click="explorarEtapa('Valoración')" :disabled="!disponible('Valoración')" :aria-pressed="etapa === 'Valoración'"><span>2</span> Valoraciones <i class="ph ph-arrow-right" aria-hidden="true"></i></button>
                        <button type="button" @click="explorarEtapa('Elemento')" :aria-pressed="etapa === 'Elemento'"><span>3</span> Elemento consultado</button>
                    </div>
                    <nav class="rm-resident-expert__path" aria-label="Camino explorado en este expediente">
                        <span><i class="ph ph-footprints" aria-hidden="true"></i> Su recorrido</span>
                        <ol><template x-for="(paso, indice) in pasosRecorrido" :key="paso.id"><li><i x-show="indice > 0" class="ph ph-caret-right" aria-hidden="true"></i><button type="button" @click="volverPaso(paso.id)" :aria-current="paso.id === seleccionado ? 'step' : null" x-text="paso.nombre"></button></li></template></ol>
                        <span class="sr-only" role="status" x-text="'Paso ' + pasosRecorrido.length + ': ' + (actual?.nombre ?? 'Seleccione un nodo')"></span>
                    </nav>
                    <div class="rm-resident-expert__graph-layout">
                        <div class="rm-resident-expert__graph-main">
                            <div class="rm-resident-expert__graph-tools" x-show="vista === 'mapa'" role="group" aria-label="Controles del mapa del residente"><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(-.15)" aria-label="Reducir mapa">−</button><button type="button" class="rm-btn rm-btn-secondary" @click="zoom(.15)" aria-label="Ampliar mapa">+</button><button type="button" class="rm-btn rm-btn-secondary" @click="centrar()">Ajustar mapa</button><button type="button" class="rm-btn rm-btn-secondary" @click="enfocarConexiones()" :aria-pressed="focoVecindario">Enfocar conexión</button><span role="status" x-text="cantidadVisible + ' de ' + nodos.length + ' nodos · ' + Math.round(escala * 100) + '%'"></span></div>
                            <nav class="rm-resident-expert__graph-tools" x-show="paginarFuentes && vista === 'mapa'" aria-label="Páginas de fuentes del mapa"><button type="button" class="rm-btn rm-btn-secondary" @click="cambiarPaginaFuente(paginaFuentes - 1)" :disabled="paginaFuentes <= 1">Fuentes anteriores</button><span role="status" x-text="'Página ' + paginaFuentes + ' de ' + paginasFuentes"></span><button type="button" class="rm-btn rm-btn-secondary" @click="cambiarPaginaFuente(paginaFuentes + 1)" :disabled="paginaFuentes >= paginasFuentes">Siguientes fuentes</button></nav>
                            <x-ui.expert-graph-canvas class="rm-resident-expert__canvas" x-show="vista === 'mapa'" label="Soportes, valoraciones y conexiones del elemento seleccionado" node-attribute="data-resident-node" :path-aware="true" />
                            <div x-show="vista === 'lista'" class="rm-resident-expert__node-list" role="group" aria-label="Lista de fuentes y valoraciones"><template x-for="nodo in nodos" :key="nodo.id"><button type="button" @click="seleccionar(nodo.id)" :aria-pressed="seleccionado === nodo.id"><span x-text="nodo.tipo"></span><strong x-text="nodo.nombre"></strong><small x-text="nodo.estado"></small></button></template></div>
                            <p class="rm-resident-expert__map-help" x-show="vista === 'mapa'">Arrastre para desplazarse · Seleccione un nodo · Use + para ampliar.</p>
                            <div x-show="aristas.length === 0" class="rm-resident-expert__no-links" role="status"><i class="ph ph-info" aria-hidden="true"></i><p>Sin soportes ni valoraciones originales vinculados a este elemento. Su estado no se interpreta como rendimiento esperado.</p></div>
                        </div>
                        <aside class="rm-resident-expert__inspector" aria-label="Detalle de la conexión del residente" aria-live="polite">
                            <template x-if="actual"><div><p class="rm-expert-eyebrow" x-text="actual.tipo + ' seleccionado'"></p><h4 x-text="actual.nombre"></h4><span class="rm-resident-expert__state" x-text="actual.estado"></span><dl><template x-for="(valor, nombre) in actual.datos" :key="nombre"><div><dt x-text="nombre"></dt><dd x-text="valor"></dd></div></template></dl>
                                <h5>Seguir una conexión</h5><template x-if="conexionesActuales.length === 0"><p>Este elemento no tiene vínculos documentados en la consulta actual.</p></template><ul><template x-for="conexion in conexionesActuales" :key="conexion.origen + '>' + conexion.destino"><li><small x-text="punto(conexion.origen === seleccionado ? conexion.destino : conexion.origen)?.datos?.Fecha ?? conexion.relacion"></small><button type="button" @click="seguirConexion(conexion.origen === seleccionado ? conexion.destino : conexion.origen)" x-text="conexion.nombre"></button></li></template></ul>
                            </div></template>
                        </aside>
                    </div>
                    <details class="rm-resident-expert__reasons" x-show="area.motivos.length"><summary>Motivos y límites de este elemento</summary><ul><template x-for="(motivo, indice) in area.motivos" :key="indice"><li x-text="motivo"></li></template></ul></details>
                </section>
            </template>
            <details class="rm-resident-expert__reasons"><summary>Acerca de este mapa</summary><p>Representa vínculos documentados del expediente actual. Explorar no guarda ni ejecuta una evaluación; la historia conserva su fecha y versión.</p></details>
        </div>
    @endif
</section>
