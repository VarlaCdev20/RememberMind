@props(['nodo' => null, 'vacio' => 'No hay un registro consultable para este apartado.', 'compacto' => false])
@if($nodo)
<article class="rm-case-data" data-case-record="{{ $nodo['id'] }}">
    <button type="button" class="rm-case-data__select" @click="consultarElemento(@js($nodo['id']))" :aria-pressed="seleccionado === @js($nodo['id'])"><i class="ph-bold" :class="icono(@js($nodo))" aria-hidden="true"></i><strong>{{ $nodo['label'] }}</strong><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></button>
    <p>{{ $nodo['texto'] }}</p>
    @unless($compacto)<dl class="rm-case-data__fields">@foreach($nodo['detalles'] ?? [] as $campo)<div><dt>{{ $campo['etiqueta'] }}</dt><dd>{{ $campo['valor'] }}</dd></div>@endforeach</dl>@endunless
</article>
@else
<p class="rm-case-data__empty">{{ $vacio }}</p>
@endif
