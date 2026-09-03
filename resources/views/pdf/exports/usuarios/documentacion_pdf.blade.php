@extends('pdf.exports.layouts.report-layout')

@section('title', 'Estatus Documental - ' . $usuario->name)
@section('report_title', 'EXPEDIENTE DOCUMENTAL DE USUARIO')

@section('content')
 <!-- Perfil de Identidad Resumido -->
 <div class="section-title">Datos del Titular</div>
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

 <!-- Estatus Documental -->
 <div class="section-title">Estatus y Avance del Expediente Documental (Avance: {{ $avance_documental['porcentaje_avance'] }}%)</div>
 <div style="margin-bottom: 12px; background-color: #F8F3ED; border: 1px solid #E6DDD3; border-radius: 6px; padding: 6px 12px; font-size: 9px; font-weight: bold; color: #2F3E5C;">
 Requisitos validados: {{ $avance_documental['validados'] }} de {{ $avance_documental['total_requeridos'] }} obligatorios requeridos.
 </div>

 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 45%;">Requisito / Documento</th>
 <th style="width: 15%;">Tipo</th>
 <th style="width: 20%;">Estatus</th>
 <th style="width: 20%;">Fecha Carga</th>
 </tr>
 </thead>
 <tbody>
 @foreach($checklist as $doc)
 <tr>
 <td style="font-weight: bold;">
 {{ $doc['nombre'] }}
 <div style="font-size: 8px; color: #7C7168; font-weight: normal; margin-top: 1px;">
 {{ $doc['descripcion'] }}
 </div>
 </td>
 <td>
 @if($doc['obligatorio'])
 <span style="color: #E27D60; font-weight: bold;">OBLIGATORIO</span>
 @else
 <span style="color: #7C7168;">OPCIONAL</span>
 @endif
 </td>
 <td>
 @php
 $statusColor = match($doc['estado']) {
 'VALIDADO' => 'color: #63775B; font-weight: bold;',
 'CARGADO' => 'color: #5E6599; font-weight: bold;',
 'OBSERVADO' => 'color: #E27D60; font-weight: bold;',
 'VENCIDO' => 'color: #7B624F; font-weight: bold;',
 default => 'color: #7C7168;'
 };
 @endphp
 <span style="{{ $statusColor }}">
 {{ $doc['estado'] }}
 </span>
 </td>
 <td>
 {{ $doc['cargado'] && $doc['documento']->created_at ? $doc['documento']->created_at->format('d/m/Y') : 'Pendiente' }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box" style="margin-top: 30px;">
 <strong>Nota de Auditoría de Control:</strong><br>
 Este informe refleja el estado de la documentación requerida por CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS para el cumplimiento de las normativas de salud y administración. Todos los archivos cargados están protegidos por estrictas políticas de confidencialidad institucional.
 </div>
@endsection

