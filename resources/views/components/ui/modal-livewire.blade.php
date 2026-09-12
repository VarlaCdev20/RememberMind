@props(['id' => null, 'title' => '', 'maxWidth' => '2xl', 'closeMethod' => 'cerrarModal'])

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
$modalId = $id ?: 'modal-'.\Illuminate\Support\Str::slug((string) ($attributes->wire('model')->value() ?: 'formulario'));
@endphp

<div 
 x-data="{ show: @entangle($attributes->wire('model')) }"
 x-show="show"
 x-on:keydown.escape.window="$wire.{{ $closeMethod }}()"
 class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 sm:p-6"
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
 <div class="absolute inset-0 bg-[var(--color-modal-overlay)] backdrop-blur-sm"></div>
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
 role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-titulo"
 class="modal-institucional enf-modal-panel relative w-full my-auto flex flex-col max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] transform overflow-hidden transition-all {{ $maxWidthClass }}"
 >
 <!-- Header -->
 <div class="enf-modal-header shrink-0 flex items-center justify-between">
 <h3 id="{{ $modalId }}-titulo" class="text-xl font-extrabold text-titulo flex items-center gap-2">
 @if(isset($icon))
 {{ $icon }}
 @endif
 {{ $title }}
 </h3>
 <button type="button" @click="$wire.{{ $closeMethod }}()" class="rm-btn-icon rounded-xl" aria-label="Cerrar formulario">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>

 <!-- Body (con scroll) -->
 <div class="flex-1 min-h-0 overflow-y-auto px-5 sm:px-6 py-4 sm:py-5">
 {{ $slot }}
 </div>

 <!-- Footer -->
 @if(isset($footer))
 <div class="enf-modal-footer shrink-0 flex flex-col sm:flex-row items-center justify-end gap-3">
 {{ $footer }}
 </div>
 @endif
 </div>
</div>
