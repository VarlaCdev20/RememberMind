@props(['kpis' => []])

@php
$estilosColor = [
 'azul-profundo' => ['bg' => 'bg-fondo-hover', 'text' => 'text-titulo', 'val' => 'text-titulo'],
 'naranja' => ['bg' => 'bg-estado-advertencia-bg', 'text' => 'text-estado-advertencia-texto', 'val' => 'text-estado-advertencia-texto'],
 'verde-salud' => ['bg' => 'bg-estado-exito-bg', 'text' => 'text-estado-exito-texto', 'val' => 'text-estado-exito-texto'],
 'morado-cog' => ['bg' => 'bg-modulo-cognitivoFondo', 'text' => 'text-modulo-cognitivoTexto', 'val' => 'text-modulo-cognitivoTexto'],
 'terracota' => ['bg' => 'bg-estado-advertencia-bg', 'text' => 'text-boton-acento', 'val' => 'text-boton-acento'],
 'verde-olivo' => ['bg' => 'bg-modulo-voluntariosFondo', 'text' => 'text-modulo-voluntariosTexto', 'val' => 'text-modulo-voluntariosTexto'],
];

$badgeNivel = [
 'normal' => 'rm-badge-neutral',
 'ok' => 'badge-green-ash',
 'alerta' => 'badge-coral',
 'advertencia' => 'badge-coral',
];
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
 @foreach($kpis as $kpi)
 @php
 $estilo = $estilosColor[$kpi['color']] ?? $estilosColor['azul-profundo'];
 $badgeClass = $badgeNivel[$kpi['nivel']] ?? $badgeNivel['normal'];
 @endphp

 <div class="card-interactiva borde-verde-suave rounded-[1.8rem] border p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
 <div class="mb-3 flex items-center justify-between">
 <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $estilo['bg'] }} {{ $estilo['text'] }}">
 <i class="ph-fill {{ $kpi['icono'] }} text-lg"></i>
 </div>
 <span class="rounded-full {{ $badgeClass }} px-2.5 py-1 text-xs font-bold uppercase tracking-wide">
 {{ $kpi['badge'] }}
 </span>
 </div>

 <p class="text-2xl font-black {{ $estilo['val'] }}">{{ $kpi['valor'] }}</p>
 <p class="mt-0.5 text-[11px] font-bold text-titulo">{{ $kpi['titulo'] }}</p>
 <p class="text-[10px] font-bold text-meta">{{ $kpi['subtitulo'] }}</p>
 </div>
 @endforeach
</div>
