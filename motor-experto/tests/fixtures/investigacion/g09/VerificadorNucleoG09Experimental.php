<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglasCognitivas;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;

/**
 * Verifica las conclusiones G09 con el núcleo D-130 real (EvaluadorReglas,
 * MemoriaTrabajo, red semántica y PaqueteConocimiento).
 *
 * TRABAJA SOLO CON TRAZAS DE PRUEBA: nunca persiste hechos ni autoriza
 * conocimiento G08. No es una segunda validación clínica independiente.
 */
final class VerificadorNucleoG09Experimental
{
    public function cotejar(array $resultado, ?array $contextoOriginal = null): array
    {
        if (($resultado['uso'] ?? null) !== 'PRUEBA_TECNICA_NO_CLINICA'
            || ($resultado['version'] ?? null) !== CatalogoReglasG09Experimental::VERSION
            || ! in_array($resultado['criterio'] ?? null, ['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], true)) {
            throw new DomainException('No se admite una inferencia fuera del ensayo G09.');
        }
        if ($contextoOriginal !== null) {
            (new EvaluadorG09Experimental)->validarResultadoFuente($contextoOriginal, $resultado);
        }
        $paquete = PaqueteSemanticoG09Experimental::crear();
        if (! $paquete->soloPruebasTecnicas) {
            throw new DomainException('Paquete semántico no segregado.');
        }
        $dominio = substr($resultado['criterio'], 4);
        $criterio = 'CRIT-'.$dominio;
        $corte = $contextoOriginal['fecha_corte'] ?? '2026-10-17';
        $c = new ContextoEjecucion(
            $contextoOriginal['evaluacion'] ?? 'SIM-NUCLEO', $contextoOriginal['residente_ficticio'] ?? 'SIM-SUJETO',
            CatalogoReglasG09Experimental::VERSION,
            new DateTimeImmutable(strlen($corte) === 10 ? $corte.'T23:59:59-04:00' : $corte.'-04:00')
        );
        $memoria = new MemoriaTrabajo($c);
        $evidencias = 0;
        foreach ($resultado['traza'] ?? [] as $t) {
            $regla = $t['regla'] ?? null;
            if (! is_string($regla) || CatalogoReglasG09Experimental::obtener($regla)['criterio'] !== $resultado['criterio']
                || ! is_string($t['evidencia'] ?? null) || $t['evidencia'] === '') {
                throw new DomainException('Traza G09 ajena o incompleta.');
            }
            $condiciones = $t['condiciones'] ?? null;
            if (! is_array($condiciones) || array_keys($condiciones) !== ['C1', 'C2', 'C3']) {
                throw new DomainException('Condiciones de la traza no conformes con el contrato.');
            }
            $cumple = true;
            foreach (['C1', 'C2', 'C3'] as $cond) {
                if ($condiciones[$cond] !== null && ! is_bool($condiciones[$cond])) {
                    throw new DomainException('Condición técnica no booleana.');
                }
                $cumple = $cumple && $condiciones[$cond] === true;
                if ($condiciones[$cond] !== true) {
                    continue;
                }
                $variable = 'VAR-'.$regla.'-'.$cond;
                $fuente = new FuenteBrutaCOGMEM(
                    'SIM-G08', 'aplicaciones_instrumento',
                    $cond, 'SIM-REG-'.$t['evidencia'].'-'.$cond,
                    $c->residente, $c->fechaCorte,
                    'SIM_TRUE', 'SIM_TRUE', 'SIM-PERSONAL', true, true,
                    contexto: ['unidad_soporte' => $t['evidencia']]
                );
                $id = 'SIM-EV-'.substr(hash('sha256', serialize([$regla, $t['evidencia'], $cond])), 0, 20);
                $e = new EvidenciaExperta(
                    $id, $c, $fuente, 'SIM-MAP-'.$variable,
                    'SIM-MV-'.$variable, $variable, 'VAL-SIM-TRUE',
                    'MAPEADO', 'ADMISIBLE', []
                );
                $memoria->incorporar($e);
                $memoria->vincular($criterio, $id, 'INSTRUMENTAL_SIMULADO');
                $evidencias++;
            }
            if (($t['cumple'] ?? null) !== $cumple) {
                throw new DomainException('La traza afirma una conjunción que sus condiciones contradicen.');
            }
        }

        $reglas = (new EvaluadorReglasCognitivas)->evaluar($paquete, $memoria, $criterio);
        $delNucleo = [];
        foreach ($reglas as $r) {
            if ($r['estado'] !== 'CUMPLE') {
                continue;
            }
            $val = $paquete->fila('valores_semanticos', $r['consecuencia']['cod_valor_semantico']);
            $delNucleo[$val['codigo_valor']] = true;
        }
        $delG09 = [];
        foreach (array_keys($resultado['consecuencias_candidatas'] ?? []) as $codigo) {
            $delG09[$codigo] = true;
        }
        ksort($delNucleo);
        ksort($delG09);
        // Si se documentaron contradicciones, G09 opta por abstención. En ese
        // caso NO se comparan conclusiones como si fuesen equivalentes.
        if (($resultado['estado_tecnico'] ?? '') === 'EVIDENCIA_CONTRADICTORIA') {
            return [
                'coincidencia' => 'NO_COMPARABLE_POR_CONTRADICCION',
                'criterio' => $resultado['criterio'],
                'reglas_nucleo' => count($reglas),
                'condiciones_positivas_sinteticas' => $evidencias,
                'consecuencias_nucleo' => array_keys($delNucleo),
            ];
        }
        if ($delNucleo !== $delG09) {
            throw new DomainException('El núcleo D-130 y G09-EXP discrepan sobre las reglas satisfechas.');
        }

        return [
            'coincidencia' => 'COINCIDE',
            'criterio' => $resultado['criterio'],
            'reglas_nucleo' => count($reglas),
            'condiciones_positivas_sinteticas' => $evidencias,
            'consecuencias_nucleo' => array_keys($delNucleo),
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'contexto_cotejo' => ['evaluacion' => $c->evaluacion, 'caso' => $c->residente, 'fecha_corte' => $c->fechaCorte->format(DATE_ATOM),
                'contexto_original' => $contextoOriginal !== null, 'origen' => 'REPRESENTACION_SINTETICA_PARA_COTEJO'],
        ];
    }
}
