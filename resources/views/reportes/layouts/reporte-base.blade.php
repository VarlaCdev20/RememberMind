<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'Reporte Institucional — Casa Amandita')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #2F3E5C;
            background: #F8F6F3;
            line-height: 1.5;
        }

        .page {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 36px 44px 40px;
        }

        /* ── Encabezado ─────────────────────────────────── */
        .rm-header {
            border-bottom: 3px solid #E97A5F;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }
        .rm-header-logo {
            color: #E97A5F;
            font-size: 20pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .rm-header-sub {
            color: #2F3E5C;
            font-size: 11pt;
            font-weight: bold;
            margin-top: 2px;
        }
        .rm-header-badge {
            display: inline-block;
            background: #E97A5F;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .rm-header-meta {
            font-size: 8.5pt;
            color: #666666;
            margin-top: 8px;
            border-top: 1px solid #E6DDD3;
            padding-top: 6px;
        }

        /* ── Secciones ──────────────────────────────────── */
        .rm-seccion {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }
        .rm-seccion-titulo {
            background: #2F3E5C;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 5px 12px;
            margin-bottom: 10px;
            letter-spacing: 0.3px;
        }
        .rm-seccion-titulo-verde {
            background: #2A9D8F;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 5px 12px;
            margin-bottom: 10px;
        }
        .rm-seccion-titulo-naranja {
            background: #D4843A;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 5px 12px;
            margin-bottom: 10px;
        }
        .rm-seccion-titulo-morado {
            background: #7A68B0;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 5px 12px;
            margin-bottom: 10px;
        }
        .rm-seccion-titulo-terracota {
            background: #E97A5F;
            color: #ffffff;
            font-size: 9.5pt;
            font-weight: bold;
            padding: 5px 12px;
            margin-bottom: 10px;
        }

        /* ── Tabla general ──────────────────────────────── */
        .rm-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 8px;
        }
        .rm-table th {
            background: #E6DDD3;
            color: #2F3E5C;
            font-weight: bold;
            padding: 5px 8px;
            text-align: left;
            border: 1px solid #C7B5A3;
            font-size: 8.5pt;
        }
        .rm-table td {
            padding: 5px 8px;
            border: 1px solid #E6DDD3;
            color: #2F3E5C;
            vertical-align: top;
        }
        .rm-table tr:nth-child(even) td {
            background: #F8F6F3;
        }

        /* ── KPI grid (tabla de 3 columnas) ─────────────── */
        .rm-kpi-wrap {
            width: 100%;
        }
        .rm-kpi-row {
            width: 100%;
        }
        .rm-kpi-cell {
            width: 33%;
            border: 1px solid #C7B5A3;
            padding: 10px 8px;
            text-align: center;
            vertical-align: top;
            background: #F8F6F3;
        }
        .rm-kpi-valor {
            font-size: 20pt;
            font-weight: bold;
            color: #E97A5F;
            line-height: 1.1;
        }
        .rm-kpi-valor-azul { color: #2F3E5C; }
        .rm-kpi-valor-verde { color: #2A9D8F; }
        .rm-kpi-valor-naranja { color: #D4843A; }
        .rm-kpi-valor-morado { color: #7A68B0; }
        .rm-kpi-valor-olivo { color: #63775B; }
        .rm-kpi-titulo {
            font-size: 8pt;
            font-weight: bold;
            color: #2F3E5C;
            margin-top: 4px;
        }
        .rm-kpi-sub {
            font-size: 7.5pt;
            color: #888888;
            margin-top: 2px;
        }

        /* ── Alertas ─────────────────────────────────────── */
        .rm-alerta {
            padding: 6px 10px;
            border-left: 3px solid #E97A5F;
            background: #FEF3EE;
            margin-bottom: 5px;
            font-size: 9pt;
        }
        .rm-alerta.info {
            border-left-color: #3b82f6;
            background: #EFF6FF;
        }
        .rm-alerta.ok {
            border-left-color: #10b981;
            background: #ECFDF5;
        }
        .rm-alerta-nivel {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #991b1b;
        }
        .rm-alerta.info .rm-alerta-nivel { color: #1e40af; }
        .rm-alerta.ok .rm-alerta-nivel { color: #065f46; }

        /* ── Métricas pequeñas 2 cols ───────────────────── */
        .rm-metrics-row {
            width: 100%;
        }
        .rm-metric-left, .rm-metric-right {
            width: 50%;
            vertical-align: top;
            padding-right: 6px;
        }
        .rm-metric-right { padding-right: 0; padding-left: 6px; }
        .rm-metric-item {
            padding: 5px 8px;
            border: 1px solid #E6DDD3;
            margin-bottom: 4px;
            background: #F8F6F3;
        }
        .rm-metric-label {
            font-size: 8pt;
            color: #888888;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .rm-metric-val {
            font-size: 14pt;
            font-weight: bold;
            color: #2F3E5C;
        }
        .rm-metric-val-verde { color: #2A9D8F; }
        .rm-metric-val-naranja { color: #D4843A; }
        .rm-metric-val-terracota { color: #E97A5F; }

        /* ── Nota legal ─────────────────────────────────── */
        .rm-nota-legal {
            background: #FEF3EE;
            border-left: 3px solid #E97A5F;
            padding: 8px 12px;
            font-size: 8.5pt;
            color: #2F3E5C;
            margin-bottom: 16px;
            font-style: italic;
        }

        /* ── Footer ─────────────────────────────────────── */
        .rm-footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #E6DDD3;
            font-size: 8pt;
            color: #aaaaaa;
            text-align: center;
        }

        /* ── Barra de acciones (solo HTML, no PDF) ──────── */
        .rm-acciones {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 999;
            background: #ffffff;
            border: 1px solid #C7B5A3;
            border-radius: 8px;
            padding: 8px 12px;
        }
        .rm-btn {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 9pt;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            border: none;
            margin-left: 6px;
        }
        .rm-btn:first-child { margin-left: 0; }
        .rm-btn-print { background: #2F3E5C; color: #ffffff; }
        .rm-btn-pdf   { background: #E97A5F; color: #ffffff; }
        .rm-btn-excel { background: #2A9D8F; color: #ffffff; }

        @media print {
            .rm-acciones { display: none !important; }
            body { background: #ffffff; }
            .page { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="page">
        @yield('contenido')
    </div>
</body>
</html>
