<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Personal;
use Carbon\Carbon;

class EnfermeriaPacienteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ÁREA DE ENFERMERÍA Y CUIDADOS
        DB::table('areas')->updateOrInsert(
            ['cod_area' => 'ARE_0001'],
            [
                'nombre' => 'ÁREA DE ENFERMERÍA Y CUIDADOS CONTINUOS',
                'descripcion' => 'Atención clínica, administración de medicamentos y cuidados continuos de enfermería.',
                'estado' => 'ACTIVA',
            ]
        );

        // 2. TURNOS INSTITUCIONALES
        DB::table('turnos')->updateOrInsert(
            ['cod_turno' => 'TUR_0001'],
            [
                'nombre' => 'TURNO MAÑANA',
                'hora_inicio' => '07:00:00',
                'hora_cierre' => '15:00:00',
                'orden' => 1,
                'estado' => 'ACTIVO',
                'observacion' => 'Turno diurno matutino',
            ]
        );

        DB::table('turnos')->updateOrInsert(
            ['cod_turno' => 'TUR_0002'],
            [
                'nombre' => 'TURNO TARDE',
                'hora_inicio' => '15:00:00',
                'hora_cierre' => '23:00:00',
                'orden' => 2,
                'estado' => 'ACTIVO',
                'observacion' => 'Turno diurno vespertino',
            ]
        );

        DB::table('turnos')->updateOrInsert(
            ['cod_turno' => 'TUR_0003'],
            [
                'nombre' => 'TURNO NOCHE',
                'hora_inicio' => '20:00:00',
                'hora_cierre' => '08:00:00',
                'orden' => 3,
                'estado' => 'ACTIVO',
                'observacion' => 'Turno nocturno cruzando medianoche',
            ]
        );

        DB::table('turnos')->updateOrInsert(
            ['cod_turno' => 'TUR_0004'],
            [
                'nombre' => 'GUARDIA CONTINUA 24H',
                'hora_inicio' => '00:00:00',
                'hora_cierre' => '23:59:59',
                'orden' => 4,
                'estado' => 'ACTIVO',
                'observacion' => 'Turno de cobertura continua las 24 horas',
            ]
        );

        // 3. USUARIO Y PERSONAL ENFERMERA
        $userEnf = User::updateOrCreate(
            ['correo' => 'enfermera.elena@remembermind.com'],
            [
                'cod_usuario' => 'USU_ENF_001',
                'contrasena' => 'CasaAmandita123',
                'estado' => 'ACTIVO',
            ]
        );
        $userEnf->assignRole('ENFERMEROS');

        $personalEnf = Personal::updateOrCreate(
            ['cod_usuario' => $userEnf->cod_usuario],
            [
                'cod_personal' => 'PER_ENF_001',
                'nombres' => 'ELENA',
                'apellido_paterno' => 'VARGAS',
                'apellido_materno' => 'ROJAS',
                'numero_documento' => '4892341 LP',
                'profesion' => 'LICENCIADA EN ENFERMERÍA',
                'fecha_ingreso' => '2023-01-15',
                'estado' => 'ACTIVO',
            ]
        );

        // Cuenta enfermería genérica
        $userGen = User::where('correo', 'enfermeria@remembermind.com')->first();
        if ($userGen) {
            $userGen->assignRole('ENFERMEROS');
        }
        $personalGen = Personal::where('cod_personal', 'PER_0003')->first();

        // 4. JORNADAS ACTIVAS
        // A) Jornada Noche actual (inició 20:00 de ayer y cierra 08:00 de hoy)
        $ayerStr = Carbon::today()->subDay()->toDateString();
        $hoyStr = Carbon::today()->toDateString();

        DB::table('jornadas')->updateOrInsert(
            ['cod_jornada' => 'JOR_0001'],
            [
                'cod_turno' => 'TUR_0003',
                'cod_usuario_apertura' => 'USU_0001',
                'fecha_jornada' => $ayerStr,
                'estado' => 'ABIERTA',
            ]
        );

        // B) Jornada Guardia 24H de hoy
        DB::table('jornadas')->updateOrInsert(
            ['cod_jornada' => 'JOR_0002'],
            [
                'cod_turno' => 'TUR_0004',
                'cod_usuario_apertura' => 'USU_0001',
                'fecha_jornada' => $hoyStr,
                'estado' => 'ABIERTA',
            ]
        );

        // 5. ASIGNACIÓN DEL PERSONAL A LAS JORNADAS
        $asigCount = 1;
        foreach (['JOR_0001', 'JOR_0002'] as $codJor) {
            DB::table('asignaciones_personal')->updateOrInsert(
                [
                    'cod_jornada' => $codJor,
                    'cod_personal' => $personalEnf->cod_personal,
                ],
                [
                    'cod_asignacion_personal' => 'ASP_' . sprintf('%04d', $asigCount++),
                    'cod_area' => 'ARE_0001',
                    'funcion' => 'ENFERMERA DE GUARDIA',
                    'tipo_asignacion' => 'TITULAR',
                    'fecha_asignacion' => now(),
                    'estado' => 'ACTIVA',
                    'observacion' => 'Asignación de turno operativo de enfermería',
                ]
            );

            if ($personalGen) {
                DB::table('asignaciones_personal')->updateOrInsert(
                    [
                        'cod_jornada' => $codJor,
                        'cod_personal' => $personalGen->cod_personal,
                    ],
                    [
                        'cod_asignacion_personal' => 'ASP_' . sprintf('%04d', $asigCount++),
                        'cod_area' => 'ARE_0001',
                        'funcion' => 'ENFERMERO DE APOYO',
                        'tipo_asignacion' => 'TITULAR',
                        'fecha_asignacion' => now(),
                        'estado' => 'ACTIVA',
                        'observacion' => 'Asignación de enfermería institucional',
                    ]
                );
            }
        }

        // 6. INFRAESTRUCTURA (HABITACIÓN Y CAMA)
        DB::table('habitaciones')->updateOrInsert(
            ['cod_habitacion' => 'HAB_101'],
            [
                'codigo' => 'HAB-101',
                'nombre' => 'Habitación 101 — Suite Asistida',
                'tipo' => 'DOBLE',
                'piso' => 1,
                'capacidad' => 2,
                'estado' => 'ACTIVO',
                'observacion' => 'Habitación en planta baja con acceso asistido',
            ]
        );

        DB::table('camas')->updateOrInsert(
            ['cod_cama' => 'CAM_101_1'],
            [
                'cod_habitacion' => 'HAB_101',
                'codigo' => 'Cama 1',
                'tipo' => 'ARTICULADA',
                'estado' => 'OCUPADA',
                'observacion' => 'Cama eléctrica con barandillas de seguridad',
            ]
        );

        // 7. PACIENTE REAL (RESIDENTE)
        DB::table('residentes')->updateOrInsert(
            ['cod_residente' => 'RES_0001'],
            [
                'nombres' => 'Mario',
                'apellido_paterno' => 'Gutiérrez',
                'apellido_materno' => 'Mendoza',
                'numero_documento' => '2384912',
                'expedicion_documento' => 'LP',
                'fecha_nacimiento' => '1947-04-12',
                'genero' => 'MASCULINO',
                'estado_civil' => 'VIUDO',
                'telefono' => '22489123',
                'celular' => '77281934',
                'direccion' => 'Av. 20 de Octubre #1230, Sopocachi',
                'grupo_sanguineo' => 'O',
                'factor_rh' => '+',
                'estado' => 'ADMITIDO',
                'observacion' => 'Residente con hipertensión arterial controlada y riesgo moderado de caída.',
            ]
        );

        // 8. ADMISIÓN Y OCUPACIÓN DE CAMA
        DB::table('admisiones')->updateOrInsert(
            ['cod_admision' => 'ADM_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_usuario_registro' => 'USU_0001',
                'fecha_hora_admision' => '2025-01-10 10:00:00',
                'tipo_ingreso' => 'RESIDENCIA PERMANENTE',
                'motivo_ingreso' => 'Ingreso formal para atención geriátrica asistida y cuidados de enfermería.',
                'estado' => 'ACTIVA',
            ]
        );

        DB::table('ocupaciones_cama')->updateOrInsert(
            ['cod_ocupacion' => 'OCP_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_cama' => 'CAM_101_1',
                'cod_admision' => 'ADM_0001',
                'cod_usuario_registro' => 'USU_0001',
                'fecha_hora_asignacion' => '2025-01-10 10:30:00',
                'estado' => 'ACTIVA',
            ]
        );

        // 9. ASIGNACIÓN DEL RESIDENTE A LA JORNADA DE LA ENFERMERA
        $asigResCount = 1;
        foreach (['JOR_0001', 'JOR_0002'] as $codJor) {
            DB::table('asignaciones_residente_jornada')->updateOrInsert(
                [
                    'cod_jornada' => $codJor,
                    'cod_residente' => 'RES_0001',
                    'cod_personal' => $personalEnf->cod_personal,
                ],
                [
                    'cod_asignacion' => 'ARJ_' . sprintf('%04d', $asigResCount++),
                    'nivel_supervision' => 'VIGILANCIA MODERADA',
                    'fecha_hora' => now()->subHours(4),
                    'estado' => 'ACTIVA',
                    'observacion' => 'Asignación de cuidado directo de enfermería',
                ]
            );

            if ($personalGen) {
                DB::table('asignaciones_residente_jornada')->updateOrInsert(
                    [
                        'cod_jornada' => $codJor,
                        'cod_residente' => 'RES_0001',
                        'cod_personal' => $personalGen->cod_personal,
                    ],
                    [
                        'cod_asignacion' => 'ARJ_' . sprintf('%04d', $asigResCount++),
                        'nivel_supervision' => 'VIGILANCIA MODERADA',
                        'fecha_hora' => now()->subHours(4),
                        'estado' => 'ACTIVA',
                        'observacion' => 'Asignación de cuidado directo de enfermería institucional',
                    ]
                );
            }
        }

        // 10. ÚLTIMOS SIGNOS VITALES
        DB::table('signos_vitales')->updateOrInsert(
            ['cod_signo' => 'SIG_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_personal' => $personalEnf->cod_personal,
                'cod_jornada' => 'JOR_0001',
                'fecha_hora' => now()->subHours(2),
                'presion_sistolica' => 120,
                'presion_diastolica' => 80,
                'frecuencia_cardiaca' => 72,
                'frecuencia_respiratoria' => 16,
                'temperatura' => 36.5,
                'saturacion_oxigeno' => 97,
                'glucemia' => 95,
                'estado' => 'NORMAL',
                'observacion' => 'Signos vitales estables y dentro de rangos normales de referencia.',
            ]
        );

        // 11. ATENCIÓN MÉDICA PREVIA
        DB::table('atenciones')->updateOrInsert(
            ['cod_atencion' => 'ATN_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_area' => 'ARE_0001',
                'cod_personal' => 'PER_0001',
                'tipo_atencion' => 'CONTROL MÉDICO PERIÓDICO',
                'motivo' => 'Evaluación cardiovascular y ajuste terapéutico antihipertensivo.',
                'fecha_hora' => now()->subDays(5),
                'estado' => 'COMPLETADA',
                'observacion' => 'Paciente compensado, mantener tratamiento con Losartán.',
            ]
        );

        // 12. MEDICAMENTO, PRESCRIPCIÓN Y HORARIOS
        DB::table('medicamentos')->updateOrInsert(
            ['cod_medicamento' => 'MED_0001'],
            [
                'nombre_generico' => 'Losartán Potásico',
                'nombre_comercial' => 'Cozaar',
                'concentracion' => '50 mg',
                'forma_farmaceutica' => 'Comprimido recubierto',
                'unidad' => 'mg',
                'via_predeterminada' => 'ORAL',
                'control_especial' => false,
                'estado' => 'ACTIVO',
                'observacion' => 'Antihipertensivo bloqueador de receptores de angiotensina II',
            ]
        );

        DB::table('prescripciones')->updateOrInsert(
            ['cod_prescripcion' => 'PRS_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_atencion' => 'ATN_0001',
                'cod_medicamento' => 'MED_0001',
                'cod_personal' => 'PER_0001',
                'dosis' => 50,
                'unidad_dosis' => 'mg',
                'via_administracion' => 'ORAL',
                'frecuencia' => 'Cada 12 horas',
                'indicacion' => 'Tomar 1 comprimido vía oral con abundante agua por las mañanas.',
                'segun_necesidad' => false,
                'fecha_hora_prescripcion' => now()->subDays(5),
                'estado' => 'ACTIVA',
                'observacion' => 'Tratamiento continuo para hipertensión arterial esencial',
            ]
        );

        DB::table('horarios_prescripcion')->updateOrInsert(
            ['cod_horario_prescripcion' => 'HPR_0001'],
            [
                'cod_prescripcion' => 'PRS_0001',
                'hora_programada' => '08:30:00',
                'dosis_programada' => 50,
                'dias_semana' => 'LUN,MAR,MIE,JUE,VIE,SAB,DOM',
                'estado' => 'ACTIVO',
            ]
        );

        // 13. ALERTA ACTIVA DEL RESIDENTE
        DB::table('alertas')->updateOrInsert(
            ['cod_alerta' => 'ALT_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_personal_responsable' => $personalEnf->cod_personal,
                'tipo' => 'CLINICA',
                'prioridad' => 'MEDIA',
                'modulo' => 'ENFERMERIA',
                'titulo' => 'Riesgo moderado de caída',
                'descripcion' => 'El residente presenta marcha inestable y requiere asistencia para bipedestación y traslados nocturnos.',
                'fecha_hora' => now()->subHours(6),
                'generacion' => 'MANUAL',
                'estado' => 'ABIERTA',
            ]
        );

        // 14. PLAN DE CUIDADO Y PRÓXIMA ATENCIÓN PROGRAMADA
        DB::table('planes_cuidado')->updateOrInsert(
            ['cod_plan' => 'PLC_0001'],
            [
                'cod_residente' => 'RES_0001',
                'cod_area' => 'ARE_0001',
                'cod_personal' => $personalEnf->cod_personal,
                'tipo_plan' => 'ENFERMERÍA',
                'nombre' => 'Plan Integral de Monitoreo y Cuidados',
                'objetivo_general' => 'Mantener estabilidad hemodinámica y prevenir caídas durante la estancia.',
                'prioridad' => 'MEDIA',
                'fecha_hora_apertura' => now()->subDays(10),
                'estado' => 'ACTIVO',
                'observacion' => 'Plan clínico continuo activo',
            ]
        );

        DB::table('intervenciones_cuidado')->updateOrInsert(
            ['cod_intervencion' => 'INT_0001'],
            [
                'cod_plan' => 'PLC_0001',
                'nombre' => 'Control de signos vitales',
                'descripcion' => 'Toma completa de tensión arterial, pulso y saturometría en turno',
                'prioridad' => 'MEDIA',
                'estado' => 'ACTIVA',
            ]
        );

        DB::table('programaciones_cuidado')->updateOrInsert(
            ['cod_programacion' => 'PRG_0001'],
            [
                'cod_intervencion' => 'INT_0001',
                'cod_turno' => 'TUR_0003',
                'frecuencia' => 'DIARIO',
                'dias_semana' => 'LUN,MAR,MIE,JUE,VIE,SAB,DOM',
                'hora_programada' => '06:00:00',
                'fecha_activacion' => now()->subDays(10)->toDateString(),
                'estado' => 'ACTIVA',
            ]
        );
    }
}
