<?php

namespace Tests\Feature;

use App\Models\Cama;
use App\Models\Habitacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Prueba optativa; exige una BDD PostgreSQL de pruebas ya migrada. */
class AlojamientoBloqueoPostgresTest extends TestCase
{
    protected function setUp(): void
    {
        if (getenv('RM_POSTGRES_CONCURRENCIA') !== '1') {
            $this->markTestSkipped('Activar explícitamente con PostgreSQL desechable; nunca usar la BDD operativa.');
        }
        parent::setUp();
        $this->assertSame('pgsql', DB::connection()->getDriverName());
        $this->assertMatchesRegularExpression('/_testing$/', DB::connection()->getDatabaseName());
    }

    public function test_bloqueo_de_cama_impide_lectura_para_asignacion_en_otra_conexion(): void
    {
        $configuracion = DB::connection()->getConfig();
        config(['database.connections.alojamiento_prueba' => $configuracion]);
        $principal = DB::connection();
        $segunda = DB::connection('alojamiento_prueba');
        $codigo = 'T_'.strtoupper(bin2hex(random_bytes(6)));
        $habitacion = null;
        $cama = null;
        try {
            $habitacion = Habitacion::create(['cod_habitacion' => $codigo, 'codigo' => $codigo, 'estado' => 'ACTIVA']);
            $cama = Cama::create(['cod_cama' => $codigo, 'codigo' => $codigo, 'cod_habitacion' => $codigo, 'estado' => 'ACTIVA']);
            $principal->beginTransaction();
            $principal->table('camas')->where('cod_cama', $codigo)->lockForUpdate()->first();
            $segunda->statement("SET lock_timeout = '250ms'");
            try {
                $segunda->table('camas')->where('cod_cama', $codigo)->lockForUpdate()->first();
                $this->fail('Otra conexión no debe poder bloquear la cama mientras la asignación la mantiene bloqueada.');
            } catch (QueryException $exception) {
                $this->assertSame('55P03', $exception->errorInfo[0]);
            }
            $principal->rollBack();
            $this->assertNotNull($segunda->table('camas')->where('cod_cama', $codigo)->lockForUpdate()->first());
        } finally {
            while ($principal->transactionLevel() > 0) {
                $principal->rollBack();
            }
            if ($cama) {
                $principal->table('camas')->where('cod_cama', $codigo)->delete();
            }
            if ($habitacion) {
                $principal->table('habitaciones')->where('cod_habitacion', $codigo)->delete();
            }
            DB::purge('alojamiento_prueba');
        }
    }
}
