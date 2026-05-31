<h2 class="text-center font-bold mb-4">COMPROMISO DE CONFIDENCIALIDAD</h2>

<p class="text-right font-bold mb-4">Fecha: {{ $fecha_actual }}</p>

<p class="mb-4">
    Yo, <span class="font-bold uppercase">{{ $nombre_completo }}</span>, con documento de identidad <span class="font-bold">{{ $ci_expedido }}</span>, 
    en calidad de <span class="font-bold uppercase">{{ $rol }}</span> en el CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS, declaro que:
</p>

<ol style="text-align: justify; padding-left: 20px;">
    <li class="mb-2">Reconozco que durante el ejercicio de mis funciones tendré acceso a información sensible, privilegiada y confidencial sobre los residentes, sus familias, el personal y el funcionamiento interno de la institución.</li>
    <li class="mb-2">Me comprometo a mantener estricta confidencialidad sobre los diagnósticos médicos, tratamientos, información financiera, y cualquier dato personal de los adultos mayores residentes.</li>
    <li class="mb-2">Entiendo que la divulgación no autorizada de información confidencial, verbal o escrita (incluyendo redes sociales o medios digitales), constituye una falta grave que puede resultar en la terminación de mi vinculación con la institución, independientemente de acciones legales que pudieran corresponder.</li>
    <li class="mb-2">Este compromiso de confidencialidad se mantiene vigente incluso después de que mi vinculación formal con la institución haya concluido.</li>
</ol>

<p class="mt-4" style="text-align: justify;">
    En conformidad con lo anterior, firmo el presente documento asumiendo todas las responsabilidades derivadas del mismo.
</p>

<div class="text-center" style="margin-top: 80px;">
    <div style="width: 250px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
        Firma<br>
        {{ $nombre_completo }}<br>
        CI: {{ $ci_expedido }}
    </div>
</div>
