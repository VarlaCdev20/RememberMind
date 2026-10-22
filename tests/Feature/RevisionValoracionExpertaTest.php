<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\RevisionEvaluacionExperta;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RevisarValoracionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarRevisionesValoracionExperta;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision as D;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Support\InformeCognitivoFixture as F;
use Tests\TestCase;

/** Verifica firma y persistencia reales con datos efímeros; no acredita metodología clínica. */
final class RevisionValoracionExpertaTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Support\CorromperConservacionParaPrueba;

    private User $medico;

    private User $psicologo;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = $this->profesional(D::MEDICINA);
        $this->psicologo = $this->profesional(D::PSICOLOGIA);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->actingAs($this->medico);
        F::conocimiento($this->medico);
        F::evaluacion($this->residente, $this->medico->personal, 'REV');
        // Solo este test define un contrato de revisión; la aplicación conserva lectura_clinica vacía.
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->update(['codigo_version' => 'VAL-REVISION-01', 'estado' => 'ACTIVO']);
        DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', 'E_REV')->update(['origen_activacion' => 'MANUAL', 'estado_ejecucion' => 'FINALIZADA']);
        DB::table('evidencias_criterio_evaluacion')->update(['estado_participacion' => 'UTILIZADA']);
        $contrato = config('sistema_experto.lectura_clinica.VER_TEST');
        $contrato['estados_ejecucion'] = $contrato['estados_inferencia'] = ['FINALIZADA'];
        $contrato['estados_participacion_soporte'] = ['UTILIZADA'];
        $contrato['revision_profesional'] = true;
        config(['sistema_experto.lectura_clinica.VER_TEST' => $contrato]);
    }

    private function profesional(D $d): User
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole($d->rol());
        Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => $d->value,
            'numero_documento' => 'REV-'.$d->value, 'profesion' => $d->value, 'estado' => 'ACTIVO']);

        return $u;
    }

    public function test_dos_firmas_separadas_reintento_identico_y_rectificacion_sin_sobrescritura(): void
    {
        $motor = \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->get()->toJson();
        $a = app(RevisarValoracionExperta::class);
        $p = $a->ejecutar($this->psicologo, 'E_REV', D::PSICOLOGIA, 'CONFIRMADA', 'Conforme con la valoración de este caso.');
        self::assertSame($this->psicologo->personal->getKey(), $p->cod_personal);
        self::assertSame(1, RevisionEvaluacionExperta::count());
        self::assertFalse(RevisionEvaluacionExperta::where('disciplina', 'MEDICINA')->exists());
        $m = $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'RECHAZADA', 'Requiere ampliar el soporte clínico.');
        $repetida = $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'RECHAZADA', 'Requiere ampliar el soporte clínico.');
        self::assertSame($m->getKey(), $repetida->getKey());
        $rectificada = $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Se rectifica la decisión tras revisar el soporte original.', $m->getKey());
        self::assertSame($m->getKey(), $rectificada->cod_revision_anterior);
        self::assertSame('RECHAZADA', $m->fresh()->decision);
        self::assertSame('CONFIRMADA', $p->fresh()->decision);
        self::assertSame(3, RevisionEvaluacionExperta::count());
        self::assertSame($motor, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('resultados_criterio')->get()->toJson());
        self::assertSame(3, DB::table('activity_log')->where('log_name', 'SistemaExperto')->count());
    }

    public function test_no_puede_firmar_por_la_otra_disciplina(): void
    {
        $this->medico->givePermissionTo(D::PSICOLOGIA->permiso());
        $this->expectException(AuthorizationException::class);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::PSICOLOGIA, 'CONFIRMADA', 'Intento ajeno.');
        } finally {
            self::assertSame(0, RevisionEvaluacionExperta::count());
        }
    }

    public function test_superadmin_con_todos_los_permisos_no_recibe_competencia_clinica(): void
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('SUPERADMINISTRADOR');
        $this->expectException(AuthorizationException::class);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($u, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Intento de auditoría.');
        } finally {
            self::assertSame(0, RevisionEvaluacionExperta::count());
        }
    }

    public function test_cuenta_inactiva_rechazada_aunque_se_reciba_instancia_anterior_activa(): void
    {
        DB::table('usuarios')->where('cod_usuario', $this->medico->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Intento obsoleto.');
        } finally {
            self::assertSame(0, RevisionEvaluacionExperta::count());
        }
    }

    public function test_contrato_no_habilitado_y_simulacion_no_se_pueden_firmar(): void
    {
        config(['sistema_experto.lectura_clinica.VER_TEST.revision_profesional' => false]);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Sin contrato.');
            self::fail();
        } catch (\DomainException $e) {
            self::assertStringContainsString('contrato operativo', $e->getMessage());
        }
        config(['sistema_experto.lectura_clinica.VER_TEST.revision_profesional' => true]);
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->update(['codigo_version' => 'SIM-ORION-LOCAL-1']);
        $this->expectException(\DomainException::class);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'No puede cambiar su origen.');
        } finally {
            self::assertSame(0, RevisionEvaluacionExperta::count());
        }
    }

    public function test_integridad_y_justificacion_obligatorias(): void
    {
        $this->corromperConservacion(fn () => DB::table('evaluacion_criterios')->where('cod_resultado_criterio', 'RC_REV')->update(['resultado_cod_valor_semantico' => 'RES_S']));
        $this->expectException(\DomainException::class);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Con soporte alterado.');
        } finally {
            self::assertSame(0, RevisionEvaluacionExperta::count());
        }
    }

    public function test_reintento_divergente_y_predecesora_ajena_no_crean_revisiones(): void
    {
        $a = app(RevisarValoracionExperta::class);
        $p = $a->ejecutar($this->psicologo, 'E_REV', D::PSICOLOGIA, 'CONFIRMADA', 'Primera decisión.');
        try {
            $a->ejecutar($this->psicologo, 'E_REV', D::PSICOLOGIA, 'RECHAZADA', 'Cambio con cabeza obsoleta.');
            self::fail();
        } catch (\DomainException $e) {
            self::assertStringContainsString('Recargue', $e->getMessage());
        }
        try {
            $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Predecesora de Psicología.', $p->getKey());
            self::fail();
        } catch (\DomainException $e) {
            self::assertStringContainsString('Recargue', $e->getMessage());
        }
        self::assertSame(1, RevisionEvaluacionExperta::count());
    }

    public function test_sql_no_permite_sobrescribir_ni_borrar_un_dictamen(): void
    {
        $r = app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Firma original.');
        foreach (['update', 'delete'] as $operacion) {
            try {
                DB::transaction(function () use ($r, $operacion): void {
                    $q = DB::table('revisiones_evaluacion_experta')->where('cod_revision_experta', $r->getKey());
                    $operacion === 'update' ? $q->update(['decision' => 'RECHAZADA']) : $q->delete();
                });
                self::fail('SQL debe preservar la firma.');
            } catch (QueryException) {
                self::assertSame('CONFIRMADA', $r->fresh()->decision);
            }
        }
    }

    public function test_interfaz_guarda_autoria_real_y_deja_la_otra_disciplina_pendiente(): void
    {
        $this->actingAs($this->psicologo);
        $this->get('/admin/psicologia/residente/'.$this->residente->getKey().'/resultados-experto')->assertOk();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Confirmación profesional')->assertSee('Pendiente de revisión profesional')
            ->call('prepararRevision', 'PSICOLOGIA')
            ->set('decisionRevision', 'CONFIRMADA')->set('justificacionRevision', 'Revisión profesional del soporte original.')
            ->call('guardarRevision')->assertHasNoErrors()->assertSee('Revisión de Psicología registrada.')
            ->assertSee('Valoración confirmada')->assertSee('Pendiente de revisión profesional');
        $r = RevisionEvaluacionExperta::sole();
        self::assertSame('PSICOLOGIA', $r->disciplina);
        self::assertSame($this->psicologo->getKey(), $r->cod_usuario);
        self::assertSame($this->psicologo->personal->getKey(), $r->cod_personal);
    }

    public function test_interaccion_reautoriza_tras_revocar_permiso_sin_escrituras(): void
    {
        $this->actingAs($this->medico);
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('prepararRevision', 'MEDICINA')->set('decisionRevision', 'CONFIRMADA')->set('justificacionRevision', 'Revisión obsoleta.');
        Role::findByName('MEDICO GENERAL/GERIATRA')->revokePermissionTo(D::MEDICINA->permiso());
        $componente->call('guardarRevision')->assertNotFound();
        self::assertSame(0, RevisionEvaluacionExperta::count());
    }

    public function test_preview_no_firma_y_los_campos_vacios_no_generan_registros(): void
    {
        $a = app(RevisarValoracionExperta::class);
        try {
            $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', '  ');
            self::fail();
        } catch (\DomainException $e) {
            self::assertStringContainsString('justificación', $e->getMessage());
        }
        $this->withSession([RolePreviewService::SESSION_KEY => 'GERENTE']);
        $this->expectException(AuthorizationException::class);
        try {
            $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Vista previa.');
        } finally {
            self::assertSame(0, RevisionEvaluacionExperta::count());
        }
    }

    public function test_fk_compuesta_impide_corregir_otra_evaluacion_o_disciplina(): void
    {
        $a = app(RevisarValoracionExperta::class);
        $r = $a->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Primera revisión.');
        $this->expectException(QueryException::class);
        DB::transaction(function () use ($r): void {
            DB::table('revisiones_evaluacion_experta')->insert(['cod_revision_experta' => 'COD_RVE_999999',
                'cod_evaluacion_experta' => 'E_REV', 'disciplina' => 'PSICOLOGIA', 'cod_personal' => $this->psicologo->personal->getKey(),
                'cod_usuario' => $this->psicologo->getKey(), 'fecha_hora_revision' => now(), 'decision' => 'RECHAZADA',
                'justificacion' => 'No puede apuntar a Medicina.', 'cod_revision_anterior' => $r->getKey()]);
        });
    }

    public function test_un_fallo_de_auditoria_revierte_la_firma_y_su_codigo(): void
    {
        $secuencia = DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson();
        DB::listen(static function ($q): void {
            if (preg_match('/^insert into ["`]?activity_log/i', $q->sql)) {
                throw new \DomainException('Fallo de auditoría de prueba.');
            }
        });
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Rollback de prueba.');
            self::fail();
        } catch (\DomainException $e) {
            self::assertSame('Fallo de auditoría de prueba.', $e->getMessage());
        }
        self::assertSame(0, RevisionEvaluacionExperta::count());
        self::assertSame($secuencia, DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson());
    }

    public function test_una_cabeza_valida_no_oculta_un_ciclo_desconectado_insertado_por_sql(): void
    {
        $raiz = app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Raíz válida.');
        $base = ['cod_evaluacion_experta' => 'E_REV', 'disciplina' => 'MEDICINA', 'cod_personal' => $this->medico->personal->getKey(),
            'cod_usuario' => $this->medico->getKey(), 'fecha_hora_revision' => now(), 'decision' => 'CONFIRMADA', 'justificacion' => 'Ciclo de prueba.'];
        DB::table('revisiones_evaluacion_experta')->insert([
            ['cod_revision_experta' => 'COD_RVE_999998', 'cod_revision_anterior' => 'COD_RVE_999999'] + $base,
            ['cod_revision_experta' => 'COD_RVE_999999', 'cod_revision_anterior' => 'COD_RVE_999998'] + $base,
        ]);
        try {
            app(ConsultarRevisionesValoracionExperta::class)->consultar($this->medico, 'E_REV');
            self::fail();
        } catch (\DomainException $e) {
            self::assertStringContainsString('desconectados', $e->getMessage());
        }
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'RECHAZADA', 'No se firma una cadena incompleta.', $raiz->getKey());
            self::fail();
        } catch (\DomainException $e) {
            self::assertStringContainsString('desconectados', $e->getMessage());
        }
        self::assertSame(3, RevisionEvaluacionExperta::count());
    }

    public function test_consulta_directa_relee_la_cuenta_antes_de_exponer_el_dictamen(): void
    {
        app(RevisarValoracionExperta::class)->ejecutar($this->medico, 'E_REV', D::MEDICINA, 'CONFIRMADA', 'Justificación profesional conservada.');
        DB::table('usuarios')->where('cod_usuario', $this->medico->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        app(ConsultarRevisionesValoracionExperta::class)->consultar($this->medico, 'E_REV');
    }

    public function test_no_descarta_una_revision_abierta_al_seleccionar_otro_historial(): void
    {
        F::evaluacion($this->residente, $this->medico->personal, 'OTRA');
        $this->actingAs($this->medico);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('seleccionarEvaluacion', 'E_REV')->call('prepararRevision', 'MEDICINA')
            ->set('justificacionRevision', 'Justificación todavía sin guardar.')
            ->call('seleccionarEvaluacion', 'E_OTRA')->assertHasErrors('revision')
            ->assertSet('evaluacion', 'E_REV')->assertSet('justificacionRevision', 'Justificación todavía sin guardar.');
        self::assertSame(0, RevisionEvaluacionExperta::count());
    }

    public function test_ruta_medica_exige_lectura_instrumental_completa_y_respeta_revocacion(): void
    {
        $ruta = route('admin.medico.residente.resultados-experto', $this->residente);
        $this->get($ruta)->assertOk();

        Role::findByName('MEDICO GENERAL/GERIATRA', 'web')->revokePermissionTo('preguntas_instrumento.ver');
        $this->get($ruta)->assertForbidden();
        self::assertSame(0, RevisionEvaluacionExperta::count());
    }
}
