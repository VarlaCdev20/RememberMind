    {{-- 4. TERCERA FILA: EVENTOS RELEVANTES (~32%) + HISTÓRICO COMPACTO (~68%)     --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        {{-- EVENTOS RELEVANTES (32% - 4 columnas) --}}
        <div class="lg:col-span-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                <div>
                    <h3 class="text-xs font-bold text-[var(--rm-text-title)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-warning-circle text-[var(--rm-warning-strong)]"></i>
                        <span>Controles del período</span>
                    </h3>
                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Interpretación de las tomas vigentes del período seleccionado</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border border-[var(--rm-warning-strong)]">
                    {{ count($eventosRelevantes) }} controles
                </span>
            </div>

            <div class="space-y-2 max-h-[420px] overflow-y-auto pr-1">
                @forelse($eventosRelevantes as $ev)
                    <div @click="abrirDrawerDesdeEvento({{ json_encode($ev) }})"
                         class="rounded-xl border border-[var(--rm-border)]/80 bg-[var(--rm-surface-alt)]/50 p-2.5 hover:bg-[var(--rm-surface-alt)] transition cursor-pointer space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $ev['badge_bg'] }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $ev['estado'] }}
                            </span>
                            <span class="text-[10px] font-medium text-[var(--rm-text-muted)] font-mono">{{ $ev['fecha_hora'] }}</span>
                        </div>
                        <div class="flex items-baseline justify-between pt-0.5">
                            <span class="text-xs font-bold text-[var(--rm-text-title)]">{{ $ev['evento'] }}</span>
                            <span class="text-xs font-bold text-[var(--rm-danger-strong)] font-mono">{{ $ev['valor'] }}</span>
                        </div>
                        <p class="text-[11px] text-[var(--rm-text-muted)] leading-tight">{{ $ev['motivo'] ?? ($ev['registro']->observacion ?? ($ev['registro']->observaciones ?? ($ev['evento'] ?? 'Hallazgo hemodinámico reportado.'))) }}</p>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-[var(--rm-text-muted)] italic bg-[var(--rm-surface-alt)]/40 rounded-xl border border-[var(--rm-border)]/50">
                        <i class="ph-bold ph-shield-check text-2xl text-[var(--rm-success-strong)] mb-1 block"></i>
                        <span>No hay controles vigentes en el período seleccionado.</span>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- HISTÓRICO DE REGISTROS COMPACTO CON NÚMEROS Y CRITERIO ("EL POR QUÉ") (68% - 8 columnas) --}}
        <div class="lg:col-span-8 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-2xs space-y-3">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                <div>
                    <h3 class="text-xs font-bold text-[var(--rm-text-title)] uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-info-strong)]"></i>
                        <span>Histórico de registros</span>
                    </h3>
                    <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">Valores numéricos, motivo clínico y justificación de control</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-[var(--rm-text-muted)] font-medium">
                        Total: <strong class="text-[var(--rm-text-title)]">{{ $totalRegistros }}</strong> tomas
                    </span>
                    <button type="button"
                            @click="exportarSignosExcel()"
                            title="Descargar Excel"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[10px] font-bold text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                        <i class="ph-bold ph-file-xls text-[var(--rm-success-strong)] text-xs"></i>
                        <span>Excel</span>
                    </button>
                </div>
            </div>

            <div class="rm-table-container max-h-[420px] overflow-y-auto">
                <table class="rm-data-table rm-table text-xs w-full">
                    <thead class="sticky top-0 bg-[var(--rm-surface)] z-10 shadow-2xs">
                        <tr class="border-b border-[var(--rm-border)] text-[10px] font-bold uppercase text-[var(--rm-text-muted)]">
                            <th class="py-2.5 px-3 text-left whitespace-nowrap">Fecha / Hora</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">PA (mmHg)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">FC (lpm)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">SpO₂ (%)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">Temp (°C)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">FR (rpm)</th>
                            <th class="py-2.5 px-2 text-left whitespace-nowrap">Dolor</th>
                            <th class="py-2.5 px-3 text-left min-w-[210px]">Criterio / Justificación Clínica</th>
                            <th class="py-2.5 px-2.5 text-left whitespace-nowrap">Responsable</th>
                            <th class="py-2.5 px-2 text-center whitespace-nowrap">Estado</th>
                            <th class="py-2.5 px-1.5 text-center">...</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--rm-border)]/50">
                        @forelse($registrosHistoricos as $item)
                            <tr @click="abrirDrawer({{ json_encode($item) }})"
                                class="hover:bg-[var(--rm-surface-alt)]/60 cursor-pointer transition text-[11px]">
                                {{-- Fecha y Hora --}}
                                <td class="py-2.5 px-3 whitespace-nowrap font-medium text-[var(--rm-text-title)]">
                                    {{ $item['fecha'] }}
                                    <span class="text-[var(--rm-text-muted)] text-[10px] block font-mono">{{ $item['hora'] }}</span>
                                </td>

                                {{-- Números con unidades claras --}}
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['pa'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['fc'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['spo2_val'] }}</span><span class="text-[10px] text-[var(--rm-text-muted)] font-sans">%</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['temp_val'] }}</span><span class="text-[10px] text-[var(--rm-text-muted)] font-sans">°</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['fr'] }}</span>
                                </td>
                                <td class="py-2.5 px-2 whitespace-nowrap font-mono">
                                    <span class="font-extrabold text-[var(--rm-text-title)] text-[12px]">{{ $item['dolor'] }}</span>
                                </td>

                                {{-- "EL POR QUÉ": Criterio clínico + Observación registrada --}}
                                <td class="py-2.5 px-3">
                                    <div class="flex flex-col gap-0.5">
                                        <span class="font-bold text-[11px] text-[var(--rm-text-title)] flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full {{ str_contains($item['badgeClass'], '--rm-danger') ? 'bg-[var(--rm-danger)]' : (str_contains($item['badgeClass'], '--rm-warning') ? 'bg-[var(--rm-warning)]' : 'bg-[var(--rm-success)]') }}"></span>
                                            <span>{{ $item['criterio'] }}</span>
                                        </span>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] truncate max-w-[240px] block" title="{{ $item['observaciones'] }}">
                                            {{ $item['observaciones'] }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Responsable --}}
                                <td class="py-2.5 px-2.5 whitespace-nowrap text-[var(--rm-text-muted)] max-w-[120px] truncate" title="{{ $item['responsable'] }}">
                                    {{ $item['responsable'] }}
                                </td>

                                {{-- Estado badge --}}
                                <td class="py-2.5 px-2 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $item['badgeClass'] }}">
                                        {{ $item['estado'] }}
                                    </span>
                                </td>

                                {{-- Botón 3 puntitos en fila --}}
                                <td class="py-2.5 px-1.5 text-center text-[var(--rm-text-muted)]">
                                    <button type="button"
                                            @click.stop="abrirDrawer({{ json_encode($item) }})"
                                            title="Ver detalle completo"
                                            class="p-1 rounded-md hover:bg-[var(--rm-surface)] hover:text-[var(--rm-action-primary)] transition cursor-pointer">
                                        <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-xs text-[var(--rm-text-muted)] italic">
                                    No se registran mediciones previas de signos vitales para este residente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
