<x-sistema-layout>
    <div class="space-y-5">
        <x-ui.collection-header title="Residentes" subtitle="Los residentes se crean exclusivamente mediante admisión formal." icon="ph-users-three" eyebrow="Gestión institucional" />
        <x-ui.filter-bar as="form" method="GET" role="search"><div class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end"><div><label for="residentes-buscar" class="rm-collection-filter-label">Buscar residente</label><input id="residentes-buscar" class="rm-input" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o documento"></div><button class="rm-btn-secondary">Buscar</button></div></x-ui.filter-bar>
        <x-ui.collection-results title="Residentes encontrados" :count="$residentes->total()" label="residentes">
            <div class="overflow-x-auto"><table class="rm-data-table rm-table w-full text-left text-sm"><thead><tr><th class="p-3">Residente</th><th class="p-3">Documento</th><th class="p-3">Estado</th><th class="p-3">Ubicación</th></tr></thead><tbody>
            @forelse($residentes as $residente)<tr class="border-t border-borde-suave"><td class="p-3 font-bold"><a class="text-boton-acento" href="{{ route('admin.residentes.show', $residente) }}">{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</a></td><td class="p-3">{{ $residente->numero_documento ?: '—' }}</td><td class="p-3">{{ $residente->estado }}</td><td class="p-3">{{ $residente->ocupacionActiva?->cama?->habitacion?->codigo ?? 'Sin cama activa' }}</td></tr>@empty<tr><td class="p-6 text-center text-meta" colspan="4">No hay residentes.</td></tr>@endforelse
            </tbody></table></div>
        </x-ui.collection-results>
        {{ $residentes->links() }}
    </div>
</x-sistema-layout>
