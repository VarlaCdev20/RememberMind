@extends('admin.usuarios.documentos.layout')
@section('content')
 <div class="document-container">
 @include('admin.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Checklist de Documentos Requeridos'])
 @include('admin.usuarios.documentos.partials.datos-usuario')
 <div style="margin-top: 25px; padding: 0 10px;">
 <p class="text-justify">Para completar de forma satisfactoria su registro y expediente en el <strong>Centro Geriátrico Jardín de los Recuerdos</strong>, el usuario debe presentar de manera obligatoria los siguientes documentos:</p><div class="highlight-box"><ul>@foreach($requisitos as $req)<li>[ &nbsp; ] {{ $req }}</li>@endforeach</ul></div><p class="text-justify" style="color: #E27D60; font-weight: bold; font-size: 11px;">* La no presentación de estos documentos en los plazos establecidos podría suspender su habilitación en el sistema.</p>
 </div>
 @include('admin.usuarios.documentos.partials.firmas')
 </div>
@endsection