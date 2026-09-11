@props([
    'titulo' => null,
])

<div {{ $attributes->merge(['class' => 'enf-card p-3 md:p-4 mb-5']) }}>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-1 flex-wrap items-center gap-3">
            @if($titulo)
                <span class="text-xs font-bold uppercase tracking-wider text-[#344563] mr-1 hidden sm:inline">
                    {{ $titulo }}
                </span>
            @endif
            {{ $slot }}
        </div>

        @if(isset($acciones))
            <div class="flex items-center gap-2 shrink-0">
                {{ $acciones }}
            </div>
        @endif
    </div>
</div>
