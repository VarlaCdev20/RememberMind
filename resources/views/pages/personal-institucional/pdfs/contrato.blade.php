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
        <p class="sub-logo">Departamento de Administración y Talento Humano</p>
    </div>

    <div class="title">
        CONTRATO DE PRESTACIÓN DE SERVICIOS
    </div>

    <div class="content">
        Conste por el presente documento privado de contrato de prestación de servicios, el cual podrá ser elevado a instrumento público con el solo reconocimiento de firmas y rúbricas, suscrito bajo las cláusulas siguientes:
    </div>

    <div class="section-title">Cláusula Primera: Partes Contratantes</div>
    <div class="content">
        Intervienen en la firma del presente contrato, por una parte el <strong>Geriátrico "Jardín de los Recuerdos"</strong>, representado para este acto por el responsable de registro y personal de Talento Humano autorizado, y por otra parte el profesional cuyos datos se detallan a continuación, a quien en lo sucesivo se denominará el <strong>Prestador de Servicios</strong>:
    </div>

    <table class="table-datos">
        <tr>
            <td class="label">Nombres y Apellidos</td>
            <td class="value">{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</td>
        </tr>
        <tr>
            <td class="label">Documento de Identidad</td>
            <td class="value">{{ $nro_documento }} {{ $expedido }}</td>
        </tr>
        <tr>
            <td class="label">Domicilio/Dirección</td>
            <td class="value">{{ $direccion }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono/Celular</td>
            <td class="value">{{ $celular }}</td>
        </tr>
        <tr>
            <td class="label">Rol Asignado</td>
            <td class="value">{{ $rol_label }}</td>
        </tr>
        <tr>
            <td class="label">Área Destinada</td>
            <td class="value">{{ $area_nombre }}</td>
        </tr>
    </table>

    <div class="section-title">Cláusula Segunda: Objeto del Contrato</div>
    <div class="content">
        El Prestador de Servicios se compromete a realizar las labores inherentes a su rol de <strong>{{ $rol_label }}</strong> en el área de <strong>{{ $area_nombre }}</strong>, brindando soporte técnico, asistencial o administrativo con el más alto nivel de diligencia, ética y apego a los protocolos del Geriátrico.
    </div>

    <div class="section-title">Cláusula Tercera: Vigencia y Condiciones</div>
    <div class="content">
        El presente vínculo entra en vigencia a partir de la fecha de su firma, con un periodo de inducción y prueba inicial de 3 meses. Los honorarios, turnos y horarios de cobertura se estipulan de conformidad con la normativa interna de administración del personal y la escala salarial correspondiente a su categoría.
    </div>

    <div class="section-title">Cláusula Cuarta: Expediente de Documentación Adjunta</div>
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
        En conformidad con todas las cláusulas precedentes, las partes firman el presente acuerdo en la ciudad de La Paz, a la fecha: <strong>{{ $fecha_hoy }}</strong>.
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
