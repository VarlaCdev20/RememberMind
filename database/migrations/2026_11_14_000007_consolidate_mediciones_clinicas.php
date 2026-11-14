<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'mediciones_clinicas';

    protected const CODIGO = 'cod_registro_medicion';

    protected const ORIGENES = [
        'signos_vitales' => ['SIGNOS', [
            'cod_signo', 'cod_residente', 'cod_personal', 'cod_jornada', 'cod_atencion', 'fecha_hora',
            'presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria',
            'temperatura', 'saturacion_oxigeno', 'glucemia', 'estado', 'observacion',
        ]],
        'mediciones_antropometricas' => ['ANTROPOMETRIA', [
            'cod_medicion', 'cod_residente', 'cod_personal', 'fecha_hora', 'peso', 'talla', 'imc',
            'perimetro_braquial', 'perimetro_pantorrilla', 'observacion',
        ]],
    ];

    protected const REFERENCIAS = [
        ['valoraciones_nutricionales', 'cod_medicion', 'valoraciones_nutricionales_cod_medicion_foreign', 'mediciones_antropometricas', 'cod_medicion', 'cod_medicion_antropometrica'],
    ];

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_registro_medicion varchar(20) NOT NULL PRIMARY KEY,
                cod_residente varchar(20) NOT NULL,
                cod_personal varchar(20) NOT NULL,
                cod_jornada varchar(20),
                cod_atencion varchar(20),
                fecha_hora timestamp(0) NOT NULL,
                presion_sistolica numeric(5,2),
                presion_diastolica numeric(5,2),
                frecuencia_cardiaca numeric(6,2),
                frecuencia_respiratoria numeric(6,2),
                temperatura numeric(4,1),
                saturacion_oxigeno numeric(5,2),
                glucemia numeric(8,2),
                estado varchar(20),
                observacion text,
                peso numeric(6,2),
                talla numeric(5,2),
                imc numeric(5,2),
                perimetro_braquial numeric(6,2),
                perimetro_pantorrilla numeric(6,2),
                tipo_evento varchar(15) NOT NULL,
                cod_medicion_antropometrica varchar(20) GENERATED ALWAYS AS (CASE WHEN tipo_evento = 'ANTROPOMETRIA' THEN cod_registro_medicion END) STORED UNIQUE,
                CONSTRAINT ck_medicion_tipo CHECK ((tipo_evento = 'SIGNOS' AND estado IS NOT NULL AND peso IS NULL AND talla IS NULL AND imc IS NULL AND perimetro_braquial IS NULL AND perimetro_pantorrilla IS NULL)
                 OR (tipo_evento = 'ANTROPOMETRIA' AND cod_jornada IS NULL AND cod_atencion IS NULL AND presion_sistolica IS NULL AND presion_diastolica IS NULL AND frecuencia_cardiaca IS NULL AND frecuencia_respiratoria IS NULL AND temperatura IS NULL AND saturacion_oxigeno IS NULL AND glucemia IS NULL AND estado IS NULL)),
                CONSTRAINT ck_med_ant_talla CHECK (talla IS NULL OR talla BETWEEN 50.0 AND 240.0),
                FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                CONSTRAINT fk_medicion_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        (require __DIR__.'/2026_09_18_001028_create_signos_vitales_table.php')->up();
        (require __DIR__.'/2026_09_18_001030_create_mediciones_antropometricas_table.php')->up();
        $this->restaurarAtencion('signos_vitales', 'fk_signo_atencion_residente');
        $this->restaurarTalla();
    }

    protected function indicesDestino(): array
    {
        return [
            'mediciones_tipo_residente_fecha_i' => 'tipo_evento, cod_residente, fecha_hora',
            'mediciones_atencion_i' => 'cod_atencion, cod_residente',
            'mediciones_jornada_i' => 'cod_jornada',
            'mediciones_personal_i' => 'cod_personal',
        ];
    }
};
