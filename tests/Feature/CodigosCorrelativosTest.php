<?php

namespace Tests\Feature;

use App\Compartido\Infraestructura\Persistencia\CatalogoCodigos;
use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CodigosCorrelativosTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservas_son_distintas_y_separadas_por_entidad(): void
    {
        $g = app(GeneradorCodigo::class);
        $u = (int) DB::table('secuencias_codigos')->where('entidad', 'usuarios')->value('ultimo_valor');
        $r = (int) DB::table('secuencias_codigos')->where('entidad', 'residentes')->value('ultimo_valor');
        $this->assertSame(CatalogoCodigos::formatear('usuarios', $u + 1), $g->siguiente('usuarios'));
        $this->assertSame(CatalogoCodigos::formatear('usuarios', $u + 2), $g->siguiente('usuarios'));
        $this->assertSame(CatalogoCodigos::formatear('residentes', $r + 1), $g->siguiente('residentes'));
        $this->assertDatabaseHas('secuencias_codigos', ['entidad' => 'usuarios', 'ultimo_valor' => $u + 2]);
    }

    public function test_fallo_institucional_revierte_incremento_transaccional(): void
    {
        $g = app(GeneradorCodigo::class);
        $u = (int) DB::table('secuencias_codigos')->where('entidad', 'usuarios')->value('ultimo_valor');
        $primero = $g->siguiente('usuarios');
        try {
            DB::transaction(function () use ($g): void {
                $g->siguiente('usuarios');
                throw new \DomainException('Fallo simulado');
            });
            $this->fail('El fallo debe propagarse.');
        } catch (\DomainException $e) {
            $this->assertSame('Fallo simulado', $e->getMessage());
        }
        $this->assertSame(CatalogoCodigos::formatear('usuarios', $u + 1), $primero);
        $this->assertSame(CatalogoCodigos::formatear('usuarios', $u + 2), $g->siguiente('usuarios'));
    }

    public function test_catalogo_cubre_106_entidades_con_prefijos_unicos_y_claves_reales(): void
    {
        $this->assertCount(106, CatalogoCodigos::ENTIDADES);
        $prefijos = [];
        foreach (CatalogoCodigos::ENTIDADES as $tabla => [$pk, $prefijo]) {
            $this->assertTrue(DB::getSchemaBuilder()->hasColumn($tabla, $pk), $tabla);
            $this->assertLessThanOrEqual(20, strlen(CatalogoCodigos::formatear($tabla, 1000000)));
            $prefijos[] = $prefijo;
        }
        $this->assertCount(106, array_unique($prefijos));
    }

    public function test_entidad_desconocida_no_reserva_ningun_codigo(): void
    {
        $antes = DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson();
        try {
            app(GeneradorCodigo::class)->siguiente('roles');
            $this->fail('Los IDs técnicos conservan su contrato.');
        } catch (\InvalidArgumentException) {
            $this->assertSame($antes, DB::table('secuencias_codigos')->orderBy('entidad')->get()->toJson());
        }
    }

    public function test_codigo_explicito_sincroniza_contador_y_editar_no_cambia_identidad(): void
    {
        $numero = (int) DB::table('secuencias_codigos')->where('entidad', 'usuarios')->value('ultimo_valor') + 25;
        $codigo = CatalogoCodigos::formatear('usuarios', $numero);
        $otro = CatalogoCodigos::formatear('usuarios', $numero + 2);
        $usuario = User::create(['cod_usuario' => $codigo, 'correo' => 'identidad@example.test',
            'contrasena' => bin2hex(random_bytes(16)), 'estado' => 'ACTIVO']);
        $this->assertSame(CatalogoCodigos::formatear('usuarios', $numero + 1), app(GeneradorCodigo::class)->siguiente('usuarios'));
        try {
            $usuario->update(['cod_usuario' => $otro]);
            $this->fail('Una edición ordinaria no cambia identidad.');
        } catch (\LogicException) {
            $this->assertDatabaseHas('usuarios', ['cod_usuario' => $codigo]);
            $this->assertDatabaseMissing('usuarios', ['cod_usuario' => $otro]);
        }
    }
}
