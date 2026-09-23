{{--
 Componente: ui/section-card
 Uso: <x-ui.section-card titulo="Datos del turno"> ... contenido ... </x-ui.section-card>
--}}
@props([
    'titulo' => null,
    'subtitulo' => null,
    'icono' => null,
    'acciones' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-[#D5CABE] dark:border-[#51483F] bg-[#F0E8DE] dark:bg-[#201E1C] p-4 sm:p-5 shadow-[0_6px_18px_rgba(70,55,45,0.05)]']) }}>
    @if($titulo || $icono || $acciones)
        <div class="flex items-center justify-between gap-3 pb-3 mb-4 border-b border-[#D5CABE]/60 dark:border-[#51483F]/60">
            <div class="flex items-center gap-2.5">
                @if($icono)
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#A35A44]/10 text-[#A35A44] dark:bg-[#A35A44]/20 dark:text-[#D6AE86]">
                        <i class="ph-bold {{ $icono }} text-base"></i>
                    </div>
                @endif
                <div>
                    @if($titulo)
                        <h3 class="text-sm sm:text-base font-bold text-[#304060] dark:text-[#E9DFD3]">{{ $titulo }}</h3>
                    @endif
                    @if($subtitulo)
                        <p class="text-[11px] sm:text-xs text-[#677084] dark:text-[#C7BEB6]">{{ $subtitulo }}</p>
                    @endif
                </div>
            </div>
            @if($acciones)
                <div class="flex items-center gap-2">
                    {{ $acciones }}
                </div>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>