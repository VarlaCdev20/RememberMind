<div class="relative mx-auto max-w-7xl space-y-6 py-6 px-4 sm:px-6 lg:px-8">
 {{-- ENCABEZADO --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
 <div class="h-1 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8DA280]"></div>
 <div class="p-6">
 <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
 <div>
 <span class="text-[11px] font-bold uppercase tracking-[0.2em] text-parrafo">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Monitoreo Activo
 </span>
 <h1 class="mt-1 text-2xl font-black text-titulo">Alertas y Pendientes</h1>
 <p class="mt-1 text-xs font-bold text-apoyo">
 Seguimiento institucional de registros incompletos, alertas activas y acciones pendientes del módulo Adultos Mayores.
 </p>
 </div>
 <div class="shrink-0">
 <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold text-inverso shadow-md transition hover:bg-fondo-panel active:scale-95">
 <i class="ph-bold ph-arrow-left text-sm"></i> Volver al Centro
 </a>
 </div>
 </div>
 </div>
 </section>

 {{-- TARJETAS DE INDICADORES REALES --}}
 <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
 {{-- Total --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Total Alertas</p>
 <p class="mt-1.5 text-2xl font-black text-boton-acento">{{ $conteos['total'] }}</p>
 </div>
 {{-- Red de Apoyo --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Red de Apoyo</p>
 <p class="mt-1.5 text-2xl font-black text-titulo">{{ $conteos['red_de_apoyo'] }}</p>
 </div>
 {{-- Documentos --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Documentos</p>
 <p class="mt-1.5 text-2xl font-black text-titulo">{{ $conteos['documentacion'] }}</p>
 </div>
 {{-- Salud --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Salud y Cuidados</p>
 <p class="mt-1.5 text-2xl font-black text-titulo">{{ $conteos['salud_y_cuidados'] }}</p>
 </div>
 {{-- Evaluaciones --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Evaluaciones</p>
 <p class="mt-1.5 text-2xl font-black text-parrafo">{{ $conteos['evaluaciones'] }}</p>
 </div>
 {{-- Seguimiento --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Seguimiento</p>
 <p class="mt-1.5 text-2xl font-black text-estado-exito">{{ $conteos['seguimiento'] }}</p>
 </div>
 {{-- Estado --}}
 <div class="rounded-[18px] border border-borde-suave bg-fondo-panel p-4 shadow-xs">
 <p class="text-[9px] font-bold uppercase tracking-[0.15em] text-apoyo">Institucional</p>
 <p class="mt-1.5 text-2xl font-black text-slate-500">{{ $conteos['estado_institucional'] }}</p>
 </div>
 </div>

 {{-- BARRA DE FILTROS Y BÚSQUEDA --}}
 <section class="overflow-hidden rounded-[24px] border border-borde bg-fondo-panel p-4 shadow-[0_4px_14px_rgba(47,62,92,0.04)]">
 <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
 {{-- Filtros Rápidos (Categorías) --}}
 <div class="flex flex-wrap items-center gap-1.5">
 <span class="text-[10px] font-bold uppercase tracking-widest text-apoyo mr-1">Filtrar:</span>
 
 @php
 $filtros = [
 ['valor' => 'todas', 'label' => 'Todas'],
 ['valor' => 'red_de_apoyo', 'label' => 'Red de Apoyo'],
 ['valor' => 'documentacion', 'label' => 'Documentos'],
 ['valor' => 'salud_y_cuidados', 'label' => 'Salud y Cuidados'],
 ['valor' => 'evaluaciones', 'label' => 'Evaluaciones'],
 ['valor' => 'seguimiento', 'label' => 'Seguimiento'],
 ['valor' => 'estado_institucional', 'label' => 'Institucional'],
 ];
 @endphp

 @foreach($filtros as $f)
 <button
 type="button"
 wire:click="$set('filtroCategoria', '{{ $f['valor'] }}')"
 class="rounded-lg px-3 py-1.5 text-[10px] font-bold transition active:scale-95 {{ $filtroCategoria === $f['valor'] ? 'bg-boton-principal text-inverso shadow-xs' : 'bg-fondo-panel text-apoyo hover:bg-fondo-panel' }}"
 >
 {{ $f['label'] }}
 </button>
 @endforeach
 </div>

 {{-- Buscador reactivo --}}
 <div class="flex items-center gap-2 rounded-xl border border-borde-suave bg-fondo-card/45 px-3 py-1.5 shrink-0">
 <i class="ph-bold ph-magnifying-glass text-xs text-apoyo"></i>
 <input
 type="text"
 wire:model.live="buscar"
 placeholder="Buscar adulto mayor..."
 class="bg-transparent text-xs font-bold text-titulo outline-none placeholder:text-apoyo w-full md:w-48"
 >
 </div>
 </div>
 </section>

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
				{{-- Fila superior: ID y Nivel --}}
				<div class="flex items-center justify-between gap-2">
					<span class="rm-alert-meta uppercase">
						{{ $alerta['adulto_id'] }}
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
