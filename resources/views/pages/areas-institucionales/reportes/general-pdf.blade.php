<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>Reporte General de Áreas Institucionales</title>
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
 <div class="header-subtitle">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS • Gestión Organizacional</div>
 </td>
 <td class="header-meta">
 <strong>Reporte General de Áreas</strong><br>
 Fecha: {{ $fecha }}<br>
 Generado por: {{ $usuario }}
 </td>
 </tr>
 </table>
 </header>

 <!-- Footer de Página -->
 <footer>
 Sistema RememberMind © {{ date('Y') }} CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS. Todos los derechos reservados.
 </footer>

 <!-- RESUMEN ESTADÍSTICO -->
 <div class="section-title">RESUMEN ESTADÍSTICO</div>
 <table class="summary-grid" cellpadding="5">
 <tr>
 <td width="16%">
 <div class="summary-card">
 <div class="summary-number">{{ $totalAreas }}</div>
 <div class="summary-label">Total Áreas</div>
 </div>
 </td>
 <td width="16%">
 <div class="summary-card">
 <div class="summary-number" style="color: #63775B;">{{ $areasActivas }}</div>
 <div class="summary-label">Activas</div>
 </div>
 </td>
 <td width="16%">
 <div class="summary-card">
 <div class="summary-number" style="color: #E27D60;">{{ $areasInactivas }}</div>
 <div class="summary-label">Inactivas</div>
 </div>
 </td>
 <td width="17%">
 <div class="summary-card">
 <div class="summary-number">{{ $usuariosVinculados }}</div>
 <div class="summary-label">Personal Vinculado</div>
 </div>
 </td>
 <td width="17%">
 <div class="summary-card">
 <div class="summary-number" style="color: #c27d38;">{{ $areasSinResponsable }}</div>
 <div class="summary-label">Sin Responsable</div>
 </div>
 </td>
 <td width="18%">
 <div class="summary-card">
 <div class="summary-number" style="color: #7c7168;">{{ $areasSinUsuarios }}</div>
 <div class="summary-label">Sin Personal</div>
 </div>
 </td>
 </tr>
 </table>

 <!-- INFORMACIÓN GENERAL -->
 <div class="section-title">INFORMACIÓN GENERAL DE ÁREAS</div>
 <table class="table-institutional">
 <thead>
 <tr>
 <th width="45%">Área Institucional</th>
 <th width="15%">Tipo</th>
 <th width="20%">Responsable del Área</th>
 <th width="10%" style="text-align: center;">Personal</th>
 <th width="10%" style="text-align: center;">Estado</th>
 </tr>
 </thead>
 <tbody>
 @foreach($areas as $area)
 <tr>
 <td style="font-weight: bold; color: #2F3E5C;">{{ $area->nombre }}</td>
 <td>{{ $area->tipo_area }}</td>
 <td>
 @if($area->responsable)
 {{ $area->responsable->name }}
 @else
 <span style="color: #E27D60; font-weight: bold; font-style: italic;">Sin responsable</span>
 @endif
 </td>
 <td style="text-align: center; font-weight: bold; color: #E27D60;">{{ $area->usuarios_count }}</td>
 <td style="text-align: center;">
 <span class="badge-status {{ $area->estado === 'ACTIVA' ? 'badge-active' : 'badge-inactive' }}">
 {{ $area->estado }}
 </span>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>

 <!-- OBSERVACIONES -->
 <div class="section-title">OBSERVACIONES Y ALERTAS DEL ORGANIGRAMA</div>
 <div class="alert-box">
 <strong>Áreas Sin Responsable Registrado:</strong><br>
 @php $sinRespList = $areas->whereNull('responsable_id'); @endphp
 @if($sinRespList->count() > 0)
 Actualmente se detectan las siguientes áreas sin un responsable asignado: 
 {{ implode(', ', $sinRespList->pluck('nombre')->toArray()) }}. Se sugiere designar responsables válidos pertenecientes a las mismas para mantener la trazabilidad administrativa.
 @else
 Excelente: Todas las áreas institucionales activas cuentan con un responsable asignado.
 @endif
 <br><br>
 <strong>Áreas Sin Personal Vinculado:</strong><br>
 @php $sinPersList = $areas->filter(fn($a) => $a->usuarios_count == 0); @endphp
 @if($sinPersList->count() > 0)
 Las siguientes áreas operan con dotación cero de personal:
 {{ implode(', ', $sinPersList->pluck('nombre')->toArray()) }}.
 @else
 Todas las áreas cuentan con personal activo vinculado.
 @endif
 </div>

</body>
</html>
