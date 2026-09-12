<div class="space-y-5" x-data="{ accionesAbiertas: false }">
    @php
        $fmtFecha = function ($valor, string $formato = 'd/m/Y') {
            if (blank($valor)) return '—';
            try { return \Carbon\Carbon::parse($valor)->format($formato); }
            catch (\Throwable $e) { return '—'; }
        };

        $fmtFechaHora = function ($valor) {
            if (blank($valor)) return '—';
            try { return \Carbon\Carbon::parse($valor)->format('d/m/Y H:i'); }
            catch (\Throwable $e) { return '—'; }
        };

        $estadoClase = match (strtoupper((string) ($adulto->estado?->estado ?? ''))) {
            'ACTIVO', 'ACTIVA', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO' => 'bg-estado-exitoBg text-estado-exito border-estado-exito/20',
            'OBSERVADO', 'SEGUIMIENTO_ESPECIAL', 'HOSPITALIZADO' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertencia/20',
            'DERIVADO', 'TRASLADADO', 'SALIDA_TEMPORAL' => 'bg-estado-infoBg text-estado-info border-estado-info/20',
            default => 'bg-fondo-panel text-apoyo border-borde',
        };

        $riesgoCog = strtoupper((string) ($resumenClinico['riesgo_cognitivo_actual'] ?? ''));
        $riesgoCogClase = match ($riesgoCog) {
            'CRITICO', 'CRÍTICO', 'ALTO' => 'bg-estado-errorBg text-estado-error border-estado-error/20',
            'MEDIO', 'MODERADO', 'PREVENTIVO' => 'bg-estado-advertenciaBg text-estado-advertencia border-estado-advertencia/20',
            'BAJO', 'NORMAL' => 'bg-estado-exitoBg text-estado-exito border-estado-exito/20',
            default => 'bg-fondo-panel text-apoyo border-borde',
        };

        $nivelDependencia = (string) ($resumenClinico['dependencia_actual'] ?? '');
        $barthelActual = $resumenClinico['barthel_actual'] ?? null;
        $barthelAnterior = $resumenClinico['barthel_anterior'] ?? null;
        $puntajeCogActual = $resumenClinico['puntaje_cognitivo_actual'] ?? null;
        $puntajeCogAnterior = $resumenClinico['puntaje_cognitivo_anterior'] ?? null;

        $ultimaNota = $notasRecientes[0] ?? null;
        $seguimientoMedico = $seguimientosSolicitaronMedico[0] ?? null;

        $tabs = [
            ['id' => 'resumen',        'icono' => 'ph-layout',              'texto' => 'Resumen',          'permiso' => 'ver_resumen'],
            ['id' => 'consultas',      'icono' => 'ph-stethoscope',         'texto' => 'Consultas',        'permiso' => 'ver_consultas'],
            ['id' => 'antecedentes',   'icono' => 'ph-file-text',           'texto' => 'Antecedentes',     'permiso' => 'ver_antecedentes'],
            ['id' => 'medicacion',     'icono' => 'ph-pill',                'texto' => 'Medicación',       'permiso' => 'ver_medicacion'],
            ['id' => 'signos',         'icono' => 'ph-heartbeat',           'texto' => 'Signos',           'permiso' => 'ver_signos'],
            ['id' => 'cognicion',      'icono' => 'ph-brain',               'texto' => 'Cognición',        'permiso' => 'ver_evaluaciones'],
            ['id' => 'funcional',      'icono' => 'ph-person-simple-walk',  'texto' => 'Funcional',        'permiso' => 'ver_funcional'],
            ['id' => 'interconsultas', 'icono' => 'ph-arrows-left-right',   'texto' => 'Interconsultas',   'permiso' => 'ver_interconsultas'],
            ['id' => 'alertas',        'icono' => 'ph-warning-circle',      'texto' => 'Alertas',          'permiso' => 'ver_alertas'],
            ['id' => 'historial',      'icono' => 'ph-clock-counter-clockwise','texto' => 'Historial',     'permiso' => 'ver_historial'],
        ];
    @endphp

    {{-- Navegación institucional existente --}}
    <x-residentes.navegacion-ficha :adulto="$adulto" />

    {{-- Aviso de expediente en solo lectura --}}
    @if($soloLectura)
        <div class="flex items-start gap-3 rounded-2xl border border-estado-advertencia/25 bg-estado-advertenciaBg px-4 py-3 text-estado-advertencia">
            <i class="ph-bold ph-lock-key mt-0.5 text-lg"></i>
            <div class="min-w-0">
                <div class="text-sm font-black">Expediente en modo de solo lectura</div>
                <div class="mt-0.5 text-xs font-semibold opacity-90">
                    {{ $motivoSoloLectura ?: 'Este expediente puede consultarse, pero no admite nuevas acciones clínicas.' }}
                </div>
            </div>
        </div>
    @endif

    {{-- Cabecera clínica compacta --}}
    <section class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-estado-infoBg text-2xl font-black text-estado-info">
                    {{ strtoupper(substr((string) $adulto->nombres, 0, 1)) }}{{ strtoupper(substr((string) $adulto->ap_paterno, 0, 1)) }}
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="truncate text-xl font-black tracking-tight text-titulo sm:text-2xl">
                            {{ $adulto->nombre_completo }}
                        </h1>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $estadoClase }}">
                            {{ $adulto->estado_humano }}
                        </span>
                    </div>

                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1.5 text-xs font-semibold text-apoyo">
                        <span><i class="ph-bold ph-calendar-blank mr-1"></i>{{ $adulto->edad_texto }}</span>
                        <span><i class="ph-bold ph-identification-card mr-1"></i>CI: {{ $adulto->ci ?: 'No registrado' }}</span>
                        <span><i class="ph-bold ph-bed mr-1"></i>{{ $adulto->ubicacion_texto }}</span>
                        @if($adulto->genero)
                            <span><i class="ph-bold ph-gender-intersex mr-1"></i>{{ $adulto->genero }}</span>
                        @endif
                        @if($grupoSanguineoTexto !== '')
                            <span><i class="ph-bold ph-drop-half mr-1"></i>{{ $grupoSanguineoTexto }}</span>
                        @endif
                    </div>

                    @if($alergiasClinicas !== '')
                        <div class="mt-3 inline-flex max-w-full items-center gap-2 rounded-xl border border-estado-error/20 bg-estado-errorBg px-3 py-1.5 text-xs font-bold text-estado-error">
                            <i class="ph-bold ph-warning"></i>
                            <span class="truncate">Alergias: {{ $alergiasClinicas }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @if($permisosUI['crear_consulta'] ?? false)
                    <button type="button"
                            wire:click="nuevaNota"
                            wire:loading.attr="disabled"
                            wire:target="nuevaNota"
                            class="rm-btn-primary flex h-10 items-center gap-2 px-4 text-xs">
                        <i class="ph-bold ph-stethoscope text-base"></i>
                        Nueva consulta
                    </button>
                @else
                    <button type="button" disabled
                            title="No disponible por permisos o por el estado actual del expediente."
                            class="flex h-10 cursor-not-allowed items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-4 text-xs font-bold text-apoyo opacity-60">
                        <i class="ph-bold ph-lock-key"></i>
                        Nueva consulta
                    </button>
                @endif

                <div class="relative" @click.outside="accionesAbiertas = false">
                    <button type="button"
                            @click="accionesAbiertas = !accionesAbiertas"
                            :aria-expanded="accionesAbiertas"
                            class="rm-btn-secondary flex h-10 items-center gap-2 px-3 text-xs">
                        <i class="ph-bold ph-dots-three-outline-vertical"></i>
                        <span class="hidden sm:inline">Acciones</span>
                    </button>

                    <div x-cloak
                         x-show="accionesAbiertas"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                         x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                         class="absolute right-0 z-30 mt-2 w-64 overflow-hidden rounded-2xl border border-borde bg-fondo-card p-1.5 shadow-xl">

                        @if($permisosUI['registrar_signos'] ?? false)
                            <button type="button" wire:click="nuevosSignos" @click="accionesAbiertas = false"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-bold text-titulo hover:bg-fondo-panel">
                                <i class="ph-bold ph-heartbeat text-base text-estado-info"></i>
                                Registrar signos vitales
                            </button>
                        @else
                            <div title="La acción no está disponible por permisos, estado del expediente o restricciones del flujo actual."
                                 class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-apoyo opacity-55">
                                <i class="ph-bold ph-lock-key text-base"></i>
                                Registrar signos vitales
                            </div>
                        @endif

                        @if($permisosUI['crear_barthel'] ?? false)
                            <button type="button" wire:click="nuevaBarthel" @click="accionesAbiertas = false"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-bold text-titulo hover:bg-fondo-panel">
                                <i class="ph-bold ph-person-simple-walk text-base text-estado-exito"></i>
                                Nueva valoración Barthel
                            </button>
                        @else
                            <div title="La valoración funcional no está habilitada para este usuario o flujo clínico."
                                 class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-apoyo opacity-55">
                                <i class="ph-bold ph-lock-key text-base"></i>
                                Nueva valoración Barthel
                            </div>
                        @endif

                        @if($permisosUI['crear_evaluacion'] ?? false)
                            <button type="button"
                                    wire:click="nuevaEvaluacionGeriatrica('ARE_COG')"
                                    @click="accionesAbiertas = false"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-bold text-titulo hover:bg-fondo-panel">
                                <i class="ph-bold ph-brain text-base text-boton-acento"></i>
                                Evaluación cognitiva
                            </button>
                        @else
                            <div class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-bold text-apoyo opacity-55">
                                <i class="ph-bold ph-lock-key text-base"></i>
                                Evaluación cognitiva
                            </div>
                        @endif

                        @if($permisosUI['ver_alertas'] ?? false)
                            <button type="button" wire:click="setTab('alertas')" @click="accionesAbiertas = false"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-bold text-titulo hover:bg-fondo-panel">
                                <i class="ph-bold ph-warning-circle text-base text-estado-advertencia"></i>
                                Revisar alertas
                            </button>
                        @endif
                    </div>
                </div>

                <a href="{{ route('admin.medico.residentes') }}"
                   class="rm-btn-secondary flex h-10 items-center gap-2 px-3 text-xs">
                    <i class="ph-bold ph-arrow-left"></i>
                    <span class="hidden sm:inline">Residentes</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Indicadores de alto valor clínico: solo cuatro bloques --}}
    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <button type="button"
                @if($permisosUI['ver_evaluaciones'] ?? false) wire:click="setTab('cognicion')" @else disabled @endif
                class="rounded-2xl border border-borde bg-fondo-card p-4 text-left shadow-sm transition {{ ($permisosUI['ver_evaluaciones'] ?? false) ? 'hover:border-borde-focus hover:-translate-y-0.5' : 'cursor-default' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Cognición</div>
                    <div class="mt-1 text-base font-black text-titulo">
                        {{ $riesgoCog !== '' ? $riesgoCog : 'Sin evaluación' }}
                    </div>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl border {{ $riesgoCogClase }}">
                    <i class="ph-bold ph-brain text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] font-semibold text-apoyo">
                @if($resumenClinico['instrumento_cognitivo_actual'] ?? null)
                    {{ $resumenClinico['instrumento_cognitivo_actual'] }}
                    @if($puntajeCogActual !== null) · {{ $puntajeCogActual }} pts @endif
                @else
                    Sin instrumento cognitivo registrado
                @endif
            </div>
        </button>

        <button type="button"
                @if($permisosUI['ver_funcional'] ?? false) wire:click="setTab('funcional')" @else disabled @endif
                class="rounded-2xl border border-borde bg-fondo-card p-4 text-left shadow-sm transition {{ ($permisosUI['ver_funcional'] ?? false) ? 'hover:border-borde-focus hover:-translate-y-0.5' : 'cursor-default' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Funcionalidad</div>
                    <div class="mt-1 text-base font-black text-titulo">
                        {{ $nivelDependencia !== '' ? $nivelDependencia : 'Sin valoración' }}
                    </div>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-exitoBg text-estado-exito">
                    <i class="ph-bold ph-person-simple-walk text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] font-semibold text-apoyo">
                Barthel: {{ $barthelActual !== null ? $barthelActual . '/100' : 'sin registro' }}
                @if($resumenClinico['riesgo_caida_actual'] ?? null)
                    · Caída: {{ $resumenClinico['riesgo_caida_actual'] }}
                @endif
            </div>
        </button>

        <button type="button"
                @if($permisosUI['ver_medicacion'] ?? false) wire:click="setTab('medicacion')" @else disabled @endif
                class="rounded-2xl border border-borde bg-fondo-card p-4 text-left shadow-sm transition {{ ($permisosUI['ver_medicacion'] ?? false) ? 'hover:border-borde-focus hover:-translate-y-0.5' : 'cursor-default' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Medicación</div>
                    <div class="mt-1 text-base font-black text-titulo">
                        {{ $resumenClinico['medicamentos_activos'] ?? 0 }} activa(s)
                    </div>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-estado-infoBg text-estado-info">
                    <i class="ph-bold ph-pill text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] font-semibold {{ ($omisionesUltimos7Dias ?? 0) > 0 ? 'text-estado-advertencia' : 'text-apoyo' }}">
                {{ $omisionesUltimos7Dias }} omisión(es) registradas en 7 días
            </div>
        </button>

        <button type="button"
                @if($permisosUI['ver_alertas'] ?? false) wire:click="setTab('alertas')" @else disabled @endif
                class="rounded-2xl border border-borde bg-fondo-card p-4 text-left shadow-sm transition {{ ($permisosUI['ver_alertas'] ?? false) ? 'hover:border-borde-focus hover:-translate-y-0.5' : 'cursor-default' }}">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Alertas activas</div>
                    <div class="mt-1 text-base font-black {{ $cntAlertasActivas > 0 ? 'text-estado-error' : 'text-titulo' }}">
                        {{ $cntAlertasActivas }}
                    </div>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $cntAlertasActivas > 0 ? 'bg-estado-errorBg text-estado-error' : 'bg-fondo-panel text-apoyo' }}">
                    <i class="ph-bold ph-warning-circle text-lg"></i>
                </div>
            </div>
            <div class="mt-2 text-[11px] font-semibold text-apoyo">
                @if($proximaAtencion)
                    Próxima atención: {{ $fmtFecha($proximaAtencion->fecha) }}
                @else
                    Sin próxima atención registrada
                @endif
            </div>
        </button>
    </section>

    {{-- Pestañas autorizadas --}}
    <nav class="overflow-x-auto rounded-2xl border border-borde bg-fondo-panel p-1" aria-label="Secciones del expediente clínico">
        <div class="flex min-w-max gap-1">
            @foreach($tabs as $item)
                @if($permisosUI[$item['permiso']] ?? false)
                    <button type="button"
                            wire:key="ficha-tab-{{ $item['id'] }}"
                            wire:click="setTab('{{ $item['id'] }}')"
                            class="flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-[11px] font-black uppercase tracking-wider transition
                                   {{ $tab === $item['id'] ? 'bg-fondo-card text-boton-acento shadow-sm' : 'text-apoyo hover:bg-fondo-card/70 hover:text-titulo' }}">
                        <i class="ph-bold {{ $item['icono'] }} text-sm"></i>
                        {{ $item['texto'] }}
                        @if($item['id'] === 'alertas' && $cntAlertasActivas > 0)
                            <span class="ml-1 rounded-full bg-estado-error px-1.5 py-0.5 text-[9px] text-white">{{ $cntAlertasActivas }}</span>
                        @endif
                    </button>
                @endif
            @endforeach
        </div>
    </nav>

    {{-- Capa de carga discreta --}}
    <div wire:loading.flex wire:target="setTab,refreshData,nuevaNota,nuevosSignos,nuevaBarthel,nuevaEvaluacionGeriatrica"
         class="items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-xs font-semibold text-apoyo">
        <i class="ph-bold ph-spinner-gap animate-spin"></i>
        Actualizando expediente…
    </div>

    {{-- ====================== RESUMEN ====================== --}}
    @if($tab === 'resumen')
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <div class="space-y-4 xl:col-span-2">
                {{-- Situación actual --}}
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Situación actual</h2>
                            <p class="mt-0.5 text-xs text-apoyo">Información de mayor valor para la revisión médica.</p>
                        </div>
                        @if($ultimaNota && ($permisosUI['ver_consultas'] ?? false))
                            <button type="button" wire:click="setTab('consultas')" class="text-xs font-black text-boton-acento hover:underline">
                                Ver consultas
                            </button>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="rounded-2xl bg-fondo-panel p-4">
                            <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Última consulta/evolución</div>
                            @if($ultimaNota)
                                <div class="mt-1 font-black text-titulo">{{ str_replace('_', ' ', $ultimaNota['tipo_nota'] ?? 'EVOLUCION') }}</div>
                                <div class="mt-1 text-xs text-apoyo">{{ $fmtFecha($ultimaNota['fecha'] ?? null) }} @if(!empty($ultimaNota['hora'])) · {{ substr((string) $ultimaNota['hora'], 0, 5) }} @endif</div>
                                @if(!empty($ultimaNota['valoracion']))
                                    <p class="mt-2 line-clamp-2 text-xs font-semibold text-parrafo">{{ $ultimaNota['valoracion'] }}</p>
                                @endif
                            @else
                                <div class="mt-2 text-sm font-semibold text-apoyo">Sin consultas registradas.</div>
                            @endif
                        </div>

                        <div class="rounded-2xl bg-fondo-panel p-4">
                            <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Últimos signos vitales</div>
                            @if($ultimosSignos)
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-bold text-titulo">
                                    <span>PA {{ $ultimosSignos->presion_formateada ?? '—' }}</span>
                                    <span>FC {{ $ultimosSignos->frecuencia_cardiaca ?? '—' }}</span>
                                    <span>SpO₂ {{ $ultimosSignos->saturacion !== null ? $ultimosSignos->saturacion . '%' : '—' }}</span>
                                </div>
                                <div class="mt-2 text-[11px] text-apoyo">
                                    {{ $fmtFecha($ultimosSignos->fecha) }} @if($ultimosSignos->hora) · {{ substr((string) $ultimosSignos->hora, 0, 5) }} @endif
                                    @if($ultimosSignos->valor_atipico_confirmado)
                                        <span class="ml-1 font-black text-estado-advertencia">· valor atípico confirmado</span>
                                    @endif
                                </div>
                            @else
                                <div class="mt-2 text-sm font-semibold text-apoyo">Sin signos vitales registrados.</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Cambios recientes sin interpretar clínicamente --}}
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="mb-4">
                        <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Comparación longitudinal</h2>
                        <p class="mt-0.5 text-xs text-apoyo">Se muestran valores anteriores y actuales; la interpretación corresponde al profesional.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-wider text-apoyo">
                                <i class="ph-bold ph-brain text-boton-acento"></i> Cognición
                            </div>
                            @if($evalCognitiva)
                                <div class="mt-2 text-sm font-black text-titulo">{{ $evalCognitiva->instrumento?->nombre ?? 'Evaluación cognitiva' }}</div>
                                <div class="mt-2 flex items-center gap-2 text-lg font-black text-titulo">
                                    <span>{{ $puntajeCogAnterior !== null ? $puntajeCogAnterior : '—' }}</span>
                                    <i class="ph-bold ph-arrow-right text-sm text-apoyo"></i>
                                    <span>{{ $puntajeCogActual !== null ? $puntajeCogActual : '—' }}</span>
                                </div>
                                <div class="mt-1 text-[11px] text-apoyo">Última evaluación: {{ $fmtFecha($evalCognitiva->fecha_eval) }}</div>
                            @else
                                <p class="mt-2 text-xs font-semibold text-apoyo">Sin evaluación cognitiva.</p>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-borde bg-fondo-panel p-4">
                            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-wider text-apoyo">
                                <i class="ph-bold ph-person-simple-walk text-estado-exito"></i> Funcionalidad
                            </div>
                            @if($valoracionFuncional)
                                <div class="mt-2 text-sm font-black text-titulo">Índice de Barthel</div>
                                <div class="mt-2 flex items-center gap-2 text-lg font-black text-titulo">
                                    <span>{{ $barthelAnterior !== null ? $barthelAnterior : '—' }}</span>
                                    <i class="ph-bold ph-arrow-right text-sm text-apoyo"></i>
                                    <span>{{ $barthelActual !== null ? $barthelActual : '—' }}</span>
                                </div>
                                <div class="mt-1 text-[11px] text-apoyo">{{ $nivelDependencia ?: 'Dependencia no clasificada' }}</div>
                            @else
                                <p class="mt-2 text-xs font-semibold text-apoyo">Sin valoración funcional.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Actividad reciente resumida --}}
                @if($permisosUI['ver_historial'] ?? false)
                    <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Actividad clínica reciente</h2>
                                <p class="mt-0.5 text-xs text-apoyo">Últimos eventos registrados en el expediente.</p>
                            </div>
                            <button type="button" wire:click="setTab('historial')" class="text-xs font-black text-boton-acento hover:underline">Ver historial</button>
                        </div>

                        @forelse(array_slice($historialClinico, 0, 5) as $evento)
                            <div class="flex gap-3 border-b border-borde/60 py-3 last:border-b-0">
                                <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-boton-acento"></div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="text-xs font-black text-titulo">{{ $evento['titulo'] ?? 'Evento clínico' }}</div>
                                        <div class="text-[10px] font-semibold text-apoyo">{{ $fmtFechaHora($evento['fecha_hora'] ?? null) }}</div>
                                    </div>
                                    @if(!empty($evento['detalle']))
                                        <p class="mt-1 line-clamp-2 text-xs text-apoyo">{{ $evento['detalle'] }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="py-5 text-center text-sm font-semibold text-apoyo">Sin actividad clínica registrada.</p>
                        @endforelse
                    </div>
                @endif
            </div>

            <aside class="space-y-4">
                {{-- Prioridades / alertas --}}
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Alertas activas</h2>
                        <span class="rounded-full {{ $cntAlertasActivas > 0 ? 'bg-estado-errorBg text-estado-error' : 'bg-fondo-panel text-apoyo' }} px-2 py-0.5 text-[10px] font-black">{{ $cntAlertasActivas }}</span>
                    </div>

                    @forelse(array_slice($alertasActivas, 0, 3) as $alerta)
                        @php
                            $nivelAlerta = strtoupper((string) ($alerta['nivel'] ?? ''));
                            $alertaClase = match ($nivelAlerta) {
                                'CRITICO', 'CRÍTICO', 'ALTO' => 'border-estado-error/20 bg-estado-errorBg text-estado-error',
                                'MEDIO', 'MODERADO' => 'border-estado-advertencia/20 bg-estado-advertenciaBg text-estado-advertencia',
                                default => 'border-borde bg-fondo-panel text-apoyo',
                            };
                        @endphp
                        <div class="mb-2 rounded-xl border p-3 {{ $alertaClase }} last:mb-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-[10px] font-black uppercase tracking-wider">{{ $alerta['tipo_alerta'] ?? 'Alerta clínica' }}</div>
                                <div class="text-[9px] font-black">{{ $nivelAlerta ?: 'SIN NIVEL' }}</div>
                            </div>
                            @if(!empty($alerta['motivo']))
                                <p class="mt-1 line-clamp-3 text-xs font-semibold text-titulo">{{ $alerta['motivo'] }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-xl bg-fondo-panel p-4 text-center">
                            <i class="ph-bold ph-check-circle text-xl text-estado-exito"></i>
                            <p class="mt-1 text-xs font-bold text-apoyo">Sin alertas activas registradas.</p>
                        </div>
                    @endforelse

                    @if(($permisosUI['ver_alertas'] ?? false) && $cntAlertasActivas > 0)
                        <button type="button" wire:click="setTab('alertas')" class="mt-3 w-full rounded-xl border border-borde bg-fondo-panel py-2 text-xs font-black text-boton-acento hover:bg-fondo-card">
                            Revisar alertas
                        </button>
                    @endif
                </div>

                {{-- Enfermería solicita médico --}}
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="mb-3 flex items-center gap-2">
                        <i class="ph-bold ph-first-aid text-estado-advertencia"></i>
                        <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Señales desde Enfermería</h2>
                    </div>

                    @if($seguimientoMedico)
                        <div class="rounded-xl bg-estado-advertenciaBg p-3">
                            <div class="text-[10px] font-black uppercase tracking-wider text-estado-advertencia">Solicitó revisión médica</div>
                            <div class="mt-1 text-[11px] font-semibold text-apoyo">{{ $fmtFecha($seguimientoMedico['fecha'] ?? null) }} @if(!empty($seguimientoMedico['hora_inicio'])) · {{ substr((string) $seguimientoMedico['hora_inicio'], 0, 5) }} @endif</div>
                            @if(!empty($seguimientoMedico['observacion']))
                                <p class="mt-2 line-clamp-4 text-xs font-semibold text-titulo">{{ $seguimientoMedico['observacion'] }}</p>
                            @endif
                        </div>
                        <p class="mt-2 text-[10px] leading-relaxed text-apoyo">El registro indica que Enfermería solicitó revisión médica; el modelo actual no confirma si ya fue resuelta.</p>
                    @else
                        <p class="text-xs font-semibold text-apoyo">No existen seguimientos recientes marcados con solicitud de revisión médica.</p>
                    @endif
                </div>

                {{-- Próxima atención --}}
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-calendar-check text-estado-info"></i>
                        <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Próxima atención</h2>
                    </div>
                    @if($proximaAtencion)
                        <div class="mt-3 text-lg font-black text-titulo">{{ $fmtFecha($proximaAtencion->fecha) }}</div>
                        <div class="mt-1 text-xs font-bold text-apoyo">
                            {{ $proximaAtencion->tipoAtencion?->nombre ?? 'Atención registrada' }}
                            @if($proximaAtencion->hora) · {{ substr((string) $proximaAtencion->hora, 0, 5) }} @endif
                        </div>
                        @if($proximaAtencion->observacion)
                            <p class="mt-2 text-xs text-parrafo">{{ $proximaAtencion->observacion }}</p>
                        @endif
                    @else
                        <p class="mt-3 text-xs font-semibold text-apoyo">No hay una atención futura registrada.</p>
                    @endif
                </div>
            </aside>
        </section>
    @endif

    {{-- ====================== CONSULTAS ====================== --}}
    @if($tab === 'consultas')
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-titulo">Consultas y evoluciones</h2>
                    <p class="text-xs text-apoyo">Registro SOAP y otras notas médicas activas.</p>
                </div>
                @if($permisosUI['crear_consulta'] ?? false)
                    <button type="button" wire:click="nuevaNota" class="rm-btn-primary flex h-10 items-center gap-2 px-4 text-xs">
                        <i class="ph-bold ph-plus"></i> Nueva consulta
                    </button>
                @else
                    <button type="button" disabled class="flex h-10 cursor-not-allowed items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-4 text-xs font-bold text-apoyo opacity-60">
                        <i class="ph-bold ph-lock-key"></i> Nueva consulta
                    </button>
                @endif
            </div>

            @forelse($notasRecientes as $nota)
                @php
                    $tipoNota = strtoupper((string) ($nota['tipo_nota'] ?? 'EVOLUCION'));
                    $tipoNotaClase = match ($tipoNota) {
                        'URGENCIA' => 'bg-estado-errorBg text-estado-error',
                        'INTERCONSULTA' => 'bg-boton-acento/10 text-boton-acento',
                        'PROCEDIMIENTO' => 'bg-estado-advertenciaBg text-estado-advertencia',
                        'INGRESO' => 'bg-estado-infoBg text-estado-info',
                        default => 'bg-estado-exitoBg text-estado-exito',
                    };
                @endphp
                <article class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-borde pb-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $tipoNotaClase }}">{{ str_replace('_', ' ', $tipoNota) }}</span>
                            <span class="text-xs font-bold text-titulo">{{ $fmtFecha($nota['fecha'] ?? null) }} @if(!empty($nota['hora'])) · {{ substr((string) $nota['hora'], 0, 5) }} @endif</span>
                        </div>
                        <span class="text-[10px] font-semibold text-apoyo">{{ data_get($nota, 'registrador.name') ?: 'Profesional no identificado' }}</span>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        @if(!empty($nota['subjetivo']))
                            <div class="rounded-xl bg-estado-infoBg/40 p-3">
                                <div class="text-[10px] font-black uppercase tracking-wider text-estado-info">S · Subjetivo</div>
                                <p class="mt-1 whitespace-pre-line text-xs font-semibold text-titulo">{{ $nota['subjetivo'] }}</p>
                            </div>
                        @endif
                        @if(!empty($nota['objetivo']))
                            <div class="rounded-xl bg-estado-advertenciaBg/40 p-3">
                                <div class="text-[10px] font-black uppercase tracking-wider text-estado-advertencia">O · Objetivo</div>
                                <p class="mt-1 whitespace-pre-line text-xs font-semibold text-titulo">{{ $nota['objetivo'] }}</p>
                            </div>
                        @endif
                        <div class="rounded-xl bg-boton-acento/5 p-3">
                            <div class="text-[10px] font-black uppercase tracking-wider text-boton-acento">A · Valoración</div>
                            <p class="mt-1 whitespace-pre-line text-xs font-semibold text-titulo">{{ $nota['valoracion'] ?? '—' }}</p>
                        </div>
                        <div class="rounded-xl bg-estado-exitoBg/40 p-3">
                            <div class="text-[10px] font-black uppercase tracking-wider text-estado-exito">P · Plan</div>
                            <p class="mt-1 whitespace-pre-line text-xs font-semibold text-titulo">{{ $nota['plan'] ?? '—' }}</p>
                        </div>
                    </div>

                    @if(!empty($nota['observaciones']))
                        <div class="mt-3 rounded-xl bg-fondo-panel px-3 py-2 text-xs text-apoyo">
                            <i class="ph-bold ph-note mr-1"></i>{{ $nota['observaciones'] }}
                        </div>
                    @endif
                </article>
            @empty
                <div class="rounded-[24px] border border-dashed border-borde bg-fondo-card py-14 text-center">
                    <i class="ph-bold ph-stethoscope text-3xl text-apoyo"></i>
                    <h3 class="mt-3 font-black text-titulo">Sin consultas o evoluciones</h3>
                    <p class="mt-1 text-sm text-apoyo">No hay notas médicas activas registradas para este residente.</p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ====================== ANTECEDENTES ====================== --}}
    @if($tab === 'antecedentes')
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Antecedentes clínicos registrados</h2>
                <p class="mt-1 text-xs text-apoyo">Se muestran únicamente campos existentes en la ficha médica.</p>

                @if(count($antecedentesClinicos) > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($antecedentesClinicos as $antecedente)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-borde bg-fondo-panel px-3 py-1.5 text-xs font-bold text-titulo">
                                <i class="ph-bold ph-check-circle text-estado-info"></i>
                                {{ $antecedente['etiqueta'] ?? 'Antecedente' }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="mt-4 rounded-xl bg-fondo-panel p-4 text-sm font-semibold text-apoyo">No hay antecedentes booleanos activos registrados en la ficha médica.</div>
                @endif
            </div>

            <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                <h2 class="text-sm font-black uppercase tracking-wider text-titulo">Información clínica relevante</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-xl {{ $alergiasClinicas !== '' ? 'bg-estado-errorBg' : 'bg-fondo-panel' }} p-3">
                        <div class="text-[10px] font-black uppercase tracking-wider {{ $alergiasClinicas !== '' ? 'text-estado-error' : 'text-apoyo' }}">Alergias</div>
                        <div class="mt-1 text-sm font-bold text-titulo">{{ $alergiasClinicas !== '' ? $alergiasClinicas : 'No registradas' }}</div>
                    </div>

                    <div class="rounded-xl bg-fondo-panel p-3">
                        <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Restricciones alimentarias</div>
                        <div class="mt-1 whitespace-pre-line text-sm font-semibold text-titulo">{{ $fichaMedica?->restricciones_alimentarias ?: 'No registradas' }}</div>
                    </div>

                    <div class="rounded-xl bg-fondo-panel p-3">
                        <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Hospitalizaciones</div>
                        <div class="mt-1 whitespace-pre-line text-sm font-semibold text-titulo">{{ $fichaMedica?->hospitalizaciones ?: 'No registradas' }}</div>
                    </div>

                    <div class="rounded-xl bg-fondo-panel p-3">
                        <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Cirugías</div>
                        <div class="mt-1 whitespace-pre-line text-sm font-semibold text-titulo">{{ $fichaMedica?->cirugias ?: 'No registradas' }}</div>
                    </div>

                    <div class="rounded-xl bg-fondo-panel p-3">
                        <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Observación médica</div>
                        <div class="mt-1 whitespace-pre-line text-sm font-semibold text-titulo">{{ $fichaMedica?->observacion_medica ?: 'Sin observación médica registrada' }}</div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ====================== MEDICACIÓN ====================== --}}
    @if($tab === 'medicacion')
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-black text-titulo">Medicación vigente</h2>
                <p class="text-xs text-apoyo">Prescripción médica y registro reciente de administración. Esta vista no administra dosis.</p>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-sm">
                @if(count($medicacionActiva) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-left text-sm">
                            <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-5 py-3">Medicamento</th>
                                    <th class="px-5 py-3">Dosis / frecuencia</th>
                                    <th class="px-5 py-3">Vía</th>
                                    <th class="px-5 py-3">Periodo</th>
                                    <th class="px-5 py-3">PRN</th>
                                    <th class="px-5 py-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde/60">
                                @foreach($medicacionActiva as $med)
                                    <tr class="hover:bg-fondo-panel/40">
                                        <td class="px-5 py-3 align-top">
                                            <div class="flex items-start gap-2">
                                                <i class="ph-bold ph-pill mt-0.5 text-estado-info"></i>
                                                <div>
                                                    <div class="font-black text-titulo">{{ $med['nombre_medicamento'] ?? '—' }}</div>
                                                    @if(!empty($med['observacion']))
                                                        <div class="mt-1 max-w-xs text-[11px] text-apoyo">{{ $med['observacion'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 align-top">
                                            <div class="font-bold text-titulo">{{ $med['dosis'] ?? '—' }}</div>
                                            <div class="text-xs text-apoyo">{{ $med['frecuencia'] ?? '—' }}</div>
                                        </td>
                                        <td class="px-5 py-3 align-top text-xs font-semibold text-apoyo">{{ $med['via_administracion'] ?? '—' }}</td>
                                        <td class="px-5 py-3 align-top text-xs text-apoyo">
                                            <div>Inicio: {{ $fmtFecha($med['fecha_inicio'] ?? null) }}</div>
                                            <div>Fin: {{ $fmtFecha($med['fecha_fin'] ?? null) }}</div>
                                        </td>
                                        <td class="px-5 py-3 align-top">
                                            @if($med['es_prn'] ?? false)
                                                <span class="rounded-full bg-estado-advertenciaBg px-2 py-0.5 text-[10px] font-black text-estado-advertencia">PRN</span>
                                                @if(!empty($med['condicion_prn']))
                                                    <div class="mt-1 max-w-[180px] text-[10px] text-apoyo">{{ $med['condicion_prn'] }}</div>
                                                @endif
                                            @else
                                                <span class="text-xs text-apoyo">No</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 align-top">
                                            <span class="rounded-full bg-estado-exitoBg px-2 py-0.5 text-[10px] font-black text-estado-exito">{{ $med['estado'] ?? 'ACTIVO' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-14 text-center">
                        <i class="ph-bold ph-pill text-3xl text-apoyo"></i>
                        <h3 class="mt-3 font-black text-titulo">Sin medicación vigente</h3>
                        <p class="mt-1 text-sm text-apoyo">No existen órdenes activas registradas para este residente.</p>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-black uppercase tracking-wider text-titulo">Omisiones recientes</h3>
                        <span class="rounded-full {{ $omisionesUltimos7Dias > 0 ? 'bg-estado-advertenciaBg text-estado-advertencia' : 'bg-fondo-panel text-apoyo' }} px-2 py-0.5 text-[10px] font-black">7 días: {{ $omisionesUltimos7Dias }}</span>
                    </div>
                    <div class="mt-3 space-y-2">
                        @forelse(array_slice($omisionesRecientes, 0, 8) as $omision)
                            <div class="rounded-xl bg-fondo-panel p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-xs font-black text-titulo">{{ data_get($omision, 'medicacion.nombre_medicamento') ?: 'Medicamento no identificado' }}</div>
                                    <div class="text-[10px] font-semibold text-apoyo">{{ $fmtFecha($omision['fecha'] ?? null) }}</div>
                                </div>
                                <div class="mt-1 text-[11px] font-semibold text-estado-advertencia">{{ $omision['resultado'] ?? 'OMITIDO' }}</div>
                                @if(!empty($omision['motivo_omision']))
                                    <div class="mt-1 text-xs text-apoyo">{{ $omision['motivo_omision'] }}</div>
                                @endif
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs font-semibold text-apoyo">Sin omisiones registradas en el periodo consultado.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wider text-titulo">Administraciones recientes</h3>
                    <div class="mt-3 space-y-2">
                        @forelse(array_slice($administracionesRecientes, 0, 8) as $admin)
                            <div class="flex items-start justify-between gap-3 rounded-xl bg-fondo-panel p-3">
                                <div class="min-w-0">
                                    <div class="truncate text-xs font-black text-titulo">{{ data_get($admin, 'medicacion.nombre_medicamento') ?: 'Medicamento no identificado' }}</div>
                                    <div class="mt-1 text-[10px] text-apoyo">{{ $fmtFecha($admin['fecha'] ?? null) }} · {{ !empty($admin['hora_real']) ? substr((string) $admin['hora_real'], 0, 5) : (!empty($admin['hora_programada']) ? substr((string) $admin['hora_programada'], 0, 5) : '—') }}</div>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-[9px] font-black {{ ($admin['administrado'] ?? false) ? 'bg-estado-exitoBg text-estado-exito' : 'bg-estado-advertenciaBg text-estado-advertencia' }}">
                                    {{ $admin['resultado'] ?? (($admin['administrado'] ?? false) ? 'ADMINISTRADO' : 'OMITIDO') }}
                                </span>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs font-semibold text-apoyo">Sin registros recientes de administración.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ====================== SIGNOS ====================== --}}
    @if($tab === 'signos')
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-titulo">Signos vitales</h2>
                    <p class="text-xs text-apoyo">Registros vigentes; no se generan alertas clínicas desde esta vista.</p>
                </div>
                @if($permisosUI['registrar_signos'] ?? false)
                    <button type="button" wire:click="nuevosSignos" class="rm-btn-primary flex h-10 items-center gap-2 px-4 text-xs">
                        <i class="ph-bold ph-heartbeat"></i> Registrar signos
                    </button>
                @else
                    <button type="button" disabled title="No disponible por permisos o restricciones del flujo clínico actual."
                            class="flex h-10 cursor-not-allowed items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-4 text-xs font-bold text-apoyo opacity-60">
                        <i class="ph-bold ph-lock-key"></i> Registrar signos
                    </button>
                @endif
            </div>

            @if($ultimosSignos)
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-black text-titulo">Último registro</h3>
                        <div class="flex items-center gap-2 text-xs text-apoyo">
                            <span>{{ $fmtFecha($ultimosSignos->fecha) }} @if($ultimosSignos->hora) · {{ substr((string) $ultimosSignos->hora, 0, 5) }} @endif</span>
                            @if($ultimosSignos->valor_atipico_confirmado)
                                <span class="rounded-full bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-black text-estado-advertencia">ATÍPICO CONFIRMADO</span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
                        @foreach([
                            ['PA', $ultimosSignos->presion_formateada ?: '—', 'mmHg', 'ph-heart'],
                            ['FC', $ultimosSignos->frecuencia_cardiaca ?? '—', 'bpm', 'ph-heartbeat'],
                            ['FR', $ultimosSignos->frecuencia_respiratoria ?? '—', 'rpm', 'ph-wind'],
                            ['Temp', $ultimosSignos->temperatura ?? '—', '°C', 'ph-thermometer'],
                            ['SpO₂', $ultimosSignos->saturacion ?? '—', '%', 'ph-drop'],
                            ['Glucosa', $ultimosSignos->glucosa ?? '—', 'mg/dL', 'ph-drop-half'],
                            ['Peso', $ultimosSignos->peso ?? '—', 'kg', 'ph-scales'],
                            ['IMC', $ultimosSignos->imc ?? '—', '', 'ph-ruler'],
                        ] as [$lab, $val, $unidad, $icono])
                            <div class="rounded-xl bg-fondo-panel p-3">
                                <div class="flex items-center gap-1 text-[10px] font-black uppercase tracking-wider text-apoyo"><i class="ph-bold {{ $icono }}"></i>{{ $lab }}</div>
                                <div class="mt-1 text-base font-black text-titulo">{{ $val }}</div>
                                @if($unidad !== '')<div class="text-[9px] font-semibold text-apoyo">{{ $unidad }}</div>@endif
                            </div>
                        @endforeach
                    </div>

                    @if($ultimosSignos->observacion)
                        <div class="mt-4 rounded-xl bg-fondo-panel px-4 py-3 text-xs text-apoyo">{{ $ultimosSignos->observacion }}</div>
                    @endif
                </div>
            @endif

            <div class="overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-sm">
                @if(count($signosRecientes) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[900px] text-left text-xs">
                            <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo">
                                <tr>
                                    <th class="px-4 py-3">Fecha / hora</th>
                                    <th class="px-4 py-3">PA</th>
                                    <th class="px-4 py-3">FC</th>
                                    <th class="px-4 py-3">FR</th>
                                    <th class="px-4 py-3">Temp.</th>
                                    <th class="px-4 py-3">SpO₂</th>
                                    <th class="px-4 py-3">Glucosa</th>
                                    <th class="px-4 py-3">Dolor</th>
                                    <th class="px-4 py-3">Registro</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde/60">
                                @foreach($signosRecientes as $signo)
                                    <tr class="hover:bg-fondo-panel/40">
                                        <td class="px-4 py-3 font-bold text-titulo">{{ $fmtFecha($signo['fecha'] ?? null) }} @if(!empty($signo['hora'])) · {{ substr((string) $signo['hora'], 0, 5) }} @endif</td>
                                        <td class="px-4 py-3 text-apoyo">{{ ($signo['presion_sistolica'] ?? null) !== null ? ($signo['presion_sistolica'] . '/' . ($signo['presion_diastolica'] ?? '—')) : ($signo['presion_arterial'] ?? '—') }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $signo['frecuencia_cardiaca'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $signo['frecuencia_respiratoria'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $signo['temperatura'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $signo['saturacion'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $signo['glucosa'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $signo['dolor'] ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            @if($signo['valor_atipico_confirmado'] ?? false)
                                                <span class="rounded-full bg-estado-advertenciaBg px-2 py-0.5 text-[9px] font-black text-estado-advertencia">ATÍPICO CONFIRMADO</span>
                                            @else
                                                <span class="text-apoyo">Vigente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-14 text-center">
                        <i class="ph-bold ph-heartbeat text-3xl text-apoyo"></i>
                        <h3 class="mt-3 font-black text-titulo">Sin signos vitales</h3>
                        <p class="mt-1 text-sm text-apoyo">No existen registros vigentes para este residente.</p>
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- ====================== COGNICIÓN ====================== --}}
    @if($tab === 'cognicion')
        @php
            $evaluacionesCog = collect($evaluacionesGeriatricasRecientes)->filter(fn ($e) => strtoupper((string) data_get($e, 'instrumento.cod_area')) === 'ARE_COG')->values();
        @endphp
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-titulo">Cognición y evaluaciones geriátricas</h2>
                    <p class="text-xs text-apoyo">La ficha muestra resultados registrados; no sustituye la interpretación profesional.</p>
                </div>
                @if($permisosUI['crear_evaluacion'] ?? false)
                    <button type="button" wire:click="nuevaEvaluacionGeriatrica('ARE_COG')" class="rm-btn-primary flex h-10 items-center gap-2 px-4 text-xs">
                        <i class="ph-bold ph-brain"></i> Nueva evaluación cognitiva
                    </button>
                @else
                    <button type="button" disabled class="flex h-10 cursor-not-allowed items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-4 text-xs font-bold text-apoyo opacity-60">
                        <i class="ph-bold ph-lock-key"></i> Nueva evaluación
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm lg:col-span-2">
                    <h3 class="text-sm font-black uppercase tracking-wider text-titulo">Evaluación cognitiva actual</h3>
                    @if($evalCognitiva)
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-xl bg-fondo-panel p-3 sm:col-span-2">
                                <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Instrumento</div>
                                <div class="mt-1 text-sm font-black text-titulo">{{ $evalCognitiva->instrumento?->nombre ?? '—' }}</div>
                            </div>
                            <div class="rounded-xl bg-fondo-panel p-3">
                                <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Puntaje</div>
                                <div class="mt-1 text-xl font-black text-titulo">{{ $evalCognitiva->puntaje_total ?? '—' }}</div>
                            </div>
                            <div class="rounded-xl bg-fondo-panel p-3">
                                <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Riesgo</div>
                                <div class="mt-1 text-sm font-black {{ $riesgoCogClase }} rounded-lg border px-2 py-1 inline-flex">{{ $evalCognitiva->nivel_riesgo ?: 'No registrado' }}</div>
                            </div>
                        </div>
                        <div class="mt-3 text-xs text-apoyo">Fecha: {{ $fmtFecha($evalCognitiva->fecha_eval) }} · Resultado: {{ $evalCognitiva->resultado_cualitativo ?: 'No registrado' }}</div>
                        @if($evalCognitiva->observaciones)
                            <div class="mt-3 rounded-xl bg-fondo-panel p-3 text-xs text-parrafo">{{ $evalCognitiva->observaciones }}</div>
                        @endif
                    @else
                        <div class="mt-4 rounded-xl border border-dashed border-borde bg-fondo-panel p-6 text-center text-sm font-semibold text-apoyo">Sin evaluación cognitiva registrada.</div>
                    @endif
                </div>

                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <h3 class="text-sm font-black uppercase tracking-wider text-titulo">Comparación</h3>
                    <div class="mt-4 rounded-xl bg-fondo-panel p-4">
                        <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Puntaje anterior → actual</div>
                        <div class="mt-2 flex items-center gap-2 text-2xl font-black text-titulo">
                            <span>{{ $puntajeCogAnterior !== null ? $puntajeCogAnterior : '—' }}</span>
                            <i class="ph-bold ph-arrow-right text-sm text-apoyo"></i>
                            <span>{{ $puntajeCogActual !== null ? $puntajeCogActual : '—' }}</span>
                        </div>
                        <p class="mt-2 text-[10px] leading-relaxed text-apoyo">No se interpreta automáticamente la dirección del cambio porque depende del instrumento aplicado.</p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-sm">
                <div class="border-b border-borde px-5 py-4">
                    <h3 class="text-sm font-black uppercase tracking-wider text-titulo">Historial cognitivo</h3>
                </div>
                @if($evaluacionesCog->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[720px] text-left text-xs">
                            <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo">
                                <tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Instrumento</th><th class="px-4 py-3">Puntaje</th><th class="px-4 py-3">Resultado</th><th class="px-4 py-3">Riesgo</th></tr>
                            </thead>
                            <tbody class="divide-y divide-borde/60">
                                @foreach($evaluacionesCog as $eval)
                                    <tr class="hover:bg-fondo-panel/40">
                                        <td class="px-4 py-3 font-bold text-titulo">{{ $fmtFecha($eval['fecha_eval'] ?? null) }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ data_get($eval, 'instrumento.nombre') ?: '—' }}</td>
                                        <td class="px-4 py-3 font-black text-titulo">{{ $eval['puntaje_total'] ?? $eval['puntaje'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $eval['resultado_cualitativo'] ?? $eval['categoria_resultado'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $eval['nivel_riesgo'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 text-center text-sm font-semibold text-apoyo">Sin historial cognitivo.</div>
                @endif
            </div>
        </section>
    @endif

    {{-- ====================== FUNCIONAL ====================== --}}
    @if($tab === 'funcional')
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-titulo">Funcionalidad</h2>
                    <p class="text-xs text-apoyo">Valoración funcional vigente e historial reciente.</p>
                </div>
                @if($permisosUI['crear_barthel'] ?? false)
                    <button type="button" wire:click="nuevaBarthel" class="rm-btn-primary flex h-10 items-center gap-2 px-4 text-xs"><i class="ph-bold ph-plus"></i>Nueva valoración Barthel</button>
                @else
                    <button type="button" disabled title="No disponible por permisos o restricciones del flujo clínico actual."
                            class="flex h-10 cursor-not-allowed items-center gap-2 rounded-xl border border-borde bg-fondo-panel px-4 text-xs font-bold text-apoyo opacity-60"><i class="ph-bold ph-lock-key"></i>Nueva valoración</button>
                @endif
            </div>

            @if($valoracionFuncional)
                <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div class="rounded-2xl bg-fondo-panel p-5 text-center">
                            <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Índice de Barthel</div>
                            <div class="mt-2 text-4xl font-black text-titulo">{{ $valoracionFuncional['indice_barthel'] ?? '—' }}</div>
                            <div class="mt-1 text-xs font-semibold text-apoyo">sobre 100</div>
                        </div>
                        <div class="rounded-2xl bg-fondo-panel p-5">
                            <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Nivel de dependencia</div>
                            <div class="mt-2 text-xl font-black text-titulo">{{ $valoracionFuncional['nivel_dependencia'] ?? 'No registrado' }}</div>
                            <div class="mt-3 text-[10px] font-black uppercase tracking-wider text-apoyo">Riesgo de caída</div>
                            <div class="mt-1 text-sm font-black text-titulo">{{ $valoracionFuncional['riesgo_caida'] ?? 'No registrado' }}</div>
                        </div>
                        <div class="rounded-2xl bg-fondo-panel p-5">
                            <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Fecha de valoración</div>
                            <div class="mt-2 text-lg font-black text-titulo">{{ $fmtFecha($valoracionFuncional['fecha_valoracion'] ?? null) }}</div>
                            @if(!empty($valoracionFuncional['observacion']))
                                <p class="mt-3 text-xs text-apoyo">{{ $valoracionFuncional['observacion'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        @foreach([
                            ['come_solo', 'Come solo'],
                            ['se_bana_solo', 'Se baña solo'],
                            ['se_viste_solo', 'Se viste solo'],
                            ['va_bano_solo', 'Usa retrete solo'],
                            ['camina_solo', 'Camina solo'],
                            ['usa_baston', 'Usa bastón'],
                            ['usa_andador', 'Usa andador'],
                            ['usa_silla_ruedas', 'Silla de ruedas'],
                            ['necesita_supervision', 'Supervisión'],
                        ] as [$campo, $texto])
                            @php $activo = (bool) ($valoracionFuncional[$campo] ?? false); @endphp
                            <div class="flex items-center gap-2 rounded-xl border border-borde px-3 py-2 {{ $activo ? 'bg-estado-exitoBg' : 'bg-fondo-panel' }}">
                                <i class="ph-bold {{ $activo ? 'ph-check-circle text-estado-exito' : 'ph-minus-circle text-apoyo' }}"></i>
                                <span class="text-[11px] font-bold {{ $activo ? 'text-titulo' : 'text-apoyo' }}">{{ $texto }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="rounded-[24px] border border-dashed border-borde bg-fondo-card py-14 text-center">
                    <i class="ph-bold ph-person-simple-walk text-3xl text-apoyo"></i>
                    <h3 class="mt-3 font-black text-titulo">Sin valoración funcional</h3>
                    <p class="mt-1 text-sm text-apoyo">No existe una valoración funcional vigente o histórica cargada.</p>
                </div>
            @endif

            <div class="overflow-hidden rounded-[24px] border border-borde bg-fondo-card shadow-sm">
                <div class="border-b border-borde px-5 py-4"><h3 class="text-sm font-black uppercase tracking-wider text-titulo">Historial funcional</h3></div>
                @if(count($valoracionesFuncionalesRecientes) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left text-xs">
                            <thead class="bg-fondo-panel text-[10px] font-black uppercase tracking-wider text-apoyo"><tr><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Barthel</th><th class="px-4 py-3">Dependencia</th><th class="px-4 py-3">Riesgo caída</th><th class="px-4 py-3">Estado</th></tr></thead>
                            <tbody class="divide-y divide-borde/60">
                                @foreach($valoracionesFuncionalesRecientes as $val)
                                    <tr class="hover:bg-fondo-panel/40">
                                        <td class="px-4 py-3 font-bold text-titulo">{{ $fmtFecha($val['fecha_valoracion'] ?? null) }}</td>
                                        <td class="px-4 py-3 font-black text-titulo">{{ $val['indice_barthel'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $val['nivel_dependencia'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $val['riesgo_caida'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-apoyo">{{ $val['estado'] ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-10 text-center text-sm font-semibold text-apoyo">Sin historial funcional.</div>
                @endif
            </div>
        </section>
    @endif

    {{-- ====================== INTERCONSULTAS ====================== --}}
    @if($tab === 'interconsultas')
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-black text-titulo">Interconsultas registradas</h2>
                <p class="text-xs text-apoyo">Se muestran notas médicas de tipo INTERCONSULTA. El esquema actual no define estados pendiente/respondida/cerrada.</p>
            </div>

            @forelse($interconsultasRecientes as $inter)
                <article class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-borde pb-3">
                        <span class="rounded-full bg-boton-acento/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-boton-acento">Interconsulta</span>
                        <span class="text-xs font-semibold text-apoyo">{{ $fmtFecha($inter['fecha'] ?? null) }} @if(!empty($inter['hora'])) · {{ substr((string) $inter['hora'], 0, 5) }} @endif</span>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div class="rounded-xl bg-fondo-panel p-3"><div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Valoración</div><p class="mt-1 whitespace-pre-line text-xs font-semibold text-titulo">{{ $inter['valoracion'] ?? '—' }}</p></div>
                        <div class="rounded-xl bg-fondo-panel p-3"><div class="text-[10px] font-black uppercase tracking-wider text-apoyo">Plan</div><p class="mt-1 whitespace-pre-line text-xs font-semibold text-titulo">{{ $inter['plan'] ?? '—' }}</p></div>
                    </div>
                    <div class="mt-3 text-[10px] font-semibold text-apoyo">Registrado por: {{ data_get($inter, 'registrador.name') ?: 'Profesional no identificado' }}</div>
                </article>
            @empty
                <div class="rounded-[24px] border border-dashed border-borde bg-fondo-card py-14 text-center">
                    <i class="ph-bold ph-arrows-left-right text-3xl text-apoyo"></i>
                    <h3 class="mt-3 font-black text-titulo">Sin interconsultas registradas</h3>
                    <p class="mt-1 text-sm text-apoyo">No existen notas activas de tipo INTERCONSULTA para este residente.</p>
                </div>
            @endforelse
        </section>
    @endif

    {{-- ====================== ALERTAS ====================== --}}
    @if($tab === 'alertas')
        <section class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-black text-titulo">Alertas clínicas activas</h2>
                    <p class="text-xs text-apoyo">Fuente formal: alertas del residente en estado ABIERTA o EN_ATENCION.</p>
                </div>
                @if($permisosUI['ver_alertas'] ?? false)
                    <a href="{{ route('admin.medico.alertas', ['adulto' => $adulto->cod_am]) }}"
                       class="rm-btn-secondary flex h-10 items-center gap-2 px-4 text-xs">
                        <i class="ph-bold ph-arrow-square-out"></i> Abrir módulo de alertas
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                @forelse($alertasActivas as $alerta)
                    @php
                        $nivel = strtoupper((string) ($alerta['nivel'] ?? ''));
                        $clase = match ($nivel) {
                            'CRITICO', 'CRÍTICO', 'ALTO' => 'border-estado-error/20 bg-estado-errorBg',
                            'MEDIO', 'MODERADO' => 'border-estado-advertencia/20 bg-estado-advertenciaBg',
                            default => 'border-borde bg-fondo-card',
                        };
                    @endphp
                    <article class="rounded-[24px] border p-5 shadow-sm {{ $clase }}">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider text-apoyo">{{ $alerta['tipo_alerta'] ?? 'Alerta clínica' }}</div>
                                <div class="mt-1 text-base font-black text-titulo">{{ $alerta['motivo'] ?? 'Sin motivo registrado' }}</div>
                            </div>
                            <div class="text-right">
                                <span class="rounded-full bg-fondo-card/70 px-2.5 py-1 text-[10px] font-black text-titulo">{{ $nivel ?: 'SIN NIVEL' }}</span>
                                <div class="mt-1 text-[10px] font-semibold text-apoyo">{{ $alerta['estado'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="rounded-xl bg-fondo-card/60 p-3"><div class="text-[9px] font-black uppercase tracking-wider text-apoyo">Origen</div><div class="mt-1 text-xs font-bold text-titulo">{{ $alerta['origen'] ?? 'No registrado' }}</div></div>
                            <div class="rounded-xl bg-fondo-card/60 p-3"><div class="text-[9px] font-black uppercase tracking-wider text-apoyo">Responsable</div><div class="mt-1 text-xs font-bold text-titulo">{{ data_get($alerta, 'responsable.name') ?: 'Sin asignar' }}</div></div>
                        </div>
                        @if(!empty($alerta['accion_tomada']))
                            <div class="mt-3 rounded-xl bg-fondo-card/60 p-3 text-xs text-parrafo"><span class="font-black">Acción registrada:</span> {{ $alerta['accion_tomada'] }}</div>
                        @endif
                    </article>
                @empty
                    <div class="xl:col-span-2 rounded-[24px] border border-dashed border-borde bg-fondo-card py-14 text-center">
                        <i class="ph-bold ph-check-circle text-3xl text-estado-exito"></i>
                        <h3 class="mt-3 font-black text-titulo">Sin alertas activas</h3>
                        <p class="mt-1 text-sm text-apoyo">No hay alertas en estado abierta o en atención para este residente.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- ====================== HISTORIAL ====================== --}}
    @if($tab === 'historial')
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-black text-titulo">Historial clínico integrado</h2>
                <p class="text-xs text-apoyo">Cronología generada con registros existentes; no crea diagnósticos ni estados adicionales.</p>
            </div>

            <div class="rounded-[24px] border border-borde bg-fondo-card p-5 shadow-sm">
                @forelse($historialClinico as $evento)
                    @php
                        $tipoEvento = $evento['tipo'] ?? '';
                        [$iconoEvento, $claseEvento] = match ($tipoEvento) {
                            'NOTA_MEDICA' => ['ph-stethoscope', 'bg-boton-acento/10 text-boton-acento'],
                            'SIGNOS_VITALES' => ['ph-heartbeat', 'bg-estado-infoBg text-estado-info'],
                            'MEDICACION' => ['ph-pill', 'bg-estado-exitoBg text-estado-exito'],
                            'EVALUACION_GERIATRICA' => ['ph-brain', 'bg-estado-infoBg text-estado-info'],
                            'VALORACION_FUNCIONAL' => ['ph-person-simple-walk', 'bg-estado-exitoBg text-estado-exito'],
                            'ALERTA' => ['ph-warning-circle', 'bg-estado-errorBg text-estado-error'],
                            'SEGUIMIENTO_ENFERMERIA' => ['ph-first-aid', 'bg-estado-advertenciaBg text-estado-advertencia'],
                            'ATENCION' => ['ph-calendar-check', 'bg-fondo-panel text-apoyo'],
                            default => ['ph-circle', 'bg-fondo-panel text-apoyo'],
                        };
                    @endphp
                    <div class="relative flex gap-4 pb-5 last:pb-0">
                        <div class="absolute left-[18px] top-9 h-[calc(100%-24px)] w-px bg-borde last:hidden"></div>
                        <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $claseEvento }}"><i class="ph-bold {{ $iconoEvento }}"></i></div>
                        <div class="min-w-0 flex-1 rounded-2xl bg-fondo-panel p-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <div class="font-black text-titulo">{{ $evento['titulo'] ?? 'Evento clínico' }}</div>
                                <div class="text-[10px] font-semibold text-apoyo">{{ $fmtFechaHora($evento['fecha_hora'] ?? null) }}</div>
                            </div>
                            @if(!empty($evento['detalle']))
                                <p class="mt-2 whitespace-pre-line text-xs text-parrafo">{{ $evento['detalle'] }}</p>
                            @endif
                            @if(!empty($evento['responsable']))
                                <div class="mt-2 text-[10px] font-semibold text-apoyo">Registrado por: {{ $evento['responsable'] }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <i class="ph-bold ph-clock-counter-clockwise text-3xl text-apoyo"></i>
                        <h3 class="mt-3 font-black text-titulo">Sin historial clínico</h3>
                        <p class="mt-1 text-sm text-apoyo">No existen eventos disponibles para construir la cronología.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- Modales: solo se montan cuando el usuario puede intentar la acción correspondiente. --}}
    @if($permisosUI['crear_consulta'] ?? false)
        @livewire('clinica.nota-evolucion-medica-modal')
    @endif

    @if($permisosUI['registrar_signos'] ?? false)
        @livewire('clinica.registro-signos-vitales-modal')
    @endif

    @if($permisosUI['crear_barthel'] ?? false)
        @livewire('valoraciones.valoracion-barthel-modal')
    @endif

    @if($permisosUI['crear_evaluacion'] ?? false)
        @livewire('valoraciones.evaluacion-geriatrica-area-modal')
    @endif
</div>
