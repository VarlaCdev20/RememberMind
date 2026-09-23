<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>Reporte de Evaluación Geriátrica - {{ $adulto->nombres }} {{ $adulto->ap_paterno }}</title>
 <style>
 @page { margin: 1.2cm; }
 body { font-family: 'Helvetica', 'Arial', sans-serif; color: #2F3E5C; font-size: 11px; line-height: 1.5; }
 .header { border-bottom: 2px solid #2F3E5C; padding-bottom: 15px; margin-bottom: 25px; }
 .logo-text { font-size: 22px; font-weight: bold; text-transform: uppercase; color: #2F3E5C; }
 .sub-logo { font-size: 8.5px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
 .title { text-align: right; }
 .title h2 { font-size: 14px; margin: 0; text-transform: uppercase; color: #5B5F97; }
 .title p { margin: 2px 0; color: #555; font-weight: bold; font-size: 9px; }
 
 .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #C7B5A3; padding-bottom: 4px; margin-bottom: 12px; color: #2F3E5C; letter-spacing: 0.5px; }
 
 .grid-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
 .grid-table td { padding: 4px 0; vertical-align: top; }
 
 .card { border: 1px solid #CBBBAA; background-color: #FDFBFA; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
 .eval-badge { 
 display: inline-block; 
 padding: 3px 10px; 
 border-radius: 8px; 
 font-size: 9px; 
 font-weight: bold; 
 text-transform: uppercase; 
 }
 .alerta-normal { background-color: #e2ebd9; color: #617453; border: 1px solid #c7d8bd; }
 .alerta-preventivo { background-color: #f7ede2; color: #9B6D4C; border: 1px solid #ebd0bc; }
 .alerta-critico { background-color: #fde8e8; color: #c81e1e; border: 1px solid #f8b4b4; }

 .meta-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
 .meta-table th, .meta-table td { border: 1px solid #E7DDD2; padding: 8px; font-size: 10px; text-align: left; }
 .meta-table th { background-color: #F2EBE3; color: #2F3E5C; font-weight: bold; text-transform: uppercase; font-size: 9px; }
 
 .observations-box { background-color: #F8F5F1; border-left: 4px solid #5B5F97; padding: 12px; border-radius: 0 8px 8px 0; margin-top: 15px; font-style: italic; }
 
 .footer { margin-top: 60px; text-align: center; border-top: 1px solid #E7DDD2; padding-top: 15px; }
 .signature-box { width: 45%; display: inline-block; text-align: center; margin-top: 40px; }
 .signature-line { border-top: 1px solid #2F3E5C; width: 75%; margin: 0 auto 5px; }
 .signature-text { font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #555; }
 </style>
</head>
<body>
 {{-- Cabecera --}}
 <div class="header">
 <table width="100%">
 <tr>
 <td width="55%">
 <span class="logo-text">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</span><br>
 <span class="sub-logo">Gestión Gerontológica e Integral</span>
 </td>
 <td class="title">
 <h2>Reporte de Valoración Geriátrica</h2>
 <p>Residente evaluado: {{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</p>
 <p>Fecha de Emisión: {{ now()->format('d/m/Y H:i') }}</p>
 </td>
 </tr>
 </table>
 </div>

 {{-- Datos del Adulto Mayor --}}
 <div class="section-title">Datos del Adulto Mayor</div>
 <table class="grid-table" style="margin-bottom: 15px;">
 <tr>
 <td width="20%" style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Nombre Completo:</td>
 <td width="30%" style="font-weight: bold;">{{ $adulto->nombres }} {{ $adulto->ap_paterno }} {{ $adulto->ap_materno }}</td>
 <td width="20%" style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Documento:</td>
 <td width="30%" style="font-weight: bold;">{{ $adulto->ci ?: 'No registrado' }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">C.I.:</td>
 <td>{{ $adulto->ci }}</td>
 <td style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Edad / Género:</td>
 <td>{{ $adulto->edad }} años · {{ $adulto->genero }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Fecha de Ingreso:</td>
 <td>{{ $adulto->fecha_ing ? \Carbon\Carbon::parse($adulto->fecha_ing)->format('d/m/Y') : 'No registrada' }}</td>
 <td style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Estado actual:</td>
 <td><span style="font-weight: bold; color: #617453;">{{ $adulto->estado->estado ?? 'ACTIVO' }}</span></td>
 </tr>
 </table>

 {{-- Detalles de la Evaluación --}}
 <div class="section-title">Información de la Evaluación</div>
 <table class="grid-table" style="margin-bottom: 20px;">
 <tr>
 <td width="25%" style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Área Geriátrica:</td>
 <td width="75%" style="font-weight: bold;">{{ $evaluacion->instrumento->area->nombre }}</td>
 </tr>
 <tr>
 <td style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Instrumento / Escala:</td>
 <td style="font-weight: bold;">{{ $evaluacion->instrumento->nombre }} ({{ $evaluacion->instrumento->siglas }})</td>
 </tr>
 <tr>
 <td style="font-weight: bold; color: #666; font-size: 9px; text-transform: uppercase;">Descripción:</td>
 <td style="color: #555; font-size: 10px;">{{ $evaluacion->instrumento->descripcion }}</td>
 </tr>
 </table>

 {{-- Tabla de Resultados --}}
 <div class="section-title">Métricas y Resultados Clínicos</div>
 <table class="meta-table">
 <thead>
 <tr>
 <th width="30%">Fecha y Hora</th>
 <th width="20%">Puntaje Obtenido</th>
 <th width="25%">Interpretación Clínica</th>
 <th width="25%">Nivel de Alerta</th>
 </tr>
 </thead>
 <tbody>
 <tr>
 <td style="font-weight: bold;">
 {{ $evaluacion->fecha_eval->format('d/m/Y') }}
 @if($evaluacion->hora_eval)
 · {{ \Carbon\Carbon::parse($evaluacion->hora_eval)->format('H:i') }}
 @endif
 </td>
 <td style="font-size: 11px; font-weight: bold; color: #2F3E5C;">
 @if($evaluacion->instrumento->tipo_resultado === 'TIEMPO')
 {{ $evaluacion->puntaje_total }} segundos
 @elseif($evaluacion->puntaje_total !== null)
 {{ $evaluacion->puntaje_total }}
 @if($evaluacion->instrumento->puntaje_maximo)
 / {{ number_format($evaluacion->instrumento->puntaje_maximo, 0) }} pts
 @endif
 @else
 --
 @endif
 </td>
 <td style="font-weight: bold; font-size: 10.5px;">
 {{ $evaluacion->categoria_resultado ?: 'Sin clasificar' }}
 </td>
 <td>
 @php
 $alerta = strtoupper($evaluacion->nivel_alerta);
 $badgeClass = match($alerta) {
 'CRITICO' => 'alerta-critico',
 'PREVENTIVO' => 'alerta-preventivo',
 default => 'alerta-normal',
 };
 $alertaLabel = match($alerta) {
 'CRITICO' => 'Crítico',
 'PREVENTIVO' => 'Preventivo',
 default => 'Normal',
 };
 @endphp
 <span class="eval-badge {{ $badgeClass }}">{{ $alertaLabel }}</span>
 </td>
 </tr>
 </tbody>
 </table>

 @if($evaluacion->nivel_riesgo)
 <div style="margin-top: 15px; font-size: 10.5px; font-weight: bold;">
 <span style="color: #666; font-size: 9px; text-transform: uppercase;">Nivel de Riesgo Asociado:</span> 
 <span style="color: #5B5F97;">{{ $evaluacion->nivel_riesgo }}</span>
 </div>
 @endif

 {{-- Observaciones del Especialista --}}
 @if($evaluacion->observaciones)
 <div style="margin-top: 20px;">
 <div class="section-title">Observaciones Clínicas del Especialista</div>
 <div class="observations-box">"{{ $evaluacion->observaciones }}"
 </div>
 </div>
 @endif

 {{-- Firma del Evaluador y Dirección --}}
 <div class="footer">
 <div class="signature-box">
 <div class="signature-line"></div>
 <div class="signature-text">Evaluador Registrado:</div>
 <div style="font-size: 9px; font-weight: bold; color: #2F3E5C; margin-top: 3px;">
 {{ $evaluacion->registrador->nombre }}
 </div>
 <div style="font-size: 7.5px; color: #777;">Especialista CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>
 </div>
 <div class="signature-box">
 <div class="signature-line"></div>
 <div class="signature-text">Firma Dirección Médica</div>
 <div style="font-size: 9px; font-weight: bold; color: #2F3E5C; margin-top: 3px;">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS
 </div>
 <div style="font-size: 7.5px; color: #777;">Control de Calidad Clínico</div>
 </div>
 <p style="margin-top: 40px; font-size: 8px; color: #999;">REPORTE OFICIAL GENERADO POR LA SUITE CLÍNICA DE REMEMBERMIND SYSTEM · EXPEDIENTE CLÍNICO INTEGRAL</p>
 </div>
</body>
</html>
