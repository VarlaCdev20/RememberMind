<?php

namespace Database\Seeders;

use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Preadmision;
use App\Models\ResidenteContacto;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Vincula un residente ficticio ya admitido a las cuentas locales de vista previa. */
class LocalDesignPreviewSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing']) || DB::getDriverName() !== 'pgsql') {
            throw new RuntimeException('La vista previa requiere PostgreSQL local o de pruebas.');
        }

        $preadmision = Preadmision::query()->find('PRE_FIC_001');
        $residente = $preadmision?->admision?->residente;
        if ($preadmision?->numero_documento !== 'FIC-RES-001' || ! $residente || $residente->estado !== 'ADMITIDO') {
            throw new RuntimeException('Ejecute primero LocalSampleDataSeeder; falta el residente ficticio admitido.');
        }

        $enfermero = DB::table('usuarios')->where('cod_usuario', 'USU_0003')->first();
        $familiar = DB::table('usuarios')->where('cod_usuario', 'USU_0010')->first();
        $personal = DB::table('personal')->where('cod_personal', 'PER_0003')->first();
        $contacto = DB::table('contactos')->where('cod_contacto', 'CON_0001')->first();
        if ($enfermero?->correo !== 'enfermeria@remembermind.com' || $enfermero->estado !== 'ACTIVO'
            || $personal?->cod_usuario !== $enfermero->cod_usuario || $personal->estado !== 'ACTIVO'
            || $familiar?->correo !== 'familiar@remembermind.com' || $familiar->estado !== 'ACTIVO'
            || $contacto?->cod_usuario !== $familiar->cod_usuario || $contacto->estado !== 'ACTIVO'
            || ! User::query()->find('USU_0003')?->hasRole('ENFERMEROS')
            || ! User::query()->find('USU_0010')?->hasRole('FAMILIAR')
            || ! DB::table('areas')->where('cod_area', 'ARE_FIC_001')->exists()) {
            throw new RuntimeException('Faltan las cuentas, el personal, el contacto o el área locales de muestra.');
        }

        $fecha = today();
        $sufijo = $fecha->format('ymd');
        $codTurno = 'TUR_FIC_UI_001';
        $codJornada = 'JOR_FIC_UI_'.$sufijo;

        DB::transaction(function () use ($residente, $contacto, $personal, $fecha, $codTurno, $codJornada, $sufijo): void {
            $turno = Turno::query()->firstOrCreate(['cod_turno' => $codTurno], [
                'nombre' => 'Vista previa ficticia',
                'hora_inicio' => '00:00:00',
                'hora_cierre' => '23:59:59',
                'orden' => 1,
                'estado' => 'ACTIVO',
            ]);
            if ($turno->nombre !== 'Vista previa ficticia' || $turno->estado !== 'ACTIVO'
                || $turno->hora_inicio !== '00:00:00' || $turno->hora_cierre !== '23:59:59') {
                throw new RuntimeException('El código del turno de vista previa ya está ocupado.');
            }

            $jornada = Jornada::query()->firstOrCreate(['cod_jornada' => $codJornada], [
                'cod_turno' => $codTurno,
                'fecha_jornada' => $fecha->toDateString(),
                'estado' => 'ABIERTA',
            ]);
            if ($jornada->cod_turno !== $codTurno || ! $jornada->fecha_jornada?->isSameDay($fecha)
                || $jornada->estado !== 'ABIERTA') {
                throw new RuntimeException('El código de la jornada de vista previa ya está ocupado.');
            }

            $asignacionPersonal = AsignacionPersonal::query()->firstOrCreate(
                ['cod_asignacion_personal' => 'ASP_FIC_UI_'.$sufijo],
                [
                    'cod_jornada' => $codJornada,
                    'cod_personal' => $personal->cod_personal,
                    'cod_area' => 'ARE_FIC_001',
                    'tipo_asignacion' => 'TITULAR',
                    'fecha_asignacion' => now(),
                    'estado' => 'ACTIVA',
                    'observacion' => 'Asignación ficticia para revisar diseños locales.',
                ]
            );
            if ($asignacionPersonal->cod_jornada !== $codJornada || $asignacionPersonal->cod_personal !== $personal->cod_personal
                || $asignacionPersonal->estado !== 'ACTIVA') {
                throw new RuntimeException('El código de asignación de personal de vista previa ya está ocupado.');
            }

            $asignacionResidente = AsignacionResidenteJornada::query()->firstOrCreate(
                ['cod_asignacion' => 'ARJ_FIC_UI_'.$sufijo],
                [
                    'cod_residente' => $residente->cod_residente,
                    'cod_jornada' => $codJornada,
                    'cod_personal' => $personal->cod_personal,
                    'fecha_hora' => now(),
                    'estado' => 'ACTIVO',
                    'observacion' => 'Residente ficticio para revisar diseños locales.',
                ]
            );
            if ($asignacionResidente->cod_residente !== $residente->cod_residente
                || $asignacionResidente->cod_jornada !== $codJornada
                || $asignacionResidente->cod_personal !== $personal->cod_personal
                || $asignacionResidente->estado !== 'ACTIVO') {
                throw new RuntimeException('El código de asignación de residente de vista previa ya está ocupado.');
            }

            $vinculo = ResidenteContacto::query()->firstOrCreate(
                ['cod_residente_contacto' => 'RCO_FIC_UI_001'],
                [
                    'cod_residente' => $residente->cod_residente,
                    'cod_contacto' => $contacto->cod_contacto,
                    'parentesco' => 'FAMILIAR',
                    'responsable_principal' => false,
                    'contacto_emergencia' => false,
                    'autoriza_informacion' => true,
                    'autoriza_salida' => false,
                    'estado' => 'ACTIVO',
                    'observacion' => 'Vínculo ficticio exclusivo de vista previa local.',
                ]
            );
            if ($vinculo->cod_residente !== $residente->cod_residente || $vinculo->cod_contacto !== $contacto->cod_contacto
                || $vinculo->estado !== 'ACTIVO' || ! $vinculo->autoriza_informacion) {
                throw new RuntimeException('El código del vínculo familiar de vista previa ya está ocupado.');
            }
        });

        Cache::forget('dashboard_roles_v2_USU_0003_'.md5('ENFERMEROS'));
        Cache::forget('dashboard_roles_v2_USU_0010_'.md5('FAMILIAR'));

        $this->command?->info('Residente ficticio disponible para las vistas autorizadas de cada rol.');
    }
}
