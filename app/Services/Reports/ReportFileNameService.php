<?php

namespace App\Services\Reports;

use Illuminate\Support\Str;

class ReportFileNameService
{
    /**
     * Genera un nombre de archivo normalizado y limpio con fecha actual.
     *
     * @param string $prefix Prefijo descriptivo del reporte (ej: áreas_institucionales)
     * @param string $extension Extensión del archivo (ej: pdf, xlsx, csv)
     * @return string Nombre del archivo formateado
     */
    public function generate(string $prefix, string $extension): string
    {
        $slug = Str::slug(Str::lower($prefix), '_');
        $date = now()->format('Y_m_d');
        $cleanExt = ltrim(Str::lower($extension), '.');

        return "reporte_{$slug}_{$date}.{$cleanExt}";
    }
}
