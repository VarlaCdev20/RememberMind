<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Prescripcion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TabMedicacionEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $adulto;
    private Jornada $jornada;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
        ]);
        $this->enfermero->assignRole(['SUPERADMINISTRADOR', 'ENFERMEROS']);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'María Carmen',
            'ap_paterno' => 'Gómez',
        ]);

        $turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_PRUEBA_MED',
            'orden' => 1,
            'nombre' => 'Turno de prueba medicación',
            'hora_inicio' => '00:00:00',
            'hora_fin' => '23:59:59',
            'estado' => 'ACTIVO',
        ]);
        $this->jornada = Jornada::create([
            'cod_jornada' => 'JOR_PRUEBA_MED',
            'cod_turno' => $turno->cod_turno,
            'cod_usuario_apertura' => $this->enfermero->cod_usuario,
            'fecha_jornada' => today(),
            'estado' => 'ACTIVA',
        ]);
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_PRUEBA_MED',
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_escenario_a_b_c_d_e_f_g_en_pestana_medicaciones(): void
    {
        // A. Medicación futura (ej: 20:00)
        $medFuturo = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Atorvastatina 20mg',
            'dosis' => '20 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '20:00',
            'frecuencia' => 'Cada 24 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // B. Medicación a la hora y C. Medicación atrasada (ej: 08:00 sin administrar)
        $medAtrasado = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Paracetamol 1g',
            'dosis' => '1 g',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'frecuencia' => 'Cada 8 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // D. Medicación administrada (ej: Omeprazol 07:00)
        $medAdmin = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Omeprazol 20mg',
            'dosis' => '20 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '07:00',
            'frecuencia' => 'En ayunas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);
        AdministracionMedicacion::create([
            'cod_prescripcion' => $medAdmin->cod_prescripcion,
            'cod_horario_prescripcion' => $medAdmin->horarios()->value('cod_horario_prescripcion'),
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'fecha_hora_programada' => today()->setTime(7, 0),
            'fecha_hora_administracion' => today()->setTime(7, 2),
            'resultado' => 'ADMINISTRADA',
            'dosis_administrada' => 20,
            'estado' => 'REGISTRADA',
        ]);

        // E. Medicamento suspendido
        $medSusp = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Digoxina 0.25mg',
            'dosis' => '0.25 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '09:00',
            'frecuencia' => 'Cada 24 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'SUSPENDIDO',
        ]);

        // F. Medicamento PRN
        $medPrn = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Lactulosa 15ml',
            'dosis' => '15 ml',
            'via_administracion' => 'Oral',
            'hora_programada' => '12:00',
            'frecuencia' => 'A demanda',
            'fecha_inicio' => today()->toDateString(),
            'es_prn' => true,
            'condicion_prn' => 'Si ausencia deposición > 48h',
            'estado' => 'ACTIVO',
        ]);

        // Probar renderizado completo de la pestaña
        $test = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('cambiarTab', 'medicacion')
            // Cabecera
            ->assertSee('MEDICACIÓN')
            ->assertSee('Administración segura, a tiempo, para su bienestar')
            ->assertSee('Hoy, 12 de septiembre de 2026')
            ->assertSee('Turno actual: 07:00 – 15:00')
            // 5 KPIs
            ->assertSee('POR ADMINISTRAR AHORA')
            ->assertSee('PRÓXIMAS DOSIS')
            ->assertSee('ADMINISTRADAS HOY')
            ->assertSee('OMITIDAS / ATRASADAS')
            ->assertSee('ADHERENCIA HOY')
            ->assertSee('89%')
            // Alerta horario
            ->assertSee('ES HORA DE ADMINISTRAR PARACETAMOL 1 g — 08:00')
            ->assertSee('ATENDER AHORA')
            ->assertSee('PRÓXIMA ADMINISTRACIÓN EN 25 MIN')
            // Filtros
            ->assertSee('Por administrar')
            ->assertSee('Próximas')
            ->assertSee('Administradas')
            ->assertSee('PRN')
            ->assertSee('Suspendidas')
            // Agenda horizontal
            ->assertSee('AGENDA DE ADMINISTRACIÓN DE HOY')
            ->assertSee('07:00')
            ->assertSee('08:00')
            ->assertSee('08:30')
            ->assertSee('14:00')
            ->assertSee('20:00')
            // PRN e Histórico
            ->assertSee('Medicamentos PRN (a demanda)')
            ->assertSee('REGISTRAR ADMINISTRACIÓN')
            ->assertSee('Histórico de administración')
            // Drawer lateral
            ->assertSee('PANEL LATERAL DE CONSULTA')
            ->assertSee('DETALLE DE MEDICACIÓN')
            ->assertSee('ADMINISTRAR AHORA');

        // G. Registro directo de administración vía backend
        $test->call('registrarAdministracionDirecta', $medAtrasado->cod_med_adulto, 'ADMINISTRADA', '08:15', 'Toma asistida sin incidencias');

        $this->assertDatabaseHas('administraciones_medicacion', [
            'cod_residente' => $this->adulto->cod_residente,
            'cod_prescripcion' => $medAtrasado->cod_prescripcion,
            'resultado' => 'ADMINISTRADA',
            'estado' => 'REGISTRADA',
        ]);
    }

    public function test_medicacion_muestra_formato_am_pm_y_reloj_pc(): void
    {
        Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Paracetamol 1g',
            'dosis' => '1 g',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'frecuencia' => 'Cada 8 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('cambiarTab', 'medicacion')
            // Cabecera con reloj PC y Turno AM/PM
            ->assertSee('Turno actual: 07:00 AM')
            ->assertSee('Hora actual PC:')
            ->assertSee('relojPC.hora12', false)
            // Horas en formato AM / PM
            ->assertSee('08:00 AM')
            ->assertSee('08:30 AM')
            ->assertSee('02:00 PM')
            ->assertSee('08:00 PM')
            // Evaluación de si se pasó de hora
            ->assertSee('relojPC.evaluarHorario', false)
            ->assertSee('Se pasó de hora', false);
    }
}
