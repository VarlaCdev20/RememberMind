<?php

namespace Database\Seeders;

use App\Models\DocumentoUsuario;
use App\Models\HorarioPersonalAdmin;
use App\Models\HorarioPersonalSalud;
use App\Models\TipoDocumentoUsuario;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        $rolesNecesarios = [
            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA',
            'ADMINISTRADOR',
        ];
        foreach ($rolesNecesarios as $rolName) {
            Role::firstOrCreate(['name' => $rolName, 'guard_name' => 'web']);
        }

        $personal = [
            [
                'nombres'    => 'CARLA PATRICIA',
                'ap_paterno' => 'MENDOZA',
                'ap_materno' => 'QUISPE',
                'correo'     => 'enfermera.mendoza@casaamandita.com',
                'password'   => 'Enfermera#2025',
                'rol'        => 'ENFERMEROS',
                'turno'      => 'MAÑANA',
                'tipo'       => 'salud',
            ],
            [
                'nombres'    => 'ROSA ELENA',
                'ap_paterno' => 'CHOQUE',
                'ap_materno' => 'MAMANI',
                'correo'     => 'enfermera.choque@casaamandita.com',
                'password'   => 'Enfermera#2025',
                'rol'        => 'ENFERMEROS',
                'turno'      => 'TARDE',
                'tipo'       => 'salud',
            ],
            [
                'nombres'    => 'JORGE RODRIGO',
                'ap_paterno' => 'SALINAS',
                'ap_materno' => 'FLORES',
                'correo'     => 'medico.salinas@casaamandita.com',
                'password'   => 'Medico#2025',
                'rol'        => 'MEDICO GENERAL/GERIATRA',
                'turno'      => 'MAÑANA',
                'tipo'       => 'salud',
            ],
            [
                'nombres'    => 'MARIANA SOLEDAD',
                'ap_paterno' => 'VILLCA',
                'ap_materno' => 'ROJAS',
                'correo'     => 'psicologa.villca@casaamandita.com',
                'password'   => 'Psicologa#2025',
                'rol'        => 'PSICOLOGO/A',
                'turno'      => 'MAÑANA',
                'tipo'       => 'admin',
            ],
            [
                'nombres'    => 'DANIELA RUTH',
                'ap_paterno' => 'COPA',
                'ap_materno' => 'LAURA',
                'correo'     => 'nutricionista.copa@casaamandita.com',
                'password'   => 'Nutricion#2025',
                'rol'        => 'NUTRICIONISTA',
                'turno'      => 'MAÑANA',
                'tipo'       => 'admin',
            ],
            [
                'nombres'    => 'IVAN GONZALO',
                'ap_paterno' => 'TORREZ',
                'ap_materno' => 'AGUILAR',
                'correo'     => 'fisio.torrez@casaamandita.com',
                'password'   => 'Fisio#2025',
                'rol'        => 'FISIOTERAPEUTA',
                'turno'      => 'TARDE',
                'tipo'       => 'salud',
            ],
            [
                'nombres'    => 'TERESA CRISTINA',
                'ap_paterno' => 'BALDELOMAR',
                'ap_materno' => 'SORIA',
                'correo'     => 'admin.baldelomar@casaamandita.com',
                'password'   => 'Admin#2025',
                'rol'        => 'ADMINISTRADOR',
                'turno'      => 'MAÑANA',
                'tipo'       => 'admin',
            ],
        ];

        $dias      = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];
        $tipoDocCI = TipoDocumentoUsuario::where('nombre', 'like', '%dula%')
                        ->orWhere('nombre', 'like', '%Identidad%')
                        ->first()
                  ?? TipoDocumentoUsuario::first();

        // Calcula el máximo numérico real para evitar el bug de ordenamiento
        // alfabético en User::booted() que causa duplicados de PK cuando
        // existen códigos como USU_9999 y USU_10000 simultáneamente.
        $maxNum = User::where('cod_usu', 'like', 'USU_%')
            ->pluck('cod_usu')
            ->map(fn($c) => (int) substr($c, 4))
            ->max() ?? 0;

        foreach ($personal as $d) {
            $user = User::where('correo', $d['correo'])->first();

            if ($user) {
                $user->password = Hash::make($d['password']);
                $user->estado   = 'ACTIVO';
                $user->save();
            } else {
                do {
                    $maxNum++;
                    $codUsu = 'USU_' . str_pad($maxNum, 4, '0', STR_PAD_LEFT);
                } while (User::where('cod_usu', $codUsu)->exists());

                $user = User::create([
                    'cod_usu'    => $codUsu,
                    'nombres'    => $d['nombres'],
                    'ap_paterno' => $d['ap_paterno'],
                    'ap_materno' => $d['ap_materno'],
                    'correo'     => $d['correo'],
                    'password'   => Hash::make($d['password']),
                    'estado'     => 'ACTIVO',
                ]);
            }

            if (! $user->hasRole($d['rol'])) {
                $user->assignRole($d['rol']);
            }

            [$hIni, $hFin] = $d['turno'] === 'TARDE'
                ? ['12:00:00', '18:00:00']
                : ['07:00:00', '13:00:00'];

            foreach ($dias as $dia) {
                if ($d['tipo'] === 'salud') {
                    HorarioPersonalSalud::firstOrCreate(
                        ['cod_usu' => $user->cod_usu, 'dia_semana' => $dia],
                        [
                            'hora_inicio' => $hIni,
                            'hora_fin'    => $hFin,
                            'turno'       => $d['turno'],
                            'estado'      => 'ACTIVO',
                            'cod_per_sal' => $user->cod_usu,
                        ]
                    );
                } else {
                    HorarioPersonalAdmin::firstOrCreate(
                        ['cod_usu' => $user->cod_usu, 'dia_semana' => $dia],
                        [
                            'hora_inicio' => '08:00:00',
                            'hora_fin'    => '16:00:00',
                            'turno'       => 'MAÑANA',
                            'estado'      => 'ACTIVO',
                            'cod_per_adm' => $user->cod_usu,
                        ]
                    );
                }
            }

            if ($tipoDocCI) {
                DocumentoUsuario::firstOrCreate(
                    ['cod_usu' => $user->cod_usu, 'cod_tipo_doc' => $tipoDocCI->cod_tipo_doc],
                    [
                        'nombre_documento' => 'Cédula de Identidad',
                        'archivo_path'     => 'demo/ci_' . strtolower($d['ap_paterno']) . '.pdf',
                        'estado'           => 'VALIDADO',
                        'fecha_subida'     => now(),
                    ]
                );
            }
        }

        $this->command->info('[PersonalSeeder] ' . count($personal) . ' usuarios de personal creados/actualizados.');
        $this->command->line('');
        $this->command->line('  Credenciales del personal:');
        foreach ($personal as $d) {
            $this->command->line("    {$d['correo']}  /  {$d['password']}  [{$d['rol']}]");
        }
    }
}
