@props([
    'titulo' => '',
    'subtitulo' => '',
    'modulo' => 'Enfermería',
    'icono' => 'ph-heartbeat',
    'turno' => null,
    'fecha' => null,
])

<header {{ $attributes->merge(['class' => 'enf-card p-4 md:p-5 mb-6']) }}>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="enf-badge enf-badge-coral">
                    <i class="ph-bold {{ $icono }} text-xs"></i>
                    <span>{{ $modulo }}</span>
                </span>

                @if($turno)
                    <span class="enf-badge enf-badge-warning">
                        <i class="ph-bold ph-sun text-xs"></i>
                        <span>{{ $turno }}</span>
                    </span>
                @endif

                @if($fecha)
                    <span class="enf-badge enf-badge-neutral">
                        <i class="ph-bold ph-calendar-blank text-xs"></i>
                        <span>{{ $fecha }}</span>
                    </span>
                @endif

                @if(isset($badges))
                    {{ $badges }}
                @endif
            </div>

            <div>
                <h1 class="enf-title text-xl md:text-2xl">{{ $titulo }}</h1>
                @if($subtitulo)
                    <p class="enf-subtitle text-xs md:text-sm mt-0.5">{{ $subtitulo }}</p>
                @endif
            </div>
        </div>

        @if($slot->isNotEmpty())
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                {{ $slot }}
            </div>
        @endif
    </div>
</header>
