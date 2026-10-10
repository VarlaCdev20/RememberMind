@extends('pages.usuarios.documentos.layout')
@section('content')
 <div class="document-container">
 @include('pages.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Checklist de Documentos Emitidos'])
 @include('pages.usuarios.documentos.partials.datos-usuario')
 <div style="margin-top: 25px; padding: 0 10px;">
 <p class="text-justify">El <strong>Centro Geriátrico Jardín de los Recuerdos</strong> ha emitido formalmente los siguientes documentos institucionales para el usuario <strong style="color: #E27D60;">{{ $nombre_completo }}</strong>:</p><div class="highlight-box"><ul>@foreach($documentos_institucionales as $doc)<li style="color: #2E5C31;">[ &nbsp; ] <strong>{{ mb_strtoupper($doc["nombre"],"UTF-8") }}</strong><br><span style="color: #6B7280; font-weight: normal; font-size: 11px;">{{ $doc["descripcion"] }}</span></li>@endforeach</ul></div>
 </div>
 @include('pages.usuarios.documentos.partials.firmas')
 </div>
@endsection