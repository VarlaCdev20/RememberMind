<?php

namespace Tests\Feature;

use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService;
use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Frontend\Livewire\Medico\Medicacion\MedicacionAdultoModal;
use App\Models\AdministracionMedicacion;
use App\Models\Residente;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Personal;
use App\Models\Prescripcion;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class TabMedicacionEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;

    private Residente $adulto;

    private Jornada $jornada;

    private Atencion $atencion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
        ]);
        $this->enfermero->assignRole(['SUPERADMINISTRADOR', 'ENFERMEROS']);

        $this->adulto = Residente::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'María Carmen',
            'ap_paterno' => 'Gómez',
        ]);

        $turno = Turno::create([
            'cod_turno' => 'TUR_PRUEBA_MED',
            'orden' => 1,
            'nombre' => 'Turno de prueba medicación',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => 'ACTIVA',
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
        $area = Area::create([
            'cod_area' => 'ARE_MED_TEST',
            'nombre' => 'Área de medicación de prueba',
            'estado' => 'ACTIVA',
        ]);
        $this->atencion = Atencion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'tipo_atencion' => 'PRESCRIPCION_MEDICA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_escenario_a_b_c_d_e_f_g_en_pestana_medicaciones(): void
    {
        Carbon::setTestNow('2026-09-12 08:05:00');
        $this->jornada->update(['fecha_jornada' => today()]);
        // A. Medicación futura (ej: 20:00)
        $medFuturo = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Atorvastatina 20mg')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Atorvastatina 20mg',
            'dosis' => '20 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '20:00',
            'frecuencia' => 'Cada 24 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // B. Medicación a la hora y C. Medicación atrasada (ej: 08:00 sin administrar)
        $medAtrasado = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Paracetamol 1g')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Paracetamol 1g',
            'dosis' => '1 g',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'frecuencia' => 'Cada 8 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        // D. Medicación administrada (ej: Omeprazol 07:00)
        $medAdmin = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Omeprazol 20mg')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Omeprazol 20mg',
            'dosis' => '20 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '07:00',
            'frecuencia' => 'En ayunas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
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
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Digoxina 0.25mg')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
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
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Lactulosa 15ml')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Lactulosa 15ml',
            'dosis' => '15 ml',
            'via_administracion' => 'Oral',
            'hora_programada' => '12:00',
            'frecuencia' => 'A demanda',
            'fecha_inicio' => today()->toDateString(),
            'es_prn' => true,
            'condicion_prn' => 'Si ausencia deposición > 48h',
            'estado' => 'ACTIVA',
        ]);

        // Probar renderizado completo de la pestaña
        $test = Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('cambiarTab', 'medicacion')
            // Cabecera
            ->assertSee('MEDICACIÓN')
            ->assertSee('Administración segura, a tiempo, para su bienestar')
            ->assertSee('Hoy, 12 de septiembre de 2026')
            // ->assertSee('Turno actual:', false)
            // 5 KPIs
            ->assertSee('POR ADMINISTRAR AHORA')
            ->assertSee('PRÓXIMAS DOSIS')
            ->assertSee('ADMINISTRADAS HOY')
            ->assertSee('OMITIDAS / ATRASADAS')
            ->assertSee('ADHERENCIA HOY')
            // ->assertSee('89%')
            // Alerta horario
            // ->assertSee('ES HORA DE ADMINISTRAR', false)
            // ->assertSee('ATENDER AHORA')
            // ->assertSee('PRÓXIMA ADMINISTRACIÓN EN 25 MIN')
            // Filtros
            ->assertSee('Por administrar')
            ->assertSee('Próximas')
            ->assertSee('Administradas')
            ->assertSee('PRN')
            ->assertSee('Suspendidas')
            // Agenda horizontal
            ->assertSee('Agenda de administraci')
            ->assertSee('Omeprazol')
            ->assertSee('Paracetamol')
            // PRN e Histórico
            ->assertSee('Medicamentos PRN (a demanda)')
            ->assertSee('REGISTRAR ADMINISTRACIÓN')
            ->assertSee('Histórico de administración')
            // Drawer lateral
            ->assertSee('PANEL LATERAL DE CONSULTA')
            ->assertSee('DETALLE DE MEDICACIÓN')
            ->assertSee('ADMINISTRAR AHORA');

        // G. Registro directo de administración vía backend
        $test->call('registrarAdministracionDirecta', $medAtrasado->cod_prescripcion, 'ADMINISTRADA', '08:15', 'Toma asistida sin incidencias');
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
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Paracetamol 1g')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Paracetamol 1g',
            'dosis' => '1 g',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'frecuencia' => 'Cada 8 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('cambiarTab', 'medicacion')
            // Cabecera con reloj PC y Turno AM/PM
            // Cabecera con reloj PC y Turno AM/PM
            // ->assertSee('Turno actual: 07:00 AM')
            ->assertSee('Hora actual PC:')
            ->assertSee('relojPC.hora12', false)
            // Horas en formato AM / PM
            ->assertSee('08:00 AM')
            // Evaluación de si se pasó de hora
            ->assertSee('relojPC.evaluarHorario', false)
            ->assertSee('Se pasó de hora', false);
    }

    private function medicamento(string $nombre): Medicamento
    {
        return Medicamento::query()->create([
            'cod_medicamento' => 'MED_'.strtoupper(Str::random(8)),
            'nombre_generico' => $nombre,
            'nombre_comercial' => $nombre,
            'forma_farmaceutica' => 'COMPRIMIDO',
            'concentracion' => '1 unidad',
            'control_especial' => false,
            'estado' => 'ACTIVA',
        ]);
    }

    public function test_aislamiento_residente_prescripcion_residente_a_visible_y_residente_b_no_visible(): void
    {
        $adultoB = Residente::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'Residente',
            'ap_paterno' => 'B',
        ]);

        $atencionB = Atencion::create([
            'cod_residente' => $adultoB->cod_residente,
            'cod_area' => $this->atencion->cod_area,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'tipo_atencion' => 'PRESCRIPCION_MEDICA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);

        Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Exclusivo Residente A')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Exclusivo Residente A',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '10:00',
            'estado' => 'ACTIVA',
        ]);

        Prescripcion::create([
            'cod_residente' => $adultoB->cod_residente,
            'cod_atencion' => $atencionB->cod_atencion,
            'cod_medicamento' => $this->medicamento('Exclusivo Residente B')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Exclusivo Residente B',
            'dosis' => '20 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '11:00',
            'estado' => 'ACTIVA',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('cambiarTab', 'medicacion')
            ->assertSee('Exclusivo Residente A')
            ->assertDontSee('Exclusivo Residente B');
    }

    public function test_administracion_no_permite_prescripcion_de_otro_residente(): void
    {
        $adultoB = Residente::factory()->create(['cod_est_adul' => 'EST_001']);
        $atencionB = Atencion::create([
            'cod_residente' => $adultoB->cod_residente,
            'cod_area' => $this->atencion->cod_area,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'tipo_atencion' => 'PRESCRIPCION_MEDICA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);

        $prescB = Prescripcion::create([
            'cod_residente' => $adultoB->cod_residente,
            'cod_atencion' => $atencionB->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med Paciente B')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med Paciente B',
            'dosis' => '5 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        $this->expectException(ValidationException::class);

        AdministracionMedicacion::create([
            'cod_prescripcion' => $prescB->cod_prescripcion,
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'resultado' => 'ADMINISTRADA',
            'estado' => 'REGISTRADA',
        ]);
    }

    public function test_horario_pertenece_a_misma_prescripcion_y_horario_ajeno_es_rechazado(): void
    {
        $presc1 = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med 1')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med 1',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        $presc2 = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med 2')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med 2',
            'dosis' => '20 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '14:00',
            'estado' => 'ACTIVA',
        ]);

        $horarioPresc2 = $presc2->horarios()->first();
        $this->assertNotNull($horarioPresc2);

        $this->expectException(ValidationException::class);

        AdministracionMedicacion::create([
            'cod_prescripcion' => $presc1->cod_prescripcion,
            'cod_horario_prescripcion' => $horarioPresc2->cod_horario_prescripcion,
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'resultado' => 'ADMINISTRADA',
            'estado' => 'REGISTRADA',
        ]);
    }

    public function test_enfermeria_no_puede_crear_ni_modificar_orden_medica(): void
    {
        $enfermeroPuro = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermeroPuro->assignRole('ENFERMEROS');

        $this->assertFalse(Gate::forUser($enfermeroPuro)->allows('create', Prescripcion::class));

        $presc = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med Orden')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med Orden',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        $this->assertFalse(Gate::forUser($enfermeroPuro)->allows('update', $presc));

        Livewire::actingAs($enfermeroPuro)
            ->test(MedicacionAdultoModal::class)
            ->call('guardar')
            ->assertForbidden();
    }

    public function test_usuario_sin_personal_no_fabrica_personal_en_administracion(): void
    {
        $userSinPersonal = User::factory()->create(['estado' => 'ACTIVO']);
        $userSinPersonal->assignRole('ENFERMEROS');

        $this->assertNull($userSinPersonal->personal);
        $this->assertSame(0, Personal::where('cod_usuario', $userSinPersonal->cod_usuario)->count());

        $presc = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med Test')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med Test',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        try {
            app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                $userSinPersonal,
                $this->adulto->cod_residente,
                $presc->cod_prescripcion,
                '08:00',
                true
            );
            $this->fail('Debió arrojar excepción por usuario sin personal asociado.');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }

        $this->assertSame(0, Personal::where('cod_usuario', $userSinPersonal->cod_usuario)->count());
    }

    public function test_falta_de_jornada_no_fabrica_jornada_y_es_rechazada(): void
    {
        $enfermeroOtro = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermeroOtro->assignRole('ENFERMEROS');
        Personal::create([
            'cod_personal' => 'PER_OTRO_MED',
            'cod_usuario' => $enfermeroOtro->cod_usuario,
            'nombres' => 'Otro',
            'apellido_paterno' => 'Enfermero',
            'numero_documento' => 'DOC-OTRO-01',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVA',
        ]);

        $presc = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med Jornada')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med Jornada',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        $conteoJornadasAntes = Jornada::count();

        try {
            app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                $enfermeroOtro,
                $this->adulto->cod_residente,
                $presc->cod_prescripcion,
                '08:00',
                true
            );
            $this->fail('Debió fallar por no tener jornada activa asignada.');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }

        $this->assertSame($conteoJornadasAntes, Jornada::count());
    }

    public function test_prescripcion_inexistente_es_rechazada(): void
    {
        $this->expectException(\Throwable::class);

        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            $this->enfermero,
            $this->adulto->cod_residente,
            'PRS_NO_EXISTE_999',
            '08:00',
            true
        );
    }

    public function test_omision_conserva_motivo_en_trazabilidad(): void
    {
        $presc = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Med Omision')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Med Omision',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        $admin = app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            $this->enfermero,
            $this->adulto->cod_residente,
            $presc->cod_prescripcion,
            '08:00',
            false,
            'Residente con náuseas rehusó la toma'
        );

        $this->assertSame('OMITIDA', $admin->resultado);
        $this->assertSame('Residente con náuseas rehusó la toma', $admin->motivo_omision);
        $this->assertDatabaseHas('administraciones_medicacion', [
            'cod_administracion' => $admin->cod_administracion,
            'resultado' => 'OMITIDA',
            'motivo_omision' => 'Residente con náuseas rehusó la toma',
        ]);
    }

    public function test_registro_directo_no_inventa_motivo_de_omision(): void
    {
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('registrarAdministracionDirecta', 'PRESCRIPCION_NO_RELEVANTE', 'OMITIDA')
            ->assertHasErrors(['motivo']);

        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_medicacion_prn_se_registra_sin_horario_ficticio(): void
    {
        $prescPrn = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Ketorolaco 10mg')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Ketorolaco 10mg',
            'dosis' => '10 mg',
            'via_administracion' => 'Oral',
            'segun_necesidad' => true,
            'frecuencia' => 'Según dolor agudo',
            'estado' => 'ACTIVA',
        ]);

        $adminPrn = app(RegistrarAdministracionMedicacionService::class)->registrarPrn(
            $this->enfermero,
            $this->adulto->cod_residente,
            $prescPrn->cod_prescripcion,
            'Dolor articular agudo en rodilla derecha',
            'EVA 8/10 en reposo, inflamación local',
            8
        );

        $this->assertNull($adminPrn->cod_horario_prescripcion);
        $this->assertSame('ADMINISTRADA', $adminPrn->resultado);
        $this->assertStringContainsString('PRN:', $adminPrn->observacion);
    }

    public function test_dosis_administrada_no_sobrescribe_dosis_de_prescripcion(): void
    {
        $presc = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Metformina 850mg')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Metformina 850mg',
            'dosis' => '850 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'ACTIVA',
        ]);

        $admin = app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            $this->enfermero,
            $this->adulto->cod_residente,
            $presc->cod_prescripcion,
            '08:00',
            true,
            null,
            'Media toma por indicación puntual',
            null,
            425.0
        );

        $this->assertEquals(425.0, (float) $admin->fresh()->dosis_administrada);
        $this->assertEquals(850.0, (float) $presc->fresh()->dosis);
    }

    public function test_prescripcion_suspendida_no_figura_en_agenda_activa(): void
    {
        $prescSusp = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $this->medicamento('Atenolol 50mg')->cod_medicamento,
            'cod_personal' => $this->enfermero->personal->cod_personal,
            'nombre_medicamento' => 'Atenolol 50mg',
            'dosis' => '50 mg',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'estado' => 'SUSPENDIDO',
        ]);

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);
        $encontrado = $agenda->first(fn ($item) => $item['medicacion']->cod_prescripcion === $prescSusp->cod_prescripcion);

        $this->assertNull($encontrado);
    }
}
