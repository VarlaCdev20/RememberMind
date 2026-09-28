@props([
    'title' => '',
    'subtitle' => null,
    'badge' => 'PANEL LATERAL DE CONSULTA',
    'icon' => null,
    'size' => null,
    'closeMethod' => 'cerrarDrawer',
    'width' => null,
    'footer' => null,
])

@php
    $resolvedWidth = $width ?? match($size) {
        'sm' => 'w-screen max-w-[var(--rm-drawer-sm,360px)]',
        'lg' => 'w-screen max-w-[var(--rm-drawer-lg,640px)]',
        default => 'w-screen max-w-[var(--rm-drawer-md,480px)] md:w-[740px] md:max-w-[760px]',
    };
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-show="show" x-cloak
     x-on:keydown.escape.window="$wire.{{ $closeMethod }}()" class="fixed inset-0 z-[var(--rm-z-drawer,500)] overflow-hidden font-sans">
    {{-- Backdrop estándar del Design System --}}
    <div class="rm-drawer-backdrop fixed inset-0 bg-[var(--rm-modal-overlay,rgba(51,39,31,0.55))] backdrop-blur-xs transition-opacity" @click="$wire.{{ $closeMethod }}()"></div>

    <div class="pointer-events-none fixed inset-y-0 right-0 z-[var(--rm-z-drawer,500)] flex max-w-full pl-6 sm:pl-10">
        <aside x-show="show"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="pointer-events-auto rm-drawer flex h-full {{ $resolvedWidth }} flex-col bg-[var(--rm-surface)] border-l border-[var(--rm-border)] shadow-[var(--rm-shadow-lg)]">

            {{-- Header Fijo con Badge de Consulta --}}
            <header class="rm-drawer-header p-4 sm:p-5 border-b border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)]">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        @if($badge)
                            <span class="rm-badge rm-badge-info text-[11px] font-bold">
                                <span class="h-1.5 w-1.5 rounded-full bg-current animate-pulse"></span>
                                {{ $badge }}
                            </span>
                        @endif
                        <div class="flex items-center gap-2 pt-0.5 min-w-0">
                            @if($icon)
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[var(--rm-action-primary)]/10 text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/20 text-sm shadow-2xs">
                                    <i class="ph-bold {{ $icon }}"></i>
                                </span>
                            @endif
                            <h2 class="text-base sm:text-lg font-bold text-[var(--rm-text-primary)] truncate">{{ $title }}</h2>
                        </div>
                        @if($subtitle)
                            <p class="text-xs text-[var(--rm-text-secondary)] truncate">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button"
                            class="rm-btn-icon rm-btn-icon-sm text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] shrink-0"
                            wire:click="{{ $closeMethod }}"
                            aria-label="Cerrar panel">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            </header>

            {{-- Body con Scroll Exclusivo --}}
            <div class="rm-drawer-body flex-1 overflow-y-auto p-4 sm:p-5">
                {{ $slot }}
            </div>

            {{-- Footer Fijo --}}
            @if(isset($footer) && $footer)
                <footer class="rm-drawer-footer p-4 border-t border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)]">
                    {{ $footer }}
                </footer>
            @endif
        </aside>
    </div>
</div>
