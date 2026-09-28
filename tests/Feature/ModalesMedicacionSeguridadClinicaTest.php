<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Enfermeria\Medicacion\SaludAdministracionMedicacionPanel;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModalesMedicacionSeguridadClinicaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $adulto;
    private Prescripcion $prescripcion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Vargas',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->adulto = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'Mario',
            'ap_paterno' => 'Gutierrez',
            'ap_materno' => 'Mendoza',
        ]);

        $turno = TurnoEnfermeria::create([
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
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nivel_supervision' => 'DIRECTA',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        Area::firstOrCreate(
            ['cod_area' => 'ARE_0001'],
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
                'cod_residente' => $this->adulto->cod_residente,
                'cod_area' => 'ARE_0001',
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
                'cod_residente' => $this->adulto->cod_residente,
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

    public function test_modal_administrar_se_abre_centrado_con_datos_bloqueados_y_5_correctos(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('modalAdministrarAbierto', false)
            ->call('abrirModalAdministrar', $this->prescripcion->cod_prescripcion, '08:00', $this->adulto->cod_residente)
            ->assertSet('modalAdministrarAbierto', true)
            ->assertSet('drawerDosisAbierto', false) // NO usa panel lateral al administrar
            ->assertSee('Administrar medicación')
            ->assertSee('Datos de la Prescripción Médica (Solo Lectura)')
            ->assertSee('Mario Gutierrez Mendoza')
            ->assertSee('08:00')
            ->assertSee('Elena')
            // Verificación de los 5 correctos
            ->assertSee('Protocolo de los 5 Correctos')
            ->assertSee('1. Residente correcto')
            ->assertSee('2. Medicamento correcto')
            ->assertSee('3. Dosis correcta')
            ->assertSee('4. Vía correcta')
            ->assertSee('5. Hora correcta')
            ->assertSee('Cancelar')
            ->assertSee('Confirmar administración')
            ->assertSet('formDosisAdministrada', '50')
            ->call('cerrarModalAdministrar')
            ->assertSet('modalAdministrarAbierto', false);
    }

    public function test_modal_administrar_exige_justificacion_si_dosis_difiere_de_prescrita(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('abrirModalAdministrar', $this->prescripcion->cod_prescripcion, '08:00', $this->adulto->cod_residente)
            ->assertSet('esModoConsulta', false)
            ->assertSet('formDosisPrescritaValor', '50')
            ->set('formDosisAdministrada', '25') // Difiere de los 50 prescritos
            ->assertSet('formDosisAdministrada', '25')
            ->assertSet('formDosisPrescritaValor', '50')
            ->assertSet('selectedPrescripcionId', $this->prescripcion->cod_prescripcion)
            ->set('formObservacionAdmin', '')   // Sin justificación
            ->assertSet('selectedPrescripcionId', $this->prescripcion->cod_prescripcion)
            ->call('guardarAdministracion')
            ->assertSet('modalAdministrarAbierto', true)
            ->assertHasErrors(['formObservacionAdmin'])
            ->set('formObservacionAdmin', 'Reducción de dosis por PA baja 90/60 según indicación médica verbal.')
            ->call('guardarAdministracion')
            ->assertHasNoErrors()
            ->assertSet('modalAdministrarAbierto', false);
    }

    public function test_modal_omision_exige_motivo_y_justificacion(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('modalOmisionAbierto', false)
            ->call('abrirModalOmision', $this->prescripcion->cod_prescripcion, '08:00', $this->adulto->cod_residente)
            ->assertSet('modalOmisionAbierto', true)
            ->assertSee('Registrar omisión')
            ->assertSee('Motivo de Omisión Justificada')
            ->set('formMotivoOmision', '')
            ->call('guardarOmision')
            ->assertHasErrors(['formMotivoOmision'])
            ->set('formMotivoOmision', 'Ayuno médico programado (analítica / procedimiento)')
            ->call('guardarOmision')
            ->assertHasNoErrors()
            ->assertSet('modalOmisionAbierto', false);
    }

    public function test_profesional_y_fecha_se_asignan_en_backend_sin_confiar_en_frontend(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->call('abrirModalAdministrar', $this->prescripcion->cod_prescripcion, '08:00', $this->adulto->cod_residente)
            ->set('formDosisAdministrada', '50')
            ->set('formEfectoObservado', 'Adecuada tolerancia sin reacciones adversas')
            ->call('guardarAdministracion')
            ->assertHasNoErrors();

        $admin = AdministracionMedicacion::where('cod_prescripcion', $this->prescripcion->cod_prescripcion)->first();
        $this->assertNotNull($admin);
        $this->assertEquals('ADMINISTRADA', $admin->resultado);
        $this->assertEquals($this->enfermero->personal->cod_personal, $admin->cod_personal); // Usuario autenticado
        $this->assertNotNull($admin->fecha_hora_administracion);
        $this->assertNotNull($admin->cod_jornada);
    }
    public function test_tabla_abre_drawer_y_drawer_abre_modales_emergentes(): void
    {
        Livewire::test(SaludAdministracionMedicacionPanel::class, ['adulto' => $this->adulto])
            ->assertSet('drawerDosisAbierto', false)
            ->call('abrirDrawerDosis', $this->prescripcion->cod_prescripcion, '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', true)
            ->assertSee('Detalle de la Dosis seleccionada')
            ->assertSee('Mario Gutierrez Mendoza')
            // Al hacer clic en Administrar desde el drawer, se abre la ventana emergente centrada y se cierra el drawer
            ->call('abrirModalAdministrar')
            ->assertSet('modalAdministrarAbierto', true)
            ->assertSet('drawerDosisAbierto', false)
            ->call('cerrarModalAdministrar')
            // Reabrir drawer y hacer clic en Registrar omisión: se abre la ventana emergente centrada y se cierra el drawer
            ->call('abrirDrawerDosis', $this->prescripcion->cod_prescripcion, '08:00', $this->adulto->cod_residente)
            ->assertSet('drawerDosisAbierto', true)
            ->call('abrirModalOmision')
            ->assertSet('modalOmisionAbierto', true)
            ->assertSet('drawerDosisAbierto', false)
            ->call('cerrarModalOmision');
    }
}
