    {{-- 11. PANEL LATERAL DERECHO (CANONICAL DRAWER - RM-DRAWER)                   --}}
    {{-- ========================================================================= --}}
    <div x-show="drawerMedAbierto"
         x-cloak
         class="relative z-50">

        {{-- Backdrop con blur --}}
        <div x-show="drawerMedAbierto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="drawerMedAbierto = false"
             class="fixed inset-0 bg-[var(--rm-modal-overlay)] backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="drawerMedAbierto"
                         x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-md bg-[var(--rm-surface)] border-l border-[var(--rm-border)] shadow-2xl flex flex-col justify-between">

                        {{-- Drawer Header --}}
                        <div class="p-5 border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)]">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)]">
                                    PANEL LATERAL DE CONSULTA
                                </span>
                                <button type="button"
                                        @click="drawerMedAbierto = false"
                                        class="h-8 w-8 rounded-full flex items-center justify-center text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface)] transition">
                                    <i class="ph-bold ph-x text-base"></i>
                                </button>
                            </div>
                            <h3 class="text-base font-bold text-[var(--rm-text-title)] mt-2 uppercase tracking-tight">
                                <span x-text="drawerPaso === 'administrar' ? 'REGISTRAR ADMINISTRACIÓN' : (drawerPaso === 'justificar' ? 'JUSTIFICAR DEMORA' : 'DETALLE DE MEDICACIÓN')"></span>
                            </h3>
                            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                                <span x-text="drawerPaso === 'administrar' ? 'Registro asistencial de enfermería de la toma programada' : (drawerPaso === 'justificar' ? 'Registro del motivo clínico de omisión o retraso' : 'Información completa del fármaco y protocolo de seguridad')"></span>
                            </p>
                        </div>

                        {{-- Drawer Body Scrollable --}}
                        <div class="p-5 overflow-y-auto space-y-4 flex-1 text-xs">

                            {{-- BLOQUE 1: Ficha del Medicamento --}}
                            <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] flex items-center justify-center shrink-0">
                                        <i class="ph-bold ph-pill text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-[var(--rm-text-title)]" x-text="medSeleccionado.nombre"></h4>
                                        <p class="text-[11px] text-[var(--rm-text-muted)]" x-text="medSeleccionado.presentacion"></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[var(--rm-border)]">
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Dosis</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="medSeleccionado.dosis"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Vía</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="medSeleccionado.via"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Horario</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="medSeleccionado.horarioAmPm || medSeleccionado.horario"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Frecuencia</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="medSeleccionado.frecuencia"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- PASO: DETALLE --}}
                            <template x-if="drawerPaso === 'detalle'">
                                <div class="space-y-4">
                                    {{-- Indicación y Prescriptor --}}
                                    <div class="space-y-2">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Indicación Clínica</span>
                                        <p class="text-xs text-[var(--rm-text-body)] p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]" x-text="medSeleccionado.indicacion"></p>
                                        <p class="text-[11px] text-[var(--rm-text-muted)] font-medium">
                                            Prescrito por: <strong class="text-[var(--rm-text-title)]" x-text="medSeleccionado.medico"></strong>
                                        </p>
                                    </div>

                                    {{-- Precauciones y Alertas --}}
                                    <div class="p-3.5 rounded-2xl border border-[var(--rm-warning-strong)] bg-[var(--rm-warning-soft)] space-y-1.5">
                                        <div class="flex items-center gap-1.5 text-[var(--rm-warning-strong)] font-bold text-[11px]">
                                            <i class="ph-bold ph-shield-warning text-[var(--rm-warning-strong)] text-sm"></i>
                                            <span>PRECAUCIONES Y ALERTAS</span>
                                        </div>
                                        <p class="text-[11px] text-[var(--rm-warning-strong)]" x-text="medSeleccionado.precauciones"></p>
                                    </div>

                                    {{-- Documentos Relacionados --}}
                                    <div class="space-y-2 pt-1">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Documentos Relacionados</span>
                                        <div class="space-y-1.5">
                                            <div class="p-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i class="ph-bold ph-file-text text-[var(--rm-info-strong)] text-base"></i>
                                                    <span class="font-bold text-[var(--rm-text-title)] text-[11px]" x-text="medSeleccionado.documentoPlan"></span>
                                                </div>
                                                <i class="ph-bold ph-arrow-square-out text-[var(--rm-text-muted)]"></i>
                                            </div>
                                            <div class="p-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i class="ph-bold ph-file-plus text-[var(--rm-success-strong)] text-base"></i>
                                                    <span class="font-bold text-[var(--rm-text-title)] text-[11px]" x-text="medSeleccionado.documentoNota"></span>
                                                </div>
                                                <i class="ph-bold ph-arrow-square-out text-[var(--rm-text-muted)]"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- PASO: ADMINISTRAR AHORA / FORMULARIO --}}
                            <template x-if="drawerPaso === 'administrar' || drawerPaso === 'justificar'">
                                <div class="space-y-3.5">
                                    {{-- Aviso de sólo lectura de prescripción --}}
                                    <div class="p-2.5 rounded-xl bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] text-[var(--rm-info-strong)] text-[11px] flex items-center gap-2">
                                        <i class="ph-bold ph-lock-key text-[var(--rm-info-strong)] text-sm shrink-0"></i>
                                        <span>Datos prescritos por médico bloqueados para seguridad. Registre los datos de ejecución.</span>
                                    </div>

                                    {{-- Resultado de administración --}}
                                    <div>
                                        <label class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block mb-1">
                                            Resultado de la acción *
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button"
                                                    @click="formAdmin.resultado = 'ADMINISTRADA'"
                                                    :class="formAdmin.resultado === 'ADMINISTRADA' ? 'bg-[var(--rm-success-action)] text-[var(--rm-text-inverse)] font-bold' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] font-bold border border-[var(--rm-border)]'"
                                                    class="py-2 px-3 rounded-xl text-center transition cursor-pointer text-xs">
                                                ✓ Administrada
                                            </button>
                                            <button type="button"
                                                    @click="formAdmin.resultado = 'OMITIDA'"
                                                    :class="formAdmin.resultado === 'OMITIDA' ? 'bg-[var(--rm-warning-action)] text-[var(--rm-text-inverse)] font-bold' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] font-bold border border-[var(--rm-border)]'"
                                                    class="py-2 px-3 rounded-xl text-center transition cursor-pointer text-xs">
                                                ✕ Omitida / Justificada
                                            </button>
                                        </div>
                                    </div>

                                                                        {{-- Hora real --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">
                                                Hora de administración real *
                                            </label>
                                            <button type="button"
                                                    @click="formAdmin.horaReal = new Date().toTimeString().slice(0,5)"
                                                    class="text-[10px] font-bold text-[var(--rm-action-primary)] hover:underline flex items-center gap-1 cursor-pointer">
                                                <i class="ph-bold ph-clock"></i>
                                                <span>Usar hora actual (<span x-text="relojPC.horaCorta"></span>)</span>
                                            </button>
                                        </div>
                                        <input type="time"
                                               x-model="formAdmin.horaReal"
                                               class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2 text-xs font-bold text-[var(--rm-text-title)]">
                                    </div>

                                    {{-- Motivo de omisión si aplica --}}
                                    <template x-if="formAdmin.resultado === 'OMITIDA'">
                                        <div>
                                            <label class="text-[10.5px] font-bold uppercase text-[var(--rm-warning-strong)] tracking-wider block mb-1">
                                                Motivo justificado de omisión / demora *
                                            </label>
                                            <textarea x-model="formAdmin.motivoOmision"
                                                      rows="2"
                                                      placeholder="Indique la causa clínica o asistencial..."
                                                      class="w-full rounded-xl border border-[var(--rm-warning-strong)] bg-[var(--rm-warning-soft)] p-2.5 text-xs text-[var(--rm-text-title)]"></textarea>
                                        </div>
                                    </template>

                                    {{-- Observaciones clínicas --}}
                                    <div>
                                        <label class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block mb-1">
                                            Observaciones asistenciales
                                        </label>
                                        <textarea x-model="formAdmin.observaciones"
                                                  rows="2"
                                                  placeholder="Tolerancia, ingesta hídrica, signos asociados..."
                                                  class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 text-xs text-[var(--rm-text-title)]"></textarea>
                                    </div>

                                    {{-- Checklist de verificación de enfermería --}}
                                    <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1.5">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="formAdmin.checklistVerificado" class="rounded text-[var(--rm-action-primary)] focus:ring-0">
                                            <span class="text-[11px] font-bold text-[var(--rm-text-title)]">Verificación de 5 correctos de enfermería</span>
                                        </label>
                                        <p class="text-[10px] text-[var(--rm-text-muted)] pl-5">
                                            Residente correcto, fármaco correcto, dosis correcta, vía correcta y horario verificado.
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Drawer Footer Fijo --}}
                        <div class="p-4 border-t border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                            <button type="button"
                                    @click="drawerMedAbierto = false"
                                    class="px-4 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] transition cursor-pointer">
                                Cerrar
                            </button>

                            <template x-if="drawerPaso === 'detalle'">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="drawerPaso = 'justificar'"
                                            class="px-3 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                                        Justificar demora
                                    </button>
                                    <button type="button"
                                            @click="drawerPaso = 'administrar'"
                                            class="px-4 py-2.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-[var(--rm-text-on-primary)] text-xs font-bold transition cursor-pointer shadow-sm flex items-center gap-1.5">
                                        <i class="ph-bold ph-check"></i>
                                        <span>ADMINISTRAR AHORA</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="drawerPaso === 'administrar' || drawerPaso === 'justificar'">
                                <button type="button"
                                        @click="confirmarAdministracion()"
                                        class="px-5 py-2.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-[var(--rm-text-on-primary)] text-xs font-bold transition cursor-pointer shadow-sm flex items-center gap-1.5">
                                    <i class="ph-bold ph-check-circle"></i>
                                    <span>CONFIRMAR REGISTRO</span>
                                </button>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
