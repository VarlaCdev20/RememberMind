{{--
 Parcial reutilizable para gráficas de barras CSS — compatible con DomPDF y HTML.

 Parámetros esperados:
 $titulo : string — título de la gráfica
 $labels : array — etiquetas de cada barra
 $data : array — valores numéricos
 $colores : array — colores hex para cada barra (mismo orden que $labels)
 $escala : 'valor' para comparar cantidades con el máximo de la misma unidad
--}}
@php
 $total = array_sum($data ?? []);
 $valoresAbsolutos = ($escala ?? null) === 'valor';
 $numericos = array_filter($data ?? [], fn ($valor) => (is_int($valor) || is_float($valor)) && is_finite((float) $valor) && $valor >= 0);
 $maximo = max([0, ...$numericos]);
 $tinta = $valoresAbsolutos ? '#403b37' : '#2F3E5C';
@endphp

<div @if($valoresAbsolutos) data-report-chart="valores" @endif style="margin-bottom: 10px;{{ $valoresAbsolutos ? ' page-break-inside: avoid;' : '' }}">
 @if(!empty($titulo))
 <div style="font-size: 8.5pt; font-weight: bold; color: {{ $tinta }}; margin-bottom: 6px;">
 {{ $titulo }}
 </div>
 @endif

 @if(empty($labels) || ($valoresAbsolutos ? count($numericos) === 0 : $total <= 0))
 <div style="font-size: 8.5pt; color: #888888; font-style: italic; padding: 6px 0;">
 Sin datos disponibles.
 </div>
 @else
 @foreach($labels as $i => $etiqueta)
 @php
 $disponible = array_key_exists($i, $numericos);
 $valor = $valoresAbsolutos && $disponible ? $data[$i] : (int) ($data[$i] ?? 0);
 $pct = $valoresAbsolutos ? ($maximo > 0 ? round(($valor / $maximo) * 100, 1) : 0)
     : ($total > 0 ? round(($valor / $total) * 100, 1) : 0);
 $color = $colores[$i] ?? ($valoresAbsolutos ? '#78826E' : '#2F3E5C');
 $barraW = $valoresAbsolutos ? ($disponible ? $pct : 0) : max($pct, 0.5);
 @endphp
 <div style="margin-bottom: 5px;">
 <div style="display: table; width: 100%;">
 <div style="display: table-cell; width: {{ $valoresAbsolutos ? 190 : 120 }}px; font-size: {{ $valoresAbsolutos ? 9 : 8 }}pt; color: {{ $tinta }}; vertical-align: middle; padding-right: 6px;">
 {{ $etiqueta }}
 </div>
 <div style="display: table-cell; vertical-align: middle;">
 <div style="background: {{ $valoresAbsolutos ? '#E8E1DA' : '#E6DDD3' }}; width: 100%; height: 14px; border-radius: 2px; position: relative;">
 <div style="background: {{ $color }}; width: {{ $barraW }}%; height: 14px; border-radius: 2px; display: block;"></div>
 </div>
 </div>
 <div style="display: table-cell; width: 60px; font-size: 8pt; color: {{ $tinta }}; text-align: right; vertical-align: middle; padding-left: 6px;">
 @if($valoresAbsolutos){{ $disponible ? $valor : 'No disponible' }}@else{{ $valor }} ({{ $pct }}%)@endif
 </div>
 </div>
 </div>
 @endforeach
 @if($valoresAbsolutos && $maximo > 0)<div style="font-size: 8pt; color: {{ $tinta }}; margin-top: 6px;">Escala de 0 a {{ $maximo }} {{ $unidad ?? '' }}</div>@endif
 @endif
</div>
