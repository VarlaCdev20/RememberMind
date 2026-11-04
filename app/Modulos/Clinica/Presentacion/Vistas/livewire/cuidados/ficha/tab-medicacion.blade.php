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
            $badgeColor = 'bg-[var(--rm-info-soft)] text-[var(--rm-info-strong)] border-[var(--rm-info-strong)]';
            $filaColor = 'bg-[var(--rm-info-soft)] hover:bg-[var(--rm-info-soft)]';

            if ($registroHoy || ($ultAdmin && ($ultAdmin->administrado ?? false))) {
                $urgencia = 5; // ADMINISTRADA
                $estadoHoy = 'Administrada';
                $minutosBadge = $ultAdmin?->fecha_hora_administracion ? 'Administrada ' . $ultAdmin->fecha_hora_administracion->format('H:i') : 'Administrada';
                $badgeColor = 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border-[var(--rm-success-strong)]';
                $filaColor = 'bg-[var(--rm-success-soft)] hover:bg-[var(--rm-success-soft)]';
            } elseif (in_array(strtoupper($m->estado ?? ''), ['SUSPENDIDA', 'SUSPENDIDO', 'INACTIVO'])) {
                $urgencia = 6; // SUSPENDIDA
                $estadoHoy = 'Suspendida';
                $minutosBadge = 'Tratamiento suspendido';
                $badgeColor = 'bg-[var(--rm-surface-soft)] text-[var(--rm-text-primary)] border-[var(--rm-border)]';
                $filaColor = 'bg-[var(--rm-surface)] hover:bg-[var(--rm-surface)] opacity-80';
            } elseif (in_array(strtoupper((string)($proximaToma['estado'] ?? '')), ['VENCIDA', 'RETRASADA'], true)) {
                $urgencia = 1;
                $estadoHoy = 'Atrasada';
                $minutosBadge = 'Dosis atrasada';
                $badgeColor = 'bg-[var(--rm-danger-soft)] text-[var(--rm-danger-strong)] border-[var(--rm-danger-strong)]';
                $filaColor = 'bg-[var(--rm-danger-soft)] hover:bg-[var(--rm-danger-soft)]';
            } elseif (strtoupper((string)($proximaToma['estado'] ?? '')) === 'PROXIMA') {
                $urgencia = 3;
                $estadoHoy = 'Próxima';
                $minutosBadge = 'Próxima dosis';
                $badgeColor = 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border-[var(--rm-warning-strong)]';
                $filaColor = 'bg-[var(--rm-warning-soft)] hover:bg-[var(--rm-warning-soft)]';
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
                'badgeClass' => $esAdm ? 'bg-[var(--rm-success-soft)] text-[var(--rm-success-strong)] border-[var(--rm-success-strong)]' : 'bg-[var(--rm-warning-soft)] text-[var(--rm-warning-strong)] border-[var(--rm-warning-strong)]',
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
    @include('clinica::livewire.cuidados.ficha.medicacion.resumen')
    @include('clinica::livewire.cuidados.ficha.medicacion.listado')
    @include('clinica::livewire.cuidados.ficha.medicacion.detalle')
    {{-- MODAL INDICACIONES GENERALES DE ADMINISTRACIÓN                            --}}
    {{-- ========================================================================= --}}
    <div x-show="modalIndicaciones"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-[var(--rm-modal-overlay)] backdrop-blur-xs p-4">
        <div class="w-full max-w-lg rounded-3xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-[var(--rm-border)] pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--rm-info-soft)] text-[var(--rm-action-primary)] border border-[var(--rm-info-strong)]">
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
                <div class="p-3 rounded-xl bg-[var(--rm-info-soft)] border border-[var(--rm-info-strong)] text-[var(--rm-info-strong)]">
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
                        class="px-4 py-2 rounded-xl bg-[var(--rm-action-primary)] text-[var(--rm-text-on-primary)] text-xs font-bold hover:bg-[var(--rm-action-primary-hover)] transition">
                    Entendido
                </button>
            </div>
        </div>
    </div>

</div>
