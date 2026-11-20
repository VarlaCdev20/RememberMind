<?php

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ContextoIadl;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\GuardasReduccionConservacion;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\ProyeccionReduccionExperta;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLA = 'hechos_instantanea_experta';

    private const CAMPO = 'iadl_cod_funcionalidad_instantanea';

    public function up(): void
    {
        $this->cambiar(true);
    }

    public function down(): void
    {
        $this->cambiar(false);
    }

    private function cambiar(bool $derivar): void
    {
        DB::transaction(function () use ($derivar): void {
            $pg = DB::getDriverName() === 'pgsql';
            if ($pg) {
                DB::statement("SET LOCAL lock_timeout='5s'");
                DB::statement('LOCK TABLE evaluaciones_expertas,hechos_instantanea_experta IN ACCESS EXCLUSIVE MODE');
            } elseif (DB::getDriverName() !== 'sqlite') {
                throw new RuntimeException('La normalización requiere PostgreSQL o SQLite.');
            }
            if (Schema::hasColumn(self::TABLA, self::CAMPO) !== $derivar) {
                throw new LogicException('El esquema IADL no corresponde al punto de partida.');
            }
            $columnas = Schema::getColumnListing(self::TABLA);
            if (! $derivar) {
                $columnas[] = self::CAMPO;
            }
            $origen = $derivar ? DB::table(self::TABLA) : ContextoIadl::consulta(DB::connection(), self::TABLA);
            if ($derivar && DB::table(self::TABLA.' as h')->join('evaluaciones_expertas as e', 'e.cod_evaluacion_experta', '=', 'h.cod_evaluacion_experta')
                ->whereNotNull('h.cod_evidencia_iadl_instantanea')->where(fn ($q) => $q->whereNull('e.cod_funcionalidad_instantanea')->orWhereColumn('h.'.self::CAMPO, '<>', 'e.cod_funcionalidad_instantanea'))->exists()) {
                throw new RuntimeException('La caracterización IADL difiere de la funcionalidad conservada.');
            }
            $origen->select($columnas);
            DB::statement('CREATE TEMPORARY TABLE fn5_copia_iadl AS '.$origen->toSql(), $origen->getBindings());
            $triggers = $this->suspenderSQLite();
            $guardas = new GuardasReduccionConservacion(true);
            if ($pg) {
                $guardas->retirarHechos();
                DB::statement('ALTER TABLE '.self::TABLA.' DROP CONSTRAINT red_iadl_bloque');
            }
            if (! $derivar) {
                $this->contexto(false);
            }
            DB::statement('DROP INDEX red_iadl_orden_u');
            if ($derivar) {
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->dropForeign($pg ? 'red_iadl_fun_f' : [self::CAMPO, 'cod_evaluacion_experta']));
                DB::statement('DROP INDEX red_iadl_fun_ix');
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->dropColumn(self::CAMPO));
                DB::statement('CREATE UNIQUE INDEX red_iadl_orden_u ON '.self::TABLA.' (cod_evaluacion_experta,iadl_orden) WHERE cod_evidencia_iadl_instantanea IS NOT NULL');
            } else {
                Schema::table(self::TABLA, fn (Blueprint $t) => $t->string(self::CAMPO, 20)->nullable());
                DB::statement('UPDATE '.self::TABLA.' SET '.self::CAMPO.'=CASE WHEN cod_evidencia_iadl_instantanea IS NOT NULL THEN (SELECT e.cod_funcionalidad_instantanea FROM evaluaciones_expertas e WHERE e.cod_evaluacion_experta='.self::TABLA.'.cod_evaluacion_experta) END');
                Schema::table(self::TABLA, function (Blueprint $t): void {
                    $t->foreign([self::CAMPO, 'cod_evaluacion_experta'], 'red_iadl_fun_f')->references(['cod_funcionalidad_instantanea', 'cod_evaluacion_experta'])->on('evaluaciones_expertas')->restrictOnDelete();
                    $t->index([self::CAMPO, 'cod_evaluacion_experta'], 'red_iadl_fun_ix');
                });
                DB::statement('CREATE UNIQUE INDEX red_iadl_orden_u ON '.self::TABLA.' ('.self::CAMPO.',iadl_orden) WHERE cod_evidencia_iadl_instantanea IS NOT NULL');
            }
            foreach ($triggers as $t) {
                if (! str_starts_with($t->name, 'red_hecho_') && ! str_starts_with($t->name, 'red_iadl_bloque_') && ! str_starts_with($t->name, 'fn5_iadl_')) {
                    DB::unprepared($t->sql);
                }
            }
            $this->bloque($derivar);
            $guardas->instalarHechos($derivar);
            if ($derivar) {
                $this->contexto(true);
            }
            $reconstruida = ($derivar ? ContextoIadl::consulta(DB::connection(), self::TABLA) : DB::table(self::TABLA))->select($columnas);
            $sql = $reconstruida->toSql();
            foreach (['SELECT * FROM fn5_copia_iadl EXCEPT ALL '.$sql, $sql.' EXCEPT ALL SELECT * FROM fn5_copia_iadl'] as $diferencia) {
                if (! $pg) {
                    $diferencia = str_replace('EXCEPT ALL', 'EXCEPT', $diferencia);
                }
                if (DB::selectOne('SELECT COUNT(*) AS n FROM ('.$diferencia.') AS diferencias', $reconstruida->getBindings())->n !== 0) {
                    throw new RuntimeException('La proyección IADL cambió durante la transición.');
                }
            }
            if (! $pg) {
                if (DB::select('PRAGMA foreign_key_check') !== []) {
                    throw new RuntimeException('La transición IADL dejó referencias inválidas.');
                }
                // La reconstrucción de SQLite deja contadores diferidos del DROP antiguo;
                // se cierran únicamente después de verificar todas las FK del esquema final.
                DB::statement('PRAGMA defer_foreign_keys=OFF');
            }
            DB::statement('DROP TABLE fn5_copia_iadl');
        });
    }

    private function bloque(bool $derivado): void
    {
        $campos = array_values(ProyeccionReduccionExperta::IAD);
        $requeridos = ['cod_evidencia_iadl_instantanea', self::CAMPO, 'iadl_actividad_id_dominio', 'iadl_estado', 'iadl_oportunidad', 'iadl_orden'];
        if ($derivado) {
            $campos = array_values(array_diff($campos, [self::CAMPO]));
            $requeridos = array_values(array_diff($requeridos, [self::CAMPO]));
        }
        $prefijo = DB::getDriverName() === 'pgsql' ? '' : 'NEW.';
        $expr = '('.implode(' AND ', array_map(fn ($c) => $prefijo.$c.' IS NULL', $campos)).') OR ('.implode(' AND ', array_map(fn ($c) => $prefijo.$c.' IS NOT NULL', $requeridos)).' AND '.$prefijo.'iadl_orden>=0)';
        if (DB::table(self::TABLA)->whereRaw('('.str_replace('NEW.', '', $expr).') IS NOT TRUE')->exists()) {
            throw new RuntimeException('La transición no puede conservar una caracterización IADL incompleta.');
        }
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE '.self::TABLA.' ADD CONSTRAINT red_iadl_bloque CHECK (('.$expr.') IS TRUE)');
        } else {
            foreach (['INSERT', 'UPDATE'] as $evento) {
                DB::unprepared('CREATE TRIGGER red_iadl_bloque_'.strtolower($evento).' BEFORE '.$evento.' ON '.self::TABLA.' WHEN COALESCE(('.$expr.'),0)=0 BEGIN SELECT RAISE(ABORT,\'red_iadl_bloque\'); END');
            }
        }
    }

    private function contexto(bool $crear): void
    {
        $reglas = [
            'hecho' => [self::TABLA, 'INSERT OR UPDATE', 'NEW.cod_evidencia_iadl_instantanea IS NOT NULL AND NOT EXISTS (SELECT 1 FROM evaluaciones_expertas e WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND e.cod_funcionalidad_instantanea IS NOT NULL)', 'PERFORM 1 FROM evidencias_evaluacion WHERE cod_evidencia_evaluacion=NEW.cod_evidencia_evaluacion FOR UPDATE; PERFORM 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta FOR UPDATE;'],
            'padre' => ['evaluaciones_expertas', 'UPDATE',
                (DB::getDriverName() === 'pgsql' ? 'OLD.cod_funcionalidad_instantanea IS DISTINCT FROM NEW.cod_funcionalidad_instantanea AND (current_setting(\'transaction_isolation\') <> \'read committed\' OR ' : 'OLD.cod_funcionalidad_instantanea IS NOT NEW.cod_funcionalidad_instantanea AND (')
                .'EXISTS (SELECT 1 FROM '.self::TABLA.' h WHERE h.cod_evaluacion_experta=OLD.cod_evaluacion_experta AND h.cod_evidencia_iadl_instantanea IS NOT NULL))', ''],
        ];
        foreach ($reglas as $id => [$tabla,$eventos,$condicion,$bloqueo]) {
            $nombre = 'fn5_iadl_'.$id;
            if (DB::getDriverName() === 'pgsql') {
                if ($crear) {
                    DB::unprepared('CREATE FUNCTION '.$nombre.'() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN '.$bloqueo.' IF ('.$condicion.") THEN RAISE EXCEPTION 'La caracterización IADL debe conservar su evaluación y funcionalidad' USING ERRCODE='23514'; END IF; RETURN NEW; END; $$;");
                    DB::statement('CREATE TRIGGER '.$nombre.' BEFORE '.$eventos.' ON '.$tabla.' FOR EACH ROW EXECUTE FUNCTION '.$nombre.'()');
                } else {
                    DB::statement('DROP TRIGGER '.$nombre.' ON '.$tabla);
                    DB::statement('DROP FUNCTION '.$nombre.'()');
                }
            } elseif ($crear) {
                foreach (explode(' OR ', $eventos) as $evento) {
                    DB::unprepared('CREATE TRIGGER '.$nombre.'_'.strtolower($evento).' BEFORE '.$evento.' ON '.$tabla.' WHEN ('.$condicion.") BEGIN SELECT RAISE(ABORT,'La caracterización IADL debe conservar su evaluación y funcionalidad'); END");
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
};
