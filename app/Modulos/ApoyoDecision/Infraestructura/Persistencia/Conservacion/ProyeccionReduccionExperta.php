<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;

/** Ensayo reversible en memoria de cuatro consolidaciones. No escribe ni sustituye al almacén vigente. */
final class ProyeccionReduccionExperta
{
    public const HECHOS = 'hechos_instantanea_experta';

    public const COGNITIVAS = 'evidencias_cognitivas_instantanea';

    public const IADL = 'evidencias_iadl_instantanea';

    public const FUNCIONALIDAD = 'funcionalidad_instantanea_experta';

    public const CLASIFICACIONES = 'clasificaciones_dcl_instantanea';

    public const SINTETICOS = 'soportes_sinteticos_criterio';

    public const VINCULOS = 'evidencias_criterio_evaluacion';

    /** Columnas fijas por entidad; no es un catálogo dinámico de atributos clínicos. */
    public const COG = ['cod_evidencia_cognitiva_instantanea' => 'cod_evidencia_cognitiva_instantanea',
        'dominio' => 'cognitiva_dominio', 'subcomponente' => 'cognitiva_subcomponente', 'hallazgo' => 'cognitiva_hallazgo',
        'instrumento_id_dominio' => 'cognitiva_instrumento_id_dominio', 'justificacion' => 'cognitiva_justificacion', 'orden' => 'cognitiva_orden',
        'referencia_instrumento' => 'cognitiva_referencia_instrumento', 'referencia_edicion' => 'cognitiva_referencia_edicion',
        'referencia_subprueba' => 'cognitiva_referencia_subprueba', 'referencia_norma' => 'cognitiva_referencia_norma',
        'referencia_idioma' => 'cognitiva_referencia_idioma', 'referencia_poblacion' => 'cognitiva_referencia_poblacion',
        'referencia_revisor' => 'cognitiva_referencia_revisor'];

    public const IAD = ['cod_evidencia_iadl_instantanea' => 'cod_evidencia_iadl_instantanea',
        'cod_funcionalidad_instantanea' => 'iadl_cod_funcionalidad_instantanea', 'actividad_id_dominio' => 'iadl_actividad_id_dominio',
        'estado' => 'iadl_estado', 'oportunidad' => 'iadl_oportunidad', 'nivel_previo' => 'iadl_nivel_previo',
        'causa_ayuda' => 'iadl_causa_ayuda', 'atribucion_cognitiva_simulada' => 'iadl_atribucion_cognitiva_simulada',
        'revisor_sintetico_id' => 'iadl_revisor_sintetico_id', 'orden' => 'iadl_orden'];

    public const FUN = ['independencia_relativa' => 'funcionalidad_independencia_relativa',
        'revisor_sintetico_id' => 'funcionalidad_revisor_sintetico_id', 'justificacion' => 'funcionalidad_justificacion'];

    public const TABLAS = [self::HECHOS, self::COGNITIVAS, self::IADL, self::FUNCIONALIDAD, self::CLASIFICACIONES, self::SINTETICOS, self::VINCULOS];

