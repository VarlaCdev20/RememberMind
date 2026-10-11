<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>Paquete Documental</title>
 <style>

{!! file_get_contents(resource_path('frontend/styles/modules/pages-usuarios-documentos-paquete_documental.css')) !!}
</style>
</head>
<body>
 @foreach($vistas_paquete as $vista)
 <div class="{{ !$loop->last ? 'page-break' : '' }}">
 <!-- Se incluye el partial de cada documento, pasando las variables requeridas -->
 @include($vista)
 </div>
 @endforeach
</body>
</html>
