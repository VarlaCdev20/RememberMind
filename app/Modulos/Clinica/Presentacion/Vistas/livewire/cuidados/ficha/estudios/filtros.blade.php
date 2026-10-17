        {{-- 3. BARRA DE FILTROS FORMATO ALERTAS                                     --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador --}}
            <div class="lg:col-span-6 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="filtroBusquedaEstudio"
                    placeholder="Buscar estudios, pruebas o resultados..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                @if(!empty($filtroBusquedaEstudio))
                    <button type="button"
                        wire:click="$set('filtroBusquedaEstudio', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Selector de Tipo --}}
            <div class="lg:col-span-3">
                <select wire:model.live="subtabEstudio"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="TODOS">Todos los tipos</option>
                    <option value="LABORATORIO">Laboratorio</option>
                    <option value="IMAGEN">Imágenes</option>
                    <option value="CARDIOLOGICO">Estudios cardiológicos</option>
                    <option value="OTROS">Otros estudios</option>
                </select>
            </div>

            {{-- Selector de Período --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroPeriodoEstudio"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="30d">30 días</option>
                    <option value="3m">3 meses</option>
                    <option value="6m">Últimos 6 meses</option>
                    <option value="1a">1 año</option>
                    <option value="todos">Todo</option>
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($filtroBusquedaEstudio) || ($subtabEstudio !== 'TODOS') || ($filtroPeriodoEstudio !== '30d');
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($filtroBusquedaEstudio))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($filtroBusquedaEstudio, 16) }}"</span>
                            <button type="button" wire:click="$set('filtroBusquedaEstudio', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($subtabEstudio !== 'TODOS')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Tipo: {{ $subtabEstudio }}</span>
                            <button type="button" wire:click="$set('subtabEstudio', 'TODOS')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroPeriodoEstudio !== '30d')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Período: {{ $filtroPeriodoEstudio }}</span>
                            <button type="button" wire:click="$set('filtroPeriodoEstudio', '30d')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('filtroBusquedaEstudio', ''); $set('subtabEstudio', 'TODOS'); $set('filtroPeriodoEstudio', '30d')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

    {{-- ========================================================================= --}}
