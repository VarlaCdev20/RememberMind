<?php

namespace MotorExperto\aplicacion\puertos;

use MotorExperto\conocimiento\PaqueteConocimiento;
use MotorExperto\hechos\MemoriaTrabajo;

interface EvaluadorExperto
{
    public function evaluarCOGMEM(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio, array $contexto): array;

    public function perfil(array $evaluaciones): array;
}
