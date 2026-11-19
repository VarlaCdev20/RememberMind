<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Acciones;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ResultadoInstitucionalConservado as R;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarResultadoInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ConservarResultadoInstitucional;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\ResultadoInstitucionalSQL;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/** Ejecuta desde originales del servidor y confirma un resultado íntegro; nunca acepta resultados del navegador. */
final readonly class GuardarResultadoInstitucional
{
    public function ejecutar(User $usuario, Residente $residente, string $solicitud): array
    {
        if (! Str::isUuid($solicitud)) { throw new DomainException('La solicitud debe tener una identidad válida.'); }
        if (DB::transactionLevel() !== 0) { throw new DomainException('La operación requiere una transacción propia.'); }
        $usuario = User::query()->findOrFail($usuario->getKey());
        $residente = Residente::query()->findOrFail($residente->getKey());
        Gate::forUser($usuario)->authorize('ejecutarInstitucional', [EvaluacionExperta::class, $residente]);
        $lector = app(ConsultarResultadoInstitucional::class);
        if (! $lector->instalado()) { throw new DomainException('El guardado institucional todavía no está instalado.'); }
        $anterior = EvaluacionExperta::query()->where('id_evaluacion_dominio', 'INST:'.$solicitud)->first();
        if ($anterior) { return $this->reintento($usuario, $residente, $anterior); }
        $dto = app(PrepararEvaluacionInstitucional::class)->preparar($usuario, $residente, CarbonImmutable::now());
        return DB::transaction(function () use ($usuario, $residente, $solicitud, $dto): array {
            $cuenta = User::query()->whereKey($usuario->getKey())->lockForUpdate()->firstOrFail();
            $caso = Residente::query()->whereKey($residente->getKey())->lockForUpdate()->firstOrFail();
            Gate::forUser($cuenta)->authorize('ejecutarInstitucional', [EvaluacionExperta::class, $caso]);
            $anterior = EvaluacionExperta::query()->where('id_evaluacion_dominio', 'INST:'.$solicitud)->first();
            if ($anterior) { return $this->reintento($cuenta, $caso, $anterior); }
            $profesion = $cuenta->hasRole('PSICOLOGO/A') ? 'PSICOLOGIA' : 'MEDICINA';
            $personal = $cuenta->personal()->where('estado', 'ACTIVO')->where('profesion', $profesion)->lockForUpdate()->sole();
            $id = app(ConservarResultadoInstitucional::class)->guardar($dto, $personal->getKey(), $solicitud);
            $sql = app(ResultadoInstitucionalSQL::class);
            $huella = $sql->leer($id, verificar: false)['huella'];
            $cerrada = DB::table('evaluaciones_expertas')->where('cod_evaluacion_experta', $id)->where('huella_memoria_cognicion', str_repeat('0', 64))->update(['huella_memoria_cognicion' => $huella]);
            if ($cerrada !== 1) { throw new DomainException('El resultado ya fue cerrado por otra ejecución.'); }
            $resultado = app(ConsultarResultadoInstitucional::class)->consultar($cuenta, $caso, $id);
            activity('SistemaExperto')->causedBy($cuenta)->performedOn(EvaluacionExperta::findOrFail($id))->log('Resultado institucional conservado e íntegro.');
            return $resultado;
        }, 5);
    }

    private function reintento(User $usuario, Residente $residente, EvaluacionExperta $e): array
    {
        abort_unless($e->cod_residente === $residente->getKey() && $e->cod_usuario_ejecucion === $usuario->getKey() && $e->version_conservacion === R::VERSION, 404);
        return app(ConsultarResultadoInstitucional::class)->consultar($usuario, $residente, $e->getKey());
    }
}
