<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\PersonalSalud;
use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\Cama;
use App\Models\AsignacionTurnoAdulto;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
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

        // 3. Crear el registro en personal_salud
        $personal = PersonalSalud::firstWhere('cod_usu', $user->cod_usu);
        if (!$personal) {
            $personal = clone $user; // not real, just a placeholder structure
            $personal = PersonalSalud::create([
                'cod_usu' => $user->cod_usu,
                'fecha_ing' => '2026-05-01',
                'estado_laboral' => 'ACTIVO',
                'cod_esp' => 6, // Enfermería general? just random id
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Asegurar Habitacion y Cama
        $habitacion = Habitacion::firstOrCreate(
            ['codigo' => 'HAB-01'],
            ['nombre' => 'Habitacion 101', 'tipo_habitacion' => 'INDIVIDUAL', 'capacidad' => 1, 'estado' => 'DISPONIBLE']
        );

        $cama = Cama::firstOrCreate(
            ['codigo' => 'CAM-01'],
            ['cod_habitacion' => $habitacion->cod_habitacion ?? 1, 'estado' => 'DISPONIBLE']
        );

        // 5. Obtener el estado activo de flujo clinico
        $estadoId = DB::table('estado_adulto')->where('estado', 'EN_SEGUIMIENTO_ACTIVO')->value('cod_est_adul');
        if (!$estadoId) {
            $estadoId = DB::table('estado_adulto')->where('estado', 'ACTIVO')->value('cod_est_adul');
        }

        // 6. Asegurar el Adulto Mayor de Demo (AM_0001)
        $paciente = AdultoMayor::firstOrCreate(
            ['cod_am' => 'AM_0001'],
            [
                'nombres'      => 'Roberto',
                'ap_paterno'   => 'Choque',
                'ap_materno'   => 'Condori',
                'ci'           => '1234567',
                'fecha_nac'    => '1945-08-20',
                'genero'       => 'MASCULINO',
                'estado_civil' => 'CASADO',
                'cod_est_adul' => $estadoId,
            ]
        );
        $paciente->cod_est_adul = $estadoId;
        $paciente->save();

        // 7. Obtener Turno Mañana
        $turno = DB::table('turnos_enfermeria')->where('nombre', 'MAÑANA')->first();
        if (!$turno) {
            DB::table('turnos_enfermeria')->insert([
                'nombre' => 'MAÑANA',
                'hora_inicio' => '06:00:00',
                'hora_fin' => '12:00:00',
                'orden' => 1,
                'estado' => 'ACTIVO'
            ]);
            $turno = DB::table('turnos_enfermeria')->where('nombre', 'MAÑANA')->first();
        }

        // 8. Asignar turno
        DB::table('asignaciones_turno_adulto')->updateOrInsert(
            ['cod_am' => $paciente->cod_am, 'cod_usu_enfermero' => $user->cod_usu, 'estado' => 'ACTIVA'],
            [
                'cod_turno' => $turno->cod_turno ?? $turno->id ?? 1,
                'cod_habitacion' => $habitacion->cod_habitacion ?? 1,
                'cod_cama' => $cama->cod_cama ?? 1,
                'fecha_inicio' => now()->toDateString(),
                'motivo_asignacion' => 'Asignación de prueba automática',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 9. Datos clínicos de prueba
        DB::table('signos_vitales_adulto')->insert([
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
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('planes_cuidado')->updateOrInsert(
            ['cod_am' => $paciente->cod_am, 'estado' => 'ACTIVO'],
            [
                'nivel_cuidado' => 'INTERMEDIO',
                'version' => 1,
                'fecha_inicio' => now(),
                'creado_por' => $user->cod_usu,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        $this->command->info("Datos de enfermeria (enfermero@casaamandita.com y paciente AM_0001) sembrados correctamente.");
    }
}
