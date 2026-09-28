<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntegridadFisicaBddV2Test extends TestCase
{
    use RefreshDatabase;

    private string $codAtencion = 'ATN_INTEGRIDAD';

    private string $codPrescripcion = 'PRS_INTEGRIDAD';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->crearEscenarioClinico();
    }

    public function test_la_base_impide_dos_ocupaciones_activas_para_la_misma_cama(): void
    {
        $ocupacion = DB::table('ocupaciones_cama')->first();
        $this->assertNotNull($ocupacion);

        $duplicada = (array) $ocupacion;
        $duplicada['cod_ocupacion'] = 'OCP_DUPLICADA';

        $this->expectException(QueryException::class);
        DB::table('ocupaciones_cama')->insert($duplicada);
    }

    public function test_la_base_impide_cruzar_una_atencion_con_otro_residente(): void
    {
        $atencion = DB::table('atenciones')->where('cod_atencion', $this->codAtencion)->first();
        $personal = DB::table('personal')->first();
        $this->assertNotNull($atencion);
        $this->assertNotNull($personal);

        $this->expectException(QueryException::class);
        DB::table('signos_vitales')->insert([
            'cod_signo' => 'SIG_CRUZADO',
            'cod_residente' => 'RES_OTRO',
            'cod_personal' => $personal->cod_personal,
            'cod_atencion' => $atencion->cod_atencion,
            'fecha_hora' => now(),
            'estado' => 'REGISTRADO',
        ]);
    }

    public function test_la_base_rechaza_una_dosis_negativa(): void
    {
        $prescripcion = DB::table('prescripciones')->where('cod_prescripcion', $this->codPrescripcion)->first();
        $this->assertNotNull($prescripcion);

        $this->expectException(QueryException::class);
        DB::table('horarios_prescripcion')->insert([
            'cod_horario_prescripcion' => 'HPR_NEGATIVO',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => '23:59:00',
            'dosis_programada' => -1,
            'estado' => 'ACTIVO',
        ]);
    }

    private function crearEscenarioClinico(): void
    {
        DB::table('areas')->insert([
            'cod_area' => 'ARE_INTEGRIDAD',
            'nombre' => 'Área Integridad',
            'estado' => 'ACTIVA',
        ]);
        DB::table('residentes')->insert([
            [
                'cod_residente' => 'RES_BASE',
                'nombres' => 'Residente',
                'apellido_paterno' => 'Base',
                'fecha_nacimiento' => '1940-01-01',
                'estado' => 'ADMITIDO',
            ],
            [
                'cod_residente' => 'RES_OTRO',
                'nombres' => 'Otra',
                'apellido_paterno' => 'Persona',
                'fecha_nacimiento' => '1945-01-01',
                'estado' => 'ADMITIDO',
            ],
        ]);
        DB::table('atenciones')->insert([
            'cod_atencion' => $this->codAtencion,
            'cod_residente' => 'RES_BASE',
            'cod_area' => 'ARE_INTEGRIDAD',
            'cod_personal' => 'PER_0001',
            'tipo_atencion' => 'MEDICA',
            'fecha_hora' => now(),
            'estado' => 'ABIERTA',
        ]);
        DB::table('medicamentos')->insert([
            'cod_medicamento' => 'MED_INTEGRIDAD',
            'nombre_generico' => 'Medicamento de prueba',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);
        DB::table('prescripciones')->insert([
            'cod_prescripcion' => $this->codPrescripcion,
            'cod_residente' => 'RES_BASE',
            'cod_atencion' => $this->codAtencion,
            'cod_medicamento' => 'MED_INTEGRIDAD',
            'cod_personal' => 'PER_0001',
            'dosis' => 1,
            'via_administracion' => 'ORAL',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => now(),
            'estado' => 'ACTIVA',
        ]);
        DB::table('habitaciones')->insert([
            'cod_habitacion' => 'HAB_INTEGRIDAD',
            'codigo' => 'HAB-INT',
            'capacidad' => 1,
            'estado' => 'ACTIVA',
        ]);
        DB::table('camas')->insert([
            'cod_cama' => 'CAM_INTEGRIDAD',
            'cod_habitacion' => 'HAB_INTEGRIDAD',
            'codigo' => 'CAM-INT',
            'estado' => 'OCUPADA',
        ]);
        DB::table('admisiones')->insert([
            'cod_admision' => 'ADM_INTEGRIDAD',
            'cod_residente' => 'RES_BASE',
            'cod_usuario_registro' => 'USU_0001',
            'fecha_hora_admision' => now(),
            'motivo_ingreso' => 'Prueba de integridad',
            'estado' => 'ACTIVA',
        ]);
        DB::table('ocupaciones_cama')->insert([
            'cod_ocupacion' => 'OCP_INTEGRIDAD',
            'cod_residente' => 'RES_BASE',
            'cod_cama' => 'CAM_INTEGRIDAD',
            'cod_admision' => 'ADM_INTEGRIDAD',
            'cod_usuario_registro' => 'USU_0001',
            'fecha_hora_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);
    }
}
