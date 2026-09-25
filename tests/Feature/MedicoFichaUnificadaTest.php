<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Personal;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\SignoVital;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class MedicoFichaUnificadaTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;
    private AdultoMayor $residente;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-12 10:00:00');
        $this->seed([ RolesAndPermissionsSeeder::class]);

        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        $personal = Personal::create([
            'cod_personal' => 'PER_MED_TEST',
            'cod_usuario' => $this->medico->cod_usuario,
            'nombres' => 'Médico',
            'apellido_paterno' => 'Clínico',
            'numero_documento' => 'MED-TEST-01',
            'profesion' => 'MEDICINA',
            'estado' => 'ACTIVO',
        ]);
        $area = Area::create([
            'cod_area' => 'ARE_MED_TEST',
            'nombre' => 'Área médica de prueba',
            'estado' => 'ACTIVA',
        ]);

        $this->residente = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres'      => 'Aurelio',
            'ap_paterno'   => 'Valdivia',
            'ap_materno'   => 'Paredes',
        ]);

        $atencion = Atencion::create([
            'cod_residente' => $this->residente->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'CONSULTA_MEDICA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);

        Prescripcion::create([
            'cod_residente' => $this->residente->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => Medicamento::create([
                'cod_medicamento' => 'MED_MED_TEST',
                'nombre_generico' => 'Enalapril',
                'nombre_comercial' => 'Enalapril 10mg',
                'forma_farmaceutica' => 'COMPRIMIDO',
                'concentracion' => '10 mg',
                'control_especial' => false,
                'estado' => 'ACTIVO',
            ])->cod_medicamento,
            'cod_personal' => $personal->cod_personal,
            'nombre_medicamento' => 'Enalapril 10mg',
            'dosis'              => '1 comprimido',
            'frecuencia'         => 'DIARIA',
            'via_administracion' => 'ORAL',
            'hora_programada'    => '08:00',
            'fecha_inicio'       => today(),
            'estado'             => 'ACTIVA',
        ]);

        SignoVital::create([
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->medico->personal()->firstOrFail()->cod_personal,
            'fecha'              => today(),
            'hora'               => '08:00',
            'presion_sistolica'  => 120,
            'presion_diastolica' => 80,
            'frecuencia_cardiaca'=> 72,
            'saturacion'         => 97,
            'temperatura'        => 36.5,
            'registrado_por'     => $this->medico->cod_usuario,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_medico_accede_correctamente_a_la_ficha_unificada_por_su_ruta(): void
    {
        $this->actingAs($this->medico);

        $response = $this->get(route('admin.medico.paciente.ficha', $this->residente->cod_residente));
        $response->assertOk();
        $response->assertSee('Aurelio');
        $response->assertSee('Valdivia');
    }

    public function test_medico_visualiza_componente_ficha_paciente_con_acceso_clinico(): void
    {
        $this->actingAs($this->medico);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_residente])
            ->assertOk()
            ->assertSee('Aurelio')
            ->assertSee('Valdivia')
            ->assertSee('Enalapril 10mg')
            ->assertSee('ÁREA MÉDICA Y CLÍNICA')
            ->assertSee('Nueva Prescripción');
    }

    public function test_medico_no_requiere_turno_de_enfermeria_para_consultar_cualquier_residente(): void
    {
        $this->actingAs($this->medico);

        // Residente sin asignación de turno de enfermería
        $otroResidente = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres'      => 'Beatriz',
            'ap_paterno'   => 'Sarmiento',
        ]);

        $this->get(route('admin.medico.residente.ficha', $otroResidente->cod_residente))
            ->assertOk()
            ->assertSee('Beatriz');
    }

    public function test_medico_visualiza_todos_los_pacientes_en_mis_pacientes_sin_quedar_bloqueado(): void
    {
        $this->actingAs($this->medico);

        $otroResidente = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres'      => 'Beatriz',
            'ap_paterno'   => 'Sarmiento',
        ]);

        Livewire::test(\App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes::class)
            ->assertOk()
            ->assertSee('Aurelio')
            ->assertSee('Beatriz');
    }
}
