<?php

namespace App\Modulos\Clinica\Aplicacion\DTOs;

use App\Models\Alerta;
use App\Models\SignoVital;
use App\Modulos\Clinica\Dominio\ObjetosValor\EvaluacionSignosVitales;

final readonly class RegistroSignosVitales
{
    public function __construct(
        public SignoVital $signo,
        public EvaluacionSignosVitales $evaluacion,
        public ?Alerta $alerta,
    ) {}
}
