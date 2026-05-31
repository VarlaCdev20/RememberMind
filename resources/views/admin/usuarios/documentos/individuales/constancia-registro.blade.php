@extends('admin.usuarios.documentos.layout')
@section('content')
    <div class="document-container">
        @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Constancia de Registro Institucional'])
        @include('admin.usuarios.documentos.partials.datos-usuario')
        <div style="margin-top: 25px; padding: 0 10px;">
            <p class="text-justify">Por medio del presente documento, el <strong>Centro Geriátrico Jardín de los Recuerdos</strong> certifica que el usuario <strong style="color: #E27D60;">{{ $nombre_completo }}</strong>, con C.I. <strong>{{ $ci }}</strong>, se encuentra debidamente registrado en nuestra plataforma institucional bajo el rol de <strong>{{ $rol }}</strong>.</p><p class="text-justify">Este registro habilita al usuario para participar en los procesos y actividades vinculadas a su rol, bajo estricto apego a los reglamentos y normativas vigentes en nuestra institución.</p>
        </div>
        @include('admin.usuarios.documentos.partials.firmas')
    </div>
@endsection