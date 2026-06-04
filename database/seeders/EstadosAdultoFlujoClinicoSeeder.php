<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstadosAdultoFlujoClinicoSeeder extends Seeder
{
    /**
     * Agrega los estados del flujo clínico sin eliminar los existentes.
     * Usa updateOrInsert para ser idempotente.
     */
    public function run(): void
    {
        $nuevosEstados = [
            // ── Flujo de admisión ───────────────────────────────────────────────
            ['estado' => 'PREADMISION',                      'descripcion' => 'Ficha en proceso de preadmisión'],
            ['estado' => 'PENDIENTE_VALORACION_ENFERMERIA',  'descripcion' => 'En espera de valoración de enfermería'],
            ['estado' => 'VALORACION_ENFERMERIA_COMPLETADA', 'descripcion' => 'Valoración de enfermería completada'],
            ['estado' => 'PENDIENTE_VALORACION_MEDICA',      'descripcion' => 'En espera de valoración médica'],
            ['estado' => 'VALORACION_MEDICA_COMPLETADA',     'descripcion' => 'Valoración médica completada'],
            // ── Resultado de admisión ───────────────────────────────────────────
            ['estado' => 'ADMITIDO',                         'descripcion' => 'Adulto mayor admitido'],
            ['estado' => 'NO_ADMITIDO',                      'descripcion' => 'No cumple criterios de admisión'],
            ['estado' => 'DERIVADO',                         'descripcion' => 'Derivado a otra institución'],
            ['estado' => 'OBSERVADO',                        'descripcion' => 'En período de observación'],
            // ── Flujo operativo ─────────────────────────────────────────────────
            ['estado' => 'ASIGNADO',                         'descripcion' => 'Asignado a habitación, cama y turno'],
            ['estado' => 'EN_SEGUIMIENTO_ACTIVO',            'descripcion' => 'En seguimiento clínico activo'],
            // ── Salida ──────────────────────────────────────────────────────────
            ['estado' => 'EGRESADO',                         'descripcion' => 'Alta o egreso del centro'],
            ['estado' => 'ARCHIVADO',                        'descripcion' => 'Ficha archivada'],
        ];

        // Verificar si la columna descripcion existe, si no, omitirla
        $cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name='estado_adulto'");
        $colNames = array_column($cols, 'column_name');
        $tieneDesc = in_array('descripcion', $colNames);

        // Sincronizar la secuencia de PostgreSQL con el máximo actual
        $maxId = DB::table('estado_adulto')->max('cod_est_adul') ?? 0;
        DB::statement("SELECT setval(pg_get_serial_sequence('estado_adulto', 'cod_est_adul'), {$maxId})");
        $this->command->line("  Secuencia sincronizada al máximo: {$maxId}");

        foreach ($nuevosEstados as $estadoData) {
            $estado = $estadoData['estado'];

            $exists = DB::table('estado_adulto')->where('estado', $estado)->exists();
            if (! $exists) {
                $insert = ['estado' => $estado, 'created_at' => now(), 'updated_at' => now()];
                if ($tieneDesc) {
                    $insert['descripcion'] = $estadoData['descripcion'];
                }
                DB::table('estado_adulto')->insert($insert);
                $this->command->line("  Estado agregado: {$estado}");
            } else {
                $this->command->line("  Estado existente (omitido): {$estado}");
            }
        }

        $this->command->info('Estados del flujo clínico sincronizados en estado_adulto.');
    }
}
