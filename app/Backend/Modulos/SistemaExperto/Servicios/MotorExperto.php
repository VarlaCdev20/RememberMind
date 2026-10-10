<?php

namespace App\Backend\Modulos\SistemaExperto\Servicios;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\Evaluadores\EvaluadorEvaluabilidadCOGMEM;
use App\Backend\Modulos\SistemaExperto\Inferencia\EvaluadorReglas;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Backend\Modulos\SistemaExperto\Resolucion\ResolvedorSoportesCOGMEM;
use App\Backend\Modulos\SistemaExperto\Trazabilidad\ConstructorTraza;
use DomainException;

final class MotorExperto
{
    /** Ejecuta el piloto técnico inactivo; la integración clínica exige otro checkpoint. */
    public function evaluarCOGMEM(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio, array $contexto): array
    {
        if (! $p->soloPruebasTecnicas) {
            throw new DomainException('El conocimiento clínico no está autorizado para activación.');
        }
        if ($p->version !== $m->contexto->version || $p->red->nodo($criterio)['codigo_semantico'] !== 'COG-MEM') {
            throw new DomainException('El piloto requiere COG-MEM y la misma versión de ejecución.');
        }
        $dominio = $p->dominioCriterio($criterio);
        if ($p->red->nodo($criterio)['estado'] !== 'ACTIVO' || $dominio['estado'] !== 'ACTIVO'
            || $p->fila('dominios_valores_expertos', $dominio['cod_dominio_valores'])['estado'] !== 'ACTIVO') {
            throw new DomainException('El criterio y su dominio de resultado no están habilitados.');
        }
        foreach ($m->paraCriterio($criterio) as $e) {
            if (! $p->variableParticipa($e->variable, $criterio)) {
                throw new DomainException('Participación sin vínculo declarado en la red semántica.');
            }
        }
        $gate = (new EvaluadorEvaluabilidadCOGMEM)->evaluar($p, $m, $criterio, $contexto);
        $reglas = $gate['habilita_resultado'] ? (new EvaluadorReglas)->evaluar($p, $m, $criterio) : [];
        $resultado = (new ResolvedorSoportesCOGMEM)->resolver($p, $m, $criterio, $gate, $reglas);
        $traza = (new ConstructorTraza)->construir($p, $m, $criterio, $gate, $reglas, $resultado);

        return ['criterio' => $criterio, 'evaluabilidad' => $gate, 'resultado' => $resultado, 'traza' => $traza, 'uso' => 'PRUEBA_TECNICA'];
    }

    /** Perfil sin agregación, sin puntuación global y con criterios separados. */
    public function perfil(array $evaluaciones): array
    {
        $perfil = [];
        $version = $ejecucion = $residente = $corte = null;
        foreach ($evaluaciones as $e) {
            $v = $e['traza']['version'];
            $id = $e['traza']['evaluacion'];
            if (isset($perfil[$e['criterio']]) || ($version !== null && ($version !== $v || $ejecucion !== $id
                || $residente !== $e['traza']['residente'] || $corte !== $e['traza']['fecha_corte']))) {
                throw new DomainException('Perfil con criterios duplicados o ejecuciones/versiones diferentes.');
            }
            $version = $v;
            $ejecucion = $id;
            $residente = $e['traza']['residente'];
            $corte = $e['traza']['fecha_corte'];
            $perfil[$e['criterio']] = $e;
        }
        ksort($perfil, SORT_STRING);

        return $perfil;
    }
}
