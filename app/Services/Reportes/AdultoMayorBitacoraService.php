<?php

namespace App\Services\Reportes;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * AdultoMayorBitacoraService
 *
 * Servicio dedicado y escalable para obtener y estructurar la bitácora
 * de actividad de un Adulto Mayor desde Spatie Activitylog.
 *
 * Para agregar nuevos tipos de eventos en el futuro:
 *   1. Agregar el event name en $iconosEvento y $coloresEvento.
 *   2. El servicio lo resolverá automáticamente.
 *
 * @version 1.0
 */
class AdultoMayorBitacoraService
{
    /**
     * Mapa de eventos → ícono Phosphor.
     * ESCALABLE: agrega aquí nuevos eventos sin tocar la vista.
     */
    protected array $iconosEvento = [
        'created'              => 'ph-bold ph-user-circle-plus',
        'updated'              => 'ph-bold ph-pencil-simple',
        'deleted'              => 'ph-bold ph-trash',
        'restored'             => 'ph-bold ph-arrow-counter-clockwise',
        'observacion_creada'   => 'ph-bold ph-clipboard-text',
        'atencion_registrada'  => 'ph-bold ph-stethoscope',
        'actividad_registrada' => 'ph-bold ph-calendar-check',
        'documento_subido'     => 'ph-bold ph-file-arrow-up',
        'familiar_vinculado'   => 'ph-bold ph-users-three',
        'acceso'               => 'ph-bold ph-sign-in',
        'estado_cambiado'      => 'ph-bold ph-toggle-right',
        'archivado'            => 'ph-bold ph-archive',
        'restaurado'           => 'ph-bold ph-arrow-u-up-left',
    ];

