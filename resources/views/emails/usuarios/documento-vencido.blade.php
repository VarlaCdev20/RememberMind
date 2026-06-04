<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Plazo Vencido de Documentación - RememberMind</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #F8F3ED;
            color: #2F3E5C;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(47, 62, 92, 0.05);
            border: 1px solid #C7B5A3;
        }
        .header {
            background-color: #A94442;
            padding: 35px 30px;
            text-align: center;
            border-bottom: 4px solid #D9534F;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .header p {
            color: #F5E7E6;
            margin: 5px 0 0 0;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            font-size: 16px;
            color: #D9534F;
            margin-top: 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .content p {
            font-size: 14px;
            line-height: 1.65;
            color: #4A4A4A;
            margin: 16px 0;
        }
        .profile-summary {
            background-color: #FDFBF7;
            border: 1px dashed #D9534F;
            border-radius: 12px;
            padding: 22px;
            margin: 25px 0;
        }
        .profile-row {
            margin-bottom: 12px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        .profile-row:last-child {
            margin-bottom: 0;
        }
        .label {
            font-weight: bold;
            color: #A94442;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.08em;
            width: 180px;
        }
        .value {
            font-weight: bold;
            color: #2F3E5C;
        }
        .documents-list {
            padding-left: 20px;
            margin: 20px 0;
        }
        .documents-list li {
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 10px;
            color: #4A4A4A;
        }
        .footer {
            background-color: #FAF8F5;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #E6DDD3;
            font-size: 11px;
            color: #967B66;
        }
        .alert-note {
            background-color: rgba(217, 83, 79, 0.08);
            border-left: 3px solid #D9534F;
            padding: 15px;
            border-radius: 0 8px 8px 0;
            margin-top: 25px;
            font-size: 12.5px;
            line-height: 1.5;
            color: #2F3E5C;
        }
    </style>
</head>
<body>
    <div style="background-color: #F8F3ED; padding: 40px 0;">
        <div class="container">
            <div class="header">
                <h1>CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</h1>
                <p>RememberMind &bull; Alerta de Control de Plazo</p>
            </div>
            
            <div class="content">
                <h2>Plazo de Presentación de Documentación Expirado</h2>
                
                <p>Estimado(a) <strong>{{ $nombre_completo }}</strong>,</p>
                
                <p>
                    Le informamos que el plazo reglamentario de 48 horas para la presentación y carga de sus documentos obligatorios ha expirado. Por este motivo, el estado de los siguientes documentos en su expediente digital ha sido actualizado a <strong>VENCIDO</strong>:
                </p>
                
                <div class="profile-summary">
                    <div class="profile-row">
                        <span class="label">Usuario:</span>
                        <span class="value">{{ $nombre_completo }}</span>
                    </div>
                    <div class="profile-row">
                        <span class="label">Código de Usuario:</span>
                        <span class="value">{{ $usuario->cod_usu }}</span>
                    </div>
                    <div class="profile-row">
                        <span class="label">Fecha de Registro:</span>
                        <span class="value">{{ $usuario->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
                
                <h3 style="font-size: 14px; color: #2F3E5C; margin-top: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em;">Documentación Vencida:</h3>
                
                <ul class="documents-list">
                    @foreach($documentos as $doc)
                        <li><strong style="color: #D9534F;">[VENCIDO]</strong> {{ $doc }}</li>
                    @endforeach
                </ul>

                <p>
                    Para regularizar su situación y evitar medidas administrativas o la restricción de su acceso, por favor suba los archivos correspondientes a través de su portal de usuario o póngase en contacto directo con el departamento de Talento Humano.
                </p>
                
                <div class="alert-note">
                    <strong>Nota administrativa importante:</strong> Esta notificación ha sido generada de manera automática por el sistema de control de calidad institucional al detectar el incumplimiento en el plazo de carga de expedientes.
                </div>
            </div>
            
            <div class="footer">
                Atentamente,<br>
                <strong>Departamento de Talento Humano &bull; CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</strong><br>
                RememberMind - Gestión Residencial Integral
            </div>
        </div>
    </div>
</body>
</html>
