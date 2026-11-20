<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ANTERIOR = 'hechos_instantanea_experta';
    private const ACTUAL = 'hechos_evaluacion_experta';

    public function up(): void
    {
        $this->renombrar(self::ANTERIOR, self::ACTUAL);
    }

    public function down(): void
    {
        $this->renombrar(self::ACTUAL, self::ANTERIOR);
    }

    private function renombrar(string $origen, string $destino): void
    {
        DB::transaction(function () use ($origen, $destino): void {
            Schema::rename($origen, $destino);
            DB::table('secuencias_codigos')->where('entidad', $origen)->update(['entidad' => $destino]);

            // PostgreSQL conserva las FK y los triggers por identidad, pero sus funciones guardan SQL textual.
            if (DB::getDriverName() === 'pgsql') {
                $funciones = DB::select(<<<'SQL'
                    SELECT pg_get_functiondef(p.oid) AS definicion
                    FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
                    WHERE n.nspname = current_schema() AND p.prorettype = 'trigger'::regtype
                      AND p.prosrc ~ ?
                    SQL, ['\\m'.$origen.'\\M']);
                foreach ($funciones as $funcion) {
                    DB::unprepared(preg_replace('/\b'.$origen.'\b/', $destino, $funcion->definicion));
                }
            }
        });
    }
};
