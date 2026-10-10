<?php

namespace App\Modulos\Clinica\Dominio\Enumeraciones;

enum ComportamientoAlerta: string
{
    case NINGUNA = 'NINGUNA';
    case SUGERIR = 'SUGERIR';
    case AUTOMATICA_AL_CONFIRMAR = 'AUTOMATICA_AL_CONFIRMAR';
}
