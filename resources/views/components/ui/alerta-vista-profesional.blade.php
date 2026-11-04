@props(['vista'])
<section class="rm-case-view rm-case-view--{{ $vista['tipo'] }}" data-case-view="{{ $vista['tipo'] }}" aria-label="{{ $vista['titulo'] }}">
    <header class="rm-case-view__heading"><p class="rm-help">{{ $vista['conCaso'] ? 'Registros del caso seleccionado' : 'Estructura del área · sin caso seleccionado' }}</p><h3>{{ $vista['titulo'] }}</h3><p>{{ $vista['pregunta'] }}</p></header>
    @if($vista['contexto'])
        <section aria-label="Procedencia del contexto profesional"><p class="rm-help">Contexto vigente del residente; no acredita el origen de la alerta.</p><x-ui.alerta-dato-consultable :nodo="$vista['contexto']" :compacto="true" /><div class="rm-case-context"><x-ui.alerta-dato-consultable :nodo="$vista['autor']" :compacto="true" /><x-ui.alerta-dato-consultable :nodo="$vista['atencion']" vacio="Este registro no presenta una atención vinculada consultable." /></div></section>
    @endif
    @switch($vista['tipo'])
        @case('cronologia')
            <section class="rm-case-view__source"><h4>{{ $vista['etapas'][0] }}</h4>@forelse($vista['origen'] as $nodo)<x-ui.alerta-dato-consultable :nodo="$nodo" />@empty<x-ui.alerta-dato-consultable vacio="No consta un registro de origen consultable." />@endforelse</section>
            <h4>{{ $vista['etapas'][1] }}</h4><ol class="rm-case-timeline">@forelse($vista['eventos'] as $nodo)<li><span class="rm-case-timeline__mark"><i class="ph-bold ph-clock-counter-clockwise" aria-hidden="true"></i></span><x-ui.alerta-dato-consultable :nodo="$nodo" /></li>@empty<li><p>Sin eventos guardados para mostrar en esta secuencia.</p></li>@endforelse</ol>
            <footer><h4>{{ $vista['etapas'][2] }}</h4><x-ui.alerta-dato-consultable :nodo="$vista['responsable']" vacio="No consta responsable del seguimiento." /></footer>
            @break
        @case('contraste')
            <div class="rm-case-evidence"><section><h4><i class="ph-bold ph-first-aid" aria-hidden="true"></i>{{ $vista['etapas'][0] }}</h4><p class="rm-help">Registro referenciado, si puede verificarse.</p>@forelse($vista['origen'] as $nodo)<x-ui.alerta-dato-consultable :nodo="$nodo" />@empty<x-ui.alerta-dato-consultable vacio="Sin fuente de origen consultable." />@endforelse</section><section><h4><i class="ph-bold ph-stethoscope" aria-hidden="true"></i>{{ $vista['etapas'][1] }}</h4><p class="rm-help">Contexto del residente; no se presenta como causa del aviso.</p><x-ui.alerta-dato-consultable :nodo="$vista['contexto']" />@foreach($vista['secciones'] as $nodo)<x-ui.alerta-dato-consultable :nodo="$nodo" />@endforeach</section></div>
            @break
        @case('panel-nutricional')
            <p class="rm-help">Valoración profesional guardada. El requerimiento hídrico está en mL por día: no equivale a consumo registrado ni a un balance calculado.</p>
            <div class="rm-case-nutrition">@foreach($vista['etapas'] as $indice=>$titulo)<section data-nutrition-section="{{ $indice }}"><header><i class="ph-bold {{ ['ph-bowl-food','ph-drop','ph-notebook'][$indice] }}" aria-hidden="true"></i><h4>{{ $titulo }}</h4></header><x-ui.alerta-dato-consultable :nodo="$vista['secciones'][$indice] ?? null" vacio="Sin valoración nutricional vinculada consultable." /></section>@endforeach</div>
            @break
        @case('matriz')
            <p class="rm-help">Descripciones guardadas, sin convertirlas en puntuaciones ni grados calculados.</p>
            @foreach($vista['etapas'] as $indice=>$titulo)<section class="rm-case-capacity"><h4><i class="ph-bold {{ ['ph-person-simple-walk','ph-hand-heart','ph-target'][$indice] }}" aria-hidden="true"></i>{{ $titulo }}</h4>@if(isset($vista['secciones'][$indice]))<table class="rm-case-capacity__matrix"><caption>Datos de {{ mb_strtolower($titulo) }}</caption><thead><tr><th scope="col">Aspecto valorado</th><th scope="col">Registro profesional</th></tr></thead><tbody>@foreach($vista['secciones'][$indice]['detalles'] ?? [] as $campo)<tr><th scope="row">{{ $campo['etiqueta'] }}</th><td>{{ $campo['valor'] }}</td></tr>@endforeach</tbody></table><button type="button" class="rm-btn rm-btn-secondary" @click="consultarElemento(@js($vista['secciones'][$indice]['id']))">Consultar procedencia de este bloque</button>@else<p class="rm-case-data__empty">Sin valoración funcional vinculada consultable.</p>@endif</section>@endforeach
            @break
        @case('recorrido')
            <p class="rm-help">Recorrido de lectura de un seguimiento guardado. Su posición no indica que se haya realizado una actividad nueva.</p>
            <ol class="rm-case-activity">@foreach($vista['etapas'] as $indice=>$titulo)<li><span class="rm-case-activity__step"><i class="ph-bold {{ $indice === 0 ? 'ph-book-open' : 'ph-chart-line' }}" aria-hidden="true"></i></span><section><h4>{{ $titulo }}</h4><x-ui.alerta-dato-consultable :nodo="$vista['secciones'][$indice] ?? null" vacio="Sin seguimiento pedagógico vinculado consultable." /></section></li>@endforeach</ol>
            @break
        @case('tablero')
            <p class="rm-help">Un caso ocupa únicamente su situación guardada. Las demás columnas no representan pasos completados.</p>
            <div class="rm-case-board">@foreach($vista['etapas'] as $indice=>$titulo)<section class="rm-case-board__column" data-current-state="{{ $vista['conCaso'] && $vista['columna'] === $indice ? 'true' : 'false' }}"><h4>{{ $titulo }}</h4>@if($vista['conCaso'] && $vista['columna'] === $indice)<x-ui.alerta-dato-consultable :nodo="$vista['estado']" /><x-ui.alerta-dato-consultable :nodo="$vista['responsable']" :compacto="true" />@else<p class="rm-help">{{ $vista['conCaso'] ? 'El caso no está en esta situación.' : 'Sin caso seleccionado.' }}</p>@endif</section>@endforeach</div>
            @break
        @case('arbol')
            <div class="rm-case-responsibility"><section class="rm-case-responsibility__owner"><h4>{{ $vista['etapas'][0] }}</h4><x-ui.alerta-dato-consultable :nodo="$vista['responsable']" /></section><div class="rm-case-responsibility__link" aria-hidden="true"><i class="ph-bold ph-arrow-down" aria-hidden="true"></i></div><section class="rm-case-responsibility__case"><h4>{{ $vista['etapas'][1] }}</h4><x-ui.alerta-dato-consultable :nodo="$vista['caso']" /></section><h4>{{ $vista['etapas'][2] }}</h4><div class="rm-case-responsibility__branches">@forelse($vista['especialidades'] as $nodo)<x-ui.alerta-dato-consultable :nodo="$nodo" />@empty<p>Sin vínculos profesionales consultables.</p>@endforelse</div></div>
            @break
        @case('procedencia')
            <ol class="rm-case-provenance"><li><h4>{{ $vista['etapas'][0] }}</h4><x-ui.alerta-dato-consultable :nodo="$vista['caso']" /></li><li><h4>{{ $vista['etapas'][1] }}</h4>@forelse($vista['origen'] as $nodo)<x-ui.alerta-dato-consultable :nodo="$nodo" />@empty<p>No consta fuente consultable. El nombre del módulo por sí solo no acredita su procedencia.</p>@endforelse</li><li><h4>{{ $vista['etapas'][2] }}</h4><x-ui.alerta-dato-consultable :nodo="$vista['responsable']" :compacto="true" />@forelse($vista['eventos'] as $nodo)<x-ui.alerta-dato-consultable :nodo="$nodo" />@empty<p>No hay eventos guardados en la lista reciente.</p>@endforelse</li></ol>
            @break
        @case('red')
            <div class="rm-case-network-guide"><span><i class="ph-bold ph-brain" aria-hidden="true"></i>Persona y contexto</span>@foreach($vista['etapas'] as $etapa)<span>{{ $etapa }}</span>@endforeach</div><p class="rm-help">Los vínculos se exploran en la red ajustable del caso. La existencia de una observación no constituye un diagnóstico.</p>
            @break
    @endswitch
    @unless($vista['conCaso'])<p class="rm-help">Esta estructura explica el uso del área. No contiene registros de residentes ni confirma solicitudes o actuaciones.</p>@endunless
</section>
