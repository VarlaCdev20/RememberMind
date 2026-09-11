{{-- TAB 7: HISTORIAL 360° (TRAYECTORIA CLÍNICA Y CUIDADOS DEL RESIDENTE) --}}
<div class="space-y-6">
    {{-- CABECERA DE LA PESTAÑA HISTORIAL 360° --}}
    <div class="rounded-3xl border border-borde bg-fondo-panel p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-borde pb-4">
            <div>
                <h2 class="text-base font-bold tracking-tight text-titulo flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-blue-600"></i>
                    <span>Historial 360°</span>
                </h2>
                <p class="text-xs text-apoyo mt-0.5 font-medium">Trayectoria clínica y de cuidados del residente · Cronología de Eventos Clínicos 360°</p>
            </div>

            {{-- Selector de Rango de Fechas --}}
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5 rounded-xl border border-borde bg-fondo-card px-2.5 py-1.5 text-xs text-parrafo">
                    <span class="text-[10px] font-bold uppercase text-apoyo">Desde:</span>
                    <input type="date" wire:model.live="historialFechaDesde" class="bg-transparent text-xs text-titulo border-none p-0 focus:ring-0 focus:outline-none" />
                </div>
                <div class="flex items-center gap-1.5 rounded-xl border border-borde bg-fondo-card px-2.5 py-1.5 text-xs text-parrafo">
                    <span class="text-[10px] font-bold uppercase text-apoyo">Hasta:</span>
                    <input type="date" wire:model.live="historialFechaHasta" class="bg-transparent text-xs text-titulo border-none p-0 focus:ring-0 focus:outline-none" />
                </div>
                @if($historialFechaDesde || $historialFechaHasta || $historialFiltroTipo !== 'TODOS')
                    <button type="button" wire:click="limpiarFiltrosHistorial" class="rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition">
                        Limpiar filtros
                    </button>
                @endif
            </div>
        </div>

        {{-- Barra de Filtros Temáticos --}}
        <div class="mt-3 flex flex-wrap items-center gap-1.5">
            @foreach([
                'TODOS' => 'Todos',
                'SIGNOS' => 'Signos',
                'MEDICACION' => 'Medicación',
                'CUIDADOS' => 'Cuidados',
                'SEGUIMIENTO' => 'Seguimiento',
                'ALERTAS' => 'Alertas',
                'INCIDENTES' => 'Incidentes',
                'VALORACIONES' => 'Valoraciones',
                'PASES' => 'Pases de turno'
            ] as $keyFiltro => $labelFiltro)
                <button type="button"
                        wire:click="setHistorialFiltro('{{ $keyFiltro }}')"
                        class="rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $historialFiltroTipo === $keyFiltro ? 'bg-blue-600 text-white shadow-sm' : 'bg-fondo-card text-parrafo hover:bg-borde' }}">
                    {{ $labelFiltro }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- CUERPO: TIMELINE + RESUMEN LONGITUDINAL --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-start">
        {{-- COLUMNA PRINCIPAL: TIMELINE CLÍNICA --}}
        <div class="space-y-4 lg:col-span-8">
            @php
                $eventos = $this->historialFiltrado;
            @endphp

            @forelse($eventos as $ev)
                <div class="rounded-2xl border border-borde bg-fondo-panel p-4 shadow-sm transition hover:border-blue-300">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5 border-b border-borde/50 pb-2 mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2 py-0.5 text-[10px] font-bold uppercase
                                {{ $ev['tipo'] === 'SIGNOS' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $ev['tipo'] === 'MEDICACION' ? 'bg-teal-50 text-teal-700 border border-teal-200' : '' }}
                                {{ $ev['tipo'] === 'TAREA' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                {{ $ev['tipo'] === 'SEGUIMIENTO' ? 'bg-sky-50 text-sky-700 border border-sky-200' : '' }}
                                {{ $ev['tipo'] === 'ALERTA' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                {{ $ev['tipo'] === 'VALORACION' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : '' }}
                                {{ $ev['tipo'] === 'PASE_TURNO' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}">
                                <i class="ph-bold {{ $ev['icon'] ?? 'ph-circle' }}"></i>
                                {{ $ev['tipo_label'] ?? $ev['tipo'] }}
                            </span>
                            <span class="text-xs font-bold text-titulo">{{ $ev['titulo'] }}</span>
                        </div>
                        <div class="text-[11px] text-apoyo font-medium">
                            {{ $ev['fecha'] ? \Carbon\Carbon::parse($ev['fecha'])->format('d/m/Y') : '' }}
                            @if($ev['hora']) · {{ substr($ev['hora'], 0, 5) }} @endif
                        </div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <p class="font-medium text-parrafo leading-relaxed">
                            {{ $ev['resumen'] }}
                        </p>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-borde/40 text-[11px] text-apoyo">
                            <span>Responsable: <strong class="text-parrafo">{{ $ev['responsable'] }}</strong></span>
                            @if(!empty($ev['estado_badge']))
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase bg-fondo-card text-parrafo border border-borde">
                                    {{ $ev['estado_badge'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-borde bg-fondo-panel p-10 text-center">
                    <i class="ph-bold ph-calendar-blank text-3xl text-apoyo mb-2 block"></i>
                    <p class="text-sm font-bold text-titulo">No se encontraron eventos en este período o filtro.</p>
                    <p class="text-xs text-apoyo mt-1">Pruebe seleccionando "Todos" o ampliando el rango de fechas.</p>
                </div>
            @endforelse
        </div>

        {{-- COLUMNA LATERAL: RESUMEN LONGITUDINAL --}}
        <div class="space-y-4 lg:col-span-4">
            <div class="rounded-3xl border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-2 border-b border-borde pb-2">
                    <i class="ph-bold ph-chart-donut text-blue-600"></i>
                    <span>Resumen Longitudinal</span>
                </h3>

                {{-- Adherencia Farmacológica --}}
                <div class="rounded-2xl bg-fondo-card p-3 border border-borde space-y-1.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-parrafo">Adherencia Medicación</span>
                        <span class="font-black text-emerald-700">{{ $this->resumenLongitudinal['adherencia_pct'] }}%</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-borde overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $this->resumenLongitudinal['adherencia_pct'] }}%;"></div>
                    </div>
                    <p class="text-[10px] text-apoyo">
                        {{ $this->resumenLongitudinal['admin_ok'] }} administradas de {{ $this->resumenLongitudinal['total_admin'] }} programadas
                    </p>
                </div>

                {{-- Cumplimiento de Cuidados --}}
                <div class="rounded-2xl bg-fondo-card p-3 border border-borde space-y-1.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-parrafo">Cumplimiento Cuidados</span>
                        <span class="font-black text-blue-700">{{ $this->resumenLongitudinal['cumplimiento_pct'] }}%</span>
                    </div>
                    <div class="h-1.5 w-full rounded-full bg-borde overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ $this->resumenLongitudinal['cumplimiento_pct'] }}%;"></div>
                    </div>
                    <p class="text-[10px] text-apoyo">
                        {{ $this->resumenLongitudinal['tareas_realizadas'] }} realizadas de {{ $this->resumenLongitudinal['total_tareas'] }} totales
                    </p>
                </div>

                {{-- Vigilancia de Alertas --}}
                <div class="rounded-2xl bg-fondo-card p-3 border border-borde text-xs space-y-1">
                    <span class="font-bold text-parrafo block mb-1">Vigilancia de Alertas</span>
                    <div class="grid grid-cols-2 gap-2 text-center">
                        <div class="rounded-lg bg-rose-50 p-2 border border-rose-200">
                            <span class="text-sm font-black text-rose-800 block">{{ $this->resumenLongitudinal['alertas_activas'] }}</span>
                            <span class="text-[10px] font-bold text-rose-700 uppercase">Activas</span>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-2 border border-emerald-200">
                            <span class="text-sm font-black text-emerald-800 block">{{ $this->resumenLongitudinal['alertas_cerradas'] }}</span>
                            <span class="text-[10px] font-bold text-emerald-700 uppercase">Cerradas</span>
                        </div>
                    </div>
                </div>

                {{-- Última Valoración Funcional --}}
                @if(!empty($this->resumenLongitudinal['ultima_valoracion']))
                    @php $val = $this->resumenLongitudinal['ultima_valoracion']; @endphp
                    <div class="rounded-2xl bg-fondo-card p-3 border border-borde text-xs space-y-1">
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Última Valoración Oficial</span>
                        <p class="font-bold text-titulo">{{ $val['instrumento'] }}</p>
                        <p class="text-parrafo text-xs">{{ $val['resultado'] }}</p>
                        <p class="text-[10px] text-apoyo pt-1">
                            {{ $val['fecha'] }} · {{ $val['evaluador'] }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
