<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Compartido\Aplicacion\Contratos\CodigosEntidad;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHallazgosObservacionales;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConservacionObservacional;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;

/** Operación explícita, atómica e idempotente; nunca se ejecuta al leer una evaluación histórica. */
final class EjecutarObservaciones
{
    public function ejecutar(User $usuario, Residente $residente, string $solicitud): string
    {
        if (! preg_match('/^[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}$/D', $solicitud)) {
            throw new DomainException('Identidad de solicitud observacional inválida.');
        }

        $operacion = fn () => DB::transaction(function () use ($usuario, $residente, $solicitud): string {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $residente = Residente::query()->lockForUpdate()->findOrFail($residente->getKey());
            Gate::forUser($usuario)->authorize('ejecutarObservaciones', [EvaluacionExperta::class, $residente]);
            $personal = $usuario->personal()->where('estado', 'ACTIVO')->whereIn('profesion', ['MEDICINA', 'PSICOLOGIA'])->lockForUpdate()->get();
            if ($personal->count() !== 1) {
                throw new DomainException('La ejecución requiere un profesional propio activo e inequívoco.');
            }
            $autor = $personal->sole();
            $previa = DB::table('evaluaciones_expertas')->where('id_evaluacion_dominio', 'OBS:'.$solicitud)->first();
            if ($previa) {
                abort_unless($previa->cod_residente === $residente->getKey() && $previa->cod_personal_solicitante === $autor->getKey()
                    && $previa->origen_activacion === 'OBSERVACIONAL', 404);
                app(ConsultarObservacionesConservadas::class)->consultar($usuario, $residente, $previa->cod_evaluacion_experta);

                return $previa->cod_evaluacion_experta;
            }
            $paquete = app(ConocimientoObservacional::class)->preparar($usuario);
            $lectura = app(ConsultarHallazgosObservacionales::class)->instantaneaCompleta($usuario, $residente, CarbonImmutable::now());
            $informe = (new EvaluadorObservaciones)->evaluar($residente->getKey(), new \DateTimeImmutable($lectura['fecha_corte']), $lectura['instantanea'], $paquete);
            $id = app(ConservacionObservacional::class)->guardar($residente->getKey(), $autor->getKey(), $solicitud, $paquete, $lectura['instantanea'], $informe);
            // La operación no confirma si la reconstrucción independiente no recupera la traza completa.
            app(ConsultarObservacionesConservadas::class)->consultar($usuario, $residente, $id);
            activity('sistema_experto')->causedBy($usuario)->event('observaciones_ejecutadas')
                ->withProperties(['evaluacion' => $id, 'version' => $paquete['codigo'], 'alcance' => 'OBSERVACIONAL'])
                ->log('Ejecución observacional conservada sin clasificación DCL.');

            return $id;
        }, 3);

        // Solo la primera carga toma el contador antes de actor/residente, igual que el gestor.
        return DB::table('versiones_modelo_experto')->where('codigo_version', EvaluadorObservaciones::VERSION)->exists()
            ? $operacion() : app(CodigosEntidad::class)->serializar('versiones_modelo_experto', $operacion);
    }
}
