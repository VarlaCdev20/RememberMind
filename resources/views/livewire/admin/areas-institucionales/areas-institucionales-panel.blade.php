<div class="p-6 md:p-8 space-y-8 relative min-h-screen bg-transparent print:bg-white print:p-0 print:space-y-4" 
 x-data="{ printListenerAdded: false }" 
 x-init="if(!printListenerAdded) { window.addEventListener('print-window', () => window.print()); printListenerAdded = true; }">

 {{-- CSS Estilos para Impresión y Marca de Agua en Pantalla/Impresora --}}
 <style>
 @media print {
 .no-print {
 display: none !important;
 }
 .print-area {
 background: white !important;
 box-shadow: none !important;
 color: #2F3E5C !important;
 padding: 0 !important;
 margin: 0 !important;
 }
 .print-break {
 page-break-before: always;
 }
 body {
 background: white !important;
 }
 }
 
 .watermark-container {
 position: relative;
 }
 .watermark-bg {
 position: absolute;
 top: 50%;
 left: 50%;
 transform: translate(-50%, -50%) rotate(-30deg);
 font-size: 5rem;
 font-weight: 900;
 color: rgba(47, 62, 92, 0.05);
 text-transform: uppercase;
 letter-spacing: 0.2em;
 pointer-events: none;
 z-index: 0;
 white-space: nowrap;
 user-select: none;
 }
 </style>

 {{-- ENCABEZADO PREMIUM --}}
 <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between no-print">
 <div>
 <h1 class="text-3xl font-black tracking-tight text-titulo">Áreas Institucionales</h1>
 <p class="text-sm font-semibold text-parrafo">Estructura y organigrama funcional operativo de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</p>
 </div>

 <div class="flex items-center gap-3">
 @can('areas.reportes')
 <button type="button"
 wire:click="abrirReportes"
 class="inline-flex items-center justify-center gap-2 rm-btn-secondary">
 <i class="ph-bold ph-chart-line-up text-base"></i>
 Reportes
 </button>
 @endcan

 @can('areas.crear')
 <button type="button"
 wire:click="crearArea"
 class="inline-flex items-center justify-center gap-2 rm-btn-primary">
 <i class="ph-bold ph-plus-circle text-base"></i>
 Nueva área
 </button>
 @endcan
 </div>
 </header>

 {{-- AVISO TEMPORAL DE PERMISOS --}}
 @if(!auth()->user()->can('areas.reportes'))
 <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-bold text-amber-800 shadow-sm flex items-center gap-2.5 no-print">
 <i class="ph-bold ph-warning-octagon text-lg text-amber-600 shrink-0"></i>
 <div>
 No tienes el permiso <code class="bg-amber-100 px-1 py-0.5 rounded text-amber-900 font-mono">areas.reportes</code> asignado en la sesión de base de datos actual. Por esta razón, los botones y secciones de reportes estarán ocultos. Contacta al administrador o ejecuta el Seeder de Permisos.
 </div>
 </div>
 @endif

 {{-- MÉTRICAS E INDICADORES ORGANIZACIONALES --}}
 <section class="grid grid-cols-2 md:grid-cols-6 gap-4 no-print">
 {{-- Total Áreas --}}
 <div class="rm-card p-4">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-parrafo">Total Áreas</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-panel text-titulo">
 <i class="ph-bold ph-layout text-lg"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black text-titulo">{{ $totalAreas }}</p>
 </div>

 {{-- Áreas Activas --}}
 <div class="rm-card p-4">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-parrafo">Activas</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
 <i class="ph-bold ph-check-circle text-lg"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black text-estado-exito">{{ $areasActivas }}</p>
 </div>

 {{-- Áreas Inactivas --}}
 <div class="rm-card p-4">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-parrafo">Inactivas</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-eye-slash text-lg"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black text-boton-acento">{{ $areasInactivas }}</p>
 </div>

 {{-- Usuarios Vinculados --}}
 <div class="rm-card p-4">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-parrafo">Personal</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-estado-peligroBg text-boton-acento">
 <i class="ph-bold ph-users-three text-lg"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black text-boton-acento">{{ $usuariosVinculados }}</p>
 </div>

 {{-- Áreas Sin Responsable --}}
 <div class="rm-card p-4">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-parrafo">Sin Responsable</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-50 text-red-700">
 <i class="ph-bold ph-warning-circle text-lg"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black text-red-600">{{ $areasSinResponsable }}</p>
 </div>

 {{-- Áreas Sin Usuarios --}}
 <div class="rm-card p-4">
 <div class="flex items-center justify-between">
 <span class="text-[10px] font-bold uppercase tracking-wider text-parrafo">Sin Personal</span>
 <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-fondo-panel text-apoyo">
 <i class="ph-bold ph-user-minus text-lg"></i>
 </span>
 </div>
 <p class="mt-2 text-2xl font-black text-apoyo">{{ $areasSinUsuarios }}</p>
 </div>
 </section>

 {{-- FILTROS DE BÚSQUEDA Y VISTA --}}
 <section class="rounded-2xl rm-surface-glass p-4 no-print">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
 {{-- Búsqueda --}}
 <div class="relative md:col-span-2">
 <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-parrafo">
 <i class="ph-bold ph-magnifying-glass"></i>
 </span>
 <input type="text"
 wire:model.live.debounce.300ms="search"
 placeholder="Buscar por nombre o tipo..."
 class="rm-input w-full pl-10">
 </div>

 {{-- Tipo de Área --}}
 <div>
 <select wire:model.live="filtroTipo"
 class="rm-select w-full">
 <option value="">-- Todos los tipos --</option>
 <option value="Administrativa">Administrativa</option>
 <option value="Salud">Salud</option>
 <option value="Social">Social</option>
 <option value="Soporte">Soporte</option>
 </select>
 </div>

 {{-- Estado --}}
 <div>
 <select wire:model.live="filtroEstado"
 class="rm-select w-full">
 <option value="">-- Todos los estados --</option>
 <option value="ACTIVA">Áreas Activas</option>
 <option value="INACTIVA">Áreas Inactivas</option>
 </select>
 </div>
 </div>
 </section>

 {{-- GRID PRINCIPAL DE CARDS --}}
 <section class="grid grid-cols-1 md:grid-cols-3 gap-6 no-print">
 @forelse($areas as $area)
 @php
 $esInactiva = $area->estado === 'INACTIVA';
 $colorAccent = $area->color ?? '#2F3E5C';
 @endphp
 <div class="group relative rounded-3xl transition-all duration-300 hover:-translate-y-1 rm-card overflow-hidden border-2 border-transparent hover:border-borde-suave flex flex-col {{ $esInactiva ? 'opacity-70 grayscale bg-[var(--surface-soft)]' : '' }}">
 
 {{-- PORTADA BORDE A BORDE --}}
 <div class="relative h-36 w-full shrink-0 overflow-hidden bg-[var(--surface-soft)]">
 @if($area->imagen_area)
 <img src="{{ \Illuminate\Support\Facades\Storage::url($area->imagen_area) }}" 
 alt="{{ $area->nombre }}" 
 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
 @else
 {{-- Fallback degradado --}}
 <div class="h-full w-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105" 
 style="background: linear-gradient(135deg, {{ $colorAccent }} 0%, #D5C7B9 100%)">
 <i class="ph-bold {{ $this->obtenerIconoTipo($area->tipo_area) }} text-inverso text-5xl opacity-40"></i>
 </div>
 @endif
 <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
 
 {{-- Badge Tipo y Estado encima de la imagen --}}
 <div class="absolute top-4 left-4 flex flex-wrap gap-2">
 <span class="inline-block rounded-full bg-fondo-card/95 px-2.5 py-0.5 text-[8px] font-black uppercase tracking-wider text-titulo shadow-sm">
 {{ $area->tipo_area }}
 </span>
 @if($esInactiva)
 <span class="rounded-full bg-red-600 px-2.5 py-0.5 text-[8px] font-black text-inverso uppercase tracking-widest shadow-sm">
 Inactiva
 </span>
 @else
 <span class="rounded-full bg-estado-exitoBg px-2.5 py-0.5 text-[8px] font-black text-inverso uppercase tracking-widest shadow-sm">
 Activa
 </span>
 @endif
 </div>

 {{-- Nombre del área encima de la imagen --}}
 <div class="absolute bottom-4 left-4 right-4">
 <h3 class="text-base font-extrabold text-inverso leading-snug drop-shadow-sm truncate">
 {{ $area->nombre }}
 </h3>
 </div>
 </div>

 {{-- CONTENIDO CARD --}}
 <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
 {{-- DESCRIPCIÓN --}}
 <p class="text-xs font-semibold text-meta line-clamp-3 leading-relaxed">
 {{ $area->descripcion }}
 </p>

 {{-- RESPONSABLE --}}
 <div class="rounded-2xl bg-fondo-panel p-3 flex items-center justify-between border border-borde-suave">
 <div class="flex items-center gap-2">
 <div class="flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-bold text-inverso shadow-sm" style="background-color: {{ $colorAccent }}">
 {{ $area->responsable ? substr($area->responsable->nombres, 0, 1) . substr($area->responsable->ap_paterno, 0, 1) : '?' }}
 </div>
 <div>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Responsable</p>
 <p class="text-[10px] font-bold text-titulo truncate max-w-[140px]">
 {{ $area->responsable ? $area->responsable->name : 'Sin Responsable' }}
 </p>
 </div>
 </div>
 
 {{-- Contador Personal --}}
 <div class="text-right">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Personal</p>
 <span class="inline-flex items-center gap-1 text-[11px] font-bold text-boton-acento">
 <i class="ph-bold ph-users-three"></i>
 {{ $area->usuarios_count }}
 </span>
 </div>
 </div>

 {{-- ACCIONES DE CARD --}}
 <div class="pt-4 border-t border-borde-suave flex items-center justify-between">
 <div class="flex items-center gap-2">
 <button type="button"
 wire:click="verArea('{{ $area->cod_area }}')"
 class="inline-flex h-8 items-center gap-1.5 rounded-full bg-fondo-panel px-3 text-[10px] font-bold text-titulo hover:bg-boton-principal hover:text-inverso transition duration-200"
 title="Ver Ficha Completa">
 <i class="ph-bold ph-eye"></i>
 Ver ficha
 </button>

 @can('areas.reportes')
 <button type="button"
 wire:click="abrirReporteArea('{{ $area->cod_area }}')"
 class="inline-flex h-8 items-center gap-1.5 rounded-full bg-estado-exitoBg px-3 text-[10px] font-bold text-estado-exito hover:bg-estado-exitoBg hover:text-inverso transition duration-200"
 title="Reporte del Área">
 <i class="ph-bold ph-file-chart"></i>
 Reporte
 </button>
 @endcan
 </div>

 <div class="flex items-center gap-1.5">
 @can('areas.editar')
 <button type="button"
 wire:click="editarArea('{{ $area->cod_area }}')"
 class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-estado-peligroBg text-boton-acento hover:bg-boton-acento hover:text-inverso transition duration-200"
 title="Editar Área">
 <i class="ph-bold ph-pencil-simple"></i>
 </button>
 @endcan

 @can('areas.cambiar_estado')
 <button type="button"
 wire:click="toggleEstado('{{ $area->cod_area }}')"
 wire:confirm="¿Está seguro de cambiar el estado de este área institucional?"
 class="inline-flex h-8 w-8 items-center justify-center rounded-full transition duration-200 {{ $esInactiva ? 'bg-estado-exitoBg text-estado-exito hover:bg-estado-exitoBg hover:text-inverso' : 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-inverso' }}"
 title="{{ $esInactiva ? 'Activar Área' : 'Desactivar Área' }}">
 <i class="ph-bold {{ $esInactiva ? 'ph-power' : 'ph-x-circle' }}"></i>
 </button>
 @endcan
 </div>
 </div>
 </div>
 </div>
 @empty
 <div class="col-span-full rounded-3xl bg-fondo-card p-12 text-center shadow-[0_12px_24px_rgba(0,0,0,0.02)]">
 <div class="flex justify-center text-5xl text-meta mb-4">
 <i class="ph-bold ph-folder-open"></i>
 </div>
 <h3 class="text-base font-extrabold text-titulo">No se encontraron áreas institucionales</h3>
 <p class="text-xs font-semibold text-parrafo mt-1">Intenta ajustando los filtros de búsqueda o registra una nueva área.</p>
 </div>
 @endforelse
 </section>

 {{-- FICHA LATERAL DETALLE DEL ÁREA (SLIDE-OVER PANEL EN MAYÚSCULAS Y DOBLE COLUMNA) --}}
 @if($mostrarFicha && $areaSeleccionada)
 <div class="fixed inset-0 z-50 overflow-hidden no-print" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
 <div class="absolute inset-0 overflow-hidden">
 <div class="rm-modal-overlay" wire:click="cerrarFicha"></div>

 <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
 <div class="pointer-events-auto w-screen max-w-5xl transform rm-modal-panel transition duration-500 ease-in-out">
 <div class="flex h-full flex-col bg-fondo-card">
 
 {{-- Portada Ficha Borde a Borde --}}
 <div class="relative h-48 shrink-0 overflow-hidden bg-[var(--surface-soft)]">
 @if($areaSeleccionada->imagen_area)
 <img src="{{ \Illuminate\Support\Facades\Storage::url($areaSeleccionada->imagen_area) }}" 
 alt="{{ $areaSeleccionada->nombre }}" 
 class="h-full w-full object-cover">
 @else
 <div class="h-full w-full flex items-center justify-center" 
 style="background: linear-gradient(135deg, {{ $areaSeleccionada->color ?? '#2F3E5C' }} 0%, #D5C7B9 100%)">
 <i class="ph-bold {{ $this->obtenerIconoTipo($areaSeleccionada->tipo_area) }} text-inverso text-6xl opacity-30"></i>
 </div>
 @endif
 <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>
 
 <button type="button"
 wire:click="cerrarFicha"
 class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-black/40 text-inverso hover:bg-fondo-card hover:text-titulo transition duration-200">
 <i class="ph-bold ph-x text-sm"></i>
 </button>

 <div class="absolute bottom-4 left-6 right-6">
 <span class="inline-block rounded-full bg-fondo-card/20 px-2.5 py-0.5 text-[8px] font-black uppercase tracking-wider text-inverso backdrop-blur-sm">
 {{ $areaSeleccionada->tipo_area }}
 </span>
 <h2 class="text-2xl font-black text-inverso mt-1 leading-tight drop-shadow">{{ $areaSeleccionada->nombre }}</h2>
 </div>
 </div>

 @php
 $totalUsuarios = count($areaSeleccionada->usuarios);
 $activosUsuarios = $areaSeleccionada->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count();
 $inactivosUsuarios = $areaSeleccionada->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count();
 $porcActivos = $totalUsuarios > 0 ? round(($activosUsuarios / $totalUsuarios) * 100, 1) : 0;
 @endphp

 {{-- Cuerpo Ficha - Distribuido en Grid de 2 Columnas en Desktop --}}
 <div class="flex-1 overflow-y-auto bg-transparent p-6 space-y-6" 
 x-data="{
 activeChart: null,
 rolChart: null,
 lineChart: null,
 initAreaCharts() {
 if (this.activeChart) this.activeChart.destroy();
 if (this.rolChart) this.rolChart.destroy();
 if (this.lineChart) this.lineChart.destroy();

 const total = {{ $totalUsuarios }};
 const activos = {{ $activosUsuarios }};
 const inactivos = {{ $inactivosUsuarios }};

 if (total > 0) {
 const ctxActive = this.$refs.canvasActive;
 if (ctxActive) {
 this.activeChart = new Chart(ctxActive, {
 type: 'doughnut',
 data: {
 labels: ['Activos', 'Inactivos'],
 datasets: [{
 data: [activos, inactivos],
 backgroundColor: ['#8DA280', '#E27D60'],
 borderWidth: 0
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 cutout: '70%',
 plugins: {
 legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } }
 }
 }
 });
 }

 const rolesLabels = @js(array_keys($this->obtenerDatosGraficoUsuariosPorRolArea($areaSeleccionada->cod_area)));
 const rolesValues = @js(array_values($this->obtenerDatosGraficoUsuariosPorRolArea($areaSeleccionada->cod_area)));
 const ctxRol = this.$refs.canvasRol;
 if (ctxRol && rolesLabels.length > 0) {
 this.rolChart = new Chart(ctxRol, {
 type: 'bar',
 data: {
 labels: rolesLabels,
 datasets: [{
 data: rolesValues,
 backgroundColor: '#2F3E5C',
 borderRadius: 6
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } },
 scales: {
 y: { beginAtZero: true, grid: { color: 'rgba(47,62,92,0.05)' } },
 x: { grid: { display: false } }
 }
 }
 });
 }
 }

 const evLabels = @js(array_keys($this->obtenerDatosGraficoEvolucionArea($areaSeleccionada->cod_area)));
 const evValues = @js(array_values($this->obtenerDatosGraficoEvolucionArea($areaSeleccionada->cod_area)));
 const ctxLine = this.$refs.canvasLine;
 if (ctxLine && evLabels.length >= 2) {
 this.lineChart = new Chart(ctxLine, {
 type: 'line',
 data: {
 labels: evLabels,
 datasets: [{
 data: evValues,
 borderColor: '#E27D60',
 backgroundColor: 'rgba(226,125,96,0.1)',
 borderWidth: 3,
 fill: true,
 tension: 0.3
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } },
 scales: {
 y: { beginAtZero: true, grid: { color: 'rgba(47,62,92,0.05)' } },
 x: { grid: { display: false } }
 }
 }
 });
 }
 }
 }" 
 x-init="$nextTick(() => initAreaCharts())">
 
 <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-8">
 
 {{-- COLUMNA IZQUIERDA: INFORMACIÓN Y PERSONAL --}}
 <div class="space-y-6">
 {{-- INFORMACIÓN DEL ÁREA --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">INFORMACIÓN DEL ÁREA</h4>
 <p class="text-xs font-semibold text-meta bg-fondo-card p-3.5 rounded-2xl shadow-sm border border-borde-suave leading-relaxed">
 {{ $areaSeleccionada->descripcion }}
 </p>
 </div>

 {{-- RESPONSABLE DEL ÁREA --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">RESPONSABLE DEL ÁREA</h4>
 <div class="flex items-center gap-3 bg-fondo-card p-3.5 rounded-2xl shadow-sm border border-borde-suave">
 <div class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold text-inverso shadow-sm" style="background-color: {{ $areaSeleccionada->color ?? '#E27D60' }}">
 {{ $areaSeleccionada->responsable ? substr($areaSeleccionada->responsable->nombres, 0, 1) . substr($areaSeleccionada->responsable->ap_paterno, 0, 1) : '?' }}
 </div>
 <div class="flex-1">
 <p class="text-xs font-bold text-titulo">
 {{ $areaSeleccionada->responsable ? $areaSeleccionada->responsable->name : 'Sin Responsable Asignado' }}
 </p>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">
 {{ $areaSeleccionada->responsable ? ($areaSeleccionada->responsable->getRoleNames()->first() ?? 'Personal') : 'Esta área aún no tiene responsable' }}
 </p>
 </div>
 </div>
 </div>

 {{-- USUARIOS VINCULADOS --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">USUARIOS VINCULADOS</h4>
 <div class="space-y-2 max-h-60 overflow-y-auto pr-1 [scrollbar-width:thin]">
 @forelse($areaSeleccionada->usuarios as $u)
 <div class="flex items-center justify-between bg-fondo-card p-2.5 rounded-xl border border-borde-suave shadow-sm transition hover:border-borde-focus">
 <div class="flex items-center gap-2.5">
 <div class="flex h-8 w-8 items-center justify-center rounded-full bg-fondo-app text-[10px] font-bold text-meta">
 {{ substr($u->nombres, 0, 1) . substr($u->ap_paterno, 0, 1) }}
 </div>
 <div>
 <h5 class="text-xs font-bold text-titulo leading-snug">{{ $u->name }}</h5>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">
 {{ $u->getRoleNames()->first() ?? 'Sin Rol' }}
 </p>
 </div>
 </div>

 <span class="rounded-full px-2 py-0.5 text-[8px] font-black uppercase tracking-wider border {{ in_array($u->estado, ['ACTIVO', 1, '1']) ? 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde' : 'bg-red-50 text-red-600 border-red-100' }}">
 {{ in_array($u->estado, ['ACTIVO', 1, '1']) ? 'Activo' : 'Inactivo' }}
 </span>
 </div>
 @empty
 <div class="rounded-xl border border-dashed border-borde-suave p-6 text-center text-xs font-semibold text-parrafo bg-fondo-card/40">
 Esta área aún no tiene usuarios asignados.
 </div>
 @endforelse
 </div>
 </div>

 {{-- OBSERVACIONES --}}
 @if($areaSeleccionada->observaciones)
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">OBSERVACIONES</h4>
 <p class="text-xs font-semibold text-meta bg-fondo-panel p-3 rounded-2xl border border-orange-200/50 leading-relaxed italic">"{{ $areaSeleccionada->observaciones }}"
 </p>
 </div>
 @endif
 </div>

 {{-- COLUMNA DERECHA: ESTADÍSTICAS Y GRÁFICAS --}}
 <div class="space-y-6">
 {{-- ESTADÍSTICAS DEL ÁREA --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">ESTADÍSTICAS DEL ÁREA</h4>
 <div class="grid grid-cols-2 gap-3">
 {{-- Total Vinculados --}}
 <div class="bg-fondo-card p-3 rounded-xl border border-borde-suave shadow-sm">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Total Personal</p>
 <p class="text-lg font-extrabold text-titulo mt-0.5">{{ $totalUsuarios }}</p>
 </div>
 {{-- Activos --}}
 <div class="bg-fondo-card p-3 rounded-xl border border-borde-suave shadow-sm">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Activos</p>
 <p class="text-lg font-extrabold text-estado-exito mt-0.5">{{ $activosUsuarios }}</p>
 </div>
 {{-- Inactivos --}}
 <div class="bg-fondo-card p-3 rounded-xl border border-borde-suave shadow-sm">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Inactivos</p>
 <p class="text-lg font-extrabold text-red-600 mt-0.5">{{ $inactivosUsuarios }}</p>
 </div>
 {{-- Porcentaje --}}
 <div class="bg-fondo-card p-3 rounded-xl border border-borde-suave shadow-sm">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">% Activos</p>
 <p class="text-lg font-extrabold text-boton-acento mt-0.5">{{ $porcActivos }}%</p>
 </div>
 {{-- Estado del Área --}}
 <div class="bg-fondo-card p-3 rounded-xl border border-borde-suave shadow-sm">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Estado Área</p>
 <p class="text-xs font-bold mt-1 {{ $areaSeleccionada->estado === 'ACTIVA' ? 'text-estado-exito' : 'text-red-600' }}">{{ $areaSeleccionada->estado }}</p>
 </div>
 {{-- Responsable Status --}}
 <div class="bg-fondo-card p-3 rounded-xl border border-borde-suave shadow-sm">
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider">Responsable</p>
 <p class="text-xs font-bold mt-1 {{ $areaSeleccionada->responsable_id ? 'text-estado-exito' : 'text-red-600' }}">
 {{ $areaSeleccionada->responsable_id ? 'Asignado' : 'Sin asignar' }}
 </p>
 </div>
 </div>
 </div>

 {{-- GRÁFICAS DE EVOLUCIÓN --}}
 <div class="space-y-4">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">GRÁFICAS DEL ÁREA</h4>
 
 @if($totalUsuarios > 0)
 {{-- Gráfico Dona Activos vs Inactivos --}}
 <div class="bg-fondo-card p-4 rounded-2xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Usuarios Activos vs Inactivos</h5>
 <div class="relative h-40 w-full">
 <canvas x-ref="canvasActive"></canvas>
 </div>
 </div>

 {{-- Gráfico Barras Roles --}}
 <div class="bg-fondo-card p-4 rounded-2xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Usuarios por Rol</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasRol"></canvas>
 </div>
 </div>
 @else
 <div class="bg-fondo-card p-6 rounded-2xl border border-borde-suave shadow-sm text-center text-xs font-semibold text-parrafo">
 <i class="ph-bold ph-chart-pie text-2xl mb-1.5 text-meta block"></i>
 Esta área aún no tiene usuarios asignados.
 </div>
 @endif

 {{-- Gráfico Evolución Mensual --}}
 @if(count($this->obtenerDatosGraficoEvolucionArea($areaSeleccionada->cod_area)) >= 2)
 <div class="bg-fondo-card p-4 rounded-2xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Evolución Asignación Personal</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasLine"></canvas>
 </div>
 </div>
 @else
 <div class="bg-fondo-card p-6 rounded-2xl border border-borde-suave shadow-sm text-center text-xs font-semibold text-parrafo">
 <i class="ph-bold ph-trend-up text-2xl mb-1.5 text-meta block"></i>
 No hay datos históricos suficientes para graficar evolución.
 </div>
 @endif
 </div>
 </div>
 
 </div>

 {{-- REPORTES DEL ÁREA --}}
 @can('areas.reportes')
 <div class="mt-6 border-t border-borde-suave pt-4 space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">REPORTES DEL ÁREA</h4>
 <div class="bg-fondo-panel p-4 rounded-2xl border border-borde-suave space-y-3">
 <p class="text-[11px] font-semibold text-meta leading-relaxed">
 Genere informes del área o exporte el listado completo de personal adscrito.
 </p>
 <div class="flex flex-wrap gap-2">
 <button type="button"
 wire:click="abrirReporteArea('{{ $areaSeleccionada->cod_area }}')"
 class="inline-flex items-center gap-1.5 rounded-full bg-boton-principal px-3.5 py-2 text-[10px] font-bold text-inverso hover:bg-fondo-panel shadow-md transition duration-200">
 <i class="ph-bold ph-file-chart text-xs"></i>
 Generar reporte del área
 </button>
 <button type="button"
 wire:click="exportarReporteAreaPdf('{{ $areaSeleccionada->cod_area }}')"
 class="inline-flex items-center gap-1.5 rounded-full bg-estado-peligroBg px-3.5 py-2 text-[10px] font-bold text-boton-acento hover:bg-boton-acento hover:text-inverso transition duration-200">
 <i class="ph-bold ph-file-pdf text-xs"></i>
 Exportar PDF
 </button>
 <button type="button"
 wire:click="exportarUsuariosAreaExcel('{{ $areaSeleccionada->cod_area }}')"
 class="inline-flex items-center gap-1.5 rounded-full bg-estado-exitoBg px-3.5 py-2 text-[10px] font-bold text-estado-exito hover:bg-estado-exitoBg hover:text-inverso transition duration-200">
 <i class="ph-bold ph-file-xls text-xs"></i>
 Exportar Excel
 </button>
 </div>
 </div>
 </div>
 @endcan

 {{-- TRAZABILIDAD --}}
 <div class="mt-6 border-t border-borde-suave pt-4 space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">TRAZABILIDAD</h4>
 <div class="bg-fondo-card p-3 rounded-2xl border border-borde-suave text-[10px] font-semibold text-meta space-y-1">
 <p><strong>Última actualización:</strong> {{ $areaSeleccionada->updated_at->format('d/m/Y H:i') }}</p>
 </div>
 </div>
 </div>

 {{-- Acciones Ficha --}}
 <div class="border-t border-borde-suave p-4 bg-fondo-card flex items-center justify-end gap-2">
 @can('areas.reportes')
 <button type="button"
 wire:click="abrirReporteArea('{{ $areaSeleccionada->cod_area }}')"
 class="inline-flex items-center gap-1.5 rounded-full border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-meta hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-file-chart"></i>
 Generar reporte del área
 </button>
 @endcan
 @can('areas.editar')
 <button type="button"
 wire:click="editarArea('{{ $areaSeleccionada->cod_area }}')"
 class="inline-flex items-center gap-1.5 rounded-full bg-boton-acento px-4 py-2 text-xs font-bold text-inverso hover:bg-fondo-panel shadow transition duration-200">
 <i class="ph-bold ph-pencil-simple"></i>
 Editar
 </button>
 @endcan
 <button type="button"
 wire:click="cerrarFicha"
 class="inline-flex items-center gap-1.5 rounded-full border border-borde-suave bg-fondo-card px-4 py-2 text-xs font-bold text-apoyo hover:bg-fondo-panel transition duration-200">
 Cerrar
 </button>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 @endif

 {{-- MODAL DE CREACIÓN / EDICIÓN LIMPIO --}}
 @if($mostrarFormulario)
 <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto no-print" role="dialog" aria-modal="true">
 <div class="rm-modal-overlay" wire:click="cerrarFormulario"></div>

 <div class="relative w-full max-w-2xl rounded-[2.2rem] rm-modal-panel p-6 transform transition-all duration-300">
 
 {{-- Encabezado Modal --}}
 <header class="flex items-center justify-between pb-4 border-b border-borde-suave">
 <div>
 <h3 class="text-xl font-extrabold text-titulo">{{ $isEdit ? 'Editar Área Institucional' : 'Nueva Área Institucional' }}</h3>
 <p class="text-xs font-semibold text-parrafo mt-0.5">Por favor, rellene las especificaciones funcionales del área.</p>
 </div>
 <button type="button"
 wire:click="cerrarFormulario"
 class="flex h-8 w-8 items-center justify-center rounded-full bg-fondo-panel text-meta hover:bg-boton-acento hover:text-inverso transition duration-200">
 <i class="ph-bold ph-x"></i>
 </button>
 </header>

 {{-- Formulario --}}
 <form wire:submit.prevent="guardarArea" class="mt-4 space-y-4">
 <div class="grid grid-cols-1 gap-4 md:grid-cols-2 max-h-[60vh] overflow-y-auto p-1 pr-2 [scrollbar-width:thin] [scrollbar-color:#C7B5A3_transparent]">
 
 {{-- ── SECCIÓN 1: IDENTIDAD DEL ÁREA ── --}}
 <div class="space-y-4 md:col-span-2">
 <h4 class="text-[10px] font-bold text-boton-acento uppercase tracking-widest border-b border-borde-focus pb-1">1. Identidad del Área</h4>
 </div>

 {{-- Nombre --}}
 <div class="md:col-span-2">
 <label class="block text-xs font-bold text-titulo mb-1">Nombre de la Área</label>
 <input type="text"
 wire:model="nombre"
 placeholder="Ej: Área de Atención Médica"
 class="rm-input w-full">
 @error('nombre') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- Tipo de Área --}}
 <div>
 <label class="block text-xs font-bold text-titulo mb-1">Tipo de Área</label>
 <select wire:model="tipo_area"
 class="rm-input w-full">
 <option value="Administrativa">Administrativa</option>
 <option value="Salud">Salud</option>
 <option value="Social">Social</option>
 <option value="Soporte">Soporte</option>
 </select>
 @error('tipo_area') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- Orden --}}
 <div>
 <label class="block text-xs font-bold text-titulo mb-1">Orden de Visualización</label>
 <input type="number"
 wire:model="orden"
 min="0"
 class="rm-input w-full">
 @error('orden') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- Descripción --}}
 <div class="md:col-span-2">
 <label class="block text-xs font-bold text-titulo mb-1">Descripción Funcional</label>
 <textarea wire:model="descripcion"
 rows="3"
 placeholder="Describa el rol operativo y responsabilidades institucionales del área..."
 class="rm-input w-full"></textarea>
 @error('descripcion') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- Estado --}}
 <div>
 <label class="block text-xs font-bold text-titulo mb-1">Estado de Área</label>
 <select wire:model="estado"
 class="rm-input w-full">
 <option value="ACTIVA">ACTIVA</option>
 <option value="INACTIVA">INACTIVA</option>
 </select>
 @error('estado') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- ── SECCIÓN 2: PRESENTACIÓN VISUAL ── --}}
 <div class="space-y-4 md:col-span-2 mt-2">
 <h4 class="text-[10px] font-bold text-boton-acento uppercase tracking-widest border-b border-borde-focus pb-1">2. Presentación Visual</h4>
 </div>

 {{-- Portada Upload --}}
 <div class="md:col-span-2">
 <label class="block text-xs font-bold text-titulo mb-1">Imagen de Portada</label>
 <div class="flex items-center gap-4">
 <div class="h-20 w-32 shrink-0 rounded-2xl border border-dashed border-borde-suave bg-fondo-panel overflow-hidden relative flex items-center justify-center">
 @if($nuevaImagen)
 <img src="{{ $nuevaImagen->temporaryUrl() }}" class="h-full w-full object-cover">
 @elseif($areaId && ($area = \App\Models\AreaInstitucional::find($areaId)) && $area->imagen_area)
 <img src="{{ \Illuminate\Support\Facades\Storage::url($area->imagen_area) }}" class="h-full w-full object-cover">
 @else
 <span class="text-[10px] font-bold text-apoyo">Sin Portada</span>
 @endif
 </div>
 <div class="flex-1">
 <input type="file"
 wire:model="nuevaImagen"
 accept="image/*"
 class="w-full text-xs text-meta file:mr-3 file:py-1.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-fondo-panel file:text-titulo file:hover:bg-fondo-panel file:cursor-pointer">
 <p class="text-[9px] text-parrafo mt-1">Formatos soportados: JPG, PNG, WEBP. Tamaño máx. 4MB.</p>
 @error('nuevaImagen') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>
 </div>
 </div>

 {{-- Color --}}
 <div>
 <label class="block text-xs font-bold text-titulo mb-1">Color Institucional</label>
 <div class="flex gap-2">
 <input type="color"
 wire:model="color"
 class="h-9 w-12 rounded-xl border border-borde-suave bg-transparent p-0 cursor-pointer">
 <input type="text"
 wire:model="color"
 placeholder="#2F3E5C"
 class="rm-input w-full">
 </div>
 @error('color') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- ── SECCIÓN 3: RESPONSABLE DEL ÁREA ── --}}
 <div class="space-y-4 md:col-span-2 mt-2">
 <h4 class="text-[10px] font-bold text-boton-acento uppercase tracking-widest border-b border-borde-focus pb-1">3. Responsable del Área</h4>
 </div>

 {{-- Responsable (Limitado a usuarios del área en edición; deshabilitado en creación) --}}
 <div class="md:col-span-2">
 <label class="block text-xs font-bold text-titulo mb-1">Asignar Responsable</label>
 @if(!$isEdit)
 <select disabled class="rm-select w-full opacity-60 cursor-not-allowed">
 <option>-- No disponible en creación (El área aún no tiene personal asignado) --</option>
 </select>
 <p class="text-[9px] text-parrafo mt-1.5">
 <i class="ph-bold ph-info mr-0.5"></i> Primero debe crear el área, luego vincular personal en el módulo Usuarios, y finalmente podrá asignarle un responsable.
 </p>
 @elseif(count($responsablesDisponibles) == 0)
 <select disabled class="rm-select w-full opacity-60 cursor-not-allowed">
 <option>-- Sin personal vinculado disponible --</option>
 </select>
 <p class="text-[9px] text-parrafo mt-1.5">
 <i class="ph-bold ph-info mr-0.5"></i> Esta área no cuenta con personal vinculado. Asigne personal a este área desde el panel de Usuarios antes de asignarle un responsable.
 </p>
 @else
 <select wire:model="responsable_id"
 class="rm-input w-full">
 <option value="">-- Sin responsable asignado --</option>
 @foreach($responsablesDisponibles as $resp)
 <option value="{{ $resp->cod_usu }}">{{ $resp->name }}</option>
 @endforeach
 </select>
 @endif
 @error('responsable_id') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>

 {{-- ── SECCIÓN 4: OBSERVACIONES ── --}}
 <div class="space-y-4 md:col-span-2 mt-2">
 <h4 class="text-[10px] font-bold text-boton-acento uppercase tracking-widest border-b border-borde-focus pb-1">4. Observaciones</h4>
 </div>

 {{-- Observaciones internas --}}
 <div class="md:col-span-2">
 <label class="block text-xs font-bold text-titulo mb-1">Observaciones Internas</label>
 <textarea wire:model="observaciones"
 rows="2"
 placeholder="Observaciones de administración interna..."
 class="rm-input w-full"></textarea>
 @error('observaciones') <span class="text-[10px] font-bold text-red-600">{{ $message }}</span> @enderror
 </div>
 </div>

 {{-- Botones de Acción --}}
 <footer class="flex items-center justify-end gap-2 pt-4 border-t border-borde-suave">
 <button type="button"
 wire:click="cerrarFormulario"
 class="rounded-full border-2 border-borde-suave bg-fondo-card px-5 py-2 text-xs font-bold text-meta shadow-sm transition hover:bg-fondo-panel">
 Cancelar
 </button>
 <button type="submit"
 class="rounded-full bg-boton-acento px-6 py-2 text-xs font-bold text-inverso shadow-lg transition hover:bg-fondo-panel">
 Guardar cambios
 </button>
 </footer>
 </form>
 </div>
 </div>
 @endif

 {{-- MODAL / PANEL DE REPORTES INTERACTIVOS --}}
 @if($mostrarReportes && $reporteData)
 <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto print:relative print:z-auto print:p-0" role="dialog" aria-modal="true">
 {{-- Fondo Oscuro no imprimible --}}
 <div class="rm-modal-overlay no-print" wire:click="cerrarReportes"></div>

 {{-- Contenedor del Modal --}}
 <div class="relative w-full max-w-4xl rounded-[2.2rem] rm-modal-panel p-6 transform transition-all duration-300 print:shadow-none print:border-none print:p-0 print:m-0 print:max-w-none">
 
 {{-- Encabezado Modal --}}
 <header class="flex items-center justify-between pb-4 border-b border-borde-suave no-print">
 <div>
 <h3 class="text-xl font-extrabold text-titulo">
 {{ $reporteTipo === 'general' ? 'REPORTES DE ÁREAS INSTITUCIONALES' : 'REPORTE ESPECÍFICO DEL ÁREA' }}
 </h3>
 <p class="text-xs font-semibold text-parrafo mt-0.5">Genere y exporte reportes técnicos estructurados de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.</p>
 </div>
 <div class="flex gap-2">
 @if($reporteTipo === 'general')
 <a href="{{ route('admin.areas-institucionales.reportes.general.pdf') }}"
 target="_blank"
 class="flex h-8 items-center gap-2 rounded-lg bg-boton-acento px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-file-pdf"></i>
 Exportar PDF
 </a>
 <a href="{{ route('admin.areas-institucionales.reportes.general.excel') }}"
 target="_blank"
 class="flex h-8 items-center gap-2 rounded-lg bg-estado-exitoBg px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-file-xls"></i>
 Exportar Excel
 </a>
 <a href="{{ route('admin.areas-institucionales.reportes.general.csv') }}"
 target="_blank"
 class="flex h-8 items-center gap-2 rounded-lg bg-fondo-panel px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-file-csv"></i>
 Exportar CSV
 </a>
 <button type="button"
 wire:click="imprimirReporteGeneral"
 class="flex h-8 items-center gap-2 rounded-lg bg-fondo-panel px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-printer"></i>
 Imprimir
 </button>
 @elseif($reporteTipo === 'especifico' && isset($reporteData['area']))
 <a href="{{ route('admin.areas-institucionales.reportes.area.pdf', $reporteData['area']['cod_area']) }}"
 target="_blank"
 class="flex h-8 items-center gap-2 rounded-lg bg-boton-acento px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-file-pdf"></i>
 Exportar PDF
 </a>
 <a href="{{ route('admin.areas-institucionales.reportes.area.excel', $reporteData['area']['cod_area']) }}"
 target="_blank"
 class="flex h-8 items-center gap-2 rounded-lg bg-estado-exitoBg px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-file-xls"></i>
 Exportar Excel
 </a>
 <button type="button"
 wire:click="imprimirReporteArea('{{ $reporteData['area']['cod_area'] }}')"
 class="flex h-8 items-center gap-2 rounded-lg bg-fondo-panel px-3 text-xs font-bold text-inverso hover:bg-fondo-panel transition duration-200">
 <i class="ph-bold ph-printer"></i>
 Imprimir
 </button>
 @endif
 <button type="button"
 wire:click="cerrarReportes"
 class="flex h-8 w-8 items-center justify-center rounded-full bg-fondo-panel text-meta hover:bg-boton-acento hover:text-inverso transition duration-200">
 <i class="ph-bold ph-x"></i>
 </button>
 </div>
 </header>

 {{-- Area visual imprimible --}}
 <div class="mt-6 p-6 rounded-2xl border border-borde-suave max-h-[65vh] overflow-y-auto bg-fondo-card watermark-container print:max-h-none print:border-none print:p-0 print:m-0"
 x-data="{
 generalChartUsr: null,
 generalChartType: null,
 generalChartActInact: null,
 generalChartEv: null,
 generalChartRanking: null,
 areaActiveChart: null,
 areaRolChart: null,
 areaLineChart: null,
 initReportCharts() {
 const type = '{{ $reporteTipo }}';
 
 // Destroy everything first
 if (this.generalChartUsr) this.generalChartUsr.destroy();
 if (this.generalChartType) this.generalChartType.destroy();
 if (this.generalChartActInact) this.generalChartActInact.destroy();
 if (this.generalChartEv) this.generalChartEv.destroy();
 if (this.generalChartRanking) this.generalChartRanking.destroy();
 if (this.areaActiveChart) this.areaActiveChart.destroy();
 if (this.areaRolChart) this.areaRolChart.destroy();
 if (this.areaLineChart) this.areaLineChart.destroy();

 if (type === 'general') {
 // Chart: Usuarios por area
 const usrLabels = @js(array_keys($this->obtenerDatosGraficoUsuariosPorArea()));
 const usrValues = @js(array_values($this->obtenerDatosGraficoUsuariosPorArea()));
 const ctxUsr = this.$refs.canvasUsr;
 if (ctxUsr && usrLabels.length > 0) {
 this.generalChartUsr = new Chart(ctxUsr, {
 type: 'bar',
 data: {
 labels: usrLabels,
 datasets: [{
 data: usrValues,
 backgroundColor: '#2F3E5C',
 borderRadius: 6
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } },
 scales: {
 y: { beginAtZero: true, grid: { color: 'rgba(47,62,92,0.05)' } },
 x: { grid: { display: false } }
 }
 }
 });
 }

 // Chart: Areas por tipo
 const typeLabels = @js(array_keys($this->obtenerDatosGraficoAreasPorTipo()));
 const typeValues = @js(array_values($this->obtenerDatosGraficoAreasPorTipo()));
 const ctxType = this.$refs.canvasType;
 if (ctxType && typeLabels.length > 0) {
 this.generalChartType = new Chart(ctxType, {
 type: 'doughnut',
 data: {
 labels: typeLabels,
 datasets: [{
 data: typeValues,
 backgroundColor: ['#2F3E5C', '#E27D60', '#8DA280', '#967B66', '#5E6599'],
 borderWidth: 0
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } }
 }
 }
 });
 }

 // Chart: Activos vs Inactivos stacked
 const actInact = @js($this->obtenerDatosGraficoActivosInactivosPorArea());
 const ctxActInact = this.$refs.canvasActInact;
 if (ctxActInact && actInact.labels && actInact.labels.length > 0) {
 this.generalChartActInact = new Chart(ctxActInact, {
 type: 'bar',
 data: {
 labels: actInact.labels,
 datasets: [
 { label: 'Activos', data: actInact.activos, backgroundColor: '#8DA280' },
 { label: 'Inactivos', data: actInact.inactivos, backgroundColor: '#E27D60' }
 ]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 scales: {
 x: { stacked: true, grid: { display: false } },
 y: { stacked: true }
 }
 }
 });
 }

 // Chart: Evolucion mensual global
 const ev = @js($this->obtenerDatosGraficoEvolucionMensual());
 const ctxEv = this.$refs.canvasEv;
 if (ctxEv && ev.labels && ev.labels.length >= 2) {
 this.generalChartEv = new Chart(ctxEv, {
 type: 'line',
 data: {
 labels: ev.labels,
 datasets: [{
 data: ev.data,
 borderColor: '#E27D60',
 backgroundColor: 'rgba(226,125,96,0.1)',
 borderWidth: 3,
 fill: true,
 tension: 0.3
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } }
 }
 });
 }
 // Chart: Ranking Top 5
 const rankData = @js($this->obtenerDatosGraficoRankingAreas());
 const ctxRank = this.$refs.canvasRank;
 if (ctxRank && rankData.labels && rankData.labels.length > 0) {
 this.generalChartRanking = new Chart(ctxRank, {
 type: 'bar',
 data: {
 labels: rankData.labels,
 datasets: [{
 data: rankData.data,
 backgroundColor: '#E27D60',
 borderRadius: 6
 }]
 },
 options: {
 indexAxis: 'y',
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } },
 scales: {
 x: { beginAtZero: true, grid: { color: 'rgba(47,62,92,0.05)' } },
 y: { grid: { display: false } }
 }
 }
 });
 }
 } else if (type === 'especifico') {
 const total = {{ $reporteData['totalUsuarios'] ?? 0 }};
 const activos = {{ $reporteData['usuariosActivos'] ?? 0 }};
 const inactivos = {{ $reporteData['usuariosInactivos'] ?? 0 }};
 const codArea = '{{ $reporteData['area']['cod_area'] ?? '' }}';

 if (total > 0) {
 const ctxAreaActive = this.$refs.canvasAreaActive;
 if (ctxAreaActive) {
 this.areaActiveChart = new Chart(ctxAreaActive, {
 type: 'doughnut',
 data: {
 labels: ['Activos', 'Inactivos'],
 datasets: [{
 data: [activos, inactivos],
 backgroundColor: ['#8DA280', '#E27D60'],
 borderWidth: 0
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 cutout: '70%',
 plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
 }
 });
 }

 const rolesLabels = @js(isset($reporteData['roles']) ? array_keys($reporteData['roles']) : []);
 const rolesValues = @js(isset($reporteData['roles']) ? array_values($reporteData['roles']) : []);
 const ctxAreaRol = this.$refs.canvasAreaRol;
 if (ctxAreaRol && rolesLabels.length > 0) {
 this.areaRolChart = new Chart(ctxAreaRol, {
 type: 'bar',
 data: {
 labels: rolesLabels,
 datasets: [{
 data: rolesValues,
 backgroundColor: '#2F3E5C',
 borderRadius: 6
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } }
 }
 });
 }
 }

 const evAreaLabels = @js(isset($reporteData['area']['cod_area']) ? array_keys($this->obtenerDatosGraficoEvolucionArea($reporteData['area']['cod_area'])) : []);
 const evAreaValues = @js(isset($reporteData['area']['cod_area']) ? array_values($this->obtenerDatosGraficoEvolucionArea($reporteData['area']['cod_area'])) : []);
 const ctxAreaLine = this.$refs.canvasAreaLine;
 if (ctxAreaLine && evAreaLabels.length >= 2) {
 this.areaLineChart = new Chart(ctxAreaLine, {
 type: 'line',
 data: {
 labels: evAreaLabels,
 datasets: [{
 data: evAreaValues,
 borderColor: '#E27D60',
 backgroundColor: 'rgba(226,125,96,0.1)',
 borderWidth: 3,
 fill: true,
 tension: 0.3
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: { legend: { display: false } }
 }
 });
 }
 }
 }
 }"
 x-init="$nextTick(() => initReportCharts())">

 {{-- Marca de Agua --}}
 <div class="watermark-bg">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>

 @if($reporteTipo === 'general')
 {{-- ── REPORTE GENERAL ── --}}
 <div class="space-y-6">
 {{-- Membrete --}}
 <div class="text-center pb-4 border-b border-borde-suave">
 <h2 class="text-2xl font-black text-titulo tracking-wide">REPORTE GENERAL DE ÁREAS INSTITUCIONALES</h2>
 <p class="text-xs font-bold text-parrafo uppercase tracking-widest mt-1">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Sistema RememberMind</p>
 <p class="text-[10px] text-parrafo mt-2 font-bold">Fecha: {{ $reporteData['fecha'] }} | Generado por: {{ $reporteData['usuario'] }}</p>
 </div>

 {{-- Resumen Ejecutivo en Reporte --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">RESUMEN EJECUTIVO ORGANIZACIONAL</h4>
 <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
 <div class="bg-fondo-panel p-4 rounded-2xl border border-borde-suave text-center shadow-sm">
 <p class="text-2xl font-black text-boton-acento">{{ $reporteData['totalAreas'] }}</p>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider mt-1">Total Áreas</p>
 <p class="text-[8px] font-semibold text-meta mt-0.5">Activas: {{ $reporteData['areasActivas'] }} | Inactivas: {{ $reporteData['areasInactivas'] }}</p>
 </div>
 <div class="bg-fondo-panel p-4 rounded-2xl border border-borde-suave text-center shadow-sm">
 <p class="text-2xl font-black text-estado-exito">{{ $reporteData['usuariosVinculados'] }}</p>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider mt-1">Personal Asignado</p>
 <p class="text-[8px] font-semibold text-meta mt-0.5">Activos: {{ $reporteData['usuariosActivosVinculados'] }} | Inactivos: {{ $reporteData['usuariosInactivosVinculados'] }}</p>
 </div>
 <div class="bg-fondo-panel p-4 rounded-2xl border border-borde-suave text-center shadow-sm">
 <p class="text-2xl font-black text-titulo truncate">{{ $reporteData['areaMasUsuariosNombre'] }}</p>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider mt-1">Área con Más Usuarios</p>
 <p class="text-[8px] font-semibold text-meta mt-0.5">Total personal adscrito: {{ $reporteData['areaMasUsuariosCount'] }}</p>
 </div>
 <div class="bg-fondo-panel p-4 rounded-2xl border border-borde-suave text-center shadow-sm">
 <p class="text-2xl font-black text-estado-exito">{{ $reporteData['porcentajeAreasResponsable'] }}%</p>
 <p class="text-[9px] font-bold text-parrafo uppercase tracking-wider mt-1">Cobertura Liderazgo</p>
 <p class="text-[8px] font-semibold text-meta mt-0.5">Sin responsable: {{ $reporteData['areasSinResponsable'] }} áreas</p>
 </div>
 </div>
 </div>

 {{-- GRÁFICAS GENERALES --}}
 <div class="space-y-4 no-print">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">GRÁFICAS ANALÍTICAS GENERALES</h4>
 
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 {{-- Barras: usuarios por area --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Personal por Área Institucional</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasUsr"></canvas>
 </div>
 </div>

 {{-- Dona: areas por tipo --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Distribución de Áreas por Tipo</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasType"></canvas>
 </div>
 </div>

 {{-- Barras apiladas: activos vs inactivos por area --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Personal Activo vs Inactivo por Área</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasActInact"></canvas>
 </div>
 </div>

 {{-- Evolucion mensual --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Evolución Mensual Asignación General</h5>
 @if(count($this->obtenerDatosGraficoEvolucionMensual()['labels']) >= 2)
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasEv"></canvas>
 </div>
 @else
 <div class="h-44 w-full flex items-center justify-center text-xs font-semibold text-parrafo bg-fondo-panel rounded-xl">
 No hay datos históricos suficientes para mostrar evolución.
 </div>
 @endif
 </div>

 {{-- Ranking Top 5 --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm col-span-1 md:col-span-2">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Top 5 Áreas con Mayor Número de Personal</h5>
 <div class="relative h-56 w-full">
 <canvas x-ref="canvasRank"></canvas>
 </div>
 </div>
 </div>
 </div>

 {{-- Tabla de Áreas --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">INFORMACIÓN GENERAL</h4>
 <table class="w-full text-left text-xs border border-borde-suave rounded-xl overflow-hidden bg-fondo-card">
 <thead>
 <tr class="bg-fondo-app text-titulo font-black border-b border-borde-suave">
 <th class="p-3">Área Institucional</th>
 <th class="p-3">Tipo</th>
 <th class="p-3">Responsable del Área</th>
 <th class="p-3 text-center">Personal</th>
 <th class="p-3 text-center">Estado</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/30">
 @foreach($reporteData['areas'] as $rep)
 <tr class="hover:bg-fondo-panel">
 <td class="p-3 font-black text-titulo">{{ $rep['nombre'] }}</td>
 <td class="p-3">{{ $rep['tipo_area'] }}</td>
 <td class="p-3">
 @if($rep['responsable'])
 {{ $rep['responsable']['nombres'] }} {{ $rep['responsable']['ap_paterno'] }}
 @else
 <span class="text-red-500 font-bold italic">Sin Responsable</span>
 @endif
 </td>
 <td class="p-3 text-center font-bold text-boton-acento">{{ $rep['usuarios_count'] }}</td>
 <td class="p-3 text-center font-bold text-[10px] {{ $rep['estado'] === 'ACTIVA' ? 'text-estado-exito' : 'text-red-600' }}">{{ $rep['estado'] }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>

 {{-- Alertas y Observaciones --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">OBSERVACIONES</h4>
 <div class="bg-fondo-panel border-l-4 border-borde-focus p-4 rounded-r-xl text-xs font-semibold text-meta space-y-2 leading-relaxed">
 <p><strong>Estatus de Liderazgo:</strong> 
 @php $sinResp = collect($reporteData['areas'])->where('responsable_id', null); @endphp
 @if($sinResp->count() > 0)
 Se detectan {{ $sinResp->count() }} áreas sin un responsable asignado ({{ implode(', ', $sinResp->pluck('nombre')->toArray()) }}). Se sugiere regularizar la asignación a la brevedad.
 @else
 Todas las áreas operativas activas disponen de un responsable adscrito correctamente.
 @endif
 </p>
 <p><strong>Estatus de Personal:</strong>
 @php $sinPers = collect($reporteData['areas'])->where('usuarios_count', 0); @endphp
 @if($sinPers->count() > 0)
 Se registran {{ $sinPers->count() }} áreas sin personal vinculado ({{ implode(', ', $sinPers->pluck('nombre')->toArray()) }}).
 @else
 Todas las áreas disponen de al menos un miembro del personal vinculado.
 @endif
 </p>
 </div>
 </div>
 </div>
 @elseif($reporteTipo === 'especifico' && isset($reporteData['area']))
 {{-- ── REPORTE ESPECÍFICO DEL ÁREA ── --}}
 <div class="space-y-6">
 {{-- Membrete --}}
 <div class="text-center pb-4 border-b border-borde-suave">
 <h2 class="text-2xl font-black text-titulo uppercase tracking-wide">REPORTE DEL ÁREA</h2>
 <h3 class="text-lg font-extrabold text-boton-acento mt-1">{{ $reporteData['area']['nombre'] }}</h3>
 <p class="text-xs font-bold text-parrafo uppercase tracking-widest mt-0.5">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Sistema RememberMind</p>
 <p class="text-[10px] text-parrafo mt-2 font-bold">Fecha: {{ $reporteData['fecha'] }} | Generado por: {{ $reporteData['usuario'] }}</p>
 </div>

 {{-- Datos Generales del Área --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">INFORMACIÓN GENERAL</h4>
 <div class="bg-fondo-panel p-4 rounded-xl border border-borde-suave space-y-2 text-xs font-semibold text-titulo">
 <p><strong>Tipo de Área:</strong> {{ $reporteData['area']['tipo_area'] }}</p>
 <p><strong>Estado:</strong> {{ $reporteData['area']['estado'] }}</p>
 <p><strong>Responsable:</strong> 
 @if($reporteData['area']['responsable'])
 {{ $reporteData['area']['responsable']['name'] }}
 @else
 <span class="text-red-500 font-bold italic">Sin responsable asignado</span>
 @endif
 </p>
 <p class="leading-relaxed"><strong>Descripción Operativa:</strong> {{ $reporteData['area']['descripcion'] }}</p>
 @if($reporteData['area']['observaciones'])
 <p class="italic text-parrafo"><strong>Observaciones Internas:</strong>"{{ $reporteData['area']['observaciones'] }}"</p>
 @endif
 <p><strong>Última actualización:</strong> {{ \Carbon\Carbon::parse($reporteData['area']['updated_at'])->format('d/m/Y H:i') }}</p>
 </div>
 </div>

 {{-- Resumen Estadístico --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">RESUMEN ESTADÍSTICO</h4>
 <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
 <div class="bg-fondo-app p-3 rounded-xl border border-borde-suave">
 <p class="text-xl font-extrabold text-titulo">{{ $reporteData['totalUsuarios'] }}</p>
 <p class="text-[8px] font-black text-parrafo uppercase tracking-wider">Total Personal</p>
 </div>
 <div class="bg-fondo-app p-3 rounded-xl border border-borde-suave">
 <p class="text-xl font-extrabold text-estado-exito">{{ $reporteData['usuariosActivos'] }}</p>
 <p class="text-[8px] font-black text-parrafo uppercase tracking-wider">Personal Activo</p>
 </div>
 <div class="bg-fondo-app p-3 rounded-xl border border-borde-suave">
 <p class="text-xl font-extrabold text-red-600">{{ $reporteData['usuariosInactivos'] }}</p>
 <p class="text-[8px] font-black text-parrafo uppercase tracking-wider">Personal Inactivo</p>
 </div>
 <div class="bg-fondo-app p-3 rounded-xl border border-borde-suave">
 <p class="text-xl font-extrabold text-boton-acento">{{ $reporteData['porcentajeActivos'] }}%</p>
 <p class="text-[8px] font-black text-parrafo uppercase tracking-wider">% Activos</p>
 </div>
 </div>
 </div>

 {{-- GRÁFICAS DE ÁREA EN REPORTE --}}
 <div class="space-y-4 no-print">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">GRÁFICAS DEL ÁREA</h4>
 
 @if($reporteData['totalUsuarios'] > 0)
 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 {{-- Dona activos vs inactivos --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Usuarios Activos vs Inactivos</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasAreaActive"></canvas>
 </div>
 </div>

 {{-- Barras usuarios por rol --}}
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Usuarios por Rol</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasAreaRol"></canvas>
 </div>
 </div>
 </div>
 @endif

 {{-- Evolución asignación --}}
 @if(count($this->obtenerDatosGraficoEvolucionArea($reporteData['area']['cod_area'])) >= 2)
 <div class="bg-fondo-card p-4 rounded-xl border border-borde-suave shadow-sm">
 <h5 class="text-[10px] font-bold text-titulo mb-2 uppercase tracking-wide">Evolución Mensual Asignación</h5>
 <div class="relative h-44 w-full">
 <canvas x-ref="canvasAreaLine"></canvas>
 </div>
 </div>
 @endif
 </div>

 {{-- Lista de Usuarios Vinculados --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">PERSONAL VINCULADO</h4>
 @if(count($reporteData['area']['usuarios']) > 0)
 <table class="w-full text-left text-xs border border-borde-suave rounded-xl overflow-hidden bg-fondo-card">
 <thead>
 <tr class="bg-fondo-app text-titulo font-black border-b border-borde-suave">
 <th class="p-3" style="width: 30%;">Nombre Completo</th>
 <th class="p-3" style="width: 15%;">Rol</th>
 <th class="p-3" style="width: 25%;">Cargo/Especialidad</th>
 <th class="p-3 text-center" style="width: 15%;">Estado</th>
 <th class="p-3 text-center" style="width: 15%;">Último Acceso</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-[#C7B5A3]/30">
 @foreach($reporteData['area']['usuarios'] as $u)
 @php
 $cargoEspecialidad = 'Sin Asignar';
 if ($u->personalSalud && $u->personalSalud->especialidad) {
 $cargoEspecialidad = $u->personalSalud->especialidad->nombre;
 } elseif ($u->personalAdmin) {
 $cargoEspecialidad = $u->personalAdmin->cargoAdmin?->nombre ?? $u->personalAdmin->cargo ?? 'Personal Administrativo';
 }
 $rolName = $u->getRoleNames()->first() ?? 'Sin Rol';
 $rolLimpio = strtoupper(str_replace('_', ' ', $rolName));
 @endphp
 <tr class="hover:bg-fondo-panel">
 <td class="p-3 font-black text-titulo">{{ $u['name'] }}</td>
 <td class="p-3 text-parrafo font-bold">{{ $rolLimpio }}</td>
 <td class="p-3 text-titulo">{{ $cargoEspecialidad }}</td>
 <td class="p-3 text-center font-bold text-[10px]">
 <span class="rounded-full px-2.5 py-0.5 border {{ in_array($u->estado, ['ACTIVO', 1, '1']) ? 'bg-estado-exitoBg text-estado-exito border-estado-exitoBorde' : 'bg-red-50 text-red-600 border-red-100' }}">
 {{ in_array($u->estado, ['ACTIVO', 1, '1']) ? 'ACTIVO' : 'INACTIVO' }}
 </span>
 </td>
 <td class="p-3 text-center text-meta">{{ $u['ultimo_acceso'] ? \Carbon\Carbon::parse($u['ultimo_acceso'])->format('d/m/Y H:i') : 'Nunca' }}</td>
 </tr>
 @endforeach
 </tbody>
 </table>
 @else
 <div class="rounded-xl border border-dashed border-borde-suave p-6 text-center text-xs font-semibold text-parrafo bg-fondo-panel/50">
 Esta área aún no tiene usuarios asignados.
 </div>
 @endif
 </div>

 {{-- Observaciones del área --}}
 <div class="space-y-2">
 <h4 class="text-[11px] font-bold uppercase tracking-[0.18em] text-apoyo">OBSERVACIONES</h4>
 <div class="bg-fondo-panel border-l-4 border-borde-focus p-4 rounded-r-xl text-xs font-semibold text-meta leading-relaxed">
 @if(count($reporteData['area']['usuarios']) == 0)
 <p class="mb-1"><strong>Alerta del Personal:</strong> Esta área aún no tiene usuarios asignados.</p>
 @endif
 @if(!$reporteData['area']['responsable_id'])
 <p class="mb-1"><strong>Alerta de Responsabilidad:</strong> Esta área no cuenta con responsable asignado.</p>
 @endif
 @if(count($this->obtenerDatosGraficoEvolucionArea($reporteData['area']['cod_area'])) < 2)
 <p class="mb-1"><strong>Alerta Histórica:</strong> No hay datos históricos suficientes para graficar evolución.</p>
 @endif
 <p class="mt-2 text-[10px] text-parrafo">El personal listado está plenamente adscrito a las operaciones funcionales del área descrita en este documento.</p>
 </div>
 </div>
 </div>
 @endif
 </div>

 {{-- Botón Cerrar Modal Reportes --}}
 <footer class="flex items-center justify-end mt-4 pt-4 border-t border-borde-suave no-print">
 <button type="button"
 wire:click="cerrarReportes"
 class="rounded-full bg-boton-principal px-6 py-2 text-xs font-bold text-inverso shadow-lg transition hover:bg-fondo-panel">
 Cerrar panel
 </button>
 </footer>
 </div>
 </div>
 @endif
</div>
