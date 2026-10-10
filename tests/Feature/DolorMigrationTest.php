<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionDolor;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DolorMigrationTest extends TestCase
{
    use DatabaseMigrations;

    protected function beforeRefreshingDatabase()
    {
        $db = DB::connection();
        $this->assertTrue(app()->environment('testing'));
        $this->assertTrue(($db->getDriverName() === 'sqlite' && $db->getDatabaseName() === ':memory:')
            || ($db->getDriverName() === 'pgsql' && preg_match('/^remembermind_experto_test_[0-9]{8}_[a-z0-9]+$/D', $db->getDatabaseName())));
    }

    public function test_dolor_v2_rollback_reapply_preserves_rows_triggers_and_table_count(): void
    {
        // DDL fuera de transacciones, como el migrador SQLite real.
        $resident = Residente::factory()->create();
        $personal = Personal::create(['cod_personal' => 'PER_DOLOR_DDL', 'cod_usuario' => User::factory()->create()->getKey(), 'nombres' => 'Rosa',
            'apellido_paterno' => 'Mamani', 'numero_documento' => 'DDL-SYNTH-01', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        foreach ([8, 5] as $index => $value) {
            ValoracionDolor::create(['cod_valoracion_dolor' => 'VD_DDL_'.$index,
                'cod_residente' => $resident->getKey(), 'cod_personal' => $personal->getKey(),
                'cod_valoracion_origen' => $index ? 'VD_DDL_0' : null,
                'fecha_hora' => now()->subMinutes(2 - $index), 'intensidad' => $value, 'estado' => 'VIGENTE']);
        }
        $count = count(Schema::getTables());
        $triggers = $this->triggers();
        if (DB::getDriverName() === 'sqlite') {
            $this->assertCount(2, $triggers, 'Conservar triggers V2 atención/residente.');
        }
        $migration = require database_path('migrations/2026_10_09_100500_add_followup_fields_to_valoraciones_dolor_table.php');
        $migration->down();
        $this->assertFalse(Schema::hasColumn('valoraciones_dolor', 'cod_valoracion_origen'));
        $this->assertDatabaseCount('valoraciones_dolor', 2);
        $this->assertSame($triggers, $this->triggers());
        $migration->up();
        $this->assertTrue(Schema::hasColumns('valoraciones_dolor', ['cod_valoracion_origen', 'frecuencia', 'factores_alivio']));
        $this->assertTrue(Schema::hasIndex('valoraciones_dolor', 'idx_dolor_origen_v2'));
        $this->assertSame($count, count(Schema::getTables()));
        $this->assertDatabaseHas('valoraciones_dolor', ['cod_valoracion_dolor' => 'VD_DDL_0', 'intensidad' => 8]);
        $this->assertDatabaseHas('valoraciones_dolor', ['cod_valoracion_dolor' => 'VD_DDL_1', 'intensidad' => 5]);
        $this->assertSame($triggers, $this->triggers());
    }

    public function test_dolor_v2_does_not_duplicate_an_existing_history_index(): void
    {
        $migration = require database_path('migrations/2026_10_09_100500_add_followup_fields_to_valoraciones_dolor_table.php');
        $migration->down();
        Schema::table('valoraciones_dolor', fn ($table) => $table->index(['cod_residente', 'fecha_hora'], 'idx_dolor_historial_previo_qa'));
        $migration->up();
        $historyIndexes = collect(Schema::getIndexes('valoraciones_dolor'))->filter(
            fn ($index) => array_slice($index['columns'], 0, 2) === ['cod_residente', 'fecha_hora']
        );
        $this->assertCount(1, $historyIndexes);
        $this->assertTrue(Schema::hasIndex('valoraciones_dolor', 'idx_dolor_historial_previo_qa'));
        $this->assertFalse(Schema::hasIndex('valoraciones_dolor', 'idx_dolor_residente_fecha_v2'));
        $migration->down();
        $this->assertTrue(Schema::hasIndex('valoraciones_dolor', 'idx_dolor_historial_previo_qa'));
        $migration->up();
    }

    private function triggers(): array
    {
        return DB::getDriverName() === 'sqlite'
            ? DB::table('sqlite_master')->where('type', 'trigger')->where('tbl_name', 'valoraciones_dolor')->orderBy('name')->pluck('sql')->all()
            : [];
    }
}
