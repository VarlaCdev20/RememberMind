<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\ReglaExperta;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarReglasExpertas as Gestor;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\IdentidadesConocimiento;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\GobiernoReglasPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

final class GobiernoReglasExpertasTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = $this->profesional('MEDICO GENERAL/GERIATRA', 'MEDICINA');
        $this->actingAs($this->medico);
        InformeCognitivoFixture::conocimiento($this->medico);
    }

    private function profesional(string $role, string $profession): User
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole($role);
        Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'GOB-'.$u->getKey(), 'profesion' => $profession, 'estado' => 'ACTIVO']);

        return $u;
    }

    private function revision(): string
    {
        return app(Gestor::class)->crearRevision($this->medico, 'VER_TEST', 'ORION-GOB-1', 'Revisión explícita del conocimiento para ensayo de ingeniería.');
    }

    public function test_archivo_crea_sucesora_con_autor_y_pendientes_sin_cambiar_el_original(): void
    {
        $original = app(ConocimientoVersionado::class)->instantanea('VER_TEST');
        $id = app(Gestor::class)->crearRevision($this->medico, 'VER_TEST', 'ORION-ARCHIVO-1', 'Archivar esta regla manteniendo su contenido original.', retiradas: ['RULE_A']);
        $new = ReglaExperta::query()->where('cod_version_modelo', $id)->where('cod_regla_anterior', 'RULE_A')->sole();
        self::assertSame('INACTIVO', $new->estado);
        self::assertSame('PENDIENTE', $new->estado_revision);
        self::assertSame($this->medico->getKey(), $new->cod_usuario_creacion);
        self::assertNull($new->cod_usuario_revision);
        self::assertNotNull(DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->value('fecha_hora_retiro'));
        self::assertSame($original, app(ConocimientoVersionado::class)->instantanea('VER_TEST'));
    }

    public function test_psicologia_registra_decision_una_vez_y_el_reintento_no_duplica_auditoria(): void
    {
        $id = $this->revision();
        $rule = ReglaExperta::query()->where('cod_version_modelo', $id)->where('cod_regla_anterior', 'RULE_A')->sole();
        $psych = $this->profesional('PSICOLOGO/A', 'PSICOLOGIA');
        $before = $rule->condicionesReglaExperta()->get()->toArray();
        app(Gestor::class)->revisar($psych, $rule->getKey(), 'APROBADA', 'Condiciones revisadas por la persona responsable.');
        app(Gestor::class)->revisar($psych, $rule->getKey(), 'APROBADA', 'Condiciones revisadas por la persona responsable.');
        self::assertSame('APROBADA', $rule->fresh()->estado_revision);
        self::assertSame($psych->getKey(), $rule->fresh()->cod_usuario_revision);
        self::assertSame($before, $rule->condicionesReglaExperta()->get()->toArray());
        self::assertSame(1, DB::table('activity_log')->where('event', 'regla_revisada')->count());
    }

    public function test_rechazo_no_se_sobrescribe_con_otra_decision(): void
    {
        $id = $this->revision();
        $rule = ReglaExperta::query()->where('cod_version_modelo', $id)->firstOrFail();
        app(Gestor::class)->revisar($this->medico, $rule->getKey(), 'RECHAZADA', 'Requiere una definición nueva para este caso.');
        try {
            app(Gestor::class)->revisar($this->medico, $rule->getKey(), 'APROBADA', 'Intento de reemplazar la decisión original.');
            self::fail('Se sobrescribió una revisión.');
        } catch (DomainException) {
            self::assertSame('RECHAZADA', $rule->fresh()->estado_revision);
        }
    }

    public function test_superadministracion_no_escribe_aunque_tenga_permiso_aislado(): void
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('SUPERADMINISTRADOR');
        $u->givePermissionTo('reglas_expertas.gestionar');
        $before = DB::table('versiones_modelo_experto')->count();
        try {
            app(Gestor::class)->crearRevision($u, 'VER_TEST', 'ORION-DENEGADA', 'No autorizar escritura por un permiso aislado.');
            self::fail('Se concedió escritura a una cuenta de consulta.');
        } catch (AuthorizationException) {
            self::assertSame($before, DB::table('versiones_modelo_experto')->count());
        }
    }

    public function test_permiso_retirado_impide_escritura(): void
    {
        $this->medico->revokePermissionTo('reglas_expertas.gestionar');
        // El permiso viene del rol: se retira del rol en esta prueba.
        $this->medico->roles()->firstOrFail()->revokePermissionTo('reglas_expertas.gestionar');
        $this->expectException(AuthorizationException::class);
        $this->revision();
    }

    public function test_fallo_de_traduccion_revierte_version_y_auditoria(): void
    {
        $counts = [DB::table('versiones_modelo_experto')->count(), DB::table('activity_log')->count()];
        $this->mock(IdentidadesConocimiento::class)->shouldReceive('traducir')->once()->andThrow(new DomainException('Fallo de traducción.'));
        try {
            $this->revision();
            self::fail('Se ignoró el fallo.');
        } catch (DomainException) {
            self::assertSame($counts, [DB::table('versiones_modelo_experto')->count(), DB::table('activity_log')->count()]);
        }
    }

    public function test_check_fisico_rechaza_firma_con_estado_nulo_y_autorreferencia(): void
    {
        foreach ([['cod_usuario_revision' => $this->medico->getKey()], ['cod_regla_anterior' => 'RULE_A']] as $bad) {
            try {
                DB::transaction(fn () => DB::table('reglas_expertas')->where('cod_regla_experta', 'RULE_A')->update($bad));
                self::fail('Se aceptó una revisión incompleta.');
            } catch (QueryException) {
                self::assertNull(ReglaExperta::query()->findOrFail('RULE_A')->estado_revision);
            }
        }
    }

    public function test_rollback_de_migracion_con_historia_se_rechaza_sin_perder_columnas(): void
    {
        $this->revision();
        $migration = require database_path('migrations/2026_10_18_002000_add_revision_y_sucesion_reglas_expertas.php');
        $this->expectException(\LogicException::class);
        $migration->down();
    }

    public function test_interfaz_crea_sucesora_y_registra_revision_con_identidad_de_sesion(): void
    {
        $panel = Livewire::test(GobiernoReglasPanel::class)
            ->set('versionSeleccionada', 'VER_TEST')->assertSee('Crear versión sucesora')
            ->call('prepararArchivo', 'RULE_A')->set('codigoVersion', 'ORION-UI-1')
            ->set('motivoCambio', 'Retiro explícito desde el gestor de reglas.')->call('crearSucesora')
            ->assertHasNoErrors();
        $version = $panel->get('versionSeleccionada');
        $rule = ReglaExperta::query()->where('cod_version_modelo', $version)->where('cod_regla_anterior', 'RULE_B')->sole();
        $panel->call('prepararRevision', $rule->getKey())->set('decision', 'APROBADA')
            ->set('justificacion', 'Definición y soportes revisados.')->call('guardarRevision')->assertHasNoErrors();
        self::assertSame($this->medico->getKey(), $rule->fresh()->cod_usuario_revision);
        self::assertSame('INACTIVO', ReglaExperta::query()->where('cod_version_modelo', $version)->where('cod_regla_anterior', 'RULE_A')->sole()->estado);
    }

    public function test_interfaz_muestra_definicion_nueva_y_conserva_sustitucion(): void
    {
        Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', 'VER_TEST')
            ->call('prepararArchivo', 'RULE_A')->set('agregarRegla', true)->assertSee('Definición de regla')
            ->set('nueva', ['codigo' => 'MEM-REV-3', 'nombre' => 'Regla sustituta', 'descripcion' => 'Conjunción explícita.',
                'fundamento' => 'Definición de ingeniería para prueba.', 'condiciones' => [['variable' => 'VAR_INS', 'valor' => 'VAL_A']],
                'criterio_dominio' => 'CR_DOM', 'valor_consecuencia' => 'RES_D'])
            ->set('codigoVersion', 'ORION-SUSTITUCION')->set('motivoCambio', 'Sustitución conservando la definición original.')
            ->call('crearSucesora')->assertHasNoErrors();
        $new = ReglaExperta::query()->where('codigo_regla', 'MEM-REV-3')->sole();
        self::assertSame('RULE_A', $new->cod_regla_anterior);
        self::assertSame('ACTIVO', $new->estado);
        self::assertSame(1, $new->condicionesReglaExperta()->count());
        self::assertSame('ACTIVO', ReglaExperta::query()->findOrFail('RULE_A')->estado);
    }

    public function test_superadministracion_consulta_sin_formularios_de_escritura(): void
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($u);
        Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', 'VER_TEST')
            ->assertSee('Reglas de la versión')->assertDontSee('Crear versión sucesora')->assertDontSee('Revisar regla')
            ->call('prepararArchivo', 'RULE_A')->assertForbidden();
        self::assertSame(1, DB::table('versiones_modelo_experto')->count());
    }

    public function test_la_ruta_exige_sesion_y_competencia_profesional(): void
    {
        $this->get(route('admin.sistema-experto.reglas'))->assertOk();
        $this->medico->personal()->update(['profesion' => 'ENFERMERIA']);
        $this->get(route('admin.sistema-experto.reglas'))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->get(route('admin.sistema-experto.reglas'))->assertRedirect(route('login'));
    }

    public function test_interfaz_rechaza_regla_ajena_a_la_version_seleccionada(): void
    {
        $id = $this->revision();
        $panel = Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', $id);
        $panel->call('prepararRevision', 'RULE_A')->assertNotFound();
        self::assertNull(ReglaExperta::query()->findOrFail('RULE_A')->estado_revision);
    }

    public function test_cuenta_desactivada_no_muta_desde_una_interfaz_ya_abierta(): void
    {
        $id = $this->revision();
        $panel = Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', $id);
        DB::table('usuarios')->where('cod_usuario', $this->medico->getKey())->update(['estado' => 'INACTIVO']);
        $panel->call('crearSucesora')->assertForbidden();
        self::assertFalse(DB::table('versiones_modelo_experto')->where('codigo_version', 'ORION-DENEGADA')->exists());
    }

    public function test_una_sucesora_no_rehabilita_la_definicion_rechazada(): void
    {
        app(Gestor::class)->revisar($this->medico, 'RULE_A', 'RECHAZADA', 'Esta definición requiere una regla distinta.');
        $id = $this->revision();
        $copy = ReglaExperta::query()->where('cod_version_modelo', $id)->where('cod_regla_anterior', 'RULE_A')->sole();
        self::assertSame('INACTIVO', $copy->estado);
        self::assertSame('RECHAZADA', ReglaExperta::query()->findOrFail('RULE_A')->estado_revision);
    }

    public function test_consecuencia_reservada_se_rechaza_sin_publicar_ni_retirar_la_anterior(): void
    {
        $definition = ['codigo' => 'MEM-REV-3', 'nombre' => 'Regla inválida', 'descripcion' => 'Consecuencia reservada.',
            'fundamento' => 'Definición negativa para prueba.', 'condiciones' => [['variable' => 'VAR_INS', 'valor' => 'VAL_A']],
            'criterio_dominio' => 'CR_DOM', 'valor_consecuencia' => 'RES_M'];
        try {
            app(Gestor::class)->crearRevision($this->medico, 'VER_TEST', 'ORION-INVALIDA', 'No publicar consecuencias del resolvedor.', $definition);
            self::fail('Se publicó una consecuencia reservada.');
        } catch (DomainException) {
            self::assertSame(1, DB::table('versiones_modelo_experto')->count());
            self::assertNull(DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->value('fecha_hora_retiro'));
        }
    }

    public function test_sql_conserva_la_firma_y_el_enlace_historico(): void
    {
        $id = $this->revision();
        $rule = ReglaExperta::query()->where('cod_version_modelo', $id)->where('cod_regla_anterior', 'RULE_A')->sole();
        app(Gestor::class)->revisar($this->medico, $rule->getKey(), 'APROBADA', 'Primera decisión conservada.');
        $original = $rule->fresh()->getAttributes();
        foreach ([['estado_revision' => 'RECHAZADA'], ['justificacion_revision' => 'Motivo reemplazado'],
            ['cod_usuario_revision' => null, 'fecha_hora_revision' => null, 'justificacion_revision' => null, 'estado_revision' => 'PENDIENTE'],
            ['cod_regla_anterior' => 'RULE_B']] as $bad) {
            try {
                DB::transaction(fn () => DB::table('reglas_expertas')->where('cod_regla_experta', $rule->getKey())->update($bad));
                self::fail('Se sobrescribió historia mediante SQL.');
            } catch (QueryException) {
                self::assertSame($original, $rule->fresh()->getAttributes());
            }
        }
    }

    public function test_codigo_de_version_demasiado_largo_falla_antes_de_insertar(): void
    {
        $this->expectException(DomainException::class);
        app(Gestor::class)->crearRevision($this->medico, 'VER_TEST', 'ORION-'.str_repeat('A', 25), 'Código fuera de la longitud física aprobada.');
    }

    public function test_rol_ajeno_personal_inactivo_o_ausente_y_preview_no_crean_conocimiento(): void
    {
        $actors = [];
        foreach (['ENFERMEROS' => 'ENFERMERIA', 'GERENTE' => 'MEDICINA', 'FAMILIAR' => 'PSICOLOGIA'] as $role => $profession) {
            $u = $this->profesional($role, $profession);
            $u->givePermissionTo(['reglas_expertas.ver', 'reglas_expertas.gestionar', 'reglas_expertas.revisar']);
            $actors[] = $u;
        }
        $sinPersonal = User::factory()->create(['estado' => 'ACTIVO']);
        $sinPersonal->assignRole('MEDICO GENERAL/GERIATRA');
        $actors[] = $sinPersonal;
        $inactivo = $this->profesional('PSICOLOGO/A', 'PSICOLOGIA');
        $inactivo->personal()->update(['estado' => 'INACTIVO']);
        $actors[] = $inactivo;
        foreach ($actors as $u) {
            try {
                app(Gestor::class)->crearRevision($u, 'VER_TEST', 'ORION-DENEGADA', 'No conceder gestión por permiso sin competencia.');
                self::fail('Se autorizó una cuenta incompatible.');
            } catch (AuthorizationException) {
                self::assertSame(1, DB::table('versiones_modelo_experto')->count());
                self::assertSame(0, DB::table('activity_log')->where('log_name', 'conocimiento_experto')->count());
            }
        }
        session()->put('preview_role', 'ENFERMEROS');
        try {
            $this->revision();
            self::fail('Se autorizó una mutación durante la previsualización.');
        } catch (AuthorizationException) {
            self::assertSame(1, DB::table('versiones_modelo_experto')->count());
            self::assertSame(2, DB::table('reglas_expertas')->count());
        } finally {
            session()->forget('preview_role');
        }
    }

    public function test_propiedades_locked_rechazan_atribuir_una_regla_desde_cliente(): void
    {
        foreach (['reglaArchivo', 'reglaRevision'] as $campo) {
            try {
                Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', 'VER_TEST')->set($campo, 'RULE_A');
                self::fail('Se manipuló una selección protegida.');
            } catch (CannotUpdateLockedPropertyException) {
                self::assertSame(1, DB::table('versiones_modelo_experto')->count());
                self::assertNull(ReglaExperta::query()->findOrFail('RULE_A')->estado_revision);
            }
        }
    }

    public function test_payload_no_puede_imponer_autor_ni_revisor(): void
    {
        foreach (['cod_usuario_creacion', 'cod_usuario_revision'] as $campo) {
            try {
                app(Gestor::class)->crearRevision($this->medico, 'VER_TEST', 'ORION-ATRIBUCION', 'No atribuir conocimiento desde una entrada manipulada.', [$campo => 'USUARIO-AJENO']);
                self::fail('Se aceptó un actor enviado por el cliente.');
            } catch (DomainException) {
                self::assertSame(1, DB::table('versiones_modelo_experto')->count());
                self::assertSame(0, DB::table('activity_log')->where('log_name', 'conocimiento_experto')->count());
            }
        }
    }

    public function test_migracion_revierte_sin_historia_y_reinstala_sin_perder_definiciones(): void
    {
        $migration = require database_path('migrations/2026_10_18_002000_add_revision_y_sucesion_reglas_expertas.php');
        $migration->down();
        self::assertFalse(Schema::hasColumn('reglas_expertas', 'estado_revision'));
        self::assertSame(2, DB::table('reglas_expertas')->count());
        $migration->up();
        self::assertTrue(Schema::hasColumn('reglas_expertas', 'estado_revision'));
        self::assertNull(ReglaExperta::query()->findOrFail('RULE_A')->estado_revision);
    }

    public function test_prueba_desde_interfaz_usa_condiciones_guardadas_sin_crear_resultados_ni_aprobar(): void
    {
        $antes = [DB::table('evaluaciones_expertas')->count(), DB::table('reglas_expertas')->count(), DB::table('activity_log')->count()];
        $panel = Livewire::test(GobiernoReglasPanel::class)->set('versionSeleccionada', 'VER_TEST')
            ->call('prepararPrueba', 'RULE_A')->set('entradaPrueba.VAR_INS', 'VAL_A')->call('probarCondiciones')->assertHasNoErrors();
        self::assertTrue($panel->get('resultadoPrueba')['cumple']);
        $panel->set('entradaPrueba.VAR_INS', 'VAL_B')->call('probarCondiciones')->assertHasNoErrors();
        self::assertFalse($panel->get('resultadoPrueba')['cumple']);
        $panel->set('entradaPrueba.VAR_INS', '')->call('probarCondiciones')->assertHasNoErrors();
        self::assertFalse($panel->get('resultadoPrueba')['cumple']);
        self::assertSame($antes, [DB::table('evaluaciones_expertas')->count(), DB::table('reglas_expertas')->count(), DB::table('activity_log')->count()]);
        self::assertNull(ReglaExperta::query()->findOrFail('RULE_A')->estado_revision);
        $panel->set('entradaPrueba.VAR_INS', 'RES_D')->call('probarCondiciones')->assertHasErrors('gobierno');
    }
}
