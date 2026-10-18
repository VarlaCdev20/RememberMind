<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\EjecucionCuidado;
use App\Models\Residente;
use Carbon\Carbon;

final class ResumenLongitudinalResidente
{
    /**
     * Adapta originales de dos períodos ya filtrados a puntos de presión arterial.
     * Conserva fechas reales, ausencias y clasificaciones. No calcula promedios,
     * tendencias ni interpolaciones; el gráfico representa solo mediciones guardadas.
     */
    public function seriePresionComparada(array $actuales, array $anteriores): array
    {
        $puntos = [];
        foreach (['anterior' => $anteriores, 'actual' => $actuales] as $periodo => $registros) {
            foreach ($registros as $registro) {
                $sis = $registro['campos']['Presión sistólica (mmHg)'];
                $dia = $registro['campos']['Presión diastólica (mmHg)'];
                if ($sis === null && $dia === null) {
                    continue;
                }
                $clasificacion = collect($registro['clasificacion']['resultados'])
                    ->filter(fn ($r) => in_array($r['variable'], InterpretacionSignosVitales::VARIABLES['PA'], true));
                $puntos[] = ['codigo' => $registro['codigo'], 'fecha' => $registro['fecha_iso'],
                    'periodo' => $periodo, 'zona' => config('app.timezone'), 'sistolica' => $sis, 'diastolica' => $dia,
                    'clasificacion' => implode(' · ', $clasificacion->map(fn ($r) => $r['severidad'] ?? 'Sin clasificación automática')->unique()->all())];
            }
        }

        return collect($puntos)->sortBy('fecha')->values()->all();
    }

