<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Resultados;

use App\Models\Alerta;
use App\Models\SignoVital;

final readonly class RegistroSignosVitales
{
    public function __construct(
        public SignoVital $signo,
        public EvaluacionSignosVitales $evaluacion,
        public ?Alerta $alerta,
    ) {}
}
