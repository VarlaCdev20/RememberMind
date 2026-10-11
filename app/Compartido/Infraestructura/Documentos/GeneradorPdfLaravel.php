<?php

namespace App\Compartido\Infraestructura\Documentos;

use App\Compartido\Aplicacion\Contratos\GeneradorPdf;
use Barryvdh\DomPDF\Facade\Pdf;

final class GeneradorPdfLaravel implements GeneradorPdf
{
    public function contenido(string $plantilla, array $datos): string
    {
        return Pdf::loadView($plantilla, $datos)->output();
    }
}
