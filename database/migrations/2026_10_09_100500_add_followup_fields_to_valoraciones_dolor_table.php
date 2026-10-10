<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $triggers = $this->sqliteTriggers();
        Schema::table('valoraciones_dolor', function (Blueprint $table) {
            $table->string('cod_valoracion_origen', 20)->nullable();
            $table->string('frecuencia', 40)->nullable();
            $table->text('factores_alivio')->nullable();
            $table->index('cod_valoracion_origen', 'idx_dolor_origen_v2');
            // La FK simple asegura existencia; el par asegura el mismo residente incluso por SQL.
            $table->unique(['cod_valoracion_dolor', 'cod_residente'], 'uq_dolor_codigo_residente_v2');
            $table->foreign('cod_valoracion_origen', 'fk_dolor_origen_v2')
                ->references('cod_valoracion_dolor')->on('valoraciones_dolor')->restrictOnDelete();
            $table->foreign(['cod_valoracion_origen', 'cod_residente'], 'fk_dolor_origen_residente_v2')
                ->references(['cod_valoracion_dolor', 'cod_residente'])->on('valoraciones_dolor')->restrictOnDelete();
        });
        $hasHistoryIndex = collect(Schema::getIndexes('valoraciones_dolor'))
            ->contains(fn (array $index) => array_slice($index['columns'], 0, 2) === ['cod_residente', 'fecha_hora']);
        if (! $hasHistoryIndex) {
            Schema::table('valoraciones_dolor', fn (Blueprint $table) => $table->index(['cod_residente', 'fecha_hora'], 'idx_dolor_residente_fecha_v2'));
        }
        $this->restoreSqliteTriggers($triggers);
    }

    public function down(): void
    {
        $triggers = $this->sqliteTriggers();
        Schema::table('valoraciones_dolor', function (Blueprint $table) {
            // SQLite identifica las FK por columnas; PostgreSQL/MySQL conservan el nombre explícito.
            $sqlite = Schema::getConnection()->getDriverName() === 'sqlite';
            $table->dropForeign($sqlite ? ['cod_valoracion_origen', 'cod_residente'] : 'fk_dolor_origen_residente_v2');
            $table->dropForeign($sqlite ? ['cod_valoracion_origen'] : 'fk_dolor_origen_v2');
        });
        Schema::table('valoraciones_dolor', fn (Blueprint $table) => $table->dropIndex('idx_dolor_origen_v2'));
        Schema::table('valoraciones_dolor', fn (Blueprint $table) => $table->dropUnique('uq_dolor_codigo_residente_v2'));
        if (Schema::hasIndex('valoraciones_dolor', 'idx_dolor_residente_fecha_v2')) {
            Schema::table('valoraciones_dolor', fn (Blueprint $table) => $table->dropIndex('idx_dolor_residente_fecha_v2'));
        }
        Schema::table('valoraciones_dolor', fn (Blueprint $table) => $table->dropColumn(['cod_valoracion_origen', 'frecuencia', 'factores_alivio']));
        $this->restoreSqliteTriggers($triggers);
    }

    /** Laravel reconstruye la tabla SQLite y no conserva sus triggers de integridad V2. */
    private function sqliteTriggers(): array
    {
        return Schema::getConnection()->getDriverName() === 'sqlite'
            ? DB::table('sqlite_master')->where('type', 'trigger')->where('tbl_name', 'valoraciones_dolor')->pluck('sql')->all()
            : [];
    }

    private function restoreSqliteTriggers(array $triggers): void
    {
        foreach ($triggers as $sql) {
            // Definiciones existentes del catálogo del motor; no son input del usuario.
            DB::unprepared($sql);
        }
    }
};
