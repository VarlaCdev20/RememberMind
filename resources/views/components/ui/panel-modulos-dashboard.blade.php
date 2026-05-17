@props(['modulos' => []])

<div class="rounded-[2rem] border border-[#C7B5A3] bg-[#E6DDD3]/88 p-5 shadow-[0_16px_38px_rgba(47,62,92,0.12)] backdrop-blur-xl">
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
               class="group rounded-[1.4rem] border border-[#C7B5A3] bg-[#D5C7B9]/75 p-3 shadow-sm transition-all duration-200 hover:scale-95 hover:bg-[#E6DDD3] hover:shadow-lg active:scale-90 {{ $proximamente ? 'opacity-50 cursor-not-allowed' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-terracota/10 text-terracota transition group-hover:rotate-6 group-hover:bg-terracota group-hover:text-white">
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
                        <span class="rounded-full bg-azul-profundo/10 px-2 py-0.5 text-[8px] font-black uppercase text-azul-profundo/40">
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