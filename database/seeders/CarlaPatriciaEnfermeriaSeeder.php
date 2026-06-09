<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Preadmision;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CarlaPatriciaEnfermeriaSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Rol ─────────────────────────────────────────────────────
        Role::firstOrCreate(['name' => 'ENFERMEROS']);

        // ── 2. Enfermera Carla Patricia ────────────────────────────────
        $carla = User::firstWhere('cod_usu', 'USU_9988')
              ?? User::firstWhere('correo', 'carla.patricia@jardinrecuerdos.bo');

        if (!$carla) {
            $carla = User::create([
                'cod_usu'    => 'USU_9988',
                'nombres'    => 'CARLA PATRICIA',
                'ap_paterno' => 'GOMEZ',
                'ap_materno' => 'QUISPE',
                'correo'     => 'carla.patricia@jardinrecuerdos.bo',
                'password'   => Hash::make('password'),
                'estado'     => 'ACTIVO',
            ]);
        } else {
            // Actualizar datos por si el registro previo tenía otro correo
            $carla->update([
                'nombres'    => 'CARLA PATRICIA',
                'ap_paterno' => 'GOMEZ',
                'ap_materno' => 'QUISPE',
                'correo'     => 'carla.patricia@jardinrecuerdos.bo',
                'estado'     => 'ACTIVO',
            ]);
        }
        if (!$carla->hasRole('ENFERMEROS')) {
            $carla->assignRole('ENFERMEROS');
        }

        $estadoId = EstadoAdulto::where('estado', 'ACTIVO')->value('cod_est_adul') ?? 'EST_001';
        $turno    = TurnoEnfermeria::where('estado', 'ACTIVO')->first();

        // ── 3. Tres pacientes asignados ────────────────────────────────
        $pacientes = [
            [
                'nombres'    => 'ROSA ELENA',
                'ap_paterno' => 'MAMANI',
                'ap_materno' => 'CONDORI',
                'ci'         => '3456781',
                'fecha_nac'  => '1948-03-15',
                'genero'     => 'FEMENINO',
                'estado_civil' => 'VIUDA',
                'tipo_ing'   => 'REGULAR',
                'permanencia'=> 'PERMANENTE',
            ],
            [
                'nombres'    => 'FELIX MARCELINO',
                'ap_paterno' => 'CHOQUE',
                'ap_materno' => 'HUANCA',
                'ci'         => '3456782',
                'fecha_nac'  => '1945-07-22',
                'genero'     => 'MASCULINO',
                'estado_civil' => 'CASADO',
                'tipo_ing'   => 'REGULAR',
                'permanencia'=> 'PERMANENTE',
            ],
            [
                'nombres'    => 'JUANA MERCEDES',
                'ap_paterno' => 'TICONA',
                'ap_materno' => 'FLORES',
                'ci'         => '3456783',
                'fecha_nac'  => '1952-11-08',
                'genero'     => 'FEMENINO',
                'estado_civil' => 'SOLTERA',
                'tipo_ing'   => 'DERIVACION',
                'permanencia'=> 'TEMPORAL',
            ],
        ];

        foreach ($pacientes as $datos) {
            $paciente = AdultoMayor::firstWhere('ci', $datos['ci']);

            if (!$paciente) {
                $paciente = AdultoMayor::create([
                    'nombres'      => $datos['nombres'],
                    'ap_paterno'   => $datos['ap_paterno'],
                    'ap_materno'   => $datos['ap_materno'],
                    'ci'           => $datos['ci'],
                    'fecha_nac'    => $datos['fecha_nac'],
                    'genero'       => $datos['genero'],
                    'estado_civil' => $datos['estado_civil'],
                    'tipo_ing'     => $datos['tipo_ing'],
                    'permanencia'  => $datos['permanencia'],
                    'cod_est_adul' => $estadoId,
                    'fecha_ing'    => now()->subDays(rand(5, 30))->toDateString(),
                    'hora_ing'     => '08:00',
                    'contacto_emergencia_nombre'     => 'FAMILIAR DE ' . $datos['ap_paterno'],
                    'contacto_emergencia_parentesco' => 'HIJO/A',
                    'contacto_emergencia_celular'    => '7' . rand(1000000, 9999999),
                    'responsable_principal'          => true,
                    'consentimiento_datos'           => true,
                ]);
            }

            // Asignar turno de enfermería al paciente
            if ($turno) {
                $yaAsignado = AsignacionTurnoAdulto::where('cod_am', $paciente->cod_am)
                    ->where('estado', 'ACTIVA')
                    ->exists();

                if (!$yaAsignado) {
                    AsignacionTurnoAdulto::create([
                        'cod_am'            => $paciente->cod_am,
                        'cod_turno'         => $turno->cod_turno,
                        'cod_usu_enfermero' => $carla->cod_usu,
                        'fecha_inicio'      => now()->subDays(rand(1, 10))->toDateString(),
                        'nivel_supervision' => 'ESTANDAR',
                        'estado'            => 'ACTIVA',
                        'motivo_asignacion' => 'Asignación institucional de turno – Enfermera Carla Patricia',
                        'asignado_por'      => $carla->cod_usu,
                    ]);
                }
            }
        }

        // ── 4. Un paciente en valoración inicial (preadmisión) ─────────
        $ciPre = '9888801';
        $preadmision = Preadmision::firstWhere('ci', $ciPre);

        if (!$preadmision) {
            Preadmision::create([
                // cod_pre explícito para no ocupar PRE_00001-PRE_00008 (reservados para demo)
                'cod_pre'             => 'PRE_CARLA_01',
                'estado'              => 'PREADMISION_ASIGNADA',
                'fecha_solicitud'     => now()->toDateString(),
                'fecha_asignacion'    => now(),
                'nombres'             => 'PEDRO SEGUNDO',
                'ap_paterno'          => 'APAZA',
                'ap_materno'          => 'LIMACHI',
                'ci'                  => $ciPre,
                'fecha_nac'           => '1950-05-12',
                'genero'              => 'MASCULINO',
                'estado_civil'        => 'VIUDO',
                'celular'             => '76543210',
                'departamento_residencia' => 'LA PAZ',
                'ciudad_municipio'    => 'EL ALTO',
                'motivo_ingreso'      => 'DETERIORO_COGNITIVO',
                'procedencia_ingreso' => 'FAMILIAR',
                'tipo_ingreso'        => 'REGULAR',
                'permanencia'         => 'PERMANENTE',
                'prioridad'           => 'MEDIA',
                'descripcion_caso'    => 'Adulto mayor con signos iniciales de deterioro cognitivo leve. Familia solicita ingreso para evaluación y cuidado integral.',
                'familiar_nombres'    => 'MARIA ELENA',
                'familiar_ap_paterno' => 'APAZA',
                'familiar_ap_materno' => 'CONDORI',
                'familiar_ci'         => '5678901',
                'familiar_parentesco' => 'HIJA',
                'familiar_celular'    => '71234567',
                'familiar_correo'     => 'maria.apaza@gmail.com',
                'familiar_direccion'  => 'ZONA 16 DE JULIO, CALLE 3, NRO 456',
                'enfermero_asignado'  => $carla->cod_usu,
                'creado_por'          => $carla->cod_usu,
                'observaciones'       => 'Paciente referido por familiar directa. Requiere valoración de enfermería para determinar nivel de cuidado.',
            ]);
        }

        $this->command->info('✓ Carla Patricia: 3 pacientes asignados + 1 en valoración inicial.');
    }
}
