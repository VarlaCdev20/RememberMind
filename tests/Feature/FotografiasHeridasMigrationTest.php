<?php

namespace Tests\Feature;

use App\Models\CuracionHerida;
use App\Models\DocumentoClinico;
use App\Models\Herida;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FotografiasHeridasMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_migracion_rollback_reaplicar_solo_dos_columnas_sin_tablas_nuevas(): void
    {
        $residente = Residente::factory()->create();
        DocumentoClinico::create(['cod_documento_clinico' => 'DOC_GENERAL_DDL', 'cod_residente' => $residente->getKey(), 'tipo_documento' => 'GENERAL', 'titulo' => 'Documento anterior', 'ruta_archivo' => 'documento.pdf', 'formato' => 'application/pdf', 'hash_archivo' => str_repeat('a', 64), 'fecha_hora' => now(), 'estado' => 'VIGENTE']);
        $tables = count(Schema::getTables());
        $columns = Schema::getColumnListing('documentos_clinicos');
        $migration = require database_path('migrations/2026_10_11_230000_add_evidencia_heridas_to_documentos_clinicos.php');
        $migration->down();
        $this->assertSame(2, count($columns) - count(Schema::getColumnListing('documentos_clinicos')));
        $this->assertFalse(Schema::hasColumn('documentos_clinicos', 'cod_herida'));
        $this->assertDatabaseCount('documentos_clinicos', 1);
        $migration->up();
        $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing('documentos_clinicos'));
        $this->assertSame($tables, count(Schema::getTables()));
        $this->assertTrue(Schema::hasIndex('documentos_clinicos', 'documentos_clinicos_cod_herida_index'));
        $this->assertTrue(Schema::hasIndex('documentos_clinicos', 'documentos_clinicos_cod_curacion_index'));
        $foreigns = collect(Schema::getForeignKeys('documentos_clinicos'))->filter(fn ($key) => in_array($key['columns'][0], ['cod_herida', 'cod_curacion']));
        $this->assertCount(2, $foreigns);
        foreach ($foreigns as $fk) {
            $this->assertSame('restrict', strtolower($fk['on_delete']));
        }
        $this->assertNull(DocumentoClinico::sole()->cod_herida);
        $this->assertNull(DocumentoClinico::sole()->cod_curacion);
    }

    public function test_fk_fisicas_impiden_borrado_de_herida_y_curacion_con_evidencia(): void
    {
        $resident = Residente::factory()->create();
        $personal = Personal::create(['cod_personal' => 'PER_FOTO_DDL', 'cod_usuario' => User::factory()->create()->getKey(), 'nombres' => 'Profesional sintético', 'apellido_paterno' => 'Prueba', 'profesion' => 'ENFERMERIA', 'numero_documento' => 'FOTO-DDL', 'estado' => 'ACTIVO']);
        $wound = Herida::create(['cod_herida' => 'HER_FOTO_DDL', 'cod_residente' => $resident->getKey(), 'cod_personal' => $personal->getKey(), 'tipo_herida' => 'Sintética', 'ubicacion' => 'Mano', 'fecha_hora_identificacion' => now(), 'estado' => 'ACTIVA']);
        $curation = CuracionHerida::create(['cod_curacion' => 'CUR_FOTO_DDL', 'cod_herida' => $wound->getKey(), 'cod_personal' => $personal->getKey(), 'fecha_hora' => now(), 'procedimiento' => 'Sintético']);
        $base = ['cod_residente' => $resident->getKey(), 'tipo_documento' => 'FOTO_HERIDA', 'titulo' => 'Foto sintética', 'ruta_archivo' => 'clinical/fixture.png', 'formato' => 'image/png', 'hash_archivo' => str_repeat('a', 64), 'fecha_hora' => now(), 'estado' => 'VIGENTE'];
        DocumentoClinico::create($base + ['cod_documento_clinico' => 'DOC_FOTO_DDL1', 'cod_herida' => $wound->getKey()]);
        DocumentoClinico::create($base + ['cod_documento_clinico' => 'DOC_FOTO_DDL2', 'cod_curacion' => $curation->getKey()]);
        foreach ([['curaciones_herida', 'cod_curacion', $curation->getKey()], ['heridas', 'cod_herida', $wound->getKey()]] as [$table,$column,$id]) {
            try {
                DB::transaction(fn () => DB::table($table)->where($column, $id)->delete());
                $this->fail('FK debe restringir borrado.');
            } catch (QueryException) {
                $this->assertDatabaseHas($table, [$column => $id]);
            }
        }
        $this->assertDatabaseCount('documentos_clinicos', 2);
        // La FK también rechaza un código inexistente sin pasar por eventos Eloquent.
        try {
            DB::transaction(fn () => DB::table('documentos_clinicos')->insert($base + ['cod_documento_clinico' => 'DOC_FOTO_INVALID', 'cod_herida' => 'NO_EXISTE']));
            $this->fail('Debe rechazar FK inválida.');
        } catch (QueryException) {
            $this->assertDatabaseCount('documentos_clinicos', 2);
        }
    }
}
