<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\AsignacionPersonal;
use App\Models\Documento;
use App\Models\MovimientoLaboralPersonal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\DocumentacionUsuarioService;
use Spatie\Activitylog\Models\Activity;

class UsuarioFichaService
{
    public function obtenerResumenGerencia(User $actor, User $usuario): array
    {
        $actor = app(AutorizarGestionPersonal::class)->autorizar($actor, $usuario);
        abort_unless(app(AutorizarGestionPersonal::class)->esGerencia($actor)
            && $actor->can('personal_institucional.ver'), 403);
        $usuario->load(['personal', 'roles']);
        $personal = $usuario->personal;
        $lectura = app(ConsultarDocumentacionPersonal::class);
        $archivos = $lectura->archivos($actor)->where('cod_usuario', $usuario->getKey())->toBase()->get(['nombre', 'estado']);
        $pendientes = collect();
        $movimientos = collect();
        $asignaciones = collect();
        $totalAsignaciones = 0;
        $hayCarpeta = $archivos->isNotEmpty();

        if ($personal && $actor->can('asignaciones_personal.ver')) {
            $query = $personal->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->whereHas('jornada', fn ($q) => $q->whereDate('fecha_jornada', '>=', today())
                    ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'ACTIVO']))
                ->with(['area', 'jornada.turno'])
                ->orderBy(\App\Models\Jornada::select('fecha_jornada')->whereColumn('jornadas.cod_jornada', 'asignaciones_personal.cod_jornada'))
                ->orderBy('cod_asignacion_personal');
            $totalAsignaciones = (clone $query)->count();
            $asignaciones = $query->limit(5)->get();
        }

        if ($personal && app(CapacidadDocumentalPersonal::class)->disponible()) {
            $solicitudes = $lectura->solicitudesEnCarpetas($actor)->where('cod_personal', $personal->getKey())->get();
            app(PlazoExpedienteDocumental::class)->preparar($solicitudes);
            $hayCarpeta = $hayCarpeta || $solicitudes->isNotEmpty();
            $pendientes = $solicitudes->map(function ($destino) use ($lectura) {
                $estado = $lectura->situacion($destino);

                return ['nombre' => $destino->solicitud->nombre_documento, 'estado' => $estado['situacion'],
                    'limite' => $estado['limite']?->format('d/m/Y'), 'requiere_entrega' => $estado['requiere_entrega']];
            })->where('requiere_entrega', true);
            $movimientos = MovimientoLaboralPersonal::where('cod_personal', $personal->getKey())
                ->orderByDesc('fecha_hora_efecto')->orderByDesc('cod_movimiento_laboral')->limit(3)->get();
        }

        $checklist = collect(app(DocumentacionUsuarioService::class)->obtenerChecklistUsuario($usuario))
            ->filter(fn ($item) => $item['obligatorio'] || $item['cargado'])->values();
        $faltantesObligatorios = $checklist->where('obligatorio', true)->where('cargado', false)->count();
        $porRevisar = $checklist->where('cargado', true)->where('conflicto', false)
            ->whereIn('estado', ['CARGADO', 'PENDIENTE'])->count();
        $edad = $personal?->fecha_nacimiento?->lte(today()) ? $personal->fecha_nacimiento->age : null;
        $mesesIngreso = $personal?->fecha_ingreso?->lte(today())
            ? (int) $personal->fecha_ingreso->diffInMonths(today()) : null;
        $antiguedad = $mesesIngreso === null ? null : ($mesesIngreso < 1 ? 'Menos de un mes'
            : \Carbon\CarbonInterval::months($mesesIngreso)->cascade()->locale('es')->forHumans(['parts' => 2]));
        $urlDocumentacion = route('admin.personal.documentacion', $hayCarpeta ? ['carpeta' => $usuario->getKey()] : []);

        return compact('usuario', 'archivos', 'pendientes', 'movimientos', 'asignaciones', 'totalAsignaciones',
            'checklist', 'faltantesObligatorios', 'porRevisar', 'edad', 'antiguedad', 'urlDocumentacion');
    }

    /**
     * Obtiene el expediente consolidado del usuario.
     */
    public function obtenerExpedienteCompleto(User $usuario): array
    {
        $usuario->load([
            'roles',
        ]);

        return [
            'usuario' => $usuario,
            'rol' => $usuario->roles->first()?->name ?? 'Sin rol',
            'nombre_rol' => $this->formatearRol($usuario->roles->first()?->name),
            'area' => $usuario->areaInstitucional?->nombre ?? 'Sin asignar',
            'fecha_ingreso' => $usuario->personal?->fecha_ingreso?->format('d/m/Y') ?? 'No registrada',
            'horarios' => $this->obtenerHorariosActivos($usuario),
            'historial_horarios' => $this->obtenerHistorialHorarios($usuario),
            'avance_documental' => app(DocumentacionUsuarioService::class)->calcularAvanceDocumental($usuario),
        ];
    }

    /**
     * Obtiene las asignaciones de horarios activas del usuario.
     */
    public function obtenerHorariosActivos(User $usuario)
    {
        // Usuario y personal tienen claves distintas. El horario se localiza por
        // personal.cod_usuario y luego por la jornada/turno relacionados.
        return AsignacionPersonal::query()
            ->with(['jornada.turno', 'area', 'personal'])
            ->whereHas('personal', fn ($query) => $query->where('cod_usuario', $usuario->cod_usuario))
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')
            ->first();
    }

    /**
     * Obtiene el historial de asignaciones de horarios pasadas.
     */
    public function obtenerHistorialHorarios(User $usuario)
    {
        // El historial V2 proviene de asignaciones_personal; no se consultan las
        // La agenda se obtiene de jornadas y asignaciones_personal V2.
        return AsignacionPersonal::query()
            ->with(['jornada.turno', 'area', 'personal'])
            ->whereHas('personal', fn ($query) => $query->where('cod_usuario', $usuario->cod_usuario))
            ->latest('fecha_asignacion')
            ->get();
    }

    /**
     * Obtiene el historial de auditoría de actividad del usuario desde Spatie Activitylog.
     */
    public function obtenerHistorialActividad(User $usuario, array $filtros = [])
    {
        $query = Activity::query()
            ->where(function ($q) use ($usuario) {
                // Eventos causados por el usuario o sobre el usuario
                $q->where('causer_id', $usuario->cod_usuario)
                    ->orWhere(function ($sub) use ($usuario) {
                        $sub->where('subject_type', User::class)
                            ->where('subject_id', $usuario->cod_usuario);
                    })
                    ->orWhere(function ($sub) use ($usuario) {
                        $sub->where('subject_type', Documento::class)
                            ->whereIn('subject_id', function ($db) use ($usuario) {
                                $db->select('cod_documento')
                                    ->from('documentos')
                                    ->where('cod_usuario', $usuario->cod_usuario);
                            });
                    });
            });

        // Aplicar filtros de fecha y acción
        if (! empty($filtros['accion'])) {
            $query->where('event', $filtros['accion']);
        }

        if (! empty($filtros['fecha_desde'])) {
            $query->where('created_at', '>=', $filtros['fecha_desde'].' 00:00:00');
        }

        if (! empty($filtros['fecha_hasta'])) {
            $query->where('created_at', '<=', $filtros['fecha_hasta'].' 23:59:59');
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    /**
     * Helper para formatear nombres de roles de forma amigable.
     */
    private function formatearRol(?string $roleName): string
    {
        if (! $roleName) {
            return 'SIN ROL';
        }

        return match ($roleName) {
            'super_admin' => 'SUPER ADMINISTRADOR',
            'admin' => 'ADMINISTRADOR',
            'personal_admin' => 'PERSONAL ADMINISTRATIVO',
            'personal_salud' => 'PERSONAL DE SALUD',
            'familiar' => 'FAMILIAR',
            default => strtoupper(str_replace('_', ' ', $roleName))
        };
    }
}
