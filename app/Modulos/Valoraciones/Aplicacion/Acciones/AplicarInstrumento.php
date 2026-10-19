<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ArchivosAplicacion;
use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\DocumentoClinico;
use App\Models\Instrumento;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\DTOs\CatalogoPreguntasInstrumento;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class AplicarInstrumento
{
    public const ROLES_APLICADORES = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function aplicar(User $usuario, array $entrada, Instrumento $instrumento, Residente $residente, ?string $codigoReservado = null, ?string $huellaCatalogo = null): AplicacionInstrumento
    {
        return $this->registrar($usuario, $entrada, $instrumento, $residente, $codigoReservado, $huellaCatalogo);
    }

    /** Conciliación sin escrituras; mismos validadores y autorización que el guardado. */
    public function conciliar(User $usuario, array $entrada, Instrumento $instrumento, Residente $residente): array
    {
        return $this->registrar($usuario, $entrada, $instrumento, $residente, null, null, true, false);
    }

    public function transcribir(User $usuario, array $entrada, Instrumento $instrumento, Residente $residente, ?string $huellaCatalogo = null): AplicacionInstrumento
    {
        return $this->registrar($usuario, $entrada, $instrumento, $residente, null, $huellaCatalogo, true);
    }

    private function registrar(User $usuario, array $entrada, Instrumento $instrumento, Residente $residente,
        ?string $codigoReservado, ?string $huellaCatalogo, bool $historica = false, bool $guardar = true): AplicacionInstrumento|array
    {
        return DB::transaction(function () use ($usuario, $entrada, $instrumento, $residente, $codigoReservado, $huellaCatalogo, $historica, $guardar): AplicacionInstrumento|array {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $instrumento = Instrumento::query()->lockForUpdate()->findOrFail($instrumento->getKey());
            $personal = $this->autorizacion->autorizarMutacion($usuario, $residente,
                $historica ? 'aplicaciones_instrumento.transcribir' : 'aplicaciones_instrumento.crear',
                $historica ? ['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'] : self::ROLES_APLICADORES);
            $origen = $historica ? $this->origen($usuario, $residente, $entrada) : [];
            $aplicador = $historica ? $origen['cod_personal'] : $personal->getKey();
            if ($codigoReservado !== null && AplicacionInstrumento::query()->whereKey($codigoReservado)->exists()) {
                throw ValidationException::withMessages(['solicitud' => 'Esta solicitud ya se registró. Consulta el historial antes de repetir la captura.']);
            }
            abort_unless($instrumento->estado === 'ACTIVO', 409, 'El instrumento no se encuentra activo.');
            $datos = Validator::make($entrada, ['fecha_hora' => ['required', 'date_format:Y-m-d H:i:s,Y-m-d\TH:i:sP,Y-m-d\TH:i:s\Z', 'before_or_equal:now'],
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
                    if ($historica && isset($respuesta['puntaje']) && ($opcion->puntaje === null
                        || number_format((float) $respuesta['puntaje'], 2, '.', '') !== number_format((float) $opcion->puntaje, 2, '.', ''))) {
                        throw ValidationException::withMessages(['respuestas' => 'La puntuación original no coincide con el catálogo. Concilie la versión antes de transcribir.']);
                    }
                    $respuesta['puntaje'] = $historica ? ($respuesta['puntaje'] ?? null) : $opcion->puntaje;
                } elseif (isset($respuesta['puntaje'])) {
                    abort_unless($pregunta->puntaje_maximo !== null && $respuesta['puntaje'] <= $pregunta->puntaje_maximo, 422, 'La puntuación requiere un rango definido para la pregunta.');
                }
            }
            unset($respuesta);
            $datos['puntaje_total'] = count($respuestas) === $preguntas->count()
                ? $this->sumarPuntajes(array_map(fn ($r) => $r['puntaje'] ?? null, $respuestas)) : null;
            $datos['puntaje_maximo'] = $this->sumarPuntajes($preguntas->pluck('puntaje_maximo')->all());
            $cabecera = ['cod_instrumento' => $instrumento->getKey(), 'cod_residente' => $residente->getKey(), 'cod_personal' => $aplicador,
                'cod_atencion' => $datos['cod_atencion'] ?? null, 'puntaje_total' => $datos['puntaje_total'], 'puntaje_maximo' => $datos['puntaje_maximo'],
                'clasificacion' => $datos['clasificacion'] ?? null, 'interpretacion' => $datos['interpretacion'] ?? null, 'observacion' => $datos['observacion'] ?? null,
                'fecha_hora' => CarbonImmutable::parse($datos['fecha_hora'])->setTimezone(config('app.timezone')), 'estado' => 'COMPLETA'];
            $anterior = null;
            if ($historica) {
                [$existente, $anterior, $vincular] = $this->conciliacion($origen, $cabecera, $respuestas);
                if ($existente !== null) {
                    if ($vincular) {
                        if (! $guardar) {
                            return ['estado' => 'VINCULACION', 'cod_aplicacion' => $existente->getKey(), 'escrituras' => false];
                        }
                        // Se añaden solo vínculos ahora cotejados. No se inventa quién digitó el registro histórico.
                        AplicacionInstrumento::query()->whereKey($existente->getKey())->update([
                            'cod_documento_clinico' => $origen['cod_documento_clinico'], 'localizador_fuente' => $origen['localizador_fuente']]);
                        activity('instrumentos')->causedBy($usuario)->performedOn($existente)->event('fuente_original_vinculada')
                            ->withProperties(['cod_documento_clinico' => $origen['cod_documento_clinico']])
                            ->log('Fuente cotejada vinculada sin modificar respuestas o autoría anteriores.');
                    }

                    return $guardar ? $existente->fresh('respuestas') : ['estado' => 'IDENTICA', 'cod_aplicacion' => $existente->getKey(), 'escrituras' => false];
                }
                if (! $guardar) {
                    return ['estado' => $anterior === null ? 'NUEVA' : 'RECTIFICACION', 'cod_aplicacion_anterior' => $anterior?->getKey(), 'escrituras' => false];
                }
            }
            $aplicacion = AplicacionInstrumento::query()->create(['cod_aplicacion' => $codigoReservado ?? app(CodigosEntidad::class)->siguiente('aplicaciones_instrumento'),
                ...$cabecera, 'cod_usuario_registro' => $usuario->getKey(), 'registrado_en' => now(),
                ...array_intersect_key($origen, array_flip(['cod_documento_clinico', 'localizador_fuente', 'cod_aplicacion_anterior']))]);
            foreach ($respuestas as $respuesta) {
                RespuestaInstrumento::query()->create(['cod_respuesta' => app(CodigosEntidad::class)->siguiente('respuestas_instrumento'), 'cod_aplicacion' => $aplicacion->cod_aplicacion,
                    ...array_intersect_key($respuesta, array_flip(['cod_pregunta', 'cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico', 'puntaje', 'observacion']))]);
            }

            activity('instrumentos')->causedBy($usuario)->performedOn($aplicacion)->event('aplicacion_registrada')
                ->withProperties(['cod_residente' => $residente->getKey(), 'cod_instrumento' => $instrumento->getKey(),
                    'cod_personal_aplicador' => $aplicador, 'fecha_hora_original' => $aplicacion->fecha_hora->toIso8601String(),
                    'cod_documento_clinico' => $origen['cod_documento_clinico'] ?? null, 'cod_aplicacion_anterior' => $anterior?->getKey()])
                ->log($historica ? 'Transcripción cotejada de un original identificado.' : 'Captura por preguntas registrada con fecha explícita y autor propio.');
            if ($anterior !== null) {
                $anterior->update(['estado' => 'ANULADA']);
                activity('instrumentos')->causedBy($usuario)->performedOn($anterior)->event('aplicacion_rectificada')
                    ->withProperties(['sucesora' => $aplicacion->getKey(), 'motivo' => $origen['motivo_rectificacion']])
                    ->log('Contenido original conservado; nueva aplicación enlazada.');
            }

            return $aplicacion->load('respuestas');
        }, 3);
    }

    private function origen(User $usuario, Residente $residente, array $entrada): array
    {
        // La excepción temporal de Superadministración nunca concede transcripción histórica.
        abort_unless($usuario->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'])
            && $usuario->checkPermissionTo('aplicaciones_instrumento.transcribir', 'web')
            && $usuario->checkPermissionTo('respuestas_instrumento.ver', 'web'), 403);
        Gate::forUser($usuario)->authorize('view', $residente);
        $d = Validator::make($entrada, ['cod_personal_original' => ['required', 'string', 'exists:personal,cod_personal'],
            'cod_documento_clinico' => ['required', 'string', 'exists:documentos_clinicos,cod_documento_clinico'],
            'localizador_fuente' => ['required', 'string', 'max:120'], 'original_cotejado' => ['required', 'accepted'],
            'cod_aplicacion_anterior' => ['nullable', 'string', 'exists:aplicaciones_instrumento,cod_aplicacion'],
            'cod_aplicacion_existente' => ['nullable', 'string', 'exists:aplicaciones_instrumento,cod_aplicacion'],
            'motivo_rectificacion' => ['required_with:cod_aplicacion_anterior', 'nullable', 'string', 'min:10', 'max:500']])->validate();
        $doc = DocumentoClinico::query()->lockForUpdate()->findOrFail($d['cod_documento_clinico']);
        abort_unless($doc->cod_residente === $residente->getKey() && $doc->estado === 'VIGENTE', 422, 'El documento original no está vigente o no pertenece al residente.');
        abort_unless($doc->cod_personal === null || $doc->cod_personal === $d['cod_personal_original'], 422, 'El autor indicado no coincide con el documento original.');
        $ficheros = app(ArchivosAplicacion::class);
        abort_unless($ficheros->existe('local', $doc->ruta_archivo), 422, 'No está disponible el archivo original.');
        $hash = $ficheros->hash('local', $doc->ruta_archivo);
        abort_unless(is_string($hash) && hash_equals($doc->hash_archivo, $hash), 422, 'El archivo no coincide con la huella conservada.');

        return ['cod_personal' => $d['cod_personal_original'], 'cod_documento_clinico' => $doc->getKey(),
            'localizador_fuente' => trim($d['localizador_fuente']), 'cod_aplicacion_anterior' => $d['cod_aplicacion_anterior'] ?? null,
            'cod_aplicacion_existente' => $d['cod_aplicacion_existente'] ?? null,
            'motivo_rectificacion' => $d['motivo_rectificacion'] ?? null];
    }

    private function conciliacion(array $origen, array $cabecera, array $respuestas): array
    {
        $actual = AplicacionInstrumento::query()->where('cod_documento_clinico', $origen['cod_documento_clinico'])
            ->where('localizador_fuente', $origen['localizador_fuente'])->whereNull('cod_aplicacion_anterior')->lockForUpdate()->first();
        if ($actual === null && $origen['cod_aplicacion_existente'] !== null) {
            $candidato = AplicacionInstrumento::query()->lockForUpdate()->findOrFail($origen['cod_aplicacion_existente']);
            abort_unless($origen['cod_aplicacion_anterior'] === null && $candidato->cod_documento_clinico === null
                && $candidato->cod_aplicacion_anterior === null && in_array($candidato->estado, ['COMPLETA', 'VIGENTE'], true)
                && $this->contenido($candidato->getAttributes(), $candidato->respuestas->toArray()) === $this->contenido($cabecera, $respuestas),
                409, 'El registro anterior no coincide íntegramente con el original indicado; no se vincula ni se sobrescribe.');

            return [$candidato, null, true];
        }
        if ($actual === null) {
            $legado = AplicacionInstrumento::query()->where('cod_residente', $cabecera['cod_residente'])->where('cod_instrumento', $cabecera['cod_instrumento'])
                ->where('fecha_hora', $cabecera['fecha_hora'])->whereNull('cod_documento_clinico')->lockForUpdate()->first();
            abort_if($legado !== null, 409, 'Existe una aplicación en esa fecha sin vínculo original. Coteje su contenido e indique la aplicación existente; no se duplicará automáticamente.');
        }
        $visitadas = [];
        while ($actual !== null && ($siguiente = $actual->sucesora()->lockForUpdate()->first()) !== null) {
            if (isset($visitadas[$actual->getKey()])) {
                throw new \DomainException('La cadena original de rectificaciones contiene un ciclo.');
            }
            $visitadas[$actual->getKey()] = true;
            $actual = $siguiente;
        }
        abort_unless($actual === null || in_array($actual->estado, ['COMPLETA', 'VIGENTE'], true), 409, 'La fuente ya fue anulada. No se reactiva mediante una transcripción o rectificación.');
        if ($actual !== null && $this->contenido($actual->getAttributes(), $actual->respuestas->toArray()) === $this->contenido($cabecera, $respuestas)) {
            if ($origen['cod_aplicacion_anterior'] !== null) {
                abort_unless($actual->cod_aplicacion_anterior === $origen['cod_aplicacion_anterior'], 409, 'La rectificación indicada no corresponde a esta revisión.');
            }

            return [$actual, null, false];
        }
        abort_unless($actual === null || $actual->estado === 'COMPLETA', 409, 'La aplicación anterior no es una cabeza completa para rectificar. Se conserva su estado original.');
        abort_unless($actual === null ? $origen['cod_aplicacion_anterior'] === null : $origen['cod_aplicacion_anterior'] === $actual->getKey(),
            409, 'La fuente ya tiene otro contenido. Concilie el conflicto y señale su aplicación anterior para rectificar.');
        if ($actual !== null) {
            abort_unless($actual->cod_residente === $cabecera['cod_residente'] && $actual->cod_instrumento === $cabecera['cod_instrumento'], 422,
                'La rectificación debe conservar residente e instrumento.');
        }

        return [null, $actual, false];
    }

    /** Representación estable de decimales/booleanos para conciliar ambos motores SQL. */
    private function contenido(array $cabecera, array $respuestas): array
    {
        $c = array_intersect_key($cabecera, array_flip(['cod_instrumento', 'cod_residente', 'cod_personal', 'cod_atencion', 'fecha_hora', 'clasificacion', 'interpretacion', 'observacion']));
        $c['fecha_hora'] = CarbonImmutable::parse($c['fecha_hora'])->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
        foreach (['puntaje_total', 'puntaje_maximo'] as $campo) {
            $c[$campo] = ($cabecera[$campo] ?? null) === null ? null : number_format((float) $cabecera[$campo], 2, '.', '');
        }
        ksort($c);
        $filas = [];
        foreach ($respuestas as $r) {
            $f = [];
            foreach (['cod_pregunta', 'cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico', 'puntaje', 'observacion'] as $campo) {
                $v = $r[$campo] ?? null;
                $f[$campo] = $v === null ? null : match ($campo) {
                    'valor_numero' => number_format((float) $v, 4, '.', ''), 'puntaje' => number_format((float) $v, 2, '.', ''),
                    'valor_logico' => (bool) $v, default => $v,
                };
            }
            $filas[$f['cod_pregunta']] = $f;
        }
        ksort($filas);

        return [$c, $filas];
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
