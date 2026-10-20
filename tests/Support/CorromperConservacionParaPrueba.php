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
    private function corromperConservacion(Closure $operacion): void
    {
        $db = DB::connection();
        if (! app()->environment('testing') || ! ($db->getDriverName() === 'sqlite' && $db->getDatabaseName() === ':memory:')
            && ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new RuntimeException('La corrupción sintética requiere la base desechable del runner.');
        }
        $guardas = new GuardasReduccionConservacion;
        $guardas->retirar();
        try {
            $operacion();
        } finally {
            $guardas->instalar();
        }
    }
}
