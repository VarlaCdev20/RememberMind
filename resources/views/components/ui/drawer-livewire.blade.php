@props(['title' => '', 'closeMethod' => 'cerrarDrawer', 'width' => 'max-w-xl'])

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-show="show" x-cloak
     x-on:keydown.escape.window="$wire.{{ $closeMethod }}()" class="fixed inset-0 z-50">
    <button type="button" aria-label="Cerrar panel" @click="$wire.{{ $closeMethod }}()"
            class="absolute inset-0 h-full w-full bg-[var(--color-modal-overlay)] backdrop-blur-sm"></button>
    <aside x-show="show" x-transition:enter="transition ease-out duration-300"
           x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
           x-transition:leave-end="translate-x-full"
           class="rm-drawer absolute inset-y-0 right-0 flex w-full {{ $width }} flex-col border-l border-[#E3D6C8] bg-[#FBF7F2] shadow-2xl">
        <header class="enf-modal-header flex items-center justify-between px-5 py-4">
            <h2 class="text-lg font-black text-titulo">{{ $title }}</h2>
            <button type="button" class="rm-btn-icon" wire:click="{{ $closeMethod }}" aria-label="Cerrar"><i class="ph-bold ph-x"></i></button>
        </header>
        <div class="flex-1 overflow-y-auto p-5">{{ $slot }}</div>
        @isset($footer)<footer class="enf-modal-footer p-4">{{ $footer }}</footer>@endisset
    </aside>
</div>
