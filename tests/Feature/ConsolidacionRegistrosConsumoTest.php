<?php

namespace Tests\Feature;

use App\Models\Jornada;
use App\Models\Personal;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ConsolidacionRegistrosConsumoTest extends TestCase
{
    use RefreshDatabase;

    public function test_conserva_eventos_independientes_y_reconstruye_los_origenes(): void
    {
        $actor = User::factory()->create();
        $personal = Personal::create([
            'cod_usuario' => $actor->getKey(), 'nombres' => 'Ana', 'apellido_paterno' => 'Sintética',
            'numero_documento' => 'CONSUMO-MIG', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO',
        ]);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-11-10', 'estado' => 'ABIERTA']);
        $residente = Residente::crearDesdeAdmision([
            'nombres' => 'Adela', 'apellido_paterno' => 'Sintética', 'fecha_nacimiento' => '1946-05-20', 'estado' => 'ADMITIDO',
        ]);
        $base = [
            'cod_residente' => $residente->getKey(), 'cod_personal' => $personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'fecha_hora' => '2026-11-10 08:10:00', 'estado' => 'VIGENTE',
        ];
        $migracion = require database_path('migrations/2026_11_14_000002_consolidate_registros_consumo.php');
        $eliminacion = require database_path('migrations/2026_11_14_000003_consolidate_eliminacion_en_consumo.php');
        $eliminacion->down();
        $migracion->down();

        DB::table('registros_ingesta')->insert($base + ['cod_ingesta' => 'EVENTO_01', 'tipo_comida' => 'DESAYUNO', 'porcentaje_consumido' => 75.5, 'dificultad_deglucion' => false]);
        DB::table('registros_hidratacion')->insert($base + ['cod_hidratacion' => 'EVENTO_01', 'cantidad_ml' => 120.35, 'tipo_liquido' => 'AGUA']);
        try {
            $migracion->up();
            $this->fail('La coincidencia de códigos debe detener la migración.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('códigos compartidos', $e->getMessage());
        }
        $this->assertFalse(Schema::hasTable('registros_consumo'));
        DB::table('registros_hidratacion')->where('cod_hidratacion', 'EVENTO_01')->update(['cod_hidratacion' => 'EVENTO_02']);
        $filas = fn () => [DB::table('registros_ingesta')->first(), DB::table('registros_hidratacion')->first()];
        $originales = $filas();

        $migracion->up();
        $this->assertFalse(Schema::hasTable('registros_ingesta'));
        $this->assertFalse(Schema::hasTable('registros_hidratacion'));
        $this->assertDatabaseCount('registros_consumo', 2);
        $this->assertSame('EVENTO_01', $residente->registrosIngesta()->sole()->cod_ingesta);
        $this->assertSame('EVENTO_02', $residente->registrosHidratacion()->sole()->cod_hidratacion);
        $this->assertNull(RegistroIngesta::find('EVENTO_02'));
        $this->assertNull(RegistroHidratacion::find('EVENTO_01'));
        $this->assertArrayNotHasKey('cantidad_ml', RegistroIngesta::findOrFail('EVENTO_01')->toArray());

        foreach ([
            ['tipo_evento' => 'INGESTA', 'tipo_comida' => 'DESAYUNO', 'cantidad_ml' => 50],
            ['tipo_evento' => 'HIDRATACION', 'tipo_liquido' => 'AGUA'],
        ] as $invalido) {
            try {
                DB::transaction(fn () => DB::table('registros_consumo')->insert($base + $invalido + ['cod_registro_consumo' => 'EVENTO_ERR']));
                $this->fail('El motor debe rechazar atributos incompatibles.');
            } catch (QueryException) {
                $this->addToAssertionCount(1);
            }
        }

        $migracion->down();
        $this->assertEquals($originales, $filas());
        $migracion->up();
        $eliminacion->up();
    }
}
