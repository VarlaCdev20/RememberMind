{{-- MODAL EMERGENTE CENTRADO: REGISTRAR OMISIÓN — PALETA UNIFICADA --}}
@if($modalOmisionAbierto)
<div
 x-data="{ omisionOpen: true }"
 x-cloak>
 <template x-teleport="body">
 <div
 x-show="omisionOpen"
 x-on:keydown.escape.window="$wire.cerrarModalOmision()"
 class="fixed inset-0 z-[99999] overflow-y-auto font-sans"
 aria-labelledby="modal-omision-title"
 role="dialog"
 aria-modal="true"
 style="display: none;">

 <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
 {{-- Backdrop con desenfoque suave --}}
 <div
 x-show="$wire.modalOmisionAbierto"
 x-transition:enter="ease-out duration-200"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="ease-in duration-150"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0"
 wire:click="cerrarModalOmision"
 class="fixed inset-0 bg-[var(--rm-modal-overlay,rgba(64,42,32,0.45))] backdrop-blur-[2px] transition-opacity">
 </div>

 {{-- Ventana Modal Flotante Mediana Centrada (max-w-xl) --}}
 <div
 x-show="$wire.modalOmisionAbierto"
 x-transition:enter="ease-out duration-200"
 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
 x-transition:leave="ease-in duration-150"
 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
 class="relative inline-block w-full max-w-xl text-left align-middle transition-all transform bg-[var(--rm-surface-soft)] rounded-2xl border border-[var(--rm-border-soft)] shadow-[0_16px_40px_rgba(48,64,96,0.18)] dark:shadow-[0_16px_40px_rgba(0,0,0,0.6)] overflow-hidden my-6 z-10">

 {{-- Header del Modal en Alerta Terracota --}}
 <div class="px-5 py-4 border-b border-[var(--rm-border-soft)] flex items-center justify-between bg-[var(--rm-surface-soft)] ">
 <div class="flex items-center gap-3">
  <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white shadow-2xs">
  <i class="ph-bold ph-warning-circle text-xl"></i>
  </span>
  <div>
  <h3 class="text-[17px] font-[800] text-[var(--rm-text-primary)] tracking-tight" id="modal-omision-title">
  Registrar omisión
  </h3>
  <p class="text-xs text-[var(--rm-text-muted)]">
  Justificación clínica y trazabilidad de dosis no suministrada
  </p>
  </div>
 </div>

 <button
  type="button"
  wire:click="cerrarModalOmision"
  class="rounded-lg p-1.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-white hover:bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] transition cursor-pointer"
  title="Cerrar modal">
  <i class="ph ph-x text-lg"></i>
 </button>
 </div>

 {{-- Formulario de Omisión --}}
 <form wire:submit.prevent="guardarOmision">
 <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4 custom-scrollbar text-xs">

  {{-- Resumen Solo Lectura de la Dosis a Omitir --}}
  <div class="rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] p-3 space-y-2">
  <div class="flex items-center justify-between">
  <span class="text-[11px] font-[800] text-[var(--rm-action-primary)] uppercase tracking-wider flex items-center gap-1.5">
  <i class="ph-bold ph-shield-warning"></i>
  Dosis Objeto de Omisión
  </span>
  <span class="text-[10.5px] font-bold text-[var(--rm-action-primary)] font-mono">
  {{ $selectedHora ?? '08:00' }} hrs
  </span>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
  <div>
  <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Residente:</span>
  <strong class="text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Residente' }}
  </strong>
  </div>

  <div>
  <span class="text-[10.5px] text-[var(--rm-text-muted)] block">Medicamento:</span>
  <strong class="text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento' }}
   ({{ $dosisDetalle['prescripcion']['dosis'] ?? '' }})
  </strong>
  </div>
  </div>

  <div class="pt-1.5 border-t border-[var(--rm-border-soft)]/60 /40 text-[10.5px] text-[var(--rm-text-muted)] flex items-center justify-between">
  <span>Profesional que omite: <strong class="text-[var(--rm-text-primary)] ">{{ auth()->user()?->name ?? 'Enfermería en turno' }}</strong></span>
  <span class="text-[var(--rm-action-primary)] font-semibold">Trazabilidad clínica</span>
  </div>
  </div>

  {{-- Campos Editables de Omisión --}}
  <div class="space-y-3.5">

  {{-- 1. Motivo de Omisión Justificada --}}
  <div>
  <label for="formMotivoOmision" class="text-xs font-[700] text-[var(--rm-text-primary)] block mb-1">
  Motivo de Omisión Justificada <span class="text-[var(--rm-danger)] ">*</span>
  </label>

  <select
  id="formMotivoOmision"
  wire:model.live="formMotivoOmision"
  required
  class="w-full h-10 px-3 text-xs rounded-xl border {{ $errors->has('formMotivoOmision') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] focus:outline-none focus:ring-1 shadow-2xs cursor-pointer">
  <option value="">Seleccione el motivo clínico o asistencial...</option>
  <option value="Rechazo voluntario del residente">Rechazo voluntario del residente</option>
  <option value="Ayuno médico programado (analítica / procedimiento)">Ayuno médico programado (analítica / procedimiento)</option>
  <option value="Residente ausente temporalmente / en traslado">Residente ausente temporalmente / en traslado</option>
  <option value="Suspensión o modificación médica verbal">Suspensión o modificación médica verbal</option>
  <option value="Fármaco no disponible en farmacia">Fármaco no disponible en farmacia</option>
  <option value="Intolerancia gástrica o náuseas previas">Intolerancia gástrica o náuseas previas</option>
  <option value="Parámetro clínico contraindicado (PA/FC/Glucemia)">Parámetro clínico contraindicado (PA/FC/Glucemia)</option>
  <option value="OTRO">Otro motivo específico (requiere justificación detallada)</option>
  </select>

  @error('formMotivoOmision')
  <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
   <i class="ph-bold ph-warning-circle"></i> {{ $message }}
  </span>
  @enderror
  </div>

  {{-- 2. Observación / Justificación Detallada --}}
  @php
  $motivoVal = (string)($formMotivoOmision ?? $this->formMotivoOmision ?? '');
  $motivoRequiereDetalle = ($motivoVal === 'OTRO' || str_contains($motivoVal, 'verbal') || str_contains($motivoVal, 'contraindicado'));
  @endphp

  <div>
  <div class="flex items-center justify-between mb-1">
  <label for="formObservacionOmision" class="text-xs font-[700] text-[var(--rm-text-primary)] ">
   Justificación Asistencial Detallada
   @if($motivoRequiereDetalle)
   <span class="text-[var(--rm-danger)] font-bold">* (Obligatoria para este motivo)</span>
   @else
   <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Recomendada)</span>
   @endif
  </label>
  <span class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">
   {{ strlen((string)($formObservacionOmision ?? $this->formObservacionOmision ?? '')) }}/1000
  </span>
  </div>

  <textarea
  id="formObservacionOmision"
  wire:model="formObservacionOmision"
  maxlength="1000"
  rows="3"
  placeholder="{{ $motivoRequiereDetalle ? 'Detalle ampliamente la razón clínica, médico que indicó la suspensión o valores de signos vitales...' : 'Indique detalles complementarios sobre la omisión de la toma...' }}"
  class="w-full text-xs rounded-xl p-3 border {{ $errors->has('formObservacionOmision') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] dark:placeholder-[var(--rm-border)] focus:outline-none focus:ring-1 resize-none shadow-2xs"></textarea>

  @error('formObservacionOmision')
  <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
   <i class="ph-bold ph-warning-circle"></i> {{ $message }}
  </span>
  @enderror
  </div>

  </div>

 </div>

 {{-- Footer con Botón Confirmar Omisión --}}
 <div class="px-5 py-3.5 bg-[var(--rm-surface-soft)] border-t border-[var(--rm-border-soft)] flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5">
  <button
  type="button"
  wire:click="cerrarModalOmision"
  class="h-10 px-4 rounded-xl text-xs font-semibold bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition cursor-pointer">
  Cancelar
  </button>

  <button
  type="submit"
  wire:loading.attr="disabled"
  class="h-10 px-5 rounded-xl text-xs font-bold bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
  <i class="ph-bold ph-warning text-sm" wire:loading.remove wire:target="guardarOmision"></i>
  <span wire:loading.remove wire:target="guardarOmision">Confirmar Omisión</span>
  <span wire:loading wire:target="guardarOmision">Registrando omisión...</span>
  </button>
 </div>
 </form>

 </div>
 </div>
</div>
</template>
</div>
@endif
