<?php

namespace App\Modulos\Identidad\Infraestructura\Documentos;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class GenerarDocumentoInstitucional
{
    public function ejecutar(string $tempId, string $vista, string $archivo, array $data): string
    {
        $tempPath = "personal-institucional/temp/{$tempId}";
        Storage::disk('public')->makeDirectory($tempPath);
        $filePath = "{$tempPath}/{$archivo}";
        $pdf = Pdf::loadView($vista, ['data' => $data]);
        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }
}
