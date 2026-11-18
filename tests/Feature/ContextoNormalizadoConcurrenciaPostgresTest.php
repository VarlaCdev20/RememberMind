<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\Feature\SistemaExperto\Infrastructure\Repositories\EscenarioRepositorios;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

class ContextoNormalizadoConcurrenciaPostgresTest extends TestCase
{
    use EsquemaSinTransaccion;

    public function test_insercion_coordina_el_bloqueo_del_padre_y_conserva_el_residente_tras_commit(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La concurrencia se comprueba con dos conexiones PostgreSQL.');
        }
        EscenarioRepositorios::preparar();
        DB::table('residentes')->insert(['cod_residente' => 'SINTETICO-OTRO', 'nombres' => 'Otro caso sintético',
            'apellido_paterno' => 'Ensayo', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        DB::table('medicamentos')->insert(['cod_medicamento' => 'MED-CONTEXTO', 'nombre_generico' => 'Catálogo sintético',
            'control_especial' => false, 'estado' => 'ACTIVO']);
        DB::beginTransaction();
        try {
            DB::table('prescripciones')->insert(['cod_prescripcion' => 'PRS-CONTEXTO', 'cod_atencion' => 'SIM-AT',
                'cod_medicamento' => 'MED-CONTEXTO', 'cod_personal' => 'SIM-PER', 'dosis' => 1, 'unidad_dosis' => 'UNIDAD',
                'via_administracion' => 'ORAL', 'frecuencia' => 'DIARIA', 'segun_necesidad' => false,
                'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
            $this->assertSame('55P03', $this->intentarTraslado());
            DB::commit();
        } finally {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        $this->assertSame('23514', $this->intentarTraslado());
        $this->assertSame('SINTETICO-RES', \App\Models\Prescripcion::findOrFail('PRS-CONTEXTO')->cod_residente);
    }

    private function intentarTraslado(): string
    {
        $config = DB::connection()->getConfig();
        $proceso = new Process([PHP_BINARY, base_path('tests/Support/ContextoNormalizadoWorker.php')], base_path(), [
            'RM_CONTEXTO_DATABASE' => $config['database'], 'RM_CONTEXTO_HOST' => $config['host'],
            'RM_CONTEXTO_PORT' => (string) $config['port'], 'RM_CONTEXTO_USER' => $config['username'],
            'RM_CONTEXTO_PASSWORD' => $config['password'],
        ], timeout: 10);
        $proceso->mustRun();

        return json_decode($proceso->getOutput(), true, 512, JSON_THROW_ON_ERROR)['estado'];
    }
}
