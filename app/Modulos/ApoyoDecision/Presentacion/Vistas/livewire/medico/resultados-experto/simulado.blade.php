@php($actual = $simulado['criterio_actual'])
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-rm-secondary mb-1" aria-label="Miga de pan">
                <span>Expediente del residente</span>
                <span>/</span>
                <span class="text-rm-primary font-medium">Evaluación cognitiva</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-rm-text-primary">Resultados del sistema experto</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="toggleSimulacion" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition shadow-sm" title="Alternar simulación">
                <i class="ph ph-sliders shrink-0"></i>
                <span>Modo Simulado: ON</span>
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl border border-rm bg-rm-surface hover:bg-rm-surface-soft transition shadow-sm">
                <i class="ph ph-tree-structure text-sm"></i>
                <span>Ver trazabilidad completa</span>
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl border border-rm bg-rm-surface hover:bg-rm-surface-soft transition shadow-sm">
                <i class="ph ph-file-text text-sm"></i>
                <span>Exportar reporte</span>
            </button>
            <button type="button" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-800 text-white hover:bg-emerald-900 transition shadow-sm">
                <i class="ph ph-plus text-sm"></i>
                <span>Nueva evaluación</span>
            </button>
        </div>
    </div>

    <section class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="relative w-14 h-14 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                    <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=160&q=80"
                         alt="{{ $simulado['residente']['nombre'] }}"
                         class="w-full h-full object-cover">
                </div>
                <div>
                    <h2 class="text-lg font-bold text-rm-text-primary leading-tight">{{ $simulado['residente']['nombre_corto'] }}</h2>
                    <p class="text-xs text-rm-secondary mt-0.5">Código: <span class="font-mono">{{ $simulado['residente']['codigo'] }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 lg:gap-8 items-center border-t lg:border-t-0 border-rm pt-3 lg:pt-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                        <i class="ph ph-user text-base"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-rm-secondary leading-none">Edad</p>
                        <p class="text-sm font-semibold text-rm-text-primary mt-1">{{ $simulado['residente']['edad'] }} años</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                        <i class="ph ph-calendar-blank text-base"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-rm-secondary leading-none">Fecha de evaluación</p>
                        <p class="text-sm font-semibold text-rm-text-primary mt-1">{{ $simulado['evaluacion']['fecha'] }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                        <i class="ph ph-gear text-base"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-rm-secondary leading-none">Versión del conocimiento</p>
                        <p class="text-sm font-semibold text-rm-text-primary mt-1">{{ $simulado['evaluacion']['version'] }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center lg:justify-end">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="ph ph-check-circle-fill text-emerald-600 text-sm"></i>
                    <span>Evaluación completada</span>
                </span>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        <aside class="lg:col-span-4 space-y-3">
            <div class="rounded-2xl border border-rm bg-rm-surface p-4 shadow-sm">
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="flex items-center gap-1.5">
                        <h3 class="text-sm font-bold text-rm-text-primary">Perfil cognitivo</h3>
                        <i class="ph ph-info text-slate-400 text-sm" title="Desglose por los 5 dominios evaluados"></i>
                    </div>
                </div>

                <div class="space-y-2.5" role="tablist" aria-label="Criterios cognitivos">
                    @foreach($simulado['perfil'] as $cod => $area)
                        @php($isSelected = ($criterio === $cod))
                        <button type="button"
                                wire:click="seleccionarCriterio('{{ $cod }}')"
                                role="tab"
                                aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                                class="w-full text-left p-3.5 rounded-xl border transition-all flex items-start gap-3 relative
                                       {{ $isSelected
                                            ? 'border-rose-300 ring-2 ring-rose-100 bg-rose-50/20 shadow-sm'
                                            : 'border-rm hover:border-slate-300 bg-rm-surface hover:bg-slate-50/80' }}">

                            <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center mt-0.5
                                        @if($area['color'] === 'rose') bg-rose-50 text-rose-600 border border-rose-200/60
                                        @elseif($area['color'] === 'emerald') bg-emerald-50 text-emerald-600 border border-emerald-200/60
                                        @elseif($area['color'] === 'amber') bg-amber-50 text-amber-600 border border-amber-200/60
                                        @else bg-slate-100 text-slate-500 border border-slate-200 @endif">
                                <i class="ph {{ $area['icono'] }} text-lg"></i>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="text-xs font-bold text-rm-text-primary truncate">
                                        {{ $area['nombre'] }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-1 mb-1.5">
                                    <span class="text-[11px] text-rm-secondary font-mono">({{ $cod }})</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide shrink-0 {{ $area['badge_clase'] }}">
                                        @if(str_contains($area['badge'], 'Pendiente')) ! @elseif(str_contains($area['badge'], 'Validado')) ✓ @elseif(str_contains($area['badge'], 'Requiere')) ! @endif
                                        {{ $area['badge'] }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-snug line-clamp-2">
                                    {{ $area['subtitulo'] }}
                                </p>
                            </div>

                            <div class="shrink-0 self-center text-slate-300">
                                <i class="ph ph-caret-right text-sm"></i>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>
        <main class="lg:col-span-8 space-y-4 min-w-0">
            <div class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0
                                @if($actual['color'] === 'rose') bg-rose-50 text-rose-600 border border-rose-200
                                @elseif($actual['color'] === 'emerald') bg-emerald-50 text-emerald-600 border border-emerald-200
                                @elseif($actual['color'] === 'amber') bg-amber-50 text-amber-600 border border-amber-200
                                @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                        <i class="ph {{ $actual['icono'] }} text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-rm-text-primary leading-tight">{{ $actual['nombre'] }}</h2>
                        <p class="text-xs text-rm-secondary font-mono tracking-wider">{{ $actual['codigo'] }}</p>
                    </div>
                </div>
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $actual['badge_clase'] }}">
                        <i class="ph ph-warning-circle text-sm"></i>
                        <span>{{ $actual['badge_header'] }}</span>
                    </span>
                </div>
            </div>

            @if(!empty($actual['alerta_clinica']))
                @php($alerta = $actual['alerta_clinica'])
                <section class="rounded-2xl border border-rose-300 bg-rose-50/80 p-4 sm:p-5 shadow-sm" role="alert" aria-live="assertive">
                    <div class="flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-700 shrink-0 mt-0.5">
                            <i class="ph ph-warning-octagon text-2xl font-bold"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base font-bold text-rose-950">{{ $alerta['titulo'] }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-rose-200 text-rose-900 border border-rose-300">
                                    Prioridad Alta · Alerta Clínica
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-rose-800 mt-1 leading-relaxed">
                                {{ $alerta['resumen'] }}
                            </p>

                            <div class="mt-3 bg-white/80 rounded-xl p-3.5 border border-rose-200/80 shadow-2xs">
                                <h4 class="text-xs font-bold text-rose-950 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <i class="ph ph-list-checks text-rose-600"></i>
                                    <span>Motivos clínicos identificados por el sistema:</span>
                                </h4>
                                <ul class="space-y-1.5 text-xs text-rose-900">
                                    @foreach($alerta['motivos'] as $motivo)
                                        <li class="flex items-start gap-2">
                                            <i class="ph ph-dot-outline text-rose-600 text-sm mt-0.5 shrink-0"></i>
                                            <span>{{ $motivo }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="mt-3 flex items-start gap-2 text-xs text-rose-900 bg-rose-100/50 p-2.5 rounded-lg border border-rose-200/50">
                                <i class="ph ph-info text-rose-700 text-sm shrink-0 mt-0.5"></i>
                                <span><strong>Conducta recomendada:</strong> {{ $alerta['conducta'] }}</span>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-rose-200/90 bg-rose-50/50 p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-bold text-rose-900 uppercase tracking-wider mb-3">
                        <span class="w-5 h-5 rounded-full bg-rose-200 text-rose-800 flex items-center justify-center text-xs font-bold">!</span>
                        <span>Resultado del sistema experto</span>
                    </div>
                    <p class="text-base font-bold text-slate-900 leading-snug">
                        {{ $actual['resultado_titulo'] }}
                    </p>
                </div>

                <div class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        <i class="ph ph-file-text text-base text-slate-500"></i>
                        <span>Interpretación del sistema experto</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ $actual['interpretacion'] }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        <i class="ph ph-lightbulb text-base text-amber-500"></i>
                        <span>¿Por qué se obtuvo este resultado?</span>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-700">
                        @foreach($actual['por_que'] as $motivo)
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 mt-1.5 shrink-0"></span>
                                <span>{{ $motivo }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                        <i class="ph ph-warning-circle text-base text-amber-600"></i>
                        <span>Alertas y observaciones</span>
                    </div>
                    <ul class="space-y-2 text-xs text-slate-700">
                        @foreach($actual['alertas'] as $alertaItem)
                            <li class="flex items-start gap-2">
                                @if($alertaItem['tipo'] === 'danger')
                                    <span class="w-4 h-4 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">!</span>
                                @elseif($alertaItem['tipo'] === 'warning')
                                    <span class="w-4 h-4 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">!</span>
                                @else
                                    <span class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">✓</span>
                                @endif
                                <span>{{ $alertaItem['texto'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                    <i class="ph ph-file-text text-base text-slate-500"></i>
                    <span>Evidencias utilizadas</span>
                </div>

                @if(!empty($actual['evidencias']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-rm text-slate-400 font-semibold">
                                    <th class="py-2.5 px-3">Código</th>
                                    <th class="py-2.5 px-3">Evidencia</th>
                                    <th class="py-2.5 px-3">Fuente</th>
                                    <th class="py-2.5 px-3 text-right">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-rm">
                                @foreach($actual['evidencias'] as $ev)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="py-3 px-3 font-mono font-medium text-slate-800">{{ $ev['codigo'] }}</td>
                                        <td class="py-3 px-3 font-semibold text-slate-900">{{ $ev['nombre'] }}</td>
                                        <td class="py-3 px-3 text-slate-600">{{ $ev['fuente'] }}</td>
                                        <td class="py-3 px-3 text-right">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <i class="ph ph-check text-xs"></i>
                                                <span>{{ $ev['estado'] }}</span>
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-slate-500 py-3">No hay evidencias registradas para este criterio.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-rm bg-rm-surface p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    <i class="ph ph-user-circle text-base text-slate-500"></i>
                    <span>Validación profesional</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-emerald-800 text-white hover:bg-emerald-900 transition shadow-xs">
                        <i class="ph ph-check text-sm"></i>
                        <span>Validar resultado</span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border border-rose-200 bg-rose-50/60 text-rose-700 hover:bg-rose-100 transition shadow-xs">
                        <i class="ph ph-x text-sm"></i>
                        <span>No coincide</span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border border-amber-200 bg-amber-50/60 text-amber-700 hover:bg-amber-100 transition shadow-xs">
                        <i class="ph ph-magnifying-glass text-sm"></i>
                        <span>Requiere más evidencia</span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border border-sky-200 bg-sky-50/60 text-sky-700 hover:bg-sky-100 transition shadow-xs">
                        <i class="ph ph-arrows-clockwise text-sm"></i>
                        <span>Solicitar reevaluación</span>
                    </button>
                    <button type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs">
                        <i class="ph ph-chat-teardrop-text text-sm"></i>
                        <span>Agregar observación</span>
                    </button>
                </div>

                <div>
                    <label for="comentario-profesional" class="block text-xs font-semibold text-slate-600 mb-1.5">
                        Comentario del profesional (opcional)
                    </label>
                    <textarea id="comentario-profesional"
                              wire:model.defer="comentarioProfesional"
                              rows="2"
                              class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition"
                              placeholder="Escriba aquí sus observaciones, consideraciones clínicas o contexto adicional..."></textarea>
                    <div class="flex justify-end mt-1">
                        <span class="text-[11px] text-slate-400">0/500</span>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
