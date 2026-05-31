@props(['kpis' => []])

@php
$estilosColor = [
    'azul-profundo' => ['bg' => 'bg-azul-profundo/10', 'text' => 'text-azul-profundo', 'val' => 'text-azul-profundo'],
    'naranja'       => ['bg' => 'bg-[#F6B08A]/25',     'text' => 'text-[#A6532B]',     'val' => 'text-[#A6532B]'],
    'verde-salud'   => ['bg' => 'bg-[#CBEFE8]/75',     'text' => 'text-[#006B5E]',     'val' => 'text-[#006B5E]'],
    'morado-cog'    => ['bg' => 'bg-[#9B8AC7]/15',     'text' => 'text-[#7A68B0]',     'val' => 'text-[#7A68B0]'],
    'terracota'     => ['bg' => 'bg-[#F28B54]/14',      'text' => 'text-[#F28B54]',     'val' => 'text-[#F28B54]'],
    'verde-olivo'   => ['bg' => 'bg-[#97E3D5]/28',     'text' => 'text-[#0B4F46]',     'val' => 'text-[#0B4F46]'],
];

$badgeNivel = [
    'normal'      => 'bg-azul-profundo/8 text-azul-profundo/60',
    'ok'          => 'badge-green-ash',
    'alerta'      => 'badge-coral',
    'advertencia' => 'badge-coral',
];
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
    @foreach($kpis as $kpi)
        @php
            $estilo     = $estilosColor[$kpi['color']] ?? $estilosColor['azul-profundo'];
            $badgeClass = $badgeNivel[$kpi['nivel']] ?? $badgeNivel['normal'];
        @endphp

        <div class="card-interactiva borde-verde-suave rounded-[1.8rem] border p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $estilo['bg'] }} {{ $estilo['text'] }}">
                    <i class="ph-fill {{ $kpi['icono'] }} text-lg"></i>
                </div>
                <span class="rounded-full {{ $badgeClass }} px-2.5 py-1 text-xs font-black uppercase tracking-wide">
                    {{ $kpi['badge'] }}
                </span>
            </div>

            <p class="text-2xl font-black {{ $estilo['val'] }}">{{ $kpi['valor'] }}</p>
            <p class="mt-0.5 text-[11px] font-black text-azul-profundo">{{ $kpi['titulo'] }}</p>
            <p class="text-[10px] font-bold text-azul-profundo/50">{{ $kpi['subtitulo'] }}</p>
        </div>
    @endforeach
</div>
