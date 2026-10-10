<?php

namespace App\Modulos\Clinica\Aplicacion\Consultas;

use App\Models\EjecucionCuidado;
use App\Models\Residente;
use Carbon\Carbon;

final class ResumenLongitudinalResidente
{
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
        $todosSignosOrdenados = $residente->signosVitales
            ->sortBy(fn ($s) => ($s->fecha ? (is_string($s->fecha) ? substr($s->fecha, 0, 10) : $s->fecha->format('Y-m-d')) : '2000-01-01').' '.($s->hora ? substr((string) $s->hora, 0, 5) : '00:00'))
            ->values();

        // Filtrar según el periodo activo: '24h', '7d', '30d', '3m', 'personalizado'
        $periodo = $periodo ?? '7d';
        $ahora = now();

        $signosOrdenados = match ($periodo) {
            '24h' => $todosSignosOrdenados->filter(function ($s) use ($ahora) {
                $fStr = $s->fecha ? (is_string($s->fecha) ? substr($s->fecha, 0, 10) : $s->fecha->format('Y-m-d')) : '';

                return $fStr ? Carbon::parse($fStr)->diffInHours($ahora) <= 36 : true;
            })->values(),
            '7d' => $todosSignosOrdenados->filter(function ($s) use ($ahora) {
                $fStr = $s->fecha ? (is_string($s->fecha) ? substr($s->fecha, 0, 10) : $s->fecha->format('Y-m-d')) : '';

                return $fStr ? Carbon::parse($fStr)->diffInDays($ahora) <= 7 : true;
            })->values(),
            '30d' => $todosSignosOrdenados->filter(function ($s) use ($ahora) {
                $fStr = $s->fecha ? (is_string($s->fecha) ? substr($s->fecha, 0, 10) : $s->fecha->format('Y-m-d')) : '';

                return $fStr ? Carbon::parse($fStr)->diffInDays($ahora) <= 30 : true;
            })->values(),
            '3m' => $todosSignosOrdenados->filter(function ($s) use ($ahora) {
                $fStr = $s->fecha ? (is_string($s->fecha) ? substr($s->fecha, 0, 10) : $s->fecha->format('Y-m-d')) : '';

                return $fStr ? Carbon::parse($fStr)->diffInDays($ahora) <= 90 : true;
            })->values(),
            default => $todosSignosOrdenados,
        };

        // Si el filtro temporal deja menos de 2 registros pero existen tomas previas, conservar al menos las últimas tomas
        if ($signosOrdenados->count() < 2 && $todosSignosOrdenados->isNotEmpty()) {
            $takeCount = match ($periodo) {
                '24h' => 4,
                '7d' => 8,
                '30d' => 15,
                default => 20,
            };
            $signosOrdenados = $todosSignosOrdenados->take(-$takeCount)->values();
        }

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

