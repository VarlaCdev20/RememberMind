<?php

namespace Database\Seeders;

use App\Models\AccionAlerta;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\PaseTurno;
use App\Models\PlanCuidado;
use App\Models\SeguimientoDiario;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnfermeriaOperativaSeeder extends Seeder
{
    public function run(): void
    {
        $adultos = AdultoMayor::where('ci', 'like', '%-DEMO')->get();
        if ($adultos->isEmpty()) {
            $this->command->warn('[EnfermeriaOperativaSeeder] No se encontraron residentes DEMO. Ejecute ResidentesSeeder primero.');
            return;
        }

        $enfermeros = User::whereHas('roles', fn($q) => $q->where('name', 'ENFERMEROS'))->get();
        $enfermero1 = $enfermeros->first() ?? User::first();
        $enfermero2 = $enfermeros->skip(1)->first() ?? $enfermero1;

        $turnoManana   = TurnoEnfermeria::where('nombre', 'MAÑANA')->first();
        $turnoTarde    = TurnoEnfermeria::where('nombre', 'TARDE')->first();
        $turnoNoche    = TurnoEnfermeria::where('nombre', 'NOCHE')->first();

        // ── PLANES DE CUIDADO ─────────────────────────────────────────────────────

        $planesTipo = [
            ['tipo' => 'INICIAL',      'nivel' => 'ESTANDAR',     'resumen' => 'Plan inicial de cuidados para control de hipertensión y movilidad asistida.'],
            ['tipo' => 'INICIAL',      'nivel' => 'INTERMEDIO',   'resumen' => 'Plan de cuidados para seguimiento cognitivo y estimulación mental diaria.'],
            ['tipo' => 'INICIAL',      'nivel' => 'ESPECIALIZADO','resumen' => 'Plan de cuidados post-operatorio con énfasis en rehabilitación de cadera.'],
            ['tipo' => 'INICIAL',      'nivel' => 'INTERMEDIO',   'resumen' => 'Plan de cuidados para control glucémico estricto y dieta diabética.'],
            ['tipo' => 'INICIAL',      'nivel' => 'BASICO',       'resumen' => 'Plan de cuidados integral con énfasis en integración social y apoyo emocional.'],
        ];

        $tareasBase = [
            // Carmen — HTA
            [
                ['titulo' => 'Control de presión arterial',          'area' => 'ENFERMERIA', 'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA',   'estado' => 'COMPLETADA'],
                ['titulo' => 'Administración de Enalapril 10mg',     'area' => 'ENFERMERIA', 'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA',   'estado' => 'COMPLETADA'],
                ['titulo' => 'Registro de ingesta hídrica diaria',   'area' => 'ENFERMERIA', 'frecuencia' => 'DIARIA', 'prioridad' => 'MEDIA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Evaluación del estado emocional',      'area' => 'PSICOLOGIA', 'frecuencia' => 'SEMANAL','prioridad' => 'MEDIA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Sesión de fisioterapia suave',         'area' => 'FISIOTERAPIA','frecuencia' => 'SEMANAL','prioridad' => 'BAJA',  'estado' => 'PENDIENTE'],
            ],
            // Pedro — Cognitivo
            [
                ['titulo' => 'Sesión de estimulación cognitiva',     'area' => 'PSICOLOGIA',  'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA',  'estado' => 'COMPLETADA'],
                ['titulo' => 'Administración de Donepezilo',         'area' => 'ENFERMERIA',  'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA',  'estado' => 'COMPLETADA'],
                ['titulo' => 'Registro de orientación temporo-espacial', 'area' => 'ENFERMERIA', 'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA', 'estado' => 'PENDIENTE'],
                ['titulo' => 'Actividad social grupal',              'area' => 'ACTIVIDADES', 'frecuencia' => 'SEMANAL','prioridad' => 'MEDIA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Lectura guiada con voluntario',        'area' => 'VOLUNTARIADO','frecuencia' => 'SEMANAL','prioridad' => 'BAJA',   'estado' => 'PENDIENTE'],
            ],
            // Elsa — Post-op
            [
                ['titulo' => 'Ejercicios de rehabilitación de cadera', 'area' => 'FISIOTERAPIA','frecuencia' => 'DIARIA','prioridad' => 'ALTA', 'estado' => 'COMPLETADA'],
                ['titulo' => 'Administración de Tramadol 50mg',       'area' => 'ENFERMERIA',  'frecuencia' => 'CADA 8 HORAS','prioridad' => 'ALTA','estado' => 'COMPLETADA'],
                ['titulo' => 'Control de herida quirúrgica',          'area' => 'ENFERMERIA',  'frecuencia' => 'DIARIA','prioridad' => 'ALTA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Evaluación de dolor post-operatorio',   'area' => 'MEDICA',      'frecuencia' => 'DIARIA','prioridad' => 'ALTA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Suplemento de Calcio + Vitamina D',     'area' => 'ENFERMERIA',  'frecuencia' => 'DIARIA','prioridad' => 'MEDIA', 'estado' => 'PENDIENTE'],
            ],
            // Rafael — Diabetes
            [
                ['titulo' => 'Control glucémico pre-comidas',        'area' => 'ENFERMERIA', 'frecuencia' => 'CADA 8 HORAS','prioridad' => 'ALTA','estado' => 'COMPLETADA'],
                ['titulo' => 'Aplicación de Insulina Glargina',      'area' => 'ENFERMERIA', 'frecuencia' => 'DIARIA (NOCHE)','prioridad' => 'ALTA','estado' => 'COMPLETADA'],
                ['titulo' => 'Revisión de pies y extremidades',      'area' => 'ENFERMERIA', 'frecuencia' => 'DIARIA','prioridad' => 'ALTA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Supervisión dieta diabética',          'area' => 'NUTRICION',  'frecuencia' => 'DIARIA','prioridad' => 'ALTA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Ejercicio caminata supervisada 20min', 'area' => 'FISIOTERAPIA','frecuencia' => 'DIARIA','prioridad' => 'MEDIA', 'estado' => 'PENDIENTE'],
            ],
            // Josefina — Vulnerable
            [
                ['titulo' => 'Acompañamiento emocional diario',      'area' => 'PSICOLOGIA',  'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA',  'estado' => 'COMPLETADA'],
                ['titulo' => 'Administración de Sertralina 50mg',    'area' => 'ENFERMERIA',  'frecuencia' => 'DIARIA', 'prioridad' => 'ALTA',  'estado' => 'COMPLETADA'],
                ['titulo' => 'Integración a actividades grupales',   'area' => 'ACTIVIDADES', 'frecuencia' => 'SEMANAL','prioridad' => 'ALTA',  'estado' => 'PENDIENTE'],
                ['titulo' => 'Control nutricional básico',           'area' => 'NUTRICION',   'frecuencia' => 'DIARIA', 'prioridad' => 'MEDIA', 'estado' => 'PENDIENTE'],
                ['titulo' => 'Evaluación cognitiva mensual',         'area' => 'PSICOLOGIA',  'frecuencia' => 'MENSUAL','prioridad' => 'MEDIA', 'estado' => 'PENDIENTE'],
            ],
        ];

        $planesCreados = [];

        foreach ($adultos as $idx => $adulto) {
            $planTipo = $planesTipo[$idx % count($planesTipo)];

            $plan = PlanCuidado::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'tipo_plan' => $planTipo['tipo']],
                [
                    'version'     => 1,
                    'nivel_cuidado' => $planTipo['nivel'],
                    'estado'      => 'ACTIVO',
                    'origen'      => 'INGRESO',
                    'resumen'     => $planTipo['resumen'],
                    'fecha_inicio'=> now()->subDays(25)->toDateString(),
                    'creado_por'  => $enfermero1->cod_usu,
                    'validado_por'=> $enfermero1->cod_usu,
                ]
            );

            $planesCreados[$adulto->cod_am] = $plan;

            $tareasDef = $tareasBase[$idx % count($tareasBase)];
            foreach ($tareasDef as $tDef) {
                TareaPlanCuidado::firstOrCreate(
                    ['cod_plan' => $plan->cod_plan, 'titulo' => $tDef['titulo']],
                    [
                        'cod_am'           => $adulto->cod_am,
                        'cod_turno'        => $turnoManana?->cod_turno,
                        'responsable_id'   => $enfermero1->cod_usu,
                        'area'             => $tDef['area'],
                        'descripcion'      => "Tarea programada: {$tDef['titulo']}",
                        'frecuencia'       => $tDef['frecuencia'],
                        'fecha_programada' => now()->toDateString(),
                        'hora_programada'  => '08:00:00',
                        'prioridad'        => $tDef['prioridad'],
                        'estado'           => $tDef['estado'],
                        'resultado'        => $tDef['estado'] === 'COMPLETADA' ? 'Tarea completada sin incidencias.' : null,
                        'registrado_por'   => $enfermero1->cod_usu,
                    ]
                );
            }

            // --- SeguimientoDiario (3 días por adulto) ---
            foreach ([2, 1, 0] as $dAtras) {
                $fecha = now()->subDays($dAtras)->toDateString();
                SeguimientoDiario::firstOrCreate(
                    ['cod_am' => $adulto->cod_am, 'fecha' => $fecha],
                    [
                        'cod_turno'              => $turnoManana?->cod_turno,
                        'cod_plan'               => $plan->cod_plan,
                        'registrado_por'         => $enfermero1->cod_usu,
                        'hora_inicio'            => '07:00:00',
                        'hora_fin'               => '13:00:00',
                        'estado_general'         => $idx < 3 ? 'BUENO' : 'REGULAR',
                        'alimentacion'           => 'COMPLETA',
                        'porcentaje_alimentacion'=> $idx === 4 ? 75 : 100,
                        'hidratacion'            => 'ADECUADA',
                        'movilidad'              => $idx === 2 ? 'CON AYUDA' : 'AUTÓNOMO',
                        'intento_caminar_solo'   => $idx !== 2,
                        'higiene'                => 'COMPLETA',
                        'sueno'                  => $idx < 2 ? 'BUENO' : 'IRREGULAR',
                        'orientacion'            => $idx === 1 ? 'PARCIAL' : 'COMPLETA',
                        'repite_preguntas'       => $idx === 1,
                        'confusion_observable'   => false,
                        'conducta'               => 'TRANQUILA',
                        'participacion'          => $idx < 4 ? 'ACTIVA' : 'PASIVA',
                        'incidente'              => false,
                        'requiere_medico'        => false,
                        'observacion'            => 'Turno sin incidentes. Residente estable.',
                    ]
                );
            }
        }

        // ── ALERTAS ───────────────────────────────────────────────────────────────

        $alertasDef = [
            [
                'adulto_idx' => 0,
                'origen'     => 'USUARIO',
                'tipo'       => 'MEDICA',
                'nivel'      => 'MEDIO',
                'motivo'     => 'Presión arterial elevada 160/100 mmHg. Fuera del rango de control habitual.',
                'estado'     => 'CERRADA',
            ],
            [
                'adulto_idx' => 1,
                'origen'     => 'SISTEMA',
                'tipo'       => 'CONDUCTUAL',
                'nivel'      => 'MEDIO',
                'motivo'     => 'Residente desorientado durante la noche. Intento de salir del cuarto en dos ocasiones.',
                'estado'     => 'EN_ATENCION',
            ],
            [
                'adulto_idx' => 2,
                'origen'     => 'USUARIO',
                'tipo'       => 'MEDICA',
                'nivel'      => 'ALTO',
                'motivo'     => 'Dolor post-operatorio con escala NRS 7/10 a pesar de Tramadol. Requiere evaluación médica urgente.',
                'estado'     => 'CERRADA',
            ],
            [
                'adulto_idx' => 3,
                'origen'     => 'SISTEMA',
                'tipo'       => 'MEDICA',
                'nivel'      => 'ALTO',
                'motivo'     => 'Glucemia en ayunas 280 mg/dL. Hiperglucemia severa. Ajuste de insulina requerido.',
                'estado'     => 'EN_ATENCION',
            ],
            [
                'adulto_idx' => 4,
                'origen'     => 'USUARIO',
                'tipo'       => 'NUTRICIONAL',
                'nivel'      => 'MEDIO',
                'motivo'     => 'Residente rechaza alimentos desde hace 2 días. Riesgo de desnutrición. Evaluación nutricional urgente.',
                'estado'     => 'ABIERTA',
            ],
        ];

        foreach ($alertasDef as $aDef) {
            $adulto = $adultos->get($aDef['adulto_idx'] % $adultos->count());
            if (! $adulto) continue;

            $alerta = AlertaAdulto::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'tipo_alerta' => $aDef['tipo'], 'motivo' => substr($aDef['motivo'], 0, 50)],
                [
                    'cod_turno'    => $turnoManana?->cod_turno,
                    'origen'       => $aDef['origen'],
                    'tipo_alerta'  => $aDef['tipo'],
                    'nivel'        => $aDef['nivel'],
                    'motivo'       => $aDef['motivo'],
                    'responsable_id'=> $enfermero1->cod_usu,
                    'estado'       => $aDef['estado'],
                    'fecha_atencion' => $aDef['estado'] !== 'ABIERTA' ? now()->subDays(1) : null,
                    'atendido_por' => $aDef['estado'] !== 'ABIERTA' ? $enfermero1->cod_usu : null,
                    'fecha_cierre' => $aDef['estado'] === 'CERRADA' ? now() : null,
                    'cerrado_por'  => $aDef['estado'] === 'CERRADA' ? $enfermero1->cod_usu : null,
                    'observacion_cierre' => $aDef['estado'] === 'CERRADA' ? 'Situación resuelta. Residente estabilizado.' : null,
                    'accion_tomada'=> $aDef['estado'] !== 'ABIERTA' ? 'Se atendió según protocolo institucional.' : null,
                ]
            );

            if (AccionAlerta::where('cod_alerta', $alerta->cod_alerta)->doesntExist()) {
                AccionAlerta::create([
                    'cod_alerta'    => $alerta->cod_alerta,
                    'accion'        => 'Evaluación inmediata por enfermero a cargo. Notificación al médico de guardia.',
                    'responsable_id'=> $enfermero1->cod_usu,
                    'fecha_accion'  => now()->subHours(2),
                    'estado'        => $aDef['estado'] === 'CERRADA' ? 'REALIZADA' : 'PENDIENTE',
                    'observacion'   => 'Acción ejecutada según protocolo de urgencias institucional.',
                ]);

                if ($aDef['estado'] !== 'ABIERTA') {
                    AccionAlerta::create([
                        'cod_alerta'    => $alerta->cod_alerta,
                        'accion'        => 'Seguimiento post-atención. Monitoreo cada 2 horas durante 12 horas.',
                        'responsable_id'=> $enfermero2->cod_usu,
                        'fecha_accion'  => now()->subHour(),
                        'estado'        => 'REALIZADA',
                        'observacion'   => 'Seguimiento completado. Residente estable.',
                    ]);
                }
            }
        }

        // ── ASIGNACIONES DE TURNO ─────────────────────────────────────────────────

        foreach ($adultos as $idx => $adulto) {
            AsignacionTurnoAdulto::firstOrCreate(
                ['cod_am' => $adulto->cod_am, 'cod_turno' => $turnoManana?->cod_turno],
                [
                    'cod_usu_enfermero' => $enfermero1->cod_usu,
                    'cod_habitacion'    => $adulto->cod_habitacion,
                    'cod_cama'          => $adulto->cod_cama,
                    'fecha_inicio'      => now()->subDays(20)->toDateString(),
                    'nivel_supervision' => $idx >= 2 ? 'ALTO' : 'ESTANDAR',
                    'estado'            => 'ACTIVO',
                    'motivo_asignacion' => 'Asignación inicial según turno de enfermería.',
                    'asignado_por'      => $enfermero1->cod_usu,
                ]
            );
        }

        // ── PASES DE TURNO ────────────────────────────────────────────────────────

        foreach ($adultos->take(5) as $idx => $adulto) {
            if (PaseTurno::where('cod_am', $adulto->cod_am)->doesntExist()) {
                PaseTurno::create([
                    'cod_am'                       => $adulto->cod_am,
                    'turno_saliente_id'             => $turnoManana?->cod_turno,
                    'turno_entrante_id'             => $turnoTarde?->cod_turno,
                    'enfermero_saliente_id'         => $enfermero1->cod_usu,
                    'enfermero_entrante_id'         => $enfermero2->cod_usu,
                    'fecha'                         => now()->subDay()->toDateString(),
                    'estado_general_cierre'         => 'ESTABLE',
                    'resumen_turno'                 => "Turno sin incidencias críticas para {$adulto->nombres} {$adulto->ap_paterno}. Todas las tareas programadas ejecutadas.",
                    'tareas_realizadas_json'        => ['Control de signos vitales', 'Administración de medicación', 'Higiene personal'],
                    'tareas_pendientes_json'        => ['Revisión médica pendiente'],
                    'alertas_activas_json'          => [],
                    'recomendacion_siguiente_turno' => 'Mantener monitoreo habitual. Sin indicaciones especiales.',
                    'requiere_vigilancia_especial'  => $idx >= 3,
                    'motivo_vigilancia'             => $idx >= 3 ? 'Condición clínica que requiere observación frecuente.' : null,
                    'estado'                        => 'RECIBIDO',
                    'fecha_recibido'                => now()->subDay()->setTime(12, 5),
                ]);
            }
        }

        $this->command->info('[EnfermeriaOperativaSeeder] Planes de cuidado, tareas, seguimientos, alertas, asignaciones de turno y pases de turno creados para 5 residentes.');
    }
}
