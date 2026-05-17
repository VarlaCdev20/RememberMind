<?php

namespace App\Services\Reports;

use Spatie\LaravelPdf\Facades\Pdf as SpatiePdf;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class ReportExportService
{
    /**
     * Exporta un reporte en formato PDF usando Spatie PDF con fallback automático a DomPDF.
     *
     * @param string $view Nombre de la vista Blade
     * @param array $data Arreglo de datos
     * @param string $filename Nombre de descarga del archivo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportPdf(string $view, array $data, string $filename)
    {
        try {
            // Seteamos la fecha y el usuario que lo genera en todos los metadatos de reporte
            $data['fecha'] = $data['fecha'] ?? now()->format('d/m/Y H:i');
            $data['usuario'] = $data['usuario'] ?? (auth()->check() ? auth()->user()->name : 'Sistema');

            // Intentamos generar el PDF usando Spatie Laravel PDF de forma inmediata para atrapar errores de Browsershot
            $pdfContent = SpatiePdf::view($view, $data)->output();
            
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, $filename);

        } catch (\Throwable $e) {
            Log::warning("Spatie PDF falló (Browsershot/Puppeteer): {$e->getMessage()}. Iniciando fallback automático a DomPDF.");

            // Fallback a DomPDF
            $pdf = DomPdf::loadView($view, $data)
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            return $pdf->download($filename);
        }
    }

    /**
     * Exporta datos a Excel utilizando Maatwebsite Excel.
     *
     * @param mixed $exportClass Instancia de la clase Export
     * @param string $filename Nombre de descarga del archivo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportExcel($exportClass, string $filename)
    {
        return Excel::download($exportClass, $filename);
    }

    /**
     * Exporta datos a CSV utilizando Maatwebsite Excel.
     *
     * @param mixed $exportClass Instancia de la clase Export
     * @param string $filename Nombre de descarga del archivo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportCsv($exportClass, string $filename)
    {
        return Excel::download($exportClass, $filename, \Maatwebsite\Excel\Excel::CSV);
    }
}
