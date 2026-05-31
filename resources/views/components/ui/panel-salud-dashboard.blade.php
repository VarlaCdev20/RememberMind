@props(['resumen' => []])

<div class="rm-card rounded-[2rem] p-5">
    <div class="mb-4">
        <span class="text-[11px] font-black uppercase tracking-widest text-modulo-salud">
            Módulo de salud
        </span>
        <h2 class="text-lg font-black text-titulo">Resumen clínico-asistencial</h2>
        <p class="text-xs font-bold text-meta">Estado operativo del área de salud institucional.</p>
    </div>

    {{-- Grid principal: 6 métricas base --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">

        <div class="rm-metric-card">
            <div class="rm-metric-label">Fichas médicas activas</div>
            <div class="rm-metric-value">{{ $resumen['fichasActivas'] ?? 0 }}</div>
            <div class="rm-metric-label mt-1">Con ficha activa</div>
        </div>

        <div class="rm-metric-card {{ ($resumen['adultosSinFicha'] ?? 0) > 0 ? 'border-boton-acento' : '' }}">
            <div class="rm-metric-label">Sin ficha médica</div>
            <div class="rm-metric-value {{ ($resumen['adultosSinFicha'] ?? 0) > 0 ? '' : 'text-titulo' }}">
                {{ $resumen['adultosSinFicha'] ?? 0 }}
            </div>
            <div class="rm-metric-label mt-1">
                @if(($resumen['adultosSinFicha'] ?? 0) > 0)
                    <span class="text-boton-acento">Requiere revisión</span>
                @else
                    Al día
                @endif
            </div>
        </div>

        <div class="rm-metric-card">
            <div class="rm-metric-label">Medicaciones activas</div>
            <div class="mt-1 text-xl font-black text-modulo-salud">{{ $resumen['medicacionesActivas'] ?? 0 }}</div>
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

        <div class="rm-metric-card {{ ($resumen['altaDependencia'] ?? 0) > 0 ? 'border-estado-advertencia-borde' : '' }}">
            <div class="rm-metric-label">Alta dependencia</div>
            <div class="mt-1 text-xl font-black {{ ($resumen['altaDependencia'] ?? 0) > 0 ? 'text-estado-advertencia-texto' : 'text-titulo' }}">
                {{ $resumen['altaDependencia'] ?? 0 }}
            </div>
            <div class="rm-metric-label mt-1">
                @if(($resumen['altaDependencia'] ?? 0) > 0)
                    <span class="text-estado-advertencia-texto">Con seguimiento</span>
                @else
                    Sin alertas
                @endif
            </div>
        </div>

    </div>

    {{-- Tira de métricas adicionales --}}
    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">

        <div class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-hover px-3 py-2">
            <i class="ph-fill ph-heartbeat text-base text-meta"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Signos vitales</p>
                <p class="text-base font-black text-titulo">{{ $resumen['signosVitales7d'] ?? 0 }}</p>
                <p class="text-[9px] font-bold text-meta">Últimos 7 días</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-hover px-3 py-2">
            <i class="ph-fill ph-brain text-base text-modulo-cognitivoTexto"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Eval. cognitiva</p>
                <p class="text-base font-black text-titulo">{{ $resumen['evalCognitivas30d'] ?? 0 }}</p>
                <p class="text-[9px] font-bold text-meta">Últimos 30 días</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-borde {{ ($resumen['riesgoCaidaAlto'] ?? 0) > 0 ? 'border-estado-advertencia-borde' : '' }} bg-fondo-hover px-3 py-2">
            <i class="ph-fill ph-warning text-base {{ ($resumen['riesgoCaidaAlto'] ?? 0) > 0 ? 'text-estado-advertencia-texto' : 'text-meta' }}"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Riesgo caída</p>
                <p class="text-base font-black {{ ($resumen['riesgoCaidaAlto'] ?? 0) > 0 ? 'text-estado-advertencia-texto' : 'text-titulo' }}">
                    {{ $resumen['riesgoCaidaAlto'] ?? 0 }}
                </p>
                <p class="text-[9px] font-bold text-meta">Alto riesgo</p>
            </div>
        </div>

        <div class="flex items-center gap-2 rounded-xl border border-borde bg-fondo-hover px-3 py-2">
            <i class="ph-fill ph-pill text-base text-modulo-salud"></i>
            <div>
                <p class="text-[10px] font-black uppercase tracking-wide text-meta">Medicación hoy</p>
                <p class="text-base font-black text-titulo">{{ $resumen['adminMedicacionHoy'] ?? 0 }}</p>
                <p class="text-[9px] font-bold text-meta">Administraciones</p>
            </div>
        </div>

    </div>
</div>
