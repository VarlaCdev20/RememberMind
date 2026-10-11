<?php

namespace MotorExperto\adaptadores\entrada;

use DomainException;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM as E;
use MotorExperto\aplicacion\casos_uso\MotorExperto;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

/** Casos artificiales que ejecutan el motor real en memoria, sin leer residentes ni persistir. */
final class DemostracionCOGMEM
{
    public const CASOS = [
        'dificultad' => 'Soporte de dificultad',
        'preservacion' => 'Soporte sin dificultad evidenciada',
        'mixto' => 'Soportes opuestos independientes',
        'contexto' => 'Contexto insuficiente',
        'cobertura' => 'Sin soporte para una regla',
    ];

    public function ejecutar(string $caso): array
    {
        if (! array_key_exists($caso, self::CASOS)) {
            throw new DomainException('Caso técnico desconocido.');
        }
        $p = E::paquete();
        $m = new MemoriaTrabajo(E::contexto());
        $literales = match ($caso) {
            'preservacion' => ['literal_B'], 'mixto' => ['literal_A', 'literal_B'],
            'cobertura' => ['literal_desconocido'], default => ['literal_A'],
        };
        foreach ($literales as $i => $literal) {
            $e = (new AdaptadorHechosCOGMEM)->adaptar($p, $m, E::fuente($literal, registro: 'APP_DEMO_'.$i), 'MAP_INS');
            $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        }
        $contexto = E::contextoClinico();
        if ($caso === 'contexto') {
            $contexto['confusores_revisados'] = false;
        }
        // Para cobertura EV2 se usa un valor mapeado sin condición correspondiente.
        if ($caso === 'cobertura') {
            $t = E::tablas();
            $t['condiciones_regla_experta'][0]['cod_variable_experta'] = 'VAR_OBS';
            $t['condiciones_regla_experta'][1]['cod_variable_experta'] = 'VAR_OBS';
            $p = E::paquete($t);
            $m = new MemoriaTrabajo(E::contexto());
            $e = (new AdaptadorHechosCOGMEM)->adaptar($p, $m, E::fuente(), 'MAP_INS');
            $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        }

        return (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', $contexto);
    }
}
