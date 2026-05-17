<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE familiar_adulto ALTER COLUMN cod_am SET NOT NULL');
        DB::statement('ALTER TABLE familiar_adulto ALTER COLUMN cod_fam SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE familiar_adulto ALTER COLUMN cod_am DROP NOT NULL');
        DB::statement('ALTER TABLE familiar_adulto ALTER COLUMN cod_fam DROP NOT NULL');
    }
};