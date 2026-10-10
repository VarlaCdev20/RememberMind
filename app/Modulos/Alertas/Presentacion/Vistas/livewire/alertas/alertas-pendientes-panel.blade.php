<div class="relative mx-auto max-w-7xl space-y-6 py-6 px-4 sm:px-6 lg:px-8">
 {{-- ENCABEZADO --}}
 <x-ui.page-header
  title="Alertas y Pendientes"
  subtitle="Seguimiento institucional de registros incompletos, alertas activas y acciones pendientes del módulo de residentes."
  overline="Centro Geriátrico Jardín de los Recuerdos · Monitoreo Activo"
  icon="ph-bell-ringing"
  :date="now()">
  <a href="{{ route('admin.adultos-mayores.index') }}" class="rm-btn rm-btn-secondary">
    <i class="ph-bold ph-arrow-left text-base"></i>
    <span>Volver al Centro</span>
  </a>
</x-ui.page-header>

 {{-- TARJETAS DE INDICADORES REALES --}}
    <section class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-7">
        <x-ui.metric-card label="Total Alertas" :value="$conteos['total']" icon="ph-bell" :variant="$conteos['total'] > 0 ? 'coral' : 'neutral'" />
        <x-ui.metric-card label="Red de Apoyo" :value="$conteos['red_de_apoyo']" icon="ph-users-three" variant="neutral" />
        <x-ui.metric-card label="Documentos" :value="$conteos['documentacion']" icon="ph-files" variant="neutral" />
        <x-ui.metric-card label="Salud y Cuidados" :value="$conteos['salud_y_cuidados']" icon="ph-first-aid" variant="sky" />
        <x-ui.metric-card label="Evaluaciones" :value="$conteos['evaluaciones']" icon="ph-clipboard-text" variant="neutral" />
        <x-ui.metric-card label="Seguimiento" :value="$conteos['seguimiento']" icon="ph-activity" variant="mint" />
        <x-ui.metric-card label="Institucional" :value="$conteos['estado_institucional']" icon="ph-buildings" variant="neutral" />
    </section>

     {{-- BARRA DE FILTROS Y BÚSQUEDA FORMATO ALERTAS (DESPLAZABLE Y CON COLORCITOS) --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            {{-- Filtros Rápidos Desplazables (Categorías) --}}
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-[var(--rm-text-secondary)] shrink-0 flex items-center gap-1">
                    <i class="ph-bold ph-funnel text-xs text-[var(--rm-primary)]"></i> Filtrar:
                </span>
                @php
                    $filtros = [
                        ['valor' => 'todas', 'label' => 'Todas', 'icon' => 'ph-circles-four'],
                        ['valor' => 'red_de_apoyo', 'label' => 'Red de Apoyo', 'icon' => 'ph-users-three'],
                        ['valor' => 'documentacion', 'label' => 'Documentos', 'icon' => 'ph-files'],
                        ['valor' => 'salud_y_cuidados', 'label' => 'Salud y Cuidados', 'icon' => 'ph-heartbeat'],
                        ['valor' => 'evaluaciones', 'label' => 'Evaluaciones', 'icon' => 'ph-clipboard-text'],
                        ['valor' => 'seguimiento', 'label' => 'Seguimiento', 'icon' => 'ph-pulse'],
                        ['valor' => 'estado_institucional', 'label' => 'Institucional', 'icon' => 'ph-buildings'],
                    ];
                @endphp

                <div class="rm-filter-pills flex-1">
                    @foreach($filtros as $f)
                        <button
                            type="button"
                            wire:click="$set('filtroCategoria', '{{ $f['valor'] }}')"
                            class="rm-filter-pill {{ $filtroCategoria === $f['valor'] ? 'is-active' : '' }}"
                        >
                            <i class="ph-bold {{ $f['icon'] }} text-xs"></i>
                            <span>{{ $f['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Buscador reactivo para escribir --}}
            <div class="relative flex items-center w-full md:w-72 shrink-0">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="buscar"
                    placeholder="Escriba para buscar..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] transition"
                />
                @if(!empty($buscar))
                    <button type="button"
                        wire:click="$set('buscar', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-danger)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($buscar) || ($filtroCategoria !== 'todas');
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="rm-filter-scroll">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($buscar))
                        <span class="rm-filter-chip rm-filter-chip--search">
                            <i class="ph-bold ph-magnifying-glass text-xs"></i>
                            <span>Búsqueda: "{{ Str::limit($buscar, 16) }}"</span>
                            <button type="button" wire:click="$set('buscar', '')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroCategoria !== 'todas')
                        <span class="rm-filter-chip rm-filter-chip--clinical">
                            <i class="ph-bold ph-tag text-xs"></i>
                            <span>Categoría: {{ str_replace('_', ' ', $filtroCategoria) }}</span>
                            <button type="button" wire:click="$set('filtroCategoria', 'todas')" title="Quitar filtro"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('buscar', ''); $set('filtroCategoria', 'todas')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    <span>Limpiar filtros</span>
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

 {{-- LISTADO DE ALERTAS --}}
 <main class="space-y-4">
 @if($alertas->isEmpty())
 <div class="rounded-2xl border border-borde bg-fondo-panel p-16 text-center shadow-xs flex flex-col items-center justify-center min-h-[300px]">
 <div class="h-16 w-16 rounded-full bg-estado-exitoBg text-estado-exito border border-estado-exitoBorde shadow-inner flex items-center justify-center mb-4">
 <i class="ph-bold ph-check text-2xl"></i>
 </div>
 <h3 class="text-base font-extrabold text-titulo">Sin alertas pendientes</h3>
 <p class="mt-1 text-xs font-semibold text-apoyo max-w-md mx-auto">
 Los registros principales se encuentran completos según los criterios actuales.
 </p>
 </div>
 @else
 	<div class="rm-alert-grid">
		@foreach($alertas as $alerta)
		<article class="rounded-[1rem] transition flex flex-col justify-between rm-alert-card rm-alert-card-compact {{ $alerta['nivel'] === 'prioritaria' ? 'rm-alert-card-prioritaria' : ($alerta['nivel'] === 'preventiva' ? 'rm-alert-card-preventiva' : '') }}">
			<div class="rm-alert-card-body">
				{{-- Fila superior: categoría y nivel --}}
				<div class="flex items-center justify-between gap-2">
					<span class="rm-alert-meta uppercase">
						{{ $alerta['categoria_label'] }}
					</span>
					<span class="rm-alert-pill {{ $alerta['nivel'] === 'prioritaria' ? 'bg-estado-peligroBg text-estado-peligro border-estado-peligroBorde' : ($alerta['nivel'] === 'preventiva' ? 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertenciaBorde' : 'bg-fondo-card text-apoyo border-borde-suave') }}">
						{{ $alerta['nivel'] }}
					</span>
				</div>

 				{{-- Nombre del adulto mayor --}}
				<div>
					<h3 class="rm-alert-title">
						{{ $alerta['nombre'] }}
					</h3>
					<div class="flex items-center gap-1 mt-0.5">
						<span class="inline-flex items-center rm-alert-category">
							<i class="ph-bold {{ $alerta['icono'] }} mr-1"></i>
							{{ $alerta['categoria_label'] }}
						</span>
					</div>
				</div>

 				{{-- Descripción de la alerta --}}
				<p class="rm-alert-description">
					{{ $alerta['descripcion'] }}
				</p>

 				{{-- Fecha relacionada si existe --}}
				@if($alerta['fecha'])
					<p class="rm-alert-meta flex items-center gap-1">
						<i class="ph-bold ph-calendar"></i>
						Fecha registrada: <strong>{{ $alerta['fecha'] }}</strong>
					</p>
				@endif
			</div>

 			{{-- Botón para revisar ficha --}}
			<div class="mt-3 border-t border-borde pt-2.5 flex justify-end">
				<a
					href="{{ route('admin.adultos-mayores.show', $alerta['adulto_id']) }}"
					class="inline-flex items-center gap-1 rm-alert-action"
				>
					<i class="ph-bold ph-eye"></i> Revisar en ficha
				</a>
			</div>
		</article>
 @endforeach
 </div>
 @endif
 </main>
</div>
