<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Acciones\CargarConocimientoOrion;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\Support\SistemaExperto\PruebaConBaseDesechable;

final class CargaConocimientoOrionTest extends PruebaConBaseDesechable
{
    private function autor(string $rol = 'SUPERADMINISTRADOR'): User
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::create(['cod_usuario' => 'USR_ORION_TEST', 'correo' => 'orion@example.test',
            'contrasena' => bin2hex(random_bytes(24)), 'estado' => 'ACTIVO']);
        $usuario->assignRole($rol);

        return $usuario;
    }

    public function test_carga_versionada_con_procedencia_sin_activar_ni_generar_resultados(): void
    {
        $resultado = (new CargarConocimientoOrion)->ejecutar($this->autor());
        $this->assertSame(55, $resultado['nodos']);
        $this->assertSame(29, $resultado['relaciones']);
        $this->assertFalse($resultado['activacion_clinica']);
        $this->assertDatabaseHas('versiones_modelo_experto', ['cod_version_modelo' => CargarConocimientoOrion::VERSION,
            'estado' => 'INACTIVO', 'fecha_hora_vigencia' => null]);
        $this->assertSame(5, DB::table('nodos_semanticos')->where('tipo_nodo', 'CRITERIO')->count());
        $this->assertSame(1, DB::table('nodos_semanticos')->where('codigo_semantico', 'BL-LON')->count());
        $this->assertStringContainsString('filas 4, 36', DB::table('nodos_semanticos')->where('codigo_semantico', 'BL-LON')->value('observacion'));
        $this->assertSame(0, DB::table('nodos_semanticos')->where('estado', 'ACTIVO')->count());
        foreach (['residentes', 'controles_cognitivos', 'evaluaciones_expertas', 'resultados_criterio', 'reglas_expertas', 'mapeos_valores_fuente'] as $tabla) {
            $this->assertDatabaseCount($tabla, 0);
        }
        $this->assertDatabaseHas('activity_log', ['event' => 'conocimiento_documental_cargado', 'causer_id' => 'USR_ORION_TEST']);
        $version = DB::table('versiones_modelo_experto')->first();
        $ids = DB::table('nodos_semanticos')->pluck('cod_nodo_semantico');
        foreach (DB::table('relaciones_semanticas')->get() as $relacion) {
            $this->assertContains($relacion->cod_nodo_origen, $ids);
            $this->assertContains($relacion->cod_nodo_destino, $ids);
            $this->assertSame($version->cod_version_modelo, $relacion->cod_version_modelo);
        }
    }

    public function test_repetir_la_carga_no_duplica_conocimiento_ni_auditoria(): void
    {
        $autor = $this->autor();
        $carga = new CargarConocimientoOrion;
        $carga->ejecutar($autor);
        $antes = DB::table('versiones_modelo_experto')->get()->toJson();
        $auditoria = DB::table('activity_log')->count();
        $this->assertTrue($carga->ejecutar($autor)['cargado_previamente']);
        $this->assertSame($antes, DB::table('versiones_modelo_experto')->get()->toJson());
        $this->assertDatabaseCount('nodos_semanticos', 55);
        $this->assertDatabaseCount('relaciones_semanticas', 29);
        $this->assertDatabaseCount('activity_log', $auditoria);
    }

    public function test_enfermeria_no_carga_conocimiento(): void
    {
        $autor = $this->autor('ENFERMEROS');
        try {
            (new CargarConocimientoOrion)->ejecutar($autor);
            $this->fail('La carga debía rechazar el rol.');
        } catch (DomainException) {
            $this->assertDatabaseCount('versiones_modelo_experto', 0);
            $this->assertDatabaseCount('nodos_semanticos', 0);
        }
    }

    public function test_no_sobrescribe_una_version_que_difiere_de_la_fuente(): void
    {
        $autor = $this->autor();
        $carga = new CargarConocimientoOrion;
        $carga->ejecutar($autor);
        DB::table('nodos_semanticos')->where('codigo_semantico', 'COG-MEM')->update(['definicion' => 'Conflicto de prueba']);
        try {
            $carga->ejecutar($autor);
            $this->fail('La carga debía conservar y rechazar la versión divergente.');
        } catch (DomainException) {
            $this->assertDatabaseHas('nodos_semanticos', ['codigo_semantico' => 'COG-MEM', 'definicion' => 'Conflicto de prueba']);
            $this->assertDatabaseCount('versiones_modelo_experto', 1);
            $this->assertDatabaseCount('nodos_semanticos', 55);
        }
    }

    public function test_cuenta_inactiva_o_sin_permiso_no_carga(): void
    {
        $autor = $this->autor();
        DB::table('usuarios')->where('cod_usuario', $autor->getKey())->update(['estado' => 'INACTIVO']);
        foreach (['inactiva', 'sin_permiso'] as $caso) {
            if ($caso === 'sin_permiso') {
                DB::table('usuarios')->where('cod_usuario', $autor->getKey())->update(['estado' => 'ACTIVO']);
                Role::findByName('SUPERADMINISTRADOR')->revokePermissionTo('auditoria.ver');
            }
            try {
                (new CargarConocimientoOrion)->ejecutar($autor);
                $this->fail('La carga debía rechazar '.$caso);
            } catch (DomainException) {
                $this->assertDatabaseCount('versiones_modelo_experto', 0);
                $this->assertDatabaseCount('nodos_semanticos', 0);
            }
        }
    }
}
