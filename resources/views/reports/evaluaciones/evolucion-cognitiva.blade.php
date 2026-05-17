@extends('reports.layouts.report-layout')

@section('title', 'Historial de Evolución Cognitiva del Residente')
@section('report_title', 'Reporte de Evolución Cognitiva')

@section('content')
    <div class="section-title">Datos del Residente Evaluado</div>
    
    <div class="details-box">
        <table class="details-table">
            <tr>
                <td class="label-detail">Nombre Completo:</td>
                <td class="value-detail"><strong>{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</strong></td>
            </tr>
            <tr>
                <td class="label-detail">RUT / Identificación:</td>
                <td class="value-detail">{{ $adulto->rut ?? 'No Registrado' }}</td>
            </tr>
            <tr>
                <td class="label-detail">Edad / Género:</td>
                <td class="value-detail">{{ $adulto->edad ?? 'No calculada' }} años ({{ $adulto->genero }})</td>
            </tr>
            <tr>
                <td class="label-detail">Estado de Ingreso:</td>
                <td class="value-detail">{{ $adulto->estado ? $adulto->estado->estado : 'Activo' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Evolución Histórica de Evaluaciones</div>
    
    @if ($evaluaciones && $evaluaciones->count() > 0)
        <table class="table-institutional">
            <thead>
                <tr>
                    <th style="width: 20%;">Fecha</th>
                    <th style="width: 25%;">Test Aplicado</th>
                    <th style="width: 15%;">Puntaje</th>
                    <th style="width: 20%;">Nivel de Riesgo</th>
                    <th style="width: 20%;">Interpretación</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($evaluaciones as $ev)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($ev->fecha_eval)->format('d/m/Y') }}</td>
                        <td>{{ $ev->tipoEvaluacion ? $ev->tipoEvaluacion->nombre : 'Test Clínico' }}</td>
                        <td><strong>{{ (float) $ev->puntaje_total }}</strong> / {{ (float) $ev->puntaje_maximo }}</td>
                        <td>
                            <span class="badge-status {{ $ev->nivel_riesgo === 'ALTO' ? 'badge-inactive' : 'badge-active' }}">
                                {{ $ev->nivel_riesgo ?? 'ESTABLE' }}
                            </span>
                        </td>
                        <td>{{ $ev->resultado_interpretacion ?? 'Sin descripción' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 20px; text-align: center; color: #7C7168; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 6px; font-size: 10px;">
            No se han registrado evaluaciones cognitivas para este residente.
        </div>
    @endif

    <div class="alert-box">
        <strong>Recomendación Clínica:</strong> Si se observa una disminución del 10% o más en el puntaje de evaluaciones consecutivas (por ejemplo, en el test MoCA o MMSE), se aconseja programar una interconsulta neurológica y revisar los esquemas de medicación activa.
    </div>
@endsection
