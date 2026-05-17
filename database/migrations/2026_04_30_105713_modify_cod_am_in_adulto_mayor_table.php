<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE documentos_adulto_mayor DROP CONSTRAINT IF EXISTS documentos_adulto_mayor_cod_am_foreign');
        DB::statement('ALTER TABLE obs_adulto DROP CONSTRAINT IF EXISTS obs_adulto_cod_am_foreign');
        DB::statement('ALTER TABLE actividades_adulto DROP CONSTRAINT IF EXISTS actividades_adulto_cod_am_foreign');
        DB::statement('ALTER TABLE atenciones_adulto DROP CONSTRAINT IF EXISTS atenciones_adulto_cod_am_foreign');
        DB::statement('ALTER TABLE asignacion_voluntarios DROP CONSTRAINT IF EXISTS asignacion_voluntarios_cod_am_foreign');
        DB::statement('ALTER TABLE familiar_adulto DROP CONSTRAINT IF EXISTS familiar_adulto_cod_am_foreign');
        DB::statement("
            ALTER TABLE adulto_mayor
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");

        DB::statement("
            ALTER TABLE documentos_adulto_mayor
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");

        DB::statement("
            ALTER TABLE obs_adulto
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");

        DB::statement("
            ALTER TABLE actividades_adulto
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");

        DB::statement("
            ALTER TABLE atenciones_adulto
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");

        DB::statement("
            ALTER TABLE asignacion_voluntarios
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");
        DB::statement("
            ALTER TABLE familiar_adulto
            ALTER COLUMN cod_am TYPE VARCHAR(10)
            USING ('AM_' || LPAD(cod_am::text, 4, '0'))
        ");

        DB::statement('ALTER TABLE documentos_adulto_mayor ADD CONSTRAINT documentos_adulto_mayor_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am)');
        DB::statement('ALTER TABLE obs_adulto ADD CONSTRAINT obs_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am)');
        DB::statement('ALTER TABLE actividades_adulto ADD CONSTRAINT actividades_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am)');
        DB::statement('ALTER TABLE atenciones_adulto ADD CONSTRAINT atenciones_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am)');
        DB::statement('ALTER TABLE asignacion_voluntarios ADD CONSTRAINT asignacion_voluntarios_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am)');
        DB::statement('ALTER TABLE familiar_adulto ADD CONSTRAINT familiar_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES adulto_mayor(cod_am)');
        }

    public function down(): void
    {
        DB::statement('ALTER TABLE documentos_adulto_mayor DROP CONSTRAINT IF EXISTS documentos_adulto_mayor_cod_am_foreign');
        DB::statement('ALTER TABLE obs_adulto DROP CONSTRAINT IF EXISTS obs_adulto_cod_am_foreign');
        DB::statement('ALTER TABLE actividades_adulto DROP CONSTRAINT IF EXISTS actividades_adulto_cod_am_foreign');
        DB::statement('ALTER TABLE atenciones_adulto DROP CONSTRAINT IF EXISTS atenciones_adulto_cod_am_foreign');
        DB::statement('ALTER TABLE asignacion_voluntarios DROP CONSTRAINT IF EXISTS asignacion_voluntarios_cod_am_foreign');
    }
};