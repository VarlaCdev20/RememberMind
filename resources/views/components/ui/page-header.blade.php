{{--
 Componente: ui/page-header
 Contrato V2 Canónico:
  - title / titulo
  - subtitle / subtitulo
  - eyebrow / overline
  - icon / icono
  - tone / color
  - context (pill descriptivo / badge a la derecha)
  - date (fecha traducida institucional)
  - actions / slot (botones interactivos)
--}}
@props([
    'titulo' => null,
    'subtitulo' => null,
    'title' => null,
    'subtitle' => null,
    'overline' => null,
    'eyebrow' => null,
    'icono' => null,
    'icon' => null,
    'color' => 'bg-[var(--rm-action-primary)]',
    'tone' => null,
    'context' => null,
    'date' => null,
    'actions' => null,
])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedSubtitle = $subtitle ?? $subtitulo;
    $resolvedIcon = $icon ?? $icono ?: 'ph-squares-four';
    $resolvedEyebrow = $eyebrow ?? $overline;
    $resolvedTone = $tone ?? (str_contains($color, 'danger') ? 'danger' : 'neutral');
    $resolvedActions = $actions ?? $slot;
@endphp

<header {{ $attributes->class(['rm-collection-header', 'rm-page-header']) }} data-tone="{{ $resolvedTone }}">
    <div class="rm-collection-header__identity">
        <span class="rm-collection-header__icon" aria-hidden="true"><i class="ph-bold {{ $resolvedIcon }}"></i></span>
        <div class="rm-collection-header__copy">
            @if($resolvedEyebrow)<p class="rm-collection-header__eyebrow">{{ $resolvedEyebrow }}</p>@endif
            <h1>{{ $resolvedTitle }}</h1>
            @if($resolvedSubtitle)<p class="rm-collection-header__subtitle">{{ $resolvedSubtitle }}</p>@endif
        </div>
    </div>
    @if($context || $date || ($resolvedActions && trim((string) $resolvedActions) !== ''))
        <div class="rm-collection-header__aside">
            @if($context)<span class="rm-collection-header__context">{{ $context }}</span>@endif
            @if($date)<time datetime="{{ $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date }}">{{ $date instanceof \DateTimeInterface ? \Illuminate\Support\Carbon::instance($date)->translatedFormat('d \d\e F \d\e Y') : $date }}</time>@endif
            @if($resolvedActions && trim((string) $resolvedActions) !== '')<div class="rm-collection-header__actions">{{ $resolvedActions }}</div>@endif
        </div>
    @endif
</header>
