<?php

use App\Compartido\Dominio\Calendario\SeleccionSemanal;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PADRES = ['horarios_prescripcion' => 'cod_horario_prescripcion', 'programaciones_cuidado' => 'cod_programacion'];

    public function up(): void
    {
        DB::transaction(function (): void {
            $this->bloquear();
            $selecciones = [];
            // Un texto desconocido no se convierte en un horario inventado.
            foreach (self::PADRES as $tabla => $pk) {
                foreach (DB::table($tabla)->orderBy($pk)->cursor() as $fila) {
                    $selecciones[$tabla][$fila->$pk] = SeleccionSemanal::codigo($fila->dias_semana, $tabla === 'horarios_prescripcion');
                }
            }
            $triggers = $this->suspenderSQLite();
            Schema::create('patrones_semanales', function (Blueprint $t): void {
                $t->string('cod_patron_semanal', 20)->primary();
                $t->boolean('todos_los_dias');
            });
            DB::statement('CREATE TABLE dias_patron_semanal (cod_patron_semanal VARCHAR(20) NOT NULL, dia_semana SMALLINT NOT NULL CHECK (dia_semana BETWEEN 1 AND 7), PRIMARY KEY (cod_patron_semanal,dia_semana), FOREIGN KEY (cod_patron_semanal) REFERENCES patrones_semanales(cod_patron_semanal) ON DELETE RESTRICT)');
            DB::table('patrones_semanales')->insert(['cod_patron_semanal' => SeleccionSemanal::TODOS, 'todos_los_dias' => true]);
            foreach (range(0, 127) as $mascara) {
                $dias = array_values(array_filter(range(1, 7), fn ($dia) => ($mascara & (1 << ($dia - 1))) !== 0));
                $codigo = SeleccionSemanal::identidad($dias);
                DB::table('patrones_semanales')->insert(['cod_patron_semanal' => $codigo, 'todos_los_dias' => false]);
                foreach ($dias as $dia) {
                    DB::table('dias_patron_semanal')->insert(['cod_patron_semanal' => $codigo, 'dia_semana' => $dia]);
                }
            }
            foreach (self::PADRES as $tabla => $pk) {
                Schema::table($tabla, function (Blueprint $t): void {
                    $t->string('cod_patron_semanal', 20)->default(SeleccionSemanal::TODOS);
                    $t->index('cod_patron_semanal');
                    $t->foreign('cod_patron_semanal')->references('cod_patron_semanal')->on('patrones_semanales')->restrictOnDelete();
                });
                foreach ($selecciones[$tabla] ?? [] as $id => $codigo) {
                    DB::table($tabla)->where($pk, $id)->update(['cod_patron_semanal' => $codigo]);
                }
            }
            DB::statement('DROP INDEX uq_horario_prescripcion_activo');
            foreach (self::PADRES as $tabla => $_) {
                Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn('dias_semana'));
            }
            DB::statement("CREATE UNIQUE INDEX uq_horario_prescripcion_activo ON horarios_prescripcion (cod_prescripcion,hora_programada,cod_patron_semanal) WHERE estado IN ('ACTIVA','ACTIVO')");
            $this->restaurarSQLite($triggers);
            $this->protegerCatalogo(true);
            $this->verificarSQLite();
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $this->bloquear();
            $dias = DB::table('dias_patron_semanal')->orderBy('dia_semana')->get()->groupBy('cod_patron_semanal');
            $textos = [];
            foreach (DB::table('patrones_semanales')->get() as $p) {
                $miembros = $dias->get($p->cod_patron_semanal, collect())->pluck('dia_semana')->all();
                if ($p->todos_los_dias) {
                    if ($p->cod_patron_semanal !== SeleccionSemanal::TODOS || $miembros !== []) {
                        throw new RuntimeException('El catálogo semanal difiere de su contrato.');
                    }
                    $textos[$p->cod_patron_semanal] = null;
                } else {
                    if (SeleccionSemanal::identidad(array_map('intval', $miembros)) !== $p->cod_patron_semanal) {
                        throw new RuntimeException('Un conjunto semanal no coincide con su identidad.');
                    }
                    $textos[$p->cod_patron_semanal] = $miembros === [] ? 'NINGUNO' : implode(',', $miembros);
                }
            }
            $triggers = $this->suspenderSQLite();
            if (DB::getDriverName() === 'pgsql') {
                $this->protegerCatalogo(false);
            }
            DB::statement('DROP INDEX uq_horario_prescripcion_activo');
            foreach (self::PADRES as $tabla => $pk) {
                Schema::table($tabla, fn (Blueprint $t) => $t->string('dias_semana', 50)->nullable());
                foreach ($textos as $codigo => $texto) {
                    DB::table($tabla)->where('cod_patron_semanal', $codigo)->update(['dias_semana' => $texto]);
                }
                if (DB::getDriverName() === 'sqlite') {
                    Schema::table($tabla, fn (Blueprint $t) => $t->dropForeign(['cod_patron_semanal']));
                }
                Schema::table($tabla, fn (Blueprint $t) => $t->dropIndex(['cod_patron_semanal']));
                Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn('cod_patron_semanal'));
            }
            Schema::drop('dias_patron_semanal');
            Schema::drop('patrones_semanales');
            DB::statement("CREATE UNIQUE INDEX uq_horario_prescripcion_activo ON horarios_prescripcion (cod_prescripcion,hora_programada,COALESCE(dias_semana,'')) WHERE estado IN ('ACTIVA','ACTIVO')");
            $this->restaurarSQLite($triggers);
            $this->verificarSQLite();
        });
    }

    private function bloquear(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SET LOCAL lock_timeout='5s'");
            DB::statement('LOCK TABLE horarios_prescripcion,programaciones_cuidado IN ACCESS EXCLUSIVE MODE');
        } elseif (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('La normalización requiere PostgreSQL o SQLite.');
        }
    }

    private function protegerCatalogo(bool $crear): void
    {
        foreach (['patrones_semanales', 'dias_patron_semanal'] as $tabla) {
            $id = 'fn5_sem_'.$tabla;
            if (DB::getDriverName() === 'pgsql') {
                if ($crear) {
                    DB::unprepared('CREATE FUNCTION '.$id."() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'La selección semanal canónica es inmutable' USING ERRCODE='23514'; RETURN NULL; END; $$;");
                    DB::statement('CREATE TRIGGER '.$id.' BEFORE INSERT OR UPDATE OR DELETE ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION '.$id.'()');
                } else {
                    DB::statement('DROP TRIGGER '.$id.' ON '.$tabla);
                    DB::statement('DROP FUNCTION '.$id.'()');
                }
            } elseif ($crear) {
                foreach (['INSERT', 'UPDATE', 'DELETE'] as $evento) {
                    DB::unprepared('CREATE TRIGGER '.$id.'_'.strtolower($evento).' BEFORE '.$evento.' ON '.$tabla." BEGIN SELECT RAISE(ABORT,'La selección semanal canónica es inmutable'); END");
                }
            }
        }
    }

    private function suspenderSQLite(): array
    {
        if (DB::getDriverName() !== 'sqlite') {
            return [];
        }
        DB::statement('PRAGMA defer_foreign_keys=ON');
        $triggers = DB::select("SELECT name,sql FROM sqlite_master WHERE type='trigger'");
        foreach ($triggers as $t) {
            DB::statement('DROP TRIGGER "'.$t->name.'"');
        }

        return $triggers;
    }

    private function restaurarSQLite(array $triggers): void
    {
        foreach ($triggers as $t) {
            if (! str_starts_with($t->name, 'fn5_sem_')) {
                DB::unprepared($t->sql);
            }
        }
    }

    private function verificarSQLite(): void
    {
        if (DB::getDriverName() === 'sqlite' && DB::select('PRAGMA foreign_key_check') !== []) {
            throw new RuntimeException('La selección semanal dejó referencias inválidas.');
        }
    }
};
