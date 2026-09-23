<x-sistema-layout>
    <div class="mx-auto max-w-6xl space-y-5 p-4 sm:p-6">
        <x-ui.page-header titulo="Notas y evolución"
            :subtitulo="$adulto_mayor->nombres.' '.$adulto_mayor->ap_paterno" />
        <x-residentes.navegacion-ficha :adulto="$adulto_mayor" />

        <x-validation-errors />
        @if (session('success'))
            <p role="status" class="rounded-lg border border-borde bg-fondo-panel p-3 text-parrafo">
                {{ session('success') }}
            </p>
        @endif

        <form method="GET" class="flex flex-wrap items-end gap-3">
            <label class="flex-1 text-sm text-parrafo">
                Buscar en notas y evolución
                <x-input class="block w-full" name="buscar" value="{{ request('buscar') }}" />
            </label>
            <x-button>Buscar</x-button>
        </form>

        @include('pages.adultos-mayores.observaciones.partials.crear')

        @forelse ($observaciones as $observacion)
            @include('pages.adultos-mayores.observaciones.partials.registro')
        @empty
            <x-ui.empty-state titulo="Sin resultados" texto="Registro, consulta y corrección de notas del residente. No hay registros que coincidan con la búsqueda." />
        @endforelse

        {{ $observaciones->links() }}
    </div>
</x-sistema-layout>
