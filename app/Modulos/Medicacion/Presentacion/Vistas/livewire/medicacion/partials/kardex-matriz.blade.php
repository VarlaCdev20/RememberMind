<div class="space-y-3 font-sans">
 {{-- ==================================================
 1. BARRA DE FILTROS COMPACTA ÚNICA
 ================================================== --}}
 <x-ui.filter-bar x-data="{ masFiltros: false }" class="mb-4">
 <div class="w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-2 items-center text-xs">
 {{-- 1. Búsqueda rápida: residente o medicamento --}}
 <div class="lg:col-span-3 relative flex items-center">
 <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-[var(--rm-text-muted)]">
  <i class="ph ph-magnifying-glass text-sm"></i>
 </span>
 <input type="text"
  wire:model.live.debounce.300ms="filtroKardexBusqueda"
  placeholder="Buscar residente o medicamento..."
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-input-placeholder)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]" />
 @if(!empty($filtroKardexBusqueda))
  <button type="button"
  wire:click="limpiarFiltro('filtroKardexBusqueda')"
  class="absolute inset-y-0 right-0 flex items-center pr-2 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] transition cursor-pointer"
  title="Limpiar búsqueda">
  <i class="ph ph-x-circle text-sm"></i>
  </button>
 @endif
 </div>

 {{-- 2. Estado --}}
 <div class="lg:col-span-2">
 <select wire:model.live="filtroKardexEstado" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  <option value="">Todos los estados</option>
  <option value="PROXIMA">Próximas</option>
  <option value="PENDIENTE">Pendientes</option>
  <option value="RETRASADA">Retrasadas</option>
  <option value="ADMINISTRADA">Administradas</option>
 </select>
 </div>

 {{-- 3. Residente --}}
 <div class="lg:col-span-3">
 <select wire:model.live="filtroKardexResidente" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  <option value="">Todos los residentes</option>
  @foreach($residentes as $res)
  <option value="{{ $res->cod_residente }}">
  {{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}
  </option>
  @endforeach
 </select>
 </div>

 {{-- 4. Horario --}}
 <div class="lg:col-span-2">
 <select wire:model.live="filtroKardexHorario" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  <option value="">Cualquier horario</option>
  <option value="MANANA">Mañana (06:00 - 13:00)</option>
  <option value="TARDE">Tarde (13:00 - 19:00)</option>
  <option value="NOCHE">Noche (19:00 - 06:00)</option>
  <option value="07:00">07:00</option>
  <option value="08:00">08:00</option>
  <option value="12:00">12:00</option>
  <option value="16:00">16:00</option>
  <option value="20:00">20:00</option>
 </select>
 </div>

 {{-- 5. Vía --}}
 <div class="lg:col-span-1">
 <select wire:model.live="filtroKardexVia" class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  <option value="">Vía (Todas)</option>
  <option value="ORAL">Oral</option>
  <option value="SUBLINGUAL">Sublingual</option>
  <option value="INTRAVENOSA">Intravenosa</option>
  <option value="INTRAMUSCULAR">Intramuscular</option>
  <option value="SUBCUTANEA">Subcutánea</option>
  <option value="TOPICA">Tópica</option>
  <option value="INHALATORIA">Inhalatoria</option>
 </select>
 </div>

 {{-- 6. Botón toggle Más Filtros --}}
 <div class="lg:col-span-1 flex items-center justify-end">
 <button type="button"
  @click="masFiltros = !masFiltros"
  class="h-[38px] px-2 w-full inline-flex items-center justify-center gap-1 text-xs font-bold rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] hover:bg-[var(--rm-surface)] transition cursor-pointer"
  :class="masFiltros ? 'bg-[var(--rm-action-primary)] text-white border-[var(--rm-action-primary)]' : ''"
  title="Ver más filtros">
  <i class="ph ph-faders text-xs"></i>
  <span class="hidden sm:inline">Más</span>
 </button>
 </div>
 </div>

 {{-- Filtros Secundarios Desplegables (PRN, Fecha) --}}
 <div x-show="masFiltros" x-transition class="pt-2 border-t border-[var(--rm-border-soft)]/60 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 text-xs">
 {{-- PRN --}}
 <div>
 <label class="block text-[10.5px] font-semibold text-[var(--rm-text-muted)] mb-1">Tipo de indicación:</label>
 <select wire:model.live="filtroKardexPrn" class="w-full h-8 px-2 text-xs rounded-md border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] text-[var(--rm-text-primary)] ">
  <option value="">PRN (Todos)</option>
  <option value="PRN">Según necesidad (PRN)</option>
  <option value="FIJO">Horario fijo</option>
 </select>
 </div>

 {{-- Fecha --}}
 <div>
 <label class="block text-[10.5px] font-semibold text-[var(--rm-text-muted)] mb-1">Fecha de visualización:</label>
 <input type="date"
  wire:model.live="filtroKardexFecha"
  class="w-full h-8 px-2 text-xs rounded-md border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] text-[var(--rm-text-primary)] " />
 </div>

 {{-- Acciones de reset --}}
 <div class="sm:col-span-2 flex items-end justify-end">
 <button type="button"
  wire:click="resetFilters"
  class="text-xs text-[var(--rm-action-primary)] hover:underline cursor-pointer inline-flex items-center gap-1 font-bold">
  <i class="ph ph-arrow-counter-clockwise"></i>
  <span>Restablecer todo</span>
 </button>
 </div>
 </div>

 {{-- Fila de chips de filtros activos --}}
 @php
 $chipsActivos = !empty($filtroKardexBusqueda)
 || !empty($filtroKardexEstado)
 || !empty($filtroKardexHorario)
 || !empty($filtroKardexResidente)
 || !empty($filtroKardexVia)
 || !empty($filtroKardexPrn)
 || !empty($filtroKardexFecha);
 @endphp

 @if($chipsActivos)
 <div class="rm-filter-bar__active">
 <div class="flex flex-wrap items-center gap-1.5">
  <span class="rm-filter-bar__active-label">Filtros activos:</span>

  @if(!empty($filtroKardexBusqueda))
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-surface-soft)] text-[11px] font-semibold text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]">
  Búsqueda: "{{ $filtroKardexBusqueda }}"
  <button type="button" wire:click="limpiarFiltro('filtroKardexBusqueda')" class="hover:text-[var(--rm-action-primary)] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
  </span>
  @endif

  @if(!empty($filtroKardexEstado))
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-surface-soft)] text-[11px] font-semibold text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]">
  Estado: {{ $filtroKardexEstado === 'RETRASADA' ? 'Retrasadas' : ($filtroKardexEstado === 'PENDIENTE' ? 'Pendientes' : ($filtroKardexEstado === 'PROXIMA' ? 'Próximas' : 'Administradas')) }}
  <button type="button" wire:click="limpiarFiltro('filtroKardexEstado')" class="hover:text-[var(--rm-action-primary)] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
  </span>
  @endif

  @if(!empty($filtroKardexPrn))
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-surface-soft)] text-[11px] font-semibold text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]">
  PRN: {{ $filtroKardexPrn === 'PRN' ? 'Según necesidad' : 'Horario fijo' }}
  <button type="button" wire:click="limpiarFiltro('filtroKardexPrn')" class="hover:text-[var(--rm-action-primary)] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
  </span>
  @endif

  @if(!empty($filtroKardexVia))
  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[var(--rm-surface-soft)] text-[11px] font-semibold text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]">
  Vía: {{ ucfirst(strtolower($filtroKardexVia)) }}
  <button type="button" wire:click="limpiarFiltro('filtroKardexVia')" class="hover:text-[var(--rm-action-primary)] cursor-pointer"><i class="ph ph-x text-xs"></i></button>
  </span>
  @endif
 </div>

 <div class="flex items-center gap-2.5">
  <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[var(--rm-text-primary)]/10 text-[var(--rm-text-primary)]">
  {{ count($dosisHoy) }} coincidentes
  </span>

  <button type="button"
  wire:click="resetFilters"
  class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-action-primary-soft)] hover:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
  <i class="ph-bold ph-arrow-counter-clockwise"></i>
  <span>Limpiar filtros</span>
  </button>
 </div>
 </div>
 @endif
 </x-ui.filter-bar>

 {{-- ==================================================
 2. TABLA KARDEX DIRECTA (SIN CONTENEDORES ANIDADOS)
 ================================================== --}}
 <div class="bg-[var(--rm-surface-soft)] rounded-xl border border-[var(--rm-border-soft)] shadow-sm overflow-hidden transition-colors">
 {{-- Header Compacto de la Tabla --}}
 <div class="px-4 py-2.5 border-b border-[var(--rm-border-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-[var(--rm-surface-soft)]/60 ">
 <div class="flex items-center gap-2">
 <h3 class="text-sm font-[800] text-[var(--rm-text-primary)] tracking-tight">
  Dosis de hoy
 </h3>
 <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10.5px] font-[600] bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-border-soft)]">
  Matriz Horaria del Turno
 </span>
 </div>

 <div class="flex items-center gap-2 self-start sm:self-center text-xs">
 <span class="text-[11px] text-[var(--rm-text-muted)]">
  Orden: <strong class="text-[var(--rm-text-primary)] ">Alertas · Retrasadas · Pendientes · Administradas</strong>
 </span>
 <span class="px-2 py-0.5 rounded-md text-[11px] font-[700] bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]">
  {{ count($dosisHoy) }} dosis
 </span>
 </div>
 </div>

 {{-- Tabla de Dosis del Turno --}}
 <div class="w-full overflow-x-auto">
 <table class="rm-data-table rm-data-table--actions w-full table-auto text-left border-collapse min-w-[700px]">
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

  // Fila con Alerta Clínica Real vinculada: rojo suave en toda la fila
  if ($esAlertaClinica) {
  $rowStyle = 'bg-[var(--rm-danger-soft)] border-l-4 border-l-[var(--rm-danger)] hover:bg-[var(--rm-danger-soft)]';
  $badgeClasses = 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40';
  }
  // Dosis Retrasada estándar: fondo normal con borde y badge terracota/rojo (no convierte toda la fila en alarma)
  elseif ($esRetrasada) {
  $rowStyle = 'bg-[var(--rm-surface-soft)] border-l-4 border-l-[var(--rm-danger)] hover:bg-[var(--rm-surface-soft)]/50';
  $badgeClasses = 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border border-[var(--rm-danger)]/40';
  }
  // Dosis Administrada: baja visualmente de importancia
  elseif ($esAdministrada) {
  $rowStyle = 'opacity-65 bg-[var(--rm-surface-soft)]/60 /60 border-l-4 border-l-[var(--rm-action-primary)]/40 hover:opacity-100 transition-opacity';
  $badgeClasses = 'bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-active)] border border-[var(--rm-action-primary)]/40';
  }
  // Pendiente o Próxima
  else {
  $rowStyle = 'bg-[var(--rm-surface-soft)] border-l-4 border-l-[var(--rm-warning)] hover:bg-[var(--rm-surface-soft)]/50';
  $badgeClasses = 'bg-[var(--rm-warning-soft)] dark:bg-[var(--rm-warning)]/20 text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-soft)] border border-[var(--rm-warning-soft)]';
  }

  if ($isSelected) {
  $rowStyle .= ' ring-1 ring-[var(--rm-action-primary)] bg-[var(--rm-action-primary-soft)]';
  }
  @endphp
  <tr
  wire:key="dosis-{{ $dosis['id'] ?? $loop->index }}"
  wire:click="abrirDrawerDosis('{{ $dosis['cod_prescripcion'] }}', '{{ $dosis['hora'] }}', '{{ $dosis['cod_residente'] }}')"
  class="cursor-pointer transition-colors duration-150 {{ $rowStyle }}">

  {{-- Hora --}}
  <td class="px-2.5 py-2 text-center whitespace-nowrap">
  <span class="inline-flex items-center px-1.5 py-0.5 rounded-md bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] font-mono text-[11px] font-[700]">
   {{ $dosis['hora'] }}
  </span>
  </td>

  {{-- Residente --}}
  <td class="px-3 py-2">
  <div class="flex items-center gap-2 min-w-0">
   <div class="w-6 h-6 rounded-full bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] flex items-center justify-center font-[700] text-[10px] text-[var(--rm-text-primary)] shrink-0">
   {{ $dosis['iniciales'] ?? 'RM' }}
   </div>
   <div class="min-w-0">
   <span class="font-[700] text-xs text-[var(--rm-text-primary)] block leading-tight">
   {{ $dosis['nombre_residente'] }}
   </span>
   @if($esAlertaClinica)
   <span class="inline-flex items-center gap-0.5 text-[9.5px] font-bold text-[var(--rm-danger)] ">
   <i class="ph ph-warning"></i> Alerta clínica
   </span>
   @endif
   </div>
  </div>
  </td>

  {{-- Hab / Cama --}}
  <td class="px-2 py-2 text-[var(--rm-text-muted)] font-medium text-xs whitespace-nowrap">
  {{ $dosis['habitacion'] ?? 'Sin habitación' }}
  </td>

  {{-- Medicamento --}}
  <td class="px-3 py-2">
  <div class="flex items-center gap-1.5">
   <span class="font-[700] text-xs text-[var(--rm-text-primary)] ">
   {{ $dosis['medicamento'] }}
   </span>
   @if(!empty($dosis['es_prn']))
   <span class="px-1 py-0.2 rounded text-[9.5px] font-bold bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-action-primary)]/30">PRN</span>
   @endif
  </div>
  </td>

  {{-- Dosis --}}
  <td class="px-2 py-2 font-mono text-xs font-[600] text-[var(--rm-text-primary)] whitespace-nowrap">
  {{ $dosis['dosis'] }}
  </td>

  {{-- Vía --}}
  <td class="px-2 py-2 text-[11px] text-[var(--rm-text-muted)] font-medium whitespace-nowrap">
  {{ $dosis['via'] }}
  </td>

  {{-- Estado --}}
  <td class="px-2 py-2 text-center whitespace-nowrap">
  <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-[10.5px] font-[700] {{ $badgeClasses }}">
   {{ $dosis['estado'] }}
  </span>
  </td>

  {{-- Acción --}}
  <td class="px-2.5 py-2 text-right whitespace-nowrap">
  <button type="button"
   wire:click.stop="abrirDrawerDosis('{{ $dosis['cod_prescripcion'] }}', '{{ $dosis['hora'] }}', '{{ $dosis['cod_residente'] }}')"
   class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold rounded-md bg-[var(--rm-surface-soft)] hover:bg-[var(--rm-surface)] border border-[var(--rm-border-soft)] text-[var(--rm-text-primary)] transition cursor-pointer">
   <span>Ver</span>
   <i class="ph ph-caret-right text-[10px]"></i>
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
