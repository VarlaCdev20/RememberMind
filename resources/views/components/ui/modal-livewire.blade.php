@props([
    'id' => null,
    'title' => '',
    'maxWidth' => 'md',
    'closeMethod' => 'cerrarModal',
    'footer' => null,
    'icon' => null,
    'subtitle' => null,
    'badge' => null,
    'showValidation' => true,
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-[var(--rm-modal-sm,420px)]',
    'md' => 'sm:max-w-[var(--rm-modal-md,560px)]',
    'lg' => 'sm:max-w-[var(--rm-modal-lg,720px)]',
    'xl', '2xl' => 'sm:max-w-[var(--rm-modal-xl,900px)]',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    default => 'sm:max-w-[var(--rm-modal-md,560px)]',
};
$modalId = $id ?: 'modal-'.\Illuminate\Support\Str::slug((string) ($attributes->wire('model')->value() ?: 'formulario'));
@endphp

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    x-cloak
    x-on:keydown.escape.window="$wire.{{ $closeMethod }}()"
    class="fixed inset-0 z-[var(--rm-z-modal,600)] flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 sm:p-6 font-sans"
    style="display: none;"
>
    <!-- Overlay Accesible Cálido -->
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
        <div class="absolute inset-0 bg-[var(--rm-modal-overlay,rgba(51,39,31,0.55))] backdrop-blur-xs"></div>
    </div>

    <!-- Modal Card Normalizada V2 -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-titulo"
        class="rm-modal-panel relative w-full my-auto flex flex-col max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-3rem)] transform overflow-hidden transition-all bg-[var(--rm-modal-bg,var(--rm-surface))] border border-[var(--rm-modal-border,var(--rm-border))] rounded-[var(--rm-radius-modal,20px)] shadow-[var(--rm-shadow-overlay)] {{ $maxWidthClass }}"
    >
        <!-- Header -->
        <header class="rm-modal-header shrink-0 flex items-start justify-between gap-4 px-5 sm:px-6 py-4 bg-[var(--rm-surface-soft)] border-b border-[var(--rm-border-soft)]">
            <div class="min-w-0">
                @if($badge)
                    <p class="mb-1 text-[var(--rm-font-size-meta)] font-extrabold uppercase tracking-[var(--rm-letter-spacing-wide)] text-[var(--rm-action-primary)]">{{ $badge }}</p>
                @endif
                <h3 id="{{ $modalId }}-titulo" class="text-lg sm:text-xl font-extrabold text-[var(--rm-text-primary)] flex items-center gap-2.5 leading-tight">
                    @if(isset($icon) && $icon)
                        {{ $icon }}
                    @endif
                    {{ $title }}
                </h3>
                @if($subtitle)
                    <p class="mt-1 text-xs font-medium text-[var(--rm-text-secondary)]">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" @click="$wire.{{ $closeMethod }}()" class="rm-btn-icon rm-btn-icon-sm text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]" aria-label="Cerrar modal">
                <i class="ph-bold ph-x text-base"></i>
            </button>
        </header>

        <!-- Body (con scroll) -->
        <div class="rm-modal-body flex-1 min-h-0 overflow-y-auto px-5 sm:px-6 py-4 sm:py-5">
            @if($showValidation)
                <x-validation-errors class="mb-4" />
            @endif
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if(isset($footer) && $footer)
            <footer class="rm-modal-footer shrink-0 flex flex-col sm:flex-row items-center justify-end gap-3 px-5 sm:px-6 py-3.5 bg-[var(--rm-surface-soft)] border-t border-[var(--rm-border-soft)]">
                {{ $footer }}
            </footer>
        @endif
    </div>
</div>
