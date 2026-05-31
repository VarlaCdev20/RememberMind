@props([
    'icon' => 'ph-chart-bar',
    'title' => 'Título',
    'value' => '0',
    'subtitle' => 'Descripción',
    'color' => 'terracota',
])

@php
    $styles = [
        'terracota' => 'text-boton-acento bg-boton-acento/10',
        'azul' => 'text-titulo bg-azul-clinico/25',
        'verde' => 'text-estado-exito bg-estado-exitoBg',
        'morado' => 'text-morado-cog bg-morado-cog/15',
    ];

    $style = $styles[$color] ?? $styles['terracota'];
@endphp

<div class="group rounded-[2.5rem] border border-borde bg-fondo-panel p-6 shadow-lg backdrop-blur-xl transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_25px_50px_rgba(47,62,92,0.22)]">
    <div class="mb-5 flex items-center justify-between">
        <div class="flex h-14 w-14 items-center justify-center rounded-2xl shadow-inner transition-all duration-500 group-hover:rotate-6 group-hover:rounded-full {{ $style }}">
            <i class="ph-fill {{ $icon }} text-3xl"></i>
        </div>

        <i class="ph-bold ph-arrow-up-right text-xl text-titulo/25 transition group-hover:text-boton-acento"></i>
    </div>

    <p class="text-sm font-black uppercase tracking-widest text-meta">
        {{ $title }}
    </p>

    <h3 class="mt-2 text-4xl font-black text-titulo">
        {{ $value }}
    </h3>

    <p class="mt-2 text-sm font-bold text-apoyo">
        {{ $subtitle }}
    </p>
</div>