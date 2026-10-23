<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\EvaluacionExperta;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPerfilInstrumental;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Carbon\CarbonImmutable;
use Database\Seeders\CohorteCognitivaInvestigacionSeeder;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class EnfermeriaPerfilPetersenTest extends TestCase
{
    use EsquemaSinTransaccion;

    public function test_enfermeria_ve_el_mismo_perfil_calculado_solo_para_su_residente_asignado(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-19 12:00:00'));
        $carga = new CohorteCognitivaInvestigacionSeeder;
        $residentes = $carga->cargar();
        $cuenta = User::factory()->create(['estado' => 'ACTIVO', 'correo' => 'enfermeria.prueba@example.invalid']);
        $cuenta->assignRole('ENFERMEROS');
        $personal = Personal::create(['cod_usuario' => $cuenta->getKey(), 'nombres' => 'Enfermera',
            'apellido_paterno' => 'De prueba', 'numero_documento' => 'ENF-PET-TEST',
            'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área de prueba de Enfermería', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno de prueba', 'hora_inicio' => '00:00', 'hora_cierre' => '23:59',
            'orden' => 9, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(),
            'cod_area' => $area->getKey(), 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        AsignacionResidenteJornada::create(['cod_residente' => $residentes['S10']->getKey(),
            'cod_personal' => $personal->getKey(), 'cod_jornada' => $jornada->getKey(),
            'nivel_supervision' => 'ALTO', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);

        $this->actingAs($cuenta);
        self::assertTrue(Gate::forUser($cuenta)->allows('consultarFuentesParaResultado', [EvaluacionExperta::class, $residentes['S10']]));
        self::assertTrue(Gate::forUser($cuenta)->allows('consultarOriginales', [ValoracionPetersenOriginal::class, $residentes['S10']]));
        self::assertFalse($cuenta->checkPermissionTo('valoraciones_petersen.ver', 'web'));
        self::assertFalse($cuenta->checkPermissionTo('aplicaciones_instrumento.ver', 'web'));
        self::assertFalse(Gate::forUser($cuenta)->allows('consultarFuentesParaResultado', [EvaluacionExperta::class, $residentes['S11']]));
        self::assertFalse(Gate::forUser($cuenta)->allows('crearOriginal', [ValoracionPetersenOriginal::class, $residentes['S10']]));
        self::assertFalse($cuenta->checkPermissionTo('evaluaciones_expertas.revisar_psicologia', 'web'));
        self::assertFalse($cuenta->checkPermissionTo('evaluaciones_expertas.revisar_medicina', 'web'));

        $perfil = app(ConsultarPerfilInstrumental::class)->consultar($cuenta, $residentes['S10'], CarbonImmutable::now());
        self::assertSame('PET-04', $perfil['petersen_institucional']['patron']);
        self::assertFalse($perfil['petersen_institucional']['persistido']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residentes['S10']])
            ->assertOk()->assertSee('Perfil compatible con posible DCL no amnésico multidominio')
            ->assertSee('Caso de entrenamiento')->assertSee('Cinco áreas cognitivas')
            ->assertDontSee('PET-04')->call('abrirSignificado')->assertSet('significadoAbierto', true)
            ->call('analizarEvidencia')->assertForbidden();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $residentes['S11']])->assertNotFound();
    }
}
