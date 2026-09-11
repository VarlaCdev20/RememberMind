@props([
    'icono' => 'ph-heartbeat',
    'titulo' => 'Sin información que mostrar',
    'descripcion' => 'No se encontraron registros para los criterios seleccionados.',
])

<div {{ $attributes->merge(['class' => 'enf-card p-8 md:p-12 text-center']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#E58C6C]/10 text-[#E58C6C] mb-4">
        <i class="ph-light {{ $icono }} text-3xl"></i>
    </div>
    <h3 class="enf-title text-base md:text-lg mb-1">{{ $titulo }}</h3>
    <p class="enf-subtitle text-xs md:text-sm max-w-md mx-auto mb-6">{{ $descripcion }}</p>

    @if($slot->isNotEmpty())
        <div class="flex items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
