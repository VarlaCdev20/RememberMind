<?php

namespace MotorExperto\aplicacion\casos_uso;

use DomainException;
use MotorExperto\aplicacion\puertos\EvaluadorExperto;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglasCognitivas;
use MotorExperto\dominio\explicacion\ConstructorTraza;
use MotorExperto\dominio\inferencia\EvaluadorEvaluabilidadCOGMEM;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\inferencia\ResolvedorSoportesCOGMEM;

final class MotorExperto implements EvaluadorExperto
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
        $reglas = $gate['habilita_resultado'] ? (new EvaluadorReglasCognitivas)->evaluar($p, $m, $criterio) : [];
        $reglasRetiradas = array_filter($p->tabla('consecuencias_regla_experta'), static function ($salida) use ($p, $dominio): bool {
            $r = $p->fila('reglas_expertas', $salida['cod_regla_experta']);

            return $salida['cod_criterio_dominio_resultado'] === $dominio['cod_criterio_dominio_resultado']
                && ($r['estado'] !== 'ACTIVO' || ($r['estado_revision'] ?? null) === 'RECHAZADA');
        });
        // El retiro explícito puede dejar el criterio sin regla aplicable; no significa rendimiento conservado.
        // Sin un retiro, EV-CM-2 sin cobertura continúa siendo el error de integridad previsto por D-131.
        $sinSoporte = $gate['habilita_resultado'] && $reglasRetiradas !== [] && ! in_array('CUMPLE', array_column($reglas, 'estado'), true);
        $resultado = $sinSoporte ? null : (new ResolvedorSoportesCOGMEM)->resolver($p, $m, $criterio, $gate, $reglas);
        $traza = (new ConstructorTraza)->construir($p, $m, $criterio, $gate, $reglas, $resultado);

        return ['criterio' => $criterio, 'evaluabilidad' => $gate, 'resultado' => $resultado, 'traza' => $traza,
            'abstencion' => $sinSoporte ? 'SIN_REGLA_CON_SOPORTE' : null, 'uso' => 'PRUEBA_TECNICA'];
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
