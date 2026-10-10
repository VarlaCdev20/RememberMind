<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Conocimiento\CargadorConocimiento;
use App\Backend\Modulos\SistemaExperto\Conocimiento\InventarioExperto;
use App\Models\NodoSemantico;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

/** Ejecutar exclusivamente después del checkpoint de migraciones expertas. */
class PersistenciaExpertaTest extends TestCase
{
    private array $contrato;

    protected function setUp(): void
    {
        parent::setUp();
        $conexion = DB::connection();
        $segura = ($conexion->getDriverName() === 'sqlite' && $conexion->getDatabaseName() === ':memory:')
            || ($conexion->getDriverName() === 'pgsql' && preg_match('/^remembermind_experto_test_[0-9]{8}_[a-z0-9]+$/D', $conexion->getDatabaseName()));
        if (! app()->environment('testing') || ! $segura) {
            throw new RuntimeException('Esta prueba requiere SQLite :memory: o una PostgreSQL desechable dedicada al experto.');
        }
        $this->contrato = json_decode(file_get_contents(base_path('tests/Fixtures/SistemaExperto/contrato-d137.json')), true, 512, JSON_THROW_ON_ERROR);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->contrato)) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            $this->artisan('migrate:rollback', ['--force' => true])->assertSuccessful();
        }
        parent::tearDown();
    }

    public function test_tablas_columnas_nullabilidad_fk_indices_y_claves_instalados(): void
    {
        $instaladas = Schema::getTableListing(schemaQualified: false);
        $this->assertCount(23, array_intersect($instaladas, InventarioExperto::TABLAS));
        $this->assertCount(71, array_diff(InventarioExperto::excluirDelInventarioOperativo($instaladas), [
            'migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs',
            'personal_access_tokens', 'activity_log', 'permissions', 'roles', 'model_has_permissions', 'model_has_roles', 'role_has_permissions',
        ]));
        foreach ($this->contrato['tables'] as $tabla) {
            $columns = Schema::getColumns($tabla['name']);
            $this->assertSame(array_column($tabla['columns'], 'name'), array_column($columns, 'name'), $tabla['name']);
            foreach ($columns as $i => $col) {
                // SQLite informa nullable=false en las PK explícitas de estas migraciones.
                $this->assertSame($tabla['columns'][$i]['nullable'], $col['nullable'], $tabla['name'].'.'.$col['name']);
                $this->assertFalse($col['auto_increment']);
                $this->assertNull($col['default']);
                $tipo = $tabla['columns'][$i]['type'];
                if ($tipo === 'string') {
                    $this->assertSame('varchar', $col['type_name']);
                    if (DB::getDriverName() === 'pgsql') {
                        $this->assertStringContainsString('('.$tabla['columns'][$i]['length'].')', $col['type']);
                    }
                }
                if ($tipo === 'text') {
                    $this->assertSame('text', $col['type_name']);
                }
                if ($tipo === 'dateTime') {
                    $this->assertContains($col['type_name'], ['datetime', 'timestamp']);
                }
            }
            $fisicas = Schema::getForeignKeys($tabla['name']);
            foreach ($fisicas as $fk) {
                $this->assertSame('restrict', $fk['on_delete']);
                $this->assertSame('no action', $fk['on_update']);
            }
            $fks = array_map(fn ($f) => ['columns' => $f['columns'], 'references_table' => $f['foreign_table'], 'references_columns' => $f['foreign_columns']], $fisicas);
            $esperadas = $tabla['foreign_keys'];
            usort($fks, fn ($a, $b) => strcmp(serialize($a), serialize($b)));
            usort($esperadas, fn ($a, $b) => strcmp(serialize($a), serialize($b)));
            $this->assertSame($esperadas, $fks, $tabla['name']);
            $indices = Schema::getIndexes($tabla['name']);
            $pk = array_values(array_filter($indices, fn ($i) => $i['primary']));
            $this->assertCount(1, $pk);
            $this->assertSame([array_values(array_filter($tabla['columns'], fn ($c) => $c['primary_key']))[0]['name']], $pk[0]['columns']);
            foreach ($tabla['unique'] as $cols) {
                $this->assertNotEmpty(array_filter($indices, fn ($i) => $i['unique'] && $i['columns'] === $cols));
            }
            foreach ($tabla['indexes'] as $cols) {
                $this->assertNotEmpty(array_filter($indices, fn ($i) => $i['columns'] === $cols));
            }
        }
    }

    private function versionesYNodos(): void
    {
        DB::table('usuarios')->insert(['cod_usuario' => 'USR_EXPERT_TEST', 'correo' => 'expert-test@example.test', 'contrasena' => password_hash(bin2hex(random_bytes(20)), PASSWORD_BCRYPT), 'estado' => 'ACTIVO']);
        foreach (['VER_TEST_A', 'VER_TEST_B'] as $id) {
            DB::table('versiones_modelo_experto')->insert(['cod_version_modelo' => $id, 'codigo_version' => $id, 'nombre' => 'Fixture técnico inactivo', 'estado' => 'INACTIVO', 'fecha_hora_creacion' => '2026-10-08 12:00:00', 'cod_usuario_creacion' => 'USR_EXPERT_TEST']);
            DB::table('nodos_semanticos')->insert(['cod_nodo_semantico' => 'NODO_'.$id, 'cod_version_modelo' => $id, 'codigo_semantico' => 'TEST-NODO', 'nombre' => 'Nodo de prueba', 'tipo_nodo' => 'VARIABLE', 'definicion' => 'Fixture sin interpretación clínica', 'estado' => 'INACTIVO', 'fecha_hora_creacion' => '2026-10-08 12:00:00', 'cod_usuario_creacion' => 'USR_EXPERT_TEST']);
        }
    }

    public function test_fk_compuesta_rechaza_relacion_entre_versiones(): void
    {
        $this->versionesYNodos();
        $this->expectException(QueryException::class);
        DB::table('relaciones_semanticas')->insert(['cod_relacion_semantica' => 'REL_TEST', 'cod_version_modelo' => 'VER_TEST_A', 'cod_nodo_origen' => 'NODO_VER_TEST_A', 'cod_nodo_destino' => 'NODO_VER_TEST_B', 'tipo_relacion' => 'TEST_RELACION', 'significado' => 'Rechazo esperado', 'estado' => 'INACTIVO', 'fecha_hora_creacion' => '2026-10-08 12:00:00', 'cod_usuario_creacion' => 'USR_EXPERT_TEST']);
    }

    public function test_variable_puede_nacer_sin_dominio_y_unique_protege_el_nodo(): void
    {
        $this->versionesYNodos();
        $fila = ['cod_variable_experta' => 'VAR_TEST', 'cod_version_modelo' => 'VER_TEST_A', 'cod_nodo_semantico' => 'NODO_VER_TEST_A', 'tipo_semantico' => 'TEST', 'papel_inferencial' => 'TEST', 'estado' => 'INACTIVO', 'fecha_hora_creacion' => '2026-10-08 12:00:00', 'cod_usuario_creacion' => 'USR_EXPERT_TEST'];
        DB::table('variables_expertas')->insert($fila);
        $this->assertNull(DB::table('variables_expertas')->where('cod_variable_experta', 'VAR_TEST')->value('cod_dominio_valores'));
        $fila['cod_variable_experta'] = 'VAR_TEST_DUP';
        $this->expectException(QueryException::class);
        DB::table('variables_expertas')->insert($fila);
    }

    public function test_restrict_preserva_version_referenciada(): void
    {
        $this->versionesYNodos();
        $this->expectException(QueryException::class);
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST_A')->delete();
    }

    public function test_rollback_inverso_de_23_expertas_conserva_esquema_operacional_y_reinstala(): void
    {
        $antes = Schema::getColumns('residentes');
        // Movilidad V2, Dolor V2 y Eliminación V2 son extensiones aprobadas posteriores al módulo experto.
        $this->artisan('migrate:rollback', ['--step' => 3, '--force' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasColumn('registros_movilidad', 'distancia_metros'));
        $this->assertFalse(Schema::hasColumn('valoraciones_dolor', 'cod_valoracion_origen'));
        $this->assertFalse(Schema::hasColumn('registros_eliminacion', 'volumen_ml'));
        $this->artisan('migrate:rollback', ['--step' => 23, '--force' => true])->assertSuccessful();
        foreach (InventarioExperto::TABLAS as $tabla) {
            $this->assertFalse(Schema::hasTable($tabla));
        }
        $this->assertSame($antes, Schema::getColumns('residentes'));
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertTrue(Schema::hasColumn('registros_eliminacion', 'volumen_ml'));
        $this->assertTrue(Schema::hasColumn('registros_movilidad', 'distancia_metros'));
        foreach (InventarioExperto::TABLAS as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla));
        }
    }

    public function test_cargador_aisla_versiones_y_no_activa_conocimiento(): void
    {
        $this->versionesYNodos();
        $paquete = (new CargadorConocimiento)->cargar('VER_TEST_A', []);
        $this->assertSame(['NODO_VER_TEST_A'], array_column($paquete->tabla('nodos_semanticos'), 'cod_nodo_semantico'));
        $this->assertFalse($paquete->soloPruebasTecnicas);
        $this->assertNull($paquete->tipoReglaResultado);
    }

    public function test_cargador_rechaza_ciclo_de_versiones(): void
    {
        $this->versionesYNodos();
        DB::beginTransaction();
        try {
            DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST_A')->update(['cod_version_anterior' => 'VER_TEST_B']);
            DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST_B')->update(['cod_version_anterior' => 'VER_TEST_A']);
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('Ciclo');
            (new CargadorConocimiento)->cargar('VER_TEST_A', []);
        } finally {
            DB::rollBack();
        }
    }

    public function test_modelo_no_reescribe_historia_guardada(): void
    {
        $this->versionesYNodos();
        $nodo = NodoSemantico::query()->findOrFail('NODO_VER_TEST_A');
        $nodo->definicion = 'Reinterpretación que debe rechazarse';
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('sin sobrescritura');
        $nodo->save();
    }
}
