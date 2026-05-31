@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Residentes (Adultos Mayores)')
@section('report_title', 'Reporte General de Residentes')

@section('content')
    <div class="section-title">Resumen de la Población Residente</div>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 25%; padding-right: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $adultos->count() }}</div>
                    <div class="summary-label">Residentes Totales</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $adultos->where('cod_est_adul', 1)->count() }}</div>
                    <div class="summary-label">Residentes Activos</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $adultos->where('genero', 'MASCULINO')->count() }}</div>
                    <div class="summary-label">Hombres</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $adultos->where('genero', 'FEMENINO')->count() }}</div>
                    <div class="summary-label">Mujeres</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Listado de Residentes</div>
    
    <table class="table-institutional">
        <thead>
            <tr>
                <th style="width: 30%;">Nombre Completo</th>
                <th style="width: 18%;">RUT / Identificación</th>
                <th style="width: 12%;">Género</th>
                <th style="width: 20%;">Estado Clínico</th>
                <th style="width: 20%;">Fecha Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($adultos as $am)
                <tr>
                    <td><strong>{{ $am->nombres }} {{ $am->ap_paterno }} {{ $am->ap_materno }}</strong></td>
                    <td>{{ $am->rut ?? 'No Registrado' }}</td>
                    <td>{{ $am->genero ?? 'No Definido' }}</td>
                    <td>{{ $am->estado ? $am->estado->estado : 'Sin Estado' }}</td>
                    <td>{{ $am->created_at ? $am->created_at->format('d/m/Y') : 'Sin fecha' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="alert-box">
        <strong>Aviso de Confidencialidad y Privacidad:</strong> La información contenida en esta ficha está resguardada bajo la ley de derechos y deberes de los pacientes. Su copia o difusión no autorizada constituye una falta grave al protocolo institucional de Casa Amandita.
    </div>
@endsection
