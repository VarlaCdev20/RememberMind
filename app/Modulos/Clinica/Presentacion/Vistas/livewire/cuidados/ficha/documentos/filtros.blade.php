        {{-- 3. BARRA DE FILTROS FORMATO ALERTAS                                     --}}
    <x-ui.filter-bar class="mb-4">
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2 items-center">
            {{-- Buscador reactivo --}}
            <div class="lg:col-span-3 relative flex items-center">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-[var(--rm-text-secondary)]">
                    <i class="ph-bold ph-magnifying-glass text-base"></i>
                </span>
                <input type="text"
                    wire:model.live.debounce.300ms="filtroBusquedaDoc"
                    placeholder="Buscar documentos..."
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 pl-9 pr-8 text-xs font-medium text-[var(--rm-text-primary)] placeholder-[var(--rm-text-secondary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                @if(!empty($filtroBusquedaDoc))
                    <button type="button"
                        wire:click="$set('filtroBusquedaDoc', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-[var(--rm-text-secondary)] hover:text-[var(--rm-primary)] cursor-pointer"
                        title="Limpiar búsqueda">
                        <i class="ph-bold ph-x-circle text-base"></i>
                    </button>
                @endif
            </div>

            {{-- Selector de Tipo --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroTipoDoc"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="TODOS">Todos los tipos</option>
                    <option value="Informe">Informe médico</option>
                    <option value="Radiografía">Radiografía</option>
                    <option value="Laboratorio">Laboratorio</option>
                    <option value="Consentimiento">Consentimiento informado</option>
                    <option value="Plan">Plan de cuidados</option>
                    <option value="Cardiológico">Cardiológico</option>
                    <option value="Caída">Informe de caída</option>
                    <option value="Identificación">Documento de identidad</option>
                    <option value="Formulario">Formulario administrativo</option>
                </select>
            </div>

            {{-- Selector de Categoría --}}
            <div class="lg:col-span-3">
                <select wire:model.live="filtroCategoriaDoc"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="TODAS">Todas las categorías</option>
                    <option value="CLINICO">Clínicos</option>
                    <option value="ADMINISTRATIVO">Administrativos</option>
                    <option value="IMAGEN">Imágenes</option>
                    <option value="LEGAL">Legales</option>
                    <option value="PERSONAL">Personal</option>
                </select>
            </div>

            {{-- Selector de Orden --}}
            <div class="lg:col-span-3">
                <select wire:model.live="ordenDoc"
                    class="w-full rounded-xl border border-[var(--rm-border)] bg-[var(--rm-input-bg)] py-2 px-3 text-xs font-medium text-[var(--rm-text-primary)] focus:border-[var(--rm-primary)] focus:ring-1 focus:ring-[var(--rm-primary)] focus:outline-none h-[38px]">
                    <option value="recientes">Más recientes primero</option>
                    <option value="antiguos">Más antiguos primero</option>
                    <option value="nombre_asc">Nombre (A - Z)</option>
                    <option value="nombre_desc">Nombre (Z - A)</option>
                    <option value="tamano_desc">Mayor tamaño</option>
                </select>
            </div>
        </div>

        @php
            $hasFiltrosActivos = !empty($filtroBusquedaDoc) || ($filtroTipoDoc !== 'TODOS') || ($filtroCategoriaDoc !== 'TODAS');
        @endphp
        @if($hasFiltrosActivos)
            <div class="rm-filter-bar__active">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="rm-filter-bar__active-label">
                        <i class="ph-bold ph-funnel text-xs"></i> Filtros activos:
                    </span>
                    @if(!empty($filtroBusquedaDoc))
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Búsqueda: "{{ Str::limit($filtroBusquedaDoc, 16) }}"</span>
                            <button type="button" wire:click="$set('filtroBusquedaDoc', '')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroTipoDoc !== 'TODOS')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Tipo: {{ $filtroTipoDoc }}</span>
                            <button type="button" wire:click="$set('filtroTipoDoc', 'TODOS')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                    @if($filtroCategoriaDoc !== 'TODAS')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-[var(--rm-surface-alt)] border border-[var(--rm-border)] text-[11px] font-semibold text-[var(--rm-text-primary)]">
                            <span>Categoría: {{ $filtroCategoriaDoc }}</span>
                            <button type="button" wire:click="$set('filtroCategoriaDoc', 'TODAS')" class="hover:text-[var(--rm-primary)] cursor-pointer ml-0.5"><i class="ph-bold ph-x text-xs"></i></button>
                        </span>
                    @endif
                </div>
                <button type="button" wire:click="$set('filtroBusquedaDoc', ''); $set('filtroTipoDoc', 'TODOS'); $set('filtroCategoriaDoc', 'TODAS')" class="rm-filter-bar__clear-btn">
                    <i class="ph-bold ph-arrow-counter-clockwise text-xs"></i>
                    Limpiar filtros
                </button>
            </div>
        @endif
    </x-ui.filter-bar>

    {{-- ========================================================================= --}}
