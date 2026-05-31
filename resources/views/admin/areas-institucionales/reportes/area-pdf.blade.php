<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Área - {{ $area->nombre }}</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2F3E5C;
            background-color: #ffffff;
            font-size: 11px;
            line-height: 1.5;
        }
        
        /* Marca de Agua */
        #watermark {
            position: fixed;
            top: 35%;
            left: 10%;
            width: 80%;
            text-align: center;
            opacity: 0.07;
            z-index: -1000;
            font-size: 70px;
            font-weight: 900;
            color: #2F3E5C;
            transform: rotate(-35deg);
            text-transform: uppercase;
            letter-spacing: 0.15em;
        }
        
        /* Encabezado */
        header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 60px;
            border-bottom: 2px solid #E27D60;
            padding-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-title {
            font-size: 14px;
            font-weight: 900;
            color: #2F3E5C;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .header-subtitle {
            font-size: 9px;
            font-weight: bold;
            color: #967B66;
            margin-top: 2px;
        }
        .header-meta {
            text-align: right;
            font-size: 9px;
            color: #7C7168;
            line-height: 1.3;
        }

        /* Footer */
        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #E6DDD3;
            padding-top: 8px;
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
            color: rgba(47, 62, 92, 0.6);
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid rgba(226, 125, 96, 0.2);
            padding-bottom: 4px;
        }

        /* Ficha de Detalles */
        .details-box {
            background-color: #FAF8F5;
            border: 1px solid #E6DDD3;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .label-detail {
            font-weight: 900;
            color: #967B66;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.08em;
            width: 120px;
        }
        .value-detail {
            font-size: 11px;
            color: #2F3E5C;
        }

        /* Resumen Estadístico Cards */
        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #F8F3ED;
            border: 1px solid #E6DDD3;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .summary-number {
            font-size: 16px;
            font-weight: 900;
            color: #E27D60;
            margin-bottom: 2px;
        }
        .summary-label {
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #967B66;
        }

        /* Tablas */
        .table-institutional {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #ffffff;
        }
        .table-institutional th {
            background-color: #F8F3ED;
            color: #2F3E5C;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.08em;
            padding: 8px 10px;
            border-bottom: 2px solid #E6DDD3;
            text-align: left;
        }
        .table-institutional td {
            padding: 8px 10px;
            border-bottom: 1px solid #F4EFEA;
            font-size: 10px;
            color: #2F3E5C;
        }
        .table-institutional tr:nth-child(even) {
            background-color: #FAF8F5;
        }
        .badge-status {
            font-weight: 900;
            font-size: 8px;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .badge-active {
            background-color: rgba(141, 162, 128, 0.15);
            color: #63775B;
        }
        .badge-inactive {
            background-color: rgba(226, 125, 96, 0.15);
            color: #E27D60;
        }

        /* Recomendaciones/Observaciones */
        .alert-box {
            background-color: #FAF8F5;
            border-left: 3px solid #E27D60;
            padding: 12px;
            border-radius: 4px;
            font-size: 10px;
            margin-top: 15px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- Marca de Agua -->
    <div id="watermark">Casa Amandita</div>

    <!-- Encabezado de Página -->
    <header>
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">Reporte de Área Institucional</div>
                    <div class="header-subtitle">Casa Amandita • Gestión Organizacional</div>
                </td>
                <td class="header-meta">
                    <strong>{{ $area->nombre }}</strong><br>
                    Fecha: {{ $fecha }}<br>
                    Generado por: {{ $usuario }}
                </td>
            </tr>
        </table>
    </header>

    <!-- Footer de Página -->
    <footer>
        Sistema RememberMind © {{ date('Y') }} Casa Amandita. Todos los derechos reservados.
    </footer>

    <!-- INFORMACIÓN GENERAL -->
    <div class="section-title">INFORMACIÓN GENERAL</div>
    <div class="details-box">
        <table class="details-table">
            <tr>
                <td class="label-detail">Nombre del Área:</td>
                <td class="value-detail" style="font-weight: bold;">{{ $area->nombre }}</td>
            </tr>
            <tr>
                <td class="label-detail">Tipo de Área:</td>
                <td class="value-detail">{{ $area->tipo_area }}</td>
            </tr>
            <tr>
                <td class="label-detail">Estado:</td>
                <td class="value-detail" style="font-weight: bold; color: {{ $area->estado === 'ACTIVA' ? '#63775B' : '#E27D60' }};">{{ $area->estado }}</td>
            </tr>
            <tr>
                <td class="label-detail">Responsable:</td>
                <td class="value-detail" style="font-weight: bold;">
                    @if($area->responsable)
                        {{ $area->responsable->name }}
                    @else
                        <span style="color: #E27D60; font-style: italic;">Sin responsable asignado</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-detail">Descripción Funcional:</td>
                <td class="value-detail" style="line-height: 1.4;">{{ $area->descripcion }}</td>
            </tr>
            @if($area->observaciones)
                <tr>
                    <td class="label-detail">Observaciones Internas:</td>
                    <td class="value-detail" style="font-style: italic; color: #7C7168;">"{{ $area->observaciones }}"</td>
                </tr>
            @endif
            <tr>
                <td class="label-detail">Última Actualización:</td>
                <td class="value-detail">{{ $area->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <!-- RESUMEN ESTADÍSTICO -->
    <div class="section-title">RESUMEN ESTADÍSTICO</div>
    <table class="summary-grid" cellpadding="5">
        <tr>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number">{{ $totalUsuarios }}</div>
                    <div class="summary-label">Total Personal</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number" style="color: #63775B;">{{ $usuariosActivos }}</div>
                    <div class="summary-label">Personal Activo</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number" style="color: #E27D60;">{{ $usuariosInactivos }}</div>
                    <div class="summary-label">Personal Inactivo</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number" style="color: #2F3E5C;">{{ $porcentajeActivos }}%</div>
                    <div class="summary-label">% Activos</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- USUARIOS VINCULADOS -->
    <div class="section-title">PERSONAL VINCULADO AL ÁREA</div>
    @if(count($area->usuarios) > 0)
        <table class="table-institutional">
            <thead>
                <tr>
                    <th width="50%">Nombre Completo</th>
                    <th width="35%">Rol Asignado</th>
                    <th width="15%" style="text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($area->usuarios as $u)
                    <tr>
                        <td style="font-weight: bold; color: #2F3E5C;">{{ $u->name }}</td>
                        <td>{{ $u->getRoleNames()->first() ?? 'Sin Rol' }}</td>
                        <td style="text-align: center;">
                            <span class="badge-status {{ $u->estado == 1 ? 'badge-active' : 'badge-inactive' }}">
                                {{ $u->estado == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert-box" style="border-left-color: #967B66;">
            <strong>Esta área aún no tiene usuarios asignados.</strong><br>
            Para poder agregar personal y configurar roles en este área institucional, diríjase al módulo de administración de usuarios.
        </div>
    @endif

    <!-- OBSERVACIONES ADICIONALES -->
    <div class="section-title">OBSERVACIONES</div>
    <div class="alert-box">
        @if(!$area->responsable)
            <strong>Alerta de Liderazgo:</strong> Esta área no cuenta con responsable asignado. Se sugiere asignar un responsable a la brevedad para garantizar la toma de decisiones y el reporte organizacional.<br><br>
        @endif
        
        <strong>Lineamiento Organizacional:</strong><br>
        El personal registrado en este reporte se encuentra plenamente adscrito a las funciones operativas descritas. Cualquier cambio de área o reasignación de rol de un miembro debe ser reportada y procesada a través del departamento de administración general del Sistema RememberMind de Casa Amandita.
    </div>

</body>
</html>
