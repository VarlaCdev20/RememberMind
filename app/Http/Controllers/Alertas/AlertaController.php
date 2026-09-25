<?php

namespace App\Http\Controllers\Alertas;

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
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('alertas.ver'), 403);
        return response()->json(Alerta::query()->with('eventos')->latest('fecha_hora')->paginate(25));
    }

    public function store(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('alertas.gestionar'), 403);
        $datos = $request->validate(['tipo' => ['required', 'string', 'max:60'], 'prioridad' => ['required', 'string', 'max:20'], 'modulo' => ['nullable', 'string', 'max:60'], 'cod_registro' => ['nullable', 'string', 'max:20'], 'titulo' => ['required', 'string', 'max:180'], 'descripcion' => ['required', 'string'], 'fecha_hora_limite' => ['nullable', 'date']]);
        $alerta = DB::transaction(function () use ($request, $residente, $datos): Alerta {
            $alerta = Alerta::query()->create(['cod_alerta' => $this->codigo('ALE'), 'cod_residente' => $residente->cod_residente, ...$datos, 'fecha_hora' => now(), 'generacion' => 'MANUAL', 'estado' => 'ABIERTA']);
            EventoAlerta::query()->create(['cod_evento_alerta' => $this->codigo('EAL'), 'cod_alerta' => $alerta->cod_alerta, 'cod_usuario' => $request->user()->cod_usuario, 'tipo_evento' => 'CREADA', 'estado_nuevo' => 'ABIERTA', 'fecha_hora' => now()]);
            return $alerta;
        });
        return response()->json($alerta->load('eventos'), 201);
    }

    public function cambiarEstado(Request $request, Alerta $alerta): JsonResponse
    {
        abort_unless($request->user()->can('alertas.gestionar'), 403);
        $datos = $request->validate(['estado' => ['required', 'in:RECONOCIDA,ASIGNADA,ATENDIDA,CERRADA,ANULADA'], 'descripcion' => ['nullable', 'string']]);
        $anterior = $alerta->estado;
        DB::transaction(function () use ($request, $alerta, $datos, $anterior): void {
            $alerta->update(['estado' => $datos['estado']]);
            EventoAlerta::query()->create(['cod_evento_alerta' => $this->codigo('EAL'), 'cod_alerta' => $alerta->cod_alerta, 'cod_usuario' => $request->user()->cod_usuario, 'tipo_evento' => 'CAMBIO_ESTADO', 'estado_anterior' => $anterior, 'estado_nuevo' => $datos['estado'], 'fecha_hora' => now(), 'descripcion' => $datos['descripcion'] ?? null]);
        });
        return response()->json($alerta->fresh('eventos'));
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
