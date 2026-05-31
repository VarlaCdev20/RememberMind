<h2 class="text-center font-bold mb-4">CONSTANCIA DE RECEPCIÓN DE REGLAMENTO INTERNO</h2>

<p class="text-right font-bold mb-4">Fecha: {{ $fecha_actual }}</p>

<p class="mb-4" style="text-align: justify;">
    Por el presente documento, yo, <span class="font-bold uppercase">{{ $nombre_completo }}</span>, con documento de identidad <span class="font-bold">{{ $ci_expedido }}</span>, 
    trabajador en el cargo de <span class="font-bold uppercase">{{ $area_cargo }}</span> bajo el rol de <span class="font-bold uppercase">{{ $rol }}</span>, dejo constancia expresa de:
</p>

<ol style="text-align: justify; padding-left: 20px;">
    <li class="mb-2">Haber recibido una copia del Reglamento Interno de Trabajo del CENTRO GERIÁTRICO JARDÍN DE LOS RECUERDOS.</li>
    <li class="mb-2">Haber leído y comprendido a cabalidad las normas, políticas, procedimientos y directrices institucionales estipuladas en dicho documento.</li>
    <li class="mb-2">Comprometerme a respetar y cumplir estrictamente con todas las disposiciones vigentes en el Reglamento Interno durante el desempeño de mis labores.</li>
    <li class="mb-2">Entender que el incumplimiento de las normas institucionales podrá derivar en sanciones administrativas, disciplinarias o la recisión del contrato, según corresponda la gravedad de la falta.</li>
</ol>

<div class="text-center" style="margin-top: 80px;">
    <div style="width: 250px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
        Firma del Trabajador<br>
        {{ $nombre_completo }}<br>
        CI: {{ $ci_expedido }}
    </div>
</div>
