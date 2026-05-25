<div class="space-y-6">
    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">Monitoreo preventivo</span>
                <h2 class="mt-1 text-xl font-black tracking-tight text-[#2F3E5C]">Alertas de salud</h2>
                <p class="mt-1 max-w-2xl text-xs font-bold leading-relaxed text-[#2F3E5C]/62">
                    Priorización cálida de registros pendientes, controles fuera de rango y seguimiento funcional.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-2 sm:min-w-[360px]">
                <div class="rounded-2xl border border-[#C7B5A3]/45 bg-[#E6DDD3]/62 p-3 text-center">
                    <p class="text-[8px] font-black uppercase tracking-wider text-[#2F3E5C]/45">Total</p>
                    <p class="mt-1 text-xl font-black text-[#2F3E5C]">{{ $conteos['total'] ?? count($alertas) }}</p>
                </div>
                <div class="rounded-2xl border border-[#E27D60]/25 bg-[#E27D60]/10 p-3 text-center">
                    <p class="text-[8px] font-black uppercase tracking-wider text-[#2F3E5C]/45">Críticas</p>
                    <p class="mt-1 text-xl font-black text-[#E27D60]">{{ $conteos['criticas'] ?? 0 }}</p>
                </div>
                <div class="rounded-2xl border border-[#D9A05B]/30 bg-[#D9A05B]/12 p-3 text-center">
                    <p class="text-[8px] font-black uppercase tracking-wider text-[#2F3E5C]/45">Preventivas</p>
                    <p class="mt-1 text-xl font-black text-[#9A6A2F]">{{ $conteos['preventivas'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-4 shadow-sm backdrop-blur-xl sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Filtro clínico</h3>
                <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Seleccione un tipo de alerta para depurar el seguimiento.</p>
            </div>
            <label class="block w-full sm:max-w-xs">
                <span class="mb-1.5 block text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/55">Tipo de alerta</span>
                <div class="relative">
                    <i class="ph-bold ph-funnel absolute left-3.5 top-1/2 -translate-y-1/2 text-[#2F3E5C]/40"></i>
                    <select wire:model.live="filtroTipo" class="w-full rounded-xl border border-[#C7B5A3]/70 bg-[#E6DDD3]/70 py-2.5 pl-10 pr-4 text-xs font-bold text-[#2F3E5C] outline-none transition focus:border-[#E27D60] focus:ring-2 focus:ring-[#E27D60]/15">
                        <option value="">Todos los tipos</option>
                        <option value="Ficha MÃ©dica">Ficha médica</option>
                        <option value="MedicaciÃ³n">Medicación</option>
                        <option value="Signos Vitales">Signos vitales</option>
                        <option value="ValoraciÃ³n">Valoración funcional</option>
                    </select>
                </div>
            </label>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($alertas as $alerta)
            @php
                $esCritica = $alerta['nivel'] === 'critica';
                $tipo = $alerta['tipo'];
                $icono = match($tipo) {
                    'Ficha MÃ©dica', 'Ficha Médica' => 'ph-file-dashed',
                    'MedicaciÃ³n', 'Medicación' => 'ph-pill',
                    'Signos Vitales' => 'ph-activity',
                    'ValoraciÃ³n', 'Valoración' => 'ph-person-simple-walk',
                    default => 'ph-warning-circle',
                };
                $panel = $esCritica
                    ? ['border' => 'border-[#E27D60]/35', 'bg' => 'bg-[#E27D60]/8', 'text' => 'text-[#E27D60]', 'badge' => 'Crítica', 'iconBg' => 'bg-[#E27D60]/12']
                    : ['border' => 'border-[#D9A05B]/35', 'bg' => 'bg-[#D9A05B]/10', 'text' => 'text-[#9A6A2F]', 'badge' => 'Preventiva', 'iconBg' => 'bg-[#D9A05B]/14'];
            @endphp

            <article class="group overflow-hidden rounded-[1.55rem] border {{ $panel['border'] }} {{ $panel['bg'] }} shadow-sm backdrop-blur-xl transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_38px_rgba(47,62,92,0.14)]">
                <div class="flex items-start gap-4 border-b border-[#C7B5A3]/30 bg-[#F3ECE4]/62 p-5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $panel['iconBg'] }} {{ $panel['text'] }}">
                        <i class="ph-bold {{ $icono }} text-2xl"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#2F3E5C]/8 px-2.5 py-1 text-[8px] font-black uppercase tracking-wider text-[#2F3E5C]/60">{{ $tipo }}</span>
                            <span class="rounded-full px-2.5 py-1 text-[8px] font-black uppercase tracking-wider {{ $panel['iconBg'] }} {{ $panel['text'] }}">{{ $panel['badge'] }}</span>
                        </div>
                        <h3 class="mt-2 truncate text-base font-black text-[#2F3E5C]">
                            {{ $alerta['adulto']->nombres }} {{ $alerta['adulto']->ap_paterno }}
                        </h3>
                        <p class="mt-0.5 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/42">{{ $alerta['adulto']->cod_am }}</p>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <p class="text-sm font-bold leading-relaxed text-[#2F3E5C]/78">{{ $alerta['mensaje'] }}</p>
                    <div class="rounded-2xl border border-[#C7B5A3]/35 bg-[#F3ECE4]/58 px-4 py-3">
                        <p class="text-[9px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Acción sugerida</p>
                        <p class="mt-1 text-xs font-black uppercase text-[#2F3E5C]">{{ $alerta['accion'] }}</p>
                    </div>
                    <a href="{{ $alerta['ruta'] }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-white shadow-[0_8px_18px_rgba(47,62,92,0.18)] transition hover:bg-[#5B5F97] active:scale-95">
                        <i class="ph-bold ph-arrow-right"></i>
                        Atender alerta
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-[1.6rem] border border-[#8DA280]/30 bg-[#8DA280]/12 p-12 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-[#8DA280]/30 bg-[#8DA280]/18 text-[#63775B]">
                    <i class="ph-bold ph-check-circle text-4xl"></i>
                </div>
                <h3 class="mt-4 text-lg font-black text-[#2F3E5C]">Sin alertas activas</h3>
                <p class="mx-auto mt-2 max-w-md text-sm font-semibold leading-relaxed text-[#2F3E5C]/60">
                    Los expedientes revisados se encuentran al día según los criterios actuales.
                </p>
            </div>
        @endforelse
    </section>
</div>
