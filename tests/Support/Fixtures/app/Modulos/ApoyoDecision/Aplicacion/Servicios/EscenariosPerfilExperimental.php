<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Servicios;

use DomainException;
use MotorExperto\investigacion\dcl\CasoSinteticoCincoDominios;

/** Entradas artificiales compartidas por consola y pantalla; nunca contiene salidas esperadas. */
final class EscenariosPerfilExperimental
{
    public const NOMBRES = [
        'sin-referencias' => 'Sin referencias independientes',
        'memoria' => 'Memoria',
        'memoria-atencion' => 'Memoria y atención',
        'ejecutivo' => 'Función ejecutiva',
        'atencion-ejecutivo' => 'Atención y función ejecutiva',
        'incompleto' => 'Información incompleta',
        'contradictorio' => 'Evidencia contradictoria',
        'diferencial' => 'Evaluación diferencial pendiente',
        'iadl-incompleta' => 'Autonomía IADL incompleta',
        'sin-hallazgos' => 'Sin hallazgos en las tareas sintéticas',
        'no-interpretable' => 'Evaluación no interpretable',
    ];

    public static function entrada(string $escenario): array
    {
        if (! isset(self::NOMBRES[$escenario])) {
            throw new DomainException('Seleccione un caso disponible del laboratorio.');
        }
        $configuraciones = ['sin-referencias' => [], 'memoria' => ['A', 'P', 'P', 'P', 'P'],
            'memoria-atencion' => ['A', 'A', 'P', 'P', 'P'], 'ejecutivo' => ['P', 'P', 'A', 'P', 'P'],
            'atencion-ejecutivo' => ['P', 'A', 'A', 'P', 'P'], 'incompleto' => [], 'contradictorio' => [],
            'diferencial' => ['A', 'P', 'P', 'P', 'P'], 'iadl-incompleta' => ['A', 'P', 'P', 'P', 'P'],
            'sin-hallazgos' => ['P', 'P', 'P', 'P', 'P'], 'no-interpretable' => []];
        $estados = $configuraciones[$escenario] === [] ? []
            : array_combine(['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], $configuraciones[$escenario]);
        $caso = $escenario === 'sin-hallazgos'
            ? CasoSinteticoCincoDominios::crear($estados, 'SIN_HALLAZGO_SIMULADO', 'literal_B')
            : CasoSinteticoCincoDominios::crear($estados);
        if ($escenario === 'incompleto') {
            $caso['capturas']['respuestas_instrumento'][0]['valor_texto'] = null;
            $caso['contexto']['contexto_memoria']['confusores_revisados'] = null;
        } elseif ($escenario === 'contradictorio') {
            $caso['contexto']['contextos_dominios']['COG-ATE']['contradiccion_documentada'] = true;
        } elseif ($escenario === 'diferencial') {
            $caso['referencias']['criterios']['DCL-C04']['estado'] = 'B';
        } elseif ($escenario === 'iadl-incompleta') {
            $caso['referencias']['criterios']['DCL-C03']['iadl_observadas'] = null;
        } elseif ($escenario === 'no-interpretable') {
            $caso['contexto']['confusor_invalidante'] = true;
            $caso['contexto']['contexto_memoria']['invalidacion_necesaria'] = true;
        }

        return $caso;
    }
}
