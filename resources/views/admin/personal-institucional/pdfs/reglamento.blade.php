<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo_doc }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #333333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #3F7D5A;
            padding-bottom: 6px;
            margin-bottom: 20px;
        }
        .logo-text {
            font-size: 14pt;
            font-weight: bold;
            color: #3F7D5A;
            margin: 0;
        }
        .sub-logo {
            font-size: 8pt;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            color: #111111;
            text-decoration: underline;
        }
        .section-title {
            font-weight: bold;
            font-size: 9.5pt;
            border-bottom: 1px solid #dddddd;
            padding-bottom: 2px;
            margin-top: 12px;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #3F7D5A;
        }
        .content {
            text-align: justify;
            margin-bottom: 10px;
        }
        .table-datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        .table-datos td {
            padding: 4px 6px;
            vertical-align: top;
            border: 1px solid #eeeeee;
        }
        .label {
            font-weight: bold;
            width: 25%;
            background-color: #f9f9f9;
            color: #444444;
        }
        .value {
            width: 75%;
        }
        .table-docs {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        .table-docs th, .table-docs td {
            border: 1px solid #dddddd;
            padding: 4px 6px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="logo-text">GERIÁTRICO JARDÍN DE LOS RECUERDOS</p>
        <p class="sub-logo">Departamento de Talento Humano y Operaciones</p>
    </div>

    <div class="title">
        ACTA DE RECEPCIÓN Y ACEPTACIÓN DEL REGLAMENTO INTERNO
    </div>

    <div class="content">
        Por medio del presente documento, yo, <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>, con C.I. número <strong>{{ $nro_documento }} {{ $expedido }}</strong>, en mi condición de ingresante para desempeñar las funciones de <strong>{{ $rol_label }}</strong> en el área <strong>{{ $area_nombre }}</strong>, dejo constancia de lo siguiente:
    </div>

    <div class="section-title">Primero: Recepción de Normas Internas</div>
    <div class="content">
        Declaro haber recibido en formato físico/digital una copia de las normas reguladoras básicas del <strong>Reglamento Interno de Trabajo</strong> del Geriátrico "Jardín de los Recuerdos", comprometiéndome a su cumplimiento estricto.
    </div>

    <div class="section-title">Segundo: Compromiso de Regularización de Expediente</div>
    <div class="content">
        Asimismo, certifico la entrega de los documentos detallados en la tabla de control a continuación. En caso de presentar documentos marcados como PENDIENTE, asumo la responsabilidad de regularizar los mismos ante el departamento de Talento Humano en el plazo establecido de 48 horas a partir de la firma del presente.
    </div>

    <div class="section-title">Tercero: Expediente de Documentación Adjunta</div>
    <div style="font-size: 8.5pt; margin-bottom: 6px;">
        <strong>Estado General del Expediente:</strong> 
        <span style="color: {{ $estado_general === 'ACTIVO' ? '#3F7D5A' : '#d9534f' }}; font-weight: bold;">
            {{ $estado_general }}
        </span>
    </div>
    
    <table class="table-docs">
        <thead>
            <tr>
                <th style="text-align: left; background-color: #f2f2f2; font-weight: bold;">Documento</th>
                <th style="text-align: center; background-color: #f2f2f2; font-weight: bold; width: 15%;">Bloque</th>
                <th style="text-align: center; background-color: #f2f2f2; font-weight: bold; width: 15%;">Estado</th>
                <th style="text-align: left; background-color: #f2f2f2; font-weight: bold; width: 35%;">Observación / Nota</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documentos as $doc)
                <tr>
                    <td style="text-align: left;">{{ $doc['nombre'] }}</td>
                    <td style="text-align: center;">{{ $doc['tipo'] }}</td>
                    <td style="text-align: center; font-weight: bold; color: {{ $doc['estado'] === 'CARGADO' || $doc['estado'] === 'VALIDADO' ? '#3F7D5A' : ($doc['estado'] === 'OBSERVADO' ? '#d9534f' : '#f0ad4e') }};">
                        {{ $doc['estado'] }}
                    </td>
                    <td style="color: #555555; text-align: left;">{{ $doc['observacion'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($observaciones_registro))
        <div class="section-title">Observaciones de Registro</div>
        <div class="content" style="font-size: 8.5pt; font-style: italic; background-color: #fafafa; padding: 6px; border-left: 3px solid #3F7D5A; margin-bottom: 12px;">
            {{ $observaciones_registro }}
        </div>
    @endif

    <div class="content" style="margin-top: 15px;">
        En fe de conformidad, aceptación y constancia, las partes firman la presente acta en la ciudad de La Paz, a la fecha: <strong>{{ $fecha_hoy }}</strong>.
    </div>

    <!-- Espacios de firma para tres personas -->
    <table style="width: 100%; margin-top: 40px; border: none; border-collapse: collapse;">
        <tr style="border: none;">
            <td style="width: 33%; text-align: center; border: none; padding: 5px;">
                <div style="height: 45px;"></div>
                <div style="border-top: 1px solid #333333; width: 85%; margin: 0 auto 4px auto;"></div>
                <div style="font-weight: bold; font-size: 8.5pt;">{{ $nombres }} {{ $ap_paterno }}</div>
                <div style="font-size: 7.5pt; color: #666666;">Ingresante (Prestador)</div>
                <div style="font-size: 7.5pt; color: #666666;">C.I. {{ $nro_documento }}</div>
            </td>
            <td style="width: 33%; text-align: center; border: none; padding: 5px;">
                <div style="height: 45px;"></div>
                <div style="border-top: 1px solid #333333; width: 85%; margin: 0 auto 4px auto;"></div>
                <div style="font-weight: bold; font-size: 8.5pt;">{{ $responsable_nombre }}</div>
                <div style="font-size: 7.5pt; color: #666666;">Responsable de Registro</div>
                <div style="font-size: 7.5pt; color: #666666;">Talento Humano</div>
            </td>
            <td style="width: 33%; text-align: center; border: none; padding: 5px;">
                <div style="height: 45px;"></div>
                <div style="border-top: 1px solid #333333; width: 85%; margin: 0 auto 4px auto;"></div>
                <div style="font-weight: bold; font-size: 8.5pt;">Lic. Amanda G. de Zelaya</div>
                <div style="font-size: 7.5pt; color: #666666;">Representante Institucional</div>
                <div style="font-size: 7.5pt; color: #666666;">Directora General</div>
            </td>
        </tr>
    </table>
</body>
</html>
