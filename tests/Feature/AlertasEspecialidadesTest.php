<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\RegistrarActuacionEspecialidadDesdeAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\CompararAlertasRoles;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AlertasEspecialidadesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-21 10:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_especialidades_guardan_sus_campos_con_autoria_transaccion_y_alerta_conservada(): void
    {
        $r = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $alerta = Alerta::create(['cod_residente' => $r->getKey(), 'tipo' => 'SEGUIMIENTO', 'prioridad' => 'MEDIA', 'estado' => 'ABIERTA', 'descripcion' => 'Situación ficticia para una prueba aislada.']);
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Jornada de prueba', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        $casos = [
            ['MEDICO GENERAL/GERIATRA', 'notas_clinicas', ['tipo_nota' => 'EVOLUCION', 'contenido' => 'Hallazgos ficticios para probar la persistencia.']],
            ['PSICOLOGO/A', 'valoraciones_psicologicas', ['estado_animo' => 'Observación de prueba', 'comunicacion' => 'Registro de comunicación de prueba.']],
            ['NUTRICIONISTA', 'valoraciones_nutricionales', ['apetito' => 'Observación de prueba', 'requerimiento_hidrico' => '1600.50']],
            ['FISIOTERAPEUTA', 'valoraciones_funcionales', ['marcha' => 'Observación de prueba', 'necesita_supervision' => false]],
            ['PEDAGOGO', 'seguimientos_pedagogicos', ['participacion' => 'Observación de prueba', 'cambio_desempeno' => false]],
        ];
        foreach ($casos as [$rol, $tabla, $campos]) {
            $usuario = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Profesional de prueba']);
            $usuario->assignRole($rol);
            $usuario->personal->update(['profesion' => match ($rol) {
                'MEDICO GENERAL/GERIATRA' => 'MEDICINA', 'PSICOLOGO/A' => 'PSICOLOGIA', 'NUTRICIONISTA' => 'NUTRICION',
                'FISIOTERAPEUTA' => 'FISIOTERAPIA', 'PEDAGOGO' => 'PEDAGOGIA',
            }]);
            AsignacionPersonal::create(['cod_personal' => $usuario->personal->getKey(), 'cod_area' => $area->getKey(),
                'cod_jornada' => $jornada->getKey(), 'estado' => 'ACTIVA', 'tipo_asignacion' => 'RESPONSABLE', 'fecha_asignacion' => now()]);
            Livewire::flushState();
            $panel = Livewire::actingAs($usuario)->test(AlertasPanel::class)
                ->call('abrirRegistroEspecialidad', $alerta->getKey())->assertSet('modalEspecialidad', true)
                ->call('guardarRegistroEspecialidad')->assertHasErrors('capturaEspecialidad');
            foreach ($campos as $campo => $valor) {
                $panel->set('capturaEspecialidad.'.$campo, $valor);
            }
            if ($rol !== 'PEDAGOGO') {
                $panel->set('atencionEspecialidad', 'NUEVA')->set('tipoAtencionEspecialidad', 'Atención de prueba');
            }
            if ($rol === 'NUTRICIONISTA') {
                $antesAtenciones = Atencion::count();
                $panel->set('capturaEspecialidad.requerimiento_hidrico', -1)->call('guardarRegistroEspecialidad')
                    ->assertHasErrors('capturaEspecialidad.requerimiento_hidrico');
                $this->assertSame($antesAtenciones, Atencion::count());
                $this->assertDatabaseCount('valoraciones_nutricionales', 0);
                $panel->set('capturaEspecialidad.requerimiento_hidrico', '1600.50');
            }
            $panel->call('solicitarCerrarModales')->assertSet('confirmarDescarte', true)->call('continuarCaptura');
            $solicitud = $panel->get('solicitudEspecialidad');
            $captura = $panel->get('capturaEspecialidad');
            $contexto = ['atencion' => $panel->get('atencionEspecialidad'), 'tipoAtencion' => $panel->get('tipoAtencionEspecialidad'), 'motivoAtencion' => ''];
            $panel->call('guardarRegistroEspecialidad')->assertHasNoErrors()->assertSet('modalEspecialidad', false);
            $registro = DB::table($tabla)->sole();
            $this->assertSame($r->getKey(), $registro->cod_residente);
            $this->assertSame($usuario->personal->getKey(), $registro->cod_personal);
            $this->assertSame('ABIERTA', $alerta->fresh()->estado);
            if ($rol === 'NUTRICIONISTA') {
                $this->assertEquals(1600.50, $registro->requerimiento_hidrico);
                app(RegistrarActuacionEspecialidadDesdeAlerta::class)->ejecutar($usuario, $alerta->getKey(), $captura, $contexto, $solicitud);
                $this->assertDatabaseCount($tabla, 1);
                $ajena = Atencion::create(['cod_residente' => Residente::factory()->create()->getKey(), 'cod_personal' => $usuario->personal->getKey(), 'cod_area' => $area->getKey(), 'tipo_atencion' => 'Otra atención', 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
                try {
                    app(RegistrarActuacionEspecialidadDesdeAlerta::class)->ejecutar($usuario, $alerta->getKey(), $captura, ['atencion' => $ajena->getKey()], (string) Str::uuid());
                    $this->fail('Debe rechazar una atención de otro residente.');
                } catch (ValidationException $e) {
                    $this->assertArrayHasKey('atencionEspecialidad', $e->errors());
                }
                $this->assertDatabaseCount($tabla, 1);
                $atencionOtroProfesional = Atencion::where('cod_residente', $r->getKey())
                    ->where('cod_personal', '!=', $usuario->personal->getKey())->firstOrFail();
                try {
                    app(RegistrarActuacionEspecialidadDesdeAlerta::class)->ejecutar($usuario, $alerta->getKey(), $captura, ['atencion' => $atencionOtroProfesional->getKey()], (string) Str::uuid());
                    $this->fail('Debe rechazar una atención de otro profesional.');
                } catch (ValidationException $e) {
                    $this->assertArrayHasKey('atencionEspecialidad', $e->errors());
                }
                $this->assertDatabaseCount($tabla, 1);
            }
        }
        $this->assertDatabaseCount('eventos_alerta', 5);
        $this->assertSame(5, DB::table('activity_log')->where('log_name', 'alertas')->count());
    }

    public function test_comparacion_muestra_diez_roles_sin_datos_personales_y_no_concede_escritura_clinica(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('ADMINISTRADOR');
        $this->actingAs($admin)->get(route('admin.enfermeria.alertas'))->assertOk()
            ->assertSee('Coordinación de alertas')->assertDontSee('class="rm-shell rm-nursing-shell', false)
            ->assertDontSee("has('fechaDesde')", false)->assertDontSee("has('fechaHasta')", false);
        $panel = Livewire::actingAs($admin)->test(CompararAlertasRoles::class)->assertSee('Comparación sin datos de residentes');
        foreach (['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'GERENTE', 'ADMINISTRADOR', 'SUPERADMINISTRADOR', 'FAMILIAR'] as $rol) {
            $panel->call('seleccionarRol', $rol)->assertSet('rol', $rol)->assertDispatched('alertas-rol-seleccionado');
            if ($rol === 'FAMILIAR') {
                $panel->assertDontSee('data-case-view=', false);
            } else {
                $tipo = ['ENFERMEROS' => 'cronologia', 'MEDICO GENERAL/GERIATRA' => 'contraste', 'PSICOLOGO/A' => 'red', 'NUTRICIONISTA' => 'panel-nutricional', 'FISIOTERAPEUTA' => 'matriz', 'PEDAGOGO' => 'recorrido', 'GERENTE' => 'arbol', 'ADMINISTRADOR' => 'tablero', 'SUPERADMINISTRADOR' => 'procedencia'][$rol];
                $panel->assertSee('data-case-view="'.$tipo.'"', false)->assertSee('sin caso seleccionado');
            }
        }
        $panel->assertSee('No accede al centro de alertas clínicas');
        $r = Residente::factory()->create();
        $alerta = Alerta::create(['cod_residente' => $r->getKey(), 'tipo' => 'SEGUIMIENTO', 'prioridad' => 'MEDIA', 'estado' => 'ABIERTA']);
        Livewire::test(AlertasPanel::class)->call('abrirRegistroEspecialidad', $alerta->getKey())->assertForbidden();
        $this->assertDatabaseCount('notas_clinicas', 0);
        $this->assertDatabaseCount('valoraciones_nutricionales', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_registro_no_admite_suplantacion_cuenta_inactiva_ni_falta_de_permiso(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('NUTRICIONISTA');
        $otroUsuario = User::factory()->create(['estado' => 'ACTIVO']);
        $otroUsuario->assignRole('ADMINISTRADOR');
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $alerta = Alerta::create(['cod_residente' => $residente->getKey(), 'tipo' => 'SEGUIMIENTO', 'prioridad' => 'MEDIA', 'estado' => 'ABIERTA']);
        $auditoriaInicial = DB::table('activity_log')->count();
        $ejecutar = function () use ($usuario, $alerta) {
            try {
                app(RegistrarActuacionEspecialidadDesdeAlerta::class)->ejecutar($usuario, $alerta->getKey(), ['apetito' => 'Observación ficticia'], ['atencion' => 'NUEVA'], (string) Str::uuid());
                $this->fail('Una entrada no autorizada no debe generar registros.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        };
        $this->actingAs($otroUsuario);
        $ejecutar();
        $this->actingAs($usuario);
        $usuario->update(['estado' => 'INACTIVO']);
        $auditoriaInicial = DB::table('activity_log')->count();
        $ejecutar();
        $usuario->update(['estado' => 'ACTIVO']);
        $usuario->roles()->firstOrFail()->revokePermissionTo('valoraciones_nutricionales.crear');
        $usuario->unsetRelation('roles');
        $auditoriaInicial = DB::table('activity_log')->count();
        $ejecutar();
        $this->assertDatabaseCount('atenciones', 0);
        $this->assertDatabaseCount('valoraciones_nutricionales', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->assertSame($auditoriaInicial, DB::table('activity_log')->count());
    }

    public function test_seeder_oficial_conserva_cuentas_datos_y_relaciones_al_repetirse(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Profesional de prueba aislada']);
        $usuario->assignRole('NUTRICIONISTA');
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $alerta = Alerta::create(['cod_residente' => $residente->getKey(), 'tipo' => 'SEGUIMIENTO', 'prioridad' => 'MEDIA', 'estado' => 'ABIERTA']);
        $filas = [$usuario->fresh()->getRawOriginal(), $usuario->personal->fresh()->getRawOriginal(), $residente->fresh()->getRawOriginal(), $alerta->fresh()->getRawOriginal()];
        $rolesCuenta = DB::table('model_has_roles')->get()->map(fn ($fila) => (array) $fila)->all();
        $cantidadPermisos = DB::table('permissions')->count();
        $cantidadRelaciones = DB::table('role_has_permissions')->count();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(RolesAndPermissionsSeeder::class);
        foreach (['NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'] as $nombre) {
            $rol = Role::findByName($nombre, 'web');
            $this->assertTrue($rol->hasPermissionTo('alertas.ver'));
            $this->assertFalse($rol->hasPermissionTo('alertas.gestionar'));
        }
        $this->assertFalse(Role::findByName('FAMILIAR', 'web')->hasPermissionTo('alertas.ver'));
        $this->assertSame($filas, [$usuario->fresh()->getRawOriginal(), $usuario->personal->fresh()->getRawOriginal(), $residente->fresh()->getRawOriginal(), $alerta->fresh()->getRawOriginal()]);
        $this->assertSame($rolesCuenta, DB::table('model_has_roles')->get()->map(fn ($fila) => (array) $fila)->all());
        $this->assertSame($cantidadPermisos, DB::table('permissions')->count());
        $this->assertSame($cantidadRelaciones, DB::table('role_has_permissions')->count());
        $this->assertTrue($usuario->fresh()->can('alertas.ver'));
    }
}
