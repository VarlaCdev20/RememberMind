@props([
    'titulo' => null,
    'subtitulo' => null,
    'icono' => null,
    'iconoColor' => 'text-[#E58C6C]',
    'interactiva' => false,
])

<div {{ $attributes->merge(['class' => ($interactiva ? 'enf-card-interactive' : 'enf-card') . ' overflow-hidden']) }}>
    @if($titulo || isset($header) || $icono)
        <div class="enf-card-header">
            <div class="flex items-center gap-3">
                @if($icono)
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#E58C6C]/10 {{ $iconoColor }}">
                        <i class="ph-bold {{ $icono }} text-base"></i>
                    </div>
                @endif
                <div>
                    @if($titulo)
                        <h3 class="enf-title text-sm md:text-base">{{ $titulo }}</h3>
                    @endif
                    @if($subtitulo)
                        <p class="enf-subtitle text-xs mt-0.5">{{ $subtitulo }}</p>
                    @endif
                </div>
            </div>

            @if(isset($header))
                <div class="flex items-center gap-2">
                    {{ $header }}
                </div>
            @endif
        </div>
    @endif

    <div class="enf-card-body">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="enf-card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
