<?php

namespace MotorExperto\adaptadores\entrada;

use MotorExperto\dominio\conocimiento\AdmisibilidadFuenteCOGMEM;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;

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
