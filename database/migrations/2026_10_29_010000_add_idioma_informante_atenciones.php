<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Tres atributos por episodio y FK opcional autorizados el 29/10/2026. */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $motor = DB::getDriverName();
            if (! in_array($motor, ['sqlite', 'pgsql'], true)) {
                throw new LogicException('Motor no admitido para esta extensión.');
            }
            DB::statement("ALTER TABLE atenciones ADD COLUMN idioma_principal_evaluacion VARCHAR(80) NULL
                CONSTRAINT atenciones_idioma_longitud CHECK(idioma_principal_evaluacion IS NULL OR length(trim(idioma_principal_evaluacion)) BETWEEN 1 AND 80)");
            DB::statement('ALTER TABLE atenciones ADD COLUMN cod_residente_contacto_informante VARCHAR(20) NULL
                CONSTRAINT atenciones_informante_fk REFERENCES residentes_contactos(cod_residente_contacto) ON DELETE RESTRICT');
            $control = $motor === 'sqlite' ? ' AND informante_conoce_nivel_previo IN (0,1)' : '';
            DB::statement("ALTER TABLE atenciones ADD COLUMN informante_conoce_nivel_previo BOOLEAN NULL
                CONSTRAINT atenciones_informante_contexto CHECK(informante_conoce_nivel_previo IS NULL OR (cod_residente_contacto_informante IS NOT NULL{$control}))");
            DB::statement('CREATE INDEX atenciones_informante_idx ON atenciones(cod_residente_contacto_informante)');
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement("SET LOCAL lock_timeout = '10s'");
                DB::statement('LOCK TABLE atenciones IN ACCESS EXCLUSIVE MODE');
            } else {
                DB::statement('UPDATE atenciones SET idioma_principal_evaluacion=idioma_principal_evaluacion WHERE 1=0');
            }
            if (DB::table('atenciones')->whereNotNull('idioma_principal_evaluacion')
                ->orWhereNotNull('cod_residente_contacto_informante')->orWhereNotNull('informante_conoce_nivel_previo')->exists()) {
                throw new LogicException('El contexto clínico conservado no se elimina por rollback.');
            }
            DB::statement('DROP INDEX atenciones_informante_idx');
            foreach (['informante_conoce_nivel_previo', 'cod_residente_contacto_informante', 'idioma_principal_evaluacion'] as $campo) {
                DB::statement('ALTER TABLE atenciones DROP COLUMN '.$campo);
            }
        });
    }
};
