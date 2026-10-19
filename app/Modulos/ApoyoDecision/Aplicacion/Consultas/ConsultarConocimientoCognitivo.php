<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\adaptadores\entrada\ConstructorMemoriaInstrumental;
use MotorExperto\aplicacion\casos_uso\EvaluarConocimientoCognitivo;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;

/** Consulta declarativa de fuentes autorizadas; no guarda ni sustituye una evaluación histórica. */
final class ConsultarConocimientoCognitivo
{
    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): array
    {
        return DB::transaction(function () use ($usuario, $residente, $corte): array {
            $usuario = User::query()->findOrFail($usuario->getKey());
            $residente = Residente::query()->findOrFail($residente->getKey());
            Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
            if ($corte->greaterThan(CarbonImmutable::now())) {
                throw new DomainException('La fecha de corte no puede estar en el futuro.');
            }
            $version = config('sistema_experto.version_cognitiva');
            if (! is_string($version) || $version === '') {
                return ['disponible' => false, 'motivo' => 'SIN_VERSION_COGNITIVA_VINCULADA', 'persistido' => false];
            }
            $v = VersionModeloExperto::query()->whereKey($version)->lockForUpdate()->sole();
            if ($v->estado !== 'ACTIVO' || $v->fecha_hora_retiro !== null || $v->fecha_hora_vigencia === null
                || CarbonImmutable::parse($v->getRawOriginal('fecha_hora_vigencia'))->greaterThan($corte)
                || preg_match('/SIM|TEST|EXPERIMENTAL/i', $v->codigo_version)) {
                throw new DomainException('La versión declarativa no está vigente o pertenece al laboratorio.');
            }
            $contrato = app(ContratosConocimiento::class)->porVersion($version);
            $tablas = app(ConocimientoVersionado::class)->instantanea($version);
            $p = new PaqueteConocimiento($version, $tablas, $contrato['relaciones'], false, $contrato['tipo_regla_resultado'], $contrato['extracciones']);
            $capturas = app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, $corte);
            if (! $capturas['disponible']) {
                return ['disponible' => false, 'motivo' => $capturas['motivo'], 'persistido' => false];
            }
            $vinculos = $contrato['vinculos_instrumentales'] ?? null;
            if (! is_array($vinculos) || $vinculos === []) {
                return ['disponible' => false, 'motivo' => 'SIN_VINCULOS_ORIGINALES_DEL_CONOCIMIENTO', 'version' => $version, 'persistido' => false];
            }
            $id = 'LECTURA-'.substr(hash('sha256', serialize([$usuario->getKey(), $residente->getKey(), $version, $corte->format('Y-m-d\TH:i:s.uP')])), 0, 12);
            $contexto = new ContextoEjecucion($id, $residente->getKey(), $version, $corte->toDateTimeImmutable());
            $entrada = (new ConstructorMemoriaInstrumental)->construir($p, $contexto, $capturas, $vinculos);
            $resultado = (new EvaluarConocimientoCognitivo)->ejecutar($p, $entrada['memoria']);

            return ['disponible' => true, 'resultado' => $resultado, 'fuentes_excluidas' => $entrada['fuentes_excluidas'],
                'persistido' => false, 'revision_profesional' => 'PENDIENTE'];
        });
    }
}
