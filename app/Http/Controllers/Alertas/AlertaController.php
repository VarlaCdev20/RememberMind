<?php

namespace App\Http\Controllers\Alertas;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Http\Controllers\Controller;
use App\Models\Alerta;
use App\Models\EventoAlerta;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AlertaController extends Controller
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('alertas.ver'), 403);
        return response()->json(Alerta::query()->with('eventos')->latest('fecha_hora')->paginate(25));
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
        $alerta = DB::transaction(function () use ($request, $residente, $datos): Alerta {
            $alerta = Alerta::query()->create([
                'cod_alerta' => $this->codigo('ALE'),
                'cod_residente' => $residente->cod_residente,
                'cod_personal_responsable' => $request->user()->personal->cod_personal,
                ...$datos,
                'fecha_hora' => now(),
                'generacion' => 'MANUAL',
                'estado' => 'ABIERTA',
            ]);
            EventoAlerta::query()->create(['cod_evento_alerta' => $this->codigo('EAL'), 'cod_alerta' => $alerta->cod_alerta, 'cod_usuario' => $request->user()->cod_usuario, 'tipo_evento' => 'CREADA', 'estado_nuevo' => 'ABIERTA', 'fecha_hora' => now()]);
            return $alerta;
        });
        return response()->json($alerta->load('eventos'), 201);
    }

    public function cambiarEstado(Request $request, Alerta $alerta): JsonResponse
    {
        $this->turnos->autorizarMutacionEnfermeria($alerta->cod_residente, 'alertas.gestionar', $request->user());
        $datos = $request->validate([
            'estado' => ['required', 'in:RECONOCIDA,ASIGNADA,EN_ATENCION,ATENDIDA,CERRADA,ANULADA'],
            'descripcion' => ['required', 'string', 'min:5', 'max:10000'],
        ]);

        $alerta = DB::transaction(function () use ($request, $alerta, $datos): Alerta {
            $bloqueada = Alerta::query()->lockForUpdate()->findOrFail($alerta->getKey());
            $anterior = strtoupper((string) $bloqueada->estado);
            $transiciones = [
                'ABIERTA' => ['RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'],
                'RECONOCIDA' => ['ASIGNADA', 'EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'],
                'ASIGNADA' => ['EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'],
                'EN_ATENCION' => ['ATENDIDA', 'CERRADA', 'ANULADA'],
                'ATENDIDA' => ['CERRADA'],
                'CERRADA' => [],
                'ANULADA' => [],
            ];
            abort_unless(in_array($datos['estado'], $transiciones[$anterior] ?? [], true), 409,
                'La transición de estado solicitada no es válida para esta alerta.');

            $bloqueada->update(['estado' => $datos['estado']]);
            EventoAlerta::query()->create(['cod_evento_alerta' => $this->codigo('EAL'), 'cod_alerta' => $bloqueada->cod_alerta, 'cod_usuario' => $request->user()->cod_usuario, 'tipo_evento' => 'CAMBIO_ESTADO', 'estado_anterior' => $anterior, 'estado_nuevo' => $datos['estado'], 'fecha_hora' => now(), 'descripcion' => trim($datos['descripcion'])]);

            return $bloqueada;
        });

        return response()->json($alerta->fresh('eventos'));
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
