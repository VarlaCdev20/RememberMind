@extends('pages.usuarios.documentos.layout')
@section('content')
 <div class="document-container">
 @include('pages.usuarios.documentos.partials.encabezado-documento', ['titulo' => 'Compromiso de Uso Responsable del Sistema'])
 @include('pages.usuarios.documentos.partials.datos-usuario')
 <div style="margin-top: 25px; padding: 0 10px;">
 <p class="text-justify">Mediante la suscripción del presente documento, el usuario asume la responsabilidad total y absoluta sobre el manejo ético y seguro de sus credenciales de acceso al sistema integral del <strong>Centro Geriátrico Jardín de los Recuerdos</strong>.</p><ul><li>La cuenta de usuario y contraseña son de carácter <strong>estrictamente personal e intransferible</strong>.</li><li>El usuario asume la autoría y responsabilidad legal de todas las acciones, registros y modificaciones efectuadas bajo su cuenta en el sistema.</li><li>Queda terminantemente prohibido el uso indebido, la alteración no autorizada de la información clínica/social o el intento de vulnerar los niveles de acceso del sistema.</li><li>Debe reportar de inmediato a la administración cualquier sospecha de acceso no autorizado a su cuenta.</li></ul>
 </div>
 @include('pages.usuarios.documentos.partials.firmas')
 </div>
@endsection