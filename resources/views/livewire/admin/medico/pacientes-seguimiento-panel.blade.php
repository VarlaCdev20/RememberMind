<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 border-b border-borde pb-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-estado-infoBg text-estado-info">
                <i class="ph-fill ph-users-three text-3xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black tracking-tight text-titulo">Pacientes — Seguimiento Médico</h2>
                <p class="text-sm font-semibold text-apoyo">Residentes activos y pacientes en valoración</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.medico.dashboard') }}"
               class="rm-btn-secondary h-10 px-4 flex items-center gap-2">
                <i class="ph-bold ph-arrow-left"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>
            <button wire:click="$refresh" class="rm-btn-secondary h-10 w-10 flex items-center justify-center">
                <i class="ph-bold ph-arrows-clockwise text-lg"></i>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-1 rounded-2xl border border-borde bg-fondo-panel p-1">
        <button wire:click="setTab('activos')"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                       {{ $tab === 'activos' ? 'bg-fondo-card text-boton-acento shadow-sm' : 'text-apoyo hover:text-titulo' }}">
            <i class="ph-bold ph-user-check text-sm"></i>
            Residentes activos
            <span class="rounded-full bg-estado-infoBg px-2 py-0.5 text-[10px] font-black text-estado-info">{{ $cntActivos }}</span>
        </button>
        <button wire:click="setTab('pendientes')"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                       {{ $tab === 'pendientes' ? 'bg-fondo-card text-estado-advertencia shadow-sm' : 'text-apoyo hover:text-titulo' }}">
            <i class="ph-bold ph-hourglass text-sm"></i>
            Pend. valoración
            @if($cntPendientes > 0)
            <span class="rounded-full bg-estado-advertencia px-2 py-0.5 text-[10px] font-black text-white">{{ $cntPendientes }}</span>
            @endif
        </button>
        <button wire:click="setTab('interconsultas')"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                       {{ $tab === 'interconsultas' ? 'bg-fondo-card text-estado-info shadow-sm' : 'text-apoyo hover:text-titulo' }}">
            <i class="ph-bold ph-arrows-left-right text-sm"></i>
            Interconsultas
            @if($cntInterconsultas > 0)
            <span class="rounded-full bg-estado-infoBg px-2 py-0.5 text-[10px] font-black text-estado-info">{{ $cntInterconsultas }}</span>
            @endif
        </button>
        <button wire:click="setTab('historial')"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-black uppercase tracking-wider transition
                       {{ $tab === 'historial' ? 'bg-fondo-card text-apoyo shadow-sm' : 'text-apoyo hover:text-titulo' }}">
            <i class="ph-bold ph-clock-clockwise text-sm"></i>
            Historial clínico
        </button>
    </div>

    {{-- Buscador --}}
    <div class="relative max-w-sm">
        <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-apoyo"></i>
        <input wire:model.live.debounce.300ms="busqueda"
               type="text"
               placeholder="Buscar por nombre o CI..."
               class="w-full rounded-xl border border-borde bg-fondo-card py-2.5 pl-10 pr-4 text-sm font-semibold text-titulo placeholder-apoyo outline-none transition focus:border-borde-focus">
    </div>

    {{-- Tabla --}}
    <div class="rounded-[24px] border border-borde bg-fondo-card shadow-sm overflow-hidden">
        @if($pacientes->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-fondo-panel text-[10px] font-bold uppercase tracking-wider text-apoyo">
                    <tr>
                        <th class="px-5 py-3">Paciente</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Últ. Signos Vitales</th>
                        <th class="px-5 py-3">Últ. Nota Médica</th>
                        <th class="px-5 py-3 text-center">Medicación</th>
                        <th class="px-5 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @foreach($pacientes as $pac)
                    @php
                        $sv = $ultimosSignos[$pac->cod_am] ?? null;
                        $nota = $ultimasNotas[$pac->cod_am] ?? null;
                        $meds = $cntMedicacion[$pac->cod_am] ?? 0;
                        $edad = $pac->fecha_nac
                            ? \Carbon\Carbon::parse($pac->fecha_nac)->age
                            : '—';

                        $estadoColor = match($pac->estado?->estado) {
                            'EN_SEGUIMIENTO_ACTIVO' => 'bg-estado-exitoBg text-estado-exito',
                            'PENDIENTE_VALORACION_MEDICA' => 'bg-estado-advertenciaBg text-estado-advertencia',
                            'DECISION_ADMISION' => 'bg-estado-infoBg text-estado-info',
                            'OBSERVADO' => 'bg-boton-acento/10 text-boton-acento',
                            default => 'bg-fondo-panel text-apoyo',
                        };

                        // Alertas de signos
                        $alertaSv = false;
                        if ($sv) {
                            $sist = $sv->presion_sistolica ?? 0;
                            $fc   = $sv->frecuencia_cardiaca ?? 0;
                            $sat  = $sv->saturacion ?? 100;
                            $alertaSv = $sist > 160 || $sist < 90 || $fc > 100 || $fc < 50 || $sat < 92;
                        }
                    @endphp
                    <tr class="hover:bg-fondo-panel/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-estado-infoBg text-estado-info font-black text-sm">
                                    {{ substr($pac->nombres, 0, 1) }}{{ substr($pac->ap_paterno, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-titulo">{{ $pac->nombres }} {{ $pac->ap_paterno }}</div>
                                    <div class="text-[10px] text-apoyo">{{ $edad }} años · CI: {{ $pac->ci }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider {{ $estadoColor }}">
                                {{ str_replace('_', ' ', $pac->estado?->estado ?? 'SIN ESTADO') }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($sv)
                                <div class="flex items-center gap-1.5">
                                    @if($alertaSv)
                                    <i class="ph-bold ph-warning text-estado-advertencia text-sm"></i>
                                    @endif
                                    <div>
                                        @if($sv->presion_sistolica)
                                        <div class="text-xs font-bold {{ $alertaSv ? 'text-estado-advertencia' : 'text-titulo' }}">
                                            PA: {{ $sv->presion_sistolica }}/{{ $sv->presion_diastolica }}
                                        </div>
                                        @endif
                                        @if($sv->frecuencia_cardiaca)
                                        <div class="text-[10px] text-apoyo">FC: {{ $sv->frecuencia_cardiaca }} bpm · Sat: {{ $sv->saturacion }}%</div>
                                        @endif
                                        <div class="text-[10px] text-meta">{{ \Carbon\Carbon::parse($sv->fecha)->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-apoyo italic">Sin registro</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            @if($nota)
                                <div>
                                    <div class="text-xs font-bold text-titulo">{{ $nota['tipo_nota'] ?? 'EVOLUCION' }}</div>
                                    <div class="text-[10px] text-apoyo max-w-[150px] truncate">{{ $nota['valoracion'] ?? '' }}</div>
                                    <div class="text-[10px] text-meta">{{ \Carbon\Carbon::parse($nota['fecha'])->diffForHumans() }}</div>
                                </div>
                            @else
                                <span class="text-xs text-apoyo italic">Sin notas</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if($meds > 0)
                            <span class="inline-flex items-center gap-1 rounded-full bg-boton-acento/10 px-2.5 py-0.5 text-[10px] font-black text-boton-acento">
                                <i class="ph-bold ph-pill text-xs"></i> {{ $meds }}
                            </span>
                            @else
                            <span class="text-xs text-apoyo">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-1.5">
                                <button wire:click="abrirFicha('{{ $pac->cod_am }}')"
                                        title="Ver ficha clínica integrada"
                                        class="h-8 px-2.5 rounded-lg bg-estado-infoBg text-estado-info hover:bg-estado-info hover:text-white transition text-[10px] font-black uppercase tracking-wider flex items-center gap-1">
                                    <i class="ph-bold ph-folder-open text-sm"></i> Ficha
                                </button>
                                <button wire:click="nuevaNota('{{ $pac->cod_am }}')"
                                        title="Nueva nota de evolución"
                                        class="h-8 w-8 rounded-lg bg-estado-exitoBg text-estado-exito hover:bg-estado-exito hover:text-white transition flex items-center justify-center">
                                    <i class="ph-bold ph-note-pencil text-sm"></i>
                                </button>
                                <button wire:click="nuevosSignos('{{ $pac->cod_am }}')"
                                        title="Registrar signos vitales"
                                        class="h-8 w-8 rounded-lg bg-fondo-panel text-parrafo hover:bg-boton-acento hover:text-white transition flex items-center justify-center">
                                    <i class="ph-bold ph-heartbeat text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-borde px-5 py-3">
            {{ $pacientes->links() }}
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-fondo-panel text-apoyo">
                <i class="ph-bold ph-users text-3xl"></i>
            </div>
            <h3 class="mt-4 text-base font-bold text-titulo">
                @if($tab === 'pendientes') Sin valoraciones pendientes
                @elseif($tab === 'interconsultas') Sin interconsultas activas
                @elseif($tab === 'historial') Sin pacientes en el historial
                @else Sin residentes activos
                @endif
            </h3>
            <p class="mt-1 text-sm text-apoyo">
                @if($tab === 'pendientes') No hay pacientes esperando valoración médica.
                @elseif($tab === 'interconsultas') No hay notas de interconsulta registradas.
                @elseif($tab === 'historial') No hay registros en el historial clínico.
                @else No hay residentes en seguimiento activo.
                @endif
            </p>
        </div>
        @endif
    </div>

    @livewire('admin.medico.nota-evolucion-medica-modal')
    @livewire('admin.medico.registro-signos-vitales-modal')
    @livewire('admin.medico.valoracion-barthel-modal')
</div>
