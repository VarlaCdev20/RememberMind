<x-sistema-layout>
    @php
        $colorClasses = [
            'emerald' => ['card' => 'border-emerald-200/70 bg-emerald-50/70', 'icon' => 'bg-emerald-100 text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700'],
            'amber' => ['card' => 'border-amber-200/70 bg-amber-50/70', 'icon' => 'bg-amber-100 text-amber-700', 'badge' => 'bg-amber-100 text-amber-700'],
            'salmon' => ['card' => 'border-[#E27D60]/25 bg-[#E27D60]/10', 'icon' => 'bg-[#E27D60]/15 text-[#C75F46]', 'badge' => 'bg-[#E27D60]/15 text-[#C75F46]'],
            'blue' => ['card' => 'border-sky-200/70 bg-sky-50/70', 'icon' => 'bg-sky-100 text-sky-700', 'badge' => 'bg-sky-100 text-sky-700'],
            'green' => ['card' => 'border-[#8DA280]/35 bg-[#8DA280]/12', 'icon' => 'bg-[#8DA280]/18 text-[#5F7E55]', 'badge' => 'bg-[#8DA280]/18 text-[#5F7E55]'],
            'violet' => ['card' => 'border-violet-200/70 bg-violet-50/70', 'icon' => 'bg-violet-100 text-violet-700', 'badge' => 'bg-violet-100 text-violet-700'],
            'rose' => ['card' => 'border-rose-200/70 bg-rose-50/70', 'icon' => 'bg-rose-100 text-rose-700', 'badge' => 'bg-rose-100 text-rose-700'],
            'indigo' => ['card' => 'border-indigo-200/70 bg-indigo-50/70', 'icon' => 'bg-indigo-100 text-indigo-700', 'badge' => 'bg-indigo-100 text-indigo-700'],
        ];

        $priorityClasses = [
            'Alta' => 'bg-rose-100 text-rose-700 border-rose-200',
            'Media' => 'bg-amber-100 text-amber-700 border-amber-200',
            'Baja' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        ];

        $nivelColor = [
            'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'amber' => 'bg-amber-100 text-amber-700 border-amber-200',
            'rose' => 'bg-rose-100 text-rose-700 border-rose-200',
        ][$estadoSocial['nivel']['color']] ?? 'bg-[#F3ECE4] text-[#2F3E5C] border-[#C7B5A3]';

        $hasRedChart = array_sum($chartData['red']['data']) > 0;
        $hasVisitasChart = ($chartData['visitas']['available'] ?? false) && array_sum($chartData['visitas']['data'] ?? []) > 0;
        $hasFichaChart = ($chartData['ficha']['available'] ?? false) && array_sum($chartData['ficha']['data'] ?? []) > 0;
    @endphp

    <section class="min-h-[calc(100vh-7rem)] bg-[#F8F3ED]/45 px-3 py-4 text-[#2F3E5C] sm:px-4 lg:px-5">
        <div class="mx-auto max-w-[1480px] space-y-4">
            <div class="overflow-hidden rounded-2xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/75 shadow-[0_16px_42px_rgba(47,62,92,0.12)] backdrop-blur-xl">
                <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
                <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#F3ECE4]/85 text-[#E27D60] shadow-sm">
                            <i class="ph-bold ph-users-three text-2xl"></i>
                        </span>
                        <div class="min-w-0">
                            <span class="text-xs font-black uppercase text-[#E27D60]">Familia y Social</span>
                            <h1 class="mt-1 text-2xl font-black text-[#2F3E5C] sm:text-3xl">Resumen familiar y social</h1>
                            <p class="mt-1 max-w-3xl text-sm font-bold leading-relaxed text-[#2F3E5C]/70">
                                Panel de seguimiento de red de apoyo, visitas, ficha social y estado social de los adultos mayores.
                            </p>
                        </div>
                    </div>

                    @can('familiares.ver')
                        <div class="flex flex-wrap gap-2">
                            @if($rutasSubmodulos['red_apoyo'])
                                <a href="{{ $rutasSubmodulos['red_apoyo'] }}" class="inline-flex items-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/85 px-3 py-2 text-xs font-black text-[#2F3E5C] shadow-sm transition hover:-translate-y-0.5 hover:border-[#E27D60]/45">
                                    <i class="ph-bold ph-hand-heart text-base text-[#E27D60]"></i>
                                    Ver red de apoyo
                                </a>
                            @endif
                            @if($rutasSubmodulos['visitas'])
                                <a href="{{ $rutasSubmodulos['visitas'] }}" class="inline-flex items-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/85 px-3 py-2 text-xs font-black text-[#2F3E5C] shadow-sm transition hover:-translate-y-0.5 hover:border-[#E27D60]/45">
                                    <i class="ph-bold ph-calendar-check text-base text-[#8DA280]"></i>
                                    Ver visitas
                                </a>
                            @endif
                            @if($rutasSubmodulos['ficha_social'])
                                <a href="{{ $rutasSubmodulos['ficha_social'] }}" class="inline-flex items-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/85 px-3 py-2 text-xs font-black text-[#2F3E5C] shadow-sm transition hover:-translate-y-0.5 hover:border-[#E27D60]/45">
                                    <i class="ph-bold ph-clipboard-text text-base text-[#2F3E5C]"></i>
                                    Ver ficha social
                                </a>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($metricas as $metrica)
                    @php($classes = $colorClasses[$metrica['color']] ?? $colorClasses['salmon'])
                    <article class="min-h-[118px] rounded-2xl border {{ $classes['card'] }} p-4 shadow-sm backdrop-blur-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $classes['icon'] }}">
                                <i class="ph-bold {{ $metrica['icono'] }} text-xl"></i>
                            </span>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $classes['badge'] }}">{{ $metrica['badge'] }}</span>
                        </div>
                        <div class="mt-3">
                            <p class="text-2xl font-black leading-none text-[#2F3E5C]">{{ number_format($metrica['valor']) }}</p>
                            <h2 class="mt-1 text-sm font-black text-[#2F3E5C]">{{ $metrica['label'] }}</h2>
                            <p class="mt-1 text-xs font-bold leading-snug text-[#2F3E5C]/62">{{ $metrica['subtitulo'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="grid gap-4 lg:grid-cols-[0.95fr_1.05fr]">
                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Estado social general</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Lectura rapida del acompanamiento familiar y social.</p>
                        </div>
                        <span class="rounded-full border px-3 py-1 text-xs font-black {{ $nivelColor }}">
                            {{ $estadoSocial['nivel']['texto'] }}
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @foreach([
                            ['label' => 'Red de apoyo registrada', 'value' => $estadoSocial['red_apoyo'], 'color' => 'bg-[#8DA280]'],
                            ['label' => 'Adultos sin red de apoyo', 'value' => $estadoSocial['sin_red'], 'color' => 'bg-[#D9A05B]'],
                            ['label' => 'Ficha social completada', 'value' => $estadoSocial['ficha_social'], 'color' => 'bg-[#E27D60]'],
                            ['label' => 'Visitas recientes', 'value' => $estadoSocial['visitas_recientes'], 'color' => 'bg-[#2F3E5C]'],
                        ] as $item)
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-xs font-black text-[#2F3E5C]">
                                    <span>{{ $item['label'] }}</span>
                                    <span>{{ $item['value'] }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-[#D5C7B9]/80">
                                    <div class="h-full rounded-full {{ $item['color'] }}" style="width: {{ $item['value'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-black text-[#2F3E5C]/65">Indice social consolidado</span>
                            <span class="text-lg font-black text-[#2F3E5C]">{{ $estadoSocial['nivel']['score'] }}%</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Cobertura de red de apoyo</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Adultos mayores con y sin vinculo familiar activo.</p>
                        </div>
                        <i class="ph-bold ph-chart-donut text-2xl text-[#E27D60]"></i>
                    </div>
                    @if($hasRedChart)
                        <div class="h-56">
                            <canvas id="familiaRedChart" class="max-h-56"></canvas>
                        </div>
                    @else
                        <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/45 text-center">
                            <div>
                                <i class="ph-bold ph-chart-pie-slice text-3xl text-[#2F3E5C]/25"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">No hay datos suficientes para generar este grafico.</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Visitas registradas</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Evolucion mensual de visitas familiares o sociales.</p>
                        </div>
                        <i class="ph-bold ph-chart-bar text-2xl text-[#8DA280]"></i>
                    </div>
                    @if($hasVisitasChart)
                        <div class="h-56">
                            <canvas id="familiaVisitasChart" class="max-h-56"></canvas>
                        </div>
                    @else
                        <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/45 text-center">
                            <div>
                                <i class="ph-bold ph-calendar-x text-3xl text-[#2F3E5C]/25"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">No existen datos suficientes para generar este grafico.</p>
                                <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">El panel queda preparado para el submodulo de visitas.</p>
                            </div>
                        </div>
                    @endif
                </section>

                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Estado de ficha social</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Fichas completas, pendientes y sin registro.</p>
                        </div>
                        <i class="ph-bold ph-chart-pie text-2xl text-[#E27D60]"></i>
                    </div>
                    @if($hasFichaChart)
                        <div class="h-56">
                            <canvas id="familiaFichaChart" class="max-h-56"></canvas>
                        </div>
                    @else
                        <div class="flex h-56 items-center justify-center rounded-xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/45 text-center">
                            <div>
                                <i class="ph-bold ph-clipboard-text text-3xl text-[#2F3E5C]/25"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">La ficha social aun no tiene registros disponibles.</p>
                                <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">No se creo ninguna migracion ni dato temporal.</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Alertas sociales</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Casos que requieren atencion administrativa o social.</p>
                        </div>
                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-black text-rose-700">{{ $alertas->count() }} alertas</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($alertas as $alerta)
                            <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#2F3E5C]">{{ $alerta['adulto'] }}</p>
                                        <p class="mt-0.5 text-xs font-bold leading-snug text-[#2F3E5C]/62">{{ $alerta['motivo'] }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="rounded-full border px-2.5 py-1 text-[11px] font-black {{ $priorityClasses[$alerta['prioridad']] ?? $priorityClasses['Media'] }}">{{ $alerta['prioridad'] }}</span>
                                        <span class="text-[11px] font-bold text-[#2F3E5C]/48">{{ $alerta['fecha'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/45 p-6 text-center">
                                <i class="ph-bold ph-check-circle text-3xl text-emerald-600/45"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">No existen alertas sociales pendientes.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Visitas recientes</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Ultimos registros de acompanamiento familiar o social.</p>
                        </div>
                        <i class="ph-bold ph-door-open text-2xl text-[#8DA280]"></i>
                    </div>

                    <div class="space-y-2">
                        @forelse($visitasRecientes as $visita)
                            <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#2F3E5C]">{{ $visita['adulto'] }}</p>
                                        <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]/62">{{ $visita['visitante'] }} · {{ $visita['motivo'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-left sm:text-right">
                                        <p class="text-xs font-black text-[#2F3E5C]">{{ $visita['fecha'] }}</p>
                                        <p class="text-[11px] font-bold text-[#2F3E5C]/50">{{ $visita['hora'] ?? 'Sin hora' }} · {{ $visita['estado'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/45 p-6 text-center">
                                <i class="ph-bold ph-calendar-x text-3xl text-[#2F3E5C]/25"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">No hay visitas registradas recientemente.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.05fr_0.95fr]">
                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Red de apoyo por completar</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Adultos mayores con vinculos, responsables o contactos pendientes.</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-black text-amber-700">{{ $redIncompleta->count() }} casos</span>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-[#C7B5A3]/55">
                        @forelse($redIncompleta as $item)
                            <div class="flex flex-col gap-3 border-b border-[#C7B5A3]/45 bg-white/30 p-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-black text-[#2F3E5C]">{{ $item['adulto'] }}</p>
                                    <p class="mt-0.5 text-xs font-bold text-[#2F3E5C]/62">{{ $item['faltante'] }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-full bg-[#E6DDD3] px-2.5 py-1 text-[11px] font-black text-[#2F3E5C]/70">{{ $item['estado'] }}</span>
                                    @can('adultos.ver')
                                        @if($item['url'])
                                            <a href="{{ $item['url'] }}" class="rounded-lg bg-[#2F3E5C] px-3 py-1.5 text-xs font-black text-white transition hover:bg-[#E27D60]">
                                                Revisar red
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <div class="bg-[#E6DDD3]/45 p-6 text-center">
                                <i class="ph-bold ph-check-circle text-3xl text-emerald-600/45"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">No se encontraron adultos mayores con red de apoyo incompleta.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Ficha social</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Estado de fichas y ultimas actualizaciones sociales.</p>
                        </div>
                        <i class="ph-bold ph-clipboard-text text-2xl text-[#E27D60]"></i>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                            <p class="text-lg font-black text-[#2F3E5C]">{{ $fichaSocial['completas'] }}</p>
                            <p class="text-[11px] font-black text-[#2F3E5C]/58">Completas</p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                            <p class="text-lg font-black text-[#2F3E5C]">{{ $fichaSocial['pendientes'] }}</p>
                            <p class="text-[11px] font-black text-[#2F3E5C]/58">Pendientes</p>
                        </div>
                        <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                            <p class="text-lg font-black text-[#2F3E5C]">{{ $fichaSocial['sin_registro'] }}</p>
                            <p class="text-[11px] font-black text-[#2F3E5C]/58">Sin registro</p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-2">
                        @forelse($fichaSocial['ultimas'] as $ficha)
                            <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-black text-[#2F3E5C]">{{ $ficha['adulto'] }}</p>
                                        <p class="mt-0.5 line-clamp-2 text-xs font-bold text-[#2F3E5C]/62">{{ $ficha['observacion'] }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="text-xs font-black text-[#2F3E5C]">{{ $ficha['estado'] }}</p>
                                        <p class="text-[11px] font-bold text-[#2F3E5C]/50">{{ $ficha['fecha'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/45 p-5 text-center">
                                <i class="ph-bold ph-folder-simple-dashed text-3xl text-[#2F3E5C]/25"></i>
                                <p class="mt-2 text-sm font-black text-[#2F3E5C]">No existen fichas sociales registradas.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1fr_0.9fr]">
                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-base font-black text-[#2F3E5C]">Reportes sociales</h2>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Reportes disponibles o preparados para evidencia institucional.</p>
                        </div>
                        <i class="ph-bold ph-file-chart text-2xl text-[#2F3E5C]"></i>
                    </div>

                    <div class="grid gap-2 md:grid-cols-2">
                        @foreach($reportesSociales as $reporte)
                            <div class="rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#E6DDD3] text-[#E27D60]">
                                        <i class="ph-bold {{ $reporte['icono'] }} text-lg"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="text-sm font-black text-[#2F3E5C]">{{ $reporte['titulo'] }}</h3>
                                            <span class="shrink-0 rounded-full bg-[#E6DDD3] px-2 py-0.5 text-[10px] font-black text-[#2F3E5C]/65">{{ $reporte['estado'] }}</span>
                                        </div>
                                        <p class="mt-1 text-xs font-bold leading-snug text-[#2F3E5C]/60">{{ $reporte['descripcion'] }}</p>
                                        @can($reporte['permiso'])
                                            @if($reporte['url'])
                                                <a href="{{ $reporte['url'] }}" class="mt-2 inline-flex items-center gap-1 text-xs font-black text-[#E27D60] transition hover:text-[#2F3E5C]">
                                                    Ver reporte
                                                    <i class="ph-bold ph-arrow-right"></i>
                                                </a>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-[#C7B5A3]/70 bg-[#F3ECE4]/80 p-4 shadow-sm backdrop-blur-xl">
                    <div class="mb-3">
                        <h2 class="text-base font-black text-[#2F3E5C]">Accesos a submodulos</h2>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">Continuidad operativa del modulo Familia y Social.</p>
                    </div>

                    <div class="space-y-2">
                        @foreach([
                            ['titulo' => 'Red de apoyo', 'descripcion' => 'Consulta y vinculacion de familiares/personas de apoyo.', 'url' => $rutasSubmodulos['red_apoyo'], 'icono' => 'ph-hand-heart'],
                            ['titulo' => 'Visitas', 'descripcion' => 'Registro y seguimiento de visitas familiares/sociales.', 'url' => $rutasSubmodulos['visitas'], 'icono' => 'ph-calendar-check'],
                            ['titulo' => 'Ficha social', 'descripcion' => 'Informacion social, familiar y de contexto del adulto mayor.', 'url' => $rutasSubmodulos['ficha_social'], 'icono' => 'ph-clipboard-text'],
                        ] as $acceso)
                            <a href="{{ $acceso['url'] ?? '#' }}" class="flex items-center gap-3 rounded-xl border border-[#C7B5A3]/55 bg-white/35 p-3 transition hover:-translate-y-0.5 hover:border-[#E27D60]/45 hover:bg-white/55">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E6DDD3] text-[#E27D60]">
                                    <i class="ph-bold {{ $acceso['icono'] }} text-xl"></i>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-black text-[#2F3E5C]">{{ $acceso['titulo'] }}</span>
                                    <span class="block text-xs font-bold leading-snug text-[#2F3E5C]/60">{{ $acceso['descripcion'] }}</span>
                                </span>
                                <i class="ph-bold ph-caret-right shrink-0 text-[#2F3E5C]/45"></i>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
    </section>

    <script>
        (() => {
            const payload = @json($chartData);
            const chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            usePointStyle: true,
                            font: { size: 11, weight: '700' },
                            color: '#2F3E5C',
                        },
                    },
                    datalabels: { display: false },
                },
            };

            const destroyPrevious = (canvas) => {
                if (canvas && canvas.__familiaChart) {
                    canvas.__familiaChart.destroy();
                    canvas.__familiaChart = null;
                }
            };

            const makeDoughnut = (id, data, colors) => {
                const canvas = document.getElementById(id);
                if (!canvas || !window.Chart || !data?.data?.some((value) => Number(value) > 0)) return;

                destroyPrevious(canvas);
                canvas.__familiaChart = new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: colors,
                            borderColor: '#F3ECE4',
                            borderWidth: 3,
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        cutout: '64%',
                    },
                });
            };

            const makeBar = (id, data) => {
                const canvas = document.getElementById(id);
                if (!canvas || !window.Chart || !data?.data?.some((value) => Number(value) > 0)) return;

                destroyPrevious(canvas);
                canvas.__familiaChart = new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            label: 'Visitas',
                            backgroundColor: '#8DA280',
                            borderColor: '#6F8A64',
                            borderRadius: 8,
                            maxBarThickness: 34,
                        }],
                    },
                    options: {
                        ...chartDefaults,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: '#2F3E5C', font: { size: 11, weight: '700' } },
                                grid: { color: 'rgba(47, 62, 92, 0.08)' },
                            },
                            x: {
                                ticks: { color: '#2F3E5C', font: { size: 11, weight: '700' } },
                                grid: { display: false },
                            },
                        },
                    },
                });
            };

            const renderCharts = () => {
                makeDoughnut('familiaRedChart', payload.red, ['#8DA280', '#D9A05B']);
                makeBar('familiaVisitasChart', payload.visitas);
                makeDoughnut('familiaFichaChart', payload.ficha, ['#8DA280', '#D9A05B', '#E27D60']);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', renderCharts, { once: true });
            } else {
                renderCharts();
            }
        })();
    </script>
</x-sistema-layout>
