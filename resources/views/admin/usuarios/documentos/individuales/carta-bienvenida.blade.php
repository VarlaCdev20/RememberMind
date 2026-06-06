@extends('admin.usuarios.documentos.layout')
@section('content')
 <div class="document-container">
 @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Carta de Bienvenida Institucional'])
 @include('admin.usuarios.documentos.partials.datos-usuario')
 <div style="margin-top: 25px; padding: 0 10px;">
 <p class="text-justify">Estimado/a <strong style="color: #E27D60; font-size: 16px;">{{ $nombre_completo }}</strong>:</p><p class="text-justify">A nombre de todo el equipo de trabajo y la directiva del <strong>Centro Geriátrico Jardín de los Recuerdos</strong>, le damos la más cordial y cálida bienvenida a nuestra institución.</p><p class="text-justify">Apreciamos profundamente su vinculación a nuestra comunidad como <strong>{{ $rol }}</strong> y confiamos plenamente en que nuestra colaboración será fructífera, ética y, sobre todo, altamente beneficiosa para la calidad de vida y el bienestar de nuestros adultos mayores residentes.</p><p class="text-justify">Quedamos a su entera disposición para cualquier consulta, apoyo institucional o requerimiento que necesite para el excelente desarrollo de su labor.</p>
 </div>
 @include('admin.usuarios.documentos.partials.firmas')
 </div>
@endsection