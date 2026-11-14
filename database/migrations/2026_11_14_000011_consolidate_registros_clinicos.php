<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'registros_clinicos';

    protected const CODIGO = 'cod_registro_clinico';

    protected const ORIGENES = [
        'antecedentes_clinicos' => ['ANTECEDENTE', ['cod_antecedente', 'cod_residente', 'cod_personal', 'tipo_antecedente', 'descripcion', 'fecha_referencia', 'fuente_informacion', 'estado', 'observacion']],
        'diagnosticos' => ['DIAGNOSTICO', ['cod_diagnostico', 'cod_residente', 'cod_atencion', 'cod_personal', 'codigo_clinico', 'nombre', 'tipo', 'certeza', 'fecha_hora', 'estado', 'observacion']],
        'alergias' => ['ALERGIA', ['cod_alergia', 'cod_residente', 'cod_personal', 'tipo', 'sustancia', 'reaccion', 'gravedad', 'fecha_hora', 'estado', 'observacion']],
        'dispositivos_clinicos' => ['DISPOSITIVO', ['cod_dispositivo', 'cod_residente', 'cod_personal', 'tipo', 'descripcion', 'ubicacion', 'fecha_colocacion', 'fecha_retiro', 'estado', 'observacion']],
    ];

    protected const REFERENCIAS = [
        ['soportes_valoracion_petersen', 'cod_antecedente', 'svp_cod_antecedente_f', 'antecedentes_clinicos', 'cod_antecedente', 'cod_antecedente_ref'],
        ['soportes_valoracion_petersen', 'cod_diagnostico', 'svp_cod_diagnostico_f', 'diagnosticos', 'cod_diagnostico', 'cod_diagnostico_ref'],
    ];

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_registro_clinico varchar(20) PRIMARY KEY NOT NULL,
                tipo_evento varchar(12) NOT NULL,
                cod_residente varchar(20) NOT NULL REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                cod_personal varchar(20) NOT NULL REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                estado varchar(20) NOT NULL,
                observacion text,
                tipo_antecedente varchar(60),
                descripcion text,
                fecha_referencia date,
                fuente_informacion varchar(80),
                cod_atencion varchar(20) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                codigo_clinico varchar(30),
                nombre varchar(160),
                tipo varchar(60),
                certeza varchar(30),
                fecha_hora timestamp(0),
                sustancia varchar(120),
                reaccion text,
                gravedad varchar(30),
                ubicacion varchar(120),
                fecha_colocacion timestamp(0),
                fecha_retiro timestamp(0),
                cod_antecedente_ref varchar(20) GENERATED ALWAYS AS
                    (CASE WHEN tipo_evento = 'ANTECEDENTE' THEN cod_registro_clinico END) STORED UNIQUE,
                cod_diagnostico_ref varchar(20) GENERATED ALWAYS AS
                    (CASE WHEN tipo_evento = 'DIAGNOSTICO' THEN cod_registro_clinico END) STORED UNIQUE,
                FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT,
                CONSTRAINT ck_registro_clinico_tipo CHECK ((
                    (tipo_evento = 'ANTECEDENTE' AND tipo_antecedente IS NOT NULL AND descripcion IS NOT NULL
                        AND cod_atencion IS NULL AND codigo_clinico IS NULL AND nombre IS NULL AND tipo IS NULL
                        AND certeza IS NULL AND fecha_hora IS NULL AND sustancia IS NULL AND reaccion IS NULL
                        AND gravedad IS NULL AND ubicacion IS NULL AND fecha_colocacion IS NULL AND fecha_retiro IS NULL)
                    OR (tipo_evento = 'DIAGNOSTICO' AND cod_atencion IS NOT NULL AND nombre IS NOT NULL
                        AND fecha_hora IS NOT NULL AND (tipo IS NULL OR length(tipo) <= 50)
                        AND tipo_antecedente IS NULL AND descripcion IS NULL AND fecha_referencia IS NULL
                        AND fuente_informacion IS NULL AND sustancia IS NULL AND reaccion IS NULL AND gravedad IS NULL
                        AND ubicacion IS NULL AND fecha_colocacion IS NULL AND fecha_retiro IS NULL)
                    OR (tipo_evento = 'ALERGIA' AND sustancia IS NOT NULL AND fecha_hora IS NOT NULL
                        AND (tipo IS NULL OR length(tipo) <= 40)
                        AND tipo_antecedente IS NULL AND descripcion IS NULL AND fecha_referencia IS NULL
                        AND fuente_informacion IS NULL AND cod_atencion IS NULL AND codigo_clinico IS NULL
                        AND nombre IS NULL AND certeza IS NULL AND ubicacion IS NULL AND fecha_colocacion IS NULL AND fecha_retiro IS NULL)
                    OR (tipo_evento = 'DISPOSITIVO' AND tipo IS NOT NULL
                        AND tipo_antecedente IS NULL AND fecha_referencia IS NULL AND fuente_informacion IS NULL
                        AND cod_atencion IS NULL AND codigo_clinico IS NULL AND nombre IS NULL AND certeza IS NULL
                        AND fecha_hora IS NULL AND sustancia IS NULL AND reaccion IS NULL AND gravedad IS NULL)
                ) IS TRUE)
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        (require __DIR__.'/2026_09_18_001023_create_antecedentes_clinicos_table.php')->up();
        (require __DIR__.'/2026_09_18_001024_create_diagnosticos_table.php')->up();
        (require __DIR__.'/2026_09_18_001025_create_alergias_table.php')->up();
        (require __DIR__.'/2026_09_18_001027_create_dispositivos_clinicos_table.php')->up();
        $this->restaurarAtencion('diagnosticos', 'fk_diagnostico_atencion_residente');
    }

    protected function indicesDestino(): array
    {
        return [
            'registro_clinico_residente_tipo_i' => 'cod_residente, tipo_evento',
            'registro_clinico_atencion_i' => 'cod_atencion',
        ];
    }
};
