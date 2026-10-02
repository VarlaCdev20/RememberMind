<x-sistema-layout>
<div class="rm-admin-page">
    <x-ui.card class="rm-admin-page__heading">
        <p class="rm-caption">Administración / Búsqueda</p>
        <h1 class="rm-heading-page">Búsqueda administrativa</h1>
        <p class="rm-body-sm">Residentes, preadmisiones, admisiones, contactos, documentos y espacios residenciales.</p>
    </x-ui.card>
    <form method="GET" action="{{ route('admin.administracion.buscar') }}" class="rm-filter-bar rm-admin-page__filters" role="search">
        <div class="rm-admin-page__filter-field rm-admin-page__filter-field--search"><label for="admin-busqueda">Buscar</label><input id="admin-busqueda" type="search" name="q" value="{{ $busqueda }}" class="rm-input" minlength="2" maxlength="100" placeholder="Nombre, documento o código"></div>
        <div class="rm-admin-page__filter-actions"><button type="submit" class="rm-btn-primary">Buscar</button></div>
    </form>
    @if(mb_strlen($busqueda) < 2)
        <x-ui.empty-state icono="ph-magnifying-glass" titulo="Escribe al menos dos caracteres" texto="La búsqueda se limita a información administrativa autorizada." />
    @elseif(empty($resultados))
        <x-ui.empty-state icono="ph-magnifying-glass" titulo="Sin resultados" texto="No se encontraron registros para esta búsqueda." />
    @else
        @foreach($resultados as $tipo => $filas)
            <x-ui.card class="rm-admin-page__table-card">
                <x-ui.section-header :title="$tipo" :count="$filas->count()" icon="ph-magnifying-glass" level="2" />
                <ul class="rm-admin-page__results">
                    @foreach($filas as $fila)
                        <li><a href="{{ $fila['url'] }}"><span><strong>{{ $fila['nombre'] }}</strong><small class="rm-admin-page__code">{{ $fila['codigo'] }} · {{ $fila['detalle'] }}</small></span><i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a></li>
                    @endforeach
                </ul>
            </x-ui.card>
        @endforeach
    @endif
</div>
</x-sistema-layout>
