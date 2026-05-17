<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('adulto_mayor')
            ->whereNotNull('celular')
            ->where('celular', '!=', '')
            ->update(['tiene_celular' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario un rollback estricto aquí, ya que solo es corrección de datos.
    }
};
