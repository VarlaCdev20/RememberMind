<div class="rm-expert-residents rm-expert-resident-directory space-y-6 text-rm-primary" x-data="{ vista: 'tarjetas' }">
    <header class="rm-card rm-resident-expert">
        <p class="rm-expert-eyebrow">Apoyo a la valoración profesional</p>
        <h1 class="rm-expert-section-title">Sistema experto por residente</h1>
        <p class="rm-expert-prose mt-2">Abra la consulta de una persona para explorar sus registros, valoraciones y conexiones. Cada expediente conserva sus propias fuentes y límites.</p>
        <ol class="rm-expert-resident-directory__journey" aria-label="Cómo recorrer la consulta"><li><span>1</span>Seleccione un residente</li><li><span>2</span>Explore sus conexiones</li><li><span>3</span>Consulte la evidencia original</li></ol>
    </header>

    <x-ui.filter-bar as="div" aria-label="Buscar residentes y elegir vista">
        <div class="flex w-full flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="w-full min-w-0 sm:max-w-lg">
                <label for="experto-buscar-residente" class="rm-collection-filter-label">Buscar residente</label>
                <div class="rm-filter-search__control">
                    <span class="rm-filter-search__icon" aria-hidden="true"><i class="ph-bold ph-magnifying-glass"></i></span>
                    <input id="experto-buscar-residente" type="search" wire:model.live.debounce.300ms="busqueda" class="rm-filter-search__input" autocomplete="off" placeholder="Nombre o apellido">
                    @if(trim($busqueda) !== '')
                        <button type="button" wire:click="$set('busqueda', '')" class="rm-filter-search__clear" aria-label="Limpiar búsqueda" title="Limpiar búsqueda">
                            <i class="ph-bold ph-x" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
            </div>
            <x-ui.collection-view-switch model="vista" label="Vista de residentes del sistema experto" :options="[
                ['value' => 'tarjetas', 'label' => 'Tarjetas', 'icon' => 'ph-squares-four'],
                ['value' => 'tabla', 'label' => 'Tabla', 'icon' => 'ph-table'],
            ]" />
        </div>
    </x-ui.filter-bar>

    <x-ui.section-card title="Residentes admitidos" subtitle="Abra el informe cognitivo del residente" icon="ph-users-three">
        <div class="mb-4">
            <p class="text-sm text-rm-secondary" aria-live="polite">{{ $residentes->total() }} {{ $residentes->total() === 1 ? 'residente encontrado' : 'residentes encontrados' }}</p>
        </div>

        @if($residentes->isEmpty())
            <p class="rounded-xl border border-rm p-5 text-sm text-rm-secondary" role="status">{{ trim($busqueda) === '' ? 'No hay residentes admitidos disponibles.' : 'No se encontraron residentes con esa búsqueda. Pruebe con otro nombre o apellido.' }}</p>
        @else
            <div x-show="vista === 'tarjetas'" role="list" aria-label="Residentes en tarjetas" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($residentes as $residente)
                    @php($acceso = $accesos[$residente->getKey()])
                    @if($acceso['consultar'])
                        <article wire:key="experto-tarjeta-{{ $residente->getKey() }}" role="listitem" class="flex min-w-0 flex-col justify-between gap-4 rounded-xl border border-rm bg-[var(--rm-surface)] p-4 shadow-sm">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[var(--rm-surface-soft)] font-bold text-rm-primary" aria-hidden="true">{{ mb_strtoupper(mb_substr($residente->nombres, 0, 1).mb_substr($residente->apellido_paterno, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <h3 class="break-words font-semibold">{{ $residente->nombre_completo }}</h3>
                                    <p class="text-sm text-rm-secondary">{{ $residente->edad !== null ? $residente->edad.' años' : 'Edad no registrada' }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @if(auth()->user()->hasRole('PSICOLOGO/A'))
                                    <a href="{{ route('admin.psicologia.residente.ficha', $residente) }}" class="rm-btn rm-btn-primary">Ficha psicológica<span class="sr-only"> de {{ $residente->nombre_completo }}</span></a>
                                @endif
                                <a href="{{ route(auth()->user()->hasRole('PSICOLOGO/A') ? 'admin.psicologia.residente.resultados-experto' : 'admin.medico.residente.resultados-experto', $residente) }}" class="rm-btn rm-btn-secondary">Ver resultados<span class="sr-only"> de {{ $residente->nombre_completo }}</span></a>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>

            <div x-cloak x-show="vista === 'tabla'" class="rm-expert-resident-table rounded-xl border border-rm">
                <table class="rm-data-table rm-table w-full text-left text-sm">
                    <caption class="sr-only">Residentes admitidos y acciones disponibles del sistema experto</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="p-3">Residente</th>
                            <th scope="col" class="p-3">Edad</th>
                            <th scope="col" class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($residentes as $residente)
                            @php($acceso = $accesos[$residente->getKey()])
                            @if($acceso['consultar'])
                                <tr wire:key="experto-fila-{{ $residente->getKey() }}" class="border-t border-rm">
                                    <th scope="row" class="p-3 font-semibold">{{ $residente->nombre_completo }}</th>
                                    <td data-label="Edad" class="p-3 text-rm-secondary">{{ $residente->edad !== null ? $residente->edad.' años' : 'No registrada' }}</td>
                                    <td data-label="Acciones" class="p-3">
                                        <div class="flex flex-wrap gap-2">
                                            @if(auth()->user()->hasRole('PSICOLOGO/A'))
                                                <a href="{{ route('admin.psicologia.residente.ficha', $residente) }}" class="rm-btn rm-btn-primary">Ficha psicológica<span class="sr-only"> de {{ $residente->nombre_completo }}</span></a>
                                            @endif
                                            <a href="{{ route(auth()->user()->hasRole('PSICOLOGO/A') ? 'admin.psicologia.residente.resultados-experto' : 'admin.medico.residente.resultados-experto', $residente) }}" class="rm-btn rm-btn-secondary">Ver resultados<span class="sr-only"> de {{ $residente->nombre_completo }}</span></a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $residentes->links() }}</div>
        @endif
    </x-ui.section-card>
</div>
