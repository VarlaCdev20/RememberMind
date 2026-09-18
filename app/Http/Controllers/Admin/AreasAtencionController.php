<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AlertaClinica;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AreasAtencionController extends Controller
{
    /**
     * Muestra el Centro de Mando de Áreas de Atención para Superadministrador.
     */
    public function index(Request $request)
    {
        // 1. Métricas de contexto institucional
        $totalResidentes = AdultoMayor::count();
        $totalUsuarios = User::where('estado', 'ACTIVO')->count();
        
        $alertasActivas = 0;
        try {
            $alertasActivas = AlertaClinica::where('estado', 'ABIERTA')->count();
        } catch (\Throwable $e) {
            try {
                $alertasActivas = AlertaAdulto::whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count();
            } catch (\Throwable $e2) {
                $alertasActivas = 0;
            }
        }

        $turnosEnfermeriaCount = 0;
        try {
            $turnosEnfermeriaCount = TurnoEnfermeria::where('estado', 'ACTIVO')->count();
        } catch (\Throwable $e) {
            $turnosEnfermeriaCount = 0;
        }

        // 2. Definición estructurada de Áreas de Atención por Rol y sus Vistas
        $areas = [
            // ÁREA DE ENFERMERÍA Y CUIDADOS
            'enfermeria' => [
                'id' => 'enfermeria',
                'nombre' => 'Área de Enfermería y Cuidados Continuos',
                'rol_principal' => 'ENFERMEROS',
                'rol_etiqueta' => 'Enfermero/a y Supervisión de Guardia',
                'icono' => 'ph-first-aid',
                'color' => 'teal',
                'color_bg' => 'bg-teal-500/10 dark:bg-teal-500/20 text-teal-700 dark:text-teal-300 border-teal-200 dark:border-teal-800',
                'color_pill' => 'bg-teal-600 text-white',
                'descripcion' => 'Monitoreo de guardia activa, administración farmacológica, confort integral y supervisión rotativa de cuidados geriátricos.',
                'subdivisiones' => [
                    'operativa' => [
                        'titulo' => 'Vista Completa de Enfermería (Atención Diaria)',
                        'subtitulo' => 'Herramientas operativas directas para el cuidado en piso, administración y seguimiento',
                        'icono' => 'ph-hand-heart',
                        'vistas' => [
                            [
                                'titulo' => 'Mi Turno / Dashboard Activo',
                                'subtitulo' => 'Monitoreo de la guardia operativa actual y novedades prioritarias',
                                'ruta' => 'admin.enfermeria.dashboard',
                                'icono' => 'ph-gauge',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Todos los Residentes / Mis Pacientes',
                                'subtitulo' => 'Censo de residentes, alertas vitales y estado del cuidado',
                                'ruta' => 'admin.enfermeria.pacientes',
                                'icono' => 'ph-users-three',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Ficha de Cuidados del Residente',
                                'subtitulo' => 'Expediente clínico integral, diagnósticos y plan de cuidados',
                                'ruta' => 'admin.salud-seguimiento.ficha.index',
                                'icono' => 'ph-folder-user',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Agenda de Cuidados',
                                'subtitulo' => 'Cronograma diario de atenciones programadas y curaciones',
                                'ruta' => 'admin.enfermeria.agenda',
                                'icono' => 'ph-calendar-check',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Medicación Prescrita',
                                'subtitulo' => 'Órdenes médicas vigentes, pautas, dosis y recetas',
                                'ruta' => 'admin.salud-seguimiento.medicacion.index',
                                'icono' => 'ph-pill',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Kardex y Administraciones',
                                'subtitulo' => 'Registro de dosis aplicadas, confirmación y control de fármacos',
                                'ruta' => 'admin.salud-seguimiento.administracion.index',
                                'icono' => 'ph-syringe',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Cuidados e Incidentes Diarios',
                                'subtitulo' => 'Registro de ingestas, evacuaciones, caídas y contingencias',
                                'ruta' => 'admin.enfermeria.registros',
                                'icono' => 'ph-notepad',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Planes y Tareas Asistenciales',
                                'subtitulo' => 'Planes de confort geriátrico y checklist de tareas por turno',
                                'ruta' => 'admin.enfermeria.tareas',
                                'icono' => 'ph-check-square-offset',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Valoraciones Iniciales de Enfermería',
                                'subtitulo' => 'Tamizaje de ingreso de enfermería y recomendaciones para geriatría',
                                'ruta' => 'admin.admision.valoracion-enfermeria',
                                'icono' => 'ph-clipboard-text',
                                'tipo' => 'Operativa',
                            ],
                        ],
                    ],
                    'supervision' => [
                        'titulo' => 'Supervisión y Gestión de Cuidados',
                        'subtitulo' => 'Coordinación institucional de guardias, relevos, asignaciones y calidad',
                        'icono' => 'ph-shield-check',
                        'vistas' => [
                            [
                                'titulo' => 'Resumen Global Institucional',
                                'subtitulo' => 'Consola unificada de guardias con visión global de todos los sectores',
                                'ruta' => 'admin.enfermeria.dashboard',
                                'icono' => 'ph-chart-pie-slice',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Gestión de Turnos de Enfermería',
                                'subtitulo' => 'Configuración de turnos mañana, tarde, noche y horarios asistenciales',
                                'ruta' => 'admin.turnos-enfermeria.index',
                                'icono' => 'ph-clock-clockwise',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Asignación de Pacientes por Turno',
                                'subtitulo' => 'Matriz de asignación de adultos mayores al personal asignado',
                                'ruta' => 'admin.asignacion-turno.index',
                                'icono' => 'ph-arrows-left-right',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Pases y Relevos de Turno',
                                'subtitulo' => 'Protocolo formal de traspaso de novedades entre personal entrante y saliente',
                                'ruta' => 'admin.enfermeria.pase-turno',
                                'icono' => 'ph-arrows-clockwise',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Consola de Alertas de Guardia',
                                'subtitulo' => 'Supervisión y cierre de alarmas clínicas y de cuidado prioritario',
                                'ruta' => 'admin.enfermeria.alertas',
                                'icono' => 'ph-warning-octagon',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Reportes y Evolución de Enfermería',
                                'subtitulo' => 'Análisis de ingesta, dolor, hidratación y horas asistenciales cubiertas',
                                'ruta' => 'admin.enfermeria.reportes',
                                'icono' => 'ph-chart-line-up',
                                'tipo' => 'Supervisión',
                            ],
                        ],
                    ],
                ],
            ],

            // ÁREA MÉDICA Y GERIÁTRICA
            'medico' => [
                'id' => 'medico',
                'nombre' => 'Área Médica y Geriátrica',
                'rol_principal' => 'MEDICO GENERAL/GERIATRA',
                'rol_etiqueta' => 'Médico General / Geriatra',
                'icono' => 'ph-stethoscope',
                'color' => 'emerald',
                'color_bg' => 'bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'color_pill' => 'bg-emerald-600 text-white',
                'descripcion' => 'Diagnóstico geriátrico, prescripción de pautas farmacológicas, seguimiento de patologías crónicas e interconsultas.',
                'subdivisiones' => [
                    'clinica' => [
                        'titulo' => 'Vistas Clínicas y Asistenciales',
                        'subtitulo' => 'Atención médica directa, historias clínicas y monitorización biomédica',
                        'icono' => 'ph-heartbeat',
                        'vistas' => [
                            [
                                'titulo' => 'Dashboard Médico Central',
                                'subtitulo' => 'Panel clínico principal, consultas programadas e indicadores médicos',
                                'ruta' => 'admin.medico.dashboard',
                                'icono' => 'ph-stethoscope',
                                'tipo' => 'Clínica',
                            ],
                            [
                                'titulo' => 'Ficha Médica y Pacientes en Observación',
                                'subtitulo' => 'Historial clínico longitudinal, antecedentes y evolución geriátrica',
                                'ruta' => 'admin.medico.pacientes.observacion',
                                'icono' => 'ph-folder-user',
                                'tipo' => 'Clínica',
                            ],
                            [
                                'titulo' => 'Valoraciones de Admisión Médica',
                                'subtitulo' => 'Historia de ingreso, examen físico integral y pronóstico',
                                'ruta' => 'admin.medico.valoraciones',
                                'icono' => 'ph-clipboard-text',
                                'tipo' => 'Evaluación',
                            ],
                            [
                                'titulo' => 'Monitoreo de Signos Vitales',
                                'subtitulo' => 'Curvas de presión arterial, saturometría, frecuencia y temperatura',
                                'ruta' => 'admin.medico.signos-vitales',
                                'icono' => 'ph-heartbeat',
                                'tipo' => 'Clínica',
                            ],
                            [
                                'titulo' => 'Interconsultas Especializadas',
                                'subtitulo' => 'Derivaciones a especialistas externos y retorno diagnóstico',
                                'ruta' => 'admin.medico.interconsultas',
                                'icono' => 'ph-chats-circle',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Alertas Clínicas Médicas',
                                'subtitulo' => 'Eventos médicos prioritarios y decisiones de hospitalización',
                                'ruta' => 'admin.salud-seguimiento.alertas',
                                'icono' => 'ph-bell-ringing',
                                'tipo' => 'Supervisión',
                            ],
                        ],
                    ],
                ],
            ],

            // ÁREA DE PSICOLOGÍA Y SALUD MENTAL
            'psicologia' => [
                'id' => 'psicologia',
                'nombre' => 'Área de Psicología y Salud Mental',
                'rol_principal' => 'PSICOLOGO/A',
                'rol_etiqueta' => 'Psicólogo/a Clínico y Neurocognitivo',
                'icono' => 'ph-brain',
                'color' => 'purple',
                'color_bg' => 'bg-purple-500/10 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800',
                'color_pill' => 'bg-purple-600 text-white',
                'descripcion' => 'Evaluación y estimulación cognitiva, bienestar emocional, manejo de alteraciones conductuales y soporte socioafectivo.',
                'subdivisiones' => [
                    'psicologia' => [
                        'titulo' => 'Vistas Psicológicas y Neurocognitivas',
                        'subtitulo' => 'Baterías estandarizadas de evaluación y seguimiento neuropsicológico',
                        'icono' => 'ph-puzzle-piece',
                        'vistas' => [
                            [
                                'titulo' => 'Dashboard de Psicología',
                                'subtitulo' => 'Panel de control de salud mental y alertas emocionales',
                                'ruta' => 'admin.psicologia.dashboard',
                                'icono' => 'ph-brain',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Mis Pacientes y Evaluaciones Asignadas',
                                'subtitulo' => 'Lista de adultos mayores en seguimiento psicológico activo',
                                'ruta' => 'admin.psicologia.evaluaciones',
                                'icono' => 'ph-users-three',
                                'tipo' => 'Clínica',
                            ],
                            [
                                'titulo' => 'Evaluación Cognitiva (MMSE, MoCA, Mini-Cog)',
                                'subtitulo' => 'Detección y clasificación de deterioro cognitivo y memoria',
                                'ruta' => 'admin.psicologia.evaluacion.cognitiva',
                                'icono' => 'ph-puzzle-piece',
                                'tipo' => 'Evaluación',
                            ],
                            [
                                'titulo' => 'Evaluación Afectiva (GDS-15, Yesavage, CESD)',
                                'subtitulo' => 'Detección de sintomatología depresiva y estado de ánimo',
                                'ruta' => 'admin.psicologia.evaluacion.afectiva',
                                'icono' => 'ph-smiley',
                                'tipo' => 'Evaluación',
                            ],
                            [
                                'titulo' => 'Funcionamiento y Autonomía (Katz, Lawton)',
                                'subtitulo' => 'Evaluación del impacto neurocognitivo en la vida diaria',
                                'ruta' => 'admin.psicologia.evaluacion.funcionamiento',
                                'icono' => 'ph-person-arms-spread',
                                'tipo' => 'Evaluación',
                            ],
                            [
                                'titulo' => 'Entorno y Red Social (OARS, Maltrato)',
                                'subtitulo' => 'Soporte familiar, redes afectivas y factores de vulnerabilidad',
                                'ruta' => 'admin.psicologia.evaluacion.entorno',
                                'icono' => 'ph-users-four',
                                'tipo' => 'Evaluación',
                            ],
                        ],
                    ],
                ],
            ],

            // ÁREA DE NUTRICIÓN Y DIETÉTICA
            'nutricion' => [
                'id' => 'nutricion',
                'nombre' => 'Área de Nutrición y Dietética',
                'rol_principal' => 'NUTRICIONISTA',
                'rol_etiqueta' => 'Nutricionista y Dietista',
                'icono' => 'ph-fork-knife',
                'color' => 'amber',
                'color_bg' => 'bg-amber-500/10 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'color_pill' => 'bg-amber-600 text-white',
                'descripcion' => 'Planes alimentarios adaptados, cribado de desnutrición geriátrica (MNA, MUST) y control antropométrico continuado.',
                'subdivisiones' => [
                    'nutricion' => [
                        'titulo' => 'Vistas Nutricionales y Antropométricas',
                        'subtitulo' => 'Herramientas de evaluación dietética y curvas de peso e IMC',
                        'icono' => 'ph-chart-line-up',
                        'vistas' => [
                            [
                                'titulo' => 'Valoración Nutricional (MNA, MUST)',
                                'subtitulo' => 'Tamizaje de riesgo nutricional y diseño de dietas terapéuticas',
                                'ruta' => 'admin.nutricion.valoracion',
                                'icono' => 'ph-clipboard-text',
                                'tipo' => 'Evaluación',
                            ],
                            [
                                'titulo' => 'Seguimiento Nutricional y Curvas de Peso',
                                'subtitulo' => 'Registro longitudinal de peso, talla, IMC e hidratación',
                                'ruta' => 'admin.nutricion.seguimiento',
                                'icono' => 'ph-chart-line-up',
                                'tipo' => 'Clínica',
                            ],
                        ],
                    ],
                ],
            ],

            // ÁREA DE TERAPIA, FISIOTERAPIA Y ESTIMULACIÓN
            'terapia' => [
                'id' => 'terapia',
                'nombre' => 'Área de Fisioterapia, Terapia y Estimulación',
                'rol_principal' => 'FISIOTERAPEUTA',
                'rol_etiqueta' => 'Fisioterapeuta y Pedagogo',
                'icono' => 'ph-barbell',
                'color' => 'blue',
                'color_bg' => 'bg-blue-500/10 dark:bg-blue-500/20 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                'color_pill' => 'bg-blue-600 text-white',
                'descripcion' => 'Rehabilitación motora, prevención de caídas, fisioterapia respiratoria, talleres recreativos y pedagógicos de estimulación.',
                'subdivisiones' => [
                    'terapia' => [
                        'titulo' => 'Vistas Terapéuticas y de Actividades',
                        'subtitulo' => 'Talleres ocupacionales, estimulación sensorial y actividad física',
                        'icono' => 'ph-sparkle',
                        'vistas' => [
                            [
                                'titulo' => 'Catálogo y Agenda de Actividades',
                                'subtitulo' => 'Talleres de motricidad, música, manualidades y recreación',
                                'ruta' => 'admin.actividades.index',
                                'icono' => 'ph-sparkle',
                                'tipo' => 'Operativa',
                            ],
                            [
                                'titulo' => 'Reportes y Participación en Actividades',
                                'subtitulo' => 'Registro de asistencia y adherencia a planes de estimulación',
                                'ruta' => 'admin.actividades.reportes',
                                'icono' => 'ph-chart-bar',
                                'tipo' => 'Reportes',
                            ],
                        ],
                    ],
                ],
            ],

            // ÁREA SOCIAL, VOLUNTARIADO Y FAMILIAS
            'social' => [
                'id' => 'social',
                'nombre' => 'Área Social, Voluntariado y Red Familiar',
                'rol_principal' => 'VOLUNTARIO',
                'rol_etiqueta' => 'Trabajo Social, Voluntariado y Familias',
                'icono' => 'ph-hand-heart',
                'color' => 'rose',
                'color_bg' => 'bg-rose-500/10 dark:bg-rose-500/20 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'color_pill' => 'bg-rose-600 text-white',
                'descripcion' => 'Acompañamiento psicosocial, vinculación de tutores y red de apoyo familiar, integración de voluntarios comunitarios.',
                'subdivisiones' => [
                    'social' => [
                        'titulo' => 'Vistas Sociales y Comunitarias',
                        'subtitulo' => 'Gestión de familias, admisiones comunitarias y voluntariado activo',
                        'icono' => 'ph-users-three',
                        'vistas' => [
                            [
                                'titulo' => 'Red Familiar y Apoyo',
                                'subtitulo' => 'Censo de contactos familiares, tutores legales y visitas',
                                'ruta' => 'admin.familia-social.resumen',
                                'icono' => 'ph-users-three',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Preadmisiones y Evaluaciones de Ingreso',
                                'subtitulo' => 'Solicitudes de nuevo ingreso, entrevista social y documentación',
                                'ruta' => 'admin.admisiones.preadmisiones',
                                'icono' => 'ph-user-plus',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Directorio y Programas de Voluntariado',
                                'subtitulo' => 'Registro de voluntarios, disponibilidad y tareas de apoyo asignadas',
                                'ruta' => 'admin.voluntariado.index',
                                'icono' => 'ph-hand-heart',
                                'tipo' => 'Operativa',
                            ],
                        ],
                    ],
                ],
            ],

            // ÁREA DE DIRECCIÓN Y GESTIÓN INSTITUCIONAL
            'administracion' => [
                'id' => 'administracion',
                'nombre' => 'Área de Dirección y Administración Institucional',
                'rol_principal' => 'SUPERADMINISTRADOR',
                'rol_etiqueta' => 'Administrador y Superadministrador',
                'icono' => 'ph-shield-check',
                'color' => 'indigo',
                'color_bg' => 'bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800',
                'color_pill' => 'bg-indigo-600 text-white',
                'descripcion' => 'Supervisión integral de infraestructura, gobierno de accesos, censo general, gobernanza de datos y trazabilidad forense.',
                'subdivisiones' => [
                    'administracion' => [
                        'titulo' => 'Vistas de Gestión y Control Institucional',
                        'subtitulo' => 'Módulos centrales de administración, personal, usuarios y seguridad',
                        'icono' => 'ph-buildings',
                        'vistas' => [
                            [
                                'titulo' => 'Dashboard Administrativo',
                                'subtitulo' => 'Panel de control de operaciones institucionales y altas',
                                'ruta' => 'admin.administracion.dashboard',
                                'icono' => 'ph-house',
                                'tipo' => 'Supervisión',
                            ],
                            [
                                'titulo' => 'Padrón General de Residentes',
                                'subtitulo' => 'Expedientes de adultos mayores, censos y estados',
                                'ruta' => 'admin.adultos-mayores.index',
                                'icono' => 'ph-users-four',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Habitaciones y Plazas/Camas',
                                'subtitulo' => 'Censo de ocupación, sectores, pabellones y asignación de plazas',
                                'ruta' => 'admin.habitaciones.index',
                                'icono' => 'ph-bed',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Personal Institucional',
                                'subtitulo' => 'Directorio de colaboradores, legajos y cargos administrativos',
                                'ruta' => 'admin.personal-institucional',
                                'icono' => 'ph-identification-badge',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Horarios y Planillas de Asignación',
                                'subtitulo' => 'Planificación horaria modular del personal administrativo y salud',
                                'ruta' => 'admin.turnos-asignaciones.index',
                                'icono' => 'ph-calendar',
                                'tipo' => 'Gestión',
                            ],
                            [
                                'titulo' => 'Áreas Institucionales',
                                'subtitulo' => 'Catálogo formal de departamentos y dependencias institucionales',
                                'ruta' => 'admin.areas-institucionales.index',
                                'icono' => 'ph-buildings',
                                'tipo' => 'Configuración',
                            ],
                            [
                                'titulo' => 'Directorio de Usuarios y Cuentas',
                                'subtitulo' => 'Cuentas de acceso, credenciales de inicio y estados de cuenta',
                                'ruta' => 'admin.usuarios.index',
                                'icono' => 'ph-user-gear',
                                'tipo' => 'Seguridad',
                            ],
                            [
                                'titulo' => 'Roles y Permisos de Seguridad',
                                'subtitulo' => 'Matriz de facultades, asignación de roles y control de acceso RBAC',
                                'ruta' => 'admin.roles-permisos.index',
                                'icono' => 'ph-shield-check',
                                'tipo' => 'Seguridad',
                            ],
                            [
                                'titulo' => 'Bitácora y Auditoría Forense',
                                'subtitulo' => 'Trazabilidad de accesos, modificaciones, exportaciones y seguridad',
                                'ruta' => 'admin.bitacora.index',
                                'icono' => 'ph-scroll',
                                'tipo' => 'Auditoría',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return view('pages.admin.areas-atencion.index', compact(
            'totalResidentes',
            'totalUsuarios',
            'alertasActivas',
            'turnosEnfermeriaCount',
            'areas'
        ));
    }
}
