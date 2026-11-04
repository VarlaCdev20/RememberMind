<div class="rm-medication-workspace__list">
 @include('livewire.medicacion.partials.kardex-filtros')
 <div class="rm-medication-workspace__agenda-layout">
 <div class="rm-medication-workspace__dose-list">
 {{-- ==================================================
 2. TABLA KARDEX DIRECTA (SIN CONTENEDORES ANIDADOS)
 ================================================== --}}
 <div class="rm-medication-workspace__table-panel rounded-xl border border-[var(--rm-border-soft)] shadow-sm overflow-hidden">
 {{-- Header Compacto de la Tabla --}}
 <div class="rm-medication-workspace__table-heading">
 <div class="flex items-center gap-2">
 <i class="ph-bold ph-calendar-check" aria-hidden="true"></i>
 <h3 class="text-sm font-[800] text-[var(--rm-text-primary)] tracking-tight">
  Dosis programadas
 </h3>
 <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-[600] bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-border-soft)]">
  {{ $filtroKardexFecha ?: now()->toDateString() }}
 </span>
 </div>

 <div class="flex items-center gap-2 self-start sm:self-center text-xs">
 <span class="rm-medication-workspace__priority">
  Alertas y retrasadas primero
 </span>
 <span class="rm-medication-workspace__dose-count">
  {{ count($dosisHoy) }} dosis
 </span>
 </div>
 </div>

 {{-- Tabla de Dosis del Turno --}}
 <div class="w-full overflow-x-auto">
 <table class="rm-medication-workspace__dose-table rm-data-table rm-data-table--actions w-full table-auto text-left border-collapse min-w-[700px]">
 <thead>
  <tr class="bg-[var(--rm-surface-soft)] text-[10.5px] font-[700] text-[var(--rm-text-muted)] uppercase tracking-wider border-b border-[var(--rm-border-soft)]">
  <th class="px-2.5 py-2 w-[65px] text-center">Hora</th>
  <th class="px-3 py-2">Residente</th>
  <th class="px-2 py-2 w-[85px] whitespace-nowrap">Hab / Cama</th>
  <th class="px-3 py-2">Medicamento</th>
  <th class="px-2 py-2 w-[75px] whitespace-nowrap">Dosis</th>
  <th class="px-2 py-2 w-[65px] whitespace-nowrap">Vía</th>
  <th class="px-2 py-2 w-[115px] text-center whitespace-nowrap">Estado</th>
  <th class="px-2.5 py-2 w-[55px] text-right whitespace-nowrap">Acción</th>
  </tr>
 </thead>
 <tbody class="divide-y divide-[var(--rm-border-soft)]/60 dark:divide-[var(--rm-text-primary)] text-xs">
  @forelse($dosisHoy as $dosis)
  @php
  $isSelected = ($selectedPrescripcionId === $dosis['cod_prescripcion'] && $selectedHora === $dosis['hora']);
  $esAlertaClinica = !empty($dosis['tiene_alerta_clinica']);
  $esRetrasada = in_array($dosis['estado_raw'] ?? '', ['VENCIDA', 'RETRASADA']);
  $esAdministrada = in_array($dosis['estado_raw'] ?? '', ['ADMINISTRADA', 'ADMINISTRADO']);

  $rowStyle = ($esAlertaClinica ? 'is-clinical-alert ' : '').($isSelected ? 'is-selected' : '');
  @endphp
  <tr
  wire:key="dosis-{{ $dosis['id'] ?? $loop->index }}"
  wire:click="abrirDrawerDosis('{{ $dosis['cod_prescripcion'] }}', '{{ $dosis['hora'] }}', '{{ $dosis['cod_residente'] }}')"
  class="rm-dose-row cursor-pointer {{ $rowStyle }}">

  {{-- Hora --}}
  <td data-label="Hora" class="rm-dose-time px-2.5 py-2 text-center whitespace-nowrap">
  <span class="rm-medication-workspace__time inline-flex items-center px-1.5 py-0.5 rounded-md font-mono text-[11px] font-[700]">
   <i class="ph-bold ph-clock" aria-hidden="true"></i>
   {{ $dosis['hora'] }}
  </span>
  </td>

  {{-- Residente --}}
  <td data-label="Residente" class="rm-dose-resident px-3 py-2">
  <div class="flex items-center gap-2 min-w-0">
   <div class="rm-dose-avatar flex items-center justify-center font-bold shrink-0" aria-hidden="true">
   {{ $dosis['iniciales'] ?? 'RM' }}
   </div>
   <div class="min-w-0">
 <span class="rm-medication-workspace__resident font-[700] text-[var(--rm-text-primary)] block leading-tight">
   {{ $dosis['nombre_residente'] }}
   </span>
   @if($esAlertaClinica)
   <span class="rm-dose-alert inline-flex items-center gap-1 font-bold">
   <i class="ph-bold ph-warning" aria-hidden="true"></i> Alerta clínica
   </span>
   @endif
   </div>
  </div>
  </td>

  {{-- Hab / Cama --}}
  <td data-label="Habitación" class="rm-dose-room px-2 py-2 text-[var(--rm-text-muted)] font-medium text-xs whitespace-nowrap">
  <span><i class="ph-bold ph-bed" aria-hidden="true"></i>{{ $dosis['habitacion'] ?? 'Sin habitación' }}</span>
  </td>

  {{-- Medicamento --}}
  <td data-label="Medicamento" class="rm-dose-medication px-3 py-2">
  <div class="flex items-center gap-1.5">
   <i class="ph-bold ph-pill rm-dose-drug-icon" aria-hidden="true"></i>
   <span class="rm-medication-workspace__drug font-[700] text-[var(--rm-text-primary)]">
   {{ $dosis['medicamento'] }}
   </span>
   @if(!empty($dosis['es_prn']))
   <span class="px-1 py-0.2 rounded text-[9.5px] font-bold bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30">PRN</span>
   @endif
  </div>
  </td>

  {{-- Dosis --}}
  <td data-label="Dosis" class="rm-dose-amount px-2 py-2 font-mono text-xs font-[600] text-[var(--rm-text-primary)] whitespace-nowrap">
  {{ $dosis['dosis'] }}
  </td>

  {{-- Vía --}}
  <td data-label="Vía" class="rm-dose-route px-2 py-2 text-[11px] text-[var(--rm-text-muted)] font-medium whitespace-nowrap">
  {{ $dosis['via'] }}
  </td>

  {{-- Estado --}}
  <td data-label="Estado" class="rm-dose-state px-2 py-2 text-center whitespace-nowrap">
  <x-ui.status-badge :estado="$dosis['estado_raw']" :label="$dosis['estado_raw'] === 'PROXIMA' ? 'Próxima' : $dosis['estado']" :variant="$esAlertaClinica || $esRetrasada ? 'danger' : ($esAdministrada ? 'success' : ($dosis['estado_raw'] === 'OMITIDA' ? 'neutral' : ($dosis['estado_raw'] === 'PROXIMA' ? 'info' : 'warning')))" />
  </td>

  {{-- Acción --}}
  <td data-label="Acción" class="rm-dose-action px-2.5 py-2 text-right whitespace-nowrap">
  <button type="button"
   aria-label="Ver dosis de {{ $dosis['medicamento'] }} para {{ $dosis['nombre_residente'] }} a las {{ $dosis['hora'] }}"
   wire:click.stop="abrirDrawerDosis('{{ $dosis['cod_prescripcion'] }}', '{{ $dosis['hora'] }}', '{{ $dosis['cod_residente'] }}')"
   class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold rounded-md bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition cursor-pointer">
   <span class="rm-dose-action-short">Ver</span><span class="rm-dose-action-long">Ver detalle</span>
   <i class="ph-bold ph-caret-right" aria-hidden="true"></i>
  </button>
  </td>
  </tr>
  @empty
  <tr>
  <td colspan="8" class="px-4 py-8 text-center text-xs text-[var(--rm-text-muted)]">
  <i class="ph ph-check-circle text-2xl text-[var(--rm-action-primary)] mb-1.5 block"></i>
  No hay dosis programadas para los filtros seleccionados.
  </td>
  </tr>
  @endforelse
 </tbody>
 </table>
 </div>
 </div>
 </div>
 <aside class="rm-medication-workspace__sidebar" aria-label="Gráficas de la agenda de medicación">
 @include('livewire.medicacion.partials.kardex-graficas')
 </aside>
 </div>
</div>
