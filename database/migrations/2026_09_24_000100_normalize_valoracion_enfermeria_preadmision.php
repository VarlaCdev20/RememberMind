<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valoraciones_enfermeria_preadmision', function (Blueprint $table) {
            $table->string('cod_valoracion_enfermeria', 20)->primary();
            $table->string('cod_preadmision', 20)->unique();
            $table->string('cod_usuario_registro', 20)->nullable();
            $table->dateTime('fecha_hora')->nullable();
            $table->string('estado_general', 40)->nullable();
            $table->string('nivel_conciencia', 40)->nullable();
            $table->string('orientacion_persona', 40)->nullable();
            $table->string('orientacion_tiempo', 40)->nullable();
            $table->string('orientacion_espacio', 40)->nullable();
            $table->string('comunicacion', 80)->nullable();
            $table->boolean('hay_dolor')->default(false);
            $table->unsignedTinyInteger('intensidad_dolor')->nullable();
            $table->string('ubicacion_dolor', 255)->nullable();
            $table->string('movilidad', 80)->nullable();
            $table->string('apoyo_movilidad', 160)->nullable();
            $table->string('riesgo_caida', 40)->nullable();
            $table->string('piel_estado', 80)->nullable();
            $table->boolean('hay_heridas')->default(false);
            $table->string('ubicacion_heridas', 255)->nullable();
            $table->string('higiene_ingreso', 80)->nullable();
            $table->string('continencia_basica', 80)->nullable();
            $table->string('alimentacion_aparente', 80)->nullable();
            $table->decimal('pa_sistolica', 6, 2)->nullable();
            $table->decimal('pa_diastolica', 6, 2)->nullable();
            $table->decimal('frecuencia_cardiaca', 6, 2)->nullable();
            $table->decimal('frecuencia_respiratoria', 6, 2)->nullable();
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->decimal('saturacion_oxigeno', 5, 2)->nullable();
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('talla', 6, 2)->nullable();
            $table->text('antecedentes_relevantes')->nullable();
            $table->text('medicacion_referida')->nullable();
            $table->text('alergias_referidas')->nullable();
            $table->string('dependencia_funcional', 60)->nullable();
            $table->string('riesgo_nutricional', 60)->nullable();
            $table->string('riesgo_cognitivo', 60)->nullable();
            $table->string('tipo_evaluacion_cognitiva', 80)->nullable();
            $table->text('necesidad_apoyo_inmediato')->nullable();
            $table->string('prioridad_sugerida', 20)->nullable();
            $table->boolean('confirmacion_documentacion')->default(false);
            $table->text('comentarios_adicionales')->nullable();
            $table->text('recomendacion_enfermeria')->nullable();

            $table->foreign('cod_preadmision')
                ->references('cod_preadmision')->on('preadmisiones')->restrictOnDelete();
            $table->foreign('cod_usuario_registro')
                ->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });

        if (Schema::hasColumn('preadmisiones', 'valoracion_enfermeria')) {
            DB::table('preadmisiones')
                ->whereNotNull('valoracion_enfermeria')
                ->orderBy('cod_preadmision')
                ->each(function (object $preadmision): void {
                    $datos = $this->decodeJson($preadmision->valoracion_enfermeria);
                    if ($datos === []) {
                        return;
                    }

                    DB::table('valoraciones_enfermeria_preadmision')->insert(
                        $this->normalizarRegistro($preadmision->cod_preadmision, $datos),
                    );
                });

            Schema::table('preadmisiones', function (Blueprint $table) {
                $table->dropColumn('valoracion_enfermeria');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('preadmisiones', 'valoracion_enfermeria')) {
            Schema::table('preadmisiones', function (Blueprint $table) {
                $table->json('valoracion_enfermeria')->nullable();
            });
        }

        DB::table('valoraciones_enfermeria_preadmision')
            ->orderBy('cod_preadmision')
            ->each(function (object $registro): void {
                $datos = (array) $registro;
                unset(
                    $datos['cod_valoracion_enfermeria'],
                    $datos['cod_preadmision'],
                    $datos['cod_usuario_registro'],
                );
                $datos['registrado_por'] = $registro->cod_usuario_registro;
                $datos['orientacion'] = sprintf(
                    'Persona: %s, Tiempo: %s, Espacio: %s',
                    $registro->orientacion_persona,
                    $registro->orientacion_tiempo,
                    $registro->orientacion_espacio,
                );

                DB::table('preadmisiones')
                    ->where('cod_preadmision', $registro->cod_preadmision)
                    ->update(['valoracion_enfermeria' => json_encode($datos)]);
            });

        Schema::dropIfExists('valoraciones_enfermeria_preadmision');
    }

    private function decodeJson(mixed $valor): array
    {
        if (is_array($valor)) {
            return $valor;
        }

        if (is_object($valor)) {
            return (array) $valor;
        }

        if (! is_string($valor) || trim($valor) === '') {
            return [];
        }

        $decodificado = json_decode($valor, true);

        return is_array($decodificado) ? $decodificado : [];
    }

    private function normalizarRegistro(string $codPreadmision, array $datos): array
    {
        $columnas = [
            'fecha_hora', 'estado_general', 'nivel_conciencia',
            'orientacion_persona', 'orientacion_tiempo', 'orientacion_espacio',
            'comunicacion', 'hay_dolor', 'intensidad_dolor', 'ubicacion_dolor',
            'movilidad', 'apoyo_movilidad', 'riesgo_caida', 'piel_estado',
            'hay_heridas', 'ubicacion_heridas', 'higiene_ingreso',
            'continencia_basica', 'alimentacion_aparente', 'pa_sistolica',
            'pa_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria',
            'temperatura', 'saturacion_oxigeno', 'peso', 'talla',
            'antecedentes_relevantes', 'medicacion_referida',
            'alergias_referidas', 'dependencia_funcional', 'riesgo_nutricional',
            'riesgo_cognitivo', 'tipo_evaluacion_cognitiva',
            'necesidad_apoyo_inmediato', 'prioridad_sugerida',
            'confirmacion_documentacion', 'comentarios_adicionales',
            'recomendacion_enfermeria',
        ];

        $registro = array_intersect_key($datos, array_flip($columnas));
        $registro['cod_valoracion_enfermeria'] = 'VEN_'.strtoupper(substr(sha1($codPreadmision), 0, 12));
        $registro['cod_preadmision'] = $codPreadmision;
        $registro['cod_usuario_registro'] = $datos['registrado_por'] ?? null;
        $registro['hay_dolor'] = filter_var($datos['hay_dolor'] ?? false, FILTER_VALIDATE_BOOL);
        $registro['hay_heridas'] = filter_var($datos['hay_heridas'] ?? false, FILTER_VALIDATE_BOOL);
        $registro['confirmacion_documentacion'] = filter_var(
            $datos['confirmacion_documentacion'] ?? false,
            FILTER_VALIDATE_BOOL,
        );

        return $registro;
    }
};
