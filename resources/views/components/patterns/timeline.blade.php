{{-- Patrón Canónico de Línea de Tiempo Cronológica --}}
@props([
    'items' => [],
    'emptyMessage' => 'No se registran eventos en el historial.',
])

<div {{ $attributes->merge(['class' => 'rm-timeline space-y-4']) }}>
    @if(empty($items) && trim($slot) === '')
        <div class="p-6 text-center rounded-2xl border border-dashed border-[var(--rm-border)] bg-[var(--rm-surface-alt)]/60 text-xs text-[var(--rm-text-secondary)] font-medium">
            <i class="ph ph-clock-counter-clockwise text-2xl text-[var(--rm-primary)] mb-1 block"></i>
            {{ $emptyMessage }}
        </div>
    @elseif(!empty($items))
        <div class="relative pl-6 sm:pl-8 space-y-4 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-[var(--rm-border-soft)] dark:before:bg-[var(--rm-text-muted)]">
            @foreach($items as $item)
                @php
                    $variant = $item['variant'] ?? 'default';
                    $dotClass = match($variant) {
                        'primary', 'terracota' => 'bg-[var(--rm-primary)] text-white border-[var(--rm-primary-hover)]',
                        'success', 'verde' => 'bg-[var(--rm-success)] text-white border-[var(--rm-success-hover)]',
                        'info', 'azul' => 'bg-[var(--rm-info)] text-white border-[var(--rm-info-hover)]',
                        'warning', 'ambar' => 'bg-[var(--rm-warning)] text-white border-[var(--rm-warning-hover)]',
                        'danger', 'critico' => 'bg-[var(--rm-danger)] text-white border-[var(--rm-danger-hover)]',
                        default => 'bg-[var(--rm-accent-brown)] text-white border-[var(--rm-accent-brown)]',
                    };
                @endphp
                <div class="relative group" wire:key="timeline-item-{{ $item['id'] ?? $loop->index }}">
                    {{-- Punto / Icono del eje cronológico --}}
                    <span class="absolute -left-6 sm:-left-8 top-1 flex h-6 w-6 items-center justify-center rounded-full border-2 {{ $dotClass }} shadow-xs text-[11px]">
                        @if(!empty($item['icon']))
                            <i class="{{ $item['icon'] }}"></i>
                        @else
                            <i class="ph ph-clock text-xs"></i>
                        @endif
                    </span>

                    {{-- Tarjeta del Evento --}}
                    <div class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 sm:p-3.5 space-y-2 shadow-2xs transition-all duration-150 hover:border-[var(--rm-primary)]/40 hover:shadow-xs">
                        {{-- Cabecera del Evento --}}
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-[var(--rm-text-primary)] flex items-center gap-1.5">
                                    <i class="ph ph-user-circle text-sm text-[var(--rm-primary)]"></i>
                                    <span>{{ $item['actor'] ?? 'Sistema / Profesional' }}</span>
                                </span>
                                @if(!empty($item['tipo']))
                                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] text-[var(--rm-text-secondary)]">
                                        {{ $item['tipo'] }}
                                    </span>
                                @endif
                                @if(!empty($item['estado']))
                                    <span class="text-[10px] uppercase font-extrabold tracking-wider px-2 py-0.5 rounded-full {{ $dotClass }}">
                                        {{ $item['estado'] }}
                                    </span>
                                @endif
                            </div>

                            <span class="text-[11px] font-mono text-[var(--rm-text-secondary)]">
                                {{ $item['fecha'] ?? '' }}
                            </span>
                        </div>

                        {{-- Descripción / Contenido --}}
                        @if(!empty($item['descripcion']))
                            <p class="text-xs text-[var(--rm-text-primary)] leading-relaxed font-medium">
                                {{ $item['descripcion'] }}
                            </p>
                        @endif

                        {{-- Contenido extra si existe --}}
                        @if(!empty($item['extra']))
                            <div class="pt-1 text-[11px] text-[var(--rm-text-secondary)]">
                                {{ $item['extra'] }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{ $slot }}
    @endif
</div>