        // Determinación de estado clínico y variaciones
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
                'subtitulo' => 'Evolución de presión sistólica y diastólica con rangos de normalidad',
                'unidad' => 'mmHg',
                'ultimo' => ($ultPas !== null && $ultPad !== null) ? "{$ultPas}/{$ultPad}" : ($ultimoSigno?->presion_arterial ?: 'Sin datos'),
                'ultimo_fmt' => ($ultPas !== null && $ultPad !== null) ? "{$ultPas}/{$ultPad} mmHg" : ($ultimoSigno?->presion_arterial ? "{$ultimoSigno->presion_arterial} mmHg" : 'Sin datos'),
                'estado' => ($ultPas === null || $ultPad === null) ? 'Sin datos' : (($ultPas >= 140 || $ultPad >= 90) ? 'Elevada' : ($ultPas < 90 ? 'Hipotensión' : 'Normal')),
                'estado_badge' => ($ultPas === null || $ultPad === null) ? $sinDatosBadge : (($ultPas >= 140 || $ultPad >= 90) ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'estado' => $ultimoSigno?->frecuencia_cardiaca === null ? 'Sin datos' : (($ultimoSigno->frecuencia_cardiaca > 100) ? 'Taquicardia' : (($ultimoSigno->frecuencia_cardiaca < 55) ? 'Bradicardia' : 'Normal')),
                'estado_badge' => $ultimoSigno?->frecuencia_cardiaca === null ? $sinDatosBadge : (($ultimoSigno->frecuencia_cardiaca > 100 || $ultimoSigno->frecuencia_cardiaca < 55) ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'estado' => $ultimoSigno?->saturacion === null ? 'Sin datos' : (($ultimoSigno->saturacion < 92) ? 'Desaturación' : (($ultimoSigno->saturacion < 95) ? 'Aceptable' : 'Normal')),
                'estado_badge' => $ultimoSigno?->saturacion === null ? $sinDatosBadge : (($ultimoSigno->saturacion < 92) ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'estado' => $ultimoSigno?->temperatura === null ? 'Sin datos' : (($ultimoSigno->temperatura >= 38.0) ? 'Fiebre' : (($ultimoSigno->temperatura >= 37.3) ? 'Febrícula' : 'Afebril')),
                'estado_badge' => $ultimoSigno?->temperatura === null ? $sinDatosBadge : (($ultimoSigno->temperatura >= 37.5) ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'estado' => $ultimoSigno?->frecuencia_respiratoria === null ? 'Sin datos' : (($ultimoSigno->frecuencia_respiratoria > 22) ? 'Taquipnea' : (($ultimoSigno->frecuencia_respiratoria < 12) ? 'Bradipnea' : 'Eupnea')),
                'estado_badge' => $ultimoSigno?->frecuencia_respiratoria === null ? $sinDatosBadge : (($ultimoSigno->frecuencia_respiratoria > 22 || $ultimoSigno->frecuencia_respiratoria < 12) ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'clave' => 'DOLOR',
                'nombre' => 'Dolor EVA',
                'titulo_grafico' => 'NIVEL DE DOLOR (ESCALA EVA)',
                'subtitulo' => 'Monitoreo de dolor percibido y respuesta analgésica',
                'unidad' => 'Escala 0-10',
                'ultimo' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) !== null ? ($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor).'/10' : 'Sin datos',
                'ultimo_fmt' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) !== null ? ($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor).'/10' : 'Sin datos',
                'estado' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) === null ? 'Sin datos' : ((($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor) >= 4) ? 'Moderado' : ((($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor) > 0) ? 'Leve' : 'Sin dolor')),
                'estado_badge' => ($ultimoSigno?->dolor ?? $ultimoSigno?->nivel_dolor) === null ? $sinDatosBadge : ((($ultimoSigno->dolor ?? $ultimoSigno->nivel_dolor) >= 4) ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'estado' => $ultimoSigno?->glucosa === null ? 'Sin datos' : (($ultimoSigno->glucosa > 140) ? 'Hiperglucemia' : (($ultimoSigno->glucosa < 70) ? 'Hipoglucemia' : 'Normal')),
                'estado_badge' => $ultimoSigno?->glucosa === null ? $sinDatosBadge : (($ultimoSigno->glucosa > 140 || $ultimoSigno->glucosa < 70) ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200'),
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
                'clave' => 'PESO',
                'nombre' => 'Peso corporal',
                'titulo_grafico' => 'PESO CORPORAL',
                'subtitulo' => 'Monitoreo ponderal continuo',
                'unidad' => 'kg',
                'ultimo' => $ultimoSigno?->peso !== null ? "{$ultimoSigno->peso} kg" : 'Sin datos',
                'ultimo_fmt' => $ultimoSigno?->peso !== null ? "{$ultimoSigno->peso} kg" : 'Sin datos',
                'estado' => $ultimoSigno?->peso === null ? 'Sin datos' : 'Registrado',
                'estado_badge' => $ultimoSigno?->peso === null ? $sinDatosBadge : 'bg-blue-100 text-blue-800 border-blue-200',
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

        // Detección de Eventos Relevantes a partir de los controles reales
        $eventosRelevantes = collect();
        foreach ($todosSignosOrdenados->reverse() as $s) {
            $pasVal = $s->presion_sistolica ?? (explode('/', $s->presion_arterial ?? '')[0] ?? null);
            $padVal = $s->presion_diastolica ?? (explode('/', $s->presion_arterial ?? '')[1] ?? null);

            $fechaHoraFmt = '';
            if ($s->fecha) {
                $cFecha = $s->fecha instanceof Carbon ? $s->fecha : Carbon::parse(substr((string) $s->fecha, 0, 10));
                $horaFmt = '';
                if ($s->hora) {
                    if (preg_match('/(\d{1,2}:\d{2})/', (string) $s->hora, $m)) {
                        $horaFmt = $m[1];
                    } else {
                        $horaFmt = substr((string) $s->hora, 0, 5);
                    }
                }
                $fechaHoraFmt = $cFecha->translatedFormat('d M Y').($horaFmt ? " · {$horaFmt}" : '');
            }

            // Hallazgo PA
            if ($pasVal && $padVal && ($pasVal >= 140 || $padVal >= 90)) {
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => 'Presión arterial elevada',
                    'motivo' => $s->observacion ?: "Sistólica/Diastólica ({$pasVal}/{$padVal} mmHg) superior a 140/90",
                    'valor' => "{$pasVal}/{$padVal} mmHg",
                    'estado' => 'Elevada',
                    'badge_bg' => 'bg-rose-100 text-rose-800 border-rose-200',
                    'icon' => 'ph-heartbeat',
                    'icon_color' => 'text-rose-600',
                    'registro' => $s,
                ]);
            } elseif ($pasVal && $pasVal < 90) {
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => 'Hipotensión registrada',
                    'motivo' => $s->observacion ?: "Sistólica ({$pasVal} mmHg) por debajo de 90 mmHg",
                    'valor' => "{$pasVal}/{$padVal} mmHg",
                    'estado' => 'Vigilancia',
                    'badge_bg' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'icon' => 'ph-heartbeat',
                    'icon_color' => 'text-amber-600',
                    'registro' => $s,
                ]);
            }

            // Hallazgo Temperatura
            if ($s->temperatura && $s->temperatura >= 37.5) {
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => $s->temperatura >= 38.0 ? 'Fiebre registrada' : 'Febrícula',
                    'motivo' => $s->observacion ?: ($s->temperatura >= 38.0 ? 'Temperatura corporal en rango febril (≥38.0 °C)' : 'Alza térmica reactiva (≥37.5 °C)'),
                    'valor' => "{$s->temperatura} °C",
                    'estado' => $s->temperatura >= 38.0 ? 'Fiebre' : 'Vigilancia',
                    'badge_bg' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'icon' => 'ph-thermometer',
                    'icon_color' => 'text-amber-600',
                    'registro' => $s,
                ]);
            }

            // Hallazgo SpO2
            if ($s->saturacion && $s->saturacion < 92) {
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => 'Desaturación de oxígeno',
                    'motivo' => $s->observacion ?: 'Saturación periférica SpO₂ en rango de riesgo (<92%)',
                    'valor' => "{$s->saturacion}% SpO₂",
                    'estado' => 'Alerta',
                    'badge_bg' => 'bg-rose-100 text-rose-800 border-rose-200',
                    'icon' => 'ph-drop',
                    'icon_color' => 'text-rose-600',
                    'registro' => $s,
                ]);
            }

            // Hallazgo Frecuencia Cardíaca
            if ($s->frecuencia_cardiaca && ($s->frecuencia_cardiaca > 100 || $s->frecuencia_cardiaca < 55)) {
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => $s->frecuencia_cardiaca > 100 ? 'Taquicardia en reposo' : 'Bradicardia',
                    'motivo' => $s->observacion ?: ($s->frecuencia_cardiaca > 100 ? 'Frecuencia cardíaca acelerada (>100 lpm)' : 'Frecuencia cardíaca disminuida (<55 lpm)'),
                    'valor' => "{$s->frecuencia_cardiaca} lpm",
                    'estado' => 'Atención',
                    'badge_bg' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'icon' => 'ph-activity',
                    'icon_color' => 'text-amber-600',
                    'registro' => $s,
                ]);
            }

            // Hallazgo Dolor
            $dolorVal = $s->dolor ?? $s->nivel_dolor;
            if ($dolorVal !== null && $dolorVal >= 4) {
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => 'Dolor moderado manifestado',
                    'motivo' => $s->observacion ?: "Puntuación de dolor de {$dolorVal}/10 en escala EVA",
                    'valor' => "{$dolorVal}/10 EVA",
                    'estado' => 'Dolor',
                    'badge_bg' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'icon' => 'ph-smiley-sad',
                    'icon_color' => 'text-amber-600',
                    'registro' => $s,
                ]);
            }
        }

        // Si no hay alteraciones, mostrar controles reales sin inferir valores ni diagnósticos.
        if ($eventosRelevantes->isEmpty() && $todosSignosOrdenados->isNotEmpty()) {
            foreach ($todosSignosOrdenados->reverse()->take(3) as $s) {
                $fechaHoraFmt = '';
                if ($s->fecha) {
                    $cFecha = $s->fecha instanceof Carbon ? $s->fecha : Carbon::parse(substr((string) $s->fecha, 0, 10));
                    $horaFmt = $s->hora ? substr((string) $s->hora, 0, 5) : '';
                    $fechaHoraFmt = $cFecha->translatedFormat('d M Y').($horaFmt ? " · {$horaFmt}" : '');
                }
                $eventosRelevantes->push([
                    'fecha_hora' => $fechaHoraFmt ?: 'Reciente',
                    'evento' => 'Control hemodinámico de rutina',
                    'motivo' => $s->observacion ?: 'Control registrado sin observaciones adicionales.',
                    'valor' => collect([
                        $s->presion_arterial ? "{$s->presion_arterial} mmHg" : null,
                        $s->frecuencia_cardiaca !== null ? "{$s->frecuencia_cardiaca} lpm" : null,
                        $s->saturacion !== null ? "{$s->saturacion}% SpO₂" : null,
                        $s->temperatura !== null ? "{$s->temperatura} °C" : null,
                    ])->filter()->join(' · ') ?: 'Sin valores cuantitativos registrados',
                    'estado' => 'Registrado',
                    'badge_bg' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'icon' => 'ph-check-circle',
                    'icon_color' => 'text-emerald-600',
                    'registro' => $s,
                ]);
            }
        }

        $metricaActivaKey = $metricaSeleccionada ?? 'PA';
        $resumenPeriodoActivo = $metricasInfo[$metricaActivaKey] ?? $metricasInfo['PA'];
        if ($ultimoSigno && $ultimoSigno->fecha) {
            $cUltFecha = $ultimoSigno->fecha instanceof Carbon ? $ultimoSigno->fecha : Carbon::parse(substr((string) $ultimoSigno->fecha, 0, 10));
            $ultHoraFmt = $ultimoSigno->hora ? substr((string) $ultimoSigno->hora, 0, 5) : null;
            $resumenPeriodoActivo['ultimo_registro_fecha'] = trim($cUltFecha->format('d/m/Y').' '.($ultHoraFmt ?? ''));
        } else {
            $resumenPeriodoActivo['ultimo_registro_fecha'] = 'Sin registros';
        }

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
                'resumen_periodo' => $resumenPeriodoActivo,
                'eventos_relevantes' => $eventosRelevantes->take(6)->values(),
            ],
        ];
    }
}
