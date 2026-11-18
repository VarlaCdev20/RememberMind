<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            (require __DIR__.'/2026_11_17_000002_consolidate_controles_observaciones.php')->down();
            (require __DIR__.'/2026_11_14_000001_consolidate_valoraciones_enfermeria_preadmision.php')->down();
            $this->protegerIadl();
            $this->protegerValoracion();
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE controles_funcionales_iadl DROP CONSTRAINT iadl_atencion_residente');
                DB::statement('DROP TRIGGER valoracion_inicial_preservar ON valoraciones_enfermeria_preadmision');
                DB::statement('DROP FUNCTION valoracion_inicial_preservar()');
            } else {
                foreach (['iadl_atencion_residente_ins', 'iadl_atencion_residente_upd', 'iadl_atencion_residente_padre', 'valoracion_inicial_preservar_upd', 'valoracion_inicial_preservar_del'] as $trigger) {
                    DB::statement('DROP TRIGGER '.$trigger);
                }
            }

            (require __DIR__.'/2026_11_14_000001_consolidate_valoraciones_enfermeria_preadmision.php')->up();
            (require __DIR__.'/2026_11_17_000002_consolidate_controles_observaciones.php')->up();
        });
    }

    private function protegerIadl(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE controles_funcionales_iadl ADD CONSTRAINT iadl_atencion_residente FOREIGN KEY (cod_atencion, cod_residente) REFERENCES atenciones(cod_atencion, cod_residente) ON DELETE RESTRICT');

            return;
        }

        if (DB::table('controles_funcionales_iadl as i')->join('atenciones as a', 'a.cod_atencion', '=', 'i.cod_atencion')
            ->whereColumn('i.cod_residente', '<>', 'a.cod_residente')->exists()) {
            throw new RuntimeException('IADL contiene una atención de otro residente.');
        }

        foreach (['INSERT' => 'ins', 'UPDATE OF cod_atencion, cod_residente' => 'upd'] as $evento => $sufijo) {
            DB::unprepared('CREATE TRIGGER iadl_atencion_residente_'.$sufijo.' BEFORE '.$evento.' ON controles_funcionales_iadl
                FOR EACH ROW WHEN NEW.cod_atencion IS NOT NULL AND NOT EXISTS (
                    SELECT 1 FROM atenciones WHERE cod_atencion = NEW.cod_atencion AND cod_residente = NEW.cod_residente
                ) BEGIN SELECT RAISE(ABORT, \'La atención IADL debe corresponder al residente\'); END');
        }
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER iadl_atencion_residente_padre BEFORE UPDATE OF cod_residente ON atenciones
            FOR EACH ROW WHEN EXISTS (
                SELECT 1 FROM controles_funcionales_iadl WHERE cod_atencion = OLD.cod_atencion AND cod_residente <> NEW.cod_residente
            ) BEGIN SELECT RAISE(ABORT, 'La atención ya pertenece a un registro IADL del residente'); END
            SQL);
    }

    private function protegerValoracion(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(<<<'SQL'
                CREATE FUNCTION valoracion_inicial_preservar() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF TG_OP = 'DELETE' THEN
                        RAISE EXCEPTION 'La valoración de Enfermería conserva su historial' USING ERRCODE = '23503';
                    ELSIF NEW.cod_valoracion_enfermeria IS DISTINCT FROM OLD.cod_valoracion_enfermeria
                        OR NEW.cod_preadmision IS DISTINCT FROM OLD.cod_preadmision THEN
                        RAISE EXCEPTION 'La identidad de la valoración de Enfermería no puede cambiar' USING ERRCODE = '23514';
                    END IF;
                    RETURN NEW;
                END;
                $$;
                CREATE TRIGGER valoracion_inicial_preservar BEFORE UPDATE OR DELETE ON valoraciones_enfermeria_preadmision
                FOR EACH ROW EXECUTE FUNCTION valoracion_inicial_preservar();
                SQL);

            return;
        }

        DB::unprepared(<<<'SQL'
            CREATE TRIGGER valoracion_inicial_preservar_del BEFORE DELETE ON valoraciones_enfermeria_preadmision
            BEGIN SELECT RAISE(ABORT, 'La valoración de Enfermería conserva su historial'); END;
            CREATE TRIGGER valoracion_inicial_preservar_upd BEFORE UPDATE ON valoraciones_enfermeria_preadmision
            FOR EACH ROW WHEN NEW.cod_valoracion_enfermeria IS NOT OLD.cod_valoracion_enfermeria
                OR NEW.cod_preadmision IS NOT OLD.cod_preadmision
            BEGIN SELECT RAISE(ABORT, 'La identidad de la valoración de Enfermería no puede cambiar'); END;
            SQL);
    }
};
