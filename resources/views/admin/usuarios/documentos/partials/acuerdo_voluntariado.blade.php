<h2 class="text-center font-bold mb-4">ACUERDO DE VOLUNTARIADO</h2>

<p class="text-right font-bold mb-4">Fecha: {{ $fecha_actual }}</p>

<p class="mb-4" style="text-align: justify;">
 Yo, <span class="font-bold uppercase">{{ $nombre_completo }}</span>, identificado(a) con CI <span class="font-bold">{{ $ci_expedido }}</span>, manifiesto mi voluntad libre y espontánea de prestar servicios como voluntario en el CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS, en el área de <span class="font-bold uppercase">{{ $area_cargo }}</span>, y acepto las siguientes condiciones:
</p>

<ol style="text-align: justify; padding-left: 20px;">
 <li class="mb-2">El servicio que presto es de carácter estrictamente altruista, solidario y no remunerado.</li>
 <li class="mb-2">Me comprometo a cumplir con el horario y actividades acordadas previamente con la coordinación de voluntarios.</li>
 <li class="mb-2">Trataré en todo momento con respeto, dignidad y empatía a los residentes adultos mayores, al personal de la institución y a los familiares.</li>
 <li class="mb-2">Respetaré las normas de bioseguridad y las directrices operativas dictadas por la administración del centro.</li>
 <li class="mb-2">Entiendo que la institución puede dar por finalizado este acuerdo si considera que mis acciones no se alinean con los valores del Geriátrico.</li>
</ol>

<div class="text-center" style="margin-top: 80px;">
 <div style="width: 250px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
 Firma del Voluntario<br>
 {{ $nombre_completo }}<br>
 CI: {{ $ci_expedido }}
 </div>
</div>
