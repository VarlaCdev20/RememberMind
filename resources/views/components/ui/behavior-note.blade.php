@props(['note', 'href' => null])
<li class="rm-behavior-note">
    <span class="rm-behavior-note__avatar" aria-hidden="true">@if($note['avatar'])<img src="{{ asset($note['avatar']) }}" alt="" loading="lazy" decoding="async">@else{{ $note['initials'] }}@endif</span>
    <div class="rm-behavior-note__body">
        <strong>{{ $note['patient'] }}</strong>
        <time datetime="{{ $note['datetime'] }}">{{ $note['time'] }}</time>
        @if($note['mood'])<span class="rm-behavior-note__mood">{{ $note['mood'] }}</span>@endif
        @if($note['change'])<small>Cambio de conducta observado</small>@endif
        @if($note['text'])<p class="{{ $href ? 'rm-behavior-note__excerpt' : '' }}">{{ $note['text'] }}</p>@endif
        @if($href)<a href="{{ $href }}" class="rm-nursing-module__more" aria-label="Ver seguimiento de {{ $note['patient'] }}">Ver seguimiento <i class="ph-bold ph-arrow-right" aria-hidden="true"></i></a>@endif
    </div>
</li>
