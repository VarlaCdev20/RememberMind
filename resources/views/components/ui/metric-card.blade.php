{{--
 Componente: ui/metric-card
 Uso: <x-ui.metric-card etiqueta="Total Adultos" :valor="$total" icono="ph-users" />
 <x-ui.metric-card etiqueta="Alertas" :valor="$alertas" icono="ph-warning" color-valor="text-estado-peligro" />
--}}
@props([
 'etiqueta' => '',
 'valor' => '—',
 'icono' => null,
 'colorValor' => 'text-boton-acento',
 'colorFondo' => 'bg-fondo-card',
 'colorBorde' => 'border-borde',
])

<div class="rounded-[18px] border {{ $colorBorde }} {{ $colorFondo }} p-4 shadow-card">
 @if($icono)
 <div class="flex items-center gap-2 mb-2">
 <i class="ph-bold {{ $icono }} text-base text-meta"></i>
 <p class="rm-metric-label">{{ $etiqueta }}</p>
 </div>
 @else
 <p class="rm-metric-label">{{ $etiqueta }}</p>
 @endif
 <p class="mt-1 text-xl font-extrabold {{ $colorValor }}">{{ $valor }}</p>

 @if($slot->isNotEmpty())
 <div class="mt-2 text-xs font-bold text-meta">
 {{ $slot }}
 </div>
 @endif
</div>
