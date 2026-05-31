@props(['alertas' => []])

@php
$estilosNivel = [
    'URGENTE'     => ['badge' => 'rm-badge-danger',   'icono_color' => 'text-rose-600'],
    'INFORMATIVA' => ['badge' => 'rm-badge-info',     'icono_color' => 'text-[#2EA9C0]'],
    'OK'          => ['badge' => 'rm-badge-success',  'icono_color' => 'text-[#006B5E]'],
];
@endphp

<div class="card-interactiva borde-verde-suave rounded-[2rem] border p-5">
    <h2 class="text-lg font-black text-azul-profundo">Alertas administrativas</h2>
    <p class="mb-4 text-xs font-bold text-azul-profundo/55">Pendientes de revisión institucional</p>

    <ul class="space-y-2">
        @forelse($alertas as $alerta)
            @php
                $nivel  = $alerta['nivel'] ?? 'INFORMATIVA';
                $estilo = $estilosNivel[$nivel] ?? $estilosNivel['INFORMATIVA'];
            @endphp

            <li class="card-interactiva rounded-[1.4rem] border border-transparent p-3 transition hover:-translate-y-0.5 hover:bg-[#CBEFE8]/65">
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
            <li class="rounded-[1.4rem] bg-[#FFFDF9]/75 p-4 text-sm font-bold text-azul-profundo/60">
                Sin alertas pendientes.
            </li>
        @endforelse
    </ul>
</div>
