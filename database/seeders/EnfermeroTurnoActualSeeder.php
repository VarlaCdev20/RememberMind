<?php

namespace Database\Seeders;

use App\Models\AdultoMayor;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnfermeroTurnoActualSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('correo', 'enfermeria@remembermind.com')->first();
        if (! $user) {
            $user = User::firstOrCreate(
                ['correo' => 'enfermeria@remembermind.com'],
                [
                    'cod_usuario' => 'USU_0003',
                    'contrasena' => bcrypt('Password123!'),
                    'estado' => 'ACTIVO',
                    'nombres' => 'BEATRIZ',
                    'ap_paterno' => 'CONDORI',
                ]
            );
        }

        if (! $user->hasRole('ENFERMEROS')) {
            $user->assignRole('ENFERMEROS');
        }

        $personal = Personal::where('cod_usuario', $user->cod_usuario)->first();
        if (! $personal) {
            $personal = Personal::create([
                'cod_personal' => 'PER_0003',
                'cod_usuario' => $user->cod_usuario,
                'nombres' => 'BEATRIZ',
                'apellido_paterno' => 'CONDORI',
                'apellido_materno' => 'QUISPE',
                'numero_documento' => 'ENF-0001',
                'profesion' => 'LICENCIATURA EN ENFERMERIA',
                'especialidad' => 'CUIDADOS GERIATRICOS',
                'matricula_profesional' => 'ENF-BOL-7821',
                'fecha_ingreso' => '2024-01-01',
                'estado' => 'ACTIVO',
            ]);
        }

        $turno = TurnoEnfermeria::first();
        if (! $turno) {
            $turno = TurnoEnfermeria::create([
                'cod_turno' => 'TUR_MANANA',
                'nombre' => 'Turno Mañana',
                'hora_inicio' => '00:00:00',
                'hora_cierre' => '23:59:59',
                'orden' => 1,
                'estado' => 'ACTIVO',
            ]);
        } else {
            $turno->update([
                'hora_inicio' => '00:00:00',
                'hora_cierre' => '23:59:59',
                'estado' => 'ACTIVO',
            ]);
        }

        $jornada = Jornada::where('fecha_jornada', today()->toDateString())
            ->where('cod_turno', $turno->cod_turno)
            ->first();

        if (! $jornada) {
            $jornada = Jornada::create([
                'cod_jornada' => 'JOR_' . Str::upper(Str::random(10)),
                'cod_turno' => $turno->cod_turno,
                'cod_usuario_apertura' => $user->cod_usuario,
                'fecha_jornada' => today()->toDateString(),
                'estado' => 'ABIERTA',
            ]);
        } else {
            $jornada->update(['estado' => 'ABIERTA']);
        }

        $residentes = AdultoMayor::all();
        foreach ($residentes as $residente) {
            $asignacion = AsignacionResidenteJornada::where('cod_residente', $residente->cod_residente)
                ->where('cod_jornada', $jornada->cod_jornada)
                ->first();

            if (! $asignacion) {
                AsignacionResidenteJornada::create([
                    'cod_asignacion' => 'ASG_' . Str::upper(Str::random(10)),
                    'cod_residente' => $residente->cod_residente,
                    'cod_jornada' => $jornada->cod_jornada,
                    'cod_personal' => $personal->cod_personal,
                    'fecha_hora' => now(),
                    'nivel_supervision' => 'ESTANDAR',
                    'estado' => 'ACTIVA',
                    'observacion' => 'Asignado para turno activo de enfermeria',
                ]);
            } else {
                $asignacion->update([
                    'cod_personal' => $personal->cod_personal,
                    'estado' => 'ACTIVA',
                ]);
            }
        }
    }
}