    /**
     * Mapa de eventos → color CSS (clases Tailwind bg/text/border).
     * ESCALABLE: agrega aquí nuevos eventos sin tocar la vista.
     */
    protected array $coloresEvento = [
        'created'              => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
        'updated'              => ['bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'badge' => 'bg-blue-100 text-blue-700 border-blue-200',    'dot' => 'bg-blue-500'],
        'deleted'              => ['bg' => 'bg-red-100',     'text' => 'text-red-700',     'badge' => 'bg-red-100 text-red-700 border-red-200',       'dot' => 'bg-red-500'],
        'restored'             => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'badge' => 'bg-amber-100 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
        'observacion_creada'   => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'badge' => 'bg-amber-100 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500'],
        'atencion_registrada'  => ['bg' => 'bg-sky-100',     'text' => 'text-sky-700',     'badge' => 'bg-sky-100 text-sky-700 border-sky-200',       'dot' => 'bg-sky-500'],
        'actividad_registrada' => ['bg' => 'bg-lime-100',    'text' => 'text-lime-700',    'badge' => 'bg-lime-100 text-lime-700 border-lime-200',    'dot' => 'bg-lime-500'],
        'documento_subido'     => ['bg' => 'bg-orange-100',  'text' => 'text-orange-700',  'badge' => 'bg-orange-100 text-orange-700 border-orange-200', 'dot' => 'bg-orange-500'],
        'familiar_vinculado'   => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'badge' => 'bg-rose-100 text-rose-700 border-rose-200',    'dot' => 'bg-rose-500'],
        'acceso'               => ['bg' => 'bg-slate-100',   'text' => 'text-slate-700',   'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500'],
        'estado_cambiado'      => ['bg' => 'bg-teal-100',    'text' => 'text-teal-700',    'badge' => 'bg-teal-100 text-teal-700 border-teal-200',    'dot' => 'bg-teal-500'],
        'archivado'            => ['bg' => 'bg-slate-100',   'text' => 'text-slate-700',   'badge' => 'bg-slate-100 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500'],
        'restaurado'           => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500'],
    ];

    /**
     * Traducción de eventos a etiquetas en español para mostrar en la vista.
     */
    protected array $etiquetasEvento = [
        'created'              => 'Ficha creada',
        'updated'              => 'Ficha actualizada',
        'deleted'              => 'Registro eliminado',
        'restored'             => 'Registro restaurado',
        'observacion_creada'   => 'Observación registrada',
        'atencion_registrada'  => 'Atención registrada',
        'actividad_registrada' => 'Actividad registrada',
        'documento_subido'     => 'Documento subido',
        'familiar_vinculado'   => 'Familiar vinculado',
        'acceso'               => 'Acceso al sistema',
        'estado_cambiado'      => 'Estado actualizado',
        'archivado'            => 'Ficha archivada',
        'restaurado'           => 'Ficha restaurada',
    ];

    /**
     * Obtiene la bitácora paginada o limitada de un adulto mayor.
     *
     * @param  string  $codAm       Código del adulto mayor (ej. AM_0001)
     * @param  int     $limite      Cantidad máxima de registros
     * @param  string|null $filtroEvento  Filtrar por evento específico
     * @return Collection
     */
    public function obtenerBitacora(
        string $codAm,
        int    $limite = 50,
        ?string $filtroEvento = null
    ): Collection {
        if (!Schema::hasTable('activity_log')) {
            return collect();
        }

        Carbon::setLocale('es');

        $query = DB::table('activity_log')
            ->leftJoin('users',
                DB::raw('activity_log.causer_id::text'),
                '=',
                DB::raw('users.cod_usu::text')
            )
            ->select([
                'activity_log.id',
                'activity_log.log_name',
                'activity_log.description',
                'activity_log.subject_id',
                'activity_log.subject_type',
                'activity_log.causer_id',
                'activity_log.causer_type',
                'activity_log.event',
                'activity_log.properties',
                'activity_log.created_at',
                'users.nombres as causer_nombres',
                'users.ap_paterno as causer_ap_paterno',
            ])
            ->where(function ($q) use ($codAm) {
                // Eventos directos sobre el AdultoMayor
                $q->where(function ($q2) use ($codAm) {
                    $q2->where('activity_log.subject_type', 'App\\Models\\AdultoMayor')
                       ->where('activity_log.subject_id', $codAm);
                })
                // Eventos sobre submódulos (observaciones, atenciones, etc.)
                ->orWhere(function ($q2) use ($codAm) {
                    $q2->whereIn('activity_log.subject_type', [
                        'App\\Models\\ObsAdulto',
                        'App\\Models\\AtencionAdulto',
                        'App\\Models\\ActividadAdulto',
                        'App\\Models\\DocumentoAdultoMayor',
                        'App\\Models\\Familiar',
                        'App\\Models\\FamiliarAdulto',
                        'App\\Models\\EvaluacionCognitiva',
                        // FASE 2: Módulos médicos y administrativos
                        'App\\Models\\FichaMedicaAdulto',
                        'App\\Models\\MedicacionAdulto',
                        'App\\Models\\AdministracionMedicacion',
                        'App\\Models\\SignosVitalesAdulto',
                        'App\\Models\\ValoracionFuncionalAdulto',
                        'App\\Models\\HistorialEstadoAdulto',
                    ])
                    ->where('activity_log.properties', 'like', '%' . $codAm . '%');
                })
                // Logs de log_name "Adulto Mayor" relacionados por descripción
                ->orWhere(function ($q2) use ($codAm) {
                    $q2->where('activity_log.log_name', 'Adulto Mayor')
                       ->where('activity_log.description', 'like', '%' . $codAm . '%');
                });
            })
            ->orderByDesc('activity_log.created_at');

        if ($filtroEvento) {
            $query->where('activity_log.event', $filtroEvento);
        }

        return $query
            ->limit($limite)
            ->get()
            ->map(fn($log) => $this->formatearRegistro($log));
    }

    /**
     * Obtiene los tipos de eventos distintos para el filtro.
     */
    public function obtenerTiposEventos(string $codAm): Collection
    {
        if (!Schema::hasTable('activity_log')) {
            return collect();
        }

        return DB::table('activity_log')
            ->where(function ($q) use ($codAm) {
                $q->where('subject_id', $codAm)
                  ->orWhere('description', 'like', '%' . $codAm . '%');
            })
            ->whereNotNull('event')
            ->distinct()
            ->pluck('event')
            ->filter()
            ->map(fn($event) => [
                'valor'    => $event,
                'etiqueta' => $this->etiquetasEvento[$event] ?? ucfirst($event),
            ]);
    }

    /**
     * Formatea un registro de la bitácora para consumo en la vista.
     */
    protected function formatearRegistro(object $log): array
    {
        $evento   = $log->event ?? 'updated';
        $colores  = $this->coloresEvento[$evento]  ?? $this->coloresEvento['updated'];
        $icono    = $this->iconosEvento[$evento]    ?? 'ph-bold ph-clock';
        $etiqueta = $this->etiquetasEvento[$evento] ?? ucfirst($evento);

        // Decodificar propiedades JSON
        $properties = [];
        if ($log->properties && $log->properties !== 'null') {
            $decoded = json_decode($log->properties, true);
            if (is_array($decoded)) {
                $properties = $decoded;
            }
        }

        // Nombre del usuario que causó el evento
        $causer = trim(($log->causer_nombres ?? '') . ' ' . ($log->causer_ap_paterno ?? ''));
        $causer = $causer ?: ($log->causer_id ? 'Usuario #' . $log->causer_id : 'Sistema');

        // Fecha relativa en español con Zona Horaria Bolivia
        $fechaRelativa = '-';
        $fechaExacta   = '-';
        if ($log->created_at) {
            try {
                $fecha         = Carbon::parse($log->created_at)->timezone('America/La_Paz');
                $fechaRelativa = $fecha->diffForHumans();
                $fechaExacta   = $fecha->format('d/m/Y H:i');
            } catch (\Exception $e) {
                // silencio
            }
        }

        return [
            'id'             => $log->id,
            'evento'         => $evento,
            'etiqueta'       => $etiqueta,
            'descripcion'    => $log->description ?? '',
            'modulo'         => $this->traducirLogName($log->log_name ?? ''),
            'causer'         => $causer,
            'fecha_relativa' => $fechaRelativa,
            'fecha_exacta'   => $fechaExacta,
            'icono'          => $icono,
            'color_bg'       => $colores['bg'],
            'color_text'     => $colores['text'],
            'color_badge'    => $colores['badge'],
            'color_dot'      => $colores['dot'] ?? 'bg-slate-500',
            'properties'     => $properties,
        ];
    }

    /**
     * Traduce el log_name técnico a nombre institucional legible.
     */
    protected function traducirLogName(string $logName): string
    {
        return match(strtolower($logName)) {
            'adulto mayor', 'adulto_mayor' => 'Adulto Mayor',
            'observacion', 'obs_adulto'    => 'Observaciones',
            'atencion', 'atenciones_adulto'=> 'Atenciones',
            'actividad', 'actividades'     => 'Actividades',
            'documento'                    => 'Documentos',
            'familiar'                     => 'Familiares',
            'default'                      => 'General',
            default                        => $logName ?: 'General',
        };
    }
}
