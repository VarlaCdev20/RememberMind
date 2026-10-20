<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Models\Atencion;
use App\Models\DocumentoClinico;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\SoporteValoracionPetersen;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen as F;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\VerificarOriginalDocumentalPetersen;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MotorExperto\dominio\petersen\EvaluarPetersenInstitucional;
use MotorExperto\dominio\petersen\ValoracionOriginalPetersen as V;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica;
use MotorExperto\soporte\conservacion\CodecInstantanea;

/** Captura original transaccional, atribuida a la sesión, idempotente y sin actualización de historia. */
final class RegistrarValoracionPetersenOriginal
{
    public function ejecutar(User $usuario, Residente $residente, array $entrada): ValoracionPetersenOriginal
    {
        return DB::transaction(function () use ($usuario, $residente, $entrada): ValoracionPetersenOriginal {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            $anterior = null;
            if (($entrada['cod_valoracion_anterior'] ?? null) !== null) {
                $anterior = ValoracionPetersenOriginal::query()->where('cod_residente', $residente->getKey())
                    ->whereKey($entrada['cod_valoracion_anterior'])->lockForUpdate()->firstOrFail();
                Gate::forUser($usuario)->authorize('rectificarOriginal', $anterior);
            } else {
                Gate::forUser($usuario)->authorize('crearOriginal', [ValoracionPetersenOriginal::class, $residente]);
            }
            $personal = $usuario->personal()->where('estado', 'ACTIVO')->sole();
            $campos = array_merge(...array_values(V::COMPROBACIONES));
            $permitidos = array_merge(['solicitud_uuid', 'elemento', 'estado_original', 'justificacion', 'fecha_hora', 'tipo_registro',
                'cod_valoracion_anterior', 'cod_atencion', 'cod_documento_clinico', 'localizador_fuente', 'soportes', 'error_fecha_original'], $campos);
            if (array_diff(array_keys($entrada), $permitidos)) {
                throw new DomainException('El registro no admite autor, cuenta, resultado esperado ni campos ajenos al contrato.');
            }
            $reglas = ['solicitud_uuid' => ['required', 'uuid'], 'elemento' => ['required', Rule::in([...EvaluarPetersenInstitucional::DOMINIOS, 'C01', 'C03', 'C04', 'C05'])],
                'estado_original' => ['nullable', Rule::in(['A', 'P', 'I', 'S', 'N'])], 'justificacion' => ['required', 'string', 'max:10000'],
                'fecha_hora' => ['required', 'date', 'before_or_equal:now'], 'tipo_registro' => ['required', Rule::in(['REGISTRO', 'RECTIFICACION', 'ANULACION'])],
                'cod_valoracion_anterior' => ['nullable', 'string', 'max:20'], 'cod_atencion' => ['nullable', 'string', 'max:20'],
                'cod_documento_clinico' => ['nullable', 'string', 'max:20'], 'localizador_fuente' => ['nullable', 'string', 'max:120'],
                'soportes' => ['present', 'array', 'max:100'], 'error_fecha_original' => ['nullable', 'string', 'min:10', 'max:500']];
            foreach ($campos as $campo) {
                $reglas[$campo] = ['nullable', 'boolean'];
            }
            $datos = Validator::make($entrada, $reglas, [
                'fecha_hora.before_or_equal' => 'La fecha y hora del acto original no pueden ser futuras.',
            ], ['fecha_hora' => 'fecha y hora del acto original', 'justificacion' => 'fundamento de la valoración'])->validate();
            $datos['justificacion'] = trim($datos['justificacion']);
            $fecha = CarbonImmutable::parse($datos['fecha_hora'])->setTimezone(config('app.timezone'));
            $tipo = $datos['tipo_registro'];
            $propios = V::COMPROBACIONES[str_starts_with($datos['elemento'], 'COG-') ? 'DOMINIO' : $datos['elemento']];
            foreach ($campos as $campo) {
                if (($datos[$campo] ?? null) !== null && (! in_array($campo, $propios, true) || $tipo === 'ANULACION')) {
                    throw new DomainException('La comprobación no corresponde al elemento o a una anulación.');
                }
                $datos[$campo] = ($datos[$campo] ?? null) === null ? null : (bool) $datos[$campo];
            }
            $estado = $datos['estado_original'] ?? null;
            if ($datos['justificacion'] === '' || ($anterior === null) !== ($tipo === 'REGISTRO')
                || ($tipo === 'ANULACION' && ($estado !== null || $datos['soportes'] !== []))
                || ($tipo !== 'ANULACION' && ! in_array($estado, str_starts_with($datos['elemento'], 'COG-') ? ['A', 'P', 'I'] : ['S', 'N'], true))) {
                throw new DomainException('Tipo, estado original, justificación o antecesora incompatibles.');
            }
            if ($anterior && ($anterior->elemento !== $datos['elemento'] || $anterior->tipo_registro === 'ANULACION')) {
                throw new DomainException('La corrección requiere el mismo elemento y una cabeza no anulada.');
            }
            if ($anterior && CarbonImmutable::now()->lessThan($anterior->registrado_en)) {
                throw new DomainException('El instante técnico no puede preceder al registro que se corrige.');
            }
            if ($anterior && ! $anterior->fecha_hora->equalTo($fecha)) {
                if ($tipo === 'ANULACION' || trim($datos['error_fecha_original'] ?? '') === '') {
                    throw new DomainException('La fecha del acto se conserva; corregirla requiere identificar expresamente el error de fecha.');
                }
                $datos['justificacion'] = 'Corrección de fecha: '.trim($datos['error_fecha_original'])."\n".$datos['justificacion'];
                if (mb_strlen($datos['justificacion']) > 10000) {
                    throw new DomainException('La justificación completa no puede superar 10000 caracteres.');
                }
            }
            unset($datos['error_fecha_original']);
            if (! array_is_list($datos['soportes'])) {
                throw new DomainException('Los soportes deben ser una lista ordenada.');
            }
            // El reenvío autorizado compara el acto ya guardado, antes de consultar fuentes que pudieron cambiar.
            if ($reintento = $this->reintento($datos, $fecha, $usuario, $residente, $personal->getKey())) {
                return $reintento;
            }
            $soportes = [];
            $vistos = [];
            $capturas = null;
            foreach ($datos['soportes'] as $orden => $s) {
                $s = Validator::make(['soporte' => $s], ['soporte' => ['required', 'array:tipo_soporte,'.implode(',', array_keys(F::FUENTES))]])->validate()['soporte'];
                $tipoSoporte = $s['tipo_soporte'] ?? '';
                $fks = array_filter(array_keys(F::FUENTES), fn ($fk) => ($s[$fk] ?? null) !== null && $s[$fk] !== '');
                if (count($fks) !== 1) {
                    throw new DomainException('Cada soporte requiere exactamente una referencia original.');
                }
                $fk = reset($fks);
                if (! in_array($fk, F::admitidas($datos['elemento'], $tipoSoporte), true) || ! is_string($s[$fk]) || strlen($s[$fk]) > 20) {
                    throw new DomainException('El tipo de soporte y su fuente no corresponden al elemento.');
                }
                [$modelo, $permiso] = F::FUENTES[$fk];
                abort_unless($usuario->checkPermissionTo($permiso, 'web'), 403, 'No dispone de autorización para una fuente original.');
                if ($fk === 'cod_control_iadl') {
                    Gate::forUser($usuario)->authorize('consultarFuncionalidad', [EvaluacionExperta::class, $residente]);
                }
                $fuente = $modelo::query()->where('cod_residente', $residente->getKey())->whereKey($s[$fk])->lockForUpdate()->firstOrFail();
                $identidad = $fuente->getTable().':'.$fuente->getKey();
                if (isset($vistos[$identidad])) {
                    throw new DomainException('No se repite una fuente original como soporte independiente.');
                }
                $vistos[$identidad] = true;
                $fechaFuente = $fuente->fecha_hora ?? $fuente->fecha_referencia;
                if (! $fechaFuente || CarbonImmutable::parse($fechaFuente)->greaterThan($fecha)
                    || in_array($fuente->estado, ['ANULADA', 'ANULADO', 'INACTIVA', 'INACTIVO', 'ARCHIVADO'], true)) {
                    throw new DomainException('Una fuente futura, anulada o sin fecha identificable no puede sustentar el acto.');
                }
                if ($fuente instanceof DocumentoClinico && ! app(VerificarOriginalDocumentalPetersen::class)->verificar($fuente)) {
                    throw new DomainException('El documento utilizado como soporte no es verificable.');
                }
                $fila = ['tipo_soporte' => $tipoSoporte, 'orden' => $orden, $fk => $fuente->getKey()];
                if ($tipoSoporte === 'DOMINIO_REVISADO') {
                    if (! in_array($fuente->elemento, EvaluarPetersenInstitucional::DOMINIOS, true) || $fuente->tipo_registro === 'ANULACION'
                        || ValoracionPetersenOriginal::query()->where('cod_valoracion_anterior', $fuente->getKey())->exists()) {
                        throw new DomainException('C05 necesita identificar una valoración de dominio vigente y anterior.');
                    }
                }
                if ($tipoSoporte === 'MEDICION') {
                    $capturas ??= app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, $fecha);
                    $captura = collect($capturas['capturas'])->firstWhere('cod_aplicacion', $fuente->getKey());
                    $perfil = $captura['perfil_captura_original'] ?? null;
                    if (! is_array($perfil)) {
                        throw new DomainException('La medida necesita su contrato instrumental exacto; no se reconstruye desde el nombre del instrumento.');
                    }
                    $fila['metodo_original'] = $perfil['metodo'];
                    $fila['huella_perfil_original'] = CodecInstantanea::huella(CapturaInstrumentalCanonica::ordenar($perfil));
                }
                $soportes[] = $fila;
            }
            if (($datos['cod_atencion'] ?? null) !== null) {
                Atencion::query()->whereKey($datos['cod_atencion'])->where('cod_residente', $residente->getKey())->lockForUpdate()->firstOrFail();
            }
            $documento = $datos['cod_documento_clinico'] ?? null;
            $localizador = isset($datos['localizador_fuente']) ? trim($datos['localizador_fuente']) : null;
            if (($documento === null) !== ($localizador === null || $localizador === '')) {
                throw new DomainException('El original requiere documento y localizador juntos.');
            }
            if ($documento !== null) {
                abort_unless($usuario->checkPermissionTo('documentos_clinicos.ver', 'web'), 403);
                $doc = DocumentoClinico::query()->whereKey($documento)->where('cod_residente', $residente->getKey())->lockForUpdate()->firstOrFail();
                if (! app(VerificarOriginalDocumentalPetersen::class)->verificar($doc)) {
                    throw new DomainException('El documento original no es verificable.');
                }
            }
            unset($datos['soportes']);
            $datos = array_replace(array_fill_keys(['cod_atencion', 'cod_documento_clinico', 'localizador_fuente', 'cod_valoracion_anterior', 'estado_original'], null), $datos);
            $datos['localizador_fuente'] = $localizador;
            $datos['fecha_hora'] = $fecha->format('Y-m-d H:i:s.u');
            $datos['cod_personal'] = $personal->getKey();
            $datos['cod_usuario_registro'] = $usuario->getKey();
            $datos['cod_residente'] = $residente->getKey();
            if ($anterior && ValoracionPetersenOriginal::query()->where('cod_valoracion_anterior', $anterior->getKey())->exists()) {
                throw new DomainException('La cadena cambió; recargue antes de corregir.');
            }
            $valoracion = ValoracionPetersenOriginal::create($datos + ['registrado_en' => CarbonImmutable::now()->format('Y-m-d H:i:s.u')]);
            foreach ($soportes as $s) {
                SoporteValoracionPetersen::create($s + ['cod_valoracion_petersen' => $valoracion->getKey()]);
            }
            activity('SistemaExperto')->causedBy($usuario)->performedOn($valoracion)->event('valoracion_original_petersen_registrada')
                ->withProperties(['elemento' => $valoracion->elemento, 'tipo_registro' => $tipo])->log('Valoración original conservada con sus soportes.');

            return $valoracion->load('soportes');
        }, 5);
    }

    private function reintento(array $datos, CarbonImmutable $fecha, User $usuario, Residente $residente, string $personal): ?ValoracionPetersenOriginal
    {
        $existente = ValoracionPetersenOriginal::query()->where('solicitud_uuid', $datos['solicitud_uuid'])->with('soportes')->first();
        if (! $existente) {
            return null;
        }
        $enlaces = [];
        foreach ($datos['soportes'] as $orden => $s) {
            $s = Validator::make(['soporte' => $s], ['soporte' => ['required', 'array:tipo_soporte,'.implode(',', array_keys(F::FUENTES))]])->validate()['soporte'];
            $fks = array_filter(array_keys(F::FUENTES), fn ($fk) => ($s[$fk] ?? null) !== null && $s[$fk] !== '');
            if (count($fks) !== 1) {
                throw new DomainException('Cada soporte requiere exactamente una referencia original.');
            }
            $fk = reset($fks);
            abort_unless($usuario->checkPermissionTo(F::FUENTES[$fk][1], 'web'), 403);
            if ($fk === 'cod_control_iadl') {
                Gate::forUser($usuario)->authorize('consultarFuncionalidad', [EvaluacionExperta::class, $residente]);
            }
            $enlaces[] = ['tipo_soporte' => $s['tipo_soporte'] ?? '', 'orden' => $orden, $fk => $s[$fk]];
        }
        unset($datos['soportes']);
        $datos = array_replace(array_fill_keys(['cod_atencion', 'cod_documento_clinico', 'localizador_fuente', 'cod_valoracion_anterior', 'estado_original'], null), $datos);
        if ($datos['cod_documento_clinico'] !== null) {
            abort_unless($usuario->checkPermissionTo('documentos_clinicos.ver', 'web'), 403);
        }
        $datos['localizador_fuente'] = isset($datos['localizador_fuente']) ? trim($datos['localizador_fuente']) : null;
        $datos['fecha_hora'] = $fecha->format('Y-m-d H:i:s.u');
        $datos['cod_personal'] = $personal;
        $datos['cod_usuario_registro'] = $usuario->getKey();
        $datos['cod_residente'] = $residente->getKey();
        $original = $existente->only(array_keys($datos));
        $original['fecha_hora'] = $existente->fecha_hora->format('Y-m-d H:i:s.u');
        $originales = $existente->soportes->map(fn ($s) => array_filter($s->only(array_merge(['tipo_soporte', 'orden'], array_keys(F::FUENTES))), fn ($v) => $v !== null))->all();
        if (CapturaInstrumentalCanonica::ordenar($original) !== CapturaInstrumentalCanonica::ordenar($datos)
            || CapturaInstrumentalCanonica::ordenar($originales) !== CapturaInstrumentalCanonica::ordenar($enlaces)) {
            throw new DomainException('La solicitud ya tiene otro contenido. Recargue antes de repetir el registro.');
        }

        return $existente;
    }
}
