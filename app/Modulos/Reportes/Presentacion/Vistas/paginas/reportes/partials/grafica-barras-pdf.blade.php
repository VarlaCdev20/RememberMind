{{--
 Parcial reutilizable para gráficas de barras CSS — compatible con DomPDF y HTML.

 Parámetros esperados:
 $titulo : string — título de la gráfica
 $labels : array — etiquetas de cada barra
 $data : array — valores numéricos
 $colores : array — colores hex para cada barra (mismo orden que $labels)
--}}
@php
 $total = array_sum($data ?? []);
@endphp

<div style="margin-bottom: 10px;">
 @if(!empty($titulo))
 <div style="font-size: 8.5pt; font-weight: bold; color: #2F3E5C; margin-bottom: 6px;">
 {{ $titulo }}
 </div>
 @endif

 @if($total <= 0 || empty($labels))
 <div style="font-size: 8.5pt; color: #888888; font-style: italic; padding: 6px 0;">
 Sin datos disponibles.
 </div>
 @else
 @foreach($labels as $i => $etiqueta)
 @php
 $valor = (int) ($data[$i] ?? 0);
 $pct = $total > 0 ? round(($valor / $total) * 100, 1) : 0;
 $color = $colores[$i] ?? '#2F3E5C';
 $barraW = max($pct, 0.5);
 @endphp
 <div style="margin-bottom: 5px;">
 <div style="display: table; width: 100%;">
 <div style="display: table-cell; width: 120px; font-size: 8pt; color: #2F3E5C; vertical-align: middle; padding-right: 6px;">
 {{ $etiqueta }}
 </div>
 <div style="display: table-cell; vertical-align: middle;">
 <div style="background: #E6DDD3; width: 100%; height: 14px; border-radius: 2px; position: relative;">
 <div style="background: {{ $color }}; width: {{ $barraW }}%; height: 14px; border-radius: 2px; display: block;"></div>
 </div>
 </div>
 <div style="display: table-cell; width: 60px; font-size: 8pt; color: #2F3E5C; text-align: right; vertical-align: middle; padding-left: 6px;">
 {{ $valor }} ({{ $pct }}%)
 </div>
 </div>
 </div>
 @endforeach
 @endif
</div>
