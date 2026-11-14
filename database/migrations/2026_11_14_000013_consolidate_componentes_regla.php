<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'componentes_regla_experta';

    protected const CODIGO = 'cod_componente_regla';

    protected const ORIGENES = [
        'condiciones_regla_experta' => ['CONDICION', ['cod_condicion_regla', 'cod_regla_experta', 'cod_variable_experta', 'cod_valor_semantico']],
        'consecuencias_regla_experta' => ['CONSECUENCIA', ['cod_consecuencia_regla', 'cod_regla_experta', 'cod_criterio_dominio_resultado', 'cod_valor_semantico']],
    ];

    protected const REFERENCIAS = [
        ['evaluaciones_condiciones_regla', 'cod_condicion_regla', 'se22_f2',
            'condiciones_regla_experta', 'cod_condicion_regla', 'cod_condicion_regla_ref'],
    ];

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_componente_regla varchar(20) PRIMARY KEY NOT NULL,
                tipo_evento varchar(12) NOT NULL,
                cod_regla_experta varchar(20) NOT NULL REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT,
                cod_valor_semantico varchar(20) NOT NULL REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT,
                cod_variable_experta varchar(20) REFERENCES nodos_semanticos(cod_variable_experta) ON DELETE RESTRICT,
                cod_criterio_dominio_resultado varchar(20) REFERENCES nodos_semanticos(cod_criterio_dominio_resultado) ON DELETE RESTRICT,
                cod_condicion_regla_ref varchar(20) GENERATED ALWAYS AS
                    (CASE WHEN tipo_evento = 'CONDICION' THEN cod_componente_regla END) STORED UNIQUE,
                UNIQUE (cod_regla_experta, cod_variable_experta, cod_valor_semantico),
                UNIQUE (cod_regla_experta, cod_criterio_dominio_resultado),
                CONSTRAINT ck_componente_regla_tipo CHECK ((
                    (tipo_evento = 'CONDICION' AND cod_variable_experta IS NOT NULL AND cod_criterio_dominio_resultado IS NULL)
                    OR (tipo_evento = 'CONSECUENCIA' AND cod_variable_experta IS NULL AND cod_criterio_dominio_resultado IS NOT NULL)
                ) IS TRUE)
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        $pgsql = DB::getDriverName() === 'pgsql';
        $unicaCondicion = $pgsql ? ', CONSTRAINT se12_u1 UNIQUE (cod_regla_experta, cod_variable_experta, cod_valor_semantico)' : '';
        $unicaConsecuencia = $pgsql ? ', CONSTRAINT se13_u1 UNIQUE (cod_regla_experta, cod_criterio_dominio_resultado)' : '';
        DB::statement(<<<SQL
            CREATE TABLE condiciones_regla_experta (
                cod_condicion_regla varchar(20) PRIMARY KEY NOT NULL,
                cod_regla_experta varchar(20) NOT NULL,
                cod_variable_experta varchar(20) NOT NULL,
                cod_valor_semantico varchar(20) NOT NULL,
                CONSTRAINT se12_f1 FOREIGN KEY (cod_regla_experta) REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT,
                CONSTRAINT se12_f2 FOREIGN KEY (cod_variable_experta) REFERENCES nodos_semanticos(cod_variable_experta) ON DELETE RESTRICT,
                CONSTRAINT se12_f3 FOREIGN KEY (cod_valor_semantico) REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT
                $unicaCondicion
            )
            SQL);
        DB::statement(<<<SQL
            CREATE TABLE consecuencias_regla_experta (
                cod_consecuencia_regla varchar(20) PRIMARY KEY NOT NULL,
                cod_regla_experta varchar(20) NOT NULL,
                cod_criterio_dominio_resultado varchar(20) NOT NULL,
                cod_valor_semantico varchar(20) NOT NULL,
                CONSTRAINT se13_f1 FOREIGN KEY (cod_regla_experta) REFERENCES reglas_expertas(cod_regla_experta) ON DELETE RESTRICT,
                CONSTRAINT se13_f2 FOREIGN KEY (cod_criterio_dominio_resultado) REFERENCES nodos_semanticos(cod_criterio_dominio_resultado) ON DELETE RESTRICT,
                CONSTRAINT se13_f3 FOREIGN KEY (cod_valor_semantico) REFERENCES valores_semanticos(cod_valor_semantico) ON DELETE RESTRICT
                $unicaConsecuencia
            )
            SQL);
        if (! $pgsql) {
            DB::statement('CREATE UNIQUE INDEX se12_u1 ON condiciones_regla_experta (cod_regla_experta, cod_variable_experta, cod_valor_semantico)');
            DB::statement('CREATE UNIQUE INDEX se13_u1 ON consecuencias_regla_experta (cod_regla_experta, cod_criterio_dominio_resultado)');
        }
        $this->indices('condiciones_regla_experta', ['se12_i1' => 'cod_variable_experta, cod_valor_semantico']);
        $this->indices('consecuencias_regla_experta', ['se13_i1' => 'cod_criterio_dominio_resultado', 'se13_i2' => 'cod_valor_semantico']);
    }

    protected function indicesDestino(): array
    {
        return [
            'componente_regla_variable_valor_i' => 'cod_variable_experta, cod_valor_semantico',
            'componente_regla_criterio_i' => 'cod_criterio_dominio_resultado',
            'componente_regla_valor_i' => 'cod_valor_semantico',
        ];
    }
};
