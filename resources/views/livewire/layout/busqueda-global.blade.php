<div class="relative w-full" x-data="{ isOpen: @entangle('isOpen') }" @click.outside="isOpen = false" @keydown.escape.window="isOpen = false">
    {{-- Input de búsqueda --}}
    <div class="relative flex items-center">
        <i class="ph-bold ph-magnifying-glass absolute left-3 text-meta text-sm"></i>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="query"
            placeholder="Buscar módulos, expedientes, personal..."
            class="rm-global-search-input rounded-full pl-9 pr-9 w-full text-sm shadow-sm"
            @focus="if($wire.query.length >= 2) isOpen = true"
        >
        @if(strlen($query) > 0)
            <button wire:click="clear" class="absolute right-2 text-meta hover:text-terracota transition">
                <i class="ph-bold ph-x-circle text-lg"></i>
            </button>
        @endif
    </div>

    {{-- Dropdown de resultados --}}
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
        class="absolute top-full left-0 right-0 mt-2 w-full min-w-[300px] z-50 rounded-xl rm-global-search-dropdown shadow-lg max-h-[70vh] overflow-y-auto"
        x-cloak
    >
        @if(strlen(trim($query)) >= 2)
            @if(empty($resultados))
                <div class="p-4 text-center">
                    <i class="ph-fill ph-magnifying-glass-minus text-3xl text-meta mb-2"></i>
                    <p class="text-sm font-bold text-titulo">No hay resultados</p>
                    <p class="text-xs font-bold text-parrafo mt-1">No se encontró información para "{{ $query }}".</p>
                </div>
            @else
                <div class="p-2 space-y-3">
                    @foreach($resultados as $grupo)
                        <div>
                            <h4 class="px-2 py-1 text-[10px] font-black uppercase tracking-widest text-meta border-b border-borde-suave mb-1">
                                {{ $grupo['grupo'] }}
                            </h4>
                            <ul class="space-y-0.5">
                                @foreach($grupo['items'] as $item)
                                    <li>
                                        <a href="{{ $item['url'] }}" class="rm-global-search-item flex items-start gap-3 px-2 py-2 group cursor-pointer">
                                            <div class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-fondo-card border border-borde-suave text-boton-acento group-hover:bg-boton-acento group-hover:text-inverso transition mt-0.5">
                                                <i class="ph-fill {{ $item['icono'] }} text-lg"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <p class="rm-global-search-title text-sm truncate">{{ $item['titulo'] }}</p>
                                                    @if(isset($item['etiqueta']))
                                                        <span class="shrink-0 text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-fondo-card border border-borde-suave text-meta group-hover:bg-boton-acento/10 group-hover:text-boton-acento group-hover:border-boton-acento/20 transition">
                                                            {{ $item['etiqueta'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <p class="rm-global-search-detail text-[10px] font-bold truncate mt-0.5">{{ $item['subtitulo'] }}</p>
                                                @if(isset($item['detalle']))
                                                    <p class="rm-global-search-detail text-[9px] font-semibold truncate mt-0.5">{{ $item['detalle'] }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center h-8">
                                                <i class="ph-bold ph-caret-right text-meta group-hover:text-boton-acento transition"></i>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="p-4 text-center">
                <i class="ph-bold ph-keyboard text-2xl text-meta mb-2"></i>
                <p class="text-xs font-bold text-parrafo">Escriba al menos 2 caracteres para buscar.</p>
            </div>
        @endif
    </div>
</div>
