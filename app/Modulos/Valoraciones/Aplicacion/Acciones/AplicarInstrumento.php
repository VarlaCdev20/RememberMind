<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\DTOs\CatalogoPreguntasInstrumento;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AplicarInstrumento
{
    public const ROLES_APLICADORES = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function aplicar(User $usuario, array $entrada, Instrumento $instrumento, Residente $residente, ?string $codigoReservado = null, ?string $huellaCatalogo = null): AplicacionInstrumento
    {
        return DB::transaction(function () use ($usuario, $entrada, $instrumento, $residente, $codigoReservado, $huellaCatalogo): AplicacionInstrumento {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $instrumento = Instrumento::query()->lockForUpdate()->findOrFail($instrumento->getKey());
            $personal = $this->autorizacion->autorizarMutacion($usuario, $residente, 'aplicaciones_instrumento.crear', self::ROLES_APLICADORES);
            if ($codigoReservado !== null && AplicacionInstrumento::query()->whereKey($codigoReservado)->exists()) {
                throw ValidationException::withMessages(['solicitud' => 'Esta solicitud ya se registró. Consulta el historial antes de repetir la captura.']);
            }
            abort_unless($instrumento->estado === 'ACTIVO', 409, 'El instrumento no se encuentra activo.');
            $datos = Validator::make($entrada, ['fecha_hora' => ['sometimes', 'required', 'date_format:Y-m-d H:i:s,Y-m-d\TH:i:sP,Y-m-d\TH:i:s\Z', 'before_or_equal:now'],
                'cod_atencion' => ['nullable', 'exists:atenciones,cod_atencion'], 'clasificacion' => ['nullable', 'string', 'max:80'], 'interpretacion' => ['nullable', 'string'], 'observacion' => ['nullable', 'string'], 'respuestas' => ['required', 'array', 'min:1'],
                'respuestas.*' => ['array:cod_pregunta,cod_opcion,valor_numero,valor_texto,valor_logico,puntaje,observacion'],
                'respuestas.*.cod_pregunta' => ['required', 'distinct', 'exists:preguntas_instrumento,cod_pregunta'], 'respuestas.*.cod_opcion' => ['nullable', 'exists:opciones_pregunta,cod_opcion'], 'respuestas.*.valor_numero' => ['nullable', 'numeric', 'between:-99999999.9999,99999999.9999', 'decimal:0,4'], 'respuestas.*.valor_texto' => ['nullable', 'string'], 'respuestas.*.valor_logico' => ['nullable', 'boolean'], 'respuestas.*.puntaje' => ['nullable', 'numeric', 'min:0', 'max:999999.99', 'decimal:0,2'], 'respuestas.*.observacion' => ['nullable', 'string']])->validate();
            $respuestas = $datos['respuestas'];
            abort_unless(array_is_list($respuestas), 422, 'Las respuestas requieren una lista ordenada.');
            unset($datos['respuestas']);
            if (isset($datos['cod_atencion'])) {
                abort_unless(Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->cod_residente)->lockForUpdate()->first(), 422, 'La atención no pertenece al residente.');
            }
            $preguntas = PreguntaInstrumento::query()->where('cod_instrumento', $instrumento->cod_instrumento)->where('estado', 'ACTIVA')->lockForUpdate()
                ->with(['opciones' => fn ($query) => $query->where('estado', 'ACTIVO')->lockForUpdate()])->get()->keyBy('cod_pregunta');
            if ($huellaCatalogo !== null && ! hash_equals($huellaCatalogo, CatalogoPreguntasInstrumento::proyectar($instrumento, $preguntas)['huella'])) {
                throw ValidationException::withMessages(['catalogo' => 'El catálogo cambió durante la captura. Revisa el instrumento actualizado antes de registrarlo.']);
            }
            foreach ($respuestas as &$respuesta) {
                $pregunta = $preguntas->get($respuesta['cod_pregunta']);
                abort_unless($pregunta, 422, 'La pregunta no pertenece al instrumento activo.');
                $tieneValor = filled($respuesta['cod_opcion'] ?? null) || array_key_exists('valor_numero', $respuesta) && $respuesta['valor_numero'] !== null || filled($respuesta['valor_texto'] ?? null) || array_key_exists('valor_logico', $respuesta) && $respuesta['valor_logico'] !== null;
                abort_unless($tieneValor, 422, 'Cada respuesta requiere un valor explícito.');
                $presentes = array_values(array_filter(['cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico'], fn ($campo) => array_key_exists($campo, $respuesta) && $respuesta[$campo] !== null && $respuesta[$campo] !== ''));
                abort_unless(count($presentes) === 1, 422, 'Cada respuesta debe tener un único tipo de valor original.');
                $esperado = match ($pregunta->tipo_respuesta) {
                    'OPCION', 'OPCION_UNICA' => 'cod_opcion', 'NUMERO', 'NUMERICO', 'NUMERICA' => 'valor_numero',
                    'LOGICO', 'BOOLEANO' => 'valor_logico', 'TEXTO' => 'valor_texto', default => null,
                };
                abort_unless($esperado !== null && $presentes[0] === $esperado, 422, 'El tipo de valor no corresponde al contrato de la pregunta.');
                if (! empty($respuesta['cod_opcion'])) {
                    $opcion = $pregunta->opciones->firstWhere('cod_opcion', $respuesta['cod_opcion']);
                    abort_unless($opcion, 422, 'La opción no pertenece a la pregunta activa.');
                    $respuesta['puntaje'] = $opcion->puntaje;
                } elseif (isset($respuesta['puntaje'])) {
                    abort_unless($pregunta->puntaje_maximo !== null && $respuesta['puntaje'] <= $pregunta->puntaje_maximo, 422, 'La puntuación requiere un rango definido para la pregunta.');
                }
            }
            unset($respuesta);
            $datos['puntaje_total'] = count($respuestas) === $preguntas->count()
                ? $this->sumarPuntajes(array_map(fn ($r) => $r['puntaje'] ?? null, $respuestas)) : null;
            $datos['puntaje_maximo'] = $this->sumarPuntajes($preguntas->pluck('puntaje_maximo')->all());
            $aplicacion = AplicacionInstrumento::query()->create(['cod_aplicacion' => $codigoReservado ?? app(CodigosEntidad::class)->siguiente('aplicaciones_instrumento'), 'cod_instrumento' => $instrumento->cod_instrumento, 'cod_residente' => $residente->cod_residente, 'cod_personal' => $personal->cod_personal,
                'cod_atencion' => $datos['cod_atencion'] ?? null, 'puntaje_total' => $datos['puntaje_total'], 'puntaje_maximo' => $datos['puntaje_maximo'],
                'clasificacion' => $datos['clasificacion'] ?? null, 'interpretacion' => $datos['interpretacion'] ?? null, 'observacion' => $datos['observacion'] ?? null,
                'fecha_hora' => isset($datos['fecha_hora']) ? CarbonImmutable::parse($datos['fecha_hora'])->setTimezone(config('app.timezone')) : now(), 'estado' => 'COMPLETA']);
            foreach ($respuestas as $respuesta) {
                RespuestaInstrumento::query()->create(['cod_respuesta' => app(CodigosEntidad::class)->siguiente('respuestas_instrumento'), 'cod_aplicacion' => $aplicacion->cod_aplicacion,
                    ...array_intersect_key($respuesta, array_flip(['cod_pregunta', 'cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico', 'puntaje', 'observacion']))]);
            }

            return $aplicacion->load('respuestas');
        }, 3);
    }

    /** Suma centésimas solo cuando todas las puntuaciones existen; NULL nunca es cero. */
    private function sumarPuntajes(array $puntajes): ?string
    {
        if ($puntajes === [] || in_array(null, $puntajes, true)) {
            return null;
        }
        $centesimas = array_sum(array_map(fn ($p) => (int) round((float) $p * 100), $puntajes));
        if ($centesimas < 0 || $centesimas > 99999999) {
            throw ValidationException::withMessages(['respuestas' => 'La suma de puntuaciones está fuera del rango de almacenamiento.']);
        }

        return number_format($centesimas / 100, 2, '.', '');
    }
}
