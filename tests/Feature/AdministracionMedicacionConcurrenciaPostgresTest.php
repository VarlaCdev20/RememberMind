<?php

namespace Tests\Feature;

use App\Models\AdministracionMedicacion;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Group;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

#[Group('postgresql')]
class AdministracionMedicacionConcurrenciaPostgresTest extends TestCase
{
    private User $enfermero;

    private Residente $residente;

    private Prescripcion $prescripcion;

    protected function setUp(): void
    {
        parent::setUp();
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requiere PostgreSQL desechable del runner.');
        }
        if (! app()->environment('testing') || ! EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            throw new \RuntimeException('Base ajena al runner desechable.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        Carbon::setTestNow('2026-10-14 09:00:00');
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->residente = Residente::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'Mario',
            'ap_paterno' => 'Gutierrez',
            'ap_materno' => 'Mendoza',
        ]);

        $turno = Turno::create([
            'cod_turno' => 'TUR_TEST_MED',
            'nombre' => 'Turno clínico',
            'orden' => 1,
            'hora_inicio' => '00:00:00',
            'hora_fin' => '23:59:59',
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_TEST_MED',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_TEST_MED',
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nivel_supervision' => 'DIRECTA',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        Area::firstOrCreate(
            ['cod_area' => 'COD_ARE_000001'],
            [
                'nombre' => 'Enfermeria Clinica',
                'descripcion' => 'Area de atencion y cuidados',
                'estado' => 'ACTIVO',
            ]
        );

        $med = Medicamento::firstOrCreate(
            ['cod_medicamento' => 'MED_TEST_01'],
            [
                'nombre_generico' => 'Losartan Potasico',
                'nombre_comercial' => 'Losartan 50mg',
                'concentracion' => '50 mg',
                'forma_farmaceutica' => 'Comprimido',
                'unidad' => 'mg',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
            ]
        );

        $atencion = Atencion::firstOrCreate(
            ['cod_atencion' => 'ATN_TEST_01'],
            [
                'cod_residente' => $this->residente->cod_residente,
                'cod_area' => 'COD_ARE_000001',
                'cod_personal' => $this->enfermero->personal->cod_personal,
                'tipo_atencion' => 'CONTROL_MEDICO',
                'motivo' => 'Control rutinario',
                'fecha_hora' => now(),
                'estado' => 'COMPLETADA',
            ]
        );

        $this->prescripcion = Prescripcion::firstOrCreate(
            ['cod_prescripcion' => 'PRS_TEST_01'],
            [
                'cod_residente' => $this->residente->cod_residente,
                'cod_atencion' => $atencion->cod_atencion,
                'cod_medicamento' => $med->cod_medicamento,
                'cod_personal' => $this->enfermero->personal->cod_personal,
                'dosis' => 50.000,
                'unidad_dosis' => 'mg',
                'via_administracion' => 'Oral',
                'frecuencia' => 'Cada 12 horas',
                'indicacion' => 'Antihipertensivo post-desayuno',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => now(),
                'estado' => 'ACTIVA',
            ]
        );

        HorarioPrescripcion::firstOrCreate(
            ['cod_horario_prescripcion' => 'HPR_TEST_01'],
            [
                'cod_prescripcion' => 'PRS_TEST_01',
                'hora_programada' => '08:00:00',
                'dosis_programada' => 50.000,
                'dias_semana' => 'TODOS',
                'estado' => 'ACTIVO',
            ]
        );

        $this->actingAs($this->enfermero);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dos_procesos_simultaneos_guardan_una_sola_ocurrencia_y_auditoria(): void
    {
        $db = DB::connection();
        $directorio = storage_path('framework/testing/medicacion-'.bin2hex(random_bytes(8)));
        mkdir($directorio, 0700, true);
        $config = $directorio.'/config.json';
        file_put_contents($config, json_encode(['conexion' => $db->getConfig(), 'usuario' => $this->enfermero->getKey(),
            'residente' => $this->residente->getKey(), 'prescripcion' => $this->prescripcion->getKey(), 'horario' => 'HPR_TEST_01', 'momento' => now()->toDateTimeString(), 'directorio' => $directorio], JSON_THROW_ON_ERROR));
        $barrera = fopen($directorio.'/barrera', 'c+');
        flock($barrera, LOCK_EX);
        $procesos = [];
        try {
            foreach ([1, 2] as $numero) {
                $pipes = [];
                $proceso = proc_open([PHP_BINARY, base_path('tests/Support/medicacion_concurrencia_worker.php'), $config, (string) $numero],
                    [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, base_path());
                $this->assertIsResource($proceso);
                fclose($pipes[0]);
                $procesos[] = [$proceso, $pipes];
            }
            $limite = microtime(true) + 20;
            while ((! is_file($directorio.'/listo-1') || ! is_file($directorio.'/listo-2')) && microtime(true) < $limite) {
                clearstatcache();
                usleep(10000);
            }
            $this->assertFileExists($directorio.'/listo-1');
            $this->assertFileExists($directorio.'/listo-2');
            flock($barrera, LOCK_UN);
            $salidas = [];
            foreach ($procesos as [$proceso,$pipes]) {
                $salida = stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $this->assertSame(0, proc_close($proceso), $error);
                $salidas[] = json_decode($salida, true, 512, JSON_THROW_ON_ERROR)['resultado'];
            }
            $procesos = [];
            sort($salidas);
            $this->assertSame(['guardada', 'rechazada'], $salidas);
            $this->assertDatabaseCount('administraciones_medicacion', 1);
            $registro = AdministracionMedicacion::firstOrFail();
            $this->assertSame('HPR_TEST_01', $registro->cod_horario_prescripcion);
            $this->assertSame(1, Activity::where('log_name', 'Administración Medicación')->where('subject_id', $registro->getKey())->count());
        } finally {
            flock($barrera, LOCK_UN);
            fclose($barrera);
            foreach ($procesos as [$proceso,$pipes]) {
                proc_terminate($proceso);
                foreach ($pipes as $pipe) {
                    if (is_resource($pipe)) {
                        fclose($pipe);
                    }
                }proc_close($proceso);
            }
            foreach (['config.json', 'barrera', 'listo-1', 'listo-2'] as $archivo) {
                if (is_file($directorio.'/'.$archivo)) {
                    unlink($directorio.'/'.$archivo);
                }
            }
            rmdir($directorio);
        }
    }
}
