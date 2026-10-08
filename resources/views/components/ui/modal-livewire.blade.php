@props([
    'id' => null,
    'title' => '',
    'maxWidth' => 'md',
    'closeMethod' => 'cerrarModal',
    'footer' => null,
    'context' => null,
    'icon' => null,
    'subtitle' => null,
    'badge' => null,
    'showValidation' => true,
    'dismissOnBackdrop' => false,
    'dismissOnEscape' => true,
    'tone' => 'neutral',
    'draggable' => false,
    'backMethod' => null,
    'backLabel' => 'Volver',
    'alpineModel' => null,
    'alpineClose' => null,
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-[var(--rm-modal-sm,420px)]',
    'md' => 'sm:max-w-[var(--rm-modal-md,620px)]',
    'lg' => 'sm:max-w-[var(--rm-modal-lg,860px)]',
    'xl', '2xl' => 'sm:max-w-[var(--rm-modal-xl,900px)]',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    default => 'sm:max-w-[var(--rm-modal-md,620px)]',
};
$modalId = $id ?: 'modal-'.\Illuminate\Support\Str::slug((string) ($attributes->wire('model')->value() ?: 'formulario'));
@endphp

<div
    x-data="{
        @if($alpineModel)
        get show() { return {{ $alpineModel }} },
        set show(value) { {{ $alpineModel }} = value },
        @else
        show: @entangle($attributes->wire('model')),
        @endif
        dragX: 0, dragY: 0, dragging: false, pointerId: null, lastX: 0, lastY: 0,
        moveBy(dx, dy) {
            if (!this.$refs.dialog) return;
            const rect = this.$refs.dialog.getBoundingClientRect();
            const edge = 8;
            this.dragX += Math.min(Math.max(dx, edge - rect.left), window.innerWidth - edge - rect.right);
            this.dragY += Math.min(Math.max(dy, edge - rect.top), window.innerHeight - edge - rect.bottom);
        },
        startDrag(event) {
            if (event.button !== 0 || window.matchMedia('(max-width: 640px)').matches) return;
            this.dragging = true;
            this.pointerId = event.pointerId;
            this.lastX = event.clientX;
            this.lastY = event.clientY;
            event.currentTarget.focus();
            event.currentTarget.setPointerCapture(event.pointerId);
        },
        drag(event) {
            if (!this.dragging || event.pointerId !== this.pointerId) return;
            this.moveBy(event.clientX - this.lastX, event.clientY - this.lastY);
            this.lastX = event.clientX;
            this.lastY = event.clientY;
        },
        endDrag(event) {
            if (event.pointerId !== this.pointerId) return;
            this.dragging = false;
            this.pointerId = null;
            if (event.currentTarget.hasPointerCapture(event.pointerId)) event.currentTarget.releasePointerCapture(event.pointerId);
        }
    }"
    x-show="show"
    :class="{ 'is-open': show }"
    x-trap.noscroll="show"
    x-cloak
    @if($draggable) x-init="$watch('show', value => { if (value) { dragX = 0; dragY = 0 } })" x-on:resize.window="if (show) { if (window.innerWidth <= 640) { dragX = 0; dragY = 0 } else { moveBy(0, 0) } }" @endif
    x-on:keydown.escape.window="if (show && @js($dismissOnEscape)) { $event.stopPropagation(); {{ $alpineClose ?: '$wire.'.$closeMethod.'()' }} }"
    class="rm-modal-shell fixed inset-0 flex items-center justify-center overflow-y-auto overflow-x-hidden p-4 font-sans {{ $attributes->get('class') }}"
>
    <!-- Overlay Accesible Cálido -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @if($dismissOnBackdrop) @click="{{ $alpineClose ?: '$wire.'.$closeMethod.'()' }}" @endif
        class="rm-modal-shell__overlay"
        aria-hidden="true"
    ></div>

    <!-- Modal Card Normalizada V2 -->
    <div
        x-show="show"
        x-transition:enter="rm-modal-panel--enter"
        x-transition:enter-start="rm-modal-panel--closed"
        x-transition:enter-end="rm-modal-panel--open"
        x-transition:leave="rm-modal-panel--leave"
        x-transition:leave-start="rm-modal-panel--open"
        x-transition:leave-end="rm-modal-panel--closed"
        role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-titulo"
        @if($subtitle) aria-describedby="{{ $modalId }}-descripcion" @endif
        tabindex="-1"
        x-ref="dialog"
        @if($draggable) :style="'translate: ' + dragX + 'px ' + dragY + 'px'" @endif
        class="rm-modal-panel rm-modal-panel--{{ $tone === 'danger' ? 'danger' : 'neutral' }} relative w-full my-auto flex flex-col {{ $maxWidthClass }}"
    >
        <!-- Header -->
        <header class="rm-modal-header shrink-0 flex items-start justify-between gap-4 px-5 sm:px-6 py-4 bg-[var(--rm-surface-soft)] border-b border-[var(--rm-border-soft)]">
            <div class="rm-modal-header__heading min-w-0">
                @if($backMethod)
                    <button type="button" class="rm-btn-icon rm-modal-panel__back" wire:click="{{ $backMethod }}" aria-label="{{ $backLabel }}">
                        <i class="ph-bold ph-arrow-left text-base" aria-hidden="true"></i>
                    </button>
                @endif
                <div class="min-w-0">
                @if($badge)
                    <p class="mb-1 text-[var(--rm-font-size-meta)] font-extrabold uppercase tracking-[var(--rm-letter-spacing-wide)] text-[var(--rm-action-primary)]">{{ $badge }}</p>
                @endif
                <h3 id="{{ $modalId }}-titulo" class="rm-modal-panel__title flex items-center gap-2.5 leading-tight">
                    @if(isset($icon) && $icon)
                        {{ $icon }}
                    @endif
                    {{ $title }}
                </h3>
                @if($subtitle)
                    <p id="{{ $modalId }}-descripcion" class="mt-1 text-xs font-medium text-[var(--rm-text-secondary)]">{{ $subtitle }}</p>
                @endif
                </div>
            </div>
            <div class="rm-modal-header__actions">
                @if($draggable)
                    <button type="button" class="rm-modal-panel__drag-handle" aria-label="Mover ventana: arrastra o usa las flechas; Inicio la centra" title="Mover ventana" @pointerdown="startDrag($event)" @pointermove="drag($event)" @pointerup="endDrag($event)" @pointercancel="endDrag($event)" @keydown.arrow-left.prevent="moveBy(-24, 0)" @keydown.arrow-right.prevent="moveBy(24, 0)" @keydown.arrow-up.prevent="moveBy(0, -24)" @keydown.arrow-down.prevent="moveBy(0, 24)" @keydown.home.prevent="dragX = 0; dragY = 0">
                        <i class="ph-bold ph-dots-six" aria-hidden="true"></i><span aria-hidden="true">Mover</span>
                    </button>
                @endif
                <button type="button" @click="{{ $alpineClose ?: '$wire.'.$closeMethod.'()' }}" class="rm-btn-icon rm-modal-panel__close text-[var(--rm-text-secondary)] hover:text-[var(--rm-text-primary)]" aria-label="Cerrar">
                    <i class="ph-bold ph-x text-base" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        @if(isset($context) && $context)
            <div class="rm-modal-context shrink-0">
                {{ $context }}
            </div>
        @endif

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
