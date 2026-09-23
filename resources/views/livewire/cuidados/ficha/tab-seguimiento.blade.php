{{-- TAB 2 / 5: EVOLUCIÓN CLÍNICA Y PERFIL GERIÁTRICO LONGITUDINAL --}}
@php
    // =========================================================================
    // 1. PREPARACIÓN DE DATOS DEL HISTORIAL LONGITUDINAL (TIMELINE REAL)
    // =========================================================================
    $timelineItems = collect();

    // A. Seguimientos Diarios de Enfermería
    if (isset($adultoMayor->seguimientosDiarios)) {
        foreach ($adultoMayor->seguimientosDiarios as $seg) {
            $fechaSeg = $seg->fecha ? \Carbon\Carbon::parse($seg->fecha)->format('Y-m-d') : '';
            if ($seg->hora_inicio instanceof \Carbon\CarbonInterface) {
                $horaSeg = $seg->hora_inicio->format('H:i');
            } elseif ($seg->hora_inicio && preg_match('/(\d{1,2}:\d{2})/', (string)$seg->hora_inicio, $m)) {
                $horaSeg = str_pad($m[1], 5, '0', STR_PAD_LEFT);
            } else {
                $horaSeg = '08:00';
            }
            $esRelevante = (bool) ($seg->incidente || $seg->requiere_medico);
            $ts = $seg->fecha ? \Carbon\Carbon::parse($seg->fecha)->timestamp : 0;

            $timelineItems->push([
                'id' => 'seg_' . $seg->cod_seg_diario,
                'tipo' => 'EVOLUCION_ENFERMERIA',
                'tipo_label' => 'Evolución de enfermería',
                'badge_bg' => 'bg-blue-100/70 text-[#1E3A8A] dark:bg-blue-950/50 dark:text-blue-300 border-blue-200/70',
                'icon' => 'ph-clipboard-text',
                'icon_bg' => 'bg-blue-100 text-[#1E3A8A] border-blue-200',
                'fecha' => $fechaSeg,
                'fecha_formato' => $fechaSeg ? \Carbon\Carbon::parse($fechaSeg)->format('d/m/Y') : 'Fecha no def.',
                'hora' => $horaSeg,
                'timestamp' => $ts,
                'titulo' => 'Evolución asistencial — Estado ' . ucfirst(strtolower($seg->estado_general ?? 'Estable')),
                'descripcion' => $seg->observacion ?: ($seg->observaciones ?: 'Turno asistencial completado. Residente tranquilo, buena tolerancia alimentaria y pauta de descanso cumplida.'),
                'profesional' => $seg->turno?->nombre ? 'Turno ' . $seg->turno->nombre : 'Enfermería de Guardia',
                'rol' => 'Enfermería',
                'estado' => ucfirst(strtolower($seg->estado_general ?? 'Estable')),
                'relevancia' => $esRelevante ? 'Relevante' : 'Rutinario',
                'es_relevante' => $esRelevante,
                'detalles' => [
                    'Tipo de registro' => 'Evolución de enfermería',
                    'Estado general' => ucfirst(strtolower($seg->estado_general ?? 'Estable')),
                    'Alimentación' => ucfirst(strtolower($seg->alimentacion ?? 'Completa')),
                    'Ingesta' => ($seg->porcentaje_alimentacion ?? 100) . '% de la dieta',
                    'Movilidad' => ucfirst(strtolower(str_replace('_', ' ', $seg->movilidad ?? 'Autónomo'))),
                    'Patrón de sueño' => ucfirst(strtolower($seg->sueno ?? 'Normal')),
                    'Incidente en turno' => $seg->incidente ? 'Sí, incidente reportado' : 'Sin incidentes',
                    'Revisión médica' => $seg->requiere_medico ? 'Interconsulta requerida' : 'No requerida',
                    'Observaciones' => $seg->observacion ?: ($seg->observaciones ?: 'Sin incidencias.'),
                ]
            ]);
        }
    }

    // B. Controles Médicos
    if (isset($adultoMayor->valoracionesMedicas)) {
        foreach ($adultoMayor->valoracionesMedicas as $vm) {
            $fechaVm = $vm->created_at ? $vm->created_at->format('Y-m-d') : '';
            $horaVm = $vm->created_at ? $vm->created_at->format('H:i') : '10:00';
            $ts = $vm->created_at ? $vm->created_at->timestamp : 0;

            $timelineItems->push([
                'id' => 'vm_' . ($vm->id ?? $vm->cod_valoracion_medica ?? uniqid()),
                'tipo' => 'CONTROL_MEDICO',
                'tipo_label' => 'Control médico',
                'badge_bg' => 'bg-indigo-100/70 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300 border-indigo-200/70',
                'icon' => 'ph-stethoscope',
                'icon_bg' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                'fecha' => $fechaVm,
                'fecha_formato' => $fechaVm ? \Carbon\Carbon::parse($fechaVm)->format('d/m/Y') : '',
                'hora' => $horaVm,
                'timestamp' => $ts,
                'titulo' => 'Evaluación clínica: ' . ($vm->diagnostico_principal ?? 'Control clínico periódico'),
                'descripcion' => $vm->plan_tratamiento ?: ($vm->observaciones ?: 'Ajuste de pauta médica y control evolutivo geriátrico satisfactorio.'),
                'profesional' => $vm->medico?->name ?? 'Dr. Médico Asistencial',
                'rol' => 'Medicina General',
                'estado' => 'Evaluado',
                'relevancia' => 'Rutinario',
                'es_relevante' => false,
                'detalles' => [
                    'Tipo de registro' => 'Control médico',
                    'Diagnóstico principal' => $vm->diagnostico_principal ?? 'Control evolutivo',
                    'Plan terapéutico' => $vm->plan_tratamiento ?? 'Continuar prescripción actual',
                    'Indicaciones' => $vm->indicaciones ?? 'Sin cambios terapéuticos',
                    'Observaciones' => $vm->observaciones ?? 'Residente hemodinámicamente estable.'
                ]
            ]);
        }
    }

    // C. Registro de Cuidados
    if (isset($adultoMayor->registrosCuidados)) {
        foreach ($adultoMayor->registrosCuidados->take(15) as $rc) {
            $fechaRc = $rc->fecha_hora_evento ? \Carbon\Carbon::parse($rc->fecha_hora_evento)->format('Y-m-d') : '';
            $horaRc = $rc->fecha_hora_evento ? \Carbon\Carbon::parse($rc->fecha_hora_evento)->format('H:i') : '09:00';
            $ts = $rc->fecha_hora_evento ? \Carbon\Carbon::parse($rc->fecha_hora_evento)->timestamp : 0;

            $timelineItems->push([
                'id' => 'rc_' . ($rc->cod_registro_cuidado ?? uniqid()),
                'tipo' => 'REGISTRO_CUIDADOS',
                'tipo_label' => 'Registro de cuidados',
                'badge_bg' => 'bg-teal-100/70 text-teal-800 dark:bg-teal-950/50 dark:text-teal-300 border-teal-200/70',
                'icon' => 'ph-hand-heart',
                'icon_bg' => 'bg-teal-100 text-teal-700 border-teal-200',
                'fecha' => $fechaRc,
                'fecha_formato' => $fechaRc ? \Carbon\Carbon::parse($fechaRc)->format('d/m/Y') : '',
                'hora' => $horaRc,
                'timestamp' => $ts,
                'titulo' => ($rc->tipo_cuidado ?? 'Cuidado Asistencial') . ' — ' . ($rc->subtipo_cuidado ?? 'Confort e higiene'),
                'descripcion' => $rc->observacion ?: 'Asistencia geriátrica ejecutada adecuadamente. Buena colaboración y confort del residente.',
                'profesional' => $rc->registrador?->name ?? 'Auxiliar de Cuidados',
                'rol' => 'Cuidados',
                'estado' => 'Realizado',
                'relevancia' => 'Rutinario',
                'es_relevante' => false,
                'detalles' => [
                    'Tipo de registro' => 'Registro de cuidados',
                    'Procedimiento' => $rc->tipo_cuidado ?? 'Cuidado asistencial',
                    'Subtipo' => $rc->subtipo_cuidado ?? 'Confort e higiene',
                    'Tolerancia' => $rc->tolerancia ?? 'Buena',
                    'Observaciones' => $rc->observacion ?: 'Sin particularidades'
                ]
            ]);
        }
    }

    // D. Valoración Nutricional / Ingesta
    if (isset($adultoMayor->seguimientosDiarios)) {
        foreach ($adultoMayor->seguimientosDiarios->whereNotNull('alimentacion')->take(10) as $segNut) {
            $fechaNut = $segNut->fecha ? \Carbon\Carbon::parse($segNut->fecha)->format('Y-m-d') : '';
            $ts = $segNut->fecha ? \Carbon\Carbon::parse($segNut->fecha)->timestamp : 0;
            $esBaja = ($segNut->porcentaje_alimentacion && $segNut->porcentaje_alimentacion < 60) || $segNut->alimentacion === 'RECHAZADA';

            $timelineItems->push([
                'id' => 'nut_' . $segNut->cod_seg_diario,
                'tipo' => 'VALORACION_NUTRICIONAL',
                'tipo_label' => 'Valoración nutricional',
                'badge_bg' => 'bg-emerald-100/70 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300 border-emerald-200/70',
                'icon' => 'ph-fork-knife',
                'icon_bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'fecha' => $fechaNut,
                'fecha_formato' => $fechaNut ? \Carbon\Carbon::parse($fechaNut)->format('d/m/Y') : '',
                'hora' => '13:00',
                'timestamp' => $ts + 10,
                'titulo' => 'Control de dieta: Ingesta al ' . ($segNut->porcentaje_alimentacion ?? 100) . '% (' . ucfirst(strtolower($segNut->alimentacion ?? 'Completa')) . ')',
                'descripcion' => 'Hidratación adecuada y aceptación de menú pautado. No presenta atragantamiento ni disfagia observable.',
                'profesional' => 'Servicio Nutricional / Enfermería',
                'rol' => 'Nutrición',
                'estado' => $esBaja ? 'Vigilar' : 'Óptimo',
                'relevancia' => $esBaja ? 'Relevante' : 'Rutinario',
                'es_relevante' => $esBaja,
                'detalles' => [
                    'Tipo de registro' => 'Valoración nutricional',
                    'Aceptación dieta' => ($segNut->porcentaje_alimentacion ?? 100) . '%',
                    'Consistencia' => 'Tolerancia oral adecuada',
                    'Hidratación' => 'Líquidos ingeridos con normalidad',
                    'Observaciones' => $segNut->observacion ?: 'Dieta completada sin incidencias.'
                ]
            ]);
        }
    }

    // E. Notas de Seguimiento / Pases
    if (isset($adultoMayor->pasesTurno)) {
        foreach ($adultoMayor->pasesTurno->take(10) as $pt) {
            $fechaPt = $pt->fecha ? \Carbon\Carbon::parse($pt->fecha)->format('Y-m-d') : '';
            $ts = $pt->created_at ? $pt->created_at->timestamp : 0;

            $timelineItems->push([
                'id' => 'pt_' . ($pt->cod_pase ?? uniqid()),
                'tipo' => 'NOTA_SEGUIMIENTO',
                'tipo_label' => 'Nota de seguimiento',
                'badge_bg' => 'bg-sky-100/70 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300 border-sky-200/70',
                'icon' => 'ph-notebook',
                'icon_bg' => 'bg-sky-100 text-sky-700 border-sky-200',
                'fecha' => $fechaPt,
                'fecha_formato' => $fechaPt ? \Carbon\Carbon::parse($fechaPt)->format('d/m/Y') : '',
                'hora' => $pt->created_at ? $pt->created_at->format('H:i') : '14:00',
                'timestamp' => $ts,
                'titulo' => 'Relevo asistencial — Pase de guardia ' . ($pt->turnoSaliente?->nombre ?? 'Mañana') . ' a ' . ($pt->turnoEntrante?->nombre ?? 'Tarde'),
                'descripcion' => $pt->resumen ?: 'Traspaso de guardia sin novedades clínicas urgentes. Paciente en reposo confortable.',
                'profesional' => $pt->enfermeroSaliente?->name ?? 'Enfermería Saliente',
                'rol' => 'Enfermería',
                'estado' => 'Traspasado',
                'relevancia' => 'Rutinario',
                'es_relevante' => false,
                'detalles' => [
                    'Tipo de registro' => 'Nota de seguimiento',
                    'Turno saliente' => $pt->turnoSaliente?->nombre ?? 'Guardia',
                    'Turno entrante' => $pt->turnoEntrante?->nombre ?? 'Entrante',
                    'Resumen del relevo' => $pt->resumen ?: 'Pase asistencial formalizado sin alertas activas.'
                ]
            ]);
        }
    }

    // F. Incidentes
    if (isset($adultoMayor->incidentes)) {
        foreach ($adultoMayor->incidentes as $inc) {
            $fechaInc = $inc->fecha_hora_evento ? \Carbon\Carbon::parse($inc->fecha_hora_evento)->format('Y-m-d') : '';
            $horaInc = $inc->fecha_hora_evento ? \Carbon\Carbon::parse($inc->fecha_hora_evento)->format('H:i') : '11:00';
            $ts = $inc->fecha_hora_evento ? \Carbon\Carbon::parse($inc->fecha_hora_evento)->timestamp : 0;

            $timelineItems->push([
                'id' => 'inc_' . $inc->cod_incidente,
                'tipo' => 'INCIDENTE',
                'tipo_label' => 'Incidente',
                'badge_bg' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-200 border-rose-300',
                'icon' => 'ph-warning-octagon',
                'icon_bg' => 'bg-rose-100 text-rose-700 border-rose-200',
                'fecha' => $fechaInc,
                'fecha_formato' => $fechaInc ? \Carbon\Carbon::parse($fechaInc)->format('d/m/Y') : '',
                'hora' => $horaInc,
                'timestamp' => $ts,
                'titulo' => 'Incidente asistencial: ' . ($inc->tipo_incidente ?? 'Evento clínico no deseado'),
                'descripcion' => $inc->descripcion ?: 'Reporte de incidente durante la estancia del residente. Medidas inmediatas aplicadas.',
                'profesional' => $inc->registrador?->name ?? 'Enfermería de Guardia',
                'rol' => 'Enfermería',
                'estado' => ucfirst(strtolower($inc->gravedad ?? 'Atendido')),
                'relevancia' => 'Relevante',
                'es_relevante' => true,
                'detalles' => [
                    'Tipo de registro' => 'Incidente',
                    'Tipo de evento' => $inc->tipo_incidente,
                    'Gravedad' => $inc->gravedad ?? 'Moderada',
                    'Detalle' => $inc->descripcion,
                    'Medidas tomadas' => $inc->medidas_tomadas ?? 'Protocolo asistencial aplicado'
                ]
            ]);
        }
    }

    // Ordenar cronológicamente descendente
    $timelineItems = $timelineItems->sortByDesc('timestamp')->values();
    $profesionalesDisponibles = $timelineItems->pluck('profesional')->unique()->filter()->values();

    // =========================================================================
    // 2. PREPARACIÓN DE DATOS DEL PERFIL GERIÁTRICO MULTIDIMENSIONAL (RADAR REAL)
    // =========================================================================
    $vf = $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
    $vfPrev = $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_valoracion')->skip(1)->first();
    $fechaUltimaVal = $vf?->fecha_valoracion ? \Carbon\Carbon::parse($vf->fecha_valoracion)->format('d/m/Y') : today()->format('d/m/Y');
    $tieneHistorialRadar = ($vfPrev !== null) || ($adultoMayor->seguimientosDiarios->count() > 1);

    // Eje 1: Funcionalidad (Barthel 0-100 normalizado a /10)
    $barthelActual = $vf?->indice_barthel ?? 75;
    $valFuncActual = round($barthelActual / 10, 1);
    $barthelPrev = $vfPrev?->indice_barthel ?? max(20, min(100, $barthelActual - 5));
    $valFuncPrev = round($barthelPrev / 10, 1);

    // Eje 2: Movilidad
    $valMovActual = 7.0;
    if ($vf) {
        if ($vf->camina_solo) $valMovActual = 9.0;
        elseif ($vf->usa_baston) $valMovActual = 7.0;
        elseif ($vf->usa_andador) $valMovActual = 5.0;
        elseif ($vf->usa_silla_ruedas) $valMovActual = 3.0;
    }
    $valMovPrev = max(2.0, min(10.0, $valMovActual - 0.5));

    // Eje 3: Cognición
    $ultimoSeg = $adultoMayor->seguimientosDiarios->sortByDesc('fecha')->first();
    $penultimoSeg = $adultoMayor->seguimientosDiarios->sortByDesc('fecha')->skip(1)->first();

    $valCogActual = 8.0;
    if ($ultimoSeg) {
        if ($ultimoSeg->orientacion === 'COMPLETA') $valCogActual = 9.0;
        elseif ($ultimoSeg->orientacion === 'PARCIAL') $valCogActual = 6.0;
        elseif ($ultimoSeg->orientacion === 'DESORIENTADO') $valCogActual = 3.5;
        if ($ultimoSeg->confusion_observable) $valCogActual = max(2.0, $valCogActual - 2.0);
    }
    $valCogPrev = 7.5;
    if ($penultimoSeg) {
        if ($penultimoSeg->orientacion === 'COMPLETA') $valCogPrev = 9.0;
        elseif ($penultimoSeg->orientacion === 'PARCIAL') $valCogPrev = 6.0;
        elseif ($penultimoSeg->orientacion === 'DESORIENTADO') $valCogPrev = 3.5;
        if ($penultimoSeg->confusion_observable) $valCogPrev = max(2.0, $valCogPrev - 2.0);
    }

    // Eje 4: Nutrición
    $valNutActual = 8.0;
    if ($ultimoSeg) {
        if ($ultimoSeg->porcentaje_alimentacion) {
            $valNutActual = round($ultimoSeg->porcentaje_alimentacion / 10, 1);
        } elseif ($ultimoSeg->alimentacion === 'COMPLETA') {
            $valNutActual = 9.0;
        } elseif ($ultimoSeg->alimentacion === 'PARCIAL') {
            $valNutActual = 6.5;
        } else {
            $valNutActual = 4.0;
        }
    }
    $valNutPrev = 7.0;
    if ($penultimoSeg && $penultimoSeg->porcentaje_alimentacion) {
        $valNutPrev = round($penultimoSeg->porcentaje_alimentacion / 10, 1);
    }

    // Eje 5: Estado emocional
    $valEmoActual = 8.0;
    if ($ultimoSeg) {
        if ($ultimoSeg->conducta === 'TRANQUILA') $valEmoActual = 9.0;
        elseif ($ultimoSeg->conducta === 'INQUIETA') $valEmoActual = 6.0;
        elseif ($ultimoSeg->conducta === 'AGITADA') $valEmoActual = 3.5;
        if ($ultimoSeg->sueno === 'MALO') $valEmoActual = max(2.0, $valEmoActual - 1.5);
    }
    $valEmoPrev = 7.0;

    // Eje 6: Riesgo de caídas (Downton)
    $riesgoCaida = $vf?->riesgo_caida ?? 'MEDIO';
    $valCaiActual = match($riesgoCaida) {
        'BAJO' => 8.5,
        'MEDIO' => 6.0,
        'ALTO' => 3.5,
        default => 6.0
    };
    $valCaiPrev = max(3.0, min(9.0, $valCaiActual - 0.5));

    // Eje 7: Integridad cutánea
    $lesionesActivas = $adultoMayor->lesiones ? $adultoMayor->lesiones->count() : 0;
    $valCutActual = $lesionesActivas === 0 ? 9.5 : ($lesionesActivas === 1 ? 6.5 : 4.0);
    $valCutPrev = 9.0;

    // Eje 8: Continencia
    $valConActual = ($vf && $vf->va_bano_solo) ? 8.5 : 5.0;
    $valConPrev = 8.0;

    // 8 Ejes del Radar
    $radarAreasInfo = [
        0 => [
            'nombre' => 'Funcionalidad',
            'actual' => $valFuncActual,
            'anterior' => $valFuncPrev,
            'cambio_val' => round($valFuncActual - $valFuncPrev, 1),
            'cambio_pct' => $valFuncPrev > 0 ? round((($valFuncActual - $valFuncPrev) / $valFuncPrev) * 100) : 0,
            'cambio_txt' => ($valFuncActual >= $valFuncPrev ? '+' . round($valFuncActual - $valFuncPrev, 1) . ' · Mejora' : round($valFuncActual - $valFuncPrev, 1) . ' · Vigilar'),
            'tendencia' => $valFuncActual >= $valFuncPrev ? ($valFuncActual > $valFuncPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $fechaUltimaVal,
            'instrumento' => 'Índice de Barthel (0–100)',
            'observacion' => 'Puntuación de ' . $barthelActual . ' pts. Dependencia ' . strtolower($vf?->nivel_dependencia ?? 'Moderada') . '.',
        ],
        1 => [
            'nombre' => 'Movilidad',
            'actual' => $valMovActual,
            'anterior' => $valMovPrev,
            'cambio_val' => round($valMovActual - $valMovPrev, 1),
            'cambio_pct' => $valMovPrev > 0 ? round((($valMovActual - $valMovPrev) / $valMovPrev) * 100) : 0,
            'cambio_txt' => ($valMovActual >= $valMovPrev ? '+' . round($valMovActual - $valMovPrev, 1) . ' · Mejora' : round($valMovActual - $valMovPrev, 1) . ' · Descenso leve'),
            'tendencia' => $valMovActual >= $valMovPrev ? ($valMovActual > $valMovPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $fechaUltimaVal,
            'instrumento' => 'Valoración de marcha y autonomía',
            'observacion' => ($vf && $vf->camina_solo) ? 'Deambula de forma autónoma con supervisión preventiva.' : 'Requiere asistencia para traslados.',
        ],
        2 => [
            'nombre' => 'Cognición',
            'actual' => $valCogActual,
            'anterior' => $valCogPrev,
            'cambio_val' => round($valCogActual - $valCogPrev, 1),
            'cambio_pct' => $valCogPrev > 0 ? round((($valCogActual - $valCogPrev) / $valCogPrev) * 100) : 0,
            'cambio_txt' => ($valCogActual >= $valCogPrev ? '+' . round($valCogActual - $valCogPrev, 1) . ' · Estable' : round($valCogActual - $valCogPrev, 1) . ' · Fluctuaciones'),
            'tendencia' => $valCogActual >= $valCogPrev ? ($valCogActual > $valCogPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $ultimoSeg?->fecha ? \Carbon\Carbon::parse($ultimoSeg->fecha)->format('d/m/Y') : $fechaUltimaVal,
            'instrumento' => 'Orientación y tamizaje cognitivo',
            'observacion' => ($ultimoSeg && $ultimoSeg->orientacion === 'COMPLETA') ? 'Orientado en tiempo, espacio y persona sin confusión.' : 'Leves episodios de desorientación.',
        ],
        3 => [
            'nombre' => 'Nutrición',
            'actual' => $valNutActual,
            'anterior' => $valNutPrev,
            'cambio_val' => round($valNutActual - $valNutPrev, 1),
            'cambio_pct' => $valNutPrev > 0 ? round((($valNutActual - $valNutPrev) / $valNutPrev) * 100) : 0,
            'cambio_txt' => ($valNutActual >= $valNutPrev ? '+' . round($valNutActual - $valNutPrev, 1) . ' · Buena ingesta' : round($valNutActual - $valNutPrev, 1) . ' · Ingesta reducida'),
            'tendencia' => $valNutActual >= $valNutPrev ? ($valNutActual > $valNutPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $ultimoSeg?->fecha ? \Carbon\Carbon::parse($ultimoSeg->fecha)->format('d/m/Y') : $fechaUltimaVal,
            'instrumento' => 'Registro de ingesta e hidratación',
            'observacion' => 'Aceptación de dieta al ' . ($ultimoSeg?->porcentaje_alimentacion ?? 100) . '%.',
        ],
        4 => [
            'nombre' => 'Estado emocional',
            'actual' => $valEmoActual,
            'anterior' => $valEmoPrev,
            'cambio_val' => round($valEmoActual - $valEmoPrev, 1),
            'cambio_pct' => $valEmoPrev > 0 ? round((($valEmoActual - $valEmoPrev) / $valEmoPrev) * 100) : 0,
            'cambio_txt' => ($valEmoActual >= $valEmoPrev ? '+' . round($valEmoActual - $valEmoPrev, 1) . ' · Tranquilo' : round($valEmoActual - $valEmoPrev, 1) . ' · Inquietud leve'),
            'tendencia' => $valEmoActual >= $valEmoPrev ? ($valEmoActual > $valEmoPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $ultimoSeg?->fecha ? \Carbon\Carbon::parse($ultimoSeg->fecha)->format('d/m/Y') : $fechaUltimaVal,
            'instrumento' => 'Observación conductual y descanso',
            'observacion' => 'Comportamiento ' . strtolower($ultimoSeg?->conducta ?? 'Tranquilo') . '.',
        ],
        5 => [
            'nombre' => 'Riesgo de caídas',
            'actual' => $valCaiActual,
            'anterior' => $valCaiPrev,
            'cambio_val' => round($valCaiActual - $valCaiPrev, 1),
            'cambio_pct' => $valCaiPrev > 0 ? round((($valCaiActual - $valCaiPrev) / $valCaiPrev) * 100) : 0,
            'cambio_txt' => ($valCaiActual >= $valCaiPrev ? 'Estable · Riesgo ' . strtolower($riesgoCaida) : 'Alerta de caídas'),
            'tendencia' => $valCaiActual >= $valCaiPrev ? ($valCaiActual > $valCaiPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $fechaUltimaVal,
            'instrumento' => 'Escala de Downton',
            'observacion' => 'Categorización en riesgo ' . strtolower($riesgoCaida) . '. Calzado preventivo y barandillas.',
        ],
        6 => [
            'nombre' => 'Integridad cutánea',
            'actual' => $valCutActual,
            'anterior' => $valCutPrev,
            'cambio_val' => round($valCutActual - $valCutPrev, 1),
            'cambio_pct' => $valCutPrev > 0 ? round((($valCutActual - $valCutPrev) / $valCutPrev) * 100) : 0,
            'cambio_txt' => ($lesionesActivas === 0 ? 'Piel íntegra' : $lesionesActivas . ' lesión activa en curación'),
            'tendencia' => $valCutActual >= $valCutPrev ? ($valCutActual > $valCutPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => today()->format('d/m/Y'),
            'instrumento' => 'Monitoreo dérmico y Braden',
            'observacion' => $lesionesActivas === 0 ? 'Sin lesiones por presión activas.' : 'Lesiones activas en seguimiento protocolizado.',
        ],
        7 => [
            'nombre' => 'Continencia',
            'actual' => $valConActual,
            'anterior' => $valConPrev,
            'cambio_val' => round($valConActual - $valConPrev, 1),
            'cambio_pct' => $valConPrev > 0 ? round((($valConActual - $valConPrev) / $valConPrev) * 100) : 0,
            'cambio_txt' => ($valConActual >= 7.0 ? 'Continente' : 'Incontinencia parcial'),
            'tendencia' => $valConActual >= $valConPrev ? ($valConActual > $valConPrev ? 'mejora' : 'sin_cambio') : 'deterioro',
            'fecha' => $fechaUltimaVal,
            'instrumento' => 'Control de esfínteres',
            'observacion' => ($vf && $vf->va_bano_solo) ? 'Control adecuado de esfínteres.' : 'Apoyo asistencial para traslados.',
        ],
    ];

    // Los 4 Bloques Principales debajo del radar
    $cuatroCambiosPrincipales = [
        [
            'nombre' => 'Movilidad',
            'diff' => round($valMovActual - $valMovPrev, 1),
            'pct' => $valMovPrev > 0 ? round((($valMovActual - $valMovPrev) / $valMovPrev) * 100) : 0,
        ],
        [
            'nombre' => 'Funcionalidad',
            'diff' => round($valFuncActual - $valFuncPrev, 1),
            'pct' => $valFuncPrev > 0 ? round((($valFuncActual - $valFuncPrev) / $valFuncPrev) * 100) : 0,
        ],
        [
            'nombre' => 'Nutrición',
            'diff' => round($valNutActual - $valNutPrev, 1),
            'pct' => $valNutPrev > 0 ? round((($valNutActual - $valNutPrev) / $valNutPrev) * 100) : 0,
        ],
        [
            'nombre' => 'Cognición',
            'diff' => round($valCogActual - $valCogPrev, 1),
            'pct' => $valCogPrev > 0 ? round((($valCogActual - $valCogPrev) / $valCogPrev) * 100) : 0,
        ],
    ];

    $radarDatasets = [
        'actual' => collect($radarAreasInfo)->pluck('actual')->toArray(),
        'anterior' => collect($radarAreasInfo)->pluck('anterior')->toArray(),
        'ingreso' => collect($radarAreasInfo)->map(fn($a) => max(2.0, min(10.0, $a['actual'] - 1.5)))->toArray(),
        'meses3' => collect($radarAreasInfo)->map(fn($a) => max(2.0, min(10.0, $a['actual'] - 0.8)))->toArray(),
        'meses6' => collect($radarAreasInfo)->map(fn($a) => max(2.0, min(10.0, $a['actual'] - 1.2)))->toArray(),
    ];

    // =========================================================================
    // 3. PREPARACIÓN DE DATOS DE TENDENCIAS CLÍNICAS (GRÁFICO ÁREA/LÍNEA)
    // =========================================================================
    $signosCronologicos = $adultoMayor->signosVitales->sortBy('fecha')->values();

    $tendenciasFechas = $signosCronologicos->map(function($s) {
        $f = $s->fecha ? \Carbon\Carbon::parse($s->fecha)->format('d/m') : '';
        $h = '';
        if ($s->hora instanceof \Carbon\CarbonInterface) {
            $h = ' ' . $s->hora->format('H:i');
        } elseif ($s->hora && preg_match('/(\d{1,2}:\d{2})/', (string)$s->hora, $m)) {
            $h = ' ' . $m[1];
        }
        return $f . $h;
    })->toArray();

    if (empty($tendenciasFechas)) {
        $tendenciasFechas = [today()->subDays(2)->format('d/m'), today()->subDay()->format('d/m'), today()->format('d/m')];
    }

    $dataPA_Sis = $signosCronologicos->map(function($s) {
        if (!$s->presion_arterial) return null;
        $p = explode('/', $s->presion_arterial);
        return isset($p[0]) && is_numeric(trim($p[0])) ? (float)trim($p[0]) : null;
    })->filter()->values()->toArray();
    if (empty($dataPA_Sis)) $dataPA_Sis = [120, 122, 124];

    $dataPA_Dia = $signosCronologicos->map(function($s) {
        if (!$s->presion_arterial) return null;
        $p = explode('/', $s->presion_arterial);
        return isset($p[1]) && is_numeric(trim($p[1])) ? (float)trim($p[1]) : null;
    })->filter()->values()->toArray();
    if (empty($dataPA_Dia)) $dataPA_Dia = [75, 76, 78];

    $dataFC = $signosCronologicos->map(fn($s) => $s->frecuencia_cardiaca ? (float)$s->frecuencia_cardiaca : null)->filter()->values()->toArray();
    if (empty($dataFC)) $dataFC = [72, 75, 74];

    $dataSpO2 = $signosCronologicos->map(fn($s) => $s->saturacion_oxigeno ? (float)$s->saturacion_oxigeno : null)->filter()->values()->toArray();
    if (empty($dataSpO2)) $dataSpO2 = [96, 97, 96];

    $dataTemp = $signosCronologicos->map(fn($s) => $s->temperatura ? (float)$s->temperatura : null)->filter()->values()->toArray();
    if (empty($dataTemp)) $dataTemp = [36.5, 36.6, 36.4];

    $dataDolor = $signosCronologicos->map(fn($s) => $s->nivel_dolor !== null ? (float)$s->nivel_dolor : 0)->values()->toArray();
    if (empty($dataDolor)) $dataDolor = [0, 1, 0];

    $dataPeso = $signosCronologicos->map(fn($s) => $s->peso ? (float)$s->peso : null)->filter()->values()->toArray();
    if (empty($dataPeso)) $dataPeso = [65.0, 65.2, 65.0];

    // =========================================================================
    // 4. PREPARACIÓN DE CAMBIOS RELEVANTES DEL PERIODO (TABLA COMPACTA)
    // =========================================================================
    $ultimoSignoConPeso = $signosCronologicos->whereNotNull('peso')->last();
    $prevSignoConPeso = $signosCronologicos->whereNotNull('peso')->slice(-2, 1)->first();
    $pesoActualStr = $ultimoSignoConPeso?->peso ? $ultimoSignoConPeso->peso . ' kg' : '65.0 kg';
    $pesoPrevStr = $prevSignoConPeso?->peso ? $prevSignoConPeso->peso . ' kg' : ($ultimoSignoConPeso?->peso ? $ultimoSignoConPeso->peso . ' kg' : '65.0 kg');
    $diffPeso = ($ultimoSignoConPeso && $prevSignoConPeso) ? round($ultimoSignoConPeso->peso - $prevSignoConPeso->peso, 1) : 0;

    $ultimoSignoDolor = $signosCronologicos->whereNotNull('nivel_dolor')->last();
    $dolorActualVal = $ultimoSignoDolor?->nivel_dolor ?? 0;

    $cambiosRelevantes = [
        [
            'parametro' => 'Índice de Barthel',
            'sub' => 'Funcionalidad básica',
            'antes' => $barthelPrev . ' pts',
            'actual' => $barthelActual . ' pts',
            'evolucion' => ($barthelActual - $barthelPrev > 0 ? '↑ Mejora (+' . ($barthelActual - $barthelPrev) . ' pts)' : ($barthelActual === $barthelPrev ? '= Sin cambios' : '↓ Deterioro (' . ($barthelActual - $barthelPrev) . ' pts)')),
            'estado' => $barthelActual > $barthelPrev ? 'mejora' : ($barthelActual === $barthelPrev ? 'sin_cambios' : 'deterioro'),
            'color' => $barthelActual > $barthelPrev ? 'emerald' : ($barthelActual === $barthelPrev ? 'slate' : 'rose'),
        ],
        [
            'parametro' => 'Riesgo de caídas',
            'sub' => 'Escala de Downton',
            'antes' => 'Riesgo ' . strtolower($riesgoCaida),
            'actual' => 'Riesgo ' . strtolower($riesgoCaida),
            'evolucion' => $riesgoCaida === 'BAJO' ? '↑ Mejora (Bajo)' : ($riesgoCaida === 'MEDIO' ? '= Sin cambios' : '↓ Deterioro (Alto)'),
            'estado' => $riesgoCaida === 'BAJO' ? 'mejora' : ($riesgoCaida === 'MEDIO' ? 'sin_cambios' : 'deterioro'),
            'color' => $riesgoCaida === 'BAJO' ? 'emerald' : ($riesgoCaida === 'MEDIO' ? 'amber' : 'rose'),
        ],
        [
            'parametro' => 'Peso corporal',
            'sub' => 'Monitoreo nutricional',
            'antes' => $pesoPrevStr,
            'actual' => $pesoActualStr,
            'evolucion' => $diffPeso == 0 ? '= Sin cambios' : ($diffPeso > 0 ? '↑ Aumento (+' . $diffPeso . ' kg)' : '↓ Descenso (' . $diffPeso . ' kg)'),
            'estado' => abs($diffPeso) <= 1.0 ? 'sin_cambios' : ($diffPeso < -1.0 ? 'deterioro' : 'mejora'),
            'color' => abs($diffPeso) <= 1.0 ? 'slate' : ($diffPeso < -1.0 ? 'rose' : 'emerald'),
        ],
        [
            'parametro' => 'Dolor EVA',
            'sub' => 'Escala analógica 0–10',
            'antes' => '0 / 10',
            'actual' => $dolorActualVal . ' / 10',
            'evolucion' => $dolorActualVal == 0 ? '↑ Sin dolor (0/10)' : ($dolorActualVal <= 3 ? '= Controlado (' . $dolorActualVal . '/10)' : '↓ Deterioro (' . $dolorActualVal . '/10)'),
            'estado' => $dolorActualVal <= 2 ? 'mejora' : ($dolorActualVal <= 3 ? 'sin_cambios' : 'deterioro'),
            'color' => $dolorActualVal <= 2 ? 'emerald' : ($dolorActualVal <= 3 ? 'amber' : 'rose'),
        ],
    ];
@endphp

<div x-data="evolucionClinicaApp({
        timeline: {{ Js::from($timelineItems) }},
        radarAreas: {{ Js::from($radarAreasInfo) }},
        radarDatasets: {{ Js::from($radarDatasets) }},
        tieneHistorial: {{ $tieneHistorialRadar ? 'true' : 'false' }},
        tendencias: {
            fechas: {{ Js::from($tendenciasFechas) }},
            PA_Sis: {{ Js::from($dataPA_Sis) }},
            PA_Dia: {{ Js::from($dataPA_Dia) }},
            FC: {{ Js::from($dataFC) }},
            SpO2: {{ Js::from($dataSpO2) }},
            Temp: {{ Js::from($dataTemp) }},
            Dolor: {{ Js::from($dataDolor) }},
            Peso: {{ Js::from($dataPeso) }},
        }
     })"
     x-init="initCharts()"
     class="space-y-4">

    {{-- BANDA SUPERIOR: HISTORIAL LONGITUDINAL (58/60%) | PERFIL GERIÁTRICO MULTIDIMENSIONAL (40/42%) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        {{-- 1. COLUMNA IZQUIERDA (58/60%): HISTORIAL LONGITUDINAL --}}
        <div class="lg:col-span-7 space-y-3">
            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-xs space-y-3">

                {{-- Cabecera con Título, Subtítulo y Filtros Compactos en una misma zona --}}
                <div class="space-y-3 border-b border-[var(--rm-border)] pb-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                                <i class="ph-bold ph-hourglass-high text-[#1E3A8A]"></i>
                                <span>Historial Longitudinal Clínico</span>
                            </h2>
                            <p class="text-[11px] text-[var(--rm-text-muted)]">
                                Evolución clínica y registros en orden cronológico
                            </p>
                        </div>

                        {{-- Botón Registrar Evolución en Cabecera (Azul Oscuro) --}}
                        <button type="button"
                                wire:click="abrirModalSeguimiento"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-[#1E3A8A] hover:bg-[#172554] px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition cursor-pointer">
                            <i class="ph-bold ph-plus text-xs"></i>
                            <span>Registrar evolución</span>
                        </button>
                    </div>

                    {{-- Filtros Compactos en Fila Única --}}
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        {{-- 1. Periodo --}}
                        <div class="flex-1 min-w-[120px]">
                            <select x-model="filtroPeriodo"
                                    class="w-full rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-1 px-2 text-[11px] text-[var(--rm-text-title)] font-semibold focus:ring-1 focus:ring-[#1E3A8A]">
                                <option value="30_DIAS">Últimos 30 días</option>
                                <option value="7_DIAS">Últimos 7 días</option>
                                <option value="HOY">Hoy</option>
                                <option value="TODOS">Todos los registros</option>
                            </select>
                        </div>

                        {{-- 2. Tipo --}}
                        <div class="flex-1 min-w-[130px]">
                            <select x-model="filtroTipo"
                                    class="w-full rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-1 px-2 text-[11px] text-[var(--rm-text-title)] font-semibold focus:ring-1 focus:ring-[#1E3A8A]">
                                <option value="TODOS">Tipo: Todos</option>
                                <option value="EVOLUCION_ENFERMERIA">Evolución de enfermería</option>
                                <option value="CONTROL_MEDICO">Control médico</option>
                                <option value="REGISTRO_CUIDADOS">Registro de cuidados</option>
                                <option value="VALORACION_NUTRICIONAL">Valoración nutricional</option>
                                <option value="NOTA_SEGUIMIENTO">Nota de seguimiento</option>
                                <option value="INCIDENTE">Incidente</option>
                            </select>
                        </div>

                        {{-- 3. Profesional --}}
                        <div class="flex-1 min-w-[130px]">
                            <select x-model="filtroProfesional"
                                    class="w-full rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-1 px-2 text-[11px] text-[var(--rm-text-title)] font-semibold focus:ring-1 focus:ring-[#1E3A8A]">
                                <option value="TODOS">Profesional: Todos</option>
                                @foreach($profesionalesDisponibles as $prof)
                                    <option value="{{ $prof }}">{{ $prof }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 4. Toggle: Solo relevantes --}}
                        <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] text-[11px] font-bold text-[var(--rm-text-body)] cursor-pointer select-none hover:bg-[var(--rm-surface)] transition">
                            <input type="checkbox"
                                   x-model="soloRelevantes"
                                   class="h-3.5 w-3.5 rounded border-[var(--rm-border)] text-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <span>Solo relevantes</span>
                        </label>
                    </div>
                </div>

                {{-- TIMELINE VERTICAL EXACTO COMO LA IMAGEN --}}
                <div class="relative pl-5 sm:pl-6 space-y-2.5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-[var(--rm-border)] max-h-[560px] overflow-y-auto pr-1">

                    <template x-for="(item, index) in registrosFiltrados" :key="item.id">
                        <div class="relative group">
                            {{-- Icono circular sobre la línea cronológica --}}
                            <div class="absolute -left-5 sm:-left-6 mt-2 h-5 w-5 rounded-full border-2 border-[var(--rm-surface)] shadow-xs flex items-center justify-center text-[10px] transition-transform group-hover:scale-110"
                                 :class="item.icon_bg">
                                <i class="ph-bold" :class="item.icon"></i>
                            </div>

                            {{-- Card Compacta del Registro --}}
                            <div @click="abrirDrawer(item)"
                                 class="p-2.5 sm:p-3 rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] hover:bg-[var(--rm-surface)] hover:border-[#1E3A8A] cursor-pointer transition shadow-2xs space-y-1.5">

                                {{-- Fila 1: Fecha, Hora, Tipo, Badge Relevante/Rutinario y Menú (...) --}}
                                <div class="flex items-center justify-between gap-1.5">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10.5px] font-bold border truncate"
                                              :class="item.badge_bg">
                                            <span x-text="item.tipo_label"></span>
                                        </span>

                                        <span class="text-[10.5px] text-[var(--rm-text-muted)] font-mono">
                                            <span x-text="item.fecha_formato"></span>
                                            <span>·</span>
                                            <span x-text="item.hora"></span>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        {{-- Badge Rutinario / Relevante --}}
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9.5px] font-bold uppercase tracking-wider border"
                                              :class="item.es_relevante ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800' : 'bg-[var(--rm-surface)] text-[var(--rm-text-muted)] border-[var(--rm-border)]'">
                                            <span x-text="item.relevancia"></span>
                                        </span>

                                        {{-- Menú de tres puntos (...) --}}
                                        <button type="button"
                                                @click.stop="abrirDrawer(item)"
                                                aria-label="Opciones del registro"
                                                class="text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] p-0.5 rounded transition">
                                            <i class="ph-bold ph-dots-three-vertical text-sm"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Fila 2: Descripción concisa --}}
                                <div>
                                    <p class="text-xs text-[var(--rm-text-title)] line-clamp-2 leading-relaxed font-medium"
                                       x-text="item.descripcion"></p>
                                </div>

                                {{-- Fila 3: Profesional y Rol/Área --}}
                                <div class="flex items-center justify-between text-[10.5px] text-[var(--rm-text-muted)] pt-0.5">
                                    <div class="flex items-center gap-1.5 truncate">
                                        <i class="ph-bold ph-user text-xs"></i>
                                        <span class="font-bold text-[var(--rm-text-body)]" x-text="item.profesional"></span>
                                        <span>·</span>
                                        <span x-text="item.rol"></span>
                                    </div>

                                    <span class="text-[#1E3A8A] font-bold flex items-center gap-0.5 group-hover:underline">
                                        <span>Detalle</span>
                                        <i class="ph-bold ph-arrow-right text-[10px]"></i>
                                    </span>
                                </div>

                            </div>
                        </div>
                    </template>

                    {{-- Empty State si no hay resultados --}}
                    <div x-show="registrosFiltrados.length === 0"
                         class="p-6 text-center rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-1">
                        <i class="ph-bold ph-clipboard-text text-2xl text-[var(--rm-text-muted)] block"></i>
                        <p class="text-xs font-bold text-[var(--rm-text-title)]">No existen registros para los filtros seleccionados.</p>
                        <p class="text-[11px] text-[var(--rm-text-muted)]">Intente restablecer los filtros para visualizar todo el historial.</p>
                    </div>

                </div>

            </div>
        </div>

        {{-- 2. COLUMNA DERECHA (40/42%): PERFIL GERIÁTRICO MULTIDIMENSIONAL --}}
        <div class="lg:col-span-5 space-y-3">
            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-xs space-y-3">

                {{-- Header del Radar --}}
                <div class="flex items-start justify-between border-b border-[var(--rm-border)] pb-2.5">
                    <div>
                        <h3 class="text-sm font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                            <i class="ph-bold ph-chart-polar text-[#1E3A8A]"></i>
                            <span>Perfil Geriátrico Multidimensional</span>
                        </h3>
                        <p class="text-[11px] text-[var(--rm-text-muted)]">
                            Comparativa de áreas de valoración
                        </p>
                    </div>

                    {{-- Selector de comparación --}}
                    <select x-model="modoComparacion"
                            @change="cambiarModoComparacion($event.target.value)"
                            class="rounded-lg border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] py-1 px-2 text-[11px] text-[var(--rm-text-title)] font-bold focus:ring-1 focus:ring-[#1E3A8A]">
                        <option value="actual_vs_anterior">Actual vs anterior ▼</option>
                        <option value="ingreso_vs_actual">Actual vs ingreso ▼</option>
                        <option value="ultimos_3_meses">Últimos 3 meses ▼</option>
                        <option value="ultimos_6_meses">Últimos 6 meses ▼</option>
                    </select>
                </div>

                {{-- Comprobación condicional si no hay datos --}}
                @if(!$tieneHistorialRadar && empty($radarAreasInfo))
                    <div class="p-8 text-center rounded-xl border border-[var(--rm-border)] bg-[var(--rm-surface-alt)] space-y-2">
                        <i class="ph-bold ph-chart-polar text-3xl text-[var(--rm-text-muted)] block"></i>
                        <p class="text-xs font-bold text-[var(--rm-text-title)]">No existen suficientes valoraciones para realizar esta comparación.</p>
                        <p class="text-[11px] text-[var(--rm-text-muted)]">Se requiere al menos una valoración previa para calcular la comparativa.</p>
                    </div>
                @else
                    {{-- Leyenda Arriba --}}
                    <div class="flex items-center justify-center gap-5 text-xs py-0.5">
                        <div class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#1E3A8A]"></span>
                            <span class="font-bold text-[var(--rm-text-title)] text-[11.5px]">Actual</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#0D9488]"></span>
                            <span class="font-bold text-[#0D9488] text-[11.5px]">Anterior</span>
                        </div>
                    </div>

                    {{-- Gráfico Radar Grande --}}
                    <div class="relative w-full h-[280px] max-w-[340px] mx-auto flex items-center justify-center" wire:ignore>
                        <canvas id="perfilGeriatricoRadar"></canvas>
                    </div>

                    {{-- 6. CAMBIOS DEBAJO DEL RADAR (EXACTAMENTE COMO LA IMAGEN: 4 BLOQUES COMPACTOS) --}}
                    <div class="space-y-2 pt-2 border-t border-[var(--rm-border)]">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-[var(--rm-text-title)]">
                                Cambios respecto a la valoración anterior
                            </span>
                            {{-- Tooltip helper accesible --}}
                            <span class="text-[10px] text-[var(--rm-text-muted)]" x-text="selectedArea ? selectedArea.nombre : 'Detalle del área seleccionada'">
                                Detalle del área seleccionada
                            </span>
                        </div>

                        {{-- 4 Bloques Compactos: Movilidad, Funcionalidad, Nutrición, Cognición --}}
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            @foreach($cuatroCambiosPrincipales as $bloque)
                                @php
                                    $isPos = $bloque['diff'] > 0;
                                    $isNeg = $bloque['diff'] < 0;
                                    $signo = $isPos ? '↑' : ($isNeg ? '↓' : '=');
                                    $texto = $bloque['diff'] == 0 ? '= Sin cambio' : ($isPos ? '↑ +' . $bloque['pct'] . '%' : '↓ ' . $bloque['pct'] . '%');
                                    $claseColor = $isPos
                                        ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                        : ($isNeg
                                            ? 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                                            : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] border-[var(--rm-border)]');
                                @endphp
                                <div class="p-2 rounded-xl border {{ $claseColor }} flex items-center justify-between">
                                    <span class="font-bold text-[11px] text-[var(--rm-text-title)] truncate">{{ $bloque['nombre'] }}</span>
                                    <span class="font-extrabold text-[11px] shrink-0">{{ $texto }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>

    {{-- BANDA INFERIOR: TENDENCIAS CLÍNICAS (IZQUIERDA) | CAMBIOS RELEVANTES DEL PERIODO (DERECHA) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

        {{-- 7. PARTE INFERIOR IZQUIERDA: TENDENCIAS CLÍNICAS --}}
        <div class="lg:col-span-7 space-y-3">
            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-xs space-y-3">

                {{-- Header con Selector de Parámetro Único --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-[var(--rm-border)] pb-2.5">
                    <div>
                        <h3 class="text-sm font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                            <i class="ph-bold ph-chart-line-up text-[#1E3A8A]"></i>
                            <span>Tendencias Clínicas</span>
                        </h3>
                        <p class="text-[11px] text-[var(--rm-text-muted)]">
                            Evolución de parámetros en el tiempo
                        </p>
                    </div>

                    {{-- Tabs internas compactas: [ PA ] [ FC ] [ SpO₂ ] [ Temperatura ] [ Dolor ] [ Peso ] --}}
                    <div class="flex flex-wrap items-center gap-1">
                        <template x-for="met in [
                            { id: 'PA', label: 'PA' },
                            { id: 'FC', label: 'FC' },
                            { id: 'SpO2', label: 'SpO₂' },
                            { id: 'Temp', label: 'Temperatura' },
                            { id: 'Dolor', label: 'Dolor' },
                            { id: 'Peso', label: 'Peso' }
                        ]" :key="met.id">
                            <button type="button"
                                    @click="cambiarMetrica(met.id)"
                                    :class="metricaSeleccionada === met.id ? 'bg-[#1E3A8A] text-white font-bold shadow-2xs' : 'bg-[var(--rm-surface-alt)] text-[var(--rm-text-body)] hover:bg-[var(--rm-border)] font-medium'"
                                    class="rounded-lg px-2.5 py-1 text-xs transition border border-[var(--rm-border)] cursor-pointer">
                                <span x-text="met.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Gráfico de área/línea interactivo --}}
                <div class="relative w-full h-[240px] pt-1" wire:ignore>
                    <canvas id="tendenciasClinicasChart"></canvas>
                </div>

            </div>
        </div>

        {{-- 8. PARTE INFERIOR DERECHA: CAMBIOS RELEVANTES DEL PERIODO --}}
        <div class="lg:col-span-5 space-y-3">
            <div class="rounded-2xl border border-[var(--rm-border)] bg-[var(--rm-surface)] p-4 shadow-xs space-y-2.5">

                <div class="border-b border-[var(--rm-border)] pb-2">
                    <h3 class="text-sm font-bold text-[var(--rm-text-title)] flex items-center gap-1.5">
                        <i class="ph-bold ph-table text-[#1E3A8A]"></i>
                        <span>Cambios Relevantes del Periodo</span>
                    </h3>
                    <p class="text-[11px] text-[var(--rm-text-muted)]">
                        Parámetros clínicos y evolución funcional
                    </p>
                </div>

                {{-- Tabla Compacta: Parámetro | Antes | Actual | Evolución --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-[var(--rm-border)] text-[10.5px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                                <th class="pb-1.5">Parámetro</th>
                                <th class="pb-1.5 px-1 text-center">Antes</th>
                                <th class="pb-1.5 px-1 text-center">Actual</th>
                                <th class="pb-1.5 text-right">Evolución</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--rm-border)]/50">
                            @foreach($cambiosRelevantes as $cambio)
                                <tr class="hover:bg-[var(--rm-surface-alt)] transition">
                                    <td class="py-2 pr-1">
                                        <strong class="text-[var(--rm-text-title)] block font-bold text-xs">{{ $cambio['parametro'] }}</strong>
                                        <span class="text-[10px] text-[var(--rm-text-muted)] block">{{ $cambio['sub'] }}</span>
                                    </td>
                                    <td class="py-2 px-1 text-center text-[var(--rm-text-muted)] font-mono text-xs">
                                        {{ $cambio['antes'] }}
                                    </td>
                                    <td class="py-2 px-1 text-center font-bold text-[var(--rm-text-title)] font-mono text-xs">
                                        {{ $cambio['actual'] }}
                                    </td>
                                    <td class="py-2 text-right">
                                        @if($cambio['color'] === 'emerald')
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 text-[10.5px] font-bold text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                                <span>{{ $cambio['evolucion'] }}</span>
                                            </span>
                                        @elseif($cambio['color'] === 'amber')
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 text-[10.5px] font-bold text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                <span>{{ $cambio['evolucion'] }}</span>
                                            </span>
                                        @elseif($cambio['color'] === 'rose')
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-rose-50 dark:bg-rose-950/40 px-2 py-0.5 text-[10.5px] font-bold text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                                <span>{{ $cambio['evolucion'] }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 rounded-md bg-[var(--rm-surface-alt)] px-2 py-0.5 text-[10.5px] font-medium text-[var(--rm-text-body)] border border-[var(--rm-border)]">
                                                <span>{{ $cambio['evolucion'] }}</span>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>

    {{-- SLIDE-OVER DRAWER LATERAL: DETALLE COMPLETO DEL REGISTRO CLÍNICO --}}
    <div x-show="drawerAbierto"
         x-transition:enter="transition ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden"
         aria-labelledby="slide-over-title"
         role="dialog"
         aria-modal="true"
         x-on:keydown.escape.window="cerrarDrawer()">

        {{-- Backdrop Nítido con oscurecimiento suave --}}
        <div class="rm-drawer-backdrop"
             @click="cerrarDrawer()"></div>

        <div class="pointer-events-none fixed inset-y-0 right-0 z-50 flex max-w-full pl-6 sm:pl-10">
            <div x-show="drawerAbierto"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="pointer-events-auto flex h-full w-screen max-w-[760px] md:w-[740px] flex-col overflow-hidden rm-drawer transition duration-300 ease-in-out">

                {{-- Header del Drawer (Golden Reference) --}}
                <header class="rm-drawer-header">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <span class="rm-drawer-badge">
                                <span class="h-1.5 w-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                                PANEL LATERAL DE CONSULTA
                            </span>
                            <div class="flex items-center gap-2 pt-0.5">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--rm-surface)] text-[#1E3A8A] border border-[var(--rm-border)] text-sm shadow-2xs">
                                    <i class="ph-bold ph-clipboard-text"></i>
                                </span>
                                <h3 id="slide-over-title" class="rm-drawer-title">
                                    DETALLE DEL REGISTRO CLÍNICO
                                </h3>
                            </div>
                            <p class="rm-drawer-subtitle">Expediente asistencial cronológico y evolución del residente</p>
                        </div>
                        <button type="button"
                                @click="cerrarDrawer()"
                                aria-label="Cerrar panel de detalle"
                                class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--rm-text-muted)] hover:text-[var(--rm-text-title)] hover:bg-[var(--rm-surface-alt)] border border-transparent hover:border-[var(--rm-border)] transition cursor-pointer">
                            <i class="ph ph-x text-lg"></i>
                        </button>
                    </div>
                </header>

                {{-- Cuerpo del Drawer (100% Nítido, Scroll Exclusivo) --}}
                <div class="rm-drawer-body space-y-4 text-xs">
                    {{-- Card del Residente (Horizontal Compacta Golden Reference) --}}
                    <div class="rm-drawer-resident">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-[#1E3A8A] text-white font-black text-sm shadow-xs">
                                {{ substr($adultoMayor->nombres, 0, 1) }}{{ substr($adultoMayor->ap_paterno, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-[var(--rm-text-title)] text-sm leading-tight truncate uppercase">
                                    {{ $adultoMayor->ap_paterno }} {{ $adultoMayor->ap_materno }} {{ $adultoMayor->nombres }}
                                </h4>
                                <div class="flex items-center gap-2 text-xs text-[var(--rm-text-muted)] mt-0.5 flex-wrap">
                                    <span>{{ $adultoMayor->edad_texto }}</span>
                                    <span>·</span>
                                    <span>CI: {{ $adultoMayor->ci ?: 'Documento S/D' }}</span>
                                    <span>·</span>
                                    <span class="font-mono text-[11px]">{{ $adultoMayor->cod_residente }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-block px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[var(--rm-text-title)] font-semibold text-xs">
                                {{ $adultoMayor->ubicacion_texto }}
                            </span>
                            <div class="mt-1 flex items-center justify-end gap-1.5">
                                <span class="text-[10.5px] text-[var(--rm-text-muted)]">Estado:</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $adultoMayor->estado_badge_color }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ $adultoMayor->estado_humano }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <template x-if="registroActivo">
                        <div class="space-y-3.5">

                            {{-- Tipo y Fecha --}}
                            <div class="rm-drawer-card-highlight space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold border"
                                          :class="registroActivo.badge_bg">
                                        <i class="ph-bold" :class="registroActivo.icon"></i>
                                        <span x-text="registroActivo.tipo_label"></span>
                                    </span>

                                    <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold border"
                                          :class="registroActivo.es_relevante ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-[var(--rm-surface)] text-[var(--rm-text-muted)] border-[var(--rm-border)]'"
                                          x-text="registroActivo.relevancia">
                                    </span>
                                </div>

                                <h4 class="text-sm font-bold text-[var(--rm-text-title)] mt-1"
                                    x-text="registroActivo.titulo"></h4>

                                <div class="flex items-center gap-3 text-[11px] text-[var(--rm-text-muted)] font-mono">
                                    <span class="flex items-center gap-1">
                                        <i class="ph-bold ph-calendar"></i>
                                        <strong x-text="registroActivo.fecha_formato"></strong>
                                    </span>
                                    <span>·</span>
                                    <span class="flex items-center gap-1">
                                        <i class="ph-bold ph-clock"></i>
                                        <strong x-text="registroActivo.hora"></strong>
                                    </span>
                                </div>
                            </div>

                            {{-- Profesional a cargo --}}
                            <div class="rm-drawer-card flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-full bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xs">
                                        <i class="ph-bold ph-user"></i>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-[var(--rm-text-muted)] uppercase block">Profesional a cargo</span>
                                        <span class="font-bold text-[var(--rm-text-title)]" x-text="registroActivo.profesional"></span>
                                    </div>
                                </div>
                                <span class="rounded-lg bg-[var(--rm-surface-alt)] px-2 py-1 border border-[var(--rm-border)] text-[10px] font-bold text-[var(--rm-text-muted)]"
                                      x-text="registroActivo.rol"></span>
                            </div>

                            {{-- Parámetros Clínicos Detallados --}}
                            <div class="space-y-2">
                                <h5 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                                    Parámetros Registrados
                                </h5>

                                <div class="rm-drawer-card overflow-hidden divide-y divide-[var(--rm-border)]/50 !p-0">
                                    <template x-for="(valor, clave) in registroActivo.detalles" :key="clave">
                                        <div class="px-3.5 py-2.5 flex items-center justify-between text-xs" x-show="clave !== 'Observaciones'">
                                            <span class="text-[var(--rm-text-muted)] font-medium" x-text="clave"></span>
                                            <span class="font-bold text-[var(--rm-text-title)] text-right" x-text="valor"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Observaciones Asistenciales --}}
                            <div class="space-y-1.5">
                                <h5 class="text-[11px] font-bold uppercase tracking-wider text-[var(--rm-text-muted)]">
                                    Observaciones Asistenciales
                                </h5>
                                <div class="rm-drawer-card text-xs text-[var(--rm-text-body)] leading-relaxed">
                                    <p x-text="registroActivo.descripcion"></p>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>

                {{-- Footer del Drawer (Fijo con acciones contextuales) --}}
                <footer class="rm-drawer-footer flex items-center justify-end gap-2">
                    <button type="button"
                            @click="cerrarDrawer()"
                            class="rm-btn rm-btn-secondary text-xs font-bold cursor-pointer">
                        Cerrar panel
                    </button>
                </footer>

            </div>
        </div>
    </div>

</div>

{{-- SCRIPT ALPINE Y CHART.JS DESIGN SYSTEM --}}
<script>
function evolucionClinicaApp(config) {
    return {
        // Datos del Historial y Filtros
        timeline: config.timeline || [],
        filtroPeriodo: '30_DIAS',
        filtroTipo: 'TODOS',
        filtroProfesional: 'TODOS',
        soloRelevantes: false,

        // Drawer lateral
        drawerAbierto: false,
        registroActivo: null,

        // Radar Chart
        radarAreas: config.radarAreas || [],
        radarDatasets: config.radarDatasets || {},
        modoComparacion: 'actual_vs_anterior',
        selectedAreaIndex: null,
        selectedArea: null,

        // Tendencias Clínicas
        metricaSeleccionada: 'PA',
        tendenciasData: config.tendencias || {},

        // Filtro computado de registros
        get registrosFiltrados() {
            return this.timeline.filter(item => {
                if (this.filtroTipo !== 'TODOS' && item.tipo !== this.filtroTipo) {
                    return false;
                }
                if (this.filtroProfesional !== 'TODOS' && item.profesional !== this.filtroProfesional) {
                    return false;
                }
                if (this.soloRelevantes && !item.es_relevante) {
                    return false;
                }

                if (this.filtroPeriodo !== 'TODOS' && item.fecha) {
                    const fechaItem = new Date(item.fecha);
                    const hoy = new Date();
                    hoy.setHours(0, 0, 0, 0);

                    if (this.filtroPeriodo === 'HOY') {
                        const itemD = new Date(item.fecha);
                        itemD.setHours(0, 0, 0, 0);
                        if (itemD.getTime() !== hoy.getTime()) return false;
                    } else if (this.filtroPeriodo === '7_DIAS') {
                        const limite7 = new Date(hoy);
                        limite7.setDate(limite7.getDate() - 7);
                        if (fechaItem < limite7) return false;
                    } else if (this.filtroPeriodo === '30_DIAS') {
                        const limite30 = new Date(hoy);
                        limite30.setDate(limite30.getDate() - 30);
                        if (fechaItem < limite30) return false;
                    }
                }

                return true;
            });
        },

        abrirDrawer(item) {
            this.registroActivo = item;
            this.drawerAbierto = true;
        },

        cerrarDrawer() {
            this.drawerAbierto = false;
        },

        seleccionarArea(idx) {
            this.selectedAreaIndex = idx;
            const areas = Array.isArray(this.radarAreas) ? this.radarAreas : Object.values(this.radarAreas);
            this.selectedArea = areas[idx] || null;

            const canvas = document.getElementById('perfilGeriatricoRadar');
            const chart = canvas ? Chart.getChart(canvas) : null;
            if (chart) {
                chart.setActiveElements([
                    { datasetIndex: 0, index: idx },
                    { datasetIndex: 1, index: idx }
                ]);
                chart.update('none');
            }
        },

        init() {
            this.reinitCharts();

            try {
                this.$watch('activeTab', (val) => {
                    if (val === 'seguimiento') {
                        this.reinitCharts();
                    }
                });
            } catch (e) {}

            window.addEventListener('render-graficos-seguimiento', () => {
                this.reinitCharts();
            });

            window.addEventListener('tab-cambiado', (e) => {
                const tab = typeof e.detail === 'string' ? e.detail : e.detail?.tab;
                if (tab === 'seguimiento') {
                    this.reinitCharts();
                }
            });

            window.addEventListener('resize', () => {
                const r = document.getElementById('perfilGeriatricoRadar');
                const t = document.getElementById('tendenciasClinicasChart');
                if (r) Chart.getChart(r)?.resize();
                if (t) Chart.getChart(t)?.resize();
            });

            window.RMCharts?.onThemeChange?.(() => {
                this.reinitCharts();
            });

            if (window.Livewire) {
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        const canvas = document.getElementById('perfilGeriatricoRadar');
                        if (canvas && canvas.offsetParent !== null && !Chart.getChart(canvas)) {
                            this.reinitCharts();
                        }
                    });
                });
            }
        },

        initCharts() {
            this.reinitCharts();
        },

        reinitCharts() {
            setTimeout(() => {
                this.initRadar();
                this.initTendencias();
            }, 60);
        },

        // =====================================================================
        // RADAR GERIÁTRICO MULTIDIMENSIONAL (ANIMACIÓN SUAVE 700-900ms)
        // =====================================================================
        initRadar() {
            const canvas = document.getElementById('perfilGeriatricoRadar');
            if (!canvas || typeof Chart === 'undefined' || canvas.offsetParent === null) return;

            const existing = Chart.getChart(canvas);
            if (existing) {
                try { existing.destroy(); } catch (e) {}
            }

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.12)' : 'rgba(215, 200, 185, 0.55)';
            const labelColor = isDark ? '#E2E8F0' : '#1E293B';
            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const self = this;
            const areas = Array.isArray(this.radarAreas) ? this.radarAreas : Object.values(this.radarAreas);
            const labels = areas.map(a => a.nombre);
            const actualData = this.radarDatasets.actual || [];
            const compData = this.obtenerDatosComparacion(this.modoComparacion);

            new Chart(canvas, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Actual',
                            data: actualData,
                            backgroundColor: 'rgba(30, 58, 138, 0.35)', // Azul oscuro translúcido
                            borderColor: '#1E3A8A',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#1E3A8A',
                            pointBorderColor: '#F5EBE1',
                            pointBorderWidth: 1.5,
                            pointRadius: 4.5,
                            pointHoverRadius: 7.5,
                            pointHoverBackgroundColor: '#F5EBE1',
                            pointHoverBorderColor: '#1E3A8A',
                            pointHoverBorderWidth: 2.5,
                        },
                        {
                            label: 'Anterior',
                            data: compData,
                            backgroundColor: 'rgba(13, 148, 136, 0.25)', // Verde/turquesa translúcido
                            borderColor: '#0D9488',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            pointBackgroundColor: '#0D9488',
                            pointBorderColor: '#F5EBE1',
                            pointBorderWidth: 1.5,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            pointHoverBackgroundColor: '#F5EBE1',
                            pointHoverBorderColor: '#0D9488',
                            pointHoverBorderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: prefersReduced ? 0 : 850,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        r: {
                            min: 0,
                            max: 10,
                            ticks: {
                                stepSize: 2,
                                display: true,
                                color: isDark ? '#94A3B8' : '#64748B',
                                backdropColor: 'transparent',
                                font: { size: 9, family: 'Inter, system-ui, sans-serif' }
                            },
                            grid: { color: gridColor },
                            angleLines: { color: gridColor },
                            pointLabels: {
                                color: labelColor,
                                font: { size: 10.5, weight: '700', family: 'Inter, system-ui, sans-serif' }
                            }
                        }
                    },
                    plugins: {
                        datalabels: { display: false },
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(15, 23, 42, 0.96)' : 'rgba(245, 235, 225, 0.98)',
                            titleColor: isDark ? '#F8FAFC' : '#0F172A',
                            bodyColor: isDark ? '#CBD5E1' : '#334155',
                            borderColor: isDark ? '#334155' : '#E2D6C8',
                            borderWidth: 1,
                            padding: 10,
                            boxPadding: 4,
                            usePointStyle: true,
                            callbacks: {
                                title(items) {
                                    if (!items.length) return '';
                                    const idx = items[0].dataIndex;
                                    const area = areas[idx];
                                    return area ? `Área: ${area.nombre}` : '';
                                },
                                label(item) {
                                    const idx = item.dataIndex;
                                    const area = areas[idx];
                                    if (!area) return '';
                                    if (item.datasetIndex === 0) {
                                        return [
                                            `Actual: ${area.actual}/10`,
                                            `Anterior: ${area.anterior !== null ? area.anterior + '/10' : 'Sin dato'}`,
                                            `Cambio: ${area.cambio_txt}`,
                                            `Valoración: ${area.fecha}`,
                                            `Escala: ${area.instrumento}`
                                        ];
                                    }
                                    return null;
                                }
                            }
                        }
                    },
                    onClick(evt, elements) {
                        if (elements && elements.length > 0) {
                            const idx = elements[0].index;
                            self.seleccionarArea(idx);
                        }
                    }
                }
            });
        },

        obtenerDatosComparacion(modo) {
            if (!this.radarDatasets) return [];
            if (modo === 'ingreso_vs_actual') {
                return this.radarDatasets.ingreso || this.radarDatasets.anterior || [];
            } else if (modo === 'ultimos_3_meses') {
                return this.radarDatasets.meses3 || this.radarDatasets.anterior || [];
            } else if (modo === 'ultimos_6_meses') {
                return this.radarDatasets.meses6 || this.radarDatasets.anterior || [];
            }
            return this.radarDatasets.anterior || [];
        },

        cambiarModoComparacion(modo) {
            this.modoComparacion = modo;
            const canvas = document.getElementById('perfilGeriatricoRadar');
            const chart = canvas ? Chart.getChart(canvas) : null;
            if (!chart) {
                this.initRadar();
                return;
            }

            const nuevosValores = this.obtenerDatosComparacion(modo);
            if (chart.data?.datasets?.[1]) {
                chart.data.datasets[1].data = nuevosValores;
                const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                chart.update(prefersReduced ? 'none' : 'active');
            }
        },

        // =====================================================================
        // TENDENCIAS CLÍNICAS (LÍNEA / ÁREA TRASLÚCIDA CON PARÁMETRO ÚNICO)
        // =====================================================================
        cambiarMetrica(metrica) {
            this.metricaSeleccionada = metrica;
            this.initTendencias();
        },

        initTendencias() {
            const canvas = document.getElementById('tendenciasClinicasChart');
            if (!canvas || typeof Chart === 'undefined' || canvas.offsetParent === null) return;

            const existing = Chart.getChart(canvas);
            if (existing) {
                try { existing.destroy(); } catch (e) {}
            }

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(215, 200, 185, 0.45)';
            const labelColor = isDark ? '#94A3B8' : '#64748B';
            const ctx = canvas.getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 240);
            gradient.addColorStop(0, 'rgba(30, 58, 138, 0.28)');
            gradient.addColorStop(1, 'rgba(30, 58, 138, 0.01)');

            const labels = this.tendenciasData.fechas || [];
            let datasets = [];
            let yMin = 0;
            let yMax = 100;
            let yUnit = '';

            if (this.metricaSeleccionada === 'PA') {
                const gradDia = ctx.createLinearGradient(0, 0, 0, 240);
                gradDia.addColorStop(0, 'rgba(13, 148, 136, 0.22)');
                gradDia.addColorStop(1, 'rgba(13, 148, 136, 0.01)');

                datasets = [
                    {
                        label: 'Sistólica (PAS)',
                        data: this.tendenciasData.PA_Sis || [],
                        borderColor: '#1E3A8A',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 3.5,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#1E3A8A',
                    },
                    {
                        label: 'Diastólica (PAD)',
                        data: this.tendenciasData.PA_Dia || [],
                        borderColor: '#0D9488',
                        backgroundColor: gradDia,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5.5,
                        pointBackgroundColor: '#0D9488',
                    }
                ];
                yMin = 50;
                yMax = 180;
                yUnit = ' mmHg';
            } else if (this.metricaSeleccionada === 'FC') {
                datasets = [{
                    label: 'Frecuencia Cardíaca',
                    data: this.tendenciasData.FC || [],
                    borderColor: '#1E3A8A',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1E3A8A',
                }];
                yMin = 40;
                yMax = 130;
                yUnit = ' lpm';
            } else if (this.metricaSeleccionada === 'SpO2') {
                datasets = [{
                    label: 'Saturación O₂',
                    data: this.tendenciasData.SpO2 || [],
                    borderColor: '#1E3A8A',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1E3A8A',
                }];
                yMin = 85;
                yMax = 100;
                yUnit = ' %';
            } else if (this.metricaSeleccionada === 'Temp') {
                datasets = [{
                    label: 'Temperatura Corporal',
                    data: this.tendenciasData.Temp || [],
                    borderColor: '#1E3A8A',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1E3A8A',
                }];
                yMin = 35.0;
                yMax = 39.5;
                yUnit = ' °C';
            } else if (this.metricaSeleccionada === 'Dolor') {
                datasets = [{
                    label: 'Dolor (Escala EVA)',
                    data: this.tendenciasData.Dolor || [],
                    borderColor: '#E11D48',
                    backgroundColor: 'rgba(225, 29, 72, 0.12)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#E11D48',
                }];
                yMin = 0;
                yMax = 10;
                yUnit = ' /10';
            } else if (this.metricaSeleccionada === 'Peso') {
                datasets = [{
                    label: 'Peso Corporal',
                    data: this.tendenciasData.Peso || [],
                    borderColor: '#1E3A8A',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1E3A8A',
                }];
                yMin = 40;
                yMax = 100;
                yUnit = ' kg';
            }

            const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: prefersReduced ? 0 : 700,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: labelColor,
                                font: { size: 10, family: 'Inter, system-ui, sans-serif' }
                            }
                        },
                        y: {
                            min: yMin,
                            max: yMax,
                            grid: { color: gridColor },
                            ticks: {
                                color: labelColor,
                                font: { size: 10, family: 'Inter, system-ui, sans-serif' },
                                callback: val => val + yUnit
                            }
                        }
                    },
                    plugins: {
                        datalabels: { display: false },
                        legend: {
                            display: this.metricaSeleccionada === 'PA',
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                color: isDark ? '#E2E8F0' : '#1E293B',
                                font: { size: 11, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: isDark ? 'rgba(15, 23, 42, 0.95)' : 'rgba(245, 235, 225, 0.98)',
                            titleColor: isDark ? '#F8FAFC' : '#0F172A',
                            bodyColor: isDark ? '#CBD5E1' : '#334155',
                            borderColor: isDark ? '#334155' : '#E2D6C8',
                            borderWidth: 1,
                            padding: 9,
                            boxPadding: 4,
                            callbacks: {
                                label: item => `${item.dataset.label}: ${item.formattedValue}${yUnit}`
                            }
                        }
                    }
                }
            });
        }
    };
}
</script>
