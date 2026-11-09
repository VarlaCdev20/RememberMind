@inject('presentarGraficas', 'App\Modulos\Reportes\Presentacion\Reportes\GraficasGerencia')
<section aria-label="Gráficas del reporte">
    <h2>Gráficas del reporte</h2>
    @foreach($presentarGraficas->series($reporte) as $grafica)
        @php($tipoGrafica = $presentarGraficas->tipo($grafica, $reporte['tipo_grafica'] ?? 'auto'))
        @if($tipoGrafica === 'barras')
            @include('reportes::paginas.reportes.partials.grafica-barras-pdf', [
                'titulo' => $grafica['titulo'], 'labels' => array_column($grafica['serie'], 'etiqueta'),
                'data' => array_column($grafica['serie'], 'valor'), 'colores' => [], 'escala' => 'valor', 'unidad' => $grafica['unidad'],
            ])
        @else
            <div data-report-chart="{{ $tipoGrafica }}" style="page-break-inside: avoid; margin: 12pt 0;">
                <h3 style="font-size: 10pt; margin: 0 0 4pt;">{{ $grafica['titulo'] }} · {{ ['dona'=>'Dona', 'torta'=>'Torta', 'radar'=>'Radar'][$tipoGrafica] }}</h3>
                <table style="border: 0; margin: 0;"><tr>
                    <td style="width: 42%; border: 0; padding: 0;"><img src="{{ $presentarGraficas->imagen($grafica, $tipoGrafica) }}" style="width: 220px; height: 176px;" alt="{{ $grafica['titulo'] }}" /></td>
                    <td style="border: 0; padding: 4pt 0 4pt 12pt; vertical-align: middle;">
                        @foreach($grafica['serie'] as $indicePunto => $punto)
                            <p style="font-size: 8.5pt; margin: 4pt 0;"><span style="color: {{ $presentarGraficas::COLORES[$indicePunto % 5] }};">●</span> {{ $indicePunto + 1 }}. {{ $punto['etiqueta'] }}: <strong>{{ $punto['valor'] ?? 'No disponible' }}</strong>@if($grafica['composicion'] && $grafica['total'] > 0) · {{ number_format($punto['valor'] * 100 / $grafica['total'], 1, ',', '.') }} %@endif</p>
                        @endforeach
                        @if($grafica['composicion'] && $grafica['total'] === 0)<p>Sin registros en estas categorías.</p>@endif
                        @if($tipoGrafica === 'radar')<p style="font-size: 8pt;">Escala de 0 a {{ $grafica['maximo'] }} {{ $grafica['unidad'] }}. Ejes numerados según la leyenda.</p>@endif
                    </td>
                </tr></table>
                <p style="font-size: 8pt;">{{ $grafica['nota'] }}</p>
            </div>
        @endif
    @endforeach
</section>
