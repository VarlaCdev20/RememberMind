@extends('pdf.exports.layouts.report-layout')

@section('title', 'Control de Horarios - ' . $usuario->name)
@section('report_title', 'CONTROL DE HORARIOS Y ASIGNACIONES')

@section('content')
 <!-- Perfil de Identidad Resumido -->
 <div class="section-title">Datos del Colaborador</div>
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Nombre Completo:</td>
 <td class="value-detail" style="font-weight: bold; font-size: 11px;">{{ $usuario->nombres }} {{ $usuario->ap_paterno }} {{ $usuario->ap_materno }}</td>
 <td class="label-detail">Código Interno:</td>
 <td class="value-detail" style="font-weight: bold; color: #E27D60;">{{ $usuario->cod_usu }}</td>
 </tr>
 <tr>
 <td class="label-detail">Documento Identidad:</td>
 <td class="value-detail">{{ $usuario->tipo_documento ?? 'CI' }} {{ $usuario->numero_documento }} {{ $usuario->expedido }}</td>
 <td class="label-detail">Rol Institucional:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $nombre_rol }}</td>
 </tr>
 <tr>
 <td class="label-detail">Correo Electrónico:</td>
 <td class="value-detail" style="text-transform: lowercase;">{{ $usuario->correo }}</td>
 <td class="label-detail">Área Operativa:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $area }}</td>
 </tr>
 </table>
 </div>

 <!-- Asignación Horaria Planificada -->
 <div class="section-title">Jornada Laboral y Turno Activo</div>
 @if($horarios)
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Turno Planificado:</td>
 <td class="value-detail" style="font-weight: bold; color: #2F3E5C;">{{ $horarios->turno?->nombre }}</td>
 <td class="label-detail">Sector Asignado:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $horarios->area?->nombre }}</td>
 </tr>
 <tr>
 <td class="label-detail">Rango Horario:</td>
 <td class="value-detail">{{ substr($horarios->turno?->hora_inicio, 0, 5) }} a {{ substr($horarios->turno?->hora_fin, 0, 5) }}</td>
 <td class="label-detail">Das de Jornada:</td>
 <td class="value-detail">
 @foreach($horarios->dias_semana ?? [] as $d)
 <span style="font-weight: bold; background-color: #2F3E5C; color: #ffffff; padding: 1px 3px; border-radius: 3px; font-size: 7px; margin-right: 2px;">
 {{ $d }}
 </span>
 @endforeach
 </td>
 </tr>
 <tr>
 <td class="label-detail">Fecha Inicio:</td>
 <td class="value-detail">{{ $horarios->fecha_inicio ? $horarios->fecha_inicio->format('d/m/Y') : '---' }}</td>
 <td class="label-detail">Fecha Fin Vigencia:</td>
 <td class="value-detail">{{ $horarios->fecha_fin ? $horarios->fecha_fin->format('d/m/Y') : 'Vigente' }}</td>
 </tr>
 </table>
 </div>
 @else
 <div class="details-box" style="text-align: center; color: #7C7168; font-style: italic; font-size: 10px; padding: 20px;">
 El usuario no tiene una asignación de jornada horaria activa registrada en el sistema.
 </div>
 @endif

 <!-- Historial de Asignaciones -->
 <div class="section-title">Historial de Asignaciones Anteriores</div>
 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 25%;">Turno / Horario</th>
 <th style="width: 25%;">Área Asignada</th>
 <th style="width: 25%;">Rango Fechas</th>
 <th style="width: 10%;">Estado</th>
 <th style="width: 15%;">Registrado Por</th>
 </tr>
 </thead>
 <tbody>
 @forelse($historial_horarios as $h)
 <tr>
 <td style="font-weight: bold;">
 {{ $h->turno?->nombre }}
 <div style="font-size: 8px; color: #7C7168; font-weight: normal; margin-top: 1px;">
 {{ substr($h->turno?->hora_inicio, 0, 5) }} - {{ substr($h->turno?->hora_fin, 0, 5) }}
 </div>
 </td>
 <td style="font-weight: bold;">{{ $h->area?->nombre }}</td>
 <td>
 {{ $h->fecha_inicio ? $h->fecha_inicio->format('d/m/Y') : '---' }}
 al
 {{ $h->fecha_fin ? $h->fecha_fin->format('d/m/Y') : 'Vigente' }}
 </td>
 <td>
 <span class="badge-status {{ $h->estado === 'ACTIVA' ? 'badge-active' : 'badge-inactive' }}">
 {{ $h->estado }}
 </span>
 </td>
 <td>{{ $h->creador?->name ?? 'Sistema' }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" style="text-align: center; color: #7C7168; padding: 12px;">
 No existen registros de turnos históricos para este colaborador.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>

 <div class="alert-box" style="margin-top: 30px;">
 <strong>Control de Asistencia e Incidencias:</strong><br>
 Esta grilla horaria determina la planificación formal del colaborador. Cualquier desvío de la jornada debe informarse por los canales oficiales de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.
 </div>
@endsection

