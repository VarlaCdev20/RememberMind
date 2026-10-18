@include('clinica::livewire.cuidados.ficha.resumen.estado-evolucion')
@include('clinica::livewire.cuidados.ficha.resumen.evolucion-comparada')
@include('clinica::livewire.cuidados.ficha.resumen.memoria-estado-cognitivo', ['resumenCognitivo' => $this->resumenCognitivo])

{{-- RESUMEN CLÍNICO CONFORMADO EXACTAMENTE A LA IMAGEN DE REFERENCIA --}}
@php
    $resumenCognitivo = $this->resumenCognitivo;
    $resLong = $this->resumenLongitudinal ?? [];
    $grafica = $resLong['grafica_signos'] ?? [];
    $labels = $grafica['labels'] ?? [];
    $cantPuntos = count($labels);
    $ultimoSigno = $grafica['ultimo'] ?? ($adultoMayor->signosVitales->first() ?? null);

    // Dimensiones para gráfico de líneas
    $width = 360;
    $height = 110;
    $paddingX = 20;
    $paddingY = 16;
    $usableW = $width - ($paddingX * 2);
    $usableH = $height - ($paddingY * 2);

    // 1. PA
    $sisData = array_slice($grafica['sistolica'] ?? [], -7);
    $diaData = array_slice($grafica['diastolica'] ?? [], -7);
    $totalPtsPa = count($sisData);
    $sisPoints = [];
    $diaPoints = [];
    $ultimoSis = null;
    $ultimoDia = null;

    if ($totalPtsPa >= 2) {
        foreach ($sisData as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsPa - 1)));
            if ($val === null) {
                continue;
            }
            $valSis = $val;
            $ySis = $height - $paddingY - ((($valSis - 70) / 110) * $usableH);
            $sisPoints[] = round($x, 1) . ',' . round($ySis, 1);

            $valDia = $diaData[$idx] ?? null;
            if ($valDia !== null) {
                $yDia = $height - $paddingY - ((($valDia - 50) / 90) * $usableH);
                $diaPoints[] = round($x, 1) . ',' . round($yDia, 1);
            }
        }
        $ultimoSis = end($sisData);
        $ultimoDia = end($diaData);
    } elseif ($ultimoSigno) {
        if ($ultimoSigno->presion_arterial && str_contains($ultimoSigno->presion_arterial, '/')) {
            $parts = explode('/', $ultimoSigno->presion_arterial);
            $ultimoSis = trim($parts[0]);
            $ultimoDia = trim($parts[1]);
        } else {
            $ultimoSis = $ultimoSigno->presion_sistolica;
            $ultimoDia = $ultimoSigno->presion_diastolica;
        }
    }
    $sisPoly = implode(' ', $sisPoints);
    $diaPoly = implode(' ', $diaPoints);

    // 2. FC
    $fcData = array_slice($grafica['fc'] ?? [], -7);
    $totalPtsFc = count($fcData);
    $fcPoints = [];
    $ultimoFc = $ultimoSigno?->frecuencia_cardiaca;
    if ($totalPtsFc >= 2) {
        foreach ($fcData as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsFc - 1)));
            if ($val === null) {
                continue;
            }
            $valFc = $val;
            $yFc = $height - $paddingY - ((($valFc - 50) / 70) * $usableH);
            $fcPoints[] = round($x, 1) . ',' . round($yFc, 1);
        }
        $ultimoFc = end($fcData) ?: $ultimoFc;
    }
    $fcPoly = implode(' ', $fcPoints);

    // 3. SpO2
    $spo2Data = array_slice($grafica['spo2'] ?? [], -7);
    $totalPtsSpo2 = count($spo2Data);
    $spo2Points = [];
    $ultimoSpo2 = $ultimoSigno?->saturacion_oxigeno ?? $ultimoSigno?->saturacion;
    if ($totalPtsSpo2 >= 2) {
        foreach ($spo2Data as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsSpo2 - 1)));
            if ($val === null) {
                continue;
            }
            $valSpo2 = $val;
            $ySpo2 = $height - $paddingY - ((($valSpo2 - 88) / 12) * $usableH);
            $spo2Points[] = round($x, 1) . ',' . round($ySpo2, 1);
        }
        $ultimoSpo2 = end($spo2Data) ?: $ultimoSpo2;
    }
    $spo2Poly = implode(' ', $spo2Points);

    // 4. Temp
    $tempData = array_slice($grafica['temp'] ?? [], -7);
    $totalPtsTemp = count($tempData);
    $tempPoints = [];
    $ultimoTemp = $ultimoSigno?->temperatura;
    if ($totalPtsTemp >= 2) {
        foreach ($tempData as $idx => $val) {
            $x = $paddingX + ($idx * ($usableW / max(1, $totalPtsTemp - 1)));
            if ($val === null) {
                continue;
            }
            $valTemp = $val;
            $yTemp = $height - $paddingY - ((($valTemp - 35.0) / 4.0) * $usableH);
            $tempPoints[] = round($x, 1) . ',' . round($yTemp, 1);
        }
        $ultimoTemp = end($tempData) ?: $ultimoTemp;
    }
    $tempPoly = implode(' ', $tempPoints);

    // Relaciones clave
    $ultFunc = $adultoMayor->valoracionesFuncionales->sortByDesc('fecha_hora')->first();
    $ultDolor = $adultoMayor->valoracionesDolor->sortByDesc('fecha_hora')->first();
    $plan = $adultoMayor->planCuidadoActivo;
    $proxAdmin = $adultoMayor->administracionesMedicacion
        ->filter(fn ($a) => $a->fecha_hora_programada && $a->fecha_hora_programada->gte(now()) && ! $a->fecha_hora_administracion && ! in_array($a->estado, ['OMITIDA', 'ANULADA', 'CANCELADA'], true))
        ->sortBy('fecha_hora_programada')
        ->take(3);
    $proxTareas = $adultoMayor->ejecucionesCuidado
        ->filter(fn ($e) => $e->fecha_hora_programada && $e->fecha_hora_programada->gte(now()) && in_array($e->estado, ['PENDIENTE', 'PROGRAMADA', 'EN_PROCESO'], true))
        ->sortBy('fecha_hora_programada')
        ->take(3);
    $proxActividades = $adultoMayor->actividades
        ->filter(fn ($a) => $a->fecha_hora && $a->fecha_hora->gte(now()) && ! in_array($a->estado, ['CANCELADA', 'ANULADA', 'FINALIZADA', 'REALIZADA'], true))
        ->sortBy('fecha_hora')
        ->take(3);
    $notasRelevantes = $adultoMayor->notasClinicas->take(2);
    $ingestaHoy = $adultoMayor->registrosIngesta->first(fn ($r) => $r->fecha_hora?->isToday());
    $hidratacionHoy = $adultoMayor->registrosHidratacion->filter(fn ($r) => $r->fecha_hora?->isToday());
    $hidratacionHoyMl = $hidratacionHoy->sum(fn ($r) => (float) $r->cantidad_ml);
    $movilidadHoy = $adultoMayor->registrosMovilidad->first(fn ($r) => $r->fecha_hora?->isToday());
    $asignacionActiva = $adultoMayor->asignacionesJornada->first(fn ($a) => in_array($a->estado, ['ACTIVA', 'ACTIVO'], true));
    $alertasAct = $adultoMayor->alertas->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
@endphp

{{-- GRID DE 3 COLUMNAS EXACTO COMO LA IMAGEN --}}
<div class="rm-clinical-summary-grid">

    {{-- ========================================================================= --}}
    @include('clinica::livewire.cuidados.ficha.resumen.informacion-clinica')
    @include('clinica::livewire.cuidados.ficha.resumen.estado-cuidados')
    @include('clinica::livewire.cuidados.ficha.resumen.tendencias-continuidad')
</div>
