<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carta de Bienvenida</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 40px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #3F7D5A; padding-bottom: 20px; margin-bottom: 40px; }
        .logo { font-size: 24px; font-weight: bold; color: #3F7D5A; }
        .date { text-align: right; margin-bottom: 40px; }
        h1 { font-size: 20px; color: #3F7D5A; }
        .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">CASA AMANDITA</div>
        <div>Centro Geriátrico "Jardín de los Recuerdos"</div>
    </div>

    <div class="date">
        La Paz, {{ date('d') }} de {{ \Carbon\Carbon::now()->translatedFormat('F') }} de {{ date('Y') }}
    </div>

    <h1>CARTA DE BIENVENIDA INSTITUCIONAL</h1>

    <p>Estimado/a <strong>{{ $data['nombre_completo'] ?? 'Profesional' }}</strong>,</p>

    <p>En nombre de la administración y dirección de Casa Amandita, nos complace darle la más cordial bienvenida a nuestro equipo de trabajo. Su incorporación en el rol de <strong>{{ $data['rol'] ?? 'Asignado' }}</strong> dentro del área de <strong>{{ $data['area'] ?? 'General' }}</strong> representa un valioso aporte para nuestra institución.</p>

    <p>Nuestro compromiso es brindar una atención de excelencia a nuestros residentes, basándonos en el respeto, la empatía y el profesionalismo. Estamos seguros de que su experiencia y cualidades humanas contribuirán significativamente a alcanzar nuestros objetivos.</p>

    <p>A partir de la fecha ({{ $data['fecha_ingreso'] ?? date('d/m/Y') }}), usted forma parte del grupo de {{ $data['clasificacion'] ?? 'trabajadores' }} de la institución. Le invitamos a revisar la documentación adjunta a este correo, firmar los ejemplares correspondientes y subirlos a la plataforma RememberMind o entregarlos físicamente en administración en un plazo no mayor a 48 horas.</p>

    <p>Atentamente,</p>

    <br><br><br>
    <p>___________________________________<br>
    <strong>Dirección de Recursos Humanos</strong><br>
    Casa Amandita - RememberMind</p>

    <div class="footer">
        Documento generado automáticamente por RememberMind - {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
