<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Dominio\Revision;

/** Responsabilidades de revisión separadas, aprobadas por la propietaria. */
enum DisciplinaRevision: string
{
    case MEDICINA = 'MEDICINA';
    case PSICOLOGIA = 'PSICOLOGIA';

    public function rol(): string
    {
        return $this === self::MEDICINA ? 'MEDICO GENERAL/GERIATRA' : 'PSICOLOGO/A';
    }

    public function permiso(): string
    {
        return 'evaluaciones_expertas.revisar_'.strtolower($this->value);
    }
}
