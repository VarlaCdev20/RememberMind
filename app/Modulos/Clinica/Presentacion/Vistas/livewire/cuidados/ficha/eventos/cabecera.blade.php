    {{-- 1. CABECERA DEL MÓDULO                                                    --}}
    {{-- ========================================================================= --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 sm:p-5 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-xs">
        <div class="flex items-center gap-3.5">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)] shadow-2xs dark:bg-[var(--rm-info-soft)] dark:text-[var(--rm-info-strong)] dark:border-[var(--rm-info-strong)]">
                <i class="ph-bold ph-shield-warning text-2xl"></i>
            </span>
            <div>
                <h1 class="text-lg sm:text-xl font-bold text-[var(--rm-text-title)] tracking-tight">
                    Eventos clínicos
                </h1>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Historial de incidentes, caídas, lesiones y eventos clínicos relevantes del residente.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if(!$consultaEnfermeria)
<button type="button"
                    wire:click="abrirModalRegistrarEvento"
                    class="px-4 py-2.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-[var(--rm-text-on-primary)] font-extrabold text-xs transition cursor-pointer shadow-sm flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-base"></i>
                <span>Registrar evento</span>
            </button>
@endif
        </div>
    </div>

    {{-- ========================================================================= --}}
