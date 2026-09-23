{{--
 Componente: ui/page-header
 Uso: <x-ui.page-header titulo="Título" subtitulo="Descripción" icono="ph-users">
 Slot opcional con botones de acción
 </x-ui.page-header>
--}}
@props([
    'titulo' => '',
    'subtitulo' => '',
    'icono' => 'ph-squares-four',
    'color' => 'bg-[#A35A44]', // Terracota canónico de acción
])

<header class="rm-page-header mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex items-center gap-3.5">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $color }} text-white shadow-sm transition-transform duration-200 hover:scale-105">
            <i class="ph-bold {{ $icono }} text-xl"></i>
        </span>
        <div class="rm-page-title-group min-w-0">
            <h1 class="text-xl sm:text-2xl font-black text-[#304060] dark:text-[#F8F2EC] leading-tight tracking-tight">
                {{ $titulo }}
            </h1>
            @if($subtitulo)
                <p class="text-xs sm:text-sm font-semibold text-[#677084] dark:text-[#B8ADA2] mt-0.5 leading-snug">
                    {{ $subtitulo }}
                </p>
            @endif
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="flex shrink-0 flex-wrap items-center gap-2.5">
            {{ $slot }}
        </div>
    @endif
</header>
