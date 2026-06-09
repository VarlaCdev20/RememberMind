<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('habitaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('habitaciones', 'piso')) {
                $table->unsignedTinyInteger('piso')->nullable()->after('capacidad');
            }

            if (! Schema::hasColumn('habitaciones', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('piso');
            }

            if (! Schema::hasColumn('habitaciones', 'ubicacion')) {
                $table->string('ubicacion', 150)->nullable()->after('tipo_habitacion');
            }

            if (! Schema::hasColumn('habitaciones', 'observacion')) {
                $table->text('observacion')->nullable()->after('observaciones');
            }
        });

        Schema::table('camas', function (Blueprint $table) {
            if (! Schema::hasColumn('camas', 'numero')) {
                $table->unsignedSmallInteger('numero')->nullable()->after('codigo');
            }

            if (! Schema::hasColumn('camas', 'observacion')) {
                $table->text('observacion')->nullable()->after('observaciones');
            }
        });
    }

    public function down(): void
    {
        Schema::table('camas', function (Blueprint $table) {
            if (Schema::hasColumn('camas', 'numero')) {
                $table->dropColumn('numero');
            }

            if (Schema::hasColumn('camas', 'observacion')) {
                $table->dropColumn('observacion');
            }
        });

        Schema::table('habitaciones', function (Blueprint $table) {
            if (Schema::hasColumn('habitaciones', 'piso')) {
                $table->dropColumn('piso');
            }

            if (Schema::hasColumn('habitaciones', 'descripcion')) {
                $table->dropColumn('descripcion');
            }

            if (Schema::hasColumn('habitaciones', 'ubicacion')) {
                $table->dropColumn('ubicacion');
            }

            if (Schema::hasColumn('habitaciones', 'observacion')) {
                $table->dropColumn('observacion');
            }
        });
    }
};