@props(['variant' => 'default', 'interactive' => false, 'spacious' => false])
@php
    $surfaceVariant = in_array($variant, ['default', 'soft', 'mint', 'sky', 'coral'], true) ? $variant : 'default';
@endphp
<section {{ $attributes->class([
    'rm-card rm-surface--'.$surfaceVariant,
    'rm-card--interactive' => $interactive,
    'rm-card--spacious' => $spacious,
]) }}>{{ $slot }}</section>
