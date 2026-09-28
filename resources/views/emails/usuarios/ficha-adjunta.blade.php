<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>Ficha Institucional Oficial - RememberMind</title>
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
 font-size: 24px;
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
 font-size: 18px;
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
 .info-box {
 background-[#FAF7F3];
 border: 1px solid #C7B5A3;
 background-color: #FDFBF7;
 border-radius: 12px;
 padding: 20px;
 margin: 25px 0;
 }
 .info-box p {
 margin: 8px 0;
 font-size: 13.5px;
 }
 .info-label {
 font-weight: bold;
 color: #967B66;
 text-transform: uppercase;
 font-size: 10px;
 letter-spacing: 0.08em;
 display: inline-block;
 width: 150px;
 }
 .info-value {
 font-weight: bold;
 color: #2F3E5C;
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
 <h1>CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</h1>
 <p>RememberMind • Gestión Residencial Integral</p>
 </div>
 <div class="content">
 <h2>Ficha Administrativa Oficial</h2>
 <p>Estimado/a <strong>{{ $usuario->nombres }} {{ $usuario->ap_paterno }}</strong>,</p>
 <p>Adjunto a este correo encontrará la <strong>Ficha Institucional / Expediente Digital</strong> oficial correspondiente a su registro en la plataforma RememberMind de CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.</p>
 
 <div class="info-box">
 <p><span class="info-label">Nombre completo:</span> <span class="info-value">{{ $usuario->name }}</span></p>
 <p><span class="info-label">Documento:</span> <span class="info-value">{{ $usuario->tipo_documento ?? 'CI' }} {{ $usuario->numero_documento }}</span></p>
 <p><span class="info-label">Correo Habilitado:</span> <span class="info-value" style="text-transform: lowercase;">{{ $usuario->correo }}</span></p>
 <p><span class="info-label">Fecha de Emisión:</span> <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span></p>
 </div>

 <p>Este documento es de carácter estrictamente institucional e informativo. Le sugerimos almacenarlo en sus archivos de resguardo profesional.</p>
 <p>Si detecta algún error en sus datos, por favor contacte de inmediato a la dirección administrativa del centro.</p>
 </div>
 <div class="footer">
 CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Sistema de Gestión Médica RememberMind © {{ date('Y') }}
 </div>
 </div>
 </div>
</body>
</html>
