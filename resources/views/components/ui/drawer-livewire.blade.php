@props([
    'title' => '',
    'subtitle' => null,
    'badge' => 'PANEL LATERAL DE CONSULTA',
    'icon' => null,
    'closeMethod' => 'cerrarDrawer',
    'width' => 'w-screen max-w-[760px] md:w-[740px]'
])

<div x-data="{ show: @entangle($attributes->wire('model')) }" x-show="show" x-cloak
     x-on:keydown.escape.window="$wire.{{ $closeMethod }}()" class="fixed inset-0 z-50 overflow-hidden font-sans">
    {{-- Backdrop estándar del Design System (Oscuro suave, blur 0-1px) --}}
    <div class="rm-drawer-backdrop" @click="$wire.{{ $closeMethod }}()"></div>

    <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
        <aside x-show="show"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="pointer-events-auto rm-drawer flex h-full {{ $width }} flex-col">
            
            {{-- Header Fijo con Badge de Consulta --}}
            <header class="rm-drawer-header">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        @if($badge)
                            <span class="rm-drawer-badge">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                {{ $badge }}
                            </span>
                        @endif
                        <div class="flex items-center gap-2 pt-0.5">
                            @if($icon)
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface)] text-[#1E3A8A] border border-[var(--rm-border)] text-sm shadow-2xs">
                                    <i class="ph-bold {{ $icon }}"></i>
                                </span>
                            @endif
                            <h2 class="rm-drawer-title">{{ $title }}</h2>
                        </div>
                        @if($subtitle)
                            <p class="rm-drawer-subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button"
                            class="rm-btn-icon text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer"
                            wire:click="{{ $closeMethod }}"
                            aria-label="Cerrar panel">
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>
            </header>

            {{-- Body con Scroll Exclusivo (100% Nítido) --}}
            <div class="rm-drawer-body">
                {{ $slot }}
            </div>

            {{-- Footer Fijo --}}
            @isset($footer)
                <footer class="rm-drawer-footer">
                    {{ $footer }}
                </footer>
            @endisset
        </aside>
    </div>
</div>
