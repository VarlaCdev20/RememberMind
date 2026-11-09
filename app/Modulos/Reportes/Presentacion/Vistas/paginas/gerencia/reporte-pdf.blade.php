<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>{{ $reporte['titulo'] ?? 'Resumen gerencial' }}</title><style>
body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #403b37; } p { margin: 6pt 0; } h1 { font-size: 20pt; margin: 10pt 0; } h2 { font-size: 14pt; margin: 14pt 0 6pt; page-break-after: avoid; } table { width: 100%; border-collapse: collapse; margin-top: 12pt; } th, td { border: 1px solid #ccc5bf; padding: 6pt; text-align: left; } thead { display: table-header-group; } tr, li { page-break-inside: avoid; } small { display: block; } .sr-only { display: none; } footer { position: fixed; bottom: 0; font-size: 8pt; }
</style></head><body>
<p>RememberMind · Información institucional agregada</p><h1>{{ $reporte['titulo'] ?? 'Resumen gerencial' }}</h1>
<p>{{ $reporte['referencia'] }} · {{ $reporte['ambito'] }}</p>
<p>Datos consultados: {{ \Carbon\Carbon::parse($reporte['consultado_en'])->format('d/m/Y H:i') }}<br>Generado: {{ now()->format('d/m/Y H:i') }}</p>
@include('reportes::paginas.gerencia.partials.graficas-reporte')
@include('reportes::paginas.gerencia.partials.tabla-reporte')
<h2>Definiciones y límites</h2><ul>@foreach($reporte['limites'] as $limite)<li>{{ $limite }}</li>@endforeach</ul>
<footer>RememberMind · Reporte agregado. No es una evaluación clínica.</footer>
</body></html>
