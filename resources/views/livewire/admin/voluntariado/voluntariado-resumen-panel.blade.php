<div class="min-h-screen bg-[#F8F3ED]/45 px-4 py-5 text-[#2F3E5C] sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-[1.65rem] border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 shadow-[0_20px_58px_rgba(47,62,92,0.13)] backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
            <div class="flex flex-col gap-4 p-5 sm:p-7 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[#E27D60]/25 bg-[#E27D60]/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">
                        <i class="ph-bold ph-hand-heart text-sm"></i>
                        CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
                    </span>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-[#2F3E5C] sm:text-4xl">
                        Voluntariado
                    </h1>
                    <p class="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-[#2F3E5C]/72">
                        Gestión de voluntarios, disponibilidad, asignaciones, asistencia y reportes institucionales.
                    </p>
                </div>

                <div class="flex items-center gap-3 rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/68 px-4 py-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C] text-white shadow-sm">
                        <i class="ph-bold ph-users-three text-xl"></i>
                    </span>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.16em] text-[#2F3E5C]/52">Equipo activo</p>
                        <p class="text-2xl font-black leading-none text-[#2F3E5C]">{{ number_format($stats['voluntarios_activos']) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $tonoClases = [
                    'azul' => ['icono' => 'bg-[#2F3E5C]/10 text-[#2F3E5C]', 'valor' => 'text-[#2F3E5C]', 'linea' => 'bg-[#2F3E5C]'],
                    'verde' => ['icono' => 'bg-[#8DA280]/18 text-[#63775B]', 'valor' => 'text-[#63775B]', 'linea' => 'bg-[#8DA280]'],
                    'terracota' => ['icono' => 'bg-[#E27D60]/12 text-[#E27D60]', 'valor' => 'text-[#E27D60]', 'linea' => 'bg-[#E27D60]'],
                    'dorado' => ['icono' => 'bg-[#D9A05B]/16 text-[#9A6B2E]', 'valor' => 'text-[#9A6B2E]', 'linea' => 'bg-[#D9A05B]'],
                    'neutro' => ['icono' => 'bg-[#D5C7B9]/60 text-[#7C7168]', 'valor' => 'text-[#7C7168]', 'linea' => 'bg-[#C7B5A3]'],
                ];
            @endphp

            @foreach($metricas as $metrica)
                @php($tono = $tonoClases[$metrica['tono']] ?? $tonoClases['azul'])
                <article class="relative min-h-[128px] overflow-hidden rounded-2xl border border-[#C7B5A3]/55 bg-[#F3ECE4]/78 p-4 shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-0.5 hover:border-[#E27D60]/35 hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)]">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $tono['linea'] }}"></div>
                    <div class="flex items-start justify-between gap-3">
                        <p class="max-w-[11rem] text-[10px] font-black uppercase leading-snug tracking-[0.15em] text-[#2F3E5C]/55">
                            {{ $metrica['label'] }}
                        </p>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tono['icono'] }}">
                            <i class="ph-bold {{ $metrica['icono'] }} text-xl"></i>
                        </span>
                    </div>
                    <p class="mt-5 text-3xl font-black leading-none {{ $tono['valor'] }}">
                        {{ number_format($metrica['valor']) }}
                    </p>
                </article>
            @endforeach
        </section>

        @if($sinDatos)
            <section class="rounded-[1.5rem] border border-dashed border-[#C7B5A3]/80 bg-[#E6DDD3]/45 p-8 text-center shadow-inner">
                <i class="ph-bold ph-hand-heart text-4xl text-[#2F3E5C]/25"></i>
                <h2 class="mt-3 text-base font-black text-[#2F3E5C]">Resumen sin registros operativos</h2>
                <p class="mx-auto mt-1 max-w-xl text-xs font-bold leading-relaxed text-[#2F3E5C]/58">
                    Cuando existan voluntarios, disponibilidades, asignaciones o asistencias, este panel consolidará los indicadores principales.
                </p>
            </section>
        @endif

        <section class="rounded-[1.5rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/75 p-5 shadow-sm backdrop-blur-xl">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.18em] text-[#E27D60]">Flujo operativo</span>
                    <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">Secuencia institucional de voluntariado</h2>
                </div>
                <i class="ph-bold ph-flow-arrow text-2xl text-[#E27D60]"></i>
            </div>

            <div class="grid gap-3 md:grid-cols-5">
                @foreach($flujoOperativo as $paso)
                    <div class="relative rounded-2xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/58 px-4 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#2F3E5C]/10 text-[#2F3E5C]">
                                <i class="ph-bold {{ $paso['icono'] }} text-lg"></i>
                            </span>
                            <p class="text-xs font-black leading-snug text-[#2F3E5C]">{{ $paso['label'] }}</p>
                        </div>

                        @if(! $loop->last)
                            <span class="absolute -right-2 top-1/2 z-10 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full border border-[#C7B5A3]/65 bg-[#F3ECE4] text-[#E27D60] md:flex">
                                <i class="ph-bold ph-caret-right text-xs"></i>
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="space-y-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-[#E27D60]">Submódulos</span>
                <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">Acceso organizado</h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach($submodulos ?? [] as $item)
                    <a href="{{ $item['url'] }}"
                       class="group min-h-[160px] rounded-2xl border p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_16px_34px_rgba(47,62,92,0.11)] {{ $item['activo'] ? 'border-[#E27D60]/70 bg-[#E27D60]/10' : 'border-[#C7B5A3]/55 bg-[#F3ECE4]/78 hover:border-[#E27D60]/35' }}"
                       title="{{ $item['label'] }}">
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $item['activo'] ? 'bg-[#E27D60] text-white' : 'bg-[#2F3E5C]/10 text-[#2F3E5C] group-hover:bg-[#E27D60]/12 group-hover:text-[#E27D60]' }}">
                                <i class="ph-bold {{ $item['icono'] }} text-xl"></i>
                            </span>
                            <span class="rounded-full bg-[#D5C7B9]/70 px-2.5 py-1 text-xs font-black uppercase tracking-wide text-[#2F3E5C]/62">
                                {{ $item['dato'] }}
                            </span>
                        </div>
                        <h3 class="mt-4 text-sm font-black text-[#2F3E5C]">{{ $item['label'] }}</h3>
                        <p class="mt-2 text-xs font-bold leading-relaxed text-[#2F3E5C]/62">{{ $item['descripcion'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-5 lg:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-[1.5rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/75 p-5 shadow-sm backdrop-blur-xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-[#E27D60]">Agenda</span>
                        <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">Próximas asignaciones</h2>
                    </div>
                    <i class="ph-bold ph-calendar-check text-2xl text-[#63775B]"></i>
                </div>

                <div class="space-y-3">
                    @forelse($proximasAsignaciones as $asignacion)
                        <div class="flex flex-col gap-3 rounded-2xl border border-[#C7B5A3]/40 bg-[#E6DDD3]/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-[#2F3E5C]/10 px-2.5 py-1 text-xs font-black uppercase tracking-wide text-[#2F3E5C]">
                                        {{ $asignacion['relativa'] }} · {{ $asignacion['fecha'] }}
                                    </span>
                                    <span class="rounded-full bg-[#8DA280]/18 px-2.5 py-1 text-xs font-black uppercase tracking-wide text-[#63775B]">
                                        {{ $asignacion['estado'] }}
                                    </span>
                                </div>
                                <p class="mt-2 truncate text-sm font-black text-[#2F3E5C]">{{ $asignacion['voluntario'] }}</p>
                                <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/60">
                                    {{ $asignacion['adulto'] }} · {{ $asignacion['area'] }}
                                </p>
                            </div>
                            <span class="shrink-0 rounded-xl border border-[#C7B5A3]/45 bg-[#F3ECE4]/70 px-3 py-2 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/55">
                                {{ $asignacion['codigo'] }}
                            </span>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/38 p-8 text-center">
                            <i class="ph-bold ph-calendar-blank text-4xl text-[#2F3E5C]/25"></i>
                            <h3 class="mt-3 text-sm font-black text-[#2F3E5C]">Sin próximas asignaciones</h3>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">No hay asignaciones vigentes o programadas para mostrar.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-[1.5rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/75 p-5 shadow-sm backdrop-blur-xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-[0.18em] text-[#E27D60]">Seguimiento</span>
                        <h2 class="mt-1 text-lg font-black text-[#2F3E5C]">Alertas operativas</h2>
                    </div>
                    <i class="ph-bold ph-warning-circle text-2xl text-[#E27D60]"></i>
                </div>

                <div class="space-y-3">
                    @forelse($alertasOperativas as $alerta)
                        <div class="rounded-2xl border border-[#C7B5A3]/40 bg-[#E6DDD3]/50 p-4">
                            <div class="flex gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $alerta['tono'] === 'terracota' ? 'bg-[#E27D60]/12 text-[#E27D60]' : ($alerta['tono'] === 'dorado' ? 'bg-[#D9A05B]/16 text-[#9A6B2E]' : 'bg-[#2F3E5C]/10 text-[#2F3E5C]') }}">
                                    <i class="ph-bold {{ $alerta['icono'] }} text-lg"></i>
                                </span>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-black text-[#2F3E5C]">{{ $alerta['titulo'] }}</h3>
                                    <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/62">{{ $alerta['detalle'] }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-[#8DA280]/35 bg-[#8DA280]/12 p-8 text-center">
                            <i class="ph-bold ph-check-circle text-4xl text-[#63775B]"></i>
                            <h3 class="mt-3 text-sm font-black text-[#2F3E5C]">Sin alertas operativas</h3>
                            <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Los indicadores del resumen no requieren seguimiento inmediato.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</div>
