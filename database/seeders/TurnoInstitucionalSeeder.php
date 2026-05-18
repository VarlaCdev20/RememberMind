<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TurnoInstitucional;

class TurnoInstitucionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turnos = [
            [
                'cod_turno' => 'TUR_0001',
                'nombre' => 'Turno Mañana',
                'hora_inicio' => '08:00:00',
                'hora_fin' => '14:00:00',
                'descripcion' => 'Horario matutino para personal administrativo y de salud.',
                'color' => '#3B82F6', // Blue
                'estado' => 'ACTIVO',
                'observaciones' => 'Turno principal de cobertura matutina.',
            ],
            [
                'cod_turno' => 'TUR_0002',
                'nombre' => 'Turno Tarde',
                'hora_inicio' => '14:00:00',
                'hora_fin' => '20:00:00',
                'descripcion' => 'Horario vespertino para personal administrativo, de salud y cuidadores.',
                'color' => '#F97316', // Orange
                'estado' => 'ACTIVO',
                'observaciones' => 'Turno principal de cobertura vespertina.',
            ],
            [
                'cod_turno' => 'TUR_0003',
                'nombre' => 'Turno Completo',
                'hora_inicio' => '08:00:00',
                'hora_fin' => '17:00:00',
                'descripcion' => 'Jornada laboral completa estándar de 8 horas más descanso.',
                'color' => '#10B981', // Green
                'estado' => 'ACTIVO',
                'observaciones' => 'Aplica principalmente a personal administrativo y operativo.',
            ],
            [
                'cod_turno' => 'TUR_0004',
                'nombre' => 'Turno Apoyo Voluntario',
                'hora_inicio' => null,
                'hora_fin' => null,
                'descripcion' => 'Horario flexible especial para voluntariado y acompañamiento.',
                'color' => '#8B5CF6', // Purple
                'estado' => 'ACTIVO',
                'observaciones' => 'Horas coordinadas directamente según disponibilidad.',
            ],
            [
                'cod_turno' => 'TUR_0005',
                'nombre' => 'Turno Flexible',
                'hora_inicio' => null,
                'hora_fin' => null,
                'descripcion' => 'Horario flexible para apoyo temporal o profesionales externos.',
                'color' => '#14B8A6', // Teal
                'estado' => 'ACTIVO',
                'observaciones' => 'Horas dinámicas a registrar bajo requerimiento.',
            ]
        ];

        foreach ($turnos as $turno) {
            TurnoInstitucional::firstOrCreate(
                ['cod_turno' => $turno['cod_turno']],
                $turno
            );
        }
    }
}
