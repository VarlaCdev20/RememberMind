@props(['id', 'maxWidth'])

@php
$id = $id ?? md5($attributes->wire('model'));

$maxWidth = [
 'sm' => 'sm:max-w-sm',
 'md' => 'sm:max-w-md',
 'lg' => 'sm:max-w-lg',
 'xl' => 'sm:max-w-xl',
 '2xl' => 'sm:max-w-2xl',
][$maxWidth ?? '2xl'];
@endphp

<div
 x-data="{ show: @entangle($attributes->wire('model')) }"
 x-on:close.stop="show = false"
 x-on:keydown.escape.window="show = false"
 x-show="show"
 id="{{ $id }}"
 class="jetstream-modal fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden px-4 py-4"
 style="display: none;"
>
 <div x-show="show" class="fixed inset-0 transform transition-all" x-on:click="show = false" x-transition:enter="ease-out duration-300"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="ease-in duration-200"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0">
 <div class="absolute inset-0 bg-[var(--rm-modal-overlay,rgba(48,44,42,0.55))] backdrop-blur-md"></div>
 </div>

 <div x-show="show" class="jetstream-modal__panel bg-[var(--rm-modal-bg,var(--rm-surface-main))] border border-[var(--rm-border-soft)] border-t-[rgba(255,255,255,0.85)] rounded-2xl overflow-hidden shadow-2xl transform transition-all w-full {{ $maxWidth }}"
 
 x-transition:enter="ease-out duration-300"
 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
 x-transition:leave="ease-in duration-200"
 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
 {{ $slot }}
 </div>
</div>
