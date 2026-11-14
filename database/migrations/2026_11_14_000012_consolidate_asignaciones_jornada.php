<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'asignaciones_jornada';

    protected const CODIGO = 'cod_asignacion_jornada';

    protected const ORIGENES = [
        'asignaciones_personal' => ['PERSONAL', ['cod_asignacion_personal', 'cod_jornada', 'cod_personal', 'cod_area',
            'funcion', 'tipo_asignacion', 'fecha_asignacion', 'estado', 'observacion']],
        'asignaciones_residente_jornada' => ['RESIDENTE', ['cod_asignacion', 'cod_residente', 'cod_jornada', 'cod_personal',
            'nivel_supervision', 'fecha_hora', 'estado', 'observacion']],
    ];

    protected const REFERENCIAS = [
        ['eventos_asignacion_personal', 'cod_asignacion_personal', 'eventos_asignacion_personal_cod_asignacion_personal_fkey',
            'asignaciones_personal', 'cod_asignacion_personal', 'cod_asignacion_personal'],
    ];

    protected function referencias(): array
    {
        return Schema::hasTable('eventos_asignacion_personal') ? parent::referencias() : [];
    }

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_asignacion_jornada varchar(20) PRIMARY KEY NOT NULL,
                tipo_evento varchar(12) NOT NULL,
                cod_jornada varchar(20) NOT NULL REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                cod_personal varchar(20) NOT NULL REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                estado varchar(20) NOT NULL,
                observacion text,
                cod_area varchar(20) REFERENCES areas(cod_area) ON DELETE RESTRICT,
                funcion varchar(80),
                tipo_asignacion varchar(30),
                fecha_asignacion timestamp(0),
                cod_residente varchar(20) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                nivel_supervision varchar(30),
                fecha_hora timestamp(0),
                cod_asignacion_personal varchar(20) GENERATED ALWAYS AS
                    (CASE WHEN tipo_evento = 'PERSONAL' THEN cod_asignacion_jornada END) STORED UNIQUE,
                CONSTRAINT ck_asignacion_jornada_tipo CHECK ((
                    (tipo_evento = 'PERSONAL' AND cod_area IS NOT NULL AND tipo_asignacion IS NOT NULL
                        AND fecha_asignacion IS NOT NULL AND cod_residente IS NULL AND nivel_supervision IS NULL AND fecha_hora IS NULL)
                    OR (tipo_evento = 'RESIDENTE' AND cod_residente IS NOT NULL AND fecha_hora IS NOT NULL
                        AND cod_area IS NULL AND funcion IS NULL AND tipo_asignacion IS NULL AND fecha_asignacion IS NULL)
                ) IS TRUE)
            )
            SQL);
    }

    private function indicesActivos(string $personal, string $residentes): void
    {
        DB::statement("CREATE UNIQUE INDEX uq_asignacion_personal_activa ON $personal (cod_jornada, cod_personal, cod_area) WHERE estado IN ('ACTIVA', 'ACTIVO') AND cod_area IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX uq_asignacion_residente_activa ON $residentes (cod_residente, cod_jornada, cod_personal) WHERE estado IN ('ACTIVA', 'ACTIVO') AND cod_residente IS NOT NULL");
    }

    protected function despuesDeMover(bool $subir): void
    {
        if ($subir) {
            $this->indicesActivos(self::DESTINO, self::DESTINO);

            return;
        }

        $this->indicesActivos('asignaciones_personal', 'asignaciones_residente_jornada');
    }

    protected function restaurarOrigenes(): void
    {
        (require __DIR__.'/2026_09_18_001013_create_asignaciones_personal_table.php')->up();
        (require __DIR__.'/2026_09_18_001039_create_asignaciones_residente_jornada_table.php')->up();
    }

    protected function indicesDestino(): array
    {
        return [
            'asignacion_jornada_personal_i' => 'cod_jornada, tipo_evento, cod_personal',
            'asignacion_jornada_residente_i' => 'cod_residente, tipo_evento, cod_jornada',
        ];
    }
};
