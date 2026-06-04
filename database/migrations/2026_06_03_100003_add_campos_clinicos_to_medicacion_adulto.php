<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicacion_adulto', function (Blueprint $table) {
            if (! Schema::hasColumn('medicacion_adulto', 'es_critica')) {
                $table->boolean('es_critica')->default(false)->after('estado');
            }
            if (! Schema::hasColumn('medicacion_adulto', 'requiere_control_signos')) {
                $table->boolean('requiere_control_signos')->default(false)->after('es_critica');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicacion_adulto', function (Blueprint $table) {
            $table->dropColumnIfExists('requiere_control_signos');
            $table->dropColumnIfExists('es_critica');
        });
    }
};
