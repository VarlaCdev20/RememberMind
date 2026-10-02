<x-sistema-layout>
<div class="rm-admin-page">
    <a class="rm-admin-page__link" href="{{ route('admin.administracion.residentes') }}"><i class="ph-bold ph-arrow-left" aria-hidden="true"></i> Volver a residentes</a>
    <x-ui.card class="rm-admin-page__heading rm-admin-resident__hero">
        <div class="rm-admin-resident__avatar" aria-hidden="true">
            @if($persona->foto)<img src="{{ asset('storage/'.$persona->foto) }}" alt="">@else{{ mb_strtoupper(mb_substr($persona->nombres, 0, 1).mb_substr($persona->apellido_paterno, 0, 1)) }}@endif
        </div>
        <div>
            <p class="rm-caption">Residente · {{ $persona->cod_residente }}</p>
            <h1 class="rm-heading-page">{{ trim($persona->nombres.' '.$persona->apellido_paterno.' '.$persona->apellido_materno) }}</h1>
            <p class="rm-body-sm">{{ $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->age.' años · ' : '' }}Documento: {{ $persona->numero_documento ?: 'Sin registrar' }}</p>
            <x-ui.status-badge :estado="$persona->estado" />
        </div>
    </x-ui.card>
    <div class="rm-admin-resident__facts">
        <x-ui.card><x-ui.section-header title="Ubicación actual" icon="ph-bed" level="2" /><p>{{ $ocupacion ? 'Habitación '.$ocupacion->habitacion.' · Cama '.$ocupacion->cama : 'Sin ocupación vigente' }}</p></x-ui.card>
        <x-ui.card><x-ui.section-header title="Responsable principal" icon="ph-address-book" level="2" /><p>{{ $responsable ? $responsable->nombres.' '.$responsable->apellido_paterno : 'Sin responsable registrado' }}</p>@if($responsable)<small>{{ $responsable->parentesco }} · {{ $responsable->celular ?: $responsable->telefono ?: 'Sin teléfono' }}</small>@endif</x-ui.card>
    </div>
    <nav class="rm-admin-resident__tabs" aria-label="Secciones administrativas del residente">
        @foreach($tabs as $clave => $etiqueta)
            <a href="{{ route('admin.administracion.residentes.show', [$persona->cod_residente, 'tab' => $clave]) }}" @if($tab === $clave) aria-current="page" @endif>{{ $etiqueta }}</a>
        @endforeach
    </nav>
    <x-ui.card class="rm-admin-page__table-card">
        <x-ui.section-header :title="$tabs[$tab]" icon="ph-folder-open" level="2" />
        @if($tab === 'resumen')
            <p class="rm-body-sm">Consulta de la situación residencial y los vínculos administrativos vigentes.</p>
            <dl class="rm-admin-resident__summary">
                <div><dt>Estado</dt><dd>{{ $persona->estado }}</dd></div>
                <div><dt>Habitación y cama</dt><dd>{{ $ocupacion ? $ocupacion->habitacion.' / '.$ocupacion->cama : 'Sin asignación vigente' }}</dd></div>
                <div><dt>Responsable</dt><dd>{{ $responsable ? $responsable->nombres.' '.$responsable->apellido_paterno : 'Sin registrar' }}</dd></div>
                <div><dt>Fecha de nacimiento</dt><dd>{{ $persona->fecha_nacimiento ? \Carbon\Carbon::parse($persona->fecha_nacimiento)->format('d/m/Y') : 'Sin registrar' }}</dd></div>
            </dl>
        @elseif($filas->isEmpty())
            <x-ui.empty-state icono="ph-folder-open" :titulo="'Sin registros de '.mb_strtolower($tabs[$tab])" texto="No hay información administrativa en esta sección." />
        @else
            <div class="rm-admin-page__table-scroll"><table class="rm-data-table rm-table">
                <thead><tr><th scope="col">Registro</th><th scope="col">Detalle</th><th scope="col">Fecha</th><th scope="col">Estado</th></tr></thead>
                <tbody>
                    @foreach($filas as $fila)
                        <tr>
                            <td data-label="Registro"><strong>{{ $fila['titulo'] }}</strong></td>
                            <td data-label="Detalle">{{ $fila['detalle'] ?: '—' }}</td>
                            <td data-label="Fecha">{{ $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y H:i') : '—' }}</td>
                            <td data-label="Estado"><x-ui.status-badge :estado="$fila['estado']" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </x-ui.card>
</div>
</x-sistema-layout>
