<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLA = 'registros_consumo';

    private const COMUNES = ['cod_residente', 'cod_personal', 'cod_jornada', 'fecha_hora', 'tolerancia', 'observacion', 'estado'];

    private const ORIGENES = [
        'registros_ingesta' => ['INGESTA', 'cod_ingesta', ['tipo_comida', 'porcentaje_consumido', 'apetito', 'dificultad_deglucion']],
        'registros_hidratacion' => ['HIDRATACION', 'cod_hidratacion', ['cantidad_ml', 'tipo_liquido']],
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(array_keys(self::ORIGENES));
            foreach (self::ORIGENES as $tabla => [$_, $codigo, $campos]) {
                $actual = Schema::getColumnListing($tabla);
                $esperado = [$codigo, ...self::COMUNES, ...$campos];
                if (array_diff($actual, $esperado) || array_diff($esperado, $actual)) {
                    throw new RuntimeException('La estructura de '.$tabla.' requiere una transición explícita.');
                }
            }
            if (DB::table('registros_ingesta as i')->join('registros_hidratacion as h', 'i.cod_ingesta', '=', 'h.cod_hidratacion')->exists()) {
                throw new RuntimeException('Existen códigos compartidos entre ingesta e hidratación.');
            }

            DB::statement(<<<'SQL'
                CREATE TABLE registros_consumo (
                    cod_registro_consumo varchar(20) PRIMARY KEY NOT NULL,
                    tipo_evento varchar(12) NOT NULL,
                    cod_residente varchar(20) NOT NULL REFERENCES residentes(cod_residente) ON DELETE RESTRICT,
                    cod_personal varchar(20) NOT NULL REFERENCES personal(cod_personal) ON DELETE RESTRICT,
                    cod_jornada varchar(20) NOT NULL REFERENCES jornadas(cod_jornada) ON DELETE RESTRICT,
                    fecha_hora timestamp(0) NOT NULL,
                    tolerancia varchar(30),
                    observacion text,
                    estado varchar(20) NOT NULL,
                    tipo_comida varchar(40),
                    porcentaje_consumido decimal(5,2),
                    apetito varchar(30),
                    dificultad_deglucion boolean,
                    cantidad_ml decimal(8,2),
                    tipo_liquido varchar(60),
                    CONSTRAINT ck_consumo_tipo CHECK (
                        (tipo_evento = 'INGESTA' AND tipo_comida IS NOT NULL AND cantidad_ml IS NULL AND tipo_liquido IS NULL)
                        OR (tipo_evento = 'HIDRATACION' AND cantidad_ml IS NOT NULL AND tipo_comida IS NULL
                            AND porcentaje_consumido IS NULL AND apetito IS NULL AND dificultad_deglucion IS NULL)
                    )
                )
                SQL);
            DB::statement('CREATE INDEX consumo_tipo_residente_fecha_i ON registros_consumo (tipo_evento, cod_residente, fecha_hora)');
            DB::statement('CREATE INDEX consumo_residente_i ON registros_consumo (cod_residente)');
            DB::statement('CREATE INDEX consumo_personal_i ON registros_consumo (cod_personal)');
            DB::statement('CREATE INDEX consumo_jornada_i ON registros_consumo (cod_jornada)');
            foreach (self::ORIGENES as $tabla => [$tipo, $codigo, $campos]) {
                $comunes = implode(', ', [...self::COMUNES, ...$campos]);
                DB::insert("INSERT INTO registros_consumo (cod_registro_consumo, tipo_evento, $comunes) SELECT $codigo, ?, $comunes FROM $tabla", [$tipo]);
            }
            $this->comparar();
            $this->verificarReferencias();
            foreach (array_keys(self::ORIGENES) as $tabla) {
                Schema::drop($tabla);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->bloquear([self::TABLA]);
            $this->verificarReferencias();
            (require __DIR__.'/2026_09_18_001046_create_registros_ingesta_table.php')->up();
            (require __DIR__.'/2026_09_18_001047_create_registros_hidratacion_table.php')->up();
            foreach (self::ORIGENES as $tabla => [$tipo, $codigo, $campos]) {
                $comunes = implode(', ', [...self::COMUNES, ...$campos]);
                DB::insert("INSERT INTO $tabla ($codigo, $comunes) SELECT cod_registro_consumo, $comunes FROM registros_consumo WHERE tipo_evento = ?", [$tipo]);
            }
            $this->comparar();
            Schema::drop(self::TABLA);
        });
    }

    private function comparar(): void
    {
        foreach (self::ORIGENES as $tabla => [$tipo, $codigo, $campos]) {
            $comunes = implode(', ', [...self::COMUNES, ...$campos]);
            $original = "SELECT $codigo, $comunes FROM $tabla";
            $consolidada = "SELECT cod_registro_consumo, $comunes FROM registros_consumo WHERE tipo_evento = ?";
            if (DB::scalar("SELECT EXISTS ($original EXCEPT $consolidada)", [$tipo])
                || DB::scalar("SELECT EXISTS ($consolidada EXCEPT $original)", [$tipo])) {
                throw new RuntimeException('La copia de '.$tabla.' difiere del origen.');
            }
        }
    }

    private function bloquear(array $tablas): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout = '5s'");
            DB::statement('LOCK TABLE '.implode(', ', $tablas).' IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La transición admite PostgreSQL y SQLite.');
        }
    }

    private function verificarReferencias(): void
    {
        foreach (Schema::getTables() as $tabla) {
            foreach (Schema::getForeignKeys($tabla['name']) as $fk) {
                if (isset(self::ORIGENES[$fk['foreign_table']]) || $fk['foreign_table'] === self::TABLA) {
                    throw new RuntimeException('Hay una clave foránea entrante que requiere una transición explícita.');
                }
            }
        }
    }
};
