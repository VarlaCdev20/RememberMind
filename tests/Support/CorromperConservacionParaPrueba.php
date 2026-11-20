<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\GuardasReduccionConservacion;
use Closure;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Inyección controlada de corrupción para probar el lector además de las guardas SQL. */
trait CorromperConservacionParaPrueba
{
    private function corromperConservacion(Closure $operacion, string $hechos = 'hechos_evaluacion_experta'): void
    {
        $db = DB::connection();
        if (! app()->environment('testing') || ! ($db->getDriverName() === 'sqlite' && $db->getDatabaseName() === ':memory:')
            && ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new RuntimeException('La corrupción sintética requiere la base desechable del runner.');
        }
        $guardas = new GuardasReduccionConservacion($db->getSchemaBuilder()->hasTable('vinculos_evidencia_experta'), $hechos);
        $funcionalidadDerivada = ! $db->getSchemaBuilder()->hasColumn($hechos, 'iadl_cod_funcionalidad_instantanea');
        $sqlite = $db->getDriverName() === 'sqlite';
        $bloques = $sqlite
            ? DB::select("SELECT name, tbl_name, sql FROM sqlite_master WHERE type='trigger' AND (name LIKE 'b4_%' OR name LIKE 'fn5_iadl_%' OR name LIKE 'dec20_variable_%')")
            : DB::select("SELECT t.tgname AS name,c.relname AS tbl_name,pg_get_triggerdef(t.oid) AS sql FROM pg_trigger t JOIN pg_class c ON c.oid=t.tgrelid JOIN pg_namespace n ON n.oid=c.relnamespace WHERE NOT t.tgisinternal AND n.nspname='public' AND (t.tgname LIKE 'b4_%' OR t.tgname LIKE 'fn5_iadl_%' OR t.tgname='dec20_variable')");
        foreach ($bloques as $trigger) {
            DB::unprepared('DROP TRIGGER "'.$trigger->name.'"'.($sqlite ? '' : ' ON "'.$trigger->tbl_name.'"'));
        }
        $guardas->retirar();
        try {
            $operacion();
        } finally {
            $guardas->instalar($funcionalidadDerivada);
            foreach ($bloques as $trigger) {
                DB::unprepared($trigger->sql);
            }
        }
    }
}
