<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>¡Te damos la Bienvenida! - RememberMind</title>
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
            background-color: #2F3E5C;
            padding: 35px 30px;
            text-align: center;
            border-bottom: 4px solid #E27D60;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .header p {
            color: #E6DDD3;
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
            font-size: 20px;
            color: #E27D60;
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
            border: 1px dashed #C7B5A3;
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
            color: #967B66;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.08em;
            width: 180px;
        }
        .value {
            font-weight: bold;
            color: #2F3E5C;
        }
        .instructions-list {
            padding-left: 20px;
            margin: 20px 0;
        }
        .instructions-list li {
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 10px;
            color: #555555;
        }
        .btn-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            background-color: #2F3E5C;
            color: #ffffff !important;
            padding: 14px 35px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            border-radius: 30px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            box-shadow: 0 4px 10px rgba(47, 62, 92, 0.2);
            transition: background-color 0.3s;
        }
        .footer {
            background-color: #E6DDD3;
            padding: 25px;
            text-align: center;
            font-size: 11px;
            color: #967B66;
            border-top: 1px solid #C7B5A3;
        }
    </style>
</head>
<body>
    <div style="padding: 20px 0;">
        <div class="container">
            <div class="header">
                <h1>Casa Amandita</h1>
                <p>RememberMind • Gestión Residencial Integral</p>
            </div>
            <div class="content">
                <h2>¡Hola, {{ $usuario->nombres }}!</h2>
                <p>Es un verdadero placer darte la bienvenida al equipo humano y asistencial de <strong>Casa Amandita</strong>. Hemos habilitado formalmente tu perfil y vinculación en nuestra plataforma médica y administrativa de control:</p>
                
                <div class="profile-summary">
                    <div class="profile-row">
                        <span class="label">Rol Asignado:</span>
                        <span class="value">{{ $rolDisplay }}</span>
                    </div>
                    <div class="profile-row">
                        <span class="label">Área de Trabajo:</span>
                        <span class="value">{{ $areaDisplay }}</span>
                    </div>
                    <div class="profile-row">
                        <span class="label">Tipo de Vinculación:</span>
                        <span class="value">{{ $usuario->tipo_vinculacion ?? 'REGULAR' }}</span>
                    </div>
                </div>

                <p>Para comenzar con tus labores en el centro, te solicitamos realizar los siguientes primeros pasos obligatorios:</p>
                
                <ul class="instructions-list">
                    <li><strong>1. Ficha de Identidad & Datos de Contacto:</strong> Revisa y confirma que tus datos personales, celular de emergencia y domicilio se encuentren perfectamente registrados.</li>
                    <li><strong>2. Documentación Digital:</strong> Accede a tu Ficha de Usuario > sección <em>Documentación</em> para subir los archivos requeridos según tu rol (Certificado de Título Profesional, Matrícula Profesional, Cédula de Identidad, Antecedentes, etc.).</li>
                    <li><strong>3. Horarios e Inducción:</strong> Coordina con la Dirección Médica o Administrativa para revisar tu asignación horaria en el calendario de turnos.</li>
                </ul>

                <div class="btn-container">
                    <a href="{{ $enlaceSistema }}/login" class="btn" target="_blank">Comenzar en el Portal</a>
                </div>

                <p>En Casa Amandita tenemos un compromiso absoluto con el bienestar y el cuidado geriátrico de excelencia de cada uno de nuestros adultos mayores. Agradecemos profundamente tu profesionalismo, vocación y dedicación diaria.</p>
            </div>
            <div class="footer">
                Casa Amandita • Sistema de Gestión Médica RememberMind © {{ date('Y') }}
            </div>
        </div>
    </div>
</body>
</html>
