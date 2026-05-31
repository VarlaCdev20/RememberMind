<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Individual - {{ $adulto->cod_am }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #2F3E5C; font-size: 11px; line-height: 1.4; }
        .header { border-bottom: 2px solid #2F3E5C; padding-bottom: 20px; margin-bottom: 20px; }
        .logo-text { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .sub-logo { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .title { text-align: right; }
        .title h2 { font-size: 18px; margin: 0; text-transform: uppercase; }
        .title p { margin: 2px 0; color: #666; font-weight: bold; }
        
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .col-left { width: 35%; padding-right: 20px; vertical-align: top; }
        .col-right { width: 65%; vertical-align: top; }
        
        .card { border: 1px solid #CBBBAA; background-color: #FDFBFA; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
        .photo-box { width: 120px; height: 120px; border: 3px solid #fff; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin: 0 auto 10px; background-color: #E7DDD2; text-align: center; overflow: hidden; border-radius: 15px; }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .name { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .status { text-align: center; }
        .status-badge { background-color: #8EA17D; color: #fff; padding: 2px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        
        .data-row { border-bottom: 1px solid #EEE; padding: 5px 0; }
        .data-label { font-size: 9px; color: #999; text-transform: uppercase; font-weight: bold; }
        .data-value { float: right; font-weight: bold; }
        
        .section-title { font-size: 12px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #EEE; padding-bottom: 5px; margin-bottom: 10px; color: #2F3E5C; }
        .eval-card { background-color: #F2EBE3; padding: 10px; border-radius: 8px; margin-bottom: 10px; }
        .eval-header { font-weight: bold; font-size: 11px; margin-bottom: 3px; }
        .eval-meta { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .risk-badge { background-color: #D96F58; color: #fff; padding: 1px 6px; border-radius: 4px; font-size: 8px; float: right; }
        
        .aten-row { padding: 8px 0; border-bottom: 1px solid #F0F0F0; }
        .aten-date { width: 40px; color: #E27D60; font-weight: bold; font-size: 9px; }
        .aten-content { font-size: 10px; }
        
        .footer { margin-top: 50px; text-align: center; border-top: 1px solid #EEE; pt: 20px; }
        .signature-box { width: 45%; display: inline-block; text-align: center; margin-top: 40px; }
        .signature-line { border-top: 1px solid #2F3E5C; width: 80%; margin: 0 auto 5px; }
        .signature-text { font-size: 9px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <table width="100%">
            <tr>
                <td width="60%">
                    <span class="logo-text">RememberMind</span><br>
                    <span class="sub-logo">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS - Gestión Gerontológica</span>
                </td>
                <td class="title">
                    <h2>Ficha Técnica Individual</h2>
                    <p>Expediente: {{ $adulto->cod_am }}</p>
                    <p>Fecha: {{ now()->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="grid">
        <tr>
            <td class="col-left">
                <div class="card">
                    <div class="photo-box">
                        @if($adulto->foto)
                            <img src="{{ public_path('storage/' . $adulto->foto) }}">
                        @else
                            <div style="padding-top: 40px; color: #CCC; font-size: 40px;">?</div>
                        @endif
                    </div>
                    <div class="name">{{ $adulto->nombres }}<br>{{ $adulto->ap_paterno }}</div>
                    <div class="status">
                        <span class="status-badge">{{ $adulto->estado->estado ?? 'ACTIVO' }}</span>
                    </div>
                    
                    <div style="margin-top: 15px;">
                        <div class="data-row"><span class="data-label">C.I.</span> <span class="data-value">{{ $adulto->ci }}</span></div>
                        <div class="data-row"><span class="data-label">Edad</span> <span class="data-value">{{ $adulto->edad }} años</span></div>
                        <div class="data-row"><span class="data-label">Género</span> <span class="data-value">{{ $adulto->genero }}</span></div>
                        <div class="data-row"><span class="data-label">Nacimiento</span> <span class="data-value">{{ $adulto->fecha_nac?->format('d/m/Y') }}</span></div>
                        <div class="data-row"><span class="data-label">Ingreso</span> <span class="data-value">{{ $adulto->fecha_ing?->format('d/m/Y') }}</span></div>
                    </div>
                </div>

                <div class="card">
                    <div class="section-title">Contactos</div>
                    @forelse($familiares as $fam)
                        <div style="margin-bottom: 8px;">
                            <div style="font-weight: bold; font-size: 10px;">{{ $fam->usuario->name }}</div>
                            <div style="font-size: 8px; color: #666;">{{ $fam->pivot->parentesco_vinculo }} · {{ $fam->usuario->telefono }}</div>
                        </div>
                    @empty
                        <div style="font-size: 9px; color: #999;">Sin contactos registrados.</div>
                    @endforelse
                </div>
            </td>
            <td class="col-right">
                {{-- Ficha Médica --}}
                @if($fichasMedicas->count() > 0)
                    <div class="section-title">Ficha Médica Activa</div>
                    <div class="eval-card" style="margin-bottom: 15px;">
                        @php $ficha = $fichasMedicas->first(); @endphp
                        <div class="eval-header">Tipo de Sangre: {{ $ficha->grupo_sanguineo ?? 'N/D' }}</div>
                        <div style="font-size: 9px; margin-top: 5px;"><strong>Alergias:</strong> {{ $ficha->alergias ?? 'Ninguna registrada' }}</div>
                        <div style="font-size: 9px;"><strong>Enfermedades Base:</strong> {{ $ficha->enfermedades_base ?? 'Ninguna registrada' }}</div>
                        <div style="font-size: 9px;"><strong>Cirugías Previas:</strong> {{ $ficha->cirugias_previas ?? 'Ninguna registrada' }}</div>
                    </div>
                @endif

                {{-- Signos Vitales y Valoración Funcional --}}
                <table width="100%" style="margin-bottom: 15px;">
                    <tr>
                        <td width="50%" style="vertical-align: top; padding-right: 10px;">
                            <div class="section-title">Últimos Signos Vitales</div>
                            @if($signosVitales->count() > 0)
                                @php $signo = $signosVitales->first(); @endphp
                                <div style="font-size: 9px;">
                                    <strong>Fecha:</strong> {{ $signo->fecha->format('d/m/Y') }} {{ $signo->hora }}<br>
                                    <strong>P.A.:</strong> {{ $signo->presion_arterial ?? 'N/D' }} mmHg<br>
                                    <strong>F.C.:</strong> {{ $signo->frecuencia_cardiaca ?? 'N/D' }} lpm<br>
                                    <strong>F.R.:</strong> {{ $signo->frecuencia_respiratoria ?? 'N/D' }} rpm<br>
                                    <strong>Temp:</strong> {{ $signo->temperatura ?? 'N/D' }} °C<br>
                                    <strong>Sat O2:</strong> {{ $signo->saturacion_oxigeno ?? 'N/D' }} %
                                </div>
                            @else
                                <div style="font-size: 9px; color: #999;">Sin registros.</div>
                            @endif
                        </td>
                        <td width="50%" style="vertical-align: top;">
                            <div class="section-title">Valoración Funcional Institucional</div>
                            @if($valoracionesFuncionales->count() > 0)
                                @php $valFunc = $valoracionesFuncionales->first(); @endphp
                                <div style="font-size: 9px;">
                                    <strong>Fecha:</strong> {{ $valFunc->fecha_valoracion->format('d/m/Y') }}<br>
                                    <strong>Act. Básicas:</strong> {{ $valFunc->puntuacion_barthel ?? 'N/D' }} pts.<br>
                                    <strong>Nivel Dependencia:</strong> {{ $valFunc->nivel_dependencia_barthel ?? 'N/D' }}<br>
                                    <strong>Act. Instrumentales:</strong> {{ $valFunc->puntuacion_lawton_brody ?? 'N/D' }} pts.<br>
                                    <strong>Nivel Dependencia:</strong> {{ $valFunc->nivel_dependencia_lawton ?? 'N/D' }}
                                </div>
                            @else
                                <div style="font-size: 9px; color: #999;">Sin registros.</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="section-title">Medicación Activa</div>
                @forelse($medicaciones->where('estado', 'ACTIVO') as $med)
                    <div class="aten-row" style="padding: 4px 0;">
                        <table width="100%">
                            <tr>
                                <td class="aten-content">
                                    <div style="font-weight: bold; font-size: 10px;">{{ $med->nombre_medicamento }} - {{ $med->dosis }}</div>
                                    <div style="color: #666; font-size: 9px;">{{ $med->frecuencia }} | Vía: {{ $med->via_administracion }}</div>
                                </td>
                                <td align="right" style="font-size: 9px; color: #E27D60; font-weight: bold;">
                                    {{ $med->fecha_inicio?->format('d/m/Y') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                @empty
                    <div style="font-size: 10px; color: #999; margin-bottom: 15px;">No hay medicación activa.</div>
                @endforelse

                <div class="section-title" style="margin-top: 15px;">Perfil Cognitivo</div>
                @forelse($evaluaciones as $eval)
                    <div class="eval-card">
                        <span class="risk-badge">NIVEL {{ $eval->nivel_riesgo }}</span>
                        <div class="eval-header">{{ $eval->tipoEvaluacion->nombre }} — {{ $eval->puntaje_total }} pts.</div>
                        <div class="eval-meta">{{ $eval->fecha_eval->format('d/m/Y') }} · Eval: {{ $eval->personalSalud->usuario->name ?? 'Personal' }}</div>
                        <div style="font-size: 9px;"><strong>Interpretación:</strong> {{ $eval->resultado_interpretacion }}</div>
                    </div>
                @empty
                    <div style="font-size: 10px; color: #999; margin-bottom: 20px;">No hay evaluaciones registradas.</div>
                @endforelse

                <div class="section-title">Historial de Atenciones</div>
                @forelse($atenciones->take(8) as $aten)
                    <div class="aten-row">
                        <table width="100%">
                            <tr>
                                <td class="aten-date">{{ $aten->fecha->format('d/m') }}</td>
                                <td class="aten-content">
                                    <div style="font-weight: bold;">{{ $aten->tipoAtencion->tipo ?? 'Atención' }}</div>
                                    <div style="color: #666; font-size: 9px;">{{ Str::limit($aten->obs ?? $aten->descripcion, 80) }}</div>
                                </td>
                                <td align="right" style="font-size: 8px; color: #999;">{{ $aten->estado }}</td>
                            </tr>
                        </table>
                    </div>
                @empty
                    <div style="font-size: 10px; color: #999; margin-bottom: 20px;">Sin atenciones registradas.</div>
                @endforelse

                <div class="section-title" style="margin-top: 15px;">Bitácora Reciente</div>
                <div style="font-size: 8px; color: #666;">
                    @foreach($bitacora->take(5) as $log)
                        <div style="margin-bottom: 4px;">
                            {{ $log->created_at->format('d/m/Y H:i') }} - {{ $log->description }} ({{ $log->causer->name ?? 'Sistema' }})
                        </div>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    {{-- ====== PÁGINA 2: RESUMEN VISUAL DE INDICADORES ====== --}}
    @php
        $svChart      = $signosVitales->where('estado', 'VIGENTE')->sortBy('fecha')->take(10)->values();
        $barthelChart = $valoracionesFuncionales->filter(fn($v) => $v->indice_barthel !== null)->sortBy('fecha_valoracion')->take(8)->values();
        $evalChart    = $evaluaciones->sortBy('fecha_eval')->take(8)->values();
        $medEstados   = $medicaciones->groupBy('estado')->map(fn($group) => $group->count());
        $hasCharts    = $svChart->isNotEmpty() || $barthelChart->isNotEmpty() || $evalChart->isNotEmpty() || $medEstados->isNotEmpty();
    @endphp

    @if($hasCharts)
    <div style="page-break-before: always;"></div>
    <table width="100%" style="border-bottom: 2px solid #2F3E5C; margin-bottom: 15px;">
        <tr>
            <td><span style="font-size:18px;font-weight:bold;text-transform:uppercase;color:#2F3E5C;">RememberMind</span><br>
                <span style="font-size:9px;color:#666;text-transform:uppercase;letter-spacing:1px;">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS · Resumen Visual de Indicadores</span></td>
            <td style="text-align:right;">
                <span style="font-size:15px;font-weight:bold;text-transform:uppercase;color:#2F3E5C;">Análisis Gráfico</span><br>
                <span style="color:#666;font-weight:bold;font-size:10px;">Expediente: {{ $adulto->cod_am }}</span>
                <span style="color:#999;font-size:9px;"> · {{ now()->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>

    <table width="100%" style="border-collapse:collapse;">
    <tr>
      {{-- GRÁFICO 1: SIGNOS VITALES --}}
      <td width="50%" style="vertical-align:top;padding:0 8px 18px 0;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;border-bottom:1px solid #DDD;padding-bottom:4px;margin-bottom:8px;color:#E27D60;">Evolución Presión / Pulso</div>
        @if($svChart->isNotEmpty())
        @php
            $cW=240;$cH=110;$pL=30;$pR=8;$pT=10;$pB=22;
            $iW=$cW-$pL-$pR;$iH=$cH-$pT-$pB;
            $n=$svChart->count();$yLo=40;$yHi=200;$yRange=$yHi-$yLo;
            $sistPts=[];$diasPts=[];$pulsPts=[];
            foreach ($svChart as $si => $sv_i) {
                $bx = $n>1 ? $pL+($iW/($n-1))*$si : $pL+$iW/2;
                if ($sv_i->presion_sistolica !== null) {
                    $y=$pT+$iH*(1-($sv_i->presion_sistolica-$yLo)/$yRange);
                    $sistPts[]=round($bx,1).','.round(max($pT,min($pT+$iH,$y)),1);
                }
                if ($sv_i->presion_diastolica !== null) {
                    $y=$pT+$iH*(1-($sv_i->presion_diastolica-$yLo)/$yRange);
                    $diasPts[]=round($bx,1).','.round(max($pT,min($pT+$iH,$y)),1);
                }
                if ($sv_i->frecuencia_cardiaca !== null) {
                    $y=$pT+$iH*(1-($sv_i->frecuencia_cardiaca-$yLo)/$yRange);
                    $pulsPts[]=round($bx,1).','.round(max($pT,min($pT+$iH,$y)),1);
                }
            }
            $svFechas=$svChart->map(fn($s)=>$s->fecha?$s->fecha->format('d/m'):'')->values()->toArray();
        @endphp
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $cW }}" height="{{ $cH }}">
          <rect x="{{ $pL }}" y="{{ $pT }}" width="{{ $iW }}" height="{{ $iH }}" fill="#FDFBFA" stroke="#DDD" stroke-width="0.5"/>
          @foreach([80,120,160] as $gl)
            @php $glY=round($pT+$iH*(1-($gl-$yLo)/$yRange),1); @endphp
            @if($glY>=$pT && $glY<=$pT+$iH)
            <line x1="{{ $pL }}" y1="{{ $glY }}" x2="{{ $pL+$iW }}" y2="{{ $glY }}" stroke="#E8E0D8" stroke-width="0.5"/>
            <text x="{{ $pL-2 }}" y="{{ $glY+2.5 }}" text-anchor="end" font-size="6" fill="#AAA">{{ $gl }}</text>
            @endif
          @endforeach
          @if(count($sistPts)>=2)<polyline points="{{ implode(' ',$sistPts) }}" fill="none" stroke="#E27D60" stroke-width="1.5"/>@endif
          @if(count($diasPts)>=2)<polyline points="{{ implode(' ',$diasPts) }}" fill="none" stroke="#5B5F97" stroke-width="1.5"/>@endif
          @if(count($pulsPts)>=2)<polyline points="{{ implode(' ',$pulsPts) }}" fill="none" stroke="#617453" stroke-width="1.5"/>@endif
          @foreach($svFechas as $fi => $fl)
            @if($fi%2==0 || $fi==$n-1)
            @php $flx=round($n>1?$pL+($iW/($n-1))*$fi:$pL+$iW/2,1); @endphp
            <text x="{{ $flx }}" y="{{ $pT+$iH+10 }}" text-anchor="middle" font-size="6" fill="#999">{{ $fl }}</text>
            @endif
          @endforeach
          <rect x="{{ $pL }}" y="{{ $cH-8 }}" width="5" height="5" fill="#E27D60"/>
          <text x="{{ $pL+7 }}" y="{{ $cH-3 }}" font-size="6" fill="#666">Sistólica</text>
          <rect x="{{ $pL+46 }}" y="{{ $cH-8 }}" width="5" height="5" fill="#5B5F97"/>
          <text x="{{ $pL+53 }}" y="{{ $cH-3 }}" font-size="6" fill="#666">Diastólica</text>
          <rect x="{{ $pL+95 }}" y="{{ $cH-8 }}" width="5" height="5" fill="#617453"/>
          <text x="{{ $pL+102 }}" y="{{ $cH-3 }}" font-size="6" fill="#666">Pulso</text>
        </svg>
        @else
        <div style="font-size:9px;color:#999;font-style:italic;padding:20px 0;text-align:center;">Sin signos vitales vigentes.</div>
        @endif
      </td>

      {{-- GRÁFICO 2: ÍNDICE BARTHEL --}}
      <td width="50%" style="vertical-align:top;padding:0 0 18px 8px;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;border-bottom:1px solid #DDD;padding-bottom:4px;margin-bottom:8px;color:#2F3E5C;">Evolución Índice Barthel</div>
        @if($barthelChart->isNotEmpty())
        @php
            $cW=240;$cH=110;$pL=28;$pR=8;$pT=10;$pB=22;
            $iW=$cW-$pL-$pR;$iH=$cH-$pT-$pB;
            $nb=$barthelChart->count();
            $bPts=[];
            foreach ($barthelChart as $bi => $bv_i) {
                $bx=$nb>1?$pL+($iW/($nb-1))*$bi:$pL+$iW/2;
                $by=$pT+$iH*(1-($bv_i->indice_barthel/100));
                $bPts[]=round($bx,1).','.round(max($pT,min($pT+$iH,$by)),1);
            }
            $bLabels=$barthelChart->map(fn($v)=>$v->fecha_valoracion?$v->fecha_valoracion->format('d/m'):'')->values()->toArray();
            $lastBV=$barthelChart->last()->indice_barthel;
            $lastNiv=$barthelChart->last()->nivel_dependencia??'';
        @endphp
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $cW }}" height="{{ $cH }}">
          <rect x="{{ $pL }}" y="{{ $pT }}" width="{{ $iW }}" height="{{ $iH }}" fill="#FDFBFA" stroke="#DDD" stroke-width="0.5"/>
          @foreach([0,25,50,75,100] as $bg)
            @php $bgY=round($pT+$iH*(1-$bg/100),1); @endphp
            <line x1="{{ $pL }}" y1="{{ $bgY }}" x2="{{ $pL+$iW }}" y2="{{ $bgY }}" stroke="#E8E0D8" stroke-width="0.5"/>
            <text x="{{ $pL-2 }}" y="{{ $bgY+2.5 }}" text-anchor="end" font-size="6" fill="#AAA">{{ $bg }}</text>
          @endforeach
          @if(count($bPts)>=2)<polyline points="{{ implode(' ',$bPts) }}" fill="none" stroke="#2F3E5C" stroke-width="2"/>@endif
          @foreach($bPts as $bpi => $bpp)
            @php $bc=explode(',',$bpp); @endphp
            <circle cx="{{ $bc[0] }}" cy="{{ $bc[1] }}" r="2.5" fill="#2F3E5C"/>
          @endforeach
          @if(count($bPts)>0)
            @php $lastC=explode(',',$bPts[count($bPts)-1]); @endphp
            <text x="{{ $lastC[0] }}" y="{{ max($pT+7,$lastC[1]-4) }}" text-anchor="middle" font-size="6.5" fill="#2F3E5C" font-weight="bold">{{ $lastBV }}</text>
          @endif
          @foreach($bLabels as $bli => $bll)
            @if($bli%2==0 || $bli==$nb-1)
            @php $blx=round($nb>1?$pL+($iW/($nb-1))*$bli:$pL+$iW/2,1); @endphp
            <text x="{{ $blx }}" y="{{ $pT+$iH+10 }}" text-anchor="middle" font-size="6" fill="#999">{{ $bll }}</text>
            @endif
          @endforeach
          <text x="{{ $pL+$iW/2 }}" y="{{ $cH-3 }}" text-anchor="middle" font-size="7" fill="#2F3E5C">Último: {{ $lastBV }}/100{{ $lastNiv ? ' · ' . $lastNiv : '' }}</text>
        </svg>
        @else
        <div style="font-size:9px;color:#999;font-style:italic;padding:20px 0;text-align:center;">Sin datos de valoración funcional.</div>
        @endif
      </td>
    </tr>
    <tr>
      {{-- GRÁFICO 3: EVALUACIONES COGNITIVAS --}}
      <td width="50%" style="vertical-align:top;padding:0 8px 10px 0;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;border-bottom:1px solid #DDD;padding-bottom:4px;margin-bottom:8px;color:#5B5F97;">Evaluaciones Cognitivas</div>
        @if($evalChart->isNotEmpty())
        @php
            $cW=240;$cH=110;$pL=28;$pR=8;$pT=10;$pB=22;
            $iW=$cW-$pL-$pR;$iH=$cH-$pT-$pB;
            $ne=$evalChart->count();
            $maxPuntaje=max((float)($evalChart->max('puntaje_maximo')??100),1);
            $groupW=$iW/$ne;
            $bw=min(12,$groupW*0.35);
        @endphp
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $cW }}" height="{{ $cH }}">
          <rect x="{{ $pL }}" y="{{ $pT }}" width="{{ $iW }}" height="{{ $iH }}" fill="#FDFBFA" stroke="#DDD" stroke-width="0.5"/>
          @foreach([25,50,75,100] as $eg)
            @php $egY=round($pT+$iH*(1-$eg/100),1); @endphp
            <line x1="{{ $pL }}" y1="{{ $egY }}" x2="{{ $pL+$iW }}" y2="{{ $egY }}" stroke="#E8E0D8" stroke-width="0.5"/>
            <text x="{{ $pL-2 }}" y="{{ $egY+2.5 }}" text-anchor="end" font-size="6" fill="#AAA">{{ $eg }}</text>
          @endforeach
          @foreach($evalChart as $ei => $ev_i)
            @php
                $gx=$pL+$groupW*$ei+$groupW/2;
                $eMax=(float)($ev_i->puntaje_maximo??100);
                $eObt=(float)($ev_i->puntaje_total??0);
                $maxH=$iH*min(1,$eMax/$maxPuntaje);
                $obtH=$iH*min(1,$eObt/$maxPuntaje);
                $ratio=$eMax>0?$eObt/$eMax:0;
                $bColor=$ratio>=0.7?'#617453':($ratio>=0.4?'#E2A45F':'#E27D60');
                $eName=substr($ev_i->tipoEvaluacion?->nombre??'Eval',0,6);
            @endphp
            <rect x="{{ round($gx-$bw,1) }}" y="{{ round($pT+$iH-$maxH,1) }}" width="{{ round($bw*0.9,1) }}" height="{{ round($maxH,1) }}" fill="#E8EDF4" stroke="#C0C8D8" stroke-width="0.5"/>
            <rect x="{{ round($gx+0.5,1) }}" y="{{ round($pT+$iH-$obtH,1) }}" width="{{ round($bw*0.9,1) }}" height="{{ round($obtH,1) }}" fill="{{ $bColor }}"/>
            <text x="{{ round($gx,1) }}" y="{{ $pT+$iH+10 }}" text-anchor="middle" font-size="5.5" fill="#888">{{ $eName }}</text>
            @if($obtH>10)
            <text x="{{ round($gx+$bw/2+0.5,1) }}" y="{{ round($pT+$iH-$obtH+8,1) }}" text-anchor="middle" font-size="6" fill="#FFF" font-weight="bold">{{ $ev_i->puntaje_total }}</text>
            @endif
          @endforeach
          <rect x="{{ $pL }}" y="{{ $cH-8 }}" width="5" height="5" fill="#E8EDF4" stroke="#C0C8D8" stroke-width="0.5"/>
          <text x="{{ $pL+7 }}" y="{{ $cH-3 }}" font-size="6" fill="#666">Máximo</text>
          <rect x="{{ $pL+45 }}" y="{{ $cH-8 }}" width="5" height="5" fill="#617453"/>
          <text x="{{ $pL+52 }}" y="{{ $cH-3 }}" font-size="6" fill="#666">Obtenido</text>
        </svg>
        @else
        <div style="font-size:9px;color:#999;font-style:italic;padding:20px 0;text-align:center;">Sin evaluaciones cognitivas registradas.</div>
        @endif
      </td>

      {{-- GRÁFICO 4: MEDICACIÓN POR ESTADO --}}
      <td width="50%" style="vertical-align:top;padding:0 0 10px 8px;">
        <div style="font-size:10px;font-weight:bold;text-transform:uppercase;border-bottom:1px solid #DDD;padding-bottom:4px;margin-bottom:8px;color:#617453;">Medicación por Estado</div>
        @if($medEstados->isNotEmpty())
        @php
            $cW=240;$cH=110;$pL=62;$pR=30;$pT=10;$pB=20;
            $iW=$cW-$pL-$pR;$iH=$cH-$pT-$pB;
            $medTotal=$medEstados->sum();
            $medColorMap=['ACTIVO'=>'#617453','SUSPENDIDO'=>'#E2A45F','FINALIZADO'=>'#5B5F97',
                          'ANULADO'=>'#E27D60','PENDIENTE'=>'#2F3E5C','INACTIVO'=>'#CBBBAA'];
            $medKeys=$medEstados->keys()->toArray();
            $nm=count($medKeys);
            $barMaxH=min($iH/max(1,$nm)*0.65,16);
            $barGapH=$iH/max(1,$nm);
        @endphp
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $cW }}" height="{{ $cH }}">
          <rect x="{{ $pL }}" y="{{ $pT }}" width="{{ $iW }}" height="{{ $iH }}" fill="#FDFBFA" stroke="#DDD" stroke-width="0.5"/>
          @foreach($medKeys as $mki => $mk)
            @php
                $cnt=$medEstados[$mk];
                $barY=$pT+$barGapH*$mki+($barGapH-$barMaxH)/2;
                $barW=$iW*($cnt/max(1,$medTotal));
                $mColor=$medColorMap[$mk]??'#AAAAAA';
            @endphp
            <rect x="{{ $pL }}" y="{{ round($barY,1) }}" width="{{ round($barW,1) }}" height="{{ round($barMaxH,1) }}" fill="{{ $mColor }}"/>
            <text x="{{ $pL-3 }}" y="{{ round($barY+$barMaxH*0.72,1) }}" text-anchor="end" font-size="6.5" fill="#444">{{ $mk }}</text>
            <text x="{{ $pL+round($barW,1)+3 }}" y="{{ round($barY+$barMaxH*0.72,1) }}" font-size="7" fill="#333" font-weight="bold">{{ $cnt }}</text>
          @endforeach
          <text x="{{ $pL+$iW/2 }}" y="{{ $pT+$iH+13 }}" text-anchor="middle" font-size="7" fill="#555">Total: {{ $medTotal }} registros</text>
        </svg>
        @else
        <div style="font-size:9px;color:#999;font-style:italic;padding:20px 0;text-align:center;">Sin medicación registrada.</div>
        @endif
      </td>
    </tr>
    </table>
    @endif

    <div class="footer">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">Firma Personal de Salud</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-text">Firma Dirección Administrativa</div>
        </div>
        <p style="margin-top: 30px; font-size: 8px; color: #999;">REPORTE GENERADO AUTOMÁTICAMENTE POR REMEMBERMIND SYSTEM - {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- ANEXO DE TRAZABILIDAD --}}
    @if($observacionesAnuladas->count() > 0 || $atencionesAnuladas->count() > 0 || $evaluacionesAnuladas->count() > 0 || $actividadesAnuladas->count() > 0)
    <div style="page-break-before: always;"></div>
    <div class="header">
        <span class="logo-text">RememberMind</span><br>
        <span class="sub-logo">Anexo: Trazabilidad de Registros Anulados</span>
    </div>
    
    <p style="font-size: 10px; color: #666; font-style: italic; margin-bottom: 20px; border-left: 3px solid #D96F58; padding-left: 10px;">
        Este documento anexo contiene información que ha sido dada de baja del registro activo, pero que se mantiene en la base de datos institucional para garantizar la integridad histórica del expediente.
    </p>

    @if($observacionesAnuladas->count() > 0)
        <div class="section-title">Historial de Observaciones Anuladas</div>
        <table width="100%" style="border-collapse: collapse; margin-bottom: 25px; font-size: 9px;">
            <thead style="background-color: #F8F8F8;">
                <tr>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left; width: 15%;">Fecha Anul.</th>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left; width: 25%;">Tipo</th>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left;">Descripción Original</th>
                </tr>
            </thead>
            <tbody>
                @foreach($observacionesAnuladas as $obs)
                <tr style="color: #666;">
                    <td style="padding: 6px; border: 1px solid #DDD;">{{ $obs->deleted_at->format('d/m/Y') }}</td>
                    <td style="padding: 6px; border: 1px solid #DDD; font-weight: bold;">{{ strtoupper($obs->tipo_obs) }}</td>
                    <td style="padding: 6px; border: 1px solid #DDD; font-style: italic;">{{ $obs->descripcion }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($atencionesAnuladas->count() > 0)
        <div class="section-title">Historial de Atenciones Anuladas</div>
        <table width="100%" style="border-collapse: collapse; margin-bottom: 25px; font-size: 9px;">
            <thead style="background-color: #F8F8F8;">
                <tr>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left; width: 15%;">Fecha Anul.</th>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left; width: 25%;">Atención</th>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left;">Motivo Original</th>
                </tr>
            </thead>
            <tbody>
                @foreach($atencionesAnuladas as $ate)
                <tr style="color: #666;">
                    <td style="padding: 6px; border: 1px solid #DDD;">{{ $ate->deleted_at->format('d/m/Y') }}</td>
                    <td style="padding: 6px; border: 1px solid #DDD; font-weight: bold;">{{ strtoupper($ate->tipoAtencion->nombre ?? 'N/D') }}</td>
                    <td style="padding: 6px; border: 1px solid #DDD; font-style: italic;">{{ $ate->motivo_consulta }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($evaluacionesAnuladas->count() > 0)
        <div class="section-title">Historial de Evaluaciones Anuladas</div>
        <table width="100%" style="border-collapse: collapse; margin-bottom: 25px; font-size: 9px;">
            <thead style="background-color: #F8F8F8;">
                <tr>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left; width: 15%;">Fecha Anul.</th>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left; width: 40%;">Evaluación</th>
                    <th style="padding: 6px; border: 1px solid #DDD; text-align: left;">Puntaje / Riesgo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluacionesAnuladas as $eval)
                <tr style="color: #666;">
                    <td style="padding: 6px; border: 1px solid #DDD;">{{ $eval->deleted_at->format('d/m/Y') }}</td>
                    <td style="padding: 6px; border: 1px solid #DDD; font-weight: bold;">{{ strtoupper($eval->tipoEvaluacion->nombre ?? 'N/D') }}</td>
                    <td style="padding: 6px; border: 1px solid #DDD; font-style: italic;">{{ $eval->puntaje_total }} pts. (RIESGO {{ $eval->nivel_riesgo }})</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    @endif
</body>
</html>
