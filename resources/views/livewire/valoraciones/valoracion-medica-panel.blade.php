<div class="space-y-6">
 <div class="flex flex-col gap-4 rounded-[1.5rem] border border-borde/70 bg-fondo-panel p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
 <div>
  <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Medicina</span>
  <h2 class="mt-1 text-xl font-extrabold text-parrafo">Valoraciones Médicas de Admisión</h2>
  <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/60">Valoraciones y evaluaciones médicas operativas V2.</p>
 </div>
 <button wire:click="abrirCrear" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition active:scale-95">
  <i class="ph-bold ph-plus"></i>
  Nueva valoración
 </button>
 </div>

 {{-- BARRA DE FILTROS UNIFICADA FORMATO ALERTAS --}}
 <section class="rm-filter-bar">
 <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
  {{-- Buscador Principal --}}
  <div class="lg:col-span-6 relative flex items-center">
  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-muted)]">
   <i class="ph-bold ph-magnifying-glass text-base"></i>
  </span>
  <input wire:model.live.debounce.300ms="search"
   type="text"
   placeholder="Buscar por nombre, apellido o CI del residente..."
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-input-placeholder)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  @if(!empty($search))
   <button type="button"
    wire:click="limpiarFiltro('search')"
    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] cursor-pointer"
    title="Limpiar búsqueda">
   <i class="ph-bold ph-x-circle text-base"></i>
   </button>
  @endif
  </div>

  {{-- Filtro Estado de la Valoración --}}
  <div class="lg:col-span-3">
  <select wire:model.live="filtroEstado"
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Todos los estados</option>
   <option value="COMPLETADA">Completada</option>
   <option value="BORRADOR">Borrador</option>
   <option value="REGISTRADA">Registrada</option>
  </select>
  </div>

  {{-- Filtro Resultado de Admisión --}}
  <div class="lg:col-span-3">
  <select wire:model.live="filtroResult"
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Todos los resultados</option>
   <option value="ADMITIDO">Admitido</option>
   <option value="OBSERVADO">Observado</option>
   <option value="NO_ADMITIDO">No Admitido</option>
   <option value="DERIVADO">Derivado</option>
  </select>
  </div>
 </div>

 {{-- Fila de chips de filtros activos --}}
 @php
  $hasFiltrosActivos = !empty($search) || !empty($filtroEstado) || !empty($filtroResult);
 @endphp
 @if($hasFiltrosActivos)
  <div class="rm-filter-bar__active">
  <div class="flex flex-wrap items-center gap-1.5">
   <span class="rm-filter-bar__active-label">
   <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
   </span>
   @if(!empty($search))
   <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
    <span>Búsqueda: "{{ Str::limit($search, 18) }}"</span>
    <button type="button" wire:click="limpiarFiltro('search')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($filtroEstado))
   <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-warning)]/15 border border-[var(--rm-warning)]/30 text-[11px] font-bold text-[var(--rm-warning-strong)] dark:text-[var(--rm-warning-soft)]">
    <span>Estado: {{ $filtroEstado }}</span>
    <button type="button" wire:click="limpiarFiltro('filtroEstado')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
   @if(!empty($filtroResult))
   <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-text-primary)]/10 border border-[var(--rm-border-soft)] text-[11px] font-bold text-[var(--rm-text-primary)]">
    <span>Resultado: {{ $filtroResult }}</span>
    <button type="button" wire:click="limpiarFiltro('filtroResult')" class="hover:text-[var(--rm-action-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
   </span>
   @endif
  </div>
  <div class="flex items-center gap-2.5">
   <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[var(--rm-text-primary)]/10 text-[var(--rm-text-primary)]">
   {{ $valoraciones->total() ?? count($valoraciones) }} coincidentes
   </span>
   <button type="button"
    wire:click="limpiarFiltros"
    class="inline-flex items-center gap-1 rounded-xl bg-[var(--rm-action-primary-soft)] hover:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] py-1 px-2.5 text-xs font-bold transition cursor-pointer">
   <i class="ph-bold ph-arrow-counter-clockwise"></i>
   <span>Limpiar filtros</span>
   </button>
  </div>
  </div>
 @endif
 </section>

 <div class="overflow-hidden rounded-[1.5rem] border border-borde/70 bg-fondo-panel shadow-sm">
 <div class="overflow-x-auto">
  <table class="w-full text-left text-sm text-parrafo">
  <thead class="bg-fondo-app text-[10px] font-bold uppercase tracking-widest text-parrafo/60">
   <tr>
   <th class="px-5 py-4">Adulto Mayor</th>
   <th class="px-5 py-4">Registro</th>
   <th class="px-5 py-4">Estado</th>
   <th class="px-5 py-4">Observación</th>
   </tr>
  </thead>
  <tbody class="divide-y divide-borde/40">
   @forelse($valoraciones as $val)
   <tr class="transition hover:bg-fondo-app/50">
    <td class="px-5 py-3 font-bold">{{ $val->adultoMayor->nombres ?? 'S/D' }} {{ $val->adultoMayor->ap_paterno ?? '' }}</td>
    <td class="px-5 py-3">{{ optional($val->created_at)->format('d/m/Y H:i') ?? 'S/D' }}</td>
    <td class="px-5 py-3"><span class="rounded-full bg-estado-exitoBg px-2.5 py-0.5 text-[10px] font-bold uppercase text-estado-exito">{{ $val->estado ?? 'ACTIVO' }}</span></td>
    <td class="max-w-md truncate px-5 py-3">{{ $val->observacion_medica ?? 'Sin observación' }}</td>
   </tr>
   @empty
   <tr><td colspan="4" class="px-5 py-10 text-center text-xs font-bold text-parrafo/60">No hay valoraciones médicas registradas.</td></tr>
   @endforelse
  </tbody>
  </table>
 </div>
 @if($valoraciones->hasPages())
  <div class="border-t border-borde/70 bg-fondo-panel px-5 py-3">{{ $valoraciones->links() }}</div>
 @endif
 </div>

 @if($modalForm)
 <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
  <div class="w-full max-w-2xl overflow-hidden rounded-[1.5rem] bg-fondo-panel shadow-2xl">
  <div class="flex items-center justify-between border-b border-borde/70 bg-fondo-app px-6 py-4">
   <h3 class="text-lg font-extrabold text-parrafo">Registro de Valoración Médica</h3>
   <button wire:click="cerrarModales" class="text-parrafo/60 transition hover:text-boton-acento"><i class="ph-bold ph-x text-xl"></i></button>
  </div>
  <div class="grid gap-4 p-6 sm:grid-cols-2">
   <label class="block sm:col-span-2">
   <span class="text-xs font-bold text-parrafo">Paciente</span>
   <select wire:model="codResidente" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
    <option value="">Seleccione...</option>
    @foreach($adultos as $adulto)
    <option value="{{ $adulto->cod_residente }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>
    @endforeach
   </select>
   @error('codResidente') <span class="text-[10px] text-boton-acento">{{ $message }}</span> @enderror
   </label>
   <label class="block">
   <span class="text-xs font-bold text-parrafo">Fecha</span>
   <input type="date" wire:model="fecha" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
   </label>
   <label class="block">
   <span class="text-xs font-bold text-parrafo">Resultado</span>
   <select wire:model="resultadoAdmision" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs">
    <option value="">Seleccione...</option>
    <option value="ADMITIDO">ADMITIDO</option>
    <option value="NO_ADMITIDO">NO ADMITIDO</option>
    <option value="DERIVADO">DERIVADO</option>
    <option value="OBSERVADO">OBSERVADO</option>
    <option value="CANCELADO">CANCELADO</option>
   </select>
   </label>
   <label class="block sm:col-span-2">
   <span class="text-xs font-bold text-parrafo">Motivo / decisión</span>
   <textarea wire:model="motivoDecision" class="mt-1 w-full rounded-xl border border-borde/70 bg-fondo-panel p-2.5 text-xs"></textarea>
   @error('motivoDecision') <span class="text-[10px] text-boton-acento">{{ $message }}</span> @enderror
   </label>
  </div>
  <div class="flex items-center justify-end gap-3 border-t border-borde/70 bg-fondo-app px-6 py-4">
   <button wire:click="cerrarModales" class="rounded-xl border border-borde/70 px-4 py-2 text-xs font-bold text-parrafo">Cancelar</button>
   <button wire:click="guardar" class="rounded-xl bg-boton-acento px-4 py-2 text-xs font-bold text-inverso">Guardar</button>
  </div>
  </div>
 </div>
 @endif
</div>
