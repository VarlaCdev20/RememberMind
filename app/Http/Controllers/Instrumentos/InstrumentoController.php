<?php

namespace App\Http\Controllers\Instrumentos;

use App\Backend\Modulos\Clinica\Servicios\AutorizacionClinicaService;
use App\Http\Controllers\Controller;
use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstrumentoController extends Controller
{
    private const ROLES_APLICADORES = [
        'MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A',
        'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO',
    ];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('instrumentos.ver'), 403);
        return response()->json(Instrumento::query()->with('preguntas.opciones')->where('estado', 'ACTIVO')->get());
    }

    public function aplicar(Request $request, Instrumento $instrumento, Residente $residente): JsonResponse
    {
        abort_unless($instrumento->estado === 'ACTIVO', 409, 'El instrumento no se encuentra activo.');
        $personal = $this->autorizacion->autorizarMutacion(
            $request->user(), $residente, 'aplicaciones_instrumento.crear', self::ROLES_APLICADORES,
        );
        $datos = $request->validate([
            'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'],
            'clasificacion' => ['nullable', 'string', 'max:80'],
            'interpretacion' => ['nullable', 'string'], 'observacion' => ['nullable', 'string'],
            'respuestas' => ['required', 'array', 'min:1'], 'respuestas.*.cod_pregunta' => ['required', 'distinct', 'exists:preguntas_instrumento,cod_pregunta'],
            'respuestas.*.cod_opcion' => ['nullable', 'exists:opciones_pregunta,cod_opcion'], 'respuestas.*.valor_numero' => ['nullable', 'numeric'],
            'respuestas.*.valor_texto' => ['nullable', 'string'], 'respuestas.*.valor_logico' => ['nullable', 'boolean'],
            'respuestas.*.puntaje' => ['nullable', 'numeric'], 'respuestas.*.observacion' => ['nullable', 'string'],
        ]);
        $respuestas = $datos['respuestas']; unset($datos['respuestas']);
        if (isset($datos['cod_atencion'])) {
            abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->exists(), 422, 'La atención no pertenece al residente.');
        }

        $preguntas = PreguntaInstrumento::query()
            ->where('cod_instrumento', $instrumento->cod_instrumento)
            ->where('estado', 'ACTIVA')
            ->with(['opciones' => fn ($query) => $query->where('estado', 'ACTIVO')])
            ->get()
            ->keyBy('cod_pregunta');
        foreach ($respuestas as &$respuesta) {
            $pregunta = $preguntas->get($respuesta['cod_pregunta']);
            abort_unless($pregunta, 422, 'La pregunta no pertenece al instrumento activo.');
            $tieneValor = filled($respuesta['cod_opcion'] ?? null)
                || (array_key_exists('valor_numero', $respuesta) && $respuesta['valor_numero'] !== null)
                || filled($respuesta['valor_texto'] ?? null)
                || (array_key_exists('valor_logico', $respuesta) && $respuesta['valor_logico'] !== null);
            abort_unless($tieneValor, 422, 'Cada respuesta requiere un valor explícito.');
            if (! empty($respuesta['cod_opcion'])) {
                $opcion = $pregunta->opciones->firstWhere('cod_opcion', $respuesta['cod_opcion']);
                abort_unless($opcion, 422, 'La opción no pertenece a la pregunta activa.');
                $respuesta['puntaje'] = $opcion->puntaje;
            } elseif (isset($respuesta['puntaje'], $pregunta->puntaje_maximo)) {
                abort_unless($respuesta['puntaje'] >= 0 && $respuesta['puntaje'] <= $pregunta->puntaje_maximo, 422,
                    'El puntaje está fuera del rango permitido para la pregunta.');
            }
        }
        unset($respuesta);
        $datos['puntaje_total'] = collect($respuestas)->sum(fn (array $fila) => (float) ($fila['puntaje'] ?? 0));
        $datos['puntaje_maximo'] = $preguntas->sum(fn (PreguntaInstrumento $pregunta) => (float) ($pregunta->puntaje_maximo ?? 0));

        $aplicacion = DB::transaction(function () use ($instrumento, $residente, $personal, $datos, $respuestas): AplicacionInstrumento {
            $aplicacion = AplicacionInstrumento::query()->create(['cod_aplicacion' => $this->codigo('APL'), 'cod_instrumento' => $instrumento->cod_instrumento, 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal, ...$datos, 'fecha_hora' => now(), 'estado' => 'COMPLETA']);
            foreach ($respuestas as $respuesta) {
                RespuestaInstrumento::query()->create(['cod_respuesta' => $this->codigo('RSP'), 'cod_aplicacion' => $aplicacion->cod_aplicacion, ...$respuesta]);
            }
            return $aplicacion;
        });
        return response()->json($aplicacion->load('respuestas'), 201);
    }

    private function codigo(string $prefijo): string { return $prefijo.'_'.Str::upper(Str::random(12)); }
}
