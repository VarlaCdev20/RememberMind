@if($modalAtender)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-data
     x-on:keydown.escape.window="$wire.cerrarModales()">
    <!-- Backdrop click to close -->
    <div class="fixed inset-0" wire:click="cerrarModales"></div>

    <!-- Modal Card -->
    <div class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl z-10 font-sans">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 px-6 py-4">
            <div class="flex items-center gap-2.5 text-blue-600 dark:text-blue-400">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                    <i class="ph ph-stethoscope text-xl"></i>
                </span>
                <h3 class="text-lg font-black text-slate-800 dark:text-white">Atender Alerta Clínica</h3>
            </div>
            <button type="button" wire:click="cerrarModales" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 p-3 text-xs font-bold text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-950 dark:text-blue-200 shadow-sm">
                <p class="font-bold flex items-center gap-1.5 mb-1 text-blue-800 dark:text-blue-300">
                    <i class="ph ph-info text-base"></i>
                    Protocolo de Intervención Asistencial
                </p>
                <p class="text-blue-900/80 dark:text-blue-300 leading-relaxed">
                    Al confirmar, el estado cambiará a <strong>EN ATENCIÓN</strong> y la acción quedará asentada cronológicamente en el expediente del residente con su usuario responsable.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Acción Inmediata Tomada <span class="text-rose-500">*</span>
                </label>
                <textarea wire:model="accionTomada"
                    rows="4"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all outline-none"
                    placeholder="Describa la intervención inicial realizada (ej.: toma de signos vitales de verificación, reposo en cama a 45°, notificación inmediata al médico tratante, administración de fármaco)..."></textarea>
                <p class="mt-1 text-[11px] text-slate-400">Mínimo 5 caracteres. Sea preciso con las medidas adoptadas.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-900/30 px-6 py-4">
            <button type="button"
                wire:click="cerrarModales"
                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                Cancelar
            </button>

            <button type="button"
                wire:click="guardarAtencion"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md transition cursor-pointer disabled:opacity-60">
                <span wire:loading.remove wire:target="guardarAtencion" class="flex items-center gap-1.5">
                    <i class="ph ph-check text-base"></i>
                    <span>Registrar Atención</span>
                </span>
                <span wire:loading wire:target="guardarAtencion">Registrando...</span>
            </button>
        </div>
    </div>
</div>
@endif