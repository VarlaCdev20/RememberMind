<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>Reporte General de Turnos y Asignaciones</title>
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
 margin-top: 25px;
 margin-bottom: 10px;
 border-bottom: 1px solid rgba(226, 125, 96, 0.2);
 padding-bottom: 4px;
 }

 /* Resumen Ejecutivo Cards */
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
 .badge-closed {
 background-color: rgba(150, 123, 102, 0.15);
 color: #7A604D;
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
 
 .page-break {
 page-break-after: always;
 }
 </style>
</head>
<body>

 <!-- Marca de Agua -->
 <div id="watermark">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</div>

 <!-- Encabezado de Página -->
 <header>
 <table class="header-table">
 <tr>
 <td>
 <div class="header-title">RememberMind</div>
 <div class="header-subtitle">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Administración General</div>
 </td>
 <td class="header-meta">
 <strong>General de Turnos y Asignaciones</strong><br>
 Fecha de emisión: {{ $fecha }}<br>
 Responsable: {{ $usuario }}
 </td>
 </tr>
 </table>
 </header>

 <!-- Footer de Página -->
 <footer>
 Sistema RememberMind © {{ date('Y') }} CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS. Todos los derechos reservados.
 </footer>

 <!-- RESUMEN ESTADÍSTICO -->
 <div class="section-title">Resumen de Cobertura Institucional</div>
 <table class="summary-grid" cellpadding="5">
 <tr>
 <td width="16%">
 <div class="summary-card">
 <div class="summary-number">{{ $totalTurnos }}</div>
 <div class="summary-label">Turnos</div>
 </div>
 </td>
 <td width="16%">
 <div class="summary-card">
 <div class="summary-number" style="color: #63775B;">{{ $totalAsignados }}</div>
 <div class="summary-label">Asignados</div>
 </div>
 </td>
 <td width="16%">
 <div class="summary-card">
 <div class="summary-number" style="color: #8da280;">{{ $areasCubiertas }}</div>
 <div class="summary-label">Áreas Cubiertas</div>
 </div>
 </td>
 <td width="17%">
 <div class="summary-card">
 <div class="summary-number" style="color: #E27D60;">{{ $areasSinCobertura }}</div>
 <div class="summary-label">Sin Cobertura</div>
 </div>
 </td>
 <td width="17%">
 <div class="summary-card">
 <div class="summary-number">{{ $asignacionesActivas }}</div>
 <div class="summary-label">Asig. Activas</div>
 </div>
 </td>
 <td width="18%">
 <div class="summary-card">
 <div class="summary-number" style="color: #7c7168;">{{ $usuariosSinTurno }}</div>
 <div class="summary-label">Sin Turno</div>
 </div>
 </td>
 </tr>
 </table>

 <!-- ASIGNACIONES DE PERSONAL -->
 <div class="section-title">ASIGNACIONES DE PERSONAL VIGENTES</div>
 <table class="table-institutional">
 <thead>
 <tr>
 <th width="25%">Nombre / Colaborador</th>
 <th width="20%">Área Operativa</th>
 <th width="15%">Turno</th>
 <th width="20%">Das Semanales</th>
 <th width="12%">Periodo</th>
 <th width="8%" style="text-align: center;">Estado</th>
 </tr>
 </thead>
 <tbody>
 @forelse($asignaciones as $asig)
 <tr>
 <td style="font-weight: bold; color: #2F3E5C;">{{ $asig->usuario ? $asig->usuario->name : 'N/D' }}</td>
 <td>{{ $asig->area ? $asig->area->nombre : 'N/D' }}</td>
 <td>
 {{ $asig->turno ? $asig->turno->nombre : 'N/D' }}
 @if($asig->turno && $asig->turno->hora_inicio)
 <div style="font-size: 8px; color: #967B66;">
 {{ substr($asig->turno->hora_inicio, 0, 5) }} - {{ substr($asig->turno->hora_fin, 0, 5) }}
 </div>
 @endif
 </td>
 <td style="font-size: 9px;">
 {{ is_array($asig->dias_semana) ? implode(', ', $asig->dias_semana) : 'N/D' }}
 </td>
 <td>
 {{ $asig->fecha_inicio ? \Carbon\Carbon::parse($asig->fecha_inicio)->format('d/m/Y') : '--' }}
 <div style="font-size: 8px; color: #967B66;">
 al {{ $asig->fecha_fin ? \Carbon\Carbon::parse($asig->fecha_fin)->format('d/m/Y') : 'Presente' }}
 </div>
 </td>
 <td style="text-align: center;">
 <span class="badge-status {{ $asig->estado === 'ACTIVA' ? 'badge-active' : ($asig->estado === 'FINALIZADA' ? 'badge-closed' : 'badge-inactive') }}">
 {{ $asig->estado }}
 </span>
 </td>
 </tr>
 @empty
 <tr>
 <td colspan="6" style="text-align: center; color: #967B66; padding: 20px;">
 No existen asignaciones registradas en el sistema.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>

 <!-- OBSERVACIONES -->
 <div class="section-title">Diagnóstico Administrativo del Personal</div>
 <div class="alert-box">
 <strong>Áreas Críticas Sin Cobertura Operativa:</strong><br>
 @if(count($nombresAreasSinCobertura) > 0)
 Se han identificado las siguientes áreas sin personal operativo asignado: 
 <span style="color: #E27D60; font-weight: bold;">{{ implode(', ', $nombresAreasSinCobertura) }}</span>.
 Es imperativo planificar asignaciones de cobertura para estas áreas con el fin de garantizar el correcto funcionamiento del centro.
 @else
 Excelente: Todas las áreas operativas activas cuentan con al menos un colaborador asignado para cobertura.
 @endif
 <br><br>
 <strong>Colaboradores Activos Sin Turno Asignado:</strong><br>
 Actualmente hay <strong>{{ $usuariosSinTurno }}</strong> colaboradores en estado Activo que no cuentan con turnos vigentes. 
 Se recomienda revisar su situación administrativa para integrarlos a las operaciones regulares o de apoyo temporal.
 </div>

</body>
</html>
