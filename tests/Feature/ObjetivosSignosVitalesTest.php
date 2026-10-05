<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Acciones\DefinirObjetivoSignoVitalAction;
use App\Backend\Modulos\Clinica\SignosVitales\EvaluadorSignosVitales;
use App\Models\ObjetivoSignoVital;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ObjetivosSignosVitalesTest extends TestCase
{
    use RefreshDatabase;

    private User $medico;
    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->medico = User::factory()->create(['estado' => 'ACTIVO']);
        $this->medico->assignRole('MEDICO GENERAL/GERIATRA');
        Personal::create([
            'cod_personal' => 'PER_OBJ_MED', 'cod_usuario' => $this->medico->cod_usuario,
            'nombres' => 'Médico', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'MED-OBJ-01', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO',
        ]);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->medico);
    }

    private function entrada(array $cambios = []): array
    {
        return array_replace([
            'parametro' => 'saturacion_oxigeno',
            'min_objetivo' => 88, 'max_objetivo' => 92,
            'min_critico' => 85, 'max_critico' => null,
            'motivo' => 'Objetivo individual indicado tras valoración médica.',
        ], $cambios);
    }

    public function test_medico_define_objetivo_versionado_y_motor_lo_prioriza(): void
    {
        $primero = app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
            $this->residente->cod_residente, $this->entrada(), $this->medico);
        $this->assertSame($this->medico->personal->cod_personal, $primero->cod_personal);

        $evaluacion = app(EvaluadorSignosVitales::class)->evaluar(
            ['saturacion_oxigeno' => 89], $this->residente->cod_residente);
        $this->assertSame('OBJETIVO_PERSONALIZADO', $evaluacion->severidadGlobal()?->value);
        $this->assertSame('OBJETIVO_MEDICO', $evaluacion->resultados[0]->fuenteEvaluacion);

        $this->travel(1)->seconds();
        $segundo = app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
            $this->residente->cod_residente, $this->entrada(['min_objetivo' => 90, 'min_critico' => 87]), $this->medico);
        $this->assertNotSame($primero->cod_objetivo_signo, $segundo->cod_objetivo_signo);
        $this->assertSame('REEMPLAZADO', $primero->fresh()->estado);
        $this->assertNotNull($primero->fresh()->vigente_hasta);
        $this->assertSame(2, ObjetivoSignoVital::query()->count());

        $evaluacion = app(EvaluadorSignosVitales::class)->evaluar(
            ['saturacion_oxigeno' => 86], $this->residente->cod_residente);
        $this->assertSame('CRITICO', $evaluacion->severidadGlobal()?->value);
    }

    public function test_base_rechaza_dos_objetivos_vigentes_para_misma_variable(): void
    {
        $primero = app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
            $this->residente->cod_residente, $this->entrada(), $this->medico);

        try {
            ObjetivoSignoVital::create([
                'cod_residente' => $this->residente->cod_residente,
                'cod_personal' => $this->medico->personal->cod_personal,
                'parametro' => 'saturacion_oxigeno', 'min_objetivo' => 90,
                'max_objetivo' => 94, 'vigente_desde' => now()->addSecond(),
                'estado' => 'VIGENTE', 'motivo' => 'Intento de duplicado activo en prueba.',
            ]);
            $this->fail('La base debe rechazar dos objetivos vigentes.');
        } catch (QueryException) {
            $this->assertSame(1, ObjetivoSignoVital::query()->count());
            $this->assertSame('VIGENTE', $primero->fresh()->estado);
        }
    }

    public function test_objetivo_no_oculta_critico_general(): void
    {
        app(DefinirObjetivoSignoVitalAction::class)->ejecutar($this->residente->cod_residente,
            $this->entrada(['parametro' => 'frecuencia_cardiaca', 'min_objetivo' => 30,
                'max_objetivo' => 140, 'min_critico' => null]), $this->medico);

        $evaluacion = app(EvaluadorSignosVitales::class)->evaluar(
            ['frecuencia_cardiaca' => 131], $this->residente->cod_residente);
        $this->assertSame('CRITICO', $evaluacion->severidadGlobal()?->value);
    }

    public function test_enfermeria_y_superadmin_no_pueden_definir_objetivos(): void
    {
        foreach (['ENFERMEROS', 'SUPERADMINISTRADOR'] as $rol) {
            $usuario = User::factory()->create(['estado' => 'ACTIVO']);
            $usuario->assignRole($rol);
            if ($rol === 'ENFERMEROS') {
                $this->assertTrue($usuario->can('objetivos_signos_vitales.ver'));
                $this->assertFalse($usuario->can('objetivos_signos_vitales.gestionar'));
            }
            $this->actingAs($usuario);
            try {
                app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
                    $this->residente->cod_residente, $this->entrada(), $usuario);
                $this->fail('El rol '.$rol.' no debe escribir objetivos médicos.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }
        $this->assertSame(0, ObjetivoSignoVital::query()->count());
    }

    public function test_limites_incoherentes_y_saturacion_imposible_se_rechazan(): void
    {
        foreach ([$this->entrada(['min_objetivo' => 95, 'max_objetivo' => 90]),
            $this->entrada(['max_objetivo' => 105]),
            $this->entrada(['min_objetivo' => null, 'max_objetivo' => 88, 'min_critico' => 90]),
            $this->entrada(['min_objetivo' => 88, 'max_objetivo' => null, 'max_critico' => 85])] as $entrada) {
            try {
                app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
                    $this->residente->cod_residente, $entrada, $this->medico);
                $this->fail('La configuración inválida debía rechazarse.');
            } catch (ValidationException) {
                $this->assertSame(0, ObjetivoSignoVital::query()->count());
            }
        }
    }

    public function test_pantalla_medica_muestra_objetivos_y_guarda_con_permiso(): void
    {
        Livewire::test(\App\Frontend\Livewire\Medico\Clinica\ObjetivosSignosVitalesPanel::class,
            ['residente' => $this->residente->cod_residente])
            ->assertSee('Objetivos de signos vitales')
            ->set('minObjetivo', '88')->set('maxObjetivo', '92')
            ->set('minCritico', '85')
            ->set('motivo', 'Objetivo individual por indicación médica.')
            ->call('guardar')->assertHasNoErrors();
        $this->assertDatabaseHas('objetivos_signos_vitales', [
            'cod_residente' => $this->residente->cod_residente,
            'parametro' => 'saturacion_oxigeno', 'estado' => 'VIGENTE',
        ]);
    }

    public function test_medico_retira_objetivo_sin_borrar_historial_y_no_puede_retirarlo_dos_veces(): void
    {
        $objetivo = app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
            $this->residente->cod_residente, $this->entrada(), $this->medico);

        Livewire::test(\App\Frontend\Livewire\Medico\Clinica\ObjetivosSignosVitalesPanel::class,
            ['residente' => $this->residente->cod_residente])
            ->set('codObjetivoRetiro', $objetivo->cod_objetivo_signo)
            ->set('motivoRetiro', 'Retiro tras nueva valoración médica individual.')
            ->call('retirar')->assertHasNoErrors()
            ->set('mostrarHistorial', true)->assertSee('Retirado');

        $this->assertSame('ANULADO', $objetivo->fresh()->estado);
        $this->assertNotNull($objetivo->fresh()->vigente_hasta);
        $this->assertSame(1, ObjetivoSignoVital::query()->count());
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => ObjetivoSignoVital::class,
            'subject_id' => $objetivo->cod_objetivo_signo,
            'event' => 'retirado',
        ]);

        try {
            app(DefinirObjetivoSignoVitalAction::class)->retirar(
                $objetivo->cod_objetivo_signo, 'Intento de retiro duplicado del objetivo.', $this->medico);
            $this->fail('No debe retirarse dos veces.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
    }

    public function test_panel_no_puede_retirar_objetivo_de_otro_residente(): void
    {
        $otro = Residente::factory()->create();
        $objetivo = app(DefinirObjetivoSignoVitalAction::class)->ejecutar(
            $otro->cod_residente, $this->entrada(), $this->medico);

        Livewire::test(\App\Frontend\Livewire\Medico\Clinica\ObjetivosSignosVitalesPanel::class,
            ['residente' => $this->residente->cod_residente])
            ->set('codObjetivoRetiro', $objetivo->cod_objetivo_signo)
            ->set('motivoRetiro', 'Intento de retiro en el residente equivocado.')
            ->call('retirar')->assertNotFound();

        $this->assertSame('VIGENTE', $objetivo->fresh()->estado);
    }
}
