<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ControlCognitivo;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\RevisionEvaluacionExperta;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\EjecutarObservaciones;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RevisarValoracionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarRevisionesValoracionExperta;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision as D;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class RevisionObservacionalExpertaTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;

    private User $psicologo;

    private Residente $residente;

    private ControlCognitivo $control;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        foreach (D::cases() as $disciplina) {
            $u = User::factory()->create(['estado' => 'ACTIVO']);
            $u->assignRole($disciplina->rol());
            Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional sintético', 'apellido_paterno' => $disciplina->value,
                'numero_documento' => 'OBS-REV-'.$disciplina->value, 'profesion' => $disciplina->value, 'estado' => 'ACTIVO']);
            $disciplina === D::MEDICINA ? $this->medico = $u : $this->psicologo = $u;
        }
        $this->actingAs($this->medico);
        $this->residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->control = ControlCognitivo::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->medico->personal->getKey(),
            'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE', 'repite_preguntas' => true, 'olvida_indicaciones' => false, 'cambio_cognitivo' => null]);
    }

    private function ejecutar(): string
    {
        return app(EjecutarObservaciones::class)->ejecutar($this->medico, $this->residente, (string) Str::uuid());
    }

    public function test_revisiones_separadas_sobre_instantanea_conservada_sin_preaprobacion_ni_modificar_resultado(): void
    {
        $id = $this->ejecutar();
        $resultado = DB::table('resultados_criterio')->get()->toJson();
        $this->control->update(['repite_preguntas' => false]);
        self::assertSame([], config('sistema_experto.lectura_clinica'));
        $accion = app(RevisarValoracionExperta::class);
        $p = $accion->ejecutar($this->psicologo, $id, D::PSICOLOGIA, 'CONFIRMADA', 'Hallazgo literal revisado.');
        $consulta = app(ConsultarRevisionesValoracionExperta::class)->consultar($this->medico, $id);
        self::assertSame('CONFIRMADA', $consulta['PSICOLOGIA']['estado']);
        self::assertSame('PENDIENTE', $consulta['MEDICINA']['estado']);
        self::assertSame($this->psicologo->personal->getKey(), $p->cod_personal);
        $m = $accion->ejecutar($this->medico, $id, D::MEDICINA, 'RECHAZADA', 'Es necesario ampliar la observación.');
        self::assertSame($m->getKey(), $accion->ejecutar($this->medico, $id, D::MEDICINA, 'RECHAZADA', 'Es necesario ampliar la observación.')->getKey());
        $nueva = $accion->ejecutar($this->medico, $id, D::MEDICINA, 'CONFIRMADA', 'Rectificación documentada.', $m->getKey());
        self::assertSame($m->getKey(), $nueva->cod_revision_anterior);
        self::assertSame('RECHAZADA', $m->fresh()->decision);
        self::assertSame($resultado, DB::table('resultados_criterio')->get()->toJson());
        self::assertDatabaseCount('revisiones_evaluacion_experta', 3);
        self::assertDatabaseCount('clasificaciones_dcl_instantanea', 0);
    }

    public function test_traza_alterada_impide_consultar_y_firmar_sin_escrituras(): void
    {
        $id = $this->ejecutar();
        DB::table('evaluaciones_reglas')->update(['estado_regla' => 'NO_CUMPLE']);
        foreach (['consulta', 'firma'] as $operacion) {
            try {
                $operacion === 'consulta' ? app(ConsultarRevisionesValoracionExperta::class)->consultar($this->medico, $id)
                    : app(RevisarValoracionExperta::class)->ejecutar($this->medico, $id, D::MEDICINA, 'CONFIRMADA', 'No admitir traza corrupta.');
                self::fail('Debe rechazar la instantánea alterada.');
            } catch (DomainException) {
                self::assertDatabaseCount('revisiones_evaluacion_experta', 0);
            }
        }
    }

    public function test_medico_no_firma_por_psicologia_aunque_se_le_conceda_el_permiso(): void
    {
        $id = $this->ejecutar();
        $this->medico->givePermissionTo(D::PSICOLOGIA->permiso());
        $this->expectException(AuthorizationException::class);
        try {
            app(RevisarValoracionExperta::class)->ejecutar($this->medico, $id, D::PSICOLOGIA, 'CONFIRMADA', 'Disciplina ajena.');
        } finally {
            self::assertDatabaseCount('revisiones_evaluacion_experta', 0);
        }
    }

    public function test_interfaz_firma_hallazgos_y_no_descarta_revision_abierta_al_cambiar_de_historia(): void
    {
        $id = $this->ejecutar();
        $otra = $this->ejecutar();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('seleccionarObservacion', $id)->assertSee('Hallazgos observacionales · Confirmación profesional')
            ->call('prepararRevisionObservacional', 'MEDICINA')->set('decisionRevision', 'CONFIRMADA')
            ->set('justificacionRevision', 'Revisión de los soportes originales conservados.')
            ->call('seleccionarObservacion', $otra)->assertHasErrors('revision')->assertSet('evaluacionObservacional', $id)
            ->call('guardarRevision')->assertHasNoErrors()->assertSee('Revisión de Medicina registrada.');
        self::assertSame($id, RevisionEvaluacionExperta::sole()->cod_evaluacion_experta);
        self::assertSame($this->medico->getKey(), RevisionEvaluacionExperta::sole()->cod_usuario);
    }

    public function test_no_hay_firma_de_hallazgos_sin_guardar_ni_de_otro_residente(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('prepararRevisionObservacional', 'MEDICINA')->assertStatus(422);
        $id = $this->ejecutar();
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $otro])->call('seleccionarObservacion', $id)->assertNotFound();
        self::assertDatabaseCount('revisiones_evaluacion_experta', 0);
    }

    public function test_otra_ejecucion_no_cambia_la_evaluacion_visible_mientras_se_firma(): void
    {
        $original = $this->ejecutar();
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('prepararRevisionObservacional', 'MEDICINA')->assertSet('evaluacionObservacional', $original);
        $nueva = $this->ejecutar();
        self::assertNotSame($original, $nueva);
        $componente->set('justificacionRevision', 'Revisión de la evaluación que se abrió originalmente.')
            ->assertViewHas('hallazgosObservacionales', fn ($r) => $r['id'] === $original)
            ->set('decisionRevision', 'CONFIRMADA')->call('guardarRevision')->assertHasNoErrors();
        self::assertSame($original, RevisionEvaluacionExperta::sole()->cod_evaluacion_experta);
    }
}
