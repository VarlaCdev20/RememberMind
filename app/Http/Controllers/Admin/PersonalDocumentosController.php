<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PersonalDocumentosController extends Controller
{
    public function generarPdf(Request $request, $docId)
    {
        $data = session('personal_registro_temp');

        if (!$data) {
            return response()->json([
                'error' => 'No hay datos temporales registrados. Complete los pasos del formulario antes de generar los documentos.'
            ], 400);
        }

        $docId = strtolower($docId);
        $views = ['contrato', 'confidencialidad', 'reglamento', 'funciones'];

        if (!in_array($docId, $views)) {
            abort(404, 'Documento no encontrado.');
        }

        // Formatear fechas
        $data['fecha_hoy'] = now()->format('d/m/Y');
        $data['anio_hoy'] = now()->format('Y');
        
        // Asignar título del documento para la cabecera
        switch ($docId) {
            case 'contrato':
                $data['titulo_doc'] = 'Contrato de Prestación de Servicios';
                break;
            case 'confidencialidad':
                $data['titulo_doc'] = 'Acuerdo de Confidencialidad y Tratamiento de Datos';
                break;
            case 'reglamento':
                $data['titulo_doc'] = 'Aceptación de Reglamento Interno y Acta de Recepción';
                break;
            case 'funciones':
                $data['titulo_doc'] = 'Formulario de Asignación Inicial de Funciones';
                break;
        }

        $pdf = Pdf::loadView("admin.personal-institucional.pdfs.{$docId}", $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("{$docId}_" . ($data['nro_documento'] ?? 'documento') . ".pdf");
    }
}
