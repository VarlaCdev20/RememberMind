<?php

namespace MotorExperto\hechos;

use MotorExperto\conocimiento\AdmisibilidadFuenteCOGMEM;
use MotorExperto\conocimiento\MapeadorSemantico;
use MotorExperto\conocimiento\PaqueteConocimiento;
use MotorExperto\hechos\MemoriaTrabajo;
use MotorExperto\hechos\EvidenciaExperta;
use MotorExperto\hechos\FuenteBrutaCOGMEM;

/** Convierte la entrada y delega las invariantes cognitivas al dominio antes de incorporarla. */
final class AdaptadorHechosCOGMEM
{
    public const VARIABLES_POR_CAMPO = AdmisibilidadFuenteCOGMEM::VARIABLES_POR_CAMPO;

    public function adaptar(PaqueteConocimiento $p, MemoriaTrabajo $memoria, FuenteBrutaCOGMEM $f, string $mapeo): EvidenciaExperta
    {
        $e = (new MapeadorSemantico)->mapear($p, $memoria->contexto, $f, $mapeo);

        return $memoria->incorporar((new AdmisibilidadFuenteCOGMEM)->validar($p, $e));
    }
}
