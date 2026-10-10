<div class="space-y-6">
 <x-ui.collection-header title="Valoraciones iniciales de Enfermería" subtitle="Historial de valoraciones registradas en el flujo de preadmisión." icon="ph-clipboard-text" eyebrow="Enfermería">
 <x-slot:actions>
  <a href="{{ route('admin.enfermeria.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
  <i class="ph-bold ph-arrow-left"></i>
  Ir al Dashboard
  </a>
 </x-slot:actions>
 </x-ui.collection-header>

 {{-- BARRA DE FILTROS UNIFICADA FORMATO ALERTAS --}}
 <x-ui.filter-bar class="mb-4">
 <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
  {{-- Buscador Principal --}}
  <div class="lg:col-span-8">
  <label for="valoracion-enfermeria-buscar" class="rm-collection-filter-label">Buscar persona</label>
  <div class="relative flex items-center">
  <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-muted)]">
   <i class="ph-bold ph-magnifying-glass text-base"></i>
  </span>
  <input id="valoracion-enfermeria-buscar" type="text"
   wire:model.live.debounce.300ms="search"
   placeholder="Buscar por nombre o apellido..."
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-input-placeholder)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  @if($search !== '')
   <button type="button"
   wire:click="limpiarFiltro('search')"
   class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] cursor-pointer"
   title="Limpiar búsqueda">
   <i class="ph-bold ph-x-circle text-base"></i>
   </button>
  @endif
  </div>
  </div>

  {{-- Filtro Estado --}}
  <div class="lg:col-span-4">
  <label for="valoracion-enfermeria-estado" class="rm-collection-filter-label">Estado</label>
  <select id="valoracion-enfermeria-estado" wire:model.live="filtroEstado"
   class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
   <option value="">Todos los estados</option>
   <option value="BORRADOR">Borrador</option>
   <option value="COMPLETADA">Completada</option>
   <option value="ANULADA">Anulada</option>
  </select>
  </div>
 </div>

 {{-- Fila de chips de filtros activos --}}
 @php
  $hasFiltrosActivos = !empty($search) || !empty($filtroEstado);
 @endphp
 @if($hasFiltrosActivos)
  <div class="rm-filter-bar__active">
   <div class="rm-filter-scroll">
    <span class="rm-filter-bar__active-label">
     <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
    </span>
    @if(!empty($search))
     <span class="rm-filter-chip rm-filter-chip--search">
      <i class="ph-bold ph-magnifying-glass text-xs"></i>
      <span>B?squeda: "{{ Str::limit($search, 18) }}"</span>
      <button type="button" wire:click="limpiarFiltro('search')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
     </span>
    @endif
    @if(!empty($filtroEstado))
     <span class="rm-filter-chip {{ $filtroEstado === 'COMPLETADA' ? 'rm-filter-chip--success' : ($filtroEstado === 'ANULADA' ? 'rm-filter-chip--danger' : 'rm-filter-chip--warning') }}">
      <span class="w-1.5 h-1.5 rounded-full {{ $filtroEstado === 'COMPLETADA' ? 'bg-[var(--rm-action-primary)]' : ($filtroEstado === 'ANULADA' ? 'bg-[var(--rm-danger)]' : 'bg-[var(--rm-status-high)]') }}"></span>
      <span>Estado: {{ $filtroEstado }}</span>
      <button type="button" wire:click="limpiarFiltro('filtroEstado')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
     </span>
    @endif
    <button type="button"
     wire:click="limpiarFiltros"
     class="rm-filter-bar__clear-btn">
     <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
     <span>Limpiar filtros</span>
    </button>
   </div>
  </div>
 @endif
