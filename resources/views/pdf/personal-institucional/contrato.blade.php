<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrato Institucional</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 40px; color: #333; text-align: justify; }
        .header { text-align: center; border-bottom: 2px solid #3F7D5A; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #3F7D5A; }
        h1 { font-size: 18px; text-align: center; text-transform: uppercase; margin-bottom: 30px; }
        .signatures { margin-top: 60px; width: 100%; border-collapse: collapse; }
        .signatures td { width: 50%; text-align: center; vertical-align: bottom; height: 100px; }
        .sign-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; margin-top: 50px; padding-top: 5px; font-size: 14px; }
        .footer { position: absolute; bottom: 30px; width: 100%; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">CASA AMANDITA</div>
        <div>Centro Geriátrico "Jardín de los Recuerdos"</div>
    </div>

    <h1>CONTRATO DE PRESTACIÓN DE SERVICIOS / LABORAL</h1>

    <p>Conste por el presente documento, que celebran por una parte <strong>Casa Amandita</strong> (en adelante "LA INSTITUCIÓN") y por otra parte el/la Sr./Sra. <strong>{{ $data['nombre_completo'] ?? '' }}</strong> con C.I. N° <strong>{{ $data['ci'] ?? '' }}</strong> (en adelante "EL TRABAJADOR"), el presente acuerdo bajo las siguientes cláusulas:</p>

    <p><strong>PRIMERA: DEL OBJETO.</strong><br>
    LA INSTITUCIÓN contrata los servicios de EL TRABAJADOR para desempeñar las funciones correspondientes al rol de <strong>{{ $data['rol'] ?? '' }}</strong> ({{ $data['cargo'] ?? '' }}) en el área de <strong>{{ $data['area'] ?? '' }}</strong>, debiendo cumplir sus tareas con la debida diligencia, ética y apego a las normas internas.</p>

    <p><strong>SEGUNDA: DE LAS FUNCIONES.</strong><br>
    EL TRABAJADOR se compromete a realizar las labores encomendadas, así como aquellas derivadas de su clasificación como personal de <strong>{{ $data['clasificacion'] ?? '' }}</strong>, respetando en todo momento el bienestar de los residentes geriátricos.</p>

    <p><strong>TERCERA: DE LA VIGENCIA.</strong><br>
    El presente contrato entrará en vigencia a partir del <strong>{{ $data['fecha_ingreso'] ?? '' }}</strong>. Su duración estará sujeta a la naturaleza del servicio y las evaluaciones periódicas de desempeño institucional.</p>

    <p>En conformidad con las cláusulas anteriores, las partes firman el presente documento en la ciudad de La Paz, a los {{ date('d') }} días del mes de {{ \Carbon\Carbon::now()->translatedFormat('F') }} de {{ date('Y') }}.</p>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-line">
                    <strong>LA INSTITUCIÓN</strong><br>
                    Casa Amandita
                </div>
            </td>
            <td>
                <div class="sign-line">
                    <strong>EL TRABAJADOR</strong><br>
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
