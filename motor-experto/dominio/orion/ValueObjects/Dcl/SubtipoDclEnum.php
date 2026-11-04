<?php

declare(strict_types=1);

namespace MotorExperto\dominio\orion\ValueObjects\Dcl;

/** Identifica los cuatro patrones orientativos de Petersen, separados de la abstención. */
enum SubtipoDclEnum: string
{
    /** PET-01: patrón candidato amnésico de dominio único. */
    case AMNESICO_DOMINIO_UNICO = 'PET-01';

    /** PET-02: patrón candidato amnésico multidominio. */
    case AMNESICO_MULTIDOMINIO = 'PET-02';

    /** PET-03: patrón candidato no amnésico de dominio único. */
    case NO_AMNESICO_DOMINIO_UNICO = 'PET-03';

    /** PET-04: patrón candidato no amnésico multidominio. */
    case NO_AMNESICO_MULTIDOMINIO = 'PET-04';

}
