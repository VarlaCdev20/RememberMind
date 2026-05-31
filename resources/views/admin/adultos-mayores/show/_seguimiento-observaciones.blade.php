{{-- TAB SEGUIMIENTO UNIFICADO Y PREMIUM --}}
<section
    x-show="tab === 'seguimiento'"
    x-data="{ filtro: 'todos' }"
    x-transition.opacity.duration.250ms
    class="space-y-6"
>


    @php
        $idAdulto = $adulto->cod_am;

        // 1. Construir feed unificado de registros activos
        $feed = collect();

        foreach ($observacionesActivas as $obs) {
            $feed->push([
                'id' => $obs->cod_obs_adul,
                'tipo' => 'observacion',
                'tipo_label' => 'Observación',
                'fecha' => \Carbon\Carbon::parse($obs->fecha),
                'hora' => null,
                'titulo' => $obs->tipo_obs,
                'descripcion' => $obs->descripcion,
                'responsable' => $obs->registrador?->name ?? 'Responsable registrado',
                'badge' => strtoupper($obs->nivel_importancia ?? 'NORMAL'),
                'badge_color' => match(strtoupper($obs->nivel_importancia ?? 'NORMAL')) {
                    'URGENTE' => 'bg-rose-50 text-rose-800 border-rose-200/50',
                    'ALTA' => 'bg-amber-50 text-amber-800 border-amber-200/50',
                    default => 'bg-[#6873A6]/10 text-[#566189] border-[#6873A6]/20',
                },
                'icono' => 'ph-fill ph-notebook',
                'icono_color' => 'bg-[#6873A6]/10 text-[#566189]',
                'raw_data' => $obs,
                'estado' => null,
            ]);
        }

        foreach ($atencionesActivas as $aten) {
            $feed->push([
                'id' => $aten->cod_aten_adul,
                'tipo' => 'atencion',
                'tipo_label' => 'Atención registrada',
                'fecha' => \Carbon\Carbon::parse($aten->fecha),
                'hora' => $aten->hora ? substr($aten->hora, 0, 5) : null,
                'titulo' => $aten->tipoAtencion->tipo ?? 'Atención',
                'descripcion' => $aten->obs ?: 'Sin notas registradas.',
                'responsable' => 'Profesional responsable',
                'badge' => strtoupper($aten->estado ?? 'PENDIENTE'),
                'badge_color' => match(strtoupper($aten->estado ?? 'PENDIENTE')) {
                    'FINALIZADA', 'REALIZADA' => 'bg-emerald-50 text-emerald-800 border-emerald-200/55',
                    'CANCELADA' => 'bg-rose-50 text-rose-800 border-rose-200/55',
                    default => 'bg-amber-50 text-amber-850 border-amber-200/55',
                },
                'icono' => 'ph-fill ph-stethoscope',
                'icono_color' => 'bg-[#9A7B60]/10 text-[#7A5C49]',
                'raw_data' => $aten,
                'estado' => $aten->estado,
            ]);
        }

        foreach ($actividadesActivas as $act) {
            $feed->push([
                'id' => $act->cod_act_adul,
                'tipo' => 'actividad',
                'tipo_label' => 'Actividad',
                'fecha' => \Carbon\Carbon::parse($act->fecha),
                'hora' => $act->hora ? substr($act->hora, 0, 5) : null,
                'titulo' => $act->tipoActividad->tipo ?? 'Actividad',
                'descripcion' => $act->obs ?: 'Sin observaciones de participación registradas.',
                'responsable' => 'Responsable registrado',
                'badge' => strtoupper($act->estado ?? 'PROGRAMADA'),
                'badge_color' => match(strtoupper($act->estado ?? 'PROGRAMADA')) {
                    'COMPLETADA', 'REALIZADA' => 'bg-emerald-50 text-emerald-800 border-emerald-200/55',
                    'CANCELADA' => 'bg-rose-50 text-rose-800 border-rose-200/55',
                    default => 'bg-amber-50 text-amber-850 border-amber-200/55',
                },
                'icono' => 'ph-fill ph-calendar-check',
                'icono_color' => 'bg-[#D9A27C]/10 text-[#9B6D4C]',
                'raw_data' => $act,
                'estado' => $act->estado,
            ]);
        }

        // Ordenar feed cronológicamente por fecha y hora descendente
        $feed = $feed->sortByDesc(function ($item) {
            $dateStr = $item['fecha']->format('Y-m-d');
            $timeStr = $item['hora'] ? $item['hora'] . ':00' : '00:00:00';
            return $dateStr . ' ' . $timeStr;
        });

        // 2. Calcular último registro de seguimiento real
        $fechas = [];
        if ($feed->isNotEmpty()) {
            foreach($feed as $item) {
                $fechas[] = $item['fecha'];
            }
        }
        $ultimoRegistroTexto = 'Ninguno';
        if (count($fechas) > 0) {
            $maxFecha = collect($fechas)->max();
            $ultimoRegistroTexto = $maxFecha->format('d/m/Y');
        }

        // 3. Construir feed de anulados
        $anuladosFeed = collect();

        foreach ($observacionesAnuladas as $obsAnu) {
            $anuladosFeed->push([
                'id' => $obsAnu->cod_obs_adul,
                'tipo' => 'observacion',
                'tipo_label' => 'Observación',
                'fecha_anul' => $obsAnu->deleted_at,
                'titulo' => $obsAnu->tipo_obs,
                'descripcion' => $obsAnu->descripcion,
                'restore_route' => route('admin.adultos-mayores.observaciones.restore', [$idAdulto, $obsAnu->cod_obs_adul]),
                'confirm_title' => 'Restaurar observación',
                'confirm_text' => 'La observación volverá a estar visible en el expediente activo.',
            ]);
        }

        foreach ($atencionesAnuladas as $ateAnu) {
            $anuladosFeed->push([
                'id' => $ateAnu->cod_aten_adul,
                'tipo' => 'atencion',
                'tipo_label' => 'Atención registrada',
                'fecha_anul' => $ateAnu->deleted_at,
                'titulo' => $ateAnu->tipoAtencion->tipo ?? 'Atención',
                'descripcion' => $ateAnu->obs ?: $ateAnu->motivo_consulta,
                'restore_route' => route('admin.adultos-mayores.atenciones.restore', [$idAdulto, $ateAnu->cod_aten_adul]),
                'confirm_title' => 'Restaurar atención',
                'confirm_text' => 'El registro de atención volverá al historial activo del paciente.',
            ]);
        }

        foreach ($actividadesAnuladas as $actAnu) {
            $anuladosFeed->push([
                'id' => $actAnu->cod_act_adul,
                'tipo' => 'actividad',
                'tipo_label' => 'Actividad',
                'fecha_anul' => $actAnu->deleted_at,
                'titulo' => $actAnu->tipoActividad->tipo ?? 'Actividad',
                'descripcion' => $actAnu->obs ?? 'Sin detalle',
                'restore_route' => route('admin.adultos-mayores.actividades.restore', [$idAdulto, $actAnu->cod_act_adul]),
                'confirm_title' => 'Restaurar actividad',
                'confirm_text' => 'El registro de participación volverá a la vista activa del expediente.',
            ]);
        }

        $anuladosFeed = $anuladosFeed->sortByDesc('fecha_anul');
    @endphp

    <!-- HEADER BLOCK -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#CBBBAA]/30 pb-5">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#6873A6]/10 text-[#6873A6]">
                <i class="ph-fill ph-notebook text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-[#2F3E5C]">Seguimiento</h2>
                <p class="text-sm font-semibold text-[#2F3E5C]/60">Observaciones, atenciones registradas y participación en actividades del adulto mayor.</p>
            </div>
        </div>
        
        <!-- Acciones Rápidas (Modales Reales Existentes) -->
        <div class="flex flex-wrap gap-2">
            <button 
                type="button"
                @click="abrir('observacion')" 
                class="inline-flex items-center gap-2 rounded-xl bg-[#6873A6] px-4 py-2.5 text-xs font-black text-white shadow-sm hover:bg-[#586393] active:scale-95 transition shrink-0"
            >
                <i class="ph-bold ph-plus"></i>
                <span>Registrar Observación</span>
            </button>

            <button 
                type="button"
                @click="abrir('atencion')" 
                class="inline-flex items-center gap-2 rounded-xl bg-[#9A7B60] px-4 py-2.5 text-xs font-black text-white shadow-sm hover:bg-[#7A5C49] active:scale-95 transition shrink-0"
            >
                <i class="ph-bold ph-plus"></i>
                <span>Registrar Atención</span>
            </button>

            <button 
                type="button"
                @click="abrir('actividad')" 
                class="inline-flex items-center gap-2 rounded-xl bg-[#D9A27C] px-4 py-2.5 text-xs font-black text-white shadow-sm hover:bg-[#C98F67] active:scale-95 transition shrink-0"
            >
                <i class="ph-bold ph-plus"></i>
                <span>Registrar Actividad</span>
            </button>
        </div>
    </div>

    <!-- METRICS GRID -->
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        <!-- Observaciones -->
        <div class="rounded-2xl border border-[#CBBBAA]/45 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Observaciones</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#6873A6]">{{ $totalObservaciones }}</span>
                <span class="text-xs font-bold text-[#2F3E5C]/40">registros</span>
            </div>
        </div>

        <!-- Atenciones -->
        <div class="rounded-2xl border border-[#CBBBAA]/45 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Atenciones Registradas</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#9A7B60]">{{ $totalAtenciones }}</span>
                <span class="text-xs font-bold text-[#2F3E5C]/40">citas</span>
            </div>
        </div>

        <!-- Actividades -->
        <div class="rounded-2xl border border-[#CBBBAA]/45 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Actividades</p>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-3xl font-black text-[#D9A27C]">{{ $totalActividades }}</span>
                <span class="text-xs font-bold text-[#2F3E5C]/40">talleres</span>
            </div>
        </div>

        <!-- Último Registro -->
        <div class="rounded-2xl border border-[#CBBBAA]/45 bg-white p-4 shadow-xs">
            <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/50">Último Registro</p>
            <div class="mt-2">
                <span class="text-xl font-black text-[#8EA17D] truncate block">{{ $ultimoRegistroTexto }}</span>
                <span class="text-[10px] font-bold text-[#2F3E5C]/40 block mt-1">Fecha de actividad reciente</span>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="flex items-center justify-between border-b border-[#CBBBAA]/20 pb-2">
        <div class="flex flex-wrap items-center gap-1.5 bg-[#EBE3DB]/40 p-1 rounded-xl border border-[#CBBBAA]/30">
            <button 
                type="button"
                @click="filtro = 'todos'"
                :class="filtro === 'todos' ? 'bg-[#2F3E5C] text-white shadow-xs' : 'text-[#2F3E5C] hover:bg-[#2F3E5C]/5'"
                class="px-4 py-1.5 text-xs font-black rounded-lg transition active:scale-95"
            >
                Todos
            </button>
            <button 
                type="button"
                @click="filtro = 'observaciones'"
                :class="filtro === 'observaciones' ? 'bg-[#6873A6] text-white shadow-xs' : 'text-[#6873A6] hover:bg-[#6873A6]/10'"
                class="px-4 py-1.5 text-xs font-black rounded-lg transition active:scale-95"
            >
                Observaciones
            </button>
            <button 
                type="button"
                @click="filtro = 'atenciones'"
                :class="filtro === 'atenciones' ? 'bg-[#9A7B60] text-white shadow-xs' : 'text-[#9A7B60] hover:bg-[#9A7B60]/10'"
                class="px-4 py-1.5 text-xs font-black rounded-lg transition active:scale-95"
            >
                Atenciones
            </button>
            <button 
                type="button"
                @click="filtro = 'actividades'"
                :class="filtro === 'actividades' ? 'bg-[#D9A27C] text-white shadow-xs' : 'text-[#D9A27C] hover:bg-[#D9A27C]/10'"
                class="px-4 py-1.5 text-xs font-black rounded-lg transition active:scale-95"
            >
                Actividades
            </button>
        </div>

        <span class="text-xs font-bold text-[#2F3E5C]/50">
            Total en vista: {{ $feed->count() }}
        </span>
    </div>

    <!-- TIMELINE FEED -->
    <div class="space-y-4 relative">
        <!-- Línea central decorativa de timeline para pantallas grandes -->
        <div class="absolute left-6 top-6 bottom-6 w-0.5 bg-[#CBBBAA]/30 hidden md:block"></div>

        @forelse($feed as $item)
            <div 
                x-show="filtro === 'todos' || filtro === '{{ $item['tipo'] }}s'"
                class="group relative flex flex-col md:flex-row gap-4 rounded-2xl border border-[#CBBBAA]/45 bg-white p-5 shadow-xs transition-all duration-200 hover:shadow-md hover:border-[#CBBBAA]/80"
                x-transition
            >
                <!-- Indicador visual tipo e ícono en la línea de tiempo -->
                <div class="flex items-center gap-3 md:items-start shrink-0">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $item['icono_color'] }} z-10 shadow-2xs">
                        <i class="{{ $item['icono'] }} text-xl"></i>
                    </div>
                    <div class="md:hidden">
                        <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full border {{ $item['badge_color'] }}">
                            {{ $item['tipo_label'] }}
                        </span>
                        <p class="text-[10px] font-bold text-[#2F3E5C]/50 mt-1">
                            {{ $item['fecha']->format('d/m/Y') }} @if($item['hora']) &middot; {{ $item['hora'] }} @endif
                        </p>
                    </div>
                </div>

                <!-- Contenido principal de la Tarjeta -->
                <div class="flex-1 min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <h4 class="text-base font-black text-[#2F3E5C] leading-snug">{{ $item['titulo'] }}</h4>
                            <span class="hidden md:inline-flex text-[9px] font-black uppercase px-2 py-0.5 rounded-full border {{ $item['badge_color'] }}">
                                {{ $item['tipo_label'] }}
                            </span>
                            @if($item['badge'] && $item['badge'] !== 'NORMAL')
                                <span class="inline-flex text-[9px] font-black uppercase px-2 py-0.5 rounded-full {{ $item['badge_color'] }}">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </div>

                        <!-- Fecha y Hora versión Desktop -->
                        <span class="hidden md:inline-flex items-center text-xs font-bold text-[#2F3E5C]/50">
                            <i class="ph-bold ph-calendar mr-1"></i>
                            {{ $item['fecha']->format('d/m/Y') }}
                            @if($item['hora'])
                                <i class="ph-bold ph-clock ml-2.5 mr-1"></i>
                                {{ $item['hora'] }}
                            @endif
                        </span>
                    </div>

                    <!-- Descripción Detallada -->
                    <p class="text-xs font-semibold text-[#2F3E5C]/75 leading-relaxed break-words">
                        {{ $item['descripcion'] }}
                    </p>

                    <!-- Responsable e Información de Seguimiento -->
                    <div class="flex items-center justify-between pt-2 border-t border-[#CBBBAA]/20 text-[10px] font-bold text-[#2F3E5C]/45">
                        <span class="inline-flex items-center">
                            <i class="ph-bold ph-user-circle mr-1"></i>
                            {{ $item['responsable'] }}
                        </span>
                        
                        @if($item['estado'])
                            <span class="text-emerald-700">Estado de ejecución: {{ $item['estado'] }}</span>
                        @endif
                    </div>
                </div>

                <!-- Acciones Rápidas Unificadas (Modales Existentes en _modales-existentes.blade.php) -->
                <div class="flex md:flex-col justify-end gap-1.5 shrink-0 border-t border-[#CBBBAA]/10 pt-3 md:pt-0 md:border-t-0 md:pl-2">
                    <button 
                        type="button" 
                        @click="abrir('{{ $item['tipo'] }}', @js($item['raw_data']), false, true)" 
                        title="Ver detalles"
                        class="flex-1 md:flex-none inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#2F3E5C]/5 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition active:scale-95"
                    >
                        <i class="ph-bold ph-eye text-base"></i>
                        <span class="md:hidden ml-2 text-xs font-bold">Ver</span>
                    </button>
                    
                    <button 
                        type="button" 
                        @click="abrir('{{ $item['tipo'] }}', @js($item['raw_data']), true, false)" 
                        title="Editar registro"
                        class="flex-1 md:flex-none inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#2F3E5C]/5 text-[#2F3E5C] hover:bg-[#2F3E5C] hover:text-white transition active:scale-95"
                    >
                        <i class="ph-bold ph-pencil-simple text-base"></i>
                        <span class="md:hidden ml-2 text-xs font-bold">Editar</span>
                    </button>

                    <!-- Formulario de anulación con confirmación SweetAlert integrada -->
                    @php
                        $destroyRoute = match($item['tipo']) {
                            'observacion' => route('admin.adultos-mayores.observaciones.destroy', [$idAdulto, $item['id']]),
                            'atencion' => route('admin.adultos-mayores.atenciones.destroy', [$idAdulto, $item['id']]),
                            'actividad' => route('admin.adultos-mayores.actividades.destroy', [$idAdulto, $item['id']]),
                        };
                        $confirmTitle = match($item['tipo']) {
                            'observacion' => 'Anular observación',
                            'atencion' => 'Anular atención registrada',
                            'actividad' => 'Anular registro de actividad',
                        };
                        $confirmText = match($item['tipo']) {
                            'observacion' => 'Esta observación será anulada del registro activo. Se conservará la información original.',
                            'atencion' => 'La atención registrada se marcará como anulada para fines de trazabilidad y auditoría.',
                            'actividad' => 'La participación en esta actividad será anulada del expediente activo.',
                        };
                    @endphp
                    <form 
                        action="{{ $destroyRoute }}" 
                        method="POST" 
                        class="flex-1 md:flex-none"
                        onsubmit="confirmarAccion(event, '{{ $confirmTitle }}', '{{ $confirmText }}')"
                    >
                        @csrf 
                        @method('DELETE')
                        <button 
                            type="submit" 
                            title="Anular" 
                            class="w-full md:w-9 inline-flex h-9 items-center justify-center rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-700 hover:text-white transition active:scale-95"
                        >
                            <i class="ph-bold ph-x-circle text-base"></i>
                            <span class="md:hidden ml-2 text-xs font-bold">Anular</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <!-- Este empty se muestra solo si la base de datos realmente no tiene nada -->
            <div class="rounded-2xl border-2 border-dashed border-[#C7B5A3]/40 p-12 text-center bg-[#F8F2EC]/30">
                <i class="ph-fill ph-folder-open text-5xl text-[#C7B5A3] mb-3"></i>
                <p class="text-sm font-bold text-[#2F3E5C]/60">Sin registros de seguimiento.</p>
            </div>
        @endforelse

        <!-- ESTADOS VACÍOS CON ALPINE (Basado en filtros interactivos) -->
        <div 
            x-show="filtro === 'todos' && {{ $feed->count() }} === 0" 
            class="rounded-2xl border-2 border-dashed border-[#C7B5A3]/40 p-12 text-center bg-[#F8F2EC]/30"
        >
            <i class="ph-fill ph-folder-open text-5xl text-[#C7B5A3] mb-3"></i>
            <p class="text-sm font-bold text-[#2F3E5C]/60">Sin registros de seguimiento.</p>
            <p class="text-xs text-[#2F3E5C]/40 mt-1">Registre una observación, atención registrada o actividad para iniciar la bitácora.</p>
        </div>

        <div 
            x-show="filtro === 'observaciones' && {{ count($observacionesActivas) }} === 0" 
            class="rounded-2xl border-2 border-dashed border-[#C7B5A3]/40 p-12 text-center bg-[#F8F2EC]/30"
            style="display: none;"
        >
            <i class="ph-fill ph-notebook text-5xl text-[#C7B5A3] mb-3"></i>
            <p class="text-sm font-bold text-[#2F3E5C]/60">Sin observaciones registradas.</p>
        </div>

        <div 
            x-show="filtro === 'atenciones' && {{ count($atencionesActivas) }} === 0" 
            class="rounded-2xl border-2 border-dashed border-[#C7B5A3]/40 p-12 text-center bg-[#F8F2EC]/30"
            style="display: none;"
        >
            <i class="ph-fill ph-stethoscope text-5xl text-[#C7B5A3] mb-3"></i>
            <p class="text-sm font-bold text-[#2F3E5C]/60">Sin atenciones registradas.</p>
        </div>

        <div 
            x-show="filtro === 'actividades' && {{ count($actividadesActivas) }} === 0" 
            class="rounded-2xl border-2 border-dashed border-[#C7B5A3]/40 p-12 text-center bg-[#F8F2EC]/30"
            style="display: none;"
        >
            <i class="ph-fill ph-calendar-check text-5xl text-[#C7B5A3] mb-3"></i>
            <p class="text-sm font-bold text-[#2F3E5C]/60">Sin actividades registradas.</p>
        </div>
    </div>

    <!-- HISTORIAL DE ANULADOS UNIFICADO (Trazabilidad y Auditoría) -->
    @if($anuladosFeed->isNotEmpty())
        <div class="mt-8 border-t border-[#CBBBAA]/20 pt-6">
            <h4 class="mb-4 text-xs font-black uppercase tracking-widest text-[#2F3E5C]/60 flex items-center gap-2">
                <i class="ph-bold ph-trash-simple text-sm"></i> Historial de Registros Anulados
            </h4>
            
            <div class="overflow-hidden rounded-[24px] border border-[#CBBBAA]/50 bg-[#E7DDD2]/45 opacity-70 grayscale-[30%] hover:opacity-100 hover:grayscale-0 transition-all duration-300 shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-[#2F3E5C]">
                        <thead class="bg-[#D5C7B9]/40 text-[9px] uppercase tracking-widest text-[#2F3E5C]/50">
                            <tr>
                                <th class="px-6 py-3">Fecha Anul.</th>
                                <th class="px-6 py-3">Tipo / Clasificación</th>
                                <th class="px-6 py-3">Descripción Original</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D5C7B9]/30">
                            @foreach($anuladosFeed as $anu)
                                <tr class="hover:bg-white/10 transition duration-150">
                                    <td class="px-6 py-3.5 whitespace-nowrap text-xs font-black text-[#2F3E5C]/60">
                                        {{ $anu['fecha_anul']->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex text-[9px] font-black uppercase px-2 py-0.5 rounded-md border border-[#2F3E5C]/15 bg-[#2F3E5C]/5 text-[#2F3E5C]/75">
                                            {{ $anu['tipo_label'] }}
                                        </span>
                                        <p class="text-xs font-black text-[#2F3E5C]/80 mt-1">{{ $anu['titulo'] }}</p>
                                    </td>
                                    <td class="px-6 py-3.5">
                                        <p class="text-xs font-semibold text-[#2F3E5C]/60 line-clamp-1 leading-normal">{{ $anu['descripcion'] }}</p>
                                    </td>
                                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                        <form 
                                            action="{{ $anu['restore_route'] }}" 
                                            method="POST" 
                                            class="inline-block"
                                            onsubmit="confirmarAccion(event, '{{ $anu['confirm_title'] }}', '{{ $anu['confirm_text'] }}')"
                                        >
                                            @csrf 
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                title="Restaurar registro" 
                                                class="rounded-lg bg-emerald-50 border border-emerald-200/50 p-1.5 text-emerald-700 hover:bg-emerald-700 hover:text-white transition active:scale-95"
                                            >
                                                <i class="ph-bold ph-arrow-counter-clockwise text-sm"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</section>