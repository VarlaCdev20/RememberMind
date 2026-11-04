<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GerenciaInstalacionExtensionesTest extends TestCase
{
    use RefreshDatabase;

    private function disenos(): array
    {
        return [require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'),
            require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php')];
    }

    public function test_instalacion_conjunta_y_reversion_vacia_conservan_contenido_previo(): void
    {
        $usuario = User::factory()->create();
        $original = DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->first();
        [$documentacion, $historia] = $this->disenos();
        $antes = Schema::getTableListing();
        DB::transaction(function () use ($documentacion, $historia) {
            $documentacion->up();
            $historia->up();
        });
        $this->assertCount(count($antes) + 6, Schema::getTableListing());
        $recuperado = (array) DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->first();
        $this->assertSame((array) $original, array_intersect_key($recuperado, (array) $original));
        $this->assertNull($recuperado['fecha_hora_alta']);
        $this->assertNull($recuperado['cambio_password_pendiente']);
        foreach (['solicitudes_documentales_personal', 'destinatarios_solicitud_documental', 'entregas_solicitud_documental',
            'movimientos_laborales_personal', 'versiones_vinculo_contacto', 'eventos_asignacion_personal'] as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
        DB::transaction(function () use ($documentacion, $historia) {
            $historia->down();
            $documentacion->down();
        });
        $this->assertEqualsCanonicalizing($antes, Schema::getTableListing());
        $this->assertSame((array) $original, (array) DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->first());
    }

    public static function puntosDeFallo(): array
    {
        return ['despues_de_35' => [false], 'despues_de_37' => [true]];
    }

    #[DataProvider('puntosDeFallo')]
    public function test_fallo_entre_disenos_o_al_final_revierte_todo_el_esquema(bool $incluirHistoria): void
    {
        $usuario = User::factory()->create();
        $antes = Schema::getTableListing();
        $columnas = Schema::getColumnListing('usuarios');
        [$documentacion, $historia] = $this->disenos();
        try {
            DB::transaction(function () use ($documentacion, $historia, $incluirHistoria) {
                $documentacion->up();
                if ($incluirHistoria) $historia->up();
                throw new \RuntimeException('Fallo de instalación sintético');
            });
        } catch (\RuntimeException $error) {
            $this->assertSame('Fallo de instalación sintético', $error->getMessage());
        }
        $this->assertEqualsCanonicalizing($antes, Schema::getTableListing());
        $this->assertSame($columnas, Schema::getColumnListing('usuarios'));
        $this->assertFalse(Schema::hasColumn('documentos', 'cod_usuario_carga'));
        $this->assertDatabaseHas('usuarios', ['cod_usuario' => $usuario->getKey()]);
        if (DB::getDriverName() === 'pgsql') {
            $this->assertSame(0, (int) DB::scalar("SELECT count(*) FROM pg_proc WHERE pronamespace='public'::regnamespace AND proname LIKE 'ger_%'"));
        }
    }
}
