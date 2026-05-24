@props(['kpis' => []])

@php
$estilosColor = [
    'azul-profundo' => ['bg' => 'bg-azul-profundo/10', 'text' => 'text-azul-profundo', 'val' => 'text-azul-profundo'],
    'naranja'       => ['bg' => 'bg-[#F4A261]/15',     'text' => 'text-[#D4843A]',     'val' => 'text-[#D4843A]'],
    'verde-salud'   => ['bg' => 'bg-[#2A9D8F]/10',     'text' => 'text-[#2A9D8F]',     'val' => 'text-[#2A9D8F]'],
    'morado-cog'    => ['bg' => 'bg-[#9B8AC7]/15',     'text' => 'text-[#7A68B0]',     'val' => 'text-[#7A68B0]'],
    'terracota'     => ['bg' => 'bg-terracota/10',      'text' => 'text-terracota',     'val' => 'text-terracota'],
    'verde-olivo'   => ['bg' => 'bg-[#8DA280]/15',     'text' => 'text-[#63775B]',     'val' => 'text-[#63775B]'],
];

$badgeNivel = [
    'normal'      => 'bg-azul-profundo/8 text-azul-profundo/60',
    'ok'          => 'bg-[#2A9D8F]/10 text-[#2A9D8F]',
    'alerta'      => 'bg-[#F4A261]/20 text-[#D4843A]',
    'advertencia' => 'bg-terracota/10 text-terracota',
];
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
    @foreach($kpis as $kpi)
        @php
            $estilo     = $estilosColor[$kpi['color']] ?? $estilosColor['azul-profundo'];
            $badgeClass = $badgeNivel[$kpi['nivel']] ?? $badgeNivel['normal'];
        @endphp

        <div class="rounded-[1.8rem] border border-[#C7B5A3] bg-[#E6DDD3]/85 p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $estilo['bg'] }} {{ $estilo['text'] }}">
                    <i class="ph-fill {{ $kpi['icono'] }} text-lg"></i>
                </div>
                <span class="rounded-full {{ $badgeClass }} px-2 py-0.5 text-[9px] font-black uppercase tracking-wide">
                    {{ $kpi['badge'] }}
                </span>
            </div>

            <p class="text-2xl font-black {{ $estilo['val'] }}">{{ $kpi['valor'] }}</p>
            <p class="mt-0.5 text-[11px] font-black text-azul-profundo">{{ $kpi['titulo'] }}</p>
            <p class="text-[10px] font-bold text-azul-profundo/50">{{ $kpi['subtitulo'] }}</p>
        </div>
    @endforeach
</div>