</x-ui.filter-bar>

 <div class="rm-table-container overflow-hidden rounded-[1.5rem] border border-borde/70 bg-fondo-panel shadow-sm">
 <div class="overflow-x-auto">
  <table class="rm-data-table rm-data-table--actions rm-table w-full text-left text-sm text-parrafo">
  <thead class="bg-fondo-app text-[10px] font-bold uppercase tracking-widest text-parrafo/60">
   <tr>
   <th class="px-5 py-4">Adulto Mayor</th>
   <th class="px-5 py-4">Fecha/Hora</th>
   <th class="px-5 py-4">Estado Gral.</th>
   <th class="px-5 py-4">Nivel Conciencia</th>
   <th class="px-5 py-4">Estado</th>
   <th class="px-5 py-4 text-right">Acciones</th>
   </tr>
  </thead>
  <tbody class="divide-y divide-borde/40">
   @forelse($valoraciones as $val)
   <tr class="transition hover:bg-fondo-app/50">
    <td class="px-5 py-3 font-bold">
    {{ $val->adultoMayor->nombre_completo ?? $val->preadmision->nombre_completo ?? trim(($val->adultoMayor->nombres ?? 'S/D') . ' ' . ($val->adultoMayor->ap_paterno ?? '')) }}
    </td>
    <td class="px-5 py-3">
    {{ optional($val->fecha_valoracion)->format('d/m/Y') }} <br>
    <span class="text-xs text-parrafo/60">{{ $val->hora_valoracion ? \Carbon\Carbon::parse($val->hora_valoracion)->format('H:i') : 'SIN HORA' }}</span>
    </td>
    <td class="px-5 py-3 font-bold">
    {{ $val->estado_general ?? 'S/D' }}
    </td>
    <td class="px-5 py-3">
    {{ $val->nivel_conciencia ?? 'S/D' }}
    </td>
    <td class="px-5 py-3">
    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $val->estado === 'COMPLETADA' ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-peligroBg text-boton-acento' }}">
     {{ $val->estado }}
    </span>
    </td>
    <td class="px-5 py-3 text-right">
    <button wire:click="abrirVer('{{ $val->cod_val_enf }}')" class="inline-flex items-center justify-center rounded-lg bg-fondo-panel p-2 text-boton-acento shadow-sm border border-borde/70 transition hover:bg-boton-acento hover:text-inverso">
     <i class="ph-bold ph-eye"></i>
    </button>
    </td>
   </tr>
   @empty
   <tr>
    <td colspan="6" class="px-5 py-10 text-center">
    <i class="ph-bold ph-file-dashed text-4xl text-parrafo/30"></i>
    <p class="mt-2 text-xs font-bold text-parrafo/60">No hay valoraciones registradas.</p>
    </td>
   </tr>
   @endforelse
  </tbody>
  </table>
 </div>
 @if($valoraciones->hasPages())
  <div class="border-t border-borde/70 bg-fondo-panel px-5 py-3">
  {{ $valoraciones->links() }}
  </div>
 @endif
 </div>

 @if($modalVer && $detalle)
 @php
  $vitals = json_decode($detalle->signos_vitales_iniciales, true);
  $obs = json_decode($detalle->observacion, true);
  $isStructuredVitals = is_array($vitals);
  $isStructuredObs = is_array($obs);
 @endphp
 <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
  <div class="w-full max-w-3xl overflow-hidden rounded-[1.5rem] bg-fondo-panel shadow-2xl border border-borde/70 flex flex-col max-h-[90vh]">
  <div class="flex items-center justify-between border-b border-borde/70 bg-fondo-app px-6 py-4 shrink-0">
   <h3 class="text-lg font-extrabold text-parrafo flex items-center gap-2">
   <i class="ph-bold ph-stethoscope text-boton-acento"></i>
   Detalle de Valoración Clínica Inicial
   </h3>
   <button wire:click="cerrarModales" class="text-parrafo/60 transition hover:text-boton-acento">
   <i class="ph-bold ph-x text-xl"></i>
   </button>
  </div>

  <div class="p-6 space-y-5 overflow-y-auto flex-1 text-xs">
   <!-- Info Paciente -->
   <div class="rounded-xl border border-borde/70 p-4 bg-fondo-app/40 flex justify-between items-start">
   <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase tracking-wider">Paciente en preadmisión</p>
    <p class="text-base font-extrabold text-parrafo mt-0.5">
    {{ $detalle->adultoMayor->nombre_completo ?? $detalle->preadmision->nombre_completo ?? 'S/D' }}
    </p>
    <p class="text-xs font-bold text-apoyo mt-1">
    CI: {{ $detalle->adultoMayor->ci ?? $detalle->preadmision->ci ?? 'S/D' }} {{ $detalle->adultoMayor->expedicion_ci ?? $detalle->preadmision->expedicion_ci ?? '' }}
    </p>
   </div>
   <div class="text-right">
    <p class="text-[10px] font-bold text-parrafo/60 uppercase tracking-wider">Registrado por</p>
    <p class="text-xs font-black text-meta mt-0.5">
    {{ $detalle->registradoPor->nombre_completo ?? $detalle->registradoPor->nombres ?? 'Desconocido' }}
    </p>
    <p class="text-[10px] font-bold text-apoyo mt-1">
    {{ optional($detalle->fecha_valoracion)->format('d/m/Y') }} {{ $detalle->hora_valoracion ? \Carbon\Carbon::parse($detalle->hora_valoracion)->format('H:i') : '' }}
    </p>
   </div>
   </div>

   <!-- Grid: Estado y Signos Vitales -->
   <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
   <!-- Estado General -->
   <div class="rounded-xl border border-borde/70 p-4 space-y-3">
    <h4 class="font-extrabold text-parrafo border-b border-borde/50 pb-1.5 uppercase tracking-wide flex items-center gap-1.5 text-boton-acento">
    <i class="ph-bold ph-brain"></i> Conciencia y Orientación
    </h4>
    <div class="grid grid-cols-2 gap-3">
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Estado General</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->estado_general ?? 'S/D' }}</p>
    </div>
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Nivel Conciencia</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->nivel_conciencia ?? 'S/D' }}</p>
    </div>
    <div class="col-span-2">
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Orientación Cognitiva</p>
     <p class="text-xs font-bold text-parrafo mt-0.5 leading-relaxed">{{ $detalle->orientacion ?? 'S/D' }}</p>
    </div>
    <div class="col-span-2">
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Comunicación</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->comunicacion ?? 'S/D' }}</p>
    </div>
    </div>
   </div>

   <!-- Signos Vitales -->
   <div class="rounded-xl border border-borde/70 p-4 space-y-3">
    <h4 class="font-extrabold text-parrafo border-b border-borde/50 pb-1.5 uppercase tracking-wide flex items-center gap-1.5 text-boton-acento">
    <i class="ph-bold ph-heartbeat"></i> Signos Vitales
    </h4>
    @if($isStructuredVitals)
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
     <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">P. Arterial</p>
     <p class="text-xs font-black text-parrafo mt-0.5">{{ $vitals['pa_sistolica'] }}/{{ $vitals['pa_diastolica'] }} mmHg</p>
     </div>
     <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Frec. Cardiaca</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $vitals['frecuencia_cardiaca'] }} Lpm</p>
     </div>
     <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Frec. Resp.</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $vitals['frecuencia_respiratoria'] }} Rpm</p>
     </div>
     <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Temperatura</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $vitals['temperatura'] }} °C</p>
     </div>
     <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Saturación O2</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $vitals['saturacion_oxigeno'] }} %</p>
     </div>
     <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Peso / Talla</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">
      {{ $vitals['peso'] ?: 'S/D' }} kg / {{ $vitals['talla'] ?: 'S/D' }} cm
     </p>
     </div>
    </div>
    @else
    <p class="text-xs text-parrafo whitespace-pre-wrap">{{ $detalle->signos_vitales_iniciales ?? 'Ninguno registrado' }}</p>
    @endif
   </div>
   </div>

   <!-- Físico, Movilidad, Dolor y Heridas -->
   <div class="rounded-xl border border-borde/70 p-4 space-y-3">
   <h4 class="font-extrabold text-parrafo border-b border-borde/50 pb-1.5 uppercase tracking-wide flex items-center gap-1.5 text-boton-acento">
    <i class="ph-bold ph-wheelchair"></i> Examen Físico y Movilidad
   </h4>
   <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
    <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Movilidad</p>
    <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->movilidad ?? 'S/D' }}</p>
    </div>
    <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Riesgo Caída</p>
    <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->riesgo_caida ?? 'S/D' }}</p>
    </div>
    <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Piel</p>
    <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->piel_estado ?? 'S/D' }}</p>
    </div>
    <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Higiene</p>
    <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->higiene_ingreso ?? 'S/D' }}</p>
    </div>
    <div class="col-span-2">
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Continencia básica</p>
    <p class="text-xs font-bold text-parrafo mt-0.5">{{ $detalle->continencia_basica ?? 'S/D' }}</p>
    </div>
   </div>

   <!-- Subgrid: Dolor / Heridas -->
   <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 pt-2 border-t border-borde/40 bg-fondo-app/20 p-2.5 rounded-lg">
    <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Dolor clínico</p>
    @if($detalle->hay_dolor)
     <p class="text-xs text-parrafo font-bold mt-0.5">
     Presente (Intensidad: <span class="text-estado-peligro font-extrabold">{{ $detalle->intensidad_dolor }}/10</span>) - {{ $detalle->ubicacion_dolor ?? 'Ubicación no especificada' }}
     </p>
    @else
     <p class="text-xs text-apoyo font-bold mt-0.5">No presenta</p>
    @endif
    </div>
    <div>
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Heridas / Úlceras</p>
    @if($detalle->hay_heridas)
     <p class="text-xs text-parrafo font-bold mt-0.5">
     Presente - <span class="text-estado-peligro">{{ $detalle->ubicacion_heridas }}</span>
     </p>
    @else
     <p class="text-xs text-apoyo font-bold mt-0.5">No presenta</p>
    @endif
    </div>
   </div>
   </div>

   <!-- Valoración Geriátrica Rápida & Anamnesis -->
   <div class="rounded-xl border border-borde/70 p-4 space-y-3">
   <h4 class="font-extrabold text-parrafo border-b border-borde/50 pb-1.5 uppercase tracking-wide flex items-center gap-1.5 text-boton-acento">
    <i class="ph-bold ph-scales"></i> Valoración Geriátrica & Anamnesis Referida
   </h4>
   @if($isStructuredObs)
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Dependencia Funcional</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ str_replace('_', ' ', $obs['dependencia_funcional']) }}</p>
    </div>
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Riesgo Nutricional</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ str_replace('_', ' ', $obs['riesgo_nutricional']) }}</p>
    </div>
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Deterioro Cognitivo</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ str_replace('_', ' ', $obs['riesgo_cognitivo']) }}</p>
    </div>
    <div class="col-span-3">
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Necesidad de apoyo inmediato</p>
     <p class="text-xs font-bold text-parrafo mt-0.5">{{ $obs['necesidad_apoyo_inmediato'] ?: 'Ninguna identificada' }}</p>
    </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-2 pt-2 border-t border-borde/40 bg-fondo-app/20 p-2.5 rounded-lg">
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Antecedentes Clínicos</p>
     <p class="text-xs text-parrafo mt-0.5 leading-relaxed uppercase">{{ $obs['antecedentes_relevantes'] ?: 'Ninguno referido' }}</p>
    </div>
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Medicación Habitual</p>
     <p class="text-xs text-parrafo mt-0.5 leading-relaxed uppercase">{{ $obs['medicacion_referida'] ?: 'Ninguna referida' }}</p>
    </div>
    <div>
     <p class="text-[10px] font-bold text-parrafo/60 uppercase">Alergias</p>
     <p class="text-xs text-parrafo mt-0.5 leading-relaxed uppercase text-estado-peligro">{{ $obs['alergias_referidas'] ?: 'Ninguna referida' }}</p>
    </div>
    </div>
   @else
    <p class="text-xs text-parrafo whitespace-pre-wrap">{{ $detalle->observacion }}</p>
   @endif
   </div>

   <!-- Recomendación y Comentarios -->
   <div class="rounded-xl border border-borde/70 p-4 bg-estado-exitoBg/5 space-y-3">
   <h4 class="font-extrabold text-parrafo border-b border-borde/50 pb-1.5 uppercase tracking-wide flex items-center gap-1.5 text-estado-exito">
    <i class="ph-bold ph-notebook"></i> Conclusiones e Indicación Directa
   </h4>
   @if($isStructuredObs && $obs['comentarios'])
    <div class="mb-3">
    <p class="text-[10px] font-bold text-parrafo/60 uppercase">Comentarios Adicionales del Enfermero</p>
    <p class="text-xs text-parrafo mt-0.5 uppercase leading-relaxed">{{ $obs['comentarios'] }}</p>
    </div>
   @endif
   <div>
    <p class="text-[10px] font-bold text-estado-exito uppercase">Recomendación Médico-Clínica de Enfermería</p>
    <p class="text-xs font-bold text-parrafo mt-0.5 uppercase leading-relaxed whitespace-pre-wrap">{{ $detalle->recomendacion_enfermeria }}</p>
   </div>
   </div>
  </div>

  <div class="border-t border-borde/70 bg-fondo-app px-6 py-4 text-right shrink-0">
   <button wire:click="cerrarModales" class="rounded-xl bg-boton-principal px-5 py-2 text-xs font-bold text-inverso transition hover:bg-fondo-panel">
   Cerrar Detalle
   </button>
  </div>
  </div>
 </div>
 @endif
</div>
