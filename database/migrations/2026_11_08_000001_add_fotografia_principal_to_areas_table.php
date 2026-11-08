<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Vínculo opcional autorizado por la propietaria el 08/11/2026; sin tablas nuevas. */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE areas ADD COLUMN cod_fotografia VARCHAR(20) NULL
            CONSTRAINT areas_cod_fotografia_foreign REFERENCES fotografias_institucionales(cod_fotografia)
            ON UPDATE RESTRICT ON DELETE RESTRICT');
        DB::statement('CREATE INDEX areas_cod_fotografia_index ON areas(cod_fotografia)');
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL lock_timeout = '10s'");
                DB::statement('LOCK TABLE areas IN ACCESS EXCLUSIVE MODE');
            } else {
                DB::statement('UPDATE areas SET cod_fotografia=cod_fotografia WHERE 1=0');
            }
            if (DB::table('areas')->whereNotNull('cod_fotografia')->exists()) {
                throw new LogicException('No se retira una relación de fotografías con vínculos guardados.');
            }
            DB::statement('DROP INDEX areas_cod_fotografia_index');
            DB::statement('ALTER TABLE areas DROP COLUMN cod_fotografia');
        });
    }
};
