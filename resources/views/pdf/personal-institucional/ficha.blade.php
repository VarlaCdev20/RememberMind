<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha Institucional</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; line-height: 1.5; margin: 30px 40px; }
        .header { text-align: center; border-bottom: 3px solid #3F7D5A; padding-bottom: 14px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #3F7D5A; letter-spacing: 2px; }
        .subtitle { font-size: 10px; color: #666; margin-top: 3px; }
        .doc-title { font-size: 15px; font-weight: bold; color: #222; margin: 16px 0 4px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .section { margin-bottom: 14px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #3F7D5A; border-bottom: 1px solid #3F7D5A; padding-bottom: 3px; margin-bottom: 8px; }
        .grid { display: table; width: 100%; border-collapse: collapse; }
        .row { display: table-row; }
        .cell { display: table-cell; padding: 4px 8px 4px 0; width: 50%; vertical-align: top; }
        .label { font-size: 9px; font-weight: bold; color: #888; text-transform: uppercase; }
        .value { font-size: 11px; color: #222; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-salud { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-admin { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .firma-block { margin-top: 40px; display: table; width: 100%; }
        .firma-col { display: table-cell; text-align: center; width: 50%; padding: 0 20px; }
        .firma-line { border-top: 1px solid #555; padding-top: 6px; margin-top: 50px; font-size: 10px; color: #555; }
        .footer { position: fixed; bottom: 20px; width: 100%; text-align: center; font-size: 9px; color: #aaa; border-top: 1px solid #ddd; padding-top: 6px; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        table.data-table td.label-col { background: #f8fafc; font-weight: bold; color: #555; width: 38%; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">CASA AMANDITA</div>
        <div class="subtitle">Centro Geriátrico "Jardín de los Recuerdos" — RememberMind</div>
    </div>

    <div class="doc-title">Ficha Institucional del Personal</div>
    <p style="text-align:center; font-size:9px; color:#888; margin-bottom:16px;">
        Generado el {{ $data['fecha_generacion'] ?? date('d/m/Y H:i') }} — Por: {{ $data['responsable'] ?? 'Sistema' }}
    </p>

    <div class="section">
        <div class="section-title">I. Datos de Identidad</div>
        <table class="data-table">
            <tr><td class="label-col">Nombre Completo</td><td>{{ $data['nombre_completo'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Cédula de Identidad</td><td>{{ $data['ci'] ?? '—' }} {{ $data['expedido'] ?? '' }}</td></tr>
            <tr><td class="label-col">Correo Electrónico</td><td>{{ $data['correo'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Teléfono</td><td>{{ $data['telefono'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Dirección</td><td>{{ $data['direccion'] ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. Clasificación Institucional</div>
        <table class="data-table">
            <tr><td class="label-col">Rol del Sistema</td><td>{{ $data['rol'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Tipo de Personal</td><td>{{ $data['clasificacion'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Cargo / Rol Operativo</td><td>{{ $data['cargo'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Área Asignada</td><td>{{ $data['area'] ?? '—' }}</td></tr>
            <tr><td class="label-col">Fecha de Ingreso</td><td>{{ $data['fecha_ingreso'] ?? '—' }}</td></tr>
        </table>
    </div>

    @if(($data['tipo_personal'] ?? '') === 'salud')
    <div class="section">
        <div class="section-title">III. Perfil Profesional (Salud)</div>
        <table class="data-table">
            <tr><td class="label-col">Años de Experiencia</td><td>{{ $data['anios_exp'] ?? '0' }} años</td></tr>
            <tr><td class="label-col">Matrícula Profesional</td><td>{{ $data['matricula_prof'] ?? 'No declarada' }}</td></tr>
            <tr><td class="label-col">Institución de Formación</td><td>{{ $data['institucion'] ?? 'No declarada' }}</td></tr>
        </table>
    </div>
    @endif

    <div class="section">
        <div class="section-title">IV. Firmas de Conformidad</div>
        <div class="firma-block">
            <div class="firma-col">
                <div class="firma-line">
                    {{ $data['nombre_completo'] ?? '___________________' }}<br>
                    <strong>Trabajador(a)</strong>
                </div>
            </div>
            <div class="firma-col">
                <div class="firma-line">
                    {{ $data['responsable'] ?? '___________________' }}<br>
                    <strong>Responsable Institucional</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Ficha Institucional — Casa Amandita — Documento generado por RememberMind — {{ date('Y') }}
    </div>
</body>
</html>
