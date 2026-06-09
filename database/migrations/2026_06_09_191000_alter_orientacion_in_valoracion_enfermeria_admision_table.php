<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valoracion_enfermeria_admision', function (Blueprint $table) {
            $table->string('orientacion', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('valoracion_enfermeria_admision', function (Blueprint $table) {
            $table->string('orientacion', 40)->nullable()->change();
        });
    }
};
