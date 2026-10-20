<div class="space-y-6 text-rm-primary">
    <x-ui.section-card title="Sistema experto" subtitle="Seleccione un residente para consultar sus resultados o registrar una valoración original" icon="ph-brain">
        <p class="text-sm text-rm-secondary">Las observaciones y mediciones se consultan en el informe. El patrón de Petersen solo se obtiene cuando sus requisitos cuentan con soportes originales suficientes.</p>
        <div class="mt-4 max-w-lg">
            <label for="experto-buscar-residente" class="block text-sm font-semibold">Buscar residente por nombre o código</label>
            <input id="experto-buscar-residente" type="search" wire:model.live.debounce.300ms="busqueda" class="rm-input w-full" autocomplete="off">
        </div>
    </x-ui.section-card>

    <x-ui.section-card title="Residentes admitidos" subtitle="Abra el informe antes o después de registrar sus fuentes originales" icon="ph-users-three">
        @if($residentes->isEmpty())
            <p>{{ trim($busqueda) === '' ? 'No hay residentes admitidos disponibles.' : 'No se encontraron residentes con esa búsqueda.' }}</p>
        @else
            <ul class="space-y-3">
                @foreach($residentes as $residente)
                    @php($acceso = $accesos[$residente->getKey()])
                    @if($acceso['consultar'])
                        <li wire:key="experto-residente-{{ $residente->getKey() }}" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-rm p-4">
                            <div class="min-w-0">
                                <p class="font-semibold">{{ $residente->nombre_completo }}</p>
                                <p class="text-sm text-rm-secondary">{{ $residente->getKey() }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route(auth()->user()->hasRole('PSICOLOGO/A') ? 'admin.psicologia.residente.resultados-experto' : 'admin.medico.residente.resultados-experto', $residente) }}" class="rm-btn rm-btn-secondary">Ver resultados</a>
                                @if($acceso['valorar'])
                                    <a href="{{ route('admin.sistema-experto.petersen-original', $residente) }}" class="rm-btn rm-btn-primary">Registrar valoración original</a>
                                @endif
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
            <div class="mt-4">{{ $residentes->links() }}</div>
        @endif
    </x-ui.section-card>
</div>
