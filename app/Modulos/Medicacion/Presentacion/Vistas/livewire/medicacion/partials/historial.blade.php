<div class="space-y-5 font-sans bg-[var(--rm-bg-app)] p-3 sm:p-5 rounded-2xl">

 {{-- ==================================================
 1. BARRA DE FILTROS ESPECÍFICA DE HISTORIAL
 ================================================== --}}
 <x-ui.filter-bar class="mb-4">

 {{-- Fila Principal: Búsqueda y Selectores Primarios --}}
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">

 {{-- 1. Búsqueda textual: residente o medicamento --}}
 <div class="lg:col-span-4 relative flex items-center">
 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-muted)]">
  <i class="ph ph-magnifying-glass text-base"></i>
 </span>
 <span class="sr-only">Buscar residente...</span>
 <input type="text"
  wire:model.live.debounce.300ms="filtroHistorialBusqueda"
  placeholder="Buscar residente o medicamento..."
  title="Buscar residente..."
  aria-label="Buscar residente..."
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-input-placeholder)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]" />
 @if(!empty($filtroHistorialBusqueda))
  <button type="button"
  wire:click="limpiarFiltro('filtroHistorialBusqueda')"
  class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] transition cursor-pointer"
  title="Limpiar búsqueda">
  <i class="ph ph-x-circle text-base"></i>
  </button>
 @endif
 </div>

 {{-- 2. Resultado (Administrada / Omitida / Rechazada) --}}
 <div class="lg:col-span-3">
 <select wire:model.live="filtroHistorialResultado"
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  <option value="">Todos los resultados</option>
  <option value="ADMINISTRADA">Administradas</option>
  <option value="OMITIDA">OMITIDA / Omisiones</option>
  <option value="RECHAZADA">Rechazadas</option>
 </select>
 </div>

 {{-- 3. Residente --}}
 <div class="lg:col-span-3">
 <select wire:model.live="filtroHistorialResidente"
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
  <option value="">Todos los residentes</option>
  @foreach($residentes as $res)
  <option value="{{ $res->cod_residente }}">
  {{ $res->apellido_paterno ?? $res->ap_paterno }} {{ $res->nombres }}
  </option>
  @endforeach
 </select>
 </div>

 {{-- 4. Vía de Administración --}}
 <div class="lg:col-span-2">
 <select wire:model.live="filtroHistorialVia"
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]">
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
 </div>

 {{-- Fila Secundaria: Filtro Fármaco y Rango de Fechas --}}
 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 pt-2 border-t border-[var(--rm-border-soft)]/60 /60 items-center">

 {{-- Filtro Medicamento Específico --}}
 <div class="lg:col-span-6 relative flex items-center">
 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-muted)]">
  <i class="ph ph-pill text-base"></i>
 </span>
 <span class="sr-only">Buscar residente...</span>
 <input type="text"
  wire:model.live.debounce.300ms="filtroHistorialMedicamento"
  placeholder="Filtrar por medicamento..."
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]" />
 @if(!empty($filtroHistorialMedicamento))
  <button type="button"
  wire:click="limpiarFiltro('filtroHistorialMedicamento')"
  class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-muted)] hover:text-[var(--rm-action-primary)] transition cursor-pointer"
  title="Limpiar medicamento">
  <i class="ph ph-x-circle text-base"></i>
  </button>
 @endif
 </div>

 {{-- Fecha Desde --}}
 <div class="lg:col-span-3 flex items-center gap-2">
 <label for="fDesde" class="text-[11px] font-bold text-[var(--rm-text-muted)] shrink-0">Desde:</label>
 <input type="date"
  id="fDesde"
  wire:model.live="filtroHistorialFechaDesde"
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]"
  title="Fecha desde" />
 </div>

 {{-- Fecha Hasta --}}
 <div class="lg:col-span-3 flex items-center gap-2">
 <label for="fHasta" class="text-[11px] font-bold text-[var(--rm-text-muted)] shrink-0">Hasta:</label>
 <input type="date"
  id="fHasta"
  wire:model.live="filtroHistorialFechaHasta"
  class="w-full rounded-xl border border-[var(--rm-border-soft)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-focus)] focus:outline-none h-[38px]"
  title="Fecha hasta" />
 </div>
 </div>

 {{-- Fila de chips de filtros activos --}}
 @php
 $historialChipsActivos = !empty($filtroHistorialBusqueda)
 || !empty($filtroHistorialResultado)
 || !empty($filtroHistorialResidente)
 || !empty($filtroHistorialMedicamento)
 || !empty($filtroHistorialVia)
 || !empty($filtroHistorialFechaDesde)
 || !empty($filtroHistorialFechaHasta);
 @endphp

 @if($historialChipsActivos)
 <div class="rm-filter-bar__active">
 <div class="flex flex-wrap items-center gap-1.5">
  <span class="rm-filter-bar__active-label">
  Filtros activos:
  </span>

  @if(!empty($filtroHistorialBusqueda))
  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] text-[11px] font-semibold shadow-2xs">
  <i class="ph ph-magnifying-glass text-xs text-[var(--rm-action-primary)]"></i>
  <span>Búsqueda: "{{ Str::limit($filtroHistorialBusqueda, 18) }}"</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialBusqueda')" class="hover:text-[var(--rm-action-primary)] transition cursor-pointer ml-0.5">
  <i class="ph ph-x"></i>
  </button>
  </span>
  @endif

  @if(!empty($filtroHistorialResultado))
  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ strtoupper($filtroHistorialResultado) === 'ADMINISTRADA' ? 'bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-active)] border border-[var(--rm-action-primary)]/40' : 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-action-primary)]/20 text-[var(--rm-action-primary)] border border-[var(--rm-danger)]/40' }} text-[11px] font-bold shadow-2xs">
  <span>Resultado: {{ ucfirst(strtolower($filtroHistorialResultado)) }}</span>
  <span class="sr-only">({{ strtoupper($filtroHistorialResultado) }})</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialResultado')" class="hover:text-[var(--rm-action-primary-active)] transition cursor-pointer ml-0.5">
  <i class="ph ph-x"></i>
  </button>
  </span>
  @endif

  @if(!empty($filtroHistorialResidente))
  @php $resHistFiltrado = $residentes->firstWhere('cod_residente', $filtroHistorialResidente); @endphp
  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] text-[11px] font-semibold shadow-2xs">
  <i class="ph ph-user text-xs text-[var(--rm-action-primary)]"></i>
  <span>Residente: {{ $resHistFiltrado ? ($resHistFiltrado->apellido_paterno ?? $resHistFiltrado->nombres) : 'Filtrado' }}</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialResidente')" class="hover:text-[var(--rm-action-primary)] transition cursor-pointer ml-0.5">
  <i class="ph ph-x"></i>
  </button>
  </span>
  @endif

  @if(!empty($filtroHistorialMedicamento))
  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] text-[11px] font-semibold shadow-2xs">
  <i class="ph ph-pill text-xs text-[var(--rm-action-primary)]"></i>
  <span>Medicamento: "{{ Str::limit($filtroHistorialMedicamento, 16) }}"</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialMedicamento')" class="hover:text-[var(--rm-action-primary)] transition cursor-pointer ml-0.5">
  <i class="ph ph-x"></i>
  </button>
  </span>
  @endif

  @if(!empty($filtroHistorialVia))
  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)] text-[11px] font-semibold shadow-2xs">
  <span>Vía: {{ ucfirst(strtolower($filtroHistorialVia)) }}</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialVia')" class="hover:text-[var(--rm-action-primary)] transition cursor-pointer ml-0.5">
  <i class="ph ph-x"></i>
  </button>
  </span>
  @endif

  @if(!empty($filtroHistorialFechaDesde))
  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)] border border-[var(--rm-border-soft)] text-[11px] font-medium shadow-2xs">
  <span>Desde: {{ \Carbon\Carbon::parse($filtroHistorialFechaDesde)->format('d/m/Y') }}</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialFechaDesde')" class="hover:text-[var(--rm-action-primary)] transition cursor-pointer ml-0.5"><i class="ph ph-x"></i></button>
  </span>
  @endif

  @if(!empty($filtroHistorialFechaHasta))
  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)] border border-[var(--rm-border-soft)] text-[11px] font-medium shadow-2xs">
  <span>Hasta: {{ \Carbon\Carbon::parse($filtroHistorialFechaHasta)->format('d/m/Y') }}</span>
  <button type="button" wire:click="limpiarFiltro('filtroHistorialFechaHasta')" class="hover:text-[var(--rm-action-primary)] transition cursor-pointer ml-0.5"><i class="ph ph-x"></i></button>
  </span>
  @endif
 </div>

 {{-- Conteo y Botón Restablecer --}}
 <div class="flex items-center gap-2.5">
  <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold bg-[var(--rm-text-primary)]/10 text-[var(--rm-text-primary)]">
  {{ count($historial) }} coincidentes
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
 2. CONTENEDOR PRINCIPAL: BITÁCORA CLÍNICA
 ================================================== --}}
 <main class="bg-[var(--rm-surface)] rounded-2xl border border-[var(--rm-border-soft)] shadow-sm overflow-hidden transition-colors">

 {{-- Header Clínico de Bitácora con Contadores Sincronizados --}}
 <header class="px-5 py-4 border-b border-[var(--rm-border-soft)] flex flex-col md:flex-row md:items-center justify-between gap-4 bg-[var(--rm-surface)] ">
 <div class="flex items-center gap-3">
 <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--rm-text-primary)] text-white shadow-2xs">
  <i class="ph-bold ph-notebook text-2xl"></i>
 </span>
 <div>
  <div class="flex items-center gap-2.5 flex-wrap">
  <h3 class="text-[17px] font-[800] text-[var(--rm-text-primary)] tracking-tight">
  Historial de Administración
  </h3>
  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-border-soft)]">
  <i class="ph-bold ph-shield-check text-xs"></i> Bitácora clínica
  </span>
  </div>
  <p class="text-[12px] text-[var(--rm-text-muted)] mt-0.5">
  Trazabilidad clínica completa: registro asistencial directo de dosis, horarios, omisiones e incidencias
  </p>
 </div>
 </div>

 {{-- Métricas Rápidas en el Header --}}
 @php
 $totalH = count($historial);
 $totalAdmin = $historial->filter(fn($r) => (($r->resultado ?? '') === 'ADMINISTRADA') || !empty($r->administrado))->count();
 $totalOmit = $historial->filter(fn($r) => (($r->resultado ?? '') === 'OMITIDA') || !empty($r->motivo_omision))->count();
 $totalRech = $historial->filter(fn($r) => ($r->resultado ?? '') === 'RECHAZADA')->count();
 @endphp
 <div class="flex flex-wrap items-center gap-2 self-start md:self-center">
 <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-xs font-bold text-[var(--rm-text-primary)] shadow-2xs">
  <span class="w-2 h-2 rounded-full bg-[var(--rm-text-primary)]"></span>
  <span>Total: {{ $totalH }}</span>
 </div>
 <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] border border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40 text-xs font-bold text-[var(--rm-action-primary)] shadow-2xs">
  <i class="ph-bold ph-check text-xs"></i>
  <span>Administradas: {{ $totalAdmin }}</span>
 </div>
 @if($totalOmit > 0)
  <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40 text-xs font-bold text-[var(--rm-danger)] shadow-2xs">
  <i class="ph-bold ph-warning text-xs"></i>
  <span>Omisiones: {{ $totalOmit }}</span>
  </div>
 @endif
 @if($totalRech > 0)
  <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40 text-xs font-bold text-[var(--rm-danger)] shadow-2xs">
  <i class="ph-bold ph-x text-xs"></i>
  <span>Rechazadas: {{ $totalRech }}</span>
  </div>
 @endif
 </div>
 </header>

 {{-- ==================================================
 3. LISTADO CRONOLÓGICO DE REGISTROS (BITÁCORA)
 ================================================== --}}
 @php
 // Asegurar estricto orden cronológico: del más reciente al más antiguo
 $historialOrdenado = $historial->sortByDesc(function($reg) {
 return $reg->fecha_hora_programada ?? $reg->fecha_hora_administracion ?? (isset($reg->created_at) ? $reg->created_at : now());
 });
 @endphp

 <div class="p-3 sm:p-5 space-y-4">
 @forelse($historialOrdenado as $reg)
 @php
  // Datos del Residente
  $res = $reg->residente ?? null;
  $nomRes = $res ? trim(($res->nombres ?? '') . ' ' . ($res->apellido_paterno ?? $res->ap_paterno ?? '') . ' ' . ($res->apellido_materno ?? $res->ap_materno ?? '')) : 'Residente';
  $docRes = isset($res->numero_documento) ? $res->numero_documento : (isset($res->nhc) ? $res->nhc : null);

  // Habitación y Cama (Acceso seguro compatible con Eloquent y stdClass)
  $camaObj = null;
  if (is_object($res)) {
  if ($res instanceof \Illuminate\Database\Eloquent\Model) {
  $camaObj = $res->ocupacionActiva?->cama;
  } elseif (isset($res->ocupacionActiva)) {
  $camaObj = $res->ocupacionActiva?->cama ?? null;
  }
  }
  $habObj = isset($camaObj->habitacion) ? $camaObj->habitacion : null;
  $camaTexto = isset($camaObj->nombre) ? $camaObj->nombre : (isset($camaObj->codigo) ? $camaObj->codigo : null);
  $habTexto = isset($habObj->nombre) ? $habObj->nombre : (isset($habObj->numero) ? "Hab. {$habObj->numero}" : null);
  $ubicacionTexto = ($habTexto && $camaTexto) ? "{$habTexto} · Cama {$camaTexto}" : ($habTexto ?: ($camaTexto ? "Cama {$camaTexto}" : 'Habitación no asignada'));

  // Fechas
  $dtProg = !empty($reg->fecha_hora_programada) ? \Carbon\Carbon::parse($reg->fecha_hora_programada) : null;
  $dtReal = !empty($reg->fecha_hora_administracion) ? \Carbon\Carbon::parse($reg->fecha_hora_administracion) : null;
  $fechaEventoTexto = $dtReal ? $dtReal->translatedFormat('d M Y') : ($dtProg ? $dtProg->translatedFormat('d M Y') : 'Fecha no registrada');

  // Estado Asistencial
  $resulRaw = strtoupper(trim((string)($reg->resultado ?? '')));
  $esAdmin = in_array($resulRaw, ['ADMINISTRADA', 'ADMINISTRADO', 'REALIZADA', 'APLICADA', 'SUMINISTRADA'], true) || (!empty($reg->administrado) && $resulRaw !== 'OMITIDA');
  $esOmision = $resulRaw === 'OMITIDA' || !empty($reg->motivo_omision);
  $esRechazo = $resulRaw === 'RECHAZADA';

  $estadoTexto = match(true) {
  $esAdmin => 'ADMINISTRADA',
  $esOmision => 'OMITIDA',
  $esRechazo => 'RECHAZADA',
  default => ($resulRaw ?: 'REGISTRADA'),
  };

  // Colores por estado estricto: Sage SOLO para positiva, Terracota/Rojo para omisión/retraso
  $estadoBadgeClass = match(true) {
  $esAdmin => 'bg-[var(--rm-action-primary-soft)] dark:bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary)] border-[var(--rm-action-primary)]/40 dark:border-[var(--rm-action-primary)]/40',
  $esOmision || $esRechazo => 'bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] text-[var(--rm-danger)] border-[var(--rm-danger)]/40 dark:border-[var(--rm-danger)]/40',
  default => 'bg-[var(--rm-warning-soft)] dark:bg-[var(--rm-warning)]/20 text-[var(--rm-warning)] dark:text-[var(--rm-warning-soft)] border-[var(--rm-warning-soft)] dark:border-[var(--rm-warning)]/40',
  };

  // Datos de Prescripción y Fármaco
  $presc = $reg->prescripcion ?? null;
  $med = isset($presc->medicamento) ? $presc->medicamento : null;
  $medGenerico = (isset($med->nombre_generico) && $med->nombre_generico) ? $med->nombre_generico : ($presc->nombre_medicamento ?? 'Fármaco sin especificar');
  $medComercial = isset($med->nombre_comercial) ? $med->nombre_comercial : null;
  $concentracion = isset($med->concentracion) ? $med->concentracion : null;
  $dosisPrescrita = ((isset($presc->dosis) && $presc->dosis) ? $presc->dosis . ' ' : '') . ($presc->unidad_dosis ?? 'mg');
  $viaTexto = ucfirst(strtolower((string)($presc->via_administracion ?? 'Oral')));
  $frecuencia = isset($presc->frecuencia) ? $presc->frecuencia : '—';
  $indicacion = isset($presc->indicacion) ? $presc->indicacion : null;
  $horaProgramadaStr = $dtProg ? $dtProg->format('H:i') : (isset($presc->hora_programada) ? $presc->hora_programada : '—');

  // Ejecución Real
  $fProgFormato = $dtProg ? $dtProg->format('d/m/Y H:i') : '—';
  $fRealFormato = $dtReal ? $dtReal->format('d/m/Y H:i') : ($esOmision ? 'No administrada' : '—');
  $dosisAdminStr = (isset($reg->dosis_administrada) && $reg->dosis_administrada !== null) ? ($reg->dosis_administrada . ' ' . ($presc->unidad_dosis ?? 'mg')) : ($esAdmin ? $dosisPrescrita : '—');

  // Diferencia de Horario
  if ($dtProg && $dtReal) {
  $minDiff = $dtProg->diffInMinutes($dtReal, false);
  if (abs($minDiff) <= 10) {
  $difTexto = 'A tiempo (en horario)';
  $difColor = 'text-[var(--rm-action-primary)] font-semibold';
  } elseif ($minDiff > 10) {
  $difTexto = "+{$minDiff} min de retraso";
  $difColor = 'text-[var(--rm-danger)] font-bold';
  } else {
  $difTexto = abs($minDiff) . " min anticipada";
  $difColor = 'text-[var(--rm-text-muted)]';
  }
  } else {
  $difTexto = $esOmision ? 'No aplica (omisión)' : '—';
  $difColor = 'text-[var(--rm-text-muted)]';
  }

  // Profesional y Trazabilidad Real
  $prof = $reg->personal ?? null;
  $profNom = $prof ? trim(($prof->nombres ?? '') . ' ' . ($prof->apellido_paterno ?? '')) : (isset($reg->registrador->nombres) ? $reg->registrador->nombres : 'Personal de enfermería');
  $codJornada = isset($reg->cod_jornada) ? $reg->cod_jornada : null;
  $fechaRegistro = $dtReal ? $dtReal->format('d/m/Y H:i:s') : ($dtProg ? $dtProg->format('d/m/Y H:i') : '—');

  // Alertas Críticas (Reacción Adversa)
  $tieneReaccion = !empty($reg->reaccion_adversa);
 @endphp

 {{-- Tarjeta Autosuficiente del Registro --}}
 <article class="rounded-xl bg-[var(--rm-surface-soft)] border {{ $tieneReaccion ? 'border-[var(--rm-danger)] shadow-sm' : ($esOmision ? 'border-[var(--rm-action-primary)]/80' : 'border-[var(--rm-border-soft)]') }} shadow-2xs overflow-hidden transition-all">

  {{-- ==================================================
  CABECERA DEL REGISTRO
  ================================================== --}}
  <div class="px-4 py-3 bg-[var(--rm-surface-soft)] border-b border-[var(--rm-border-soft)] flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">

  {{-- Identificación del Residente y Ubicación --}}
  <div class="flex items-center gap-2.5 min-w-0">
  <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[var(--rm-text-primary)] text-white font-bold text-xs shadow-2xs">
  <i class="ph-bold ph-user"></i>
  </span>
  <div class="min-w-0">
  <div class="flex items-center gap-2 flex-wrap">
   <h4 class="text-sm font-extrabold text-[var(--rm-text-primary)] truncate">
   {{ $nomRes }}
   </h4>
   @if($docRes)
   <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[var(--rm-surface-soft)] text-[var(--rm-text-muted)] border border-[var(--rm-border-soft)]">
   NHC/Doc: {{ $docRes }}
   </span>
   @endif
  </div>
  <div class="flex items-center gap-3 text-[11px] text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
   <span class="inline-flex items-center gap-1 font-medium">
   <i class="ph ph-door text-xs text-[var(--rm-action-primary)]"></i>
   {{ $ubicacionTexto }}
   </span>
   <span class="inline-flex items-center gap-1 font-medium">
   <i class="ph ph-calendar-blank text-xs text-[var(--rm-action-primary)]"></i>
   {{ $fechaEventoTexto }}
   </span>
  </div>
  </div>
  </div>

  {{-- Estado y Profesional Registrador --}}
  <div class="flex items-center gap-2 self-start sm:self-center shrink-0 flex-wrap">
  <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-extrabold uppercase tracking-wide border shadow-2xs {{ $estadoBadgeClass }}">
  @if($esAdmin)
   <i class="ph-bold ph-check text-xs"></i>
  @elseif($esOmision)
   <i class="ph-bold ph-warning text-xs"></i>
  @elseif($esRechazo)
   <i class="ph-bold ph-x text-xs"></i>
  @else
   <i class="ph-bold ph-clock text-xs"></i>
  @endif
  <span>{{ $estadoTexto }}</span>
  </span>

  <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] text-[11px] text-[var(--rm-text-primary)] ">
  <i class="ph ph-identification-badge text-xs text-[var(--rm-action-primary)]"></i>
  <span class="font-medium truncate max-w-[140px]">{{ $profNom }}</span>
  </div>
  </div>
  </div>

  {{-- CUERPO DEL REGISTRO: 3 a 4 BLOQUES DIRECTOS --}}
  <div class="p-3 sm:p-4 space-y-3">

  {{-- BANNER DE ALERTA: REACCIÓN ADVERSA (SI EXISTE, PRIORIDAD VISUAL MÁXIMA) --}}
  @if($tieneReaccion)
  <div class="p-3 rounded-xl bg-[var(--rm-danger-soft)] dark:bg-[var(--rm-danger-soft)] border-2 border-[var(--rm-danger)] flex items-start gap-2.5 text-xs text-[var(--rm-action-primary-active)] shadow-2xs animate-pulse">
  <i class="ph-bold ph-warning-octagon text-lg text-[var(--rm-danger)] shrink-0 mt-0.5"></i>
  <div>
   <span class="font-extrabold uppercase tracking-wider block text-[11px] text-[var(--rm-danger)]">
   Alerta Clínica Prioritaria: Reacción Adversa
   </span>
   <p class="font-bold text-xs mt-0.5 leading-snug">
   {{ $reg->reaccion_adversa }}
   </p>
  </div>
  </div>
  @endif

  {{-- GRID PRINCIPAL DE BLOQUES CLÍNICOS --}}
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 text-xs">

  {{-- ==================================================
   BLOQUE 1 — FÁRMACO Y PAUTA
   ================================================== --}}
  <div class="lg:col-span-4 p-3 rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] flex flex-col justify-between space-y-2">
  <div>
   <div class="flex items-center justify-between pb-1.5 border-b border-[var(--rm-border-soft)]/60 ">
   <span class="text-[10px] font-extrabold uppercase tracking-wider text-[var(--rm-action-primary)] flex items-center gap-1">
   <i class="ph-bold ph-pill"></i> Fármaco y Pauta
   </span>
   <span class="text-[10px] font-bold text-[var(--rm-text-muted)]">
   Prog: {{ $horaProgramadaStr }}
   </span>
   </div>

   <div class="mt-2">
   <div class="font-extrabold text-[13.5px] text-[var(--rm-text-primary)] leading-tight">
   {{ $medGenerico }}
   </div>
   @if(!empty($medComercial) && $medComercial !== $medGenerico)
   <div class="text-[11px] font-semibold text-[var(--rm-text-muted)] mt-0.5">
   Comercial: {{ $medComercial }}
   </div>
   @endif
   </div>

   <div class="mt-2 space-y-1 text-[11px]">
   <div class="flex justify-between py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Concentración:</span>
   <span class="font-bold text-[var(--rm-text-primary)] ">{{ $concentracion ?: '—' }}</span>
   </div>
   <div class="flex justify-between py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Dosis prescrita:</span>
   <span class="font-bold text-[var(--rm-text-primary)] ">{{ $dosisPrescrita ?: '—' }}</span>
   </div>
   <div class="flex justify-between py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Vía:</span>
   <span class="font-bold text-[var(--rm-text-primary)] ">{{ $viaTexto }}</span>
   </div>
   <div class="flex justify-between py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Frecuencia:</span>
   <span class="font-bold text-[var(--rm-text-primary)] ">{{ $frecuencia }}</span>
   </div>
   @if(!empty($indicacion))
   <div class="pt-1 text-[10.5px]">
   <span class="text-[var(--rm-text-muted)] block">Indicación:</span>
   <span class="font-semibold text-[var(--rm-text-primary)] ">{{ $indicacion }}</span>
   </div>
   @endif
   </div>
  </div>
  </div>

  {{-- ==================================================
   BLOQUE 2 — EJECUCIÓN REAL
   ================================================== --}}
  <div class="lg:col-span-4 p-3 rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] flex flex-col justify-between space-y-2">
  <div>
   <div class="flex items-center justify-between pb-1.5 border-b border-[var(--rm-border-soft)]/60 ">
   <span class="text-[10px] font-extrabold uppercase tracking-wider text-[var(--rm-action-primary)] flex items-center gap-1">
   <i class="ph-bold ph-clock-counter-clockwise"></i> Ejecución Real
   </span>
   <span class="text-[10px] font-bold {{ $difColor }}">
   {{ $difTexto }}
   </span>
   </div>

   <div class="mt-2 space-y-1.5 text-[11.5px]">
   <div class="flex justify-between items-center py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Programada:</span>
   <span class="font-bold text-[var(--rm-text-primary)] ">{{ $fProgFormato }}</span>
   </div>
   <div class="flex justify-between items-center py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Administración:</span>
   <span class="font-bold {{ $esOmision ? 'text-[var(--rm-danger)]' : 'text-[var(--rm-text-primary)] ' }}">
   {{ $fRealFormato }}
   </span>
   </div>
   <div class="flex justify-between items-center py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Resultado:</span>
   <span class="font-extrabold {{ $esAdmin ? 'text-[var(--rm-action-primary)]' : ($esOmision ? 'text-[var(--rm-danger)]' : 'text-[var(--rm-text-primary)]') }}">
   {{ $estadoTexto }}
   </span>
   </div>
   <div class="flex justify-between items-center py-0.5 border-b border-[var(--rm-border-soft)]/40">
   <span class="text-[var(--rm-text-muted)]">Dosis administrada:</span>
   <span class="font-bold text-[var(--rm-text-primary)] ">{{ $dosisAdminStr }}</span>
   </div>
   </div>
  </div>

  {{-- Detalle de diferencia horaria si corresponde --}}
  <div class="pt-1.5 text-[10.5px] text-[var(--rm-text-muted)] flex items-center justify-between">
   <span>Puntualidad:</span>
   <span class="{{ $difColor }}">{{ $difTexto }}</span>
  </div>
  </div>

  {{-- ==================================================
   BLOQUE 3 — INCIDENCIAS / SEGUIMIENTO
   ================================================== --}}
  <div class="lg:col-span-4 p-3 rounded-xl {{ $esOmision ? 'bg-[var(--rm-danger-soft)] border-l-4 border-l-[var(--rm-danger)] border-t border-r border-b border-[var(--rm-danger)]/40' : 'bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)]' }} flex flex-col justify-between space-y-2">
  <div>
   <div class="flex items-center justify-between pb-1.5 border-b border-[var(--rm-border-soft)]/60 ">
   <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $esOmision ? 'text-[var(--rm-danger)]' : 'text-[var(--rm-action-primary)] ' }} flex items-center gap-1">
   <i class="ph-bold {{ $esOmision ? 'ph-warning-circle' : 'ph-activity' }}"></i> Incidencias / Seguimiento
   </span>
   @if($esOmision)
   <span class="text-[10px] font-bold text-[var(--rm-danger)] bg-[var(--rm-danger-soft)] px-2 py-0.5 rounded-full border border-[var(--rm-danger)]/40">
   Dosis no aplicada
   </span>
   @endif
   </div>

   <div class="mt-2 space-y-2 text-[11px]">
   {{-- Motivo de Omisión --}}
   <div>
   <span class="text-[var(--rm-text-muted)] block text-[10px] uppercase font-bold tracking-wider">
   Motivo de Omisión:
   </span>
   @if(!empty($reg->motivo_omision))
   <div class="font-extrabold text-[var(--rm-danger)] mt-0.5 flex items-center gap-1">
    <i class="ph-bold ph-warning"></i>
    <span>{{ $reg->motivo_omision }}</span>
   </div>
   @else
   <span class="text-[var(--rm-text-muted)] font-medium">—</span>
   @endif
   </div>

   {{-- Efecto Observado --}}
   <div>
   <span class="text-[var(--rm-text-muted)] block text-[10px] uppercase font-bold tracking-wider">
   Efecto Observado:
   </span>
   <span class="font-semibold text-[var(--rm-text-primary)] ">
   {{ !empty($reg->efecto_observado) ? $reg->efecto_observado : '—' }}
   </span>
   </div>

   {{-- Reacción Adversa --}}
   <div>
   <span class="text-[var(--rm-text-muted)] block text-[10px] uppercase font-bold tracking-wider">
   Reacción Adversa:
   </span>
   @if($tieneReaccion)
   <span class="font-extrabold text-[var(--rm-danger)] block">
    {{ $reg->reaccion_adversa }}
   </span>
   @else
   <span class="text-[var(--rm-text-muted)] font-medium">Sin registro</span>
   @endif
   </div>
   </div>
  </div>
  </div>
  </div>

  {{-- ==================================================
  BLOQUE 4 — NOTA DE ENFERMERÍA Y OBSERVACIÓN
  ================================================== --}}
  <div class="p-3 rounded-xl bg-[var(--rm-surface-raised)] border border-[var(--rm-border-soft)] space-y-1">
  <div class="flex items-center justify-between pb-1 border-b border-[var(--rm-border-soft)]/50 ">
  <span class="text-[10px] font-extrabold uppercase tracking-wider text-[var(--rm-action-primary)] flex items-center gap-1">
   <i class="ph-bold ph-note-pencil"></i> Nota de Enfermería / Observación Clínica
  </span>
  <span class="text-[10px] text-[var(--rm-text-muted)]">
   Texto completo
  </span>
  </div>
  <div class="text-xs text-[var(--rm-text-primary)] leading-relaxed pt-1 whitespace-pre-line break-words font-medium">
  {{ !empty($reg->observacion) ? $reg->observacion : 'Sin observaciones registradas.' }}
  </div>
  </div>

  {{-- ==================================================
  TRAZABILIDAD DEL REGISTRO (SIN BOTÓN "VER DETALLE")
  ================================================== --}}
  <div class="pt-2 border-t border-[var(--rm-border-soft)]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
  <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[var(--rm-text-muted)]">
  <span class="inline-flex items-center gap-1.5">
   <i class="ph-bold ph-user-circle text-[var(--rm-action-primary)]"></i>
   <span>Responsable: <strong class="text-[var(--rm-text-primary)] font-bold">{{ $profNom }}</strong></span>
  </span>

  @if($codJornada)
   <span class="inline-flex items-center gap-1">
   <span class="text-[var(--rm-border-soft)]">&bull;</span>
   <i class="ph ph-sun text-xs text-[var(--rm-action-primary)]"></i>
   <span>Jornada: <strong class="text-[var(--rm-text-primary)] font-semibold">{{ $codJornada }}</strong></span>
   </span>
  @endif

  <span class="inline-flex items-center gap-1">
   <span class="text-[var(--rm-border-soft)]">&bull;</span>
   <i class="ph ph-clock text-xs text-[var(--rm-text-muted)]"></i>
   <span>Registro: {{ $fechaRegistro }}</span>
  </span>
  </div>

  <div class="flex items-center gap-2 self-end sm:self-auto">
  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border border-[var(--rm-border-soft)]">
   <i class="ph-bold ph-check-circle text-xs text-[var(--rm-action-primary)]"></i> Registro clínico trazable
  </span>
  </div>
  </div>

  </div>
 </article>
 @empty
 {{-- ESTADO VACÍO --}}
 <div class="p-8 sm:p-12 text-center rounded-xl bg-[var(--rm-surface-soft)] border border-[var(--rm-border-soft)] space-y-3">
  <div class="w-14 h-14 mx-auto rounded-full bg-[var(--rm-surface-soft)] text-[var(--rm-action-primary)] flex items-center justify-center text-2xl shadow-2xs">
  <i class="ph ph-magnifying-glass"></i>
  </div>
  <div class="space-y-1">
  <h4 class="text-sm font-bold text-[var(--rm-text-primary)] ">
  No se encontraron registros en el historial
  </h4>
  <p class="text-xs text-[var(--rm-text-muted)] max-w-md mx-auto">
  No existen administraciones u omisiones con los criterios de búsqueda seleccionados.
  </p>
  </div>
  <button type="button"
  wire:click="resetFilters"
  class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-[var(--rm-action-primary)] text-white hover:bg-[var(--rm-action-primary-hover)] text-xs font-bold transition shadow-2xs cursor-pointer">
  <i class="ph ph-arrow-counter-clockwise"></i>
  <span>Restablecer filtros</span>
  </button>
 </div>
 @endforelse
 </div>
 </main>

</div>
