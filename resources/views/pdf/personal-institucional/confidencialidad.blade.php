<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acuerdo de Confidencialidad</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 40px; color: #333; text-align: justify; }
        .header { text-align: center; border-bottom: 2px solid #3F7D5A; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #3F7D5A; }
        h1 { font-size: 18px; text-align: center; text-transform: uppercase; margin-bottom: 30px; }
        .signatures { margin-top: 60px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 100%; text-align: center; vertical-align: bottom; height: 100px; }
        .sign-line { border-top: 1px solid #000; width: 50%; margin: 0 auto; margin-top: 50px; padding-top: 5px; font-size: 14px; }
        .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">CASA AMANDITA</div>
        <div>Centro Geriátrico "Jardín de los Recuerdos"</div>
    </div>

    <h1>ACUERDO DE CONFIDENCIALIDAD Y CONFORMIDAD INSTITUCIONAL</h1>

    <p>Yo, <strong>{{ $data['nombre_completo'] ?? '' }}</strong>, con C.I. N° <strong>{{ $data['ci'] ?? '' }}</strong>, en mi calidad de personal de <strong>{{ $data['clasificacion'] ?? '' }}</strong> (Rol: {{ $data['rol'] ?? '' }}), por el presente documento declaro de manera libre y voluntaria que:</p>

    <p>1. Me comprometo a guardar absoluta reserva y confidencialidad respecto a toda la información médica, personal, familiar, administrativa y financiera a la que tenga acceso durante el ejercicio de mis funciones en <strong>Casa Amandita</strong>.</p>

    <p>2. Entiendo que la divulgación de datos sensibles de los residentes o información privativa de la institución constituye una falta grave que puede derivar en sanciones administrativas, civiles o penales según la legislación vigente.</p>

    <p>3. Acepto cumplir con el Reglamento Interno de Trabajo, los manuales de procedimientos y las directrices de mi área (<strong>{{ $data['area'] ?? '' }}</strong>).</p>

    <p>4. Declaro que los datos personales y profesionales proporcionados en mi proceso de registro son verídicos y comprobables.</p>

    <p>Firmado en señal de conformidad en la ciudad de La Paz, a los {{ date('d') }} días del mes de {{ \Carbon\Carbon::now()->translatedFormat('F') }} de {{ date('Y') }}.</p>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-line">
                    <strong>Firma del Trabajador / Voluntario</strong><br>
                    {{ $data['nombre_completo'] ?? '' }}<br>
                    C.I. {{ $data['ci'] ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado automáticamente por RememberMind - {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
