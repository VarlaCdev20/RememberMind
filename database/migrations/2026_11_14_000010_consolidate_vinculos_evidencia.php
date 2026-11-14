<?php

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\GuardasReduccionConservacion;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'vinculos_evidencia_experta';

    protected const CODIGO = 'cod_vinculo_evidencia';

    protected const REFERENCIAS = [];

    protected const ORIGENES = [
        'evidencias_criterio_evaluacion' => ['CRITERIO', ['cod_evidencia_criterio', 'cod_evaluacion_criterio',
            'cod_evidencia_evaluacion', 'rol_en_criterio', 'estado_participacion', 'justificacion', 'fecha_hora_vinculacion',
            'orden_conservacion', 'cod_hecho_instantanea', 'cod_soporte_sintetico']],
        'evidencias_soporte_condicion' => ['CONDICION', ['cod_evidencia_soporte_condicion', 'cod_evaluacion_condicion',
            'cod_evidencia_evaluacion']],
    ];

    private const RAMAS_CRITERIO = <<<'SQL'
        (cod_evidencia_evaluacion IS NOT NULL AND cod_hecho_instantanea IS NULL AND cod_soporte_sintetico IS NULL
            AND fecha_hora_vinculacion IS NOT NULL)
        OR (cod_evidencia_evaluacion IS NULL AND cod_hecho_instantanea IS NOT NULL AND cod_soporte_sintetico IS NOT NULL
            AND rol_en_criterio = 'SOPORTE_EXPERIMENTAL' AND estado_participacion = 'PRUEBA_TECNICA'
            AND justificacion IS NULL AND fecha_hora_vinculacion IS NULL AND orden_conservacion IS NOT NULL AND orden_conservacion >= 0)
        SQL;

    protected function antesDeMover(bool $subir): void
    {
        (new GuardasReduccionConservacion(! $subir))->retirar();
    }

    protected function despuesDeMover(bool $subir): void
    {
        (new GuardasReduccionConservacion($subir))->instalar();
    }

    protected function crearDestino(string $tabla): void
    {
        $ramas = self::RAMAS_CRITERIO;
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_vinculo_evidencia varchar(20) PRIMARY KEY NOT NULL,
                tipo_evento varchar(12) NOT NULL,
                cod_evaluacion_criterio varchar(20) REFERENCES evaluacion_criterios(cod_evaluacion_criterio) ON DELETE RESTRICT,
                cod_evaluacion_condicion varchar(20) REFERENCES evaluaciones_condiciones_regla(cod_evaluacion_condicion) ON DELETE RESTRICT,
                cod_evidencia_evaluacion varchar(20) REFERENCES evidencias_evaluacion(cod_evidencia_evaluacion) ON DELETE RESTRICT,
                rol_en_criterio varchar(30),
                estado_participacion varchar(30),
                justificacion text,
                fecha_hora_vinculacion timestamp(0),
                orden_conservacion integer,
                cod_hecho_instantanea varchar(20) REFERENCES hechos_instantanea_experta(cod_hecho_instantanea) ON DELETE RESTRICT,
                cod_soporte_sintetico varchar(20) UNIQUE,
                UNIQUE (cod_evaluacion_criterio, cod_evidencia_evaluacion),
                UNIQUE (cod_evaluacion_criterio, cod_hecho_instantanea),
                UNIQUE (cod_evaluacion_condicion, cod_evidencia_evaluacion),
                CONSTRAINT ck_vinculo_evidencia_tipo CHECK ((
                    (tipo_evento = 'CRITERIO' AND cod_evaluacion_criterio IS NOT NULL AND cod_evaluacion_condicion IS NULL
                        AND rol_en_criterio IS NOT NULL AND estado_participacion IS NOT NULL AND ($ramas))
                    OR (tipo_evento = 'CONDICION' AND cod_evaluacion_condicion IS NOT NULL AND cod_evaluacion_criterio IS NULL
                        AND cod_evidencia_evaluacion IS NOT NULL AND rol_en_criterio IS NULL AND estado_participacion IS NULL
                        AND justificacion IS NULL AND fecha_hora_vinculacion IS NULL AND orden_conservacion IS NULL
                        AND cod_hecho_instantanea IS NULL AND cod_soporte_sintetico IS NULL)
                ) IS TRUE)
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        $ramas = self::RAMAS_CRITERIO;
        $check = DB::getDriverName() === 'pgsql'
            ? ", CONSTRAINT red_v_rama CHECK (($ramas) IS TRUE), CONSTRAINT se18_u1 UNIQUE (cod_evaluacion_criterio, cod_evidencia_evaluacion)" : '';
        DB::statement(<<<SQL
            CREATE TABLE evidencias_criterio_evaluacion (
                cod_evidencia_criterio varchar(20) PRIMARY KEY NOT NULL,
                cod_evaluacion_criterio varchar(20) NOT NULL,
                cod_evidencia_evaluacion varchar(20),
                rol_en_criterio varchar(30) NOT NULL,
                estado_participacion varchar(30) NOT NULL,
                justificacion text,
                fecha_hora_vinculacion timestamp(0),
                orden_conservacion integer,
                cod_hecho_instantanea varchar(20),
                cod_soporte_sintetico varchar(20),
                CONSTRAINT se18_f1 FOREIGN KEY (cod_evaluacion_criterio)
                    REFERENCES evaluacion_criterios(cod_evaluacion_criterio) ON DELETE RESTRICT,
                CONSTRAINT se18_f2 FOREIGN KEY (cod_evidencia_evaluacion)
                    REFERENCES evidencias_evaluacion(cod_evidencia_evaluacion) ON DELETE RESTRICT,
                CONSTRAINT red_v_hecho_f FOREIGN KEY (cod_hecho_instantanea)
                    REFERENCES hechos_instantanea_experta(cod_hecho_instantanea) ON DELETE RESTRICT
                $check
            )
            SQL);
        $this->indices('evidencias_criterio_evaluacion', ['se18_i1' => 'cod_evidencia_evaluacion',
            'se18_i2' => 'estado_participacion', 'red_v_hecho_ix' => 'cod_hecho_instantanea']);
        DB::statement('CREATE UNIQUE INDEX red_v_alias_u ON evidencias_criterio_evaluacion (cod_soporte_sintetico) WHERE cod_soporte_sintetico IS NOT NULL');
        DB::statement('CREATE UNIQUE INDEX red_v_hecho_u ON evidencias_criterio_evaluacion (cod_evaluacion_criterio, cod_hecho_instantanea) WHERE cod_hecho_instantanea IS NOT NULL');
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE UNIQUE INDEX se18_u1 ON evidencias_criterio_evaluacion (cod_evaluacion_criterio, cod_evidencia_evaluacion)');
            $predicado = preg_replace("/'[^']*'(*SKIP)(*F)|\\b(cod_[a-z_]+|fecha_hora_vinculacion|rol_en_criterio|estado_participacion|justificacion|orden_conservacion)\\b/", 'NEW.$1', $ramas);
            foreach (['INSERT', 'UPDATE'] as $evento) {
                $nombre = 'red_v_rama_'.strtolower($evento);
                DB::unprepared("CREATE TRIGGER $nombre BEFORE $evento ON evidencias_criterio_evaluacion WHEN COALESCE(($predicado),0)=0 BEGIN SELECT RAISE(ABORT,'red_v_rama'); END");
            }
        }
        (require __DIR__.'/2026_10_08_120023_create_evidencias_soporte_condicion_table.php')->up();
    }

    protected function indicesDestino(): array
    {
        return ['vinculo_evidencia_origen_i' => 'cod_evidencia_evaluacion',
            'vinculo_evidencia_hecho_i' => 'cod_hecho_instantanea', 'vinculo_evidencia_participacion_i' => 'estado_participacion'];
    }
};
