@props(['id', 'title' => '', 'maxWidth' => '2xl', 'closeMethod' => 'cerrarModal'])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    '6xl' => 'sm:max-w-6xl',
    '7xl' => 'sm:max-w-7xl',
    default => 'sm:max-w-2xl',
};
@endphp

<div 
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    x-on:keydown.escape.window="$wire.{{ $closeMethod }}()"
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto overflow-x-hidden pt-16 px-4 pb-10 sm:pt-20"
    style="display: none;"
>
    <!-- Overlay -->
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transform transition-all"
        @click="$wire.{{ $closeMethod }}()"
    >
        <div class="absolute inset-0 bg-[#2F3E5C]/60 backdrop-blur-sm"></div>
    </div>

    <!-- Modal Card -->
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full flex flex-col max-h-[82vh] transform overflow-hidden rounded-[24px] border border-[#C7B5A3] bg-[#F8F2EC] shadow-[0_20px_50px_rgba(47,62,92,0.2)] transition-all {{ $maxWidthClass }}"
    >
        <!-- Header -->
        <div class="border-b border-[#D5C7B9] bg-[#E6DDD3]/50 px-6 py-4 flex items-center justify-between">
            <h3 class="text-xl font-black text-[#2F3E5C] flex items-center gap-2">
                @if(isset($icon))
                    {{ $icon }}
                @endif
                {{ $title }}
            </h3>
            <button type="button" @click="$wire.{{ $closeMethod }}()" class="flex h-8 w-8 items-center justify-center rounded-xl bg-[#D5C7B9]/50 text-[#2F3E5C] transition hover:bg-[#2F3E5C] hover:text-white active:scale-95">
                <i class="ph-bold ph-x"></i>
            </button>
        </div>

        <!-- Body (con scroll) -->
        <div class="overflow-y-auto px-6 py-5">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if(isset($footer))
            <div class="border-t border-[#D5C7B9] bg-[#E6DDD3]/30 px-6 py-4 flex flex-col sm:flex-row items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
