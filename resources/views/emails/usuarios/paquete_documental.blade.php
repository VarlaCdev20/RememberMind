<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>Paquete Documental - Centro Geriátrico Jardín de los Recuerdos</title>
</head>
<body style="font-family: 'Helvetica', 'Arial', sans-serif; color: #374151; line-height: 1.6; background-color: #FAF7F2; margin: 0; padding: 30px;">
 <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(226, 125, 96, 0.2);">
 
 <!-- Cabecera -->
 <div style="background-color: #2E5C31; padding: 25px; text-align: center; border-bottom: 5px solid #E27D60;">
 <h1 style="margin: 0; color: #FDF1ED; font-size: 18px; font-weight: bold; letter-spacing: 1px;">CENTRO GERIÁTRICO</h1>
 <h2 style="margin: 0; color: #E27D60; font-size: 20px; font-weight: 900; letter-spacing: 1.5px;">JARDÍN DE LOS RECUERDOS</h2>
 </div>

 <!-- Contenido -->
 <div style="padding: 30px;">
 <h2 style="color: #2E5C31; margin-top: 0;">¡Hola, {{ $usuario->nombres }}!</h2>
 
 <p>Adjuntamos a este correo tu <strong>Paquete Documental Institucional</strong>. Este archivo contiene los documentos oficiales necesarios para tu integración y permanencia en nuestra institución.</p>
 
 <div style="background-color: #FDF1ED; border-left: 4px solid #E27D60; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0;">
 <p style="margin: 0; color: #374151;"><strong>Por favor revisa el contenido.</strong> Si hay documentos que requieran firma, te solicitamos que los imprimas, los firmes y los entregues a la brevedad posible en la administración.</p>
 </div>

 @if(!empty($requisitos))
 <h3 style="color: #2E5C31; border-bottom: 2px solid #E27D60; display: inline-block; padding-bottom: 4px;">Requisitos pendientes a entregar:</h3>
 <ul style="color: #374151; padding-left: 20px;">
 @foreach($requisitos as $req)
 <li style="margin-bottom: 5px;">{{ $req }}</li>
 @endforeach
 </ul>
 @endif

 <p>Si tienes alguna consulta, no dudes en contactarnos. Estamos a tu entera disposición.</p>
 
 <br>
 <p style="margin-bottom: 5px;">Atentamente,</p>
 <p style="margin-top: 0; color: #2E5C31; font-weight: bold;">Administración - CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</p>
 </div>

 <!-- Pie de página -->
 <div style="background-color: #FAF7F2; padding: 15px; text-align: center; border-top: 1px solid #E5E7EB;">
 <p style="margin: 0; font-size: 11px; color: #6B7280;">Este es un mensaje automático del Sistema de Gestión Institucional. Por favor, no respondas a este correo.</p>
 </div>
 </div>
</body>
</html>
