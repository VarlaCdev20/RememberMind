<?php

namespace Tests\Feature;

use App\Livewire\Medicacion\SaludMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Medicacion\RegistrarAdministracionMedicacionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MedicacionV2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_panel_restaurado_crea_prescripcion_y_horario_v2(): void
    {
        [$medico, $residente] = $this->escenarioMedico();
        $this->actingAs($medico);

        Livewire::test(SaludMedicacionPanel::class, ['adulto' => AdultoMayor::query()->findOrFail($residente->cod_residente)])
            ->set('nuevo_cod_am', $residente->cod_residente)
            ->set('nuevo_nombre', 'Paracetamol')
            ->set('nuevo_dosis', '500 mg')
            ->set('nuevo_frecuencia', 'CADA 8 HORAS')
            ->set('nuevo_via', 'ORAL')
            ->set('nuevo_hora', now()->format('H:i'))
            ->set('nuevo_fecha_inicio', today()->format('Y-m-d'))
            ->set('nuevo_observacion', 'Tratamiento indicado en atención médica.')
            ->call('guardarNuevoMedicamento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('medicamentos', [
            'nombre_generico' => 'PARACETAMOL',
            'estado' => 'ACTIVO',
        ]);
        $this->assertDatabaseHas('prescripciones', [
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $medico->personal->cod_personal,
            'dosis' => 500,
            'unidad_dosis' => 'mg',
            'estado' => 'ACTIVA',
        ]);
        $this->assertDatabaseCount('horarios_prescripcion', 1);
    }

    public function test_enfermeria_registra_administracion_sobre_prescripcion_y_jornada_v2(): void
    {
        [$medico, $residente] = $this->escenarioMedico();
        $this->actingAs($medico);
        $hora = now()->format('H:i');

        Livewire::test(SaludMedicacionPanel::class, ['adulto' => AdultoMayor::query()->findOrFail($residente->cod_residente)])
            ->set('nuevo_cod_am', $residente->cod_residente)
            ->set('nuevo_nombre', 'Losartan')
            ->set('nuevo_dosis', '50 mg')
            ->set('nuevo_frecuencia', 'CADA 24 HORAS')
            ->set('nuevo_via', 'ORAL')
            ->set('nuevo_hora', $hora)
            ->set('nuevo_fecha_inicio', today()->format('Y-m-d'))
            ->call('guardarNuevoMedicamento')
            ->assertHasNoErrors();

        $prescripcion = $residente->prescripciones()->firstOrFail();
        $enfermera = $this->asignarEnfermera($residente);
        $this->actingAs($enfermera);

        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            $enfermera,
            $residente->cod_residente,
            $prescripcion->cod_prescripcion,
            $hora,
            true,
            observacion: 'Dosis tolerada sin incidentes.',
        );

        $this->assertDatabaseHas('administraciones_medicacion', [
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $enfermera->personal->cod_personal,
            'resultado' => 'ADMINISTRADA',
            'estado' => 'REGISTRADA',
        ]);
    }

    public function test_pantalla_restaurada_de_medicacion_carga_con_un_residente_v2(): void
    {
        [, $residente] = $this->escenarioMedico();
        $super = User::query()->where('correo', 'admincasaamandita@gmail.com')->firstOrFail();

        $this->actingAs($super)
            ->get(route('admin.salud-seguimiento.medicacion', ['adulto' => $residente->cod_residente]))
            ->assertOk()
            ->assertSee('Gestión y Prescripción de Medicación');
    }

    private function escenarioMedico(): array
    {
        $medico = User::factory()->create();
        $medico->assignRole('MEDICO GENERAL/GERIATRA');
        $personal = Personal::query()->create([
            'cod_personal' => 'PER_MED_TEST',
            'cod_usuario' => $medico->cod_usuario,
            'nombres' => 'Mario',
            'apellido_paterno' => 'Lopez',
            'numero_documento' => 'MED-TEST',
            'profesion' => 'MEDICO',
            'estado' => 'ACTIVO',
        ]);
        $area = Area::query()->create([
            'cod_area' => 'ARE_MED_TEST',
            'nombre' => 'ATENCION MEDICA TEST',
            'estado' => 'ACTIVO',
        ]);
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_MED_TEST',
            'nombres' => 'Elvira',
            'apellido_paterno' => 'Quispe',
            'fecha_nacimiento' => '1944-03-02',
            'estado' => 'ADMITIDO',
        ]);
        Atencion::query()->create([
            'cod_atencion' => 'ATE_MED_TEST',
            'cod_residente' => $residente->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'CONSULTA MEDICA',
            'motivo' => 'Evaluación para tratamiento farmacológico.',
            'fecha_hora' => now()->subMinute(),
            'estado' => 'ABIERTA',
        ]);

        return [$medico, $residente];
    }

    private function asignarEnfermera(Residente $residente): User
    {
        $enfermera = User::factory()->create();
        $enfermera->assignRole('ENFERMEROS');
        $personal = Personal::query()->create([
            'cod_personal' => 'PER_ENF_MED_TEST',
            'cod_usuario' => $enfermera->cod_usuario,
            'nombres' => 'Elena',
            'apellido_paterno' => 'Rojas',
            'numero_documento' => 'ENF-MED-TEST',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        $turno = TurnoEnfermeria::query()->create([
            'cod_turno' => 'TUR_MED_TEST',
            'nombre' => 'TURNO MEDICACION TEST',
            'hora_inicio' => '00:00:00',
            'hora_cierre' => '23:59:59',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_MED_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionResidenteJornada::query()->create([
            'cod_asignacion' => 'ARJ_MED_TEST',
            'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        return $enfermera;
    }
}
