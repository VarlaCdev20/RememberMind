<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'valoraciones_profesionales';

    protected const CODIGO = 'cod_valoracion_profesional';

    protected const ORIGENES = [
        'valoraciones_psicologicas' => ['PSICOLOGICA', [
            'cod_valoracion_psicologica', 'cod_residente', 'cod_personal', 'cod_atencion', 'fecha_hora',
            'estado_animo', 'afecto', 'ansiedad', 'apatia', 'percepcion', 'conducta', 'comunicacion',
            'interaccion_social', 'impresion_cognitiva', 'conclusion', 'recomendacion', 'estado',
        ]],
        'valoraciones_nutricionales' => ['NUTRICIONAL', [
            'cod_valoracion_nutricional', 'cod_residente', 'cod_personal', 'cod_atencion', 'cod_medicion',
            'fecha_hora', 'estado_nutricional', 'apetito', 'deglucion', 'riesgo_desnutricion',
            'necesidad_asistencia', 'requerimiento_hidrico', 'restricciones_alimentarias', 'conclusion',
            'recomendacion', 'estado',
        ]],
        'valoraciones_funcionales' => ['FUNCIONAL', [
            'cod_valoracion_funcional', 'cod_residente', 'cod_personal', 'cod_atencion', 'fecha_hora',
            'marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia', 'alimentacion_autonoma',
            'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma',
            'necesita_supervision', 'nivel_dependencia', 'conclusion', 'recomendacion', 'estado',
        ]],
    ];

    protected const REFERENCIAS = [
        ['soportes_valoracion_petersen', 'cod_valoracion_psicologica', 'svp_cod_valoracion_psicologica_f', 'valoraciones_psicologicas', 'cod_valoracion_psicologica', 'cod_valoracion_psicologica_ref'],
    ];

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_valoracion_profesional varchar(20) NOT NULL PRIMARY KEY,
                cod_residente varchar(20) NOT NULL,
                cod_personal varchar(20) NOT NULL,
                cod_atencion varchar(20) NOT NULL,
                fecha_hora timestamp(0) NOT NULL,
                estado_animo varchar(50),
                afecto varchar(50),
                ansiedad varchar(50),
                apatia varchar(50),
                percepcion text,
                conducta text,
                comunicacion text,
                interaccion_social text,
                impresion_cognitiva text,
                conclusion text,
                recomendacion text,
                estado varchar(20) NOT NULL,
                cod_medicion varchar(20),
                estado_nutricional varchar(60),
                apetito varchar(40),
                deglucion varchar(40),
                riesgo_desnutricion varchar(40),
                necesidad_asistencia varchar(40),
                requerimiento_hidrico numeric(10,2),
                restricciones_alimentarias text,
                marcha varchar(40),
                equilibrio varchar(40),
                traslado varchar(40),
                fuerza_funcional varchar(40),
                resistencia varchar(40),
                alimentacion_autonoma varchar(30),
                bano_autonomo varchar(30),
                vestido_autonomo varchar(30),
                higiene_autonoma varchar(30),
                continencia varchar(30),
                movilidad_autonoma varchar(30),
                necesita_supervision boolean,
                nivel_dependencia varchar(40),
                tipo_evento varchar(15) NOT NULL,
                cod_valoracion_psicologica_ref varchar(20) GENERATED ALWAYS AS (CASE WHEN tipo_evento = 'PSICOLOGICA' THEN cod_valoracion_profesional END) STORED UNIQUE,
                CONSTRAINT ck_valoracion_tipo CHECK ((tipo_evento = 'PSICOLOGICA' AND cod_medicion IS NULL AND estado_nutricional IS NULL AND apetito IS NULL AND deglucion IS NULL AND riesgo_desnutricion IS NULL AND necesidad_asistencia IS NULL AND requerimiento_hidrico IS NULL AND restricciones_alimentarias IS NULL AND marcha IS NULL AND equilibrio IS NULL AND traslado IS NULL AND fuerza_funcional IS NULL AND resistencia IS NULL AND alimentacion_autonoma IS NULL AND bano_autonomo IS NULL AND vestido_autonomo IS NULL AND higiene_autonoma IS NULL AND continencia IS NULL AND movilidad_autonoma IS NULL AND necesita_supervision IS NULL AND nivel_dependencia IS NULL)
                 OR (tipo_evento = 'NUTRICIONAL' AND estado_animo IS NULL AND afecto IS NULL AND ansiedad IS NULL AND apatia IS NULL AND percepcion IS NULL AND conducta IS NULL AND comunicacion IS NULL AND interaccion_social IS NULL AND impresion_cognitiva IS NULL AND marcha IS NULL AND equilibrio IS NULL AND traslado IS NULL AND fuerza_funcional IS NULL AND resistencia IS NULL AND alimentacion_autonoma IS NULL AND bano_autonomo IS NULL AND vestido_autonomo IS NULL AND higiene_autonoma IS NULL AND continencia IS NULL AND movilidad_autonoma IS NULL AND necesita_supervision IS NULL AND nivel_dependencia IS NULL)
                 OR (tipo_evento = 'FUNCIONAL' AND estado_animo IS NULL AND afecto IS NULL AND ansiedad IS NULL AND apatia IS NULL AND percepcion IS NULL AND conducta IS NULL AND comunicacion IS NULL AND interaccion_social IS NULL AND impresion_cognitiva IS NULL AND cod_medicion IS NULL AND estado_nutricional IS NULL AND apetito IS NULL AND deglucion IS NULL AND riesgo_desnutricion IS NULL AND necesidad_asistencia IS NULL AND requerimiento_hidrico IS NULL AND restricciones_alimentarias IS NULL)),
                FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                CONSTRAINT fk_valoracion_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT,
                FOREIGN KEY (cod_medicion) REFERENCES mediciones_clinicas(cod_medicion_antropometrica) ON DELETE RESTRICT
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        (require __DIR__.'/2026_09_18_001061_create_valoraciones_psicologicas_table.php')->up();
        $this->restaurarNutricionales();
        (require __DIR__.'/2026_09_18_001063_create_valoraciones_funcionales_table.php')->up();
        $this->restaurarAtencion('valoraciones_psicologicas', 'fk_psicologica_atencion_residente');
        $this->restaurarAtencion('valoraciones_funcionales', 'fk_funcional_atencion_residente');
        $this->restaurarAtencion('valoraciones_nutricionales', 'fk_nutricional_atencion_residente');
    }

    private function restaurarNutricionales(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE valoraciones_nutricionales (
                cod_valoracion_nutricional varchar(20) NOT NULL PRIMARY KEY,
                cod_residente varchar(20) NOT NULL,
                cod_personal varchar(20) NOT NULL,
                cod_atencion varchar(20) NOT NULL,
                cod_medicion varchar(20),
                fecha_hora timestamp(0) NOT NULL,
                estado_nutricional varchar(60),
                apetito varchar(40),
                deglucion varchar(40),
                riesgo_desnutricion varchar(40),
                necesidad_asistencia varchar(40),
                requerimiento_hidrico numeric(10,2),
                restricciones_alimentarias text,
                conclusion text,
                recomendacion text,
                estado varchar(20) NOT NULL,
                CONSTRAINT valoraciones_nutricionales_cod_medicion_foreign
                    FOREIGN KEY (cod_medicion) REFERENCES mediciones_clinicas(cod_medicion_antropometrica) ON DELETE RESTRICT,
                FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT
            )
            SQL);
    }

    protected function indicesDestino(): array
    {
        return [
            'valoraciones_tipo_residente_fecha_i' => 'tipo_evento, cod_residente, fecha_hora',
            'valoraciones_atencion_i' => 'cod_atencion, cod_residente',
            'valoraciones_personal_i' => 'cod_personal',
            'valoraciones_medicion_i' => 'cod_medicion',
        ];
    }
};
