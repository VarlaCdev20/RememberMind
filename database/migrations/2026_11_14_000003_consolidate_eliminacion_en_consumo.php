<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DESTINO = 'registros_consumo';

    private const CODIGO = 'cod_registro_consumo';

    private const TEMPORAL = 'registros_consumo_transicion';

    private const ORIGENES = [
        'registros_consumo' => ['', 'cod_registro_consumo', [
            'cod_registro_consumo', 'tipo_evento', 'cod_residente', 'cod_personal', 'cod_jornada',
            'fecha_hora', 'tolerancia', 'observacion', 'estado', 'tipo_comida',
            'porcentaje_consumido', 'apetito', 'dificultad_deglucion', 'cantidad_ml', 'tipo_liquido',
        ]],
        'registros_eliminacion' => ['ELIMINACION', 'cod_eliminacion', [
            'cod_eliminacion', 'cod_residente', 'cod_personal', 'cod_jornada', 'fecha_hora',
            'tipo_eliminacion', 'cantidad', 'caracteristica', 'continencia', 'observacion', 'estado',
            'cantidad_cualitativa', 'volumen_ml', 'color_orina', 'aspecto_orina', 'olor_orina',
            'tipo_miccion', 'tipo_bristol', 'color_heces', 'esfuerzo_defecacion', 'presencia_sangre',
            'presencia_moco', 'molestia_eliminacion', 'descripcion_molestia',
        ]],
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(array_keys(self::ORIGENES));
            $this->verificarReferencias(array_keys(self::ORIGENES));
            DB::statement(<<<'SQL'
                CREATE TABLE registros_consumo_transicion (
                    cod_registro_consumo varchar(20) NOT NULL PRIMARY KEY,
                    tipo_evento varchar(12) NOT NULL,
                    cod_residente varchar(20) NOT NULL,
                    cod_personal varchar(20) NOT NULL,
                    cod_jornada varchar(20) NOT NULL,
                    fecha_hora timestamp(0) NOT NULL,
                    estado varchar(20) NOT NULL,
                    tolerancia varchar(30),
                    observacion text,
                    tipo_comida varchar(40),
                    porcentaje_consumido numeric(5,2),
                    apetito varchar(30),
                    dificultad_deglucion boolean,
                    cantidad_ml numeric(8,2),
                    tipo_liquido varchar(60),
                    tipo_eliminacion varchar(30),
                    cantidad varchar(40),
                    caracteristica varchar(120),
                    continencia varchar(30),
                    cantidad_cualitativa varchar(20),
                    volumen_ml numeric(8,2),
                    color_orina varchar(30),
                    aspecto_orina varchar(30),
                    olor_orina varchar(30),
                    tipo_miccion varchar(30),
                    tipo_bristol smallint,
                    color_heces varchar(30),
                    esfuerzo_defecacion varchar(30),
                    presencia_sangre boolean,
                    presencia_moco boolean,
                    molestia_eliminacion boolean,
                    descripcion_molestia varchar(250),
                    CONSTRAINT ck_consumo_tipo CHECK ((((tipo_evento = 'INGESTA' AND tipo_comida IS NOT NULL AND cantidad_ml IS NULL AND tipo_liquido IS NULL) OR (tipo_evento = 'HIDRATACION' AND cantidad_ml IS NOT NULL AND tipo_comida IS NULL AND porcentaje_consumido IS NULL AND apetito IS NULL AND dificultad_deglucion IS NULL)) AND tipo_eliminacion IS NULL AND cantidad IS NULL AND caracteristica IS NULL AND continencia IS NULL AND cantidad_cualitativa IS NULL AND volumen_ml IS NULL AND color_orina IS NULL AND aspecto_orina IS NULL AND olor_orina IS NULL AND tipo_miccion IS NULL AND tipo_bristol IS NULL AND color_heces IS NULL AND esfuerzo_defecacion IS NULL AND presencia_sangre IS NULL AND presencia_moco IS NULL AND molestia_eliminacion IS NULL AND descripcion_molestia IS NULL) OR (tipo_evento = 'ELIMINACION' AND tipo_eliminacion IS NOT NULL AND tolerancia IS NULL AND tipo_comida IS NULL AND porcentaje_consumido IS NULL AND apetito IS NULL AND dificultad_deglucion IS NULL AND cantidad_ml IS NULL AND tipo_liquido IS NULL)),
                    FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT
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
                $tipoSeparado = $tipo !== '';
                DB::insert('INSERT INTO '.self::TEMPORAL." ($destino".($tipoSeparado ? ', tipo_evento' : '')
                    .") SELECT $seleccion".($tipoSeparado ? ', ?' : '')." FROM $tabla", $tipoSeparado ? [$tipo] : []);
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
        DB::transaction(function (): void {
            $this->bloquear([self::DESTINO]);
            $this->verificarReferencias([self::DESTINO]);
            Schema::rename(self::DESTINO, self::TEMPORAL);
            foreach (['consumo_tipo_residente_fecha_i', 'consumo_residente_i', 'consumo_personal_i', 'consumo_jornada_i'] as $indice) {
                DB::statement('DROP INDEX '.$indice);
            }
            DB::statement(<<<'SQL'
                CREATE TABLE registros_consumo (
                    cod_registro_consumo varchar(20) NOT NULL PRIMARY KEY,
                    tipo_evento varchar(12) NOT NULL,
                    cod_residente varchar(20) NOT NULL,
                    cod_personal varchar(20) NOT NULL,
                    cod_jornada varchar(20) NOT NULL,
                    fecha_hora timestamp(0) NOT NULL,
                    tolerancia varchar(30),
                    observacion text,
                    estado varchar(20) NOT NULL,
                    tipo_comida varchar(40),
                    porcentaje_consumido numeric(5,2),
                    apetito varchar(30),
                    dificultad_deglucion boolean,
                    cantidad_ml numeric(8,2),
                    tipo_liquido varchar(60),
                    FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                    CONSTRAINT ck_consumo_tipo CHECK ((tipo_evento = 'INGESTA' AND tipo_comida IS NOT NULL AND cantidad_ml IS NULL AND tipo_liquido IS NULL) OR (tipo_evento = 'HIDRATACION' AND cantidad_ml IS NOT NULL AND tipo_comida IS NULL AND porcentaje_consumido IS NULL AND apetito IS NULL AND dificultad_deglucion IS NULL))
                )
                SQL);
            DB::statement(<<<'SQL'
                CREATE TABLE registros_eliminacion (
                    cod_eliminacion varchar(20) NOT NULL PRIMARY KEY,
                    cod_residente varchar(20) NOT NULL,
                    cod_personal varchar(20) NOT NULL,
                    cod_jornada varchar(20) NOT NULL,
                    fecha_hora timestamp(0) NOT NULL,
                    tipo_eliminacion varchar(30) NOT NULL,
                    cantidad varchar(40),
                    caracteristica varchar(120),
                    continencia varchar(30),
                    observacion text,
                    estado varchar(20) NOT NULL,
                    cantidad_cualitativa varchar(20),
                    volumen_ml numeric(8,2),
                    color_orina varchar(30),
                    aspecto_orina varchar(30),
                    olor_orina varchar(30),
                    tipo_miccion varchar(30),
                    tipo_bristol smallint,
                    color_heces varchar(30),
                    esfuerzo_defecacion varchar(30),
                    presencia_sangre boolean,
                    presencia_moco boolean,
                    molestia_eliminacion boolean,
                    descripcion_molestia varchar(250),
                    FOREIGN KEY (cod_residente) REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_personal) REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                    FOREIGN KEY (cod_jornada) REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT
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

        return $tipo === ''
            ? [$select." WHERE tipo_evento IN ('INGESTA', 'HIDRATACION')", []]
            : [$select.' WHERE tipo_evento = ?', [$tipo]];
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
            'registros_consumo' => [
                'consumo_tipo_residente_fecha_i' => 'tipo_evento, cod_residente, fecha_hora',
                'consumo_residente_i' => 'cod_residente',
                'consumo_personal_i' => 'cod_personal',
                'consumo_jornada_i' => 'cod_jornada',
            ],
            'registros_eliminacion' => [
                'registros_eliminacion_cod_residente_fecha_hora_index' => 'cod_residente, fecha_hora',
            ],
        ];
        foreach ($indices[$tabla] as $nombre => $columnas) {
            DB::statement("CREATE INDEX $nombre ON $tabla ($columnas)");
        }
    }
};
