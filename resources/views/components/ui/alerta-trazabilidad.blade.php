@props(['traza', 'titulo' => 'Relaciones del caso', 'icono' => 'ph-link', 'foco' => 'auditoria', 'registroEspecialidad' => false])
@php
    $vistaProfesional = app(\App\Modulos\Alertas\Presentacion\Soporte\ComponerVistaCasoAlerta::class)->componer($traza, $traza['foco'] ?? $foco);
@endphp
@if($traza)
    @php
        $datosMapa = $traza + ['modoInicial' => $vistaProfesional['tipo'] === 'red' ? 'mapa' : 'area'];
        $gruposMapa = ['todas'=>'Vista completa','caso'=>'Persona y responsabilidad'];
        $gruposPresentes = collect($traza['nodos'])->pluck('grupo')->all();
        if(in_array('profesional', $gruposPresentes, true)) $gruposMapa['profesional'] = 'Contexto de la especialidad';
        if(in_array('origen', $gruposPresentes, true)) $gruposMapa['origen'] = 'Registro de origen';
        if(in_array('eventos', $gruposPresentes, true)) $gruposMapa['eventos'] = 'Seguimiento documentado';
        $porId = collect($traza['nodos'])->keyBy('id');
    @endphp
<section {{ $attributes->class(['rm-alert-trace', 'rm-alert-map']) }} wire:key="traza-{{ $traza['codigo'] }}-{{ md5(json_encode($traza)) }}" x-data="rmTrazaAlerta(@js($datosMapa))" aria-label="{{ $traza['titulo'] ?? $titulo }}" data-graph-readonly="true" data-map-theme="{{ $traza['foco'] ?? 'auditoria' }}" :data-arranging="organizar" :data-dragging="!!arrastre" @fullscreenchange="pantallaCompleta = document.fullscreenElement === $el">
    <header class="rm-alert-map__heading"><span class="rm-alert-map__emblem"><i class="ph-bold" :class="presentacion.icono" aria-hidden="true"></i></span><div><p class="rm-help">Consulta del caso · vista ajustable</p><h3>{{ $traza['titulo'] ?? $titulo }}</h3></div></header>
    <p>{{ $traza['proposito'] ?? 'Relaciones guardadas de la alerta seleccionada.' }}</p>
    <p class="rm-alert-map__resident"><i class="ph-bold ph-user" aria-hidden="true"></i>Residente: {{ $traza['residente'] }}</p>
    <nav class="rm-alert-trace__tabs" aria-label="Cómo consultar el caso">
        @if($vistaProfesional['tipo'] !== 'red')<button type="button" class="rm-btn rm-btn-secondary" @click="cambiarModo('area')" :aria-pressed="modo === 'area'" aria-controls="vista-area-{{ $traza['codigo'] }}"><i class="ph-bold" :class="presentacion.icono" aria-hidden="true"></i>{{ $vistaProfesional['titulo'] }}</button>@endif
        <button type="button" class="rm-btn rm-btn-secondary" @click="cambiarModo('mapa')" :aria-pressed="modo === 'mapa'" aria-controls="mapa-{{ $traza['codigo'] }}"><i class="ph-bold ph-share-network" aria-hidden="true"></i>{{ $vistaProfesional['tipo'] === 'red' ? 'Red de bienestar' : 'Explorar vínculos completos' }}</button>
        <button type="button" class="rm-btn rm-btn-secondary" @click="cambiarModo('tarjetas')" :aria-pressed="modo === 'tarjetas'" aria-controls="relaciones-{{ $traza['codigo'] }}"><i class="ph-bold ph-cards" aria-hidden="true"></i>Fichas de consulta</button>
        <button type="button" class="rm-btn rm-btn-secondary" @click="alternarPantallaCompleta" :disabled="cambiandoPantalla" :aria-pressed="pantallaCompleta"><i class="ph-bold ph-corners-out" aria-hidden="true"></i><span x-text="pantallaCompleta ? 'Salir de pantalla completa' : 'Pantalla completa'"></span></button>
    </nav>
    <p class="rm-help" role="status" x-text="aviso" x-show="aviso" x-cloak></p>
    <div id="vista-area-{{ $traza['codigo'] }}" @if($vistaProfesional['tipo'] !== 'red') x-show="modo === 'area'" @else x-show="modo === 'mapa'" @endif><x-ui.alerta-vista-profesional :vista="$vistaProfesional" /></div>
    <div class="rm-case-view__next"><p class="rm-help">Continuar según tu responsabilidad; el formulario conserva sus permisos y bloqueos.</p><button type="button" class="rm-btn rm-btn-primary" @click="abrirContinuacion(@js($registroEspecialidad))" :disabled="abriendoDetalle || cambiandoPantalla"><i class="ph-bold" :class="presentacion.icono" aria-hidden="true"></i>{{ $vistaProfesional['accion'] }}</button></div>
    <nav class="rm-alert-trace__layers" x-show="modo !== 'area'" aria-label="Información que deseas explorar">@foreach($gruposMapa as $capa=>$etiqueta)<button type="button" class="rm-btn rm-btn-secondary" @click="cambiarCapa('{{ $capa }}')" :aria-pressed="capa === '{{ $capa }}'">{{ $etiqueta }}</button>@endforeach</nav>
    <div id="mapa-{{ $traza['codigo'] }}" x-show="modo === 'mapa'">
        <div class="rm-alert-map__settings">
            <label for="altura-mapa-{{ $traza['codigo'] }}">Altura de la vista <output x-text="altura + ' px'"></output></label>
            <input id="altura-mapa-{{ $traza['codigo'] }}" type="range" min="280" max="680" step="20" x-model.number="altura" aria-label="Altura de la vista de relaciones">
        </div>
        <p class="rm-help" x-text="presentacion.guia"></p>
        <div class="rm-alert-map__canvas" x-ref="lienzo" :style="{ '--rm-map-height': altura + 'px' }">
        <svg x-bind:view-box.camel="vista" viewBox="0 0 900 900" class="rm-alert-trace__svg rm-alert-trace__svg--relational" role="group" aria-label="Relaciones del caso. Tab y flechas recorren los elementos; Enter permite consultarlos." @pointerdown="iniciarArrastre($event)" @pointermove="arrastrar($event)" @pointerup="terminarArrastre($event)" @pointercancel="terminarArrastre($event)" @lostpointercapture="terminarArrastre($event)">
            <defs><marker id="flecha-{{ $traza['codigo'] }}" markerWidth="8" markerHeight="8" refX="52" refY="4" orient="auto" markerUnits="userSpaceOnUse"><path d="M 0 0 L 8 4 L 0 8 z" class="rm-alert-trace__arrow" /></marker></defs>
            @foreach($traza['aristas'] as $arista)
                <g class="rm-alert-trace__edge" data-relation="{{ $arista['tipo'] ?? 'relacion' }}" :class="{ 'is-selected': conectado(@js($arista)) }" x-show="aristaVisible(@js($arista))"><title>{{ $porId[$arista['desde']]['label'] }} · {{ $arista['label'] }} · {{ $porId[$arista['hasta']]['label'] }}</title><line :x1="posicion(@js($arista['desde'])).x" :y1="posicion(@js($arista['desde'])).y" :x2="posicion(@js($arista['hasta'])).x" :y2="posicion(@js($arista['hasta'])).y" marker-end="url(#flecha-{{ $traza['codigo'] }})" /></g>
            @endforeach
            @foreach($traza['nodos'] as $nodo)
                <g role="button" tabindex="0" data-traza-nodo="{{ $nodo['id'] }}" x-show="nodoVisible(@js($nodo))" aria-label="{{ $nodo['label'] }}: {{ $nodo['texto'] }}" :aria-pressed="seleccionado === @js($nodo['id'])" :transform="'translate(' + posicion(@js($nodo['id'])).x + ',' + posicion(@js($nodo['id'])).y + ')'" class="rm-alert-trace__node" data-tone="{{ $nodo['tono'] }}" @click="seleccionar(@js($nodo['id']))" @keydown.enter.prevent="seleccionar(@js($nodo['id']))" @keydown.space.prevent="seleccionar(@js($nodo['id']))" @keydown="mover($event, @js($nodo['id']))" @focus="enfocarNodo(@js($nodo['id']))" @blur="destacado = null" @mouseenter="destacado = @js($nodo['id'])" @mouseleave="destacado = null">
                    <rect x="-50" y="-44" width="100" height="88" fill="transparent" />
                    <path :d="silueta" class="rm-alert-trace__shape" />
                    <foreignObject x="-17" y="-17" width="34" height="34" aria-hidden="true"><div xmlns="http://www.w3.org/1999/xhtml" class="rm-alert-map__node-icon"><i class="ph-bold" :class="icono(@js($nodo))"></i></div></foreignObject>
                    <text y="53" text-anchor="middle" class="rm-alert-trace__label" aria-hidden="true">{{ \Illuminate\Support\Str::limit($nodo['label'],23) }}</text>
                    <text y="73" text-anchor="middle" class="rm-alert-trace__value" aria-hidden="true">{{ \Illuminate\Support\Str::limit($nodo['texto'],28) }}</text>
                </g>
            @endforeach
        </svg>
        </div>
        <nav class="rm-alert-trace__controls" aria-label="Acercamiento y enfoque"><button type="button" class="rm-btn rm-btn-secondary" @click="ampliar(-0.2)" :disabled="zoom <= 0.6" aria-label="Alejar vista"><i class="ph-bold ph-minus" aria-hidden="true"></i></button><output class="rm-alert-map__scale" aria-label="Acercamiento" x-text="Math.round(zoom * 100) + '%'"></output><button type="button" class="rm-btn rm-btn-secondary" @click="ampliar(0.2)" :disabled="zoom >= 4" aria-label="Acercar vista"><i class="ph-bold ph-plus" aria-hidden="true"></i></button><button type="button" class="rm-btn rm-btn-secondary" @click="centrar"><i class="ph-bold ph-arrows-in" aria-hidden="true"></i>Ajustar a la vista</button><button type="button" class="rm-btn rm-btn-secondary" @click="centrarSeleccion">Enfocar selección</button></nav>
        <div class="rm-alert-map__organize"><button type="button" class="rm-btn rm-btn-secondary" @click="organizar = !organizar" :aria-pressed="organizar"><i class="ph-bold ph-hand" aria-hidden="true"></i><span x-text="organizar ? 'Terminar organización' : 'Reorganizar elementos'"></span></button><button type="button" class="rm-btn rm-btn-ghost" @click="restablecer">Restablecer distribución</button></div>
        <p class="rm-help">Arrastra el fondo para desplazar la vista. <span x-show="organizar">Arrastra un elemento o usa Alt + flechas con el foco sobre él. Solo cambia su posición en esta pantalla.</span> Tab y flechas recorren los elementos; Enter muestra su información.</p>
    </div>
    <button type="button" class="rm-btn rm-btn-secondary" @click="consultarSeleccion">Ir a la información seleccionada</button>
    <section class="rm-alert-trace__selection" x-ref="inspector" tabindex="-1" aria-live="polite" aria-label="Información del elemento seleccionado">
        <template x-for="seleccion in (nodo ? [nodo] : [])" :key="seleccion.id"><div class="rm-alert-map__inspector-content"><div class="rm-alert-map__selection-title"><i class="ph-bold" :class="icono(seleccion)" aria-hidden="true"></i><strong x-text="seleccion.label"></strong></div><p x-text="seleccion.texto"></p><dl class="rm-alert-desk__fields"><template x-for="(campo, indice) in (seleccion.detalles || [])" :key="indice"><div><dt x-text="campo.etiqueta"></dt><dd x-text="campo.valor"></dd></div></template></dl><ul class="rm-alert-trace__connections"><template x-for="(relacion, indice) in relacionesSeleccionadas" :key="indice"><li x-text="relacion"></li></template></ul></div></template>
    </section>
    <p class="rm-help">Las flechas indican el sentido del vínculo. La línea discontinua identifica contexto del residente, sin atribuirle el origen de la alerta. Los grupos de campos pertenecen al mismo registro.</p>
    @unless($traza['fuenteDisponible'])<p class="rm-help">No hay un registro de origen verificable consultable para esta alerta. El contexto profesional, cuando existe, se identifica por separado.</p>@endunless
    <div id="relaciones-{{ $traza['codigo'] }}" class="rm-alert-trace__relations" x-show="modo === 'tarjetas'" x-cloak>
        @foreach($traza['nodos'] as $nodo)<button type="button" class="rm-alert-relation-card" x-show="nodoVisible(@js($nodo))" @click="seleccionar(@js($nodo['id']))" :aria-pressed="seleccionado === @js($nodo['id'])"><span class="rm-alert-relation-card__label"><i class="ph-bold" :class="icono(@js($nodo))" aria-hidden="true"></i>{{ $nodo['label'] }}</span><span>{{ $nodo['texto'] }}</span>@foreach($traza['aristas'] as $arista)@if($arista['hasta'] === $nodo['id'])<small>{{ $porId[$arista['desde']]['label'] }} · {{ $arista['label'] }} · {{ $nodo['label'] }}</small>@endif @endforeach</button>@endforeach
    </div>
    <button type="button" class="rm-btn rm-btn-secondary" @click="consultarDetalle" :disabled="abriendoDetalle || cambiandoPantalla"><i class="ph-bold ph-notebook" aria-hidden="true"></i><span x-text="abriendoDetalle ? 'Abriendo el caso…' : 'Consultar campos e historial del caso'"></span></button>
    <p class="rm-help"><i class="ph-bold ph-lock-simple" aria-hidden="true"></i> Explorar y organizar esta vista no edita registros. {{ count($traza['movimientos']) }} de {{ $traza['totalEventos'] }} eventos en la lista reciente; hasta 3 en la vista de relaciones. El historial completo está en el detalle.</p>
</section>
@else
<section class="rm-alert-trace rm-alert-map"><header class="rm-alert-map__heading"><span class="rm-alert-map__emblem"><i class="ph-bold {{ $icono }}" aria-hidden="true"></i></span><h3>{{ $titulo }}</h3></header><x-ui.alerta-vista-profesional :vista="$vistaProfesional" /><p>Selecciona una alerta vinculada para consultar sus datos y continuar en el formulario autorizado.</p></section>
@endif
