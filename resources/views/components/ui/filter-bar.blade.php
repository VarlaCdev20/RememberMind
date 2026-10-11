{{-- Barra canónica de filtros. Cada pantalla conserva sus propios campos y datos. --}}
@props([
    'as' => 'section',
])

<{{ $as }} {{ $attributes->class(['rm-filter-bar', 'rm-collection-toolbar']) }}>
    {{ $slot }}
</{{ $as }}>
