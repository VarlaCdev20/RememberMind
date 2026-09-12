@if($modalCerrar && $alertaActiva)
@php
    $badgeEstadoClass = match($alertaActiva->estado) {
        'ABIERTA' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800',
        'EN_ATENCION' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
        'CERRADA' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800',
        default => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700'
    };
    $estadoTexto = match($alertaActiva->estado) {
        'ABIERTA' => 'Por Atender',
        'EN_ATENCION' => 'En Atención',
        'CERRADA' => 'Resuelta',
        default => $alertaActiva->estado
    };
@endphp
<div class="rm-modal-backdrop"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modal-cerrar-title"
     x-data
     x-on:keydown.escape.window="$wire.cerrarModales()">
    <!-- Backdrop dismiss -->
    <div class="fixed inset-0" wire:click="cerrarModales"></div>

    <!-- Modal Card Pequeño / Mediano -->
    <div class="rm-modal rm-modal-sm z-10 font-sans shadow-2xl rounded-2xl overflow-hidden border border-[var(--rm-border)] dark:border-slate-700/80 bg-[var(--rm-surface)] dark:bg-[#18222A]" @click.stop>
        <!-- Header -->
        <div class="rm-modal-header shrink-0 px-5 py-3.5 border-b border-[var(--rm-border)] dark:border-slate-700/80 bg-[var(--rm-surface-soft)] dark:bg-[#131B22] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 shadow-xs">
                    <i class="ph-bold ph-check-circle text-xl"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-700/50 shadow-xs flex items-center gap-1">
                            <i class="ph-bold ph-check-circle"></i> Formulario de Cierre y Archivo
                        </span>
                    </div>
                    <h3 id="modal-cerrar-title" class="text-base font-bold text-[var(--rm-text-title)] dark:text-slate-100">
                        Cerrar y Archivar Alerta
                    </h3>
                    <p class="text-xs text-[var(--rm-text-muted)] dark:text-slate-400">
                        Resolución asistencial conservando el historial clínico
                    </p>
                </div>
            </div>
            <button type="button"
                wire:click="cerrarModales"
                aria-label="Cerrar modal"
                class="h-8 w-8 rounded-lg flex items-center justify-center text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-200/50 dark:hover:bg-slate-800 transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="rm-modal-body flex-1 min-h-0 overflow-y-auto p-5 sm:p-6 space-y-4 text-xs bg-[var(--rm-surface)] dark:bg-[#18222A]">
            @if ($errors->any())
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 text-rose-800 dark:text-rose-300">
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Resumen Contextual de la Alerta -->
            <div class="p-4 rounded-xl bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)] space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-[var(--rm-text-title)] dark:text-slate-100 text-sm">
                            {{ $alertaActiva->adultoMayor?->ap_paterno }} {{ $alertaActiva->adultoMayor?->ap_materno }} {{ $alertaActiva->adultoMayor?->nombres }}
                        </h4>
                        <p class="text-[11px] text-[var(--rm-text-muted)] dark:text-slate-400 font-mono">
                            {{ $alertaActiva->adultoMayor?->ubicacion_texto ?? 'Sin ubicación asignada' }}
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold uppercase border {{ $badgeEstadoClass }}">
                        {{ $estadoTexto }}
                    </span>
                </div>

                <div class="pt-2 border-t border-[var(--rm-border-soft)] dark:border-slate-700/50 text-xs text-[var(--rm-text-body)] dark:text-slate-300">
                    <p class="truncate"><strong class="text-[var(--rm-text-title)] dark:text-slate-100">Alerta:</strong> {{ $alertaActiva->tipo_alerta }}</p>
                    <p class="text-[11px] text-[var(--rm-text-muted)] dark:text-slate-400 line-clamp-2 mt-0.5">{{ $alertaActiva->motivo }}</p>
                </div>
            </div>

            <!-- Principio Clínico: Cerrar no es borrar -->
            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-900 dark:text-emerald-200 space-y-1">
                <p class="font-bold flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400">
                    <i class="ph-bold ph-shield-check text-base"></i>
                    Conservación Integral del Historial Clínico
                </p>
                <p class="leading-relaxed text-[11px] text-emerald-800/90 dark:text-emerald-300/90">
                    Al confirmar, el estado cambiará a <strong>RESUELTA</strong>. La alerta y todas sus intervenciones previas permanecerán disponibles permanentemente para auditoría médica.
                </p>
            </div>

            <!-- Campo Justificación de Cierre -->
            <div class="p-4 rounded-xl bg-[var(--rm-surface-alt)]/60 border border-[var(--rm-border)] space-y-2">
                <label for="observacionCierreInput" class="block text-xs font-semibold text-[var(--rm-text-title)] dark:text-slate-200 mb-1 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <i class="ph-bold ph-seal-check text-emerald-600 dark:text-emerald-400"></i>
                        Justificación Clínica del Cierre <span class="text-rose-500">*</span>
                    </span>
                    <span class="text-[10.5px] text-[var(--rm-text-muted)] dark:text-slate-400 font-normal">Mín. 5 caracteres</span>
                </label>
                <textarea id="observacionCierreInput"
                    wire:model="observacionCierre"
                    rows="3"
                    maxlength="1000"
                    class="rm-textarea text-xs @error('observacionCierre') border-rose-500 dark:border-rose-500 @enderror"
                    placeholder="Detalle el motivo del cierre asistencial (ej.: parámetros normalizados tras medicación y reposo, residente asintomático, alta médica confirmada)..."></textarea>
                @error('observacionCierre')
                    <span class="text-[11px] text-rose-600 dark:text-rose-400 font-semibold mt-1 flex items-center gap-1">
                        <i class="ph-bold ph-warning-circle"></i> {{ $message }}
                    </span>
                @enderror
            </div>
        </div>

        <!-- Footer -->
        <div class="rm-modal-footer shrink-0 px-5 py-3 border-t border-[var(--rm-border)] dark:border-slate-700/80 bg-[var(--rm-surface-soft)] dark:bg-[#131B22] flex items-center justify-between">
            <button type="button"
                wire:click="cerrarModales"
                class="px-4 py-2 rounded-xl text-xs font-bold text-[var(--rm-text-muted)] dark:text-slate-400 hover:text-[var(--rm-text-title)] dark:hover:text-slate-200 hover:bg-slate-200/50 dark:hover:bg-slate-800 transition cursor-pointer">
                Cancelar
            </button>

            <button type="button"
                wire:click="confirmarCierre"
                wire:loading.attr="disabled"
                class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                <span wire:loading.remove wire:target="confirmarCierre" class="flex items-center gap-1.5">
                    <i class="ph-bold ph-archive-box text-base"></i>
                    <span>Confirmar Cierre</span>
                </span>
                <span wire:loading wire:target="confirmarCierre" class="flex items-center gap-1.5">
                    <i class="ph ph-arrows-clockwise text-base animate-spin"></i>
                    <span>Cerrando...</span>
                </span>
            </button>
        </div>
    </div>
</div>
@endif
