<?php
if (getenv('APP_ENV') !== 'testing' || getenv('RM_GERENCIA_QA_CARPETAS') !== '1') throw new RuntimeException('Fuera del ensayo.');
$carpetasPrueba = \App\Models\Documento::orderBy('nombre')->get();
foreach (['TITULO','CI','CV'] as $indiceCarpeta=>$tipoCarpeta) $carpetasPrueba[$indiceCarpeta]->update(['tipo_documento'=>$tipoCarpeta]);
$rutaImagenCarpeta = 'documentos_personal_privados/imagen-carpeta-prueba.png';
$bytesImagenCarpeta = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9N8AAAAASUVORK5CYII=');
$diskCarpeta = \Illuminate\Support\Facades\Storage::build(['driver'=>'local','root'=>$archivo.'.storage','throw'=>true]);
$diskCarpeta->put($rutaImagenCarpeta,$bytesImagenCarpeta);
\App\Models\Documento::create(['cod_usuario'=>\App\Models\User::where('correo','equipo1@example.test')->sole()->getKey(),
    'nombre'=>'Fotografía de documento de prueba','tipo_documento'=>'FOTO_DOC','tipo_archivo'=>'image/png','ruta_archivo'=>$rutaImagenCarpeta,
    'hash_archivo'=>hash('sha256',$bytesImagenCarpeta),'estado'=>'CARGADO','fecha_hora_carga'=>now(),'fecha_emision'=>today(),'cod_usuario_carga'=>$actor->getKey()]);
