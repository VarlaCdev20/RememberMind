<?php

namespace Database\Seeders;

use App\Models\HorarioPersonalSalud;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EnfermeroConHorarioSeeder extends Seeder
{
    public const CORREO = 'enfermero.prueba@casaamandita.com';
    public const CLAVE  = 'Enfermera#2024';

    public function run(): void
    {
        Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);

        $enfermero = User::firstWhere('correo', self::CORREO);

        if (! $enfermero) {
            $enfermero = User::create([
                'nombres'        => 'LUCÍA FERNANDA',
                'ap_paterno'     => 'VARGAS',
                'ap_materno'     => 'CONDORI',
                'correo'         => self::CORREO,
                'password'       => Hash::make(self::CLAVE),
                'estado'         => 'ACTIVO',
                'acceso_sistema' => true,
            ]);
        } else {
            $enfermero->password = Hash::make(self::CLAVE);
            $enfermero->estado   = 'ACTIVO';
            $enfermero->save();
        }

        if (! $enfermero->hasRole('ENFERMEROS')) {
            $enfermero->assignRole('ENFERMEROS');
        }

        // Turno y rango horario que contiene la hora actual del sistema
        $hora = now()->hour;

        if ($hora >= 6 && $hora < 12) {
            [$turno, $inicio, $fin] = ['MAÑANA',   '06:00:00', '12:00:00'];
        } elseif ($hora >= 12 && $hora < 18) {
            [$turno, $inicio, $fin] = ['TARDE',     '12:00:00', '18:00:00'];
        } elseif ($hora >= 18) {
            [$turno, $inicio, $fin] = ['NOCHE',     '18:00:00', '23:59:00'];
        } else {
            [$turno, $inicio, $fin] = ['MADRUGADA', '00:00:00', '06:00:00'];
        }

        $diasEsp = [
            0 => 'DOMINGO',
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
        ];

        $dia = $diasEsp[now()->dayOfWeek];

        HorarioPersonalSalud::firstOrCreate(
            [
                'cod_usu'   => $enfermero->cod_usu,
                'dia_semana' => $dia,
            ],
            [
                'hora_inicio'   => $inicio,
                'hora_fin'      => $fin,
                'turno'         => $turno,
                'estado'        => 'ACTIVO',
                'observaciones' => 'Horario generado por seeder de prueba.',
                'cod_per_sal'   => $enfermero->cod_usu,
            ]
        );

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════╗');
        $this->command->info('║        ENFERMERO CREADO              ║');
        $this->command->info('╠══════════════════════════════════════╣');
        $this->command->info("║  Código  : {$enfermero->cod_usu}");
        $this->command->info('║  Correo  : ' . self::CORREO);
        $this->command->info('║  Clave   : ' . self::CLAVE);
        $this->command->info("║  Horario : {$dia} — Turno {$turno}");
        $this->command->info("║  Rango   : {$inicio} – {$fin}");
        $this->command->info('╚══════════════════════════════════════╝');
    }
}
