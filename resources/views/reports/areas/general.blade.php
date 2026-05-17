@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Áreas Institucionales')
@section('report_title', 'Reporte General de Áreas')

@section('content')
    <div class="section-title">Resumen Estadístico Institucional</div>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 25%; padding-right: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $totales['total_areas'] }}</div>
                    <div class="summary-label">Áreas Registradas</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $totales['personal_activo'] }}</div>
                    <div class="summary-label">Personal Activo</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $totales['sin_responsable'] }}</div>
                    <div class="summary-label">Sin Responsable</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $totales['total_usuarios'] }}</div>
                    <div class="summary-label">Total Usuarios</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Listado General de Áreas</div>
    
    <table class="table-institutional">
        <thead>
            <tr>
                <th style="width: 25%;">Nombre del Área</th>
                <th style="width: 15%;">Tipo de Área</th>
                <th style="width: 25%;">Responsable</th>
                <th style="width: 10%;">Personal Activo</th>
                <th style="width: 10%;">Personal Inactivo</th>
                <th style="width: 15%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($areas as $area)
                <tr>
                    <td><strong>{{ $area['nombre'] }}</strong></td>
                    <td>{{ $area['tipo_area'] }}</td>
                    <td>{{ $area['responsable_nombre'] ?? 'Sin asignar' }}</td>
                    <td>{{ $area['usuarios_activos_count'] }}</td>
                    <td>{{ $area['usuarios_inactivos_count'] }}</td>
                    <td>
                        <span class="badge-status {{ $area['estado'] === 'ACTIVO' ? 'badge-active' : 'badge-inactive' }}">
                            {{ $area['estado'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="alert-box">
        <strong>Nota Administrativa:</strong> La asignación de personal a cada área y la vigencia del Responsable son clave para la trazabilidad de la auditoría y bitácora de eventos del sistema. Asegúrese de realizar revisiones de seguridad y roles periódicamente.
    </div>
@endsection
