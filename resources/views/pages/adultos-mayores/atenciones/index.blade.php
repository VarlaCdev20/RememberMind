<x-sistema-layout>
    <div class="mx-auto max-w-6xl space-y-5 p-4 sm:p-6">
        <x-ui.page-header titulo="Atenciones"
            :subtitulo="$adulto_mayor->nombres.' '.$adulto_mayor->ap_paterno" />
        <x-residentes.navegacion-ficha :adulto="$adulto_mayor" />

        <x-validation-errors />
        @if (session('success'))
            <p role="status" class="rounded-lg border border-borde bg-fondo-panel p-3 text-parrafo">
                {{ session('success') }}
            </p>
        @endif

        <form method="GET" class="rm-filter-bar">
            <div class="grid items-end gap-3 sm:grid-cols-[1fr_auto]">
                <label class="text-sm text-parrafo">
                    Buscar en atenciones
                    <x-input class="block w-full" name="buscar" value="{{ request('buscar') }}" />
                </label>
                <x-button>Buscar</x-button>
            </div>
        </form>

        @include('pages.adultos-mayores.atenciones.partials.crear')

        @forelse ($atenciones as $atencion)
            @include('pages.adultos-mayores.atenciones.partials.registro')
        @empty
            <x-ui.empty-state titulo="Sin resultados" texto="Registro e historial de atenciones del residente. No hay registros que coincidan con la búsqueda." />
        @endforelse

        {{ $atenciones->links() }}
    </div>
</x-sistema-layout>
