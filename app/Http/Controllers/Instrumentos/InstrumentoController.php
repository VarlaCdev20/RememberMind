<?php

namespace App\Http\Controllers\Instrumentos;

use App\Http\Controllers\Controller;
use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstrumentoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('instrumentos.ver'), 403);
        return response()->json(Instrumento::query()->with('preguntas.opciones')->where('estado', 'ACTIVO')->get());
    }

    public function aplicar(Request $request, Instrumento $instrumento, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        abort_unless($request->user()->can('aplicaciones_instrumento.crear'), 403);
        $personal = $request->user()->personal()->where('estado', 'ACTIVO')->firstOrFail();
        $datos = $request->validate([
            'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'puntaje_total' => ['nullable', 'numeric'],
            'puntaje_maximo' => ['nullable', 'numeric'], 'clasificacion' => ['nullable', 'string', 'max:80'],
            'interpretacion' => ['nullable', 'string'], 'observacion' => ['nullable', 'string'],
            'respuestas' => ['required', 'array', 'min:1'], 'respuestas.*.cod_pregunta' => ['required', 'exists:preguntas_instrumento,cod_pregunta'],
            'respuestas.*.cod_opcion' => ['nullable', 'exists:opciones_pregunta,cod_opcion'], 'respuestas.*.valor_numero' => ['nullable', 'numeric'],
            'respuestas.*.valor_texto' => ['nullable', 'string'], 'respuestas.*.valor_logico' => ['nullable', 'boolean'],
            'respuestas.*.puntaje' => ['nullable', 'numeric'], 'respuestas.*.observacion' => ['nullable', 'string'],
        ]);
        $respuestas = $datos['respuestas']; unset($datos['respuestas']);
        if (isset($datos['cod_atencion'])) {
            abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }

        $aplicacion = DB::transaction(function () use ($instrumento, $residente, $personal, $datos, $respuestas): AplicacionInstrumento {
            $aplicacion = AplicacionInstrumento::query()->create(['cod_aplicacion' => $this->codigo('APL'), 'cod_instrumento' => $instrumento->cod_instrumento, 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'COMPLETA']);
            foreach ($respuestas as $respuesta) {
                abort_unless(PreguntaInstrumento::query()->whereKey($respuesta['cod_pregunta'])->where('cod_instrumento', $instrumento->cod_instrumento)->exists(), 422, 'La pregunta no pertenece al instrumento.');
                if (! empty($respuesta['cod_opcion'])) {
                    abort_unless(OpcionPregunta::query()->whereKey($respuesta['cod_opcion'])->where('cod_pregunta', $respuesta['cod_pregunta'])->exists(), 422, 'La opción no pertenece a la pregunta.');
                }
                RespuestaInstrumento::query()->create(['cod_respuesta' => $this->codigo('RSP'), 'cod_aplicacion' => $aplicacion->cod_aplicacion, ...$respuesta]);
            }
            return $aplicacion;
        });
        return response()->json($aplicacion->load('respuestas'), 201);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