    public function proyectar(array $t): array
    {
        if (array_diff(self::TABLAS, array_keys($t)) || array_diff(array_keys($t), self::TABLAS)) {
            throw new DomainException('El ensayo requiere exactamente las siete relaciones de origen.');
        }
        $h = $this->indexar($t[self::HECHOS], 'cod_hecho_instantanea');
        $f = $this->indexar($t[self::FUNCIONALIDAD], 'cod_funcionalidad_instantanea');
        $c = $this->indexar($t[self::CLASIFICACIONES], 'cod_clasificacion_dcl_instantanea');
        $porEvaluacion = $fUsadas = $evaluacionesC = [];
        foreach ($f as $fila) {
            if (isset($porEvaluacion[$fila['cod_evaluacion_experta']])) {
                throw new DomainException('La evaluación contiene más de una funcionalidad.');
            }
            $porEvaluacion[$fila['cod_evaluacion_experta']] = true;
            $this->columnas($fila, ['cod_funcionalidad_instantanea', 'cod_evaluacion_experta', ...array_keys(self::FUN)]);
        }
        foreach ($c as &$fila) {
            $original = $f[$fila['cod_funcionalidad_instantanea']] ?? throw new DomainException('Clasificación sin funcionalidad original.');
            if ($fila['cod_evaluacion_experta'] !== $original['cod_evaluacion_experta'] || isset($fUsadas[$original['cod_funcionalidad_instantanea']])
                || isset($evaluacionesC[$fila['cod_evaluacion_experta']])) {
                throw new DomainException('La clasificación cruza evaluaciones o comparte funcionalidad.');
            }
            $fUsadas[$original['cod_funcionalidad_instantanea']] = true;
            $evaluacionesC[$fila['cod_evaluacion_experta']] = true;
            $this->anexar($fila, $original, self::FUN);
        }
        unset($fila);
        if (count($fUsadas) !== count($f)) {
            throw new DomainException('Funcionalidad sin clasificación: no se reinterpreta como una nueva etapa válida.');
        }
        foreach ($h as &$fila) {
            $this->anexar($fila, [], self::COG);
            $this->anexar($fila, [], self::IAD);
        }
        unset($fila);
        foreach ([[self::COGNITIVAS, self::COG], [self::IADL, self::IAD]] as [$tabla, $mapa]) {
            $this->indexar($t[$tabla], array_key_first($mapa));
            $usadas = [];
            foreach ($t[$tabla] as $fila) {
                $this->columnas($fila, ['cod_hecho_instantanea', ...array_keys($mapa)]);
                $id = $fila['cod_hecho_instantanea'];
                if (! isset($h[$id]) || isset($usadas[$id])) {
                    throw new DomainException('Caracterización sin hecho o varias caracterizaciones del mismo tipo por hecho.');
                }
                $usadas[$id] = true;
                if ($tabla === self::IADL && (($f[$fila['cod_funcionalidad_instantanea']]['cod_evaluacion_experta'] ?? null) !== $h[$id]['cod_evaluacion_experta'])) {
                    throw new DomainException('La evidencia IADL y su hecho pertenecen a evaluaciones distintas.');
                }
                foreach ($mapa as $origen => $destino) {
                    $h[$id][$destino] = $fila[$origen];
                }
            }
        }
        $vinculos = $t[self::VINCULOS];
        $this->indexar($vinculos, 'cod_evidencia_criterio');
        foreach ($vinculos as &$fila) {
            if ($fila['cod_evidencia_evaluacion'] === null) {
                throw new DomainException('El vínculo anterior requiere evidencia institucional.');
            }
            $this->anexar($fila, [], ['cod_hecho_instantanea' => 'cod_hecho_instantanea', 'cod_soporte_sintetico' => 'cod_soporte_sintetico']);
        }
        unset($fila);
        $this->indexar($t[self::SINTETICOS], 'cod_soporte_sintetico');
        foreach ($t[self::SINTETICOS] as $fila) {
            $this->columnas($fila, ['cod_soporte_sintetico', 'cod_evaluacion_criterio', 'cod_hecho_instantanea', 'orden']);
            if (! isset($h[$fila['cod_hecho_instantanea']])) {
                throw new DomainException('Soporte sin hecho original.');
            }
            // La migración futura debe reservar una PK real mediante el generador.
            // NULL aquí identifica exclusivamente una fila proyectada, nunca persistible.
            $vinculos[] = ['cod_evidencia_criterio' => null, 'cod_evaluacion_criterio' => $fila['cod_evaluacion_criterio'],
                'cod_evidencia_evaluacion' => null, 'cod_hecho_instantanea' => $fila['cod_hecho_instantanea'],
                'cod_soporte_sintetico' => $fila['cod_soporte_sintetico'], 'rol_en_criterio' => 'SOPORTE_EXPERIMENTAL',
                'estado_participacion' => 'PRUEBA_TECNICA', 'justificacion' => null,
                'fecha_hora_vinculacion' => null, 'orden_conservacion' => $fila['orden']];
        }

        return [self::HECHOS => array_values($h), self::CLASIFICACIONES => array_values($c), self::VINCULOS => $vinculos];
    }

