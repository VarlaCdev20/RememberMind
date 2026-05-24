@props(['alertas' => []])

@php
$estilosNivel = [
    'URGENTE'     => ['badge' => 'rm-badge-danger',   'icono_color' => 'text-rose-600'],
    'INFORMATIVA' => ['badge' => 'rm-badge-info',     'icono_color' => 'text-blue-600'],
    'OK'          => ['badge' => 'rm-badge-success',  'icono_color' => 'text-emerald-600'],
];
@endphp

<div class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)]">
    <h2 class="text-lg font-black text-azul-profundo">Alertas administrativas</h2>
    <p class="mb-4 text-xs font-bold text-azul-profundo/55">Pendientes de revisión institucional</p>

    <ul class="space-y-2">
        @forelse($alertas as $alerta)
            @php
                $nivel  = $alerta['nivel'] ?? 'INFORMATIVA';
                $estilo = $estilosNivel[$nivel] ?? $estilosNivel['INFORMATIVA'];
            @endphp

            <li class="rounded-[1.4rem] bg-[#D5C7B9]/75 p-3 transition hover:-translate-y-0.5 hover:bg-[#D5C7B9]">
                <div class="flex items-start gap-2">
                    <i class="ph-bold {{ $alerta['icono'] ?? 'ph-info' }} mt-0.5 shrink-0 text-base {{ $estilo['icono_color'] }}"></i>
                    <div class="min-w-0 flex-1">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="{{ $estilo['badge'] }}">{{ $nivel }}</span>
                        </div>
                        <p class="text-xs font-bold leading-4 text-azul-profundo/75">
                            {{ $alerta['descripcion'] ?? 'Sin descripción' }}
                        </p>
                        @if(!empty($alerta['accion']))
                            <p class="mt-1 text-[10px] font-black uppercase tracking-wide text-azul-profundo/45">
                                {{ $alerta['accion'] }}
                            </p>
                        @endif
                    </div>
                </div>
            </li>
        @empty
            <li class="rounded-[1.4rem] bg-[#D5C7B9]/70 p-4 text-sm font-bold text-azul-profundo/55">
                Sin alertas pendientes.
            </li>
        @endforelse
    </ul>
</div>
