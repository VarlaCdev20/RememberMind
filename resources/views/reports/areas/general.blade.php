@extends('reports.layouts.report-layout')

@section('title', 'Reporte General de Áreas Institucionales')
@section('report_title', 'Reporte General de Áreas')

@section('content')
    <div class="section-title">Resumen Ejecutivo Institucional</div>
    
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
        <tr>
            <td style="width: 25%; padding-right: 10px; padding-bottom: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $totales['total_areas'] }}</div>
                    <div class="summary-label">Áreas Registradas</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px; padding-bottom: 10px;">
                <div class="summary-card">
                    <div class="summary-number" style="color: #63775B;">{{ $totales['areas_activas'] }}</div>
                    <div class="summary-label">Áreas Activas ({{ $totales['porcentaje_areas_activas'] }}%)</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px; padding-bottom: 10px;">
                <div class="summary-card">
                    <div class="summary-number" style="color: #E27D60;">{{ $totales['areas_inactivas'] }}</div>
                    <div class="summary-label">Áreas Inactivas</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 10px; padding-bottom: 10px;">
                <div class="summary-card">
                    <div class="summary-number">{{ $totales['personal_activo'] }}</div>
                    <div class="summary-label">Personal Asignado</div>
                </div>
            </td>
        </tr>
        <tr>
            <td style="width: 25%; padding-right: 10px;">
                <div class="summary-card">
                    <div class="summary-number" style="color: #E27D60;">{{ $totales['sin_responsable'] }}</div>
                    <div class="summary-label">Sin Responsable</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number" style="color: #7C7168;">{{ $totales['areas_sin_usuarios'] }}</div>
                    <div class="summary-label">Sin Personal Asignado</div>
                </div>
            </td>
            <td style="width: 25%; padding-right: 10px; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number" style="font-size: 11px; padding: 2px 0;">{{ \Str::limit($totales['area_mas_usuarios_nombre'], 15) }}</div>
                    <div class="summary-label">Área con más usuarios ({{ $totales['area_mas_usuarios_count'] }})</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 10px;">
                <div class="summary-card">
                    <div class="summary-number" style="color: #63775B;">{{ $totales['porcentaje_areas_responsable'] }}%</div>
                    <div class="summary-label">Cobertura Responsables</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Matriz de Áreas Institucionales</div>
    
    <table class="table-institutional">
        <thead>
            <tr>
                <th style="width: 25%;">Nombre del Área</th>
                <th style="width: 15%;">Tipo de Área</th>
                <th style="width: 25%;">Responsable del Área</th>
                <th style="width: 10%; text-align: center;">Activos</th>
                <th style="width: 10%; text-align: center;">Inactivos</th>
                <th style="width: 15%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($areas as $area)
                <tr>
                    <td><strong>{{ $area['nombre'] }}</strong></td>
                    <td>{{ $area['tipo_area'] }}</td>
                    <td>{{ $area['responsable_nombre'] ?? 'Sin asignar' }}</td>
                    <td style="text-align: center;">{{ $area['usuarios_activos_count'] }}</td>
                    <td style="text-align: center;">{{ $area['usuarios_inactivos_count'] }}</td>
                    <td style="text-align: center;">
                        <span class="badge-status {{ $area['estado'] === 'ACTIVA' ? 'badge-active' : 'badge-inactive' }}">
                            {{ $area['estado'] }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $sinResponsableList = collect($areas)->filter(fn($a) => $a['responsable_nombre'] === 'Sin asignar' || !$a['responsable_nombre']);
        $inactivasList = collect($areas)->filter(fn($a) => $a['estado'] === 'INACTIVA');
    @endphp

    @if($sinResponsableList->count() > 0 || $inactivasList->count() > 0)
    <div class="section-title">Observaciones Administrativas</div>
    <div class="alert-box" style="margin-top: 5px;">
        @if($sinResponsableList->count() > 0)
            <div style="margin-bottom: 4px;">
                <strong>⚠️ Áreas sin responsable asignado:</strong> 
                {{ $sinResponsableList->pluck('nombre')->implode(', ') }}. Se recomienda designar un líder de área para garantizar la firma y trazabilidad operacional.
            </div>
        @endif
        @if($inactivasList->count() > 0)
            <div>
                <strong>🚫 Áreas actualmente inactivas:</strong> 
                {{ $inactivasList->pluck('nombre')->implode(', ') }}. El personal adscrito a estas áreas no podrá registrar bitácoras operativas asociadas.
            </div>
        @endif
    </div>
    @endif

    <div class="alert-box" style="border-left-color: #2F3E5C; margin-top: 15px;">
        <strong>Nota Administrativa de Seguridad:</strong> La asignación de personal a cada área y la vigencia del Responsable son clave para la trazabilidad de la auditoría y bitácora de eventos del sistema. Asegúrese de realizar revisiones de seguridad y roles periódicamente.
    </div>
@endsection
