<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\dcl;

/** Ensayo segregado de cinco dominios y fuentes de referencia ficticias. */
final class IntegradorReferenciasDCLExperimental
{
    public function __construct(private readonly OrquestadorCincoDominiosExperimental $orquestador) {}

    public function evaluar(array $contexto, array $tareas, string $memoria, array $referencias): array
    {
        $perfil = $this->orquestador->ejecutar($contexto, $tareas, $memoria);
        $puente = (new AdaptadorEstadosPetersenExperimental)->preparar($perfil, $referencias);
        $clasificacion = (new ClasificadorPetersenExperimental)->clasificar($puente['caso']);
        $perfil['versiones']['PUENTE_DCL'] = AdaptadorEstadosPetersenExperimental::VERSION;
        $perfil['puente_dcl'] = [
            'auditoria' => $puente['auditoria'],
            'advertencia' => $puente['advertencia'],
            'origen' => 'REFERENCIA_INDEPENDIENTE_SINTETICA',
        ];
        $perfil['clasificacion_dcl'] = $clasificacion;
        $perfil['informe_dcl'] = (new ConstructorInformePetersenExperimental)->construir($clasificacion);
        $perfil['limitacion'] = 'Estados DCL solamente desde referencias inventadas de laboratorio, nunca desde G09 o D-131.';

        return $perfil;
    }
}
