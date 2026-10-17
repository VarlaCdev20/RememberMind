        {{-- COLUMNA DERECHA (50–52%): DETALLE DEL EVENTO SELECCIONADO            --}}
        {{-- ===================================================================== --}}
        <div class="lg:col-span-6 rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-sm space-y-4 lg:sticky lg:top-4">
            @if(!$eventoActivo)
                <div class="py-16 text-center text-xs text-[var(--rm-text-muted)]">
                    <i class="ph-bold ph-cursor-click text-3xl text-[var(--rm-text-muted)] block mb-1"></i>
                    <p class="font-bold text-[var(--rm-text-title)]">Seleccione un evento clínico de la lista</p>
                    <p>Haga clic en cualquier evento de la izquierda para ver su detalle completo.</p>
                </div>
            @else
                {{-- Cabecera del Detalle --}}
                <div class="border-b border-[var(--rm-border)] pb-3.5 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-base sm:text-lg font-bold text-[var(--rm-text-title)] tracking-tight">
                                {{ $eventoActivo['titulo'] }}
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $eventoActivo['estado_color'] }}">
                                {{ $eventoActivo['estado_badge'] }}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-[var(--rm-text-body)] font-medium leading-relaxed">
                        {{ $eventoActivo['descripcion_resumida'] }}
                    </p>

                    {{-- Si está resuelto, mostrar banner con datos de cierre --}}
                    @if($eventoActivo['estado'] === 'RESUELTO' && !empty($eventoActivo['resolucion']))
                        <div class="p-2.5 rounded-xl bg-[var(--rm-success-soft)] border border-[var(--rm-success-strong)] text-[var(--rm-success-strong)] text-xs flex items-center justify-between gap-2">
                            <span class="flex items-center gap-1.5 font-bold">
                                <i class="ph-bold ph-check-circle text-[var(--rm-success-strong)]"></i>
                                <span>Resuelto: {{ $eventoActivo['resolucion']['descripcion'] ?? 'Caso cerrado satisfactoriamente.' }}</span>
                            </span>
                            <span class="font-mono text-[10.5px] text-[var(--rm-success-strong)] shrink-0">
                                {{ $eventoActivo['resolucion']['fecha'] ?? '' }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Tabs Internas del Detalle --}}
                <div class="flex items-center gap-1.5 border-b border-[var(--rm-border)] pb-1 overflow-x-auto text-xs scrollbar-none">
                    @php
                        $tabsDetalle = [
                            'resumen' => 'Resumen',
                            'valoracion' => 'Valoración',
                            'intervenciones' => 'Intervenciones',
                            'seguimiento' => 'Seguimiento',
                            'documentos' => 'Documentos',
                            'trazabilidad' => 'Trazabilidad',
                        ];
                    @endphp

                    @foreach($tabsDetalle as $tKey => $tLabel)
                        <button type="button"
                                wire:click="setTabDetalleEvento('{{ $tKey }}')"
                                class="px-3 py-1.5 rounded-xl font-extrabold transition cursor-pointer whitespace-nowrap {{ $tabDetalleEvento === $tKey ? 'bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] shadow-2xs' : 'text-[var(--rm-text-body)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)]' }}">
                            {{ $tLabel }}
                        </button>
                    @endforeach
                </div>

                {{-- Contenido de las Tabs Internas --}}
                <div class="space-y-4 text-xs">

                    {{-- TAB 1: RESUMEN (ACTIVO POR DEFECTO) --}}
                    @if($tabDetalleEvento === 'resumen')
                        <div class="space-y-4">
                            {{-- Dos columnas internas: Información básica + Descripción completa --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                {{-- Información básica --}}
                                <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-2">
                                    <h4 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                                        Información básica
                                    </h4>
                                    <dl class="space-y-1.5 text-xs">
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Fecha y hora</dt>
                                            <dd class="font-mono font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['fecha'] }} · {{ $eventoActivo['hora'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Lugar</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['lugar'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Tipo de evento</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['tipo_label'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Severidad</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['severidad'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Estado</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['estado_badge'] }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] text-[var(--rm-text-muted)] font-semibold uppercase">Registrado por</dt>
                                            <dd class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['profesional_nombre'] }} ({{ $eventoActivo['profesional_rol'] }})</dd>
                                        </div>
                                    </dl>
                                </div>

                                {{-- Descripción del evento --}}
                                <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between space-y-3">
                                    <div>
                                        <h4 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Descripción del evento
                                        </h4>
                                        <p class="mt-1.5 text-xs text-[var(--rm-text-body)] leading-relaxed">
                                            {{ $eventoActivo['descripcion_completa'] }}
                                        </p>
                                    </div>

                                    {{-- Conclusión inicial --}}
                                    <div class="p-3 rounded-xl bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] dark:bg-[var(--rm-info-soft)] dark:border-[var(--rm-info-strong)] text-[var(--rm-info-strong)] dark:text-[var(--rm-info-strong)]">
                                        <h5 class="text-[10.5px] font-bold uppercase tracking-wide text-[var(--rm-action-primary)] dark:text-[var(--rm-info-strong)]">
                                            Conclusión inicial
                                        </h5>
                                        <p class="text-xs font-semibold mt-0.5">
                                            {{ $eventoActivo['conclusion_inicial'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Parte inferior del Detalle: 3 cards horizontales --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                {{-- 1. Ubicación del evento --}}
                                <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-map-pin text-[var(--rm-action-primary)] text-base"></i>
                                        <h5 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Ubicación
                                        </h5>
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <p class="font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['lugar'] }}</p>
                                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">{{ $eventoActivo['piso'] }} · {{ $eventoActivo['habitacion'] }}</p>
                                    </div>
                                </div>

                                {{-- 2. Próxima evaluación --}}
                                <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-calendar-check text-[var(--rm-warning-strong)] text-base"></i>
                                        <h5 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Próxima evaluación
                                        </h5>
                                    </div>
                                    <div class="mt-2 text-xs">
                                        <p class="font-mono font-bold text-[var(--rm-text-title)]">{{ $eventoActivo['proxima_evaluacion_fecha'] }}</p>
                                        <p class="text-[11px] text-[var(--rm-text-muted)] mt-0.5">{{ $eventoActivo['proxima_evaluacion_responsable'] }}</p>
                                    </div>
                                </div>

                                {{-- 3. Plan de seguimiento --}}
                                <div class="p-3 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex flex-col justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-bold ph-check-square-offset text-[var(--rm-success-strong)] text-base"></i>
                                        <h5 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)]">
                                            Plan de seguimiento
                                        </h5>
                                    </div>
                                    <ul class="mt-2 space-y-1 text-[11px] text-[var(--rm-text-body)]">
                                        @foreach($eventoActivo['plan_seguimiento'] as $tarea)
                                            <li class="flex items-center gap-1.5">
                                                <i class="ph-bold ph-check text-[var(--rm-success-strong)] text-xs"></i>
                                                <span class="truncate">{{ $tarea['texto'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 2: VALORACIÓN --}}
                    @if($tabDetalleEvento === 'valoracion')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3.5">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-stethoscope text-[var(--rm-action-primary)]"></i>
                                <span>Valoración clínica estructurada</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] block">Estado general</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['estado_general'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] block">Nivel de conciencia</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['nivel_conciencia'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] block">Dolor (Escala EVA)</span>
                                    <p class="font-bold text-[var(--rm-danger-strong)] mt-0.5">{{ $eventoActivo['valoracion']['dolor_eva'] }} / 10</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] block">Signos vitales al momento</span>
                                    <p class="font-mono font-bold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['signos_vitales'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] block">Movilidad posterior</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['movilidad'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] block">Lesiones encontradas</span>
                                    <p class="font-semibold text-[var(--rm-text-title)] mt-0.5">{{ $eventoActivo['valoracion']['lesiones_encontradas'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 3: INTERVENCIONES --}}
                    @if($tabDetalleEvento === 'intervenciones')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-first-aid-kit text-[var(--rm-action-primary)]"></i>
                                <span>Intervenciones asistenciales realizadas</span>
                            </h4>
                            <div class="space-y-2">
                                @foreach($eventoActivo['intervenciones'] as $acc)
                                    <div class="flex items-start gap-3 p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                        <span class="font-mono text-xs font-bold text-[var(--rm-action-primary)] shrink-0 pt-0.5">
                                            {{ $acc['hora'] }}
                                        </span>
                                        <div class="flex-1">
                                            <p class="font-bold text-[var(--rm-text-title)] text-xs flex items-center gap-1.5">
                                                <i class="ph-bold ph-check text-[var(--rm-success-strong)]"></i>
                                                <span>{{ $acc['accion'] }}</span>
                                            </p>
                                            <p class="text-[10.5px] text-[var(--rm-text-muted)] mt-0.5">
                                                Responsable: {{ $acc['profesional'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- TAB 4: SEGUIMIENTO --}}
                    @if($tabDetalleEvento === 'seguimiento')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3.5">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-bold uppercase">Estado actual</span>
                                    <p class="font-bold text-[var(--rm-action-primary)] text-xs mt-0.5">{{ $eventoActivo['seguimiento']['estado_actual'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-bold uppercase">Próxima reevaluación</span>
                                    <p class="font-mono font-bold text-[var(--rm-text-title)] text-xs mt-0.5">{{ $eventoActivo['seguimiento']['proxima_reevaluacion'] }}</p>
                                </div>
                                <div class="p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                    <span class="text-[10px] text-[var(--rm-text-muted)] font-bold uppercase">Responsable</span>
                                    <p class="font-bold text-[var(--rm-text-title)] text-xs mt-0.5">{{ $eventoActivo['seguimiento']['responsable'] }}</p>
                                </div>
                            </div>

                            <div>
                                <h5 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-title)] mb-2">
                                    Acciones de seguimiento en curso
                                </h5>
                                <div class="space-y-1.5">
                                    @foreach($eventoActivo['seguimiento']['acciones_pendientes'] as $ap)
                                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                            <span class="font-bold text-xs text-[var(--rm-text-title)]">{{ $ap['titulo'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)]">
                                                {{ $ap['estado'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- TAB 5: DOCUMENTOS --}}
                    @if($tabDetalleEvento === 'documentos')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-files text-[var(--rm-action-primary)]"></i>
                                <span>Documentos y reportes clínicos</span>
                            </h4>
                            <div class="space-y-2">
                                @forelse($eventoActivo['documentos'] as $doc)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-[var(--rm-surface)] border border-[var(--rm-border)]">
                                        <div class="flex items-center gap-2.5">
                                            <i class="ph-bold {{ $doc['icono'] }} text-xl"></i>
                                            <div>
                                                <p class="font-bold text-xs text-[var(--rm-text-title)]">{{ $doc['nombre'] }}</p>
                                                <p class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">{{ $doc['fecha'] }} · {{ $doc['tamano'] }}</p>
                                            </div>
                                        </div>
                                        <button type="button"
                                                onclick="alert('Descargando copia clínica oficial: {{ $doc['nombre'] }}')"
                                                class="px-2.5 py-1 rounded-lg border border-[var(--rm-border)] hover:bg-[var(--rm-surface-soft)] text-xs font-bold transition cursor-pointer">
                                            Consultar
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-xs text-[var(--rm-text-muted)] py-4 text-center">Sin documentos adjuntos.</p>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    {{-- TAB 6: TRAZABILIDAD --}}
                    @if($tabDetalleEvento === 'trazabilidad')
                        <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[var(--rm-text-title)] flex items-center gap-2">
                                <i class="ph-bold ph-fingerprint text-[var(--rm-action-primary)]"></i>
                                <span>Trazabilidad y auditoría clínica (Inmutable)</span>
                            </h4>
                            <div class="space-y-2.5 relative pl-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-[var(--rm-surface-soft)] dark:before:bg-[var(--rm-surface-soft)]">
                                @foreach($eventoActivo['trazabilidad'] as $tz)
                                    <div class="relative flex items-start gap-2 text-xs">
                                        <span class="h-2 w-2 rounded-full bg-[var(--rm-action-primary)] mt-1 shrink-0 -ml-[19px]"></span>
                                        <div>
                                            <p class="font-bold text-[var(--rm-text-title)]">{{ $tz['accion'] }}</p>
                                            <p class="font-mono text-[10.5px] text-[var(--rm-text-muted)] mt-0.5">
                                                {{ $tz['fecha_hora'] }} · {{ $tz['usuario'] }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            @endif
        </div>


    {{-- ========================================================================= --}}
