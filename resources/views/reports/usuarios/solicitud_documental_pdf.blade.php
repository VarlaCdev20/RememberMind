@extends('reports.layouts.report-layout')

@section('title', 'Solicitud de Documentación - ' . $usuario_reg->name)
@section('report_title', 'SOLICITUD DE DOCUMENTACIÓN INSTITUCIONAL')

@section('content')
 <!-- Datos del Solicitante -->
 <div class="section-title">Datos del Solicitante</div>
 <div class="details-box">
 <table class="details-table">
 <tr>
 <td class="label-detail">Usuario:</td>
 <td class="value-detail" style="font-weight: bold; font-size: 11px;">{{ $usuario_reg->nombres }} {{ $usuario_reg->ap_paterno }} {{ $usuario_reg->ap_materno }}</td>
 <td class="label-detail">Tipo de Usuario:</td>
 <td class="value-detail" style="font-weight: bold;">{{ $rol_legible }}</td>
 </tr>
 <tr>
 <td class="label-detail">Fecha de Registro:</td>
 <td class="value-detail">{{ $fecha_registro }}</td>
 <td class="label-detail">Plazo de Presentación:</td>
 <td class="value-detail" style="font-weight: bold; color: #E27D60;">48 HORAS</td>
 </tr>
 <tr>
 <td class="label-detail">Fecha Límite:</td>
 <td class="value-detail" style="font-weight: bold; color: #E27D60;">{{ $fecha_limite }}</td>
 <td class="label-detail">Estado Documental:</td>
 <td class="value-detail" style="font-weight: bold;">PENDIENTE</td>
 </tr>
 </table>
 </div>

 <!-- Requisitos Documentales -->
 <div class="section-title">Documentos Requeridos</div>
 <div style="margin-bottom: 12px; background-color: #F8F3ED; border: 1px solid #E6DDD3; border-radius: 6px; padding: 10px 12px; font-size: 9px; line-height: 1.4; color: #2F3E5C;">
 Por favor, presente o suba los siguientes documentos a la plataforma para completar su expediente:
 </div>

 <table class="table-institutional">
 <thead>
 <tr>
 <th style="width: 15%; text-align: center;">[ Check ]</th>
 <th style="width: 85%;">Documento / Requisito</th>
 </tr>
 </thead>
 <tbody>
 @foreach($documentos as $doc)
 <tr>
 <td style="text-align: center; font-size: 14px; font-family: monospace; color: #7C7168; vertical-align: middle; padding: 8px 0;">
 [ &nbsp; ]
 </td>
 <td style="font-weight: bold; font-size: 10px; padding: 8px 10px;">
 {{ $doc }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <div class="alert-box" style="margin-top: 30px;">
 <strong>Observación Institucional:</strong><br>
 La documentación debe ser registrada en la plataforma dentro del plazo establecido. En caso de no completar la documentación, el sistema generará una alerta administrativa para seguimiento.
 </div>
@endsection
