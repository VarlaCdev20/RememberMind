<?php

namespace Tests\Feature;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Backend\Modulos\Clinica\Servicios\AccesoClinicoTemporalService;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Atencion;
use App\Models\Contacto;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Preadmision;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class Fase3SeguridadNucleoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function familiar(): array
    {
        $usuario = User::factory()->create();
        $usuario->assignRole('FAMILIAR');
        $residente = Residente::factory()->create();
        $contacto = Contacto::create(['cod_contacto' => 'CTO_F3', 'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Familia sintética', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        ResidenteContacto::create(['cod_residente_contacto' => 'RCO_F3', 'cod_contacto' => $contacto->cod_contacto,
            'cod_residente' => $residente->cod_residente, 'parentesco' => 'HIJA', 'estado' => 'ACTIVO',
            'autoriza_informacion' => true, 'responsable_principal' => true,
            'contacto_emergencia' => true, 'autoriza_salida' => false]);
        $this->actingAs($usuario);

        return [$usuario, $residente, $contacto];
    }

    public function test_familiar_vinculado_recibe_solo_identidad_permitida_en_json_y_html(): void
    {
        [, $residente] = $this->familiar();
        $respuesta = $this->getJson(route('admin.residentes.show', $residente))->assertOk();
        $this->assertSame(['cod_residente', 'nombres', 'apellido_paterno'], array_keys($respuesta->json()));
        $respuesta->assertJsonPath('cod_residente', $residente->cod_residente);
        $this->get(route('admin.residentes.show', $residente))->assertOk()
            ->assertSee($residente->nombres)->assertDontSee('Prescripciones')->assertDontSee('Expediente JSON');
    }

    public function test_familiar_ajeno_no_recibe_datos_y_pdf_clinico_no_esta_publicado(): void
    {
        [, $residente] = $this->familiar();
        $ajeno = Residente::factory()->create();
        $this->getJson(route('admin.residentes.show', $ajeno))->assertForbidden()->assertDontSee($ajeno->nombres);
        $this->get(route('admin.reportes.residente', $residente))->assertForbidden();
    }

    public function test_contacto_inactivo_no_habilita_lectura_familiar(): void
    {
        [, $residente, $contacto] = $this->familiar();
        $contacto->update(['estado' => 'INACTIVO']);
        $this->getJson(route('admin.residentes.show', $residente))->assertForbidden();
    }

    public function test_familiar_no_recibe_listados_globales_clinicos_o_redes_de_apoyo_ajenas(): void
    {
        $this->familiar();
        foreach (['admin.adultos-mayores.alertas-pendientes', 'admin.familia-social.resumen', 'admin.familia-social.red-apoyo'] as $ruta) {
            $this->get(route($ruta))->assertForbidden();
        }
    }

    public function test_ficha_legacy_familiar_dirige_a_proyeccion_segura_y_no_lista_documentos_del_residente(): void
    {
        [, $residente] = $this->familiar();
        $this->get(route('admin.adultos-mayores.show', $residente))->assertRedirect(route('admin.residentes.show', $residente));
        $this->get(route('admin.adultos-mayores.documentos.index', $residente))->assertForbidden();
    }

    public function test_relaciones_familiares_no_serializan_contactos_ajenos_o_clinica(): void
    {
        [, $residente] = $this->familiar();
        $this->getJson(route('admin.residentes.relaciones', $residente))->assertOk()
            ->assertJsonMissingPath('contactos')->assertJsonMissingPath('consentimientos')->assertJsonMissingPath('ocupacion');
    }

    public function test_excepcion_superadmin_no_habilita_escritura_en_produccion_con_flag_forzado(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Profesional sintético']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        config()->set('remembermind.superadmin_clinical_write', true);
        $this->app->detectEnvironment(fn () => 'production');
        $this->assertFalse(app(AccesoClinicoTemporalService::class)->sustituyeRol($usuario));
        $this->assertFalse($usuario->can('prescripciones.crear'));
    }

    public function test_action_admision_deniega_actor_sin_permiso_antes_de_persistir(): void
    {
        $usuario = User::factory()->create();
        $solicitud = new Preadmision(['cod_preadmision' => 'PRE_INEXISTENTE']);
        try {
            app(FormalizarAdmision::class)->ejecutar($solicitud, ['cod_cama' => 'CAM_INEXISTENTE'], $usuario);
            $this->fail('La Action debe denegar antes de consultar o persistir.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
        $this->assertDatabaseCount('residentes', 0);
        $this->assertDatabaseCount('admisiones', 0);
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }

    private function escenarioMedico(): array
    {
        $medico = User::factory()->create(['nombres' => 'Médico sintético']);
        $medico->assignRole('MEDICO GENERAL/GERIATRA');
        $medico->personal->update(['profesion' => 'MEDICO']);
        $residente = Residente::factory()->create();
        $area = Area::create(['cod_area' => 'ARE_MED_F3', 'nombre' => 'Medicina sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['cod_turno' => 'TUR_MED_F3', 'nombre' => 'Prueba', 'orden' => 1,
            'hora_inicio' => '00:00:00', 'hora_cierre' => '23:59:59', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_jornada' => 'JOR_MED_F3', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_asignacion_personal' => 'ASP_MED_F3', 'cod_personal' => $medico->personal->cod_personal,
            'cod_area' => $area->cod_area, 'cod_jornada' => $jornada->cod_jornada, 'tipo_asignacion' => 'RESPONSABLE',
            'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        $atencion = Atencion::create(['cod_atencion' => 'ATE_MED_F3', 'cod_residente' => $residente->cod_residente,
            'cod_personal' => $medico->personal->cod_personal, 'cod_area' => $area->cod_area,
            'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'ABIERTA']);
        $medicamento = Medicamento::create(['cod_medicamento' => 'MED_F3', 'nombre_generico' => 'Medicamento sintético',
            'control_especial' => false, 'estado' => 'ACTIVO']);

        return [$medico, $residente, $atencion, $medicamento];
    }

    public function test_medico_prescribe_y_el_payload_no_puede_elegir_otro_autor(): void
    {
        [$medico, $residente, $atencion, $medicamento] = $this->escenarioMedico();
        $this->actingAs($medico)->postJson(route('admin.prescripciones.store', $residente), [
            'cod_atencion' => $atencion->cod_atencion, 'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => 'AUTOR_AJENO', 'via_administracion' => 'ORAL', 'segun_necesidad' => false,
            'horarios' => [['hora_programada' => '10:00']],
        ])->assertCreated()->assertJsonPath('cod_personal', $medico->personal->cod_personal);
        $this->assertDatabaseCount('prescripciones', 1);
        $this->assertDatabaseCount('horarios_prescripcion', 1);
    }

    public function test_enfermeria_no_crea_ni_edita_prescripciones_aunque_tenga_permiso(): void
    {
        [$medico, $residente, $atencion, $medicamento] = $this->escenarioMedico();
        $prescripcion = Prescripcion::create(['cod_prescripcion' => 'PRS_F3', 'cod_residente' => $residente->cod_residente,
            'cod_atencion' => $atencion->cod_atencion, 'cod_medicamento' => $medicamento->cod_medicamento,
            'cod_personal' => $medico->personal->cod_personal, 'via_administracion' => 'ORAL',
            'segun_necesidad' => false, 'fecha_hora_prescripcion' => now(), 'estado' => 'ACTIVA']);
        $enfermera = User::factory()->create(['nombres' => 'Enfermera sintética']);
        $enfermera->assignRole('ENFERMEROS');
        $enfermera->givePermissionTo(['prescripciones.crear', 'prescripciones.editar']);
        $this->actingAs($enfermera)->postJson(route('admin.prescripciones.store', $residente), [])->assertForbidden();
        $this->putJson(route('admin.adultos-mayores.medicacion.update', [$residente, $prescripcion]), [])->assertForbidden();
        $this->assertDatabaseCount('prescripciones', 1);
        $this->assertSame($medico->personal->cod_personal, $prescripcion->fresh()->cod_personal);
        $this->assertSame('ACTIVA', $prescripcion->fresh()->estado);
    }

    public function test_administrador_no_crea_diagnostico_con_un_grant_tecnico(): void
    {
        $administrador = User::factory()->create();
        $administrador->assignRole('ADMINISTRADOR');
        $administrador->givePermissionTo('diagnosticos.crear');
        $residente = Residente::factory()->create();
        $this->actingAs($administrador)->postJson(route('admin.clinica.store', [$residente, 'diagnostico']), [])->assertForbidden();
        $this->assertDatabaseCount('diagnosticos', 0);
    }

    public function test_superadmin_sin_excepcion_no_escribe_clinica_por_su_rol(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Superadmin sintético']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        config()->set('remembermind.superadmin_clinical_write', false);
        $residente = Residente::factory()->create();
        $this->actingAs($usuario)->postJson(route('admin.prescripciones.store', $residente), [])->assertForbidden();
        $this->assertDatabaseCount('prescripciones', 0);
    }
}
