<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/ConsolidacionTipada20261116.php';

/** Unión disjunta autorizada; conserva cada regla y cada condición como filas independientes. */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            Schema::table('evaluaciones_condiciones_regla', static fn (Blueprint $t) => $t->renameColumn('cod_evaluacion_regla', 'cod_evaluacion_regla_padre'));
            $this->union()->up();
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->union()->down();
            Schema::table('evaluaciones_condiciones_regla', static fn (Blueprint $t) => $t->renameColumn('cod_evaluacion_regla_padre', 'cod_evaluacion_regla'));
        });
    }

    private function union(): ConsolidacionTipada20261116
    {
        return new class extends ConsolidacionTipada20261116
        {
            protected const DESTINO = 'evaluaciones_reglas';

            protected const CODIGO = 'cod_elemento_evaluacion';

            protected const FUENTES = [
                'evaluaciones_reglas' => [
                    'type' => 'REGLA',
                    'key' => 'cod_evaluacion_regla',
                    'columns' => [
                        'cod_evaluacion_regla' => ['character varying(20)', false, null],
                        'cod_traza_inferencia' => ['character varying(20)', false, null],
                        'cod_regla_experta' => ['character varying(20)', false, null],
                        'estado_regla' => ['character varying(20)', false, null],
                        'fecha_hora_evaluacion' => ['timestamp(0)', false, null],
                    ],
                    'foreign' => [['se21_f1', ['cod_traza_inferencia'], 'evaluacion_criterios', ['cod_traza_inferencia']], ['se21_f2', ['cod_regla_experta'], 'reglas_expertas', ['cod_regla_experta']]],
                    'checks' => [],
                    'indexes' => [['se21_i1', ['cod_regla_experta'], false], ['se21_i2', ['estado_regla'], false], ['se21_u1', ['cod_traza_inferencia', 'cod_regla_experta'], true]],
                ],
                'evaluaciones_condiciones_regla' => [
                    'type' => 'CONDICION',
                    'key' => 'cod_evaluacion_condicion',
                    'columns' => [
                        'cod_evaluacion_condicion' => ['character varying(20)', false, null],
                        'cod_evaluacion_regla_padre' => ['character varying(20)', false, null],
                        'cod_condicion_regla' => ['character varying(20)', false, null],
                        'estado_condicion' => ['character varying(20)', false, null],
                        'fecha_hora_evaluacion' => ['timestamp(0)', false, null],
                    ],
                    'foreign' => [['se22_f1', ['cod_evaluacion_regla_padre'], 'evaluaciones_reglas', ['cod_evaluacion_regla']], ['se22_f2', ['cod_condicion_regla'], 'componentes_regla_experta', ['cod_condicion_regla_ref']]],
                    'checks' => [],
                    'indexes' => [['se22_i1', ['cod_condicion_regla'], false], ['se22_i2', ['estado_condicion'], false], ['se22_u1', ['cod_evaluacion_regla_padre', 'cod_condicion_regla'], true]],
                ],
            ];
        };
    }
};
