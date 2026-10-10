@php
 $inputCls = 'w-full rounded-xl border border-borde-suave bg-fondo-app px-3 py-2.5 text-sm font-bold text-titulo outline-none ring-[var(--rm-accent-terracotta)]/25 transition focus:border-borde-focus focus:ring-2';
 $labelCls = 'block text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo mb-1.5';
 $errCls = 'mt-1 text-[10px] font-bold text-boton-acento';

 $resolverColorIcono = function(string $tipo): array {
 $t = strtolower($tipo);
 $mapa = [
 'terapia' => ['cls' => 'bg-fondo-panel text-parrafo', 'icon' => 'ph-hand-heart', 'linea' => 'bg-fondo-panel'],
 'estimulaci'=> ['cls' => 'bg-fondo-panel text-parrafo', 'icon' => 'ph-brain', 'linea' => 'bg-fondo-panel'],
 'gimnas' => ['cls' => 'bg-estado-exitoBg text-estado-exito', 'icon' => 'ph-person-simple-walk', 'linea' => 'bg-estado-exitoBg'],
 'musico' => ['cls' => 'bg-fondo-panel text-titulo', 'icon' => 'ph-music-notes', 'linea' => 'bg-boton-principal'],
 'ludot' => ['cls' => 'bg-estado-advertenciaBg text-estado-advertencia', 'icon' => 'ph-game-controller', 'linea' => 'bg-estado-advertenciaBg'],
 'integ' => ['cls' => 'bg-estado-peligroBg text-parrafo', 'icon' => 'ph-users-three', 'linea' => 'bg-boton-acento'],
 'recrea' => ['cls' => 'bg-estado-peligroBg text-boton-acento', 'icon' => 'ph-smiley', 'linea' => 'bg-boton-acento'],
 'cultur' => ['cls' => 'bg-estado-advertenciaBg text-estado-advertencia', 'icon' => 'ph-palette', 'linea' => 'bg-estado-advertenciaBg'],
 'artis' => ['cls' => 'bg-estado-advertenciaBg text-estado-advertencia', 'icon' => 'ph-paint-brush', 'linea' => 'bg-estado-advertenciaBg'],
 'cogni' => ['cls' => 'bg-fondo-panel text-parrafo', 'icon' => 'ph-lightbulb', 'linea' => 'bg-fondo-panel'],
 'social' => ['cls' => 'bg-estado-peligroBg text-parrafo', 'icon' => 'ph-users', 'linea' => 'bg-boton-acento'],
 'manual' => ['cls' => 'bg-fondo-panel text-meta', 'icon' => 'ph-scissors', 'linea' => 'bg-fondo-panel'],
 'fis' => ['cls' => 'bg-estado-exitoBg text-estado-exito', 'icon' => 'ph-heartbeat', 'linea' => 'bg-estado-exitoBg'],
 'danza' => ['cls' => 'bg-estado-peligroBg text-boton-acento', 'icon' => 'ph-person-simple-run', 'linea' => 'bg-boton-acento'],
 'espiritu' => ['cls' => 'bg-fondo-panel text-parrafo', 'icon' => 'ph-flower-lotus', 'linea' => 'bg-fondo-panel'],
 ];
 foreach ($mapa as $clave => $vals) {
 if (str_contains($t, $clave)) return $vals;
 }
 $paletas = [
 ['cls' => 'bg-estado-peligroBg text-boton-acento', 'icon' => 'ph-star', 'linea' => 'bg-boton-acento'],
 ['cls' => 'bg-estado-exitoBg text-estado-exito', 'icon' => 'ph-leaf', 'linea' => 'bg-estado-exitoBg'],
 ['cls' => 'bg-fondo-panel text-titulo', 'icon' => 'ph-circles-four','linea' => 'bg-boton-principal'],
 ['cls' => 'bg-estado-advertenciaBg text-estado-advertencia', 'icon' => 'ph-sun', 'linea' => 'bg-estado-advertenciaBg'],
 ['cls' => 'bg-fondo-panel text-parrafo', 'icon' => 'ph-sparkle', 'linea' => 'bg-fondo-panel'],
 ['cls' => 'bg-fondo-panel text-meta', 'icon' => 'ph-tag', 'linea' => 'bg-fondo-panel'],
 ];
 return $paletas[abs(crc32($tipo)) % count($paletas)];
 };
