@props([
    'title' => '',
    'subtitle' => null,
    'badge' => 'PANEL LATERAL DE CONSULTA',
    'icon' => null,
    'size' => null,
    'closeMethod' => 'cerrarDrawer',
    'width' => null,
    'footer' => null,
    'dismissOnBackdrop' => false,
    'dismissOnEscape' => true,
    'backMethod' => null,
    'backLabel' => 'Volver',
])

@php
    $resolvedWidth = $width ?? match($size) {
        'sm' => 'w-screen max-w-[var(--rm-drawer-sm,360px)]',
        'lg' => 'w-screen max-w-[var(--rm-drawer-lg,640px)]',
        default => 'w-screen max-w-[var(--rm-drawer-md,460px)]',
    };
    $drawerId = 'drawer-'.\Illuminate\Support\Str::slug((string) ($attributes->wire('model')->value() ?: $title));
@endphp

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-show="show" x-trap.noscroll="show" x-cloak
     x-on:keydown.escape.window="if (show && @js($dismissOnEscape)) { $event.stopPropagation(); $wire.{{ $closeMethod }}() }" class="rm-drawer-shell fixed inset-0 overflow-hidden font-sans">
    {{-- Backdrop estándar del Design System --}}
    <div class="rm-drawer-backdrop" @if($dismissOnBackdrop) @click="$wire.{{ $closeMethod }}()" @endif aria-hidden="true"></div>

    <div class="rm-drawer-shell__position pointer-events-none fixed inset-y-0 right-0 flex max-w-full">
        <aside x-show="show"
               x-transition:enter="transition ease-out duration-[280ms]"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               id="{{ $drawerId }}"
               role="dialog"
               aria-modal="true"
               aria-labelledby="{{ $drawerId }}-title"
               tabindex="-1"
               class="pointer-events-auto rm-drawer rm-drawer--{{ in_array($size, ['sm', 'lg']) ? $size : 'md' }} flex h-full {{ $resolvedWidth }} flex-col overflow-hidden">

            {{-- Header Fijo con Badge de Consulta --}}
            <header class="rm-drawer-header p-4 sm:p-5 border-b border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)]">
                <div class="flex items-start justify-between gap-3">
                    @if($backMethod)
                        <button type="button" class="rm-btn-icon rm-drawer__back shrink-0" wire:click="{{ $backMethod }}" aria-label="{{ $backLabel }}">
                            <i class="ph-bold ph-arrow-left text-base" aria-hidden="true"></i>
                        </button>
                    @endif
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
                            <h2 id="{{ $drawerId }}-title" tabindex="-1" class="text-base sm:text-lg font-bold text-[var(--rm-text-primary)] leading-tight">{{ $title }}</h2>
                        </div>
                        @if($subtitle)
                            <p class="text-xs text-[var(--rm-text-secondary)] truncate">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button"
                            class="rm-btn-icon rm-drawer__close text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)] shrink-0"
                            wire:click="{{ $closeMethod }}"
                            aria-label="Cerrar panel">
                        <i class="ph-bold ph-x text-base" aria-hidden="true"></i>
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
