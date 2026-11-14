<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'registros_estudio';

    protected const CODIGO = 'cod_registro_estudio';

    protected const REFERENCIAS = [];

    protected const ORIGENES = [
        'resultados_estudio' => ['RESULTADO', ['cod_resultado_estudio', 'cod_estudio', 'cod_componente',
            'valor_numerico', 'valor_texto', 'unidad', 'rango_referencia', 'clasificacion', 'observacion']],
        'informes_estudio' => ['INFORME', ['cod_informe_estudio', 'cod_estudio', 'cod_personal', 'fecha_hora',
            'hallazgos', 'conclusion', 'recomendacion', 'origen', 'profesional_externo', 'estado']],
    ];

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_registro_estudio varchar(20) PRIMARY KEY NOT NULL,
                tipo_evento varchar(12) NOT NULL,
                cod_estudio varchar(20) NOT NULL REFERENCES estudios_clinicos(cod_estudio) ON DELETE RESTRICT,
                cod_componente varchar(20) REFERENCES componentes_estudio(cod_componente) ON DELETE RESTRICT,
                valor_numerico numeric(18,6),
                valor_texto text,
                unidad varchar(40),
                rango_referencia varchar(120),
                clasificacion varchar(30),
                observacion text,
                cod_personal varchar(20) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                fecha_hora timestamp(0),
                hallazgos text,
                conclusion text,
                recomendacion text,
                origen varchar(20),
                profesional_externo varchar(160),
                estado varchar(20),
                UNIQUE (cod_estudio, cod_componente),
                CONSTRAINT ck_registro_estudio_tipo CHECK (
                    (tipo_evento = 'RESULTADO' AND cod_componente IS NOT NULL AND cod_personal IS NULL
                        AND fecha_hora IS NULL AND hallazgos IS NULL AND conclusion IS NULL AND recomendacion IS NULL
                        AND origen IS NULL AND profesional_externo IS NULL AND estado IS NULL)
                    OR (tipo_evento = 'INFORME' AND fecha_hora IS NOT NULL AND origen IS NOT NULL AND estado IS NOT NULL
                        AND cod_componente IS NULL AND valor_numerico IS NULL AND valor_texto IS NULL AND unidad IS NULL
                        AND rango_referencia IS NULL AND clasificacion IS NULL AND observacion IS NULL)
                )
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        (require __DIR__.'/2026_09_18_001033_create_resultados_estudio_table.php')->up();
        (require __DIR__.'/2026_09_18_001034_create_informes_estudio_table.php')->up();
    }

    protected function indicesDestino(): array
    {
        return ['registro_estudio_fecha_i' => 'cod_estudio, tipo_evento, fecha_hora',
            'registro_estudio_componente_i' => 'cod_componente', 'registro_estudio_personal_i' => 'cod_personal'];
    }
};
