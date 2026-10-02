<x-sistema-layout>
    <div class="rm-page-layout">
        <x-ui.page-header titulo="Residentes" subtitulo="Consulta de personas formalmente admitidas y su ubicación actual." :breadcrumb="[['label' => 'Inicio', 'url' => route('dashboard')], ['label' => 'Residentes']]" />
        <form method="GET" action="{{ route('admin.residentes.index') }}" role="search" class="rm-card rm-card-soft rm-card-body rm-search-form">
            <x-ui.field name="buscar" label="Buscar residente" :value="request('buscar')" helper="Busca por nombre, apellido paterno o número de documento." autocomplete="off" />
            <button type="submit" class="rm-btn rm-btn-primary">Buscar</button>
            @if(request()->filled('buscar'))<a class="rm-btn rm-btn-ghost" href="{{ route('admin.residentes.index') }}">Limpiar</a>@endif
        </form>
        <section class="rm-section" aria-labelledby="lista-residentes-titulo">
            <div class="rm-section-heading"><h2 id="lista-residentes-titulo">Listado de residentes</h2></div>
            @if($residentes->isEmpty())
                <x-ui.empty-state icono="ph-users-three" :titulo="request()->filled('buscar') ? 'No se encontraron residentes' : 'Todavía no hay residentes'" :texto="request()->filled('buscar') ? 'Prueba con otro nombre o documento.' : 'Los residentes aparecerán aquí después de la admisión formal.'" />
            @else
                <div class="rm-table-container rm-table-scroll">
                    <table class="rm-table">
                        <thead><tr><th scope="col">Residente</th><th scope="col">Documento</th><th scope="col">Estado</th><th scope="col">Ubicación</th></tr></thead>
                        <tbody>
                            @foreach($residentes as $residente)
                                <tr>
                                    <td><a class="rm-inline-link" href="{{ route('admin.residentes.show', $residente) }}">{{ trim($residente->nombres.' '.$residente->apellido_paterno.' '.$residente->apellido_materno) }}</a></td>
                                    <td>{{ $residente->numero_documento ?: '—' }}</td>
                                    <td><x-ui.status-badge :estado="$residente->estado" /></td>
                                    <td>{{ $residente->ocupacionActiva?->cama?->habitacion?->codigo ?? 'Sin habitación activa' }}@if($residente->ocupacionActiva?->cama?->codigo) · Cama {{ $residente->ocupacionActiva->cama->codigo }}@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $residentes->links() }}
            @endif
        </section>
    </div>
</x-sistema-layout>
