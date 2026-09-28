<?php

namespace App\Exports\Reportes;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteInstitucionalExport implements WithMultipleSheets
{
    public function __construct(protected array $datos) {}

    public function sheets(): array
    {
        return [
            new Sheets\ResumenGeneralSheet($this->datos),
            new Sheets\SaludSheet($this->datos),
            new Sheets\FamiliaresSheet($this->datos),
            new Sheets\EquipoSheet($this->datos),
            new Sheets\BitacoraSheet($this->datos),
        ];
    }
}
