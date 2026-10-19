<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Dictámenes de Psicología y Medicina, separados e inmutables. Extensión aprobada el 17/10/2026. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisiones_evaluacion_experta', function (Blueprint $t): void {
            $t->string('cod_revision_experta', 20)->primary();
            $t->string('cod_evaluacion_experta', 20);
            $t->string('disciplina', 20);
            $t->string('cod_personal', 20);
            $t->string('cod_usuario', 20);
            $t->dateTime('fecha_hora_revision');
            $t->string('decision', 20);
            $t->text('justificacion');
            $t->string('cod_revision_anterior', 20)->nullable();
            $t->unique(['cod_revision_experta', 'cod_evaluacion_experta', 'disciplina'], 'rev_contexto_uq');
            $t->unique('cod_revision_anterior', 'rev_sucesora_uq');
            $t->index(['cod_evaluacion_experta', 'disciplina'], 'rev_evaluacion_disciplina_ix');
            $t->index('cod_personal', 'rev_personal_ix');
            $t->index('cod_usuario', 'rev_usuario_ix');
            $t->foreign('cod_evaluacion_experta')->references('cod_evaluacion_experta')->on('evaluaciones_expertas')->restrictOnDelete();
            $t->foreign('cod_personal')->references('cod_personal')->on('personal')->restrictOnDelete();
            $t->foreign('cod_usuario')->references('cod_usuario')->on('usuarios')->restrictOnDelete();
        });
        Schema::table('revisiones_evaluacion_experta', function (Blueprint $t): void {
            $t->foreign(['cod_revision_anterior', 'cod_evaluacion_experta', 'disciplina'], 'rev_anterior_contexto_fk')
                ->references(['cod_revision_experta', 'cod_evaluacion_experta', 'disciplina'])->on('revisiones_evaluacion_experta')->restrictOnDelete();
        });
        DB::statement('CREATE UNIQUE INDEX rev_primera_uq ON revisiones_evaluacion_experta (cod_evaluacion_experta, disciplina) WHERE cod_revision_anterior IS NULL');
        $condicion = "disciplina IN ('MEDICINA','PSICOLOGIA') AND decision IN ('CONFIRMADA','RECHAZADA') AND length(trim(justificacion)) > 0 AND (cod_revision_anterior IS NULL OR cod_revision_anterior <> cod_revision_experta)";
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE revisiones_evaluacion_experta ADD CONSTRAINT rev_contrato_ck CHECK ('.$condicion.')');
            DB::unprepared("CREATE OR REPLACE FUNCTION rev_impedir_mutacion() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN RAISE EXCEPTION 'Las revisiones profesionales son inmutables'; END; \$\$");
            DB::unprepared('CREATE TRIGGER rev_inmutable BEFORE UPDATE OR DELETE ON revisiones_evaluacion_experta FOR EACH ROW EXECUTE FUNCTION rev_impedir_mutacion()');
        } elseif (DB::getDriverName() === 'sqlite') {
            foreach (['INSERT', 'UPDATE'] as $evento) {
                $regla = preg_replace('/\b(disciplina|decision|justificacion|cod_revision_anterior|cod_revision_experta)\b/', 'NEW.$1', $condicion);
                DB::unprepared('CREATE TRIGGER rev_contrato_'.strtolower($evento).' BEFORE '.$evento.' ON revisiones_evaluacion_experta WHEN NOT ('.$regla.") BEGIN SELECT RAISE(ABORT, 'Revision incompatible'); END");
            }
            foreach (['UPDATE', 'DELETE'] as $evento) {
                DB::unprepared('CREATE TRIGGER rev_inmutable_'.strtolower($evento).' BEFORE '.$evento." ON revisiones_evaluacion_experta BEGIN SELECT RAISE(ABORT, 'Las revisiones profesionales son inmutables'); END");
            }
        } else {
            throw new RuntimeException('La revisión experta requiere un motor de persistencia soportado.');
        }
    }

    public function down(): void
    {
        if (DB::table('revisiones_evaluacion_experta')->exists()) {
            throw new RuntimeException('No se retiran revisiones profesionales guardadas.');
        }
        Schema::dropIfExists('revisiones_evaluacion_experta');
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP FUNCTION IF EXISTS rev_impedir_mutacion()');
        }
    }
};
