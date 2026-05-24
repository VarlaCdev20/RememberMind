@props(['resumen' => []])

<div class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)]">
    <div class="mb-4">
        <span class="text-[11px] font-black uppercase tracking-widest text-[#2A9D8F]">
            Módulo de salud
        </span>
        <h2 class="text-lg font-black text-azul-profundo">Resumen clínico-asistencial</h2>
        <p class="text-xs font-bold text-azul-profundo/55">Estado operativo del área de salud institucional.</p>
    </div>

    {{-- Grid principal: 6 métricas base --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">

        <div class="rm-metric-card">
            <div class="rm-metric-label">Fichas médicas activas</div>
            <div class="rm-metric-value">{{ $resumen['fichasActivas'] ?? 0 }}</div>
            <div class="rm-metric-label mt-1">Con ficha activa</div>
        </div>

        <div class="rm-metric-card {{ ($resumen['adultosSinFicha'] ?? 0) > 0 ? 'border-terracota/40' : '' }}">
            <div class="rm-metric-label">Sin ficha médica</div>
            <div class="rm-metric-value {{ ($resumen['adultosSinFicha'] ?? 0) > 0 ? '' : 'text-azul-profundo' }}">
                {{ $resumen['adultosSinFicha'] ?? 0 }}
            </div>
            <div class="rm-metric-label mt-1">
                @if(($resumen['adultosSinFicha'] ?? 0) > 0)
                    <span class="text-terracota">Requiere revisión</span>
                @else
                    Al día
                @endif
            </div>
        </div>

        <div class="rm-metric-card">
            <div class="rm-metric-label">Medicaciones activas</div>
            <div class="mt-1 text-xl font-black text-[#2A9D8F]">{{ $resumen['medicacionesActivas'] ?? 0 }}</div>
            <div class="rm-metric-label mt-1">En seguimiento</div>
        </div>

        <div class="rm-metric-card">
            <div class="rm-metric-label">Atenciones este mes</div>
            <div class="rm-metric-value-neutral">{{ $resumen['atencionesMes'] ?? 0 }}</div>
            <div class="rm-metric-label mt-1">Mes en curso</div>
        </div>

        <div class="rm-metric-card">
            <div class="rm-metric-label">Valoraciones recientes</div>
            <div class="rm-metric-value-neutral">{{ $resumen['valoracionesRecientes'] ?? 0 }}</div>
            <div class="rm-metric-label mt-1">Últimos 30 días</div>
        </div>

        <div class="rm-metric-card {{ ($resumen['altaDependencia'] ?? 0) > 0 ? 'border-[#F4A261]/50' : '' }}">
            <div class="rm-metric-label">Alta dependencia</div>
            <div class="mt-1 text-xl font-black {{ ($resumen['altaDependencia'] ?? 0) > 0 ? 'text-[#D4843A]' : 'text-azul-profundo' }}">
                {{ $resumen['altaDependencia'] ?? 0 }}
            </div>
            <div class="rm-metric-label mt-1">
                @if(($resumen['altaDependencia'] ?? 0) > 0)
                    <span class="text-[#D4843A]">Con seguimiento</span>
                @else
                    Sin alertas
                @endif
            </div>
        </div>

    </div>

    {{-- Tira de métricas adicionales --}}
    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">

        <div class="flex items-center gap-2 rounded-xl border border-[#D5C7B9] bg-[#F2EBE3]/70 px-3 py-2">
            <i class="ph-fill ph-heartbeat text-base text-azul-profundo/50"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-azul-profundo/45">Signos vitales</p>
                <p class="text-base font-black text-azul-profundo">{{ $resumen['signosVitales7d'] ?? 0 }}</p>
                <p class="text-[9px] font-bold text-azul-profundo/40">Últimos 7 días</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-[#D5C7B9] bg-[#F2EBE3]/70 px-3 py-2">
            <i class="ph-fill ph-brain text-base text-[#7A68B0]"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-azul-profundo/45">Eval. cognitiva</p>
                <p class="text-base font-black text-azul-profundo">{{ $resumen['evalCognitivas30d'] ?? 0 }}</p>
                <p class="text-[9px] font-bold text-azul-profundo/40">Últimos 30 días</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-[#D5C7B9] {{ ($resumen['riesgoCaidaAlto'] ?? 0) > 0 ? 'border-[#F4A261]/50' : '' }} bg-[#F2EBE3]/70 px-3 py-2">
            <i class="ph-fill ph-warning text-base {{ ($resumen['riesgoCaidaAlto'] ?? 0) > 0 ? 'text-[#D4843A]' : 'text-azul-profundo/40' }}"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-azul-profundo/45">Riesgo caída</p>
                <p class="text-base font-black {{ ($resumen['riesgoCaidaAlto'] ?? 0) > 0 ? 'text-[#D4843A]' : 'text-azul-profundo' }}">
                    {{ $resumen['riesgoCaidaAlto'] ?? 0 }}
                </p>
                <p class="text-[9px] font-bold text-azul-profundo/40">Alto riesgo</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-[#D5C7B9] bg-[#F2EBE3]/70 px-3 py-2">
            <i class="ph-fill ph-pill text-base text-[#2A9D8F]"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-azul-profundo/45">Medicación hoy</p>
                <p class="text-base font-black text-azul-profundo">{{ $resumen['adminMedicacionHoy'] ?? 0 }}</p>
                <p class="text-[9px] font-bold text-azul-profundo/40">Administraciones</p>
            </div>
        </div>

    </div>
</div>
