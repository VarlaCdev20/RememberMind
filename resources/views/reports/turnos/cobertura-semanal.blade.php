<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cobertura Semanal</title>
    <style>
        @page {
            margin: 100px 40px 80px 40px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #2F3E5C;
            background-color: #ffffff;
            font-size: 10px;
            line-height: 1.4;
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
            font-size: 60px;
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
            font-size: 16px;
            font-weight: 900;
            color: #2F3E5C;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .header-subtitle {
            font-size: 10px;
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
            margin-bottom: 8px;
            border-bottom: 1px solid rgba(226, 125, 96, 0.2);
            padding-bottom: 4px;
        }

        /* Resumen Ejecutivo Cards */
        .summary-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-card {
            background-color: #F8F3ED;
            border: 1px solid #E6DDD3;
            border-radius: 8px;
            padding: 8px;
            text-align: center;
        }
        .summary-number {
            font-size: 14px;
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
        .table-weekly {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #ffffff;
        }
        .table-weekly th {
            background-color: #2F3E5C;
            color: #ffffff;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.08em;
            padding: 6px 8px;
            border: 1px solid #E6DDD3;
            text-align: center;
        }
        .table-weekly td {
            padding: 8px;
            border: 1px solid #E6DDD3;
            font-size: 9px;
            color: #2F3E5C;
            vertical-align: top;
        }
        
        .day-active {
            background-color: rgba(141, 162, 128, 0.15) !important;
            color: #63775B !important;
            font-weight: bold;
            text-align: center;
        }
        .day-inactive {
            background-color: #FAF8F5;
            color: #CBBBAA;
            text-align: center;
            font-style: italic;
        }

        .badge-status {
            font-weight: 900;
            font-size: 8px;
            text-transform: uppercase;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .badge-active {
            background-color: rgba(141, 162, 128, 0.15);
            color: #63775B;
        }
        .badge-temp {
            background-color: rgba(226, 125, 96, 0.15);
            color: #E27D60;
        }

        /* Recomendaciones/Observaciones */
        .alert-box {
            background-color: #FAF8F5;
            border-left: 3px solid #E27D60;
            padding: 10px;
            border-radius: 4px;
            font-size: 9px;
            margin-top: 15px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

    <!-- Marca de Agua -->
    <div id="watermark">RememberMind</div>

    <!-- Encabezado de Página -->
    <header>
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">RememberMind</div>
                    <div class="header-subtitle">Casa Amandita • Planificación Semanal</div>
                </td>
                <td class="header-meta">
                    <strong>Reporte de Cobertura Semanal</strong><br>
                    Fecha de emisión: {{ $fecha }}<br>
                    Responsable: {{ $usuario }}
                </td>
            </tr>
        </table>
    </header>

    <!-- Footer de Página -->
    <footer>
        Sistema RememberMind © {{ date('Y') }} Casa Amandita. Todos los derechos reservados.
    </footer>

    <!-- RESUMEN ESTADÍSTICO -->
    <div class="section-title">Resumen de Carga de Trabajo Semanal</div>
    <table class="summary-grid" cellpadding="5">
        <tr>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number">{{ $totalAsignaciones }}</div>
                    <div class="summary-label">Asignaciones Activas</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number" style="color: #63775B;">{{ $totalColaboradores }}</div>
                    <div class="summary-label">Colaboradores en Turno</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number" style="color: #8da280;">{{ $totalAreas }}</div>
                    <div class="summary-label">Áreas Operadas</div>
                </div>
            </td>
            <td width="25%">
                <div class="summary-card">
                    <div class="summary-number" style="color: #E27D60;">{{ $areasSinCobertura }}</div>
                    <div class="summary-label">Áreas Sin Cobertura</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- GRILLA SEMANAL -->
    <div class="section-title">PLANIFICACIÓN SEMANAL DEL PERSONAL</div>
    <table class="table-weekly">
        <thead>
            <tr>
                <th width="20%">Colaborador / Área</th>
                <th width="10%">Turno / Horario</th>
                <th width="10%">Lunes</th>
                <th width="10%">Martes</th>
                <th width="10%">Miércoles</th>
                <th width="10%">Jueves</th>
                <th width="10%">Viernes</th>
                <th width="10%">Sábado</th>
                <th width="10%">Domingo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($asignaciones as $asig)
                @php
                    $dias = is_array($asig->dias_semana) ? $asig->dias_semana : [];
                @endphp
                <tr>
                    <td style="font-weight: bold; color: #2F3E5C;">
                        {{ $asig->usuario ? $asig->usuario->name : 'N/D' }}
                        <div style="font-size: 7.5px; color: #967B66; font-weight: normal; margin-top: 2px;">
                            Área: {{ $asig->area ? $asig->area->nombre : 'N/D' }}
                        </div>
                    </td>
                    <td>
                        {{ $asig->turno ? $asig->turno->nombre : 'Flexible' }}
                        @if($asig->turno && $asig->turno->hora_inicio)
                            <div style="font-size: 7.5px; color: #7C7168;">
                                {{ substr($asig->turno->hora_inicio, 0, 5) }}-{{ substr($asig->turno->hora_fin, 0, 5) }}
                            </div>
                        @endif
                    </td>
                    <td class="{{ in_array('LUNES', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('LUNES', $dias) ? '✓' : '—' }}
                    </td>
                    <td class="{{ in_array('MARTES', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('MARTES', $dias) ? '✓' : '—' }}
                    </td>
                    <td class="{{ in_array('MIERCOLES', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('MIERCOLES', $dias) ? '✓' : '—' }}
                    </td>
                    <td class="{{ in_array('JUEVES', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('JUEVES', $dias) ? '✓' : '—' }}
                    </td>
                    <td class="{{ in_array('VIERNES', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('VIERNES', $dias) ? '✓' : '—' }}
                    </td>
                    <td class="{{ in_array('SABADO', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('SABADO', $dias) ? '✓' : '—' }}
                    </td>
                    <td class="{{ in_array('DOMINGO', $dias) ? 'day-active' : 'day-inactive' }}">
                        {{ in_array('DOMINGO', $dias) ? '✓' : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #967B66; padding: 20px;">
                        No existen asignaciones de turno programadas para esta semana.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- CONEXIÓN FUTURA -->
    <div class="section-title">Trazabilidad y Cobertura Futura</div>
    <div class="alert-box">
        <strong>Conexión con el Expediente Médico de Adulto Mayor:</strong><br>
        Esta planificación semanal sirve como base de disponibilidad para las asignaciones profesionales personalizadas que se realicen en el módulo de Salud y Seguimiento. Solo el personal de salud con turnos activos en esta grilla estará disponible para ser asignado como profesional tratante de cabecera de los adultos mayores de Casa Amandita, garantizando que haya cobertura real y sin solapamiento de horarios.
    </div>

</body>
</html>
