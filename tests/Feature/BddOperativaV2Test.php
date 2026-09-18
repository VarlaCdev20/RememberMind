<?php

namespace Tests\Feature;

use App\Actions\Admisiones\FormalizarAdmision;
use App\Models\AdministracionMedicacion;
use App\Models\Admision;
use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\Atencion;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\OcupacionCama;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\PreguntaInstrumento;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\Turno;
use App\Models\User;
use App\Policies\AdministracionMedicacionPolicy;
use App\Policies\PrescripcionPolicy;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BddOperativaV2Test extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_existen_exactamente_las_69_tablas_operativas(): void
    {
        $esperadas = $this->tablasOperativas();
        $tecnicas = [
            'migrations','password_reset_tokens','sessions','cache','cache_locks','jobs','job_batches',
            'failed_jobs','personal_access_tokens','activity_log','permissions','roles',
            'model_has_permissions','model_has_roles','role_has_permissions',
        ];
        $listado = array_map(
            static fn (string $tabla): string => (string) str($tabla)->afterLast('.'),
            Schema::getTableListing()
        );
        $reales = array_values(array_diff($listado, $tecnicas));
        sort($esperadas);
        sort($reales);

        $this->assertCount(69, $reales);
        $this->assertSame($esperadas, $reales);
        foreach ($esperadas as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla), "Falta la tabla operativa {$tabla}");
        }
        $this->assertFalse(Schema::hasTable('adulto_mayor'));
        $this->assertFalse(Schema::hasTable('voluntarios'));
    }

    public function test_las_claves_y_columnas_de_identidad_respetan_el_baseline(): void
    {
        $this->assertSame(['cod_usuario','correo','contrasena','foto','estado'], Schema::getColumnListing('usuarios'));
        $this->assertTrue(Schema::hasColumns('residentes', ['cod_residente','numero_documento','fecha_nacimiento','estado']));
        $this->assertFalse(Schema::hasColumn('usuarios', 'cod_usu'));
        $this->assertFalse(Schema::hasColumn('residentes', 'cod_am'));
    }

    public function test_autenticacion_y_sanctum_funcionan_con_cod_usuario_string(): void
    {
        $usuario = User::factory()->create(['correo'=>'acceso@test.local','contrasena'=>'ClaveSegura123!']);
        $this->post('/login', ['correo'=>'acceso@test.local','password'=>'ClaveSegura123!'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($usuario);
        $token = $usuario->createToken('prueba');
        $this->assertSame($usuario->cod_usuario, $token->accessToken->tokenable_id);
    }

    public function test_perfil_jetstream_sigue_disponible(): void
    {
        $usuario = User::factory()->create();
        $this->actingAs($usuario)->get('/user/profile')->assertOk();
    }

    public function test_fk_rechaza_una_cama_sin_habitacion(): void
    {
        $this->expectException(QueryException::class);
        Cama::query()->create(['cod_cama'=>'CAM_X','cod_habitacion'=>'HAB_X','codigo'=>'X','estado'=>'ACTIVA']);
    }

    public function test_residente_solo_se_crea_al_formalizar_admision_y_aprobar_no_lo_crea(): void
    {
        $datos = $this->escenarioAdmision();
        $this->assertDatabaseCount('residentes', 0);

        $residente = app(FormalizarAdmision::class)->ejecutar($datos['preadmision'], [
            'cod_cama' => $datos['cama']->cod_cama,
            'cod_contacto' => $datos['contacto']->cod_contacto,
            'parentesco' => 'HIJA',
        ], $datos['usuario']);

        $this->assertSame('ADMITIDO', $residente->estado);
        $this->assertDatabaseHas('admisiones', ['cod_residente'=>$residente->cod_residente]);
        $this->assertDatabaseHas('residentes_contactos', ['cod_residente'=>$residente->cod_residente]);
        $this->assertDatabaseHas('ocupaciones_cama', ['cod_residente'=>$residente->cod_residente,'estado'=>'ACTIVA']);
        $this->assertDatabaseHas('historial_estados_residente', ['cod_residente'=>$residente->cod_residente,'estado_nuevo'=>'ADMITIDO']);
        $this->assertDatabaseHas('consentimientos', ['cod_residente'=>$residente->cod_residente,'estado'=>'VIGENTE']);
    }

    public function test_creacion_directa_de_residente_esta_bloqueada(): void
    {
        $this->expectException(\LogicException::class);
        Residente::query()->create([
            'cod_residente'=>'RES_DIRECTO','nombres'=>'No','apellido_paterno'=>'Permitido',
            'fecha_nacimiento'=>'1940-01-01','estado'=>'ADMITIDO',
        ]);
    }

    public function test_no_se_reutiliza_cama_ni_residente_con_ocupacion_activa(): void
    {
        $datos = $this->escenarioAdmision();
        $residente = app(FormalizarAdmision::class)->ejecutar($datos['preadmision'], [
            'cod_cama'=>$datos['cama']->cod_cama,'cod_contacto'=>$datos['contacto']->cod_contacto,
        ], $datos['usuario']);

        $admision = Admision::query()->firstOrFail();
        $this->expectException(ValidationException::class);
        OcupacionCama::query()->create([
            'cod_ocupacion'=>'OCU_DUP','cod_residente'=>$residente->cod_residente,
            'cod_cama'=>$datos['cama']->cod_cama,'cod_admision'=>$admision->cod_admision,
            'cod_usuario_registro'=>$datos['usuario']->cod_usuario,'fecha_hora_asignacion'=>now(),'estado'=>'ACTIVA',
        ]);
    }

    public function test_administracion_debe_corresponder_a_prescripcion_del_mismo_residente(): void
    {
        [$base, $residente] = $this->escenarioClinico();
        $otro = $this->crearSegundoResidente($base);
        $prescripcion = $this->crearPrescripcion($base, $residente);

        $this->expectException(ValidationException::class);
        AdministracionMedicacion::query()->create([
            'cod_administracion'=>'AMD_X','cod_prescripcion'=>$prescripcion->cod_prescripcion,
            'cod_residente'=>$otro->cod_residente,'cod_jornada'=>$base['jornada']->cod_jornada,
            'cod_personal'=>$base['personal']->cod_personal,'resultado'=>'ADMINISTRADA','estado'=>'REGISTRADA',
        ]);
    }

    public function test_respuestas_solo_aceptan_preguntas_del_instrumento_aplicado(): void
    {
        [$base, $residente] = $this->escenarioClinico();
        $i1 = Instrumento::query()->create(['cod_instrumento'=>'INS_1','codigo'=>'I1','nombre'=>'Uno','tipo'=>'COGNITIVO','estado'=>'ACTIVO']);
        $i2 = Instrumento::query()->create(['cod_instrumento'=>'INS_2','codigo'=>'I2','nombre'=>'Dos','tipo'=>'FUNCIONAL','estado'=>'ACTIVO']);
        $pregunta = PreguntaInstrumento::query()->create(['cod_pregunta'=>'PRE_2','cod_instrumento'=>$i2->cod_instrumento,'codigo'=>'P1','enunciado'=>'Pregunta','tipo_respuesta'=>'TEXTO','orden'=>1,'estado'=>'ACTIVA']);
        $aplicacion = AplicacionInstrumento::query()->create(['cod_aplicacion'=>'APL_1','cod_instrumento'=>$i1->cod_instrumento,'cod_residente'=>$residente->cod_residente,'cod_personal'=>$base['personal']->cod_personal,'fecha_hora'=>now(),'estado'=>'COMPLETA']);

        $this->expectException(ValidationException::class);
        RespuestaInstrumento::query()->create(['cod_respuesta'=>'RSP_1','cod_aplicacion'=>$aplicacion->cod_aplicacion,'cod_pregunta'=>$pregunta->cod_pregunta,'valor_texto'=>'X']);
    }

    public function test_roles_competencias_y_acceso_familiar_estan_separados(): void
    {
        $datos = $this->escenarioAdmision(true);
        $residente = app(FormalizarAdmision::class)->ejecutar($datos['preadmision'], ['cod_cama'=>$datos['cama']->cod_cama,'cod_contacto'=>$datos['contacto']->cod_contacto], $datos['usuario']);

        $medico = $this->usuarioRol('medico@test.local','MEDICO GENERAL/GERIATRA');
        $enfermera = $this->usuarioRol('enfermeria@test.local','ENFERMEROS');
        $administrador = $this->usuarioRol('administrador@test.local','ADMINISTRADOR');

        $this->assertTrue(app(PrescripcionPolicy::class)->create($medico));
        $this->assertFalse(app(PrescripcionPolicy::class)->create($enfermera));
        $this->assertTrue(app(AdministracionMedicacionPolicy::class)->create($enfermera));
        $this->assertFalse($administrador->can('diagnosticos.crear'));
        $this->assertTrue(Gate::forUser($datos['familiar'])->allows('view', $residente));

        $ajeno = $this->usuarioRol('familiar.ajeno@test.local','FAMILIAR');
        $this->assertFalse(Gate::forUser($ajeno)->allows('view', $residente));
        $this->assertFalse(Role::query()->where('name','VOLUNTARIO')->exists());
        $this->assertCount(9, Role::all());
    }

    public function test_superadministrador_puede_ver_todas_las_tablas_operativas(): void
    {
        $super = User::query()->where('correo','admincasaamandita@gmail.com')->firstOrFail();
        foreach ($this->tablasOperativas() as $tabla) {
            $this->assertTrue($super->can($tabla.'.ver'), "Falta lectura de {$tabla}");
        }
        $this->assertFalse($super->can('prescripciones.crear'));
        $this->assertFalse($super->can('diagnosticos.crear'));
    }

    private function escenarioAdmision(bool $contactoConCuenta = false): array
    {
        $usuario = User::factory()->create();
        $familiar = $contactoConCuenta ? $this->usuarioRol('familiar@test.local','FAMILIAR') : null;
        $contacto = Contacto::query()->create(['cod_contacto'=>'CTO_1','cod_usuario'=>$familiar?->cod_usuario,'nombres'=>'Ana','apellido_paterno'=>'Pérez','estado'=>'ACTIVO']);
        $habitacion = Habitacion::query()->create(['cod_habitacion'=>'HAB_1','codigo'=>'H-1','capacidad'=>2,'estado'=>'ACTIVA']);
        $cama = Cama::query()->create(['cod_cama'=>'CAM_1','cod_habitacion'=>$habitacion->cod_habitacion,'codigo'=>'C-1','estado'=>'ACTIVA']);
        $preadmision = Preadmision::query()->create([
            'cod_preadmision'=>'PRE_1','cod_contacto'=>$contacto->cod_contacto,
            'cod_usuario_registro'=>$usuario->cod_usuario,'nombres'=>'Rosa','apellido_paterno'=>'Flores',
            'fecha_nacimiento'=>'1945-05-05','motivo_ingreso'=>'Cuidado integral','fecha_solicitud'=>now(),'estado'=>'APROBADA',
        ]);

        return compact('usuario','familiar','contacto','habitacion','cama','preadmision');
    }

    private function escenarioClinico(): array
    {
        $datos = $this->escenarioAdmision();
        $residente = app(FormalizarAdmision::class)->ejecutar($datos['preadmision'], ['cod_cama'=>$datos['cama']->cod_cama,'cod_contacto'=>$datos['contacto']->cod_contacto], $datos['usuario']);
        $area = Area::query()->create(['cod_area'=>'ARE_1','nombre'=>'Clínica','estado'=>'ACTIVA']);
        $personal = Personal::query()->create(['cod_personal'=>'PER_1','cod_usuario'=>$datos['usuario']->cod_usuario,'nombres'=>'Mario','apellido_paterno'=>'Médico','numero_documento'=>'DOC-1','profesion'=>'MÉDICO','estado'=>'ACTIVO']);
        $turno = Turno::query()->create(['cod_turno'=>'TUR_1','nombre'=>'Mañana','hora_inicio'=>'08:00','hora_cierre'=>'16:00','orden'=>1,'estado'=>'ACTIVO']);
        $jornada = Jornada::query()->create(['cod_jornada'=>'JOR_1','cod_turno'=>$turno->cod_turno,'fecha_jornada'=>today(),'estado'=>'ABIERTA']);

        return [[...$datos, ...compact('area','personal','turno','jornada')], $residente];
    }

    private function crearSegundoResidente(array $base): Residente
    {
        $contacto = Contacto::query()->create(['cod_contacto'=>'CTO_2','nombres'=>'Luis','apellido_paterno'=>'Rojas','estado'=>'ACTIVO']);
        $cama = Cama::query()->create(['cod_cama'=>'CAM_2','cod_habitacion'=>$base['habitacion']->cod_habitacion,'codigo'=>'C-2','estado'=>'ACTIVA']);
        $pre = Preadmision::query()->create(['cod_preadmision'=>'PRE_2','cod_contacto'=>$contacto->cod_contacto,'cod_usuario_registro'=>$base['usuario']->cod_usuario,'nombres'=>'José','apellido_paterno'=>'Rojas','fecha_nacimiento'=>'1941-01-01','motivo_ingreso'=>'Cuidados','fecha_solicitud'=>now(),'estado'=>'APROBADA']);

        return app(FormalizarAdmision::class)->ejecutar($pre, ['cod_cama'=>$cama->cod_cama,'cod_contacto'=>$contacto->cod_contacto], $base['usuario']);
    }

    private function crearPrescripcion(array $base, Residente $residente): Prescripcion
    {
        $atencion = Atencion::query()->create(['cod_atencion'=>'ATE_1','cod_residente'=>$residente->cod_residente,'cod_area'=>$base['area']->cod_area,'cod_personal'=>$base['personal']->cod_personal,'tipo_atencion'=>'CONSULTA','fecha_hora'=>now(),'estado'=>'FINALIZADA']);
        $medicamento = Medicamento::query()->create(['cod_medicamento'=>'MED_1','nombre_generico'=>'Paracetamol','control_especial'=>false,'estado'=>'ACTIVO']);

        return Prescripcion::query()->create(['cod_prescripcion'=>'PRS_1','cod_residente'=>$residente->cod_residente,'cod_atencion'=>$atencion->cod_atencion,'cod_medicamento'=>$medicamento->cod_medicamento,'cod_personal'=>$base['personal']->cod_personal,'via_administracion'=>'ORAL','segun_necesidad'=>false,'fecha_hora_prescripcion'=>now(),'estado'=>'ACTIVA']);
    }

    private function usuarioRol(string $correo, string $rol): User
    {
        $usuario = User::factory()->create(['correo'=>$correo,'contrasena'=>Hash::make('password')]);
        $usuario->assignRole($rol);
        return $usuario;
    }

    private function tablasOperativas(): array
    {
        return [
            'usuarios','personal','areas','turnos','contactos','residentes','habitaciones','camas','tipos_estudio_clinico','medicamentos','instrumentos',
            'jornadas','preadmisiones','admisiones','historial_estados_residente','documentos','consentimientos','atenciones','notas_clinicas','antecedentes_clinicos','diagnosticos','alergias','seguros_residente','dispositivos_clinicos','signos_vitales','valoraciones_dolor','mediciones_antropometricas','estudios_clinicos','informes_estudio','documentos_clinicos','derivaciones','incidentes','indicaciones_clinicas','controles_cognitivos','registros_conductuales','registros_sueno','registros_ingesta','registros_hidratacion','registros_eliminacion','registros_movilidad','heridas','curaciones_herida','pases_turno','planes_cuidado','ejecuciones_cuidado','prescripciones','administraciones_medicacion','aplicaciones_instrumento','valoraciones_psicologicas','valoraciones_nutricionales','valoraciones_funcionales','seguimientos_pedagogicos','actividades','visitas','alertas','eventos_alerta',
            'asignaciones_personal','residentes_contactos','ocupaciones_cama','resultados_estudio','asignaciones_residente_jornada','respuestas_instrumento','participantes_actividad',
            'componentes_estudio','intervenciones_cuidado','programaciones_cuidado','horarios_prescripcion','preguntas_instrumento','opciones_pregunta',
        ];
    }
}
