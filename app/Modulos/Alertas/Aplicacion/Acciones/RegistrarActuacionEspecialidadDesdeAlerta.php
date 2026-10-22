<?php

namespace App\Modulos\Alertas\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\ContextoEjecucion;
use App\Models\Alerta;
use App\Models\Atencion;
use App\Models\EventoAlerta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\CamposRegistroEspecialidad;
use App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas;
use App\Modulos\Clinica\Aplicacion\Acciones\RegistrarExpedienteClinico;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionProfesional;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LogicException;

final class RegistrarActuacionEspecialidadDesdeAlerta
{
    public function __construct(private readonly ContextoEjecucion $contexto) {}

    public function ejecutar(User $usuario, string $id, array $captura, array $contexto, string $solicitud): string
    {
        abort_unless($this->contexto->usuario()?->getKey() === $usuario->getKey()
            && $usuario->fresh()?->estado === 'ACTIVO', 403);
        abort_if(app(RolePreviewService::class)->isActive($usuario), 403);
        $definicion = app(CamposRegistroEspecialidad::class)->paraRol(app(ExperienciaAlertas::class)->paraUsuario($usuario)['rol']);
        abort_unless($definicion && $usuario->can($definicion['permiso']), 403);
        Validator::make(['solicitud' => $solicitud], ['solicitud' => 'required|uuid'])->validate();
        $datos = array_intersect_key($captura, $definicion['campos']);
        if (! collect($datos)->contains(fn ($v) => $v !== null && (! is_string($v) || trim($v) !== ''))) {
            throw ValidationException::withMessages(['capturaEspecialidad' => 'Registra al menos un hallazgo o una observación profesional.']);
        }
        foreach ($datos as $campo => $valor) {
            if (is_string($valor)) {
                $datos[$campo] = trim($valor) === '' ? null : trim($valor);
            }
        }
        $hash = hash('sha256', json_encode([$id, $definicion['tipo'], $datos, $contexto]));
        $clave = 'alertas:especialidad:'.hash('sha256', $usuario->getKey().'|'.$solicitud);

        return Cache::lock($clave.':lock', 15)->block(3, function () use ($usuario, $id, $datos, $contexto, $definicion, $clave, $hash) {
            $alerta = app(BandejaAlertas::class)->consulta($usuario)->findOrFail($id);
            if ($previo = Cache::get($clave)) {
                abort_unless($previo['hash'] === $hash, 409, 'Esta solicitud ya corresponde a otra captura.');
                if (EventoAlerta::whereKey($previo['evento'])->where('cod_alerta', $id)->exists()) {
                    return $previo['registro'];
                }
                Cache::forget($clave);
            }

            return DB::transaction(function () use ($usuario, $alerta, $datos, $contexto, $definicion, $clave, $hash) {
                $bloqueada = Alerta::lockForUpdate()->findOrFail($alerta->getKey());
                $residente = Residente::findOrFail($bloqueada->cod_residente);
                $entrada = $datos;
                if ($definicion['requiereAtencion']) {
                    if (($contexto['atencion'] ?? '') === 'NUEVA') {
                        try {
                            $personal = app(ContextoClinicoService::class)->personalActivo($usuario);
                            $area = app(ContextoClinicoService::class)->areaAtencion($personal);
                        } catch (LogicException $excepcion) {
                            throw ValidationException::withMessages(['atencionEspecialidad' => $excepcion->getMessage()]);
                        }
                        $atencion = app(RegistrarExpedienteClinico::class)->crearAtencion($usuario, [
                            'cod_area' => $area->getKey(), 'tipo_atencion' => $contexto['tipoAtencion'] ?? '',
                            'motivo' => $contexto['motivoAtencion'] ?? null,
                        ], $residente);
                    } else {
                        $atencion = Atencion::whereKey($contexto['atencion'] ?? '')
                            ->where('cod_residente', $residente->getKey())
                            ->where('cod_personal', $usuario->personal?->getKey())->first();
                        if (! $atencion) {
                            throw ValidationException::withMessages(['atencionEspecialidad' => 'Selecciona una atención propia de este residente o registra una nueva.']);
                        }
                    }
                    $entrada['cod_atencion'] = $atencion->getKey();
                }
                $registro = $definicion['tipo'] === 'nota'
                    ? app(RegistrarExpedienteClinico::class)->registrar($usuario, $entrada, $residente, 'nota')
                    : app(RegistrarValoracionProfesional::class)->registrar($usuario, $entrada, $residente, $definicion['tipo']);
                $evento = $bloqueada->eventos()->create([
                    'cod_usuario' => $usuario->getKey(), 'tipo_evento' => 'SEGUIMIENTO',
                    'estado_anterior' => $bloqueada->estado, 'estado_nuevo' => $bloqueada->estado,
                    'fecha_hora' => now(), 'descripcion' => $definicion['titulo'].' guardada en el expediente ('.$registro->getKey().'). El estado de la alerta se conserva.',
                ]);
                activity('alertas')->performedOn($registro)->causedBy($usuario)
                    ->withProperties(['cod_alerta' => $bloqueada->getKey()])->log('Registro de especialidad desde una alerta');
                Cache::put($clave, ['hash' => $hash, 'registro' => $registro->getKey(), 'evento' => $evento->getKey()], now()->addHour());

                return $registro->getKey();
            });
        });
    }
}
