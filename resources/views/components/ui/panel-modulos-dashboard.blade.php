@props(['modulos' => []])

<div class="card-interactiva borde-verde-suave rounded-[2rem] border p-5 backdrop-blur-xl">
    <h2 class="text-lg font-black text-azul-profundo">
        Módulos del sistema
    </h2>

    <p class="mb-4 text-xs font-bold text-azul-profundo/55">
        Accesos disponibles según rol.
    </p>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
        @forelse($modulos as $modulo)
            @php
                $proximamente = ($modulo['ruta'] ?? '#') === '#';
            @endphp
            <a href="{{ $proximamente ? 'javascript:void(0)' : $modulo['ruta'] }}" 
               @if($proximamente) title="Próximamente" @endif
               class="group card-interactiva borde-verde-suave rounded-[1.4rem] border p-3 shadow-sm transition-all duration-200 hover:scale-95 hover:bg-[#CBEFE8]/65 hover:shadow-lg active:scale-90 {{ $proximamente ? 'opacity-50 cursor-not-allowed' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#CBEFE8]/75 text-[#006B5E] transition group-hover:rotate-6 group-hover:bg-[#006B5E] group-hover:text-white">
                        <i class="ph-fill {{ $modulo['icono'] ?? 'ph-squares-four' }} text-lg"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-black text-azul-profundo">
                            {{ $modulo['titulo'] ?? 'Módulo' }}
                        </h3>

                        <p class="truncate text-xs font-bold text-azul-profundo/55">
                            {{ $modulo['descripcion'] ?? 'Sin descripción' }}
                        </p>
                    </div>

                    @if($proximamente)
                        <span class="badge-coral text-[8px]">
                            Próx.
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="rounded-[1.3rem] bg-terracota/10 p-4 text-sm font-black text-terracota">
                Sin módulos asignados.
            </div>
        @endforelse
    </div>
</div>
