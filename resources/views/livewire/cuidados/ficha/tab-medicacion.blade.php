{{-- TAB 3: ADMINISTRACIÓN DE MEDICACIÓN DE ENFERMERÍA (GOLDEN REFERENCE REMEMBERMIND) --}}
@php
    // =========================================================================
    // 1. PREPARACIÓN DE DATOS DE MEDICACIONES Y ADMINISTRACIONES
    // =========================================================================
    $medicacionesActivas = $adultoMayor->medicaciones ? $adultoMayor->medicaciones->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE']) : collect();
    $medicacionesPRN = $adultoMayor->medicaciones ? $adultoMayor->medicaciones->filter(fn ($m) => (bool) $m->es_prn) : collect();
    $medicacionesRegulares = $adultoMayor->medicaciones ? $adultoMayor->medicaciones->filter(fn ($m) => !(bool) $m->es_prn) : collect();
    $administracionesHistorico = $adultoMayor->administracionesMedicacion ?: collect();

    // Función auxiliar para formatear hora a 12 horas (AM/PM)
    $formatearAmPm = function (?string $hora) {
        if (!$hora) return 'Sin horario';
        try {
            $hLimpia = substr((string)$hora, 0, 5);
            return \Carbon\Carbon::parse("2000-01-01 {$hLimpia}")->format('h:i A');
        } catch (\Throwable $e) {
            return $hora;
        }
    };

    // Generar tratamientos clínicos únicamente desde la base de datos.
    $listaItems = collect();

    if ($adultoMayor->medicaciones && $adultoMayor->medicaciones->count() > 0) {
        foreach ($adultoMayor->medicaciones as $idx => $m) {
            $tomasMed = isset($agendaMedicacion) ? $agendaMedicacion->where('medicacion.cod_prescripcion', $m->cod_prescripcion) : collect();
            $proximaToma = $tomasMed->first(fn ($t) => empty($t['registro']));
            $registroHoy = $tomasMed->first(fn ($t) => !empty($t['registro']))['registro'] ?? null;
            $ultAdmin = $administracionesHistorico->where('cod_prescripcion', $m->cod_prescripcion)->first();

            $horaProg = $m->hora_programada ? ($m->hora_programada instanceof \Carbon\CarbonInterface ? $m->hora_programada->format('H:i') : substr((string)$m->hora_programada, 0, 5)) : null;

            // Determinar urgencia clínica
            $urgencia = 4; // PROGRAMADA por defecto
            $estadoHoy = 'Programada';
            $minutosBadge = $horaProg ? 'Horario ' . $horaProg : 'Sin horario registrado';
            $badgeColor = 'bg-blue-50 text-blue-800 border-blue-200';
            $filaColor = 'bg-blue-50/20 hover:bg-blue-50/40';

            if ($registroHoy || ($ultAdmin && ($ultAdmin->administrado ?? false))) {
                $urgencia = 5; // ADMINISTRADA
                $estadoHoy = 'Administrada';
                $minutosBadge = $ultAdmin?->fecha_hora_administracion ? 'Administrada ' . $ultAdmin->fecha_hora_administracion->format('H:i') : 'Administrada';
                $badgeColor = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                $filaColor = 'bg-emerald-50/30 hover:bg-emerald-50/50';
            } elseif (in_array(strtoupper($m->estado ?? ''), ['SUSPENDIDA', 'SUSPENDIDO', 'INACTIVO'])) {
                $urgencia = 6; // SUSPENDIDA
                $estadoHoy = 'Suspendida';
                $minutosBadge = 'Tratamiento suspendido';
                $badgeColor = 'bg-slate-100 text-slate-700 border-slate-200';
                $filaColor = 'bg-slate-50/40 hover:bg-slate-50/60 opacity-80';
            } elseif (in_array(strtoupper((string)($proximaToma['estado'] ?? '')), ['VENCIDA', 'RETRASADA'], true)) {
                $urgencia = 1;
                $estadoHoy = 'Atrasada';
                $minutosBadge = 'Dosis atrasada';
                $badgeColor = 'bg-rose-50 text-rose-800 border-rose-200';
                $filaColor = 'bg-rose-50/40 hover:bg-rose-50/60';
            } elseif (strtoupper((string)($proximaToma['estado'] ?? '')) === 'PROXIMA') {
                $urgencia = 3;
                $estadoHoy = 'Próxima';
                $minutosBadge = 'Próxima dosis';
                $badgeColor = 'bg-amber-50 text-amber-800 border-amber-200';
                $filaColor = 'bg-amber-50/30 hover:bg-amber-50/50';
            }

            $listaItems->push([
                'id' => $m->cod_prescripcion,
                'nombre' => $m->nombre_medicamento,
                'presentacion' => 'Comprimidos / Vía ' . ucfirst(strtolower($m->via_administracion ?: 'Oral')),
                'dosis' => $m->dosis ?: '1 dosis',
                'via' => ucfirst(strtolower($m->via_administracion ?: 'Oral')),
                'horario' => $horaProg ?? 'Sin horario',
                'horario_12h' => $formatearAmPm($horaProg),
                'frecuencia' => $m->frecuencia ?: 'Cada 8 horas',
                'indicacion' => $m->observacion ?: ($m->condicion_prn ?: 'Tratamiento asistencial prescrito'),
                'urgencia' => $urgencia,
                'estadoHoy' => $estadoHoy,
                'minutosBadge' => $minutosBadge,
                'badgeColor' => $badgeColor,
                'filaColor' => $filaColor,
                'ultimaAdmin' => $ultAdmin ? (($ultAdmin->hora_real ? substr((string)$ultAdmin->hora_real, 0, 5) : 'Sin hora') . ' · ' . ($ultAdmin->registrador?->name ?? 'Responsable no registrado')) : '—',
                'medico' => $m->medico_indica ?: 'No registrado',
                'fechaInicio' => $m->fecha_inicio ? ($m->fecha_inicio instanceof \Carbon\CarbonInterface ? $m->fecha_inicio->format('d/m/Y') : substr((string)$m->fecha_inicio, 0, 10)) : 'No registrada',
                'fechaFin' => $m->fecha_fin ? ($m->fecha_fin instanceof \Carbon\CarbonInterface ? $m->fecha_fin->format('d/m/Y') : substr((string)$m->fecha_fin, 0, 10)) : '—',
                'es_prn' => (bool)$m->es_prn,
                'precauciones' => $m->observacion ?: 'Sin precauciones específicas registradas.',
            ]);
        }
    }

    /* Los tratamientos no se completan con referencias visuales: cada fila debe
       corresponder a una prescripción persistida. */
    // No se agregan prescripciones de referencia ni tratamientos simulados.

    // Ordenar clínicamente por urgencia estricta:
    // 1. ATRASADA -> 2. ADMINISTRAR AHORA -> 3. PRÓXIMA -> 4. PROGRAMADA -> 5. ADMINISTRADA -> 6. SUSPENDIDA
    $listaOrdenada = $listaItems->sortBy('urgencia')->values();

    // Medicamentos PRN persistidos.
    $medicamentosPRNLista = $listaItems->where('es_prn', true)->map(fn ($item) => [
        'id' => $item['id'],
        'nombre' => $item['nombre'],
        'dosis' => $item['dosis'],
        'via' => $item['via'],
        'indicacion' => $item['indicacion'],
        'frecuenciaMax' => $item['frecuencia'],
        'ultimaAdmin' => $item['ultimaAdmin'],
    ])->values();

    // Histórico de administraciones persistidas.
    $historicoAdminLista = collect();
    if ($administracionesHistorico && $administracionesHistorico->count() > 0) {
        foreach ($administracionesHistorico as $adm) {
            $esAdm = (bool) $adm->administrado;
            $resTxt = $adm->resultado ?: ($esAdm ? 'ADMINISTRADA' : 'OMITIDA');
            $fecStr = $adm->fecha_hora_administracion ? $adm->fecha_hora_administracion->format('d/m/Y') : ($adm->fecha_hora_programada ? $adm->fecha_hora_programada->format('d/m/Y') : 'Sin fecha');
            $horStr = $adm->fecha_hora_administracion ? $adm->fecha_hora_administracion->format('H:i') : ($adm->fecha_hora_programada ? $adm->fecha_hora_programada->format('H:i') : 'Sin hora');

            $historicoAdminLista->push([
                'fechaHora' => "{$fecStr} {$horStr}",
                'medicamento' => $adm->medicacion?->nombre_medicamento ?: 'Medicación prescrita',
                'dosis' => $adm->dosis_administrada ?: ($adm->medicacion?->dosis ?: '1 dosis'),
                'via' => ucfirst(strtolower($adm->medicacion?->via_administracion ?: 'Oral')),
                'resultado' => $resTxt,
                'badgeClass' => $esAdm ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200',
                'administradoPor' => $adm->personal ? trim("{$adm->personal->nombres} {$adm->personal->apellido_paterno}") : 'Enfermero/a',
                'observaciones' => $adm->observacion ?: ($adm->motivo_omision ?: 'Registro asistencial en expediente.'),
            ]);
        }
    }

    // El historial contiene únicamente administraciones persistidas.
    $conteoPrioritarias = $listaOrdenada->whereIn('estadoHoy', ['Atrasada', 'Pendiente'])->count();
    $conteoProximas = $listaOrdenada->where('estadoHoy', 'Próxima')->count();
    $conteoAdministradas = $listaOrdenada->where('estadoHoy', 'Administrada')->count();
    $conteoOmitidas = $historicoAdminLista->where('resultado', 'OMITIDA')->count();
    $totalProgramadas = $listaOrdenada->count();
    $adherencia = $totalProgramadas > 0 ? (int) round(($conteoAdministradas / $totalProgramadas) * 100) : 0;
    $conteoPrn = $listaOrdenada->where('es_prn', true)->count();
    $conteoSuspendidas = $listaOrdenada->where('estadoHoy', 'Suspendida')->count();
