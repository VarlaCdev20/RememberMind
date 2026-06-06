<div>
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-fondo-card-calido p-6 rounded-2xl border border-borde shadow-card">
        <div>
            <h1 class="text-3xl font-bold text-titulo tracking-tight">Preadmisiones</h1>
            <p class="text-apoyo mt-1">Control del ingreso, documentación, valoración y seguimiento en fase de preadmisión.</p>
        </div>
        <div class="flex gap-3">
            @can('admisiones.crear')
            <button wire:click="abrirModalNuevo" class="inline-flex items-center gap-2 px-4 py-2 bg-boton-acento hover:bg-boton-acentoHover text-boton-acentoTexto text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="ph ph-plus-circle text-lg"></i>
                Nuevo ingreso
            </button>
            @endcan
            <button wire:click="exportarReportePdf" class="inline-flex items-center gap-2 px-4 py-2 bg-boton-secundario hover:bg-boton-secundarioHover text-boton-secundarioTexto border border-borde text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="ph ph-file-pdf text-lg"></i>
                Exportar PDF
            </button>
        </div>
    </div>

    <!-- Cards de Resumen -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-titulo">{{ $metricas['total_procesos'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">Total</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-suave">{{ $metricas['borradores'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">Borradores</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-modulo-salud">{{ $metricas['preadmisiones_activas'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">En preadmisión</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-terracota">{{ $metricas['documentos_pendientes'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">Docs pendientes</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-estado-advertencia">{{ $metricas['pendientes_valoracion_inicial'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">Listas para val. inicial</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-estado-info">{{ $metricas['valoracion_inicial'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">En val. inicial</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-azul-clinico">{{ $metricas['pendientes_valoracion_medica'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">En val. médica</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-modulo-cognitivoTexto">{{ $metricas['pendientes_decision'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">Pendientes decisión</span>
        </div>
        <div class="bg-fondo-card p-4 rounded-xl border border-borde shadow-card flex flex-col items-center justify-center text-center">
            <span class="text-3xl font-bold text-naranja">{{ $metricas['derivados'] }}</span>
            <span class="text-xs font-medium text-apoyo uppercase mt-1">Derivados</span>
        </div>
    </div>

    <!-- Línea de Etapas -->
    <div class="bg-fondo-card p-6 rounded-2xl border border-borde shadow-card mb-8">
        <h2 class="text-lg font-bold text-titulo mb-6">Embudo de Admisión</h2>
        <div class="relative">
            <div class="absolute top-1/2 left-0 w-full h-1 bg-borde-suave -translate-y-1/2 z-0 rounded-full"></div>
            <div class="relative z-10 flex justify-between">
                @foreach($etapas as $etapa)
                <div class="flex flex-col items-center group cursor-pointer">
                    <div class="w-12 h-12 rounded-full {{ $etapa['color'] }} text-white flex items-center justify-center font-bold text-lg shadow-md ring-4 ring-fondo-card transition-transform group-hover:scale-110">
                        {{ $etapa['cantidad'] }}
                    </div>
                    <div class="mt-3 text-center">
                        <span class="block text-xs font-bold text-titulo">{{ $etapa['nombre'] }}</span>
                        <span class="block text-xs {{ $etapa['text'] }} font-semibold">{{ $etapa['porcentaje'] }}%</span>
                    </div>
                    <button class="mt-2 text-[10px] uppercase font-bold text-suave hover:text-apoyo opacity-0 group-hover:opacity-100 transition-opacity">Ver casos</button>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Gráficas Chart.js -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8" x-data="admisionesCharts()">
        <div class="bg-fondo-card p-5 rounded-2xl border border-borde shadow-card">
            <h3 class="text-sm font-bold text-titulo mb-4">Distribución por estado</h3>
            <div class="h-48 relative w-full">
                <canvas id="chartDonaEstado"></canvas>
            </div>
        </div>
        <div class="bg-fondo-card p-5 rounded-2xl border border-borde shadow-card">
            <h3 class="text-sm font-bold text-titulo mb-4">Procesos por etapa</h3>
            <div class="h-48 relative w-full">
                <canvas id="chartBarrasEtapa"></canvas>
            </div>
        </div>
        <div class="bg-fondo-card p-5 rounded-2xl border border-borde shadow-card">
            <h3 class="text-sm font-bold text-titulo mb-4">Preadmisiones por fecha</h3>
            <div class="h-48 relative w-full">
                <canvas id="chartLineaFecha"></canvas>
            </div>
        </div>
        <div class="bg-fondo-card p-5 rounded-2xl border border-borde shadow-card lg:col-span-2">
            <h3 class="text-sm font-bold text-titulo mb-4">Documentos pendientes por tipo</h3>
            <div class="h-48 relative w-full">
                <canvas id="chartBarrasDoc"></canvas>
            </div>
        </div>
        <div class="bg-fondo-card p-5 rounded-2xl border border-borde shadow-card">
            <h3 class="text-sm font-bold text-titulo mb-4">Decisiones institucionales</h3>
            <div class="h-48 relative w-full">
                <canvas id="chartDonaDecision"></canvas>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="bg-fondo-card p-5 rounded-2xl border border-borde shadow-card mb-6" x-data="{ expanded: false }">
        <div class="flex justify-between items-center mb-4 cursor-pointer" @click="expanded = !expanded">
            <h3 class="text-sm font-bold text-titulo flex items-center gap-2">
                <i class="ph ph-funnel text-lg text-apoyo"></i>
                Filtros Avanzados
            </h3>
            <i class="ph ph-caret-down transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4" x-show="expanded" x-collapse>
            <div class="col-span-1 md:col-span-2">
                <label class="block text-xs font-semibold text-apoyo mb-1">Buscar</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ph ph-magnifying-glass text-apoyo text-lg"></i>
                    </div>
                    <input type="text" wire:model.live.debounce.300ms="search" class="w-full pl-10 pr-4 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto placeholder-input-placeholder" placeholder="Nombre, apellidos, CI, código...">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Estado</label>
                <select wire:model.live="estado" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
                    <option value="">Todos los estados</option>
                    <option value="PENDIENTE_VALORACION_INICIAL">Pendiente Val. Inicial</option>
                    <option value="VALORACION_INICIAL">Valoración Inicial</option>
                    <option value="PENDIENTE_VALORACION_MEDICA">Pendiente Val. Médica</option>
                    <option value="ADMITIDO">Admitido</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Etapa actual</label>
                <select wire:model.live="etapa" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
                    <option value="">Todas las etapas</option>
                    <option value="PREADMISION">Preadmisión</option>
                    <option value="VALORACION_INICIAL">Valoración Inicial</option>
                    <option value="VALORACION_MEDICA">Valoración Médica</option>
                    <option value="DECISION">Decisión</option>
                    <option value="ASIGNACION">Asignación</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Documentación</label>
                <select wire:model.live="documentacion" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
                    <option value="">Todos</option>
                    <option value="completa">Completa</option>
                    <option value="incompleta">Incompleta</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Desde</label>
                <input type="date" wire:model.live="fecha_inicio" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Hasta</label>
                <input type="date" wire:model.live="fecha_fin" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Prioridad (Triage)</label>
                <select wire:model.live="prioridad" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
                    <option value="">Todas</option>
                    <option value="alta">Alta</option>
                    <option value="media">Media</option>
                    <option value="baja">Baja</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Enfermero Valorador</label>
                <select wire:model.live="enfermero_id" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
                    <option value="">Todos</option>
                    @foreach($enfermeros as $enfermero)
                        <option value="{{ $enfermero->id }}">{{ $enfermero->nombres }} {{ $enfermero->apellidos }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-apoyo mb-1">Médico Valorador</label>
                <select wire:model.live="medico_id" class="w-full px-3 py-2 bg-input-bg border border-input-borde rounded-lg text-sm focus:ring-input-ringFocus focus:border-input-bordeFocus text-input-texto">
                    <option value="">Todos</option>
                    @foreach($medicos as $medico)
                        <option value="{{ $medico->id }}">{{ $medico->nombres }} {{ $medico->apellidos }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-full pt-2 border-t border-borde flex justify-end">
                <button wire:click="$reset(['estado', 'etapa', 'documentacion', 'fecha_inicio', 'fecha_fin', 'prioridad', 'enfermero_id', 'medico_id'])" class="text-xs text-naranja font-medium hover:underline">Limpiar filtros</button>
            </div>
        </div>
    </div>

    <!-- Tabla Principal -->
    <div class="bg-fondo-card rounded-2xl border border-borde shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-tabla-header text-tabla-headerTexto uppercase text-xs font-bold border-b border-tabla-headerBorde">
                    <tr>
                        <th class="px-6 py-4">Código</th>
                        <th class="px-6 py-4">Adulto mayor</th>
                        <th class="px-6 py-4">CI</th>
                        <th class="px-6 py-4">Edad</th>
                        <th class="px-6 py-4">Familiar</th>
                        <th class="px-6 py-4">Motivo</th>
                        <th class="px-6 py-4">Procedencia</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Documentación</th>
                        <th class="px-6 py-4">Enfermero valorador</th>
                        <th class="px-6 py-4">Médico</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-tabla-rowBorde bg-fondo-tabla">
                    @forelse($admisiones as $admision)
                        <tr class="hover:bg-tabla-rowHover transition-colors text-texto-principal">
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $admision->cod_am }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $admision->nombres }} {{ $admision->ap_paterno }} {{ $admision->ap_materno }}</div>
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                {{ $admision->ci }} {{ $admision->expedicion_ci }}
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                {{ \Carbon\Carbon::parse($admision->fecha_nac)->age }} años
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                @php
                                    $responsable = $admision->familiares->where('pivot.es_responsable', 1)->first() ?? $admision->familiares->first();
                                @endphp
                                {{ $responsable ? $responsable->nombres . ' ' . $responsable->apellidos : 'Sin registrar' }}
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                {{ $admision->motivo_ingreso ?? 'No especificado' }}
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                {{ $admision->procedencia ?? 'Domicilio' }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $estado = $admision->estado->estado ?? 'DESCONOCIDO';
                                    $color = match($estado) {
                                        'PENDIENTE_VALORACION_INICIAL' => 'bg-estado-advertenciaBg text-estado-advertencia ring-1 ring-estado-advertenciaBorde',
                                        'VALORACION_INICIAL' => 'bg-estado-infoBg text-estado-info ring-1 ring-estado-infoBorde',
                                        'ADMITIDO' => 'bg-estado-exitoBg text-estado-exito ring-1 ring-estado-exitoBorde',
                                        'NO_ADMITIDO' => 'bg-estado-peligroBg text-estado-peligro ring-1 ring-estado-peligroBorde',
                                        default => 'bg-estado-neutralBg text-estado-neutral ring-1 ring-estado-neutralBorde'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $color }}">
                                    {{ str_replace('_', ' ', $estado) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-1">
                                    <div class="w-2 h-2 rounded-full bg-estado-exitoTexto" title="Completo"></div>
                                    <div class="w-2 h-2 rounded-full bg-estado-neutralBorde" title="Pendiente"></div>
                                </div>
                                <div class="text-[10px] text-apoyo mt-1">Revisión docs</div>
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                {{ $admision->enfermero_valorador ?? 'Sin asignar' }}
                            </td>
                            <td class="px-6 py-4 text-texto-secundario">
                                {{ $admision->medico_valorador ?? 'Sin asignar' }}
                            </td>
                            <td class="px-6 py-4 text-texto-secundario text-xs font-medium">
                                {{ $admision->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button class="w-7 h-7 rounded-lg bg-boton-fantasma hover:bg-boton-fantasmaHover text-boton-fantasmaTexto flex items-center justify-center transition-colors" title="Ver detalle">
                                        <i class="ph ph-eye text-base"></i>
                                    </button>
                                    <button class="w-7 h-7 rounded-lg bg-modulo-saludSuave hover:bg-modulo-saludFondo text-modulo-salud flex items-center justify-center transition-colors" title="Continuar proceso">
                                        <i class="ph ph-arrow-right text-base"></i>
                                    </button>
                                    <button class="w-7 h-7 rounded-lg bg-estado-infoBg hover:bg-estado-infoBorde text-estado-info flex items-center justify-center transition-colors" title="Ver documentos">
                                        <i class="ph ph-files text-base"></i>
                                    </button>
                                    <button class="w-7 h-7 rounded-lg bg-estado-advertenciaBg hover:bg-estado-advertenciaBorde text-estado-advertencia flex items-center justify-center transition-colors" title="Asignar valoración inicial">
                                        <i class="ph ph-user-circle-plus text-base"></i>
                                    </button>
                                    <button wire:click="exportarFichaPdf('{{ $admision->cod_am }}')" class="w-7 h-7 rounded-lg bg-fondo-card-calido hover:bg-fondo-card-calido text-terracota flex items-center justify-center transition-colors" title="Exportar Ficha PDF">
                                        <i class="ph ph-file-pdf text-base"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-6 py-12 text-center text-apoyo">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-fondo-empty flex items-center justify-center mb-4">
                                        <i class="ph ph-users text-3xl text-meta"></i>
                                    </div>
                                    <p class="text-base font-medium text-titulo mb-1">No hay registros de admisiones</p>
                                    <p class="text-sm">No se encontraron pacientes en proceso de admisión con los filtros aplicados.</p>
                                    @can('admisiones.crear')
                                    <button wire:click="abrirModalNuevo" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-boton-acento hover:bg-boton-acentoHover text-boton-acentoTexto text-sm font-medium rounded-lg transition-colors shadow-sm">
                                        <i class="ph ph-plus text-lg"></i>
                                        Nuevo ingreso
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($admisiones->hasPages())
        <div class="px-6 py-4 border-t border-tabla-rowBorde bg-fondo-card">
            {{ $admisiones->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Nuevo Ingreso -->
    @if($showModalNuevo)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-modal-overlay backdrop-blur-sm transition-opacity" wire:click="cerrarModalNuevo"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <!-- Contenedor del Modal -->
            <div class="relative transform overflow-hidden rounded-2xl bg-modal-bg text-left shadow-modal transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-modal-borde">
                
                <!-- Header del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-b border-modal-headerBorde flex justify-between items-center">
                    <h3 class="text-xl font-bold text-modal-titulo flex items-center gap-2" id="modal-title">
                        <i class="ph ph-user-plus text-boton-acento text-2xl"></i>
                        Nueva Preadmisión
                    </h3>
                    <button wire:click="cerrarModalNuevo" class="text-apoyo hover:text-terracota transition-colors rounded-lg p-1 hover:bg-fondo-hover">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <div class="px-6 py-4 bg-fondo-app">
                    <!-- Barra de progreso -->
                    <div class="mb-8">
                        <div class="flex justify-between text-xs font-semibold text-apoyo mb-2 px-1">
                            <span>Paso {{ $paso }} de {{ $totalPasos }}</span>
                            <span>{{ round(($paso / $totalPasos) * 100) }}%</span>
                        </div>
                        <div class="w-full bg-borde-suave rounded-full h-2.5 overflow-hidden">
                            <div class="bg-boton-acento h-2.5 rounded-full transition-all duration-500 ease-out" style="width: {{ ($paso / $totalPasos) * 100 }}%"></div>
                        </div>
                        <div class="hidden justify-between mt-3 text-[10px] uppercase font-bold text-apoyo tracking-wider md:flex">
                            <span class="{{ $paso >= 1 ? 'text-boton-acento' : '' }}">Identificación</span>
                            <span class="{{ $paso >= 2 ? 'text-boton-acento' : '' }}">Datos Personales</span>
                            <span class="{{ $paso >= 3 ? 'text-boton-acento' : '' }}">Dirección</span>
                            <span class="{{ $paso >= 4 ? 'text-boton-acento' : '' }}">Familiar</span>
                            <span class="{{ $paso >= 5 ? 'text-boton-acento' : '' }}">Motivo</span>
                            <span class="{{ $paso >= 6 ? 'text-boton-acento' : '' }}">Docs Iniciales</span>
                            <span class="{{ $paso >= 7 ? 'text-boton-acento' : '' }}">Contratos</span>
                            <span class="{{ $paso >= 8 ? 'text-boton-acento' : '' }}">Asignación</span>
                            <span class="{{ $paso == 9 ? 'text-boton-acento' : '' }}">Confirmar</span>
                        </div>
                    </div>

                    <!-- Contenido del Paso -->
                    <div class="bg-fondo-card rounded-xl p-6 border border-borde shadow-sm min-h-[300px]">
                        @if($paso == 1)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 1: Identificación del Adulto Mayor</h4>
                            <div class="flex flex-col items-center justify-center gap-8 lg:flex-row lg:items-start lg:justify-start mb-6">
                                <div class="relative group">
                                    <div class="h-32 w-32 overflow-hidden rounded-3xl border-4 border-white bg-fondo-card shadow-2xl transition-transform group-hover:scale-105">
                                        @if($foto)
                                            <img src="{{ $foto->temporaryUrl() }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-azul-clinico/5 to-azul-clinico/10 text-titulo/20">
                                                <i class="ph-fill ph-user text-6xl"></i>
                                                <span class="mt-2 text-[10px] font-bold uppercase tracking-widest">Sin foto</span>
                                            </div>
                                        @endif
                                        <div wire:loading wire:target="foto" class="absolute inset-0 flex items-center justify-center bg-boton-acento/40 backdrop-blur-sm">
                                            <i class="ph-bold ph-circle-notch animate-spin text-3xl text-white"></i>
                                        </div>
                                    </div>
                                    <label class="absolute -bottom-2 -right-2 flex h-12 w-12 cursor-pointer items-center justify-center rounded-2xl bg-boton-acento text-white shadow-xl transition hover:scale-110 hover:bg-boton-acentoHover active:scale-95">
                                        <i class="ph-bold ph-camera text-xl"></i>
                                        <input type="file" wire:model="foto" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                <div class="flex-1 space-y-4 w-full">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-label mb-1">Nombres <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.live.debounce.250ms="nombres" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                            @error('nombres') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-label mb-1">Apellido Paterno <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.live.debounce.250ms="ap_paterno" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                            @error('ap_paterno') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-label mb-1">Apellido Materno</label>
                                            <input type="text" wire:model.live.debounce.250ms="ap_materno" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                            @error('ap_materno') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-label mb-1">Cédula de Identidad <span class="text-estado-peligro">*</span></label>
                                            <input type="text" wire:model.live.debounce.250ms="ci" maxlength="9" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                            @error('ci') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-label mb-1">Expedición <span class="text-estado-peligro">*</span></label>
                                            <select wire:model="expedicion_ci" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                                <option value="">Seleccione...</option>
                                                @foreach(['LP'=>'La Paz', 'SC'=>'Santa Cruz', 'CB'=>'Cochabamba', 'OR'=>'Oruro', 'PT'=>'Potosí', 'CH'=>'Chuquisaca', 'TJ'=>'Tarija', 'BE'=>'Beni', 'PA'=>'Pando'] as $val => $text)
                                                    <option value="{{ $val }}">{{ $text }} ({{ $val }})</option>
                                                @endforeach
                                            </select>
                                            @error('expedicion_ci') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-label mb-1">Estado Civil <span class="text-estado-peligro">*</span></label>
                                            <select wire:model="estado_civil" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                                <option value="">Seleccione...</option>
                                                <option value="SOLTERO/A">Soltero/a</option>
                                                <option value="CASADO/A">Casado/a</option>
                                                <option value="VIUDO/A">Viudo/a</option>
                                                <option value="DIVORCIADO/A">Divorciado/a</option>
                                                <option value="UNIÓN LIBRE">Unión Libre</option>
                                                <option value="NO ESPECIFICADO">No especificado</option>
                                            </select>
                                            @error('estado_civil') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                        @elseif($paso == 2)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 2: Datos Personales</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Fecha Nacimiento <span class="text-estado-peligro">*</span></label>
                                    <input type="date" wire:model.live="fecha_nac" max="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('fecha_nac') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Edad Estimada</label>
                                    @if($this->edad !== null && $this->edad < 60)
                                    <div class="flex h-[42px] w-full items-center gap-2 rounded-lg border border-estado-peligroBorde bg-estado-peligroBg px-3 text-sm font-bold text-estado-peligro shadow-sm">
                                        <i class="ph-fill ph-warning text-base"></i>
                                        {{ $this->edad }} años (menor de 60)
                                    </div>
                                    @else
                                    <div class="flex h-[42px] w-full items-center rounded-lg border border-input-borde bg-fondo-card px-3 text-sm font-bold text-titulo/50 shadow-sm">
                                        {{ $this->edad !== null ? $this->edad . ' años' : '--' }}
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Género <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="genero" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        <option value="MASCULINO">Masculino</option>
                                        <option value="FEMENINO">Femenino</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                    @error('genero') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Grupo Sanguíneo <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="grupo_sanguineo" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $gs)
                                            <option value="{{ $gs }}">{{ $gs }}</option>
                                        @endforeach
                                    </select>
                                    @error('grupo_sanguineo') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Seguro de Salud <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="seguro_salud" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['SUS','CAJA NACIONAL CNS','CAJA PETROLERA','SEGURO PRIVADO','NINGUNO','OTRO'] as $seguro)
                                            <option value="{{ $seguro }}">{{ $seguro }}</option>
                                        @endforeach
                                    </select>
                                    @error('seguro_salud') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Nivel Educativo <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="nivel_educat" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['ANALFABETO','PRIMARIA','SECUNDARIA','TÉCNICO','UNIVERSITARIO','POSTGRADO','NO ESPECIFICADO'] as $nivel)
                                            <option value="{{ $nivel }}">{{ ucfirst(strtolower($nivel)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('nivel_educat') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-label mb-1">Alergias Conocidas <span class="text-estado-peligro">*</span></label>
                                    <textarea wire:model="alergias" rows="2" placeholder="Especifique o deje 'NINGUNA'..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase"></textarea>
                                    @error('alergias') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <hr class="my-6 border-borde-suave">
                            <h5 class="text-md font-bold text-titulo mb-4">Contacto</h5>
                            
                            <div class="flex items-center gap-3 mb-4 bg-fondo-hover p-3 rounded-lg border border-borde-suave">
                                <input type="checkbox" wire:model.live="tiene_celular" id="tiene_celular" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                <label for="tiene_celular" class="text-sm font-semibold text-titulo cursor-pointer">Cuenta con dispositivo móvil personal</label>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @if($tiene_celular)
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Celular <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.live.debounce.250ms="celular" maxlength="8" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('celular') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="flex items-center gap-2 mt-7">
                                    <input type="checkbox" wire:model="sabe_usar_whatsapp" id="sabe_ws" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento">
                                    <label for="sabe_ws" class="text-sm font-semibold text-titulo cursor-pointer">Sabe usar WhatsApp</label>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Teléfono Fijo / Referencia</label>
                                    <input type="text" wire:model.live.debounce.250ms="telefono_fijo" maxlength="8" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('telefono_fijo') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        @elseif($paso == 3)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 3: Dirección y Procedencia</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Departamento Residencia <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="departamento_residencia" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['LA PAZ', 'SANTA CRUZ', 'COCHABAMBA', 'ORURO', 'POTOSÍ', 'CHUQUISACA', 'TARIJA', 'BENI', 'PANDO'] as $dep)
                                            <option value="{{ $dep }}">{{ $dep }}</option>
                                        @endforeach
                                    </select>
                                    @error('departamento_residencia') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Ciudad / Municipio <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model="ciudad_municipio" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('ciudad_municipio') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Zona / Barrio <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model="zona" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('zona') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Procedencia</label>
                                    <input type="text" wire:model="procedencia" placeholder="Ej. Domicilio propio, Hospital..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-label mb-1">Calle / Avenida / Referencia Visual <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model="calle" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('calle') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @elseif($paso == 4)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 4: Familiar Responsable</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Nombres <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_nombres" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('familiar_nombres') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Apellido Paterno</label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_ap_paterno" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('familiar_ap_paterno') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Apellido Materno</label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_ap_materno" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('familiar_ap_materno') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Cédula de Identidad <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_ci" maxlength="9" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('familiar_ci') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Expedición</label>
                                    <select wire:model="familiar_expedicion" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['LP'=>'La Paz', 'SC'=>'Santa Cruz', 'CB'=>'Cochabamba', 'OR'=>'Oruro', 'PT'=>'Potosí', 'CH'=>'Chuquisaca', 'TJ'=>'Tarija', 'BE'=>'Beni', 'PA'=>'Pando'] as $val => $text)
                                            <option value="{{ $val }}">{{ $text }} ({{ $val }})</option>
                                        @endforeach
                                    </select>
                                    @error('familiar_expedicion') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Parentesco <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="familiar_parentesco" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['HIJO/A','ESPOSO/A','HERMANO/A','SOBRINO/A','NIETO/A','TUTOR/A','APODERADO/A','OTRO'] as $par)
                                            <option value="{{ $par }}">{{ ucfirst(strtolower($par)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('familiar_parentesco') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Celular <span class="text-estado-peligro">*</span></label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_celular" maxlength="8" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('familiar_celular') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Teléfono Alternativo</label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_telefono_alt" maxlength="8" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('familiar_telefono_alt') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Ocupación</label>
                                    <input type="text" wire:model.live.debounce.250ms="familiar_ocupacion" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase">
                                    @error('familiar_ocupacion') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-label mb-1">Correo Electrónico</label>
                                    <input type="email" wire:model.live.debounce.250ms="familiar_correo" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                    @error('familiar_correo') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-sm font-semibold text-label mb-1">Dirección <span class="text-estado-peligro">*</span></label>
                                    <textarea wire:model.live.debounce.250ms="familiar_direccion" rows="2" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase"></textarea>
                                    @error('familiar_direccion') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <hr class="my-6 border-borde-suave">
                            <h5 class="text-md font-bold text-titulo mb-4">Autorizaciones y Responsabilidades</h5>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-fondo-hover p-4 rounded-lg border border-borde-suave">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="familiar_es_responsable" id="fam_responsable" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento cursor-pointer">
                                    <label for="fam_responsable" class="text-sm font-semibold text-titulo cursor-pointer">Responsable Principal</label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="familiar_autorizado_medica" id="fam_aut_medica" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento cursor-pointer">
                                    <label for="fam_aut_medica" class="text-sm font-semibold text-titulo cursor-pointer">Autorizado para Información Médica</label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="familiar_autorizado_firmar" id="fam_aut_firmar" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento cursor-pointer">
                                    <label for="fam_aut_firmar" class="text-sm font-semibold text-titulo cursor-pointer">Autorizado para Firmar Documentos</label>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="familiar_contacto_emergencia" id="fam_emergencia" class="h-5 w-5 rounded border-borde-suave text-boton-acento focus:ring-boton-acento cursor-pointer">
                                    <label for="fam_emergencia" class="text-sm font-semibold text-titulo cursor-pointer">Contacto de Emergencia Principal</label>
                                </div>
                            </div>
                        @elseif($paso == 5)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 5: Detalles del Ingreso</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Motivo de Ingreso <span class="text-estado-peligro">*</span></label>
                                    <select wire:model.live="motivo_ingreso" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['CUIDADO_PERMANENTE','CUIDADO_TEMPORAL','CONTROL_MEDICACION','RIESGO_CAIDAS','OLVIDOS_FRECUENTES','DEPENDENCIA_FUNCIONAL','SOLEDAD_FAMILIAR','ALTERACION_CONDUCTUAL','RECUPERACION_POST_HOSPITALARIA','OTRO'] as $mot)
                                            <option value="{{ $mot }}">{{ str_replace('_', ' ', $mot) }}</option>
                                        @endforeach
                                    </select>
                                    @error('motivo_ingreso') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Procedencia (Derivación) <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="procedencia_ingreso" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        @foreach(['DOMICILIO_FAMILIAR','HOSPITAL','OTRO_CENTRO_GERIATRICO','INSTITUCION_SOCIAL','CONSULTA_MEDICA_EXTERNA','OTRO'] as $proc)
                                            <option value="{{ $proc }}">{{ str_replace('_', ' ', $proc) }}</option>
                                        @endforeach
                                    </select>
                                    @error('procedencia_ingreso') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Tipo de Ingreso <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="tipo_ingreso" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        <option value="REGULAR">Regular / Voluntario</option>
                                        <option value="URGENCIA">Urgencia</option>
                                        <option value="ORDEN_JUDICIAL">Orden Judicial / Legal</option>
                                        <option value="ABANDONO">Caso Social / Abandono</option>
                                    </select>
                                    @error('tipo_ingreso') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-label mb-1">Tipo de Permanencia <span class="text-estado-peligro">*</span></label>
                                    <select wire:model="permanencia" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm">
                                        <option value="">Seleccionar</option>
                                        <option value="PERMANENTE">Residencia Permanente (24h)</option>
                                        <option value="CENTRO_DE_DIA">Centro de Día (Diurno)</option>
                                        <option value="TEMPORAL">Estancia Temporal / Recuperación</option>
                                    </select>
                                    @error('permanencia') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-label mb-1">Prioridad de Atención <span class="text-estado-peligro">*</span></label>
                                    <div class="flex gap-4 p-3 bg-fondo-hover rounded-lg border border-borde-suave">
                                        @foreach(['BAJA' => 'bg-modulo-salud', 'MEDIA' => 'bg-estado-info', 'ALTA' => 'bg-estado-advertencia', 'CRITICA' => 'bg-estado-peligro'] as $pri => $color)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" wire:model.live="prioridad" value="{{ $pri }}" class="h-4 w-4 text-boton-acento focus:ring-boton-acento">
                                                <span class="text-sm font-semibold text-titulo flex items-center gap-1">
                                                    <span class="w-2 h-2 rounded-full {{ $color }}"></span>
                                                    {{ $pri }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('prioridad') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-label mb-1">Descripción del Caso / Motivo Específico @if($motivo_ingreso === 'OTRO' || in_array($prioridad, ['ALTA', 'CRITICA'])) <span class="text-estado-peligro">*</span> @endif</label>
                                    <textarea wire:model.live.debounce.500ms="descripcion_caso" rows="3" placeholder="Detalle la situación particular de este ingreso..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase"></textarea>
                                    <p class="text-xs text-apoyo mt-1">@if(in_array($prioridad, ['ALTA', 'CRITICA'])) Mínimo 20 caracteres para prioridad Alta o Crítica. @endif</p>
                                    @error('descripcion_caso') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-label mb-1">Observaciones Administrativas (Interno)</label>
                                    <textarea wire:model.live.debounce.500ms="observacion_administrativa" rows="2" placeholder="Ej. Falta firma del responsable, requiere validación de seguro..." class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase"></textarea>
                                    @error('observacion_administrativa') <span class="text-estado-peligro text-xs font-medium mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @elseif($paso == 6)
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-bold text-titulo">Paso 6: Documentación Inicial</h4>
                                <span class="text-xs font-semibold px-2 py-1 bg-estado-infoBg text-estado-info rounded-md border border-estado-infoBorde">Obligatorios requeridos para continuar</span>
                            </div>
                            
                            <!-- Adulto Mayor Docs -->
                            <h5 class="font-bold text-modulo-salud mb-3 flex items-center gap-2"><i class="ph-bold ph-user-circle"></i> 1. Documentación del Adulto Mayor</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                @foreach($this->esquema_documentos as $key => $doc)
                                    @if($doc['tipo'] === 'adulto')
                                        <div class="bg-fondo-card border {{ isset($this->documentos_subidos[$key]) && $this->documentos_subidos[$key]['estado'] == 'VALIDADO' ? 'border-estado-exito' : (isset($this->documentos_subidos[$key]) && $this->documentos_subidos[$key]['estado'] == 'OBSERVADO' ? 'border-estado-peligro' : 'border-borde-suave') }} rounded-lg p-4 relative flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                                            <div>
                                                <div class="flex justify-between items-start mb-2">
                                                    <h6 class="text-sm font-bold text-titulo pr-6">{{ $doc['titulo'] }}</h6>
                                                    @if($doc['obligatorio'])
                                                        <span class="absolute top-4 right-4 text-xs font-bold bg-estado-peligro text-white px-1.5 py-0.5 rounded shadow-sm">REQ</span>
                                                    @elseif($doc['plazo_48h'])
                                                        <span class="absolute top-4 right-4 text-[10px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertencia px-1.5 py-0.5 rounded">48H</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-apoyo mb-3">{{ $doc['desc'] }}</p>
                                                
                                                @if(isset($this->documentos_subidos[$key]))
                                                    <div class="mb-3 text-xs p-2 rounded bg-fondo-hover border border-borde-suave flex flex-col gap-1">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-semibold truncate text-modulo-salud"><i class="ph-bold {{ $this->documentos_subidos[$key]['is_pdf'] ? 'ph-file-pdf' : 'ph-image' }} mr-1"></i> {{ $this->documentos_subidos[$key]['nombre_original'] }}</span>
                                                            <span class="text-apoyo">{{ $this->documentos_subidos[$key]['size'] }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $this->documentos_subidos[$key]['estado'] == 'VALIDADO' ? 'bg-estado-exitoBg text-estado-exito' : ($this->documentos_subidos[$key]['estado'] == 'OBSERVADO' ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-estado-infoBg text-estado-info') }}">
                                                                {{ $this->documentos_subidos[$key]['estado'] }}
                                                            </span>
                                                            @if($this->documentos_subidos[$key]['observacion'])
                                                                <span class="text-[10px] text-estado-peligro truncate" title="{{ $this->documentos_subidos[$key]['observacion'] }}"><i class="ph-bold ph-warning"></i> Obs.</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="flex gap-2 justify-end mt-2">
                                                @if(!isset($this->documentos_subidos[$key]))
                                                    <label class="cursor-pointer text-xs font-bold text-white bg-boton-acento hover:bg-boton-acentoHover px-3 py-1.5 rounded-lg flex items-center gap-1 transition" wire:click="prepararSubidaDocumento('{{ $key }}')">
                                                        <i class="ph-bold ph-upload-simple"></i> Subir
                                                        <input type="file" wire:model.live="documento_temp_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                                    </label>
                                                @else
                                                    @if($this->documentos_subidos[$key]['temp_url'])
                                                        <a href="{{ $this->documentos_subidos[$key]['temp_url'] }}" target="_blank" class="text-xs font-bold text-boton-secundario hover:text-boton-secundarioHover bg-fondo-hover px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-borde-suave">
                                                            <i class="ph-bold ph-eye"></i> Ver
                                                        </a>
                                                    @endif
                                                    <label class="cursor-pointer text-xs font-bold text-boton-secundario hover:text-boton-secundarioHover bg-fondo-hover px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-borde-suave" wire:click="prepararSubidaDocumento('{{ $key }}')">
                                                        <i class="ph-bold ph-arrows-clockwise"></i> Cambiar
                                                        <input type="file" wire:model.live="documento_temp_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                                    </label>
                                                    <button type="button" wire:click="validarDocumento('{{ $key }}')" class="text-xs font-bold {{ $this->documentos_subidos[$key]['estado'] == 'VALIDADO' ? 'text-estado-exito bg-estado-exitoBg border-estado-exito' : 'text-estado-exito hover:bg-estado-exitoBg' }} px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-transparent">
                                                        <i class="ph-bold ph-check-circle"></i>
                                                    </button>
                                                    <button type="button" x-data x-on:click="$dispatch('open-obs-modal', { key: '{{ $key }}' })" class="text-xs font-bold {{ $this->documentos_subidos[$key]['estado'] == 'OBSERVADO' ? 'text-estado-peligro bg-estado-peligroBg border-estado-peligro' : 'text-estado-peligro hover:bg-estado-peligroBg' }} px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-transparent">
                                                        <i class="ph-bold ph-warning-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <!-- Familiar Responsable Docs -->
                            <h5 class="font-bold text-modulo-personal mb-3 flex items-center gap-2"><i class="ph-bold ph-users"></i> 2. Documentación del Familiar</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($this->esquema_documentos as $key => $doc)
                                    @if($doc['tipo'] === 'familiar')
                                        <div class="bg-fondo-card border {{ isset($this->documentos_subidos[$key]) && $this->documentos_subidos[$key]['estado'] == 'VALIDADO' ? 'border-estado-exito' : (isset($this->documentos_subidos[$key]) && $this->documentos_subidos[$key]['estado'] == 'OBSERVADO' ? 'border-estado-peligro' : 'border-borde-suave') }} rounded-lg p-4 relative flex flex-col justify-between shadow-sm hover:shadow-md transition-shadow">
                                            <div>
                                                <div class="flex justify-between items-start mb-2">
                                                    <h6 class="text-sm font-bold text-titulo pr-6">{{ $doc['titulo'] }}</h6>
                                                    @if($doc['obligatorio'] || ($key === 'ci_familiar' && $familiar_autorizado_firmar))
                                                        <span class="absolute top-4 right-4 text-xs font-bold bg-estado-peligro text-white px-1.5 py-0.5 rounded shadow-sm">REQ</span>
                                                    @elseif($doc['plazo_48h'])
                                                        <span class="absolute top-4 right-4 text-[10px] font-bold bg-estado-advertenciaBg text-estado-advertencia border border-estado-advertencia px-1.5 py-0.5 rounded">48H</span>
                                                    @endif
                                                </div>
                                                <p class="text-xs text-apoyo mb-3">{{ $doc['desc'] }}</p>
                                                
                                                @if(isset($this->documentos_subidos[$key]))
                                                    <div class="mb-3 text-xs p-2 rounded bg-fondo-hover border border-borde-suave flex flex-col gap-1">
                                                        <div class="flex items-center justify-between">
                                                            <span class="font-semibold truncate text-modulo-personal"><i class="ph-bold {{ $this->documentos_subidos[$key]['is_pdf'] ? 'ph-file-pdf' : 'ph-image' }} mr-1"></i> {{ $this->documentos_subidos[$key]['nombre_original'] }}</span>
                                                            <span class="text-apoyo">{{ $this->documentos_subidos[$key]['size'] }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $this->documentos_subidos[$key]['estado'] == 'VALIDADO' ? 'bg-estado-exitoBg text-estado-exito' : ($this->documentos_subidos[$key]['estado'] == 'OBSERVADO' ? 'bg-estado-peligroBg text-estado-peligro' : 'bg-estado-infoBg text-estado-info') }}">
                                                                {{ $this->documentos_subidos[$key]['estado'] }}
                                                            </span>
                                                            @if($this->documentos_subidos[$key]['observacion'])
                                                                <span class="text-[10px] text-estado-peligro truncate" title="{{ $this->documentos_subidos[$key]['observacion'] }}"><i class="ph-bold ph-warning"></i> Obs.</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="flex gap-2 justify-end mt-2">
                                                @if(!isset($this->documentos_subidos[$key]))
                                                    <label class="cursor-pointer text-xs font-bold text-white bg-boton-acento hover:bg-boton-acentoHover px-3 py-1.5 rounded-lg flex items-center gap-1 transition" wire:click="prepararSubidaDocumento('{{ $key }}')">
                                                        <i class="ph-bold ph-upload-simple"></i> Subir
                                                        <input type="file" wire:model.live="documento_temp_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                                    </label>
                                                @else
                                                    @if($this->documentos_subidos[$key]['temp_url'])
                                                        <a href="{{ $this->documentos_subidos[$key]['temp_url'] }}" target="_blank" class="text-xs font-bold text-boton-secundario hover:text-boton-secundarioHover bg-fondo-hover px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-borde-suave">
                                                            <i class="ph-bold ph-eye"></i> Ver
                                                        </a>
                                                    @endif
                                                    <label class="cursor-pointer text-xs font-bold text-boton-secundario hover:text-boton-secundarioHover bg-fondo-hover px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-borde-suave" wire:click="prepararSubidaDocumento('{{ $key }}')">
                                                        <i class="ph-bold ph-arrows-clockwise"></i> Cambiar
                                                        <input type="file" wire:model.live="documento_temp_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                                    </label>
                                                    <button type="button" wire:click="validarDocumento('{{ $key }}')" class="text-xs font-bold {{ $this->documentos_subidos[$key]['estado'] == 'VALIDADO' ? 'text-estado-exito bg-estado-exitoBg border-estado-exito' : 'text-estado-exito hover:bg-estado-exitoBg' }} px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-transparent">
                                                        <i class="ph-bold ph-check-circle"></i>
                                                    </button>
                                                    <button type="button" x-data x-on:click="$dispatch('open-obs-modal', { key: '{{ $key }}' })" class="text-xs font-bold {{ $this->documentos_subidos[$key]['estado'] == 'OBSERVADO' ? 'text-estado-peligro bg-estado-peligroBg border-estado-peligro' : 'text-estado-peligro hover:bg-estado-peligroBg' }} px-2 py-1.5 rounded-lg flex items-center gap-1 transition border border-transparent">
                                                        <i class="ph-bold ph-warning-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>

                            <div wire:loading wire:target="documento_temp_file" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                                <div class="bg-fondo-card p-6 rounded-xl flex flex-col items-center shadow-2xl">
                                    <i class="ph-bold ph-circle-notch animate-spin text-4xl text-boton-acento mb-2"></i>
                                    <p class="font-bold text-titulo">Cargando documento...</p>
                                    <p class="text-xs text-apoyo mt-1">Por favor espere.</p>
                                </div>
                            </div>
                            
                            <!-- Observation Modal Alpine -->
                            <div x-data="{ open: false, key: '' }" 
                                 x-on:open-obs-modal.window="open = true; key = $event.detail.key; @this.prepararObservarDocumento(key)"
                                 x-on:close-obs-modal.window="open = false"
                                 x-show="open" 
                                 style="display: none;"
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                                <div class="bg-fondo-card rounded-xl shadow-2xl w-full max-w-md p-6" x-on:click.away="open = false">
                                    <h4 class="text-lg font-bold text-titulo mb-4">Observar Documento</h4>
                                    <textarea wire:model.defer="documento_observacion_temp" rows="4" class="w-full rounded-lg border-input-borde bg-input-bg text-input-texto focus:border-input-bordeFocus focus:ring-input-ringFocus shadow-sm uppercase mb-4" placeholder="Describa la razón de la observación (ej. Imagen borrosa, documento caducado...)"></textarea>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" x-on:click="open = false" class="px-4 py-2 text-sm font-bold text-boton-secundario bg-fondo-hover border border-borde-suave rounded-lg hover:bg-borde-suave transition">Cancelar</button>
                                        <button type="button" wire:click="observarDocumento" x-on:click="open = false" class="px-4 py-2 text-sm font-bold text-white bg-estado-peligro hover:bg-red-600 rounded-lg transition">Guardar Observación</button>
                                    </div>
                                </div>
                            </div>

                        @elseif($paso == 7)
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-bold text-titulo">Paso 7: Documentos Institucionales</h4>
                                <button type="button" wire:click="generarTodosDocumentos" class="bg-modulo-personal text-white px-4 py-2 rounded-lg text-sm font-bold shadow hover:bg-modulo-personalHover transition flex items-center gap-2">
                                    <i class="ph-bold ph-magic-wand"></i> Generar Todos
                                </button>
                            </div>
                            <p class="text-sm text-apoyo mb-4">Genere, imprima y solicite las firmas de los documentos legales e institucionales requeridos.</p>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach($this->documentos_autogenerados as $key => $doc)
                                    <div class="bg-fondo-card border {{ $doc['estado'] === 'SUBIDO_FIRMADO' || $doc['estado'] === 'FIRMADO' ? 'border-estado-exito' : ($doc['estado'] === 'PENDIENTE' ? 'border-borde-suave' : 'border-estado-info') }} rounded-lg p-4 shadow-sm flex flex-col justify-between">
                                        <div class="mb-3">
                                            <div class="flex justify-between items-start">
                                                <h6 class="text-sm font-bold text-titulo pr-2">{{ $doc['titulo'] }}</h6>
                                                @if($doc['obligatorio'])
                                                    <span class="text-[10px] font-bold bg-estado-peligro text-white px-1.5 py-0.5 rounded">REQ</span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex items-center gap-2">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded 
                                                    {{ $doc['estado'] === 'PENDIENTE' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 
                                                       ($doc['estado'] === 'GENERADO' ? 'bg-estado-infoBg text-estado-info' : 
                                                       ($doc['estado'] === 'IMPRESO' ? 'bg-modulo-cognitivoFondo text-modulo-cognitivoTexto' : 
                                                       'bg-estado-exitoBg text-estado-exito')) }}">
                                                    {{ str_replace('_', ' ', $doc['estado']) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-wrap gap-2 justify-end mt-2">
                                            @if($doc['estado'] === 'PENDIENTE')
                                                <button type="button" wire:click="generarDocumento('{{ $key }}')" class="text-xs font-bold text-modulo-personal bg-modulo-personalSuave hover:bg-modulo-personal/20 px-3 py-1.5 rounded-lg flex items-center gap-1 transition">
                                                    <i class="ph-bold ph-file-pdf"></i> Generar
                                                </button>
                                            @else
                                                <button type="button" wire:click="descargarDocumento('{{ $key }}')" class="text-xs font-bold text-boton-secundario bg-fondo-hover border border-borde-suave hover:bg-borde-suave px-2 py-1.5 rounded-lg transition" title="Descargar PDF">
                                                    <i class="ph-bold ph-download-simple"></i>
                                                </button>
                                                
                                                @if($doc['estado'] !== 'SUBIDO_FIRMADO')
                                                    <button type="button" wire:click="marcarImpreso('{{ $key }}')" class="text-xs font-bold text-modulo-cognitivoTexto bg-modulo-cognitivoFondo hover:bg-modulo-cognitivoFondo/80 px-2 py-1.5 rounded-lg transition" title="Marcar como impreso">
                                                        <i class="ph-bold ph-printer"></i>
                                                    </button>
                                                    <button type="button" wire:click="marcarFirmado('{{ $key }}')" class="text-xs font-bold text-estado-exito bg-estado-exitoBg hover:bg-green-200 px-2 py-1.5 rounded-lg transition" title="Marcar como firmado físicamente">
                                                        <i class="ph-bold ph-signature"></i>
                                                    </button>
                                                    <label class="cursor-pointer text-xs font-bold text-white bg-boton-acento hover:bg-boton-acentoHover px-2 py-1.5 rounded-lg flex items-center gap-1 transition" wire:click="prepararSubidaFirma('{{ $key }}')" title="Subir documento firmado escaneado">
                                                        <i class="ph-bold ph-upload-simple"></i>
                                                        <input type="file" wire:model.live="doc_auto_upload" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                                    </label>
                                                @else
                                                    <a href="{{ $doc['temp_url'] }}" target="_blank" class="text-xs font-bold text-boton-secundario bg-fondo-hover border border-borde-suave hover:bg-borde-suave px-2 py-1.5 rounded-lg transition">
                                                        <i class="ph-bold ph-eye"></i> Ver Subido
                                                    </a>
                                                    <label class="cursor-pointer text-xs font-bold text-boton-secundario bg-fondo-hover border border-borde-suave hover:bg-borde-suave px-2 py-1.5 rounded-lg transition" wire:click="prepararSubidaFirma('{{ $key }}')">
                                                        <i class="ph-bold ph-arrows-clockwise"></i>
                                                        <input type="file" wire:model.live="doc_auto_upload" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                                                    </label>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div wire:loading wire:target="doc_auto_upload" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
                                <div class="bg-fondo-card p-6 rounded-xl flex flex-col items-center shadow-2xl">
                                    <i class="ph-bold ph-circle-notch animate-spin text-4xl text-boton-acento mb-2"></i>
                                    <p class="font-bold text-titulo">Subiendo documento firmado...</p>
                                    <p class="text-xs text-apoyo mt-1">Por favor espere.</p>
                                </div>
                            </div>

                        @elseif($paso == 8)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 8: Enfermero Valorador</h4>
                            <p class="text-sm text-apoyo mb-4">Asigne un enfermero para que realice la valoración inicial y triage.</p>
                        @elseif($paso == 8)
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-bold text-titulo">Paso 8: Enfermero Valorador Inicial</h4>
                                <span class="text-xs font-semibold px-2 py-1 bg-estado-infoBg text-estado-info rounded-md border border-estado-infoBorde">Asignación Temporal</span>
                            </div>
                            <p class="text-sm text-apoyo mb-4">Seleccione el enfermero de turno que realizará la valoración inicial (triage). Este NO será necesariamente el enfermero definitivo del residente.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($this->enfermeros_valoradores as $enf)
                                    <div class="bg-fondo-card border {{ $enfermero_asignado == $enf['id'] ? 'border-boton-acento ring-2 ring-boton-acento/20' : 'border-borde-suave hover:border-borde-fuerte' }} rounded-xl p-4 shadow-sm cursor-pointer transition relative" wire:click="seleccionarEnfermero({{ $enf['id'] }}, {{ $enf['en_turno'] ? 'true' : 'false' }})">
                                        @if($enfermero_asignado == $enf['id'])
                                            <div class="absolute top-3 right-3 text-boton-acento bg-boton-acento/10 p-1 rounded-full">
                                                <i class="ph-fill ph-check-circle text-xl"></i>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-3 mb-3">
                                            <div class="h-10 w-10 rounded-full bg-modulo-personalSuave text-modulo-personal flex items-center justify-center font-bold text-lg border border-modulo-personal/20">
                                                {{ substr($enf['nombre'], 0, 1) }}
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-bold text-titulo leading-tight pr-6">{{ $enf['nombre'] }}</h5>
                                                <p class="text-[10px] text-apoyo uppercase tracking-wider">{{ $enf['tipo'] }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                                            <div class="bg-fondo-hover p-2 rounded border border-borde-suave flex flex-col justify-center items-center text-center">
                                                <p class="text-[10px] text-apoyo font-semibold mb-0.5">Turno Actual</p>
                                                <p class="font-bold {{ $enf['en_turno'] ? 'text-estado-info' : 'text-estado-peligro' }}">{{ $enf['turno_actual'] }}</p>
                                            </div>
                                            <div class="bg-fondo-hover p-2 rounded border border-borde-suave flex flex-col justify-center items-center text-center">
                                                <p class="text-[10px] text-apoyo font-semibold mb-0.5">Pendientes</p>
                                                <p class="font-bold text-titulo">{{ $enf['pendientes'] }} casos</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-borde-suave">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $enf['disponibilidad'] == 'ALTA' ? 'bg-estado-exitoBg text-estado-exito' : ($enf['disponibilidad'] == 'MEDIA' ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-estado-peligroBg text-estado-peligro') }}">
                                                DISP. {{ $enf['disponibilidad'] }}
                                            </span>
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border {{ $enf['estado'] == 'ACTIVO' ? 'border-estado-exito text-estado-exito' : 'border-estado-peligro text-estado-peligro' }}">
                                                {{ $enf['estado'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('enfermero_asignado') <span class="text-estado-peligro text-xs font-medium block mt-3 text-center bg-estado-peligroBg p-2 rounded"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror

                        @elseif($paso == 9)
                            <h4 class="text-lg font-bold text-titulo mb-4">Paso 9: Confirmación Final</h4>
                            <p class="text-sm text-apoyo mb-4">Revise que todos los requisitos institucionales estén cumplidos antes de generar el ingreso.</p>
                            
                            <div class="bg-fondo-card border border-borde-suave rounded-xl p-0 overflow-hidden mb-4">
                                <div class="bg-estado-infoBg px-4 py-3 border-b border-estado-infoBorde flex items-center justify-between">
                                    <h5 class="font-bold text-estado-info flex items-center gap-2"><i class="ph-bold ph-list-checks text-xl"></i> Checklist de Preadmisión</h5>
                                    <span class="text-xs font-bold bg-white text-estado-info px-2 py-1 rounded shadow-sm">Adulto Mayor: {{ $nombres }} {{ $ap_paterno }}</span>
                                </div>
                                
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">1. Datos Personales Completos</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">2. Dirección Registrada</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">3. Familiar Responsable ({{ $familiar_nombres }})</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">4. Motivo y Procedencia Definidos</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">5. Documentos Obligatorios Validados</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">6. Control de Pendientes 48h Activo</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">7. Documentos Institucionales Listos</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="ph-fill ph-check-circle text-estado-exito text-lg"></i>
                                        <span class="text-titulo">8. Enfermero Asignado</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-estado-infoBg border border-estado-infoBorde rounded-xl p-4 shadow-sm text-sm text-estado-info">
                                <p class="font-medium leading-relaxed">
                                    Al confirmar, el paciente ingresará formalmente al sistema bajo el estado 
                                    <span class="font-bold bg-estado-info text-white px-2 py-0.5 rounded text-xs mx-1 shadow-sm">VALORACIÓN INICIAL</span>.
                                </p>
                                <p class="text-xs mt-2 text-estado-info/80"><i class="ph-bold ph-info"></i> El enfermero valorador recibirá una notificación inmediata en su panel operativo para proceder con el Triage. No se ha ocupado ninguna cama ni generado planes definitivos aún.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="bg-modal-bg px-6 py-4 border-t border-modal-footerBorde flex justify-between items-center rounded-b-2xl">
                    <button wire:click="cerrarModalNuevo" class="px-4 py-2 text-sm font-bold text-apoyo hover:text-texto-principal transition-colors">
                        Cancelar
                    </button>
                    
                    <div class="flex items-center gap-3">
                        @if($paso > 1)
                            <button wire:click="anteriorPaso" class="px-4 py-2 bg-boton-fantasma hover:bg-boton-fantasmaHover text-boton-fantasmaTexto border border-boton-fantasmaBorde text-sm font-bold rounded-lg transition-colors">
                                Anterior
                            </button>
                        @endif

                        @if($paso < $totalPasos)
                            <button wire:click="siguientePaso" class="px-4 py-2 bg-boton-acento hover:bg-boton-acentoHover text-boton-acentoTexto shadow-glow text-sm font-bold rounded-lg transition-colors">
                                Continuar
                            </button>
                            <button wire:click="guardarBorrador" class="px-4 py-2 bg-boton-secundario hover:bg-boton-secundarioHover text-boton-secundarioTexto border border-borde text-sm font-bold rounded-lg transition-colors">
                                Guardar Borrador
                            </button>
                        @else
                            <button wire:click="procesarConfirmacion" class="px-5 py-2 bg-estado-exitoBg hover:bg-estado-exitoBorde text-estado-exito border border-estado-exitoBorde shadow-sm text-sm font-bold rounded-lg transition-colors flex items-center gap-2">
                                <i class="ph-bold ph-check-circle text-lg"></i>
                                Confirmar Preadmisión
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        /* Estilos dinámicos para inputs con errores basados en el DOM adyacente */
        input:has(+ span.text-estado-peligro), 
        select:has(+ span.text-estado-peligro), 
        textarea:has(+ span.text-estado-peligro) {
            border-color: #ef4444 !important; /* Rojo de estado peligro */
            color: #ef4444 !important;
        }
        input:has(+ span.text-estado-peligro):focus, 
        select:has(+ span.text-estado-peligro):focus, 
        textarea:has(+ span.text-estado-peligro):focus {
            --tw-ring-color: #ef4444 !important;
        }
    </style>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('admisionesCharts', () => ({
                init() {
                    this.initCharts();
                },
                initCharts() {
                    const ctxDonaEstado = document.getElementById('chartDonaEstado');
                    if(ctxDonaEstado) {
                        new Chart(ctxDonaEstado, {
                            type: 'doughnut',
                            data: {
                                labels: ['Pendiente Val. Inicial', 'En Val. Inicial', 'Pendiente Val. Médica', 'Pendiente Decisión'],
                                datasets: [{
                                    data: [{{ $metricas['pendientes_valoracion_inicial'] }}, {{ $metricas['valoracion_inicial'] }}, {{ $metricas['pendientes_valoracion_medica'] }}, {{ $metricas['pendientes_decision'] }}],
                                    backgroundColor: ['#f59e0b', '#60a5fa', '#3b82f6', '#a855f7'],
                                    borderWidth: 0
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } } }
                        });
                    }

                    const ctxBarrasEtapa = document.getElementById('chartBarrasEtapa');
                    if(ctxBarrasEtapa) {
                        new Chart(ctxBarrasEtapa, {
                            type: 'bar',
                            data: {
                                labels: ['Pre', 'Val. In.', 'Val. Med.', 'Decisión', 'Asign.'],
                                datasets: [{
                                    label: 'Procesos',
                                    data: [{{ $metricas['preadmisiones_activas'] }}, {{ $metricas['pendientes_valoracion_inicial'] }}, {{ $metricas['pendientes_valoracion_medica'] }}, {{ $metricas['pendientes_decision'] }}, 1],
                                    backgroundColor: '#7a9d8c',
                                    borderRadius: 4
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } } }
                        });
                    }

                    const ctxLineaFecha = document.getElementById('chartLineaFecha');
                    if(ctxLineaFecha) {
                        new Chart(ctxLineaFecha, {
                            type: 'line',
                            data: {
                                labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
                                datasets: [{
                                    label: 'Nuevas preadmisiones',
                                    data: [2, 5, 3, 8, 4, 1, 0],
                                    borderColor: '#c48b71',
                                    backgroundColor: 'rgba(196, 139, 113, 0.1)',
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } } }
                        });
                    }

                    const ctxBarrasDoc = document.getElementById('chartBarrasDoc');
                    if(ctxBarrasDoc) {
                        new Chart(ctxBarrasDoc, {
                            type: 'bar',
                            data: {
                                labels: ['CI Adulto', 'CI Familiar', 'Croquis', 'Contrato', 'Cert. Médico'],
                                datasets: [{
                                    label: 'Pendientes',
                                    data: [2, 4, 15, 30, 25],
                                    backgroundColor: '#f87171',
                                    borderRadius: 4
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { borderDash: [2, 4] } }, x: { grid: { display: false } } } }
                        });
                    }

                    const ctxDonaDecision = document.getElementById('chartDonaDecision');
                    if(ctxDonaDecision) {
                        new Chart(ctxDonaDecision, {
                            type: 'doughnut',
                            data: {
                                labels: ['En proceso', 'Derivado'],
                                datasets: [{
                                    data: [45, {{ $metricas['derivados'] }}],
                                    backgroundColor: ['#10b981', '#f97316'],
                                    borderWidth: 0
                                }]
                            },
                            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } } }
                        });
                    }
                }
            }));
        });
    </script>

    <script>
        // Navegación rápida por formulario usando teclas de flecha
        document.addEventListener('keydown', function(e) {
            if (['ArrowUp', 'ArrowDown'].includes(e.key)) {
                if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'SELECT') {
                    if(document.activeElement.tagName !== 'TEXTAREA') {
                        e.preventDefault();
                    }
                    
                    const interactables = Array.from(document.querySelectorAll('.fixed.inset-0 input:not([type="hidden"]):not([disabled]), .fixed.inset-0 select:not([disabled])')).filter(el => el.offsetParent !== null);
                    const index = interactables.indexOf(document.activeElement);
                    if (index > -1) {
                        if (e.key === 'ArrowDown' && index < interactables.length - 1) {
                            interactables[index + 1].focus();
                        } else if (e.key === 'ArrowUp' && index > 0) {
                            interactables[index - 1].focus();
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</div>
