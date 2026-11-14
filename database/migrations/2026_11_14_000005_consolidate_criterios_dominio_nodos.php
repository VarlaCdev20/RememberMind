<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CAMPOS = [
        'cod_criterio_dominio_resultado' => 'cod_criterio_dominio_resultado',
        'cod_dominio_valores' => 'criterio_cod_dominio_valores',
        'estado' => 'criterio_estado',
        'fecha_hora_creacion' => 'criterio_fecha_hora_creacion',
        'cod_usuario_creacion' => 'criterio_cod_usuario_creacion',
        'observacion' => 'criterio_observacion',
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(true);
            $this->verificarReferencia('criterios_dominios_resultado');
            DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN cod_criterio_dominio_resultado varchar(20)');
            DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN criterio_cod_dominio_valores varchar(20) REFERENCES dominios_valores_expertos(cod_dominio_valores) ON DELETE RESTRICT');
            DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN criterio_cod_usuario_creacion varchar(20) REFERENCES usuarios(cod_usuario) ON DELETE RESTRICT');
            DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN criterio_estado varchar(20)');
            DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN criterio_fecha_hora_creacion timestamp(0)');
            DB::statement('ALTER TABLE nodos_semanticos ADD COLUMN criterio_observacion text');
            DB::statement('CREATE UNIQUE INDEX nodo_criterio_resultado_u ON nodos_semanticos (cod_criterio_dominio_resultado)');
            DB::statement('CREATE INDEX nodo_criterio_resultado_dominio_i ON nodos_semanticos (criterio_cod_dominio_valores)');
            DB::statement('CREATE INDEX nodo_criterio_resultado_usuario_i ON nodos_semanticos (criterio_cod_usuario_creacion)');
            DB::statement('CREATE INDEX nodo_criterio_resultado_estado_i ON nodos_semanticos (criterio_estado)');
            $asignaciones = [];
            foreach (self::CAMPOS as $origen => $destino) {
                $asignaciones[] = "$destino = c.$origen";
            }
            $this->guardas(true);
            DB::statement('UPDATE nodos_semanticos SET '.implode(', ', $asignaciones)
                .' FROM criterios_dominios_resultado c WHERE cod_nodo_semantico = c.cod_nodo_criterio');
            $this->comparar();
            $this->cambiarReferencia('nodos_semanticos');
            Schema::drop('criterios_dominios_resultado');
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->bloquear(false);
            $this->verificarReferencia('nodos_semanticos');
            (require __DIR__.'/2026_10_08_120010_create_criterios_dominios_resultado_table.php')->up();
            DB::statement('INSERT INTO criterios_dominios_resultado (cod_nodo_criterio, '.implode(', ', array_keys(self::CAMPOS)).') '.$this->proyeccion());
            $this->comparar();
            $this->cambiarReferencia('criterios_dominios_resultado');
            $this->guardas(false);
            foreach (['u', 'dominio_i', 'usuario_i', 'estado_i'] as $sufijo) {
                DB::statement('DROP INDEX nodo_criterio_resultado_'.$sufijo);
            }
            foreach (self::CAMPOS as $columna) {
                DB::statement('ALTER TABLE nodos_semanticos DROP COLUMN '.$columna);
            }
        });
    }

    private function cambiarReferencia(string $destino): void
    {
        Schema::table('consecuencias_regla_experta', function (Blueprint $tabla) use ($destino): void {
            $tabla->dropForeign(DB::getDriverName() === 'pgsql' ? 'se13_f2' : ['cod_criterio_dominio_resultado']);
            $tabla->foreign('cod_criterio_dominio_resultado', 'se13_f2')
                ->references('cod_criterio_dominio_resultado')->on($destino)->restrictOnDelete()->noActionOnUpdate();
        });
    }

    private function verificarReferencia(string $origen): void
    {
        $encontradas = 0;
        foreach (Schema::getTables() as $tabla) {
            foreach (Schema::getForeignKeys($tabla['name']) as $fk) {
                if ($fk['foreign_table'] !== $origen || $fk['foreign_columns'] !== ['cod_criterio_dominio_resultado']) {
                    continue;
                }
                if ($tabla['name'] !== 'consecuencias_regla_experta' || $fk['columns'] !== ['cod_criterio_dominio_resultado']) {
                    throw new RuntimeException('Hay una referencia al criterio que requiere una transición explícita.');
                }
                $encontradas++;
            }
        }
        if ($encontradas !== 1) {
            throw new RuntimeException('La referencia original de las consecuencias no es la esperada.');
        }
    }

    private function guardas(bool $crear): void
    {
        $ausencia = 'NEW.cod_criterio_dominio_resultado IS NULL AND (NEW.criterio_cod_dominio_valores IS NOT NULL OR NEW.criterio_cod_usuario_creacion IS NOT NULL OR NEW.criterio_estado IS NOT NULL OR NEW.criterio_fecha_hora_creacion IS NOT NULL OR NEW.criterio_observacion IS NOT NULL)';
        $incompleto = 'NEW.cod_criterio_dominio_resultado IS NOT NULL AND (NEW.criterio_cod_dominio_valores IS NULL OR NEW.criterio_cod_usuario_creacion IS NULL OR NEW.criterio_estado IS NULL OR NEW.criterio_fecha_hora_creacion IS NULL)';
        if (DB::getDriverName() === 'pgsql') {
            if ($crear) {
                DB::statement('ALTER TABLE nodos_semanticos ADD CONSTRAINT ck_nodo_criterio_bloque CHECK (NOT ('.str_replace('NEW.', '', $ausencia).') AND NOT ('.str_replace('NEW.', '', $incompleto).'))');
            } else {
                DB::statement('ALTER TABLE nodos_semanticos DROP CONSTRAINT ck_nodo_criterio_bloque');
            }

            return;
        }

        foreach (['INSERT' => 'ins', 'UPDATE' => 'upd'] as $evento => $sufijo) {
            $nombre = 'trg_nodo_criterio_bloque_'.$sufijo;
            if ($crear) {
                DB::unprepared("CREATE TRIGGER $nombre BEFORE $evento ON nodos_semanticos FOR EACH ROW WHEN ($ausencia) OR ($incompleto) BEGIN SELECT RAISE(ABORT, 'Bloque de criterio incompleto'); END");
            } else {
                DB::statement('DROP TRIGGER '.$nombre);
            }
        }
    }

    private function proyeccion(): string
    {
        return 'SELECT cod_nodo_semantico, '.implode(', ', self::CAMPOS).' FROM nodos_semanticos WHERE cod_criterio_dominio_resultado IS NOT NULL';
    }

    private function comparar(): void
    {
        $origen = 'SELECT cod_nodo_criterio, '.implode(', ', array_keys(self::CAMPOS)).' FROM criterios_dominios_resultado';
        $consolidado = $this->proyeccion();
        if (DB::scalar("SELECT EXISTS ($origen EXCEPT $consolidado)") || DB::scalar("SELECT EXISTS ($consolidado EXCEPT $origen)")) {
            throw new RuntimeException('La proyección del criterio difiere del origen.');
        }
    }

    private function bloquear(bool $subir): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout = '5s'");
            DB::statement('LOCK TABLE consecuencias_regla_experta, nodos_semanticos'.($subir ? ', criterios_dominios_resultado' : '').' IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La transición requiere PostgreSQL o SQLite.');
        }
    }
};
