<?php

namespace App\Backend\Modulos\Reportes\Servicios;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Models\Personal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        private readonly RolePreviewService $rolePreview,
        private readonly RoleDashboardDataService $roleDashboardData,
    ) {}

    public function obtenerDatosDashboard($usuario): array
    {
        $rolEfectivo = $this->rolePreview->activeRole($usuario);
        $perfil = $rolEfectivo ?? $usuario->getRoleNames()->sort()->implode('|');
        $claveCache = 'dashboard_roles_v2_'.$usuario->cod_usuario.'_'.md5($perfil);

        return Cache::remember($claveCache, 60, function () use ($usuario, $rolEfectivo) {
            $rol = $rolEfectivo ?? $usuario->getRoleNames()->first();
            $esSalud = in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA'], true);

            $perfil = $this->obtenerPerfilDashboardRol($usuario, $rol);
            $datosPorRol = $this->roleDashboardData->forRole($usuario, $rol ?? 'USUARIO');
            $perfil['indicadores'] = $datosPorRol['metrics'];
            $perfil['panels'] = $datosPorRol['panels'];

            return [
                'saludo' => $this->obtenerSaludoUsuario($usuario, $rol),
                'perfilDashboardRol' => $perfil,
                'infoRol' => [
                    'es_salud' => $esSalud,
                    'nombre_rol' => $rol ?? 'Usuario',
                ],
            ];
        });
    }

    public function obtenerSaludoUsuario($usuario, ?string $rolEfectivo = null): array
    {
        $partes = array_filter([
            $usuario->nombres ?? null,
            $usuario->ap_paterno ?? null,
            $usuario->ap_materno ?? null,
        ]);

        $nombre = ! empty($partes)
            ? trim(implode(' ', $partes))
            : ($usuario->name ?? $usuario->correo ?? 'Usuario del sistema');

        $rolesLegibles = [
            'SUPERADMINISTRADOR' => 'Superadministrador',
            'ADMINISTRADOR' => 'Administración',
            'GERENTE' => 'Gerente',
            'ENFERMEROS' => 'Enfermero/a',
            'MEDICO GENERAL/GERIATRA' => 'Médico',
            'PSICOLOGO/A' => 'Psicólogo/a',
            'PEDAGOGO' => 'Pedagogo/a',
            'NUTRICIONISTA' => 'Nutricionista',
            'FISIOTERAPEUTA' => 'Fisioterapeuta',
            'FAMILIAR' => 'Familiar',
        ];
        $rolClave = $rolEfectivo ?? $usuario->getRoleNames()->first();
        $rolLegible = $rolesLegibles[$rolClave] ?? 'Usuario del sistema';

        $hora = (int) now()->format('H');
        $saludo = match (true) {
            $hora < 12 => 'Buenos días',
            $hora < 19 => 'Buenas tardes',
            default => 'Buenas noches',
        };

        Carbon::setLocale('es');
        $fecha = ucfirst(now()->translatedFormat('l, d \d\e F \d\e Y'));

        return compact('nombre', 'rolLegible', 'saludo', 'fecha');
    }

    /**
     * Presentación y áreas funcionales del panel genérico por rol.
     * No fabrica datos clínicos: describe competencias y enlaza únicamente
     * rutas existentes cuya autorización continúa protegida por middleware.
     */
    public function obtenerPerfilDashboardRol($usuario, ?string $rolEfectivo = null): array
    {
        $rol = $rolEfectivo ?? $usuario->getRoleNames()->first() ?? 'USUARIO';

        $comun = [
            'eyebrow' => 'CENTRO GERIÁTRICO LOS ALMENDROS',
            'title' => 'Tu jornada,',
            'highlight' => 'organizada con propósito',
            'description' => 'Consulta tus áreas de trabajo y accede a las funciones autorizadas para tu perfil institucional.',
            'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/489963938_1158744422930145_8442506970304201426_n.jpg',
            'quote' => 'Cada especialidad aporta bienestar',
            'areas' => [],
            'accesos' => [],
            'indicadores' => [],
        ];

        $perfiles = [
            'SUPERADMINISTRADOR' => [
                'title' => 'RememberMind completo,',
                'highlight' => 'bajo supervisión global',
                'description' => 'Supervisa sistema, institución, residencia, operación y actividad clínica. La información clínica es de lectura salvo competencia profesional adicional.',
                'quote' => 'Autoridad técnica con trazabilidad y reglas de negocio',
                'areas' => [
                    ['icon' => 'ph-shield-check', 'title' => 'Sistema', 'copy' => 'Usuarios, roles, seguridad y auditoría.'],
                    ['icon' => 'ph-buildings', 'title' => 'Institución', 'copy' => 'Personal, áreas, turnos, jornadas y cobertura.'],
                    ['icon' => 'ph-house-line', 'title' => 'Residencia y operación', 'copy' => 'Admisiones, ocupación, documentos, actividades y alertas.'],
                    ['icon' => 'ph-first-aid-kit', 'title' => 'Supervisión clínica', 'copy' => 'Lectura agregada sin atribuir competencia profesional.'],
                ],
                'accesos' => [
                    ['route' => 'admin.usuarios.index', 'permission' => 'usuarios.ver', 'icon' => 'ph-users-three', 'label' => 'Usuarios'],
                    ['route' => 'admin.adultos-mayores.index', 'permission' => 'residentes.ver', 'icon' => 'ph-house-line', 'label' => 'Residentes'],
                    ['route' => 'admin.personal-institucional', 'permission' => 'personal_institucional.ver', 'icon' => 'ph-identification-badge', 'label' => 'Personal'],
                    ['route' => 'admin.bitacora.index', 'permission' => 'bitacora.ver', 'icon' => 'ph-scroll', 'label' => 'Auditoría'],
                    ['route' => 'admin.alertas-clinicas.index', 'permission' => 'alertas.ver', 'icon' => 'ph-warning', 'label' => 'Alertas'],
                    ['route' => 'admin.reportes.institucional.preview', 'permission' => 'reportes.institucional', 'icon' => 'ph-chart-bar', 'label' => 'Reportes'],
                ],
            ],
            'GERENTE' => [
                'title' => 'Dirección institucional,',
                'highlight' => 'con visión de cobertura',
                'description' => 'Conduce personal, áreas, turnos y planificación general con indicadores institucionales de consulta.',
                'quote' => 'Planificar es cuidar la capacidad institucional',
                'areas' => [
                    ['icon' => 'ph-identification-badge', 'title' => 'Personal', 'copy' => 'Incorporación, profesión, especialidad y vigencia.'],
                    ['icon' => 'ph-buildings', 'title' => 'Áreas', 'copy' => 'Distribución institucional del equipo.'],
                    ['icon' => 'ph-clock', 'title' => 'Turnos', 'copy' => 'Configuración de turnos maestros.'],
                    ['icon' => 'ph-chart-line-up', 'title' => 'Cobertura', 'copy' => 'Planificación y disponibilidad futura.'],
                ],
                'accesos' => [
                    ['route' => 'admin.personal-institucional', 'permission' => 'personal_institucional.ver', 'icon' => 'ph-identification-badge', 'label' => 'Personal'],
                    ['route' => 'admin.turnos-asignaciones.index', 'permission' => 'turnos.ver', 'icon' => 'ph-clock', 'label' => 'Turnos'],
                    ['route' => 'admin.reportes.institucional.preview', 'permission' => 'reportes.institucional', 'icon' => 'ph-chart-bar', 'label' => 'Reportes gerenciales'],
                ],
            ],
            'ADMINISTRADOR' => [
                'title' => 'Operación diaria,',
                'highlight' => 'coordinada de principio a fin',
                'description' => 'Gestiona admisiones, alojamiento, jornadas, asignaciones, documentación, visitas y alertas operativas.',
                'quote' => 'Coordinar bien convierte la planificación en cuidado',
                'areas' => [
                    ['icon' => 'ph-user-plus', 'title' => 'Admisiones', 'copy' => 'Preadmisión, revisión y formalización del ingreso.'],
                    ['icon' => 'ph-bed', 'title' => 'Alojamiento', 'copy' => 'Disponibilidad, ocupación y asignación segura.'],
                    ['icon' => 'ph-calendar-check', 'title' => 'Jornadas', 'copy' => 'Apertura, cobertura y asignaciones del día.'],
                    ['icon' => 'ph-bell-ringing', 'title' => 'Control operativo', 'copy' => 'Alertas, incidentes y pendientes administrativos.'],
                ],
                'accesos' => [
                    ['route' => 'admin.admisiones.preadmisiones', 'permission' => 'admisiones.ver_dashboard', 'icon' => 'ph-user-plus', 'label' => 'Preadmisiones'],
                    ['route' => 'admin.habitaciones.index', 'permission' => 'habitaciones.ver', 'icon' => 'ph-bed', 'label' => 'Alojamiento'],
                    ['route' => 'admin.alertas-clinicas.index', 'permission' => 'alertas.ver', 'icon' => 'ph-bell-ringing', 'label' => 'Alertas'],
                ],
            ],
            'MEDICO GENERAL/GERIATRA' => [
                'title' => 'Atención médica,',
                'highlight' => 'con contexto clínico integral',
                'description' => 'Consulta pacientes, valoraciones, estudios, prescripciones e interconsultas según las competencias profesionales autorizadas.',
                'quote' => 'Decisiones clínicas con identidad y trazabilidad',
                'areas' => [
                    ['icon' => 'ph-stethoscope', 'title' => 'Consulta y evolución', 'copy' => 'Atención médica longitudinal.'],
                    ['icon' => 'ph-pill', 'title' => 'Prescripciones', 'copy' => 'Tratamiento farmacológico autorizado.'],
                    ['icon' => 'ph-flask', 'title' => 'Estudios', 'copy' => 'Solicitudes, resultados e informes.'],
                    ['icon' => 'ph-warning', 'title' => 'Alertas clínicas', 'copy' => 'Riesgos que requieren evaluación.'],
                ],
                'accesos' => [
                    ['route' => 'admin.medico.pacientes.observacion', 'permission' => 'residentes.ver', 'icon' => 'ph-users-three', 'label' => 'Pacientes'],
                    ['route' => 'admin.salud-seguimiento.medicacion.index', 'permission' => 'prescripciones.ver', 'icon' => 'ph-pill', 'label' => 'Medicación'],
                    ['route' => 'admin.medico.alertas', 'permission' => 'alertas.ver', 'icon' => 'ph-warning', 'label' => 'Alertas'],
                ],
            ],
            'ENFERMEROS' => [
                'title' => 'Cuidado continuo,',
                'highlight' => 'organizado por turno',
                'description' => 'Consulta residentes asignados, cuidados, medicación administrable, pases e incidentes de enfermería.',
                'quote' => 'La continuidad también cuida',
                'areas' => [
                    ['icon' => 'ph-users-three', 'title' => 'Residentes', 'copy' => 'Pacientes y ubicación durante el turno.'],
                    ['icon' => 'ph-heartbeat', 'title' => 'Cuidados', 'copy' => 'Signos, seguimiento y planes.'],
                    ['icon' => 'ph-pill', 'title' => 'Medicación', 'copy' => 'Administración según prescripción vigente.'],
                    ['icon' => 'ph-arrows-clockwise', 'title' => 'Continuidad', 'copy' => 'Pases de turno, alertas e incidentes.'],
                ],
                'accesos' => [
                    ['route' => 'admin.enfermeria.pacientes', 'permission' => 'enfermeria.ver_pacientes_asignados', 'icon' => 'ph-users-three', 'label' => 'Mis residentes'],
                    ['route' => 'admin.enfermeria.tareas', 'permission' => 'ejecuciones_cuidado.ver', 'icon' => 'ph-heartbeat', 'label' => 'Cuidados'],
                    ['route' => 'admin.enfermeria.alertas', 'permission' => 'alertas.ver', 'icon' => 'ph-warning', 'label' => 'Alertas'],
                ],
            ],
            'PSICOLOGO/A' => [
                'title' => 'Bienestar emocional,',
                'highlight' => 'con seguimiento profesional',
                'description' => 'Consulta evaluaciones cognitivas, afectivas, funcionales y del entorno social.',
                'quote' => 'Escuchar también es cuidar',
                'areas' => [
                    ['icon' => 'ph-brain', 'title' => 'Cognición', 'copy' => 'Evaluación y seguimiento cognitivo.'],
                    ['icon' => 'ph-heart', 'title' => 'Afectividad', 'copy' => 'Bienestar emocional y conducta.'],
                    ['icon' => 'ph-person', 'title' => 'Funcionamiento', 'copy' => 'Autonomía y adaptación.'],
                    ['icon' => 'ph-users', 'title' => 'Entorno', 'copy' => 'Red de apoyo y contexto social.'],
                ],
                'accesos' => [
                    ['route' => 'admin.psicologia.evaluaciones', 'permission' => 'residentes.ver', 'icon' => 'ph-list-magnifying-glass', 'label' => 'Evaluaciones'],
                ],
            ],
            'NUTRICIONISTA' => [
                'title' => 'Nutrir hoy,',
                'highlight' => 'es fortalecer el mañana',
                'description' => 'Organiza la valoración nutricional, las mediciones antropométricas y el seguimiento de ingesta e hidratación.',
                'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/569897738_1324313409706578_2951129905561208154_n.jpg',
                'quote' => 'Alimentar también es acompañar',
                'areas' => [
                    ['icon' => 'ph-bowl-food', 'title' => 'Valoración nutricional', 'copy' => 'Estado nutricional y necesidades individuales.'],
                    ['icon' => 'ph-ruler', 'title' => 'Antropometría', 'copy' => 'Peso, talla y evolución de medidas.'],
                    ['icon' => 'ph-drop', 'title' => 'Ingesta e hidratación', 'copy' => 'Seguimiento de consumo y tolerancia.'],
                    ['icon' => 'ph-notebook', 'title' => 'Plan de cuidado', 'copy' => 'Indicaciones y objetivos interdisciplinarios.'],
                ],
            ],
            'FISIOTERAPEUTA' => [
                'title' => 'Moverse hoy,',
                'highlight' => 'es conservar autonomía',
                'description' => 'Prioriza movilidad, dolor, funcionalidad y prevención de caídas con una lectura clara de cada residente.',
                'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/577031711_1337134741757778_4846830420773518569_n.jpg',
                'quote' => 'Cada movimiento cuenta',
                'areas' => [
                    ['icon' => 'ph-person-arms-spread', 'title' => 'Movilidad', 'copy' => 'Marcha, transferencias y asistencia requerida.'],
                    ['icon' => 'ph-activity', 'title' => 'Funcionalidad', 'copy' => 'Capacidad y progreso terapéutico.'],
                    ['icon' => 'ph-first-aid', 'title' => 'Dolor', 'copy' => 'Valoración y respuesta al tratamiento.'],
                    ['icon' => 'ph-shield-check', 'title' => 'Prevención', 'copy' => 'Riesgo de caídas y entorno seguro.'],
                ],
            ],
            'PEDAGOGO' => [
                'title' => 'Estimular hoy,',
                'highlight' => 'es mantener capacidades',
                'description' => 'Planifica experiencias educativas y cognitivas adaptadas a los intereses, ritmos y habilidades de cada residente.',
                'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/558487013_1337134818424437_2282337776297854403_n.jpg',
                'quote' => 'Aprender mantiene viva la curiosidad',
                'areas' => [
                    ['icon' => 'ph-brain', 'title' => 'Estimulación cognitiva', 'copy' => 'Atención, memoria y funciones ejecutivas.'],
                    ['icon' => 'ph-pencil-line', 'title' => 'Seguimiento pedagógico', 'copy' => 'Objetivos y evolución individual.'],
                    ['icon' => 'ph-palette', 'title' => 'Talleres', 'copy' => 'Actividades significativas y participación.'],
                    ['icon' => 'ph-users-three', 'title' => 'Integración social', 'copy' => 'Vínculos, expresión y convivencia.'],
                ],
            ],
            'FAMILIAR' => [
                'title' => 'Acompañar cerca,',
                'highlight' => 'incluso a la distancia',
                'description' => 'Consulta la información compartida por el centro y mantente conectado con las actividades y el bienestar de tu familiar.',
                'image' => 'images/FOTOS CENTRO DE ADULTOS MAYORES/600320307_1368093028661949_6493928930338402983_n.jpg',
                'quote' => 'La familia también forma parte del cuidado',
                'areas' => [
                    ['icon' => 'ph-heart', 'title' => 'Bienestar', 'copy' => 'Información autorizada del residente vinculado.'],
                    ['icon' => 'ph-calendar-check', 'title' => 'Actividades', 'copy' => 'Participación y experiencias del centro.'],
                    ['icon' => 'ph-users', 'title' => 'Visitas', 'copy' => 'Organización del acompañamiento familiar.'],
                    ['icon' => 'ph-file-text', 'title' => 'Documentos', 'copy' => 'Documentación y consentimientos disponibles.'],
                ],
            ],
        ];

        $perfil = array_replace($comun, $perfiles[$rol] ?? []);
        $perfil['rol'] = $rol;
        if (empty($perfil['accesos'])) {
            $perfil['accesos'] = $rol === 'FAMILIAR'
                ? [['route' => 'profile.show', 'permission' => null, 'icon' => 'ph-user-circle', 'label' => 'Mi perfil']]
                : [
                    ['route' => 'admin.residentes.index', 'permission' => 'residentes.ver', 'icon' => 'ph-users-three', 'label' => 'Residentes'],
                    ['route' => 'admin.actividades.index', 'permission' => 'actividades.ver', 'icon' => 'ph-calendar-check', 'label' => 'Actividades'],
                    ['route' => 'profile.show', 'permission' => null, 'icon' => 'ph-user-circle', 'label' => 'Mi perfil'],
                ];
        }

        return $perfil;
    }

    public function obtenerKpisInstitucionales(): array
    {
        $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');
        $seguimientoEspecial = $this->conteoAdultosConEstado('SEGUIMIENTO_ESPECIAL');

        $fichasActivas = DB::table('atenciones')
            ->where('estado', 'ACTIVA')
            ->distinct('cod_residente')
            ->count('cod_residente');

        $personalSalud = Personal::where('estado', 'ACTIVO')
            ->whereIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        $personalAdmin = Personal::where('estado', 'ACTIVO')
            ->whereNotIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        $prescripcionesActivas = DB::table('prescripciones')
            ->where('estado', 'ACTIVA')
            ->count();

        return [
            [
                'clave' => 'adultos_activos',
                'titulo' => 'Adultos mayores',
                'valor' => $adultosActivos,
                'subtitulo' => 'Residentes activos',
                'icono' => 'ph-users-three',
                'color' => 'azul-profundo',
                'badge' => $adultosActivos > 0 ? 'Registrados' : 'Sin registros',
                'nivel' => 'normal',
            ],
            [
                'clave' => 'seguimiento_especial',
                'titulo' => 'Seguimiento especial',
                'valor' => $seguimientoEspecial,
                'subtitulo' => 'Alerta orientativa',
                'icono' => 'ph-eye',
                'color' => 'naranja',
                'badge' => $seguimientoEspecial > 0 ? 'Requiere atención' : 'Sin novedades',
                'nivel' => $seguimientoEspecial > 0 ? 'alerta' : 'normal',
            ],
            [
                'clave' => 'fichas_activas',
                'titulo' => 'Fichas clínicas',
                'valor' => $fichasActivas,
                'subtitulo' => 'Con atención activa',
                'icono' => 'ph-clipboard-text',
                'color' => 'verde-salud',
                'badge' => ($adultosActivos > 0 && $fichasActivas >= $adultosActivos)
                    ? 'Al día' : 'Revisar',
                'nivel' => ($adultosActivos > 0 && $fichasActivas >= $adultosActivos)
                    ? 'ok' : 'advertencia',
            ],
            [
                'clave' => 'personal_salud',
                'titulo' => 'Personal de salud',
                'valor' => $personalSalud,
                'subtitulo' => 'Registrados',
                'icono' => 'ph-stethoscope',
                'color' => 'morado-cog',
                'badge' => 'Equipo activo',
                'nivel' => 'normal',
            ],
            [
                'clave' => 'personal_admin',
                'titulo' => 'Personal administrativo',
                'valor' => $personalAdmin,
                'subtitulo' => 'Registrados',
                'icono' => 'ph-briefcase',
                'color' => 'terracota',
                'badge' => 'Equipo activo',
                'nivel' => 'normal',
            ],
            [
                'clave' => 'prescripciones_activas',
                'titulo' => 'Prescripciones',
                'valor' => $prescripcionesActivas,
                'subtitulo' => 'Órdenes médicas vigentes',
                'icono' => 'ph-pill',
                'color' => 'verde-olivo',
                'badge' => $prescripcionesActivas > 0 ? 'Vigentes' : 'Sin órdenes',
                'nivel' => 'normal',
            ],
        ];
    }

    public function obtenerResumenSalud(): array
    {
        $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');

        $fichasActivas = DB::table('atenciones')
            ->where('estado', 'ACTIVA')
            ->distinct('cod_residente')
            ->count('cod_residente');

        $adultosSinFicha = max(0, $adultosActivos - $fichasActivas);

        $medicacionesActivas = DB::table('prescripciones')
            ->where('estado', 'ACTIVA')
            ->count();

        $atencionesMes = DB::table('atenciones')
            ->whereYear('fecha_hora', now()->year)
            ->whereMonth('fecha_hora', now()->month)
            ->count();

        $valoracionesRecientes = DB::table('aplicaciones_instrumento')
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->count();

        $altaDependencia = DB::table('valoraciones_funcionales')
            ->where('nivel_dependencia', 'ALTA_DEPENDENCIA')
            ->count();

        $signosVitales7d = DB::table('signos_vitales')
            ->where('fecha_hora', '>=', now()->subDays(7))
            ->count();

        $evalCognitivas30d = DB::table('aplicaciones_instrumento')
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->count();

        $riesgoCaidaAlto = DB::table('registros_movilidad')
            ->whereIn('riesgo_caida', ['ALTO', 'INTENTO_CAMINAR_SOLO'])
            ->count();

        $adminMedicacionHoy = DB::table('administraciones_medicacion')
            ->whereDate('fecha_hora_programada', today())
            ->count();

        return compact(
            'fichasActivas',
            'adultosSinFicha',
            'medicacionesActivas',
            'atencionesMes',
            'valoracionesRecientes',
            'altaDependencia',
            'signosVitales7d',
            'evalCognitivas30d',
            'riesgoCaidaAlto',
            'adminMedicacionHoy'
        );
    }

    public function obtenerAlertasEstructuradas(): array
    {
        $alertas = [];

        $alertasDB = DB::table('alertas')
            ->whereIn('estado', ['ABIERTA', 'PENDIENTE', 'EN_PROCESO'])
            ->orderByDesc('fecha_hora')
            ->limit(4)
            ->get();

        foreach ($alertasDB as $a) {
            $alertas[] = [
                'nivel' => $a->nivel_gravedad ?? 'URGENTE',
                'descripcion' => $a->descripcion,
                'icono' => 'ph-warning-octagon',
                'accion' => 'Revisar en módulo de alertas',
                'url' => route('admin.alertas.index'),
            ];
        }

        $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');
        $conAtencion = DB::table('atenciones')
            ->where('estado', 'ACTIVA')
            ->distinct('cod_residente')
            ->count('cod_residente');
        $sinFicha = max(0, $adultosActivos - $conAtencion);
        if ($sinFicha > 0) {
            $etiqueta = $sinFicha === 1
                ? '1 residente no tiene atención clínica activa'
                : "{$sinFicha} residentes no tienen atención clínica activa";
            $alertas[] = [
                'nivel' => 'URGENTE',
                'descripcion' => "{$etiqueta} — requiere revisión.",
                'icono' => 'ph-warning-circle',
                'accion' => 'Ir a Residentes',
                'url' => route('admin.residentes.index'),
            ];
        }

        $seguimiento = $this->conteoAdultosConEstado('SEGUIMIENTO_ESPECIAL');
        if ($seguimiento > 0) {
            $etiqueta = $seguimiento === 1
                ? '1 residente requiere'
                : "{$seguimiento} residentes requieren";
            $alertas[] = [
                'nivel' => 'URGENTE',
                'descripcion' => "{$etiqueta} seguimiento especial — alerta orientativa.",
                'icono' => 'ph-eye',
                'accion' => 'Ver residentes',
                'url' => route('admin.residentes.index'),
            ];
        }

        if (empty($alertas)) {
            $alertas[] = [
                'nivel' => 'OK',
                'descripcion' => 'El sistema no presenta alertas administrativas pendientes.',
                'icono' => 'ph-check-circle',
                'accion' => null,
            ];
        }

        return $alertas;
    }

    public function obtenerEquipoInstitucional(): array
    {
        $rolesSalud = [
            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A',
            'PEDAGOGO',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA',
        ];

        $psTotales = User::role($rolesSalud)->count();
        $psActivos = User::role($rolesSalud)->where('estado', 'ACTIVO')->count();

        $especialidades = DB::table('model_has_roles as mhr')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->join('usuarios as u', 'mhr.model_id', '=', 'u.cod_usuario')
            ->where('mhr.model_type', '=', User::class)
            ->whereIn('r.name', $rolesSalud)
            ->where('u.estado', '=', 'ACTIVO')
            ->select('r.name as nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($e) => [
                'nombre' => $e->nombre,
                'total' => (int) $e->total,
            ])
            ->toArray();

        $rolesAdmin = [
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR',
        ];

        $paTotales = User::role($rolesAdmin)->count();
        $paActivos = User::role($rolesAdmin)->where('estado', 'ACTIVO')->count();

        $cargos = DB::table('model_has_roles as mhr')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->join('usuarios as u', 'mhr.model_id', '=', 'u.cod_usuario')
            ->where('mhr.model_type', '=', User::class)
            ->whereIn('r.name', $rolesAdmin)
            ->where('u.estado', '=', 'ACTIVO')
            ->select('r.name as cargo_nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($c) => [
                'nombre' => $c->cargo_nombre,
                'total' => (int) $c->total,
            ])
            ->toArray();

        return [
            'personal_salud' => [
                'total' => $psTotales,
                'activos' => $psActivos,
                'sin_especialidad' => 0,
                'especialidades' => $especialidades,
            ],
            'personal_admin' => [
                'total' => $paTotales,
                'activos' => $paActivos,
                'cargos' => $cargos,
            ],
        ];
    }

    public function obtenerAdultosPorEstado(): array
    {
        return DB::table('residentes')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($e) => [
                'estado' => $e->estado,
                'total' => (int) $e->total,
                'color' => match ($e->estado) {
                    'ACTIVO', 'ADMITIDO' => 'verde',
                    'BAJA', 'FALLECIDO' => 'rojo',
                    'HOSPITALIZADO' => 'naranja',
                    default => 'azul',
                },
            ])
            ->toArray();
    }

    public function obtenerDistribucionEquipoInstitucional(): array
    {
        $ps = Personal::where('estado', 'ACTIVO')
            ->whereIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();
        $pa = Personal::where('estado', 'ACTIVO')
            ->whereNotIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        return [
            'personal_salud' => $ps,
            'personal_admin' => $pa,
        ];
    }

    public function obtenerRedFamiliar(): array
    {
        $totalContactos = DB::table('contactos')->count();
        $vinculosActivos = DB::table('residentes_contactos')->where('estado', 'ACTIVO')->count();
        $residentesConFamiliar = DB::table('residentes_contactos')->distinct('cod_residente')->count('cod_residente');
        $totalResidentes = DB::table('residentes')->where('estado', 'ACTIVO')->count();
        $residentesSinFamiliar = max(0, $totalResidentes - $residentesConFamiliar);

        return [
            'total_familiares' => $totalContactos,
            'vinculos_activos' => $vinculosActivos,
            'residentes_con_familiar' => $residentesConFamiliar,
            'residentes_sin_familiar' => $residentesSinFamiliar,
        ];
    }

    private function conteoAdultosConEstado(string $estado): int
    {
        return DB::table('residentes')->where('estado', $estado)->count();
    }

    private function obtenerEstadisticas($usuario): array
    {
        return [
            'total_adultos' => DB::table('residentes')->count(),
            'total_usuarios' => DB::table('usuarios')->count(),
            'total_atenciones' => DB::table('atenciones')->count(),
            'total_prescripciones' => DB::table('prescripciones')->count(),
            'total_alertas' => DB::table('alertas')->whereIn('estado', ['PENDIENTE', 'ABIERTA'])->count(),
        ];
    }

    private function obtenerDistribucionRoles(): array
    {
        return DB::table('model_has_roles as mhr')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('r.name as rol', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'rol' => $item->rol,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function obtenerActividadMensual(): array
    {
        $dateExpr = DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', fecha_hora)" : "TO_CHAR(fecha_hora, 'YYYY-MM')";

        return DB::table('atenciones')
            ->select(
                DB::raw("{$dateExpr} as periodo"),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_hora', '>=', now()->subMonths(6))
            ->groupBy(DB::raw($dateExpr))
            ->orderBy('periodo')
            ->get()
            ->map(fn ($item) => [
                'mes' => $item->periodo,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function obtenerListaUsuarios(): array
    {
        return DB::table('usuarios as u')
            ->leftJoin('personal as p', 'u.cod_usuario', '=', 'p.cod_usuario')
            ->select(
                'u.cod_usuario',
                'u.correo',
                'u.estado',
                'p.nombres',
                'p.apellido_paterno as ap_paterno'
            )
            ->orderByDesc('u.cod_usuario')
            ->limit(10)
            ->get()
            ->map(fn ($u) => [
                'cod_usuario' => $u->cod_usuario,
                'nombre' => trim(($u->nombres ?? '').' '.($u->ap_paterno ?? '')) ?: $u->correo,
                'correo' => $u->correo,
                'estado' => $u->estado ?? 'ACTIVO',
                'rol' => $this->obtenerRolPrimario($u->cod_usuario),
            ])
            ->toArray();
    }

    private function obtenerUltimasActividades(): array
    {
        return DB::table('actividades')
            ->orderByDesc('fecha_hora')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'titulo' => 'Actividad: '.($a->tipo ?? 'General'),
                'detalle' => ($a->nombre ?? 'Sin título').' - '.($a->fecha_hora ? Carbon::parse($a->fecha_hora)->format('d/m/Y') : 'Hoy'),
                'icono' => 'ph-calendar-check',
            ])
            ->toArray();
    }

    private function generarAlertasInteligentes(): array
    {
        $alertas = DB::table('alertas')
            ->whereIn('estado', ['ABIERTA', 'PENDIENTE'])
            ->limit(5)
            ->pluck('descripcion')
            ->toArray();

        return count($alertas) > 0 ? $alertas : ['El sistema no presenta alertas administrativas pendientes.'];
    }

    private function obtenerModulosInstitucionales(): array
    {
        return [
            ['titulo' => 'Usuarios',       'descripcion' => 'Control de accesos y perfiles',    'icono' => 'ph-users-three',        'ruta' => route('admin.usuarios.index')],
            ['titulo' => 'Residentes',     'descripcion' => 'Seguimiento y fichas clínicas',     'icono' => 'ph-identification-card', 'ruta' => route('admin.residentes.index')],
            ['titulo' => 'Personal',       'descripcion' => 'Gestión de RRHH y especialistas',  'icono' => 'ph-stethoscope',         'ruta' => route('admin.personal-institucional')],
            ['titulo' => 'Preadmisiones',  'descripcion' => 'Casos y admisiones formalizadas',   'icono' => 'ph-door',                'ruta' => route('admin.preadmisiones.index')],
            ['titulo' => 'Actividades',    'descripcion' => 'Planificación de talleres diarios', 'icono' => 'ph-calendar-check',      'ruta' => route('admin.actividades.index')],
            ['titulo' => 'Alertas',        'descripcion' => 'Gestión de alertas y eventos',     'icono' => 'ph-warning',             'ruta' => route('admin.alertas.index')],
        ];
    }

    public function obtenerBitacoraAuditoria(): array
    {
        Carbon::setLocale('es');

        return DB::table('activity_log')
            ->leftJoin('usuarios as u', 'activity_log.causer_id', '=', 'u.cod_usuario')
            ->leftJoin('personal as p', 'p.cod_usuario', '=', 'u.cod_usuario')
            ->select('activity_log.*', 'p.nombres as usuario_nombre')
            ->orderByDesc('activity_log.created_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'fecha' => isset($log->created_at) ? Carbon::parse($log->created_at)->diffForHumans() : '-',
                    'usuario' => $log->usuario_nombre ?? 'Sistema',
                    'accion' => $this->traducirEvento($log->event),
                    'modulo' => $this->traducirModulo($log->log_name),
                    'detalle' => $this->limpiarDescripcion($log->description ?? ''),
                ];
            })
            ->toArray();
    }

    private function traducirEvento(?string $evento): string
    {
        return match ($evento) {
            'created', 'registro' => 'Registro creado',
            'updated', 'edicion' => 'Registro actualizado',
            'deleted', 'borrado' => 'Registro eliminado',
            'restored' => 'Registro restaurado',
            'accessed', 'acceso' => 'Acceso al sistema',
            'login' => 'Inicio de sesión',
            'logout' => 'Cierre de sesión',
            'assigned' => 'Asignación realizada',
            default => 'Acción registrada',
        };
    }

    private function traducirModulo(?string $modulo): string
    {
        return match ($modulo) {
            'dashboard', 'Panel principal' => 'Panel principal',
            'users', 'usuarios' => 'Usuarios',
            'roles' => 'Roles',
            'evaluaciones' => 'Evaluaciones',
            'actividades' => 'Actividades',
            'asignaciones' => 'Asignaciones',
            'default' => 'General',
            default => $modulo ?? 'General',
        };
    }

    private function limpiarDescripcion(string $descripcion): string
    {
        $traducciones = [
            'User updated' => 'Usuario actualizado',
            'Accessed dashboard' => 'Acceso al panel principal',
            'Access to institutional dashboard' => 'Acceso al dashboard institucional',
            'Acceso al dashboard institucional' => 'Acceso al panel institucional',
        ];

        return $traducciones[$descripcion] ?? $descripcion;
    }

    private function obtenerRolPrimario($codUsuario): string
    {
        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $codUsuario)
            ->value('roles.name') ?? 'Sin rol';
    }

    public function limpiarCache($idUsuario): void
    {
        $usuario = User::query()->find($idUsuario);
        if ($usuario) {
            $perfil = $usuario->getRoleNames()->sort()->implode('|');
            Cache::forget('dashboard_roles_v2_'.$idUsuario.'_'.md5($perfil));
        }
    }
}
