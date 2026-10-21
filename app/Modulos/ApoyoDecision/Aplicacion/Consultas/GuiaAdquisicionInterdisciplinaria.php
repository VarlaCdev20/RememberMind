<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use LogicException;
use MotorExperto\dominio\instrumentos\CatalogoCapturaCognitiva;
use MotorExperto\dominio\instrumentos\RegistroMetodosInstrumentales;

/** Selección de adquisición propuesta; nunca acredita una aplicación ni publica una interpretación. */
final class GuiaAdquisicionInterdisciplinaria
{
    public const VERSION = 'APORTE-INTERDISCIPLINARIO-20261020';

    private const CANDIDATOS = [
        'MEM-S01' => 'HVLT-R-BO-2015-F5',
        'MEM-S02' => 'HVLT-R-BO-2015-F5',
        'MEM-S03' => 'FCSRT-ES-SABIEX-2021-APRENDIZAJE',
        'MEM-S04' => 'CERAD-RECONOCIMIENTO-CO-API-2019',
        'ATE-S01' => 'CANCELACION-A-CO-API-2019',
        'ATE-S02' => 'LC-PW-ES-SCAND-2023-EFICIENCIA',
        'ATE-S03' => 'BTA-BO-2015-NL',
        'EJE-S01' => 'STROOP-BO-2015-GOLDEN',
        'EJE-S02' => 'TMT-B-BO-2015',
        'EJE-S03' => 'DSB-ES-SABIEX-2021-LONGITUD',
        'EJE-S04' => 'TOL-F-DE-GHS-2019-24',
        'EJE-S05' => 'RAVEN-A-CO-API-2019',
        'LEN-S01' => 'BNT-BO-2015-60',
        'LEN-S02' => 'FLUIDEZ-BO-2015-FAS-ANIMALES',
        'LEN-S03' => 'TOKEN-CO-UNAL-2017-TABLAS',
        'LEN-S04' => 'TB-A-ES-NEURONORMA-2011-LENGUAJE',
        'LEN-S05' => 'TB-A-ES-NEURONORMA-2011-LENGUAJE',
        'VIS-S01' => 'JLO-ES-SABIEX-2021-30',
        'VIS-S02' => 'ROCF-BO-2015-COPIA-A',
    ];

    public function componentes(): array
    {
        $catalogo = (new CatalogoCapturaCognitiva)->componentes();
        $registro = (new RegistroMetodosInstrumentales)->metodos();
        if (array_diff(array_keys($catalogo), array_keys(self::CANDIDATOS)) !== []
            || array_diff(array_keys(self::CANDIDATOS), array_keys($catalogo)) !== []) {
            throw new LogicException('La selección de adquisición no coincide con los componentes vigentes.');
        }

        $filas = [];
        foreach (self::CANDIDATOS as $componente => $metodo) {
            $definicion = $registro[$metodo] ?? null;
            if ($definicion === null || ! in_array($componente, $definicion['componentes'] ?? [], true)) {
                throw new LogicException("El método {$metodo} no corresponde a {$componente}.");
            }
            $filas[] = [
                'componente' => $componente,
                'metodo' => $metodo,
                'instrumento' => $definicion['instrumento'],
                'identidad' => $definicion['identidad'] ?? [],
                'entradas' => array_keys($definicion['entradas'] ?? []),
                'fuente' => $definicion['fuente'],
                'edad_minima' => $definicion['edad_minima'] ?? null,
                'edad_maxima' => $definicion['edad_maxima'] ?? null,
                'escolaridad_minima' => $definicion['escolaridad_minima'] ?? null,
                'escolaridad_maxima' => $definicion['escolaridad_maxima'] ?? null,
                'edades_excluidas' => $definicion['edades_excluidas_por_conflicto'] ?? [],
            ];
        }

        return $filas;
    }
}
