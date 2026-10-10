<?php

namespace App\Http\Controllers\Alertas;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertaController extends Controller
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('alertas.ver') && ! $usuario->hasRole('FAMILIAR'), 403);
        $query = Alerta::query();
        if (! $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            $query = $this->turnos->acotarAlertasQuery($query, $usuario);
        }
        return response()->json($query->with('eventos')->latest('fecha_hora')->paginate(25));
    }

    public function store(Request $request, Residente $residente): JsonResponse
    {
        $usuario = $request->user();
        $this->turnos->autorizarMutacionEnfermeria($residente, 'alertas.gestionar', $usuario);
        $datos = $request->validate([
            'tipo' => ['required', 'string', 'min:3', 'max:60'],
            'prioridad' => ['required', 'in:BAJO,MEDIO,ALTO,CRITICO'],
            'modulo' => ['nullable', 'string', 'max:60'],
            'cod_registro' => ['nullable', 'string', 'max:20'],
            'titulo' => ['required', 'string', 'min:3', 'max:180'],
            'descripcion' => ['required', 'string', 'min:10', 'max:10000'],
            'fecha_hora_limite' => ['nullable', 'date', 'after:now'],
        ]);
        $alerta = app(AlertasService::class)->crearDesdeHttp($residente->cod_residente, $datos, $usuario);
        return response()->json($alerta->load('eventos'), 201);
    }

    public function cambiarEstado(Request $request, Alerta $alerta): JsonResponse
    {
        $datos = $request->validate([
            'estado' => ['required', 'in:RECONOCIDA,ASIGNADA,EN_ATENCION,ATENDIDA,CERRADA,ANULADA'],
            'descripcion' => ['required', 'string', 'min:5', 'max:10000'],
        ]);
        $alerta = app(AlertasService::class)->cambiarEstado(
            $alerta, $datos['estado'], $datos['descripcion'], $request->user());
        return response()->json($alerta->fresh('eventos'));
    }
}
