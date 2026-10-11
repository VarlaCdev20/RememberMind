<div class="space-y-4 font-sans text-xs">
  @if (session()->has('mensaje'))
 <x-ui.callout variant="success" title="Operación completada">
  {{ session('mensaje') }}
 </x-ui.callout>
 @endif

 {{-- ============================================================
 1. ESTADO ACTUAL DE LA ALERTA (ARRIBA)
 ============================================================ --}}
 <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 space-y-3 shadow-2xs">
 <div class="flex items-center justify-between gap-2 border-b border-[var(--rm-border-soft)] pb-2.5 flex-wrap">
  <div class="flex items-center gap-2">
  <span class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-secondary)]">
   Estado Actual:
  </span>
  <x-ui.status-badge :estado="$alerta->estado" />
  </div>

  <div class="flex items-center gap-2">
  <span class="text-[10px] font-black uppercase tracking-wider text-[var(--rm-text-secondary)]">
   Severidad:
  </span>
  <x-ui.status-badge :estado="$alerta->prioridad" />
  </div>
 </div>

 <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
  <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
  <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Origen / Canal</span>
  <span class="font-bold text-[var(--rm-text-primary)] mt-0.5 block">{{ $alerta->modulo }}</span>
  </div>

  <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
  <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Responsable Asignado</span>
  <span class="font-bold text-[var(--rm-text-primary)] mt-0.5 block truncate">
   {{ $alerta->responsable?->usuario?->name ?? 'Sistema Automático' }}
  </span>
  </div>

  <div class="p-2.5 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)]">
  <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">Fecha de Detección</span>
  <span class="font-bold text-[var(--rm-text-primary)] mt-0.5 block">
   {{ $alerta->fecha_hora?->translatedFormat('d M Y, H:i') }}
  </span>
  </div>
 </div>

 {{-- Diagnóstico / Motivo Clínico --}}
 <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border-soft)] space-y-1">
  <span class="block text-[10px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">
  Motivo y Diagnóstico Clínico
  </span>
  <p class="text-xs text-[var(--rm-text-primary)] leading-relaxed">
  <strong class="text-[var(--rm-accent-brown)]">{{ $alerta->tipo }}:</strong> {{ $alerta->descripcion }}
  </p>
 </div>
 </div>

 {{-- ============================================================
 2. HISTORIAL CRONOLÓGICO (DEBAJO - INMUTABLE)
 ============================================================ --}}
 <div class="space-y-2.5">
 <div class="flex items-center justify-between border-b border-[var(--rm-border-soft)] pb-1.5">
  <span class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)] flex items-center gap-1.5">
  <i class="ph ph-clock-counter-clockwise text-base text-[var(--rm-primary)]"></i>
  Historial de Intervenciones y Trazabilidad ({{ count($timelineItems) }})
  </span>
  <span class="text-[11px] text-[var(--rm-text-secondary)] font-mono">Orden cronológico</span>
 </div>

 <x-patterns.timeline :items="$timelineItems" emptyMessage="No se han registrado intervenciones aún en esta alerta." />
 </div>

 {{-- ============================================================
 3. REGISTRO DE NUEVA INTERVENCIÓN (SI SIGUE ABIERTA)
 ============================================================ --}}
 @if($alerta->puedeCerrarse())
 @can('alertas.gestionar')
  <div class="p-3.5 rounded-2xl bg-[var(--rm-surface)] border border-[var(--rm-border)] space-y-2 shadow-2xs">
  <label for="nuevaIntervencionHistorialInput" class="block text-xs font-bold uppercase tracking-wider text-[var(--rm-text-primary)]">
   Agregar Nota de Evolución o Intervención
  </label>
  <div class="flex gap-2">
   <input type="text"
   id="nuevaIntervencionHistorialInput"
   wire:model="accion"
   placeholder="Describa la evolución o acción realizada (mínimo 3 caracteres)..."
   class="rm-input flex-1 text-xs" />
   <button type="button"
   wire:click="guardarAccion"
   wire:loading.attr="disabled"
   class="rm-btn rm-btn-accent cursor-pointer disabled:opacity-60 flex items-center gap-1.5 shrink-0">
   <i class="ph ph-plus-circle text-base"></i>
   <span wire:loading.remove wire:target="guardarAccion">Registrar</span>
   <span wire:loading wire:target="guardarAccion">Guardando...</span>
   </button>
  </div>
  @error('accion')
   <span class="text-xs text-[var(--rm-danger)] font-medium block">{{ $message }}</span>
  @enderror
  </div>
 @endcan
 @endif
</div>
