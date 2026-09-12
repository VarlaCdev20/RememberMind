<div class="rm-page-layout font-sans space-y-5">
    <header class="rm-page-header-card p-4 sm:p-5 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-boton-acento">Continuidad asistencial</p>
        <div class="mt-1 flex flex-col gap-3 md:flex-row md:items-center md:justify-between"><div><h1 class="text-2xl font-black text-titulo">Reportes de Enfermería</h1><p class="text-sm text-apoyo">{{ $esSuperAdmin ? 'Actividad institucional y evolución individual por periodo.' : 'Actividad real del turno y evolución individual por periodo.' }}</p></div><a href="{{ route('admin.enfermeria.dashboard') }}" class="rm-btn-secondary px-4 py-2 text-xs font-bold">{{ $esSuperAdmin ? 'Volver al resumen global' : 'Volver a Mi turno' }}</a></div>
    </header>
    <section class="rm-filter-bar p-3.5 grid gap-3 md:grid-cols-3">
        <label class="text-xs font-bold text-titulo">Desde<input wire:model.live="desde" type="date" class="mt-1 rm-input w-full text-xs"></label>
        <label class="text-xs font-bold text-titulo">Hasta<input wire:model.live="hasta" type="date" class="mt-1 rm-input w-full text-xs"></label>
        <label class="text-xs font-bold text-titulo">Residente<select wire:model.live="codAm" class="mt-1 rm-input w-full text-xs"><option value="">{{ $esSuperAdmin ? 'Todos los residentes' : 'Todos los asignados' }}</option>@foreach($pacientes as $p)<option value="{{ $p->cod_am }}">{{ $p->ap_paterno }}, {{ $p->nombres }}</option>@endforeach</select></label>
    </section>
    <section class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">@foreach(['cuidados'=>'Cuidados','signos'=>'Controles','dosis'=>'Dosis administradas','omisiones'=>'Omisiones','incidentes'=>'Incidentes','alertas_abiertas'=>'Alertas abiertas'] as $k=>$e)<div class="rm-card-metric p-4"><p class="text-2xl font-black text-titulo">{{ $indicadores[$k] }}</p><p class="mt-1 text-xs font-bold text-apoyo">{{ $e }}</p></div>@endforeach</section>
    <section class="rm-surface-card bg-fondo-panel rounded-2xl border border-borde p-4 sm:p-5 shadow-sm">
        <div class="flex items-center justify-between"><div><h2 class="text-lg font-black text-titulo">Evolución de cuidados</h2><p class="text-xs text-apoyo">Ingesta, hidratación y dolor calculados desde registros firmados.</p></div>@if($codAm)<a href="{{ route('admin.enfermeria.pacientes.ficha', ['adulto' => $codAm, 'tab' => 'historial']) }}" class="text-xs font-bold text-boton-principal">Abrir registros originales</a>@endif</div>
        <div class="mt-5 grid min-h-48 grid-cols-7 items-end gap-3 overflow-x-auto md:grid-cols-14">
            @forelse($tendencia as $dia)
                <div class="flex min-w-12 flex-col items-center gap-1" title="{{ $dia['fecha'] }} · Alimentación {{ $dia['alimentacion'] ?? 'S/D' }}% · Hidratación {{ $dia['hidratacion'] }} ml · Dolor {{ $dia['dolor'] ?? 'S/D' }}">
                    <span class="text-[9px] font-bold text-apoyo">{{ $dia['alimentacion'] !== null ? $dia['alimentacion'].'%' : 'S/D' }}</span>
                    <div class="flex h-32 w-full items-end rounded-lg bg-fondo-card p-1"><div class="w-full rounded-md bg-boton-principal" style="height: {{ max(3, (int) ($dia['alimentacion'] ?? 0)) }}%"></div></div>
                    <span class="text-[9px] text-apoyo">{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m') }}</span>
                </div>
            @empty
                <p class="col-span-full self-center text-center text-sm text-apoyo">Registre cuidados para visualizar la tendencia.</p>
            @endforelse
        </div>
        @if($tendencia->isNotEmpty())
            <div class="mt-4 rm-table-container"><table class="rm-table"><thead class="rm-table-header"><tr><th class="p-2">Fecha</th><th class="p-2">Alimentación</th><th class="p-2">Hidratación</th><th class="p-2">Dolor medio</th></tr></thead><tbody>@foreach($tendencia as $dia)<tr class="rm-table-row"><td class="p-2">{{ \Carbon\Carbon::parse($dia['fecha'])->format('d/m/Y') }}</td><td class="p-2">{{ $dia['alimentacion'] !== null ? $dia['alimentacion'].'%' : 'Sin dato' }}</td><td class="p-2">{{ $dia['hidratacion'] }} ml</td><td class="p-2">{{ $dia['dolor'] ?? 'Sin dato' }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
    <div class="grid gap-5 lg:grid-cols-2">
        <section class="rm-surface-card bg-fondo-panel rounded-2xl border border-borde p-4 sm:p-5 shadow-sm"><h2 class="text-lg font-black text-titulo">Cuidados registrados</h2><div class="mt-4 max-h-[32rem] space-y-2 overflow-y-auto">@forelse($cuidados as $r)<article class="rounded-xl bg-fondo-card p-3"><div class="flex justify-between gap-3"><p class="text-xs font-black text-titulo">{{ $r->adultoMayor?->nombres }} {{ $r->adultoMayor?->ap_paterno }} · {{ str_replace('_',' ',$r->tipo) }}</p><time class="text-[11px] text-apoyo">{{ $r->fecha_hora_evento->format('d/m H:i') }}</time></div><p class="mt-1 text-xs text-parrafo">{{ $r->subtipo }}{{ $r->observacion ? ' · '.$r->observacion : '' }}</p></article>@empty<p class="text-sm text-apoyo">Sin actividad en el periodo.</p>@endforelse</div></section>
        <section class="rm-surface-card bg-fondo-panel rounded-2xl border border-borde p-4 sm:p-5 shadow-sm"><h2 class="text-lg font-black text-titulo">Incidentes y seguimiento</h2><div class="mt-4 max-h-[32rem] space-y-2 overflow-y-auto">@forelse($incidentes as $i)<article class="rounded-xl border border-estado-advertenciaBorde bg-estado-advertenciaBg p-3"><div class="flex justify-between gap-3"><p class="text-xs font-black text-titulo">{{ $i->adultoMayor?->nombres }} · {{ str_replace('_',' ',$i->tipo) }}</p><time class="text-[11px] text-apoyo">{{ $i->fecha_hora_evento->format('d/m H:i') }}</time></div><p class="mt-1 text-xs text-parrafo">{{ $i->descripcion }}</p></article>@empty<p class="text-sm text-apoyo">Sin incidentes en el periodo.</p>@endforelse</div></section>
    </div>
</div>
