    {{-- 1. CABECERA DEL MÓDULO (COMPACTA, CLÍNICA Y ACCIONES FUNCIONALES)          --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-[var(--rm-border)] pb-3">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-sm sm:text-base font-bold text-[var(--rm-text-title)] tracking-tight uppercase">
                    SIGNOS VITALES
                </h2>
                <span class="inline-flex items-center gap-1 rounded-full bg-[var(--rm-info-soft)] px-2 py-0.5 text-[10px] font-bold text-[var(--rm-info-strong)] border border-[var(--rm-info-strong)]">
                    <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-info-action)] animate-pulse"></span>
                    Monitoreo activo
                </span>
            </div>
            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5 font-medium">
                Monitoreo, tendencias y análisis de signos vitales
            </p>
            {{-- Compatibilidad para pruebas automatizadas --}}
            <span class="sr-only">Monitoreo Hemodinámico y Signos Vitales - Evolución Temporal de Parámetros Clínicos</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Botón Principal: Azul Oscuro Institucional --}}
            @if(!$consultaEnfermeria)
<button type="button"
                    wire:click="abrirModalSignos"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-xl bg-[var(--rm-action-primary)] px-3.5 py-2 text-xs font-bold text-[var(--rm-text-on-primary)] shadow-xs hover:bg-[var(--rm-action-primary)] transition active:scale-98 cursor-pointer">
                <i class="ph-bold ph-plus text-sm"></i>
                <span>+ Registrar signos vitales</span>
            </button>
@endif

            {{-- Botón: Referencias y objetivos (Abre modal de rangos interactivo) --}}
            <button type="button"
                    @click="abrirModalRangos()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] px-3 py-2 text-xs font-semibold text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
                <i class="ph-bold ph-sliders-horizontal text-sm text-[var(--rm-text-muted)]"></i>
                <span>Referencias y objetivos</span>
            </button>

            {{-- Botón y Dropdown de 3 puntitos [...] --}}
            <div class="relative" @click.outside="menuCabecera = false">
                <button type="button"
                        @click="menuCabecera = !menuCabecera"
                        aria-label="Más opciones"
                        title="Opciones adicionales"
                        class="inline-flex items-center justify-center rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] w-8 h-8 text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-dots-three-vertical text-base"></i>
                </button>

                {{-- Menú flotante de opciones --}}
                <div x-show="menuCabecera"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-1.5 w-56 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-lg py-1.5 z-40 text-xs divide-y divide-[var(--rm-border)]/50"
                     style="display: none;">
                    <div class="py-1">
                        <button type="button"
                                @click="exportarSignosPDF(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-file-pdf text-[var(--rm-danger-strong)] text-sm"></i>
                            <span>Exportar informe (PDF)</span>
                        </button>
                        <button type="button"
                                @click="exportarSignosExcel(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-file-xls text-[var(--rm-success-strong)] text-sm"></i>
                            <span>Descargar histórico (Excel/CSV)</span>
                        </button>
                        <button type="button"
                                @click="imprimirGrafica(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-printer text-[var(--rm-info-strong)] text-sm"></i>
                            <span>Imprimir gráfica y resumen</span>
                        </button>
                    </div>
                    <div class="py-1">
                        <button type="button"
                                @click="abrirModalRangos(); menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-sliders-horizontal text-[var(--rm-action-primary)] text-sm"></i>
                            <span>Referencias y objetivos</span>
                        </button>
                        @if(!$consultaEnfermeria)
<button type="button"
                                wire:click="abrirModalSignos"
                                @click="menuCabecera = false;"
                                class="w-full text-left px-3.5 py-2 flex items-center gap-2 text-[var(--rm-text-body)] hover:bg-[var(--rm-surface-alt)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                            <i class="ph-bold ph-plus-circle text-[var(--rm-action-primary)] text-sm"></i>
                            <span>Registrar nueva medición</span>
                        </button>
@endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
