<?php

namespace Tests\Feature;

use App\Backend\Modulos\Clinica\Servicios\ValidacionSignosVitalesService;
use App\Models\Personal;
use App\Models\Preadmision;
use App\Models\Residente;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\TestCase;

class TallaCanonicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
        $this->seed(DatabaseSeeder::class);
    }

    public function test_validacion_talla_en_centimetros_y_rango_canonico(): void
    {
        // 1. Talla canónica válida (165 cm)
        $validatorValido = Validator::make(
            ['talla' => 165.0],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']],
            ValidacionSignosVitalesService::mensajes()
        );
        $this->assertFalse($validatorValido->fails());

        // 2. Valor ambiguo o en metros (1.65) debe ser RECHAZADO por ser < 50 cm
        $validatorMetros = Validator::make(
            ['talla' => 1.65],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']],
            ValidacionSignosVitalesService::mensajes()
        );
        $this->assertTrue($validatorMetros->fails());
        $this->assertStringContainsString('50', $validatorMetros->errors()->first('talla'));

        // 3. Límite inferior exacto (50.0 cm) debe pasar
        $validatorMin = Validator::make(
            ['talla' => 50.0],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']]
        );
        $this->assertFalse($validatorMin->fails());

        // 4. Límite superior exacto (240.0 cm) debe pasar
        $validatorMax = Validator::make(
            ['talla' => 240.0],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']]
        );
        $this->assertFalse($validatorMax->fails());

        // 5. Valor menor al límite inferior (49.9 cm) debe ser rechazado
        $validatorBajo = Validator::make(
            ['talla' => 49.9],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']]
        );
        $this->assertTrue($validatorBajo->fails());

        // 6. Valor superior al límite máximo (240.1 cm) debe ser rechazado
        $validatorAlto = Validator::make(
            ['talla' => 240.1],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']]
        );
        $this->assertTrue($validatorAlto->fails());

        // 7. Valor decimal con precisión de 0.1 cm (172.3 cm) debe ser válido y normalizado fielmente
        $validatorDecimal = Validator::make(
            ['talla' => 172.3],
            ['talla' => ValidacionSignosVitalesService::reglas()['talla']]
        );
        $this->assertFalse($validatorDecimal->fails());
        $this->assertSame(172.3, ValidacionSignosVitalesService::normalizarTalla(172.3));
    }

    public function test_vistas_declaran_step_0_1_y_rango_canonico_en_inputs_de_talla(): void
    {
        $f1 = resource_path('views/livewire/clinica/registro-signos-vitales-modal.blade.php');
        $c1 = file_get_contents($f1);
        $this->assertStringContainsString('step="0.1"', $c1);
        $this->assertStringContainsString('min="50"', $c1);
        $this->assertStringContainsString('max="240"', $c1);

        $f2 = resource_path('views/livewire/clinica/salud-signos-panel.blade.php');
        $c2 = file_get_contents($f2);
        $this->assertStringNotContainsString('step="0.5"', $c2);
        $this->assertStringContainsString('step="0.1"', $c2);

        $f3 = resource_path('views/livewire/valoraciones/valoracion-inicial-modal.blade.php');
        $c3 = file_get_contents($f3);
        $this->assertStringContainsString('step="0.1"', $c3);
        $this->assertStringContainsString('min="50"', $c3);
        $this->assertStringContainsString('max="240"', $c3);
    }

    public function test_calculo_imc_convierte_centimetros_a_metros_internamente(): void
    {
        // Peso: 70 kg, Talla: 175 cm => 70 / (1.75 * 1.75) = 22.857... => 22.9
        $imc = ValidacionSignosVitalesService::calcularImc(70.0, 175.0);
        $this->assertSame(22.9, $imc);

        // Si se pasa una talla en metros (< 50 cm) o fuera de rango, no calcula IMC
        $imcInvalido = ValidacionSignosVitalesService::calcularImc(70.0, 1.75);
        $this->assertNull($imcInvalido);
    }

    public function test_restriccion_en_valoraciones_enfermeria_preadmision_rechaza_talla_menor_a_50_cm(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $personal = $enfermero->personal;
        $this->assertNotNull($personal);

        $preadmision = Preadmision::create([
            'cod_preadmision' => 'PRE_' . strtoupper(Str::random(10)),
            'cod_usuario_registro' => $enfermero->cod_usuario,
            'nombres' => 'Residente',
            'apellido_paterno' => 'Prueba',
            'fecha_nacimiento' => '1950-01-01',
            'motivo_ingreso' => 'Prueba',
            'fecha_solicitud' => now(),
            'estado' => 'PENDIENTE',
        ]);

        // Intento con talla = 1.65 m (debe fallar por la restricción CHECK de motor / trigger)
        $this->expectException(QueryException::class);
        DB::table('valoraciones_enfermeria_preadmision')->insert([
            'cod_valoracion_enfermeria' => 'VAL_TEST_' . Str::random(5),
            'cod_preadmision' => $preadmision->cod_preadmision,
            'cod_personal_valorador' => $personal->cod_personal,
            'cod_usuario_registro' => $enfermero->cod_usuario,
            'fecha_hora' => now(),
            'talla' => 1.65,
        ]);
    }

    public function test_restriccion_en_mediciones_antropometricas_rechaza_talla_menor_a_50_cm(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $personal = $enfermero->personal;
        $this->assertNotNull($personal);

        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TEST_' . Str::random(5),
            'nombres' => 'Juan',
            'apellido_paterno' => 'Perez',
            'fecha_nacimiento' => '1945-01-01',
            'estado' => 'ACTIVO',
        ]);

        // Intento con talla = 1.70 (en metros) debe fallar por check ck_med_ant_talla
        $this->expectException(QueryException::class);
        DB::table('mediciones_antropometricas')->insert([
            'cod_medicion' => 'MED_TEST_' . Str::random(5),
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'talla' => 1.70,
        ]);
    }

    public function test_insercion_valida_en_centimetros_persiste_exitosamente(): void
    {
        $enfermero = User::where('correo', 'enfermeria@remembermind.com')->firstOrFail();
        $personal = $enfermero->personal;
        $this->assertNotNull($personal);

        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_TEST_' . Str::random(5),
            'nombres' => 'Juan',
            'apellido_paterno' => 'Perez',
            'fecha_nacimiento' => '1945-01-01',
            'estado' => 'ACTIVO',
        ]);

        $codMed = 'MED_TEST_' . Str::random(5);
        DB::table('mediciones_antropometricas')->insert([
            'cod_medicion' => $codMed,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'peso' => 68.5,
            'talla' => 168.0,
            'imc' => 24.3,
        ]);

        $guardado = DB::table('mediciones_antropometricas')->where('cod_medicion', $codMed)->first();
        $this->assertNotNull($guardado);
        $this->assertEquals(168.0, (float) $guardado->talla);
    }
}
