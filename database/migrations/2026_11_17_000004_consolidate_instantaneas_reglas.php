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
            Schema::table('condiciones_regla_instantanea', static fn (Blueprint $t) => $t->renameColumn('cod_regla_aplicada_dcl', 'cod_regla_propietaria'));
            $this->union()->up();
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->union()->down();
            Schema::table('condiciones_regla_instantanea', static fn (Blueprint $t) => $t->renameColumn('cod_regla_propietaria', 'cod_regla_aplicada_dcl'));
        });
    }

    private function union(): ConsolidacionTipada20261116
    {
        return new class extends ConsolidacionTipada20261116
        {
            protected const DESTINO = 'reglas_aplicadas_dcl_instantanea';

            protected const CODIGO = 'cod_elemento_regla_dcl';

            protected const FUENTES = [
                'reglas_aplicadas_dcl_instantanea' => [
                    'type' => 'REGLA',
                    'key' => 'cod_regla_aplicada_dcl',
                    'columns' => [
                        'cod_regla_aplicada_dcl' => ['character varying(20)', false, null],
                        'cod_clasificacion_dcl_instantanea' => ['character varying(20)', false, null],
                        'cod_regla_experta' => ['character varying(20)', true, null],
                        'id_regla_dominio' => ['text', false, null],
                        'fuente' => ['text', false, null],
                        'habilitada' => ['boolean', false, null],
                        'concepto_consecuencia' => ['text', false, null],
                        'consecuencia_tipo' => ['character varying(8)', false, null],
                        'consecuencia_texto' => ['text', true, null],
                        'consecuencia_entero' => ['bigint', true, null],
                        'consecuencia_real_ieee754' => ['character(16)', true, null],
                        'consecuencia_logico' => ['boolean', true, null],
                        'orden' => ['integer', false, null],
                    ],
                    'foreign' => [['sv2_1cfb109cd020_fk', ['cod_regla_experta'], 'reglas_expertas', ['cod_regla_experta']], ['sv2_5f0cd4a449ea_fk', ['cod_clasificacion_dcl_instantanea'], 'evaluaciones_expertas', ['cod_clasificacion_dcl_instantanea']]],
                    'checks' => [['snap_consecuencia_conocida', 'CHECK ((((consecuencia_tipo) <> \'NULL\') IS TRUE))'], ['snap_consecuencia_tipo', 'CHECK ((((((consecuencia_tipo) = \'NULL\') AND (consecuencia_texto IS NULL) AND (consecuencia_entero IS NULL) AND (consecuencia_real_ieee754 IS NULL) AND (consecuencia_logico IS NULL)) OR (((consecuencia_tipo) = \'STRING\') AND (consecuencia_texto IS NOT NULL) AND (consecuencia_entero IS NULL) AND (consecuencia_real_ieee754 IS NULL) AND (consecuencia_logico IS NULL)) OR (((consecuencia_tipo) = \'INT\') AND (consecuencia_texto IS NULL) AND (consecuencia_entero IS NOT NULL) AND (consecuencia_real_ieee754 IS NULL) AND (consecuencia_logico IS NULL)) OR (((consecuencia_tipo) = \'FLOAT\') AND (consecuencia_texto IS NULL) AND (consecuencia_entero IS NULL) AND (consecuencia_real_ieee754 IS NOT NULL) AND (consecuencia_logico IS NULL)) OR (((consecuencia_tipo) = \'BOOL\') AND (consecuencia_texto IS NULL) AND (consecuencia_entero IS NULL) AND (consecuencia_real_ieee754 IS NULL) AND (consecuencia_logico IS NOT NULL))) IS TRUE))'], ['snap_orden_7', 'CHECK (((orden >= 0) IS TRUE))']],
                    'indexes' => [['snap_regla_id', ['cod_clasificacion_dcl_instantanea', 'id_regla_dominio'], true], ['snap_regla_ord', ['cod_clasificacion_dcl_instantanea', 'orden'], true], ['sv2_1cfb109cd020_ix', ['cod_regla_experta'], false], ['sv2_5f0cd4a449ea_ix', ['cod_clasificacion_dcl_instantanea'], false]],
                ],
                'condiciones_regla_instantanea' => [
                    'type' => 'CONDICION',
                    'key' => 'cod_condicion_instantanea',
                    'columns' => [
                        'cod_condicion_instantanea' => ['character varying(20)', false, null],
                        'cod_regla_propietaria' => ['character varying(20)', false, null],
                        'cod_condicion_padre' => ['character varying(20)', true, null],
                        'operador' => ['character varying(20)', false, null],
                        'variable' => ['text', true, null],
                        'literal_tipo' => ['character varying(8)', false, null],
                        'literal_texto' => ['text', true, null],
                        'literal_entero' => ['bigint', true, null],
                        'literal_real_ieee754' => ['character(16)', true, null],
                        'literal_logico' => ['boolean', true, null],
                        'orden' => ['integer', false, null],
                    ],
                    'foreign' => [['sv2_2ff4c51be364_fk', ['cod_condicion_padre'], 'condiciones_regla_instantanea', ['cod_condicion_instantanea']], ['sv2_a48aa5a00c63_fk', ['cod_regla_propietaria'], 'reglas_aplicadas_dcl_instantanea', ['cod_regla_aplicada_dcl']]],
                    'checks' => [['snap_forma_condicion', 'CHECK ((((operador IN (\'IGUAL_A\',\'DISTINTO_DE\') AND variable IS NOT NULL AND literal_tipo <> \'NULL\') OR (operador IN (\'Y\',\'O\',\'NO\') AND variable IS NULL AND literal_tipo = \'NULL\')) IS TRUE))'], ['snap_literal_tipo', 'CHECK ((((((literal_tipo) = \'NULL\') AND (literal_texto IS NULL) AND (literal_entero IS NULL) AND (literal_real_ieee754 IS NULL) AND (literal_logico IS NULL)) OR (((literal_tipo) = \'STRING\') AND (literal_texto IS NOT NULL) AND (literal_entero IS NULL) AND (literal_real_ieee754 IS NULL) AND (literal_logico IS NULL)) OR (((literal_tipo) = \'INT\') AND (literal_texto IS NULL) AND (literal_entero IS NOT NULL) AND (literal_real_ieee754 IS NULL) AND (literal_logico IS NULL)) OR (((literal_tipo) = \'FLOAT\') AND (literal_texto IS NULL) AND (literal_entero IS NULL) AND (literal_real_ieee754 IS NOT NULL) AND (literal_logico IS NULL)) OR (((literal_tipo) = \'BOOL\') AND (literal_texto IS NULL) AND (literal_entero IS NULL) AND (literal_real_ieee754 IS NULL) AND (literal_logico IS NOT NULL))) IS TRUE))'], ['snap_orden_8', 'CHECK (((orden >= 0) IS TRUE))']],
                    'indexes' => [['sv2_2ff4c51be364_ix', ['cod_condicion_padre'], false], ['sv2_a48aa5a00c63_ix', ['cod_regla_propietaria'], false]],
                ],
            ];
        };
    }
};
