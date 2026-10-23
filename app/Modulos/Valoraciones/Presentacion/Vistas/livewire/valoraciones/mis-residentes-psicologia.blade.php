<div class="rm-resident-directory" data-role="psychology">
    <x-ui.residents-page-header title="MIS RESIDENTES" subtitle="Residentes admitidos disponibles para consulta y valoración psicológica.">
        <x-slot:actions>
            <a href="{{ route('admin.psicologia.dashboard') }}" class="rm-btn rm-btn-secondary">
                <i class="ph-bold ph-house" aria-hidden="true"></i><span>Inicio de Psicología</span>
            </a>
        </x-slot:actions>
    </x-ui.residents-page-header>

    <div class="rm-resident-directory__filter-layout">
        <x-ui.filter-bar as="div" class="rm-resident-directory__filters" aria-label="Buscar y ordenar residentes">
            <div class="rm-resident-directory__filter-search">
                <label for="psychology-residents-search">Buscar por nombre, apellido o habitación</label>
                <div class="rm-resident-directory__filter-search-control">
                    <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
                    <input id="psychology-residents-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Escribe un nombre o habitación" autocomplete="off">
                </div>
            </div>
            <div class="rm-resident-directory__filter-bottom">
                <div class="rm-resident-directory__filter-chips">
                    <span class="rm-resident-directory__filter-chip is-active">{{ $total }} {{ $total === 1 ? 'residente admitido' : 'residentes admitidos' }}</span>
                </div>
                <div class="rm-resident-directory__filter-selects">
                    <div class="rm-resident-directory__filter-field">
                        <label for="psychology-residents-order">Ordenar por</label>
                        <select id="psychology-residents-order" wire:model.live="orden">
                            <option value="NOMBRE_ASC">Apellido A–Z</option>
                            <option value="NOMBRE_DESC">Apellido Z–A</option>
                        </select>
                    </div>
                </div>
                <x-ui.view-toggle model="vistaModo" :value="$vistaModo" label="Vista de residentes" :options="[
                    ['value' => 'tarjetas', 'label' => 'Ver tarjetas', 'icon' => 'ph-squares-four'],
                    ['value' => 'tabla', 'label' => 'Ver listado', 'icon' => 'ph-list-dashes'],
                ]" />
            </div>
        </x-ui.filter-bar>
    </div>

    <section class="rm-resident-directory__results" aria-labelledby="psychology-residents-title" wire:loading.class="is-loading" wire:target="search,orden,vistaModo">
        <div class="rm-resident-directory__results-header">
            <h2 id="psychology-residents-title">{{ $residentes->total() }} {{ $residentes->total() === 1 ? 'residente encontrado' : 'residentes encontrados' }}</h2>
            @if($residentes->total() > 0)
                <span class="rm-resident-directory__shown" aria-live="polite">Mostrando {{ $residentes->firstItem() }}–{{ $residentes->lastItem() }} de {{ $residentes->total() }}</span>
            @endif
        </div>
        <div wire:loading wire:target="search,orden,vistaModo" class="rm-resident-directory__loading" role="status" aria-label="Actualizando residentes">
            <div class="rm-resident-directory__skeleton-rows" aria-hidden="true"><span></span><span></span><span></span></div>
            <span class="sr-only">Actualizando residentes…</span>
        </div>

        @if($residentes->isEmpty())
            <div class="rm-resident-directory__empty" wire:loading.remove wire:target="search,orden,vistaModo">
                <span class="rm-resident-directory__empty-icon" aria-hidden="true"><i class="ph-bold ph-users-three"></i></span>
                <h3>{{ $total === 0 ? 'Aún no hay residentes admitidos' : 'No encontramos residentes con esa búsqueda' }}</h3>
                <p>{{ $total === 0 ? 'El listado aparecerá cuando se formalice una admisión.' : 'Prueba con otro nombre, apellido o habitación.' }}</p>
                @if($search !== '')
                    <button type="button" wire:click="$set('search', '')" class="rm-btn rm-btn-secondary">Limpiar búsqueda</button>
                @endif
            </div>
        @elseif($vistaModo === 'tabla')
            <div class="rm-resident-directory__list" role="list" wire:loading.remove wire:target="search,orden,vistaModo">
                <div class="rm-resident-directory__list-heading" aria-hidden="true"><span>Residente</span><span>Ubicación</span><span>Estado</span><span>Última atención psicológica</span><span></span></div>
                @foreach($residentes as $residente)
                    <x-ui.resident-card :resident="$residente" mode="row" variant="psychology" status-label="Admitido" status-tone="slate"
                        context-label="Última atención psicológica"
                        :context-value="$residente->ultima_atencion_psicologia ? \Illuminate\Support\Carbon::parse($residente->ultima_atencion_psicologia)->translatedFormat('d M Y') : 'Sin atención registrada'"
                        :primary-href="route('admin.psicologia.residente.ficha', $residente)" primary-label="Abrir ficha"
                        :key="'psychology-row-'.$residente->getKey()" />
                @endforeach
            </div>
        @else
            <div class="rm-resident-directory__cards" role="list" wire:loading.remove wire:target="search,orden,vistaModo">
                @foreach($residentes as $residente)
                    <x-ui.resident-card :resident="$residente" mode="card" variant="psychology"
                        context-label="Última atención psicológica"
                        :context-value="$residente->ultima_atencion_psicologia ? \Illuminate\Support\Carbon::parse($residente->ultima_atencion_psicologia)->translatedFormat('d M Y') : 'Sin atención registrada'"
                        :primary-href="route('admin.psicologia.residente.ficha', $residente)" primary-label="Abrir ficha"
                        :key="'psychology-card-'.$residente->getKey()" />
                @endforeach
            </div>
        @endif
    </section>

    @if($residentes->hasPages())
        <nav class="rm-resident-directory__pagination" aria-label="Páginas de residentes">{{ $residentes->links() }}</nav>
    @endif
</div>
