@props([
    'title',
    'subtitle' => null,
    'icon' => 'ph-squares-four',
    'eyebrow' => null,
    'tone' => 'neutral',
    'context' => null,
    'date' => null,
])

<header {{ $attributes->class(['rm-collection-header']) }} data-tone="{{ $tone }}">
    <div class="rm-collection-header__identity">
        <span class="rm-collection-header__icon" aria-hidden="true"><i class="ph-bold {{ $icon }}"></i></span>
        <div class="rm-collection-header__copy">
            @if($eyebrow)<p class="rm-collection-header__eyebrow">{{ $eyebrow }}</p>@endif
            <h1>{{ $title }}</h1>
            @if($subtitle)<p class="rm-collection-header__subtitle">{{ $subtitle }}</p>@endif
        </div>
    </div>
    @if($context || $date || isset($actions))
        <div class="rm-collection-header__aside">
            @if($context)<span class="rm-collection-header__context">{{ $context }}</span>@endif
            @if($date)<time datetime="{{ $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date }}">{{ $date instanceof \DateTimeInterface ? \Illuminate\Support\Carbon::instance($date)->translatedFormat('d \d\e F \d\e Y') : $date }}</time>@endif
            @if(isset($actions))<div class="rm-collection-header__actions">{{ $actions }}</div>@endif
        </div>
    @endif
</header>
