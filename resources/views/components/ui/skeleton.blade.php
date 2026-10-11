@props(['variant' => 'text', 'label' => 'Cargando contenido'])
@php $shape = in_array($variant, ['text', 'circle', 'card'], true) ? $variant : 'text'; @endphp
<span {{ $attributes->class(['rm-skeleton rm-skeleton-'.$shape]) }} role="status" aria-label="{{ $label }}"><span class="sr-only">{{ $label }}</span></span>
