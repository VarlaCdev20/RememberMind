<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>Reporte de Asignaciones por Área</title>
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
 </style>
</head>
<body>

 <!-- Marca de Agua -->
 <div id="watermark">{{ $area->nombre }}</div>

 <!-- Encabezado de Página -->
 <header>
 <table class="header-table">
 <tr>
 <td>
 <div class="header-title">RememberMind</div>
 <div class="header-subtitle">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Cobertura Sectorial</div>
 </td>
 <td class="header-meta">
 <strong>Asignaciones por Área Operativa</strong><br>
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

 <!-- FICHA DEL ÁREA -->
 <div class="section-title">Detalles del Área Institucional</div>
 <table class="summary-grid" cellpadding="5">
 <tr>
 <td width="33%">
 <div class="summary-card">
 <div class="summary-number" style="font-size: 14px; color: #2F3E5C;">{{ $area->nombre }}</div>
 <div class="summary-label">Nombre del Área</div>
 </div>
 </td>
 <td width="33%">
 <div class="summary-card">
 <div class="summary-number" style="font-size: 14px; color: #63775B;">
 {{ $area->responsable ? $area->responsable->name : 'Sin Responsable' }}
 </div>
 <div class="summary-label">Responsable Designado</div>
 </div>
 </td>
 <td width="34%">
 <div class="summary-card">
 <div class="summary-number">{{ count($asignaciones) }}</div>
 <div class="summary-label">Asignaciones en este Reporte</div>
 </div>
 </td>
 </tr>
 </table>

 <!-- LISTADO DE ASIGNADOS -->
 <div class="section-title">Colaboradores Asignados al Área</div>
 <table class="table-institutional">
 <thead>
 <tr>
 <th width="30%">Colaborador</th>
 <th width="20%">Turno Asignado</th>
 <th width="25%">Días de Cobertura</th>
 <th width="15%">Periodo</th>
 <th width="10%" style="text-align: center;">Estado</th>
 </tr>
 </thead>
 <tbody>
 @forelse($asignaciones as $asig)
 <tr>
 <td style="font-weight: bold; color: #2F3E5C;">{{ $asig->usuario ? $asig->usuario->name : 'N/D' }}</td>
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
 <td colspan="5" style="text-align: center; color: #967B66; padding: 20px;">
 No existen colaboradores asignados operativamente a esta área en este momento.
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>

 <!-- OBSERVACIONES -->
 <div class="section-title">Observaciones Sectoriales</div>
 <div class="alert-box">
 <strong>Análisis de Cobertura en el Área de {{ $area->nombre }}:</strong><br>
 @if(count($asignaciones) == 0)
 <span style="color: #E27D60; font-weight: bold;">ADVERTENCIA CRÍTICA:</span> Esta área no cuenta con ninguna asignación de turno activa. Se encuentra sin cobertura de personal, lo cual pone en riesgo las actividades diarias e institucionales vinculadas.
 @else
 El área cuenta con un total de <strong>{{ count($asignaciones->where('estado', 'ACTIVA')) }}</strong> asignaciones operativas activas. 
 Se sugiere verificar que los días y horarios cubran adecuadamente los flujos de trabajo específicos de esta sección.
 @endif
 <br><br>
 <strong>Rol del Responsable del Área:</strong><br>
 El responsable del área debe coordinar las actividades de este personal y supervisar que se cumplan las normativas de asistencia e interacción con los adultos mayores residentes.
 </div>

</body>
</html>
