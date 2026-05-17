<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE adulto_mayor DROP CONSTRAINT IF EXISTS adulto_mayor_ci_expedicion_ci_complemento_ci_unique');

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS adulto_mayor_identidad_unica_idx
            ON adulto_mayor (ci, expedicion_ci, COALESCE(complemento_ci, ''))
            WHERE ci IS NOT NULL AND expedicion_ci IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS adulto_mayor_identidad_unica_idx');

        DB::statement("
            ALTER TABLE adulto_mayor
            ADD CONSTRAINT adulto_mayor_ci_expedicion_ci_complemento_ci_unique
            UNIQUE (ci, expedicion_ci, complemento_ci)
        ");
    }
};