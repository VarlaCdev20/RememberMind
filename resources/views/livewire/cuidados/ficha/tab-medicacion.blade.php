{{-- TAB 3: MEDICACIÓN (MEDICACIÓN ACTIVA PRESCRITA + HISTORIAL DE ADMINISTRACIÓN) --}}
<div class="space-y-6">
    {{-- SECCIÓN 1: MEDICACIÓN ACTIVA PRESCRITA --}}
    <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--rm-border)] pb-3">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                    <i class="ph-bold ph-pill text-emerald-600 text-sm"></i>
                    <span>Medicación Activa Prescrita</span>
                </h2>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Tratamientos farmacológicos vigentes y pautas de administración médica.</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                    {{ $adultoMayor->medicaciones->count() }} fármacos activos
                </span>
                <a href="{{ route('admin.salud-seguimiento.medicacion', ['adulto' => $adultoMayor->cod_am]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-boton-principal bg-boton-principal px-3 py-1.5 text-xs font-bold text-inverso shadow-sm transition hover:bg-boton-principalHover active:scale-95">
                    <i class="ph-bold ph-plus-circle text-sm"></i>
                    <span>Agregar Medicamento</span>
                </a>
            </div>
        </div>

        <div class="rm-table-container">
            <table class="rm-table text-xs">
                <thead>
                    <tr class="rm-table-header">
                        <th class="py-2.5 px-3">Medicamento</th>
                        <th class="py-2.5 px-3">Dosis</th>
                        <th class="py-2.5 px-3">Vía</th>
                        <th class="py-2.5 px-3">Horario / Frecuencia</th>
                        <th class="py-2.5 px-3">Próxima Administración</th>
                        <th class="py-2.5 px-3">Estado</th>
                        <th class="py-2.5 px-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @forelse($adultoMayor->medicaciones as $med)
                        @php
                            $tomasMedicamento = $agendaMedicacion->where('medicacion.cod_med_adulto', $med->cod_med_adulto);
                            $proximaToma = $tomasMedicamento->first(fn ($toma) => !$toma['registro']) ?: $tomasMedicamento->last();
                            $registroHoy = $proximaToma['registro'] ?? null;
                            $horaProgramada = $proximaToma['hora'] ?? null;
                            $estadoToma = $proximaToma['estado'] ?? 'Sin horario programado';
                        @endphp
                        <tr class="rm-table-row">
                            <td class="py-3 px-3 font-bold text-[var(--rm-text-title)]">
                                {{ $med->nombre_medicamento }}
                                @if($med->indicaciones)
                                    <span class="block text-[11px] font-normal text-[var(--rm-text-muted)]">{{ $med->indicaciones }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-medium text-[var(--rm-text-body)]">{{ $med->dosis }}</td>
                            <td class="py-3 px-3">
                                <span class="rounded bg-[var(--rm-surface-alt)] px-2 py-0.5 text-[11px] font-bold text-[var(--rm-text-body)] border border-[var(--rm-border)]">
                                    {{ $med->via_administracion ?: 'Oral' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-[var(--rm-text-body)] font-medium">{{ $med->frecuencia ?: ($med->horario ?: 'Según pauta') }}</td>
                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($horaProgramada)
                                    <span class="inline-flex items-center gap-1 rounded border border-boton-principal/30 bg-boton-principal/10 px-2 py-0.5 font-bold text-boton-principal">
                                        <i class="ph-bold ph-clock"></i> {{ $horaProgramada }} · {{ $estadoToma }}
                                    </span>
                                @else
                                    <span class="text-[var(--rm-text-muted)] italic">Sin toma pendiente hoy</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    {{ ucfirst(strtolower($med->estado)) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if(!$registroHoy)
                                    <button type="button"
                                            wire:click="abrirModalMedicacion('{{ $med->cod_med_adulto }}', '{{ $horaProgramada }}')"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                                        <i class="ph-bold ph-check"></i>
                                        <span>Administrar</span>
                                    </button>
                                @else
                                    <span class="text-[11px] text-[var(--rm-text-muted)] font-medium">{{ $registroHoy->administrado ? 'Administrada' : 'Omitida' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-xs text-[var(--rm-text-muted)] italic">
                                No se registran medicamentos activos prescritos para este residente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECCIÓN 2: HISTORIAL DE ADMINISTRACIÓN --}}
    <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--rm-border)] pb-3">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-blue-600 text-sm"></i>
                    <span>Historial de Administración Farmacológica</span>
                </h2>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">Registro auditado de fármacos administrados y omisiones justificadas.</p>
            </div>
            @php
                $totAdmin = $adultoMayor->administracionesMedicacion->count();
                $admOk = $adultoMayor->administracionesMedicacion->where('administrado', true)->count();
                $adhPct = $totAdmin > 0 ? (int) round(($admOk / $totAdmin) * 100) : 100;
            @endphp
            <div class="flex items-center gap-2 text-xs">
                <span class="text-[var(--rm-text-muted)]">Adherencia:</span>
                <span class="rounded-lg px-2 py-0.5 font-bold {{ $adhPct >= 90 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                    {{ $adhPct }}%
                </span>
            </div>
        </div>

        <div class="rm-table-container">
            <table class="rm-table text-xs">
                <thead>
                    <tr class="rm-table-header">
                        <th class="py-2.5 px-3">Fecha / Hora</th>
                        <th class="py-2.5 px-3">Medicamento</th>
                        <th class="py-2.5 px-3">Dosis y Vía</th>
                        <th class="py-2.5 px-3">Estado</th>
                        <th class="py-2.5 px-3">Motivo Omisión / Efecto</th>
                        <th class="py-2.5 px-3">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @forelse($adultoMayor->administracionesMedicacion as $adm)
                        @php
                            $esOk = (bool) $adm->administrado;
                        @endphp
                        <tr class="rm-table-row">
                            <td class="py-2.5 px-3 whitespace-nowrap font-medium text-[var(--rm-text-title)]">
                                {{ $adm->fecha ? \Carbon\Carbon::parse($adm->fecha)->format('d/m/Y') : '' }}
                                <span class="text-[var(--rm-text-muted)] block text-[10px]">
                                    Prog: {{ substr($adm->hora_programada ?? '00:00', 0, 5) }}
                                    @if($adm->hora_real) · Real: {{ substr($adm->hora_real, 0, 5) }} @endif
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-bold text-[var(--rm-text-title)]">
                                {{ $adm->medicacion->nombre_medicamento ?? 'Fármaco prescrito' }}
                            </td>
                            <td class="py-2.5 px-3 text-[var(--rm-text-body)]">
                                {{ $adm->medicacion->dosis ?? '' }}
                                <span class="text-[var(--rm-text-muted)] text-[10px] block">Vía {{ $adm->medicacion->via_administracion ?? 'Oral' }}</span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($esOk)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        <i class="ph-bold ph-check-circle"></i> ADMINISTRADA
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                        <i class="ph-bold ph-warning-circle"></i> OMITIDA
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-[var(--rm-text-body)] max-w-[220px]">
                                @if(!$esOk && $adm->motivo_omision)
                                    <span class="text-amber-800 font-medium">{{ $adm->motivo_omision }}</span>
                                @elseif($adm->efecto_observado)
                                    <span>{{ $adm->efecto_observado }}</span>
                                @else
                                    <span class="text-[var(--rm-text-muted)]">Sin incidencias</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-[var(--rm-text-muted)] font-medium">
                                {{ $adm->registrador?->name ?? 'Enfermería' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-[var(--rm-text-muted)] italic">
                                No se registran eventos de administración en el historial.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
