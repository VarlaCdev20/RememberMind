    {{-- 5. TABLA PRINCIPAL DE MEDICACIONES (ORDEN CLÍNICO POR URGENCIA)           --}}
    {{-- ========================================================================= --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="rm-data-table rm-data-table--actions w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                        <th class="py-3 px-4">Medicamento</th>
                        <th class="py-3 px-3">Dosis</th>
                        <th class="py-3 px-3">Vía</th>
                        <th class="py-3 px-3">Horario</th>
                        <th class="py-3 px-4">Indicación</th>
                        <th class="py-3 px-3">Estado actual</th>
                        <th class="py-3 px-4">Última administración</th>
                        <th class="py-3 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)]">
                    @foreach($listaOrdenada as $item)
                        @php
                            $filtroKey = match($item['urgencia']) {
                                1, 2 => 'por_administrar',
                                3 => 'proximas',
                                4 => 'proximas',
                                5 => 'administradas',
                                6 => 'suspendidas',
                                default => 'por_administrar'
                            };
                            if ($item['es_prn']) $filtroKey = 'prn';
                        @endphp
                        <tr x-show="filtroPrioritario === '{{ $filtroKey }}' || filtroPrioritario === 'todos' || ('{{ $item['urgencia'] }}' === '1' && filtroPrioritario === 'por_administrar')"
                            class="transition-colors {{ $item['filaColor'] }}">

                            {{-- Medicamento --}}
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0 {{ $item['urgencia'] <= 2 ? 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)]' : ($item['urgencia'] === 3 ? 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)]' : ($item['urgencia'] === 5 ? 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)]' : 'bg-[var(--rm-info-soft)] text-[var(--rm-info-strong)]')) }}">
                                        <i class="ph-bold ph-pill text-base"></i>
                                    </div>
                                    <div>
                                        <button type="button"
                                                @click="abrirDetalle({{ json_encode($item) }})"
                                                class="font-bold text-[var(--rm-text-title)] hover:text-[var(--rm-action-primary)] text-left transition cursor-pointer text-xs">
                                            {{ $item['nombre'] }}
                                        </button>
                                        <p class="text-[10.5px] text-[var(--rm-text-muted)] font-medium">
                                            {{ $item['presentacion'] }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Dosis --}}
                            <td class="py-3.5 px-3 font-bold text-[var(--rm-text-title)]">
                                {{ $item['dosis'] }}
                            </td>

                            {{-- Vía --}}
                            <td class="py-3.5 px-3 font-medium text-[var(--rm-text-body)]">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[10.5px]">
                                    {{ $item['via'] }}
                                </span>
                            </td>

                            {{-- Horario con Badge de Estado y Evaluación en Vivo PC --}}
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-xs text-[var(--rm-text-title)] block font-mono">
                                        {{ $item['horario_12h'] ?? $item['horario'] }}
                                    </span>
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-mono font-medium">({{ $item['horario'] }})</span>
                                </div>
                                @if($item['urgencia'] === 5)
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $item['badgeColor'] }}">
                                        {{ $item['minutosBadge'] }}
                                    </span>
                                @else
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[10px] font-bold border"
                                          :class="relojPC.evaluarHorario('{{ $item['horario_12h'] ?? $item['horario'] }}', false).sePaso ? 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)] border-[var(--rm-danger-strong)] animate-pulse' : '{{ $item['badgeColor'] }}'"
                                          x-text="relojPC.evaluarHorario('{{ $item['horario_12h'] ?? $item['horario'] }}', false).texto">
                                        {{ $item['minutosBadge'] }}
                                    </span>
                                @endif
                            </td>

                            {{-- Indicación --}}
                            <td class="py-3.5 px-4 text-[var(--rm-text-body)] max-w-xs truncate" title="{{ $item['indicacion'] }}">
                                {{ $item['indicacion'] }}
                            </td>

                            {{-- Estado Actual --}}
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold border {{ $item['badgeColor'] }}">
                                    @if($item['urgencia'] === 1)
                                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-danger-action)] animate-pulse"></span>
                                    @elseif($item['urgencia'] === 2)
                                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-warning-action)] animate-pulse"></span>
                                    @elseif($item['urgencia'] === 3)
                                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--rm-warning)]"></span>
                                    @elseif($item['urgencia'] === 5)
                                        <i class="ph-bold ph-check text-[11px]"></i>
                                    @endif
                                    <span>{{ $item['estadoHoy'] }}</span>
                                </span>
                            </td>

                            {{-- Última Administración --}}
                            <td class="py-3.5 px-4 text-[var(--rm-text-muted)] text-[11px]">
                                {{ $item['ultimaAdmin'] }}
                            </td>

                            {{-- Acciones --}}
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    @if($item['urgencia'] <= 2)
                                        <button type="button"
                                                @click="abrirFormularioAdministrar({{ json_encode($item) }})"
                                                class="px-3 py-1.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-[var(--rm-text-on-primary)] font-extrabold text-[11px] transition cursor-pointer shadow-2xs flex items-center gap-1">
                                            <i class="ph-bold ph-check"></i>
                                            <span>ADMINISTRAR</span>
                                        </button>
                                    @elseif($item['urgencia'] === 3)
                                        <button type="button"
                                                @click="abrirFormularioAdministrar({{ json_encode($item) }})"
                                                class="px-3 py-1.5 rounded-xl bg-[var(--rm-warning-action)] hover:bg-[var(--rm-warning-action)] text-[var(--rm-text-inverse)] font-extrabold text-[11px] transition cursor-pointer shadow-2xs flex items-center gap-1">
                                            <i class="ph-bold ph-clock"></i>
                                            <span>ADMINISTRAR</span>
                                        </button>
                                    @elseif($item['urgencia'] === 5)
                                        <button type="button"
                                                @click="abrirDetalle({{ json_encode($item) }})"
                                                class="px-2.5 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-[var(--rm-text-title)] font-bold text-[11px] transition cursor-pointer">
                                            <span>Registrada</span>
                                        </button>
                                    @else
                                        <button type="button"
                                                @click="abrirDetalle({{ json_encode($item) }})"
                                                class="px-2.5 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-[var(--rm-text-title)] font-bold text-[11px] transition cursor-pointer">
                                            <span>Consultar</span>
                                        </button>
                                    @endif

                                    <button type="button"
                                            @click="abrirDetalle({{ json_encode($item) }})"
                                            title="Detalle completo"
                                            class="p-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                                        <i class="ph-bold ph-dots-three text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 8. AGENDA DE ADMINISTRACIÓN DE HOY --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs space-y-4">
        <div class="border-b border-[var(--rm-border)] pb-3">
            <h3 class="text-sm font-bold text-[var(--rm-text-title)] uppercase tracking-wide">Agenda de administración de hoy</h3>
            <p class="mt-0.5 text-xs text-[var(--rm-text-muted)]">Cronograma construido desde prescripciones y administraciones registradas.</p>
        </div>
        @forelse($listaOrdenada as $item)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-3 text-xs">
                <div>
                    <div class="font-bold text-[var(--rm-text-title)]">{{ $item['horario_12h'] }} · {{ $item['nombre'] }}</div>
                    <div class="mt-0.5 text-[var(--rm-text-muted)]">{{ $item['dosis'] }} · {{ $item['via'] }}</div>
                </div>
                <span class="inline-flex rounded-full border px-2 py-0.5 text-[10px] font-extrabold {{ $item['badgeColor'] }}">{{ $item['estadoHoy'] }}</span>
            </div>
                @empty
            <x-ui.empty-state
                compact="true"
                icon="ph-pill"
                title="Sin dosis programadas"
                description="No existen dosis programadas actualmente para este residente." />
        @endforelse
    </div>

    {{-- 9. MEDICAMENTOS PRN (A DEMANDA)                                            --}}
    {{-- ========================================================================= --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-sm font-bold text-[var(--rm-text-title)] uppercase tracking-wide flex items-center gap-2">
                    <i class="ph-bold ph-first-aid text-[var(--rm-action-primary)] text-base"></i>
                    <span>Medicamentos PRN (a demanda)</span>
                </h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Administración condicionada a valoración clínica previa (intervalo mínimo, dosis máxima y prescripción médica)
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] text-xs font-bold border border-[var(--rm-info-strong)] self-start sm:self-auto">
                <i class="ph-bold ph-shield-check text-[var(--rm-info-strong)]"></i>
                Validación previa obligatoria
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="rm-data-table rm-data-table--actions w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[10.5px] font-bold uppercase text-[var(--rm-text-title)]">
                        <th class="py-2.5 px-4">Medicamento</th>
                        <th class="py-2.5 px-3">Dosis</th>
                        <th class="py-2.5 px-3">Vía</th>
                        <th class="py-2.5 px-4">Indicación</th>
                        <th class="py-2.5 px-3">Frecuencia máxima</th>
                        <th class="py-2.5 px-4">Última administración</th>
                        <th class="py-2.5 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)]">
                    @foreach($medicamentosPRNLista as $prn)
                        <tr class="hover:bg-[var(--rm-surface-alt)]/50 transition-colors">
                            <td class="py-3 px-4 font-bold text-[var(--rm-text-title)]">
                                {{ $prn['nombre'] }}
                            </td>
                            <td class="py-3 px-3 font-bold text-[var(--rm-text-title)]">
                                {{ $prn['dosis'] }}
                            </td>
                            <td class="py-3 px-3 text-[var(--rm-text-body)]">
                                {{ $prn['via'] }}
                            </td>
                            <td class="py-3 px-4 text-[var(--rm-text-body)]">
                                {{ $prn['indicacion'] }}
                            </td>
                            <td class="py-3 px-3 text-[var(--rm-text-muted)] font-medium">
                                {{ $prn['frecuenciaMax'] }}
                            </td>
                            <td class="py-3 px-4 text-[var(--rm-text-muted)] text-[11px]">
                                {{ $prn['ultimaAdmin'] }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button type="button"
                                        @click="abrirFormularioAdministrar({
                                            id: '{{ $prn['id'] }}',
                                            nombre: '{{ $prn['nombre'] }}',
                                            dosis: '{{ $prn['dosis'] }}',
                                            via: '{{ $prn['via'] }}',
                                            horario: 'PRN',
                                            frecuencia: '{{ $prn['frecuenciaMax'] }}',
                                            indicacion: '{{ $prn['indicacion'] }}',
                                            estadoHoy: 'PRN'
                                        })"
                                        class="px-3.5 py-1.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-[var(--rm-text-on-primary)] font-bold text-xs transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                                    <i class="ph-bold ph-plus-circle"></i>
                                    <span>REGISTRAR ADMINISTRACIÓN</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 10. HISTÓRICO DE ADMINISTRACIÓN (COMPACTO)                                --}}
    {{-- ========================================================================= --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-5 shadow-2xs space-y-3.5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-[var(--rm-text-title)] uppercase tracking-wide flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-[var(--rm-action-primary)] text-base"></i>
                    <span>Histórico de administración</span>
                </h3>
                <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                    Auditoría de dosis administradas, enfermera responsable y observaciones clínicas
                </p>
            </div>
            <button type="button"
                    class="text-xs font-bold text-[var(--rm-action-primary)] hover:underline cursor-pointer">
                Ver todos
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="rm-data-table w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[10.5px] font-bold uppercase text-[var(--rm-text-title)]">
                        <th class="py-2.5 px-4">Fecha / Hora</th>
                        <th class="py-2.5 px-3">Medicamento</th>
                        <th class="py-2.5 px-3">Dosis</th>
                        <th class="py-2.5 px-3">Vía</th>
                        <th class="py-2.5 px-3">Resultado</th>
                        <th class="py-2.5 px-4">Administrado por</th>
                        <th class="py-2.5 px-4">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--rm-border)]">
                    @foreach($historicoAdminLista as $hist)
                        <tr class="hover:bg-[var(--rm-surface-alt)]/50 transition-colors">
                            <td class="py-2.5 px-4 font-mono font-bold text-[var(--rm-text-title)] whitespace-nowrap">
                                {{ $hist['fechaHora'] }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-[var(--rm-text-title)]">
                                {{ $hist['medicamento'] }}
                            </td>
                            <td class="py-2.5 px-3 text-[var(--rm-text-body)]">
                                {{ $hist['dosis'] }}
                            </td>
                            <td class="py-2.5 px-3 text-[var(--rm-text-body)]">
                                {{ $hist['via'] }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.2 rounded-full text-[10px] font-extrabold border {{ $hist['badgeClass'] }}">
                                    {{ $hist['resultado'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-[var(--rm-text-body)]">
                                {{ $hist['administradoPor'] }}
                            </td>
                            <td class="py-2.5 px-4 text-[var(--rm-text-muted)] text-[11px] max-w-sm truncate" title="{{ $hist['observaciones'] }}">
                                {{ $hist['observaciones'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================================================= --}}
