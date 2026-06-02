@props([
    'id',
    'titulo' => 'Gráfico',
    'subtitulo' => '',
    'icono' => 'ph-chart-line-up',
    'color' => 'terracota',
])
@php
    $colores = [
        'terracota' => 'bg-estado-advertencia-bg text-boton-acento',
        'azul' => 'bg-fondo-hover text-titulo',
        'verde' => 'bg-estado-exito-bg text-estado-exito-texto',
        'marron' => 'bg-modulo-voluntariosFondo text-modulo-voluntariosTexto',
    ];

    $estilo = $colores[$color] ?? $colores['terracota'];
@endphp
    
       <div class="rm-card group h-full rounded-[1.8rem] p-4 backdrop-blur-xl transition-all duration-300 hover:-translate-y-1">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
            <h2 class="text-base font-extrabold text-titulo md:text-lg">
                {{ $titulo }}
            </h2>
      
 
            <p class="text-xs font-bold text-meta">
                {{ $subtitulo }}
            </p>
        </div>
  
         <div class="flex h-10 w-10 items-center justify-center rounded-xl shadow-inner transition-all duration-300 group-hover:rotate-6 group-hover:scale-105 {{ $estilo }}">
            <i class="ph-fill {{ $icono }} text-xl"></i>
        </div>
    </div>

 <div class="h-44 rm-chart-panel p-3">
 <canvas id="{{ $id }}"></canvas>
 </div>
</div>