{{--
 Componente: ui/page-header
 Contrato V2: overline opcional, title (Nunito Sans 32/800), subtitle (body/secondary),
 icon opcional (no forzado), actions slot.
--}}
@props([
    'titulo' => null,
    'subtitulo' => null,
    'title' => null,
    'subtitle' => null,
    'overline' => null,
    'icono' => null,
    'icon' => null,
    'color' => 'bg-[var(--rm-action-primary)]',
    'actions' => null,
])

@php
    $resolvedTitle = $title ?? $titulo;
    $resolvedSubtitle = $subtitle ?? $subtitulo;
    $resolvedIcon = $icon ?? $icono;
    $resolvedActions = $actions ?? $slot;
@endphp

<x-ui.collection-header
    class="rm-page-header"
    :title="$resolvedTitle"
    :subtitle="$resolvedSubtitle"
    :icon="$resolvedIcon ?: 'ph-squares-four'"
    :eyebrow="$overline"
    :tone="str_contains($color, 'danger') ? 'danger' : 'neutral'"
>
    @if($resolvedActions && trim($resolvedActions) !== '')
        <x-slot:actions>{{ $resolvedActions }}</x-slot:actions>
    @endif
</x-ui.collection-header>
