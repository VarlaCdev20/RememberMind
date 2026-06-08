<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Funciones</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 40px; color: #333; text-align: justify; }
        .header { text-align: center; border-bottom: 2px solid #3F7D5A; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #3F7D5A; }
        h1 { font-size: 18px; text-align: center; text-transform: uppercase; margin-bottom: 30px; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.info th, table.info td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        table.info th { background-color: #f9f9f9; width: 30%; }
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

    <h1>ACTA DE ASIGNACIÓN DE FUNCIONES Y HORARIO</h1>

    <p>Mediante la presente acta, se deja constancia de la asignación inicial de funciones y responsabilidades al personal detallado a continuación:</p>

    <table class="info">
        <tr>
            <th>Nombre del Funcionario:</th>
            <td>{{ $data['nombre_completo'] ?? '' }}</td>
        </tr>
        <tr>
            <th>C.I.:</th>
            <td>{{ $data['ci'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Rol Operativo:</th>
            <td>{{ $data['rol'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Área Asignada:</th>
            <td>{{ $data['area'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Tipo de Personal:</th>
            <td>{{ $data['clasificacion'] ?? '' }}</td>
        </tr>
        <tr>
            <th>Cargo / Responsabilidad:</th>
            <td>{{ $data['cargo'] ?? 'Personal Operativo' }}</td>
        </tr>
    </table>

    <p><strong>Funciones Principales:</strong><br>
    Las funciones específicas estarán determinadas por el Manual de Organización y Funciones de la Institución correspondiente a su rol, así como por las instrucciones directas de su jefe inmediato. El trabajador declara haber recibido la inducción necesaria para el ejercicio de sus labores.</p>

    <p>Se firma la presente Acta en señal de conocimiento y conformidad, en la ciudad de La Paz, a los {{ date('d') }} días del mes de {{ \Carbon\Carbon::now()->translatedFormat('F') }} de {{ date('Y') }}.</p>

    <table class="signatures">
        <tr>
            <td>
                <div class="sign-line">
                    <strong>Jefatura de Recursos Humanos</strong><br>
                    Casa Amandita
                </div>
            </td>
            <td>
                <div class="sign-line">
                    <strong>Firma de Conformidad</strong><br>
                    {{ $data['nombre_completo'] ?? '' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento generado automáticamente por RememberMind - {{ date('d/m/Y H:i:s') }}
    </div>
</body>
</html>
