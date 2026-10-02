@props(['etiqueta', 'valor', 'icono' => null, 'href' => null])
<article {{ $attributes->class(['rm-card-metric']) }}>
    <div class="rm-metric-top">
        <span class="rm-metric-label">{{ $etiqueta }}</span>
        @if($icono)<i class="ph {{ $icono }}" aria-hidden="true"></i>@endif
    </div>
    <strong class="rm-metric-value">{{ $valor }}</strong>
    @if($slot->isNotEmpty())<div class="rm-metric-context">{{ $slot }}</div>@endif
    @if($href)<a class="rm-inline-link rm-metric-action" href="{{ $href }}">Ver detalle</a>@endif
</article>
