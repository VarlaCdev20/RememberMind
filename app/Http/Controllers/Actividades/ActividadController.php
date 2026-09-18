<?php

namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActividadController extends Controller
{
    public function index(): JsonResponse { return response()->json(Actividad::query()->with('participantes.residente')->latest('fecha_hora')->paginate(25)); }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('actividades.gestionar'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate(['cod_area' => ['required', 'exists:areas,cod_area'], 'tipo' => ['required', 'string', 'max:60'], 'nombre' => ['required', 'string', 'max:160'], 'descripcion' => ['nullable', 'string'], 'fecha_hora' => ['required', 'date'], 'duracion_minutos' => ['nullable', 'integer', 'min:1'], 'lugar' => ['nullable', 'string', 'max:120'], 'cupo' => ['nullable', 'integer', 'min:1'], 'observacion' => ['nullable', 'string']]);
        return response()->json(Actividad::query()->create(['cod_actividad' => $this->codigo('ACT'), 'cod_personal' => $personal->cod_personal, ...$datos, 'estado' => 'PROGRAMADA']), 201);
    }

    public function participante(Request $request, Actividad $actividad): JsonResponse
    {
        abort_unless($request->user()->can('actividades.gestionar'), 403);
        $datos = $request->validate(['cod_residente' => ['required', 'exists:residentes,cod_residente'], 'asistencia' => ['nullable', 'string', 'max:30'], 'nivel_participacion' => ['nullable', 'string', 'max:40'], 'desempeno' => ['nullable', 'string', 'max:40'], 'observacion' => ['nullable', 'string']]);
        abort_if(ParticipanteActividad::query()->where('cod_actividad', $actividad->cod_actividad)->where('cod_residente', $datos['cod_residente'])->exists(), 409, 'El residente ya participa en la actividad.');
        return response()->json(ParticipanteActividad::query()->create(['cod_participante' => $this->codigo('PAR'), 'cod_actividad' => $actividad->cod_actividad, ...$datos]), 201);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
