@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Evaluaciones Cognitivas')
@section('report_title', 'Reporte General de Evaluaciones')

@section('content')
    <div class="section-title">Resumen de Seguimiento Cognitivo</div>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 33%; padding-right: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $evaluaciones->count() }}</div>
                    <div class="summary-label">Evaluaciones Realizadas</div>
                </div>
            </td>
            <td style="width: 33%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $evaluaciones->where('nivel_riesgo', 'ALTO')->count() }}</div>
                    <div class="summary-label">Riesgo Alto Detectado</div>
                </div>
            </td>
            <td style="width: 34%; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $evaluaciones->where('nivel_riesgo', 'BAJO')->count() }}</div>
                    <div class="summary-label">Riesgo Bajo / Estable</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Detalle de Controles Cognitivos</div>
    
    <table class="table-institutional">
        <thead>
            <tr>
                <th style="width: 25%;">Residente</th>
                <th style="width: 25%;">Test Aplicado</th>
                <th style="width: 15%;">Puntaje</th>
                <th style="width: 20%;">Riesgo</th>
                <th style="width: 15%;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($evaluaciones as $ev)
                <tr>
                    <td><strong>{{ $ev->adultoMayor ? $ev->adultoMayor->nombres . ' ' . $ev->adultoMayor->ap_paterno : 'No Registrado' }}</strong></td>
                    <td>{{ $ev->tipoEvaluacion ? $ev->tipoEvaluacion->nombre : 'Test Clínico' }}</td>
                    <td><strong>{{ (float) $ev->puntaje_total }}</strong> / {{ (float) $ev->puntaje_maximo }}</td>
                    <td>
                        <span class="badge-status {{ $ev->nivel_riesgo === 'ALTO' ? 'badge-inactive' : 'badge-active' }}">
                            {{ $ev->nivel_riesgo ?? 'ESTABLE' }}
                        </span>
                    </td>
                    <td>{{ $ev->fecha_eval ? \Carbon\Carbon::parse($ev->fecha_eval)->format('d/m/Y') : 'Sin fecha' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="alert-box">
        <strong>Nota de Psicología y Terapia Ocupacional:</strong> La detección oportuna de deterioro cognitivo permite ajustar las actividades diarias y terapias de estimulación para preservar la autonomía funcional del residente por más tiempo.
    </div>
@endsection
