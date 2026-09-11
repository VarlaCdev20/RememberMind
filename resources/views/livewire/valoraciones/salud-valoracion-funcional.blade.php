<div class="salud-valoracion-scope space-y-6">
 <style>

{!! file_get_contents(resource_path('frontend/styles/modules/livewire-admin-salud-seguimiento-salud-valoracion-funcional.css')) !!}
</style>
 @if($adulto)<x-residentes.navegacion-ficha :adulto="$adulto" />@endif
 <section class="overflow-hidden rounded-[1.6rem] border border-borde/65 bg-fondo-panel shadow-sm backdrop-blur-xl">
 <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
 <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
 <div class="flex items-center gap-3">
 <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-borde/55 bg-fondo-panel text-boton-acento shadow-sm">
 <i class="ph-bold ph-person-simple-walk text-2xl"></i>
 </span>
 <div>
 <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-boton-acento">Autonomía y dependencia</span>
 <h2 class="text-xl font-extrabold tracking-tight text-parrafo">Valoración funcional</h2>
 <p class="mt-1 text-xs font-bold leading-relaxed text-parrafo/62">
 Seguimiento de autonomía, nivel de dependencia funcional, riesgo de caída y apoyos requeridos.
 </p>
 </div>
 </div>

 @can('salud.valoracion.crear')
 <button wire:click="abrirFormNuevo" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-[0_10px_22px_rgba(226,125,96,0.24)] transition hover:-translate-y-0.5 hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-plus-circle text-sm"></i>
 Registrar valoración
 </button>
 @endcan
 </div>
 </section>
 {{-- ENCABEZADO DE SECCIÓN --}}
 <div class="hidden">
 <div>
 <h2 class="text-xl font-extrabold uppercase tracking-tight text-parrafo flex items-center gap-2">
 <i class="ph-bold ph-person-simple-walk text-boton-acento"></i> Valoración Funcional
 </h2>
 <p class="mt-1 text-xs font-bold text-apoyo">
 Seguimiento de autonomía, nivel de dependencia funcional y riesgo de caída.
 </p>
 </div>
 </div>

 {{-- ═══════════════════════════════════════════════════
 ALERTAS DINÁMICAS
 ════════════════════════════════════════════════════ --}}
 @if($vigente)
 @if($vigente->riesgo_caida === 'ALTO')
 <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
 <i class="ph-bold ph-warning text-xl text-red-500 mt-0.5 shrink-0"></i>
 <div>
 <p class="text-sm font-bold text-red-700">Riesgo de caída alto, requiere revisión.</p>
 <p class="text-xs font-bold text-red-600/70 mt-0.5">Este aviso no constituye diagnóstico médico.</p>
 </div>
 </div>
 @endif

 @if(in_array($vigente->nivel_dependencia, ['ALTA_DEPENDENCIA', 'SUPERVISION_PERMANENTE']))
 <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
 <i class="ph-bold ph-wheelchair text-xl text-estado-advertencia mt-0.5 shrink-0"></i>
 <div>
 <p class="text-sm font-bold text-estado-advertencia">Dependencia funcional alta, requiere seguimiento.</p>
 <p class="text-xs font-bold text-estado-advertencia/70 mt-0.5">Este aviso no constituye diagnóstico médico.</p>
 </div>
 </div>
 @endif

 @if($vigente->indice_barthel !== null && $vigente->indice_barthel < 40)
 <div class="flex items-start gap-3 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-4">
 <i class="ph-bold ph-chart-bar-decreasing text-xl text-orange-500 mt-0.5 shrink-0"></i>
 <div>
 <p class="text-sm font-bold text-orange-700">Índice funcional bajo (Barthel {{ $vigente->indice_barthel }}/100), requiere revisión.</p>
 <p class="text-xs font-bold text-orange-600/70 mt-0.5">Este aviso no constituye diagnóstico médico.</p>
 </div>
 </div>
 @endif
 @endif

 {{-- ═══════════════════════════════════════════════════
 MÉTRICAS SUPERIORES
 ════════════════════════════════════════════════════ --}}
 <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
 <x-ui.metric-card
 etiqueta="Total valoraciones"
 :valor="$totalRegistros"
 icono="ph-clipboard-text"
 color-valor="text-titulo"
 />
 <x-ui.metric-card
 etiqueta="Nivel de dependencia"
 :valor="$vigente ? match($vigente->nivel_dependencia) {
 'INDEPENDIENTE' => 'Independiente',
 'DEPENDENCIA_PARCIAL' => 'Parcial',
 'ALTA_DEPENDENCIA' => 'Alta',
 'SUPERVISION_PERMANENTE' => 'Supervisión',
 default => $vigente->nivel_dependencia,
 } : '—'"
 icono="ph-wheelchair"
 :color-valor="$vigente && in_array($vigente->nivel_dependencia, ['ALTA_DEPENDENCIA','SUPERVISION_PERMANENTE']) ? 'text-red-600' : 'text-titulo'"
 />
 <x-ui.metric-card
 etiqueta="Riesgo de caída"
 :valor="$vigente->riesgo_caida ?? '—'"
 icono="ph-warning"
 :color-valor="$vigente && $vigente->riesgo_caida === 'ALTO' ? 'text-red-600' : ($vigente && $vigente->riesgo_caida === 'MEDIO' ? 'text-estado-advertencia' : 'text-estado-exito')"
 />
 <x-ui.metric-card
 etiqueta="Índice de Barthel"
 :valor="$vigente && $vigente->indice_barthel !== null ? $vigente->indice_barthel . '/100' : '—'"
 icono="ph-chart-bar"
 :color-valor="$vigente && $vigente->indice_barthel !== null && $vigente->indice_barthel < 40 ? 'text-red-600' : 'text-titulo'"
 />
 </div>

 {{-- ═══════════════════════════════════════════════════
 COMPARACIÓN CON VALORACIÓN ANTERIOR
 ════════════════════════════════════════════════════ --}}
 @if($vigente && $anterior)
 <div class="rm-card p-4">
 <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-meta">
 Comparación con valoración anterior ({{ $anterior->fecha_valoracion->format('d/m/Y') }})
 </p>
 <div class="grid grid-cols-3 gap-3 text-center text-xs font-bold">
 <div>
 <span class="block text-meta mb-1">Nivel dependencia</span>
 <span class="block text-titulo">{{ $anterior->nivel_dependencia }}</span>
 <i class="ph-bold ph-arrow-down text-titulo/30 my-1"></i>
 <span class="block font-black text-boton-acento">{{ $vigente->nivel_dependencia }}</span>
 </div>
 <div>
 <span class="block text-meta mb-1">Riesgo caída</span>
 <span class="block text-titulo">{{ $anterior->riesgo_caida ?? '—' }}</span>
 <i class="ph-bold ph-arrow-down text-titulo/30 my-1"></i>
 <span class="block font-black text-boton-acento">{{ $vigente->riesgo_caida ?? '—' }}</span>
 </div>
 <div>
 <span class="block text-meta mb-1">Índice Barthel</span>
 <span class="block text-titulo">{{ $anterior->indice_barthel !== null ? $anterior->indice_barthel : '—' }}</span>
 <i class="ph-bold ph-arrow-down text-titulo/30 my-1"></i>
 <span class="block font-black text-boton-acento">{{ $vigente->indice_barthel !== null ? $vigente->indice_barthel : '—' }}</span>
 </div>
 </div>
 </div>
 @endif

 {{-- ═══════════════════════════════════════════════════
 TABLA DE VALORACIONES
 ════════════════════════════════════════════════════ --}}
 <div class="rm-card p-6">

 {{-- Barra superior: título + botón nueva valoración --}}
 <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
 <h2 class="text-sm font-bold uppercase tracking-wider text-apoyo">
 Historial de valoraciones funcionales
 </h2>
 @can('salud.valoracion.crear')
 <button wire:click="abrirFormNuevo" type="button" class="rm-btn-terracota">
 <i class="ph-bold ph-plus"></i>
 <span>Registrar valoración</span>
 </button>
 @endcan
 </div>

 {{-- Filtros --}}
 <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
 <select wire:model.live="filtroEstado" class="rm-select text-xs">
 <option value="">Todos los estados</option>
 <option value="VIGENTE">Vigente</option>
 <option value="HISTORICA">Histórica</option>
 <option value="ANULADA">Anulada</option>
 </select>
 <select wire:model.live="filtroRiesgo" class="rm-select text-xs">
 <option value="">Todo riesgo</option>
 <option value="BAJO">Bajo</option>
 <option value="MEDIO">Medio</option>
 <option value="ALTO">Alto</option>
 </select>
 <input wire:model.live.debounce.400ms="fechaDesde" type="date" class="rm-input text-xs" placeholder="Desde">
 <input wire:model.live.debounce.400ms="fechaHasta" type="date" class="rm-input text-xs" placeholder="Hasta">
 </div>

 {{-- Tabla --}}
 @if($valoraciones->isEmpty())
 <x-ui.empty-state
 icono="ph-person-simple-walk"
 titulo="Sin valoraciones registradas"
 texto="Registre la primera valoración funcional del paciente para iniciar el seguimiento."
 class="py-12"
 >
 @can('salud.valoracion.crear')
 <button wire:click="abrirFormNuevo" type="button" class="rm-btn-terracota mt-4">
 <i class="ph-bold ph-plus"></i> Registrar valoración
 </button>
 @endcan
 </x-ui.empty-state>
 @else
 <div class="overflow-hidden rounded-2xl border border-borde-suave">
 <table class="rm-table">
 <thead class="rm-table-header">
 <tr>
 <th>Fecha</th>
 <th>Dependencia</th>
 <th>Riesgo caída</th>
 <th>Barthel</th>
 <th>Estado</th>
 <th>Registrado por</th>
 <th>Acciones</th>
 </tr>
 </thead>
 <tbody>
 @foreach($valoraciones as $val)
 <tr class="rm-table-row">
 <td class="rm-table-cell-label">
 {{ $val->fecha_valoracion->format('d/m/Y') }}
 </td>
 <td>
 <span class="text-xs font-bold">
 {{ match($val->nivel_dependencia) {
 'INDEPENDIENTE' => 'Independiente',
 'DEPENDENCIA_PARCIAL' => 'Dependencia parcial',
 'ALTA_DEPENDENCIA' => 'Alta dependencia',
 'SUPERVISION_PERMANENTE' => 'Supervisión permanente',
 default => $val->nivel_dependencia,
 } }}
 </span>
 </td>
 <td>
 @if($val->riesgo_caida)
 <span @class([
 'rm-badge',
 'rm-badge-danger' => $val->riesgo_caida === 'ALTO',
 'rm-badge-warning' => $val->riesgo_caida === 'MEDIO',
 'rm-badge-success' => $val->riesgo_caida === 'BAJO',
 ])>{{ $val->riesgo_caida }}</span>
 @else
 <span class="text-titulo/30 text-xs">—</span>
 @endif
 </td>
 <td class="text-center">
 @if($val->indice_barthel !== null)
 <span class="font-bold text-sm {{ $val->indice_barthel < 40 ? 'text-red-600' : 'text-titulo' }}">
 {{ $val->indice_barthel }}
 </span>
 @else
 <span class="text-titulo/30 text-xs">—</span>
 @endif
 </td>
 <td>
 <x-ui.status-badge :estado="$val->estado" />
 </td>
 <td class="rm-table-cell-meta">
 {{ $val->registradoPor?->name ?? $val->registrado_por ?? '—' }}
 </td>
 <td>
 <div class="flex items-center gap-1.5">
 {{-- Ver detalle --}}
 <button wire:click="abrirDetalle('{{ $val->cod_val_func }}')"
 type="button" class="rm-btn-icon text-apoyo hover:text-titulo"
 title="Ver detalle">
 <i class="ph-bold ph-eye"></i>
 </button>

 @if($val->estado !== 'ANULADA')
 {{-- Editar --}}
 @can('salud.valoracion.editar')
 <button wire:click="abrirFormEditar('{{ $val->cod_val_func }}')"
 type="button" class="rm-btn-icon text-apoyo hover:text-titulo"
 title="Editar">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @endcan

 {{-- Marcar vigente (solo para históricas) --}}
 @if($val->estado === 'HISTORICA')
 @can('salud.valoracion.editar')
 <button wire:click="marcarVigente('{{ $val->cod_val_func }}')"
 type="button" class="rm-btn-icon text-estado-exito hover:text-estado-exito"
 title="Marcar como vigente">
 <i class="ph-bold ph-check-circle"></i>
 </button>
 @endcan
 @endif

 {{-- Anular --}}
 @can('salud.valoracion.anular')
 <button wire:click="abrirAnular('{{ $val->cod_val_func }}')"
 type="button" class="rm-btn-icon text-red-500 hover:text-red-700"
 title="Anular">
 <i class="ph-bold ph-x-circle"></i>
 </button>
 @endcan
 @else
 {{-- Restaurar (anuladas) --}}
 @can('salud.valoracion.editar')
 <button wire:click="restaurar('{{ $val->cod_val_func }}')"
 type="button" class="rm-btn-icon text-estado-exito hover:text-estado-exito"
 title="Restaurar valoración">
 <i class="ph-bold ph-arrow-counter-clockwise"></i>
 </button>
 @endcan
 @endif
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>

 {{-- Paginación --}}
 @if($valoraciones->hasPages())
 <div class="mt-4">
 {{ $valoraciones->links() }}
 </div>
 @endif
 @endif
 </div>

 {{-- /container --}}

 {{-- ═══════════════════════════════════════════════════════════════════════
 MODAL CREAR / EDITAR
 ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalFormOpen)
 <div class="rm-modal-overlay" wire:click.self="cerrarModales">
 <div class="rm-modal-panel max-w-2xl w-full max-h-[90vh] overflow-y-auto">

 {{-- Cabecera --}}
 <div class="rm-modal-header">
 <h3 class="rm-modal-title">
 <i class="ph-bold ph-person-simple-walk mr-2"></i>
 {{ $editandoId ? 'Editar valoración funcional' : 'Registrar nueva valoración funcional' }}
 </h3>
 <button wire:click="cerrarModales" type="button" class="rm-btn-icon">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>

 {{-- Cuerpo --}}
 <div class="rm-modal-body space-y-6">

 {{-- Fecha y clasificación --}}
 <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
 <div>
 <label class="rm-label">Fecha de valoración <span class="text-red-500">*</span></label>
 <input wire:model="fecha_valoracion" type="date" class="rm-input">
 @error('fecha_valoracion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="rm-label">Nivel de dependencia <span class="text-red-500">*</span></label>
 <select wire:model="nivel_dependencia" class="rm-select">
 <option value="">Seleccionar…</option>
 <option value="INDEPENDIENTE">Independiente</option>
 <option value="DEPENDENCIA_PARCIAL">Dependencia parcial</option>
 <option value="ALTA_DEPENDENCIA">Alta dependencia</option>
 <option value="SUPERVISION_PERMANENTE">Supervisión permanente</option>
 </select>
 @error('nivel_dependencia') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="rm-label">Riesgo de caída <span class="text-red-500">*</span></label>
 <select wire:model="riesgo_caida" class="rm-select">
 <option value="">Seleccionar…</option>
 <option value="BAJO">Bajo</option>
 <option value="MEDIO">Medio</option>
 <option value="ALTO">Alto</option>
 </select>
 @error('riesgo_caida') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 </div>

 {{-- Índice Barthel --}}
 <div class="grid grid-cols-2 gap-4">
 <div>
 <label class="rm-label">Índice de Barthel (0–100)</label>
 <input wire:model="indice_barthel" type="number" min="0" max="100" class="rm-input"
 placeholder="Ej: 75">
 <p class="mt-1 text-[10px] text-meta">0–20 total · 21–60 severa · 61–90 moderada · 91–99 leve · 100 independiente</p>
 @error('indice_barthel') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 </div>

 {{-- AVD (Actividades de la Vida Diaria) --}}
 <div>
 <p class="rm-label-soft mb-3">Actividades de la vida diaria (AVD)</p>
 <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
 @foreach([
 'come_solo' => 'Come solo',
 'se_bana_solo' => 'Se baña solo',
 'se_viste_solo' => 'Se viste solo',
 'va_bano_solo' => 'Va al baño solo',
 'camina_solo' => 'Camina solo',
 ] as $campo => $etiqueta)
 <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-borde-suave px-3 py-2.5 transition hover:border-terracota/40 hover:bg-fondo-panel">
 <input wire:model="{{ $campo }}" type="checkbox" class="h-4 w-4 rounded border-borde text-boton-acento focus:ring-borde-focus">
 <span class="text-xs font-bold text-titulo">{{ $etiqueta }}</span>
 </label>
 @endforeach
 </div>
 </div>

 {{-- Dispositivos de asistencia --}}
 <div>
 <p class="rm-label-soft mb-3">Dispositivos de asistencia</p>
 <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
 @foreach([
 'usa_baston' => 'Usa bastón',
 'usa_andador' => 'Usa andador',
 'usa_silla_ruedas' => 'Usa silla de ruedas',
 ] as $campo => $etiqueta)
 <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-borde-suave px-3 py-2.5 transition hover:border-terracota/40 hover:bg-fondo-panel">
 <input wire:model="{{ $campo }}" type="checkbox" class="h-4 w-4 rounded border-borde text-boton-acento focus:ring-borde-focus">
 <span class="text-xs font-bold text-titulo">{{ $etiqueta }}</span>
 </label>
 @endforeach
 </div>
 </div>

 {{-- Capacidades sensoriales y conductuales --}}
 <div>
 <p class="rm-label-soft mb-3">Capacidades sensoriales y conductuales</p>
 <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
 @foreach([
 'baja_vision' => 'Baja visión',
 'baja_audicion' => 'Baja audición',
 'dificultad_hablar' => 'Dificultad para hablar',
 'molestia_luz' => 'Molestia ante la luz',
 'molestia_ruido' => 'Molestia ante el ruido',
 'se_asusta_facil' => 'Se asusta fácilmente',
 'necesita_supervision' => 'Necesita supervisión',
 ] as $campo => $etiqueta)
 <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-borde-suave px-3 py-2.5 transition hover:border-terracota/40 hover:bg-fondo-panel">
 <input wire:model="{{ $campo }}" type="checkbox" class="h-4 w-4 rounded border-borde text-boton-acento focus:ring-borde-focus">
 <span class="text-xs font-bold text-titulo">{{ $etiqueta }}</span>
 </label>
 @endforeach
 </div>
 </div>

 {{-- Observación --}}
 <div>
 <label class="rm-label">Observaciones</label>
 <textarea wire:model="observacion" rows="3" class="rm-textarea"
 placeholder="Observaciones adicionales del evaluador…"></textarea>
 @error('observacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>

 </div>{{-- /modal-body --}}

 {{-- Footer --}}
 <div class="rm-modal-footer">
 <button wire:click="cerrarModales" type="button" class="rm-btn-ghost">
 Cancelar
 </button>
 <button wire:click="guardar" wire:loading.attr="disabled" type="button" class="rm-btn-terracota">
 <span wire:loading.remove wire:target="guardar">
 <i class="ph-bold ph-floppy-disk mr-1"></i>
 {{ $editandoId ? 'Guardar cambios' : 'Registrar valoración' }}
 </span>
 <span wire:loading wire:target="guardar" class="flex items-center gap-2">
 <i class="ph-bold ph-circle-notch animate-spin"></i> Guardando…
 </span>
 </button>
 </div>

 </div>
 </div>
 @endif


 {{-- ═══════════════════════════════════════════════════════════════════════
 MODAL VER DETALLE
 ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalDetalleOpen && $viendoDetalle)
 <div class="rm-modal-overlay" wire:click.self="cerrarModales">
 <div class="rm-modal-panel max-w-xl w-full max-h-[90vh] overflow-y-auto">

 <div class="rm-modal-header">
 <h3 class="rm-modal-title">
 <i class="ph-bold ph-eye mr-2"></i>
 Detalle — Valoración {{ $viendoDetalle->fecha_valoracion->format('d/m/Y') }}
 </h3>
 <button wire:click="cerrarModales" type="button" class="rm-btn-icon">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>

 <div class="rm-modal-body space-y-5">

 {{-- Resumen clínico --}}
 <div class="grid grid-cols-3 gap-3 text-center">
 <div class="rounded-xl bg-fondo-panel px-3 py-4">
 <p class="text-[10px] font-bold uppercase tracking-widest text-meta">Dependencia</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ match($viendoDetalle->nivel_dependencia) {
 'INDEPENDIENTE' => 'Independiente',
 'DEPENDENCIA_PARCIAL' => 'Parcial',
 'ALTA_DEPENDENCIA' => 'Alta',
 'SUPERVISION_PERMANENTE' => 'Supervisión',
 default => $viendoDetalle->nivel_dependencia,
 } }}
 </p>
 </div>
 <div class="rounded-xl bg-fondo-panel px-3 py-4">
 <p class="text-[10px] font-bold uppercase tracking-widest text-meta">Riesgo caída</p>
 @if($viendoDetalle->riesgo_caida)
 <span @class([
 'rm-badge mt-1 inline-block',
 'rm-badge-danger' => $viendoDetalle->riesgo_caida === 'ALTO',
 'rm-badge-warning' => $viendoDetalle->riesgo_caida === 'MEDIO',
 'rm-badge-success' => $viendoDetalle->riesgo_caida === 'BAJO',
 ])>{{ $viendoDetalle->riesgo_caida }}</span>
 @else
 <p class="mt-1 text-sm font-bold text-meta">—</p>
 @endif
 </div>
 <div class="rounded-xl bg-fondo-panel px-3 py-4">
 <p class="text-[10px] font-bold uppercase tracking-widest text-meta">Barthel</p>
 <p class="mt-1 text-sm font-bold text-titulo">
 {{ $viendoDetalle->indice_barthel !== null ? $viendoDetalle->indice_barthel . '/100' : '—' }}
 </p>
 </div>
 </div>

 {{-- AVD --}}
 <div>
 <p class="rm-label-soft mb-2">Actividades de la vida diaria</p>
 <div class="flex flex-wrap gap-2">
 @foreach([
 'come_solo' => 'Come solo', 'se_bana_solo' => 'Se baña solo',
 'se_viste_solo' => 'Se viste solo', 'va_bano_solo' => 'Va al baño solo',
 'camina_solo' => 'Camina solo',
 ] as $campo => $etiqueta)
 <span @class([
 'rm-badge',
 'rm-badge-success' => $viendoDetalle->$campo,
 'rm-badge-neutral' => !$viendoDetalle->$campo,
 ])>
 <i @class(['ph-bold mr-1', 'ph-check' => $viendoDetalle->$campo, 'ph-x' => !$viendoDetalle->$campo])></i>
 {{ $etiqueta }}
 </span>
 @endforeach
 </div>
 </div>

 {{-- Dispositivos --}}
 <div>
 <p class="rm-label-soft mb-2">Dispositivos de asistencia</p>
 <div class="flex flex-wrap gap-2">
 @foreach([
 'usa_baston' => 'Bastón', 'usa_andador' => 'Andador', 'usa_silla_ruedas' => 'Silla de ruedas',
 ] as $campo => $etiqueta)
 @if($viendoDetalle->$campo)
 <span class="rm-badge rm-badge-info">{{ $etiqueta }}</span>
 @endif
 @endforeach
 @if(!$viendoDetalle->usa_baston && !$viendoDetalle->usa_andador && !$viendoDetalle->usa_silla_ruedas)
 <span class="text-xs font-bold text-meta">Ninguno</span>
 @endif
 </div>
 </div>

 {{-- Sensoriales --}}
 <div>
 <p class="rm-label-soft mb-2">Capacidades sensoriales y conductuales</p>
 <div class="flex flex-wrap gap-2">
 @foreach([
 'baja_vision' => 'Baja visión', 'baja_audicion' => 'Baja audición',
 'dificultad_hablar' => 'Dificultad al hablar', 'molestia_luz' => 'Molestia luz',
 'molestia_ruido' => 'Molestia ruido', 'se_asusta_facil' => 'Se asusta fácil',
 'necesita_supervision' => 'Necesita supervisión',
 ] as $campo => $etiqueta)
 @if($viendoDetalle->$campo)
 <span class="rm-badge rm-badge-warning">{{ $etiqueta }}</span>
 @endif
 @endforeach
 </div>
 </div>

 {{-- Observación --}}
 @if($viendoDetalle->observacion)
 <div>
 <p class="rm-label-soft mb-1">Observaciones</p>
 <p class="text-sm text-titulo">{{ $viendoDetalle->observacion }}</p>
 </div>
 @endif

 {{-- Estado y registro --}}
 <div class="flex flex-wrap gap-4 border-t border-borde-suave pt-4 text-xs font-bold text-apoyo">
 <span><x-ui.status-badge :estado="$viendoDetalle->estado" /></span>
 <span>Registrado por: {{ $viendoDetalle->registradoPor?->name ?? $viendoDetalle->registrado_por ?? '—' }}</span>
 @if($viendoDetalle->estado === 'ANULADA')
 <span class="text-red-600">
 Anulado: {{ $viendoDetalle->fecha_anulacion?->format('d/m/Y H:i') }}
 por {{ $viendoDetalle->anuladoPor?->name ?? $viendoDetalle->anulado_por ?? '—' }}
 </span>
 @if($viendoDetalle->motivo_anulacion)
 <span class="w-full text-red-600">Motivo: {{ $viendoDetalle->motivo_anulacion }}</span>
 @endif
 @endif
 </div>

 </div>{{-- /modal-body --}}

 <div class="rm-modal-footer">
 <button wire:click="cerrarModales" type="button" class="rm-btn-ghost">Cerrar</button>
 </div>

 </div>
 </div>
 @endif


 {{-- ═══════════════════════════════════════════════════════════════════════
 MODAL ANULAR
 ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalAnularOpen)
 <div class="rm-modal-overlay" wire:click.self="cerrarModales">
 <div class="rm-modal-panel max-w-md w-full">

 <div class="rm-modal-header">
 <h3 class="rm-modal-title text-red-600">
 <i class="ph-bold ph-x-circle mr-2"></i>
 Anular valoración
 </h3>
 <button wire:click="cerrarModales" type="button" class="rm-btn-icon">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>

 <div class="rm-modal-body">
 <p class="mb-4 text-sm font-bold text-apoyo">
 Esta acción anulará la valoración funcional. No se eliminan datos. La valoración quedará registrada en el historial como anulada.
 </p>
 <div>
 <label class="rm-label">Motivo de anulación <span class="text-red-500">*</span></label>
 <textarea wire:model="motivo_anulacion" rows="4" class="rm-textarea"
 placeholder="Describa el motivo (mínimo 10 caracteres)…"></textarea>
 @error('motivo_anulacion') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
 </div>
 </div>

 <div class="rm-modal-footer">
 <button wire:click="cerrarModales" type="button" class="rm-btn-ghost">Cancelar</button>
 <button wire:click="confirmarAnular" wire:loading.attr="disabled" type="button" class="rm-btn-danger">
 <span wire:loading.remove wire:target="confirmarAnular">
 <i class="ph-bold ph-x-circle mr-1"></i> Confirmar anulación
 </span>
 <span wire:loading wire:target="confirmarAnular" class="flex items-center gap-2">
 <i class="ph-bold ph-circle-notch animate-spin"></i> Procesando…
 </span>
 </button>
 </div>

 </div>
 </div>
 @endif

</div>
