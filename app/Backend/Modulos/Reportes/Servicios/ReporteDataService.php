<?php

namespace App\Backend\Modulos\Reportes\Servicios;

use Illuminate\Support\Facades\DB;
use App\Models\User;

class ReporteDataService
{
    public static function expresionAnioMes(string $columna): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$columna}, 'YYYY-MM')",
            'mysql', 'mariadb' => "DATE_FORMAT({$columna}, '%Y-%m')",
            default => "strftime('%Y-%m', {$columna})",
        };
    }

    public static function expresionDia(string $columna): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$columna}, 'YYYY-MM-DD')",
            'mysql', 'mariadb' => "DATE_FORMAT({$columna}, '%Y-%m-%d')",
            default => "strftime('%Y-%m-%d', {$columna})",
        };
    }
    public static function expresionMes(string $columna, bool $nombre = false): string
    {
        if (!in_array($columna, ['fecha', 'fecha_hora', 'created_at'])) throw new \InvalidArgumentException('Columna no permitida');
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "TO_CHAR({$columna}, '".($nombre ? 'Mon' : 'MM')."')",
            'mysql', 'mariadb' => "DATE_FORMAT({$columna}, '".($nombre ? '%b' : '%m')."')",
            default => "strftime('%m', {$columna})",
        };
    }

    // ────────────────────────────────────────────────────────────
    // RESIDENTES (ADULTOS MAYORES V2)
    // ────────────────────────────────────────────────────────────

    public function adultosResumen(): array
    {
        $total      = DB::table('residentes')->count();
        $activos    = DB::table('residentes')->where('estado', 'ACTIVO')->count();
        $archivados = DB::table('residentes')->whereIn('estado', ['INACTIVO', 'BAJA', 'FALLECIDO'])->count();
        $nuevosMes  = DB::table('admisiones')
            ->whereYear('fecha_hora_admision', now()->year)
            ->whereMonth('fecha_hora_admision', now()->month)
            ->count();

        $conFamiliar = DB::table('residentes_contactos')
            ->where('estado', 'ACTIVO')
            ->distinct('cod_residente')
            ->count('cod_residente');

        $conFicha = DB::table('atenciones')
            ->distinct('cod_residente')
            ->count('cod_residente');

        return [
            'total'        => $total,
            'activos'      => $activos,
            'archivados'   => $archivados,
            'nuevos_mes'   => $nuevosMes,
            'con_familiar' => $conFamiliar,
            'sin_familiar' => max(0, $total - $conFamiliar),
            'con_ficha'    => $conFicha,
            'sin_ficha'    => max(0, $total - $conFicha),
        ];
    }

    public function adultosListaCompleta(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('residentes as r')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno, ' ', COALESCE(r.apellido_materno, ''))) AS nombre_completo"),
                'r.numero_documento as ci',
                'r.genero',
                'r.fecha_nacimiento as fecha_nac',
                DB::raw('NULL AS edad'),
                'r.estado_civil',
                'r.nivel_educativo as nivel_educat',
                'r.estado as nombre_estado',
                DB::raw("(SELECT fecha_hora_admision FROM admisiones adm WHERE adm.cod_residente = r.cod_residente ORDER BY fecha_hora_admision DESC LIMIT 1) AS fecha_ing"),
                DB::raw("(SELECT tipo_ingreso FROM admisiones adm WHERE adm.cod_residente = r.cod_residente ORDER BY fecha_hora_admision DESC LIMIT 1) AS tipo_ing"),
                DB::raw("(EXISTS (SELECT 1 FROM residentes_contactos rc WHERE rc.cod_residente = r.cod_residente AND rc.estado = 'ACTIVO')) AS tiene_familiar"),
                DB::raw("(EXISTS (SELECT 1 FROM atenciones a WHERE a.cod_residente = r.cod_residente)) AS tiene_ficha")
            )
            ->orderBy('r.apellido_paterno')
            ->limit($limite)
            ->get()->map(function ($adulto) {
                $adulto->edad = $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age : null;
                return $adulto;
            });
    }

    public function adultosLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('residentes as r')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno, ' ', COALESCE(r.apellido_materno, ''))) AS nombre_completo"),
                'r.numero_documento as ci',
                'r.genero',
                'r.fecha_nacimiento as fecha_nac',
                'r.estado as nombre_estado'
            )
            ->orderBy('r.apellido_paterno')
            ->limit($limite)
            ->get()->map(function ($adulto) {
                $adulto->edad = $adulto->fecha_nac ? \Carbon\Carbon::parse($adulto->fecha_nac)->age : null;
                return $adulto;
            });
    }

    public function adultosEstado(): array
    {
        $resultados = DB::table('residentes')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'ACTIVO'         => '#2A9D8F',
            'ADMITIDO'       => '#2A9D8F',
            'HOSPITALIZADO'  => '#E76F51',
            'BAJA'           => '#E97A5F',
            'FALLECIDO'      => '#6B7280',
            'INACTIVO'       => '#9CA3AF',
        ];

        return [
            'labels'  => $resultados->pluck('estado')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->estado)] ?? '#2F3E5C')->toArray(),
        ];
    }

    public function adultosGenero(): array
    {
        $resultados = DB::table('residentes')
            ->whereNotNull('genero')
            ->select('genero', DB::raw('COUNT(*) as total'))
            ->groupBy('genero')
            ->get();

        $mapaColores = [
            'MASCULINO' => '#2F3E5C',
            'FEMENINO'  => '#E97A5F',
            'OTRO'      => '#7A68B0',
        ];

        return [
            'labels'  => $resultados->pluck('genero')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->genero)] ?? '#2A9D8F')->toArray(),
        ];
    }

    public function adultosEdad(): array
    {
        $rangos = [
            '60-69' => [60, 69],
            '70-79' => [70, 79],
            '80-89' => [80, 89],
            '90+'   => [90, 150],
        ];

        $data = [];
        foreach ($rangos as $etiqueta => [$min, $max]) {
            $count = DB::table('residentes')
                ->whereDate('fecha_nacimiento', '<=', now()->subYears($min)->toDateString())
                ->whereDate('fecha_nacimiento', '>', now()->subYears($max + 1)->toDateString())
                ->whereNotNull('fecha_nacimiento')
                ->count();
            $data[] = (int) $count;
        }

        $promedio = (int) round(DB::table('residentes')->whereNotNull('fecha_nacimiento')->pluck('fecha_nacimiento')
            ->avg(fn ($fecha) => \Carbon\Carbon::parse($fecha)->age) ?? 0);

        return [
            'labels'   => array_keys($rangos),
            'data'     => $data,
            'colores'  => ['#2F3E5C', '#2A9D8F', '#D4843A', '#E97A5F'],
            'promedio' => $promedio,
        ];
    }

    // ────────────────────────────────────────────────────────────
    // SALUD Y SEGUIMIENTO V2
    // ────────────────────────────────────────────────────────────

    public function saludResumen(): array
    {
        return [
            'fichas'       => DB::table('atenciones')->count(),
            'medicaciones' => DB::table('prescripciones')->where('estado', 'ACTIVA')->count(),
            'valoraciones' => DB::table('aplicaciones_instrumento')->count(),
            'atenciones'   => DB::table('atenciones')->count(),
        ];
    }

    public function fichasLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('atenciones as a')
            ->join('residentes as r', 'a.cod_residente', '=', 'r.cod_residente')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno)) AS nombre"),
                'a.estado',
                'a.fecha_hora as created_at'
            )
            ->orderByDesc('a.fecha_hora')
            ->limit($limite)
            ->get();
    }

    public function medicacionLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('prescripciones as p')
            ->join('residentes as r', 'p.cod_residente', '=', 'r.cod_residente')
            ->leftJoin('medicamentos as m', 'p.cod_medicamento', '=', 'm.cod_medicamento')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno)) AS nombre"),
                DB::raw("COALESCE(m.nombre_comercial, m.nombre_generico, 'Medicamento') AS medicamento"),
                'p.dosis',
                'p.via_administracion as via',
                'p.estado',
                'p.fecha_hora_prescripcion as fecha_inicio'
            )
            ->orderByDesc('p.fecha_hora_prescripcion')
            ->limit($limite)
            ->get();
    }

    public function valoracionesLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('aplicaciones_instrumento as ai')
            ->join('residentes as r', 'ai.cod_residente', '=', 'r.cod_residente')
            ->leftJoin('instrumentos as i', 'ai.cod_instrumento', '=', 'i.cod_instrumento')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno)) AS nombre"),
                DB::raw("COALESCE(i.nombre, 'Instrumento') AS tipo"),
                'ai.puntaje_total as puntaje',
                'ai.clasificacion as clasificacion',
                'ai.fecha_hora as fecha'
            )
            ->orderByDesc('ai.fecha_hora')
            ->limit($limite)
            ->get();
    }

    public function atencionesPorMes(): array
    {
        $resultados = DB::table('atenciones')
            ->where('fecha_hora', '>=', now()->subMonths(6))
            ->select(
                DB::raw(self::expresionAnioMes('fecha_hora') . " as mes"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw(self::expresionAnioMes('fecha_hora')))
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $resultados->pluck('mes')->toArray(),
            'data'   => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    public function dependenciaDistribucion(): array
    {
        $resultados = DB::table('valoraciones_funcionales')
            ->whereNotNull('nivel_dependencia')
            ->select('nivel_dependencia', DB::raw('COUNT(*) as total'))
            ->groupBy('nivel_dependencia')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'INDEPENDIENTE'       => '#2A9D8F',
            'DEPENDENCIA_LEVE'    => '#7A68B0',
            'DEPENDENCIA_MODERADA'=> '#D4843A',
            'DEPENDENCIA_SEVERA'  => '#E76F51',
            'DEPENDENCIA_TOTAL'   => '#E97A5F',
        ];

        return [
            'labels'  => $resultados->pluck('nivel_dependencia')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->nivel_dependencia)] ?? '#2F3E5C')->toArray(),
        ];
    }

    public function riesgosCaida(): array
    {
        $resultados = DB::table('registros_movilidad')
            ->whereNotNull('riesgo_caida')
            ->select('riesgo_caida', DB::raw('COUNT(*) as total'))
            ->groupBy('riesgo_caida')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'BAJO'  => '#2A9D8F',
            'MEDIO' => '#D4843A',
            'ALTO'  => '#E76F51',
        ];

        return [
            'labels'  => $resultados->pluck('riesgo_caida')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->riesgo_caida)] ?? '#7A68B0')->toArray(),
        ];
    }

    // ────────────────────────────────────────────────────────────
    // CONTACTOS / FAMILIARES V2
    // ────────────────────────────────────────────────────────────

    public function familiaresResumen(): array
    {
        $totalFamiliares    = DB::table('contactos')->count();
        $vinculosActivos    = DB::table('residentes_contactos')->where('estado', 'ACTIVO')->count();
        $adultosSinFamiliar = $this->adultosSinFamiliar();

        return [
            'total_familiares'     => $totalFamiliares,
            'vinculos_activos'     => $vinculosActivos,
            'adultos_sin_familiar' => count($adultosSinFamiliar),
        ];
    }

    public function vinculosLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('residentes_contactos as rc')
            ->join('residentes as r', 'rc.cod_residente', '=', 'r.cod_residente')
            ->join('contactos as c', 'rc.cod_contacto', '=', 'c.cod_contacto')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno)) AS adulto"),
                DB::raw("TRIM(CONCAT(c.nombres, ' ', c.apellido_paterno, ' ', COALESCE(c.apellido_materno, ''))) AS familiar"),
                'rc.parentesco as parentesco_vinculo',
                'rc.responsable_principal as es_responsable',
                'rc.estado'
            )
            ->orderBy('r.apellido_paterno')
            ->limit($limite)
            ->get();
    }

    public function adultosSinFamiliar(): array
    {
        $conFamiliar = DB::table('residentes_contactos')
            ->where('estado', 'ACTIVO')
            ->pluck('cod_residente')
            ->unique()
            ->toArray();

        return DB::table('residentes')
            ->where('estado', 'ACTIVO')
            ->whereNotIn('cod_residente', $conFamiliar)
            ->select('cod_residente', DB::raw("TRIM(CONCAT(nombres, ' ', apellido_paterno)) AS nombre"))
            ->orderBy('apellido_paterno')
            ->get()
            ->toArray();
    }

    public function parentescosDistribucion(): array
    {
        $resultados = DB::table('residentes_contactos')
            ->whereNotNull('parentesco')
            ->select('parentesco as parentesco_vinculo', DB::raw('COUNT(*) as total'))
            ->groupBy('parentesco')
            ->orderByDesc('total')
            ->get();

        return [
            'labels'  => $resultados->pluck('parentesco_vinculo')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => ['#2F3E5C', '#2A9D8F', '#D4843A', '#7A68B0', '#E76F51', '#4A7C59'],
        ];
    }

    // ────────────────────────────────────────────────────────────
    // EQUIPO INSTITUCIONAL V2
    // ────────────────────────────────────────────────────────────

    public function equipoResumen(): array
    {
        $salud = DB::table('personal')->where('estado', 'ACTIVO')
            ->whereIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();
        $admin = DB::table('personal')->where('estado', 'ACTIVO')
            ->whereNotIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        return [
            'total'          => $salud + $admin,
            'personal_salud' => $salud,
            'personal_admin' => $admin,
        ];
    }

    public function personalSaludLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('personal as p')
            ->join('usuarios as u', 'p.cod_usuario', '=', 'u.cod_usuario')
            ->whereIn('p.profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->select(
                'p.cod_personal',
                DB::raw("TRIM(CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS nombre_completo"),
                'p.profesion as rol_nombre',
                'p.especialidad',
                'p.matricula_profesional',
                'u.correo',
                'p.telefono',
                'p.estado',
                // V2 no guarda fecha de ingreso en personal. Se deriva de la
                // primera asignación institucional registrada para la persona.
                DB::raw('(SELECT MIN(ap.fecha_asignacion) FROM asignaciones_personal ap WHERE ap.cod_personal = p.cod_personal) AS fecha_ingreso')
            )
            ->orderBy('p.apellido_paterno')
            ->limit($limite)
            ->get();
    }

    public function personalAdminLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('personal as p')
            ->join('usuarios as u', 'p.cod_usuario', '=', 'u.cod_usuario')
            ->whereNotIn('p.profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->select(
                'p.cod_personal',
                DB::raw("TRIM(CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS nombre_completo"),
                'p.profesion as rol_nombre',
                'u.correo',
                'p.telefono',
                'p.estado',
                // El área vigente se obtiene de la asignación más reciente;
                // áreas no tiene una columna responsable ni personal directo.
                DB::raw('(SELECT a.nombre FROM asignaciones_personal ap JOIN areas a ON a.cod_area = ap.cod_area WHERE ap.cod_personal = p.cod_personal ORDER BY ap.fecha_asignacion DESC LIMIT 1) AS area_nombre'),
                DB::raw('(SELECT MIN(ap.fecha_asignacion) FROM asignaciones_personal ap WHERE ap.cod_personal = p.cod_personal) AS fecha_ingreso')
            )
            ->orderBy('p.apellido_paterno')
            ->limit($limite)
            ->get();
    }

    public function especialidadesDistribucion(): array
    {
        $resultados = DB::table('personal')
            ->whereNotNull('especialidad')
            ->select('especialidad as nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('especialidad')
            ->orderByDesc('total')
            ->get();

        return [
            'labels'  => $resultados->pluck('nombre')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => ['#2F3E5C', '#2A9D8F', '#D4843A', '#7A68B0', '#E76F51'],
        ];
    }

    // ────────────────────────────────────────────────────────────
    // ACTIVIDADES V2
    // ────────────────────────────────────────────────────────────

    public function actividadesResumen(): array
    {
        return [
            'total'       => DB::table('actividades')->count(),
            'activas'     => DB::table('actividades')->where('estado', 'ACTIVA')->count(),
            'programadas' => DB::table('actividades')->where('estado', 'PROGRAMADA')->count(),
            'pendientes'  => DB::table('actividades')->whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(),
            'completadas' => DB::table('actividades')->whereIn('estado', ['REALIZADA', 'COMPLETADA'])->count(),
            'canceladas'  => DB::table('actividades')->whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
        ];
    }

    public function actividadesLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('actividades')
            ->select('cod_actividad', 'nombre as titulo', 'tipo as tipo_actividad', 'fecha_hora as fecha', 'estado')
            ->orderByDesc('fecha_hora')
            ->limit($limite)
            ->get();
    }

    public function actividadesPorMes(): array
    {
        $resultados = DB::table('actividades')
            ->where('fecha_hora', '>=', now()->subMonths(6)->toDateString())
            ->select(
                DB::raw(self::expresionAnioMes('fecha_hora') . " as mes"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw(self::expresionAnioMes('fecha_hora')))
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $resultados->pluck('mes')->toArray(),
            'data'   => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    public function actividadesEstado(): array
    {
        $resultados = DB::table('actividades')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        return [
            'labels'  => $resultados->pluck('estado')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => ['#2A9D8F', '#D4843A', '#2F3E5C', '#E76F51'],
        ];
    }

    public function actividadesPorAdulto(int $limite = 20): \Illuminate\Support\Collection
    {
        return DB::table('participantes_actividad as pa')
            ->join('residentes as r', 'pa.cod_residente', '=', 'r.cod_residente')
            ->select(
                'r.cod_residente',
                DB::raw("TRIM(CONCAT(r.nombres, ' ', r.apellido_paterno)) AS nombre"),
                DB::raw('COUNT(pa.cod_actividad) as total_actividades')
            )
            ->groupBy('r.cod_residente', 'r.nombres', 'r.apellido_paterno')
            ->orderByDesc('total_actividades')
            ->limit($limite)
            ->get();
    }

    // ────────────────────────────────────────────────────────────
    // AUDITORÍA / BITÁCORA V2
    // ────────────────────────────────────────────────────────────

    public function bitacoraResumen(): array
    {
        return [
            'total'       => DB::table('activity_log')->count(),
            'hoy'         => DB::table('activity_log')->whereDate('created_at', today())->count(),
            'esta_semana' => DB::table('activity_log')->where('created_at', '>=', now()->subDays(7))->count(),
            'modulos'     => DB::table('activity_log')->distinct('log_name')->count('log_name'),
        ];
    }

    public function bitacoraLista(int $limite = 50): \Illuminate\Support\Collection
    {
        return DB::table('activity_log as al')
            ->leftJoin('usuarios as u', 'al.causer_id', '=', 'u.cod_usuario')
            ->leftJoin('personal as p', 'p.cod_usuario', '=', 'u.cod_usuario')
            ->select(
                'al.id',
                'al.log_name',
                'al.description',
                'al.event',
                'al.created_at',
                DB::raw("COALESCE(TRIM(CONCAT(p.nombres, ' ', p.apellido_paterno)), u.correo, 'Sistema') AS usuario_nombre")
            )
            ->orderByDesc('al.created_at')
            ->limit($limite)
            ->get();
    }

    public function bitacoraModulos(): array
    {
        $resultados = DB::table('activity_log')
            ->select('log_name', DB::raw('COUNT(*) as total'))
            ->groupBy('log_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels'  => $resultados->pluck('log_name')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => ['#2F3E5C', '#2A9D8F', '#D4843A', '#7A68B0', '#E76F51', '#4A7C59', '#3D5A80', '#E97A5F'],
        ];
    }

    public function bitacoraEventos(): array
    {
        $resultados = DB::table('activity_log')
            ->whereNotNull('event')
            ->select('event', DB::raw('COUNT(*) as total'))
            ->groupBy('event')
            ->orderByDesc('total')
            ->get();

        return [
            'labels'  => $resultados->pluck('event')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => ['#2A9D8F', '#D4843A', '#E76F51', '#2F3E5C'],
        ];
    }

    public function bitacoraTendencia(): array
    {
        $resultados = DB::table('activity_log')
            ->where('created_at', '>=', now()->subDays(14))
            ->select(
                DB::raw(self::expresionDia('created_at') . " as dia"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw(self::expresionDia('created_at')))
            ->orderBy('dia')
            ->get();

        return [
            'labels'  => $resultados->pluck('dia')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int)$v)->toArray(),
            'colores' => ['#7A68B0', '#2A9D8F', '#2F3E5C', '#D4843A'],
        ];
    }
}
