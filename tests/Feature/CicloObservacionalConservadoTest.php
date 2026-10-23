<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ControlCognitivo;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarRevisionesValoracionExperta;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Aceptación de un resultado conservado real del software, con fuentes sintéticas desechables. */
final class CicloObservacionalConservadoTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('ordenesIndependientes')]
    public function test_guardar_recuperar_y_revisar_por_ambas_disciplinas_sin_alterar_el_resultado(array $orden): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-22T12:00:00-04:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
        $actores = [];
        foreach (DisciplinaRevision::cases() as $disciplina) {
            $actor = User::factory()->create(['estado' => 'ACTIVO']);
            $actor->assignRole($disciplina->rol());
            Personal::create(['cod_usuario' => $actor->getKey(), 'nombres' => 'Profesional',
                'apellido_paterno' => 'Sintético', 'numero_documento' => 'CICLO-'.$disciplina->value,
                'profesion' => $disciplina->value, 'estado' => 'ACTIVO']);
            $actores[$disciplina->value] = $actor;
        }
        $medico = $actores['MEDICINA'];
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $control = ControlCognitivo::create(['cod_residente' => $residente->getKey(),
            'cod_personal' => $medico->personal->getKey(), 'fecha_hora' => now()->subHour(), 'estado' => 'VIGENTE',
            'repite_preguntas' => true, 'olvida_indicaciones' => false, 'cambio_cognitivo' => null]);

        $this->actingAs($medico);
        $pagina = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residente])
            ->call('ejecutarObservaciones')->assertHasNoErrors('observaciones');
        $id = $pagina->get('evaluacionObservacional');
        $original = app(ConsultarObservacionesConservadas::class)->consultar($medico, $residente, $id);
        self::assertTrue($original['persistido']);
        self::assertSame(['CUMPLE', 'NO_CUMPLE', 'NO_EVALUABLE'], array_column($original['resultados'], 'estado'));

        // Cambio sintético posterior: la consulta histórica debe seguir leyendo lo guardado.
        $control->update(['repite_preguntas' => false, 'olvida_indicaciones' => true]);
        $enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermera->assignRole('ENFERMEROS');
        $personalEnfermeria = Personal::create(['cod_usuario' => $enfermera->getKey(), 'nombres' => 'Enfermera',
            'apellido_paterno' => 'Sintética', 'numero_documento' => 'CICLO-ENF', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '07:00', 'hora_cierre' => '19:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionResidenteJornada::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $personalEnfermeria->getKey(),
            'cod_jornada' => $jornada->getKey(), 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
        $this->actingAs($enfermera);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residente])
            ->assertSee('Evaluación observacional guardada con 1 control.')
            ->assertSee('Se documentó un episodio de repetición de preguntas.')
            ->assertDontSee('Consulta actual de 1 de 1 control');
        $revisadas = [];
        foreach ($orden as $disciplina) {
            $actor = $actores[$disciplina];
            $this->actingAs($actor);
            $decision = $disciplina === 'MEDICINA' ? 'RECHAZADA' : 'CONFIRMADA';
            $justificacion = 'Revisión independiente del resultado sintético conservado: '.$disciplina;
            Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residente])
                ->call('seleccionarObservacion', $id)
                ->call('prepararRevisionObservacional', $disciplina)
                ->set('decisionRevision', $decision)->set('justificacionRevision', $justificacion)
                ->call('guardarRevision')->assertHasNoErrors()
                ->assertSee('registrada.');
            $revisadas[] = $disciplina;
            $revisiones = app(ConsultarRevisionesValoracionExperta::class)->consultar($actor, $id);
            foreach (DisciplinaRevision::cases() as $esperada) {
                $estado = in_array($esperada->value, $revisadas, true)
                    ? ($esperada === DisciplinaRevision::MEDICINA ? 'RECHAZADA' : 'CONFIRMADA') : 'PENDIENTE';
                self::assertSame($estado, $revisiones[$esperada->value]['estado']);
            }
            $this->assertDatabaseHas('revisiones_evaluacion_experta', ['cod_evaluacion_experta' => $id,
                'disciplina' => $disciplina, 'decision' => $decision, 'cod_usuario' => $actor->getKey(),
                'cod_personal' => $actor->personal->getKey(), 'justificacion' => $justificacion]);
            $historico = app(ConsultarObservacionesConservadas::class)->consultar($actor, $residente, $id);
            self::assertSame($original['informe_completo'], $historico['informe_completo']);
            self::assertSame(CodecInstantanea::huella($original['instantanea']), CodecInstantanea::huella($historico['instantanea']));
        }
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
        $this->assertDatabaseCount('revisiones_evaluacion_experta', 2);
        self::assertSame(0, \App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion\BloquesExpertos::consulta('clasificaciones_dcl_instantanea')->count());
    }

    public static function ordenesIndependientes(): array
    {
        return ['medicina primero' => [['MEDICINA', 'PSICOLOGIA']],
            'psicologia primero' => [['PSICOLOGIA', 'MEDICINA']]];
    }
}
