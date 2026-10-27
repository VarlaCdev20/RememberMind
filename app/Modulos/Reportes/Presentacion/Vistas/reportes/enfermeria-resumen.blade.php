<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Resumen de Enfermería</title>
<style>
@page { margin: 24px; } body { font-family: DejaVu Sans,sans-serif; font-size: 10px; color: #4f4742; margin: 16px; background: #fff; } h1 { font-size: 20px; } h2 { font-size: 14px; margin-top: 20px; } table { border-collapse: collapse; width: 100%; margin-top: 12px; } th,td { border-bottom: 1px solid #d7cec7; padding: 8px 5px; vertical-align: top; text-align: left; overflow-wrap: anywhere; } th { background: #ece9e6; } thead { display: table-header-group; } tr { page-break-inside: avoid; } .sr-only { display:none; } .nota { color:#665f57; line-height:1.6; } @media screen { body { max-width: 1200px; margin: 24px auto; padding: 16px; font-size: 13px; } .scroll { overflow-x:auto; } }
</style></head><body>
<h1>Resumen de Enfermería</h1>
<p><strong>Residente o alcance:</strong> {{ $reporte['alcance'] }}</p>
<p><strong>Período:</strong> {{ \Carbon\Carbon::parse($reporte['filtros']['desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($reporte['filtros']['hasta'])->format('d/m/Y') }} · {{ \App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria::TIPOS[$reporte['filtros']['tipo']] }}</p>
<p><strong>Generado:</strong> {{ $reporte['generado']->format('d/m/Y H:i') }} ({{ config('app.timezone') }}) · <strong>Solicitante:</strong> {{ $reporte['autor'] }}</p>
<p class="nota">Consulta de registros disponibles al generar el documento. No es una evaluación clínica ni una instantánea histórica conservada. Los registros anulados se excluyen. Cuidados y medicación se ubican por su fecha efectiva; cuando falta se utiliza la programada y se conserva su estado.</p>
<h2>{{ $reporte['filas']->count() }} registros en el período</h2>
<table><thead><tr>@foreach($reporte['totales'] as $total)<th>{{ $total['nombre'] }}</th>@endforeach</tr></thead><tbody><tr>@foreach($reporte['totales'] as $total)<td>{{ $total['cantidad'] }}</td>@endforeach</tr></tbody></table>
<h2>Detalle de registros</h2>
<div class="scroll">@include('reportes::reportes.enfermeria-registros', ['filas' => $reporte['filas']])</div>
<p class="nota">Los incidentes provienen de su registro original; las alertas son seguimientos separados y no se suman como nuevos incidentes. Ausencia de registros no demuestra ausencia de problemas.</p>
</body></html>
