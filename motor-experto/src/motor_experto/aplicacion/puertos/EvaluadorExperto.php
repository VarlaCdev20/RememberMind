<?php

namespace MotorExperto\aplicacion\puertos;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

interface EvaluadorExperto
{
    public function evaluarCOGMEM(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio, array $contexto): array;

    public function perfil(array $evaluaciones): array;
}
