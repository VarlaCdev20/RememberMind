<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentos pendientes — Casa Amandita</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 0; color: #333; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: #3F7D5A; padding: 28px 32px; text-align: center; }
        .header h1 { color: #fff; font-size: 22px; margin: 0; letter-spacing: 1px; }
        .header p { color: rgba(255,255,255,0.8); font-size: 12px; margin: 6px 0 0; }
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; margin-bottom: 16px; }
        .info-box { background: #f0f9f4; border-left: 4px solid #3F7D5A; padding: 14px 16px; border-radius: 0 8px 8px 0; margin-bottom: 20px; }
        .info-box p { margin: 0 0 4px; font-size: 13px; }
        .section-title { font-size: 13px; font-weight: bold; color: #3F7D5A; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
        .doc-list { list-style: none; padding: 0; margin: 0 0 20px; }
        .doc-list li { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; margin-bottom: 8px; font-size: 13px; color: #555; }
        .doc-list li::before { content: "⏳"; font-size: 16px; }
        .alert-box { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; font-size: 13px; color: #856404; }
        .alert-box strong { display: block; margin-bottom: 4px; font-size: 14px; }
        .footer-note { font-size: 12px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 16px; margin-top: 8px; }
        .footer { background: #f4f6f8; padding: 16px 32px; text-align: center; font-size: 11px; color: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CASA AMANDITA</h1>
            <p>Centro Geriátrico "Jardín de los Recuerdos" — RememberMind</p>
        </div>
        <div class="body">
            <p class="greeting">Estimado/a <strong>{{ $nombreFamiliar }}</strong>,</p>

            <p style="font-size:13px; margin-bottom:16px;">
                Le informamos que la preadmisión del adulto mayor <strong>{{ $nombreAdulto }}</strong>
                ha sido registrada exitosamente en nuestro sistema. Sin embargo, quedan documentos
                pendientes de entrega que deben ser presentados dentro del plazo establecido.
            </p>

            <div class="info-box">
                <p><strong>Código de preadmisión:</strong> {{ $preadmision->cod_pre }}</p>
                <p><strong>Adulto mayor:</strong> {{ $nombreAdulto }}</p>
                <p><strong>Fecha de registro:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <p class="section-title">Documentos pendientes de entrega</p>
            <ul class="doc-list">
                @foreach ($documentosPendientes as $doc)
                    <li>{{ $doc }}</li>
                @endforeach
            </ul>

            <div class="alert-box">
                <strong>⚠ Fecha límite de entrega: {{ $fechaLimite }}</strong>
                Por favor, presente los documentos indicados en las instalaciones de Casa Amandita
                o comuníquese con el área de admisiones antes de la fecha límite para evitar
                observaciones en el expediente de preadmisión.
            </div>

            <p style="font-size:13px; margin-bottom:8px;">
                Si ya presentó alguno de estos documentos o tiene alguna consulta, no dude en
                comunicarse con nosotros directamente.
            </p>

            <p class="footer-note">
                Este correo es generado automáticamente por el sistema RememberMind — Casa Amandita.<br>
                Por favor no responda a este mensaje.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Casa Amandita — Centro Geriátrico "Jardín de los Recuerdos"
        </div>
    </div>
</body>
</html>
