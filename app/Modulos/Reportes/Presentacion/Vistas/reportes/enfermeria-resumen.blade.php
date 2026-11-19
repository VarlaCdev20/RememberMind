<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow"><title>Resumen de Enfermería</title>
<style>
@page { margin: 24px; } body { font-family: DejaVu Sans,sans-serif; font-size: 10px; color: #4f4742; margin: 16px; background: #fff; } h1 { font-size: 20px; } h2 { font-size: 14px; margin-top: 20px; } table { border-collapse: collapse; width: 100%; margin-top: 12px; } th,td { border-bottom: 1px solid #d7cec7; padding: 8px 5px; vertical-align: top; text-align: left; overflow-wrap: anywhere; } th { background: #ece9e6; } thead { display: table-header-group; } tr { page-break-inside: avoid; } .sr-only { display:none; } .nota { color:#665f57; line-height:1.6; } @media screen { body { max-width: 1200px; margin: 24px auto; padding: 16px; font-size: 13px; } .scroll { overflow-x:auto; } }
.rm-data-table { table-layout:fixed; }.rm-data-table :is(th,td) { word-wrap:break-word; }.rm-data-table th:nth-child(1){width:13%}.rm-data-table th:nth-child(2){width:14%}.rm-data-table th:nth-child(3){width:14%}.rm-data-table th:nth-child(4){width:12%}.rm-data-table th:nth-child(5){width:33%}.rm-data-table th:nth-child(6){width:14%}
</style>
@unless($pdf)
@vite('resources/frontend/scripts/modules/nursing-report-print.js')
<style>.report-tools{display:flex;gap:12px;flex-wrap:wrap;padding:14px;background:#f4f1ec;border:1px solid #d7cec7;border-radius:10px}.report-tools a,.report-tools button{padding:12px;color:#315744;border:1px solid #b6b3a9;border-radius:8px;font:600 14px sans-serif;cursor:pointer;background:white;text-decoration:none}.report-tools a:focus-visible,.report-tools button:focus-visible{outline:3px solid #315744;outline-offset:3px}@media print{.report-tools{display:none}body{margin:0}}</style>
@endunless
</head><body @unless($pdf) data-print-requested="{{ request()->boolean('imprimir') ? 'true' : 'false' }}" @endunless>
@unless($pdf)
<nav class="report-tools" aria-label="Acciones del reporte">@can('enfermeria.ver_ficha_paciente')<button type="button" data-report-print>Imprimir</button><a href="{{ route('admin.enfermeria.reportes.pdf', $reporte['filtros']) }}">Exportar PDF</a><a href="{{ route('admin.enfermeria.reportes.csv', $reporte['filtros']) }}">Exportar CSV</a>@endcan<a href="{{ route('admin.enfermeria.reportes', $reporte['filtros']) }}">Modificar filtros</a></nav>
@endunless
<h1>Resumen de Enfermería</h1>
<p><strong>Residente o alcance:</strong> {{ $reporte['alcance'] }}</p>
<p><strong>Período:</strong> {{ \Carbon\Carbon::parse($reporte['filtros']['desde'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($reporte['filtros']['hasta'])->format('d/m/Y') }} · {{ \App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria::TIPOS[$reporte['filtros']['tipo']] }}</p>
<p><strong>Generado:</strong> {{ $reporte['generado']->format('d/m/Y H:i') }} ({{ config('app.timezone') }}) · <strong>Solicitante:</strong> {{ $reporte['autor'] }}</p>
<p class="nota">Consulta de registros disponibles al generar el documento. No es una evaluación clínica ni una instantánea histórica conservada. Los registros anulados se excluyen. Cuidados y medicación se ubican por su fecha efectiva; cuando falta se utiliza la programada y se conserva su estado.</p>
<p class="nota">Sueño conserva su fecha sin inventar una hora. Dispositivos se ordenan por retiro o, cuando no consta, por colocación. Los dispositivos sin ninguna de esas fechas no se asignan a un período. Heridas y curaciones se cuentan por separado.</p>
<h2>{{ $reporte['filas']->count() }} registros en el período</h2>
<table><thead><tr><th>Sección</th><th>Cantidad de registros</th></tr></thead><tbody>@foreach($reporte['totales'] as $total)<tr><td>{{ $total['nombre'] }}</td><td>{{ $total['cantidad'] }}</td></tr>@endforeach</tbody></table>
<h2>Detalle de registros</h2>
@forelse($reporte['filas']->groupBy('categoria') as $filasSeccion)
<h2>{{ $filasSeccion->first()['tipo'] }} · {{ $filasSeccion->count() }} {{ $filasSeccion->count() === 1 ? 'registro' : 'registros' }}</h2>
<div class="scroll">@include('reportes::reportes.enfermeria-registros', ['filas' => $filasSeccion])</div>
@empty<p>No hay registros para los filtros aplicados.</p>@endforelse
<p class="nota">Los incidentes provienen de su registro original; las alertas son seguimientos separados y no se suman como nuevos incidentes. Ausencia de registros no demuestra ausencia de problemas.</p>
</body></html>
