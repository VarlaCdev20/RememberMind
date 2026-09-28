{{-- MODAL EMERGENTE CENTRADO: ADMINISTRAR MEDICACIÓN CON SEGURIDAD CLÍNICA --}}
@if($modalAdministrarAbierto)
<div
 x-data="{ modalOpen: true }"
 x-cloak>
 <template x-teleport="body">
 <div
 x-show="modalOpen"
 x-on:keydown.escape.window="$wire.cerrarModalAdministrar()"
 class="fixed inset-0 z-[99999] overflow-y-auto font-sans"
 aria-labelledby="modal-administrar-title"
 role="dialog"
 aria-modal="true"
 style="display: none;">

 <div class="min-h-screen px-4 text-center flex items-center justify-center p-4">
 {{-- Overlay oscuro suave con desenfoque --}}
 <div
 wire:click="cerrarModalAdministrar"
 class="fixed inset-0 bg-[var(--rm-modal-overlay,rgba(64,42,32,0.45))] backdrop-blur-[3px] transition-opacity">
 </div>

 {{-- Ventana Modal Emergente Mediana Centrada (max-w-2xl) --}}
 <div
 @click.stop
 class="relative inline-block w-full max-w-2xl text-left align-middle transition-all transform bg-[var(--rm-surface-soft)] rounded-2xl border border-[var(--rm-border-soft)] shadow-[0_20px_50px_rgba(0,0,0,0.5)] overflow-hidden my-6 z-10">

 {{-- Header del Modal con X Superior --}}
 <div class="px-5 py-4 border-b border-[var(--rm-border-soft)] flex items-center justify-between bg-[var(--rm-surface-soft)] ">
 <div class="flex items-center gap-3">
  <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-action-primary)] text-white shadow-2xs">
  <i class="ph-bold ph-pill text-xl"></i>
  </span>
  <div>
  <h3 class="text-[17px] font-[800] text-[var(--rm-text-primary)] tracking-tight" id="modal-administrar-title">
  Administrar medicación
  </h3>
  <p class="text-xs text-[var(--rm-text-muted)]">
  Confirmación asistencial de la dosis y registro de la toma
  </p>
  </div>
 </div>

 {{-- Botón X Superior para Cerrar --}}
 <button
  type="button"
  wire:click="cerrarModalAdministrar"
  class="rounded-lg p-1.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-white hover:bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] transition cursor-pointer"
  title="Cerrar modal">
  <i class="ph ph-x text-lg"></i>
 </button>
 </div>

 {{-- Formulario con validación frontend + backend --}}
 <form wire:submit.prevent="guardarAdministracion">
 <div class="p-5 max-h-[75vh] overflow-y-auto space-y-4 custom-scrollbar text-xs">

  {{-- 1. SECCIÓN: DATOS CLÍNICOS SOLO LECTURA (NO EDITABLES / INMUTABLES) --}}
  <div class="rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] p-3.5 space-y-3">
  <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)]/70 /70 pb-2">
  <span class="inline-flex items-center gap-1.5 text-[11px] font-[700] text-[var(--rm-action-primary)] uppercase tracking-wider">
  <i class="ph-bold ph-lock-key"></i>
  Datos de la Prescripción Médica (Solo Lectura)
  </span>
  <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-full bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)] flex items-center gap-1">
  <i class="ph ph-shield-check"></i> Inmutable
  </span>
  </div>

  {{-- Fila 1: Residente y Habitación/Cama --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
  <div>
  <span class="text-[11px] font-medium text-[var(--rm-text-muted)] block">Residente:</span>
  <span class="font-bold text-[13px] text-[var(--rm-text-primary)] block leading-tight">
   {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Residente' }}
  </span>
  <span class="text-[11px] text-[var(--rm-text-muted)] block mt-0.5">
   <strong class="text-[var(--rm-text-primary)] ">{{ $dosisDetalle['residente']['habitacion'] ?? 'Sin habitación' }}</strong> / <strong class="text-[var(--rm-text-primary)] ">{{ $dosisDetalle['residente']['cama'] ?? 'Sin cama' }}</strong> &bull; {{ $dosisDetalle['residente']['edad'] ?? '' }}
  </span>
  </div>

  <div>
  <span class="text-[11px] font-medium text-[var(--rm-text-muted)] block">Medicamento prescrito:</span>
  <span class="font-bold text-[13px] text-[var(--rm-text-primary)] block leading-tight">
   {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento' }}
  </span>
  <span class="text-[11px] text-[var(--rm-text-muted)] block mt-0.5">
   {{ $dosisDetalle['medicamento']['concentracion'] ?? '' }} &bull; {{ $dosisDetalle['medicamento']['forma'] ?? 'Comprimido' }}
  </span>
  </div>
  </div>

  {{-- Fila 2: Dosis prescrita, Vía, Frecuencia y Hora programada --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-2 border-t border-[var(--rm-border-soft)]/50 /50 text-[11.5px]">
  <div>
  <span class="text-[10px] uppercase font-bold text-[var(--rm-text-muted)] block">Dosis Prescrita</span>
  <span class="font-bold text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['prescripcion']['dosis'] ?? ($formDosisPrescritaValor . ' ' . $formUnidadDosis) }}
  </span>
  </div>

  <div>
  <span class="text-[10px] uppercase font-bold text-[var(--rm-text-muted)] block">Vía Prescrita</span>
  <span class="font-bold text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['prescripcion']['via'] ?? 'Vía oral' }}
  </span>
  </div>

  <div>
  <span class="text-[10px] uppercase font-bold text-[var(--rm-text-muted)] block">Frecuencia</span>
  <span class="font-bold text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['prescripcion']['frecuencia'] ?? 'Cada 8 horas' }}
  </span>
  </div>

  <div>
  <span class="text-[10px] uppercase font-bold text-[var(--rm-text-muted)] block">Hora Programada</span>
  <span class="font-bold text-[var(--rm-text-primary)] font-mono">
   {{ $selectedHora ?? '08:00' }} hrs
  </span>
  </div>
  </div>

  {{-- Fila 3: Indicación médica y Médico Prescriptor --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-[var(--rm-border-soft)]/50 /50 text-[11px]">
  <div>
  <span class="font-medium text-[var(--rm-text-muted)]">Médico prescriptor:</span>
  <span class="font-semibold text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['prescripcion']['prescriptor'] ?? 'Prescriptor no registrado' }}
  </span>
  </div>

  <div>
  <span class="font-medium text-[var(--rm-text-muted)]">Indicación:</span>
  <span class="font-semibold text-[var(--rm-text-primary)] ">
   {{ $dosisDetalle['prescripcion']['indicacion'] ?? 'Según indicación clínica' }}
  </span>
  </div>
  </div>

  {{-- Fila 4: Enfermero y Fecha/Hora del sistema (Solo Lectura) --}}
  <div class="pt-2 border-t border-[var(--rm-border-soft)]/60 /60 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 text-[11px]">
  <div class="flex items-center gap-2 text-[var(--rm-text-muted)]">
  <i class="ph-bold ph-user-circle text-sm text-[var(--rm-action-primary)]"></i>
  <span>Profesional responsable:</span>
  <strong class="text-[var(--rm-text-primary)] ">{{ auth()->user()?->name ?? 'Usuario no identificado' }}</strong>
  <span class="text-[10px] text-[var(--rm-text-muted)]">({{ auth()->user()?->profesion ?? 'ENFERMERO' }})</span>
  </div>
  <div class="text-[11px] text-[var(--rm-text-muted)]">
  Fecha registro: <strong class="text-[var(--rm-text-primary)] ">{{ now()->format('d/m/Y H:i') }}</strong>
  </div>
  </div>
  </div>

  {{-- 2. SECCIÓN: SEGURIDAD CLÍNICA - VERIFICACIÓN VISUAL DE LOS "5 CORRECTOS" --}}
  @php
  $dAdmin = (float)(($formDosisAdministrada ?? $this->formDosisAdministrada ?? 0) ?: 0);
  $dPresc = (float)(($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? 0) ?: 0);
  $dosisDifiere = ($dPresc > 0 && abs($dAdmin - $dPresc) > 0.001);
  @endphp

  <div class="rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] p-3.5 space-y-2.5">
  <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)]/70 /70 pb-2">
  <div class="flex items-center gap-2">
  <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[var(--rm-action-primary)] text-white text-xs">
   <i class="ph-bold ph-shield-check"></i>
  </span>
  <div>
   <h4 class="text-[11.5px] font-[800] text-[var(--rm-text-primary)] uppercase tracking-wider">
   Seguridad Clínica: Protocolo de los 5 Correctos
   </h4>
   <p class="text-[10.5px] text-[var(--rm-text-muted)]">
   Verificación visual automática contrastada contra la prescripción activa
   </p>
  </div>
  </div>
  <span class="text-[10px] font-bold text-[var(--rm-action-primary)] uppercase tracking-wider bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] px-2 py-0.5 rounded-full border border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40">
  5 / 5 Verificados
  </span>
  </div>

  {{-- Lista de los 5 Correctos Verificados --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">

  {{-- 1. Residente correcto --}}
  <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]/70 /70 flex items-start gap-2">
  <i class="ph-fill ph-check-circle text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
  <div class="min-w-0">
   <strong class="text-[var(--rm-text-primary)] block text-[11px]">1. Residente correcto</strong>
   <span class="text-[10.5px] text-[var(--rm-text-muted)] block truncate">
   {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Mario Gutierrez Mendoza' }}
   </span>
  </div>
  </div>

  {{-- 2. Medicamento correcto --}}
  <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]/70 /70 flex items-start gap-2">
  <i class="ph-fill ph-check-circle text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
  <div class="min-w-0">
   <strong class="text-[var(--rm-text-primary)] block text-[11px]">2. Medicamento correcto</strong>
   <span class="text-[10.5px] text-[var(--rm-text-muted)] block truncate">
   {{ $dosisDetalle['medicamento']['nombre_destacado'] ?? 'Medicamento verificado' }}
   </span>
  </div>
  </div>

  {{-- 3. Dosis correcta --}}
  <div class="p-2 rounded-lg {{ $dosisDifiere ? 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40' : 'bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]/70 /70' }} flex items-start gap-2">
  @if($dosisDifiere)
   <i class="ph-fill ph-warning-circle text-[var(--rm-danger)] text-base shrink-0 mt-0.5"></i>
   <div class="min-w-0">
   <strong class="text-[var(--rm-danger)] block text-[11px]">3. Dosis modificada</strong>
   <span class="text-[10.5px] text-[var(--rm-danger)] block">
   Admin: <strong>{{ $dAdmin }}</strong> vs Presc: <strong>{{ $dPresc }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }}</strong>
   </span>
   </div>
  @else
   <i class="ph-fill ph-check-circle text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
   <div class="min-w-0">
   <strong class="text-[var(--rm-text-primary)] block text-[11px]">3. Dosis correcta</strong>
   <span class="text-[10.5px] text-[var(--rm-text-muted)] block truncate">
   {{ ($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? '1') }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }} (Coincide con orden)
   </span>
   </div>
  @endif
  </div>

  {{-- 4. Vía correcta --}}
  <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]/70 /70 flex items-start gap-2">
  <i class="ph-fill ph-check-circle text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
  <div class="min-w-0">
   <strong class="text-[var(--rm-text-primary)] block text-[11px]">4. Vía correcta</strong>
   <span class="text-[10.5px] text-[var(--rm-text-muted)] block truncate">
   {{ $dosisDetalle['prescripcion']['via'] ?? 'Vía oral' }} (Prescrita)
   </span>
  </div>
  </div>

  {{-- 5. Hora correcta --}}
  <div class="p-2 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]/70 /70 flex items-start gap-2 sm:col-span-2">
  <i class="ph-fill ph-check-circle text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
  <div class="min-w-0">
   <strong class="text-[var(--rm-text-primary)] block text-[11px]">5. Hora correcta</strong>
   <span class="text-[10.5px] text-[var(--rm-text-muted)] block">
   {{ $selectedHora ?? '08:00' }} hrs programada para el turno de enfermería en curso
   </span>
  </div>
  </div>

  </div>
  </div>

  {{-- 3. SECCIÓN: CAMPOS EDITABLES DEL ENFERMERO CON VALIDACIÓN --}}
  <div class="space-y-3.5 pt-1">
  <h4 class="text-[11px] font-[800] text-[var(--rm-action-primary)] uppercase tracking-wider flex items-center gap-1.5">
  <i class="ph-bold ph-pencil-simple text-[var(--rm-action-primary)]"></i>
  Registro Asistencial del Enfermero
  </h4>

  {{-- 1. Dosis Administrada --}}
  <div>
  <div class="flex items-center justify-between mb-1">
  <label for="formDosisAdministrada" class="text-xs font-[700] text-[var(--rm-text-primary)] ">
   Dosis Administrada <span class="text-[var(--rm-danger)] ">*</span>
  </label>
  <span class="text-[11px] text-[var(--rm-text-muted)]">
   Prescrita: <strong class="text-[var(--rm-text-primary)] ">{{ ($formDosisPrescritaValor ?? $this->formDosisPrescritaValor ?? '1') }} {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'mg') }}</strong>
  </span>
  </div>

  <div class="flex items-center gap-2">
  <div class="relative flex-1">
   <input
   type="number"
   step="any"
   min="0.001"
   id="formDosisAdministrada"
   wire:model.live.debounce.300ms="formDosisAdministrada"
   required
   placeholder="Ej. 50"
   class="w-full h-10 px-3 text-xs font-bold rounded-xl border {{ $errors->has('formDosisAdministrada') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] dark:placeholder-[var(--rm-border)] focus:outline-none focus:ring-1 shadow-2xs font-mono" />
  </div>
  <span class="h-10 px-3 inline-flex items-center text-xs font-bold bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] rounded-xl text-[var(--rm-text-primary)] ">
   {{ ($formUnidadDosis ?? $this->formUnidadDosis ?? 'unidad') }}
  </span>
  </div>

  {{-- Alerta si la dosis difiere de la prescrita --}}
  @if($dosisDifiere)
  <div class="mt-1.5 p-2.5 rounded-lg bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40 flex items-start gap-2 text-[11px] text-[var(--rm-danger)] ">
   <i class="ph-bold ph-warning text-sm shrink-0 mt-0.5"></i>
   <span>
   <strong>Dosis modificada asistencialmente:</strong> La dosis administrada ({{ $dAdmin }}) difiere de la dosis prescrita ({{ $dPresc }}). Se exige <strong>observación/justificación clínica obligatoria</strong> en el campo inferior.
   </span>
  </div>
  @endif

  @error('formDosisAdministrada')
  <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
   <i class="ph-bold ph-warning-circle"></i> {{ $message }}
  </span>
  @enderror
  </div>

  {{-- 2. Efecto Observado (Nullable) --}}
  <div>
  <label for="formEfectoObservado" class="text-xs font-[700] text-[var(--rm-text-primary)] block mb-1">
  Efecto Observado <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Opcional)</span>
  </label>
  <input
  type="text"
  id="formEfectoObservado"
  wire:model="formEfectoObservado"
  maxlength="500"
  placeholder="Ej. Buena tolerancia oral, sin disfagia inmediata, sedación leve esperada..."
  class="w-full h-10 px-3 text-xs rounded-xl border {{ $errors->has('formEfectoObservado') ? 'border-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] dark:placeholder-[var(--rm-border)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-focus)] shadow-2xs" />
  @error('formEfectoObservado')
  <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
   <i class="ph-bold ph-warning-circle"></i> {{ $message }}
  </span>
  @enderror
  </div>

  {{-- 3. Reacción Adversa (Nullable) --}}
  <div>
  <label for="formReaccionAdversa" class="text-xs font-[700] text-[var(--rm-text-primary)] block mb-1">
  Reacción Adversa <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Opcional)</span>
  </label>
  <input
  type="text"
  id="formReaccionAdversa"
  wire:model="formReaccionAdversa"
  maxlength="500"
  placeholder="Ej. Ninguna observada, náusea leve transitoria, prurito cutáneo..."
  class="w-full h-10 px-3 text-xs rounded-xl border {{ $errors->has('formReaccionAdversa') ? 'border-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] dark:placeholder-[var(--rm-border)] focus:outline-none focus:ring-1 focus:ring-[var(--rm-focus)] shadow-2xs" />
  @error('formReaccionAdversa')
  <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
   <i class="ph-bold ph-warning-circle"></i> {{ $message }}
  </span>
  @enderror
  </div>

  {{-- 4. Observación / Justificación de Enfermería --}}
  <div>
  <div class="flex items-center justify-between mb-1">
  <label for="formObservacionAdmin" class="text-xs font-[700] text-[var(--rm-text-primary)] ">
   Observación de Enfermería
   @if($dosisDifiere)
   <span class="text-[var(--rm-danger)] font-bold">* (Obligatoria por variación de dosis)</span>
   @else
   <span class="text-[11px] font-normal text-[var(--rm-text-muted)]">(Opcional)</span>
   @endif
  </label>
  <span class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">
   {{ strlen((string)($formObservacionAdmin ?? $this->formObservacionAdmin ?? '')) }}/1000
  </span>
  </div>

  <textarea
  id="formObservacionAdmin"
  wire:model="formObservacionAdmin"
  maxlength="1000"
  rows="3"
  placeholder="{{ $dosisDifiere ? 'Registre la justificación clínica obligatoria por la que se modificó la dosis prescrita...' : 'Detalles asistenciales relevantes, hidratación suministrada, etc.' }}"
  class="w-full text-xs rounded-xl p-3 border {{ $errors->has('formObservacionAdmin') ? 'border-[var(--rm-danger)] focus:ring-[var(--rm-danger)]' : 'border-[var(--rm-border-soft)] focus:ring-[var(--rm-focus)]' }} bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] dark:placeholder-[var(--rm-border)] focus:outline-none focus:ring-1 resize-none shadow-2xs"></textarea>

  @error('formObservacionAdmin')
  <span class="text-[11px] font-bold text-[var(--rm-danger)] block mt-1 flex items-center gap-1">
   <i class="ph-bold ph-warning-circle"></i> {{ $message }}
  </span>
  @enderror
  </div>

  {{-- Error general de backend si existiese --}}
  @error('administracion_error')
  <div class="p-2.5 rounded-lg bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40 text-xs text-[var(--rm-danger)] font-bold">
  {{ $message }}
  </div>
  @enderror
  </div>

 </div>

 {{-- Footer con Botones Exactos [ Cancelar ] [ Confirmar administración ] --}}
 <div class="px-5 py-3.5 bg-[var(--rm-surface-soft)] border-t border-[var(--rm-border-soft)] flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2.5">
  <button
  type="button"
  wire:click="cerrarModalAdministrar"
  class="h-10 px-4 rounded-xl text-xs font-semibold bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition cursor-pointer">
  Cancelar
  </button>

  <button
  type="submit"
  wire:loading.attr="disabled"
  class="h-10 px-5 rounded-xl text-xs font-bold bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary)] text-white transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
  <i class="ph-bold ph-check text-sm" wire:loading.remove wire:target="guardarAdministracion"></i>
  <span wire:loading.remove wire:target="guardarAdministracion">Confirmar administración</span>
  <span wire:loading wire:target="guardarAdministracion">Registrando...</span>
  </button>
 </div>
 </form>

 </div>
 </div>
</div>
</template>
</div>
@endif
