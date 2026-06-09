<div class="space-y-6">

    {{-- Breadcrumb + Encabezado --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-start md:justify-between">
        <div>
            <nav class="mb-2 flex text-[10px] font-bold uppercase tracking-widest text-meta" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1">
                    <li>
                        <a href="{{ route('admin.psicologia.dashboard') }}" class="hover:text-parrafo">
                            Psicología
                        </a>
                    </li>
                    <li class="flex items-center">
                        <i class="ph-bold ph-caret-right mx-1"></i>
                        <span class="text-apoyo">{{ $nombreArea }}</span>
                    </li>
                </ol>
            </nav>
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }}">
                    <i class="ph-bold {{ $areaConfig['icono'] }} text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-titulo">
                        Evaluación {{ $nombreArea }}
                    </h2>
                    @if($descripcionArea)
                    <p class="text-sm font-semibold text-apoyo">{{ $descripcionArea }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.psicologia.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel">
                <i class="ph-bold ph-arrow-left"></i> Volver
            </a>
            <button wire:click="nuevaEvaluacion()"
                    class="inline-flex items-center gap-2 rounded-xl bg-boton-principal px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-sm transition hover:-translate-y-0.5 hover:shadow-md active:scale-95">
                <i class="ph-bold ph-plus-circle text-sm"></i> Nueva Evaluación
            </button>
        </div>
    </div>

    {{-- Stats del área --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }}">
                <i class="ph-bold ph-list-checks text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Evaluaciones</p>
                <p class="text-2xl font-black text-titulo">{{ $totalEvaluaciones }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                <i class="ph-bold ph-user-check text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Pacientes cubiertos</p>
                <p class="text-2xl font-black text-titulo">{{ $pacientesCubiertos }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-borde bg-fondo-card p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-estado-peligroBg text-estado-peligro">
                <i class="ph-bold ph-warning-circle text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-apoyo">Alertas críticas</p>
                <p class="text-2xl font-black text-titulo">{{ $alertasCriticas }}</p>
            </div>
        </div>
    </div>

    {{-- Instrumentos disponibles --}}
    @if($instrumentos->count() > 0)
    <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
        <p class="mb-3 text-[10px] font-bold uppercase tracking-widest text-apoyo">Instrumentos disponibles en esta área</p>
        <div class="flex flex-wrap gap-2">
            @foreach($instrumentos as $inst)
            <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-card px-3 py-1 text-[11px] font-bold text-parrafo">
                <span class="{{ $areaConfig['color_txt'] }} font-black">{{ $inst->siglas }}</span>
                {{ $inst->nombre }}
                @if($inst->puntaje_maximo)
                <span class="text-apoyo">/ {{ number_format($inst->puntaje_maximo, 0) }} pts</span>
                @endif
            </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Filtros --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
            <input wire:model.live.debounce.300ms="busqueda"
                   type="text"
                   placeholder="Buscar por nombre del paciente o CI..."
                   class="w-full rounded-xl border border-borde bg-fondo-card py-2.5 pl-10 pr-4 text-sm font-semibold text-titulo placeholder-apoyo outline-none transition focus:border-borde-focus">
        </div>
        <select wire:model.live="filtroAlerta"
                class="rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
            <option value="">Todos los niveles</option>
            <option value="NORMAL">Normal</option>
            <option value="PREVENTIVO">Preventivo</option>
            <option value="CRITICO">Crítico</option>
        </select>
        <select wire:model.live="filtroInstrumento"
                class="rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-sm font-bold text-titulo outline-none transition focus:border-borde-focus">
            <option value="">Todos los instrumentos</option>
            @foreach($instrumentos as $inst)
            <option value="{{ $inst->cod_instrumento }}">{{ $inst->siglas }} — {{ $inst->nombre }}</option>
            @endforeach
        </select>
        @if($busqueda || $filtroAlerta || $filtroInstrumento)
        <button wire:click="$set('busqueda', ''); $set('filtroAlerta', ''); $set('filtroInstrumento', '')"
                class="flex items-center gap-1.5 rounded-xl border border-borde bg-fondo-card px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-parrafo transition hover:bg-fondo-panel">
            <i class="ph-bold ph-x"></i> Limpiar
        </button>
        @endif
    </div>

    {{-- Tabla de evaluaciones --}}
    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        @if($evaluaciones->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-3">Paciente</th>
                        <th class="px-5 py-3">Instrumento</th>
                        <th class="px-5 py-3">Puntaje</th>
                        <th class="px-5 py-3">Resultado / Categoría</th>
                        <th class="px-5 py-3">Nivel Alerta</th>
                        <th class="px-5 py-3">Evaluador</th>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($evaluaciones as $eval)
                    @php
                        $alertaClass = match($eval->nivel_alerta) {
                            'CRITICO'    => 'bg-estado-peligro text-white',
                            'PREVENTIVO' => 'bg-estado-advertencia text-white',
                            default      => 'bg-estado-exitoBg text-estado-exito',
                        };
                    @endphp
                    <tr class="hover:bg-fondo-panel/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }} text-sm font-black">
                                    {{ substr($eval->adulto?->nombres ?? '?', 0, 1) }}{{ substr($eval->adulto?->ap_paterno ?? '', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-titulo">
                                        {{ $eval->adulto?->nombres ?? '—' }} {{ $eval->adulto?->ap_paterno ?? '' }}
                                    </div>
                                    <div class="text-[10px] text-apoyo">CI: {{ $eval->adulto?->ci ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-bold text-titulo">{{ $eval->instrumento?->siglas ?? '—' }}</div>
                            <div class="text-[10px] text-apoyo max-w-[160px] truncate">{{ $eval->instrumento?->nombre ?? '' }}</div>
                        </td>
                        <td class="px-5 py-3 font-black text-titulo">
                            {{ $eval->puntaje_total !== null ? number_format($eval->puntaje_total, 0) : '—' }}
                            @if($eval->instrumento?->puntaje_maximo)
                            <span class="text-[10px] font-semibold text-apoyo">/ {{ number_format($eval->instrumento->puntaje_maximo, 0) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-xs font-semibold text-parrafo max-w-[160px] truncate"
                            title="{{ $eval->categoria_resultado ?? '' }}">
                            {{ $eval->categoria_resultado ?? '—' }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $alertaClass }}">
                                {{ $eval->nivel_alerta ?? 'NORMAL' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs text-apoyo">
                            {{ $eval->evaluador?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 text-xs font-medium text-apoyo">
                            {{ $eval->fecha_eval ? \Carbon\Carbon::parse($eval->fecha_eval)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <button wire:click="nuevaEvaluacion('{{ $eval->adulto?->cod_am }}')"
                                    title="Nueva evaluación para este paciente"
                                    class="h-8 w-8 rounded-lg bg-fondo-panel text-parrafo hover:bg-boton-acento hover:text-white transition-colors flex items-center justify-center mx-auto">
                                <i class="ph-bold ph-plus text-sm"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-borde px-5 py-3">
            {{ $evaluaciones->links() }}
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }}">
                <i class="ph-bold {{ $areaConfig['icono'] }} text-3xl"></i>
            </div>
            <h3 class="mt-4 text-lg font-bold text-titulo">
                Sin evaluaciones registradas
                @if($busqueda || $filtroAlerta || $filtroInstrumento)
                    con estos filtros
                @endif
            </h3>
            <p class="mt-1 text-sm text-apoyo max-w-sm">
                @if($busqueda || $filtroAlerta || $filtroInstrumento)
                    Intenta modificar los filtros de búsqueda.
                @else
                    Registra la primera evaluación de <strong>{{ $nombreArea }}</strong> para un adulto mayor.
                @endif
            </p>
            @if(!$busqueda && !$filtroAlerta && !$filtroInstrumento)
            <button wire:click="nuevaEvaluacion()"
                    class="mt-5 inline-flex items-center gap-2 rounded-xl bg-boton-principal px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-inverso shadow-md transition hover:-translate-y-0.5 active:scale-95">
                <i class="ph-bold ph-plus-circle text-sm"></i> Nueva Evaluación
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- Observaciones clínicas por área --}}
    <div class="rounded-2xl border border-borde bg-fondo-panel p-5">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $areaConfig['color_bg'] }} {{ $areaConfig['color_txt'] }}">
                <i class="ph-bold ph-info text-base"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-wider text-titulo mb-1">
                    Guía de Interpretación — Área {{ $nombreArea }}
                </p>
                @php
                    $guias = [
                        'ARE_COG' => 'Evalúa memoria, orientación, lenguaje y funciones ejecutivas. Instrumentos: MMSE (≥24 normal), MoCA (≥26 normal), Mini-Cog (0-2 riesgo). Puntajes bajos indican deterioro cognitivo que requiere seguimiento especializado.',
                        'ARE_AFE' => 'Detecta síntomas depresivos y estado emocional. GDS-15: 0-4 normal, 5-8 depresión leve, ≥9 depresión severa. CESD-7: ≥6 sugiere sintomatología depresiva. Requiere intervención psicológica inmediata si es crítico.',
                        'ARE_FUN' => 'Mide la capacidad funcional y autonomía. Katz (0-6 pts): evalúa actividades básicas. Lawton (0-8 pts): actividades instrumentales. SPPB ≤9 indica fragilidad. TUG >12 seg indica riesgo de caída.',
                        'ARE_NUT' => 'Detecta riesgo de malnutrición. MNA-SF (≥12 normal, 8-11 riesgo, ≤7 malnutrido). MUST: 0 bajo riesgo, ≥2 alto riesgo. SARC-F: ≥4 sugiere sarcopenia.',
                        'ARE_SOC' => 'Evalúa recursos sociales, familiares y entorno físico. Detecta riesgo de maltrato, aislamiento y barreras arquitectónicas. Escala de maltrato: ≥6 indica riesgo significativo.',
                    ];
                @endphp
                <p class="text-xs font-semibold text-parrafo leading-relaxed">
                    {{ $guias[$codArea] ?? 'Consultar los criterios específicos del instrumento utilizado.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Modal de nueva evaluación --}}
    @livewire('admin.psicologia.evaluacion-geriatrica-area-modal')

</div>
