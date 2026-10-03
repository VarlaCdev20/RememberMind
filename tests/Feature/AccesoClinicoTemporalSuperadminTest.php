<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Servicios\AccesoClinicoTemporalService;
use App\Backend\Modulos\Clinica\Servicios\AutorizacionClinicaService;
use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Policies\PrescripcionPolicy;
use App\Policies\ValoracionEnfermeriaPreadmisionPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoClinicoTemporalSuperadminTest extends TestCase
{
    use RefreshDatabase;

    public function test_escritura_temporal_exige_personal_propio_activo_sin_area_asignada(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $sinPersonal = User::factory()->create(['estado' => 'ACTIVO']);
        $sinPersonal->assignRole('SUPERADMINISTRADOR');

        $user = User::factory()->create([
            'nombres' => 'Elena',
            'ap_paterno' => 'Prueba',
            'estado' => 'ACTIVO',
        ]);
        $user->assignRole('SUPERADMINISTRADOR');

        $acceso = app(AccesoClinicoTemporalService::class);
        $this->assertFalse($acceso->sustituyeRol($sinPersonal));
        $this->assertTrue($sinPersonal->hasPermissionTo('prescripciones.crear'));
        $this->assertFalse($sinPersonal->can('prescripciones.crear'));
        $this->assertFalse(app(PrescripcionPolicy::class)->create($sinPersonal));
        $this->assertTrue($acceso->sustituyeRol($user));
        $this->assertTrue($user->can('prescripciones.crear'));
        $this->assertTrue(app(PrescripcionPolicy::class)->create($user));
        $this->assertTrue(app(ValoracionEnfermeriaPreadmisionPolicy::class)->create($user));

        $area = Area::create([
            'cod_area' => 'ARE_SUPER_TEST', 'nombre' => 'Atención médica de prueba',
            'estado' => 'ACTIVA',
        ]);
        $this->assertSame($area->cod_area, app(AutorizacionClinicaService::class)
            ->areaActiva($user->personal, $area->cod_area)->cod_area);

        $user->personal->update(['estado' => 'INACTIVO']);
        $this->assertFalse($acceso->sustituyeRol($user));
        $user->personal->update(['estado' => 'ACTIVO']);

        app(RolePreviewService::class)->activate($user, 'ENFERMEROS');
        $this->assertFalse($acceso->sustituyeRol($user));
        app(RolePreviewService::class)->clear();

        config()->set('remembermind.superadmin_clinical_write', false);
        $this->assertFalse($acceso->sustituyeRol($user));
        $this->assertFalse($user->can('prescripciones.crear'));
        $this->assertFalse(app(PrescripcionPolicy::class)->create($user));
    }

    public function test_superadmin_no_necesita_turno_propio_pero_si_jornada_del_residente(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $superadmin = User::factory()->create(['nombres' => 'Elena', 'ap_paterno' => 'Prueba']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $enfermera = User::factory()->create(['nombres' => 'Ana', 'ap_paterno' => 'Prueba']);
        $enfermera->assignRole('ENFERMEROS');
        $residente = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        $this->actingAs($superadmin);
        $turnos = app(TurnoEnfermeriaService::class);
        try {
            $turnos->autorizarMutacionPaciente($residente, 'signos_vitales.crear', $superadmin);
            $this->fail('La mutación necesita una jornada real del residente.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $turno = TurnoEnfermeria::create([
            'cod_turno' => 'TUR_SUPER_TEST', 'nombre' => 'Turno clínico', 'orden' => 1,
            'hora_inicio' => '00:00:00', 'hora_fin' => '23:59:59', 'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_SUPER_TEST', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ABIERTA',
        ]);
        AsignacionResidenteJornada::create([
            'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $enfermera->personal->cod_personal,
            'nivel_supervision' => 'DIRECTA',
            'estado' => 'ACTIVA',
        ]);

        $this->assertSame($turno->cod_turno,
            $turnos->autorizarMutacionPaciente($residente, 'signos_vitales.crear', $superadmin)->cod_turno);
    }
}
