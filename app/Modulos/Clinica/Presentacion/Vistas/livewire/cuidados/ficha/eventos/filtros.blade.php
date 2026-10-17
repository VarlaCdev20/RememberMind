        {{-- 3. BARRA DE FILTROS FORMATO ALERTAS                                     --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
                {{-- Buscador en tiempo real --}}
                <div class="relative min-w-[220px] sm:min-w-[260px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                        <i class="ph-bold ph-magnifying-glass text-base"></i>
                    </span>
                    <input type="text"
                        wire:model.live.debounce.200ms="filtroBusquedaEvento"
                        placeholder="Buscar eventos..."
                        class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    @if(!empty($filtroBusquedaEvento))
                        <button type="button"
                            wire:click="$set('filtroBusquedaEvento', '')"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                            title="Limpiar búsqueda">
                            <i class="ph-bold ph-x-circle text-base"></i>
                        </button>
                    @endif
                </div>

                {{-- Chips de Categoría Desplazables con Colorcitos --}}
                <div class="rm-filter-pills">
                    @php
                        $chips = [
                            'TODOS' => ['label' => 'Todos (' . count($this->eventosClinicos) . ')', 'icon' => 'ph-circles-four'],
                            'CAIDA' => ['label' => 'Caídas (' . ($porTipo['data'][0] ?? 4) . ')', 'icon' => 'ph-person-simple-throw'],
                            'LESION' => ['label' => 'Lesiones (' . ($porTipo['data'][1] ?? 2) . ')', 'icon' => 'ph-bandaids'],
                            'INCIDENTE' => ['label' => 'Incidentes (' . ($porTipo['data'][2] ?? 2) . ')', 'icon' => 'ph-warning'],
                            'COMPLICACION' => ['label' => 'Complicaciones (' . ($porTipo['data'][3] ?? 1) . ')', 'icon' => 'ph-shield-warning'],
                            'OTRO' => ['label' => 'Otros (' . ($porTipo['data'][4] ?? 1) . ')', 'icon' => 'ph-dots-three-circle'],
                        ];
                    @endphp

                    @foreach($chips as $key => $item)
                        <button type="button"
                            wire:click="setFiltroTipoEvento('{{ $key }}')"
                            class="rm-filter-pill {{ $filtroTipoEvento === $key ? 'is-active' : '' }}">
                            <i class="ph-bold {{ $item['icon'] }} text-xs"></i>
                            <span>{{ $item['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Selector de período --}}
            <div class="flex items-center gap-2 shrink-0">
                <span class="text-xs text-[var(--rm-text-secondary)] font-medium">Período:</span>
                <select wire:model.live="filtroPeriodoEvento"
                    class="rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px] cursor-pointer transition">
                    <option value="30d">30 días</option>
                    <option value="3m">3 meses</option>
                    <option value="6m">Últimos 6 meses</option>
                    <option value="1a">1 año</option>
                    <option value="todos">Todo el historial</option>
                </select>
            </div>
        </div>
    </x-ui.filter-bar>

    {{-- ========================================================================= --}}