    /** Recupera las filas antiguas del ensayo, preservando tipos, identidades, orden y NULL. */
    public function restaurar(array $proyeccion): array
    {
        $t = array_fill_keys(self::TABLAS, []);
        foreach ($proyeccion[self::HECHOS] as $fila) {
            foreach ([[self::COGNITIVAS, self::COG, 'cod_evidencia_cognitiva_instantanea'],
                [self::IADL, self::IAD, 'cod_evidencia_iadl_instantanea']] as [$tabla, $mapa, $pk]) {
                if ($fila[$pk] !== null) {
                    $original = ['cod_hecho_instantanea' => $fila['cod_hecho_instantanea']];
                    foreach ($mapa as $origen => $destino) {
                        $original[$origen] = $fila[$destino];
                    }
                    $t[$tabla][] = $original;
                }
                foreach ($mapa as $destino) {
                    unset($fila[$destino]);
                }
            }
            $t[self::HECHOS][] = $fila;
        }
        foreach ($proyeccion[self::CLASIFICACIONES] as $fila) {
            $f = ['cod_funcionalidad_instantanea' => $fila['cod_funcionalidad_instantanea'], 'cod_evaluacion_experta' => $fila['cod_evaluacion_experta']];
            foreach (self::FUN as $origen => $destino) {
                $f[$origen] = $fila[$destino];
                unset($fila[$destino]);
            }
            $t[self::FUNCIONALIDAD][] = $f;
            $t[self::CLASIFICACIONES][] = $fila;
        }
        foreach ($proyeccion[self::VINCULOS] as $fila) {
            if ($fila['cod_soporte_sintetico'] !== null) {
                $t[self::SINTETICOS][] = ['cod_soporte_sintetico' => $fila['cod_soporte_sintetico'],
                    'cod_evaluacion_criterio' => $fila['cod_evaluacion_criterio'], 'cod_hecho_instantanea' => $fila['cod_hecho_instantanea'],
                    'orden' => $fila['orden_conservacion']];
            } else {
                unset($fila['cod_soporte_sintetico'], $fila['cod_hecho_instantanea']);
                $t[self::VINCULOS][] = $fila;
            }
        }

        return $t;
    }

    /** Comparación sin depender del orden SQL o del orden de las claves de cada fila. */
    public function canonizar(array $t): array
    {
        ksort($t, SORT_STRING);
        foreach ($t as &$filas) {
            foreach ($filas as &$fila) {
                ksort($fila, SORT_STRING);
            }
            unset($fila);
            usort($filas, static fn ($a, $b) => strcmp(serialize($a), serialize($b)));
        }
        unset($filas);

        return $t;
    }

    private function indexar(array $filas, string $pk): array
    {
        $indice = [];
        foreach ($filas as $fila) {
            if (! isset($fila[$pk]) || isset($indice[$fila[$pk]])) {
                throw new DomainException('Identidad ausente o duplicada en el ensayo de reducción.');
            }
            $indice[$fila[$pk]] = $fila;
        }

        return $indice;
    }

    private function columnas(array $fila, array $esperadas): void
    {
        if (array_diff(array_keys($fila), $esperadas) || array_diff($esperadas, array_keys($fila))) {
            throw new DomainException('La tabla contiene columnas distintas del mapa aprobado para el ensayo.');
        }
    }

    private function anexar(array &$fila, array $origen, array $mapa): void
    {
        foreach ($mapa as $columna => $destino) {
            if (array_key_exists($destino, $fila)) {
                throw new DomainException('La proyección colisiona con una columna existente.');
            }
            $fila[$destino] = $origen[$columna] ?? null;
        }
    }
}
