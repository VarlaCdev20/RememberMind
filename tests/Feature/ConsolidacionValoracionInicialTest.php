<?php

namespace Tests\Feature;

use App\Models\Preadmision;
use App\Models\User;
use App\Modulos\Valoraciones\Infraestructura\Persistencia\BloqueValoracionInicial as Bloque;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsolidacionValoracionInicialTest extends TestCase
{
    use RefreshDatabase;

    public function test_conserva_datos_y_permite_reversion_poblada(): void
    {
        $usuario = User::factory()->create();
        $personal = 'PER_VEN_MIG';
        DB::table('personal')->insert([
            'cod_personal' => $personal,
            'cod_usuario' => $usuario->getKey(),
            'nombres' => 'Persona',
            'apellido_paterno' => 'Sintética',
            'numero_documento' => 'VEN-MIG-01',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        $solicitud = $this->solicitud($usuario, 'PRE_VEN_MIG');
        $sinValoracion = $this->solicitud($usuario, 'PRE_VEN_VAC');
        $transicion = require database_path('migrations/2026_11_18_000001_separate_clinical_assessments.php');
        $this->assertTrue(Schema::hasTable(Bloque::ORIGEN));

        DB::table(Bloque::ORIGEN)->insert([
            'cod_preadmision' => $solicitud->getKey(),
            'cod_valoracion_enfermeria' => 'VEN_MIG_01',
            'cod_usuario_registro' => $usuario->getKey(),
            'cod_personal_valorador' => $personal,
            'fecha_hora' => '2026-10-12 09:15:00',
            'confirmacion_documentacion' => true,
            'talla' => 152.5,
            'recomendacion_enfermeria' => 'Revisar el expediente.',
        ]);
        $antes = (array) DB::table(Bloque::ORIGEN)->first();
        ksort($antes);

        $transicion->down();
        $this->assertFalse(Schema::hasTable(Bloque::ORIGEN));
        $this->assertSame('VEN_MIG_01', DB::table('preadmisiones')->where('cod_preadmision', $solicitud->getKey())->value(Bloque::CODIGO));
        $transicion->up();
        $this->assertNull($sinValoracion->fresh()->valoracionEnfermeriaRegistro());
        $solicitud = $solicitud->fresh();
        $this->assertSame('VEN_MIG_01', $solicitud->valoracionEnfermeriaRegistro()->getKey());
        $this->assertSame('152.50', $solicitud->valoracion_enfermeria['talla']);
        $this->assertArrayNotHasKey('valoracion_talla', $solicitud->toArray());
        $solicitud->fill(['valoracion_talla' => 180]);
        $this->assertFalse($solicitud->isDirty('valoracion_talla'));

        $this->rechazar(fn () => DB::table('preadmisiones')->where('cod_preadmision', $solicitud->getKey())->delete());
        $this->rechazar(fn () => DB::table(Bloque::ORIGEN)->where('cod_preadmision', $solicitud->getKey())
            ->update(['cod_valoracion_enfermeria' => 'VEN_MIG_02']));
        $this->rechazar(fn () => DB::table(Bloque::ORIGEN)->where('cod_preadmision', $solicitud->getKey())->delete());
        $this->rechazar(fn () => DB::table('preadmisiones')->where('cod_preadmision', $sinValoracion->getKey())
            ->update(['valoracion_talla' => 152.5]));

        $despues = (array) DB::table(Bloque::ORIGEN)->first();
        ksort($despues);
        $this->assertSame($antes, $despues);
        $this->assertFalse(Schema::hasColumn('preadmisiones', 'valoracion_talla'));
        $this->assertCount(24, Schema::getColumnListing('preadmisiones'));
    }

    private function solicitud(User $usuario, string $codigo): Preadmision
    {
        return Preadmision::create([
            'cod_preadmision' => $codigo,
            'cod_usuario_registro' => $usuario->getKey(),
            'nombres' => 'Marta',
            'apellido_paterno' => 'Flores',
            'fecha_nacimiento' => '1945-01-01',
            'motivo_ingreso' => 'Evaluación',
            'fecha_solicitud' => now(),
            'estado' => 'PENDIENTE',
        ]);
    }

    private function rechazar(callable $accion): void
    {
        try {
            DB::transaction($accion);
            $this->fail('La base debe rechazar la operación.');
        } catch (QueryException) {
            $this->addToAssertionCount(1);
        }
    }
}