@endphp

<div x-data="{
    filtroPrioritario: 'por_administrar',
    drawerMedAbierto: false,
    drawerPaso: 'detalle', // 'detalle' | 'administrar' | 'justificar'
    modalIndicaciones: false,
    alertaInterruptiva: false,
    relojPC: {
        hora12: '',
        horaCorta: '',
        segundosDesdeMedianoche: 0,
        init() {
            const tick = () => {
                const now = new Date();
                let h = now.getHours();
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                const ampm = h >= 12 ? 'PM' : 'AM';
                const h12 = h % 12 || 12;
                this.hora12 = `${String(h12).padStart(2, '0')}:${m}:${s} ${ampm}`;
                this.horaCorta = `${String(h12).padStart(2, '0')}:${m}:${s} ${ampm}`;
                this.segundosDesdeMedianoche = (now.getHours() * 3600) + (now.getMinutes() * 60) + now.getSeconds();
            };
            tick();
            setInterval(tick, 1000);
        },
        tiempoDiferenciaFormateado(horaStr) {
            if (!horaStr) return '00h 00m 00s';
            let [horaParte, ampm] = horaStr.trim().split(/\s+/);
            let [h, m] = horaParte.split(':').map(Number);
            if (ampm) {
                ampm = ampm.toUpperCase();
                if (ampm === 'PM' && h < 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
            }
            const segProg = (h * 3600) + ((m || 0) * 60);
            const diffSeg = Math.abs(this.segundosDesdeMedianoche - segProg);
            const horas = Math.floor(diffSeg / 3600);
            const minutos = Math.floor((diffSeg % 3600) / 60);
            const segundos = diffSeg % 60;
            return `${String(horas).padStart(2, '0')}h ${String(minutos).padStart(2, '0')}m ${String(segundos).padStart(2, '0')}s`;
        },
        evaluarHorario(horarioStr, administrado = false) {
            if (!horarioStr) return { estado: 'Programada', diffSeg: 0, sePaso: false, texto: 'Programada' };
            if (administrado) return { estado: 'Administrada', diffSeg: 0, sePaso: false, texto: '✓ Administrada' };

            let [horaParte, ampm] = horarioStr.trim().split(/\s+/);
            let [h, m] = horaParte.split(':').map(Number);
            if (ampm) {
                ampm = ampm.toUpperCase();
                if (ampm === 'PM' && h < 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
            }
            const segProg = (h * 3600) + ((m || 0) * 60);
            const diff = this.segundosDesdeMedianoche - segProg;
            const absDiff = Math.abs(diff);
            const horas = Math.floor(absDiff / 3600);
            const minutos = Math.floor((absDiff % 3600) / 60);
            const segundos = absDiff % 60;
            const tiempoHMS = `${String(horas).padStart(2, '0')}h ${String(minutos).padStart(2, '0')}m ${String(segundos).padStart(2, '0')}s`;

            if (diff > 0) {
                return {
                    estado: 'Atrasada',
                    diffSeg: diff,
                    sePaso: true,
                    texto: `⚠️ Se pasó de hora por ${tiempoHMS}`
                };
            } else if (diff >= -1800) {
                return {
                    estado: 'Por administrar',
                    diffSeg: absDiff,
                    sePaso: false,
                    texto: diff === 0 ? '⏰ Administrar ahora' : `⏱️ Próxima en ${tiempoHMS}`
                };
            } else {
                return {
                    estado: 'Programada',
                    diffSeg: absDiff,
                    sePaso: false,
                    texto: `Programada (en ${tiempoHMS})`
                };
            }
        }
    },
    init() {
        this.relojPC.init();
    },
    medSeleccionado: {},
    // Formulario de administración operativa
    formAdmin: {
        horaReal: '{{ now()->format("H:i") }}',
        resultado: 'ADMINISTRADA',
        observaciones: '',
        motivoOmision: '',
        reaccionAdversa: false,
        checklistVerificado: true
    },
    abrirDetalle(item) {
        this.medSeleccionado = {
            id: item.id || '',
            nombre: item.nombre || '',
            presentacion: item.presentacion || (item.dosis + ' · Vía ' + item.via),
            dosis: item.dosis || '',
            via: item.via || '',
            horario: item.horario || '',
            horarioAmPm: item.horario_12h || item.horario || '',
            frecuencia: item.frecuencia || 'No registrada',
            indicacion: item.indicacion || '',
            medico: item.medico || 'No registrado',
            ultimaAdmin: item.ultimaAdmin || '—',
            proximaDosis: item.minutosBadge || 'Horario programado',
            estado: item.estadoHoy || 'Programada',
            precauciones: item.precauciones || 'Verificar 5 correctos de enfermería.',
            documentoPlan: 'Prescripción Médica Vigente',
            documentoNota: 'Plan Asistencial de Cuidados'
        };
        this.drawerPaso = 'detalle';
        this.drawerMedAbierto = true;
    },
    abrirFormularioAdministrar(item) {
        this.abrirDetalle(item);
        this.drawerPaso = 'administrar';
        this.formAdmin.resultado = 'ADMINISTRADA';
        this.formAdmin.horaReal = '{{ now()->format("H:i") }}';
    },
    abrirJustificarDemora(item) {
        this.abrirDetalle(item);
        this.drawerPaso = 'justificar';
        this.formAdmin.resultado = 'OMITIDA';
        this.formAdmin.motivoOmision = '';
    },
    confirmarAdministracion() {
        if (this.$wire && typeof this.$wire.registrarAdministracionDirecta === 'function') {
            this.$wire.registrarAdministracionDirecta(
                this.medSeleccionado.id,
                this.formAdmin.resultado,
                this.formAdmin.horaReal,
                this.formAdmin.observaciones,
                this.formAdmin.motivoOmision
            );
        } else if (this.$wire && typeof this.$wire.abrirAdministrarMed === 'function') {
            this.$wire.abrirAdministrarMed(this.medSeleccionado.id, this.medSeleccionado.horario);
        }
        this.drawerMedAbierto = false;
        this.alertaInterruptiva = false;
    }
}" class="space-y-5 font-sans">

    {{-- ========================================================================= --}}
    {{-- 2. CABECERA OPERATIVA EXACTA                                              --}}
    {{-- ========================================================================= --}}
    <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 sm:p-5 shadow-2xs space-y-3.5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[var(--rm-action-primary)] border border-blue-200 shadow-2xs">
                    <i class="ph-bold ph-pill text-2xl"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-bold tracking-tight text-[var(--rm-text-title)] uppercase">
                            MEDICACIÓN
                        </h2>
                        {{-- Badges de contexto institucional --}}
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-[var(--rm-action-primary)] border border-blue-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            Medicación Activa Prescrita
                        </span>
                        <span class="text-[11px] font-bold text-blue-700 bg-blue-50/80 px-2 py-0.5 rounded-md border border-blue-100">
                            Plan de medicación
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-[var(--rm-text-muted)] mt-0.5">
                        Administración segura, a tiempo, para su bienestar
                    </p>
                </div>
            </div>

            {{-- Bloque derecho con fecha y turno exactos --}}
            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                @if(app(\App\Backend\Modulos\Clinica\Servicios\AccesoClinicoTemporalService::class)
                    ->tieneRol(Auth::user(), ['MEDICO GENERAL/GERIATRA']) && Auth::user()?->can('prescripciones.crear'))
                <button type="button"
                        @click="$dispatch('abrirModalMedicacion', { cod_residente: '{{ $adultoMayor->cod_residente }}' })"
                        class="rm-btn-primary h-9 px-3.5 rounded-xl text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition cursor-pointer">
                    <i class="ph-bold ph-plus-circle text-base"></i>
                    <span>Nueva Prescripción</span>
                </button>
                @endif
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)]">
                    <i class="ph-bold ph-calendar text-blue-600"></i>
                    <span>Hoy, {{ today()->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>

                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 text-xs font-bold text-emerald-800 dark:text-emerald-300 shadow-2xs">
                    <i class="ph-bold ph-desktop text-emerald-600 animate-pulse"></i>
                    <span>Hora actual PC: <strong x-text="relojPC.hora12" class="font-mono font-bold text-emerald-900 dark:text-emerald-200"></strong></span>
                </div>

                <button type="button"
                        @click="modalIndicaciones = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-title)] transition cursor-pointer shadow-2xs">
                    <i class="ph-bold ph-info text-blue-600"></i>
                    <span>Ver indicaciones generales</span>
                </button>
            </div>
        </div>

        {{-- Recordatorio funcional de enfermería --}}
        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-xs text-[var(--rm-text-body)]">
            <i class="ph-bold ph-shield-check text-[var(--rm-action-primary)] text-base shrink-0 mt-0.5"></i>
            <div class="leading-relaxed">
                <span class="font-bold text-[var(--rm-text-title)]">Enfermería administra y registra medicación prescrita.</span>
                <span class="text-[var(--rm-text-muted)] ml-1">La prescripción y modificaciones corresponden al personal médico. No modificar dosis ni pautas médicas.</span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. KPIS NUEVOS EXACTOS (REEMPLAZO COMPLETO DE LOS ANTERIORES)              --}}
    {{-- ========================================================================= --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
        {{-- KPI 1: POR ADMINISTRAR AHORA (ROJO si existen dosis vencidas/actuales) --}}
        <div class="p-3.5 rounded-2xl border bg-rose-50/70 border-rose-300 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-rose-800 uppercase tracking-wider">POR ADMINISTRAR AHORA</span>
                <i class="ph-bold ph-warning-circle text-rose-600 text-base animate-pulse"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-rose-700 font-mono">{{ $conteoPrioritarias }}</span>
                <span class="text-[10px] font-bold text-rose-600">dosis prioritarias</span>
            </div>
            <span class="text-[10px] text-rose-700 font-semibold block">Atrasadas o pendientes ahora</span>
        </div>

        {{-- KPI 2: PRÓXIMAS DOSIS (En las próximas 2 horas) --}}
        <div class="p-3.5 rounded-2xl border border-amber-200 bg-amber-50/50 shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider">PRÓXIMAS DOSIS</span>
                <i class="ph-bold ph-clock-countdown text-amber-600 text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-amber-800 font-mono">{{ $conteoProximas }}</span>
                <span class="text-[10px] font-bold text-amber-700">próximas</span>
            </div>
            <span class="text-[10px] text-amber-800 font-medium block">Ventana de administración</span>
        </div>

        {{-- KPI 3: ADMINISTRADAS HOY (4 de 6 programadas) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">ADMINISTRADAS HOY</span>
                <i class="ph-bold ph-check-circle text-emerald-600 text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-emerald-700 font-mono">{{ $conteoAdministradas }} de {{ $totalProgramadas }}</span>
                <span class="text-[10px] text-[var(--rm-text-muted)]">programadas</span>
            </div>
            <span class="text-[10px] text-emerald-600 font-semibold block">Registros confirmados</span>
        </div>

        {{-- KPI 4: OMITIDAS / ATRASADAS (1 Requiere atención) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">OMITIDAS / ATRASADAS</span>
                <i class="ph-bold ph-bell-ringing text-rose-500 text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-rose-600 font-mono">{{ $conteoOmitidas + $listaOrdenada->where('estadoHoy', 'Atrasada')->count() }}</span>
                <span class="text-[10px] font-bold text-rose-600">requiere atención</span>
            </div>
            <span class="text-[10px] text-[var(--rm-text-muted)] font-medium block">Según registros clínicos</span>
        </div>

        {{-- KPI 5: ADHERENCIA HOY (89% 8 de 9 administradas) --}}
        <div class="p-3.5 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] shadow-2xs space-y-1">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase tracking-wider">ADHERENCIA HOY</span>
                <i class="ph-bold ph-chart-donut text-blue-600 text-base"></i>
            </div>
            <div class="flex items-baseline gap-1.5 pt-0.5">
                <span class="text-2xl font-bold text-[var(--rm-action-primary)] font-mono">{{ $adherencia }}%</span>
                <span class="text-[10px] text-[var(--rm-text-muted)]">{{ $conteoAdministradas }} de {{ $totalProgramadas }} administradas</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-1.5 mt-1 overflow-hidden">
                <div class="bg-[var(--rm-action-primary)] h-1.5 rounded-full" style="width: {{ $adherencia }}%"></div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 7. FILTROS PRIORITARIOS (FOCO EN 'POR ADMINISTRAR')                       --}}
    {{-- ========================================================================= --}}
    <x-ui.filter-bar class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="rm-filter-pills flex-1">
            {{-- Por administrar --}}
            <button type="button"
                    @click="filtroPrioritario = 'por_administrar'"
                    :class="filtroPrioritario === 'por_administrar' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-bell-ringing text-xs"></i>
                <span>Por administrar</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'por_administrar' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger)]'">{{ $conteoPrioritarias }}</span>
            </button>

            {{-- Próximas --}}
            <button type="button"
                    @click="filtroPrioritario = 'proximas'"
                    :class="filtroPrioritario === 'proximas' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-clock text-xs"></i>
                <span>Próximas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'proximas' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-warning-soft)] text-[var(--rm-status-high)]'">{{ $conteoProximas }}</span>
            </button>

            {{-- Administradas --}}
            <button type="button"
                    @click="filtroPrioritario = 'administradas'"
                    :class="filtroPrioritario === 'administradas' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-check-circle text-xs"></i>
                <span>Administradas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'administradas' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-action-primary-soft)] text-[var(--rm-action-primary-ink)]'">{{ $conteoAdministradas }}</span>
            </button>

            {{-- PRN --}}
            <button type="button"
                    @click="filtroPrioritario = 'prn'"
                    :class="filtroPrioritario === 'prn' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-first-aid text-xs"></i>
                <span>PRN</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'prn' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-clinical-soft)] text-[var(--rm-clinical-strong)]'">{{ $conteoPrn }}</span>
            </button>

            {{-- Suspendidas --}}
            <button type="button"
                    @click="filtroPrioritario = 'suspendidas'"
                    :class="filtroPrioritario === 'suspendidas' ? 'is-active' : ''"
                    class="rm-filter-pill">
                <i class="ph-bold ph-prohibit text-xs"></i>
                <span>Suspendidas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold" :class="filtroPrioritario === 'suspendidas' ? 'bg-white text-[var(--rm-action-primary-ink)]' : 'bg-[var(--rm-surface)] text-[var(--rm-text-secondary)]'">{{ $conteoSuspendidas }}</span>
            </button>
        </div>

        {{-- Selector / indicador de contexto --}}
        <div class="text-xs text-[var(--rm-text-muted)] font-medium flex items-center gap-1.5">
            <i class="ph-bold ph-funnel text-[var(--rm-action-primary)]"></i>
            <span>Orden clínico: URGENCIA ASISTENCIAL</span>
        </div>
    </x-ui.filter-bar>

    {{-- ========================================================================= --}}
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
                                    <div class="h-8 w-8 rounded-lg flex items-center justify-center shrink-0 {{ $item['urgencia'] <= 2 ? 'bg-rose-100 text-rose-700' : ($item['urgencia'] === 3 ? 'bg-amber-100 text-amber-700' : ($item['urgencia'] === 5 ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700')) }}">
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
                                          :class="relojPC.evaluarHorario('{{ $item['horario_12h'] ?? $item['horario'] }}', false).sePaso ? 'bg-rose-100 text-rose-800 border-rose-300 animate-pulse' : '{{ $item['badgeColor'] }}'"
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
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                    @elseif($item['urgencia'] === 2)
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-600 animate-pulse"></span>
                                    @elseif($item['urgencia'] === 3)
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
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
                                                class="px-3 py-1.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white font-extrabold text-[11px] transition cursor-pointer shadow-2xs flex items-center gap-1">
                                            <i class="ph-bold ph-check"></i>
                                            <span>ADMINISTRAR</span>
                                        </button>
                                    @elseif($item['urgencia'] === 3)
                                        <button type="button"
                                                @click="abrirFormularioAdministrar({{ json_encode($item) }})"
                                                class="px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-[11px] transition cursor-pointer shadow-2xs flex items-center gap-1">
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
            <div class="rounded-xl border border-dashed border-[var(--rm-border)] p-5 text-center text-xs text-[var(--rm-text-muted)]">
                No existen dosis programadas para este residente.
            </div>
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
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-[var(--rm-action-primary)] text-xs font-bold border border-blue-200 self-start sm:self-auto">
                <i class="ph-bold ph-shield-check text-blue-600"></i>
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
                                        class="px-3.5 py-1.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white font-bold text-xs transition cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
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
    {{-- 11. PANEL LATERAL DERECHO (CANONICAL DRAWER - RM-DRAWER)                   --}}
    {{-- ========================================================================= --}}
    <div x-show="drawerMedAbierto"
         x-cloak
         class="relative z-50">

        {{-- Backdrop con blur --}}
        <div x-show="drawerMedAbierto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="drawerMedAbierto = false"
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="drawerMedAbierto"
                         x-transition:enter="transform transition ease-in-out duration-300 sm:duration-400"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300 sm:duration-400"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-md bg-[var(--rm-surface)] border-l border-[var(--rm-border)] shadow-2xl flex flex-col justify-between">

                        {{-- Drawer Header --}}
                        <div class="p-5 border-b border-[var(--rm-border)] bg-[var(--rm-surface-alt)]">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-[var(--rm-action-primary)]">
                                    PANEL LATERAL DE CONSULTA
                                </span>
                                <button type="button"
                                        @click="drawerMedAbierto = false"
                                        class="h-8 w-8 rounded-full flex items-center justify-center text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface)] transition">
                                    <i class="ph-bold ph-x text-base"></i>
                                </button>
                            </div>
                            <h3 class="text-base font-bold text-[var(--rm-text-title)] mt-2 uppercase tracking-tight">
                                <span x-text="drawerPaso === 'administrar' ? 'REGISTRAR ADMINISTRACIÓN' : (drawerPaso === 'justificar' ? 'JUSTIFICAR DEMORA' : 'DETALLE DE MEDICACIÓN')"></span>
                            </h3>
                            <p class="text-xs text-[var(--rm-text-muted)] mt-0.5">
                                <span x-text="drawerPaso === 'administrar' ? 'Registro asistencial de enfermería de la toma programada' : (drawerPaso === 'justificar' ? 'Registro del motivo clínico de omisión o retraso' : 'Información completa del fármaco y protocolo de seguridad')"></span>
                            </p>
                        </div>

                        {{-- Drawer Body Scrollable --}}
                        <div class="p-5 overflow-y-auto space-y-4 flex-1 text-xs">

                            {{-- BLOQUE 1: Ficha del Medicamento --}}
                            <div class="p-4 rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-blue-100 text-[var(--rm-action-primary)] flex items-center justify-center shrink-0">
                                        <i class="ph-bold ph-pill text-xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-[var(--rm-text-title)]" x-text="medSeleccionado.nombre"></h4>
                                        <p class="text-[11px] text-[var(--rm-text-muted)]" x-text="medSeleccionado.presentacion"></p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[var(--rm-border)]">
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Dosis</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="medSeleccionado.dosis"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Vía</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="medSeleccionado.via"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Horario</span>
                                        <span class="font-bold text-[var(--rm-text-title)] font-mono" x-text="medSeleccionado.horarioAmPm || medSeleccionado.horario"></span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] uppercase font-bold block">Frecuencia</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="medSeleccionado.frecuencia"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- PASO: DETALLE --}}
                            <template x-if="drawerPaso === 'detalle'">
                                <div class="space-y-4">
                                    {{-- Indicación y Prescriptor --}}
                                    <div class="space-y-2">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Indicación Clínica</span>
                                        <p class="text-xs text-[var(--rm-text-body)] p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)]" x-text="medSeleccionado.indicacion"></p>
                                        <p class="text-[11px] text-[var(--rm-text-muted)] font-medium">
                                            Prescrito por: <strong class="text-[var(--rm-text-title)]" x-text="medSeleccionado.medico"></strong>
                                        </p>
                                    </div>

                                    {{-- Precauciones y Alertas --}}
                                    <div class="p-3.5 rounded-2xl border border-amber-200 bg-amber-50/50 space-y-1.5">
                                        <div class="flex items-center gap-1.5 text-amber-900 font-bold text-[11px]">
                                            <i class="ph-bold ph-shield-warning text-amber-600 text-sm"></i>
                                            <span>PRECAUCIONES Y ALERTAS</span>
                                        </div>
                                        <p class="text-[11px] text-amber-900" x-text="medSeleccionado.precauciones"></p>
                                    </div>

                                    {{-- Documentos Relacionados --}}
                                    <div class="space-y-2 pt-1">
                                        <span class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">Documentos Relacionados</span>
                                        <div class="space-y-1.5">
                                            <div class="p-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i class="ph-bold ph-file-text text-blue-600 text-base"></i>
                                                    <span class="font-bold text-[var(--rm-text-title)] text-[11px]" x-text="medSeleccionado.documentoPlan"></span>
                                                </div>
                                                <i class="ph-bold ph-arrow-square-out text-[var(--rm-text-muted)]"></i>
                                            </div>
                                            <div class="p-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i class="ph-bold ph-file-plus text-emerald-600 text-base"></i>
                                                    <span class="font-bold text-[var(--rm-text-title)] text-[11px]" x-text="medSeleccionado.documentoNota"></span>
                                                </div>
                                                <i class="ph-bold ph-arrow-square-out text-[var(--rm-text-muted)]"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- PASO: ADMINISTRAR AHORA / FORMULARIO --}}
                            <template x-if="drawerPaso === 'administrar' || drawerPaso === 'justificar'">
                                <div class="space-y-3.5">
                                    {{-- Aviso de sólo lectura de prescripción --}}
                                    <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-[11px] flex items-center gap-2">
                                        <i class="ph-bold ph-lock-key text-blue-700 text-sm shrink-0"></i>
                                        <span>Datos prescritos por médico bloqueados para seguridad. Registre los datos de ejecución.</span>
                                    </div>

                                    {{-- Resultado de administración --}}
                                    <div>
                                        <label class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block mb-1">
                                            Resultado de la acción *
                                        </label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button"
                                                    @click="formAdmin.resultado = 'ADMINISTRADA'"
                                                    :class="formAdmin.resultado === 'ADMINISTRADA' ? 'bg-emerald-600 text-white font-bold' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] font-bold border border-[var(--rm-border)]'"
                                                    class="py-2 px-3 rounded-xl text-center transition cursor-pointer text-xs">
                                                ✓ Administrada
                                            </button>
                                            <button type="button"
                                                    @click="formAdmin.resultado = 'OMITIDA'"
                                                    :class="formAdmin.resultado === 'OMITIDA' ? 'bg-amber-600 text-white font-bold' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-title)] font-bold border border-[var(--rm-border)]'"
                                                    class="py-2 px-3 rounded-xl text-center transition cursor-pointer text-xs">
                                                ✕ Omitida / Justificada
                                            </button>
                                        </div>
                                    </div>

                                                                        {{-- Hora real --}}
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block">
                                                Hora de administración real *
                                            </label>
                                            <button type="button"
                                                    @click="formAdmin.horaReal = new Date().toTimeString().slice(0,5)"
                                                    class="text-[10px] font-bold text-[var(--rm-action-primary)] hover:underline flex items-center gap-1 cursor-pointer">
                                                <i class="ph-bold ph-clock"></i>
                                                <span>Usar hora actual (<span x-text="relojPC.horaCorta"></span>)</span>
                                            </button>
                                        </div>
                                        <input type="time"
                                               x-model="formAdmin.horaReal"
                                               class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] px-3 py-2 text-xs font-bold text-[var(--rm-text-title)]">
                                    </div>

                                    {{-- Motivo de omisión si aplica --}}
                                    <template x-if="formAdmin.resultado === 'OMITIDA'">
                                        <div>
                                            <label class="text-[10.5px] font-bold uppercase text-amber-800 tracking-wider block mb-1">
                                                Motivo justificado de omisión / demora *
                                            </label>
                                            <textarea x-model="formAdmin.motivoOmision"
                                                      rows="2"
                                                      placeholder="Indique la causa clínica o asistencial..."
                                                      class="w-full rounded-xl border border-amber-300 bg-amber-50/50 p-2.5 text-xs text-[var(--rm-text-title)]"></textarea>
                                        </div>
                                    </template>

                                    {{-- Observaciones clínicas --}}
                                    <div>
                                        <label class="text-[10.5px] font-bold uppercase text-[var(--rm-text-muted)] tracking-wider block mb-1">
                                            Observaciones asistenciales
                                        </label>
                                        <textarea x-model="formAdmin.observaciones"
                                                  rows="2"
                                                  placeholder="Tolerancia, ingesta hídrica, signos asociados..."
                                                  class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] p-2.5 text-xs text-[var(--rm-text-title)]"></textarea>
                                    </div>

                                    {{-- Checklist de verificación de enfermería --}}
                                    <div class="p-3 rounded-xl bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] space-y-1.5">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" x-model="formAdmin.checklistVerificado" class="rounded text-[var(--rm-action-primary)] focus:ring-0">
                                            <span class="text-[11px] font-bold text-[var(--rm-text-title)]">Verificación de 5 correctos de enfermería</span>
                                        </label>
                                        <p class="text-[10px] text-[var(--rm-text-muted)] pl-5">
                                            Residente correcto, fármaco correcto, dosis correcta, vía correcta y horario verificado.
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Drawer Footer Fijo --}}
                        <div class="p-4 border-t border-[var(--rm-border)] bg-[var(--rm-surface-alt)] flex items-center justify-between gap-3">
                            <button type="button"
                                    @click="drawerMedAbierto = false"
                                    class="px-4 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] hover:bg-[var(--rm-surface-alt)] text-xs font-bold text-[var(--rm-text-title)] transition cursor-pointer">
                                Cerrar
                            </button>

                            <template x-if="drawerPaso === 'detalle'">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            @click="drawerPaso = 'justificar'"
                                            class="px-3 py-2.5 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface)] text-xs font-bold text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] transition cursor-pointer">
                                        Justificar demora
                                    </button>
                                    <button type="button"
                                            @click="drawerPaso = 'administrar'"
                                            class="px-4 py-2.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white text-xs font-bold transition cursor-pointer shadow-sm flex items-center gap-1.5">
                                        <i class="ph-bold ph-check"></i>
                                        <span>ADMINISTRAR AHORA</span>
                                    </button>
                                </div>
                            </template>

                            <template x-if="drawerPaso === 'administrar' || drawerPaso === 'justificar'">
                                <button type="button"
                                        @click="confirmarAdministracion()"
                                        class="px-5 py-2.5 rounded-xl bg-[var(--rm-action-primary)] hover:bg-[var(--rm-action-primary-hover)] text-white text-xs font-bold transition cursor-pointer shadow-sm flex items-center gap-1.5">
                                    <i class="ph-bold ph-check-circle"></i>
                                    <span>CONFIRMAR REGISTRO</span>
                                </button>
                            </template>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL INDICACIONES GENERALES DE ADMINISTRACIÓN                            --}}
    {{-- ========================================================================= --}}
    <div x-show="modalIndicaciones"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-[var(--rm-action-primary)] border border-blue-200">
                        <i class="ph-bold ph-info text-lg"></i>
                    </span>
                    <h3 class="text-sm font-bold text-[var(--rm-text-title)] uppercase">
                        Indicaciones Generales de Medicación
                    </h3>
                </div>
                <button type="button"
                        @click="modalIndicaciones = false"
                        class="h-7 w-7 rounded-full flex items-center justify-center text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)]">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs text-[var(--rm-text-body)]">
                <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-200 text-blue-900">
                    <p class="font-bold">Protocolo Institucional RememberMind:</p>
                    <p class="text-[11px] mt-1">El personal de enfermería administra estrictamente los tratamientos prescritos en el expediente clínico del residente. Toda omisión o rechazo debe justificarse en el sistema.</p>
                </div>

                <div class="space-y-2">
                    <h4 class="font-bold text-[var(--rm-text-title)] text-[11.5px] uppercase">Reglas de Seguridad:</h4>
                    <ul class="space-y-1.5 list-disc pl-4 text-[11px] text-[var(--rm-text-body)]">
                        <li>Verificar la identidad del residente mediante doble comprobación antes de cualquier toma.</li>
                        <li>Verificar la ausencia de alergias registradas en la cabecera clínica.</li>
                        <li>Registrar la administración inmediatamente después de completada.</li>
                        <li>En medicamentos PRN, comprobar el intervalo horario y registrar la intensidad de síntoma previo.</li>
                    </ul>
                </div>
            </div>

            <div class="flex justify-end pt-2 border-t border-[var(--rm-border)]">
                <button type="button"
                        @click="modalIndicaciones = false"
                        class="px-4 py-2 rounded-xl bg-[var(--rm-action-primary)] text-white text-xs font-bold hover:bg-[var(--rm-action-primary-hover)] transition">
                    Entendido
                </button>
            </div>
        </div>
    </div>

</div>
