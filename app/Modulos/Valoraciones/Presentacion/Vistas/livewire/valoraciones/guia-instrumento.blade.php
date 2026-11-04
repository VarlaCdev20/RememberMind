@if($guia)
<x-ui.modal-livewire id="guia-instrumento" wire:model="mostrarGuia" :title="'Guía · '.$guia['nombre']" subtitle="Orientación de registro y consulta de fuentes" max-width="xl" close-method="cerrarGuia" :show-validation="false" class="rm-guia-modal">
    <div x-data="rmGuiaVisual()" wire:key="guia-contenido-{{ $guia['id'] }}" class="rm-guia">
        <p class="rm-guia__precaucion" role="note"><i class="ph-bold ph-info" aria-hidden="true"></i>{{ $guia['precaucion'] }}</p>
        <nav class="rm-guia__tabs" role="tablist" aria-label="Contenido de la guía" @keydown="teclado($event)">
            @foreach(['resumen' => 'Resumen', 'pasos' => 'Cómo se aplica', 'materiales' => 'Materiales', 'condiciones' => 'Condiciones y precauciones', 'datos' => 'Datos que se registran', 'aporte' => 'Qué información aporta', 'fuentes' => 'Fuentes'] as $id => $titulo)
                <button type="button" id="guia-tab-{{ $id }}" role="tab" :aria-selected="tab === '{{ $id }}'" :tabindex="tab === '{{ $id }}' ? 0 : -1" aria-controls="guia-panel-{{ $id }}" @click="seleccionar('{{ $id }}')">{{ $titulo }}</button>
            @endforeach
        </nav>
        @foreach(['resumen', 'pasos', 'materiales', 'condiciones', 'datos', 'aporte', 'fuentes'] as $panel)
        <section id="guia-panel-{{ $panel }}" role="tabpanel" aria-labelledby="guia-tab-{{ $panel }}" tabindex="0" x-show="tab === '{{ $panel }}'" x-cloak class="rm-guia__panel">
            @switch($panel)
            @case('resumen')
                <x-ui.avatar-guia estado="explicacion" :mensaje="$guia['descripcion']" />
                <h4>{{ $guia['nombre'] }}</h4><p>{{ $guia['codigo'] }} · {{ $guia['version'] }}</p><p>{{ $guia['modalidad'] }} · {{ $guia['estado_catalogo'] }}</p><p>{{ $guia['limite'] }}</p>
                @break
            @case('pasos')
                <p>Preparación y registro documental. La aplicación específica requiere el manual autorizado.</p>
                <ol class="rm-guia__progreso" aria-label="Pasos de preparación">@foreach($guia['pasos'] as $i => $paso)<li :aria-current="paso === {{ $i }} ? 'step' : null"><button type="button" @click="paso = {{ $i }}" :aria-label="'Paso {{ $i + 1 }}: {{ $paso['titulo'] }}'" :aria-pressed="paso === {{ $i }}">{{ $i + 1 }}</button></li>@endforeach</ol>
                <div aria-live="polite" aria-atomic="true">
                @foreach($guia['pasos'] as $i => $paso)
                    <article x-show="paso === {{ $i }}" x-cloak class="rm-guia__escena"><div class="rm-guia__ilustracion" aria-hidden="true"><x-ui.escena-guia :paso="$i" /><span>{{ $i + 1 }}</span></div><div><p class="rm-explorador__eyebrow">Paso {{ $i + 1 }} de {{ count($guia['pasos']) }}</p><h4>{{ $paso['titulo'] }}</h4><dl><dt>Qué preparar</dt><dd>{{ $paso['preparar'] }}</dd><dt>Con el residente</dt><dd>{{ $paso['interaccion'] }}</dd><dt>Dato original</dt><dd>{{ $paso['dato'] }}</dd></dl><p class="rm-guia__precaucion">{{ $paso['precaucion'] }}</p></div></article>
                @endforeach
                </div>
                <div class="rm-guia__navegacion"><button type="button" class="rm-btn rm-btn-secondary" @click="mover(-1, {{ count($guia['pasos']) }})" :disabled="paso === 0">Anterior</button><span x-text="'Paso ' + (paso + 1) + ' de {{ count($guia['pasos']) }}'"></span><button type="button" class="rm-btn rm-btn-primary" @click="mover(1, {{ count($guia['pasos']) }})" :disabled="paso === {{ count($guia['pasos']) - 1 }}">Siguiente</button></div>
                @break
            @case('materiales')<h4>Materiales y edición</h4><p>{{ $guia['materiales'] }}</p>@break
            @case('condiciones')<x-ui.avatar-guia estado="atencion" mensaje="Revisa fuente, edición y condiciones. Una ilustración no constituye un protocolo de aplicación." /><h4>Antes de comenzar</h4><p>{{ $guia['precaucion'] }}</p><p>Confirma autoría, fecha clínica, contexto y requisitos del instrumento. No registres condiciones no comprobadas.</p>@break
            @case('datos')<h4>Datos originales</h4><p>El catálogo vigente determina las preguntas y el tipo de respuesta. Se conserva residente, profesional propio, fecha clínica y fuente original cuando corresponde.</p>@if($guia['entradas'])<p>Entradas técnicas del método candidato; no son un cuestionario clínico:</p><ul>@foreach($guia['entradas'] as $entrada)<li>{{ str_replace('_', ' ', $entrada) }}</li>@endforeach</ul>@else<p>Consulta las preguntas vigentes dentro de la captura autorizada. No se crean preguntas desde esta guía.</p>@endif @break
            @case('aporte')<x-ui.avatar-guia estado="empatia" mensaje="Si falta evidencia, es válido dejar constancia de lo que aún no se puede interpretar." /><h4>Qué información puede aportar</h4><p>{{ $guia['aporte'] }}.</p><h4>Qué no demuestra</h4><p>{{ $guia['limite'] }}</p><h4>Procedencia e interpretación</h4><p>{{ $guia['relacion'] }}</p>@break
            @case('fuentes')<h4>Origen y estado de la guía</h4><dl><dt>Fuente</dt><dd>{{ $guia['fuente'] }}</dd><dt>Versión de la guía</dt><dd>{{ $guia['version_guia'] }}</dd><dt>Estado</dt><dd>{{ $guia['validacion'] }}</dd></dl>@if($guia['url'])<a class="rm-btn rm-btn-secondary" href="{{ $guia['url'] }}" target="_blank" rel="noopener noreferrer">Consultar fuente publicada</a>@endif<p>Diccionario de adquisición, revisión 19/10/2026; contrato institucional de Petersen, versión 1.0 del 21/10/2026. Guía de aplicación clínica pendiente de validación por el responsable.</p>@break
            @endswitch
        </section>
        @endforeach
    </div>
    <x-slot:footer><button type="button" class="rm-btn rm-btn-secondary" wire:click="cerrarGuia">Volver al explorador</button></x-slot:footer>
</x-ui.modal-livewire>
@endif
