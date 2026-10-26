@if($guia)
<x-ui.modal-livewire id="guia-instrumento" wire:model="mostrarGuia" :title="'Guía · '.$guia['nombre']" subtitle="Orientación de registro y consulta de fuentes" max-width="xl" close-method="cerrarGuia" :show-validation="false" class="rm-guia-modal">
    <div x-data="rmGuiaVisual(@js($guia['campos_preview']))" wire:key="guia-contenido-{{ $guia['id'] }}" class="rm-guia">
        <header class="rm-guia__bienvenida">
            <div><p class="rm-explorador__eyebrow">Conoce · prepara · revisa</p><h3>{{ $guia['nombre'] }}</h3><p>{{ $guia['descripcion'] }}</p><span class="rm-badge">{{ $guia['estado'] === 'CANDIDATO_INVESTIGADO' ? 'Biblioteca de investigación' : 'Catálogo institucional' }}</span></div>
            <x-ui.avatar-guia estado="explicacion" mensaje="Te acompaño a explorar esta ficha. Selecciona un paso o un campo y veamos qué debes revisar." :interactiva="true" />
        </header>
        <p class="rm-guia__precaucion" role="note"><i class="ph-bold ph-info" aria-hidden="true"></i>{{ $guia['precaucion'] }}</p>
        <nav class="rm-guia__tabs" role="tablist" aria-label="Contenido de la guía" @keydown="teclado($event)">
            @foreach(['resumen' => 'Resumen', 'pasos' => 'Cómo se aplica', 'materiales' => 'Materiales', 'condiciones' => 'Condiciones y precauciones', 'datos' => 'Datos que se registran', 'aporte' => 'Qué información aporta', 'fuentes' => 'Fuentes'] as $id => $titulo)
                <button type="button" id="guia-tab-{{ $id }}" x-ref="tab{{ ucfirst($id) }}" role="tab" :aria-selected="tab === '{{ $id }}'" :tabindex="tab === '{{ $id }}' ? 0 : -1" aria-controls="guia-panel-{{ $id }}" @click="seleccionar('{{ $id }}')">{{ $titulo }}</button>
            @endforeach
        </nav>
        @foreach(['resumen', 'pasos', 'materiales', 'condiciones', 'datos', 'aporte', 'fuentes'] as $panel)
        <section id="guia-panel-{{ $panel }}" role="tabpanel" aria-labelledby="guia-tab-{{ $panel }}" tabindex="0" x-show="tab === '{{ $panel }}'" x-cloak class="rm-guia__panel">
            @switch($panel)
            @case('resumen')
                <dl class="rm-guia__ficha"><div><dt>Edición / referencia</dt><dd>{{ $guia['version'] }}</dd></div><div><dt>Modalidad</dt><dd>{{ $guia['modalidad'] }}</dd></div><div><dt>Estado real</dt><dd>{{ $guia['estado_catalogo'] }}</dd></div><div><dt>Qué puede aportar</dt><dd>{{ $guia['aporte'] }}</dd></div></dl>
                <div class="rm-guia__invitacion"><i class="ph-bold ph-path" aria-hidden="true"></i><div><h4>Un recorrido para orientarte</h4><p>Revisa el contexto, la fuente, los datos originales y la confirmación. Explora los campos sin introducir información del residente.</p></div></div>
                <div class="rm-guia__atajos"><button type="button" class="rm-btn rm-btn-primary" @click="seleccionar('pasos'); $nextTick(() => $refs.tabPasos.focus())"><i class="ph-bold ph-play" aria-hidden="true"></i>Empezar el recorrido</button><button type="button" class="rm-btn rm-btn-secondary" @click="seleccionar('datos'); $nextTick(() => $refs.tabDatos.focus())">Explorar los campos</button></div><p>{{ $guia['limite'] }}</p>
                @break
            @case('pasos')
                <p>Preparación y registro documental. La aplicación específica requiere el manual autorizado.</p>
                <ol class="rm-guia__progreso" aria-label="Pasos de preparación">@foreach($guia['pasos'] as $i => $paso)<li :aria-current="paso === {{ $i }} ? 'step' : null"><button type="button" @click="irPaso({{ $i }}, {{ count($guia['pasos']) }})" :aria-pressed="paso === {{ $i }}"><span class="rm-guia__numero" x-text="revisados.includes({{ $i }}) ? '✓' : '{{ $i + 1 }}'" aria-hidden="true"></span><span>{{ $paso['titulo'] }}</span><span class="sr-only" x-show="revisados.includes({{ $i }})">Revisado en esta visita</span></button></li>@endforeach</ol>
                <div aria-live="polite" aria-atomic="true">
                @foreach($guia['pasos'] as $i => $paso)
                    <article x-show="paso === {{ $i }}" x-cloak class="rm-guia__escena"><div class="rm-guia__ilustracion" aria-hidden="true"><x-ui.escena-guia :paso="$i" /><span>{{ $i + 1 }}</span></div><div><p class="rm-explorador__eyebrow">Paso {{ $i + 1 }} de {{ count($guia['pasos']) }}</p><h4>{{ $paso['titulo'] }}</h4><dl><dt>Qué preparar</dt><dd>{{ $paso['preparar'] }}</dd><dt>Con el residente</dt><dd>{{ $paso['interaccion'] }}</dd><dt>Dato original</dt><dd>{{ $paso['dato'] }}</dd></dl><p class="rm-guia__precaucion">{{ $paso['precaucion'] }}</p></div></article>
                @endforeach
                </div>
                <div class="rm-guia__revision"><button type="button" class="rm-btn rm-btn-secondary" :aria-pressed="revisados.includes(paso)" @click="revisarPaso()"><i class="ph-bold" :class="revisados.includes(paso) ? 'ph-check-square' : 'ph-square'" aria-hidden="true"></i><span x-text="revisados.includes(paso) ? 'Paso revisado en esta visita' : 'Marcar paso como revisado'"></span></button><span x-text="revisados.length + ' de {{ count($guia['pasos']) }} pasos revisados'"></span><progress :value="revisados.length" max="{{ count($guia['pasos']) }}" aria-label="Pasos de la guía revisados"></progress><small>Revisión pedagógica temporal. No guarda ni valida datos clínicos.</small></div>
                <div class="rm-guia__navegacion"><button type="button" class="rm-btn rm-btn-secondary" @click="mover(-1, {{ count($guia['pasos']) }})" :disabled="paso === 0">Anterior</button><span x-text="'Paso ' + (paso + 1) + ' de {{ count($guia['pasos']) }}'"></span><button type="button" class="rm-btn rm-btn-primary" @click="mover(1, {{ count($guia['pasos']) }})" :disabled="paso === {{ count($guia['pasos']) - 1 }}">Siguiente</button></div>
                @break
            @case('materiales')<h4>Materiales y edición</h4><p>{{ $guia['materiales'] }}</p>@break
            @case('condiciones')<h4>Antes de comenzar</h4><p>{{ $guia['precaucion'] }}</p><p>Confirma autoría, fecha clínica, contexto y requisitos del instrumento. No registres condiciones no comprobadas.</p>@break
            @case('datos')<h4>Datos originales</h4><p>El catálogo vigente determina las preguntas y el tipo de respuesta. Se conserva residente, profesional propio, fecha clínica y fuente original cuando corresponde.</p>@if($guia['entradas'])<p>Entradas técnicas del método candidato; no son un cuestionario clínico:</p><ul>@foreach($guia['entradas'] as $entrada)<li>{{ str_replace('_', ' ', $entrada) }}</li>@endforeach</ul>@else<p>Consulta las preguntas vigentes dentro de la captura autorizada. No se crean preguntas desde esta guía.</p>@endif @break
            @case('aporte')<h4>Qué información puede aportar</h4><p>{{ $guia['aporte'] }}.</p><h4>Qué no demuestra</h4><p>{{ $guia['limite'] }}</p><h4>Procedencia e interpretación</h4><p>{{ $guia['relacion'] }}</p>@break
            @case('fuentes')<h4>Origen y estado de la guía</h4><dl><dt>Fuente</dt><dd>{{ $guia['fuente'] }}</dd><dt>Versión de la guía</dt><dd>{{ $guia['version_guia'] }}</dd><dt>Estado</dt><dd>{{ $guia['validacion'] }}</dd></dl>@if($guia['url'])<a class="rm-btn rm-btn-secondary" href="{{ $guia['url'] }}" target="_blank" rel="noopener noreferrer">Consultar fuente publicada</a>@endif<p>Diccionario de adquisición, revisión 19/10/2026; contrato institucional de Petersen, versión 1.0 del 21/10/2026. Guía de aplicación clínica pendiente de validación por el responsable.</p>@break
            @endswitch
        </section>
        @endforeach
        <section class="rm-guia__preview" x-show="tab === 'pasos' || tab === 'datos'" x-cloak aria-labelledby="guia-preview-titulo">
            <div class="rm-guia__preview-cabecera"><div><p class="rm-explorador__eyebrow">Vista previa explicada · sin captura</p><h4 id="guia-preview-titulo">¿Qué se conserva en cada campo?</h4></div><i class="ph-bold ph-cursor-click" aria-hidden="true"></i></div>
            <div class="rm-guia__campos" role="group" aria-label="Campos de la captura, solo explicación">
                @foreach($guia['campos_preview'] as $i => $campo)
                    <button type="button" :aria-pressed="campo === {{ $i }}" @click="elegirCampo({{ $i }})" aria-controls="guia-detalle-campo"><i class="ph-bold {{ $campo['icono'] }}" aria-hidden="true"></i><span>{{ $campo['nombre'] }}</span><i class="ph-bold ph-caret-right" aria-hidden="true"></i></button>
                @endforeach
            </div>
            <div id="guia-detalle-campo" class="rm-guia__campo-detalle" aria-live="polite" aria-atomic="true">
                @foreach($guia['campos_preview'] as $i => $campo)
                    <article x-show="campo === {{ $i }}" x-cloak><h5>{{ $campo['nombre'] }}</h5><p>{{ $campo['ayuda'] }}</p><dl><dt>De dónde procede</dt><dd>{{ $campo['origen'] }}</dd><dt>Qué debes tener presente</dt><dd>{{ $campo['limite'] }}</dd></dl><details><summary>Ver correspondencia técnica del formulario</summary><p>{{ $campo['campos'] }}</p></details></article>
                @endforeach
            </div>
        </section>
    </div>
    <x-slot:footer><button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarGuia">Volver al explorador</button></x-slot:footer>
</x-ui.modal-livewire>
@endif
