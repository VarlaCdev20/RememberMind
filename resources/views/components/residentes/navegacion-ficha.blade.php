@props(['adulto'])

@php
    $codigo = data_get($adulto, 'cod_am');
    $enlace = static fn (string $nombre, array|string $parametros = []) => [
        'href' => route($nombre, $parametros),
        'activo' => request()->routeIs($nombre),
    ];
    $principal = $codigo ? $enlace('admin.adultos-mayores.show', $codigo) : null;
@endphp

@if($principal)
<nav aria-label="Navegación unificada del residente" class="rounded-2xl border border-borde bg-fondo-card p-2 shadow-sm">
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="{{ $principal['href'] }}" @class(['rm-patient-nav-link', 'is-active' => $principal['activo']])
           aria-current="{{ $principal['activo'] ? 'page' : 'false' }}">
            <i class="ph-bold ph-identification-card"></i> Expediente 360°
        </a>

        @can('enfermeria.ver_ficha_paciente')
            @php $ficha360 = $enlace('admin.enfermeria.pacientes.ficha', $codigo); @endphp
            <a href="{{ $ficha360['href'] }}" @class(['rm-patient-nav-link', 'is-active' => $ficha360['activo']])>
                <i class="ph-bold ph-first-aid-kit"></i> Atención de Enfermería
            </a>
        @endcan

        @can('salud.ver')
            @foreach([
                ['ficha', 'ph-file-medical', 'Ficha clínica'], ['signos', 'ph-heartbeat', 'Signos vitales'],
                ['medicacion', 'ph-pill', 'Medicamentos'], ['administracion', 'ph-check-circle', 'Administraciones'],
                ['valoracion', 'ph-clipboard-text', 'Valoración funcional'], ['evaluaciones-geriatricas', 'ph-brain', 'VGI'],
            ] as [$ruta, $icono, $texto])
                @php $item = $enlace('admin.salud-seguimiento.'.$ruta, $codigo); @endphp
                <a href="{{ $item['href'] }}" @class(['rm-patient-nav-link', 'is-active' => $item['activo']])>
                    <i class="ph-bold {{ $icono }}"></i> {{ $texto }}
                </a>
            @endforeach
        @endcan

        @can('adultos.ver')
            @foreach([
                ['atenciones', 'ph-stethoscope', 'Atenciones'], ['observaciones', 'ph-note-pencil', 'Notas y evolución'],
                ['documentos', 'ph-folder-open', 'Documentos'],
            ] as [$ruta, $icono, $texto])
                @php $item = $enlace('admin.adultos-mayores.'.$ruta.'.index', $codigo); @endphp
                <a href="{{ $item['href'] }}" @class(['rm-patient-nav-link', 'is-active' => $item['activo']])>
                    <i class="ph-bold {{ $icono }}"></i> {{ $texto }}
                </a>
            @endforeach
        @endcan

        @can('familiares.ver')
            @php $red = $enlace('admin.familia-social.red-apoyo', ['adulto' => $codigo]); @endphp
            <a href="{{ $red['href'] }}" @class(['rm-patient-nav-link', 'is-active' => $red['activo']])>
                <i class="ph-bold ph-users-three"></i> Red de apoyo
            </a>
        @endcan
    </div>
</nav>
@endif
