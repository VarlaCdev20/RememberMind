<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PreparacionFuentesCOGMEM;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use MotorExperto\dominio\conocimiento\versiones\PaqueteInvestigadoCOGMEM;
use MotorExperto\dominio\instrumentos\NormalizadorHvltBolivia2015;
use Tests\Support\InformeCognitivoFixture as F;
use Tests\TestCase;

final class PreparacionInstrumentalInvestigadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_cuenta_aplicaciones_completas_no_futuras_ni_anuladas_sin_asignarles_baremos(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(12, 0));
        $this->seed(RolesAndPermissionsSeeder::class);
        $u = User::factory()->create(['estado' => 'ACTIVO']);
        $u->assignRole('MEDICO GENERAL/GERIATRA');
        $p = Personal::create(['cod_usuario' => $u->getKey(), 'nombres' => 'Profesional', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'NORMAS-PREP', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->actingAs($u);
        $r = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $otro = Residente::factory()->create(['estado' => 'ADMITIDO']);
        F::conocimiento($u);
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->update(['codigo_version' => PaqueteInvestigadoCOGMEM::CODIGO]);
        F::insertar('instrumentos', ['cod_instrumento' => 'INST-NOMINAL', 'nombre' => 'Tarea transcrita', 'version' => 'VERSION-ORIGEN']);
        foreach ([['AP-COMPLETA', $r, 'COMPLETA', now()->subDay()], ['AP-ANULADA', $r, 'ANULADA', now()->subDay()],
            ['AP-FUTURA', $r, 'COMPLETA', now()->addDay()], ['AP-AJENA', $otro, 'COMPLETA', now()->subDay()]] as [$id, $residente, $estado, $fecha]) {
            F::insertar('aplicaciones_instrumento', ['cod_aplicacion' => $id, 'cod_residente' => $residente->getKey(),
                'cod_personal' => $p->getKey(), 'cod_instrumento' => 'INST-NOMINAL', 'estado' => $estado, 'fecha_hora' => $fecha->format('Y-m-d H:i:s'),
                'puntaje_total' => null, 'clasificacion' => null]);
        }
        $resultado = app(PreparacionFuentesCOGMEM::class)->consultar($u, $r);
        self::assertSame(1, $resultado['aplicaciones']);
        self::assertCount(1, $resultado['instrumentos']);
        self::assertArrayHasKey(NormalizadorHvltBolivia2015::METODO, $resultado['metodos_instrumentales']);
        self::assertFalse($resultado['puede_emitir_resultado']);
        self::assertNull($resultado['resultado']);
        self::assertSame(0, DB::table('evaluaciones_expertas')->count());
        self::assertNull(DB::table('aplicaciones_instrumento')->where('cod_aplicacion', 'AP-COMPLETA')->value('puntaje_total'));
        DB::table('usuarios')->where('cod_usuario', $u->getKey())->update(['estado' => 'INACTIVO']);
        $this->expectException(AuthorizationException::class);
        app(PreparacionFuentesCOGMEM::class)->consultar($u, $r);
    }
}
