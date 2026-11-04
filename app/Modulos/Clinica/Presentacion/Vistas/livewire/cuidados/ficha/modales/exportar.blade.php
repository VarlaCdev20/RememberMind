{{-- 8. MODAL FLOTANTE: EXPORTAR FICHA E INFORMES (DESIGN SYSTEM)             --}}
{{-- ========================================================================= --}}
<div x-show="modalExportar"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 font-sans"
     role="dialog"
     aria-modal="true">

    <div class="w-full max-w-xl rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-5"
         @click.outside="modalExportar = false">

        {{-- Header --}}
        <div class="flex items-start justify-between border-b border-[var(--rm-border)] pb-4">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)]">
                    <i class="ph-bold ph-download-simple text-2xl"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-[var(--rm-text-title)]">Exportar Ficha e Informes</h3>
                    <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Residente: {{ $adultoMayor->nombres }} {{ $adultoMayor->ap_paterno }} · CI: {{ $adultoMayor->ci }}</p>
                </div>
            </div>
            <button type="button"
                    @click="modalExportar = false"
                    class="p-2 rounded-xl text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] transition cursor-pointer">
                <i class="ph-bold ph-x text-lg"></i>
            </button>
        </div>

        {{-- Grid de Opciones de Exportación --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            {{-- Opción 1: Ficha Médica Completa --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-[var(--rm-info-strong)] hover:bg-[var(--rm-info-soft)] transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] shrink-0">
                        <i class="ph-bold ph-file-pdf text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Ficha Médica Integral</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Informe clínico consolidado con antecedentes, signos y medicación.</p>
                    </div>
                </div>
                <button type="button"
                        onclick="window.print()"
                        class="w-full py-2 px-3 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] font-bold text-xs hover:bg-[var(--rm-action-primary-hover)] transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-printer"></i>
                    <span>Imprimir / Guardar PDF</span>
                </button>
            </div>

            {{-- Opción 2: Signos Vitales CSV --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-[var(--rm-success-strong)] hover:bg-[var(--rm-success-soft)] transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] shrink-0">
                        <i class="ph-bold ph-file-csv text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Historial de Signos (CSV)</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Planilla estructurada con tomas de PA, FC, FR, SpO2, T° y Glucemia.</p>
                    </div>
                </div>
                <button type="button"
                        @click="modalExportar = false; activeTab = 'signos'; $wire.cambiarTab('signos')"
                        class="w-full py-2 px-3 rounded-xl bg-[var(--rm-success-action)] text-[var(--rm-text-inverse)] font-bold text-xs hover:bg-[var(--rm-action-primary)] transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-table"></i>
                    <span>Ver y Exportar en Signos</span>
                </button>
            </div>

            {{-- Opción 3: Plan Farmacológico --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-[var(--rm-warning-strong)] hover:bg-[var(--rm-warning-soft)] transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] shrink-0">
                        <i class="ph-bold ph-pill text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Cronograma Farmacológico</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Receta y tratamientos activos, dosis programadas y adherencia.</p>
                    </div>
                </div>
                <button type="button"
                        @click="modalExportar = false; activeTab = 'medicacion'; $wire.cambiarTab('medicacion')"
                        class="w-full py-2 px-3 rounded-xl bg-[var(--rm-warning-action)] text-[var(--rm-text-inverse)] font-bold text-xs hover:bg-[var(--rm-warning-action)] transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-eye"></i>
                    <span>Consultar en Medicación</span>
                </button>
            </div>

            {{-- Opción 4: Documentos y Estudios --}}
            <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:border-[var(--rm-border-strong)] hover:bg-[var(--rm-action-primary-soft)] transition flex flex-col justify-between gap-3">
                <div class="flex items-start gap-2.5">
                    <span class="p-2 rounded-xl bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] shrink-0">
                        <i class="ph-bold ph-folder-open text-xl"></i>
                    </span>
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)]">Expediente Digital</h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Laboratorios, estudios de imagenología y documentación de ingreso.</p>
                    </div>
                </div>
                <button type="button"
                        @click="modalExportar = false; activeTab = 'documentos'; $wire.cambiarTab('documentos')"
                        class="w-full py-2 px-3 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] font-bold text-xs hover:bg-[var(--rm-action-primary)] transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-folder"></i>
                    <span>Ir a Documentación</span>
                </button>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-[var(--rm-border)] pt-3 text-xs">
            <span class="text-[11px] text-[var(--rm-text-muted)] flex items-center gap-1">
                <i class="ph-bold ph-shield-check text-[var(--rm-success-strong)] text-sm"></i>
                Documento trazable y validado por auditoría clínica.
            </span>
            <button type="button"
                    @click="modalExportar = false"
                    class="px-4 py-2 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] font-bold text-[var(--rm-text-title)] hover:bg-[var(--rm-border)] transition cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>
</div>


{{-- ========================================================================= --}}
