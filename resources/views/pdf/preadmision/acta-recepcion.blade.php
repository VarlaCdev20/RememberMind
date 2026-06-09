<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Acta de Recepción de Documentos</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; line-height: 1.6; margin: 30px 40px; }
        .header { text-align: center; border-bottom: 3px solid #3F7D5A; padding-bottom: 14px; margin-bottom: 20px; }
        .logo { font-size: 20px; font-weight: bold; color: #3F7D5A; letter-spacing: 2px; }
        .subtitle { font-size: 10px; color: #666; margin-top: 3px; }
        .doc-title { font-size: 15px; font-weight: bold; color: #222; margin: 16px 0 4px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .meta { text-align: center; font-size: 9px; color: #888; margin-bottom: 20px; }
        .body-text { font-size: 11px; text-align: justify; margin-bottom: 14px; line-height: 1.8; }
        .section { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #3F7D5A; border-bottom: 1px solid #3F7D5A; padding-bottom: 3px; margin-bottom: 8px; }
        table.data-table { width: 100%; border-collapse: collapse; }
        table.data-table td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        table.data-table td.label-col { background: #f8fafc; font-weight: bold; color: #555; width: 38%; }
        table.doc-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.doc-table th { background: #3F7D5A; color: #fff; padding: 6px 8px; font-size: 10px; text-align: left; }
        table.doc-table td { padding: 5px 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        table.doc-table tr:nth-child(even) td { background: #f8fafc; }
        .badge-ok { color: #166534; font-weight: bold; }
        .badge-pend { color: #92400e; font-weight: bold; }
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

    <div class="doc-title">Acta de Recepción de Documentos</div>
    <p class="meta">Generado el {{ $fecha }} — Código de preadmisión: {{ $preadmision->cod_pre }}</p>

    <div class="section">
        <div class="section-title">I. Datos de la Preadmisión</div>
        <table class="data-table">
            <tr><td class="label-col">Adulto mayor</td><td>{{ $preadmision->nombre_completo }}</td></tr>
            <tr><td class="label-col">CI</td><td>{{ $preadmision->ci }} — {{ $preadmision->expedicion_ci }}</td></tr>
            <tr><td class="label-col">Familiar / Responsable</td><td>{{ $preadmision->familiar_completo }}</td></tr>
            <tr><td class="label-col">Fecha de solicitud</td><td>{{ $preadmision->fecha_solicitud?->format('d/m/Y') }}</td></tr>
            <tr><td class="label-col">Tipo de ingreso</td><td>{{ $preadmision->tipo_ingreso ?? '—' }}</td></tr>
            <tr><td class="label-col">Prioridad</td><td>{{ $preadmision->prioridad ?? '—' }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">II. Documentos Recibidos</div>
        <table class="doc-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Documento</th>
                    <th>Grupo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($preadmision->documentos as $i => $doc)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $doc->nombre_documento }}</td>
                        <td>{{ strtoupper($doc->grupo_documento ?? ($doc->es_institucional ? 'institucional' : 'solicitante')) }}</td>
                        <td>
                            @if(in_array($doc->estado, ['RECIBIDO', 'GENERADO']))
                                <span class="badge-ok">✓ {{ $doc->estado }}</span>
                            @else
                                <span class="badge-pend">⏳ {{ $doc->estado }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <p class="body-text">
            La institución <strong>Casa Amandita</strong> deja constancia de haber recibido la documentación
            indicada en el presente acta para el proceso de preadmisión del adulto mayor
            <strong>{{ $preadmision->nombre_completo }}</strong>, registrado bajo el código
            <strong>{{ $preadmision->cod_pre }}</strong>. Los documentos pendientes deberán ser
            entregados dentro del plazo establecido.
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
                Recibido por — Admisiones<br>
                <small>Casa Amandita</small>
            </div>
        </div>
    </div>

    <div class="footer">
        Casa Amandita — RememberMind · Documento generado el {{ $fecha }} · {{ $preadmision->cod_pre }}
    </div>
</body>
</html>
