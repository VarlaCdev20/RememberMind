<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use App\Models\HorarioPrescripcion;
use App\Models\AdultoMayor;
use App\Models\Atencion;
use App\Models\Personal;
use Carbon\Carbon;

class AsignarMedicacionResidenteSeeder extends Seeder
{
    public function run(): void
    {
        $residente = AdultoMayor::first();
        if (!$residente) {
            $this->command->error('No se encontro ningun residente en la base de datos.');
            return;
        }

        $atencion = Atencion::where('cod_residente', $residente->cod_residente)->first();
        $personal = Personal::first();

        // 1. Medicamento: Losartan Potasico 50 mg
        $losartan = Medicamento::firstOrCreate(
            ['cod_medicamento' => 'MED_0001'],
            [
                'nombre_generico' => 'Losartan Potasico',
                'nombre_comercial' => 'Cozaar',
                'concentracion' => '50 mg',
                'forma_farmaceutica' => 'Comprimido recubierto',
                'unidad' => 'mg',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
                'observacion' => 'Antihipertensivo bloqueador de receptores de angiotensina II'
            ]
        );

        // 2. Medicamento: Metformina 850 mg
        $metformina = Medicamento::firstOrCreate(
            ['cod_medicamento' => 'MED_0002'],
            [
                'nombre_generico' => 'Metformina Clorhidrato',
                'nombre_comercial' => 'Glucophage',
                'concentracion' => '850 mg',
                'forma_farmaceutica' => 'Comprimido recubierto',
                'unidad' => 'mg',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
                'observacion' => 'Antidiabetico oral normoglucemiante'
            ]
        );

        // 3. Medicamento: Omeprazol 20 mg
        $omeprazol = Medicamento::firstOrCreate(
            ['cod_medicamento' => 'MED_0003'],
            [
                'nombre_generico' => 'Omeprazol',
                'nombre_comercial' => 'Prilosec',
                'concentracion' => '20 mg',
                'forma_farmaceutica' => 'Capsula',
                'unidad' => 'mg',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
                'observacion' => 'Inhibidor de la bomba de protones / Protector gastrico'
            ]
        );

        // Prescripcion 1: Losartan 50 mg
        $prs1 = Prescripcion::firstOrCreate(
            ['cod_prescripcion' => 'PRS_0001'],
            [
                'cod_residente' => $residente->cod_residente,
                'cod_atencion' => $atencion?->cod_atencion ?? 'ATN_0001',
                'cod_medicamento' => 'MED_0001',
                'cod_personal' => $personal?->cod_personal ?? 'PER_0001',
                'dosis' => 50.000,
                'unidad_dosis' => 'mg',
                'via_administracion' => 'ORAL',
                'frecuencia' => 'Cada 12 horas',
                'indicacion' => 'Tomar 1 comprimido via oral cada 12 horas con las comidas.',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => Carbon::now()->subDays(10),
                'estado' => 'ACTIVA',
                'observacion' => 'Tratamiento continuo para hipertension arterial esencial'
            ]
        );

        HorarioPrescripcion::updateOrCreate(
            ['cod_horario_prescripcion' => 'HPR_0001'],
            [
                'cod_prescripcion' => 'PRS_0001',
                'hora_programada' => '08:00:00',
                'dosis_programada' => 50.000,
                'dias_semana' => 'LUN,MAR,MIE,JUE,VIE,SAB,DOM',
                'estado' => 'ACTIVO'
            ]
        );

        HorarioPrescripcion::updateOrCreate(
            ['cod_horario_prescripcion' => 'HPR_0002'],
            [
                'cod_prescripcion' => 'PRS_0001',
                'hora_programada' => '20:00:00',
                'dosis_programada' => 50.000,
                'dias_semana' => 'LUN,MAR,MIE,JUE,VIE,SAB,DOM',
                'estado' => 'ACTIVO'
            ]
        );

        // Prescripcion 2: Metformina 850 mg
        $prs2 = Prescripcion::firstOrCreate(
            ['cod_prescripcion' => 'PRS_0002'],
            [
                'cod_residente' => $residente->cod_residente,
                'cod_atencion' => $atencion?->cod_atencion ?? 'ATN_0001',
                'cod_medicamento' => 'MED_0002',
                'cod_personal' => $personal?->cod_personal ?? 'PER_0001',
                'dosis' => 850.000,
                'unidad_dosis' => 'mg',
                'via_administracion' => 'ORAL',
                'frecuencia' => 'Cada 24 horas',
                'indicacion' => 'Tomar 1 comprimido via oral durante el desayuno.',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => Carbon::now()->subDays(10),
                'estado' => 'ACTIVA',
                'observacion' => 'Control de diabetes mellitus tipo 2'
            ]
        );

        HorarioPrescripcion::updateOrCreate(
            ['cod_horario_prescripcion' => 'HPR_0003'],
            [
                'cod_prescripcion' => 'PRS_0002',
                'hora_programada' => '08:00:00',
                'dosis_programada' => 850.000,
                'dias_semana' => 'LUN,MAR,MIE,JUE,VIE,SAB,DOM',
                'estado' => 'ACTIVO'
            ]
        );

        // Prescripcion 3: Omeprazol 20 mg
        $prs3 = Prescripcion::firstOrCreate(
            ['cod_prescripcion' => 'PRS_0003'],
            [
                'cod_residente' => $residente->cod_residente,
                'cod_atencion' => $atencion?->cod_atencion ?? 'ATN_0001',
                'cod_medicamento' => 'MED_0003',
                'cod_personal' => $personal?->cod_personal ?? 'PER_0001',
                'dosis' => 20.000,
                'unidad_dosis' => 'mg',
                'via_administracion' => 'ORAL',
                'frecuencia' => 'Cada 24 horas',
                'indicacion' => 'Tomar 1 capsula en ayunas 30 minutos antes del desayuno.',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => Carbon::now()->subDays(10),
                'estado' => 'ACTIVA',
                'observacion' => 'Gastroproteccion'
            ]
        );

        HorarioPrescripcion::updateOrCreate(
            ['cod_horario_prescripcion' => 'HPR_0004'],
            [
                'cod_prescripcion' => 'PRS_0003',
                'hora_programada' => '07:00:00',
                'dosis_programada' => 20.000,
                'dias_semana' => 'LUN,MAR,MIE,JUE,VIE,SAB,DOM',
                'estado' => 'ACTIVO'
            ]
        );

        $this->command->info('Prescripciones asignadas exitosamente al paciente.');
    }
}
