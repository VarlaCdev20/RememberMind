<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} - {{ $adulto->cod_am }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #2F3E5C; font-size: 11px; line-height: 1.4; }
        .header { border-bottom: 2px solid #2F3E5C; padding-bottom: 20px; margin-bottom: 20px; }
        .logo-text { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .sub-logo { font-size: 9px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .title { text-align: right; }
        .title h2 { font-size: 18px; margin: 0; text-transform: uppercase; }
        .title p { margin: 2px 0; color: #666; font-weight: bold; }
        
        .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #EEE; padding-bottom: 5px; margin-bottom: 15px; margin-top: 20px; color: #2F3E5C; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        table.data-table th { background-color: #F2EBE3; padding: 8px; text-align: left; border: 1px solid #D5C7B9; font-weight: bold; }
        table.data-table td { padding: 8px; border: 1px solid #D5C7B9; }
        
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
                    <h2>{{ $titulo }}</h2>
                    <p>Residente: {{ $adulto->nombres }} {{ $adulto->ap_paterno }} ({{ $adulto->cod_am }})</p>
                    <p>Periodo: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Inicio' }} al {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Actualidad' }}</p>
                    <p>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    @if($tipo === 'medico')
        <div class="section-title">Fichas Médicas Registradas</div>
        @if($fichas->count() > 0)
            @foreach($fichas as $ficha)
                <div style="background: #FDFBFA; border: 1px solid #CBBBAA; padding: 10px; margin-bottom: 10px; border-radius: 5px;">
                    <p><strong>Tipo de Sangre:</strong> {{ $ficha->grupo_sanguineo }}</p>
                    <p><strong>Alergias:</strong> {{ $ficha->alergias ?? 'Ninguna' }}</p>
                    <p><strong>Enfermedades Base:</strong> {{ $ficha->enfermedades_base ?? 'Ninguna' }}</p>
                    <p><strong>Cirugías Previas:</strong> {{ $ficha->cirugias_previas ?? 'Ninguna' }}</p>
                </div>
            @endforeach
        @else
            <p>Sin registros de ficha médica activa.</p>
        @endif

        <div class="section-title">Historial de Atenciones Clínicas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Observación / Seguimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($atenciones as $aten)
                    <tr>
                        <td>{{ $aten->fecha->format('d/m/Y') }}</td>
                        <td>{{ $aten->tipoAtencion->tipo ?? 'N/D' }}</td>
                        <td>{{ $aten->obs ?? $aten->descripcion }}</td>
                        <td>{{ $aten->estado }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" align="center">Sin atenciones registradas en el periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tipo === 'medicacion')
        <div class="section-title">Medicación Activa / Prescrita</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Dosis</th>
                    <th>Frecuencia</th>
                    <th>Vía</th>
                    <th>Inicio / Fin</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicaciones as $med)
                    <tr>
                        <td><strong>{{ $med->nombre_medicamento }}</strong></td>
                        <td>{{ $med->dosis }}</td>
                        <td>{{ $med->frecuencia }}</td>
                        <td>{{ $med->via_administracion }}</td>
                        <td>{{ $med->fecha_inicio?->format('d/m/Y') }} - {{ $med->fecha_fin ? $med->fecha_fin->format('d/m/Y') : 'Continuo' }}</td>
                        <td>{{ $med->estado }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" align="center">Sin medicación registrada en el periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Últimas Administraciones de Medicación Registradas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora Programada</th>
                    <th>Hora Real</th>
                    <th>Estado</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($administraciones->take(20) as $admin)
                    <tr>
                        <td>{{ $admin->fecha->format('d/m/Y') }}</td>
                        <td>{{ substr($admin->hora_programada, 0, 5) }}</td>
                        <td>{{ $admin->hora_real ? substr($admin->hora_real, 0, 5) : '-' }}</td>
                        <td>{{ $admin->estado }}</td>
                        <td>{{ $admin->observacion ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" align="center">Sin administraciones registradas en el periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tipo === 'signos')
        <div class="section-title">Historial de Signos Vitales Registrados</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha y Hora</th>
                    <th>P. Arterial</th>
                    <th>F. Cardíaca</th>
                    <th>F. Respiratoria</th>
                    <th>Temperatura</th>
                    <th>Saturación</th>
                </tr>
            </thead>
            <tbody>
                @forelse($signosVitales as $signo)
                    <tr>
                        <td>{{ $signo->fecha->format('d/m/Y') }} {{ substr($signo->hora, 0, 5) }}</td>
                        <td>{{ $signo->presion_arterial ?? '-' }}</td>
                        <td>{{ $signo->frecuencia_cardiaca ?? '-' }}</td>
                        <td>{{ $signo->frecuencia_respiratoria ?? '-' }}</td>
                        <td>{{ $signo->temperatura ?? '-' }} °C</td>
                        <td>{{ $signo->saturacion_oxigeno ?? '-' }} %</td>
                    </tr>
                @empty
                    <tr><td colspan="6" align="center">Sin signos vitales registrados en el periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tipo === 'funcional')
        <div class="section-title">Historial de Valoración Funcional Institucional</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Puntuación Act. Básicas</th>
                    <th>Nivel de Dependencia Registrado</th>
                    <th>Puntuación Act. Instrumentales</th>
                    <th>Nivel de Dependencia Registrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($valoraciones as $val)
                    <tr>
                        <td>{{ $val->fecha_valoracion->format('d/m/Y') }}</td>
                        <td>{{ $val->puntuacion_barthel ?? '-' }}</td>
                        <td>{{ $val->nivel_dependencia_barthel ?? '-' }}</td>
                        <td>{{ $val->puntuacion_lawton_brody ?? '-' }}</td>
                        <td>{{ $val->nivel_dependencia_lawton ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" align="center">Sin valoraciones funcionales registradas en el periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tipo === 'cognitivo')
        <div class="section-title">Historial de Evaluaciones Cognitivas</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Prueba / Test</th>
                    <th>Puntaje</th>
                    <th>Observación de Nivel</th>
                    <th>Interpretación Registrada</th>
                    <th>Evaluador</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluaciones as $eval)
                    <tr>
                        <td>{{ $eval->fecha_eval->format('d/m/Y') }}</td>
                        <td>{{ $eval->tipoEvaluacion->nombre ?? 'N/D' }}</td>
                        <td>{{ $eval->puntaje_total }}</td>
                        <td>{{ $eval->nivel_riesgo }}</td>
                        <td>{{ $eval->resultado_interpretacion }}</td>
                        <td>{{ $eval->personalSalud->usuario->name ?? 'N/D' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" align="center">Sin evaluaciones cognitivas registradas en el periodo.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p style="margin-top: 30px; font-size: 8px; color: #999;">REPORTE GENERADO AUTOMÁTICAMENTE POR REMEMBERMIND SYSTEM - USUARIO: {{ auth()->user()->name ?? 'SISTEMA' }}</p>
    </div>
</body>
</html>
