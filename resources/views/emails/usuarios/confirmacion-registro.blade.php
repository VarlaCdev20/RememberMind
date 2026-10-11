<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmación de Registro - RememberMind</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #3F7D5A;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header p {
            color: #d1ebd9;
            margin: 5px 0 0 0;
            font-size: 12px;
            font-weight: 500;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            font-size: 16px;
            color: #3F7D5A;
            margin-top: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .content p {
            font-size: 14px;
            line-height: 1.6;
            color: #4a5568;
            margin: 15px 0;
        }
        .summary-card {
            background-color: #f7fafc;
            border-left: 4px solid #3F7D5A;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .summary-row {
            margin-bottom: 10px;
            font-size: 14px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
        }
        .label {
            font-weight: bold;
            color: #718096;
            text-transform: uppercase;
            font-size: 11px;
            width: 150px;
            display: inline-block;
        }
        .value {
            font-weight: bold;
            color: #2d3748;
        }
        .documents-list {
            padding-left: 20px;
            margin: 20px 0;
        }
        .documents-list li {
            font-size: 13.5px;
            margin-bottom: 8px;
            color: #4a5568;
        }
        .alert-box {
            background-color: #fffaf0;
            border-left: 4px solid #dd6b20;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            color: #7b341e;
        }
        .footer {
            background-color: #f7fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #edf2f7;
            font-size: 11px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>GERIÁTRICO JARDÍN DE LOS RECUERDOS</h1>
            <p>RememberMind &bull; Confirmación de Registro e Incorporación</p>
        </div>
        
        <div class="content">
            <h2>Bienvenido(a) a la Institución</h2>
            
            <p>Estimado(a) <strong>{{ $nombre_completo }}</strong>,</p>
            
            <p>
                Nos complace informarle que su proceso de vinculación en el sistema **RememberMind** del **Geriátrico Jardín de los Recuerdos** ha sido registrado formalmente. 
            </p>
            
            <div class="summary-card">
                <div class="summary-row">
                    <span class="label">Rol Asignado:</span>
                    <span class="value">{{ $rol_label }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Área Destinada:</span>
                    <span class="value">{{ $area_nombre }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Estado de Cuenta:</span>
                    <span class="value" style="color: {{ $estado_general === 'ACTIVO' ? '#38a169' : '#dd6b20' }};">{{ $estado_general }}</span>
                </div>
            </div>

            @if(count($documentos_pendientes) > 0)
                <h3 style="font-size: 14px; color: #2d3748; margin-top: 20px; font-weight: bold; text-transform: uppercase;">DOCUMENTOS PENDIENTES DE PRESENTAR / FIRMAR:</h3>
                <ul class="documents-list">
                    @foreach($documentos_pendientes as $doc)
                        <li>
                            <strong style="color: #e53e3e;">[PENDIENTE]</strong> {{ $doc }}
                        </li>
                    @endforeach
                </ul>
                
                <div class="alert-box">
                    <strong>Plazo de Entrega Obligatorio:</strong> Dispone de un plazo máximo de <strong>48 horas</strong> para regularizar y subir los documentos pendientes indicados. Fecha límite establecida: <strong>{{ $fecha_limite }}</strong>.
                </div>
            @else
                <div class="alert-box" style="background-color: #f0fff4; border-left-color: #38a169; color: #22543d;">
                    <strong>Expediente Completo:</strong> Ha entregado y firmado la totalidad de los documentos obligatorios requeridos para su contratación. ¡Gracias por su colaboración!
                </div>
            @endif

            <p style="margin-top: 20px;">
                Adjunto a este correo encontrará copias en formato PDF de los documentos institucionales autogenerados y firmados para su resguardo administrativo personal.
            </p>
        </div>
        
        <div class="footer">
            Atentamente,<br>
            <strong>Departamento de Administración y Talento Humano</strong><br>
            Geriátrico Jardín de los Recuerdos &bull; RememberMind
        </div>
    </div>
</body>
</html>