@endphp

<div class="min-h-screen bg-transparent px-4 py-5 text-titulo sm:px-6 lg:px-8"
 x-data
 @keydown.window.escape="$wire.cerrarModales()">
 <div class="mx-auto max-w-7xl space-y-6">

 {{-- ── CABECERA ─────────────────────────────────────────────────────── --}}
 <x-ui.collection-header title="Tipos de actividades" subtitle="Catálogo para clasificar actividades recreativas, cognitivas, físicas, sociales y culturales." icon="ph-tag" eyebrow="Catálogo institucional">
 <x-slot:actions>
 <a href="{{ route('admin.actividades.index') }}"
 class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-4 py-2.5 text-xs font-bold text-apoyo transition hover:border-borde-fuerte hover:text-titulo">
 <i class="ph-bold ph-arrow-left text-sm"></i>
 Actividades
 </a>
 <button wire:click="$refresh"
 class="inline-flex items-center justify-center gap-2 rounded-xl border border-borde-suave bg-fondo-app px-4 py-2.5 text-xs font-bold text-apoyo transition hover:border-borde-fuerte hover:text-titulo">
 <i class="ph-bold ph-arrows-clockwise text-sm" wire:loading.class="animate-spin" wire:target="$refresh"></i>
 Actualizar
 </button>
 @can('actividades.gestionar')
 <button wire:click="abrirRegistrar"
 class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-acento px-4 py-2.5 text-xs font-bold uppercase tracking-[0.12em] text-inverso shadow-sm transition hover:bg-fondo-panel hover:shadow-md active:scale-95">
 <i class="ph-bold ph-plus text-sm"></i>
 Registrar tipo
 </button>
 @endcan
 </x-slot:actions>
 </x-ui.collection-header>

 {{-- ── 6 MÉTRICAS ──────────────────────────────────────────────────── --}}
 <section class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
 <x-ui.metric-card icon="ph-tag" :value="number_format($stats['total'])" label="Total en catálogo" variant="neutral" />
 <x-ui.metric-card icon="ph-check-circle" :value="number_format($stats['con_actividades'])" label="Tipos con actividades" variant="mint" />
 <x-ui.metric-card icon="ph-minus-circle" :value="number_format($stats['sin_actividades'])" label="Sin actividades" variant="neutral" />
 <x-ui.metric-card icon="ph-calendar-check" :value="number_format($stats['total_actividades'])" label="Actividades clasificadas" variant="sky" />
 <x-ui.metric-card icon="ph-trophy" :value="number_format($stats['mas_count'])" label="Tipo más utilizado" :description="$stats['mas_nombre']" variant="neutral" />
 <x-ui.metric-card icon="ph-chart-bar" :value="$stats['promedio']" label="Promedio por tipo" description="Actividades por tipo" variant="neutral" />
 </section>

     {{-- ── FILTROS Y BÚSQUEDA ───────────────────────────────────────────── --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Búsqueda textual --}}
            <div class="lg:col-span-8">
                <label for="tipos-actividad-buscar" class="rm-collection-filter-label">Buscar tipo de actividad</label>
                <div class="relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input id="tipos-actividad-buscar" wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar tipo o descripción..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]" />
                @if(!empty($search))
                    <button type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
                </div>
            </div>

            {{-- Filtro Uso --}}
            <div class="lg:col-span-4">
                <label for="tipos-actividad-uso" class="rm-collection-filter-label">Uso</label>
                <select id="tipos-actividad-uso" wire:model.live="filtroUso"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="">Todos los usos</option>
                    <option value="con">Con actividades</option>
                    <option value="sin">Sin actividades</option>
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($search) || !empty($filtroUso);
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($search, 16) }}"</span>
                            <button type="button" wire:click="$set('search', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if(!empty($filtroUso))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Uso: {{ $filtroUso === 'con' ? 'Con actividades' : 'Sin actividades' }}</span>
                            <button type="button" wire:click="$set('filtroUso', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="limpiarFiltros" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 {{-- ── TABLA PRINCIPAL ──────────────────────────────────────────────── --}}
 <x-ui.collection-results title="Catálogo de tipos" :count="$tipos->total()" label="tipos">
 <div class="p-5">
 @if($tipos->isEmpty())
        <div class="py-4">
            <x-ui.empty-state
                compact
                icono="ph-tag"
                titulo="Sin tipos de actividad encontrados"
                :texto="$search || $filtroUso ? 'No se encontraron tipos con los filtros seleccionados.' : 'No hay tipos de actividades registrados en el catálogo.'"
                :actionMethod="$search || $filtroUso ? 'limpiarFiltros' : null"
                actionText="Restablecer filtros"
            />
        </div>
        @else
        <div class="overflow-x-auto"
            wire:loading.class="opacity-50 transition-opacity"
            wire:target="search,filtroUso">
            <table class="rm-data-table rm-data-table--actions rm-table w-full min-w-[640px] text-xs">
                <thead>
                    <tr class="border-b border-borde-suave">
                        <th class="w-10 pb-2.5"></th>
                        <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Tipo de actividad</th>
                        <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Descripción</th>
                        <th class="pb-2.5 text-center font-black uppercase tracking-[0.12em] text-apoyo">Actividades</th>
                        <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Estado</th>
                        <th class="pb-2.5 text-left font-black uppercase tracking-[0.12em] text-apoyo">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)]/30">
        @foreach($tipos as $tipo)
 @php($vi = $resolverColorIcono($tipo->tipo))
 <tr wire:key="tipo-{{ $tipo->cod_tipo_act }}" class="group transition hover:bg-fondo-panel">
 {{-- Ícono visual --}}
 <td class="py-3 pr-3">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $vi['cls'] }}">
 <i class="ph-bold {{ $vi['icon'] }} text-base"></i>
 </span>
 </td>
 {{-- Nombre --}}
 <td class="py-3 pr-4">
 <span class="font-black text-titulo">{{ $tipo->tipo }}</span>
 </td>
 {{-- Descripción --}}
 <td class="py-3 pr-4 max-w-[240px]">
 @if($tipo->descripcion)
 <span class="line-clamp-2 text-apoyo font-bold">{{ $tipo->descripcion }}</span>
 @else
 <span class="text-apoyo italic">Sin descripción</span>
 @endif
 </td>
 {{-- Conteo --}}
 <td class="py-3 pr-4 text-center">
 @if($tipo->actividades_count > 0)
 <span class="inline-flex items-center rounded-full border border-estado-exitoBorde bg-estado-exitoBg px-2.5 py-0.5 text-[10px] font-bold text-estado-exito">
 {{ number_format($tipo->actividades_count) }}
 </span>
 @else
 <span class="inline-flex items-center rounded-full border border-borde-suave bg-fondo-panel px-2.5 py-0.5 text-[10px] font-bold text-meta">
 0
 </span>
 @endif
 </td>
 {{-- Estado --}}
 <td class="py-3 pr-4">
 <x-ui.status-badge estado="ACTIVO" />
 </td>
 {{-- Acciones --}}
 <td class="py-3">
 <div class="flex items-center gap-1.5">
 {{-- Ver detalle --}}
 <button wire:click="abrirDetalle('{{ $tipo->cod_tipo_act }}')"
 title="Ver detalle"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-fuerte bg-fondo-panel text-apoyo transition hover:border-borde-fuerte hover:bg-fondo-panel">
 <i class="ph-bold ph-eye text-xs"></i>
 </button>
 {{-- Editar --}}
 @can('actividades.gestionar')
 <button wire:click="abrirEditar('{{ $tipo->cod_tipo_act }}')"
 title="Editar"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-estado-advertenciaBorde bg-estado-advertenciaBg text-estado-advertencia transition hover:border-estado-advertenciaBorde hover:bg-estado-advertenciaBg">
 <i class="ph-bold ph-pencil text-xs"></i>
 </button>
 @endcan
 {{-- Eliminar --}}
 @can('actividades.gestionar')
 @if($tipo->actividades_count === 0)
 <button type="button"
 title="Eliminar tipo"
 x-data
 @click="
 window.SwalAmandita.fire({
 title: '¿Eliminar tipo?',
 text: 'Se eliminará del catálogo institucional. Esta acción no se puede deshacer.',
 icon: 'warning',
 showCancelButton: true,
 confirmButtonText: 'Sí, eliminar',
 cancelButtonText: 'Cancelar'
 }).then(r => { if (r.isConfirmed) $wire.eliminarTipo({{ $tipo->cod_tipo_act }}) })"
 class="flex h-7 w-7 items-center justify-center rounded-lg border border-borde-focus bg-estado-peligroBg text-boton-acento transition hover:border-borde-focus hover:bg-estado-peligroBg">
 <i class="ph-bold ph-trash text-xs"></i>
 </button>
 @else
 <button type="button"
 title="No se puede eliminar: tiene actividades asociadas"
 x-data
 @click="
 window.SwalAmandita.fire({
 icon: 'warning',
 title: 'No es posible eliminar',
 text: 'Este tipo tiene {{ $tipo->actividades_count }} actividad(es) asociada(s). Para eliminarlo, primero gestione las actividades vinculadas.',
 })"
 class="flex h-7 w-7 cursor-not-allowed items-center justify-center rounded-lg border border-borde-suave bg-fondo-panel text-meta transition hover:border-borde-suave">
 <i class="ph-bold ph-trash text-xs"></i>
 </button>
 @endif
 @endcan
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @if($tipos->hasPages())
 <div class="mt-5 border-t border-borde-suave pt-4">
 {{ $tipos->links() }}
 </div>
 @endif
 @endif
 </div>
 </x-ui.collection-results>

 {{-- ── NOTA INSTITUCIONAL ───────────────────────────────────────────── --}}
 <div class="flex items-start gap-3 rounded-2xl border border-borde-suave bg-fondo-panel p-4">
 <i class="ph-bold ph-info mt-0.5 shrink-0 text-lg text-apoyo"></i>
 <p class="text-xs font-bold leading-relaxed text-apoyo">
 El catálogo no dispone de campo de estado en base de datos — todos los tipos se muestran como activos. Los colores e íconos son visuales calculados y no se almacenan. Para desactivar un tipo, elimínelo solo si no tiene actividades asociadas.
 </p>
 </div>

 </div>

 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL — REGISTRAR TIPO --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalRegistrar)
 <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-10"
 class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8 bg-[var(--rm-modal-backdrop,rgba(47,62,92,0.6))] backdrop-blur-sm"
 wire:click.self="cerrarModales">
 <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
 <div class="h-1 bg-gradient-to-r from-[var(--rm-accent-terracotta)] via-[var(--rm-warning)] to-[var(--rm-action-primary)]"></div>
 <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex items-center gap-2.5">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-tag-simple text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Catálogo</p>
 <h3 class="text-sm font-bold text-titulo">Registrar tipo de actividad</h3>
 </div>
 </div>
 <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>
 <form wire:submit.prevent="guardarTipo" class="p-5 space-y-4">
 <div>
 <label class="{{ $labelCls }}">Nombre del tipo <span class="text-boton-acento">*</span></label>
 <input wire:model="tipo" type="text" maxlength="50"
 placeholder="Ej: Terapia Ocupacional"
 class="{{ $inputCls }}" />
 @error('tipo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 <p class="mt-1 text-[10px] font-bold text-apoyo">Máximo 50 caracteres. Debe ser único en el catálogo.</p>
 </div>
 <div>
 <label class="{{ $labelCls }}">Descripción <span class="text-apoyo normal-case tracking-normal">(opcional)</span></label>
 <textarea wire:model="descripcion" rows="3" maxlength="1000"
 placeholder="Descripción breve del tipo de actividad..."
 class="{{ $inputCls }} resize-none"></textarea>
 @error('descripcion') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
 <button type="button" wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo transition hover:border-borde-suave hover:text-titulo">
 Cancelar
 </button>
 <button type="submit"
 class="inline-flex items-center gap-2 rounded-xl bg-boton-acento px-5 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-floppy-disk text-sm"></i>
 Registrar tipo
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif

 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL — EDITAR TIPO --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalEditar)
 <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-10"
 class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8 bg-[var(--rm-modal-backdrop,rgba(47,62,92,0.6))] backdrop-blur-sm"
 wire:click.self="cerrarModales">
 <div class="w-full max-w-lg overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
 <div class="h-1 bg-gradient-to-r from-[var(--rm-warning)] via-[var(--rm-accent-terracotta)] to-[var(--rm-action-primary)]"></div>
 <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex items-center gap-2.5">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
 <i class="ph-bold ph-pencil-simple text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Catálogo</p>
 <h3 class="text-sm font-bold text-titulo">Editar tipo #{{ $editandoId }}</h3>
 </div>
 </div>
 <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>
 <form wire:submit.prevent="actualizarTipo" class="p-5 space-y-4">
 <div>
 <label class="{{ $labelCls }}">Nombre del tipo <span class="text-boton-acento">*</span></label>
 <input wire:model="tipo" type="text" maxlength="50"
 class="{{ $inputCls }}" />
 @error('tipo') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 <p class="mt-1 text-[10px] font-bold text-apoyo">La validación ignora el nombre actual del tipo (permite actualizar sin conflicto).</p>
 </div>
 <div>
 <label class="{{ $labelCls }}">Descripción <span class="text-apoyo normal-case tracking-normal">(opcional)</span></label>
 <textarea wire:model="descripcion" rows="3" maxlength="1000"
 class="{{ $inputCls }} resize-none"></textarea>
 @error('descripcion') <p class="{{ $errCls }}">{{ $message }}</p> @enderror
 </div>
 <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
 <button type="button" wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo transition hover:border-borde-suave hover:text-titulo">
 Cancelar
 </button>
 <button type="submit"
 class="inline-flex items-center gap-2 rounded-xl bg-estado-advertenciaBg px-5 py-2 text-xs font-bold text-inverso shadow-sm transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-floppy-disk text-sm"></i>
 Guardar cambios
 </button>
 </div>
 </form>
 </div>
 </div>
 @endif

 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 {{-- MODAL — DETALLE --}}
 {{-- ════════════════════════════════════════════════════════════════════════ --}}
 @if($modalDetalle && $detalle)
 @php($vi = $resolverColorIcono($detalle->tipo))
 <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-10"
 class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto px-4 py-8 bg-[var(--rm-modal-backdrop,rgba(47,62,92,0.6))] backdrop-blur-sm"
 wire:click.self="cerrarModales">
 <div class="w-full max-w-xl overflow-hidden rounded-[1.45rem] border border-borde-suave bg-fondo-panel shadow-2xl">
 <div class="h-1 bg-gradient-to-r from-[var(--rm-action-primary)] via-[var(--rm-warning)] to-[var(--rm-accent-terracotta)]"></div>
 <div class="flex items-center justify-between border-b border-borde-suave bg-fondo-panel px-5 py-4">
 <div class="flex items-center gap-2.5">
 <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $vi['cls'] }}">
 <i class="ph-bold {{ $vi['icon'] }} text-lg"></i>
 </span>
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-apoyo">Catálogo</p>
 <h3 class="text-sm font-bold text-titulo">Detalle del tipo</h3>
 </div>
 </div>
 <button wire:click="cerrarModales" class="flex h-8 w-8 items-center justify-center rounded-xl border border-borde-suave text-apoyo transition hover:border-borde-focus hover:text-boton-acento">
 <i class="ph-bold ph-x text-sm"></i>
 </button>
 </div>
 <div class="p-5 space-y-4">
 {{-- Nombre + estado --}}
 <div class="flex items-start justify-between gap-4">
 <div>
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Tipo de actividad</p>
 <h4 class="mt-1 text-lg font-extrabold text-titulo">{{ $detalle->tipo }}</h4>
 </div>
 <div class="flex shrink-0 flex-col items-end gap-1.5">
 <x-ui.status-badge estado="ACTIVO" />
 <span class="text-[10px] font-bold text-apoyo">Catálogo institucional</span>
 </div>
 </div>
 {{-- Descripción --}}
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Descripción</p>
 <p class="mt-1 text-xs font-bold leading-relaxed text-apoyo">
 {{ $detalle->descripcion ?: 'Sin descripción registrada.' }}
 </p>
 </div>
 {{-- Métricas rápidas --}}
 <div class="grid grid-cols-2 gap-3">
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Actividades asociadas</p>
 <p class="mt-1 text-2xl font-black text-titulo">{{ number_format($detalle->actividades_count) }}</p>
 </div>
 <div class="rounded-xl border border-borde-suave bg-fondo-panel px-4 py-3 text-center">
 <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">Icono representativo</p>
 <div class="mt-2 flex justify-center">
 <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $vi['cls'] }}">
 <i class="ph-bold {{ $vi['icon'] }} text-xl"></i>
 </span>
 </div>
 </div>
 </div>
 {{-- Últimas actividades --}}
 @if($detalle->actividades->isNotEmpty())
 <div>
 <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.13em] text-apoyo">
 Últimas {{ $detalle->actividades->count() }} actividad(es) registradas
 </p>
 <div class="space-y-1.5">
 @foreach($detalle->actividades as $act)
 @php($ne = \App\Models\Actividad::normalizarEstado($act->estado ?? ''))
 <div class="flex items-center justify-between gap-3 rounded-xl border border-borde-suave bg-fondo-panel px-3 py-2">
 <div class="min-w-0 flex-1">
 <p class="truncate text-[10px] font-bold text-titulo">
 {{ optional($act->adultoMayor)->ap_paterno ?? '—' }}
 {{ optional($act->adultoMayor)->nombres ?? '' }}
 </p>
 <p class="text-[9px] font-bold text-apoyo">
 {{ \Carbon\Carbon::parse($act->fecha)->format('d/m/Y') }}
 @if($act->hora) — {{ substr($act->hora, 0, 5) }} @endif
 </p>
 </div>
 <span class="shrink-0 inline-flex items-center rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-wide {{ $ne['clase'] }}">
 {{ $ne['etiqueta'] }}
 </span>
 </div>
 @endforeach
 </div>
 </div>
 @endif
 {{-- Botones --}}
 <div class="flex justify-end gap-2.5 border-t border-borde-suave pt-4">
 @can('actividades.gestionar')
 <button type="button" wire:click="abrirEditar('{{ $detalle->cod_tipo_act }}')"
 class="inline-flex items-center gap-1.5 rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg px-4 py-2 text-xs font-bold text-estado-advertencia transition hover:bg-estado-advertenciaBg">
 <i class="ph-bold ph-pencil text-xs"></i>
 Editar
 </button>
 @endcan
 <button type="button" wire:click="cerrarModales"
 class="rounded-xl border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo transition hover:border-borde-suave hover:text-titulo">
 Cerrar
 </button>
 </div>
 </div>
 </div>
 </div>
 @endif

</div>
