<?php

use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secuencias_codigos', function (Blueprint $table): void {
            $table->string('entidad', 64)->primary();
            $table->bigInteger('ultimo_valor')->default(0);
        });
    }

    public function down(): void
    {
        $db = DB::connection();
        if (app()->environment('testing') && BaseTecnicaDesechable::permite($db->getDriverName(), $db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))) {
            Schema::dropIfExists('secuencias_codigos');

            return;
        }
        // No eliminar reservas confirmadas: permite reutilizar identidades.
        throw new LogicException('El contador conserva identidades reservadas; requiere reversión coordinada.');
    }
};
