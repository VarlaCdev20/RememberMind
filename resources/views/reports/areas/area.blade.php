@extends('reports.layouts.report-layout')

@section('title', 'Ficha Técnica de Área Institucional')
@section('report_title', 'Ficha Técnica del Área')

@section('content')
    <div class="section-title">Detalle del Registro</div>
    
    <div class="details-box">
        <table class="details-table">
            <tr>
                <td class="label-detail">Nombre del Área:</td>
                <td class="value-detail"><strong>{{ $area->nombre }}</strong></td>
            </tr>
            <tr>
                <td class="label-detail">Clasificación / Tipo:</td>
                <td class="value-detail">{{ $area->tipo_area }}</td>
            </tr>
            <tr>
                <td class="label-detail">Responsable del Área:</td>
                <td class="value-detail"><strong>{{ $area->responsable ? $area->responsable->name : 'Sin Responsable Asignado' }}</strong></td>
            </tr>
            <tr>
                <td class="label-detail">Estado Administrativo:</td>
                <td class="value-detail">
                    <span class="badge-status {{ strtoupper($area->estado) === 'ACTIVO' ? 'badge-active' : 'badge-inactive' }}">
                        {{ $area->estado }}
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label-detail">Descripción Operativa:</td>
                <td class="value-detail">{{ $area->descripcion ?? 'Sin observaciones ni descripción provista.' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">Personal Vinculado al Área</div>
    
    @if ($area->usuarios && $area->usuarios->count() > 0)
        <table class="table-institutional">
            <thead>
                <tr>
                    <th style="width: 40%;">Nombre Completo</th>
                    <th style="width: 35%;">Correo Electrónico</th>
                    <th style="width: 25%;">Estado de Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($area->usuarios as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <span class="badge-status {{ $u->estado == 1 ? 'badge-active' : 'badge-inactive' }}">
                                {{ $u->estado == 1 ? 'ACTIVO' : 'INACTIVO' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="padding: 20px; text-align: center; color: #7C7168; background-color: #FAF8F5; border: 1px dashed #E6DDD3; border-radius: 6px; font-size: 10px;">
            No hay personal ni usuarios adscritos a esta área institucional actualmente.
        </div>
    @endif

    <div class="alert-box">
        <strong>Trazabilidad y Control Interno:</strong> Todo cambio en los cargos, roles, o la inhabilitación de usuarios pertenecientes al área se registra automáticamente en la bitácora global del sistema para auditorías periódicas.
    </div>
@endsection
