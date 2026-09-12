<?php

namespace Tests\Feature;

use App\Livewire\Alertas\AlertasPanel;
use App\Livewire\Medicacion\MedicacionAdultoModal;
use App\Livewire\Cuidados\PaseTurnoPanel;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\MedicacionAdulto;
use App\Models\RecepcionTurno;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\RegistrarAdministracionMedicacionService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SeguridadCriticaEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $residente;
    private TurnoEnfermeria $turno;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-11 10:00:00');
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $this->enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermero->assignRole('ENFERMEROS');
        $this->turno = TurnoEnfermeria::create([
            'nombre' => 'Mañana', 'orden' => 1, 'hora_inicio' => '07:00',
            'hora_fin' => '15:00', 'estado' => 'ACTIVO',
        ]);
        $this->residente = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001', 'estado_operativo' => 'EN_CENTRO',
        ]);
        $this->asignar($this->enfermero, $this->residente, $this->turno);
        $this->actingAs($this->enfermero);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_enfermero_no_puede_mutar_sin_recibir_turno(): void
    {
        $this->expectException(HttpException::class);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente(
            $this->residente, 'seguimiento.crear', $this->enfermero
        );
    }

    public function test_mutacion_exige_asignacion_vigente_y_residente_en_centro(): void
    {
        $this->recibir($this->enfermero, $this->turno);
        $otro = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001', 'estado_operativo' => 'EN_CENTRO']);
        try {
            app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($otro, 'seguimiento.crear', $this->enfermero);
            $this->fail('Se autorizó un residente no asignado.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->residente->update(['estado_operativo' => 'HOSPITALIZADO']);
        $this->expectException(HttpException::class);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($this->residente, 'seguimiento.crear', $this->enfermero);
    }

    public function test_enfermeria_no_gestiona_ordenes_medicas_aunque_se_le_otorgue_permiso(): void
    {
        $this->enfermero->givePermissionTo('medicacion.crear');
        Livewire::test(MedicacionAdultoModal::class)
            ->call('abrirModalMedicacion', $this->residente->cod_am)
            ->assertForbidden();
    }

    public function test_dosis_programada_se_resuelve_en_servidor_y_no_se_duplica(): void
    {
        $this->recibir($this->enfermero, $this->turno);
        $orden = $this->orden(['hora_programada' => '08:00', 'intervalo_horas' => 12]);
        $servicio = app(RegistrarAdministracionMedicacionService::class);

        $this->expectValidationErrors(fn () => $servicio->registrarProgramada(
            $this->enfermero, $this->residente->cod_am, $orden->cod_med_adulto, '09:37', true
        ));

        $registro = $servicio->registrarProgramada(
            $this->enfermero, $this->residente->cod_am, $orden->cod_med_adulto, '08:00', true
        );
        $this->assertSame(today()->toDateString(), $registro->fecha->toDateString());
        $this->assertNotNull($registro->hora_real);
        $this->expectValidationErrors(fn () => $servicio->registrarProgramada(
            $this->enfermero, $this->residente->cod_am, $orden->cod_med_adulto, '08:00', true
        ));
    }

    public function test_prn_exige_valoracion_intensidad_intervalo_y_programa_reevaluacion(): void
    {
        $this->recibir($this->enfermero, $this->turno);
        $orden = $this->orden([
            'es_prn' => true, 'condicion_prn' => 'Dolor agudo referido',
            'intervalo_horas' => 6, 'hora_programada' => null,
        ]);
        $servicio = app(RegistrarAdministracionMedicacionService::class);
        $registro = $servicio->registrarPrn(
            $this->enfermero, $this->residente->cod_am, $orden->cod_med_adulto,
            'Dolor lumbar intenso', 'Dolor verificado antes de administrar', 8
        );
        $this->assertTrue($registro->requiere_reevaluacion);
        $this->assertNotNull($registro->fecha_hora_reevaluacion);
        $this->expectValidationErrors(fn () => $servicio->registrarPrn(
            $this->enfermero, $this->residente->cod_am, $orden->cod_med_adulto,
            'Dolor nuevamente', 'Nueva valoración previa', 7
        ));
    }

    public function test_alerta_por_id_fuera_del_alcance_es_rechazada(): void
    {
        $otro = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001', 'estado_operativo' => 'EN_CENTRO']);
        $alerta = AlertaAdulto::create([
            'cod_am' => $otro->cod_am, 'origen' => 'MANUAL', 'tipo_alerta' => 'RIESGO',
            'nivel' => 'ALTO', 'motivo' => 'Alerta fuera del ámbito asignado.', 'estado' => 'ABIERTA',
        ]);
        Livewire::test(AlertasPanel::class)->call('verDetalle', $alerta->cod_alerta)->assertForbidden();
    }

    public function test_pase_solo_se_entrega_a_enfermero_asignado_al_turno_entrante(): void
    {
        $this->recibir($this->enfermero, $this->turno);
        $entrante = TurnoEnfermeria::create([
            'nombre' => 'Tarde', 'orden' => 2, 'hora_inicio' => '15:00',
            'hora_fin' => '23:00', 'estado' => 'ACTIVO',
        ]);
        $noEnfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $componente = Livewire::test(PaseTurnoPanel::class)
            ->set('codAm', $this->residente->cod_am)
            ->set('turnoSalienteId', $this->turno->cod_turno)
            ->set('turnoEntranteId', $entrante->cod_turno)
            ->set('enfermeroEntranteId', $noEnfermero->cod_usu)
            ->set('resumenTurno', 'Residente estable con cuidados completados y vigilancia habitual.')
            ->call('generarPase')
            ->assertHasErrors(['enfermeroEntranteId']);

        $receptor = User::factory()->create(['estado' => 'ACTIVO']);
        $receptor->assignRole('ENFERMEROS');
        $this->asignar($receptor, $this->residente, $entrante);
        $componente->set('enfermeroEntranteId', $receptor->cod_usu)
            ->call('generarPase')->assertHasNoErrors();
        $this->assertDatabaseHas('pases_turno', [
            'cod_am' => $this->residente->cod_am,
            'enfermero_entrante_id' => $receptor->cod_usu,
            'turno_entrante_id' => $entrante->cod_turno,
        ]);
    }

    private function asignar(User $usuario, AdultoMayor $adulto, TurnoEnfermeria $turno): void
    {
        AsignacionTurnoAdulto::create([
            'cod_am' => $adulto->cod_am, 'cod_turno' => $turno->cod_turno,
            'cod_usu_enfermero' => $usuario->cod_usu, 'fecha_inicio' => today(),
            'estado' => 'ACTIVA', 'nivel_supervision' => 'ESTANDAR',
            'motivo_asignacion' => 'Prueba de seguridad', 'asignado_por' => $usuario->cod_usu,
        ]);
    }

    private function recibir(User $usuario, TurnoEnfermeria $turno): void
    {
        RecepcionTurno::create([
            'cod_turno' => $turno->cod_turno, 'cod_usuario' => $usuario->cod_usu,
            'fecha_hora_recepcion' => now(),
        ]);
    }

    private function orden(array $datos = []): MedicacionAdulto
    {
        return MedicacionAdulto::create(array_merge([
            'cod_am' => $this->residente->cod_am, 'nombre_medicamento' => 'Paracetamol',
            'dosis' => '500 mg', 'frecuencia' => 'CADA 12 HORAS', 'es_prn' => false,
            'via_administracion' => 'ORAL', 'hora_programada' => '08:00',
            'fecha_inicio' => today(), 'estado' => 'ACTIVO',
        ], $datos));
    }

    private function expectValidationErrors(callable $accion): void
    {
        try {
            $accion();
            $this->fail('Se esperaba un error de validación.');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }
}
