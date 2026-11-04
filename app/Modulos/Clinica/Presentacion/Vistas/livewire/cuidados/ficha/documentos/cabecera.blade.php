    {{-- 1. HEADER DEL MÓDULO                                                      --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[var(--rm-border)] pb-4">
        <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-2xl bg-[var(--rm-action-primary)]/10 text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/20 flex items-center justify-center shrink-0">
                <i class="ph-bold ph-files text-2xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-lg font-bold text-[var(--rm-text-title)] tracking-tight">
                        Documentación
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[var(--rm-action-primary)]/10 text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/20 uppercase">
                        Expediente
                    </span>
                </div>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Gestión y consulta de documentos clínicos, administrativos y personales del residente.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            {{-- Botón Subir Documento (permiso-gated) --}}
            @if(!$consultaEnfermeria)
<button type="button"
                    wire:click="abrirModalSubirDoc"
                    class="px-4 py-2.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary)]/90 text-[var(--rm-text-on-primary)] font-bold text-xs transition-all shadow-sm hover:shadow flex items-center gap-2 cursor-pointer active:scale-95">
                <i class="ph-bold ph-upload-simple text-sm"></i>
                <span>+ Subir documento</span>
            </button>
@endif
        </div>
    </div>

    {{-- ========================================================================= --}}
