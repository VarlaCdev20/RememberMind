<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-boton-acento/10 text-boton-acento">
                <i class="ph-fill ph-brain text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">Dashboard Psicología</h2>
                <p class="text-sm font-semibold text-apoyo">Evaluaciones geriátricas multidimensionales</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="$refresh" class="rm-btn-secondary h-10 px-4">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                <span class="hidden sm:inline">Actualizar</span>
            </button>
        </div>
    </div>

    {{-- Stats globales --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                <i class="ph-bold ph-users text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Total Pacientes</p>
                <p class="text-3xl font-black text-titulo">{{ $totalPacientes }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                <i class="ph-bold ph-list-checks text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Total Evaluaciones</p>
                <p class="text-3xl font-black text-titulo">{{ $totalEvaluaciones }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-estado-peligroBg text-estado-peligro">
                <i class="ph-bold ph-warning-circle text-2xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Alertas Críticas</p>
                <p class="text-3xl font-black text-titulo">{{ $alertasCriticas }}</p>
            </div>
        </div>
    </div>

    {{-- Áreas de Evaluación Geriátrica --}}
    <div>
        <h3 class="mb-4 text-xs font-black uppercase tracking-widest text-apoyo">Áreas de Evaluación Geriátrica</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach($statsPorArea as $codArea => $area)
            <a href="{{ route($area['ruta']) }}"
               class="group flex flex-col gap-3 rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-borde hover:shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $area['color_bg'] }} {{ $area['color_txt'] }}">
                        <i class="ph-bold {{ $area['icono'] }} text-2xl"></i>
                    </div>
                    @if($area['criticas'] > 0)
                    <span class="flex h-6 min-w-[24px] items-center justify-center rounded-full bg-estado-peligro px-1.5 text-[10px] font-black text-white">
                        {{ $area['criticas'] }}
                    </span>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-black text-titulo group-hover:text-boton-acento transition-colors">
                        {{ $area['nombre'] }}
                    </h4>
                    <p class="mt-0.5 text-[11px] font-semibold text-apoyo">
                        {{ $area['total'] }} evaluación{{ $area['total'] !== 1 ? 'es' : '' }}
                    </p>
                </div>
                <div class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider {{ $area['color_txt'] }}">
                    Ver panel <i class="ph-bold ph-arrow-right text-xs"></i>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    {{-- Evaluaciones Recientes --}}
    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-borde bg-fondo-panel px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                    <i class="ph-bold ph-clock-counter-clockwise text-lg"></i>
                </div>
                <h3 class="text-sm font-black text-titulo">Evaluaciones Recientes</h3>
            </div>
        </div>

        @if($evaluacionesRecientes->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-3">Paciente</th>
                        <th class="px-5 py-3">Área</th>
                        <th class="px-5 py-3">Instrumento</th>
                        <th class="px-5 py-3">Puntaje</th>
                        <th class="px-5 py-3">Resultado</th>
                        <th class="px-5 py-3">Alerta</th>
                        <th class="px-5 py-3">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($evaluacionesRecientes as $eval)
                    @php
                        $alertaClass = match($eval->nivel_alerta) {
                            'CRITICO'   => 'bg-estado-peligro text-white',
                            'PREVENTIVO'=> 'bg-estado-advertencia text-white',
                            default     => 'bg-estado-exitoBg text-estado-exito',
                        };
                    @endphp
                    <tr class="hover:bg-fondo-panel/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="font-bold text-titulo">
                                {{ $eval->adulto?->nombres ?? '—' }}
                                {{ $eval->adulto?->ap_paterno ?? '' }}
                            </div>
                            <div class="text-[10px] text-apoyo">CI: {{ $eval->adulto?->ci ?? '—' }}</div>
                        </td>
                        <td class="px-5 py-3 text-xs font-semibold text-parrafo">
                            {{ $eval->instrumento?->area?->nombre ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-xs font-bold text-titulo">{{ $eval->instrumento?->siglas ?? '—' }}</div>
                            <div class="text-[10px] text-apoyo">{{ $eval->instrumento?->nombre ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3 font-black text-titulo">
                            {{ $eval->puntaje_total !== null ? number_format($eval->puntaje_total, 0) : '—' }}
                            @if($eval->instrumento?->puntaje_maximo)
                            <span class="text-[10px] font-semibold text-apoyo">/ {{ number_format($eval->instrumento->puntaje_maximo, 0) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-semibold text-parrafo">
                            {{ $eval->categoria_resultado ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $alertaClass }}">
                                {{ $eval->nivel_alerta ?? 'NORMAL' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs font-medium text-apoyo">
                            {{ $eval->fecha_eval ? \Carbon\Carbon::parse($eval->fecha_eval)->format('d/m/Y') : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-apoyo">
                <i class="ph-bold ph-clipboard-text text-3xl"></i>
            </div>
            <h3 class="mt-4 text-base font-bold text-titulo">Sin evaluaciones registradas</h3>
            <p class="mt-1 text-sm text-apoyo">
                Selecciona un área para comenzar a registrar evaluaciones geriátricas.
            </p>
        </div>
        @endif
    </div>

</div>
