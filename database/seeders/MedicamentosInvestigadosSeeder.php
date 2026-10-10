<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MedicamentosInvestigadosSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('El catálogo investigado se carga explícitamente en desarrollo/testing.');
        }

        DB::transaction(function (): void {
            foreach (require __DIR__.'/data/medicamentos_investigados.php' as [$code, $name, $strength, $form, $unit, $route]) {
                $id = 'MED_LIN_'.$code;
                // Conservar la identidad del medicamento que ya tiene órdenes ajenas al dataset.
                $existing = DB::table('medicamentos')->where('concentracion', $strength)
                    ->whereIn('nombre_generico', $name === 'Losartán' ? [$name, 'Losartán Potásico'] : [$name])->first();
                if ($existing) {
                    continue;
                }
                if (DB::table('medicamentos')->where('cod_medicamento', $id)->exists()) {
                    throw new RuntimeException('Colisión de identidad del catálogo LINAME: '.$code);
                }
                DB::table('medicamentos')->insert([
                    'cod_medicamento' => $id, 'nombre_generico' => $name,
                    'nombre_comercial' => null, 'concentracion' => $strength,
                    'forma_farmaceutica' => $form, 'unidad' => $unit,
                    'via_predeterminada' => $route, 'control_especial' => false, 'estado' => 'ACTIVO',
                    'observacion' => 'Presentación verificada en LINAME 2026–2027, AGEMED CR/29/2026 (07/09/2026), código '.$code.'. La concentración no establece una pauta de tratamiento.',
                ]);
            }
        });
    }
}
