<?php

namespace Database\Seeders;

use App\Models\AdultoMayor;
use App\Models\AsignacionVoluntario;
use App\Models\DisponibilidadVoluntario;
use App\Models\Voluntario;
use Illuminate\Database\Seeder;

class VoluntariosSeeder extends Seeder
{
    public function run(): void
    {
        $voluntariosData = [
            [
                'nombres'               => 'ANDREA PATRICIA',
                'ap_paterno'            => 'GUTIERREZ',
                'ap_materno'            => 'LUNA',
                'ci'                    => '6677889-VOL',
                'celular'               => '75011223',
                'correo'                => 'andrea.gutierrez.vol@email.com',
                'fecha_nac'             => '1990-06-15',
                'profesion_ocupacion'   => 'Enfermera auxiliar en formación',
                'area_apoyo_preferente' => 'Cuidado personal y salud básica',
                'disponibilidad'        => [
                    ['dia' => 'LUNES',     'inicio' => '09:00:00', 'fin' => '12:00:00'],
                    ['dia' => 'MIERCOLES', 'inicio' => '09:00:00', 'fin' => '12:00:00'],
                    ['dia' => 'VIERNES',   'inicio' => '09:00:00', 'fin' => '12:00:00'],
                ],
                'meses_atras' => 5,
            ],
            [
                'nombres'               => 'CARLOS EDUARDO',
                'ap_paterno'            => 'PINTO',
                'ap_materno'            => 'SERRANO',
                'ci'                    => '7788900-VOL',
                'celular'               => '75122334',
                'correo'                => 'carlos.pinto.vol@email.com',
                'fecha_nac'             => '1955-02-20',
                'profesion_ocupacion'   => 'Profesor jubilado',
                'area_apoyo_preferente' => 'Estimulación cognitiva y lectura',
                'disponibilidad'        => [
                    ['dia' => 'MARTES',   'inicio' => '10:00:00', 'fin' => '13:00:00'],
                    ['dia' => 'JUEVES',   'inicio' => '10:00:00', 'fin' => '13:00:00'],
                    ['dia' => 'SABADO',   'inicio' => '09:00:00', 'fin' => '12:00:00'],
                ],
                'meses_atras' => 4,
            ],
            [
                'nombres'               => 'SILVANA BEATRIZ',
                'ap_paterno'            => 'ROCA',
                'ap_materno'            => 'IBAÑEZ',
                'ci'                    => '8899011-VOL',
                'celular'               => '75233445',
                'correo'                => 'silvana.roca.vol@email.com',
                'fecha_nac'             => '1998-09-10',
                'profesion_ocupacion'   => 'Estudiante de Trabajo Social — 5to año',
                'area_apoyo_preferente' => 'Acompañamiento emocional y actividades lúdicas',
                'disponibilidad'        => [
                    ['dia' => 'VIERNES', 'inicio' => '14:00:00', 'fin' => '17:00:00'],
                    ['dia' => 'SABADO',  'inicio' => '09:00:00', 'fin' => '12:00:00'],
                    ['dia' => 'DOMINGO', 'inicio' => '10:00:00', 'fin' => '13:00:00'],
                ],
                'meses_atras' => 3,
            ],
            [
                'nombres'               => 'MARCOS ANTONIO',
                'ap_paterno'            => 'ZEBALLOS',
                'ap_materno'            => 'BARRIGA',
                'ci'                    => '9900122-VOL',
                'celular'               => '75344556',
                'correo'                => 'marcos.zeballos.vol@email.com',
                'fecha_nac'             => '1972-12-05',
                'profesion_ocupacion'   => 'Músico y compositor',
                'area_apoyo_preferente' => 'Musicoterapia y actividades artísticas',
                'disponibilidad'        => [
                    ['dia' => 'MIERCOLES', 'inicio' => '15:00:00', 'fin' => '18:00:00'],
                    ['dia' => 'VIERNES',   'inicio' => '15:00:00', 'fin' => '18:00:00'],
                    ['dia' => 'SABADO',    'inicio' => '14:00:00', 'fin' => '17:00:00'],
                ],
                'meses_atras' => 6,
            ],
            [
                'nombres'               => 'NATALIA FERNANDA',
                'ap_paterno'            => 'BUSTAMANTE',
                'ap_materno'            => 'VARGAS',
                'ci'                    => '0011233-VOL',
                'celular'               => '75455667',
                'correo'                => 'natalia.bustamante.vol@email.com',
                'fecha_nac'             => '2000-04-18',
                'profesion_ocupacion'   => 'Estudiante de Fisioterapia — 4to año',
                'area_apoyo_preferente' => 'Ejercicios de movilidad y rehabilitación',
                'disponibilidad'        => [
                    ['dia' => 'LUNES',  'inicio' => '14:00:00', 'fin' => '17:00:00'],
                    ['dia' => 'JUEVES', 'inicio' => '14:00:00', 'fin' => '17:00:00'],
                    ['dia' => 'SABADO', 'inicio' => '08:00:00', 'fin' => '11:00:00'],
                ],
                'meses_atras' => 2,
            ],
        ];

        $adultos = AdultoMayor::where('ci', 'like', '%-DEMO')->get();

        foreach ($voluntariosData as $idx => $vData) {
            $vol = Voluntario::firstWhere('ci', $vData['ci']);
            if (! $vol) {
                $vol = Voluntario::create([
                    'nombres'               => $vData['nombres'],
                    'ap_paterno'            => $vData['ap_paterno'],
                    'ap_materno'            => $vData['ap_materno'],
                    'ci'                    => $vData['ci'],
                    'celular'               => $vData['celular'],
                    'correo'                => $vData['correo'],
                    'fecha_nac'             => $vData['fecha_nac'],
                    'fecha_ing'             => now()->subMonths($vData['meses_atras'])->toDateString(),
                    'profesion_ocupacion'   => $vData['profesion_ocupacion'],
                    'area_apoyo_preferente' => $vData['area_apoyo_preferente'],
                    'estado'                => 'ACTIVO',
                ]);
            }

            foreach ($vData['disponibilidad'] as $disp) {
                DisponibilidadVoluntario::firstOrCreate(
                    ['cod_vol' => $vol->cod_vol, 'dia_semana' => $disp['dia']],
                    [
                        'hora_inicio' => $disp['inicio'],
                        'hora_fin'    => $disp['fin'],
                        'estado'      => 'ACTIVO',
                    ]
                );
            }

            if ($adultos->isNotEmpty()) {
                $adulto = $adultos[$idx % $adultos->count()];
                AsignacionVoluntario::firstOrCreate(
                    ['cod_vol' => $vol->cod_vol, 'cod_am' => $adulto->cod_am],
                    [
                        'fecha_asig' => now()->subMonths($vData['meses_atras'])->toDateString(),
                        'estado'     => 'ACTIVA',
                        'obser'      => "Voluntario asignado según disponibilidad y perfil. Área: {$vData['area_apoyo_preferente']}.",
                    ]
                );
            }
        }

        $this->command->info('[VoluntariosSeeder] 5 voluntarios creados con disponibilidades y asignaciones.');
    }
}
