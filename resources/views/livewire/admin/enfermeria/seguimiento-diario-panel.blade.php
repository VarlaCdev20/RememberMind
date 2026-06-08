<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-3xl border border-borde bg-fondo-card p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-meta">Enfermería</p>
            <h1 class="text-2xl font-black text-titulo">Seguimiento diario</h1>
            <p class="text-sm font-semibold text-apoyo">Registro operativo por adulto mayor, turno, plan de cuidado e incidencias.</p>
        </div>
        <button type="button" wire:click="abrirCrear" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black uppercase tracking-wider text-white shadow-sm">
            Registrar seguimiento
        </button>
    </div>

    <div class="grid gap-3 rounded-2xl border border-borde bg-fondo-card p-4 md:grid-cols-3">
        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar adulto mayor" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
        <select wire:model.live="filtroTurno" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
            <option value="">Todos los turnos</option>
            @foreach($turnos as $turno)
                <option value="{{ $turno->cod_turno }}">{{ $turno->nombre }} ({{ substr($turno->hora_inicio, 0, 5) }})</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="filtroFecha" class="rounded-xl border border-borde bg-fondo-panel px-4 py-2 text-sm text-parrafo">
    </div>

    <div class="overflow-hidden rounded-3xl border border-borde bg-fondo-card shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead class="bg-fondo-panel text-xs font-black uppercase tracking-wider text-meta">
                <tr>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Adulto mayor</th>
                    <th class="px-4 py-3">Turno</th>
                    <th class="px-4 py-3">Estado general</th>
                    <th class="px-4 py-3">Plan</th>
                    <th class="px-4 py-3">Alertas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-borde/60">
                @forelse($seguimientos as $seg)
                    <tr class="hover:bg-fondo-hover/60">
                        <td class="px-4 py-3 font-bold text-parrafo">{{ optional($seg->fecha)->format('d/m/Y') }}<br><span class="text-xs text-apoyo">{{ substr($seg->hora_inicio ?? '', 0, 5) }} - {{ substr($seg->hora_fin ?? '', 0, 5) ?: '—' }}</span></td>
                        <td class="px-4 py-3 font-black text-titulo">{{ $seg->adultoMayor?->nombres }} {{ $seg->adultoMayor?->ap_paterno }}</td>
                        <td class="px-4 py-3 text-parrafo">{{ $seg->turno?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-parrafo">{{ $seg->estado_general ?? 'Sin observación' }}</td>
                        <td class="px-4 py-3 text-parrafo">{{ $seg->plan?->tipo_plan ?? 'Sin plan vinculado' }}</td>
                        <td class="px-4 py-3 text-xs font-bold text-apoyo">
                            @if($seg->incidente) Incidente @endif
                            @if($seg->requiere_medico) {{ $seg->incidente ? ' / ' : '' }}Requiere médico @endif
                            @if(!$seg->incidente && !$seg->requiere_medico) Sin alertas @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-sm font-bold text-apoyo">No hay seguimientos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $seguimientos->links() }}

    @if($modalForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form wire:submit.prevent="guardar" class="w-full max-w-3xl space-y-4 rounded-3xl border border-borde bg-fondo-card p-6 shadow-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-black text-titulo">Registrar seguimiento diario</h2>
                    <button type="button" wire:click="cerrarModales" class="text-sm font-bold text-apoyo">Cerrar</button>
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                    <select wire:model="codAm" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="">Adulto mayor</option>@foreach($adultos as $adulto)<option value="{{ $adulto->cod_am }}">{{ $adulto->nombres }} {{ $adulto->ap_paterno }}</option>@endforeach</select>
                    <select wire:model="codTurno" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="">Turno</option>@foreach($turnos as $turno)<option value="{{ $turno->cod_turno }}">{{ $turno->nombre }}</option>@endforeach</select>
                    <select wire:model="codPlan" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"><option value="">Plan opcional</option>@foreach($planes as $plan)<option value="{{ $plan->cod_plan }}">{{ $plan->adultoMayor?->nombres }} - {{ $plan->tipo_plan }}</option>@endforeach</select>
                    <input type="date" wire:model="fecha" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm">
                    <input type="time" wire:model="horaInicio" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm">
                    <input type="time" wire:model="horaFin" class="rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm">
                </div>
                <textarea wire:model="observacion" rows="4" placeholder="Observación de enfermería" class="w-full rounded-xl border border-borde bg-fondo-panel px-3 py-2 text-sm"></textarea>
                <div class="flex justify-end gap-2"><button type="button" wire:click="cerrarModales" class="rounded-xl border border-borde px-4 py-2 text-xs font-bold">Cancelar</button><button type="submit" class="rounded-xl bg-boton-principal px-4 py-2 text-xs font-black text-white">Guardar</button></div>
            </form>
        </div>
    @endif
</div>
