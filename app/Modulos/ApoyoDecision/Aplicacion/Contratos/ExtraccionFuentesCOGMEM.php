<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Contratos;

use App\Models\AplicacionInstrumento;
use App\Models\ControlCognitivo;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

/** Frontera Laravel–motor: no incorpora reglas ni conocimiento clínico nuevos. */
interface ExtraccionFuentesCOGMEM
{
    public function extraerControl(ControlCognitivo $control, PaqueteConocimiento $p, MemoriaTrabajo $m, array $contexto = []): array;

    public function extraerAplicacion(AplicacionInstrumento $a, PaqueteConocimiento $p, MemoriaTrabajo $m, array $componente, array $contexto = []): array;
}
