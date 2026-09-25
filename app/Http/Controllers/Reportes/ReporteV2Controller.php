<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteV2Controller extends Controller
{
    public function residentePdf(Request $request, Residente $residente)
    {
        $this->authorize('view', $residente);
        $residente->load(['admisiones', 'vinculosContacto.contacto', 'ocupacionActiva.cama.habitacion', 'atenciones.notas', 'prescripciones.medicamento']);
        activity('Reportes')->causedBy($request->user())->performedOn($residente)->log('Expediente PDF generado.');
        return Pdf::loadView('reportes.residente', compact('residente'))->download('expediente-'.$residente->cod_residente.'.pdf');
    }

    public function residentesCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', Residente::class);
        return response()->streamDownload(function (): void {
            $salida = fopen('php://output', 'wb');
            fputcsv($salida, ['Código', 'Nombres', 'Apellido paterno', 'Apellido materno', 'Documento', 'Estado']);
            Residente::query()->orderBy('apellido_paterno')->chunk(200, function ($residentes) use ($salida): void {
                foreach ($residentes as $residente) {
                    fputcsv($salida, [$residente->cod_residente, $residente->nombres, $residente->apellido_paterno, $residente->apellido_materno, $residente->numero_documento, $residente->estado]);
                }
            });
            fclose($salida);
        }, 'residentes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
