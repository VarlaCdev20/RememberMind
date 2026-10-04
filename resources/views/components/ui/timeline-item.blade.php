@props(['fecha', 'hora', 'tipo', 'dato'])
<li class="rm-resident-summary__timeline-item">
    <time>{{ $fecha }} · {{ $hora }}</time>
    <div><strong>{{ $tipo }}</strong><p>{{ $dato }}</p></div>
</li>
