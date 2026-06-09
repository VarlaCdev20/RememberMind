<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\Cama;
use App\Models\AsignacionAdultoMayor;
use App\Models\SignosVitalesAdulto;
use App\Models\PlanCuidado;
use App\Models\TurnoEnfermeria;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class EnfermeriaPacienteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el rol si no existe
        $role = Role::firstOrCreate(['name' => 'ENFERMEROS']);

        // 2. Crear al Enfermero (User)
        $user = User::firstWhere('correo', 'enfermero@casaamandita.com');
        if (!$user) {
            $user = User::create([
                'cod_usu' => 'USU_9999',
                'nombres' => 'Juan',
                'ap_paterno' => 'Perez',
                'correo' => 'enfermero@casaamandita.com',
                'password' => Hash::make('password'),
                'estado' => 'ACTIVO'
            ]);
        }
        if (!$user->hasRole('ENFERMEROS')) {
            $user->assignRole('ENFERMEROS');
        }

        // 3. Asegurar Habitacion y Cama
        $habitacion = Habitacion::firstWhere('nombre', 'Habitacion 101');
        if (!$habitacion) {
            $habitacion = Habitacion::create([
                'codigo' => 'HAB-101',
                'nombre' => 'Habitacion 101',
                'tipo_habitacion' => 'INDIVIDUAL',
                'capacidad' => 1,
                'estado' => 'DISPONIBLE'
            ]);
        }

        $cama = Cama::firstWhere('cod_habitacion', $habitacion->cod_habitacion);
        if (!$cama) {
            $cama = Cama::create([
                'codigo' => 'CAM-101',
                'cod_habitacion' => $habitacion->cod_habitacion,
                'estado' => 'DISPONIBLE'
            ]);
        }

        // 4. Obtener el estado activo de flujo clinico
        $estadoId = EstadoAdulto::where('estado', 'EN_SEGUIMIENTO_ACTIVO')->value('cod_est_adul');
        if (!$estadoId) {
            $estadoId = EstadoAdulto::where('estado', 'ACTIVO')->value('cod_est_adul');
        }

        // 5. Asegurar el Adulto Mayor de Demo (AM_002)
        $paciente = AdultoMayor::firstWhere('ci', '1234567-DEMO');
        if (!$paciente) {
            $paciente = AdultoMayor::create([
                'nombres'      => 'Roberto',
                'ap_paterno'   => 'Choque',
                'ap_materno'   => 'Condori',
                'ci'           => '1284567',
                'fecha_nac'    => '1945-08-20',
                'genero'       => 'MASCULINO',
                'estado_civil' => 'CASADO',
                'cod_est_adul' => $estadoId,
                'fecha_ing'    => now()->toDateString(),
                'hora_ing'     => '08:00',
                'tipo_ing'     => 'REGULAR',
                'permanencia'  => 'PERMANENTE',
                'nivel_educat' => 'PRIMARIA',
                'grupo_sanguineo' => 'O+',
                'factor_rh'    => '+',
                'alergias'     => 'NINGUNA',
                'seguro_salud' => 'SUS',
                'contacto_emergencia_nombre' => 'JUAN CHOQUE',
                'contacto_emergencia_parentesco' => 'HIJO/A',
                'contacto_emergencia_celular' => '71111111',
                'contacto_emergencia_direccion' => 'MIRAFLORES',
                'responsable_principal' => true,
                'autorizado_informacion_medica' => true,
                'consentimiento_datos' => true,
            ]);
        }

        // 6. Asignar cama/habitacion en asignacion_adulto_mayor
        $asignacion = AsignacionAdultoMayor::where('cod_am', $paciente->cod_am)->first();
        if (!$asignacion) {
            AsignacionAdultoMayor::create([
                'cod_am' => $paciente->cod_am,
                'cod_habitacion' => $habitacion->cod_habitacion,
                'cod_cama' => $cama->cod_cama,
                'fecha_asignacion' => now()->toDateString(),
                'hora_asignacion' => now()->toTimeString(),
                'estado' => 'ACTIVO',
                'observaciones' => 'Asignación de prueba automática',
                'registrado_por' => $user->cod_usu
            ]);
        }

        // 7. Signos vitales de prueba
        SignosVitalesAdulto::create([
            'cod_am' => $paciente->cod_am,
            'registrado_por' => $user->cod_usu,
            'fecha' => now()->toDateString(),
            'hora' => now()->toTimeString(),
            'presion_sistolica' => 120,
            'presion_diastolica' => 80,
            'frecuencia_cardiaca' => 75,
            'frecuencia_respiratoria' => 18,
            'temperatura' => 36.5,
            'saturacion' => 95,
        ]);

        // 8. Plan de cuidado de prueba
        $plan = PlanCuidado::where('cod_am', $paciente->cod_am)->first();
        if (!$plan) {
            PlanCuidado::create([
                'cod_am' => $paciente->cod_am,
                'nivel_cuidado' => 'INTERMEDIO',
                'version' => 1,
                'fecha_inicio' => now()->toDateString(),
                'creado_por' => $user->cod_usu,
                'estado' => 'ACTIVO'
            ]);
        }

        $this->command->info("Datos de enfermeria (enfermero@casaamandita.com y paciente AM_002) sembrados correctamente.");
    }
}
