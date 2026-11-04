<x-sistema-layout>
<div class="rm-management-workspace space-y-4">
    <x-ui.collection-header :title="$persona->nombre_completo" subtitle="Ficha administrativa del residente" icon="ph-user"><x-slot:actions><a class="rm-btn-secondary" href="{{ route('admin.residentes.index') }}">Volver a residentes</a></x-slot:actions></x-ui.collection-header>
    <nav class="rm-management-tabs" aria-label="Secciones autorizadas de la ficha">@foreach($tabs as $clave=>$nombre)<a href="{{ route('admin.residentes.show', [$persona, 'tab'=>$clave]) }}" class="{{ $tab === $clave ? 'rm-btn-primary' : 'rm-btn-secondary' }}" @if($tab === $clave) aria-current="page" @endif>{{ $nombre }}</a>@endforeach</nav>
    <x-ui.section-card :title="$tabs[$tab]" icon="ph-identification-card">
        @if($tab === 'resumen')
        <dl class="rm-management-context"><div><dt>Nombre</dt><dd>{{ $persona->nombre_completo }}</dd></div><div><dt>Edad</dt><dd>{{ $persona->fecha_nacimiento ? $persona->fecha_nacimiento->age.' años' : 'Sin registrar' }}</dd></div><div><dt>Estado administrativo</dt><dd>{{ str_replace('_', ' ', $persona->estado) }}</dd></div></dl>
        <p class="rm-help">El estado administrativo no describe el estado de salud. Las actuaciones clínicas corresponden a sus profesionales autorizados.</p>
        @elseif($filas->isEmpty())<x-ui.empty-state title="Sin registros en esta sección" description="No hay registros disponibles para esta consulta autorizada." />
        @else<div class="rm-table-scroll"><table class="rm-table"><caption class="sr-only">{{ $tabs[$tab] }} del residente</caption><thead><tr><th scope="col">Registro</th><th scope="col">Detalle</th><th scope="col">Fecha</th><th scope="col">Estado</th></tr></thead><tbody>@foreach($filas as $fila)<tr><th scope="row">{{ $fila['titulo'] }}</th><td>{{ $fila['detalle'] ?: 'Sin detalle registrado' }}</td><td>{{ $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y H:i') : 'No consta' }}</td><td>{{ str_replace('_', ' ', $fila['estado'] ?? 'Sin registrar') }}</td></tr>@endforeach</tbody></table></div>@endif
    </x-ui.section-card>
</div>
</x-sistema-layout>
