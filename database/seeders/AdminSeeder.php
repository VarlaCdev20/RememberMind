<?php

namespace Database\Seeders;

use App\Models\Contacto;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Matriz de usuarios y perfiles normados de RememberMind.
     * Cumple con la BDD Operativa V2.1:
     * - cod_usuario canonico (string USU_...)
     * - cod_personal canonico (string PER_...)
     * - cod_contacto canonico para rol FAMILIAR (string CON_...)
     * - Contrasenas no hardcodeadas generadas dinamicamente o leidas de entorno.
     */
    public function run(): void
    {
        if (! app()->environment('testing') && ! filter_var(env('SEED_DEMO_ACCOUNTS', false), FILTER_VALIDATE_BOOL)) {
            $this->command?->warn('AdminSeeder omitido: las cuentas de demostración requieren SEED_DEMO_ACCOUNTS=true.');

            return;
        }

        $cuentas = [
            // 1. Superadministrador - Direccion General Casa Amandita
            [
                'correo' => 'admincasaamandita@gmail.com',
                'cod_usuario' => 'USU_0001',
                'rol' => 'SUPERADMINISTRADOR',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0001',
                    'nombres' => 'CARLA',
                    'apellido_paterno' => 'ENCINAS',
                    'numero_documento' => 'ADMIN-0001',
                    'profesion' => 'ADMINISTRACION',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 2. Superadministrador - Desarrollador TI
            [
                'correo' => 'carlaencinas78@gmail.com',
                'cod_usuario' => 'USU_0002',
                'rol' => 'SUPERADMINISTRADOR',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0002',
                    'nombres' => 'CARLA',
                    'apellido_paterno' => 'ENCINAS',
                    'numero_documento' => 'DEV-0001',
                    'profesion' => 'ADMINISTRACION',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 3. Enfermeria - Cuidados Continuos
            [
                'correo' => 'enfermeria@remembermind.com',
                'cod_usuario' => 'USU_0003',
                'rol' => 'ENFERMEROS',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0003',
                    'nombres' => 'ROSA',
                    'apellido_paterno' => 'MAMANI',
                    'apellido_materno' => 'CHOQUE',
                    'numero_documento' => 'ENF-0001',
                    'profesion' => 'ENFERMERIA',
                    'especialidad' => 'CUIDADOS GERIATRICOS',
                    'matricula_profesional' => 'ENF-BOL-7821',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 4. Administrador - Gestion Institucional
            [
                'correo' => 'administracion@remembermind.com',
                'cod_usuario' => 'USU_0004',
                'rol' => 'ADMINISTRADOR',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0004',
                    'nombres' => 'MARCELO',
                    'apellido_paterno' => 'ROCHA',
                    'apellido_materno' => 'ROJAS',
                    'numero_documento' => 'ADM-0002',
                    'profesion' => 'ADMINISTRACION INSTITUCIONAL',
                    'especialidad' => 'GESTION RESIDENCIAL',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 5. Medico General / Geriatra - Atencion Medica
            [
                'correo' => 'medico@remembermind.com',
                'cod_usuario' => 'USU_0005',
                'rol' => 'MEDICO GENERAL/GERIATRA',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0005',
                    'nombres' => 'JAVIER',
                    'apellido_paterno' => 'MONTES',
                    'apellido_materno' => 'FLORES',
                    'numero_documento' => 'MED-0001',
                    'profesion' => 'MEDICINA GENERAL Y GERIATRIA',
                    'especialidad' => 'GERIATRIA Y GERONTOLOGIA',
                    'matricula_profesional' => 'MED-BOL-5421',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 6. Psicologo/a - Salud Mental y Evaluacion Cognitiva
            [
                'correo' => 'psicologia@remembermind.com',
                'cod_usuario' => 'USU_0006',
                'rol' => 'PSICOLOGO/A',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0006',
                    'nombres' => 'VALERIA',
                    'apellido_paterno' => 'TORREZ',
                    'apellido_materno' => 'MENDOZA',
                    'numero_documento' => 'PSI-0001',
                    'profesion' => 'PSICOLOGIA CLINICA',
                    'especialidad' => 'PSICOGERIATRIA Y SALUD MENTAL',
                    'matricula_profesional' => 'PSI-BOL-3180',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 7. Pedagogo - Estimulacion Cognitiva y Recreacion
            [
                'correo' => 'pedagogia@remembermind.com',
                'cod_usuario' => 'USU_0007',
                'rol' => 'PEDAGOGO',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0007',
                    'nombres' => 'ANDREA',
                    'apellido_paterno' => 'GOMEZ',
                    'apellido_materno' => 'GUTIERREZ',
                    'numero_documento' => 'PED-0001',
                    'profesion' => 'PEDAGOGIA SOCIAL',
                    'especialidad' => 'ESTIMULACION COGNITIVA',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 8. Nutricionista - Nutricion y Dietetica
            [
                'correo' => 'nutricion@remembermind.com',
                'cod_usuario' => 'USU_0008',
                'rol' => 'NUTRICIONISTA',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0008',
                    'nombres' => 'PATRICIA',
                    'apellido_paterno' => 'SUAREZ',
                    'apellido_materno' => 'LOPEZ',
                    'numero_documento' => 'NUT-0001',
                    'profesion' => 'NUTRICION Y DIETETICA',
                    'especialidad' => 'NUTRICION GERIATRICA',
                    'matricula_profesional' => 'NUT-BOL-1942',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 9. Fisioterapeuta - Rehabilitacion y Terapia Fisica
            [
                'correo' => 'fisioterapia@remembermind.com',
                'cod_usuario' => 'USU_0009',
                'rol' => 'FISIOTERAPEUTA',
                'tipo_entidad' => 'personal',
                'entidad' => [
                    'cod_personal' => 'PER_0009',
                    'nombres' => 'GABRIEL',
                    'apellido_paterno' => 'PAREDES',
                    'apellido_materno' => 'CALLISAYA',
                    'numero_documento' => 'FIS-0001',
                    'profesion' => 'FISIOTERAPIA Y KINESIOLOGIA',
                    'especialidad' => 'REHABILITACION FISICA GERIATRICA',
                    'matricula_profesional' => 'FIS-BOL-0853',
                    'fecha_ingreso' => '2024-01-01',
                    'estado' => 'ACTIVO',
                ],
            ],

            // 10. Familiar - Contacto y Seguimiento Familiar
            [
                'correo' => 'familiar@remembermind.com',
                'cod_usuario' => 'USU_0010',
                'rol' => 'FAMILIAR',
                'tipo_entidad' => 'contacto',
                'entidad' => [
                    'cod_contacto' => 'CON_0001',
                    'nombres' => 'MARIO',
                    'apellido_paterno' => 'ENCINAS',
                    'apellido_materno' => 'VACA',
                    'numero_documento' => 'FAM-0001',
                    'telefono' => '22334455',
                    'celular' => '70012345',
                    'correo' => 'familiar@remembermind.com',
                    'direccion' => 'Av. Arce #2450',
                    'estado' => 'ACTIVO',
                ],
            ],
        ];

        foreach ($cuentas as $cuenta) {
            $rawPassword = env('SEED_DEFAULT_PASSWORD') ?: Str::password(20);

            $user = User::firstOrCreate(
                ['correo' => $cuenta['correo']],
                [
                    'cod_usuario' => $cuenta['cod_usuario'],
                    'contrasena' => $rawPassword,
                    'estado' => 'ACTIVO',
                ]
            );

            if (! $user->hasRole($cuenta['rol'])) {
                $user->assignRole($cuenta['rol']);
            }

            if ($cuenta['tipo_entidad'] === 'personal') {
                Personal::updateOrCreate(
                    ['cod_usuario' => $user->cod_usuario],
                    $cuenta['entidad']
                );
            } elseif ($cuenta['tipo_entidad'] === 'contacto') {
                Contacto::updateOrCreate(
                    ['cod_usuario' => $user->cod_usuario],
                    $cuenta['entidad']
                );
            }
        }
    }
}
