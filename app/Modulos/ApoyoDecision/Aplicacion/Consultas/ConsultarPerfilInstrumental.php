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
    public function __construct(private ConsultarConocimientoCognitivo $conocimiento, private FuenteConocimientoOrion $fuente) {}

    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): ?array
    {
        $consulta = $this->conocimiento->consultar($usuario, $residente, $corte);
        $captura = $consulta['capturas'];
        if (! $captura['disponible']) {
            return null;
        }
        $red = (new PerfilConceptualCognitivo)->construir($this->fuente->leer());

        $perfil = (new PerfilInstrumentalCognitivo)->construir($red, $captura);
        foreach ($perfil['dominios'] as $codigo => &$dominio) {
            $dominio['integracion'] = $consulta['resultado']['perfil'][$codigo] ?? null;
            foreach ($dominio['componentes'] as $id => &$componente) {
                $componente['observaciones'] = $consulta['resultado']['observaciones_por_componente'][$id] ?? [];
            }
            unset($componente);
        }
        unset($dominio);
        unset($consulta['capturas']);
        $perfil['inferencia_declarativa'] = $consulta;

        return $perfil;
    }
}
