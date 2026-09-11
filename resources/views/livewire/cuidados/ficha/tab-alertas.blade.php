{{-- TAB 6: ALERTAS (ALERTAS ACTIVAS PRIMERO + HISTORIAL DE ALERTAS CERRADAS) --}}
<div class="space-y-6">
    {{-- 1. ALERTAS ACTIVAS (ABIERTA / EN_ATENCION) --}}
    @php
        $alertasActivas = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
        $alertasCerradas = $adultoMayor->alertas->where('estado', 'CERRADA');
    @endphp

    <div class="rounded-3xl border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-borde pb-3">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-2">
                    <i class="ph-bold ph-warning-octagon text-rose-600 text-sm"></i>
                    <span>Alertas Clínicas Activas</span>
                </h2>
                <p class="text-xs text-apoyo mt-0.5">Situaciones de vigilancia prioritaria que requieren intervención asistencial.</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-bold self-start sm:self-auto {{ $alertasActivas->count() > 0 ? 'bg-rose-100 text-rose-800 border border-rose-300 animate-pulse' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                {{ $alertasActivas->count() }} {{ $alertasActivas->count() === 1 ? 'alerta activa' : 'alertas activas' }}
            </span>
        </div>

        @if($alertasActivas->count() > 0)
            <div class="space-y-3">
                @foreach($alertasActivas as $al)
                    @php
                        $esAbierta = $al->estado === 'ABIERTA';
                        $esEnAtencion = $al->estado === 'EN_ATENCION';
                    @endphp
                    <div class="rounded-2xl border p-4 transition {{ $esAbierta ? 'border-rose-200 bg-rose-50/40' : 'border-amber-200 bg-amber-50/40' }}">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="space-y-1.5 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-black uppercase {{ $esAbierta ? 'bg-rose-600 text-white' : 'bg-amber-600 text-white' }}">
                                        {{ $al->estado }}
                                    </span>
                                    <span class="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ in_array(strtoupper($al->nivel), ['ALTO', 'CRITICO']) ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                                        Nivel {{ $al->nivel }}
                                    </span>
                                    <span class="text-xs font-bold text-titulo">
                                        {{ $al->tipo_alerta }}
                                    </span>
                                </div>

                                <p class="text-xs font-medium text-parrafo leading-relaxed">
                                    {{ $al->motivo ?? 'Alerta generada por parámetros fuera de rango' }}
                                </p>

                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-apoyo pt-1">
                                    <span>Detectada: <strong>{{ $al->created_at ? $al->created_at->format('d/m/Y H:i') : 'Reciente' }}</strong></span>
                                    <span>·</span>
                                    <span>Origen: <strong>{{ $al->origen ?? 'Sistema Clínico' }}</strong></span>
                                    @if($al->responsable)
                                        <span>·</span>
                                        <span>Registrada por: <strong>{{ $al->responsable->name }}</strong></span>
                                    @endif
                                </div>

                                {{-- Acciones Realizadas hasta el momento --}}
                                @if($al->acciones && $al->acciones->count() > 0)
                                    <div class="mt-2 rounded-xl bg-white/80 p-2.5 border border-borde text-xs space-y-1">
                                        <span class="font-bold text-titulo text-[11px] block">Acciones registradas:</span>
                                        @foreach($al->acciones as $acc)
                                            <p class="text-parrafo text-[11px]">
                                                · {{ $acc->accion }} <span class="text-apoyo">({{ $acc->fecha_accion?->format('H:i') ?? $acc->created_at?->format('H:i') }})</span>
                                            </p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- ACCIONES SEGÚN ESTADO --}}
                            <div class="shrink-0 pt-1">
                                @if($esAbierta)
                                    <button type="button"
                                            wire:click="abrirModalAtenderAlerta('{{ $al->cod_alerta }}')"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 transition">
                                        <i class="ph-bold ph-hand-heart"></i>
                                        <span>Atender alerta</span>
                                    </button>
                                @elseif($esEnAtencion)
                                    <button type="button"
                                            wire:click="abrirModalCerrarAlerta('{{ $al->cod_alerta }}')"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                                        <i class="ph-bold ph-check-circle"></i>
                                        <span>Registrar acción / Cerrar</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl bg-emerald-50/40 p-6 border border-emerald-200 text-center">
                <i class="ph-bold ph-shield-check text-2xl text-emerald-600 mb-1 block"></i>
                <p class="text-xs font-bold text-emerald-900">Sin alertas clínicas activas</p>
                <p class="text-[11px] text-emerald-700 mt-0.5">El residente se encuentra hemodinámicamente estable y sin condiciones críticas abiertas.</p>
            </div>
        @endif
    </div>

    {{-- 2. HISTORIAL DE ALERTAS CERRADAS (HISTÓRICO AUDITADO) --}}
    <div class="rounded-3xl border border-borde bg-fondo-panel p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-borde pb-3">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-blue-600 text-sm"></i>
                    <span>Historial de Alertas Resueltas</span>
                </h2>
                <p class="text-xs text-apoyo mt-0.5">Registro auditado de alertas atendidas, cerradas o anuladas.</p>
            </div>
            <span class="text-xs font-bold text-apoyo">
                Total: {{ $alertasCerradas->count() }} resueltas
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-borde text-[10px] font-black uppercase text-apoyo">
                        <th class="py-2.5 px-3">Fecha / Hora</th>
                        <th class="py-2.5 px-3">Tipo / Motivo</th>
                        <th class="py-2.5 px-3">Prioridad</th>
                        <th class="py-2.5 px-3">Resolución / Acciones Realizadas</th>
                        <th class="py-2.5 px-3">Estado</th>
                        <th class="py-2.5 px-3">Responsable Cierre</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @forelse($alertasCerradas as $alc)
                        <tr class="hover:bg-fondo-card/60 transition">
                            <td class="py-3 px-3 whitespace-nowrap font-medium text-titulo">
                                {{ $alc->created_at ? $alc->created_at->format('d/m/Y') : '' }}
                                <span class="text-apoyo block text-[10px]">{{ $alc->created_at ? $alc->created_at->format('H:i') : '' }}</span>
                            </td>

                            <td class="py-3 px-3 font-bold text-titulo max-w-[200px]">
                                {{ $alc->tipo_alerta }}
                                <span class="text-parrafo font-normal text-[11px] block truncate" title="{{ $alc->motivo }}">
                                    {{ $alc->motivo }}
                                </span>
                            </td>

                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold bg-fondo-card text-parrafo border border-borde">
                                    {{ $alc->nivel }}
                                </span>
                            </td>

                            <td class="py-3 px-3 text-parrafo max-w-[260px]">
                                @if($alc->observacion_cierre)
                                    <span class="text-titulo font-medium block">{{ $alc->observacion_cierre }}</span>
                                @endif
                                @if($alc->acciones && $alc->acciones->count() > 0)
                                    <span class="text-[10px] text-apoyo block truncate">
                                        Acción: {{ $alc->acciones->last()->accion ?? 'Atención oportuna' }}
                                    </span>
                                @else
                                    <span class="text-[10px] text-apoyo">Cerrada tras resolución clínica</span>
                                @endif
                            </td>

                            <td class="py-3 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-700 border border-slate-200">
                                    <i class="ph-bold ph-check"></i> CERRADA
                                </span>
                            </td>

                            <td class="py-3 px-3 whitespace-nowrap text-apoyo font-medium">
                                {{ $alc->cerradoPor?->name ?? $alc->responsable?->name ?? 'Enfermería' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-xs text-apoyo italic">
                                No se registran alertas históricas cerradas para este residente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
