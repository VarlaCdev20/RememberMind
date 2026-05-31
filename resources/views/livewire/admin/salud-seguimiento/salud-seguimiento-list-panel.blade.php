<div class="relative mx-auto max-w-7xl space-y-5 overflow-hidden rounded-[2rem] border border-[#C7B5A3]/70 bg-[#E6DDD3]/55 p-3 text-[#2F3E5C] shadow-[0_22px_70px_rgba(47,62,92,0.16)] backdrop-blur-xl sm:p-5 lg:p-6">
    <div class="pointer-events-none absolute inset-0 dash-noise opacity-[0.04]"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-48 bg-gradient-to-b from-[#F8F3ED]/70 to-transparent"></div>
    <div class="pointer-events-none absolute -right-24 top-16 h-72 w-72 rounded-full bg-[#E27D60]/12 blur-3xl"></div>
    <div class="pointer-events-none absolute -left-24 bottom-20 h-72 w-72 rounded-full bg-[#8DA280]/12 blur-3xl"></div>

    <div class="relative z-10 space-y-5">
        @if($seccionActiva === 'resumen')
            {{-- ENCABEZADO PRINCIPAL --}}
            <section class="overflow-hidden rounded-[1.75rem] border border-[#C7B5A3]/80 bg-gradient-to-br from-[#E6DDD3]/95 via-[#F3ECE4]/92 to-[#D5C7B9]/85 shadow-[0_18px_46px_rgba(47,62,92,0.13)]">
                <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                <div class="p-5 sm:p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/20 bg-[#E27D60]/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-[#E27D60]">
                            <i class="ph-bold ph-heartbeat text-sm"></i>
                            CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS - Area clinico asistencial
                        </span>
                        <h1 class="mt-3 text-2xl font-black tracking-tight text-[#2F3E5C] sm:text-3xl">
                            Salud y Seguimiento
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/72">
                            Panel institucional para revisar fichas medicas, signos vitales, valoraciones funcionales,
                            medicacion, administraciones y alertas preventivas de los adultos mayores.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @can('salud.ficha.crear')
                                <button wire:click="cambiarSeccion('ficha')" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#E27D60] px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-[0_10px_24px_rgba(226,125,96,0.25)] transition hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-95">
                                    <i class="ph-bold ph-plus-circle text-sm"></i>
                                    Nuevo registro de salud
                                </button>
                            @endcan
                            <button wire:click="cambiarSeccion('reportes')" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/80 bg-[#D5C7B9]/65 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] shadow-sm transition hover:bg-[#C7B5A3]/70 active:scale-95">
                                <i class="ph-bold ph-chart-bar text-sm"></i>
                                Reportes
                            </button>
                        </div>
                    </div>

                    <div class="grid w-full grid-cols-2 gap-3 sm:grid-cols-3 xl:max-w-xl">
                        @php
                            $metricasHeader = [
                                ['label' => 'Seguimientos activos', 'valor' => $stats['seguimientos_activos'] ?? 0, 'icono' => 'ph-users-three', 'color' => 'text-[#2F3E5C]', 'bg' => 'bg-[#F8F3ED]/70'],
                                ['label' => 'Alertas pendientes', 'valor' => $stats['alertas_pendientes'] ?? 0, 'icono' => 'ph-warning-circle', 'color' => 'text-[#E27D60]', 'bg' => 'bg-[#E27D60]/10'],
                                ['label' => 'Medicaciones activas', 'valor' => $stats['medicaciones_activas'] ?? 0, 'icono' => 'ph-pill', 'color' => 'text-[#63775B]', 'bg' => 'bg-[#8DA280]/14'],
                                ['label' => 'Controles recientes', 'valor' => $stats['signos_recientes'] ?? 0, 'icono' => 'ph-activity', 'color' => 'text-[#2F3E5C]', 'bg' => 'bg-[#D5C7B9]/55'],
                                ['label' => 'Valoraciones registradas', 'valor' => $stats['valoraciones'] ?? 0, 'icono' => 'ph-person-simple-walk', 'color' => 'text-[#9A7B60]', 'bg' => 'bg-[#F8F3ED]/62'],
                                ['label' => 'Controles de hoy', 'valor' => $stats['controles_hoy'] ?? 0, 'icono' => 'ph-calendar-check', 'color' => 'text-[#E27D60]', 'bg' => 'bg-[#F2DFD8]/70'],
                            ];
                        @endphp
                        @foreach($metricasHeader as $metrica)
                            <div class="relative overflow-hidden rounded-2xl border border-[#C7B5A3]/55 {{ $metrica['bg'] }} p-3.5 shadow-sm backdrop-blur-md">
                                <i class="ph-bold {{ $metrica['icono'] }} absolute right-3 top-3 text-2xl text-[#2F3E5C]/10"></i>
                                <p class="pr-7 text-[10px] font-black uppercase leading-tight tracking-[0.12em] text-[#2F3E5C]/60">{{ $metrica['label'] }}</p>
                                <p class="mt-2 text-2xl font-black leading-none {{ $metrica['color'] }}">{{ $metrica['valor'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- TABS --}}
        <nav class="overflow-x-auto rounded-[1.45rem] border border-[#C7B5A3]/70 bg-[#D5C7B9]/70 p-2 shadow-sm backdrop-blur-xl scrollbar-hidden">
            <div class="flex min-w-max items-center gap-2">
                @php
                    $tabs = [
                        'resumen' => ['label' => 'Resumen', 'icon' => 'ph-squares-four'],
                        'ficha' => ['label' => 'Ficha medica', 'icon' => 'ph-file-text'],
                        'signos' => ['label' => 'Signos vitales', 'icon' => 'ph-activity'],
                        'valoracion' => ['label' => 'Valoracion funcional', 'icon' => 'ph-person-simple-walk'],
                        'medicacion' => ['label' => 'Medicacion', 'icon' => 'ph-pill'],
                        'administracion' => ['label' => 'Administracion', 'icon' => 'ph-prescription'],
                        'alertas' => ['label' => 'Alertas', 'icon' => 'ph-warning-circle'],
                        'reportes' => ['label' => 'Reportes', 'icon' => 'ph-chart-bar'],
                    ];
                @endphp

                @foreach($tabs as $key => $tab)
                    <button
                        wire:click="cambiarSeccion('{{ $key }}')"
                        type="button"
                        class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border px-3.5 text-[11px] font-black uppercase tracking-wide transition active:scale-95 {{ $seccionActiva === $key ? 'border-[#2F3E5C] bg-[#2F3E5C] text-white shadow-[0_8px_18px_rgba(47,62,92,0.18)]' : 'border-transparent bg-[#E6DDD3]/45 text-[#2F3E5C]/72 hover:border-[#C7B5A3]/70 hover:bg-[#E6DDD3]/85 hover:text-[#E27D60]' }}"
                    >
                        <i class="ph-bold {{ $tab['icon'] }} text-sm {{ $seccionActiva === $key ? 'text-[#E27D60]' : 'text-[#2F3E5C]/52' }}"></i>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>
        </nav>

        {{-- CONTENIDO --}}
            @php
                $totalBase = max(($stats['seguimientos_activos'] ?? 0), 1);
                $porcentajeFichas = min(100, round((($stats['total_fichas'] ?? 0) / $totalBase) * 100));
                $porcentajeMedicacion = min(100, round((($stats['medicaciones_activas'] ?? 0) / $totalBase) * 100));
                $porcentajeControles = min(100, round((($stats['signos_recientes'] ?? 0) / $totalBase) * 100));
            @endphp

            <section class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="space-y-5">
                    <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">Resumen ejecutivo</span>
                                <h2 class="mt-1 text-xl font-black text-[#2F3E5C]">Mapa operativo de salud</h2>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full bg-[#2F3E5C]/8 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/65">
                                <i class="ph-bold ph-clock"></i>
                                Actualizado en tiempo real
                            </span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            @foreach([
                                ['label' => 'Cobertura de fichas', 'valor' => $porcentajeFichas, 'icono' => 'ph-file-text', 'color' => '#E27D60'],
                                ['label' => 'Medicacion activa', 'valor' => $porcentajeMedicacion, 'icono' => 'ph-pill', 'color' => '#63775B'],
                                ['label' => 'Controles 7 dias', 'valor' => $porcentajeControles, 'icono' => 'ph-activity', 'color' => '#2F3E5C'],
                            ] as $barra)
                                <div class="rounded-2xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/62 p-4">
                                    <div class="mb-3 flex items-center justify-between gap-2">
                                        <p class="text-[10px] font-black uppercase tracking-[0.12em] text-[#2F3E5C]/60">{{ $barra['label'] }}</p>
                                        <i class="ph-bold {{ $barra['icono'] }} text-lg" style="color: {{ $barra['color'] }}"></i>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-[#D5C7B9]/75">
                                        <div class="h-full rounded-full" style="width: {{ $barra['valor'] }}%; background-color: {{ $barra['color'] }}"></div>
                                    </div>
                                    <p class="mt-2 text-2xl font-black leading-none" style="color: {{ $barra['color'] }}">{{ $barra['valor'] }}%</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid gap-5 xl:grid-cols-2">
                        <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Ultimos controles</h3>
                                <i class="ph-bold ph-activity text-xl text-[#E27D60]"></i>
                            </div>
                            <div class="space-y-3">
                                @forelse($resumenData['controlesRecientes'] as $control)
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/55 px-3 py-3">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-black text-[#2F3E5C]">{{ $control->adultoMayor?->nombres }} {{ $control->adultoMayor?->ap_paterno }}</p>
                                            <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]/55">{{ $control->fecha?->format('d/m/Y') }} - {{ $control->hora_formateada }}</p>
                                        </div>
                                        <div class="flex shrink-0 gap-1.5 text-[10px] font-black">
                                            <span class="rounded-full bg-[#2F3E5C]/8 px-2 py-1 text-[#2F3E5C]">{{ $control->presion_formateada ?? 'S/D' }}</span>
                                            <span class="rounded-full bg-[#E27D60]/10 px-2 py-1 text-[#E27D60]">{{ $control->saturacion !== null ? $control->saturacion . '%' : 'SpO2' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-[#C7B5A3]/60 bg-[#E6DDD3]/35 p-6 text-center">
                                        <p class="text-xs font-bold text-[#2F3E5C]/55">Aun no hay controles recientes.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Medicacion activa</h3>
                                <i class="ph-bold ph-pill text-xl text-[#63775B]"></i>
                            </div>
                            <div class="space-y-3">
                                @forelse($resumenData['medicaciones'] as $med)
                                    <div class="rounded-2xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/55 px-3 py-3">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-black text-[#2F3E5C]">{{ $med->nombre_medicamento }}</p>
                                                <p class="mt-0.5 text-[10px] font-bold text-[#2F3E5C]/55">{{ $med->adultoMayor?->nombres }} {{ $med->adultoMayor?->ap_paterno }}</p>
                                            </div>
                                            <span class="rounded-full bg-[#8DA280]/18 px-2 py-1 text-xs font-black uppercase text-[#63775B]">Activo</span>
                                        </div>
                                        <p class="mt-2 text-xs font-bold text-[#2F3E5C]/65">{{ $med->dosis }} - {{ $med->frecuencia }} - {{ $med->via_administracion }}</p>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-[#C7B5A3]/60 bg-[#E6DDD3]/35 p-6 text-center">
                                        <p class="text-xs font-bold text-[#2F3E5C]/55">No hay medicaciones activas registradas.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-[1.6rem] border border-[#E27D60]/25 bg-[#E27D60]/8 p-5 shadow-sm backdrop-blur-xl">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-[#E27D60]">Alertas</span>
                                <h3 class="mt-1 text-lg font-black text-[#2F3E5C]">Prioridades preventivas</h3>
                            </div>
                            <i class="ph-bold ph-warning-circle text-2xl text-[#E27D60]"></i>
                        </div>
                        <div class="space-y-3">
                            @forelse($resumenData['alertas'] as $alerta)
                                <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#F3ECE4]/70 p-3">
                                    <div class="flex items-start gap-3">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#E27D60]/12 text-[#E27D60]">
                                            <i class="ph-bold {{ $alerta['icono'] }}"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="truncate text-xs font-black text-[#2F3E5C]">{{ $alerta['titulo'] }}</p>
                                                <span class="rounded-full bg-[#D5C7B9]/70 px-2 py-0.5 text-[10px] font-black uppercase tracking-wide text-[#2F3E5C]/60">{{ $alerta['tipo'] }}</span>
                                            </div>
                                            <p class="mt-1 text-[11px] font-bold leading-relaxed text-[#2F3E5C]/65">{{ $alerta['detalle'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-[#8DA280]/30 bg-[#8DA280]/12 p-5 text-center">
                                    <i class="ph-bold ph-check-circle text-3xl text-[#63775B]"></i>
                                    <p class="mt-2 text-xs font-black text-[#2F3E5C]">Sin alertas pendientes</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-5 shadow-sm backdrop-blur-xl">
                        <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Accesos rapidos</h3>
                        <div class="mt-4 grid grid-cols-2 gap-2">
                            @foreach([
                                ['key' => 'ficha', 'label' => 'Ficha medica', 'icon' => 'ph-file-text'],
                                ['key' => 'signos', 'label' => 'Signos vitales', 'icon' => 'ph-activity'],
                                ['key' => 'medicacion', 'label' => 'Medicacion', 'icon' => 'ph-pill'],
                                ['key' => 'alertas', 'label' => 'Alertas', 'icon' => 'ph-warning'],
                            ] as $atajo)
                                <button wire:click="cambiarSeccion('{{ $atajo['key'] }}')" type="button" class="group rounded-2xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/62 px-3 py-3 text-left transition hover:-translate-y-0.5 hover:border-[#E27D60]/45 hover:bg-[#F8F3ED]/70">
                                    <i class="ph-bold {{ $atajo['icon'] }} text-lg text-[#E27D60] transition group-hover:scale-110"></i>
                                    <p class="mt-2 text-xs font-black uppercase leading-tight tracking-wide text-[#2F3E5C]">{{ $atajo['label'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </aside>
            </section>
        @else
            {{-- BOTÓN GLOBAL "VOLVER AL RESUMEN" PARA LOS DEMÁS SUBMÓDULOS --}}
            <div class="mb-2">
                <button wire:click="cambiarSeccion('resumen')" class="inline-flex items-center gap-2 rounded-xl bg-[#E6DDD3]/60 px-4 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition-all hover:bg-[#C7B5A3]/70 hover:shadow-sm">
                    <i class="ph-bold ph-arrow-left text-sm"></i>
                    Volver al resumen
                </button>
            </div>

            @if($seccionActiva === 'alertas')
                <section class="animate-in fade-in duration-200">
                @livewire('admin.salud-seguimiento.salud-alertas-panel', key('alertas'))
            </section>
        @elseif($seccionActiva === 'reportes')
            <section class="animate-in fade-in duration-200">
                @livewire('admin.salud-seguimiento.salud-reportes-panel', key('reportes'))
            </section>
        @elseif($seccionActiva === 'medicacion')
            <section class="animate-in fade-in duration-200">
                @livewire('admin.salud-seguimiento.salud-medicacion-panel', key('medicacion'))
            </section>
        @elseif($seccionActiva === 'signos')
            <section class="animate-in fade-in duration-200">
                @livewire('admin.salud-seguimiento.salud-signos-panel', key('signos'))
            </section>
        @elseif($seccionActiva === 'ficha')
            <section class="animate-in fade-in duration-200">
                @livewire('admin.salud-seguimiento.salud-ficha-panel', key('ficha-general'))
            </section>
        @else
            <section class="space-y-5 animate-in fade-in duration-200">
                <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-4 shadow-sm backdrop-blur-xl sm:p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-2xl">
                            <span class="text-xs font-black uppercase tracking-[0.15em] text-[#E27D60]">{{ $contexto['titulo'] }}</span>
                            <h2 class="mt-1 text-xl font-black text-[#2F3E5C]">Seleccionar expediente</h2>
                            <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/62">{{ $contexto['descripcion'] }}</p>
                        </div>
                        <div class="grid w-full gap-3 sm:grid-cols-[1fr_auto] lg:max-w-xl">
                            <label class="relative block">
                                <span class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Buscar adulto mayor</span>
                                <i class="ph-bold ph-magnifying-glass absolute bottom-3 left-3.5 text-[#2F3E5C]/40"></i>
                                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nombre, apellido o codigo..." class="w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 py-2.5 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition placeholder:text-[#2F3E5C]/40 focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                            </label>
                            <div class="flex items-end">
                                <button type="button" wire:click="$refresh" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#D5C7B9]/70 px-4 text-xs font-black uppercase tracking-wider text-[#2F3E5C] transition hover:bg-[#C7B5A3]/80 active:scale-95">
                                    <i class="ph-bold ph-arrows-clockwise"></i>
                                    Actualizar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse($adultos as $adulto)
                        @php
                            $estadoTexto = strtoupper($adulto->estado->estado ?? 'ACTIVO');
                            $ficha = $adulto->fichasMedicas->sortByDesc('updated_at')->first();
                            $signo = $adulto->signosVitales->sortByDesc('fecha')->first();
                            $valoracion = $adulto->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
                            $medicacionesActivas = $adulto->medicaciones->where('estado', 'ACTIVO')->count();
                            $edad = $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age . ' anos' : 'Sin edad';
                            $estadoClase = in_array($estadoTexto, ['ACTIVO', 'ACTIVA']) ? 'bg-[#8DA280]/18 text-[#63775B] border-[#8DA280]/25' : 'bg-[#D5C7B9]/70 text-[#2F3E5C]/60 border-[#C7B5A3]/40';
                        @endphp

                        <article class="group relative overflow-hidden rounded-[1.55rem] border border-[#C7B5A3]/60 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:border-[#E27D60]/40 hover:shadow-[0_18px_38px_rgba(47,62,92,0.14)]">
                            <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                            <div class="relative bg-gradient-to-b from-[#D5C7B9]/72 to-[#E6DDD3]/30 px-5 pb-5 pt-4 text-center">
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <span class="rounded-full border px-2.5 py-1 text-xs font-black uppercase tracking-wide {{ $estadoClase }}">{{ $estadoTexto }}</span>
                                    <span class="rounded-full border border-[#C7B5A3]/45 bg-[#F8F3ED]/62 px-2.5 py-1 text-xs font-black uppercase tracking-wide text-[#2F3E5C]/55">{{ $adulto->cod_am }}</span>
                                </div>

                                <div class="mx-auto h-20 w-20 overflow-hidden rounded-2xl border-[4px] border-[#F8F3ED]/75 bg-[#2F3E5C] shadow-md transition group-hover:scale-105">
                                    @if($adulto->foto)
                                        <img src="{{ Storage::url($adulto->foto) }}" alt="{{ $adulto->nombres }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] text-xl font-black text-white">
                                            {{ substr($adulto->nombres, 0, 1) }}{{ substr($adulto->ap_paterno, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <h3 class="mt-3 text-base font-black leading-tight text-[#2F3E5C]">{{ $adulto->nombres }}</h3>
                                <p class="text-[11px] font-bold text-[#2F3E5C]/65">{{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</p>
                            </div>

                            <div class="space-y-3 px-5 py-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="rounded-xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/48 px-3 py-2">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-[#2F3E5C]/55">Edad</p>
                                        <p class="mt-0.5 text-xs font-black text-[#2F3E5C]">{{ $edad }}</p>
                                    </div>
                                    <div class="rounded-xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/48 px-3 py-2">
                                        <p class="text-[10px] font-black uppercase tracking-wide text-[#2F3E5C]/55">C.I.</p>
                                        <p class="mt-0.5 truncate text-xs font-black text-[#2F3E5C]">{{ $adulto->ci ?: 'S/D' }}</p>
                                    </div>
                                </div>

                                @if($seccionActiva === 'ficha')
                                    <div class="rounded-2xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/48 p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wide text-[#2F3E5C]/60">Estado de ficha</span>
                                            <span class="rounded-full px-2.5 py-0.5 text-xs font-black uppercase {{ $ficha ? 'bg-[#8DA280]/18 text-[#63775B]' : 'bg-[#E27D60]/12 text-[#E27D60]' }}">{{ $ficha ? 'Registrada' : 'Pendiente' }}</span>
                                        </div>
                                        <p class="mt-2 text-xs font-bold text-[#2F3E5C]/62">{{ $ficha ? 'Actualizada ' . $ficha->updated_at->format('d/m/Y') : 'Requiere apertura de expediente medico base.' }}</p>
                                    </div>
                                @elseif($seccionActiva === 'signos')
                                    <div class="grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-xl bg-[#E6DDD3]/55 px-2 py-2">
                                            <p class="text-[10px] font-black uppercase text-[#2F3E5C]/55">P.A.</p>
                                            <p class="text-xs font-black text-[#2F3E5C]">{{ $signo?->presion_formateada ?? 'S/D' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-[#E6DDD3]/55 px-2 py-2">
                                            <p class="text-[10px] font-black uppercase text-[#2F3E5C]/55">Temp.</p>
                                            <p class="text-xs font-black text-[#E27D60]">{{ $signo?->temperatura ? number_format($signo->temperatura, 1) . 'C' : 'S/D' }}</p>
                                        </div>
                                        <div class="rounded-xl bg-[#E6DDD3]/55 px-2 py-2">
                                            <p class="text-[10px] font-black uppercase text-[#2F3E5C]/55">SpO2</p>
                                            <p class="text-xs font-black text-[#63775B]">{{ $signo?->saturacion !== null ? $signo->saturacion . '%' : 'S/D' }}</p>
                                        </div>
                                    </div>
                                @elseif($seccionActiva === 'valoracion')
                                    <div class="rounded-2xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/48 p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wide text-[#2F3E5C]/60">Dependencia</span>
                                            <span class="rounded-full bg-[#2F3E5C]/8 px-2.5 py-0.5 text-xs font-black uppercase text-[#2F3E5C]">{{ $valoracion?->nivel_dependencia ?? 'Sin dato' }}</span>
                                        </div>
                                        <p class="mt-2 text-xs font-bold text-[#2F3E5C]/62">Riesgo de caida: <span class="font-black text-[#E27D60]">{{ $valoracion?->riesgo_caida ?? 'Sin valorar' }}</span></p>
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-[#C7B5A3]/35 bg-[#E6DDD3]/48 p-3">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[10px] font-black uppercase tracking-wide text-[#2F3E5C]/60">Tratamientos activos</span>
                                            <span class="rounded-full bg-[#8DA280]/18 px-2.5 py-0.5 text-xs font-black uppercase text-[#63775B]">{{ $medicacionesActivas }}</span>
                                        </div>
                                        <p class="mt-2 text-xs font-bold text-[#2F3E5C]/62">{{ $medicacionesActivas > 0 ? 'Listo para revisar prescripciones y administraciones.' : 'Sin medicacion activa registrada.' }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="border-t border-[#C7B5A3]/35 bg-[#D5C7B9]/32 p-4">
                                <button wire:click="abrirExpediente('{{ $adulto->cod_am }}')" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-[#5B5F97] active:scale-95">
                                    <i class="ph-bold {{ $contexto['icono'] }}"></i>
                                    {{ $contexto['boton'] }}
                                </button>
                            </div>
                        </article>
                    @empty
                        <div class="col-span-full rounded-[1.6rem] border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/42 p-12 text-center shadow-inner">
                            <i class="ph-bold ph-users-three text-4xl text-[#2F3E5C]/25"></i>
                            <h3 class="mt-3 text-base font-black text-[#2F3E5C]">No se encontraron expedientes</h3>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Ajusta la busqueda o actualiza la vista para revisar otros registros.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6 flex justify-center">
                    {{ $adultos->links() }}
                </div>
            </section>
            @endif
        @endif
    </div>

    {{-- MODAL LATERAL DE EXPEDIENTE INDIVIDUAL --}}
    @if($adultoSeleccionadoParaModal)
        <div class="fixed inset-0 z-[100] flex justify-end font-sans">
            <div class="absolute inset-0 bg-[#2F3E5C]/62 backdrop-blur-md" wire:click="cerrarExpediente"></div>

            <aside class="salud-slide-panel relative flex h-full w-full max-w-5xl flex-col overflow-hidden border-l border-[#C7B5A3]/70 bg-[#E6DDD3] shadow-[-22px_0_60px_rgba(47,62,92,0.28)] sm:rounded-l-[2rem]">
                <div class="pointer-events-none absolute inset-0 dash-noise opacity-[0.035]"></div>
                <div class="pointer-events-none absolute -left-20 -top-20 h-64 w-64 rounded-full bg-[#E27D60]/12 blur-3xl"></div>
                <div class="relative z-10 flex items-center justify-between border-b border-[#C7B5A3]/60 bg-[#F3ECE4]/85 px-5 py-4 backdrop-blur-xl sm:px-7">
                    <div class="flex min-w-0 items-center gap-4">
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl border-[3px] border-[#F8F3ED]/80 bg-[#2F3E5C] shadow-sm">
                            @if($adultoSeleccionadoParaModal->foto)
                                <img src="{{ Storage::url($adultoSeleccionadoParaModal->foto) }}" alt="{{ $adultoSeleccionadoParaModal->nombres }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#2F3E5C] to-[#5B5F97] text-sm font-black text-white">
                                    {{ substr($adultoSeleccionadoParaModal->nombres, 0, 1) }}{{ substr($adultoSeleccionadoParaModal->ap_paterno, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#E27D60]/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-[#E27D60]">
                                <i class="ph-bold {{ $contexto['icono'] }}"></i>
                                {{ $contexto['titulo'] }}
                            </span>
                            <h2 class="mt-1 truncate text-lg font-black text-[#2F3E5C]">
                                {{ $adultoSeleccionadoParaModal->nombres }} {{ $adultoSeleccionadoParaModal->ap_paterno }} {{ $adultoSeleccionadoParaModal->ap_materno }}
                            </h2>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-[#2F3E5C]/45">{{ $adultoSeleccionadoParaModal->cod_am }}</p>
                        </div>
                    </div>
                    <button wire:click="cerrarExpediente" type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[#C7B5A3]/60 bg-[#D5C7B9]/70 text-[#2F3E5C] shadow-sm transition hover:bg-[#E27D60] hover:text-white active:scale-95">
                        <i class="ph-bold ph-x text-lg"></i>
                    </button>
                </div>

                <div class="relative z-10 flex-1 overflow-y-auto bg-[#F3ECE4]/60 p-4 sm:p-6">
                    @if($seccionActiva === 'valoracion')
                        @livewire('admin.salud-seguimiento.salud-valoracion-panel', ['adulto' => $adultoSeleccionadoParaModal], key('val-'.$adultoSeleccionadoParaModal->cod_am))
                    @elseif($seccionActiva === 'administracion')
                        @livewire('admin.salud-seguimiento.salud-administracion-medicacion-panel', ['adulto' => $adultoSeleccionadoParaModal], key('adminmed-'.$adultoSeleccionadoParaModal->cod_am))
                    @endif
                </div>
            </aside>
        </div>
    @endif
</div>
