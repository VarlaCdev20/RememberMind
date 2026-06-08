<?php

namespace Database\Seeders;

use App\Models\TurnoEnfermeria;
use Illuminate\Database\Seeder;

class TurnosEnfermeriaSeeder extends Seeder
{
    public function run(): void
    {
        $turnos = [
            [
                'nombre'      => 'MAÑANA',
                'hora_inicio' => '06:00:00',
                'hora_fin'    => '12:00:00',
                'orden'       => 1,
                'estado'      => 'ACTIVO',
                'observacion' => 'Turno matutino',
            ],
            [
                'nombre'      => 'TARDE',
                'hora_inicio' => '12:00:00',
                'hora_fin'    => '18:00:00',
                'orden'       => 2,
                'estado'      => 'ACTIVO',
                'observacion' => 'Turno vespertino',
            ],
            [
                'nombre'      => 'NOCHE',
                'hora_inicio' => '18:00:00',
                'hora_fin'    => '00:00:00',
                'orden'       => 3,
                'estado'      => 'ACTIVO',
                'observacion' => 'Turno nocturno',
            ],
            [
                'nombre'      => 'MADRUGADA',
                'hora_inicio' => '00:00:00',
                'hora_fin'    => '06:00:00',
                'orden'       => 4,
                'estado'      => 'ACTIVO',
                'observacion' => 'Turno de madrugada',
            ],
        ];

        foreach ($turnos as $turno) {
            $exists = TurnoEnfermeria::where('nombre', $turno['nombre'])->exists();

            if (! $exists) {
                $created = TurnoEnfermeria::create($turno);
                $this->command->line("  Turno creado: {$created->nombre} ({$created->cod_turno})");
            } else {
                $this->command->line("  Turno existente (omitido): {$turno['nombre']}");
            }
        }

        $this->command->info('Turnos de enfermería sincronizados.');
    }
}
