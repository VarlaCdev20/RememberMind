<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha de Preadmisión</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; line-height: 1.5; margin: 30px 40px; }
        .header { text-align: center; border-bottom: 3px solid #3F7D5A; padding-bottom: 14px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #3F7D5A; letter-spacing: 2px; }
        .subtitle { font-size: 10px; color: #666; margin-top: 3px; }
        .doc-title { font-size: 15px; font-weight: bold; color: #222; margin: 16px 0 4px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .meta { text-align: center; font-size: 9px; color: #888; margin-bottom: 16px; }
        .section { margin-bottom: 14px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #3F7D5A; border-bottom: 1px solid #3F7D5A; padding-bottom: 3px; margin-bottom: 8px; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        table.data-table td.label-col { background: #f8fafc; font-weight: bold; color: #555; width: 38%; }
        .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-warn { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
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

    <div class="doc-title">Ficha Institucional de Preadmisión</div>
    <p class="meta">Generado el {{ $fecha }} — Código: {{ $preadmision->cod_pre }}</p>

    <div class="section">
        <div class="section-title">I. Datos del Adulto Mayor</div>
        <table class="data-table">
            <tr><td class="label-col">Nombre completo</td><td>{{ $preadmision->nombre_completo }}</td></tr>
            <tr><td class="label-col">Cédula de identidad</td><td>{{ $preadmision->ci }} — {{ $preadmision->expedicion_ci }}</td></tr>
            <tr><td class="label-col">Fecha de nacimiento</td><td>{{ $preadmision->fecha_nac?->format('d/m/Y') }}</td></tr>
            <tr><td class="label-col">Género</td><td>{{ $preadmision->genero }}</td></tr>
            <tr><td class="label-col">Estado civil</td><td>{{ $preadmision->estado_civil ?? '—' }}</td></tr>
            <tr><td class="label-col">Teléfono / Celular</td><td>{{ $preadmision->telefono ?? '—' }} / {{ $preadmision->celular ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. Domicilio de Referencia</div>
        <table class="data-table">
            <tr><td class="label-col">Departamento</td><td>{{ $preadmision->departamento_residencia ?? '—' }}</td></tr>
            <tr><td class="label-col">Ciudad / Municipio</td><td>{{ $preadmision->ciudad_municipio ?? '—' }}</td></tr>
            <tr><td class="label-col">Zona</td><td>{{ $preadmision->zona ?? '—' }}</td></tr>
            <tr><td class="label-col">Calle / Avenida</td><td>{{ $preadmision->calle ?? '—' }}</td></tr>
            <tr><td class="label-col">Referencia</td><td>{{ $preadmision->direccion_referencia ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">III. Familiar / Responsable</div>
        <table class="data-table">
            <tr><td class="label-col">Nombre completo</td><td>{{ $preadmision->familiar_completo }}</td></tr>
            <tr><td class="label-col">Parentesco</td><td>{{ $preadmision->familiar_parentesco }}</td></tr>
            <tr><td class="label-col">CI familiar</td><td>{{ $preadmision->familiar_ci ?? '—' }}</td></tr>
            <tr><td class="label-col">Celular</td><td>{{ $preadmision->familiar_celular }}</td></tr>
            <tr><td class="label-col">Correo</td><td>{{ $preadmision->familiar_correo ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">IV. Datos del Caso</div>
        <table class="data-table">
            <tr><td class="label-col">Motivo de ingreso</td><td>{{ str_replace('_', ' ', $preadmision->motivo_ingreso ?? '—') }}</td></tr>
            <tr><td class="label-col">Procedencia</td><td>{{ str_replace('_', ' ', $preadmision->procedencia_ingreso ?? '—') }}</td></tr>
            <tr><td class="label-col">Tipo de ingreso</td><td>{{ $preadmision->tipo_ingreso ?? '—' }}</td></tr>
            <tr><td class="label-col">Permanencia</td><td>{{ $preadmision->permanencia ?? '—' }}</td></tr>
            <tr><td class="label-col">Prioridad</td><td>{{ $preadmision->prioridad ?? '—' }}</td></tr>
            <tr><td class="label-col">Descripción</td><td>{{ $preadmision->descripcion_caso ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="firma-block">
        <div class="firma-col">
            <div class="firma-line">Firma del Familiar / Responsable</div>
        </div>
        <div class="firma-col">
            <div class="firma-line">Sello y Firma — Institución</div>
        </div>
    </div>

    <div class="footer">
        Casa Amandita — RememberMind · Documento generado el {{ $fecha }} · {{ $preadmision->cod_pre }}
    </div>
</body>
</html>
