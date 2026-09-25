@props(['kpis' => []])

@php
$estilosColor = [
    'azul-profundo' => ['bg' => 'bg-[#304060]/10 text-[#304060] dark:bg-[#304060]/30 dark:text-[#8595B5]', 'val' => 'text-[#304060] dark:text-[#F8F2EC]'],
    'naranja' => ['bg' => 'bg-[#D2A45E]/15 text-[#966B24] dark:bg-[#D2A45E]/30 dark:text-[#E0B36D]', 'val' => 'text-[#966B24] dark:text-[#E0B36D]'],
    'verde-salud' => ['bg' => 'bg-[#8DA280]/20 text-[#63775B] dark:bg-[#63775B]/30 dark:text-[#9DB491]', 'val' => 'text-[#63775B] dark:text-[#9DB491]'],
    'morado-cog' => ['bg' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300', 'val' => 'text-purple-700 dark:text-purple-300'],
    'terracota' => ['bg' => 'bg-[#A35A44]/15 text-[#A35A44] dark:bg-[#A35A44]/30 dark:text-[#D68A72]', 'val' => 'text-[#A35A44] dark:text-[#D68A72]'],
    'verde-olivo' => ['bg' => 'bg-[#63775B]/15 text-[#63775B] dark:bg-[#63775B]/30 dark:text-[#9DB491]', 'val' => 'text-[#63775B] dark:text-[#9DB491]'],
];

$badgeNivel = [
    'normal' => 'bg-[#E0D5C9] text-[#677084] border border-[#D5CABE] dark:bg-[#34302C] dark:text-[#B8ADA2] dark:border-[#4A443E]',
    'ok' => 'bg-[#E8F1E5] text-[#63775B] border border-[#B8CDAE] dark:bg-[#63775B]/25 dark:text-[#9DB491] dark:border-[#63775B]',
    'alerta' => 'bg-[#FFF0F0] text-[#A7443B] border border-[#EFA3A3] dark:bg-[#A7443B]/20 dark:text-[#F07A70] dark:border-[#A7443B]/50',
    'advertencia' => 'bg-[#FFF1D6] text-[#966B24] border border-[#E8C178] dark:bg-[#D2A45E]/20 dark:text-[#E0B36D] dark:border-[#D2A45E]/50',
];
@endphp

<div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
    @foreach($kpis as $kpi)
        @php
            $estilo = $estilosColor[$kpi['color']] ?? $estilosColor['azul-profundo'];
            $badgeClass = $badgeNivel[$kpi['nivel']] ?? $badgeNivel['normal'];
        @endphp

        <div class="rounded-2xl border border-[#D5CABE] dark:border-[#4A443E] bg-[#F0E8DE] dark:bg-[#262422] p-4 shadow-[0_6px_18px_rgba(70,55,45,0.05)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md flex flex-col justify-between">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $estilo['bg'] }}">
                    <i class="ph-bold {{ $kpi['icono'] }} text-lg"></i>
                </div>
                <span class="rounded-lg {{ $badgeClass }} px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                    {{ $kpi['badge'] }}
                </span>
            </div>

            <div>
                <p class="text-2xl font-black tracking-tight {{ $estilo['val'] }}">{{ $kpi['valor'] }}</p>
                <p class="mt-0.5 text-[11px] font-bold text-[#304060] dark:text-[#F8F2EC] truncate">{{ $kpi['titulo'] }}</p>
                <p class="text-[10px] font-semibold text-[#677084] dark:text-[#B8ADA2] truncate">{{ $kpi['subtitulo'] }}</p>
            </div>
        </div>
    @endforeach
</div>
