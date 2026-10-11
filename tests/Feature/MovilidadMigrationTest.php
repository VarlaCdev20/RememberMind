<?php

namespace Tests\Feature;

use App\Models\RegistroMovilidad;
use App\Models\Residente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MovilidadMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_extension_exacta_reversible_preserva_filas_fk_indices_y_numero_de_tablas(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['nombres' => 'Enfermería QA']);
        $user->assignRole('ENFERMEROS');
        $resident = Residente::factory()->create();
        RegistroMovilidad::create(['cod_movilidad' => 'MOV_OLD_TEST', 'cod_residente' => $resident->getKey(),
            'cod_personal' => $user->personal()->firstOrFail()->getKey(), 'fecha_hora' => now(),
            'marcha' => 'ASISTIDA', 'dispositivo' => 'Andador anterior en texto libre', 'observacion' => 'Historia preservada.', 'estado' => 'VIGENTE']);
        $before = DB::table('registros_movilidad')->where('cod_movilidad', 'MOV_OLD_TEST')->first();
        $tables = Schema::getTableListing();
        $columns = Schema::getColumnListing('registros_movilidad');
        $indexes = Schema::getIndexes('registros_movilidad');
        $keys = Schema::getForeignKeys('registros_movilidad');
        $migration = require database_path('migrations/2026_10_10_000100_extend_registros_movilidad_v2.php');
        $added = ['motivo_registro', 'actividad_realizada', 'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual', 'tolerancia_movilidad'];
        $migration->down();
        $this->assertEqualsCanonicalizing($added, array_values(array_diff($columns, Schema::getColumnListing('registros_movilidad'))));
        $legacy = DB::table('registros_movilidad')->where('cod_movilidad', 'MOV_OLD_TEST')->first();
        foreach ((array) $legacy as $field => $value) {
            $this->assertEquals($before->{$field}, $value);
        }
        $migration->up();
        $this->assertEqualsCanonicalizing($columns, Schema::getColumnListing('registros_movilidad'));
        $this->assertEqualsCanonicalizing($tables, Schema::getTableListing());
        $this->assertEqualsCanonicalizing($indexes, Schema::getIndexes('registros_movilidad'));
        $this->assertEqualsCanonicalizing($keys, Schema::getForeignKeys('registros_movilidad'));
        $record = RegistroMovilidad::findOrFail('MOV_OLD_TEST');
        foreach ($added as $field) {
            $this->assertNull($record->{$field});
        }
        $this->assertSame('Andador anterior en texto libre', $record->dispositivo);
        $this->assertSame('Historia preservada.', $record->observacion);
        $this->assertSame('Andador anterior en texto libre', collect($record->resumenOperacional()['campos'])->firstWhere('nombre', 'Dispositivo')['valor']);
    }
}
