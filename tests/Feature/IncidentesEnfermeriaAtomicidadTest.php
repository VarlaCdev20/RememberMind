<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Herida;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarIncidentesEnfermeria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IncidentesEnfermeriaAtomicidadTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermera;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->startOfDay()->addHours(12));
        Permission::create(['name' => 'atenciones.crear', 'guard_name' => 'web']);
        Role::create(['name' => 'ENFERMEROS', 'guard_name' => 'web'])->givePermissionTo('atenciones.crear');
        $this->enfermera = User::factory()->create(['nombres' => 'Profesional', 'ap_paterno' => 'Sintética']);
        $this->enfermera->assignRole('ENFERMEROS');
        $this->actingAs($this->enfermera);

        Residente::crearDesdeAdmision(['cod_residente' => 'RES_ATOMICO', 'nombres' => 'Residente', 'apellido_paterno' => 'Sintético', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        Area::create(['cod_area' => 'ARE_ATOMICA', 'nombre' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        Turno::create(['cod_turno' => 'TUR_ATOMICO', 'nombre' => 'Turno sintético', 'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        Jornada::create(['cod_jornada' => 'JOR_ATOMICA', 'cod_turno' => 'TUR_ATOMICO', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_ATOMICA', 'cod_jornada' => 'JOR_ATOMICA', 'cod_personal' => $this->enfermera->personal->cod_personal, 'cod_area' => 'ARE_ATOMICA', 'funcion' => 'ENFERMERO_TURNO', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_ATOMICA', 'cod_residente' => 'RES_ATOMICO', 'cod_jornada' => 'JOR_ATOMICA', 'cod_personal' => $this->enfermera->personal->cod_personal, 'nivel_supervision' => 'ESTANDAR', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
    }

    public function test_registra_incidente_herida_y_alerta_con_la_misma_autoria(): void
    {
        app(GestionarIncidentesEnfermeria::class)->guardarIncidente($this->enfermera, $this->entrada());

        $this->assertDatabaseCount('incidentes', 1);
        $this->assertDatabaseCount('heridas', 1);
        $this->assertDatabaseCount('alertas', 1);
        $personal = $this->enfermera->personal->cod_personal;
        $this->assertDatabaseHas('incidentes', ['cod_residente' => 'RES_ATOMICO', 'cod_personal' => $personal, 'cod_jornada' => 'JOR_ATOMICA', 'tipo_incidente' => 'CAIDA', 'gravedad' => 'MODERADA', 'estado' => 'EN_SEGUIMIENTO']);
        $this->assertDatabaseHas('heridas', ['cod_residente' => 'RES_ATOMICO', 'cod_personal' => $personal, 'tipo_herida' => 'CONTUSION', 'ubicacion' => 'Brazo', 'estado' => 'ACTIVA']);
        $this->assertDatabaseHas('alertas', ['cod_residente' => 'RES_ATOMICO', 'cod_personal_responsable' => $personal, 'tipo' => 'CAIDA', 'prioridad' => 'ALTA', 'estado' => 'ABIERTA']);
    }

    #[TestWith([Herida::class])]
    #[TestWith([Alerta::class])]
    public function test_un_fallo_despues_de_insertar_revierte_todas_las_escrituras(string $modelo): void
    {
        $evento = 'eloquent.created: '.$modelo;
        Event::listen($evento, static function (): void {
            throw new RuntimeException('Fallo sintético después de persistir.');
        });
        $nivelAnterior = DB::transactionLevel();
        try {
            app(GestionarIncidentesEnfermeria::class)->guardarIncidente($this->enfermera, $this->entrada());
            $this->fail('El fallo de persistencia debe propagarse.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Fallo sintético después de persistir.', $exception->getMessage());
        } finally {
            Event::forget($evento);
        }

        $this->assertSame($nivelAnterior, DB::transactionLevel());
        $this->assertDatabaseCount('incidentes', 0);
        $this->assertDatabaseCount('heridas', 0);
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_residente_no_asignado_se_rechaza_sin_escrituras(): void
    {
        Residente::crearDesdeAdmision(['cod_residente' => 'RES_AJENO', 'nombres' => 'Otro', 'apellido_paterno' => 'Sintético', 'fecha_nacimiento' => '1940-01-01', 'estado' => 'ADMITIDO']);
        try {
            app(GestionarIncidentesEnfermeria::class)->guardarIncidente($this->enfermera, array_replace($this->entrada(), ['codResidente' => 'RES_AJENO']));
            $this->fail('Un residente ajeno debe rechazarse.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('incidentes', 0);
        $this->assertDatabaseCount('heridas', 0);
        $this->assertDatabaseCount('alertas', 0);
    }

    private function entrada(): array
    {
        return ['codResidente' => 'RES_ATOMICO', 'tipoIncidente' => 'CAIDA', 'lugarIncidente' => 'Sala sintética', 'actividadPrevia' => null, 'presenciado' => false, 'testigo' => null, 'descripcionIncidente' => 'Incidente sintético para comprobar atomicidad.', 'dolorIncidente' => 4, 'hayLesion' => true, 'tipoLesion' => 'CONTUSION', 'zonaLesion' => 'Brazo', 'movilidadPosterior' => null, 'familiarInformado' => false, 'medicoInformado' => true, 'requiereSeguimiento' => true];
    }
}
