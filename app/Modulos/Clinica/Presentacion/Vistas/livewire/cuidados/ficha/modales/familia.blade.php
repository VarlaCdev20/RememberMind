{{-- 10. VENTANA LATERAL FLOTANTE: RED DE APOYO FAMILIAR (SLIDE-OVER DRAWER)    --}}
{{-- ========================================================================= --}}
<div x-show="drawerFamilia"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden font-sans"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="drawerFamilia = false">

    {{-- Backdrop --}}
    <div class="rm-drawer-backdrop"
         x-show="drawerFamilia"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="drawerFamilia = false"></div>

    {{-- Panel Deslizante Lateral --}}
    <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
        <aside x-show="drawerFamilia"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="pointer-events-auto rm-drawer flex h-full w-screen max-w-[620px] flex-col bg-[var(--rm-surface)] shadow-2xl border-l border-[var(--rm-border)] overflow-hidden">

            {{-- Header Fijo --}}
            <header class="rm-drawer-header p-5 border-b border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-start justify-between gap-3 shrink-0">
                <div class="space-y-1">
                    <span class="rm-drawer-badge">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-success-action)] animate-pulse"></span>
                        RED DE APOYO Y CONTACTOS FAMILIARES
                    </span>
                    <div class="flex items-center gap-2 pt-0.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border border-[var(--rm-success-strong)] text-base shadow-2xs">
                            <i class="ph-bold ph-users-three"></i>
                        </span>
                        <h2 class="text-lg font-bold text-[var(--rm-text-title)]">Contactos Familiares</h2>
                    </div>
                    <p class="text-xs text-[var(--rm-text-muted)]">Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }}</p>
                </div>
                <button type="button"
                        @click="drawerFamilia = false"
                        class="p-2 rounded-xl text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </header>

            {{-- Body Scrollable --}}
            <div class="rm-drawer-body flex-1 overflow-y-auto p-5 space-y-4 text-xs">
                {{-- Contacto Principal de Emergencia --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger-strong)] dark:border-[var(--rm-danger-strong)] space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-danger-strong)] dark:text-[var(--rm-danger-strong)] tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-phone-call text-sm"></i>
                            Contacto Principal de Emergencia
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)] dark:bg-[var(--rm-danger-soft)] dark:text-[var(--rm-danger-strong)] uppercase">Prioridad 1</span>
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div>
                            <h4 class="text-sm font-bold text-[var(--rm-text-title)]">{{ $contactoEmergencia?->nombre_completo ?: 'Sin contacto de emergencia designado' }}</h4>
                            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">{{ $contactoEmergencia?->pivot?->parentesco ?: 'Sin parentesco registrado' }}</p>
                        </div>
                        @if($contactoEmergencia?->celular || $contactoEmergencia?->telefono)
                        <a href="tel:{{ $contactoEmergencia->celular ?: $contactoEmergencia->telefono }}"
                           class="px-3 py-2 rounded-xl bg-[var(--rm-success-action)] text-[var(--rm-text-inverse)] font-bold text-xs hover:bg-[var(--rm-success-action)] transition inline-flex items-center gap-1.5 shadow-2xs">
                            <i class="ph-bold ph-phone"></i>
                            <span>{{ $contactoEmergencia->celular ?: $contactoEmergencia->telefono }}</span>
                        </a>
                        @else
                        <span class="text-xs text-[var(--rm-text-muted)] italic">Teléfono no registrado</span>
                        @endif
                    </div>
                </div>

                {{-- Listado de Familiares Registrados --}}
                <div class="space-y-3">
                    <h4 class="font-bold text-[var(--rm-text-title)] uppercase text-[11px] tracking-wider text-[var(--rm-text-muted)]">Familiares y Apoderados Registrados</h4>

                    @if($familiaresActivos->isNotEmpty())
                        @foreach($familiaresActivos as $fam)
                        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="ph-bold ph-user"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-xs text-[var(--rm-text-title)]">{{ $fam->nombre_completo ?? ($fam->nombres . ' ' . $fam->ap_paterno) }}</h5>
                                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">{{ $fam->pivot->parentesco ?? 'Familiar' }} · {{ $fam->telefono ?: ($fam->celular ?: 'Sin celular') }}</p>
                                </div>
                            </div>
                            @if($fam->telefono || $fam->celular)
                            <a href="tel:{{ $fam->telefono ?: $fam->celular }}"
                               class="h-8 w-8 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] flex items-center justify-center transition"
                               title="Llamar">
                                <i class="ph-bold ph-phone text-sm"></i>
                            </a>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] flex items-center justify-center font-bold text-sm shrink-0">
                                    <i class="ph-bold ph-user"></i>
                                </div>
                                <div>
                                    <h5 class="font-bold text-xs text-[var(--rm-text-title)]">No registrado</h5>
                                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">No existen familiares activos registrados.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Frecuencia de Visitas y Acompañamiento --}}
                <div class="p-4 rounded-2xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-2">
                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Régimen de Visitas</span>
                    <p class="text-xs text-[var(--rm-text-body)] leading-relaxed">
                        No existe un régimen de visitas registrado para este residente.
                    </p>
                </div>
            </div>

            {{-- Footer Fijo --}}
            <footer class="p-4 border-t border-[var(--rm-border)] bg-[var(--rm-surface)] flex items-center justify-between gap-3 shrink-0 text-xs">
                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Información social y familiar</span>
                <button type="button"
                        @click="drawerFamilia = false"
                        class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition cursor-pointer">
                    Cerrar Panel
                </button>
            </footer>
        </aside>
    </div>
</div>
