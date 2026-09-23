<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>Credenciales de Acceso - RememberMind</title>
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
 padding: 30px;
 text-align: center;
 border-bottom: 4px solid #E27D60;
 }
 .header h1 {
 color: #ffffff;
 margin: 0;
 font-size: 24px;
 font-weight: 900;
 letter-spacing: 0.08em;
 text-transform: uppercase;
 }
 .header p {
 color: #E6DDD3;
 margin: 5px 0 0 0;
 font-size: 11px;
 font-weight: bold;
 letter-spacing: 0.1em;
 text-transform: uppercase;
 }
 .content {
 padding: 40px 30px;
 }
 .content h2 {
 font-size: 18px;
 color: #2F3E5C;
 margin-top: 0;
 font-weight: bold;
 text-transform: uppercase;
 letter-spacing: 0.05em;
 }
 .content p {
 font-size: 14px;
 line-height: 1.6;
 color: #555555;
 margin: 15px 0;
 }
 .credentials-box {
 background-color: #FAF7F3;
 border: 1px solid #C7B5A3;
 border-radius: 12px;
 padding: 20px;
 margin: 25px 0;
 }
 .credentials-row {
 margin-bottom: 12px;
 font-size: 14px;
 }
 .credentials-row:last-child {
 margin-bottom: 0;
 }
 .label {
 font-weight: bold;
 color: #967B66;
 text-transform: uppercase;
 font-size: 10px;
 letter-spacing: 0.05em;
 display: inline-block;
 width: 150px;
 }
 .value {
 font-weight: bold;
 color: #2F3E5C;
 }
 .btn-container {
 text-align: center;
 margin: 30px 0;
 }
 .btn {
 background-color: #E27D60;
 color: #ffffff !important;
 padding: 14px 35px;
 text-decoration: none;
 font-weight: bold;
 font-size: 13px;
 border-radius: 30px;
 display: inline-block;
 text-transform: uppercase;
 letter-spacing: 0.08em;
 box-shadow: 0 4px 10px rgba(226, 125, 96, 0.2);
 transition: background-color 0.3s;
 }
 .warning-box {
 background-color: #FDF1ED;
 border-left: 4px solid #E27D60;
 padding: 15px;
 border-radius: 0 8px 8px 0;
 font-size: 12px;
 line-height: 1.5;
 color: #7B3E2F;
 margin: 25px 0;
 }
 .footer {
 background-color: #E6DDD3;
 padding: 20px;
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
 <h1>CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</h1>
 <p>RememberMind • Gestión Residencial Integral</p>
 </div>
 <div class="content">
 <h2>Hola, {{ $usuario->nombres }}</h2>
 <p>Se ha registrado tu expediente digital en el portal de personal médico-administrativo de <strong>CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</strong>. A continuación, te proporcionamos las credenciales de acceso iniciales para ingresar al sistema:</p>
 
 <div class="credentials-box">
 <div class="credentials-row">
 <span class="label">Correo de Acceso:</span>
 <span class="value" style="text-transform: lowercase;">{{ $usuario->correo }}</span>
 </div>
 <div class="credentials-row">
 <span class="label">Clave Temporal:</span>
 <span class="value" style="font-family: monospace; font-size: 15px; color: #E27D60;">{{ $passwordTemporal }}</span>
 </div>
 </div>

 <div class="warning-box">
 <strong>⚠️ Cambio Obligatorio de Contraseña:</strong><br>
 Por políticas estrictas de seguridad de datos clínicos e institucionales, el sistema requerirá de manera obligatoria que modifiques esta contraseña temporal al iniciar sesión por primera vez.
 </div>

 <div class="btn-container">
 <a href="{{ $enlaceSistema }}/login" class="btn" target="_blank">Acceder a RememberMind</a>
 </div>

 <p>Si tienes alguna duda o inconveniente para acceder a la plataforma, por favor ponte en contacto directo con la Dirección de Sistemas o el Responsable Administrativo del centro.</p>
 </div>
 <div class="footer">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Tel: +56 9 1234 5678 • Sistema de Gestión Médica RememberMind © {{ date('Y') }}
 </div>
 </div>
 </div>
</body>
</html>
