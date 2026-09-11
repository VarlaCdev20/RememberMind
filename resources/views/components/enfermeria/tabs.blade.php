@props([
    'activo' => null,
])

<nav {{ $attributes->merge(['class' => 'enf-nav-tabs shadow-sm', 'aria-label' => 'Pestañas de navegación']) }}>
    {{ $slot }}
</nav>
