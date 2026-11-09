<?php

namespace App\Modulos\Reportes\Aplicacion\Consultas;

use App\Models\Area;
use App\Models\Contacto;
use App\Models\FotografiaInstitucional;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Identidad\Aplicacion\Consultas\CapacidadDocumentalPersonal;
use App\Modulos\Identidad\Aplicacion\Consultas\ConsultarDocumentacionPersonal;
use App\Modulos\Identidad\Aplicacion\Consultas\PlazoExpedienteDocumental;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarCoberturaEquipo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class ConsultarReportePorVistaGerencia
{
    private const VISTAS = [
        'inicio' => ['Resumen de Gerencia', 'dashboard', ['personal_institucional.ver', 'habitaciones.ver', 'ocupaciones_cama.ver', 'alertas.ver']],
        'personal' => ['Personal institucional', 'admin.personal-institucional', ['personal_institucional.ver']],
        'documentacion' => ['Documentación laboral', 'admin.personal.documentacion', ['personal_institucional.ver']],
        'solicitudes' => ['Solicitudes y entregas', 'admin.personal.documentacion', ['personal_institucional.ver']],
        'areas' => ['Áreas y equipo', 'admin.areas-institucionales.index', ['areas.ver', 'areas.reportes']],
        'planificacion' => ['Planificación del equipo', 'admin.turnos-asignaciones.index', ['turnos.ver', 'jornadas.ver', 'asignaciones_personal.ver']],
        'cobertura' => ['Cobertura del equipo', 'admin.cobertura-equipo.index', ['jornadas.ver', 'asignaciones_personal.ver']],
        'residentes' => ['Residentes', 'admin.residentes.index', ['residentes.ver']],
        'contactos' => ['Familiares y contactos', 'admin.familia-social.red-apoyo', ['contactos.ver', 'residentes_contactos.ver', 'residentes.ver']],
        'ocupacion' => ['Ocupación', 'admin.habitaciones.index', ['habitaciones.ver', 'ocupaciones_cama.ver']],
        'alertas' => ['Avisos administrativos', 'admin.alertas-clinicas.index', ['alertas.ver', 'personal_institucional.ver']],
        'galeria' => ['Galería del centro', 'admin.galeria.index', ['fotografias_institucionales.ver']],
        'avisos-planificacion' => ['Avisos de planificación', 'admin.avisos-planificacion.index', ['turnos.asignar']],
    ];

    public function puedePreparar(?User $actor, string $vista): bool
    {
        if (! isset(self::VISTAS[$vista]) || $actor?->estado !== 'ACTIVO' || ! $actor->hasRole('GERENTE')
            || $actor->hasAnyRole(['SUPERADMINISTRADOR', 'FAMILIAR']) || app(RolePreviewService::class)->isActive($actor)
            || ! $actor->can('reportes.institucional') || ! $actor->can(self::VISTAS[$vista][2])) {
            return false;
        }

        return match ($vista) {
            'residentes', 'contactos' => Gate::forUser($actor)->allows('viewAny', Residente::class),
            'galeria' => Gate::forUser($actor)->allows('viewAny', FotografiaInstitucional::class),
            'avisos-planificacion' => class_exists(ConsultarAvisosPlanificacion::class)
                && app(ConsultarAvisosPlanificacion::class)->puedeConsultar($actor),
            default => true,
        };
    }

    public function autorizar(User $actor, string $vista): void
    {
        abort_unless(isset(self::VISTAS[$vista]), 404);
        abort_unless($this->puedePreparar($actor->fresh(), $vista), 403);
    }

    public function consultar(User $actor, string $vista, array $entrada): array
    {
        $this->autorizar($actor, $vista);
        [$titulo, $ruta] = self::VISTAS[$vista];
        $referencia = 'Situación actual';
        $ambito = 'Resumen general de la vista';
        $parametros = [];
        $limites = ['Los indicadores son agregados. Una consulta actual no equivale a un cierre histórico.'];

        if (in_array($vista, ['inicio', 'ocupacion', 'alertas'], true)) {
            $seccion = $vista === 'inicio' ? 'resumen' : $vista;
            $reporte = app(ConsultarIndicadoresGerencia::class)->consultar($actor, $seccion);

            return ['titulo' => $titulo, 'vista_origen' => $vista, 'volver' => route($ruta, absolute: false)]
                + array_intersect_key($reporte, array_flip(['seccion', 'referencia', 'ambito', 'filas', 'limites', 'consultado_en']));
        }

        if (in_array($vista, ['cobertura', 'planificacion'], true)) {
            [$indicadores, $parametros, $referencia, $ambito, $limite] = $vista === 'cobertura'
                ? $this->cobertura($actor, $entrada) : $this->planificacion($entrada);
            $limites[] = $limite;
        } else {
            $indicadores = $this->indicadoresGenerales($actor, $vista);
            $limites[] = 'El resumen abarca los registros autorizados de la vista con independencia de los filtros y de la página abierta.';
        }

        if ($vista === 'solicitudes') {
            $parametros = ['vista' => 'solicitudes'];
        }
        $filas = array_map(fn (array $fila) => ['indicador' => $fila[0], 'valor' => $fila[1], 'unidad' => $fila[2],
            'referencia' => $referencia, 'calidad' => $fila[1] === null ? 'Consulta no disponible' : 'Consulta realizada'], $indicadores);

        return ['vista_origen' => $vista, 'volver' => route($ruta, $parametros, absolute: false), 'seccion' => $vista,
            'consultado_en' => now()->toIso8601String()] + compact('titulo', 'referencia', 'ambito', 'filas', 'limites');
    }

    private function indicadoresGenerales(User $actor, string $vista): array
    {
        return match ($vista) {
            'personal' => $this->porEstado(Personal::query(), 'Trabajadores registrados', 'personas', ['ACTIVO' => 'Trabajadores activos', 'INACTIVO' => 'Trabajadores inactivos']),
            'documentacion' => $this->porEstado(app(ConsultarDocumentacionPersonal::class)->archivos($actor), 'Archivos laborales registrados', 'archivos', [
                'VALIDADO' => 'Archivos validados', 'OBSERVADO' => 'Archivos observados', 'CARGADO' => 'Archivos cargados', 'PENDIENTE' => 'Archivos pendientes de revisión',
            ]),
            'solicitudes' => $this->solicitudes($actor),
            'areas' => $this->porEstado(Area::query(), 'Áreas registradas', 'áreas', ['ACTIVO' => 'Áreas activas', 'ACTIVA' => 'Áreas activas']),
            'residentes' => $this->porEstado(Residente::query(), 'Residentes registrados', 'personas', ['ADMITIDO' => 'Residentes admitidos']),
            'contactos' => [
                ['Personas de contacto registradas', Contacto::query()->count(), 'contactos'],
                ['Contactos activos', Contacto::query()->where('estado', 'ACTIVO')->count(), 'contactos'],
                ['Vínculos activos con residentes', ResidenteContacto::query()->where('estado', 'ACTIVO')->count(), 'vínculos'],
            ],
            'galeria' => $this->porEstado(FotografiaInstitucional::query(), 'Fotografías registradas', 'fotografías', ['ACTIVA' => 'Fotografías activas', 'ARCHIVADA' => 'Fotografías archivadas']),
            'avisos-planificacion' => $this->avisosPlanificacion($actor),
        };
    }

    private function porEstado(Builder $consulta, string $titulo, string $unidad, array $etiquetas): array
    {
        $totales = $consulta->reorder()->select('estado')->selectRaw('COUNT(*) AS cantidad')->groupBy('estado')->pluck('cantidad', 'estado');
        $grupos = [];
        foreach ($etiquetas as $estado => $etiqueta) {
            $grupos[$etiqueta] = ($grupos[$etiqueta] ?? 0) + (int) $totales->get($estado, 0);
        }

        return [[$titulo, (int) $totales->sum(), $unidad], ...collect($grupos)->map(fn ($valor, $etiqueta) => [$etiqueta, $valor, $unidad])->values()->all()];
    }

    private function solicitudes(User $actor): array
    {
        abort_unless(app(CapacidadDocumentalPersonal::class)->disponible(), 409, 'El seguimiento documental todavía no está disponible.');
        $lectura = app(ConsultarDocumentacionPersonal::class);
        $solicitudes = $lectura->solicitudesEnCarpetas($actor)->get();
        app(PlazoExpedienteDocumental::class)->preparar($solicitudes);
        $situaciones = $solicitudes->map(fn ($solicitud) => $lectura->situacion($solicitud));

        return [
            ['Solicitudes por trabajador', $solicitudes->count(), 'solicitudes'],
            ['Pendientes de entrega o corrección', $situaciones->where('requiere_entrega', true)->count(), 'solicitudes'],
            ['Con plazo vencido', $situaciones->where('tipoAviso', 'vencido')->count(), 'solicitudes'],
            ['Recibidas para revisión', $situaciones->where('tipoAviso', 'recibido')->count(), 'solicitudes'],
        ];
    }

    private function avisosPlanificacion(User $actor): array
    {
        $consulta = app(ConsultarAvisosPlanificacion::class)->consulta($actor);
        $totales = $consulta->reorder()->select('e.estado_aviso_planificacion')->selectRaw('COUNT(*) AS cantidad')
            ->groupBy('e.estado_aviso_planificacion')->pluck('cantidad', 'e.estado_aviso_planificacion');

        return [
            ['Cambios visibles en la bandeja', (int) $totales->sum(), 'avisos'],
            ['Pendientes de preparación', (int) $totales->get('PENDIENTE', 0), 'avisos'],
            ['Con preparación fallida', (int) $totales->get('FALLIDO', 0), 'avisos'],
        ];
    }

    private function cobertura(User $actor, array $entrada): array
    {
        $datos = app(ConsultarCoberturaEquipo::class)->consultar($actor, $entrada);
        $parametros = $datos['filtros'];
        $referencia = $datos['vigente'] ? 'Horario vigente al '.$datos['ahora']->format('d/m/Y H:i') : 'Fecha de jornada '.Carbon::parse($datos['fecha'])->format('d/m/Y');
        $ambito = implode(' · ', array_filter([
            $datos['areas']->firstWhere('cod_area', $parametros['area'] ?? null)?->nombre ?? 'Todas las áreas',
            $datos['turnos']->firstWhere('cod_turno', $parametros['turno'] ?? null)?->nombre ?? 'Todos los turnos',
            $datos['vigente'] ? 'Jornadas abiertas' : (isset($parametros['estado']) ? 'Jornada '.$parametros['estado'] : null),
        ]));
        $indicadores = [
            ['Personas con asignación registrada', $datos['resumen']['personas'], 'personas'],
            ['Turnos con asignaciones', $datos['resumen']['turnos'], 'turnos'],
            ['Asignaciones registradas', $datos['resumen']['asignaciones'], 'asignaciones'],
        ];

        return [$indicadores, $parametros, $referencia, $ambito, 'Las asignaciones registradas no acreditan asistencia ni suficiencia de dotación.'];
    }

    private function planificacion(array $entrada): array
    {
        $filtros = Validator::make($entrada, [
            'fecha' => ['nullable', 'date_format:Y-m-d'], 'periodo' => ['nullable', 'in:dia,semana,mes'],
            'area' => ['nullable', 'string', 'exists:areas,cod_area'], 'turno' => ['nullable', 'string', 'exists:turnos,cod_turno'],
        ])->validate();
        $fecha = Carbon::parse($filtros['fecha'] ?? today());
        [$inicio, $fin] = match ($filtros['periodo'] ?? 'semana') {
            'dia' => [$fecha, $fecha],
            'mes' => [$fecha->copy()->startOfMonth(), $fecha->copy()->endOfMonth()],
            'semana' => [$fecha->copy()->startOfWeek(Carbon::MONDAY), $fecha->copy()->endOfWeek(Carbon::SUNDAY)],
        };
        $referencia = 'Jornadas del '.$inicio->format('d/m/Y').' al '.$fin->format('d/m/Y');
        $area = isset($filtros['area']) ? Area::find($filtros['area'])?->nombre : null;
        $turno = isset($filtros['turno']) ? Turno::find($filtros['turno'])?->nombre : null;
        $ambito = ($area ?? 'Todas las áreas').' · '.($turno ?? 'Todos los turnos');
        $consulta = DB::table('asignaciones_personal as ap')->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
            ->whereDate('j.fecha_jornada', '>=', $inicio->toDateString())->whereDate('j.fecha_jornada', '<=', $fin->toDateString())
            ->whereIn('ap.estado', ['ACTIVO', 'ACTIVA'])
            ->when($filtros['area'] ?? null, fn ($q, $codigo) => $q->where('ap.cod_area', $codigo))
            ->when($filtros['turno'] ?? null, fn ($q, $codigo) => $q->where('j.cod_turno', $codigo));

        $indicadores = [
            ['Asignaciones planificadas', (clone $consulta)->count(), 'asignaciones'],
            ['Personas con asignación', (clone $consulta)->distinct()->count('ap.cod_personal'), 'personas'],
            ['Jornadas con asignaciones', $consulta->distinct()->count('ap.cod_jornada'), 'jornadas'],
        ];

        return [$indicadores, $filtros, $referencia, $ambito,
            'Incluye asignaciones activas registradas para el período, el área y el turno. La búsqueda por trabajador corresponde al listado en pantalla.'];
    }
}
