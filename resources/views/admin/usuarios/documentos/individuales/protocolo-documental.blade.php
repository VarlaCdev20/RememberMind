@extends('admin.usuarios.documentos.layout')
@section('content')
    <div class="document-container">
        @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Protocolo de Manejo Administrativo y Documental'])
        @include('admin.usuarios.documentos.partials.datos-usuario')
        <div style="margin-top: 25px; padding: 0 10px;">
            <p class="text-justify">Este documento establece de forma vinculante las condiciones, lineamientos y compromisos específicos aplicables al desarrollo de su rol como <strong style="color: #E27D60;">{{ mb_strtoupper($rol, 'UTF-8') }}</strong> dentro de las instalaciones y el marco normativo del <strong>Centro Geriátrico Jardín de los Recuerdos</strong>.</p>
            <p class="text-justify">Por medio del presente, el usuario declara haber leído, comprendido a cabalidad y aceptado de libre voluntad todas las directrices, protocolos y reglamentos institucionales pertinentes para el correcto ejercicio de sus actividades, comprometiéndose a velar en todo momento por el bienestar, la dignidad y la integridad de nuestros residentes adultos mayores.</p>
            
            @if(isset($vinculos) && count($vinculos) > 0)
                <div class="highlight-box">
                    <h4 style="margin-top: 0;">ADULTOS MAYORES VINCULADOS A SU RESPONSABILIDAD:</h4>
                    <ul style="margin-bottom: 0;">
                        @foreach($vinculos as $v)
                            <li style="color: #374151;"><strong>{{ $v['nombre'] }}</strong> <br><span style="color: #E27D60; font-size: 11px;">PARENTESCO / RELACIÓN: {{ $v['parentesco'] }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @include('admin.usuarios.documentos.partials.firmas')
    </div>
@endsection