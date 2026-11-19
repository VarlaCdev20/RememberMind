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
                // Descripciones generales de las fichas DailyMed y del índice de medicamentos del NHS.
                $uso = 'Uso habitual: '.match ($code) {
                    'N0208' => 'Alivio del dolor y reducción de la fiebre.',
                    'A1007', 'A1004' => 'Control de glucemia en diabetes mellitus tipo 2.',
                    'C0901', 'C0902', 'C0807', 'C0306', 'C0706' => 'Tratamiento de la hipertensión arterial.',
                    'C1001', 'C1005' => 'Reducción del colesterol LDL en personas con indicación médica.',
                    'C0304', 'C0303' => 'Tratamiento de la retención de líquidos en situaciones indicadas.',
                    'H0303', 'H0301' => 'Reposición de hormona tiroidea en hipotiroidismo.',
                    'A0202' => 'Disminución de la acidez gástrica, por ejemplo en reflujo.',
                    'B0301' => 'Tratamiento de la deficiencia de folato.',
                    'B0306' => 'Tratamiento de la deficiencia de hierro.',
                    'M0503' => 'Tratamiento de la osteoporosis.',
                    'B0101' => 'Prevención secundaria de eventos vasculares en personas con indicación médica.',
                    'D0103' => 'Tratamiento tópico de determinadas infecciones fúngicas de la piel.',
                };
                // Conservar la identidad del medicamento que ya tiene órdenes ajenas al dataset.
                $existing = DB::table('medicamentos')->where('concentracion', $strength)
                    ->whereIn('nombre_generico', $name === 'Losartán' ? [$name, 'Losartán Potásico'] : [$name])->first();
                if ($existing) {
                    $observacion = trim((string) $existing->observacion);
                    if (! str_contains($observacion, 'Uso habitual:')) {
                        DB::table('medicamentos')->where('cod_medicamento', $existing->cod_medicamento)
                            ->update(['observacion' => trim($observacion.' '.$uso)]);
                    }
                    continue;
                }
                $id = app(\App\Compartido\Infraestructura\Persistencia\GeneradorCodigo::class)->siguiente('medicamentos');
                DB::table('medicamentos')->insert([
                    'cod_medicamento' => $id, 'nombre_generico' => $name,
                    'nombre_comercial' => null, 'concentracion' => $strength,
                    'forma_farmaceutica' => $form, 'unidad' => $unit,
                    'via_predeterminada' => $route, 'control_especial' => false, 'estado' => 'ACTIVO',
                    'observacion' => 'Presentación verificada en LINAME 2026–2027, AGEMED CR/29/2026 (07/09/2026), código '.$code.'. '.$uso,
                ]);
            }
        });
    }
}
