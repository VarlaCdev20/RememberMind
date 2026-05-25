{{-- HEADER COMPACTO (Para pestañas secundarias) --}}
            <section x-cloak x-show="tab !== 'resumen'" class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_10px_25px_rgba(47,62,92,0.08)] backdrop-blur-xl transition-all duration-300">
                <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8EA17D]"></div>
                <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @if($fotoUrl)
                            <img src="{{ $fotoUrl }}" alt="Foto de {{ $nombreCompleto }}" class="h-14 w-14 rounded-[14px] border-2 border-[#F8F2EC] object-cover shadow-sm">
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-[14px] bg-gradient-to-br from-[#4E5D8A] to-[#6873A6] text-xl font-black text-white shadow-sm">
                                {{ $iniciales ?: 'AM' }}
                            </div>
                        @endif
                        <div>
                            <h2 class="text-lg font-black text-[#2F3E5C] leading-tight">{{ $nombreCompleto ?: 'Adulto Mayor' }}</h2>
                            <div class="flex flex-wrap gap-2 items-center mt-1">
                                <span class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/60 bg-white/50 px-2 py-0.5 rounded-md border border-[#D5C7B9]"># {{ $idAdulto }}</span>
                                <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-md {{ $estadoTexto === 'ACTIVO' ? 'bg-[#8EA17D]/18 text-[#617453]' : 'bg-[#A37C62]/18 text-[#7A5C49]' }}">{{ ucfirst(strtolower($estadoTexto)) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="tab = 'resumen'" class="rounded-xl border border-[#C7B5A3] bg-[#F2EBE3] px-5 py-2.5 text-xs font-black transition hover:bg-[#E7DDD2] active:scale-95">
                            Cerrar edición
                        </button>

                        <a href="{{ route('admin.adultos-mayores.reporte-individual', $idAdulto) }}" target="_blank" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-5 py-2.5 text-xs font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#1F2E4C] active:scale-95">
                            <i class="ph-bold ph-file-pdf"></i> Imprimir Expediente
                        </a>
                    </div>
                </div>
            </section>

            {{-- HEADER PRINCIPAL COMPLETO (Solo en Resumen) --}}
            <section x-show="tab === 'resumen'" class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_16px_40px_rgba(47,62,92,0.10)] backdrop-blur-xl transition-all duration-300">
    {{-- Barra superior decorativa --}}
    <div class="h-1.5 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8EA17D]"></div>

    <div class="p-5 sm:p-6 lg:p-7">
        <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)]">

            {{-- COLUMNA IZQUIERDA --}}
            <aside class="rounded-[22px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-2">
                    <span class="inline-flex items-center rounded-full bg-[#2F3E5C]/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#2F3E5C]">
                        Ficha {{ $idAdulto ?? 'N/D' }}
                    </span>

                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-wide
                        {{ $estadoTexto === 'ACTIVO'
                            ? 'bg-[#8EA17D]/18 text-[#617453]'
                            : 'bg-[#A37C62]/18 text-[#7A5C49]' }}">
                        {{ ucfirst(strtolower($estadoTexto)) }}
                    </span>
                </div>

                <div class="flex flex-col items-center text-center">
                    @if($fotoUrl)
                        <img
                            src="{{ $fotoUrl }}"
                            alt="Foto de {{ $nombreCompleto }}"
                            class="h-28 w-28 rounded-[22px] border-4 border-[#F8F2EC] object-cover shadow-[0_14px_28px_rgba(47,62,92,0.14)]"
                        >
                    @else
                        <div class="flex h-28 w-28 items-center justify-center rounded-[22px] bg-gradient-to-br from-[#4E5D8A] to-[#6873A6] text-4xl font-black text-white shadow-[0_14px_28px_rgba(47,62,92,0.18)]">
                            {{ $iniciales ?: 'AM' }}
                        </div>
                    @endif

                    <h1 class="mt-4 text-[1.45rem] font-black leading-tight text-[#2F3E5C]">
                        {{ $nombreCompleto ?: 'Adulto mayor sin nombre registrado' }}
                    </h1>

                    <p class="mt-1 text-sm font-semibold text-[#2F3E5C]/65">
                        Perfil institucional del residente
                    </p>
                </div>

                <div class="mt-5 space-y-2.5">
                    <div class="rounded-2xl bg-[#E7DDD2] px-3.5 py-3">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#2F3E5C]/45">CI</p>
                        <p class="mt-1 text-sm font-bold text-[#2F3E5C]">{{ $ci }}</p>
                    </div>

                    <div class="rounded-2xl bg-[#E7DDD2] px-3.5 py-3">
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.18em] text-[#2F3E5C]/45">Edad / Género</p>
                        <p class="mt-1 text-sm font-bold text-[#2F3E5C]">
                            {{ $edad ? $edad . ' años' : 'Edad no disponible' }} · {{ $genero }}
                        </p>
                    </div>
                </div>
            </aside>

            {{-- COLUMNA DERECHA --}}
            <div class="space-y-4">
                {{-- RESUMEN SUPERIOR --}}
                <div class="rounded-[22px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 sm:p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-[#E27D60]/14 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#D96F58]">
                                    Adulto mayor
                                </span>

                                <span class="rounded-full bg-[#8EA17D]/16 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#617453]">
                                    Seguimiento institucional
                                </span>
                            </div>

                            <div>
                                <h2 class="text-[1.75rem] font-black leading-tight text-[#2F3E5C]">
                                    Resumen general del residente
                                </h2>
                                <p class="mt-1 max-w-3xl text-sm font-semibold leading-6 text-[#2F3E5C]/62">
                                    Información administrativa y de control del adulto mayor, con acceso rápido a edición,
                                    observaciones, documentos y seguimiento integral.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-2 sm:grid-cols-2 lg:min-w-[310px]">
                            <a href="{{ route('admin.adultos-mayores.index') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[#CDBDAF] bg-[#E3D6C8] px-4 py-3 text-sm font-extrabold text-[#2F3E5C] transition duration-200 hover:-translate-y-0.5 hover:bg-[#D8C9B9] active:scale-[0.98]">
                                <i class="ph-bold ph-arrow-left"></i>
                                Volver al panel
                            </a>

                            @if($idAdulto)
                                <a href="{{ route('admin.adultos-mayores.edit', $idAdulto) }}"
                                   class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#E27D60] px-4 py-3 text-sm font-extrabold text-white shadow-[0_10px_20px_rgba(226,125,96,0.22)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#D96F58] active:scale-[0.98]">
                                    <i class="ph-bold ph-pencil-simple"></i>
                                    Editar ficha completa
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- GRID DE DATOS RESUMEN --}}
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm transition duration-200 hover:-translate-y-0.5">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#E27D60]/14 text-[#E27D60]">
                                <i class="ph-bold ph-identification-card text-lg"></i>
                            </div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#2F3E5C]/48">Identificación</p>
                        </div>
                        <p class="text-sm font-bold text-[#2F3E5C]">CI {{ $ci }}</p>
                        <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">Registro personal institucional</p>
                    </div>

                    <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm transition duration-200 hover:-translate-y-0.5">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#8EA17D]/16 text-[#617453]">
                                <i class="ph-bold ph-user-check text-lg"></i>
                            </div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#2F3E5C]/48">Estado</p>
                        </div>
                        <p class="text-sm font-bold text-[#2F3E5C]">{{ ucfirst(strtolower($estadoTexto)) }}</p>
                        <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">Situación institucional actual</p>
                    </div>

                    <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm transition duration-200 hover:-translate-y-0.5">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#D9A27C]/18 text-[#9B6D4C]">
                                <i class="ph-bold ph-calendar text-lg"></i>
                            </div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#2F3E5C]/48">Nacimiento</p>
                        </div>
                        <p class="text-sm font-bold text-[#2F3E5C]">{{ $fechaNacimientoFormateada }}</p>
                        <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">
                            {{ $edad ? $edad . ' años' : 'Edad no disponible' }}
                        </p>
                    </div>

                    <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm transition duration-200 hover:-translate-y-0.5">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-[#6873A6]/16 text-[#566189]">
                                <i class="ph-bold ph-buildings text-lg"></i>
                            </div>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#2F3E5C]/48">Ingreso</p>
                        </div>
                        <p class="text-sm font-bold text-[#2F3E5C]">{{ $fechaIngresoFormateada }}</p>
                        <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">Fecha de ingreso institucional</p>
                    </div>
                </div>

                {{-- BLOQUE EXTRA --}}
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#2F3E5C]/45">Contacto</p>
                        @if($tieneCelular && $celular !== 'No registrado')
                            <div class="mt-2 flex items-center gap-2">
                                <p class="text-sm font-bold text-[#2F3E5C]">{{ $celular }}</p>
                                @if($sabeUsarWhatsapp)
                                    <a href="https://wa.me/591{{ $celular }}" target="_blank" title="Contactar por WhatsApp"
                                       class="flex h-6 w-6 items-center justify-center rounded-full bg-[#25D366]/10 text-[#25D366] transition hover:bg-[#25D366] hover:text-white">
                                        <i class="ph-fill ph-whatsapp-logo text-sm"></i>
                                    </a>
                                @endif
                            </div>
                            <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">Celular del residente</p>
                        @else
                            <p class="mt-2 text-sm font-bold text-[#2F3E5C]">{{ $telefonoFijo !== 'No registrado' ? $telefonoFijo : 'Sin celular' }}</p>
                            <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">Teléfono fijo</p>
                        @endif
                    </div>

                    <div class="rounded-[20px] border border-[#D5C7B9] bg-[#F2EBE3]/85 p-4 shadow-sm">
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.16em] text-[#2F3E5C]/45">Dirección referencial</p>
                        <p class="mt-2 text-sm font-bold text-[#2F3E5C]">{{ $zona }}</p>
                        <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/55">{{ $calle }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

            {{-- INDICADORES --}}
            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Familiares</p>
                    <h3 class="mt-1 text-2xl font-black text-[#5F7357]">{{ $totalFamiliares }}</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/50">Vinculados</p>
                </div>

                <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Observaciones</p>
                    <h3 class="mt-1 text-2xl font-black text-[#5B5F97]">{{ $totalObservaciones }}</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/50">Seguimiento</p>
                </div>

                <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Atenciones</p>
                    <h3 class="mt-1 text-2xl font-black text-[#7A604B]">{{ $totalAtenciones }}</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/50">Historial</p>
                </div>

                <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Actividades</p>
                    <h3 class="mt-1 text-2xl font-black text-[#A86B3C]">{{ $totalActividades }}</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/50">Participación</p>
                </div>

                <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Documentos</p>
                    <h3 class="mt-1 text-2xl font-black text-[#2F3E5C]">{{ $totalDocumentos }}</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/50">Archivos</p>
                </div>

                <div class="rounded-2xl border border-[#C7B5A3] bg-[#E6DDD3]/90 p-4 shadow-sm transition hover:-translate-y-0.5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45">Asignaciones</p>
                    <h3 class="mt-1 text-2xl font-black text-[#E27D60]">{{ $totalAsignaciones }}</h3>
                    <p class="text-xs font-bold text-[#2F3E5C]/50">Responsables</p>
                </div>
            </section>

<section class="rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-3 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8">
        @php
            $tabs = [
                [
                    'key' => 'resumen',
                    'icon' => 'ph-user-circle',
                    'label' => 'Resumen',
                    'desc' => 'Panel general',
                    'color' => '#E27D60'
                ],
                [
                    'key' => 'datos',
                    'icon' => 'ph-identification-card',
                    'label' => 'Datos y Apoyo',
                    'desc' => 'Filiación y familia',
                    'color' => '#8EA17D'
                ],
                [
                    'key' => 'documentos',
                    'icon' => 'ph-folder-open',
                    'label' => 'Documentos',
                    'desc' => 'Expediente físico',
                    'color' => '#2F3E5C'
                ],
                [
                    'key' => 'salud',
                    'icon' => 'ph-heartbeat',
                    'label' => 'Salud y Seguimiento',
                    'desc' => 'Resumen médico clínico',
                    'color' => '#C45F4B'
                ],
                [
                    'key' => 'evaluaciones',
                    'icon' => 'ph-brain',
                    'label' => 'Cognitivas',
                    'desc' => 'MoCA, MMSE y reportes',
                    'color' => '#5B5F97'
                ],
                [
                    'key' => 'seguimiento',
                    'icon' => 'ph-users-three',
                    'label' => 'Actividades y Atención',
                    'desc' => 'Seguimiento institucional',
                    'color' => '#6873A6'
                ],
                [
                    'key' => 'historial',
                    'icon' => 'ph-clock-counter-clockwise',
                    'label' => 'Historial',
                    'desc' => 'Estados e institucional',
                    'color' => '#9A7B60'
                ],
                [
                    'key' => 'reportes',
                    'icon' => 'ph-chart-line-up',
                    'label' => 'Reportes',
                    'desc' => 'Evolución analítica',
                    'color' => '#5F7357'
                ],
            ];
        @endphp

        @foreach($tabs as $item)
            <button
                type="button"
                @click="tab = '{{ $item['key'] }}'"
                class="group relative overflow-hidden rounded-[18px] border px-3 py-3 text-left transition-all duration-200 active:scale-[0.98]"
                :class="tab === '{{ $item['key'] }}'
                    ? 'border-[#E27D60]/40 bg-[#F2EBE3] shadow-[0_10px_22px_rgba(47,62,92,0.10)] -translate-y-0.5'
                    : 'border-[#D5C7B9] bg-[#D5C7B9]/55 hover:bg-[#F2EBE3] hover:-translate-y-0.5'"
            >
                <div
                    class="absolute left-0 top-0 h-full w-1 transition-all duration-200"
                    :class="tab === '{{ $item['key'] }}' ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'"
                    style="background-color: {{ $item['color'] }};">
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl transition"
                        :class="tab === '{{ $item['key'] }}'
                            ? 'bg-[#E27D60]/14 text-[#E27D60]'
                            : 'bg-[#2F3E5C]/8 text-[#2F3E5C]/70 group-hover:bg-[#E27D60]/12 group-hover:text-[#E27D60]'"
                    >
                        <i class="ph-bold {{ $item['icon'] }} text-lg"></i>
                    </div>

                    <div class="min-w-0">
                        <p
                            class="truncate text-xs font-black"
                            :class="tab === '{{ $item['key'] }}' ? 'text-[#2F3E5C]' : 'text-[#2F3E5C]/70'"
                        >
                            {{ $item['label'] }}
                        </p>

                        <p class="truncate text-[10px] font-bold text-[#2F3E5C]/45">
                            {{ $item['desc'] }}
                        </p>
                    </div>
                </div>
            </button>
        @endforeach
    </div>
</section>

            