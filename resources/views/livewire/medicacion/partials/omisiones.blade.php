{{-- OMISIONES — PALETA INSTITUCIONAL UNIFICADA --}}
<div class="bg-[var(--rm-surface)] rounded-2xl border border-[var(--rm-border-soft)] shadow-sm overflow-hidden transition-colors font-sans">

 {{-- Header de Sección --}}
 <div class="px-5 py-4 border-b border-[var(--rm-border-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 bg-[var(--rm-surface)] ">
 <div>
 <div class="flex items-center gap-2">
 <h3 class="text-[17px] font-[800] text-[var(--rm-text-primary)] tracking-tight">
  Registro de Omisiones del Turno
 </h3>
 <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-[600] bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40">
  Justificaciones clínicas
 </span>
 </div>
 <p class="text-[12px] text-[var(--rm-text-muted)] mt-0.5">
 Dosis no administradas con justificación clínica o rechazo documentado
 </p>
 </div>
 <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-[600] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] self-start sm:self-center">
 {{ count($omisiones) }} omisiones registradas
 </span>
 </div>

 {{-- Tabla Continua con Proporciones Estables y Scroll Horizontal Responsivo --}}
 <div class="w-full overflow-hidden">
 <table class="w-full table-auto text-left border-collapse">
 <thead>
 <tr class="bg-[var(--rm-surface-soft)] text-[11px] font-[700] text-[var(--rm-text-muted)] uppercase tracking-wider border-b border-[var(--rm-border-soft)]">
  <th class="px-2.5 py-2.5 w-[65px] text-center">Hora</th>
  <th class="px-3 py-2.5">Residente</th>
  <th class="px-3 py-2.5">Medicamento</th>
  <th class="px-2 py-2.5 w-[70px] whitespace-nowrap">Dosis</th>
  <th class="px-3 py-2.5">Motivo</th>
  <th class="px-2.5 py-2.5 w-[110px] whitespace-nowrap">Profesional</th>
  <th class="px-2 py-2.5 w-[55px] text-right whitespace-nowrap">Acción</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] text-xs">
 @forelse($omisiones as $omision)
  @php
  $nomRes = $omision->residente ? trim("{$omision->residente->nombres} {$omision->residente->apellido_paterno}") : 'Residente';
  $partes = explode(' ', $nomRes);
  $iniciales = strtoupper(substr($partes[0] ?? 'R', 0, 1) . substr($partes[1] ?? 'M', 0, 1));

  $codPresc = $omision->cod_prescripcion ?? $omision->prescripcion?->cod_prescripcion;
  $horaStr = $omision->fecha_hora_programada?->format('H:i') ?? '08:00';
  $codRes = $omision->cod_residente ?? $omision->residente?->cod_residente;
  @endphp
  <tr
  wire:key="omision-{{ $omision->cod_administracion ?? $loop->index }}"
  @if($codPresc && $codRes) wire:click="abrirDrawerDosis('{{ $codPresc }}', '{{ $horaStr }}', '{{ $codRes }}')" @endif
  class="cursor-pointer transition-colors duration-150 hover:bg-[var(--rm-surface-soft)]/50 dark:hover:bg-[var(--rm-surface-raised)]/[0.03]">

  {{-- Hora --}}
  <td class="px-2.5 py-2.5 text-center whitespace-nowrap">
  <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] font-mono text-[11px] font-[700]">
  {{ $horaStr }}
  </span>
  </td>

  {{-- Residente --}}
  <td class="px-3 py-2.5">
  <div class="flex items-center gap-2 min-w-0">
  <div class="w-7 h-7 rounded-full bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] flex items-center justify-center font-[700] text-[10.5px] text-[var(--rm-text-primary)] shrink-0">
   {{ $iniciales }}
  </div>
  <span class="font-[700] text-[13px] text-[var(--rm-text-primary)] leading-snug break-words whitespace-normal">
   {{ $nomRes }}
  </span>
  </div>
  </td>

  {{-- Medicamento --}}
  <td class="px-3 py-2.5">
  <span class="font-[600] text-[13px] text-[var(--rm-text-primary)] leading-snug break-words whitespace-normal">
  {{ $omision->prescripcion?->medicamento?->nombre_generico ?? ($omision->prescripcion?->nombre_medicamento ?? 'Medicamento') }}
  </span>
  </td>

  {{-- Dosis --}}
  <td class="px-2 py-2.5 text-[var(--rm-text-muted)] font-mono text-[11.5px] whitespace-nowrap">
  {{ $omision->prescripcion?->dosis ?? '1' }} {{ $omision->prescripcion?->unidad_dosis ?? 'comp' }}
  </td>

  {{-- Motivo con Tag de Omisión --}}
  <td class="px-3 py-2.5">
  <div class="space-y-0.5">
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10.5px] font-[700] bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40">
   <i class="ph-bold ph-warning text-xs"></i>
   {{ $omision->motivo_omision ?? 'Ayuno médico programado' }}
  </span>
  @if(!empty($omision->observacion))
   <p class="text-[11px] text-[var(--rm-text-muted)] truncate max-w-[220px]">
   {{ $omision->observacion }}
   </p>
  @endif
  </div>
  </td>

  {{-- Profesional --}}
  <td class="px-2.5 py-2.5 text-[var(--rm-text-primary)] whitespace-nowrap font-medium text-xs">
  {{ $omision->personal?->nombres ?? ($omision->registrador?->nombres ?? 'Personal de enfermería') }}
  </td>

  {{-- Acción: Botón Ver --}}
  <td class="px-2.5 py-2.5 text-right whitespace-nowrap" @click.stop>
  <button
  type="button"
  wire:click="abrirDrawerDosis('{{ $codPresc }}', '{{ $horaStr }}', '{{ $codRes }}')"
  class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition shadow-2xs cursor-pointer"
  title="Ver ficha del paciente">
  <span>Ver</span>
  <i class="ph ph-arrow-right text-xs text-[var(--rm-action-primary)]"></i>
  </button>
  </td>
  </tr>
 @empty
  <tr>
  <td colspan="7" class="p-8 text-center text-[var(--rm-text-muted)]">
  <div class="w-12 h-12 mx-auto rounded-full bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)] flex items-center justify-center text-xl shadow-2xs mb-2">
  <i class="ph ph-check-circle"></i>
  </div>
  <p class="text-xs font-semibold text-[var(--rm-text-primary)] ">
  No hay omisiones registradas en el turno actual
  </p>
  </td>
  </tr>
 @endforelse
 </tbody>
 </table>
 </div>
</div>
