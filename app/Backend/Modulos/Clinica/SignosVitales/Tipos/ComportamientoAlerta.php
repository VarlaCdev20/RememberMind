<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Tipos;

enum ComportamientoAlerta: string
{
    case NINGUNA = 'NINGUNA';
    case SUGERIR = 'SUGERIR';
    case AUTOMATICA_AL_CONFIRMAR = 'AUTOMATICA_AL_CONFIRMAR';
}
