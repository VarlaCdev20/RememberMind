<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAsignacionesJornada;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\AsignacionTurnoPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AsignacionesJornadaPlanificadaTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $enfermera;

    private Personal $personal;

    private Jornada $jornada;

    private Turno $turno;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfDay()->setTime(10, 0));
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        foreach (['turnos.ver', 'turnos.asignar'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $this->actor->givePermissionTo($permission);
        }
        Role::findOrCreate('ENFERMEROS', 'web');
        $this->enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermera->assignRole('ENFERMEROS');
        $this->personal = Personal::create(['cod_personal' => 'PER_PLAN', 'cod_usuario' => $this->enfermera->getKey(),
            'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'numero_documento' => 'PLAN-TEST', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        Area::create(['cod_area' => 'ARE_PLAN', 'nombre' => 'Cuidados de prueba', 'estado' => 'ACTIVA']);
        $this->turno = Turno::create(['cod_turno' => 'TUR_PLAN', 'nombre' => 'Mañana prueba', 'hora_inicio' => '07:00:00', 'hora_cierre' => '19:00:00', 'estado' => 'ACTIVO']);
        $this->jornada = Jornada::create(['cod_jornada' => 'JOR_PLAN', 'cod_turno' => $this->turno->getKey(), 'fecha_jornada' => today()->addDay(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_PLAN', 'cod_jornada' => $this->jornada->getKey(), 'cod_personal' => $this->personal->getKey(), 'cod_area' => 'ARE_PLAN', 'estado' => 'ACTIVA']);
        $this->residente = Residente::crearDesdeAdmision(['cod_residente' => 'RES_PLAN', 'nombres' => 'Paciente', 'apellido_paterno' => 'Sintetico', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
    }

    private function entrada(string $nivel = 'ESTANDAR'): array
    {
        return ['codResidente' => $this->residente->getKey(), 'codTurno' => $this->turno->getKey(), 'codEnfermero' => $this->enfermera->getKey(),
            'fechaInicio' => today()->addDay()->toDateString(), 'nivelSupervision' => $nivel, 'motivoAsignacion' => 'Continuidad asistencial planificada.'];
    }

    public static function niveles(): array
    {
        return array_map(fn ($nivel) => [$nivel], ['MINIMO', 'ESTANDAR', 'INTENSIVO', 'CRITICO']);
    }

    #[DataProvider('niveles')]
    public function test_panel_planifica_en_jornada_existente_sin_activar_turno_antes_de_hora(string $nivel): void
    {
        $this->actingAs($this->actor);
        $panel = Livewire::test(AsignacionTurnoPanel::class)->call('abrirCrear');
        foreach ($this->entrada($nivel) as $key => $value) {
            $panel->set($key, $value);
        }
        $panel->call('guardar')->assertHasNoErrors();
        $this->assertDatabaseCount('jornadas', 1);
        $this->assertDatabaseHas('asignaciones_residente_jornada', ['cod_residente' => 'RES_PLAN', 'cod_jornada' => 'JOR_PLAN', 'cod_personal' => 'PER_PLAN', 'nivel_supervision' => $nivel, 'estado' => 'ACTIVA']);
        $audit = Activity::where('log_name', 'asignaciones_jornada')->sole();
        $this->assertSame($this->actor->getKey(), $audit->causer_id);
        $this->assertTrue($audit->properties['planificada']);
        $service = app(MiTurnoService::class);
        $this->assertNull($service->resolverJornadaActual($this->personal, now()));
        $this->assertSame('JOR_PLAN', $service->resolverJornadaActual($this->personal, today()->addDay()->setTime(10, 0))?->getKey());
        $this->assertNull($service->resolverJornadaActual($this->personal, today()->addDay()->setTime(19, 0, 1)));
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }

    public static function negativas(): array
    {
        return array_map(fn ($caso) => [$caso], ['actor_inactivo', 'sin_permiso', 'cuenta_inactiva', 'personal_inactivo', 'sin_rol', 'jornada_cerrada', 'sin_asignacion_laboral', 'area_inactiva', 'sin_jornada', 'turno_inactivo', 'residente_retirado']);
    }

    #[DataProvider('negativas')]
    public function test_rechaza_planificacion_incoherente_sin_escrituras(string $caso): void
    {
        match ($caso) {
            'actor_inactivo' => $this->actor->update(['estado' => 'INACTIVO']),
            'sin_permiso' => $this->actor->revokePermissionTo('turnos.asignar'),
            'cuenta_inactiva' => $this->enfermera->update(['estado' => 'INACTIVO']),
            'personal_inactivo' => $this->personal->update(['estado' => 'INACTIVO']),
            'sin_rol' => $this->enfermera->removeRole('ENFERMEROS'),
            'jornada_cerrada' => $this->jornada->update(['estado' => 'CERRADA']),
            'sin_asignacion_laboral' => AsignacionPersonal::whereKey('ASP_PLAN')->update(['estado' => 'INACTIVA']),
            'area_inactiva' => Area::whereKey('ARE_PLAN')->update(['estado' => 'INACTIVA']),
            'sin_jornada' => $this->jornada->update(['fecha_jornada' => today()]),
            'turno_inactivo' => $this->turno->update(['estado' => 'INACTIVO']),
            'residente_retirado' => $this->residente->update(['estado' => 'RETIRADO']),
        };
        try {
            app(GestionarAsignacionesJornada::class)->guardar($this->actor, $this->entrada());
            $this->fail('La planificación incoherente fue aceptada.');
        } catch (ValidationException|HttpException $error) {
            $this->assertDatabaseCount('asignaciones_residente_jornada', 0);
            $this->assertSame(0, Activity::where('log_name', 'asignaciones_jornada')->count());
            $this->assertDatabaseCount('jornadas', 1);
        }
    }

    public function test_reintento_no_duplica_asignacion_ni_auditoria(): void
    {
        $action = app(GestionarAsignacionesJornada::class);
        $action->guardar($this->actor, $this->entrada());
        try {
            $action->guardar($this->actor, $this->entrada('CRITICO'));
            $this->fail('Se aceptó una asignación duplicada.');
        } catch (ValidationException $error) {
            $this->assertDatabaseCount('asignaciones_residente_jornada', 1);
            $this->assertSame('ESTANDAR', AsignacionResidenteJornada::sole()->nivel_supervision);
            $this->assertSame(1, Activity::where('log_name', 'asignaciones_jornada')->count());
        }
    }
}
