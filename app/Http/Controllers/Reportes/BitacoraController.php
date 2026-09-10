<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

/**
 * BitacoraController — Vista básica de bitácora global del sistema.
 *
 * Consulta directamente la tabla activity_log de Spatie Activitylog.
 * En fases posteriores se migrará a Livewire con filtros avanzados,
 * paginación dinámica y exportación de reportes.
 */
class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        if (!Schema::hasTable('activity_log')) {
            return view('pages.bitacora.index', ['registros' => collect(), 'usuarios' => collect()]);
        }

        Carbon::setLocale('es');
        $timezone = config('app.timezone', 'America/La_Paz');

        $query = DB::table('activity_log')
            ->leftJoin('users', DB::raw('activity_log.causer_id::text'), '=', DB::raw('users.cod_usu::text'))
            ->select([
                'activity_log.id',
                'activity_log.log_name',
                'activity_log.description',
                'activity_log.subject_type',
                'activity_log.subject_id',
                'activity_log.causer_id',
                'activity_log.event',
                'activity_log.properties',
                'activity_log.created_at',
                'users.nombres as causer_nombres',
                'users.ap_paterno as causer_ap_paterno',
                'users.correo as causer_correo',
            ])
            ->orderByDesc('activity_log.created_at');

        // Filtros
        if ($request->filled('usuario')) {
            $query->where('activity_log.causer_id', $request->usuario);
        }

        if ($request->filled('modulo')) {
            $query->where('activity_log.log_name', $request->modulo);
        }

        if ($request->filled('evento')) {
            $query->where('activity_log.event', $request->evento);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('activity_log.created_at', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('activity_log.created_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('activity_log.description', 'like', "%$buscar%")
                  ->orWhere('activity_log.properties', 'like', "%$buscar%")
                  ->orWhere('users.nombres', 'like', "%$buscar%")
                  ->orWhere('users.ap_paterno', 'like', "%$buscar%");
            });
        }

        $registros = $query->paginate(30)->withQueryString();

        // Enriquecer datos
        $registros->getCollection()->transform(function ($log) use ($timezone) {
            $log->causer_nombre = trim(($log->causer_nombres ?? '') . ' ' . ($log->causer_ap_paterno ?? ''));
            $log->causer_nombre = $log->causer_nombre ?: ($log->causer_id ? 'Usuario #' . $log->causer_id : 'Sistema');
            
            // Obtener Rol
            $user = \App\Models\User::find($log->causer_id);
            $log->causer_rol = $user ? ($user->getRoleNames()->first() ?? 'Sin Rol') : 'Sistema';

            $log->modulo_info = $this->obtenerInfoModulo($log->log_name ?? $log->subject_type ?? '');
            $log->evento_info = $this->obtenerInfoEvento($log->event ?? '');
            $log->registro_afectado = $this->identificarRegistroAfectado($log);

            if ($log->created_at) {
                $fecha = Carbon::parse($log->created_at)->timezone($timezone);
                $log->fecha_relativa = $fecha->diffForHumans();
                $log->fecha_formateada = $fecha->format('d/m/Y H:i');
                $log->hora = $fecha->format('H:i');
                $log->fecha_dia = $fecha->format('d M, Y');
            } else {
                $log->fecha_relativa = '-';
                $log->fecha_formateada = '-';
                $log->hora = '-';
                $log->fecha_dia = '-';
            }

            return $log;
        });

        // Datos para filtros
        $usuarios = DB::table('users')->select('cod_usu', 'nombres', 'ap_paterno')->orderBy('nombres')->get();
        
        $modulosRaw = DB::table('activity_log')->distinct()->pluck('log_name')->filter()->sort();
        $modulos = $modulosRaw->mapWithKeys(function($m) {
            return [$m => $this->obtenerInfoModulo($m)['nombre']];
        });

        $eventosRaw = DB::table('activity_log')->distinct()->pluck('event')->filter()->sort();
        $eventos = $eventosRaw->mapWithKeys(function($e) {
            return [$e => $this->obtenerInfoEvento($e)['nombre']];
        });

        return view('pages.bitacora.index', compact('registros', 'usuarios', 'modulos', 'eventos'));
    }

    private function obtenerInfoModulo(string $name): array
    {
        $name = strtolower($name);

        // Mapeo de subject_type a nombres amigables si log_name es genérico
        if (str_contains($name, 'app\\models\\')) {
            $name = match($name) {
                'app\\models\\user' => 'usuarios',
                'app\\models\\adultomayor' => 'adulto mayor',
                'app\\models\\familiar' => 'familiares',
                'app\\models\\familiaradulto' => 'familiares',
                'app\\models\\obsadulto' => 'observaciones',
                'app\\models\\atencionadulto' => 'atenciones',
                'app\\models\\actividadadulto' => 'actividades',
                'app\\models\\evaluacioncognitiva' => 'cognitivo',
                'app\\models\\documentoadultomayor' => 'documentos',
                'app\\models\\fichamedicaadulto' => 'ficha médica',
                'app\\models\\medicacionadulto' => 'medicación',
                'app\\models\\administracionmedicacion' => 'medicación',
                'app\\models\\signosvitalesadulto' => 'signos vitales',
                'app\\models\\valoracionfuncionaladulto' => 'valoración funcional',
                'app\\models\\historialestadoadulto' => 'historial',
                default => class_basename($name),
            };
        }

        return match ($name) {
            'seguridad', 'panel principal' => ['nombre' => 'Seguridad', 'color' => 'bg-slate-100 text-slate-700 border-slate-200', 'icono' => 'ph-shield-check', 'dot' => 'bg-slate-500'],
            'usuarios'                     => ['nombre' => 'Usuarios', 'color' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'icono' => 'ph-users', 'dot' => 'bg-indigo-500'],
            'adulto mayor', 'adulto_mayor' => ['nombre' => 'Adulto Mayor', 'color' => 'bg-teal-100 text-teal-700 border-teal-200', 'icono' => 'ph-user-circle', 'dot' => 'bg-teal-500'],
            'familiares', 'familiar'       => ['nombre' => 'Familiares', 'color' => 'bg-rose-100 text-rose-700 border-rose-200', 'icono' => 'ph-users-three', 'dot' => 'bg-rose-500'],
            'medicación', 'medicacion'     => ['nombre' => 'Medicación', 'color' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'icono' => 'ph-pill', 'dot' => 'bg-emerald-500'],
            'signos vitales'               => ['nombre' => 'Signos vitales', 'color' => 'bg-red-100 text-red-700 border-red-200', 'icono' => 'ph-heartbeat', 'dot' => 'bg-red-500'],
            'atenciones', 'atencion'       => ['nombre' => 'Atenciones', 'color' => 'bg-sky-100 text-sky-700 border-sky-200', 'icono' => 'ph-stethoscope', 'dot' => 'bg-sky-500'],
            'observaciones', 'observacion' => ['nombre' => 'Observaciones', 'color' => 'bg-amber-100 text-amber-700 border-amber-200', 'icono' => 'ph-clipboard-text', 'dot' => 'bg-amber-500'],
            'cognitivo', 'evaluaciones'    => ['nombre' => 'Cognitivo', 'color' => 'bg-violet-100 text-violet-700 border-violet-200', 'icono' => 'ph-brain', 'dot' => 'bg-violet-500'],
            'documentos'                   => ['nombre' => 'Documentos', 'color' => 'bg-orange-100 text-orange-700 border-orange-200', 'icono' => 'ph-file-text', 'dot' => 'bg-orange-500'],
            'permisos'                     => ['nombre' => 'Permisos', 'color' => 'bg-purple-100 text-purple-700 border-purple-200', 'icono' => 'ph-key', 'dot' => 'bg-purple-500'],
            'reportes'                     => ['nombre' => 'Reportes', 'color' => 'bg-cyan-100 text-cyan-700 border-cyan-200', 'icono' => 'ph-file-pdf', 'dot' => 'bg-cyan-500'],
            'actividades', 'actividad'     => ['nombre' => 'Actividades', 'color' => 'bg-lime-100 text-lime-700 border-lime-200', 'icono' => 'ph-calendar-check', 'dot' => 'bg-lime-500'],
            'voluntarios'                  => ['nombre' => 'Voluntarios', 'color' => 'bg-pink-100 text-pink-700 border-pink-200', 'icono' => 'ph-hand-heart', 'dot' => 'bg-pink-500'],
            'general', 'default'           => ['nombre' => 'General', 'color' => 'bg-gray-100 text-gray-700 border-gray-200', 'icono' => 'ph-info', 'dot' => 'bg-gray-500'],
            default                        => ['nombre' => ucfirst($name ?: 'General'), 'color' => 'bg-gray-100 text-gray-700 border-gray-200', 'icono' => 'ph-info', 'dot' => 'bg-gray-500'],
        };
    }

    private function obtenerInfoEvento(?string $event): array
    {
        return match (strtolower($event)) {
            'created', 'registro'      => ['nombre' => 'Registró', 'color' => 'bg-emerald-500', 'icono' => 'ph-plus-circle'],
            'updated', 'edicion'       => ['nombre' => 'Actualizó', 'color' => 'bg-blue-500', 'icono' => 'ph-pencil-simple'],
            'deleted'                  => ['nombre' => 'Eliminó', 'color' => 'bg-rose-500', 'icono' => 'ph-trash'],
            'restored'                 => ['nombre' => 'Restauró', 'color' => 'bg-amber-500', 'icono' => 'ph-arrow-counter-clockwise'],
            'archived', 'archivó'      => ['nombre' => 'Archivó', 'color' => 'bg-slate-500', 'icono' => 'ph-archive'],
            'inicio_sesion', 'acceso', 'login' => ['nombre' => 'Inició sesión', 'color' => 'bg-slate-600', 'icono' => 'ph-sign-in'],
            'logout', 'cerró sesión'   => ['nombre' => 'Cerró sesión', 'color' => 'bg-slate-400', 'icono' => 'ph-sign-out'],
            'estado_cambiado'          => ['nombre' => 'Cambió estado', 'color' => 'bg-emerald-600', 'icono' => 'ph-toggle-right'],
            'reporte_generado'         => ['nombre' => 'Generó reporte', 'color' => 'bg-teal-600', 'icono' => 'ph-file-pdf'],
            'medicacion_omitida'       => ['nombre' => 'Registró omisión de medicación', 'color' => 'bg-rose-600', 'icono' => 'ph-warning-octagon'],
            default                    => ['nombre' => ucfirst($event ?: 'Acción'), 'color' => 'bg-gray-500', 'icono' => 'ph-dot'],
        };
    }

    private function identificarRegistroAfectado($log): string
    {
        if (!$log->subject_type) return 'Registro no disponible';

        $properties = json_decode($log->properties, true);
        
        // Intentar obtener nombre desde properties si existe
        if (isset($properties['nombre'])) return $properties['nombre'];
        if (isset($properties['nombres'])) return $properties['nombres'];
        if (isset($properties['ci'])) return "CI: " . $properties['ci'];

        // Resolver por modelo
        try {
            return match ($log->subject_type) {
                'App\\Models\\AdultoMayor' => DB::table('adulto_mayor')->where('cod_am', $log->subject_id)->value(DB::raw("nombres || ' ' || ap_paterno")) ?? "AM: $log->subject_id",
                'App\\Models\\User'        => DB::table('users')->where('cod_usu', $log->subject_id)->value(DB::raw("nombres || ' ' || ap_paterno")) ?? "Usuario: $log->subject_id",
                'App\\Models\\Familiar'    => DB::table('familiares')->where('cod_fam', $log->subject_id)->value(DB::raw("nombres || ' ' || ap_paterno")) ?? "Familiar: $log->subject_id",
                'App\\Models\\DocumentoAdultoMayor' => "Doc: " . (DB::table('documentos_adulto_mayor')->where('cod_doc_am', $log->subject_id)->value('nombre') ?? $log->subject_id),
                'App\\Models\\MedicacionAdulto' => "Medicamento: " . (DB::table('medicacion_adulto')->where('cod_med_adulto', $log->subject_id)->value('nombre_medicamento') ?? $log->subject_id),
                'Spatie\\Permission\\Models\\Role' => "Rol: " . (DB::table('roles')->where('id', $log->subject_id)->value('name') ?? $log->subject_id),
                default => class_basename($log->subject_type) . " #$log->subject_id",
            };
        } catch (\Exception $e) {
            return "Registro no disponible";
        }
    }
}
