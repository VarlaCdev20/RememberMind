@extends('pages.usuarios.documentos.layout')

@section('content')
 @foreach($vistas_paquete as $index => $vista)
 <div class="document-container">
 @include($vista)
 </div>
 @if(!$loop->last)
 <div class="page-break"></div>
 @endif
 @endforeach
@endsection