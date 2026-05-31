<h2 class="text-center font-bold mb-4">ACUERDO DE CORRESPONSABILIDAD FAMILIAR</h2>

<p class="text-right font-bold mb-4">Fecha: {{ $fecha_actual }}</p>

<p class="mb-4" style="text-align: justify;">
    Yo, <span class="font-bold uppercase">{{ $nombre_completo }}</span>, con CI <span class="font-bold">{{ $ci_expedido }}</span>, en mi calidad de familiar responsable, suscribo el presente acuerdo con el CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS referente al cuidado y estancia de mi(s) familiar(es):
</p>

@if(!empty($vinculos))
<ul class="mb-4" style="padding-left: 20px;">
    @foreach($vinculos as $v)
        <li><span class="font-bold uppercase">{{ $v['nombre'] }}</span> (Parentesco: {{ $v['parentesco'] }})</li>
    @endforeach
</ul>
@else
<p class="text-center italic mb-4">Sin adultos mayores vinculados al momento de la impresión.</p>
@endif

<p class="font-bold mt-4 mb-2">Me comprometo formalmente a:</p>
<ol style="text-align: justify; padding-left: 20px;">
    <li class="mb-2">Proveer oportunamente los medicamentos, insumos de higiene personal y vestimenta requerida para el bienestar de mi familiar.</li>
    <li class="mb-2">Cumplir puntualmente con los aportes y obligaciones económicas acordadas con la administración del centro.</li>
    <li class="mb-2">Asistir a las reuniones convocadas por el equipo interdisciplinario (médico, psicológico y social) para tratar asuntos relacionados con la evolución y el cuidado de mi familiar.</li>
    <li class="mb-2">Mantener visitas periódicas y contacto constante, entendiendo que el apoyo afectivo familiar es indispensable para la salud integral del adulto mayor.</li>
    <li class="mb-2">Respetar los horarios de visita, normativas de bioseguridad y el reglamento interno de la institución.</li>
</ol>

<div class="text-center" style="margin-top: 80px;">
    <div style="width: 250px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
        Firma del Familiar Responsable<br>
        {{ $nombre_completo }}<br>
        CI: {{ $ci_expedido }}
    </div>
</div>
