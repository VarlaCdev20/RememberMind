    {{-- 6. SLIDE-OVER DRAWER LATERAL (NÍTIDO, SIN BLUR)                           --}}
    {{-- ========================================================================= --}}
    <div x-show="drawerAbierto"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="relative z-50"
         role="dialog"
         aria-modal="true"
         x-on:keydown.escape.window="cerrarDrawer()"
         style="display: none;">

        {{-- Backdrop oscurecido suave sin blur --}}
        <div class="rm-drawer-backdrop" @click="cerrarDrawer()"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
            <div x-show="drawerAbierto"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="pointer-events-auto flex h-full w-screen max-w-[760px] md:w-[740px] flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out">

                {{-- Header del Drawer (Golden Reference) --}}
                <header class="rm-drawer-header">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <span class="rm-drawer-badge">
                                <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-info-action)] animate-pulse"></span>
                                PANEL LATERAL DE CONSULTA
                            </span>
                            <div class="flex items-center gap-2 pt-0.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface)] text-[var(--rm-action-primary)] border border-[var(--rm-border)] text-sm shadow-2xs">
                                    <i class="ph-bold ph-heartbeat"></i>
                                </span>
                                <h3 class="rm-drawer-title">DETALLE DEL CONTROL HEMODINÁMICO</h3>
                            </div>
                            <p class="rm-drawer-subtitle">Registro clínico y evaluación longitudinal de constantes vitales</p>
                        </div>
                        <button type="button"
                                @click="cerrarDrawer()"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] border border-transparent hover:border-[var(--rm-border)] transition cursor-pointer"
                                aria-label="Cerrar panel lateral">
                            <i class="ph ph-x text-lg"></i>
                        </button>
                    </div>
                </header>

                {{-- Cuerpo del Drawer (100% Nítido, Scroll Exclusivo) --}}
                <div class="rm-drawer-body space-y-4">
                    {{-- Card del Residente (Horizontal Compacta Golden Reference) --}}
                    <div class="rm-drawer-resident">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] font-bold text-sm shadow-xs">
                                {{ substr($adultoMayor->nombres, 0, 1) }}{{ substr($adultoMayor->ap_paterno, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight truncate uppercase">
                                    {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }} {{ $adultoMayor->nombres }}
                                </h4>
                                <div class="flex items-center gap-2 text-xs text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
                                    <span>{{ $adultoMayor->edad_texto }}</span>
                                    <span>·</span>
                                    <span>CI: {{ $adultoMayor->ci ?: 'Documento S/D' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-semibold text-xs">
                                {{ $adultoMayor->ubicacion_texto }}
                            </span>
                            <div class="mt-1 flex items-center justify-end gap-1.5">
                                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Estado:</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $adultoMayor->estado_badge_color }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $adultoMayor->estado_humano }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <template x-if="registroActivo">
                        <div class="space-y-3.5">
                            {{-- Resumen Superior --}}
                            <div class="rm-drawer-card-highlight space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-bold border"
                                          :class="registroActivo.badgeClass || 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border-[var(--rm-success-strong)]'">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        <span x-text="registroActivo.estado"></span>
                                    </span>
                                    <span class="text-xs font-semibold text-[var(--rm-text-body)]"
                                          x-text="`${registroActivo.fecha} · ${registroActivo.hora}`"></span>
                                </div>
                                <div class="text-base font-bold text-[var(--rm-text-title)]"
                                     x-text="`Presión Arterial: ${registroActivo.pa} mmHg`"></div>
                                <div class="text-[11px] text-[var(--rm-text-muted)] font-medium"
                                     x-text="`Criterio: ${registroActivo.criterio || 'Monitoreo estándar'}`"></div>
                            </div>

                            {{-- Profesional Responsable --}}
                            <div class="rm-drawer-card flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-full bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] flex items-center justify-center font-bold text-xs">
                                        <i class="ph-bold ph-user"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] uppercase font-bold text-[var(--rm-text-muted)] block">Registrado por</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="registroActivo.responsable"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Grid de Todas las Constantes --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Constantes Fisiológicas
                                </h5>
                                <div class="rm-drawer-card overflow-hidden divide-y divide-[var(--rm-border)]/50 !p-0">
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Frecuencia cardíaca</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="`${registroActivo.fc} lpm`"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Saturación SpO₂</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.spo2"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Temperatura</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.temp"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Frecuencia respiratoria</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="`${registroActivo.fr} rpm`"></span>
                                    </div>
                                    <div class="px-3.5 py-2 flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Escala de dolor (EVA)</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.dolor"></span>
                                    </div>
                                    <template x-if="registroActivo.glucosa && registroActivo.glucosa !== '--'">
                                        <div class="px-3.5 py-2 flex items-center justify-between">
                                            <span class="text-[var(--rm-text-muted)]">Glucemia capilar</span>
                                            <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.glucosa"></span>
                                        </div>
                                    </template>
                                    <template x-if="registroActivo.peso && registroActivo.peso !== '--'">
                                        <div class="px-3.5 py-2 flex items-center justify-between">
                                            <span class="text-[var(--rm-text-muted)]">Peso corporal</span>
                                            <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="registroActivo.peso"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Condiciones Clínicas Adicionales --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Condiciones del Paciente
                                </h5>
                                <div class="rm-drawer-card space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Posición de toma:</span>
                                        <span class="font-semibold text-[var(--rm-text-title)]" x-text="registroActivo.posicion"></span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[var(--rm-text-muted)]">Oxigenoterapia:</span>
                                        <span class="font-semibold text-[var(--rm-text-title)]" x-text="registroActivo.oxigeno"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- Observaciones Clínicas / "El por qué" --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Observaciones / Motivo de Control
                                </h5>
                                <div class="rm-drawer-card bg-[var(--rm-surface-alt)]/50">
                                    <p class="text-xs text-[var(--rm-text-body)] leading-relaxed italic"
                                       x-text="registroActivo.observaciones"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer del Drawer --}}
                <div class="rm-drawer-footer flex items-center justify-end gap-2">
                    <button type="button"
                            @click="cerrarDrawer()"
                            class="rm-btn rm-btn-secondary text-xs font-bold cursor-pointer">
                        Cerrar detalle
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
