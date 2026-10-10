<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionFuncional;
use App\Modulos\Admisiones\Aplicacion\Acciones\GestionarPreadmisionesHttp;
use App\Modulos\Admisiones\Aplicacion\Acciones\RegistrarDecisionAdmision;
use App\Modulos\Admisiones\Presentacion\Livewire\Funcionalidades\DecisionAdmisionModal;
use App\Modulos\Clinica\Aplicacion\Acciones\RegistrarEventoFichaMedica;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarUsuariosHttp;
use App\Modulos\Identidad\Aplicacion\Acciones\RegistrarCuentaInstitucionalHttp;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarInfraestructuraResidencialHttp;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarInstitucionHttp;
use App\Modulos\Residentes\Aplicacion\Acciones\CambiarEstadoResidenteHttp;
use App\Modulos\Residentes\Aplicacion\Acciones\GestionarDocumentosResidenteHttp;
use App\Modulos\Valoraciones\Presentacion\Livewire\Funcionalidades\ValoracionBarthelModal;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AccionesModularesClinicasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public static function decisiones(): array
    {
        return [['ADMITIDO_NORMAL', 'PENDIENTE_ASIGNACION'], ['DERIVADO', 'DERIVADO']];
    }

    public function test_casos_http_exigen_permisos_antes_de_validar_o_escribir(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Cuenta sintética sin permisos']);
        $residente = Residente::factory()->create();
        $habitacion = new Habitacion;
        $preadmision = new Preadmision;
        $casos = [
            [GestionarUsuariosHttp::class, 'store', [$usuario, []]],
            [GestionarUsuariosHttp::class, 'update', [$usuario, [], $usuario]],
            [GestionarUsuariosHttp::class, 'activar', [$usuario, [], $usuario]],
            [GestionarUsuariosHttp::class, 'desactivar', [$usuario, [], $usuario]],
            [GestionarDocumentosResidenteHttp::class, 'store', [$usuario, [], $residente]],
            [GestionarDocumentosResidenteHttp::class, 'update', [$usuario, [], $residente, 'DOC_SINTETICO']],
            [GestionarDocumentosResidenteHttp::class, 'destroy', [$usuario, [], $residente, 'DOC_SINTETICO']],
            [GestionarDocumentosResidenteHttp::class, 'restore', [$usuario, [], $residente, 'DOC_SINTETICO']],
            [GestionarInfraestructuraResidencialHttp::class, 'habitacion', [$usuario, []]],
            [GestionarInfraestructuraResidencialHttp::class, 'cama', [$usuario, [], $habitacion]],
            [GestionarInstitucionHttp::class, 'guardarPersonal', [$usuario, []]],
            [GestionarInstitucionHttp::class, 'guardarArea', [$usuario, []]],
            [GestionarInstitucionHttp::class, 'guardarTurno', [$usuario, []]],
            [RegistrarCuentaInstitucionalHttp::class, 'guardarUsuario', [$usuario, []]],
            [GestionarPreadmisionesHttp::class, 'store', [$usuario, []]],
            [GestionarPreadmisionesHttp::class, 'revisar', [$usuario, [], $preadmision]],
            [CambiarEstadoResidenteHttp::class, 'cambiarEstado', [$usuario, [], $residente]],
            [RegistrarEventoFichaMedica::class, 'archivar', [$usuario, $residente]],
            [RegistrarEventoFichaMedica::class, 'restore', [$usuario, $residente]],
        ];
        $antes = [User::count(), Activity::count(), Residente::count()];
        foreach ($casos as [$clase, $metodo, $argumentos]) {
            try {
                app($clase)->{$metodo}(...$argumentos);
                $this->fail($clase.'::'.$metodo.' debe exigir el permiso de su ruta original.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode(), $clase.'::'.$metodo);
            }
        }
        $this->assertSame($antes, [User::count(), Activity::count(), Residente::count()]);
        $this->assertDatabaseCount('documentos', 0);
        $this->assertDatabaseCount('preadmisiones', 0);
    }

    public function test_permisos_documentales_no_sustituyen_el_vinculo_familiar(): void
    {
        $familiar = User::factory()->create(['nombres' => 'Familiar sintético']);
        $familiar->assignRole('FAMILIAR');
        $familiar->givePermissionTo(['residentes.ver', 'documentos.gestionar', 'atenciones.anular', 'atenciones.editar']);
        $residente = Residente::factory()->create();
        $this->actingAs($familiar);
        $antes = Activity::count();
        foreach ([
            [GestionarDocumentosResidenteHttp::class, 'store', [$familiar, [], $residente]],
            [GestionarDocumentosResidenteHttp::class, 'restore', [$familiar, [], $residente, 'DOC_SINTETICO']],
            [RegistrarEventoFichaMedica::class, 'archivar', [$familiar, $residente]],
            [RegistrarEventoFichaMedica::class, 'restore', [$familiar, $residente]],
        ] as [$clase, $metodo, $argumentos]) {
            try {
                app($clase)->{$metodo}(...$argumentos);
                $this->fail('La operación debe respetar la Policy del residente.');
            } catch (AuthorizationException $exception) {
                $this->assertTrue($exception->hasStatus() ? $exception->status() === 403 : true);
            }
        }
        $this->assertSame($antes, Activity::count());
        $this->assertDatabaseCount('documentos', 0);
    }

    #[DataProvider('decisiones')]
    public function test_decision_conserva_estado_historial_y_rechaza_reintento(string $decision, string $estado): void
    {
        $usuario = User::factory()->create(['nombres' => 'Profesional sintético']);
        $usuario->assignRole('MEDICO GENERAL/GERIATRA');
        $residente = Residente::factory()->create(['estado' => 'DECISION_ADMISION']);
        $this->actingAs($usuario);
        Livewire::test(DecisionAdmisionModal::class)
            ->call('open', $residente->cod_residente)
            ->set('decision', $decision)
            ->set('motivo_decision', 'Motivo sintético de admisión')
            ->set('motivo_derivacion', 'Motivo sintético de derivación')
            ->call('guardar')->assertHasNoErrors()->assertDispatched('valoracionMedicaCompletada');
        $this->assertSame($estado, $residente->fresh()->estado);
        $this->assertDatabaseHas('historial_estados_residente', ['cod_residente' => $residente->cod_residente,
            'estado_anterior' => 'DECISION_ADMISION', 'estado_nuevo' => $estado,
            'cod_usuario_registro' => $usuario->cod_usuario]);
        try {
            app(RegistrarDecisionAdmision::class)->ejecutar($usuario, ['adulto' => $residente]);
            $this->fail('El reintento debe rechazarse antes de escribir.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('historial_estados_residente', 1);
    }

    public function test_fallo_de_auditoria_revierte_decision_e_historial(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Profesional sintético']);
        $usuario->assignRole('MEDICO GENERAL/GERIATRA');
        $this->actingAs($usuario);
        $residente = Residente::factory()->create(['estado' => 'DECISION_ADMISION']);
        $antes = Activity::count();
        Activity::creating(fn () => throw new \RuntimeException('Fallo sintético de auditoría'));
        try {
            app(RegistrarDecisionAdmision::class)->ejecutar($usuario, ['adulto' => $residente,
                'decision' => 'ADMITIDO_NORMAL', 'motivo_decision' => 'Motivo sintético',
                'motivo_derivacion' => null, 'seguimiento_requerido' => null,
                'cuidado_especial_requerido' => null, 'institucion_derivada' => null,
                'recomendacion_final' => null]);
            $this->fail('El fallo debe propagarse.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Fallo sintético de auditoría', $exception->getMessage());
        }
        $this->assertSame('DECISION_ADMISION', $residente->fresh()->estado);
        $this->assertDatabaseCount('historial_estados_residente', 0);
        $this->assertSame($antes, Activity::count());
    }

    public function test_barthel_guarda_los_diez_items_y_conserva_la_valoracion_anterior(): void
    {
        $enfermera = User::factory()->create(['nombres' => 'Enfermera sintética']);
        $enfermera->assignRole('ENFERMEROS');
        $personal = $enfermera->personal;
        $turno = Turno::create(['cod_turno' => 'TUR_MIG_TEST', 'nombre' => 'Turno sintético',
            'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_jornada' => 'JOR_MIG_TEST', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $area = Area::create(['cod_area' => 'ARE_MIG_TEST', 'nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_MIG_TEST', 'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal, 'cod_area' => $area->cod_area,
            'funcion' => 'ENFERMERO', 'tipo_asignacion' => 'TURNO', 'fecha_asignacion' => today(), 'estado' => 'ACTIVO']);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        AsignacionResidenteJornada::create(['cod_asignacion' => 'ARJ_MIG_TEST', 'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada, 'cod_personal' => $personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($enfermera);
        $vista = Livewire::test(ValoracionBarthelModal::class)->set('cod_residente', $residente->cod_residente)
            ->set('fecha_valoracion', today()->toDateString());
        foreach (['alimentacion' => 10, 'bano' => 5, 'aseo_personal' => 5, 'vestido' => 10,
            'control_intestinal' => 10, 'control_vesical' => 10, 'uso_retrete' => 10,
            'traslados' => 15, 'deambulacion' => 15, 'escaleras' => 10] as $campo => $valor) {
            $vista->set($campo, $valor);
        }
        $vista->call('guardar')->assertHasNoErrors()->assertDispatched('valoracion-barthel-guardada');
        $primera = ValoracionFuncional::sole();
        $this->assertStringContainsString('Barthel 100/100', $primera->conclusion);
        $this->assertSame($personal->cod_personal, $primera->cod_personal);
        $vista->set('cod_residente', $residente->cod_residente)->set('fecha_valoracion', today()->toDateString())
            ->set('alimentacion', 10)->set('bano', 5)->set('aseo_personal', 5)->set('vestido', 10)
            ->set('control_intestinal', 10)->set('control_vesical', 10)->set('traslados', 15)->set('deambulacion', 15)
            ->set('escaleras', 0)->set('uso_retrete', 0)->call('guardar')->assertHasNoErrors();
        $this->assertSame('HISTORICA', $primera->fresh()->estado);
        $this->assertStringContainsString('Barthel 80/100', ValoracionFuncional::where('estado', 'ACTIVA')->sole()->conclusion);
        $this->assertDatabaseCount('valoraciones_funcionales', 2);
    }
}
