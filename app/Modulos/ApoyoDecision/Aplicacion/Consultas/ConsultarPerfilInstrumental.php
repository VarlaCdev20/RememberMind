<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use Carbon\CarbonImmutable;
use MotorExperto\dominio\conocimiento\PerfilConceptualCognitivo;
use MotorExperto\dominio\instrumentos\PerfilInstrumentalCognitivo;

/** Orquesta fuentes institucionales autorizadas y la red documental; no usa casos del laboratorio. */
final readonly class ConsultarPerfilInstrumental
{
    public function __construct(private CapturasInstrumentalesResidente $capturas, private FuenteConocimientoOrion $fuente) {}

    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): ?array
    {
        $captura = $this->capturas->consultar($usuario, $residente, $corte);
        if (! $captura['disponible']) {
            return null;
        }
        $red = (new PerfilConceptualCognitivo)->construir($this->fuente->leer());

        $perfil = (new PerfilInstrumentalCognitivo)->construir($red, $captura);
        $perfil['inferencia_declarativa'] = app(ConsultarConocimientoCognitivo::class)->consultar($usuario, $residente, $corte);

        return $perfil;
    }
}
