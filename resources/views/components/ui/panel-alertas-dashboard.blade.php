@props(['alertas' => []])

@php
$estilosNivel = [
 'URGENTE' => ['badge' => 'rm-badge-danger', 'icono_color' => 'text-estado-peligro-texto'],
 'INFORMATIVA' => ['badge' => 'rm-badge-info', 'icono_color' => 'text-estado-info-texto'],
 'OK' => ['badge' => 'rm-badge-success', 'icono_color' => 'text-estado-exito-texto'],
];
@endphp

<div class="card-interactiva borde-verde-suave rounded-[2rem] border p-5">
 <h2 class="text-lg font-extrabold text-titulo">Alertas administrativas</h2>
 <p class="mb-4 text-xs font-bold text-meta">Pendientes de revisión institucional</p>

 <ul class="space-y-2">
 @forelse($alertas as $alerta)
 @php
 $nivel = $alerta['nivel'] ?? 'INFORMATIVA';
 $estilo = $estilosNivel[$nivel] ?? $estilosNivel['INFORMATIVA'];
 @endphp

 <li class="card-interactiva rounded-[1.4rem] border border-transparent p-3 transition hover:-translate-y-0.5 hover:bg-fondo-hover">
 <div class="flex items-start gap-2">
 <i class="ph-bold {{ $alerta['icono'] ?? 'ph-info' }} mt-0.5 shrink-0 text-base {{ $estilo['icono_color'] }}"></i>
 <div class="min-w-0 flex-1">
 <div class="mb-1 flex items-center gap-2">
 <span class="{{ $estilo['badge'] }}">{{ $nivel }}</span>
 </div>
 <p class="text-xs font-bold leading-4 text-apoyo">
 {{ $alerta['descripcion'] ?? 'Sin descripción' }}
 </p>
 @if(!empty($alerta['accion']))
 <div class="mt-2 flex items-center">
     <a href="{{ $alerta['url'] ?? route('admin.alertas-clinicas.index') }}"
        class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[11px] font-bold text-verde-interactivo bg-fondo-card hover:bg-verde-interactivo/10 transition border border-borde-suave group shadow-xs">
         <span>{{ $alerta['accion'] }}</span>
         <i class="ph-bold ph-arrow-right text-[10px] transition-transform group-hover:translate-x-0.5"></i>
     </a>
 </div>
 @endif
 </div>
 </div>
 </li>
 @empty
 <li class="rounded-[1.4rem] bg-fondo-card p-4 text-sm font-bold text-apoyo">
 Sin alertas pendientes.
 </li>
 @endforelse
 </ul>
</div>
