<h2 class="text-center font-bold mb-4">FORMULARIO DE DATOS PERSONALES</h2>

<table class="w-full">
 <tr>
 <th class="bg-gray" colspan="2">INFORMACIÓN BÁSICA</th>
 </tr>
 <tr>
 <td class="font-bold w-1/3">Nombre Completo:</td>
 <td>{{ $nombre_completo }}</td>
 </tr>
 <tr>
 <td class="font-bold">Documento de Identidad:</td>
 <td>{{ $ci_expedido }}</td>
 </tr>
 <tr>
 <td class="font-bold">Fecha de Nacimiento:</td>
 <td>{{ \Carbon\Carbon::parse($usuario->fecha_nacimiento)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($usuario->fecha_nacimiento)->age }} años)</td>
 </tr>
 <tr>
 <td class="font-bold">Género:</td>
 <td>{{ mb_strtoupper($usuario->genero, 'UTF-8') }}</td>
 </tr>
</table>

<table class="w-full">
 <tr>
 <th class="bg-gray" colspan="2">INFORMACIÓN DE CONTACTO</th>
 </tr>
 <tr>
 <td class="font-bold w-1/3">Dirección:</td>
 <td>{{ $direccion }}</td>
 </tr>
 <tr>
 <td class="font-bold">Teléfono/Celular:</td>
 <td>{{ $usuario->codigo_telefono }} {{ $telefono }}</td>
 </tr>
 <tr>
 <td class="font-bold">Correo Electrónico:</td>
 <td>{{ strtolower($correo) }}</td>
 </tr>
</table>

<table class="w-full">
 <tr>
 <th class="bg-gray" colspan="2">CONTACTO DE EMERGENCIA</th>
 </tr>
 <tr>
 <td class="font-bold w-1/3">Nombre:</td>
 <td>{{ mb_strtoupper($usuario->contacto_emergencia . ' ' . $usuario->ap_paterno_emergencia . ' ' . $usuario->ap_materno_emergencia, 'UTF-8') }}</td>
 </tr>
 <tr>
 <td class="font-bold">Parentesco:</td>
 <td>{{ mb_strtoupper($usuario->parentesco_emergencia ?? '', 'UTF-8') }}</td>
 </tr>
 <tr>
 <td class="font-bold">Celular:</td>
 <td>{{ $usuario->celular_emergencia }}</td>
 </tr>
</table>

@if(!empty($vinculos))
<table class="w-full">
 <tr>
 <th class="bg-gray" colspan="2">VÍNCULOS FAMILIARES REGISTRADOS</th>
 </tr>
 @foreach($vinculos as $v)
 <tr>
 <td class="font-bold w-1/3">Adulto Mayor:</td>
 <td>{{ $v['nombre'] }} - <span class="font-bold">Parentesco:</span> {{ $v['parentesco'] }}</td>
 </tr>
 @endforeach
</table>
@endif

<div style="margin-top: 40px; text-align: justify;">
 <p>Declaro que los datos proporcionados en este formulario son verdaderos y correctos, y autorizo al CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS a utilizarlos para fines institucionales y administrativos.</p>
</div>

<div class="text-center" style="margin-top: 80px;">
 <div style="width: 250px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
 Firma del Titular<br>
 {{ $nombre_completo }}<br>
 CI: {{ $ci_expedido }}
 </div>
</div>
