<div class="relative mx-auto max-w-7xl space-y-6 py-6 px-4 sm:px-6 lg:px-8">
    {{-- ENCABEZADO --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 shadow-[0_12px_28px_rgba(47,62,92,0.08)] backdrop-blur-xl">
        <div class="h-1 w-full bg-gradient-to-r from-[#E27D60] via-[#D9A27C] to-[#8DA280]"></div>
        <div class="p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="text-[11px] font-black uppercase tracking-[0.2em] text-[#9A7B60]">
                        CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Monitoreo Activo
                    </span>
                    <h1 class="mt-1 text-2xl font-black text-[#2F3E5C]">Alertas y Pendientes</h1>
                    <p class="mt-1 text-xs font-bold text-[#2F3E5C]/60">
                        Seguimiento institucional de registros incompletos, alertas activas y acciones pendientes del módulo Adultos Mayores.
                    </p>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('admin.adultos-mayores.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2F3E5C] px-4 py-2.5 text-xs font-black text-white shadow-md transition hover:bg-[#1F2E4C] active:scale-95">
                        <i class="ph-bold ph-arrow-left text-sm"></i> Volver al Centro
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- TARJETAS DE INDICADORES REALES --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-7">
        {{-- Total --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Total Alertas</p>
            <p class="mt-1.5 text-2xl font-black text-[#E27D60]">{{ $conteos['total'] }}</p>
        </div>
        {{-- Red de Apoyo --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Red de Apoyo</p>
            <p class="mt-1.5 text-2xl font-black text-[#2F3E5C]">{{ $conteos['red_de_apoyo'] }}</p>
        </div>
        {{-- Documentos --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Documentos</p>
            <p class="mt-1.5 text-2xl font-black text-[#2F3E5C]">{{ $conteos['documentacion'] }}</p>
        </div>
        {{-- Salud --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Salud y Cuidados</p>
            <p class="mt-1.5 text-2xl font-black text-[#2F3E5C]">{{ $conteos['salud_y_cuidados'] }}</p>
        </div>
        {{-- Evaluaciones --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Evaluaciones</p>
            <p class="mt-1.5 text-2xl font-black text-[#5B5F97]">{{ $conteos['evaluaciones'] }}</p>
        </div>
        {{-- Seguimiento --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Seguimiento</p>
            <p class="mt-1.5 text-2xl font-black text-[#8DA280]">{{ $conteos['seguimiento'] }}</p>
        </div>
        {{-- Estado --}}
        <div class="rounded-[18px] border border-[#D5C7B9] bg-[#F2EBE3]/80 p-4 shadow-xs">
            <p class="text-[9px] font-black uppercase tracking-[0.15em] text-[#2F3E5C]/45">Institucional</p>
            <p class="mt-1.5 text-2xl font-black text-slate-500">{{ $conteos['estado_institucional'] }}</p>
        </div>
    </div>

    {{-- BARRA DE FILTROS Y BÚSQUEDA --}}
    <section class="overflow-hidden rounded-[24px] border border-[#CBBBAA] bg-[#E7DDD2]/95 p-4 shadow-[0_4px_14px_rgba(47,62,92,0.04)]">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            {{-- Filtros Rápidos (Categorías) --}}
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[10px] font-black uppercase tracking-widest text-[#2F3E5C]/45 mr-1">Filtrar:</span>
                
                @php
                    $filtros = [
                        ['valor' => 'todas', 'label' => 'Todas'],
                        ['valor' => 'red_de_apoyo', 'label' => 'Red de Apoyo'],
                        ['valor' => 'documentacion', 'label' => 'Documentos'],
                        ['valor' => 'salud_y_cuidados', 'label' => 'Salud y Cuidados'],
                        ['valor' => 'evaluaciones', 'label' => 'Evaluaciones'],
                        ['valor' => 'seguimiento', 'label' => 'Seguimiento'],
                        ['valor' => 'estado_institucional', 'label' => 'Institucional'],
                    ];
                @endphp

                @foreach($filtros as $f)
                    <button
                        type="button"
                        wire:click="$set('filtroCategoria', '{{ $f['valor'] }}')"
                        class="rounded-lg px-3 py-1.5 text-[10px] font-black transition active:scale-95 {{ $filtroCategoria === $f['valor'] ? 'bg-[#2F3E5C] text-white shadow-xs' : 'bg-[#D5C7B9]/60 text-[#2F3E5C]/75 hover:bg-[#C7B5A3]' }}"
                    >
                        {{ $f['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Buscador reactivo --}}
            <div class="flex items-center gap-2 rounded-xl border border-[#C7B5A3] bg-white/45 px-3 py-1.5 shrink-0">
                <i class="ph-bold ph-magnifying-glass text-xs text-[#2F3E5C]/45"></i>
                <input
                    type="text"
                    wire:model.live="buscar"
                    placeholder="Buscar adulto mayor..."
                    class="bg-transparent text-xs font-bold text-[#2F3E5C] outline-none placeholder:text-[#2F3E5C]/35 w-full md:w-48"
                >
            </div>
        </div>
    </section>

    {{-- LISTADO DE ALERTAS --}}
    <main class="space-y-4">
        @if($alertas->isEmpty())
            <div class="rounded-2xl border border-[#CBBBAA] bg-[#E7DDD2]/95 p-16 text-center shadow-xs flex flex-col items-center justify-center min-h-[300px]">
                <div class="h-16 w-16 rounded-full bg-[#8DA280]/10 text-[#8DA280] border border-[#8DA280]/30 shadow-inner flex items-center justify-center mb-4">
                    <i class="ph-bold ph-check text-2xl"></i>
                </div>
                <h3 class="text-base font-black text-[#2F3E5C]">Sin alertas pendientes</h3>
                <p class="mt-1 text-xs font-semibold text-[#2F3E5C]/60 max-w-md mx-auto">
                    Los registros principales se encuentran completos según los criterios actuales.
                </p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach($alertas as $alerta)
                    <article class="rounded-2xl border {{ $alerta['color_border'] }} {{ $alerta['color_bg'] }} p-5 shadow-xs transition hover:shadow-sm flex flex-col justify-between">
                        <div class="space-y-3">
                            {{-- Fila superior: ID y Nivel --}}
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[10px] font-black uppercase tracking-wider text-[#2F3E5C]/50">
                                    {{ $alerta['adulto_id'] }}
                                </span>
                                <span class="rounded-full px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider {{ $alerta['nivel'] === 'prioritaria' ? 'bg-red-200/50 text-red-800' : ($alerta['nivel'] === 'preventiva' ? 'bg-amber-200/50 text-amber-800' : 'bg-slate-200 text-slate-800') }}">
                                    {{ $alerta['nivel'] }}
                                </span>
                            </div>

                            {{-- Nombre del adulto mayor --}}
                            <div>
                                <h3 class="text-sm font-black text-[#2F3E5C] leading-snug">
                                    {{ $alerta['nombre'] }}
                                </h3>
                                <div class="flex items-center gap-1.5 mt-1">
                                    <span class="inline-flex items-center rounded-md bg-white/50 border border-[#CBBBAA]/50 px-2 py-0.5 text-[9px] font-black text-[#2F3E5C]/60 uppercase tracking-wide">
                                        <i class="ph-bold {{ $alerta['icono'] }} mr-1"></i>
                                        {{ $alerta['categoria_label'] }}
                                    </span>
                                </div>
                            </div>

                            {{-- Descripción de la alerta --}}
                            <p class="text-xs font-bold leading-relaxed text-[#2F3E5C]/75">
                                {{ $alerta['descripcion'] }}
                            </p>

                            {{-- Fecha relacionada si existe --}}
                            @if($alerta['fecha'])
                                <p class="text-[10px] font-semibold text-[#2F3E5C]/45 flex items-center gap-1">
                                    <i class="ph-bold ph-calendar"></i>
                                    Fecha registrada: <strong>{{ $alerta['fecha'] }}</strong>
                                </p>
                            @endif
                        </div>

                        {{-- Botón para revisar ficha --}}
                        <div class="mt-4 border-t border-[#CBBBAA]/20 pt-3 flex justify-end">
                            <a
                                href="{{ route('admin.adultos-mayores.show', $alerta['adulto_id']) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-white/70 px-3.5 py-2 text-[10px] font-black uppercase tracking-wider text-[#2F3E5C] border border-[#CBBBAA] transition hover:bg-white active:scale-95"
                            >
                                <i class="ph-bold ph-eye"></i> Revisar en ficha
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>
</div>
