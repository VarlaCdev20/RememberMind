<div class="space-y-6" x-data>
    <style>
        .salud-hidden-livewire-modals > div > div.rounded-\[24px\] {
            display: none !important;
        }
    </style>

    <section class="overflow-hidden rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/78 shadow-sm backdrop-blur-xl">
        <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A05B] to-[#8DA280]"></div>
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl border border-[#C7B5A3]/55 bg-[#E6DDD3]/70 text-[#E27D60] shadow-sm">
                    <i class="ph-bold ph-prescription text-2xl"></i>
                </span>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#E27D60]">Control diario de tomas</span>
                    <h2 class="text-xl font-black tracking-tight text-[#2F3E5C]">Administración de medicación</h2>
                    <p class="mt-1 text-xs font-bold leading-relaxed text-[#2F3E5C]/62">
                        Registro de administraciones, omisiones, responsables, fechas y observaciones del tratamiento.
                    </p>
                </div>
            </div>

            @can('salud.medicacion.crear')
                <button type="button" @click="$dispatch('abrirModalMedicacion', { cod_am: '{{ $adulto->cod_am }}' })" class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#C7B5A3]/70 bg-[#D5C7B9]/65 px-5 py-2.5 text-xs font-black uppercase tracking-wider text-[#2F3E5C] shadow-sm transition hover:bg-[#C7B5A3]/75 active:scale-95">
                    <i class="ph-bold ph-pill text-sm"></i>
                    Nueva medicación
                </button>
            @endcan
        </div>
    </section>

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach([
            ['label' => 'Administradas hoy', 'valor' => $stats['administradas_hoy'] ?? 0, 'icon' => 'ph-check-circle', 'color' => 'text-[#63775B]', 'bg' => 'bg-[#8DA280]/14', 'border' => 'border-[#8DA280]/30'],
            ['label' => 'Omitidas hoy', 'valor' => $stats['omitidas_hoy'] ?? 0, 'icon' => 'ph-minus-circle', 'color' => 'text-[#E27D60]', 'bg' => 'bg-[#E27D60]/10', 'border' => 'border-[#E27D60]/25'],
            ['label' => 'Total hoy', 'valor' => $stats['total_hoy'] ?? 0, 'icon' => 'ph-calendar-check', 'color' => 'text-[#2F3E5C]', 'bg' => 'bg-[#E6DDD3]/62', 'border' => 'border-[#C7B5A3]/45'],
            ['label' => 'Pendientes', 'valor' => $stats['pendientes'] ?? 0, 'icon' => 'ph-clock-countdown', 'color' => 'text-[#9A7B60]', 'bg' => 'bg-[#D5C7B9]/55', 'border' => 'border-[#C7B5A3]/45'],
        ] as $item)
            <div class="relative overflow-hidden rounded-2xl border {{ $item['border'] }} {{ $item['bg'] }} p-4 shadow-sm backdrop-blur-md">
                <i class="ph-bold {{ $item['icon'] }} absolute right-3 top-3 text-2xl text-[#2F3E5C]/10"></i>
                <p class="pr-7 text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/48">{{ $item['label'] }}</p>
                <p class="mt-2 text-2xl font-black leading-none {{ $item['color'] }}">{{ $item['valor'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-4 shadow-sm backdrop-blur-xl sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Tomas programadas</h3>
                    <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Medicaciones activas disponibles para registrar administración.</p>
                </div>
                <i class="ph-bold ph-clock text-xl text-[#E27D60]"></i>
            </div>

            <div class="space-y-3">
                @forelse($medicacionesActivas as $med)
                    <article class="rounded-[1.35rem] border border-[#C7B5A3]/45 bg-[#E6DDD3]/55 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="truncate text-sm font-black text-[#2F3E5C]">{{ $med->nombre_medicamento }}</h4>
                                <p class="mt-1 text-[11px] font-bold text-[#2F3E5C]/60">{{ $med->dosis }} - {{ $med->frecuencia }} - {{ $med->via_administracion }}</p>
                                <p class="mt-1 text-[10px] font-black uppercase tracking-wider text-[#E27D60]">
                                    <i class="ph-bold ph-clock mr-1"></i>
                                    {{ $med->hora_programada ? \Carbon\Carbon::parse($med->hora_programada)->format('H:i') : 'Sin hora' }}
                                </p>
                            </div>
                            <span class="rounded-full border border-[#8DA280]/30 bg-[#8DA280]/18 px-2.5 py-1 text-[9px] font-black uppercase text-[#63775B]">Activa</span>
                        </div>

                        @can('salud.administracion.crear')
                            <button type="button" @click="$dispatch('abrirModalAdministracion', { cod_am: '{{ $adulto->cod_am }}', cod_med_adulto: {{ $med->cod_med_adulto }} })" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-[10px] font-black uppercase tracking-wider text-white shadow-[0_8px_18px_rgba(47,62,92,0.16)] transition hover:bg-[#5B5F97] active:scale-95">
                                <i class="ph-bold ph-check-square"></i>
                                Registrar toma u omisión
                            </button>
                        @endcan
                    </article>
                @empty
                    <div class="rounded-[1.4rem] border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/42 p-8 text-center shadow-inner">
                        <i class="ph-bold ph-pill text-4xl text-[#2F3E5C]/25"></i>
                        <h3 class="mt-3 text-base font-black text-[#2F3E5C]">Sin medicaciones activas</h3>
                        <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Registre una medicación antes de controlar administraciones.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-[1.6rem] border border-[#C7B5A3]/65 bg-[#F3ECE4]/72 p-4 shadow-sm backdrop-blur-xl sm:p-5">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-wider text-[#2F3E5C]">Historial de administraciones</h3>
                    <p class="mt-1 text-xs font-bold text-[#2F3E5C]/55">Secuencia reciente de tomas, omisiones y responsables.</p>
                </div>
                <span class="rounded-full bg-[#2F3E5C]/8 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/65">{{ $administraciones->count() }} registros</span>
            </div>

            <div class="space-y-3">
                @forelse($administraciones as $admin)
                    @php
                        $administrado = (bool) $admin->administrado;
                        $estadoTexto = $administrado ? 'Administrada' : 'Omitida';
                    @endphp
                    <article class="relative rounded-[1.35rem] border border-[#C7B5A3]/45 bg-[#E6DDD3]/55 p-4 shadow-sm">
                        <div class="absolute left-5 top-5 bottom-5 w-px bg-[#C7B5A3]/45"></div>
                        <div class="relative flex gap-4">
                            <span class="z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border {{ $administrado ? 'border-[#8DA280]/30 bg-[#8DA280]/18 text-[#63775B]' : 'border-[#E27D60]/30 bg-[#E27D60]/12 text-[#E27D60]' }}">
                                <i class="ph-bold {{ $administrado ? 'ph-check-circle' : 'ph-x-circle' }} text-xl"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h4 class="truncate text-sm font-black text-[#2F3E5C]">{{ $admin->medicacion?->nombre_medicamento ?? 'Medicación no disponible' }}</h4>
                                    <span class="rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $administrado ? 'bg-[#8DA280]/18 text-[#63775B]' : 'bg-[#E27D60]/12 text-[#E27D60]' }}">{{ $estadoTexto }}</span>
                                </div>
                                <div class="mt-2 grid gap-2 text-[11px] font-bold text-[#2F3E5C]/65 sm:grid-cols-2">
                                    <p><i class="ph-bold ph-calendar mr-1 text-[#E27D60]"></i>{{ $admin->fecha?->format('d/m/Y') ?? 'S/D' }}</p>
                                    <p><i class="ph-bold ph-clock mr-1 text-[#E27D60]"></i>Prog. {{ $admin->hora_programada ? \Carbon\Carbon::parse($admin->hora_programada)->format('H:i') : 'S/D' }} @if($admin->hora_real) / Real {{ \Carbon\Carbon::parse($admin->hora_real)->format('H:i') }} @endif</p>
                                    <p class="sm:col-span-2"><i class="ph-bold ph-user-circle mr-1 text-[#E27D60]"></i>{{ $admin->registrador?->name ?? 'Sistema' }}</p>
                                </div>
                                @if(!$administrado && $admin->motivo_omision)
                                    <p class="mt-3 rounded-xl border border-[#E27D60]/20 bg-[#E27D60]/8 px-3 py-2 text-[11px] font-bold leading-relaxed text-[#E27D60]">{{ $admin->motivo_omision }}</p>
                                @endif
                                @if($admin->observacion || $admin->efecto_observado)
                                    <p class="mt-3 rounded-xl border border-[#C7B5A3]/35 bg-[#F3ECE4]/58 px-3 py-2 text-[11px] font-semibold leading-relaxed text-[#2F3E5C]/70">
                                        {{ $admin->efecto_observado ?: $admin->observacion }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[1.4rem] border border-dashed border-[#C7B5A3]/70 bg-[#E6DDD3]/42 p-10 text-center shadow-inner">
                        <i class="ph-bold ph-clipboard-text text-4xl text-[#2F3E5C]/25"></i>
                        <h3 class="mt-3 text-base font-black text-[#2F3E5C]">Sin administraciones registradas</h3>
                        <p class="mx-auto mt-1 max-w-md text-xs font-bold text-[#2F3E5C]/55">Las tomas administradas u omitidas aparecerán aquí como historial institucional.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <div class="salud-hidden-livewire-modals">
        <livewire:admin.adultos-mayores.salud.medicacion-adulto-modal :cod_am="$adulto->cod_am" />
        <livewire:admin.adultos-mayores.salud.administracion-medicacion-modal :cod_am="$adulto->cod_am" />
    </div>
</div>
