@php
    $planActivo = $adultoMayor->planCuidadoActivo;
    $intervenciones = $planActivo
        ? $planActivo->intervenciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->with(['ejecuciones' => fn ($q) => $q->latest('fecha_hora_programada')])->get()
        : collect();
    $ejecuciones = $adultoMayor->registrosCuidados()
        ->with(['intervencion', 'personal'])
        ->latest('fecha_hora_programada')
        ->take(30)
        ->get();
    $ejecucionesHoy = $ejecuciones->filter(fn ($e) => optional($e->fecha_hora_programada)->isToday());
    $realizadosHoy = $ejecucionesHoy->where('estado', 'REALIZADA')->count();
    $incidenciasHoy = $ejecucionesHoy->whereIn('estado', ['OMITIDA', 'INCIDENCIA'])->count();
    $pendientesHoy = $ejecucionesHoy->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->count();
    $totalHoy = $ejecucionesHoy->count();
    $cumplimientoPct = $totalHoy > 0 ? (int) round(($realizadosHoy / $totalHoy) * 100) : 0;
@endphp

<div x-data="{
    modalRegistro: false,
    intervencion: null,
    resultado: 'REALIZADA',
    observacion: '',
    error: '',
    abrir(item) {
        this.intervencion = item;
        this.resultado = 'REALIZADA';
        this.observacion = '';
        this.error = '';
        this.modalRegistro = true;
    },
    async confirmar() {
        if (!this.intervencion || this.observacion.trim().length < 10) {
            this.error = 'Registre una observación de al menos 10 caracteres.';
            return;
        }
        await this.$wire.registrarCuidadoDirecto(
            this.intervencion.cod_intervencion,
            this.resultado,
            this.observacion.trim()
        );
        this.modalRegistro = false;
    }
}" class="space-y-5 font-sans">
    <section class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <i class="ph-bold ph-hand-heart text-2xl text-[var(--rm-action-primary)]"></i>
                    <h2 class="text-xl font-bold text-[var(--rm-text-primary)]">Plan de cuidados</h2>
                </div>
                <p class="mt-1 text-xs font-semibold text-[var(--rm-text-secondary)]">Cuidados asistenciales, confort y seguimiento diario</p>
            </div>
            @if($planActivo)
                <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-800">
                    {{ $planActivo->nombre }} · {{ $planActivo->estado }}
                </span>
            @endif
        </div>
        <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50/70 p-3 text-xs text-blue-950">
            <strong>Enfermería ejecuta y registra cuidados programados.</strong>
            Cada acción se vincula a la intervención, jornada y profesional reales.
        </div>
    </section>

    <section class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-3 lg:grid-cols-5">
        @foreach([
            ['Cuidados activos', $intervenciones->count(), 'text-[var(--rm-action-primary)]'],
            ['Realizados hoy', $realizadosHoy, 'text-emerald-700'],
            ['Pendientes', $pendientesHoy, 'text-amber-700'],
            ['Incidencias', $incidenciasHoy, 'text-rose-700'],
            ['Cumplimiento', $cumplimientoPct.'%', 'text-[var(--rm-action-primary)]'],
        ] as [$label, $valor, $color])
            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs">
                <div class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-secondary)]">{{ $label }}</div>
                <div class="mt-1 text-2xl font-bold {{ $color }}">{{ $valor }}</div>
            </div>
        @endforeach
    </section>

    <section class="overflow-hidden rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs">
        <div class="border-b border-[var(--rm-border)] p-4">
            <h3 class="text-sm font-bold uppercase tracking-wide text-[var(--rm-text-primary)]">Intervenciones activas</h3>
            <p class="mt-0.5 text-xs text-[var(--rm-text-secondary)]">Acciones definidas en el plan vigente del residente.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="rm-data-table rm-data-table--actions w-full text-left text-xs">
                <thead class="bg-[var(--rm-surface-soft)] text-[11px] font-bold uppercase text-[var(--rm-text-secondary)]">
                    <tr>
                        <th class="px-4 py-3">Cuidado</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3">Último registro</th>
                        <th class="px-4 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)]">
                    @forelse($intervenciones as $intervencion)
                        @php($ultima = $intervencion->ejecuciones->first())
                        <tr>
                            <td class="px-4 py-3 font-bold text-[var(--rm-text-primary)]">{{ $intervencion->nombre }}</td>
                            <td class="px-4 py-3 text-[var(--rm-text-body)]">{{ $intervencion->descripcion ?: 'Sin descripción registrada' }}</td>
                            <td class="px-4 py-3 text-[var(--rm-text-secondary)]">
                                {{ $ultima?->fecha_hora_ejecucion?->format('d/m/Y H:i') ?? 'Sin ejecuciones' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @can('ejecuciones_cuidado.gestionar')
                                    <button type="button"
                                            @click='abrir(@js(["cod_intervencion" => $intervencion->cod_intervencion, "nombre" => $intervencion->nombre]))'
                                            class="rounded-xl bg-[var(--rm-action-primary)] px-3 py-2 font-bold text-white hover:bg-[var(--rm-action-primary-hover)]">
                                        Registrar
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-[var(--rm-text-secondary)]">
                                No existe un plan activo con intervenciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs">
        <div class="border-b border-[var(--rm-border)] p-4">
            <h3 class="text-sm font-bold uppercase tracking-wide text-[var(--rm-text-primary)]">Histórico de cuidados</h3>
            <p class="mt-0.5 text-xs text-[var(--rm-text-secondary)]">Ejecuciones persistidas con responsable y resultado.</p>
        </div>
        <div class="divide-y divide-[var(--rm-border)]">
            @forelse($ejecuciones as $ejecucion)
                <div class="grid gap-2 p-4 text-xs sm:grid-cols-[1fr_auto]">
                    <div>
                        <div class="font-bold text-[var(--rm-text-primary)]">{{ $ejecucion->intervencion?->nombre ?? 'Intervención no disponible' }}</div>
                        <div class="mt-1 text-[var(--rm-text-body)]">{{ $ejecucion->resultado ?: $ejecucion->observacion ?: 'Sin resultado registrado' }}</div>
                        <div class="mt-1 text-[11px] text-[var(--rm-text-secondary)]">
                            {{ $ejecucion->personal ? trim($ejecucion->personal->nombres.' '.$ejecucion->personal->apellido_paterno) : 'Responsable no disponible' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="rounded-full border px-2 py-0.5 text-[11px] font-bold">{{ $ejecucion->estado }}</span>
                        <div class="mt-1 text-[11px] text-[var(--rm-text-secondary)]">{{ $ejecucion->fecha_hora_ejecucion?->format('d/m/Y H:i') ?? $ejecucion->fecha_hora_programada?->format('d/m/Y H:i') ?? 'Sin fecha' }}</div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-[var(--rm-text-secondary)]">No existen cuidados ejecutados para este residente.</div>
            @endforelse
        </div>
    </section>

    <div x-show="modalRegistro" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.outside="modalRegistro = false" class="w-full max-w-lg rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xl">
            <h3 class="text-base font-bold text-[var(--rm-text-primary)]">Registrar cuidado</h3>
            <p class="mt-1 text-xs text-[var(--rm-text-secondary)]" x-text="intervencion?.nombre"></p>
            <label class="mt-4 block text-[11px] font-bold uppercase text-[var(--rm-text-secondary)]">Resultado</label>
            <select x-model="resultado" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] px-3 py-2 text-xs">
                <option value="REALIZADA">Realizada</option>
                <option value="INCIDENCIA">Incidencia</option>
            </select>
            <label class="mt-3 block text-[11px] font-bold uppercase text-[var(--rm-text-secondary)]">Observación clínica</label>
            <textarea x-model="observacion" rows="4" class="mt-1 w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-soft)] p-3 text-xs" placeholder="Describa el cuidado realizado y la respuesta del residente"></textarea>
            <p x-show="error" x-text="error" class="mt-1 text-xs font-semibold text-rose-700"></p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" @click="modalRegistro = false" class="rounded-xl border border-[var(--rm-border)] px-4 py-2 text-xs font-bold">Cancelar</button>
                <button type="button" @click="confirmar()" class="rounded-xl bg-[var(--rm-action-primary)] px-4 py-2 text-xs font-bold text-white">Confirmar registro</button>
            </div>
        </div>
    </div>
</div>
