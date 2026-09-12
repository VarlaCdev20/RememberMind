<div
    wire:key="dashboard-medico-root"
    class="rm-page-layout mx-auto max-w-[1600px] space-y-5 pb-10 font-sans"
    x-data="{ ready: false }"
    x-init="$nextTick(() => requestAnimationFrame(() => ready = true))"
>
    @php
        $valoraciones = collect($valoracionesPendientes ?? []);
        $prioridades = collect($prioridadesMedicas ?? [])->take(6);
        $alertasVisibles = collect($alertasPacientes ?? [])->take(4);
        $controlesVisibles = collect($controlesProximos ?? [])->take(4);
        $actividadVisible = collect($notasRecientes ?? [])->take(4);
        $cognicionVisible = collect($resumenCognitivo ?? [])->take(4);
        $incidenciasVisibles = collect($incidenciasMedicacion ?? [])->take(3);

        $dependenciaLabels = $chartDependencia['labels'] ?? [];
        $dependenciaValues = $chartDependencia['values'] ?? [];
        $dependenciaTotal = array_sum($dependenciaValues);

        $rutaResidentes = \Illuminate\Support\Facades\Route::has('admin.medico.residentes')
            ? route('admin.medico.residentes')
            : route('admin.medico.pacientes.observacion');

        $rutaAlertas = \Illuminate\Support\Facades\Route::has('admin.medico.alertas')
            ? route('admin.medico.alertas')
            : route('admin.alertas-clinicas.index');

        $rutaConsultas = \Illuminate\Support\Facades\Route::has('admin.medico.consultas')
            ? route('admin.medico.consultas')
            : $rutaResidentes;

        $rutaCognicion = \Illuminate\Support\Facades\Route::has('admin.medico.cognicion-riesgo')
            ? route('admin.medico.cognicion-riesgo')
            : $rutaResidentes;

        $rutaMedicacion = \Illuminate\Support\Facades\Route::has('admin.medico.medicacion')
            ? route('admin.medico.medicacion')
            : $rutaResidentes;

        $rutaReportes = \Illuminate\Support\Facades\Route::has('admin.medico.reportes')
            ? route('admin.medico.reportes')
            : $rutaResidentes;
    @endphp

    {{-- 0. ESTADO DE ACTUALIZACIÓN LIVEWIRE --}}
    <div
        wire:loading.delay
        class="pointer-events-none fixed right-5 top-5 z-[70] rounded-xl border border-borde bg-fondo-card/95 px-3 py-2 shadow-lg backdrop-blur"
    >
        <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-wider text-apoyo">
            <i class="ph-bold ph-spinner-gap animate-spin text-sm text-estado-info"></i>
            Actualizando panel
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         ENCABEZADO
    ══════════════════════════════════════════════════════ --}}
    <header
        x-cloak
        x-show="ready"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 -translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        style="transition-delay: 0ms"
        class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm motion-reduce:transform-none motion-reduce:transition-none"
    >
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-estado-infoBg text-estado-info shadow-sm">
                    <i class="ph-fill ph-stethoscope text-3xl"></i>
                </div>

                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-estado-info/20 bg-estado-infoBg px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-estado-info">
                            <i class="ph-bold ph-shield-check text-xs"></i>
                            Medicina / Geriatría
                        </span>
                        <span class="text-[10px] font-semibold text-apoyo">Jardín de los Recuerdos</span>
                    </div>

                    <h1 class="mt-2 text-2xl font-black tracking-tight text-titulo">
                        @if($seccion === 'valoraciones')
                            Valoraciones médicas de admisión
                        @elseif($seccion === 'decisiones')
                            Decisiones de admisión
                        @else
                            Panel Médico General
                        @endif
                    </h1>

                    <p
                        class="mt-1 text-xs font-semibold text-apoyo"
                        x-data="{ hora: '' }"
                        x-init="
                            const actualizar = () => {
                                const ahora = new Date();
                                hora = ahora.toLocaleDateString('es-BO', {
                                    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
                                }) + ' · ' + ahora.toLocaleTimeString('es-BO', {
                                    hour: '2-digit', minute: '2-digit'
                                });
                            };
                            actualizar();
                            setInterval(actualizar, 30000);
                        "
                        x-text="hora"
                    ></p>
                </div>
            </div>

            @if($seccion === 'dashboard')
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $rutaResidentes }}" class="rm-btn-secondary flex h-9 items-center gap-2 px-4 text-xs font-bold">
                        <i class="ph-bold ph-users-three"></i>
                        Residentes
                    </a>

                    <a href="{{ route('admin.medico.valoraciones') }}" class="rm-btn-secondary flex h-9 items-center gap-2 px-4 text-xs font-bold">
                        <i class="ph-bold ph-clipboard-text"></i>
                        Valoraciones
                    </a>

                    <button
                        type="button"
                        wire:click="$refresh"
                        wire:loading.attr="disabled"
                        class="rm-btn-secondary flex h-9 items-center gap-2 px-4 text-xs font-bold disabled:cursor-wait disabled:opacity-60"
                    >
                        <i class="ph-bold ph-arrows-clockwise" wire:loading.class="animate-spin"></i>
                        Actualizar
                    </button>
                </div>
            @else
                <a href="{{ route('admin.medico.dashboard') }}" class="rm-btn-secondary flex h-9 items-center gap-2 px-4 text-xs font-bold">
                    <i class="ph-bold ph-arrow-left"></i>
                    Volver al panel médico
                </a>
            @endif
        </div>
    </header>

    @if($seccion === 'dashboard')
        {{-- ══════════════════════════════════════════════════════
             KPIs PRINCIPALES
        ══════════════════════════════════════════════════════ --}}
        <section
            x-cloak
            x-show="ready"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="transition-delay: 70ms"
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4 motion-reduce:transform-none motion-reduce:transition-none"
        >
            <a href="{{ $rutaResidentes }}" class="group rm-card-metric rm-card-interactive border-l-4 border-l-estado-info p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info transition duration-300 group-hover:scale-105">
                        <i class="ph-bold ph-users-three text-lg"></i>
                    </div>
                    <i class="ph-bold ph-arrow-up-right text-xs text-apoyo opacity-0 transition duration-200 group-hover:opacity-100"></i>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-black text-titulo">{{ $totalResidentes }}</p>
                    <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Residentes activos</p>
                    <p class="mt-1 text-[10px] text-meta">Universo clínico del médico</p>
                </div>
            </a>

            <a href="{{ route('admin.medico.valoraciones') }}" class="group rm-card-metric rm-card-interactive border-l-4 border-l-estado-advertencia p-4 {{ $pendientesValoracion > 0 ? 'ring-1 ring-estado-advertencia/25' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia transition duration-300 group-hover:scale-105">
                        <i class="ph-bold ph-clipboard-text text-lg"></i>
                    </div>
                    @if($pendientesValoracion > 0)
                        <span class="rounded-full bg-estado-advertenciaBg px-2 py-1 text-[9px] font-black uppercase tracking-wide text-estado-advertencia">
                            Revisar
                        </span>
                    @endif
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-black text-titulo">{{ $pendientesValoracion }}</p>
                    <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Valoraciones pendientes</p>
                    <p class="mt-1 text-[10px] text-meta">Admisión médica pendiente</p>
                </div>
            </a>

            <a href="{{ $rutaAlertas }}" class="group rm-card-metric rm-card-interactive border-l-4 border-l-estado-error p-4 {{ $alertasCriticas > 0 ? 'ring-1 ring-estado-error/25' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="relative flex h-10 w-10 items-center justify-center rounded-xl bg-estado-errorBg text-estado-error transition duration-300 group-hover:scale-105">
                        <i class="ph-bold ph-bell-ringing text-lg"></i>
                        @if($alertasCriticas > 0)
                            <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-estado-error animate-pulse"></span>
                        @endif
                    </div>
                    <i class="ph-bold ph-arrow-up-right text-xs text-apoyo opacity-0 transition duration-200 group-hover:opacity-100"></i>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-black text-titulo">{{ $alertasAbiertas }}</p>
                    <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Alertas clínicas abiertas</p>
                    <p class="mt-1 text-[10px] {{ $alertasCriticas > 0 ? 'font-bold text-estado-error' : 'text-meta' }}">
                        {{ $alertasCriticas > 0 ? $alertasCriticas . ' crítica(s)' : 'Sin críticas activas' }}
                    </p>
                </div>
            </a>

            <a href="{{ $rutaConsultas }}" class="group rm-card-metric rm-card-interactive border-l-4 border-l-estado-exito p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito transition duration-300 group-hover:scale-105">
                        <i class="ph-bold ph-calendar-check text-lg"></i>
                    </div>
                    @if($controlesVencidos > 0)
                        <span class="rounded-full bg-estado-errorBg px-2 py-1 text-[9px] font-black uppercase tracking-wide text-estado-error">
                            {{ $controlesVencidos }} vencido(s)
                        </span>
                    @endif
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-black text-titulo">{{ $controlesHoy }}</p>
                    <p class="text-[10px] font-black uppercase tracking-wider text-apoyo">Controles para hoy</p>
                    <p class="mt-1 text-[10px] text-meta">Atenciones programadas</p>
                </div>
            </a>
        </section>

        {{-- ══════════════════════════════════════════════════════
             PRIORIDAD MÉDICA + ALERTAS
        ══════════════════════════════════════════════════════ --}}
        <section
            x-cloak
            x-show="ready"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-5"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="transition-delay: 140ms"
            class="grid grid-cols-1 gap-5 xl:grid-cols-12 motion-reduce:transform-none motion-reduce:transition-none"
        >
            <div class="overflow-hidden rounded-2xl border border-borde bg-fondo-card shadow-sm xl:col-span-8">
                <div class="flex flex-col gap-3 border-b border-borde px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-errorBg text-estado-error">
                            <i class="ph-bold ph-first-aid-kit text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-black text-titulo">Atención médica prioritaria</h2>
                            <p class="text-[10px] font-semibold text-apoyo">Alertas, admisiones, controles, cognición y medicación que requieren revisión</p>
                        </div>
                    </div>

                    <a href="{{ $rutaResidentes }}" class="inline-flex items-center gap-1 text-[10px] font-black text-estado-info hover:underline">
                        Ver residentes <i class="ph-bold ph-arrow-right"></i>
                    </a>
                </div>

                @if($prioridades->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-left">
                            <thead class="bg-fondo-panel text-[9px] font-black uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-5 py-3">Residente</th>
                                    <th class="px-5 py-3">Origen</th>
                                    <th class="px-5 py-3">Motivo</th>
                                    <th class="px-5 py-3">Prioridad</th>
                                    <th class="px-5 py-3">Fecha</th>
                                    <th class="px-5 py-3 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde/60">
                                @foreach($prioridades as $item)
                                    @php
                                        $prioridad = strtoupper($item['prioridad'] ?? 'MEDIA');
                                        $origen = strtoupper($item['origen'] ?? 'CLINICA');
                                        $prioridadClase = match($prioridad) {
                                            'CRITICA', 'CRÍTICA' => 'bg-estado-error text-white',
                                            'ALTA' => 'bg-estado-errorBg text-estado-error',
                                            default => 'bg-estado-advertenciaBg text-estado-advertencia',
                                        };
                                        [$origenLabel, $origenIcono] = match($origen) {
                                            'ALERTA_CLINICA' => ['Alerta', 'ph-bell-ringing'],
                                            'ADMISION' => ['Admisión', 'ph-clipboard-text'],
                                            'CONTROL' => ['Control', 'ph-calendar-check'],
                                            'COGNICION' => ['Cognición', 'ph-brain'],
                                            'MEDICACION' => ['Medicación', 'ph-pill'],
                                            default => ['Clínica', 'ph-stethoscope'],
                                        };
                                    @endphp
                                    <tr class="group transition duration-200 hover:bg-fondo-panel/70">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-estado-infoBg text-[10px] font-black text-estado-info transition duration-200 group-hover:scale-105">
                                                    {{ mb_substr($item['paciente'] ?? 'R', 0, 1) }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="max-w-[170px] truncate text-xs font-black text-titulo">{{ $item['paciente'] ?? 'Residente' }}</p>
                                                    <p class="mt-0.5 text-[9px] text-meta">{{ str_replace('_', ' ', $item['estado'] ?? '') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-fondo-panel px-2 py-1 text-[9px] font-black text-apoyo">
                                                <i class="ph-bold {{ $origenIcono }}"></i>{{ $origenLabel }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <p class="max-w-[300px] text-xs font-semibold leading-5 text-titulo">{{ $item['motivo'] ?? 'Revisión médica requerida' }}</p>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-wide {{ $prioridadClase }}">
                                                {{ $prioridad }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-[10px] text-apoyo">{{ $item['fecha'] ?? '—' }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            @if($origen === 'ADMISION' && in_array($item['estado'] ?? '', ['VALORACION_MEDICA', 'PENDIENTE_VALORACION_MEDICA']))
                                                <button
                                                    type="button"
                                                    wire:click="iniciarValoracionMedica('{{ $item['cod_am'] }}')"
                                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-titulo px-3 text-[10px] font-black text-white transition duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                                >
                                                    <i class="ph-bold ph-stethoscope"></i> Valorar
                                                </button>
                                            @elseif($origen === 'ADMISION' && ($item['estado'] ?? '') === 'DECISION_ADMISION')
                                                <button
                                                    type="button"
                                                    wire:click="abrirDecisionAdmision('{{ $item['cod_am'] }}')"
                                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-estado-exito px-3 text-[10px] font-black text-white transition duration-200 hover:-translate-y-0.5 hover:shadow-sm"
                                                >
                                                    <i class="ph-bold ph-check-circle"></i> Dictamen
                                                </button>
                                            @else
                                                <a
                                                    href="{{ route('admin.medico.paciente.ficha', $item['cod_am']) }}"
                                                    class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-estado-infoBg px-3 text-[10px] font-black text-estado-info transition duration-200 hover:-translate-y-0.5 hover:bg-estado-info hover:text-white hover:shadow-sm"
                                                >
                                                    <i class="ph-bold ph-folder-open"></i> Revisar
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex min-h-[260px] flex-col items-center justify-center px-6 py-10 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-estado-exitoBg text-estado-exito">
                            <i class="ph-bold ph-check-circle text-2xl"></i>
                        </div>
                        <h3 class="mt-3 text-sm font-black text-titulo">Sin prioridades clínicas pendientes</h3>
                        <p class="mt-1 max-w-md text-xs text-apoyo">No hay alertas críticas, controles vencidos, valoraciones o incidencias recientes que requieran priorización médica.</p>
                    </div>
                @endif
            </div>

            <div class="space-y-5 xl:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-borde bg-fondo-card shadow-sm">
                    <div class="flex items-center justify-between border-b border-borde px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-estado-errorBg text-estado-error">
                                <i class="ph-bold ph-bell-ringing"></i>
                                @if($alertasCriticas > 0)
                                    <span class="absolute -right-1 -top-1 h-2 w-2 rounded-full bg-estado-error animate-pulse"></span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-titulo">Alertas clínicas</h3>
                                <p class="text-[10px] text-apoyo">{{ $alertasAbiertas }} abiertas · {{ $alertasCriticas }} críticas</p>
                            </div>
                        </div>
                        <a href="{{ $rutaAlertas }}" class="text-[9px] font-black text-estado-info hover:underline">Ver todas</a>
                    </div>

                    @if($alertasVisibles->isNotEmpty())
                        <div class="divide-y divide-borde/60">
                            @foreach($alertasVisibles as $alerta)
                                @php
                                    $nivel = strtoupper($alerta['nivel'] ?? 'MEDIO');
                                    $nivelPunto = match($nivel) {
                                        'CRITICO', 'CRÍTICO' => 'bg-estado-error animate-pulse',
                                        'ALTO' => 'bg-estado-error',
                                        'MEDIO' => 'bg-estado-advertencia',
                                        default => 'bg-estado-info',
                                    };
                                @endphp
                                <div class="group flex items-start gap-3 px-5 py-3.5 transition duration-200 hover:bg-fondo-panel/60">
                                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $nivelPunto }}"></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-xs font-black text-titulo">{{ $alerta['nombre'] ?? 'Residente' }}</p>
                                            <span class="text-[8px] font-black uppercase text-meta">{{ $nivel }}</span>
                                        </div>
                                        <p class="mt-0.5 text-[10px] font-semibold text-apoyo">{{ $alerta['tipo'] ?? 'Alerta clínica' }}</p>
                                        @if(!empty($alerta['motivo']))
                                            <p class="mt-1 line-clamp-2 text-[9px] leading-4 text-meta">{{ $alerta['motivo'] }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.medico.paciente.ficha', $alerta['cod_am']) }}" class="shrink-0 rounded-lg bg-fondo-panel p-2 text-estado-info transition duration-200 group-hover:bg-estado-info group-hover:text-white">
                                        <i class="ph-bold ph-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-5 py-8 text-center">
                            <i class="ph-bold ph-check-circle text-2xl text-estado-exito"></i>
                            <p class="mt-2 text-xs font-bold text-titulo">Sin alertas clínicas abiertas</p>
                            <p class="mt-1 text-[10px] text-apoyo">No hay alertas pendientes de revisión.</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xs font-black uppercase tracking-wider text-apoyo">Próximos controles</h3>
                            <p class="mt-1 text-[10px] text-meta">Agenda clínica registrada</p>
                        </div>
                        <a href="{{ $rutaConsultas }}" class="text-[9px] font-black text-estado-info hover:underline">Ver agenda</a>
                    </div>

                    @if($controlesVisibles->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach($controlesVisibles as $control)
                                <a href="{{ route('admin.medico.paciente.ficha', $control['cod_am']) }}" class="group flex items-center gap-3 rounded-xl border border-borde/70 bg-fondo-panel/40 px-3 py-2.5 transition duration-200 hover:-translate-y-0.5 hover:border-estado-info/40 hover:bg-fondo-panel">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-estado-infoBg text-estado-info">
                                        <i class="ph-bold ph-calendar-check"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-[10px] font-black text-titulo">{{ $control['paciente'] ?? 'Residente' }}</p>
                                        <p class="truncate text-[9px] text-apoyo">{{ $control['tipo'] ?? 'Atención' }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-[9px] font-black text-titulo">{{ $control['fecha'] ?? '—' }}</p>
                                        <p class="text-[9px] text-meta">{{ $control['hora'] ?? '—' }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-3 rounded-xl bg-fondo-panel px-4 py-5 text-center">
                            <i class="ph-bold ph-calendar-blank text-xl text-apoyo"></i>
                            <p class="mt-1 text-[10px] font-bold text-titulo">Sin controles próximos</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════
             RESUMEN CLÍNICO
        ══════════════════════════════════════════════════════ --}}
        <section
            x-cloak
            x-show="ready"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-5"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="transition-delay: 210ms"
            class="grid grid-cols-1 gap-5 lg:grid-cols-3 motion-reduce:transform-none motion-reduce:transition-none"
        >
            {{-- Cognición y riesgo --}}
            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#EEE8FF] text-[#6E56CF] dark:bg-[#2f2850] dark:text-[#b7a8ff]">
                            <i class="ph-bold ph-brain text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-titulo">Cognición y riesgo</h3>
                            <p class="text-[10px] text-apoyo">Última evaluación cognitiva por residente</p>
                        </div>
                    </div>
                    <a href="{{ $rutaCognicion }}" class="text-[9px] font-black text-estado-info hover:underline">Abrir</a>
                </div>

                <div class="mt-4 grid grid-cols-4 gap-2">
                    <div class="rounded-xl bg-estado-errorBg px-2 py-2.5 text-center">
                        <p class="text-lg font-black text-estado-error">{{ $cognitivoAlto }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide text-estado-error">Alto</p>
                    </div>
                    <div class="rounded-xl bg-estado-advertenciaBg px-2 py-2.5 text-center">
                        <p class="text-lg font-black text-estado-advertencia">{{ $cognitivoMedio }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide text-estado-advertencia">Medio</p>
                    </div>
                    <div class="rounded-xl bg-estado-exitoBg px-2 py-2.5 text-center">
                        <p class="text-lg font-black text-estado-exito">{{ $cognitivoBajo }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide text-estado-exito">Bajo</p>
                    </div>
                    <div class="rounded-xl bg-fondo-panel px-2 py-2.5 text-center">
                        <p class="text-lg font-black text-titulo">{{ $cognitivoSinEvaluacion }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide text-apoyo">Sin eval.</p>
                    </div>
                </div>

                @if($cognicionVisible->isNotEmpty())
                    <div class="mt-4 space-y-2 border-t border-borde pt-3">
                        @foreach($cognicionVisible->take(3) as $eval)
                            @php
                                $riesgo = strtoupper($eval['riesgo'] ?? 'SIN_CLASIFICAR');
                                $riesgoClase = match($riesgo) {
                                    'ALTO' => 'text-estado-error bg-estado-errorBg',
                                    'MEDIO' => 'text-estado-advertencia bg-estado-advertenciaBg',
                                    'BAJO' => 'text-estado-exito bg-estado-exitoBg',
                                    default => 'text-apoyo bg-fondo-panel',
                                };
                            @endphp
                            <a href="{{ route('admin.medico.paciente.ficha', $eval['cod_am']) }}" class="group flex items-center gap-3 rounded-xl px-2 py-2 transition duration-200 hover:bg-fondo-panel">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[10px] font-black text-titulo">{{ $eval['paciente'] ?? 'Residente' }}</p>
                                    <p class="mt-0.5 truncate text-[9px] text-apoyo">{{ $eval['instrumento'] ?? 'Evaluación' }} · {{ $eval['fecha'] ?? '—' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-1 text-[8px] font-black uppercase {{ $riesgoClase }}">{{ str_replace('_', ' ', $riesgo) }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-fondo-panel px-4 py-4 text-center">
                        <p class="text-[10px] font-bold text-titulo">Sin evaluaciones cognitivas registradas</p>
                    </div>
                @endif
            </div>

            {{-- Medicación --}}
            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                            <i class="ph-bold ph-pill text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-titulo">Medicación</h3>
                            <p class="text-[10px] text-apoyo">Tratamientos e incidencias registradas</p>
                        </div>
                    </div>
                    <a href="{{ $rutaMedicacion }}" class="text-[9px] font-black text-estado-info hover:underline">Abrir</a>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <div class="rounded-xl bg-fondo-panel px-2 py-3 text-center">
                        <p class="text-lg font-black text-titulo">{{ $totalMedicacionActiva }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide text-apoyo">Activas</p>
                    </div>
                    <div class="rounded-xl {{ $omisionesMedicacion7d > 0 ? 'bg-estado-errorBg' : 'bg-fondo-panel' }} px-2 py-3 text-center">
                        <p class="text-lg font-black {{ $omisionesMedicacion7d > 0 ? 'text-estado-error' : 'text-titulo' }}">{{ $omisionesMedicacion7d }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide {{ $omisionesMedicacion7d > 0 ? 'text-estado-error' : 'text-apoyo' }}">Omisiones 7d</p>
                    </div>
                    <div class="rounded-xl {{ $reevaluacionesMedicacion > 0 ? 'bg-estado-advertenciaBg' : 'bg-fondo-panel' }} px-2 py-3 text-center">
                        <p class="text-lg font-black {{ $reevaluacionesMedicacion > 0 ? 'text-estado-advertencia' : 'text-titulo' }}">{{ $reevaluacionesMedicacion }}</p>
                        <p class="text-[8px] font-black uppercase tracking-wide {{ $reevaluacionesMedicacion > 0 ? 'text-estado-advertencia' : 'text-apoyo' }}">Reevaluar</p>
                    </div>
                </div>

                @if($incidenciasVisibles->isNotEmpty())
                    <div class="mt-4 space-y-2 border-t border-borde pt-3">
                        @foreach($incidenciasVisibles as $incidencia)
                            <a href="{{ route('admin.medico.paciente.ficha', $incidencia['cod_am']) }}" class="group flex items-center gap-3 rounded-xl px-2 py-2 transition duration-200 hover:bg-fondo-panel">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-estado-errorBg text-estado-error">
                                    <i class="ph-bold ph-warning-circle text-sm"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-[10px] font-black text-titulo">{{ $incidencia['paciente'] ?? 'Residente' }}</p>
                                    <p class="mt-0.5 truncate text-[9px] text-apoyo">{{ $incidencia['medicamento'] ?? 'Medicamento' }} · {{ $incidencia['motivo'] ?? 'Omisión' }}</p>
                                </div>
                                <span class="shrink-0 text-[8px] font-bold text-meta">{{ $incidencia['fecha'] ?? '—' }}</span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-estado-exitoBg/40 px-4 py-4 text-center">
                        <i class="ph-bold ph-check-circle text-lg text-estado-exito"></i>
                        <p class="mt-1 text-[10px] font-bold text-titulo">Sin omisiones recientes registradas</p>
                    </div>
                @endif
            </div>

            {{-- Estado funcional --}}
            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-advertenciaBg text-estado-advertencia">
                        <i class="ph-bold ph-person-simple-walk text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-titulo">Estado funcional</h3>
                        <p class="text-[10px] text-apoyo">Última dependencia registrada por residente</p>
                    </div>
                </div>

                @if($dependenciaTotal > 0)
                    <div class="mt-4 space-y-3">
                        @foreach($dependenciaLabels as $i => $label)
                            @php
                                $valor = (int) ($dependenciaValues[$i] ?? 0);
                                $porcentaje = $dependenciaTotal > 0 ? round(($valor / $dependenciaTotal) * 100) : 0;
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-[10px]">
                                    <span class="truncate font-semibold text-apoyo">{{ $label }}</span>
                                    <span class="font-black text-titulo">{{ $valor }}</span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-fondo-panel">
                                    <div class="h-full rounded-full bg-estado-info transition-all duration-700" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 border-t border-borde pt-3 text-[9px] leading-4 text-meta">
                        Se resume una valoración funcional vigente/reciente por residente para evitar duplicar históricos.
                    </p>
                @else
                    <div class="mt-5 rounded-xl bg-fondo-panel px-4 py-5 text-center">
                        <i class="ph-bold ph-person-simple-walk text-xl text-apoyo"></i>
                        <p class="mt-2 text-xs font-bold text-titulo">Sin valoraciones funcionales</p>
                    </div>
                @endif
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════
             ACTIVIDAD + TENDENCIA
        ══════════════════════════════════════════════════════ --}}
        <section
            x-cloak
            x-show="ready"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-5"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="transition-delay: 280ms"
            class="grid grid-cols-1 gap-5 xl:grid-cols-12 motion-reduce:transform-none motion-reduce:transition-none"
        >
            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm xl:col-span-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                            <i class="ph-bold ph-clock-counter-clockwise text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-titulo">Actividad clínica reciente</h3>
                            <p class="text-[10px] text-apoyo">Últimas notas y evoluciones médicas</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-fondo-panel px-2 py-1 text-[9px] font-black uppercase tracking-wide text-apoyo">Hoy: {{ $notasHoy }}</span>
                </div>

                @if($actividadVisible->isNotEmpty())
                    <div class="mt-4 divide-y divide-borde/60">
                        @foreach($actividadVisible as $nota)
                            <a href="{{ route('admin.medico.paciente.ficha', $nota['cod_am']) }}" class="group flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="w-12 shrink-0 pt-0.5 text-[10px] font-black text-apoyo">{{ $nota['hora'] ?: '—' }}</div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-black text-titulo transition group-hover:text-estado-info">{{ $nota['paciente'] }}</p>
                                    <p class="mt-0.5 truncate text-[10px] text-apoyo">
                                        {{ str_replace('_', ' ', $nota['tipo']) }}
                                        @if(!empty($nota['valoracion'])) · {{ $nota['valoracion'] }} @endif
                                    </p>
                                    <p class="mt-1 text-[9px] text-meta">{{ $nota['fecha'] }} · {{ $nota['medico'] ?? 'Sistema' }}</p>
                                </div>
                                <i class="ph-bold ph-arrow-right mt-1 shrink-0 text-estado-info transition duration-200 group-hover:translate-x-1"></i>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-fondo-panel px-4 py-8 text-center">
                        <i class="ph-bold ph-note-pencil text-2xl text-apoyo"></i>
                        <p class="mt-2 text-xs font-bold text-titulo">Sin actividad médica reciente</p>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm xl:col-span-7">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-boton-acento/10 text-boton-acento">
                            <i class="ph-bold ph-chart-line-up text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-titulo">Tendencia institucional de signos</h3>
                            <p class="text-[10px] text-apoyo">Promedios diarios de registros vigentes · últimos 30 días</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.medico.signos-vitales') }}" class="text-[9px] font-black text-estado-info hover:underline">Abrir monitor</a>
                </div>

                <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <div class="rounded-xl bg-fondo-panel px-3 py-2">
                        <p class="text-[8px] font-black uppercase tracking-wide text-meta">PA sistólica media</p>
                        <p class="mt-1 text-sm font-black text-titulo">{{ $kpiPaMedia ?: '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-fondo-panel px-3 py-2">
                        <p class="text-[8px] font-black uppercase tracking-wide text-meta">FC media</p>
                        <p class="mt-1 text-sm font-black text-titulo">{{ $kpiFcMedia ?: '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-fondo-panel px-3 py-2">
                        <p class="text-[8px] font-black uppercase tracking-wide text-meta">SpO₂ media</p>
                        <p class="mt-1 text-sm font-black text-titulo">{{ $kpiSatMedia ?: '—' }}{{ $kpiSatMedia ? '%' : '' }}</p>
                    </div>
                    <div class="rounded-xl {{ $kpiSinRegistroHoy > 0 ? 'bg-estado-advertenciaBg' : 'bg-fondo-panel' }} px-3 py-2">
                        <p class="text-[8px] font-black uppercase tracking-wide {{ $kpiSinRegistroHoy > 0 ? 'text-estado-advertencia' : 'text-meta' }}">Sin registro hoy</p>
                        <p class="mt-1 text-sm font-black {{ $kpiSinRegistroHoy > 0 ? 'text-estado-advertencia' : 'text-titulo' }}">{{ $kpiSinRegistroHoy }}</p>
                    </div>
                </div>

                @if(!empty($chartTendencia['labels']))
                    <div wire:ignore x-data="graficoTendencia(@js($chartTendencia))" x-init="init()" class="min-h-[230px]">
                        <canvas x-ref="canvas" style="height:230px"></canvas>
                    </div>
                @else
                    <div class="flex min-h-[230px] items-center justify-center rounded-xl bg-fondo-panel text-xs font-semibold text-apoyo">
                        Sin datos suficientes para mostrar una tendencia.
                    </div>
                @endif
            </div>
        </section>

        {{-- ══════════════════════════════════════════════════════
             ACCIONES RÁPIDAS + ANALÍTICA SECUNDARIA
        ══════════════════════════════════════════════════════ --}}
        <section
            x-cloak
            x-show="ready"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-5"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="transition-delay: 350ms"
            class="grid grid-cols-1 gap-5 xl:grid-cols-12 motion-reduce:transform-none motion-reduce:transition-none"
        >
            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm xl:col-span-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                        <i class="ph-bold ph-lightning text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-titulo">Acciones rápidas</h3>
                        <p class="text-[10px] text-apoyo">Accesos del flujo médico</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="{{ $rutaResidentes }}" class="group flex items-center gap-2 rounded-xl bg-titulo px-3 py-3 text-[10px] font-black text-white transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                        <i class="ph-bold ph-users-three text-sm transition group-hover:scale-110"></i> Residentes
                    </a>
                    <a href="{{ route('admin.medico.valoraciones') }}" class="group flex items-center gap-2 rounded-xl bg-estado-advertenciaBg px-3 py-3 text-[10px] font-black text-estado-advertencia transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                        <i class="ph-bold ph-clipboard-text text-sm transition group-hover:scale-110"></i> Valoraciones
                    </a>
                    <a href="{{ $rutaCognicion }}" class="group flex items-center gap-2 rounded-xl bg-[#EEE8FF] px-3 py-3 text-[10px] font-black text-[#6E56CF] transition duration-200 hover:-translate-y-0.5 hover:shadow-sm dark:bg-[#2f2850] dark:text-[#b7a8ff]">
                        <i class="ph-bold ph-brain text-sm transition group-hover:scale-110"></i> Cognición
                    </a>
                    <a href="{{ route('admin.medico.signos-vitales') }}" class="group flex items-center gap-2 rounded-xl bg-estado-infoBg px-3 py-3 text-[10px] font-black text-estado-info transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                        <i class="ph-bold ph-heartbeat text-sm transition group-hover:scale-110"></i> Signos
                    </a>
                    <a href="{{ $rutaMedicacion }}" class="group flex items-center gap-2 rounded-xl bg-estado-exitoBg px-3 py-3 text-[10px] font-black text-estado-exito transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                        <i class="ph-bold ph-pill text-sm transition group-hover:scale-110"></i> Medicación
                    </a>
                    <a href="{{ $rutaAlertas }}" class="group flex items-center gap-2 rounded-xl bg-estado-errorBg px-3 py-3 text-[10px] font-black text-estado-error transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                        <i class="ph-bold ph-bell-ringing text-sm transition group-hover:scale-110"></i> Alertas
                    </a>
                    <a href="{{ $rutaConsultas }}" class="group flex items-center gap-2 rounded-xl bg-fondo-panel px-3 py-3 text-[10px] font-black text-titulo transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                        <i class="ph-bold ph-note-pencil text-sm transition group-hover:scale-110"></i> Evoluciones
                    </a>
                    <a href="{{ $rutaReportes }}" class="group flex items-center gap-2 rounded-xl bg-fondo-panel px-3 py-3 text-[10px] font-black text-titulo transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                        <i class="ph-bold ph-file-chart text-sm transition group-hover:scale-110"></i> Reportes
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-borde bg-fondo-card p-5 shadow-sm xl:col-span-8">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                        <i class="ph-bold ph-chart-bar text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-titulo">Actividad médica por tipo</h3>
                        <p class="text-[10px] text-apoyo">Distribución de notas clínicas registradas; no representa diagnóstico ni riesgo</p>
                    </div>
                </div>

                @if(array_sum($chartNotasTipo['values'] ?? []) > 0)
                    <div wire:ignore x-data="graficoNotasTipo(@js($chartNotasTipo))" x-init="init()" class="min-h-[210px]">
                        <canvas x-ref="canvas" style="height:210px"></canvas>
                    </div>
                @else
                    <div class="flex min-h-[210px] items-center justify-center rounded-xl bg-fondo-panel text-xs font-semibold text-apoyo">
                        Aún no existen notas suficientes para graficar actividad médica.
                    </div>
                @endif
            </div>
        </section>
    @else
        {{-- ══════════════════════════════════════════════════════
             VISTA ENFOCADA: VALORACIONES / DECISIONES
        ══════════════════════════════════════════════════════ --}}
        <section
            x-cloak
            x-show="ready"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            style="transition-delay: 80ms"
            class="overflow-hidden rounded-2xl border border-borde bg-fondo-card shadow-sm motion-reduce:transform-none motion-reduce:transition-none"
        >
            <div class="flex flex-col gap-3 border-b border-borde px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-black text-titulo">
                        {{ $seccion === 'decisiones' ? 'Residentes pendientes de decisión' : 'Residentes pendientes de valoración médica' }}
                    </h2>
                    <p class="mt-1 text-xs text-apoyo">{{ $valoraciones->count() }} registro(s) pendiente(s)</p>
                </div>
            </div>

            @if($valoraciones->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left">
                        <thead class="bg-fondo-panel text-[9px] font-black uppercase tracking-wider text-apoyo">
                            <tr>
                                <th class="px-5 py-3">Residente</th>
                                <th class="px-5 py-3">Estado</th>
                                <th class="px-5 py-3">Motivo de ingreso</th>
                                <th class="px-5 py-3">Procedencia</th>
                                <th class="px-5 py-3">En espera</th>
                                <th class="px-5 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde/60">
                            @foreach($valoraciones as $residente)
                                @php $estado = $residente->estado?->estado ?? 'SIN_ESTADO'; @endphp
                                <tr class="transition duration-200 hover:bg-fondo-panel/60">
                                    <td class="px-5 py-4">
                                        <p class="text-xs font-black text-titulo">{{ $residente->nombres }} {{ $residente->ap_paterno }}</p>
                                        <p class="mt-0.5 text-[10px] text-apoyo">
                                            {{ $residente->fecha_nac ? \Carbon\Carbon::parse($residente->fecha_nac)->age . ' años' : '—' }}
                                            · CI {{ $residente->ci ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-fondo-panel px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-apoyo">{{ str_replace('_', ' ', $estado) }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-xs font-semibold text-titulo">{{ $residente->motivo_ingreso ?? 'No especificado' }}</td>
                                    <td class="px-5 py-4 text-xs text-apoyo">{{ $residente->procedencia_ingreso ?? '—' }}</td>
                                    <td class="px-5 py-4 text-[10px] text-apoyo">{{ $residente->created_at ? $residente->created_at->diffForHumans() : '—' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        @if(in_array($estado, ['VALORACION_MEDICA', 'PENDIENTE_VALORACION_MEDICA']))
                                            <button type="button" wire:click="iniciarValoracionMedica('{{ $residente->cod_am }}')" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-estado-advertencia px-3 text-[10px] font-black text-white transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                                                <i class="ph-bold ph-stethoscope"></i> Valorar
                                            </button>
                                        @elseif($estado === 'DECISION_ADMISION')
                                            <button type="button" wire:click="abrirDecisionAdmision('{{ $residente->cod_am }}')" class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-estado-exito px-3 text-[10px] font-black text-white transition duration-200 hover:-translate-y-0.5 hover:shadow-sm">
                                                <i class="ph-bold ph-check-circle"></i> Emitir dictamen
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex min-h-[280px] flex-col items-center justify-center px-6 text-center">
                    <i class="ph-bold ph-check-circle text-4xl text-estado-exito"></i>
                    <h3 class="mt-3 text-sm font-black text-titulo">No hay registros pendientes</h3>
                    <p class="mt-1 text-xs text-apoyo">No se requieren actuaciones médicas en esta sección.</p>
                </div>
            @endif
        </section>
    @endif

    {{-- 90. MODALES CLÍNICOS REUTILIZADOS --}}
    @livewire('valoraciones.valoracion-medica-modal')
    @livewire('admisiones.decision-admision-modal')

    {{-- ══════════════════════════════════════════════════════
         COMPORTAMIENTO LOCAL DEL DASHBOARD
         - Se mantiene dentro del Blade para uniformidad del módulo médico.
         - Usa Alpine + Chart.js ya cargados por el layout del sistema.
         - No depende de un archivo JS exclusivo del dashboard.
    ══════════════════════════════════════════════════════ --}}
    @script
    <script>
(() => {
    if (!window.Alpine || window.__rememberMindDashboardMedicoRegistrado) {
        return;
    }

    window.__rememberMindDashboardMedicoRegistrado = true;

    const prefersReducedMotion = () =>
        window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

    function getRMColors() {
        if (window.RMCharts) {
            const pal = window.RMCharts.palette();
            const sem = window.RMCharts.semanticColors();

            return {
                azulProfundo: pal[0] || '#344D7A',
                azulClinico: pal[5] || '#4E8CA6',
                verdeSalud: sem.success || '#5F9271',
                naranja: sem.warningHigh || '#E67A22',
                danger: sem.danger || '#E5534B',
                morado: pal[4] || '#7565A8',
                salmon: pal[6] || '#A85C73',
            };
        }

        return {
            azulProfundo: '#344D7A',
            azulClinico: '#4E8CA6',
            verdeSalud: '#5F9271',
            naranja: '#E67A22',
            danger: '#E5534B',
            morado: '#7565A8',
            salmon: '#A85C73',
        };
    }

    function toTranslucent(color, alpha = 0.78) {
        if (window.RMCharts?.hexToRgba) {
            return window.RMCharts.hexToRgba(color, alpha);
        }

        return color;
    }

    function baseOptions() {
        const base = window.RMCharts?.baseOptions?.() ?? {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: { display: false },
                legend: {
                    labels: {
                        font: { family: 'Inter', size: 11, weight: '600' },
                    },
                },
            },
        };

        return {
            ...base,
            animation: prefersReducedMotion()
                ? false
                : {
                    duration: 650,
                    easing: 'easeOutQuart',
                },
        };
    }

    function safeArray(value) {
        return Array.isArray(value) ? value : [];
    }

    Alpine.data('graficoTendencia', (initial = {}) => ({
        chart: null,
        current: initial,

        init() {
            this.$nextTick(() => this.draw(initial));

            this.$watch('$wire.chartTendencia', (data) => {
                this.current = data || {};
                this.draw(this.current);
            });

            window.RMCharts?.onThemeChange(() => this.draw(this.current));
        },

        normalize(data = {}) {
            return {
                labels: safeArray(data.labels),
                pa: safeArray(data.pa),
                // Compatibilidad con el PHP anterior y el PHP corregido.
                spo2: safeArray(data.spo2 ?? data.sat),
                glucosa: safeArray(data.glucosa ?? data.gluc),
            };
        },

        draw(raw) {
            if (!window.Chart || !this.$refs.canvas) {
                return;
            }

            const data = this.normalize(raw);
            const RM = getRMColors();
            const options = baseOptions();

            this.chart?.destroy();

            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'PA sistólica',
                            data: data.pa,
                            borderColor: RM.danger,
                            backgroundColor: toTranslucent(RM.danger, 0.12),
                            borderWidth: 2.25,
                            pointRadius: 2.75,
                            pointHoverRadius: 5,
                            tension: 0.36,
                            fill: true,
                            yAxisID: 'y',
                        },
                        {
                            label: 'SpO₂ %',
                            data: data.spo2,
                            borderColor: RM.verdeSalud,
                            backgroundColor: toTranslucent(RM.verdeSalud, 0.10),
                            borderWidth: 2.1,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            tension: 0.36,
                            fill: true,
                            yAxisID: 'y1',
                        },
                        {
                            label: 'Glucosa',
                            data: data.glucosa,
                            borderColor: RM.naranja,
                            backgroundColor: toTranslucent(RM.naranja, 0.08),
                            borderWidth: 2,
                            pointRadius: 2.5,
                            pointHoverRadius: 5,
                            tension: 0.36,
                            fill: true,
                            yAxisID: 'y',
                        },
                    ],
                },
                options: {
                    ...options,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        ...options.plugins,
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 7,
                                boxHeight: 7,
                                padding: 14,
                                font: { size: 10, weight: '600' },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 },
                        },
                        y: {
                            position: 'left',
                            beginAtZero: false,
                            grid: { color: 'rgba(100,116,139,0.08)' },
                        },
                        y1: {
                            position: 'right',
                            suggestedMin: 80,
                            suggestedMax: 100,
                            grid: { drawOnChartArea: false },
                        },
                    },
                },
            });
        },
    }));

    Alpine.data('graficoNotasTipo', (initial = {}) => ({
        chart: null,
        current: initial,

        init() {
            this.$nextTick(() => this.draw(initial));

            this.$watch('$wire.chartNotasTipo', (data) => {
                this.current = data || {};
                this.draw(this.current);
            });

            window.RMCharts?.onThemeChange(() => this.draw(this.current));
        },

        draw(data = {}) {
            if (!window.Chart || !this.$refs.canvas) {
                return;
            }

            const RM = getRMColors();
            const options = baseOptions();
            const labels = safeArray(data.labels);
            const values = safeArray(data.values);
            const palette = [
                RM.verdeSalud,
                RM.azulClinico,
                RM.azulProfundo,
                RM.morado,
                RM.danger,
                RM.salmon,
            ];

            this.chart?.destroy();

            this.chart = new window.Chart(this.$refs.canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Notas',
                        data: values,
                        backgroundColor: labels.map((_, i) =>
                            toTranslucent(palette[i % palette.length], 0.76)
                        ),
                        borderColor: labels.map((_, i) =>
                            toTranslucent(palette[i % palette.length], 0.96)
                        ),
                        borderWidth: 1.25,
                        borderRadius: 8,
                        barPercentage: 0.72,
                        categoryPercentage: 0.78,
                    }],
                },
                options: {
                    ...options,
                    plugins: {
                        ...options.plugins,
                        legend: { display: false },
                        datalabels: {
                            display: true,
                            anchor: 'end',
                            align: 'top',
                            formatter: (value) => value > 0 ? value : '',
                            font: { size: 10, weight: '700' },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0 },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(100,116,139,0.08)' },
                        },
                    },
                },
            });
        },
    }));
})();
    </script>
    @endscript
</div>
