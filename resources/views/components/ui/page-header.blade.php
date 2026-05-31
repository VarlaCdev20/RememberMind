{{--
    Componente: ui/page-header
    Uso: <x-ui.page-header titulo="Título" subtitulo="Descripción" icono="ph-users">
             Slot opcional con botones de acción
         </x-ui.page-header>
--}}
@props([
    'titulo'    => '',
    'subtitulo' => '',
    'icono'     => 'ph-squares-four',
    'color'     => 'bg-azul-profundo',   // clase bg para el ícono
])

<div class="rm-page-header">
    <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $color }} shadow-sm">
            <i class="ph-bold {{ $icono }} text-lg text-white"></i>
        </div>
        <div>
            <h1 class="rm-section-title">{{ $titulo }}</h1>
            @if($subtitulo)
                <p class="rm-section-subtitle">{{ $subtitulo }}</p>
            @endif
        </div>
    </div>

    @if($slot->isNotEmpty())
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
