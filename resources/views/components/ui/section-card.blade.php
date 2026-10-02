{{--
 Componente: ui/section-card
 Contrato V2: title, subtitle opcional, actions opcional, body (slot), footer opcional.
 Agnóstico de dominio, utiliza tokens de superficie, borde y tipografía V2.
--}}
@props([
    'titulo' => null,
    'subtitulo' => null,
    'title' => null,
    'subtitle' => null,
    'icono' => null,
    'icon' => null,
    'acciones' => null,
    'actions' => null,
    'footer' => null,
    'variant' => 'default',
    'count' => null,
    'interactive' => false,
])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedSubtitle = $subtitle ?? $subtitulo;
    $resolvedIcon = $icon ?? $icono;
    $resolvedActions = $actions ?? $acciones;
@endphp

<section {{ $attributes->class(['rm-card rm-surface--'.(in_array($variant, ['default', 'soft', 'mint', 'sky', 'coral'], true) ? $variant : 'default'), 'rm-card--interactive' => $interactive]) }}>
    @if($resolvedTitle || $resolvedIcon || $resolvedActions)
        <x-ui.section-header class="rm-card__section-header" :title="$resolvedTitle ?? ''" :subtitle="$resolvedSubtitle" :icon="$resolvedIcon" :count="$count" :actions="$resolvedActions" />
    @endif

    <div class="rm-card-content flex-1">
        {{ $slot }}
    </div>

    @if(isset($footer) && $footer)
        <footer class="mt-4 pt-3 border-t border-[var(--rm-border-soft)] flex items-center justify-between gap-3 text-xs text-[var(--rm-text-secondary)]">
            {{ $footer }}
        </footer>
    @endif
</section>
