<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE adulto_mayor ALTER COLUMN cod_am DROP DEFAULT');
    }

    public function down(): void
    {
        // No restauramos el DEFAULT anterior porque pertenecía a la estructura vieja integer/sequence.
    }
};