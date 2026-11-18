<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionNutricional;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class ContextoValoracionConcurrentePostgresTest extends TestCase
{
    public function test_la_atencion_y_la_valoracion_coordinan_escrituras_en_dos_conexiones(): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requiere PostgreSQL desechable.');
        }
        if (! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Destino ajeno al corredor de pruebas.');
        }
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $usuario = User::factory()->create();
        $autor = Personal::create(['cod_usuario' => $usuario->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'VPR-BLOQUEO', 'profesion' => 'NUTRICION', 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área sintética de valoración', 'estado' => 'ACTIVA']);
        $residente = Residente::factory()->create();
        $otro = Residente::factory()->create();
        $datos = ['cod_residente' => $residente->getKey(), 'cod_personal' => $autor->getKey(), 'fecha_hora' => now(), 'estado' => 'VIGENTE'];
        $atencion = Atencion::create($datos + ['cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA']);
        $otraAtencion = Atencion::create($datos + ['cod_area' => $area->getKey(), 'tipo_atencion' => 'CONSULTA']);

        config(['database.connections.valoracion_concurrente' => ['name' => 'valoracion_concurrente'] + $db->getConfig()]);
        $segunda = DB::connection('valoracion_concurrente');
        $this->assertNotSame($db->scalar('SELECT pg_backend_pid()'), $segunda->scalar('SELECT pg_backend_pid()'));
        $segunda->statement("SET lock_timeout = '300ms'");
        $trasladar = fn () => $segunda->table('atenciones')->where('cod_atencion', $atencion->getKey())
            ->update(['cod_residente' => $otro->getKey()]);
        $capturar = fn () => ValoracionNutricional::on('valoracion_concurrente')->create($datos + ['cod_atencion' => $otraAtencion->getKey()]);
        try {
            $db->beginTransaction();
            $db->table('valoraciones_profesionales')->insert([
                'cod_valoracion_profesional' => 'VPR-CARRERA', 'tipo_evento' => 'NUTRICIONAL', 'cod_atencion' => $atencion->getKey(),
                'cod_personal' => $autor->getKey(), 'fecha_hora' => $datos['fecha_hora'], 'estado' => 'VIGENTE',
            ]);
            $this->rechazar($trasladar, '55P03');
            $db->commit();
            $this->rechazar($trasladar, '23514');

            $db->beginTransaction();
            $db->table('atenciones')->where('cod_atencion', $otraAtencion->getKey())->update(['cod_residente' => $otro->getKey()]);
            $this->rechazar($capturar, '55P03');
            $db->commit();
            try {
                $capturar();
                $this->fail('El residente de la solicitud ya no coincide con la atención.');
            } catch (ValidationException $error) {
                $this->assertArrayHasKey('cod_atencion', $error->errors());
            }
            $this->assertSame($residente->getKey(), ValoracionNutricional::where('cod_atencion', $atencion->getKey())->sole()->cod_residente);
            $this->assertFalse(ValoracionNutricional::where('cod_atencion', $otraAtencion->getKey())->exists());
        } finally {
            while ($db->transactionLevel() > 0) {
                $db->rollBack();
            }
            DB::purge('valoracion_concurrente');
            // Las pruebas con RefreshDatabase deben reiniciar los datos confirmados de este escenario.
            RefreshDatabaseState::$migrated = false;
        }
    }

    private function rechazar(callable $operacion, string $sqlstate): void
    {
        try {
            $operacion();
            $this->fail('El guardado concurrente debió rechazarse.');
        } catch (QueryException $error) {
            $this->assertSame($sqlstate, $error->errorInfo[0]);
        }
    }
}
