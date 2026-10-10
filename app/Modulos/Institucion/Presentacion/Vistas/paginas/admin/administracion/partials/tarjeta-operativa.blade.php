@php
    $destacados = match($modulo) {
        'jornadas' => ['fecha', 'horario', 'asignados'],
        'asignaciones' => ['dia_jornada', 'jornada', 'detalle', 'funcion', 'fecha'],
        'contactos' => ['telefono', 'vinculos', 'principal', 'emergencia'],
        'documentacion' => ['titular', 'detalle', 'fecha', 'validacion'],
        'consentimientos' => ['detalle', 'firmante', 'fecha'],
        'seguros' => ['detalle', 'plan', 'afiliacion', 'titular'],
        'actividades' => ['fecha', 'detalle', 'responsable', 'area', 'cupo', 'participantes'],
        'visitas' => ['detalle', 'programada', 'ingreso', 'salida'],
        'alertas' => ['residente', 'detalle', 'responsable', 'fecha'],
        'incidentes' => ['residente', 'fecha', 'gravedad', 'detalle', 'requiere_medico', 'requiere_derivacion'],
    };
@endphp
<article class="rm-operation-card">
    <header><span class="rm-operation-card__icon"><i class="ph-bold {{ $definicion['icono'] }}" aria-hidden="true"></i></span><div><h3>{{ $registro->titulo }}</h3><small>{{ $registro->codigo }}</small></div><x-ui.status-badge :estado="$registro->estado" /></header>
    <dl>@foreach($destacados as $campo)<div><dt>{{ $columnas[$campo] }}</dt><dd>@include('pages.admin.administracion.partials.campo-operativo',['valor'=>$registro->{$campo} ?? null])</dd></div>@endforeach</dl>
    <footer><a class="rm-btn-secondary" href="{{ $enlace(['detalle'=>$registro->codigo]) }}" data-registro="{{ $registro->codigo }}" @click.prevent="abrirFicha(@js($enlace(['detalle'=>$registro->codigo])), @js($registro->codigo), $event)" aria-label="Ver detalle de {{ $registro->titulo }}"><i class="ph-bold ph-eye" aria-hidden="true"></i> Abrir ficha <i class="ph-bold ph-arrow-up-right" aria-hidden="true"></i></a></footer>
</article>
