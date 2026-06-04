<div class="space-y-6">
    <!-- Header Principal -->
    <div class="rm-page-header lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner">
                <i class="ph-fill ph-users-three text-4xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="rm-badge-info bg-estado-info/10 text-estado-info border-estado-info/20 px-2 py-0.5 text-[10px]">Gestión del Sistema</span>
                </div>
                <h2 class="rm-section-title">
                    Personal Institucional
                </h2>
                <p class="rm-section-subtitle max-w-xl">
                    Gestión centralizada del equipo médico, administrativo, disponibilidad y documentación legal del centro.
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button wire:click="$refresh" class="bg-white border border-borde text-texto hover:bg-fondo hover:text-titulo hover:border-borde-hover transition-colors rounded-xl h-10 w-10 flex items-center justify-center tooltip-btn shadow-sm" title="Actualizar">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
            </button>
            <button class="bg-white border border-borde text-texto hover:bg-fondo hover:text-titulo hover:border-borde-hover transition-colors rounded-xl h-10 px-4 flex items-center gap-2 font-bold text-sm shadow-sm">
                <i class="ph-bold ph-file-pdf text-lg"></i> Exportar PDF
            </button>
            <button class="rm-btn-success h-10">
                <i class="ph-bold ph-calendar-plus text-lg"></i> Asignar horario
            </button>
            @can('usuarios.crear')
            <button wire:click="abrirModalNuevo" class="rm-btn-primary h-10">
                <i class="ph-bold ph-plus text-lg"></i>
                <span>Registrar personal</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Pestañas Principales (Nav) -->
    <div class="flex overflow-x-auto border-b border-borde scrollbar-hide">
        <div class="flex space-x-6 px-2">
            <button wire:click="setTab('resumen')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'resumen' ? 'text-boton-acento border-boton-acento' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'resumen' ? 'ph-fill' : 'ph-bold' }} ph-squares-four text-lg"></i> Resumen
            </button>
            <button wire:click="setTab('salud')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'salud' ? 'text-estado-info border-estado-info' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'salud' ? 'ph-fill' : 'ph-bold' }} ph-stethoscope text-lg"></i> Personal de salud
            </button>
            <button wire:click="setTab('admin')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'admin' ? 'text-estado-advertencia border-estado-advertencia' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'admin' ? 'ph-fill' : 'ph-bold' }} ph-desktop text-lg"></i> Personal administrativo
            </button>
            <button wire:click="setTab('horarios')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'horarios' ? 'text-boton-acento border-boton-acento' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'horarios' ? 'ph-fill' : 'ph-bold' }} ph-calendar-check text-lg"></i> Horarios y turnos
            </button>
            <button wire:click="setTab('documentacion')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'documentacion' ? 'text-boton-acento border-boton-acento' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'documentacion' ? 'ph-fill' : 'ph-bold' }} ph-folders text-lg"></i> Documentación
            </button>
            <button wire:click="setTab('disponibilidad')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'disponibilidad' ? 'text-boton-acento border-boton-acento' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'disponibilidad' ? 'ph-fill' : 'ph-bold' }} ph-clock-user text-lg"></i> Disponibilidad
            </button>
            <button wire:click="setTab('reportes')" class="whitespace-nowrap pb-3 text-sm font-bold transition-all border-b-2 flex items-center gap-2 {{ $tabActiva === 'reportes' ? 'text-boton-acento border-boton-acento' : 'text-apoyo border-transparent hover:text-titulo' }}">
                <i class="{{ $tabActiva === 'reportes' ? 'ph-fill' : 'ph-bold' }} ph-chart-bar text-lg"></i> Reportes
            </button>
        </div>
    </div>

    <!-- Contenido Dinámico según Pestaña -->
    <div class="min-h-[400px]">
        @if(in_array($tabActiva, ['resumen', 'salud', 'admin']))
            @if($tabActiva === 'resumen')
            <div class="space-y-5 mb-8">
                <!-- Bloque Superior: KPIs Principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Total Personal (Crema Cálido) -->
                    <div class="rm-card border border-[#E9E0D7] bg-[#FDFBF7] p-5 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 text-[#D3C3B3]/20 group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-500">
                            <i class="ph-fill ph-users-three text-7xl"></i>
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between gap-4">
                            <div class="flex items-start justify-between">
                                <div class="h-12 w-12 rounded-2xl bg-[#E9E0D7] text-[#8C7A6B] flex items-center justify-center shadow-sm">
                                    <i class="ph-fill ph-users text-2xl"></i>
                                </div>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-[#5C4D40] leading-none tracking-tight mb-2">{{ $estadisticas['total'] }}</div>
                                <h3 class="text-sm font-bold text-[#8C7A6B] uppercase tracking-wider">Total de personal</h3>
                                <p class="text-[11px] text-[#A6978A] mt-1 font-medium">Personal registrado en la institución.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Activo (Verde Salvia Suave) -->
                    <div class="rm-card border border-[#C6D9CE] bg-[#F2F7F4] p-5 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 text-[#3F7D5A]/10 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-500">
                            <i class="ph-fill ph-check-circle text-7xl"></i>
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between gap-4">
                            <div class="flex items-start justify-between">
                                <div class="h-12 w-12 rounded-2xl bg-[#D6E6DF] text-[#3F7D5A] flex items-center justify-center shadow-sm">
                                    <i class="ph-bold ph-activity text-2xl"></i>
                                </div>
                                <span class="bg-[#3F7D5A] text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wide shadow-sm">Activo</span>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-[#2A523B] leading-none tracking-tight mb-2">{{ $estadisticas['activos'] }}</div>
                                <h3 class="text-sm font-bold text-[#3F7D5A] uppercase tracking-wider">Personal activo</h3>
                                <p class="text-[11px] text-[#558A6E] mt-1 font-medium">Trabajadores habilitados actualmente.</p>
                            </div>
                        </div>
                    </div>

                    <!-- En Turno Actual (Azul Profundo Suave) -->
                    <div class="rm-card border border-[#C5D0E6] bg-[#F0F4FA] p-5 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 text-[#293A59]/10 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-500">
                            <i class="ph-fill ph-clock-user text-7xl"></i>
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between gap-4">
                            <div class="flex items-start justify-between">
                                <div class="h-12 w-12 rounded-2xl bg-[#D9E2F2] text-[#293A59] flex items-center justify-center shadow-sm">
                                    <i class="ph-bold ph-clock-user text-2xl"></i>
                                </div>
                                <span class="bg-[#293A59] text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wide shadow-sm">En Turno</span>
                            </div>
                            <div>
                                <div class="text-4xl font-black text-[#1A2538] leading-none tracking-tight mb-2">{{ $estadisticas['en_turno'] }}</div>
                                <h3 class="text-sm font-bold text-[#293A59] uppercase tracking-wider">En turno actual</h3>
                                <p class="text-[11px] text-[#455A7F] mt-1 font-medium">Personal disponible según horario.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Documentación Pendiente (Terracota Suave) -->
                    <div class="rm-card border border-[#F2CFC4] bg-[#FDF5F2] p-5 shadow-sm relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 text-[#D9795F]/10 group-hover:scale-110 group-hover:rotate-6 transition-transform duration-500">
                            <i class="ph-fill ph-warning-circle text-7xl"></i>
                        </div>
                        <div class="relative z-10 flex flex-col h-full justify-between gap-4">
                            <div class="flex items-start justify-between">
                                <div class="h-12 w-12 rounded-2xl bg-[#F6DDD5] text-[#D9795F] flex items-center justify-center shadow-sm">
                                    <i class="ph-bold ph-folders text-2xl"></i>
                                </div>
                                @if($estadisticas['doc_pendiente'] > 0)
                                <span class="bg-[#D9795F] text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wide shadow-sm flex items-center gap-1"><i class="ph-bold ph-warning"></i> Pendiente</span>
                                @else
                                <span class="bg-[#3F7D5A] text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wide shadow-sm">Al día</span>
                                @endif
                            </div>
                            <div>
                                <div class="text-4xl font-black text-[#A64C35] leading-none tracking-tight mb-2">{{ $estadisticas['doc_pendiente'] }}</div>
                                <h3 class="text-sm font-bold text-[#D9795F] uppercase tracking-wider">Documentación pendiente</h3>
                                <p class="text-[11px] text-[#B86852] mt-1 font-medium">Registros con documentos faltantes.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bloque Secundario: Equipo y Detalles -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-borde">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-modulo-salud-fondo text-modulo-salud flex items-center justify-center group-hover:bg-modulo-salud group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-stethoscope text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-titulo leading-none">{{ $estadisticas['medicos'] }}</div>
                            <div class="text-[11px] font-bold text-apoyo uppercase tracking-wider mt-1">Médicos generales</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-borde">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-modulo-salud-fondo text-modulo-salud flex items-center justify-center group-hover:bg-modulo-salud group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-first-aid text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-titulo leading-none">{{ $estadisticas['enfermeros'] }}</div>
                            <div class="text-[11px] font-bold text-apoyo uppercase tracking-wider mt-1">Enfermeros</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-borde">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-modulo-cognitivo-fondo text-modulo-cognitivo-texto flex items-center justify-center group-hover:bg-modulo-cognitivo group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-brain text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-titulo leading-none">{{ $estadisticas['psicologos'] }}</div>
                            <div class="text-[11px] font-bold text-apoyo uppercase tracking-wider mt-1">Psicólogos</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-borde">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-modulo-adulto-mayor-fondo text-modulo-adulto-mayor flex items-center justify-center group-hover:bg-modulo-adulto-mayor group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-person-arms-spread text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-titulo leading-none">{{ $estadisticas['fisioterapeutas'] }}</div>
                            <div class="text-[11px] font-bold text-apoyo uppercase tracking-wider mt-1">Fisioterapeutas</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-borde">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-modulo-voluntarios-fondo text-modulo-voluntarios-texto flex items-center justify-center group-hover:bg-modulo-voluntarios group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-apple-logo text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-titulo leading-none">{{ $estadisticas['nutricionistas'] }}</div>
                            <div class="text-[11px] font-bold text-apoyo uppercase tracking-wider mt-1">Nutricionistas</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-borde">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-modulo-administrativo-fondo text-modulo-administrativo flex items-center justify-center group-hover:bg-modulo-administrativo group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-desktop text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-titulo leading-none">{{ $estadisticas['admin'] }}</div>
                            <div class="text-[11px] font-bold text-apoyo uppercase tracking-wider mt-1">Administrativos</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-[#FDECD8] bg-[#FFF8F0]">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-[#FDECD8] text-[#E9A05F] flex items-center justify-center group-hover:bg-[#E9A05F] group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-prohibit text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-[#B0733E] leading-none">{{ $estadisticas['suspendidos'] }}</div>
                            <div class="text-[11px] font-bold text-[#E9A05F] uppercase tracking-wider mt-1">Suspendidos</div>
                        </div>
                    </div>
                    <div class="rm-card p-4 flex items-center gap-4 group cursor-default shadow-sm border border-[#E5E7EB] bg-[#F9FAFB]">
                        <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-[#E5E7EB] text-[#6B7280] flex items-center justify-center group-hover:bg-[#6B7280] group-hover:text-white transition-colors shadow-sm"><i class="ph-fill ph-bed text-xl"></i></div>
                        <div>
                            <div class="text-2xl font-black text-[#374151] leading-none">{{ $estadisticas['fuera_turno'] }}</div>
                            <div class="text-[11px] font-bold text-[#6B7280] uppercase tracking-wider mt-1">Fuera de turno</div>
                        </div>
                    </div>
                </div>

                <!-- Gráficas (Chart.js via Alpine) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- 1. Dona: Distribución por áreas -->
                    <div class="rm-chart-panel p-5 h-[320px] overflow-hidden flex flex-col border border-borde shadow-sm bg-white rounded-2xl">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-titulo flex items-center gap-2"><i class="ph-fill ph-chart-pie-slice text-boton-acento"></i> Distribución de Áreas</h4>
                        </div>
                        <div class="flex-1 min-h-0 relative w-full flex justify-center" x-data="{
                            hasData: {{ collect($chartData['area_data'])->sum() > 0 ? 'true' : 'false' }},
                            init() {
                                if(this.hasData) {
                                    new Chart(this.$refs.chart, {
                                        type: 'doughnut',
                                        data: {
                                            labels: {{ json_encode($chartData['area_labels']) }},
                                            datasets: [{
                                                data: {{ json_encode($chartData['area_data']) }},
                                                backgroundColor: ['#3F7D5A', '#E28B70', '#CDBEAF'],
                                                borderWidth: 0,
                                                hoverOffset: 4
                                            }]
                                        },
                                        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 16 }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, boxWidth: 8, font: { family: 'Outfit', size: 10, weight: '500' } } } }, cutout: '62%' }
                                    });
                                }
                            }
                        }">
                            <template x-if="hasData"><canvas x-ref="chart" class="w-full h-full"></canvas></template>
                            <template x-if="!hasData">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                    <i class="ph-fill ph-chart-pie-slice text-4xl mb-2 opacity-50"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">Sin datos de áreas</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 2. Barras: Estado laboral -->
                    <div class="rm-chart-panel p-5 h-[320px] overflow-hidden flex flex-col border border-borde shadow-sm bg-white rounded-2xl">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-titulo flex items-center gap-2"><i class="ph-fill ph-chart-bar text-estado-info"></i> Estado Laboral</h4>
                        </div>
                        <div class="flex-1 min-h-0 relative w-full flex justify-center" x-data="{
                            hasData: {{ collect($chartData['estado_data'])->sum() > 0 ? 'true' : 'false' }},
                            init() {
                                if(this.hasData) {
                                    new Chart(this.$refs.chart, {
                                        type: 'bar',
                                        data: {
                                            labels: {{ json_encode($chartData['estado_labels']) }},
                                            datasets: [{
                                                label: 'Personal',
                                                data: {{ json_encode($chartData['estado_data']) }},
                                                backgroundColor: ['#3F7D5A', '#928C84', '#E9A05F', '#D9795F'],
                                                borderRadius: 4
                                            }]
                                        },
                                        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 16 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: '#f3f4f6' }, ticks: { font: { family: 'Outfit', size: 10 } } }, x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 10, weight: '600' } } } } }
                                    });
                                }
                            }
                        }">
                            <template x-if="hasData"><canvas x-ref="chart" class="w-full h-full"></canvas></template>
                            <template x-if="!hasData">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                    <i class="ph-fill ph-chart-bar text-4xl mb-2 opacity-50"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">Sin datos laborales</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 3. Barras: Enfermeros por Turno -->
                    <div class="rm-chart-panel p-5 h-[320px] overflow-hidden flex flex-col border border-borde shadow-sm bg-white rounded-2xl">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-titulo flex items-center gap-2"><i class="ph-fill ph-clock text-modulo-salud"></i> Enfermeros por Turno</h4>
                        </div>
                        <div class="flex-1 min-h-0 relative w-full flex justify-center" x-data="{
                            hasData: {{ collect($chartData['turno_data'])->sum() > 0 ? 'true' : 'false' }},
                            init() {
                                if(this.hasData) {
                                    new Chart(this.$refs.chart, {
                                        type: 'bar',
                                        data: {
                                            labels: {{ json_encode($chartData['turno_labels']) }},
                                            datasets: [{
                                                label: 'Enfermeros',
                                                data: {{ json_encode($chartData['turno_data']) }},
                                                backgroundColor: '#7FA587',
                                                borderRadius: 4
                                            }]
                                        },
                                        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 16 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, border: {display: false}, grid: { color: '#f3f4f6' }, ticks: { stepSize: 1, font: { family: 'Outfit', size: 10 } } }, x: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 10, weight: '600' } } } } }
                                    });
                                }
                            }
                        }">
                            <template x-if="hasData"><canvas x-ref="chart" class="w-full h-full"></canvas></template>
                            <template x-if="!hasData">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                    <i class="ph-fill ph-clock-user text-4xl mb-2 opacity-50"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">Sin turnos asignados</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 4. Dona: Documentación Pendiente por Tipo -->
                    <div class="rm-chart-panel p-5 h-[320px] overflow-hidden flex flex-col border border-borde shadow-sm bg-white rounded-2xl">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-titulo flex items-center gap-2"><i class="ph-fill ph-folders text-estado-peligro"></i> Docs. Pendientes</h4>
                        </div>
                        <div class="flex-1 min-h-0 relative w-full flex justify-center" x-data="{
                            hasData: {{ collect($chartData['docs_data'])->sum() > 0 ? 'true' : 'false' }},
                            init() {
                                if(this.hasData) {
                                    new Chart(this.$refs.chart, {
                                        type: 'doughnut',
                                        data: {
                                            labels: {{ json_encode($chartData['docs_labels']) }},
                                            datasets: [{
                                                data: {{ json_encode($chartData['docs_data']) }},
                                                backgroundColor: ['#D9795F', '#E9A05F', '#E28B70', '#C87C35', '#293A59', '#7FA587'],
                                                borderWidth: 0,
                                                hoverOffset: 4
                                            }]
                                        },
                                        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 16 }, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15, boxWidth: 8, font: { family: 'Outfit', size: 10, weight: '500' } } } }, cutout: '62%' }
                                    });
                                }
                            }
                        }">
                            <template x-if="hasData"><canvas x-ref="chart" class="w-full h-full"></canvas></template>
                            <template x-if="!hasData">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-estado-exito/80">
                                    <i class="ph-fill ph-check-circle text-4xl mb-2 opacity-80"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider text-estado-exito">0 Pendientes</span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- 5. Dona: Disponibilidad Actual (Card Ancha) -->
                    <div class="rm-chart-panel p-5 h-[320px] overflow-hidden flex flex-col md:col-span-2 border border-borde shadow-sm bg-gradient-to-r from-white to-[#F0F4FA] rounded-2xl">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-titulo flex items-center gap-2"><i class="ph-fill ph-activity text-[#293A59]"></i> Disponibilidad Actual</h4>
                        </div>
                        <div class="flex-1 min-h-0 relative w-full flex justify-center" x-data="{
                            hasData: {{ collect($chartData['disp_data'])->sum() > 0 ? 'true' : 'false' }},
                            init() {
                                if(this.hasData) {
                                    new Chart(this.$refs.chart, {
                                        type: 'bar',
                                        data: {
                                            labels: {{ json_encode($chartData['disp_labels']) }},
                                            datasets: [{
                                                label: 'Disponibilidad',
                                                data: {{ json_encode($chartData['disp_data']) }},
                                                backgroundColor: ['#3F7D5A', '#293A59', '#D9795F'],
                                                borderRadius: 4,
                                                barThickness: 40
                                            }]
                                        },
                                        options: { responsive: true, maintainAspectRatio: false, layout: { padding: 16 }, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, border: {display: false}, grid: { color: '#f3f4f6' }, ticks: { font: { family: 'Outfit', size: 10 } } }, y: { grid: { display: false }, ticks: { font: { family: 'Outfit', size: 11, weight: '600' } } } } }
                                    });
                                }
                            }
                        }">
                            <template x-if="hasData"><canvas x-ref="chart" class="w-full h-full"></canvas></template>
                            <template x-if="!hasData">
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-apoyo/60">
                                    <i class="ph-fill ph-activity text-4xl mb-2 opacity-50"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">Sin datos</span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Contenedor de Tabla -->
            <div class="rm-card overflow-hidden flex flex-col mb-4 !p-0">
                <div class="border-b border-borde px-5 py-4 bg-fondo-hover/30" x-data="{ openFilters: false }">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <h3 class="text-base font-black text-titulo flex items-center gap-3">
                            @if($tabActiva === 'salud')
                                <div class="h-8 w-8 rounded-xl bg-estado-infoBg text-estado-info flex items-center justify-center border border-estado-infoBorde shadow-sm"><i class="ph-fill ph-stethoscope text-lg"></i></div> Directorio de Salud
                            @elseif($tabActiva === 'admin')
                                <div class="h-8 w-8 rounded-xl bg-estado-advertenciaBg text-estado-advertencia flex items-center justify-center border border-estado-advertenciaBorde shadow-sm"><i class="ph-fill ph-desktop text-lg"></i></div> Directorio Administrativo
                            @else
                                <div class="h-8 w-8 rounded-xl bg-[#FDFBF7] text-[#8C7A6B] flex items-center justify-center border border-[#E9E0D7] shadow-sm"><i class="ph-fill ph-list-dashes text-lg"></i></div> Listado General de Personal
                            @endif
                        </h3>
                        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                            <div class="w-full md:w-72 relative group">
                                <i class="ph-bold ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-apoyo group-focus-within:text-[#D9795F] transition-colors"></i>
                                <input type="text" wire:model.live.debounce.300ms="busqueda" placeholder="Buscar nombre, correo o CI..." class="w-full bg-white border border-borde rounded-xl pl-9 pr-3 h-10 text-sm focus:border-[#D9795F] focus:ring-1 focus:ring-[#D9795F] outline-none shadow-sm transition-all text-texto placeholder:text-apoyo/70">
                            </div>
                            <button @click="openFilters = !openFilters" class="bg-white border border-borde text-texto hover:bg-fondo hover:text-titulo transition-colors rounded-xl h-10 px-4 flex items-center gap-2 font-bold text-sm shadow-sm md:w-auto w-full justify-center">
                                <i class="ph-bold ph-funnel text-lg"></i> Filtros
                            </button>
                        </div>
                    </div>
                    
                    <!-- Panel de Filtros Expandible -->
                    <div x-show="openFilters" x-collapse x-cloak class="mt-4 pt-4 border-t border-borde grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Tipo</label>
                            <select class="w-full bg-white border border-borde rounded-lg h-9 px-3 text-sm text-texto outline-none focus:border-[#D9795F]">
                                <option value="">Todos</option>
                                <option value="salud">Salud</option>
                                <option value="admin">Administrativo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Rol</label>
                            <select class="w-full bg-white border border-borde rounded-lg h-9 px-3 text-sm text-texto outline-none focus:border-[#D9795F]">
                                <option value="">Todos</option>
                                <option value="medico">Médico</option>
                                <option value="enfermero">Enfermero</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Estado</label>
                            <select class="w-full bg-white border border-borde rounded-lg h-9 px-3 text-sm text-texto outline-none focus:border-[#D9795F]">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-apoyo uppercase tracking-wider mb-1">Disponibilidad</label>
                            <select class="w-full bg-white border border-borde rounded-lg h-9 px-3 text-sm text-texto outline-none focus:border-[#D9795F]">
                                <option value="">Todas</option>
                                <option value="libre">Libre</option>
                                <option value="ocupado">Ocupado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left border-collapse hidden md:table">
                        <thead class="bg-fondo-hover/50 text-apoyo text-[10px] uppercase font-bold tracking-wider border-y border-borde">
                            <tr>
                                <th class="px-4 py-3 w-3/12">Personal</th>
                                <th class="px-4 py-3 w-2/12">Rol / Tipo</th>
                                <th class="px-4 py-3 w-2/12">Área / Turno</th>
                                <th class="px-4 py-3 text-center w-2/12">Estado</th>
                                <th class="px-4 py-3 text-center w-1/12">Docs.</th>
                                <th class="px-4 py-3 text-center w-1/12">Carga</th>
                                <th class="px-4 py-3 text-center w-1/12">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde/50 bg-white">
                            @forelse($usuarios as $usuario)
                                <tr class="group hover:bg-[#FDFBF7] transition-colors">
                                    <!-- 1. Personal -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-xl border border-borde group-hover:border-[#D3C3B3] transition-colors bg-fondo flex items-center justify-center text-[#8C7A6B] font-black text-sm shadow-sm">
                                                @if($usuario->profile_photo_path)
                                                    <img src="{{ $usuario->profile_photo_url }}" alt="{{ $usuario->name }}" class="h-full w-full object-cover" />
                                                @else
                                                    {{ mb_substr($usuario->nombres, 0, 1) }}{{ mb_substr($usuario->ap_paterno, 0, 1) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-black text-titulo truncate group-hover:text-[#D9795F] transition-colors">{{ $usuario->name }}</div>
                                                <div class="text-[11px] text-apoyo truncate font-medium flex items-center gap-1 mt-0.5"><i class="ph-fill ph-envelope-simple"></i> {{ $usuario->correo }}</div>
                                                @if($usuario->ci)
                                                <div class="text-[10px] text-apoyo font-bold tracking-wide mt-0.5">CI: {{ $usuario->ci }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 2. Rol / Tipo -->
                                    <td class="px-4 py-3">
                                        @if($usuario->personalSalud)
                                            <div class="text-xs font-black text-titulo truncate">{{ $usuario->personalSalud->tipo_personal_salud }}</div>
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded-md border border-[#C6D9CE] bg-[#F2F7F4] text-[#3F7D5A] text-[9px] font-bold uppercase tracking-wider">Salud</span>
                                        @elseif($usuario->personalAdmin)
                                            <div class="text-xs font-black text-titulo truncate">ADMINISTRATIVO</div>
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded-md border border-[#F2CFC4] bg-[#FDF5F2] text-[#D9795F] text-[9px] font-bold uppercase tracking-wider">Administración</span>
                                        @else
                                            <div class="text-xs font-bold text-apoyo truncate">SISTEMA</div>
                                            <span class="inline-block mt-1 px-2 py-0.5 rounded-md border border-borde bg-fondo text-texto text-[9px] font-bold uppercase tracking-wider">Otros</span>
                                        @endif
                                    </td>

                                    <!-- 3. Área / Turno -->
                                    <td class="px-4 py-3">
                                        <div class="text-xs font-bold text-titulo truncate">
                                            @if($usuario->personalSalud)
                                                {{ $usuario->personalSalud->especialidad->nombre_especialidad ?? 'Medicina General' }}
                                            @elseif($usuario->personalAdmin)
                                                {{ $usuario->personalAdmin->cargo->nombre_cargo ?? 'Staff' }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1 mt-1 text-[11px] font-medium text-apoyo">
                                            <i class="ph-fill ph-clock"></i> {{ ['Mañana', 'Tarde', 'Noche', 'Rotativo'][rand(0,3)] }}
                                        </div>
                                    </td>

                                    <!-- 4. Estado -->
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $estadoClass = '';
                                            $estadoLabel = '';
                                            if($usuario->estado === 'ACTIVO' || $usuario->estado == 1) {
                                                $estadoClass = 'bg-[#3F7D5A] text-white';
                                                $estadoLabel = 'ACTIVO';
                                            } elseif($usuario->estado === 'INACTIVO' || $usuario->estado == 0) {
                                                $estadoClass = 'bg-[#D9795F] text-white';
                                                $estadoLabel = 'INACTIVO';
                                            } elseif($usuario->estado === 'SUSPENDIDO') {
                                                $estadoClass = 'bg-[#E9A05F] text-white';
                                                $estadoLabel = 'SUSPENDIDO';
                                            } else {
                                                $estadoClass = 'bg-fondo-hover text-texto';
                                                $estadoLabel = 'RETIRADO';
                                            }
                                        @endphp
                                        <span class="inline-block px-2 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider shadow-sm {{ $estadoClass }}">
                                            {{ $estadoLabel }}
                                        </span>
                                        <div class="mt-1.5 flex justify-center">
                                            @if($usuario->estado === 'ACTIVO' || $usuario->estado == 1)
                                                <span class="text-[10px] font-bold text-[#3F7D5A] uppercase tracking-wider flex items-center gap-1"><i class="ph-fill ph-check-circle"></i> Disponible</span>
                                            @else
                                                <span class="text-[10px] font-bold text-[#D9795F] uppercase tracking-wider flex items-center gap-1"><i class="ph-fill ph-x-circle"></i> No disp.</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- 5. Documentación -->
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $pendientes = \App\Models\DocumentoUsuario::where('cod_usu', $usuario->cod_usu)->where('estado', 'PENDIENTE')->count();
                                        @endphp
                                        @if($pendientes > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-[#FDF5F2] text-[#D9795F] border border-[#F2CFC4] text-[10px] font-bold shadow-sm tooltip-btn cursor-default" title="{{ $pendientes }} pendientes">
                                                {{ $pendientes }} <i class="ph-fill ph-warning-circle"></i>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-[#F2F7F4] text-[#3F7D5A] tooltip-btn cursor-default" title="Completa">
                                                <i class="ph-fill ph-check-circle text-sm"></i>
                                            </span>
                                        @endif
                                    </td>

                                    <!-- 6. Carga -->
                                    <td class="px-4 py-3 text-center">
                                        @if($usuario->personalSalud && $usuario->personalSalud->tipo_personal_salud == 'ENFERMERO')
                                            <span class="text-xs font-black text-[#5C4D40] bg-[#FDFBF7] px-2 py-1 rounded-md border border-[#E9E0D7]">{{ rand(1,3) }}/3</span>
                                        @else
                                            <span class="text-[10px] font-bold text-apoyo uppercase">N/A</span>
                                        @endif
                                    </td>

                                    <!-- 7. Acciones -->
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            @can('usuarios.editar')
                                            <button wire:click="abrirModalEdicion('{{ $usuario->cod_usu }}')" class="h-8 w-8 rounded-lg flex items-center justify-center text-apoyo hover:bg-[#FDFBF7] hover:text-[#D9795F] transition-colors tooltip-btn border border-transparent hover:border-[#E9E0D7] shadow-sm" title="Editar Ficha">
                                                <i class="ph-bold ph-pencil-simple text-base"></i>
                                            </button>
                                            @endcan
                                            <button class="h-8 w-8 rounded-lg flex items-center justify-center text-apoyo hover:bg-[#F0F4FA] hover:text-[#293A59] transition-colors tooltip-btn border border-transparent hover:border-[#C5D0E6] shadow-sm" title="Horario">
                                                <i class="ph-bold ph-calendar-plus text-base"></i>
                                            </button>
                                            <button class="h-8 w-8 rounded-lg flex items-center justify-center text-apoyo hover:bg-[#F2F7F4] hover:text-[#3F7D5A] transition-colors tooltip-btn border border-transparent hover:border-[#C6D9CE] shadow-sm md:hidden lg:flex" title="Documentos">
                                                <i class="ph-bold ph-folders text-base"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-10 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="h-16 w-16 bg-[#FDFBF7] rounded-2xl flex items-center justify-center mb-4 border border-[#E9E0D7]">
                                                <i class="ph-fill ph-users-slash text-3xl text-[#D3C3B3]"></i>
                                            </div>
                                            <h4 class="text-sm font-black text-titulo">No se encontró personal</h4>
                                            <p class="text-xs font-medium text-apoyo mt-1 max-w-sm">Ajusta los filtros de búsqueda o ingresa un nuevo registro para comenzar.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Vista Móvil (Cards) -->
                    <div class="md:hidden flex flex-col gap-3 p-4">
                        @forelse($usuarios as $usuario)
                            <div class="border border-borde rounded-xl p-4 bg-white shadow-sm flex flex-col gap-3 relative">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-xl border border-[#E9E0D7] bg-[#FDFBF7] flex items-center justify-center text-[#8C7A6B] font-black text-sm">
                                            @if($usuario->profile_photo_path)
                                                <img src="{{ $usuario->profile_photo_url }}" alt="{{ $usuario->name }}" class="h-full w-full object-cover" />
                                            @else
                                                {{ mb_substr($usuario->nombres, 0, 1) }}{{ mb_substr($usuario->ap_paterno, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-black text-titulo truncate">{{ $usuario->name }}</div>
                                            <div class="text-[11px] text-apoyo truncate font-medium mt-0.5"><i class="ph-fill ph-envelope-simple"></i> {{ $usuario->correo }}</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1">
                                        @can('usuarios.editar')
                                        <button wire:click="abrirModalEdicion('{{ $usuario->cod_usu }}')" class="h-8 w-8 bg-fondo text-texto rounded-lg flex items-center justify-center border border-borde shadow-sm"><i class="ph-bold ph-pencil-simple"></i></button>
                                        @endcan
                                        <button class="h-8 w-8 bg-fondo text-texto rounded-lg flex items-center justify-center border border-borde shadow-sm"><i class="ph-bold ph-calendar-plus"></i></button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2 pt-3 border-t border-borde/50">
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-apoyo tracking-wider mb-0.5">Rol</div>
                                        <div class="text-xs font-black text-titulo truncate">
                                            @if($usuario->personalSalud) {{ $usuario->personalSalud->tipo_personal_salud }} @elseif($usuario->personalAdmin) ADMIN @else SISTEMA @endif
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-apoyo tracking-wider mb-0.5">Área</div>
                                        <div class="text-xs font-bold text-titulo truncate">
                                            @if($usuario->personalSalud) {{ $usuario->personalSalud->especialidad->nombre_especialidad ?? 'Gral.' }} @else - @endif
                                        </div>
                                    </div>
                                    <div class="col-span-2 flex items-center justify-between mt-2 pt-2 border-t border-borde/50">
                                        <span class="inline-block px-2 py-0.5 rounded-md {{ $usuario->estado === 'ACTIVO' ? 'bg-[#3F7D5A] text-white' : 'bg-fondo text-texto' }} text-[9px] font-bold uppercase tracking-wider">
                                            {{ $usuario->estado === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO' }}
                                        </span>
                                        @php $pendientes = \App\Models\DocumentoUsuario::where('cod_usu', $usuario->cod_usu)->where('estado', 'PENDIENTE')->count(); @endphp
                                        @if($pendientes > 0)
                                            <span class="text-[10px] font-bold text-[#D9795F]"><i class="ph-fill ph-warning-circle"></i> {{ $pendientes }} docs pend.</span>
                                        @else
                                            <span class="text-[10px] font-bold text-[#3F7D5A]"><i class="ph-fill ph-check-circle"></i> Docs al día</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center border border-dashed border-[#E9E0D7] rounded-xl bg-[#FDFBF7]">
                                <h4 class="text-sm font-black text-titulo">No se encontró personal</h4>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        @elseif($tabActiva === 'horarios')
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center border-2 border-dashed border-borde rounded-3xl bg-fondo-card/50">
                <div class="h-20 w-20 bg-white rounded-2xl flex items-center justify-center border border-borde shadow-sm mb-4">
                    <i class="ph-fill ph-calendar-check text-4xl text-boton-acento"></i>
                </div>
                <h3 class="text-xl font-black text-titulo">Módulo de Horarios y Turnos</h3>
                <p class="text-sm font-semibold text-apoyo mt-2 max-w-md">Aquí visualizarás la matriz general de horarios, turnos rotativos y cobertura del personal médico y administrativo.</p>
                <button class="rm-btn-primary h-10 px-6 mt-6">Configurar Matriz</button>
            </div>
            
        @elseif($tabActiva === 'documentacion')
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center border-2 border-dashed border-borde rounded-3xl bg-fondo-card/50">
                <div class="h-20 w-20 bg-white rounded-2xl flex items-center justify-center border border-borde shadow-sm mb-4">
                    <i class="ph-fill ph-folders text-4xl text-estado-advertencia"></i>
                </div>
                <h3 class="text-xl font-black text-titulo">Bóveda Documental Institucional</h3>
                <p class="text-sm font-semibold text-apoyo mt-2 max-w-md">Repositorio centralizado para contratos, títulos profesionales, certificaciones y documentos legales de toda la plantilla.</p>
                <button class="mt-6 flex h-10 items-center justify-center gap-2 rounded-xl bg-estado-advertenciaBg px-6 text-sm font-bold text-estado-advertencia hover:bg-estado-advertencia hover:text-white transition-colors border border-estado-advertenciaBorde">Auditar Documentos</button>
            </div>

        @elseif($tabActiva === 'disponibilidad')
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center border-2 border-dashed border-borde rounded-3xl bg-fondo-card/50">
                <div class="h-20 w-20 bg-white rounded-2xl flex items-center justify-center border border-borde shadow-sm mb-4">
                    <i class="ph-fill ph-clock-user text-4xl text-estado-info"></i>
                </div>
                <h3 class="text-xl font-black text-titulo">Mapa de Disponibilidad</h3>
                <p class="text-sm font-semibold text-apoyo mt-2 max-w-md">Gestión de ausencias, vacaciones, bajas médicas y suplencias del personal.</p>
            </div>

        @elseif($tabActiva === 'reportes')
            <div class="flex flex-col items-center justify-center py-20 px-4 text-center border-2 border-dashed border-borde rounded-3xl bg-fondo-card/50">
                <div class="h-20 w-20 bg-white rounded-2xl flex items-center justify-center border border-borde shadow-sm mb-4">
                    <i class="ph-fill ph-chart-bar text-4xl text-estado-exito"></i>
                </div>
                <h3 class="text-xl font-black text-titulo">Reportes Institucionales</h3>
                <p class="text-sm font-semibold text-apoyo mt-2 max-w-md">Métricas de cumplimiento, carga operativa, asistencia y efectividad de procesos.</p>
            </div>
        @endif
    </div>

    <!-- Modal de Gestión Integral de Personal -->
    @if($modalGestionAbierto)
        @if(!$usuarioSeleccionadoId)
            <!-- MODO NUEVO REGISTRO: MODAL FLOTANTE WIZARD -->
            <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 backdrop-blur-sm px-4 sm:p-6">
                <div class="relative w-full max-w-3xl bg-fondo-card rounded-[2rem] shadow-2xl overflow-hidden flex flex-col border border-borde my-8">
                    <livewire:admin.personal-institucional.partials.personal-institucional-form :usuario-id="null" wire:key="wizard-nuevo" />
                </div>
            </div>
        @else
            <!-- MODO EDICIÓN: FICHA OPERATIVA -->
            <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 backdrop-blur-sm px-4 sm:p-6">
                <div class="relative w-full max-w-5xl bg-fondo-card rounded-[2rem] shadow-2xl overflow-hidden flex flex-col max-h-[90vh] border border-borde">
                    
                    <!-- Header del Modal -->
                    <div class="flex items-center justify-between border-b border-borde px-8 py-5 bg-white sticky top-0 z-10">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento shadow-inner">
                                <i class="ph-fill ph-user-circle-gear text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-titulo">
                                    Ficha Operativa de Personal
                                </h3>
                                <p class="text-xs font-semibold text-apoyo mt-0.5">
                                    Administra datos, documentos y horarios del empleado.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="h-10 px-3 rounded-xl border border-borde text-apoyo hover:bg-fondo-hover hover:text-titulo font-bold text-sm transition-colors flex items-center gap-2">
                                <i class="ph-bold ph-printer"></i> Imprimir
                            </button>
                            <button wire:click="cerrarModal" class="flex h-10 w-10 items-center justify-center rounded-xl bg-fondo-hover text-apoyo hover:bg-estado-peligroBg hover:text-estado-peligro transition-colors border border-borde">
                                <i class="ph-bold ph-x text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Contenido con Tabs Internos (Alpine.js) -->
                    <div x-data="{ tabInterna: 'informacion' }" class="flex flex-col md:flex-row flex-1 overflow-hidden bg-fondo-card">
                        <!-- Sidebar de Tabs Internos -->
                        <div class="w-full md:w-64 border-r border-borde bg-white p-4 flex flex-col gap-2 overflow-y-auto">
                            <div class="text-[10px] font-black text-apoyo uppercase tracking-wider mb-2 px-3">Navegación del Perfil</div>
                            
                            <button @click="tabInterna = 'informacion'" :class="tabInterna === 'informacion' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'" class="flex items-center gap-3 px-4 py-3 rounded-xl border-l-4 font-bold text-sm transition-all text-left group">
                                <i class="ph-fill ph-identification-card text-lg group-hover:scale-110 transition-transform"></i>
                                Información Base
                            </button>
                            
                            <button @click="tabInterna = 'documentacion'" :class="tabInterna === 'documentacion' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'" class="flex items-center justify-between px-4 py-3 rounded-xl border-l-4 font-bold text-sm transition-all text-left group">
                                <div class="flex items-center gap-3">
                                    <i class="ph-fill ph-folder-open text-lg group-hover:scale-110 transition-transform"></i>
                                    Documentación
                                </div>
                            </button>
                            <button @click="tabInterna = 'horarios'" :class="tabInterna === 'horarios' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'" class="flex items-center gap-3 px-4 py-3 rounded-xl border-l-4 font-bold text-sm transition-all text-left group">
                                <i class="ph-fill ph-calendar-check text-lg group-hover:scale-110 transition-transform"></i>
                                Horarios y Turnos
                            </button>
                            <div class="my-2 border-t border-borde"></div>
                            <button @click="tabInterna = 'reportes'" :class="tabInterna === 'reportes' ? 'bg-boton-acento/10 border-boton-acento text-boton-acento' : 'border-transparent text-apoyo hover:bg-fondo-hover hover:text-titulo'" class="flex items-center gap-3 px-4 py-3 rounded-xl border-l-4 font-bold text-sm transition-all text-left group">
                                <i class="ph-fill ph-chart-polar text-lg group-hover:scale-110 transition-transform"></i>
                                Carga Operativa
                            </button>
                        </div>

                        <!-- Contenido de las Tabs Internas -->
                        <div class="flex-1 overflow-y-auto p-6 lg:p-8 bg-fondo-card">
                            <!-- Tab: Información -->
                            <div x-show="tabInterna === 'informacion'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <livewire:admin.personal-institucional.partials.personal-institucional-form :usuario-id="$usuarioSeleccionadoId" wire:key="form-{{ $usuarioSeleccionadoId }}" />
                            </div>

                            <!-- Tab: Documentación -->
                            <div x-show="tabInterna === 'documentacion'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <livewire:admin.personal-institucional.partials.personal-institucional-documentos :usuario-id="$usuarioSeleccionadoId" wire:key="docs-{{ $usuarioSeleccionadoId }}" />
                            </div>

                            <!-- Tab: Horarios -->
                            <div x-show="tabInterna === 'horarios'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <livewire:admin.personal-institucional.partials.personal-institucional-horarios :usuario-id="$usuarioSeleccionadoId" wire:key="horarios-{{ $usuarioSeleccionadoId }}" />
                            </div>
                            
                            <!-- Tab: Reportes/Carga -->
                            <div x-show="tabInterna === 'reportes'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="flex flex-col items-center justify-center p-12 text-apoyo border-2 border-dashed border-borde rounded-3xl bg-white shadow-sm">
                                    <i class="ph-fill ph-chart-line-up text-6xl mb-4 text-boton-acento/30"></i>
                                    <h4 class="text-xl font-black text-titulo">Desempeño y Carga</h4>
                                    <p class="text-sm font-semibold mt-2 text-center max-w-md">Estadísticas de pacientes atendidos, procedimientos completados y asistencia del empleado.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
