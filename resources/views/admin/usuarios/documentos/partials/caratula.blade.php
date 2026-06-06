<div class="text-center" style="margin-top: 150px;">
 @php
 $logoPath = public_path('storage/imagenes/LOGO.png');
 if(!file_exists($logoPath)) {
 $logoPath = storage_path('app/public/imagenes/LOGO.png');
 }
 @endphp
 @if(file_exists($logoPath))
 <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="header-logo" alt="Logo">
 @endif
 
 <h1 style="font-size: 24px; margin-top: 50px; margin-bottom: 10px;">CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS</h1>
 <h2 style="font-size: 20px; color: #555; margin-bottom: 50px;">PAQUETE DOCUMENTAL INSTITUCIONAL</h2>
 
 <div class="border-box" style="margin: 0 auto; width: 60%; text-align: left;">
 <p class="mb-2"><span class="font-bold">NOMBRE:</span> {{ $nombre_completo }}</p>
 <p class="mb-2"><span class="font-bold">DOCUMENTO:</span> {{ $ci_expedido }}</p>
 <p class="mb-2"><span class="font-bold">ROL:</span> {{ $rol }}</p>
 <p class="mb-2"><span class="font-bold">FECHA:</span> {{ $fecha_actual }}</p>
 </div>
</div>