    public function consultar(Residente $residente, ?string $periodo = '7d', ?string $metricaSeleccionada = 'PA'): array
    {
        // 1. Alertas
        $alertasTotal = $residente->alertas->count();
        $alertasActivas = $residente->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count();
        $alertasCerradas = $residente->alertas->where('estado', 'CERRADA')->count();

        // 2. Adherencia medicación
        $totalAdmin = $residente->administracionesMedicacion->count();
        $adminOk = $residente->administracionesMedicacion->where('administrado', true)->count();
        $adminOmitidas = $totalAdmin - $adminOk;
        $adherenciaPct = $totalAdmin > 0 ? (int) round(($adminOk / $totalAdmin) * 100) : 0;

        // 3. Cumplimiento cuidados
        $totalTareas = EjecucionCuidado::where('cod_residente', $residente->cod_residente)->count();
        $tareasRealizadas = EjecucionCuidado::where('cod_residente', $residente->cod_residente)
            ->whereIn('estado', ['REALIZADA', 'EJECUTADA', 'COMPLETADA'])
            ->count();
        $cumplimientoPct = $totalTareas > 0 ? (int) round(($tareasRealizadas / $totalTareas) * 100) : 0;

        // 4. Seguimientos
        $totalSeguimientos = $residente->pasesTurno()->count();
        $totalIncidentes = $residente->alertas
            ->whereIn('tipo', ['INCIDENTE', 'CAIDA', 'EVENTO_ADVERSO'])
            ->count();

        // 5. Última valoración oficial
        $ultimaFuncional = $residente->valoracionesFuncionales->sortByDesc('fecha_valoracion')->first();
        $ultimaMedica = $residente->atenciones->sortByDesc('fecha_hora')->first();

        $ultimaValoracion = null;
        if ($ultimaFuncional) {
            $ultimaValoracion = [
                'instrumento' => 'Índice de Barthel',
                'resultado' => ($ultimaFuncional->indice_barthel ?? $ultimaFuncional->barthel_total) !== null
                    ? ($ultimaFuncional->indice_barthel ?? $ultimaFuncional->barthel_total).'/100'.($ultimaFuncional->nivel_dependencia ? " ({$ultimaFuncional->nivel_dependencia})" : '')
                    : 'Resultado no registrado',
                'fecha' => $ultimaFuncional->fecha_valoracion ? Carbon::parse($ultimaFuncional->fecha_valoracion)->format('d/m/Y') : 'Reciente',
                'evaluador' => $ultimaFuncional->registradoPor?->usuario?->name ?? 'Profesional no registrado',
            ];
        } elseif ($ultimaMedica) {
            $ultimaValoracion = [
                'instrumento' => 'Ficha Médica',
                'resultado' => $ultimaMedica->motivo ?: 'Atención clínica registrada',
                'fecha' => $ultimaMedica->fecha_hora?->format('d/m/Y') ?? 'Reciente',
                'evaluador' => $ultimaMedica->personal?->usuario?->name ?? 'Equipo Médico',
            ];
        }

        // 6. Tendencia longitudinal y análisis estructurado de signos vitales
        $historialCompleto = $residente->signosVitales->sortBy('fecha_hora')->values();
        $todosSignosOrdenados = $historialCompleto->whereIn('estado', ['VIGENTE', 'ACTIVO'])->values();
        $periodo ??= '7d';
        $ahora = now();
        $desde = match ($periodo) {
            '24h' => $ahora->copy()->subHours(24),
            '7d' => $ahora->copy()->subDays(7),
            '30d' => $ahora->copy()->subDays(30),
            '3m' => $ahora->copy()->subMonths(3),
            default => null,
        };
        $signosOrdenados = $todosSignosOrdenados->filter(fn ($s) => $s->fecha_hora !== null
            && $s->fecha_hora->lte($ahora) && ($desde === null || $s->fecha_hora->gte($desde)))->values();

        $labels = [];
        $labelsLargas = [];
        $sistolica = [];
        $diastolica = [];
        $fc = [];
        $spo2 = [];
        $temp = [];
        $fr = [];
        $dolor = [];
        $glucosa = [];
        $peso = [];

        foreach ($signosOrdenados as $s) {
            $fechaFmt = '';
            $fechaLarga = '';
            if ($s->fecha) {
                $cFecha = $s->fecha instanceof Carbon ? $s->fecha : Carbon::parse(substr((string) $s->fecha, 0, 10));
                $fechaFmt = $cFecha->format('d/m');
                $fechaLarga = $cFecha->translatedFormat('d M Y');
            }
            $horaFmt = '';
            if ($s->hora) {
                if (preg_match('/(\d{1,2}:\d{2})/', (string) $s->hora, $m)) {
                    $horaFmt = $m[1];
                } else {
                    $horaFmt = substr((string) $s->hora, 0, 5);
                }
            }
            $labels[] = trim("{$fechaFmt} {$horaFmt}");
            $labelsLargas[] = trim("{$fechaLarga} · {$horaFmt}");

            $pas = null;
            $pad = null;
            if ($s->presion_sistolica && $s->presion_diastolica) {
                $pas = (float) $s->presion_sistolica;
                $pad = (float) $s->presion_diastolica;
            } elseif ($s->presion_arterial && str_contains($s->presion_arterial, '/')) {
                $partes = explode('/', $s->presion_arterial);
                $pas = is_numeric(trim($partes[0])) ? (float) trim($partes[0]) : null;
                $pad = is_numeric(trim($partes[1])) ? (float) trim($partes[1]) : null;
            }
            $sistolica[] = $pas;
            $diastolica[] = $pad;
            $fc[] = $s->frecuencia_cardiaca ? (float) $s->frecuencia_cardiaca : null;
            $spo2[] = $s->saturacion ? (float) $s->saturacion : null;
            $temp[] = $s->temperatura ? (float) $s->temperatura : null;
            $fr[] = $s->frecuencia_respiratoria ? (float) $s->frecuencia_respiratoria : null;
            $dolor[] = $s->dolor !== null ? (float) $s->dolor : ($s->nivel_dolor !== null ? (float) $s->nivel_dolor : null);
            $glucosa[] = $s->glucosa ? (float) $s->glucosa : null;
            $peso[] = $s->peso ? (float) $s->peso : null;
        }

        $ultimoSigno = $todosSignosOrdenados->last();
        $penultimoSigno = $todosSignosOrdenados->count() > 1 ? $todosSignosOrdenados->get($todosSignosOrdenados->count() - 2) : null;

        // Extraer valores del último y penúltimo para variaciones
        $ultPas = $ultimoSigno ? ($ultimoSigno->presion_sistolica ?? (explode('/', $ultimoSigno->presion_arterial ?? '')[0] ?? null)) : null;
        $ultPad = $ultimoSigno ? ($ultimoSigno->presion_diastolica ?? (explode('/', $ultimoSigno->presion_arterial ?? '')[1] ?? null)) : null;
        $prevPas = $penultimoSigno ? ($penultimoSigno->presion_sistolica ?? (explode('/', $penultimoSigno->presion_arterial ?? '')[0] ?? null)) : null;
        $prevPad = $penultimoSigno ? ($penultimoSigno->presion_diastolica ?? (explode('/', $penultimoSigno->presion_arterial ?? '')[1] ?? null)) : null;

        // Cálculos estadísticos para el panel derecho "RESUMEN DEL PERÍODO"
        $calcStats = function (array $nums, int $precision = 0) {
            $validos = array_values(array_filter($nums, fn ($v) => $v !== null));
            if (empty($validos)) {
                return ['min' => '--', 'max' => '--', 'avg' => '--'];
            }

            return [
                'min' => number_format(min($validos), $precision),
                'max' => number_format(max($validos), $precision),
                'avg' => number_format(array_sum($validos) / count($validos), $precision),
            ];
        };

        $statsSistolica = $calcStats($sistolica, 0);
        $statsDiastolica = $calcStats($diastolica, 0);
        $statsFc = $calcStats($fc, 0);
        $statsSpo2 = $calcStats($spo2, 0);
        $statsTemp = $calcStats($temp, 1);
        $statsFr = $calcStats($fr, 0);
        $statsDolor = $calcStats($dolor, 0);
        $statsGlucosa = $calcStats($glucosa, 0);
        $statsPeso = $calcStats($peso, 1);

        // Variaciones numéricas; la interpretación clínica procede del evaluador compartido.
        $diffPas = ($ultPas !== null && $prevPas !== null && is_numeric($ultPas) && is_numeric($prevPas)) ? ((int) $ultPas - (int) $prevPas) : 0;
        $diffPad = ($ultPad !== null && $prevPad !== null && is_numeric($ultPad) && is_numeric($prevPad)) ? ((int) $ultPad - (int) $prevPad) : 0;
        $signoDiff = function ($val) {
            return $val > 0 ? "+{$val}" : (string) $val;
        };

        // Resúmenes estructurados de cada métrica para el selector reactivo
        $sinDatosBadge = 'bg-slate-100 text-slate-700 border-slate-200';
        $metricasInfo = [
            'PA' => [
                'clave' => 'PA',
                'nombre' => 'Presión arterial',
                'titulo_grafico' => 'PRESIÓN ARTERIAL',
                'subtitulo' => 'Evolución de presión sistólica y diastólica con interpretación del servidor',
                'unidad' => 'mmHg',
                'ultimo' => ($ultPas !== null && $ultPad !== null) ? "{$ultPas}/{$ultPad}" : ($ultimoSigno?->presion_arterial ?: 'Sin datos'),
                'ultimo_fmt' => ($ultPas !== null && $ultPad !== null) ? "{$ultPas}/{$ultPad} mmHg" : ($ultimoSigno?->presion_arterial ? "{$ultimoSigno->presion_arterial} mmHg" : 'Sin datos'),
                'min' => "{$statsSistolica['min']}/{$statsDiastolica['min']}",
                'max' => "{$statsSistolica['max']}/{$statsDiastolica['max']}",
                'promedio' => "{$statsSistolica['avg']}/{$statsDiastolica['avg']}",
                'variacion' => ($ultPas !== null && $prevPas !== null && $ultPad !== null && $prevPad !== null) ? (($diffPas != 0 || $diffPad != 0) ? (($diffPas <= 0 ? '↓ ' : '↑ ').$signoDiff($diffPas).' / '.$signoDiff($diffPad).' mmHg') : '= Sin variación') : 'Sin registros comparables',
                'tendencia' => ($ultPas !== null && $prevPas !== null) ? ((abs($diffPas) <= 5 && abs($diffPad) <= 5) ? 'Tendencia estable' : ($diffPas > 5 ? 'Tendencia ascendente' : 'Tendencia descendente')) : 'Datos insuficientes',
                'icon' => 'ph-heartbeat',
                'color' => '#EF4444',
                'sparkline' => ['sistolica' => $sistolica, 'diastolica' => $diastolica],
            ],
            'FC' => [
                'clave' => 'FC',
                'nombre' => 'Frecuencia cardíaca',
                'titulo_grafico' => 'FRECUENCIA CARDÍACA',
                'subtitulo' => 'Ritmo cardíaco y variabilidad en reposo',
                'unidad' => 'lpm',
                'ultimo' => $ultimoSigno?->frecuencia_cardiaca !== null ? (string) $ultimoSigno->frecuencia_cardiaca : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->frecuencia_cardiaca !== null ? "{$ultimoSigno->frecuencia_cardiaca} lpm" : 'Sin datos',
                'min' => $statsFc['min'],
                'max' => $statsFc['max'],
                'promedio' => $statsFc['avg'],
                'variacion' => ($ultimoSigno?->frecuencia_cardiaca !== null && $penultimoSigno?->frecuencia_cardiaca !== null) ? (($ultimoSigno->frecuencia_cardiaca - $penultimoSigno->frecuencia_cardiaca >= 0 ? '↑ +' : '↓ ').($ultimoSigno->frecuencia_cardiaca - $penultimoSigno->frecuencia_cardiaca).' lpm') : 'Sin registros comparables',
                'tendencia' => ($ultimoSigno?->frecuencia_cardiaca !== null && $penultimoSigno?->frecuencia_cardiaca !== null) ? 'Tendencia calculada con registros reales' : 'Datos insuficientes',
                'icon' => 'ph-activity',
                'color' => '#F59E0B',
                'sparkline' => $fc,
            ],
            'SPO2' => [
                'clave' => 'SPO2',
                'nombre' => 'Saturación O₂',
                'titulo_grafico' => 'SATURACIÓN DE OXÍGENO',
                'subtitulo' => 'Oximetría de pulso y oxigenación tisular',
                'unidad' => '% SpO₂',
                'ultimo' => $ultimoSigno?->saturacion !== null ? "{$ultimoSigno->saturacion}%" : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->saturacion !== null ? "{$ultimoSigno->saturacion}% SpO₂" : 'Sin datos',
                'min' => $statsSpo2['min'],
                'max' => $statsSpo2['max'],
                'promedio' => $statsSpo2['avg'],
                'variacion' => ($ultimoSigno?->saturacion !== null && $penultimoSigno?->saturacion !== null) ? (($ultimoSigno->saturacion - $penultimoSigno->saturacion >= 0 ? '↑ +' : '↓ ').($ultimoSigno->saturacion - $penultimoSigno->saturacion).'%') : 'Sin registros comparables',
                'tendencia' => ($ultimoSigno?->saturacion !== null && $penultimoSigno?->saturacion !== null) ? 'Tendencia calculada con registros reales' : 'Datos insuficientes',
                'icon' => 'ph-drop',
                'color' => '#10B981',
                'sparkline' => $spo2,
            ],
            'TEMP' => [
                'clave' => 'TEMP',
                'nombre' => 'Temperatura',
                'titulo_grafico' => 'TEMPERATURA CORPORAL',
                'subtitulo' => 'Curva térmica y detección temprana de cuadros febriles',
                'unidad' => '°C',
                'ultimo' => $ultimoSigno?->temperatura !== null ? "{$ultimoSigno->temperatura}°C" : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->temperatura !== null ? "{$ultimoSigno->temperatura} °C" : 'Sin datos',
                'min' => $statsTemp['min'],
                'max' => $statsTemp['max'],
                'promedio' => $statsTemp['avg'],
                'variacion' => ($ultimoSigno?->temperatura !== null && $penultimoSigno?->temperatura !== null) ? (($ultimoSigno->temperatura - $penultimoSigno->temperatura >= 0 ? '↑ +' : '↓ ').number_format($ultimoSigno->temperatura - $penultimoSigno->temperatura, 1).' °C') : 'Sin registros comparables',
                'tendencia' => ($ultimoSigno?->temperatura !== null && $penultimoSigno?->temperatura !== null) ? 'Tendencia calculada con registros reales' : 'Datos insuficientes',
                'icon' => 'ph-thermometer',
                'color' => '#EA580C',
                'sparkline' => $temp,
            ],
            'FR' => [
                'clave' => 'FR',
                'nombre' => 'Frecuencia respiratoria',
                'titulo_grafico' => 'FRECUENCIA RESPIRATORIA',
                'subtitulo' => 'Monitoreo ventilatorio y patrón respiratorio',
                'unidad' => 'rpm',
                'ultimo' => $ultimoSigno?->frecuencia_respiratoria !== null ? (string) $ultimoSigno->frecuencia_respiratoria : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->frecuencia_respiratoria !== null ? "{$ultimoSigno->frecuencia_respiratoria} rpm" : 'Sin datos',
                'min' => $statsFr['min'],
                'max' => $statsFr['max'],
                'promedio' => $statsFr['avg'],
                'variacion' => ($ultimoSigno?->frecuencia_respiratoria !== null && $penultimoSigno?->frecuencia_respiratoria !== null) ? (($ultimoSigno->frecuencia_respiratoria - $penultimoSigno->frecuencia_respiratoria >= 0 ? '↑ +' : '↓ ').($ultimoSigno->frecuencia_respiratoria - $penultimoSigno->frecuencia_respiratoria).' rpm') : 'Sin registros comparables',
                'tendencia' => ($ultimoSigno?->frecuencia_respiratoria !== null && $penultimoSigno?->frecuencia_respiratoria !== null) ? 'Tendencia calculada con registros reales' : 'Datos insuficientes',
                'icon' => 'ph-wind',
                'color' => '#06B6D4',
                'sparkline' => $fr,
            ],
            'DOLOR' => [
                'estado' => 'Sin datos', 'estado_badge' => $sinDatosBadge,
                'clave' => 'DOLOR',
                'nombre' => 'Dolor EVA',
                'titulo_grafico' => 'NIVEL DE DOLOR (ESCALA EVA)',
                'subtitulo' => 'Monitoreo de dolor percibido y respuesta analgésica',
                'unidad' => 'Escala 0-10',
                'ultimo' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) !== null ? ($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor).'/10' : 'Sin datos',
                'ultimo_fmt' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) !== null ? ($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor).'/10' : 'Sin datos',
                'min' => $statsDolor['min'],
                'max' => $statsDolor['max'],
                'promedio' => $statsDolor['avg'],
                'variacion' => ($ultimoSigno && $penultimoSigno && ($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor) !== null && ($penultimoSigno->dolor ?? $penultimoSigno->nivel_dolor) !== null) ? (($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor) - ($penultimoSigno->dolor ?? $penultimoSigno->nivel_dolor) >= 0 ? '↑ +' : '↓ ').(($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor) - ($penultimoSigno->dolor ?? $penultimoSigno->nivel_dolor)) : 'Sin registros comparables',
                'tendencia' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) !== null ? 'Basado en la última valoración registrada' : 'Datos insuficientes',
                'icon' => 'ph-smiley-meh',
                'color' => '#8B5CF6',
                'sparkline' => $dolor,
            ],
            'GLUCOSA' => [
                'clave' => 'GLUCOSA',
                'nombre' => 'Glucemia',
                'titulo_grafico' => 'GLUCEMIA CAPILAR',
                'subtitulo' => 'Control de glucosa en sangre en ayunas y postprandial',
                'unidad' => 'mg/dL',
                'ultimo' => $ultimoSigno?->glucosa !== null ? "{$ultimoSigno->glucosa} mg/dL" : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->glucosa !== null ? "{$ultimoSigno->glucosa} mg/dL" : 'Sin datos',
                'min' => $statsGlucosa['min'],
                'max' => $statsGlucosa['max'],
                'promedio' => $statsGlucosa['avg'],
                'variacion' => 'Sin registros comparables',
                'tendencia' => $ultimoSigno?->glucosa !== null ? 'Basado en el último registro' : 'Datos insuficientes',
                'icon' => 'ph-drop-half-bottom',
                'color' => '#0284C7',
                'sparkline' => $glucosa,
            ],
            'PESO' => [
                'estado' => $ultimoSigno?->peso === null ? 'Sin datos' : 'Registrado', 'estado_badge' => $sinDatosBadge,
                'clave' => 'PESO',
                'nombre' => 'Peso corporal',
                'titulo_grafico' => 'PESO CORPORAL',
                'subtitulo' => 'Monitoreo ponderal continuo',
                'unidad' => 'kg',
                'ultimo' => $ultimoSigno?->peso !== null ? "{$ultimoSigno->peso} kg" : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->peso !== null ? "{$ultimoSigno->peso} kg" : 'Sin datos',
                'min' => $statsPeso['min'],
                'max' => $statsPeso['max'],
                'promedio' => $statsPeso['avg'],
                'variacion' => 'Sin registros comparables',
                'tendencia' => $ultimoSigno?->peso !== null ? 'Basado en el último registro' : 'Datos insuficientes',
                'icon' => 'ph-scales',
                'color' => '#64748B',
                'sparkline' => $peso,
            ],
        ];

        $lector = app(InterpretacionSignosVitales::class);
        $interpretaciones = $lector->coleccion($historialCompleto);
        $registrosHistoricos = $historialCompleto->reverse()->map(
            fn ($s) => $lector->fila($s, $interpretaciones[$s->cod_signo])
        )->values();
        foreach (InterpretacionSignosVitales::VARIABLES as $clave => $variables) {
            $estado = $ultimoSigno ? $interpretaciones[$ultimoSigno->cod_signo]['metricas'][$clave] : $lector->resumir([]);
            $metricasInfo[$clave]['estado'] = $estado['label'];
            $metricasInfo[$clave]['estado_badge'] = $estado['estado_badge'];
            $metricasInfo[$clave]['criterio'] = $estado['message'];
            $metricasInfo[$clave]['resultados'] = $estado['resultados'];
        }
        $puntosInterpretados = $signosOrdenados->map(
            fn ($s) => $interpretaciones[$s->cod_signo]['metricas']
        )->values()->all();
        $filasPorCodigo = $registrosHistoricos->keyBy('id');
        $eventosRelevantes = $signosOrdenados->reverse()->map(fn ($s) => $filasPorCodigo[$s->cod_signo])->map(fn (array $fila) => [
            'fecha_hora' => trim($fila['fecha'].' '.$fila['hora']),
            'evento' => 'Control de signos vitales', 'motivo' => $fila['criterio'],
            'valor' => $fila['pa'] !== '--' ? $fila['pa'].' mmHg' : 'Ver mediciones',
            'estado' => $fila['estado'], 'badge_bg' => $fila['badgeClass'],
            'icon' => 'ph-heartbeat', 'icon_color' => 'text-[var(--rm-text-primary)]', 'registro' => $fila,
        ]);

        $metricaActivaKey = $metricaSeleccionada ?? 'PA';
        $resumenPeriodoActivo = $metricasInfo[$metricaActivaKey] ?? $metricasInfo['PA'];
        if ($ultimoSigno && $ultimoSigno->fecha) {
            $cUltFecha = $ultimoSigno->fecha instanceof Carbon ? $ultimoSigno->fecha : Carbon::parse(substr((string) $ultimoSigno->fecha, 0, 10));
            $ultHoraFmt = $ultimoSigno->hora ? substr((string) $ultimoSigno->hora, 0, 5) : null;
            $resumenPeriodoActivo['ultimo_registro_fecha'] = trim($cUltFecha->format('d/m/Y').' '.($ultHoraFmt ?? ''));
        } else {
            $resumenPeriodoActivo['ultimo_registro_fecha'] = 'Sin registros';
        }

        foreach ($metricasInfo as &$metrica) {
            $metrica['ultimo_registro_fecha'] = $resumenPeriodoActivo['ultimo_registro_fecha'];
        }
        unset($metrica);

        return [
            'alertas_total' => $alertasTotal,
            'alertas_activas' => $alertasActivas,
            'alertas_cerradas' => $alertasCerradas,
            'total_admin' => $totalAdmin,
            'admin_ok' => $adminOk,
            'admin_omitidas' => $adminOmitidas,
            'adherencia_pct' => $adherenciaPct,
            'total_tareas' => $totalTareas,
            'tareas_realizadas' => $tareasRealizadas,
            'cumplimiento_pct' => $cumplimientoPct,
            'total_seguimientos' => $totalSeguimientos,
            'total_incidentes' => $totalIncidentes,
            'ultima_valoracion' => $ultimaValoracion,
            'grafica_signos' => [
                'labels' => $labels,
                'labels_largas' => $labelsLargas,
                'sistolica' => $sistolica,
                'diastolica' => $diastolica,
                'fc' => $fc,
                'spo2' => $spo2,
                'temp' => $temp,
                'fr' => $fr,
                'dolor' => $dolor,
                'glucosa' => $glucosa,
                'peso' => $peso,
                'ultimo' => $ultimoSigno,
                'metricas_info' => $metricasInfo,
                'registros_historicos' => $registrosHistoricos->all(),
                'interpretaciones' => $puntosInterpretados,
                'resumen_periodo' => $resumenPeriodoActivo,
                'eventos_relevantes' => $eventosRelevantes->take(6)->values(),
            ],
        ];
    }
}
