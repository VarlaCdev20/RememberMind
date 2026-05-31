<h2 class="text-center font-bold mb-4">CHECKLIST DE RECEPCIÓN DOCUMENTAL</h2>

<p class="text-right font-bold mb-4">Fecha: {{ $fecha_actual }}</p>

<p class="mb-4" style="text-align: justify;">
    El siguiente documento constituye el listado oficial de los requisitos que el usuario <span class="font-bold uppercase">{{ $nombre_completo }}</span> debe entregar a la administración para completar su expediente en calidad de <span class="font-bold uppercase">{{ $rol }}</span>.
</p>

<table class="w-full mt-4" style="border: 1px solid #000;">
    <thead>
        <tr class="bg-gray">
            <th style="width: 5%; text-align: center;">Nº</th>
            <th style="width: 75%;">Requisito Documental</th>
            <th style="width: 20%; text-align: center;">Entregado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($requisitos as $index => $req)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ mb_strtoupper($req, 'UTF-8') }}</td>
            <td class="text-center">
                <div style="width: 20px; height: 20px; border: 1px solid #000; display: inline-block;"></div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 50px;">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="text-align: center; border: none;">
                <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
                    Entregué Conforme<br>
                    (Firma del Usuario)
                </div>
            </td>
            <td style="text-align: center; border: none;">
                <div style="width: 200px; border-top: 1px solid #000; margin: 0 auto; padding-top: 5px;">
                    Recibí Conforme<br>
                    (Firma Administración)
                </div>
            </td>
        </tr>
    </table>
</div>
