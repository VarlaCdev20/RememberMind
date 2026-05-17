<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reporte Casa Amandita')</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px;
        }

        body {
            font-family: 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
            color: #2F3E5C;
            background-color: #ffffff;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Marca de Agua */
        #watermark {
            position: fixed;
            top: 35%;
            left: 10%;
            width: 80%;
            text-align: center;
            opacity: 0.05;
            z-index: -1000;
            font-size: 72px;
            font-weight: 900;
            color: #2F3E5C;
            transform: rotate(-25deg);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            pointer-events: none;
            user-select: none;
        }

        /* Encabezado */
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #E27D60;
            padding-bottom: 8px;
            display: block;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .header-logo-text {
            font-size: 15px;
            font-weight: 900;
            color: #2F3E5C;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        
        .header-logo-sub {
            font-size: 9px;
            font-weight: bold;
            color: #967B66;
            margin-top: 1px;
        }
        
        .header-meta {
            text-align: right;
            font-size: 8px;
            color: #7C7168;
            line-height: 1.3;
        }

        /* Pie de Página */
        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #E6DDD3;
            padding-top: 6px;
            font-size: 8px;
            color: #967B66;
            text-align: center;
        }

        /* Títulos de Sección */
        .section-title {
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: rgba(47, 62, 92, 0.7);
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(226, 125, 96, 0.25);
            padding-bottom: 4px;
            page-break-after: avoid;
        }

        /* Tarjeta Resumen / Detalles */
        .details-box {
            background-color: #FAF8F5;
            border: 1px solid #E6DDD3;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .details-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        
        .label-detail {
            font-weight: 900;
            color: #967B66;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.08em;
            width: 130px;
        }
        
        .value-detail {
            font-size: 10px;
            color: #2F3E5C;
        }

        /* Resumen Estadístico Grid */
        .summary-grid {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        
        .summary-card {
            background-color: #F8F3ED;
            border: 1px solid #E6DDD3;
            border-radius: 6px;
            padding: 8px;
            text-align: center;
        }
        
        .summary-number {
            font-size: 14px;
            font-weight: 900;
            color: #E27D60;
            margin-bottom: 1px;
        }
        
        .summary-label {
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #967B66;
        }

        /* Tablas */
        .table-institutional {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        
        .table-institutional th {
            background-color: #F8F3ED;
            color: #2F3E5C;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.06em;
            padding: 6px 8px;
            border-bottom: 2px solid #E6DDD3;
            text-align: left;
        }
        
        .table-institutional td {
            padding: 6px 8px;
            border-bottom: 1px solid #FAF8F5;
            font-size: 9px;
            color: #2F3E5C;
        }
        
        .table-institutional tr:nth-child(even) {
            background-color: #FAF8F5;
        }
        
        .badge-status {
            font-weight: 900;
            font-size: 7px;
            text-transform: uppercase;
            padding: 1px 4px;
            border-radius: 3px;
        }
        
        .badge-active {
            background-color: rgba(141, 162, 128, 0.15);
            color: #63775B;
        }
        
        .badge-inactive {
            background-color: rgba(226, 125, 96, 0.15);
            color: #E27D60;
        }

        /* Cajas de Alerta/Recomendación */
        .alert-box {
            background-color: #FAF8F5;
            border-left: 3px solid #E27D60;
            padding: 10px;
            border-radius: 0 4px 4px 0;
            font-size: 9px;
            margin-top: 10px;
            line-height: 1.4;
            page-break-inside: avoid;
        }

        /* Control de salto de página */
        .page-break {
            page-break-before: always;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Marca de Agua de Fondo -->
    <div id="watermark">Casa Amandita</div>

    <!-- Encabezado Fijo -->
    <header>
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-logo-text">Casa Amandita</div>
                    <div class="header-logo-sub">RememberMind • Gestión Residencial Integral</div>
                </td>
                <td class="header-meta">
                    <strong>Reporte: @yield('report_title', 'Reporte Técnico')</strong><br>
                    Fecha: {{ $fecha }}<br>
                    Operador: {{ $usuario }}
                </td>
            </tr>
        </table>
    </header>

    <!-- Pie de Página Fijo -->
    <footer>
        Casa Amandita • Av. Falsa 123 • Tel: +56 9 1234 5678 • Sistema RememberMind © {{ date('Y') }}
    </footer>

    <!-- Contenido Principal -->
    <main>
        @yield('content')
    </main>

</body>
</html>
