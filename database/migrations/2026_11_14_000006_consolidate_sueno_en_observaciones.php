<?php

use Illuminate\Support\Facades\DB;

require_once __DIR__.'/support/ConsolidacionClinica20261114.php';

return new class extends ConsolidacionClinica20261114
{
    protected const DESTINO = 'registros_observaciones_cuidado';

    protected const CODIGO = 'cod_observacion_cuidado';

    protected const ORIGENES = [
        'registros_observaciones_cuidado' => [null, [
            'cod_observacion_cuidado', 'tipo_evento', 'cod_residente', 'cod_personal', 'cod_jornada',
            'fecha_hora', 'estado', 'cod_atencion', 'marcha', 'equilibrio', 'traslado', 'tipo_apoyo',
            'dispositivo', 'fatiga', 'riesgo_caida', 'observacion', 'motivo_registro', 'actividad_realizada',
            'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual',
            'tolerancia_movilidad', 'estado_animo', 'apatia', 'agitacion', 'agresividad', 'ansiedad',
            'aislamiento', 'deambulacion', 'participacion', 'cambio_conducta', 'descripcion', 'intervencion',
            'respuesta',
        ]],
        'registros_sueno' => ['SUENO', [
            'cod_registro_sueno', 'cod_residente', 'cod_personal', 'cod_jornada', 'fecha', 'horas_sueno',
            'despertares', 'insomnio', 'somnolencia_diurna', 'agitacion_nocturna', 'calidad', 'observacion',
            'estado',
        ]],
    ];

    protected const REFERENCIAS = [];

    protected function crearDestino(string $tabla): void
    {
        DB::statement(<<<SQL
            CREATE TABLE $tabla (
                cod_observacion_cuidado varchar(20) NOT NULL PRIMARY KEY,
                tipo_evento varchar(12),
                cod_residente varchar(20) NOT NULL,
                cod_personal varchar(20) NOT NULL,
                cod_jornada varchar(20),
                fecha_hora timestamp(0),
                estado varchar(20) NOT NULL,
                cod_atencion varchar(20),
                marcha varchar(40),
                equilibrio varchar(40),
                traslado varchar(40),
                tipo_apoyo varchar(60),
                dispositivo varchar(80),
                fatiga varchar(30),
                riesgo_caida varchar(30),
                observacion text,
                motivo_registro varchar(30),
                actividad_realizada varchar(40),
                distancia_metros numeric(7,2),
                dolor_movilidad boolean,
                mareo boolean,
                disnea boolean,
                debilidad boolean,
                cambio_habitual varchar(20),
                tolerancia_movilidad varchar(30),
                estado_animo varchar(40),
                apatia boolean,
                agitacion boolean,
                agresividad boolean,
                ansiedad boolean,
                aislamiento boolean,
                deambulacion boolean,
                participacion varchar(30),
                cambio_conducta boolean,
                descripcion text,
                intervencion text,
                respuesta text,
                fecha date,
                horas_sueno numeric(4,2),
                despertares smallint,
                insomnio boolean,
                somnolencia_diurna boolean,
                agitacion_nocturna boolean,
                calidad varchar(30),
                CONSTRAINT ck_observacion_tipo CHECK ((tipo_evento = 'MOVILIDAD' AND fecha_hora IS NOT NULL AND fecha IS NULL AND estado_animo IS NULL AND apatia IS NULL AND agitacion IS NULL AND agresividad IS NULL AND ansiedad IS NULL AND aislamiento IS NULL AND deambulacion IS NULL AND participacion IS NULL AND cambio_conducta IS NULL AND descripcion IS NULL AND intervencion IS NULL AND respuesta IS NULL AND horas_sueno IS NULL AND despertares IS NULL AND insomnio IS NULL AND somnolencia_diurna IS NULL AND agitacion_nocturna IS NULL AND calidad IS NULL)
                 OR (tipo_evento = 'CONDUCTA' AND fecha_hora IS NOT NULL AND fecha IS NULL AND marcha IS NULL AND equilibrio IS NULL AND traslado IS NULL AND tipo_apoyo IS NULL AND dispositivo IS NULL AND fatiga IS NULL AND riesgo_caida IS NULL AND observacion IS NULL AND motivo_registro IS NULL AND actividad_realizada IS NULL AND distancia_metros IS NULL AND dolor_movilidad IS NULL AND mareo IS NULL AND disnea IS NULL AND debilidad IS NULL AND cambio_habitual IS NULL AND tolerancia_movilidad IS NULL AND horas_sueno IS NULL AND despertares IS NULL AND insomnio IS NULL AND somnolencia_diurna IS NULL AND agitacion_nocturna IS NULL AND calidad IS NULL)
                 OR (tipo_evento = 'SUENO' AND fecha IS NOT NULL AND fecha_hora IS NULL AND cod_atencion IS NULL AND marcha IS NULL AND equilibrio IS NULL AND traslado IS NULL AND tipo_apoyo IS NULL AND dispositivo IS NULL AND fatiga IS NULL AND riesgo_caida IS NULL AND motivo_registro IS NULL AND actividad_realizada IS NULL AND distancia_metros IS NULL AND dolor_movilidad IS NULL AND mareo IS NULL AND disnea IS NULL AND debilidad IS NULL AND cambio_habitual IS NULL AND tolerancia_movilidad IS NULL AND estado_animo IS NULL AND apatia IS NULL AND agitacion IS NULL AND agresividad IS NULL AND ansiedad IS NULL AND aislamiento IS NULL AND deambulacion IS NULL AND participacion IS NULL AND cambio_conducta IS NULL AND descripcion IS NULL AND intervencion IS NULL AND respuesta IS NULL)),
                FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                CONSTRAINT fk_observacion_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT
            )
            SQL);
    }

    protected function restaurarOrigenes(): void
    {
        DB::statement(<<<'SQL'
            CREATE TABLE registros_observaciones_cuidado (
                cod_observacion_cuidado varchar(20) NOT NULL PRIMARY KEY,
                tipo_evento varchar(12) NOT NULL,
                cod_residente varchar(20) NOT NULL,
                cod_personal varchar(20) NOT NULL,
                cod_jornada varchar(20),
                fecha_hora timestamp(0) NOT NULL,
                estado varchar(20) NOT NULL,
                cod_atencion varchar(20),
                marcha varchar(40),
                equilibrio varchar(40),
                traslado varchar(40),
                tipo_apoyo varchar(60),
                dispositivo varchar(80),
                fatiga varchar(30),
                riesgo_caida varchar(30),
                observacion text,
                motivo_registro varchar(30),
                actividad_realizada varchar(40),
                distancia_metros numeric(7,2),
                dolor_movilidad boolean,
                mareo boolean,
                disnea boolean,
                debilidad boolean,
                cambio_habitual varchar(20),
                tolerancia_movilidad varchar(30),
                estado_animo varchar(40),
                apatia boolean,
                agitacion boolean,
                agresividad boolean,
                ansiedad boolean,
                aislamiento boolean,
                deambulacion boolean,
                participacion varchar(30),
                cambio_conducta boolean,
                descripcion text,
                intervencion text,
                respuesta text,
                CONSTRAINT ck_observacion_tipo CHECK ((tipo_evento = 'MOVILIDAD' AND estado_animo IS NULL AND apatia IS NULL AND agitacion IS NULL AND agresividad IS NULL AND ansiedad IS NULL AND aislamiento IS NULL AND deambulacion IS NULL AND participacion IS NULL AND cambio_conducta IS NULL AND descripcion IS NULL AND intervencion IS NULL AND respuesta IS NULL) OR (tipo_evento = 'CONDUCTA' AND marcha IS NULL AND equilibrio IS NULL AND traslado IS NULL AND tipo_apoyo IS NULL AND dispositivo IS NULL AND fatiga IS NULL AND riesgo_caida IS NULL AND observacion IS NULL AND motivo_registro IS NULL AND actividad_realizada IS NULL AND distancia_metros IS NULL AND dolor_movilidad IS NULL AND mareo IS NULL AND disnea IS NULL AND debilidad IS NULL AND cambio_habitual IS NULL AND tolerancia_movilidad IS NULL)),
                FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                CONSTRAINT fk_observacion_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT
            )
            SQL);
        $this->indices(self::DESTINO, array_diff_key($this->indicesDestino(), ['observacion_sueno_residente_fecha_i' => true]));
        (require __DIR__.'/2026_09_18_001042_create_registros_sueno_table.php')->up();
    }

    protected function indicesDestino(): array
    {
        return [
            'observacion_tipo_residente_fecha_i' => 'tipo_evento, cod_residente, fecha_hora',
            'observacion_residente_i' => 'cod_residente',
            'observacion_personal_i' => 'cod_personal',
            'observacion_jornada_i' => 'cod_jornada',
            'observacion_atencion_residente_i' => 'cod_atencion, cod_residente',
            'observacion_sueno_residente_fecha_i' => 'tipo_evento, cod_residente, fecha',
        ];
    }
};
