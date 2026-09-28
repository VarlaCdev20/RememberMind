{{-- DRAWER LATERAL REDUCIDO — PALETA INSTITUCIONAL UNIFICADA --}}
<div
 x-cloak
 x-show="$wire.drawerDosisAbierto"
 class="fixed inset-0 z-40 overflow-hidden font-sans"
 aria-labelledby="slide-over-title"
 role="dialog"
 aria-modal="true">

 {{-- Backdrop suave --}}
 <div
 x-show="$wire.drawerDosisAbierto"
 x-transition:enter="ease-out duration-200"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="ease-in duration-150"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0"
 wire:click="cerrarDrawerDosis"
 class="fixed inset-0 bg-[var(--rm-modal-overlay,rgba(64,42,32,0.45))] backdrop-blur-[2px] transition-opacity">
 </div>

 {{-- Panel Deslizante (Ancho óptimo 390-420px) --}}
 <div class="fixed inset-y-0 right-0 flex max-w-full pl-4 sm:inset-y-3 sm:right-3 sm:pl-8">
 <div
 x-show="$wire.drawerDosisAbierto"
 x-transition:enter="transform transition ease-out duration-250"
 x-transition:enter-start="translate-x-full"
 x-transition:enter-end="translate-x-0"
 x-transition:leave="transform transition ease-in duration-200"
 x-transition:leave-start="translate-x-0"
 x-transition:leave-end="translate-x-full"
 class="rm-drawer w-screen max-w-[410px] flex flex-col justify-between overflow-hidden transition-colors">

 {{-- Header Drawer --}}
 <div class="px-5 py-4 border-b border-[var(--rm-border-soft)] flex items-center justify-between bg-[var(--rm-surface-soft)] shrink-0">
 <div>
  <h3 class="text-[17px] font-[800] text-[var(--rm-text-primary)] tracking-tight" id="slide-over-title">
  Administrar medicación
  </h3>
  <p class="text-[12px] text-[var(--rm-text-muted)] mt-0.5">
  Detalle de la Dosis seleccionada
  </p>
 </div>
 <button
  type="button"
  wire:click="cerrarDrawerDosis"
  class="rounded-lg p-1.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-text-primary)] dark:hover:text-white hover:bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] transition cursor-pointer"
  title="Cerrar panel">
  <i class="ph ph-x text-lg"></i>
 </button>
 </div>

 {{-- Contenido Abierto --}}
 <div class="overflow-y-auto flex-1 px-5 py-4 space-y-4 custom-scrollbar text-xs divide-y divide-[var(--rm-border-soft)]/70 dark:divide-[var(--rm-text-primary)]">
 @if(!empty($dosisDetalle))

  {{-- RESIDENTE --}}
  <div class="flex items-center gap-3 pb-3">
  <div class="w-[42px] h-[42px] rounded-full bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] flex items-center justify-center font-[700] text-sm text-[var(--rm-text-primary)] shrink-0">
  {{ $dosisDetalle['residente']['iniciales'] ?? 'SR' }}
  </div>
  <div class="min-w-0 flex-1">
  <div class="font-[700] text-[14.5px] text-[var(--rm-text-primary)] truncate">
  {{ $dosisDetalle['residente']['nombre_completo'] ?? 'Sin residente' }}
  </div>
  <div class="text-[11.5px] text-[var(--rm-text-muted)] flex items-center gap-2 mt-0.5 flex-wrap">
  <span>{{ $dosisDetalle['residente']['edad'] ?? 'Edad no registrada' }}</span>
  <span>·</span>
  <span>{{ $dosisDetalle['residente']['habitacion'] ?? 'Sin habitación' }}</span>
  <span>·</span>
  <span class="font-mono">NHC: {{ $dosisDetalle['residente']['nhc'] ?? 'No registrado' }}</span>
  </div>
  </div>
  </div>

  {{-- MEDICAMENTO --}}
  <div class="pt-3 pb-1">
  <div class="text-[10.5px] font-[800] uppercase tracking-wider text-[var(--rm-action-primary)] mb-1.5">
  Medicamento
  </div>
  <div class="p-3 rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] flex items-center justify-between gap-3">
  <div class="flex items-center gap-2.5 min-w-0">
  <div class="w-8 h-8 rounded-lg bg-[var(--rm-action-primary)] text-white flex items-center justify-center shrink-0">
   <i class="ph ph-pill text-base"></i>
  </div>
  <div class="min-w-0">
   <div class="font-[700] text-[13.5px] text-[var(--rm-text-primary)] truncate">
   {{ $dosisDetalle['medicamento']['nombre_generico'] ?? 'Omeprazol' }} {{ $dosisDetalle['medicamento']['concentracion'] ?? '20 mg' }}
   </div>
   <div class="text-[11px] text-[var(--rm-text-muted)] truncate">
   {{ $dosisDetalle['medicamento']['nombre_comercial'] ?? 'Normon®' }} · {{ $dosisDetalle['medicamento']['forma_farmaceutica'] ?? 'Cápsula' }}
   </div>
  </div>
  </div>
  <button
  type="button"
  wire:click="abrirModalMedicamento('{{ $dosisDetalle['cod_medicamento'] ?? 'OMEPRAZOL' }}')"
  class="shrink-0 px-2.5 py-1 text-xs font-[600] rounded-lg bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition shadow-2xs">
  Ver ficha
  </button>
  </div>
  </div>

  {{-- PRESCRIPCIÓN MÉDICA --}}
  <div class="pt-3 pb-1 space-y-2">
  <div class="text-[10.5px] font-[800] uppercase tracking-wider text-[var(--rm-action-primary)] ">
  Prescripción Médica
  </div>

  <div class="grid grid-cols-2 gap-y-1.5 text-xs py-1">
  <span class="text-[var(--rm-text-muted)]">Dosis:</span>
  <span class="font-[700] text-[var(--rm-text-primary)] text-right font-mono">{{ $dosisDetalle['prescripcion']['dosis'] ?? '20 mg' }}</span>

  <span class="text-[var(--rm-text-muted)]">Vía:</span>
  <span class="font-[600] text-[var(--rm-text-primary)] text-right">{{ $dosisDetalle['prescripcion']['via'] ?? 'Oral' }}</span>

  <span class="text-[var(--rm-text-muted)]">Frecuencia:</span>
  <span class="font-[600] text-[var(--rm-text-primary)] text-right">{{ $dosisDetalle['prescripcion']['frecuencia'] ?? 'Cada 24 horas' }}</span>
  </div>

  {{-- Indicación médica --}}
  <div class="pt-1 text-xs">
  <span class="text-[var(--rm-text-muted)] block text-[11px] font-medium">Indicación:</span>
  <p class="text-[var(--rm-text-primary)] mt-0.5 leading-relaxed bg-[var(--rm-surface-soft)]/60 /60 p-2 rounded-lg border border-[var(--rm-border-soft)]/40">
  {{ $dosisDetalle['prescripcion']['indicacion'] ?? 'Tomar 1 cápsula en ayunas 30 minutos antes del desayuno.' }}
  </p>
  </div>

  <div class="grid grid-cols-2 gap-y-1.5 text-xs pt-1">
  <span class="text-[var(--rm-text-muted)]">Prescrito por:</span>
  <span class="font-[600] text-[var(--rm-text-primary)] text-right truncate">{{ $dosisDetalle['prescripcion']['prescriptor'] ?? 'Prescriptor no registrado' }}</span>

  <span class="text-[var(--rm-text-muted)]">Programación del Horario:</span>
  <span class="font-mono font-[700] text-[var(--rm-text-primary)] text-right">{{ $dosisDetalle['programacion']['hora_programada'] ?? $dosisDetalle['hora'] ?? '08:00' }}</span>
  </div>
  </div>

  {{-- SEGURIDAD Y ALERGIAS --}}
  <div class="pt-3 pb-1 space-y-2">
  <div class="text-[10.5px] font-[800] uppercase tracking-wider text-[var(--rm-action-primary)] ">
  Seguridad y Alergias
  </div>

  {{-- 5 Correctos --}}
  <div class="grid grid-cols-2 gap-1.5 text-[11.5px] py-1 font-[600] text-[var(--rm-action-primary)] ">
  <div class="flex items-center gap-1.5">
  <i class="ph ph-check-circle text-[var(--rm-action-primary)]"></i>
  <span>Paciente correcto</span>
  </div>
  <div class="flex items-center gap-1.5">
  <i class="ph ph-check-circle text-[var(--rm-action-primary)]"></i>
  <span>Medicamento correcto</span>
  </div>
  <div class="flex items-center gap-1.5">
  <i class="ph ph-check-circle text-[var(--rm-action-primary)]"></i>
  <span>Dosis correcta</span>
  </div>
  <div class="flex items-center gap-1.5">
  <i class="ph ph-check-circle text-[var(--rm-action-primary)]"></i>
  <span>Vía correcta</span>
  </div>
  <div class="flex items-center gap-1.5 col-span-2">
  <i class="ph ph-check-circle text-[var(--rm-action-primary)]"></i>
  <span>Hora correcta</span>
  </div>
  </div>

  {{-- Alergias --}}
  <div>
  @if(empty($dosisDetalle['seguridad']['alergias']) || str_contains($dosisDetalle['seguridad']['alergias'], 'Sin alergias'))
  <div class="p-2 rounded-lg bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40 text-[11px] font-[600] flex items-center gap-1.5">
   <i class="ph ph-shield-check text-[var(--rm-action-primary)] text-sm"></i>
   <span>Sin alergias medicamentosas registradas</span>
  </div>
  @else
  <div class="p-2 rounded-lg bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40 text-[11px] font-[600] flex items-center gap-1.5">
   <i class="ph ph-warning-octagon text-[var(--rm-danger)] text-sm"></i>
   <span>{{ $dosisDetalle['seguridad']['alergias'] }}</span>
  </div>
  @endif
  </div>
  </div>

  {{-- SEGUIMIENTO CLÍNICO --}}
  <div class="pt-3 pb-1 text-xs">
  <div class="flex items-center justify-between text-[11.5px] text-[var(--rm-text-muted)]">
  <span>Último Seguimiento:</span>
  <span class="font-medium text-[var(--rm-text-primary)] ">
  {{ $dosisDetalle['seguimiento']['ultima_admin'] ?? 'Sin administraciones previas' }}
  </span>
  </div>
  </div>

  {{-- OBSERVACIÓN DE ENFERMERÍA --}}
  @if(empty($dosisDetalle['ya_registrada']))
  <div class="pt-3">
  <h4 class="text-[10.5px] font-[800] uppercase tracking-wider text-[var(--rm-action-primary)] mb-1.5">
  Observación de Enfermería
  </h4>
  <div class="relative">
  <textarea
   wire:model="observacionEnfermeria"
   maxlength="200"
   rows="3"
   placeholder="Añadir observación de administración (opcional)..."
   class="w-full h-[76px] text-xs rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] text-[var(--rm-text-primary)] placeholder-[var(--rm-text-muted)] dark:placeholder-[var(--rm-border)] focus:ring-1 focus:ring-[var(--rm-focus)] p-2.5 resize-none shadow-2xs"></textarea>
  <span class="absolute bottom-2 right-2.5 text-[10px] text-[var(--rm-text-muted)] font-mono pointer-events-none">
   {{ strlen($observacionEnfermeria) }}/200
  </span>
  </div>
  </div>
  @endif

 @else
  <div class="py-14 text-center text-[var(--rm-text-muted)]">
  Cargando detalle de la dosis...
  </div>
 @endif
 </div>

 {{-- Footer Drawer con Botones de Acción --}}
 <div class="p-3.5 sm:px-5 sm:py-3.5 bg-[var(--rm-surface-soft)] border-t border-[var(--rm-border-soft)] shrink-0">
 @if(!empty($dosisDetalle) && empty($dosisDetalle['ya_registrada']))
  <div class="grid grid-cols-2 gap-2.5">

  {{-- Registrar Omisión (Terracota) --}}
  <button
  type="button"
  wire:click="abrirModalOmision"
  class="h-[40px] px-3 rounded-xl bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40 text-[var(--rm-danger)] hover:bg-[var(--rm-danger-soft)] font-[700] text-xs transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs">
  <i class="ph ph-warning-circle text-base"></i>
  <span>Registrar omisión</span>
  </button>

  {{-- Administrar (Sage) --}}
  <button
  type="button"
  wire:click="abrirModalAdministrar"
  wire:loading.attr="disabled"
  class="h-[40px] px-3 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary)] text-white font-[700] text-xs transition shadow-2xs flex items-center justify-center gap-1.5 cursor-pointer">
  <i class="ph ph-check-circle text-base" wire:loading.remove wire:target="administrarDosisConfirmada"></i>
  <span wire:loading.remove wire:target="administrarDosisConfirmada">Administrar</span>
  <span wire:loading wire:target="administrarDosisConfirmada">Guardando...</span>
  </button>

  </div>
 @elseif(!empty($dosisDetalle) && !empty($dosisDetalle['ya_registrada']))
  <div class="p-2.5 rounded-xl bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] text-center text-xs font-[700] border border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40">
  Esta dosis ya ha sido registrada en el sistema.
  </div>
 @endif
 </div>

 </div>
 </div>
</div>
