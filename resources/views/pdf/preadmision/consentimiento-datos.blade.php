<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consentimiento de Tratamiento de Datos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; line-height: 1.6; margin: 30px 40px; }
        .header { text-align: center; border-bottom: 3px solid #3F7D5A; padding-bottom: 14px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #3F7D5A; letter-spacing: 2px; }
        .subtitle { font-size: 10px; color: #666; margin-top: 3px; }
        .doc-title { font-size: 15px; font-weight: bold; color: #222; margin: 16px 0 4px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .meta { text-align: center; font-size: 9px; color: #888; margin-bottom: 20px; }
        .body-text { font-size: 11px; text-align: justify; margin-bottom: 14px; line-height: 1.8; }
        .highlight { font-weight: bold; }
        .section { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #3F7D5A; border-bottom: 1px solid #3F7D5A; padding-bottom: 3px; margin-bottom: 8px; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        table.data-table td.label-col { background: #f8fafc; font-weight: bold; color: #555; width: 38%; }
        .firma-block { margin-top: 40px; display: table; width: 100%; }
        .firma-col { display: table-cell; text-align: center; width: 50%; padding: 0 20px; }
        .firma-line { border-top: 1px solid #555; padding-top: 6px; margin-top: 50px; font-size: 10px; color: #555; }
        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 9px; color: #aaa; border-top: 1px solid #ddd; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">CASA AMANDITA</div>
        <div class="subtitle">Centro Geriátrico "Jardín de los Recuerdos" — RememberMind</div>
    </div>

    <div class="doc-title">Consentimiento de Tratamiento de Datos Personales</div>
    <p class="meta">Generado el {{ $fecha }} — Código: {{ $preadmision->cod_pre }}</p>

    <div class="section">
        <div class="section-title">I. Identificación</div>
        <table class="data-table">
            <tr><td class="label-col">Adulto mayor</td><td>{{ $preadmision->nombre_completo }}</td></tr>
            <tr><td class="label-col">Familiar / Responsable</td><td>{{ $preadmision->familiar_completo }}</td></tr>
            <tr><td class="label-col">Parentesco</td><td>{{ $preadmision->familiar_parentesco }}</td></tr>
            <tr><td class="label-col">Fecha de registro</td><td>{{ $preadmision->created_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. Declaración de Consentimiento</div>
        <p class="body-text">
            Yo, <span class="highlight">{{ $preadmision->familiar_completo }}</span>, en calidad de
            familiar y/o responsable legal del adulto mayor
            <span class="highlight">{{ $preadmision->nombre_completo }}</span>, declaro haber sido informado/a
            sobre el tratamiento de datos personales que realiza
            <span class="highlight">Casa Amandita — Centro Geriátrico "Jardín de los Recuerdos"</span>
            y otorgo mi consentimiento expreso para:
        </p>
        <ul style="margin: 8px 0 14px 20px; font-size: 11px; line-height: 1.8;">
            <li>El registro, almacenamiento y procesamiento de datos personales y de salud del adulto mayor en el sistema RememberMind.</li>
            <li>El uso de datos para la gestión del cuidado, seguimiento médico y administrativo institucional.</li>
            <li>La comunicación de información relevante a los profesionales de salud de la institución.</li>
            <li>El envío de notificaciones administrativas al correo electrónico y/o celular registrado.</li>
        </ul>
        <p class="body-text">
            Los datos personales serán tratados con estricta confidencialidad y no serán compartidos con
            terceros sin consentimiento expreso, salvo obligación legal. Tiene derecho a solicitar acceso,
            rectificación o eliminación de sus datos en cualquier momento.
        </p>
    </div>

    <div class="firma-block">
        <div class="firma-col">
            <div class="firma-line">
                Firma del Familiar / Responsable<br>
                <small>{{ $preadmision->familiar_completo }}</small>
            </div>
        </div>
        <div class="firma-col">
            <div class="firma-line">
                Responsable Institucional<br>
                <small>Casa Amandita</small>
            </div>
        </div>
    </div>

    <div class="footer">
        Casa Amandita — RememberMind · Documento generado el {{ $fecha }} · {{ $preadmision->cod_pre }}
    </div>
</body>
</html>
