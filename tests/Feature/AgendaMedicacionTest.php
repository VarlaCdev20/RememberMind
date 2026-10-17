<?php

namespace Tests\Feature;

use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Medicacion\Presentacion\Livewire\Paginas\SaludMedicacionPanel;
use App\Models\AdministracionMedicacion;
use App\Models\Residente;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\HorarioPrescripcion;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AgendaMedicacionTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private Residente $adulto;
    private Atencion $atencion;
    private Jornada $jornada;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class]);
        $this->usuario = User::factory()->create([
            'nombres' => 'Mario',
            'ap_paterno' => 'Médico',
            'estado' => 'ACTIVO',
        ]);
        $this->usuario->assignRole('MEDICO GENERAL/GERIATRA');
        $this->adulto = Residente::factory()->create(['estado' => 'ADMITIDO']);

        $area = Area::query()->create([
            'cod_area' => 'ARE_MED',
            'nombre' => 'Atención médica',
            'estado' => 'ACTIVA',
        ]);
        $this->atencion = Atencion::query()->create([
            'cod_atencion' => 'ATE_AGENDA',
            'cod_residente' => $this->adulto->cod_residente,
            'cod_area' => $area->cod_area,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'tipo_atencion' => 'CONSULTA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_AGENDA',
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_cierre' => '15:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $this->jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_AGENDA',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => '2026-09-10',
            'estado' => 'ABIERTA',
        ]);
        $this->actingAs($this->usuario);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_prescribir_no_crea_una_administracion_u_omision_ficticia(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');

        Livewire::test(SaludMedicacionPanel::class, ['adulto' => $this->adulto])
            ->set('nuevo_cod_residente', $this->adulto->cod_residente)
            ->set('nuevo_nombre', 'Losartán')
            ->set('nuevo_dosis', '50 mg')
            ->set('nuevo_frecuencia', 'Cada 24 horas')
            ->set('nuevo_via', 'ORAL')
            ->set('nuevo_hora', '08:00')
            ->set('nuevo_fecha_inicio', '2026-09-10')
            ->set('nuevo_medico', '')
            ->call('guardarNuevoMedicamento')
            ->assertHasNoErrors();

        $prescripcion = Prescripcion::query()->with(['medicamento', 'horarios'])->sole();
        $this->assertSame($this->adulto->cod_residente, $prescripcion->cod_residente);
        $this->assertSame('LOSARTÁN', $prescripcion->medicamento->nombre_generico);
        $this->assertSame('08:00', substr((string) $prescripcion->horarios->sole()->hora_programada, 0, 5));
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_agenda_cambia_de_proxima_a_administrada_segun_el_registro_real(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');
        $medicacion = $this->crearMedicacion('08:00');

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);
        $this->assertSame('PROXIMA', $agenda->first()['estado']);

        AdministracionMedicacion::create([
            'cod_administracion' => 'ADM_AGENDA',
            'cod_prescripcion' => $medicacion->cod_prescripcion,
            'cod_horario_prescripcion' => $medicacion->horarios()->value('cod_horario_prescripcion'),
            'cod_residente' => $this->adulto->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'fecha_hora_programada' => '2026-09-10 08:00:00',
            'fecha_hora_administracion' => '2026-09-10 07:35:00',
            'resultado' => 'ADMINISTRADA',
            'estado' => 'FINALIZADO',
        ]);

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);
        $this->assertSame('ADMINISTRADA', $agenda->first()['estado']);
    }

    public function test_campana_muestra_y_cuenta_una_dosis_vencida(): void
    {
        Carbon::setTestNow('2026-09-10 09:00:00');
        $this->crearMedicacion('08:00');

        Livewire::test(CampanaNotificaciones::class)
            ->assertSet('conteoAbiertas', 1)
            ->assertSee('Losartán')
            ->assertSee('Vencida')
            ->assertSee('Programada: 08:00');
    }

    public function test_varios_horarios_v2_generan_todas_las_dosis_del_dia(): void
    {
        Carbon::setTestNow('2026-09-10 07:30:00');
        $medicacion = $this->crearMedicacion('08:00');
        $medicacion->update(['frecuencia' => 'Cada 8 horas']);
        foreach (['00:00', '16:00'] as $indice => $hora) {
            HorarioPrescripcion::query()->create([
                'cod_horario_prescripcion' => 'HPR_EXTRA_'.$indice,
                'cod_prescripcion' => $medicacion->cod_prescripcion,
                'hora_programada' => $hora,
                'dosis_programada' => 50,
                'estado' => 'ACTIVO',
            ]);
        }

        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente);

        $this->assertSame(['00:00', '08:00', '16:00'], $agenda->pluck('hora')->all());
        $this->assertSame(['VENCIDA', 'PROXIMA', 'PENDIENTE'], $agenda->pluck('estado')->all());
    }

    private function crearMedicacion(string $hora): Prescripcion
    {
        $medicamento = Medicamento::query()->firstOrCreate(
            ['cod_medicamento' => 'MED_AGENDA'],
            ['nombre_generico' => 'Losartán', 'control_especial' => false, 'estado' => 'ACTIVO'],
        );
        $prescripcion = Prescripcion::query()->create([
            'cod_prescripcion' => 'PRE_AGENDA',
            'cod_residente' => $this->adulto->cod_residente,
            'cod_atencion' => $this->atencion->cod_atencion,
            'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'dosis' => 50,
            'unidad_dosis' => 'mg',
            'frecuencia' => 'Cada 24 horas',
            'via_administracion' => 'ORAL',
            'estado' => 'ACTIVA',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => '2026-09-01 08:00:00',
        ]);
        HorarioPrescripcion::query()->create([
            'cod_horario_prescripcion' => 'HPR_AGENDA',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'hora_programada' => $hora,
            'dosis_programada' => 50,
            'estado' => 'ACTIVO',
        ]);

        return $prescripcion;
    }

    public function test_agenda_de_manana_conserva_el_reloj_real_y_el_dia_de_semana(): void
    {
        Carbon::setTestNow('2026-09-10 23:00:00');
        $prescripcion = $this->crearMedicacion('06:00');
        $prescripcion->horarios()->update(['dias_semana' => 'VIERNES']);
        $agenda = app(AgendaMedicacionService::class)->paraAdulto($this->adulto->getKey(), now(), now()->addDay());
        $this->assertCount(1, $agenda);
        $this->assertSame('2026-09-11 06:00:00', $agenda->first()['programada']->toDateTimeString());
        $this->assertSame('PENDIENTE', $agenda->first()['estado']);
        $this->assertSame(420, $agenda->first()['minutos']);
        $this->assertFalse($agenda->first()['se_paso']);
        $prescripcion->update(['fecha_hora_prescripcion' => '2026-09-11 12:00:00']);
        $this->assertCount(0, app(AgendaMedicacionService::class)->paraAdulto($this->adulto->getKey(), now(), now()->addDay()));
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_administracion_anulada_no_oculta_la_dosis_pendiente(): void
    {
        Carbon::setTestNow('2026-09-10 09:00:00');
        $medicacion=$this->crearMedicacion('08:00');
        AdministracionMedicacion::create([
            'cod_prescripcion'=>$medicacion->cod_prescripcion,'cod_horario_prescripcion'=>$medicacion->horarios()->value('cod_horario_prescripcion'),
            'cod_residente'=>$this->adulto->cod_residente,'cod_jornada'=>$this->jornada->cod_jornada,'cod_personal'=>$this->usuario->personal->cod_personal,
            'fecha_hora_programada'=>'2026-09-10 08:00:00','fecha_hora_administracion'=>'2026-09-10 08:00:00','resultado'=>'ADMINISTRADA','estado'=>'ANULADA',
        ]);
        $item=app(AgendaMedicacionService::class)->paraAdulto($this->adulto->cod_residente)->first();
        $this->assertSame('VENCIDA',$item['estado']); $this->assertNull($item['registro']);
        $this->assertDatabaseCount('administraciones_medicacion',1);
        // Tampoco una omisión anulada debe originar una nueva alerta clínica.
        AdministracionMedicacion::query()->sole()->update(['resultado' => 'OMITIDA']);
        $this->usuario->assignRole('ADMINISTRADOR');
        $this->usuario->givePermissionTo('alertas.gestionar');
        $this->assertSame(0, app(\App\Modulos\Alertas\Aplicacion\Acciones\DeteccionAlertasService::class)->detectar($this->adulto->cod_residente));
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_campana_anuncia_primera_dosis_y_su_paso_a_vencida(): void
    {
        Carbon::setTestNow('2026-09-10 06:00:00');
        $this->crearMedicacion('08:00');
        $campana=Livewire::test(CampanaNotificaciones::class)->assertSet('conteoAbiertas',0);
        Carbon::setTestNow('2026-09-10 07:30:00');
        $campana->call('verificarAlertas')->assertDispatched('alerta-nueva',fn($name,$params)=>str_contains($params[0]['mensaje'],'PROXIMA'));
        Carbon::setTestNow('2026-09-10 08:01:00');
        $campana->call('verificarAlertas')->assertDispatched('alerta-nueva',fn($name,$params)=>str_contains($params[0]['mensaje'],'VENCIDA'));
        $this->assertDatabaseCount('administraciones_medicacion',0);
    }
}
