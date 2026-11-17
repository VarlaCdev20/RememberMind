<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DESTINO = 'registros_observaciones_cuidado';

    private const CODIGO = 'cod_observacion_cuidado';

    private const TEMPORAL = 'registros_observaciones_cuidado_transicion';

    private const ORIGENES = [
        'registros_movilidad' => ['MOVILIDAD', 'cod_movilidad', [
            'cod_movilidad', 'cod_residente', 'cod_personal', 'cod_jornada', 'cod_atencion',
            'fecha_hora', 'marcha', 'equilibrio', 'traslado', 'tipo_apoyo', 'dispositivo', 'fatiga',
            'riesgo_caida', 'observacion', 'estado', 'motivo_registro', 'actividad_realizada',
            'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual',
            'tolerancia_movilidad',
        ]],
        'registros_conductuales' => ['CONDUCTA', 'cod_registro_conductual', [
            'cod_registro_conductual', 'cod_residente', 'cod_personal', 'cod_jornada',
            'cod_atencion', 'fecha_hora', 'estado_animo', 'apatia', 'agitacion', 'agresividad',
            'ansiedad', 'aislamiento', 'deambulacion', 'participacion', 'cambio_conducta',
            'descripcion', 'intervencion', 'respuesta', 'estado',
        ]],
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(array_keys(self::ORIGENES));
            $this->verificarReferencias(array_keys(self::ORIGENES));
            DB::statement(<<<'SQL'
                CREATE TABLE registros_observaciones_cuidado_transicion (
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
            foreach (self::ORIGENES as $tabla => [$tipo, $codigo, $columnas]) {
                $actual = Schema::getColumnListing($tabla);
                if (array_diff($actual, $columnas) || array_diff($columnas, $actual)) {
                    throw new RuntimeException('La estructura del origen cambió: '.$tabla);
                }
                $fisicas = array_map(fn ($columna) => $columna === $codigo ? self::CODIGO : $columna, $columnas);
                $seleccion = implode(', ', $columnas);
                $destino = implode(', ', $fisicas);
                DB::insert('INSERT INTO '.self::TEMPORAL." ($destino, tipo_evento) SELECT $seleccion, ? FROM $tabla", [$tipo]);
                $this->comparar($tabla, $columnas, $fisicas, $tipo);
            }
            foreach (array_keys(self::ORIGENES) as $tabla) {
                Schema::drop($tabla);
            }
            Schema::rename(self::TEMPORAL, self::DESTINO);
            $this->indices(self::DESTINO);
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn(self::DESTINO, 'cod_control_iadl')) {
            throw new LogicException('Revierta primero la consolidación de controles cognitivos e IADL.');
        }
        DB::transaction(function (): void {
            $this->bloquear([self::DESTINO]);
            $this->verificarReferencias([self::DESTINO]);
            Schema::rename(self::DESTINO, self::TEMPORAL);
            DB::statement(<<<'SQL'
                CREATE TABLE registros_movilidad (
                    cod_movilidad varchar(20) NOT NULL PRIMARY KEY,
                    cod_residente varchar(20) NOT NULL,
                    cod_personal varchar(20) NOT NULL,
                    cod_jornada varchar(20),
                    cod_atencion varchar(20),
                    fecha_hora timestamp(0) NOT NULL,
                    marcha varchar(40),
                    equilibrio varchar(40),
                    traslado varchar(40),
                    tipo_apoyo varchar(60),
                    dispositivo varchar(80),
                    fatiga varchar(30),
                    riesgo_caida varchar(30),
                    observacion text,
                    estado varchar(20) NOT NULL,
                    motivo_registro varchar(30),
                    actividad_realizada varchar(40),
                    distancia_metros numeric(7,2),
                    dolor_movilidad boolean,
                    mareo boolean,
                    disnea boolean,
                    debilidad boolean,
                    cambio_habitual varchar(20),
                    tolerancia_movilidad varchar(30),
                    FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                    CONSTRAINT fk_movilidad_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT
                )
                SQL);
            DB::statement(<<<'SQL'
                CREATE TABLE registros_conductuales (
                    cod_registro_conductual varchar(20) NOT NULL PRIMARY KEY,
                    cod_residente varchar(20) NOT NULL,
                    cod_personal varchar(20) NOT NULL,
                    cod_jornada varchar(20),
                    cod_atencion varchar(20),
                    fecha_hora timestamp(0) NOT NULL,
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
                    estado varchar(20) NOT NULL,
                    FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_atencion) REFERENCES atenciones(cod_atencion) ON DELETE RESTRICT,
                    CONSTRAINT fk_conductual_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT
                )
                SQL);
            foreach (self::ORIGENES as $tabla => [$tipo, $codigo, $columnas]) {
                $fisicas = array_map(fn ($columna) => $columna === $codigo ? self::CODIGO : $columna, $columnas);
                [$seleccion, $parametros] = $this->seleccion($fisicas, $tipo);
                DB::insert('INSERT INTO '.$tabla.' ('.implode(', ', $columnas).') '.$seleccion, $parametros);
                $this->comparar($tabla, $columnas, $fisicas, $tipo);
                $this->indices($tabla);
            }
            Schema::drop(self::TEMPORAL);
        });
    }

    private function seleccion(array $fisicas, string $tipo): array
    {
        $select = 'SELECT '.implode(', ', $fisicas).' FROM '.self::TEMPORAL;

        return [$select.' WHERE tipo_evento = ?', [$tipo]];
    }

    private function comparar(string $tabla, array $columnas, array $fisicas, string $tipo): void
    {
        $original = 'SELECT '.implode(', ', $columnas).' FROM '.$tabla;
        [$seleccion, $parametros] = $this->seleccion($fisicas, $tipo);
        if (DB::scalar("SELECT EXISTS ($original EXCEPT $seleccion)", $parametros)
            || DB::scalar("SELECT EXISTS ($seleccion EXCEPT $original)", $parametros)) {
            throw new RuntimeException('Diferencia en la copia de '.$tabla.'.');
        }
    }

    private function bloquear(array $tablas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout = '5s'");
            DB::statement('LOCK TABLE '.implode(', ', $tablas).' IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La transición requiere PostgreSQL o SQLite.');
        }
    }

    private function verificarReferencias(array $origenes): void
    {
        foreach (Schema::getTables() as $tabla) {
            foreach (Schema::getForeignKeys($tabla['name']) as $fk) {
                if (in_array($fk['foreign_table'], $origenes, true)) {
                    throw new RuntimeException('Hay una FK entrante sin transición para '.$fk['foreign_table'].'.');
                }
            }
        }
    }

    private function indices(string $tabla): void
    {
        $indices = [
            'registros_observaciones_cuidado' => [
                'observacion_tipo_residente_fecha_i' => 'tipo_evento, cod_residente, fecha_hora',
                'observacion_residente_i' => 'cod_residente',
                'observacion_personal_i' => 'cod_personal',
                'observacion_jornada_i' => 'cod_jornada',
                'observacion_atencion_residente_i' => 'cod_atencion, cod_residente',
            ],
            'registros_movilidad' => [
                'registros_movilidad_cod_residente_fecha_hora_index' => 'cod_residente, fecha_hora',
            ],
            'registros_conductuales' => [
                'registros_conductuales_cod_residente_fecha_hora_index' => 'cod_residente, fecha_hora',
            ],
        ];
        foreach ($indices[$tabla] as $nombre => $columnas) {
            DB::statement("CREATE INDEX $nombre ON $tabla ($columnas)");
        }
    }
};
