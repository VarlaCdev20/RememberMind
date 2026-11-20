<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AplicacionInstrumento;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\ControlCognitivo;
use App\Models\Diagnostico;
use App\Models\EvaluacionExperta;
use App\Models\Instrumento;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RegistrarConocimientoPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarResultadoInstitucional;
use Carbon\CarbonImmutable;
use Database\Seeders\EscenariosDclResidentesSeeder as S;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\EsquemaSinTransaccion;
use Tests\TestCase;

final class EscenariosDclResidentesSeederTest extends TestCase
{
    use EsquemaSinTransaccion;

    private User $actor;

    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(CarbonImmutable::parse('2026-11-19 18:00:00', 'America/La_Paz'));
        (require base_path('database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('PSICOLOGO/A');
        Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Sintético',
            'numero_documento' => 'DCL10-PSI', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO']);
        $this->actingAs($this->actor);
        request()->setMethod('POST');
        $this->area = Area::create(['nombre' => 'Psicología integral', 'estado' => 'ACTIVA']);
        foreach (range(1, 30) as $n) {
            Residente::factory()->create(['numero_documento' => sprintf('991200%02d', $n), 'fecha_nacimiento' => '1945-01-01',
                'observacion' => 'CASO SINTÉTICO DE PRUEBA · HISTORIAL ANUAL', 'estado' => 'ADMITIDO']);
        }
        app(RegistrarConocimientoInstitucional::class)->ejecutar($this->actor);
        app(RegistrarConocimientoPetersen::class)->ejecutar($this->actor);
    }

    private function cargar(): array
    {
        return app(S::class)->ejecutar(DB::connection()->getDatabaseName(), $this->actor->getKey(), $this->area->getKey());
    }

    public function test_diez_casos_recorren_inferencia_y_reintento_conserva_fuentes_historia_y_tablas(): void
    {
        $tablas = Schema::getTableListing();
        $residente = Residente::where('numero_documento', '99120002')->sole();
        $anterior = ControlCognitivo::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $this->actor->personal->getKey(),
            'fecha_hora' => '2026-10-01 12:00:00', 'observacion' => 'Registro previo que debe permanecer intacto.', 'estado' => 'VIGENTE']);
        $contenidoAnterior = $anterior->fresh()->getAttributes();
        $r = $this->cargar();
        self::assertCount(10, $r['casos']);
        self::assertSame(['PET-01' => 3, 'PET-02' => 3, 'PET-03' => 2, 'PET-04' => 2], collect($r['casos'])->countBy('patron')->sortKeys()->all());
        foreach ($r['casos'] as $caso) {
            self::assertTrue($caso['nuevo']);
            self::assertGreaterThan(0, $caso['hechos']);
            self::assertSame(6, $caso['criterios']);
            $e = EvaluacionExperta::findOrFail($caso['evaluacion']);
            self::assertSame($caso['patron'], $e->institucional_patron);
            self::assertNotNull($e->huella_memoria_cognicion);
        }
        self::assertSame(30, Residente::count());
        self::assertSame(0, Diagnostico::count());
        self::assertSame(50, AplicacionInstrumento::count());
        self::assertSame(90, ValoracionPetersenOriginal::count());
        self::assertEqualsCanonicalizing($tablas, Schema::getTableListing());
        self::assertSame($contenidoAnterior, $anterior->fresh()->getAttributes());
        self::assertSame([], config('sistema_experto.perfiles_instrumentales'));
        $conteos = collect($tablas)->mapWithKeys(fn ($tabla) => [$tabla => DB::table($tabla)->count()])->all();
        $otra = $this->cargar();
        self::assertTrue(collect($otra['casos'])->every(fn ($c) => $c['nuevo'] === false));
        foreach ($conteos as $tabla => $cantidad) {
            self::assertSame($cantidad, DB::table($tabla)->count(), $tabla);
        }
        self::assertSame(array_column($r['casos'], 'evaluacion'), array_column($otra['casos'], 'evaluacion'));

        // La lectura histórica funciona con la configuración normal, sin el perfil temporal del seeder.
        $enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermera->assignRole('ENFERMEROS');
        $personal = Personal::create(['cod_usuario' => $enfermera->getKey(), 'nombres' => 'Natalia', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'DCL10-ENF', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'TARDE', 'hora_inicio' => '15:00:00', 'hora_cierre' => '23:00:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-11-19', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_area' => $this->area->getKey(), 'estado' => 'ACTIVA']);
        AsignacionResidenteJornada::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_residente' => $residente->getKey(), 'estado' => 'ACTIVA']);
        $historico = app(ConsultarResultadoInstitucional::class)->consultar($enfermera, $residente, $r['casos'][0]['evaluacion']);
        self::assertSame('PET-01', $historico['contenido']['patron']);
        self::assertTrue($historico['persistido']);
        $this->actingAs($enfermera)->get(route('admin.enfermeria.pacientes.resultados-experto', $residente))
            ->assertOk()->assertSee('Posible DCL amnésico de dominio único')
            ->assertSee('Panorama cognitivo')->assertSee('Estado de cada área')
            ->assertSee('Alteración sustentada en Memoria.')
            ->assertSee('Fuentes del resultado')->assertSee('Datos utilizados en la evaluación');
        self::assertSame(10, EvaluacionExperta::count());
    }

    public function test_destino_incorrecto_y_residente_no_sintetico_no_crean_fuentes(): void
    {
        try {
            app(S::class)->ejecutar('base_ajena', $this->actor->getKey(), $this->area->getKey());
            self::fail('No debe escribir en otro destino.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('Destino no autorizado', $e->getMessage());
        }
        Residente::where('numero_documento', '99120010')->sole()->update(['observacion' => null]);
        try {
            $this->cargar();
            self::fail('No debe añadir datos ficticios a un expediente sin marca sintética.');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('residentes ficticios', $e->getMessage());
        }
        self::assertSame(0, AplicacionInstrumento::count());
        self::assertSame(0, EvaluacionExperta::count());
        self::assertSame(0, Instrumento::where('codigo', 'like', 'DCL10-%')->count());
    }

    public function test_enfermeria_no_ejecuta_y_fallo_de_fuente_revierte_el_caso_sin_fingir_resultado(): void
    {
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('ENFERMEROS');
        try {
            app(S::class)->ejecutar(DB::connection()->getDatabaseName(), $u->getKey(), $this->area->getKey());
            self::fail('Enfermería no adquiere ejecución del experto por el seeder.');
        } catch (HttpException $e) {
            self::assertSame(403, $e->getStatusCode());
        }
        self::assertSame(0, Instrumento::where('codigo', 'like', 'DCL10-%')->count());
        $fallo = false;
        DB::connection()->beforeExecuting(function (string $sql) use (&$fallo): void {
            if (! $fallo && str_starts_with($sql, 'insert into "valoraciones_petersen_originales"')) {
                $fallo = true;
                throw new \RuntimeException('Fallo controlado de persistencia.');
            }
        });
        try {
            $this->cargar();
            self::fail('Debía conservar el fallo real.');
        } catch (\RuntimeException $e) {
            self::assertSame('Fallo controlado de persistencia.', $e->getMessage());
        }
        self::assertSame(0, AplicacionInstrumento::count());
        self::assertSame(0, Atencion::count());
        self::assertSame(0, ValoracionPetersenOriginal::count());
        self::assertSame(0, EvaluacionExperta::count());
        self::assertSame([], config('sistema_experto.perfiles_instrumentales'));
    }
}
