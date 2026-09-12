<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdultoMayor;
use App\Models\EstadoAdulto;
use App\Models\Habitacion;
use App\Models\Cama;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\SignosVitalesAdulto;
use App\Models\PlanCuidado;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\AlertaAdulto;
use App\Models\AdministracionMedicacion;
use App\Models\MedicacionAdulto;
use App\Models\Preadmision;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Seeder completo para poblar datos de demostración del módulo de enfermería.
 *
 * Schemas reales verificados:
 * - preadmisiones: cod_pre, estado, fecha_solicitud, fecha_asignacion, nombres, ap_paterno, ap_materno, ci, expedicion_ci, fecha_nac, genero, estado_civil, telefono, celular, departamento_residencia, ciudad_municipio, zona, calle, direccion_referencia, familiar_nombres, familiar_ap_paterno, familiar_ap_materno, familiar_ci, familiar_parentesco, familiar_celular, familiar_correo, familiar_direccion, motivo_ingreso, procedencia_ingreso, tipo_ingreso, permanencia, prioridad, descripcion_caso, documentos_iniciales_completos, documentos_institucionales_generados, enfermero_asignado, creado_por, observaciones, created_at, updated_at, motivo_rechazo, observacion_rechazo, fecha_rechazo, rechazado_por, fecha_aprobacion, aprobado_por, cod_am_generado, cod_fam_generado
 * - alertas_adulto: cod_alerta, cod_am, cod_turno, origen, tipo_alerta, nivel, motivo, responsable_id, estado, accion_tomada, fecha_atencion, atendido_por, fecha_cierre, cerrado_por, observacion_cierre, created_at, updated_at
 * - medicacion_adulto: cod_med_adulto, cod_am, nombre_medicamento, dosis, frecuencia, via_administracion, hora_programada, fecha_inicio, fecha_fin, medico_indica, documento_receta, estado, observacion, registrado_por, created_at, updated_at, deleted_at
 * - administracion_medicacion: cod_admin_med, cod_med_adulto, cod_am, fecha, hora_programada, hora_real, administrado, motivo_omision, efecto_observado, observacion, registrado_por, created_at, updated_at
 * - planes_cuidado: cod_plan, cod_am, tipo_plan, version, nivel_cuidado, estado, origen, resumen, fecha_inicio, fecha_fin, creado_por, validado_por, created_at, updated_at, deleted_at
 * - tareas_plan_cuidado: cod_tarea, cod_plan, cod_am, cod_turno, responsable_id, area, titulo, descripcion, frecuencia, fecha_programada, hora_programada, prioridad, estado, fecha_realizada, resultado, observacion, motivo_omision, transferida_a_turno_id, registrado_por, created_at, updated_at
 * - asignaciones_turno_adulto: cod_asig_turno, cod_am, cod_turno, cod_usu_enfermero, cod_habitacion, cod_cama, fecha_inicio, fecha_fin, nivel_supervision, estado, motivo_asignacion, asignado_por, created_at, updated_at
 * - signos_vitales_adulto: cod_signo, cod_am, fecha, hora, presion_arterial, presion_sistolica, presion_diastolica, frecuencia_cardiaca, frecuencia_respiratoria, temperatura, saturacion, glucosa, peso, talla, imc, dolor, observacion, registrado_por, estado, motivo_anulacion, anulado_por, fecha_anulacion, created_at, updated_at
 */
class DatosEnfermeriaDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 1. ENFERMERO DE DEMO
        // ═══════════════════════════════════════════════════════════════
        Role::firstOrCreate(['name' => 'ENFERMEROS']);
        $enfermero = User::firstOrCreate(
            ['correo' => 'enfermero@casaamandita.com'],
            [
                'cod_usu' => 'USU_E' . rand(100, 999),
                'nombres' => 'Juan',
                'ap_paterno' => 'Perez',
                'password' => Hash::make('password'),
                'estado' => 'ACTIVO'
            ]
        );
        if (!$enfermero->hasRole('ENFERMEROS')) {
            $enfermero->assignRole('ENFERMEROS');
        }

        // ═══════════════════════════════════════════════════════════════
        // 2. TURNO DE ENFERMERÍA
        // ═══════════════════════════════════════════════════════════════
        $turnoActual = TurnoEnfermeria::whereTime('hora_inicio', '<=', now()->format('H:i:s'))
            ->whereTime('hora_fin', '>=', now()->format('H:i:s'))
            ->first();

        if (!$turnoActual) {
            $turnoActual = TurnoEnfermeria::first();
        }

        if (!$turnoActual) {
            $maxOrden = TurnoEnfermeria::max('orden') ?? 0;
            $turnoActual = TurnoEnfermeria::create([
                'nombre' => 'Turno Día Completo',
                'hora_inicio' => '00:00:00',
                'hora_fin' => '23:59:59',
                'estado' => 'ACTIVO',
                'orden' => $maxOrden + 1
            ]);
        }

        // ═══════════════════════════════════════════════════════════════
        // 3. ESTADOS DE FLUJO CLÍNICO
        // ═══════════════════════════════════════════════════════════════
        $estadoSeguimiento = EstadoAdulto::where('estado', 'EN_SEGUIMIENTO_ACTIVO')->value('cod_est_adul')
            ?? EstadoAdulto::where('estado', 'ACTIVO')->value('cod_est_adul');
        $estadoPreadmision = EstadoAdulto::where('estado', 'PENDIENTE_VALORACION_ENFERMERIA')->value('cod_est_adul')
            ?? EstadoAdulto::where('estado', 'PREADMISION')->value('cod_est_adul')
            ?? $estadoSeguimiento;

        // ═══════════════════════════════════════════════════════════════
        // 4. CREAR 3 PACIENTES CON DATOS COMPLETOS
        // ═══════════════════════════════════════════════════════════════
        $pacientesData = [
            [
                'nombres' => 'Roberto', 'ap_paterno' => 'Choque', 'ap_materno' => 'Condori',
                'ci' => '1111111-DEMO', 'hab' => '101', 'nivel' => 'ALTA',
                'genero' => 'MASCULINO', 'edad' => 78,
            ],
            [
                'nombres' => 'María', 'ap_paterno' => 'Mamani', 'ap_materno' => 'Quispe',
                'ci' => '2222222-DEMO', 'hab' => '102', 'nivel' => 'MEDIA',
                'genero' => 'FEMENINO', 'edad' => 82,
            ],
            [
                'nombres' => 'José', 'ap_paterno' => 'Condori', 'ap_materno' => 'Flores',
                'ci' => '3333333-DEMO', 'hab' => '103', 'nivel' => 'BAJA',
                'genero' => 'MASCULINO', 'edad' => 71,
            ],
        ];

        foreach ($pacientesData as $index => $data) {
            // ── Habitación y Cama ──
            $habitacion = Habitacion::firstOrCreate(
                ['codigo' => 'HAB-' . $data['hab']],
                ['nombre' => 'Habitacion ' . $data['hab'], 'tipo_habitacion' => 'INDIVIDUAL', 'capacidad' => 1, 'estado' => 'DISPONIBLE']
            );
            $cama = Cama::firstOrCreate(
                ['codigo' => 'CAM-' . $data['hab']],
                ['cod_habitacion' => $habitacion->cod_habitacion, 'estado' => 'DISPONIBLE']
            );

            // ── Adulto Mayor ──
            $paciente = AdultoMayor::firstOrCreate(
                ['ci' => $data['ci']],
                [
                    'nombres' => $data['nombres'],
                    'ap_paterno' => $data['ap_paterno'],
                    'ap_materno' => $data['ap_materno'],
                    'fecha_nac' => Carbon::now()->subYears($data['edad'])->toDateString(),
                    'genero' => $data['genero'],
                    'estado_civil' => 'VIUDO',
                    'cod_est_adul' => $estadoSeguimiento,
                    'fecha_ing' => now()->toDateString(),
                    'hora_ing' => '08:00',
                    'tipo_ing' => 'REGULAR',
                    'permanencia' => 'PERMANENTE',
                    'nivel_educat' => 'PRIMARIA',
                    'grupo_sanguineo' => 'O+',
                    'factor_rh' => '+',
                    'alergias' => 'PENICILINA',
                    'seguro_salud' => 'SUS',
                    'contacto_emergencia_nombre' => 'Familiar ' . $data['nombres'],
                    'contacto_emergencia_parentesco' => 'HIJO/A',
                    'contacto_emergencia_celular' => '7000000' . $index,
                    'responsable_principal' => true,
                    'autorizado_informacion_medica' => true,
                    'consentimiento_datos' => true,
                    'cod_habitacion' => $habitacion->cod_habitacion,
                    'cod_cama' => $cama->cod_cama,
                ]
            );

            // ── Asignación paciente a cama ──
            AsignacionAdultoMayor::firstOrCreate(
                ['cod_am' => $paciente->cod_am],
                [
                    'cod_habitacion' => $habitacion->cod_habitacion,
                    'cod_cama' => $cama->cod_cama,
                    'fecha_asignacion' => now()->toDateString(),
                    'hora_asignacion' => now()->toTimeString(),
                    'estado' => 'ACTIVO',
                    'registrado_por' => $enfermero->cod_usu
                ]
            );

            // ── Asignación a turno y enfermero (VITAL para dashboard) ──
            AsignacionTurnoAdulto::firstOrCreate(
                ['cod_am' => $paciente->cod_am, 'cod_turno' => $turnoActual->cod_turno, 'cod_usu_enfermero' => $enfermero->cod_usu],
                [
                    'cod_habitacion' => $habitacion->cod_habitacion,
                    'cod_cama' => $cama->cod_cama,
                    'fecha_inicio' => now()->toDateString(),
                    'nivel_supervision' => $data['nivel'],
                    'estado' => 'ACTIVO',
                    'asignado_por' => $enfermero->cod_usu,
                ]
            );

            // ── Plan de Cuidado ──
            $plan = PlanCuidado::firstOrCreate(
                ['cod_am' => $paciente->cod_am],
                [
                    'tipo_plan' => 'GENERAL',
                    'version' => 1,
                    'nivel_cuidado' => 'INTERMEDIO',
                    'estado' => 'ACTIVO',
                    'origen' => 'ENFERMERIA',
                    'resumen' => 'Plan de cuidado integral para ' . $data['nombres'],
                    'fecha_inicio' => now()->toDateString(),
                    'creado_por' => $enfermero->cod_usu,
                ]
            );

            // ── Tareas de Enfermería (4 por paciente) ──
            $tareas = [
                ['Control de Signos Vitales (Mañana)', 'MEDIA', 'REALIZADA', 0],
                ['Administración de Insulina', 'ALTA', 'PENDIENTE', 1],
                ['Baño de Esponja Asistido', 'BAJA', 'PENDIENTE', 2],
                ['Terapia Física Programada', 'MEDIA', 'OMITIDA', 3],
            ];

            foreach ($tareas as $t) {
                TareaPlanCuidado::firstOrCreate(
                    [
                        'cod_am' => $paciente->cod_am,
                        'cod_turno' => $turnoActual->cod_turno,
                        'fecha_programada' => now()->toDateString(),
                        'titulo' => $t[0],
                    ],
                    [
                        'cod_plan' => $plan->cod_plan,
                        'area' => 'ENFERMERIA',
                        'descripcion' => 'Tarea autogenerada para demo',
                        'hora_programada' => now()->copy()->startOfDay()->addHours(8 + $t[3])->toTimeString(),
                        'prioridad' => $t[1],
                        'estado' => $t[2],
                        'registrado_por' => $enfermero->cod_usu,
                    ]
                );
            }

            // ── Medicación (prescripción + administración) ──
            $medicacion = MedicacionAdulto::firstOrCreate(
                ['cod_am' => $paciente->cod_am, 'nombre_medicamento' => 'Paracetamol 500mg'],
                [
                    'dosis' => '1 Tableta',
                    'frecuencia' => 'Cada 8 horas',
                    'via_administracion' => 'Oral',
                    'hora_programada' => '08:00',
                    'fecha_inicio' => now()->toDateString(),
                    'estado' => 'ACTIVO',
                    'registrado_por' => $enfermero->cod_usu,
                ]
            );

            AdministracionMedicacion::firstOrCreate(
                ['cod_med_adulto' => $medicacion->cod_med_adulto, 'cod_am' => $paciente->cod_am, 'fecha' => now()->toDateString()],
                [
                    'hora_programada' => '08:00',
                    'hora_real' => $index % 2 == 0 ? now()->toTimeString() : null,
                    'administrado' => $index % 2 == 0,
                    'motivo_omision' => $index % 2 == 0
                        ? null
                        : 'Residente en ayuno indicado para valoración clínica.',
                    'registrado_por' => $enfermero->cod_usu,
                ]
            );

            // ── Signos Vitales ──
            SignosVitalesAdulto::firstOrCreate(
                ['cod_am' => $paciente->cod_am, 'fecha' => now()->toDateString()],
                [
                    'hora' => now()->toTimeString(),
                    'presion_sistolica' => rand(110, 140),
                    'presion_diastolica' => rand(70, 90),
                    'frecuencia_cardiaca' => rand(60, 100),
                    'frecuencia_respiratoria' => rand(12, 20),
                    'temperatura' => rand(360, 375) / 10,
                    'saturacion' => rand(90, 100),
                    'registrado_por' => $enfermero->cod_usu,
                ]
            );

            // ── Alertas Clínicas ──
            $nivelesAlerta = ['LEVE', 'MODERADA', 'CRITICA'];
            $tiposAlerta = ['CAMBIO_SIGNOS_VITALES', 'CAIDA', 'REACCION_ADVERSA'];
            AlertaAdulto::firstOrCreate(
                ['cod_am' => $paciente->cod_am, 'nivel' => $nivelesAlerta[$index]],
                [
                    'cod_turno' => $turnoActual->cod_turno,
                    'origen' => 'ENFERMERIA',
                    'tipo_alerta' => $tiposAlerta[$index],
                    'motivo' => 'Alerta generada para demostración clínica.',
                    'responsable_id' => $enfermero->cod_usu,
                    'estado' => 'NUEVA',
                ]
            );
        }

        // ═══════════════════════════════════════════════════════════════
        // 5. PREADMISIÓN PENDIENTE DE VALORACIÓN (aparece en triage)
        // ═══════════════════════════════════════════════════════════════
        $preadmision = Preadmision::firstOrCreate(
            ['ci' => '9999999-PRE'],
            [
                // cod_pre se auto-genera — no forzar PRE_DEMO_01 que ya usa AdultoConPreadmisionAprobadaSeeder
                'nombres' => 'Carmen',
                'ap_paterno' => 'Rosa',
                'ap_materno' => 'Vargas',
                'fecha_nac' => '1950-01-01',
                'genero' => 'FEMENINO',
                'estado' => 'APROBADA',
                'fecha_solicitud' => now()->toDateString(),
                'fecha_aprobacion' => now()->toDateString(),
                'aprobado_por' => $enfermero->cod_usu,
                'familiar_nombres' => 'Pedro',
                'familiar_ap_paterno' => 'Rosa',
                'familiar_ap_materno' => 'López',
                'familiar_ci' => '8888888',
                'familiar_parentesco' => 'HIJO/A',
                'familiar_celular' => '71234567',
                'motivo_ingreso' => 'ABANDONO_SOCIAL',
                'procedencia_ingreso' => 'FAMILIA',
                'tipo_ingreso' => 'REGULAR',
                'permanencia' => 'PERMANENTE',
                'prioridad' => 'MEDIA',
                'descripcion_caso' => 'Paciente de demo para preadmisión.',
                'enfermero_asignado' => $enfermero->cod_usu,
                'creado_por' => $enfermero->cod_usu,
            ]
        );

        if (!$preadmision->cod_am_generado) {
            $pacienteGenerado = AdultoMayor::firstOrCreate(
                ['ci' => '9999999-PRE'],
                [
                    'nombres' => 'Carmen',
                    'ap_paterno' => 'Rosa',
                    'ap_materno' => 'Vargas',
                    'fecha_nac' => '1950-01-01',
                    'genero' => 'FEMENINO',
                    'estado_civil' => 'SOLTERO/A',
                    // Adulto generado desde preadmisión APROBADA → estado ADMITIDO, no de valoración
                    'cod_est_adul' => EstadoAdulto::firstOrCreate(['estado' => 'ADMITIDO'], ['cod_est_adul' => 'EST_014'])->cod_est_adul,

                    'fecha_ing' => now()->toDateString(),
                    'hora_ing' => '08:00',
                    'tipo_ing' => 'REGULAR',
                    'permanencia' => 'PERMANENTE',
                    'nivel_educat' => 'PRIMARIA',
                    'grupo_sanguineo' => 'O+',
                    'factor_rh' => '+',
                    'alergias' => 'NINGUNA',
                    'seguro_salud' => 'SUS',

                    'contacto_emergencia_nombre' => 'Pedro Rosa López',
                    'contacto_emergencia_parentesco' => 'HIJO/A',
                    'contacto_emergencia_celular' => '71234567',
                    'contacto_emergencia_direccion' => 'Sin dirección registrada',

                    'responsable_principal' => true,
                    'autorizado_informacion_medica' => true,
                    'consentimiento_datos' => true,

                    'motivo_ingreso' => 'ABANDONO_SOCIAL',
                    'procedencia_ingreso' => 'FAMILIA',
                    'cod_pre_origen' => $preadmision->cod_pre,
                ]
            );

            $preadmision->update(['cod_am_generado' => $pacienteGenerado->cod_am]);
        }

        $this->command->info("✅ Datos de enfermería generados exitosamente:");
        $this->command->info("   → 3 pacientes con: signos vitales, tareas, alertas, medicación y plan de cuidado");
        $this->command->info("   → 1 preadmisión pendiente de valoración (triage)");
        $this->command->info("   → Usuario: enfermero@casaamandita.com / password");
    }
}
