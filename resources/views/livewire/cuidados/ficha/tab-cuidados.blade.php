{{-- TAB 4: CUIDADOS (PLAN DE CUIDADOS VIGENTE + TABLA DE TAREAS PROGRAMADAS) --}}
<div class="space-y-6">
    {{-- 1. PLAN DE CUIDADOS VIGENTE --}}
    @php
        $plan = $adultoMayor->planCuidadoActivo;
    @endphp

    <div class="rounded-3xl border border-borde rm-surface-card bg-fondo-panel p-5 shadow-sm space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-borde pb-3">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-2">
                    <i class="ph-bold ph-hand-heart text-blue-600 text-sm"></i>
                    <span>Plan de Cuidados de Enfermería Vigente</span>
                </h2>
                <p class="text-xs text-apoyo mt-0.5">Planificación individualizada y metas terapéuticas del residente.</p>
            </div>
            @if($plan)
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Plan Activo
                </span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 border border-slate-200">
                    Sin plan activo
                </span>
            @endif
        </div>

        @if($plan)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="rounded-2xl bg-fondo-card p-3.5 border border-borde md:col-span-2 space-y-2">
                    <div>
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Diagnóstico de Enfermería / Enfoque</span>
                        <h3 class="text-sm font-bold text-titulo mt-0.5">
                            {{ $plan->diagnostico_enfermeria ?? $plan->nombre ?? 'Atención Integral de Enfermería' }}
                        </h3>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Objetivos Terapéuticos</span>
                        <p class="text-parrafo mt-0.5 leading-relaxed">
                            {{ $plan->objetivo ?? 'Mantenimiento del bienestar físico, prevención de riesgos y fomento de autonomía.' }}
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl bg-fondo-card p-3.5 border border-borde space-y-2">
                    <div>
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Período de Vigencia</span>
                        <p class="text-parrafo font-bold mt-0.5">
                            Desde: {{ $plan->fecha_inicio ? \Carbon\Carbon::parse($plan->fecha_inicio)->format('d/m/Y') : 'Inicio' }}
                            <br>
                            Hasta: {{ $plan->fecha_fin ? \Carbon\Carbon::parse($plan->fecha_fin)->format('d/m/Y') : 'Indefinido' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-apoyo uppercase block">Frecuencia de Evaluación</span>
                        <p class="text-apoyo mt-0.5">Continua por turno asistencial</p>
                    </div>
                </div>
            </div>
        @else
            <div class="py-4 text-center text-xs text-apoyo italic">
                No se encuentra formulado un plan de cuidados activo para este residente.
            </div>
        @endif
    </div>

    {{-- 2. TAREAS PROGRAMADAS Y ESTADO DE EJECUCIÓN --}}
    <div class="rounded-3xl border border-borde rm-surface-card bg-fondo-panel p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-borde pb-3">
            <div>
                <h2 class="text-xs font-black uppercase tracking-wider text-titulo flex items-center gap-2">
                    <i class="ph-bold ph-check-square-offset text-sky-600 text-sm"></i>
                    <span>Tareas Programadas y Estado de Ejecución</span>
                </h2>
                <p class="text-xs text-apoyo mt-0.5">Control y registro de actividades programadas de enfermería.</p>
            </div>
            @php
                $totTareas = $adultoMayor->tareasActuales->count();
                $pendientes = $adultoMayor->tareasActuales->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->count();
                $completadas = $adultoMayor->tareasActuales->where('estado', 'REALIZADA')->count();
            @endphp
            <div class="flex items-center gap-2 text-xs">
                <span class="rounded-lg bg-blue-50 px-2.5 py-1 font-bold text-blue-700 border border-blue-200">
                    {{ $pendientes }} pendientes
                </span>
                <span class="rounded-lg bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700 border border-emerald-200">
                    {{ $completadas }} realizadas
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-borde text-[10px] font-black uppercase text-apoyo">
                        <th class="py-2.5 px-3">Hora</th>
                        <th class="py-2.5 px-3">Tarea / Actividad</th>
                        <th class="py-2.5 px-3">Prioridad</th>
                        <th class="py-2.5 px-3">Estado</th>
                        <th class="py-2.5 px-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde/50">
                    @forelse($adultoMayor->tareasActuales as $tar)
                        @php
                            $esPendiente = in_array($tar->estado, ['PENDIENTE', 'EN_PROCESO']);
                            $esRealizada = $tar->estado === 'REALIZADA';
                            $esOmitida = in_array($tar->estado, ['OMITIDA', 'CANCELADA']);
                        @endphp
                        <tr class="hover:bg-fondo-card/60 transition">
                            <td class="py-3 px-3 whitespace-nowrap font-bold text-titulo">
                                <span class="rounded bg-fondo-card px-2 py-1 text-[11px] font-mono border border-borde">
                                    {{ substr($tar->hora_programada ?? '00:00', 0, 5) }}
                                </span>
                            </td>

                            <td class="py-3 px-3">
                                <p class="font-bold text-titulo">{{ $tar->titulo }}</p>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-apoyo">
                                    <span>Área: <strong>{{ $tar->area ?? 'General' }}</strong></span>
                                    @if($tar->resultado && $esRealizada)
                                        <span>· Resultado: <strong class="text-emerald-700">{{ $tar->resultado }}</strong></span>
                                    @endif
                                    @if($tar->motivo_omision && $esOmitida)
                                        <span>· Motivo: <strong class="text-amber-700">{{ $tar->motivo_omision }}</strong></span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-3 px-3 whitespace-nowrap">
                                @php
                                    $prio = strtoupper($tar->prioridad ?? 'MEDIA');
                                @endphp
                                @if(in_array($prio, ['ALTA', 'URGENTE']))
                                    <span class="inline-flex items-center gap-1 rounded bg-rose-50 px-2 py-0.5 text-[10px] font-black text-rose-700 border border-rose-200">
                                        {{ $prio }}
                                    </span>
                                @elseif($prio === 'MEDIA')
                                    <span class="inline-flex items-center gap-1 rounded bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                        {{ $prio }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700 border border-slate-200">
                                        {{ $prio }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-3 whitespace-nowrap">
                                @if($esRealizada)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                        <i class="ph-bold ph-check"></i> REALIZADA
                                    </span>
                                @elseif($tar->estado === 'EN_PROCESO')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 border border-blue-200">
                                        <i class="ph-bold ph-spinner"></i> EN PROCESO
                                    </span>
                                @elseif($esOmitida)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 border border-amber-200">
                                        <i class="ph-bold ph-prohibit"></i> {{ ucfirst(strtolower($tar->estado)) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-700 border border-slate-200">
                                        <i class="ph-bold ph-clock"></i> PENDIENTE
                                    </span>
                                @endif
                            </td>

                            {{-- SOLO MOSTRAR ACCIONES POSIBLES SEGÚN ESTADO --}}
                            <td class="py-3 px-3 text-right whitespace-nowrap">
                                @if($esPendiente)
                                    <button type="button"
                                            wire:click="abrirModalTarea('{{ $tar->cod_tarea }}')"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition">
                                        <i class="ph-bold ph-pencil-simple"></i>
                                        <span>Registrar</span>
                                    </button>
                                @elseif($esRealizada)
                                    <span class="text-[11px] text-emerald-700 font-bold inline-flex items-center gap-1">
                                        <i class="ph-bold ph-check-circle"></i> Completada
                                    </span>
                                @elseif($esOmitida)
                                    <span class="text-[11px] text-amber-700 font-medium inline-flex items-center gap-1">
                                        <i class="ph-bold ph-info"></i> Justificada
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-xs text-apoyo italic">
                                No se registran tareas de cuidados programadas para este turno.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
