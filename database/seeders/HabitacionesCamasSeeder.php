<?php

namespace Database\Seeders;

use App\Models\Cama;
use App\Models\Habitacion;
use Illuminate\Database\Seeder;

class HabitacionesCamasSeeder extends Seeder
{
    public function run(): void
    {
        $habitaciones = [
            [
                'codigo'          => 'HAB-A01',
                'nombre'          => 'Habitación A-01',
                'tipo_habitacion' => 'INDIVIDUAL',
                'ubicacion'       => 'Planta Baja — Ala A',
                'capacidad'       => 1,
                'observaciones'   => 'Con baño privado. Adaptada para adultos con movilidad reducida.',
                'camas'           => ['CAM-A01-01'],
            ],
            [
                'codigo'          => 'HAB-A02',
                'nombre'          => 'Habitación A-02',
                'tipo_habitacion' => 'DOBLE',
                'ubicacion'       => 'Planta Baja — Ala A',
                'capacidad'       => 2,
                'observaciones'   => 'Habitación doble compartida. Baño compartido.',
                'camas'           => ['CAM-A02-01', 'CAM-A02-02'],
            ],
            [
                'codigo'          => 'HAB-B01',
                'nombre'          => 'Habitación B-01',
                'tipo_habitacion' => 'MULTIPLE',
                'ubicacion'       => 'Primer Piso — Ala B',
                'capacidad'       => 4,
                'observaciones'   => 'Habitación múltiple con 4 camas y baño compartido.',
                'camas'           => ['CAM-B01-01', 'CAM-B01-02', 'CAM-B01-03', 'CAM-B01-04'],
            ],
            [
                'codigo'          => 'HAB-B02',
                'nombre'          => 'Habitación B-02 Especializada',
                'tipo_habitacion' => 'ESPECIALIZADA',
                'ubicacion'       => 'Primer Piso — Ala B',
                'capacidad'       => 2,
                'observaciones'   => 'Equipada para adultos con necesidades especiales. Barras de apoyo y rampa de acceso.',
                'camas'           => ['CAM-B02-01', 'CAM-B02-02'],
            ],
            [
                'codigo'          => 'HAB-C01',
                'nombre'          => 'Habitación C-01',
                'tipo_habitacion' => 'INDIVIDUAL',
                'ubicacion'       => 'Segundo Piso — Ala C',
                'capacidad'       => 1,
                'observaciones'   => 'Individual con vista al jardín. Baño privado.',
                'camas'           => ['CAM-C01-01'],
            ],
        ];

        foreach ($habitaciones as $h) {
            $hab = Habitacion::updateOrCreate(
                ['codigo' => $h['codigo']],
                [
                    'nombre'          => $h['nombre'],
                    'tipo_habitacion' => $h['tipo_habitacion'],
                    'ubicacion'       => $h['ubicacion'],
                    'capacidad'       => $h['capacidad'],
                    'estado'          => 'DISPONIBLE',
                    'observaciones'   => $h['observaciones'],
                ]
            );

            foreach ($h['camas'] as $codigoCama) {
                Cama::firstOrCreate(
                    ['codigo' => $codigoCama],
                    [
                        'cod_habitacion' => $hab->cod_habitacion,
                        'estado'         => 'DISPONIBLE',
                        'observaciones'  => "Cama {$codigoCama} — {$h['nombre']}",
                    ]
                );
            }
        }

        $this->command->info('[HabitacionesCamasSeeder] 5 habitaciones y sus camas creadas/actualizadas.');
    }
}
