        {{-- COLUMNA IZQUIERDA (48–50%): LÍNEA DE TIEMPO DE EVENTOS               --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-6 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-action-primary)] text-base"></i>
                    <h3 class="text-xs sm:text-sm font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                        Línea de tiempo de eventos
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] dark:bg-[var(--rm-info-soft)] dark:text-[var(--rm-info-strong)] border border-[var(--rm-info-strong)] dark:border-[var(--rm-info-strong)]" title="Panel deslizable verticalmente">
                        <i class="ph-bold ph-arrows-down-up text-[11px]"></i>
                        <span>Deslizable</span>
                    </span>
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)]">
                        {{ count($eventos) }} registros
                    </span>
                </div>
            </div>

            @if(count($eventos) === 0)
                <div class="py-12 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                    <i class="ph-bold ph-shield-check text-3xl text-[var(--rm-text-muted)] dark:text-[var(--rm-text-secondary)] block"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">No se encontraron eventos clínicos con los filtros actuales.</p>
                    <p>Modifique la búsqueda o el período seleccionado.</p>
                </div>
            @else
                {{-- Contenedor deslizable (scrollable timeline) para explorar eventos cómodamente --}}
                <div class="max-h-[640px] overflow-y-auto pr-2 sm:pr-2.5 space-y-3.5 custom-timeline-scroll focus:outline-hidden" tabindex="0" title="Desliza verticalmente para ver todos los eventos">
                    {{-- Línea vertical con puntos semánticos --}}
                    <div class="relative pl-6 space-y-3.5 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-[var(--rm-surface-soft)] dark:before:bg-[var(--rm-surface-soft)]">
                    @foreach($eventos as $ev)
                        @php
                            $esSeleccionado = ($eventoActivo['id'] ?? '') === $ev['id'];
                        @endphp
                        <div class="relative">
                            {{-- Punto semántico vertical --}}
                            <span class="absolute -left-6 top-4 h-3.5 w-3.5 rounded-full border-2 border-white dark:border-[var(--rm-border)] {{ $ev['color_dot'] }} shadow-xs transition-transform {{ $esSeleccionado ? 'scale-125 ring-2 ring-[var(--rm-action-primary)]/40' : '' }}"></span>

                            {{-- Tarjeta interactiva del evento --}}
                            <div wire:click="seleccionarEvento('{{ $ev['id'] }}')"
                                 class="w-full text-left p-3.5 rounded-2xl border transition cursor-pointer {{ $esSeleccionado ? 'border-[var(--rm-action-primary)] bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] ring-1 ring-[var(--rm-action-primary)]/30 shadow-xs' : 'border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-[var(--rm-border)] dark:hover:border-[var(--rm-border)]' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-[10.5px] font-bold text-[var(--rm-text-muted)]">
                                            {{ $ev['fecha'] }} · {{ $ev['hora'] }}
                                        </span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border shrink-0 {{ $ev['estado_color'] }}">
                                        {{ $ev['estado_badge'] }}
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center gap-2">
                                    <i class="{{ $ev['icono'] }} text-base shrink-0"></i>
                                    <h4 class="text-xs font-bold uppercase tracking-wide text-[var(--rm-text-title)]">
                                        {{ $ev['titulo'] }}
                                    </h4>
                                </div>

                                <p class="text-xs text-[var(--rm-text-body)] line-clamp-2 mt-1 font-normal leading-relaxed">
                                    {{ $ev['descripcion_resumida'] }}
                                </p>

                                <div class="mt-2.5 pt-2 border-t border-[var(--rm-border)]/60 flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                                    <span class="font-medium">
                                        {{ $ev['profesional_nombre'] }} · {{ $ev['profesional_rol'] }}
                                    </span>
                                    <span class="flex items-center gap-0.5 text-xs font-bold {{ $esSeleccionado ? 'text-[var(--rm-action-primary)] dark:text-[var(--rm-info-strong)]' : 'text-[var(--rm-text-muted)]' }}">
                                        <span>Detalle</span>
                                        <i class="ph-bold ph-caret-right"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>

                {{-- Indicador inferior de navegación deslizable --}}
                @if(count($eventos) > 2)
                    <div class="pt-2 border-t border-[var(--rm-border)]/60 flex items-center justify-between text-[11px] text-[var(--rm-text-muted)]">
                        <span class="flex items-center gap-1.5 font-medium">
                            <i class="ph-bold ph-mouse-simple text-[var(--rm-action-primary)]"></i>
                            <span>Desliza para ver más eventos</span>
                        </span>
                        <span class="font-mono text-[10.5px]">
                            Mostrando {{ count($eventos) }} eventos
                        </span>
                    </div>
                @endif
            @endif
        </div>

        {{-- ===================================================================== --}}
