<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evaluaciones_geriatricas', function (Blueprint $table) {
            $table->string('cod_eval_ger', 20)->primary(); // EVG_0001
            $table->string('cod_am', 20); // FK a adulto_mayor
            $table->string('cod_instrumento', 30); // FK a instrumentos_geriatricos
            
            // Registrador y Auditoría
            $table->string('registrado_por', 20); // FK a users.cod_usu
            
            // Polimorfismo String
            $table->string('evaluador_id', 20)->nullable();
            $table->string('evaluador_tipo', 100)->nullable();
            
            $table->date('fecha_eval');
            $table->time('hora_eval')->nullable(); // Nullable
            
            // Métricas Nullables
            $table->decimal('puntaje_total', 8, 2)->nullable();
            $table->string('categoria_resultado', 150)->nullable(); // Nullable
            $table->string('nivel_alerta', 30)->default('NORMAL'); // NORMAL, PREVENTIVO, CRITICO
            $table->string('nivel_riesgo', 30)->nullable(); // Nullable
            
            $table->text('observaciones')->nullable();
            $table->jsonb('datos_formulario')->nullable(); // Guardado JSON
            
            // Gestión de Anulación (Control No Destructivo)
            $table->string('estado_eval', 20)->default('ACTIVO'); // ACTIVO, ANULADO
            $table->text('motivo_anulacion')->nullable();
            $table->string('anulado_por', 20)->nullable(); // FK a users.cod_usu
            $table->timestamp('anulado_en')->nullable();
            
            $table->foreign('cod_am')->references('cod_am')->on('adulto_mayor')->onDelete('restrict');
            $table->foreign('cod_instrumento')->references('cod_instrumento')->on('instrumentos_geriatricos')->onDelete('restrict');
            $table->foreign('registrado_por')->references('cod_usu')->on('users')->onDelete('restrict');
            $table->foreign('anulado_por')->references('cod_usu')->on('users')->onDelete('restrict');
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_geriatricas');
    }
};
