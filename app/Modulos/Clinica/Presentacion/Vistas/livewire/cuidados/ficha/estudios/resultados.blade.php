        {{-- COLUMNA IZQUIERDA (70–72%): GRÁFICO + RANGOS + LISTA DE ESTUDIOS      --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-8 space-y-5">

            {{-- 4.1 GRÁFICO DE EVOLUCIÓN DE PARÁMETROS CLAVE + VALORES DE REFERENCIA --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
                {{-- Gráfico Temporal Line/Area --}}
                <div class="md:col-span-8 rm-chart-card rm-chart-glass space-y-3.5">
                    <div class="rm-chart-header flex-col sm:flex-row sm:items-center">
                        <div class="flex items-center gap-2">
                            <i class="ph-bold ph-chart-line-up text-[var(--rm-action-primary)] text-base"></i>
                            <h3 class="rm-chart-title">
                                Evolución de parámetros clave
                            </h3>
                        </div>
                        <span class="rm-chart-kpi-badge">
                            Últimos 6 meses
                        </span>
                    </div>

                    {{-- Tabs de Parámetros Clave --}}
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                        @php
                            $paramsClave = [
                                'glucosa' => 'Glucosa',
                                'hemoglobina' => 'Hemoglobina',
                                'creatinina' => 'Creatinina',
                                'sodio' => 'Sodio',
                                'potasio' => 'Potasio',
                            ];
                        @endphp

                        @foreach($paramsClave as $pKey => $pLabel)
                            <button type="button"
                                    wire:click="setParametroGraficoEstudio('{{ $pKey }}')"
                                    class="px-2.5 py-1 rounded-xl text-xs font-bold transition cursor-pointer whitespace-nowrap {{ $parametroGraficoEstudio === $pKey ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] border border-[var(--rm-border)]' }}">
                                {{ $pLabel }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Canvas del Gráfico Line/Area --}}
                    <div class="rm-chart-body h-48 sm:h-52 w-full relative" style="height: 13rem; min-height: 12rem; max-height: 13rem;" wire:ignore>
                        <canvas id="chartEvolucionEstudiosCanvas" role="img" aria-label="Evolución de resultados de estudios clínicos"></canvas>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-[var(--rm-text-muted)] pt-1 border-t border-[var(--rm-border)]/50">
                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full inline-block bg-[var(--rm-chart-clinical-500)]"></span>
                            <span class="font-bold text-[var(--rm-text-title)]">{{ $graficoData['nombre'] }}:</span>
                            <span>Último {{ $graficoData['ultimo_valor'] }} {{ $graficoData['unidad'] }}</span>
                        </span>
                        <span class="font-mono text-[10.5px]">
                            Rango ref.: {{ $graficoData['texto_rango'] }}
                        </span>
                    </div>
                </div>

                {{-- Valores de Referencia Clínicos (Card al lado del gráfico) --}}
                <div class="md:col-span-4 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-[var(--rm-border)]/60 pb-2.5">
                            <div class="flex items-center gap-1.5">
                                <i class="ph-bold ph-scales text-[var(--rm-action-primary)] text-sm"></i>
                                <h4 class="text-xs font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                                    Valores de referencia
                                </h4>
                            </div>
                        </div>

                        <div class="mt-2.5 space-y-2">
                            <div class="p-2 rounded-xl bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] dark:border-[var(--rm-info-strong)]">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-[var(--rm-action-primary)] dark:text-[var(--rm-info-strong)] block">Parámetro</span>
                                <span class="text-xs font-bold text-[var(--rm-text-title)] block">{{ $rangosParam['nombre'] }}</span>
                            </div>

                            <div class="space-y-1.5">
                                @foreach($rangosParam['clasificaciones'] as $clasif)
                                    <div class="flex items-center justify-between p-1.5 rounded-lg border border-[var(--rm-border)]/50 {{ $clasif['bg'] }} text-xs">
                                        <span class="font-bold text-[11px] {{ $clasif['color'] }}">
                                            {{ $clasif['etiqueta'] }}
                                        </span>
                                        <span class="font-mono text-[10.5px] font-bold text-[var(--rm-text-title)]">
                                            {{ $clasif['rango'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[var(--rm-border)]/50 text-[10px] text-[var(--rm-text-muted)] leading-relaxed italic">
                        <i class="ph-bold ph-info text-[var(--rm-info-strong)] mr-0.5"></i>
                        {{ $rangosParam['guia_clinica'] }}
                    </div>
                </div>
            </div>

            {{-- 4.2 LISTA PRINCIPAL DE ESTUDIOS Y RESULTADOS (TABLA CLÍNICA) --}}
            <div class="rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-table text-[var(--rm-action-primary)] text-base"></i>
                        <h3 class="text-xs sm:text-sm font-bold text-[var(--rm-text-title)] uppercase tracking-wider">
                            Lista de estudios y resultados
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-full bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-muted)]">
                            {{ count($estudios) }} estudios
                        </span>
                    </div>
                </div>

                @if(count($estudios) === 0)
                    <div class="py-12 text-center text-xs text-[var(--rm-text-muted)] space-y-2">
                        <i class="ph-bold ph-folder-open text-3xl text-[var(--rm-text-muted)] dark:text-[var(--rm-text-secondary)] block"></i>
                        <p class="font-bold text-[var(--rm-text-title)]">No existen resultados registrados para este período.</p>
                        <p>Modifique los filtros seleccionados o explore otro período de consulta.</p>
                    </div>
                @else
                    <div class="max-h-[580px] overflow-y-auto custom-timeline-scroll border border-[var(--rm-border)]/60 rounded-2xl">
                        <table class="rm-data-table rm-data-table--actions w-full text-left text-xs border-collapse">
                            <thead class="sticky top-0 z-10 bg-[var(--rm-surface-alt)] text-[var(--rm-text-muted)] font-bold text-[10.5px] uppercase tracking-wider border-b border-[var(--rm-border)]">
                                <tr>
                                    <th class="py-3 px-3.5">Fecha</th>
                                    <th class="py-3 px-3.5">Estudio / Prueba</th>
                                    <th class="py-3 px-3.5">Tipo</th>
                                    <th class="py-3 px-3.5">Resultado</th>
                                    <th class="py-3 px-3.5">Rango de referencia</th>
                                    <th class="py-3 px-3.5">Estado</th>
                                    <th class="py-3 px-3.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--rm-border)]/50 font-medium">
                                @foreach($estudios as $est)
                                    @php
                                        $esSeleccionado = ($estudioActivo['id'] ?? '') === $est['id'];
                                    @endphp
                                    <tr wire:click="seleccionarEstudio('{{ $est['id'] }}')"
                                        class="transition cursor-pointer {{ $esSeleccionado ? 'bg-[var(--rm-info-soft)] dark:bg-[var(--rm-info-soft)] text-[var(--rm-text-title)] ring-1 ring-[var(--rm-action-primary)]/30' : 'hover:bg-[var(--rm-surface-alt)]/60 text-[var(--rm-text-body)]' }}">
                                        {{-- Fecha --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap font-mono text-[11px]">
                                            <span class="font-bold text-[var(--rm-text-title)]">{{ $est['fecha'] }}</span>
                                            <span class="block text-[10px] text-[var(--rm-text-muted)]">{{ $est['hora'] }}</span>
                                        </td>

                                        {{-- Estudio / Prueba --}}
                                        <td class="py-3 px-3.5">
                                            <span class="font-bold text-[var(--rm-text-title)] block leading-snug">
                                                {{ $est['titulo'] }}
                                            </span>
                                            <span class="text-[10.5px] text-[var(--rm-text-muted)] block truncate max-w-[180px]">
                                                {{ $est['profesional_nombre'] }}
                                            </span>
                                        </td>

                                        {{-- Tipo --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)]">
                                                {{ $est['tipo_texto'] }}
                                            </span>
                                        </td>

                                        {{-- Resultado --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap font-mono font-bold text-xs text-[var(--rm-text-title)]">
                                            {{ $est['resultado_valor'] }} <span class="text-[10px] font-normal text-[var(--rm-text-muted)]">{{ $est['resultado_unidad'] }}</span>
                                        </td>

                                        {{-- Rango de referencia --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap font-mono text-[11px] text-[var(--rm-text-muted)]">
                                            {{ $est['rango_referencia'] }}
                                        </td>

                                        {{-- Estado --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-extrabold border {{ $est['estado_color'] }}">
                                                <i class="{{ $est['estado_icono'] }} text-xs"></i>
                                                <span>{{ $est['estado_badge'] }}</span>
                                            </span>
                                        </td>

                                        {{-- Acciones --}}
                                        <td class="py-3 px-3.5 whitespace-nowrap text-right">
                                            <button type="button"
                                                    wire:click.stop="seleccionarEstudio('{{ $est['id'] }}')"
                                                    class="px-2.5 py-1 rounded-lg font-extrabold text-[11px] transition inline-flex items-center gap-1 {{ $esSeleccionado ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs' : 'bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] dark:bg-[var(--rm-info-soft)] dark:text-[var(--rm-info-strong)] border border-[var(--rm-info-strong)] dark:border-[var(--rm-info-strong)] hover:bg-[var(--rm-info-soft)]' }}">
                                                <span>Ver</span>
                                                <i class="ph-bold ph-caret-right text-[10px]"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        {{-- ===================================================================== --}}
