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
        .list-funciones {
            margin-bottom: 12px;
            padding-left: 15px;
            font-size: 9pt;
        }
        .list-funciones li {
            margin-bottom: 6px;
            text-align: justify;
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
        <p class="sub-logo">Dirección Operativa y Coordinación de Cuidado</p>
    </div>

    <div class="title">
        FORMULARIO DE ASIGNACIÓN INICIAL DE FUNCIONES
    </div>

    <div class="content">
        En la fecha, se procede a la asignación oficial de las funciones y responsabilidades iniciales que deberá desempeñar <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>, con C.I. <strong>{{ $nro_documento }} {{ $expedido }}</strong>, bajo el cargo de <strong>{{ $rol_label }}</strong> asignado al área de <strong>{{ $area_nombre }}</strong> dentro del Geriátrico "Jardín de los Recuerdos":
    </div>

    <div class="section-title">Funciones Operativas Principales</div>
    <ul class="list-funciones">
        <li><strong>Cuidado e Intervención:</strong> Brindar asistencia directa, oportuna y empática de acuerdo a los manuales del puesto.</li>
        <li><strong>Registro y Bitácora de Turno:</strong> Registrar obligatoriamente de forma veraz, descriptiva y oportuna cualquier novedad, eventualidad o reporte diario en el sistema de bitácora institucional.</li>
        <li><strong>Seguridad y Emergencia:</strong> Aplicar estrictamente los protocolos de movilización y primeros auxilios de la institución, informando inmediatamente al superior sobre cualquier alerta médica.</li>
        <li><strong>Trabajo en Equipo:</strong> Participar activamente en las entregas de turno e inducciones constantes.</li>
    </ul>

    <div class="section-title">Expediente de Documentación Adjunta</div>
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
        En señal de conformidad y aceptación mutua, firman el presente formulario en la ciudad de La Paz, a la fecha: <strong>{{ $fecha_hoy }}</strong>.
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
