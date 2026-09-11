@if($modalCrear)
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-data
     x-on:keydown.escape.window="$wire.cerrarModales()">
    <!-- Backdrop click to close -->
    <div class="fixed inset-0" wire:click="cerrarModales"></div>

    <!-- Modal Card -->
    <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xl z-10 font-sans">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 px-6 py-4">
            <div class="flex items-center gap-2.5 text-rose-600 dark:text-rose-400">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400">
                    <i class="ph ph-bell-ringing text-xl"></i>
                </span>
                <h3 class="text-lg font-black text-slate-800 dark:text-white">Registrar Nueva Alerta Clínica</h3>
            </div>
            <button type="button" wire:click="cerrarModales" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Scrollable Body -->
        <div class="overflow-y-auto p-6 space-y-4 flex-1">
            @if ($errors->any())
                <div class="rounded-xl bg-rose-50 dark:bg-rose-950/40 p-3 text-xs font-bold text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Adulto Mayor --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Adulto Mayor Afectado <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="codAm"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none">
                        <option value="">-- Seleccione Residente --</option>
                        @foreach($adultos as $ad)
                            <option value="{{ $ad->cod_am }}">
                                {{ $ad->nombres }} {{ $ad->ap_paterno }} ({{ $ad->cod_am }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Origen --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Canal u Origen <span class="text-rose-500">*</span>
                    </label>
                    <select wire:model="origen"
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3 py-2.5 text-sm text-slate-800 dark:text-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none">
                        <option value="SIGNOS">Signos Vitales</option>
                        <option value="MEDICACION">Medicación</option>
                        <option value="INCIDENTE">Incidente Asistencial</option>
                        <option value="PLAN">Plan de Cuidado</option>
                        <option value="SEGUIMIENTO">Seguimiento Clínico</option>
                        <option value="SOLICITUD_MEDICA">Solicitud Médica</option>
                        <option value="MANUAL">Registro Manual</option>
                        <option value="FICHA">Ficha Clínica</option>
                        <option value="VALORACION">Valoración Funcional</option>
                    </select>
                </div>

                {{-- Tipo Alerta --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Tipo o Diagnóstico Clínico <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                        wire:model="tipoAlerta"
                        placeholder="Ej.: CRISIS_HIPERTENSIVA, PICO_FEBRIL, CAIDA..."
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none uppercase font-mono" />
                </div>
            </div>

            {{-- Nivel de Severidad Flotante con Colores Enteros --}}
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                    Nivel de Severidad y Prioridad <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    {{-- Crítico --}}
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'CRITICO' ? 'border-rose-600 bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'border-slate-200 dark:border-slate-700 hover:border-rose-300 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800' }}">
                        <input type="radio" wire:model.live="nivel" value="CRITICO" class="sr-only" />
                        <span class="text-sm font-extrabold flex items-center gap-1.5">
                            <i class="ph ph-warning-octagon text-lg"></i>
                            Crítico
                        </span>
                        <span class="text-[10px] mt-0.5 opacity-80">Riesgo vital inmediato</span>
                    </label>

                    {{-- Alto --}}
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'ALTO' ? 'border-amber-500 bg-amber-500 text-white shadow-md shadow-amber-500/30' : 'border-slate-200 dark:border-slate-700 hover:border-amber-300 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800' }}">
                        <input type="radio" wire:model.live="nivel" value="ALTO" class="sr-only" />
                        <span class="text-sm font-extrabold flex items-center gap-1.5">
                            <i class="ph ph-warning text-lg"></i>
                            Alto
                        </span>
                        <span class="text-[10px] mt-0.5 opacity-80">Atención urgente</span>
                    </label>

                    {{-- Medio --}}
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'MEDIO' ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'border-slate-200 dark:border-slate-700 hover:border-blue-300 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800' }}">
                        <input type="radio" wire:model.live="nivel" value="MEDIO" class="sr-only" />
                        <span class="text-sm font-extrabold flex items-center gap-1.5">
                            <i class="ph ph-info text-lg"></i>
                            Medio
                        </span>
                        <span class="text-[10px] mt-0.5 opacity-80">Seguimiento en turno</span>
                    </label>

                    {{-- Bajo --}}
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $nivel === 'BAJO' ? 'border-emerald-600 bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'border-slate-200 dark:border-slate-700 hover:border-emerald-300 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800' }}">
                        <input type="radio" wire:model.live="nivel" value="BAJO" class="sr-only" />
                        <span class="text-sm font-extrabold flex items-center gap-1.5">
                            <i class="ph ph-check-circle text-lg"></i>
                            Bajo
                        </span>
                        <span class="text-[10px] mt-0.5 opacity-80">Preventivo de rutina</span>
                    </label>
                </div>
            </div>

            {{-- Motivo Clínico --}}
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Motivo Clínico Detallado <span class="text-rose-500">*</span>
                </label>
                <textarea wire:model="motivo"
                    rows="4"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-800 dark:text-white placeholder-slate-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all outline-none"
                    placeholder="Describa con precisión los hallazgos clínicos, cifras de signos vitales, sintomatología del paciente o el incidente ocurrido..."></textarea>
                <p class="mt-1 text-[11px] text-slate-400">Mínimo 10 caracteres. Sea riguroso con la información clínica.</p>
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
                wire:click="guardarAlerta"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md transition cursor-pointer disabled:opacity-60">
                <span wire:loading.remove wire:target="guardarAlerta" class="flex items-center gap-1.5">
                    <i class="ph ph-floppy-disk text-base"></i>
                    <span>Guardar Alerta</span>
                </span>
                <span wire:loading wire:target="guardarAlerta">Guardando...</span>
            </button>
        </div>
    </div>
</div>
@endif