<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratosConocimiento;
use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\soporte\conservacion\InstantaneaInstrumental;

/** Consulta declarativa de fuentes autorizadas; no guarda ni sustituye una evaluación histórica. */
final class ConsultarConocimientoCognitivo
{
    public function consultar(User $usuario, Residente $residente, CarbonImmutable $corte): array
    {
        $consulta = $this->adquirir($usuario, $residente, $corte);
        unset($consulta['_instantanea']);

        return $consulta;
    }

    /** Solo backend: conserva el DTO autorizado en memoria, sin enviarlo al estado de Livewire. */
    public function preparar(User $usuario, Residente $residente, CarbonImmutable $corte): InstantaneaInstrumental
    {
        $consulta = $this->adquirir($usuario, $residente, $corte);

        return $consulta['_instantanea'] ?? throw new DomainException('No se puede preparar la evaluación: '.$consulta['motivo']);
    }

    /** Solo backend: permite compartir la captura verificada sin releer sus mediciones en Petersen. */
    public function adquirir(User $usuario, Residente $residente, CarbonImmutable $corte): array
    {
        return DB::transaction(function () use ($usuario, $residente, $corte): array {
            $usuario = User::query()->findOrFail($usuario->getKey());
            $residente = Residente::query()->findOrFail($residente->getKey());
            Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
            if ($corte->greaterThan(CarbonImmutable::now())) {
                throw new DomainException('La fecha de corte no puede estar en el futuro.');
            }
            $version = config('sistema_experto.version_cognitiva');
            if ($version === null || $version === '') {
                $version = app(ContratosConocimiento::class)->versionInstitucionalVigente($corte);
            }
            $sinVersion = ! is_string($version) || $version === '';
            $contrato = $p = null;
            $vinculos = [];
            if (! $sinVersion) {
                $v = VersionModeloExperto::query()->whereKey($version)->lockForUpdate()->sole();
                if ($v->estado !== 'ACTIVO' || ($v->fecha_hora_retiro !== null && CarbonImmutable::parse($v->getRawOriginal('fecha_hora_retiro'))->lessThanOrEqualTo($corte)) || $v->fecha_hora_vigencia === null
                    || CarbonImmutable::parse($v->getRawOriginal('fecha_hora_vigencia'))->greaterThan($corte)
                    || preg_match('/SIM|TEST|EXPERIMENTAL/i', $v->codigo_version)) {
                    throw new DomainException('La versión declarativa no está vigente o pertenece al laboratorio.');
                }
                $contrato = app(ContratosConocimiento::class)->porVersion($version);
                $tablas = app(ConocimientoVersionado::class)->instantanea($version);
                $p = new PaqueteConocimiento($version, $tablas, $contrato['relaciones'], false, $contrato['tipo_regla_resultado'], $contrato['extracciones']);
                $vinculos = $contrato['vinculos_instrumentales'] ?? [];
                if (! is_array($vinculos)) {
                    throw new DomainException('Los vínculos instrumentales del servidor deben ser un mapa.');
                }
            }
            // Una sola lectura autorizada sirve a mediciones, métricas y reglas.
            $capturas = app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, $corte,
                array_values(array_unique(array_column($vinculos, 'cod_pregunta'))));
            if (! $capturas['disponible']) {
                return ['disponible' => false, 'motivo' => $capturas['motivo'], 'persistido' => false, 'capturas' => $capturas];
            }
            if ($sinVersion) {
                return ['disponible' => false, 'motivo' => 'SIN_VERSION_COGNITIVA_VINCULADA', 'persistido' => false, 'capturas' => $capturas];
            }
            if ($vinculos === [] && ($contrato['admite_memoria_vacia'] ?? false) !== true) {
                return ['disponible' => false, 'motivo' => 'SIN_VINCULOS_ORIGINALES_DEL_CONOCIMIENTO', 'version' => $version, 'persistido' => false, 'capturas' => $capturas];
            }
            $id = 'LECTURA-'.substr(hash('sha256', serialize([$usuario->getKey(), $residente->getKey(), $version, $corte->format('Y-m-d\TH:i:s.uP')])), 0, 12);
            $contexto = new ContextoEjecucion($id, $residente->getKey(), $version, $corte->toDateTimeImmutable());
            $instantanea = InstantaneaInstrumental::crear($p, $contexto, $capturas, $vinculos, $usuario->getKey(), $v->getAttributes(),
                'INSTRUMENTAL-DECLARATIVO-3', app(FuenteConocimientoOrion::class)->leer());
            $salida = $instantanea->contenido['salida'];

            return ['_instantanea' => $instantanea, 'disponible' => true, 'resultado' => $salida['resultado'], 'fuentes_excluidas' => $salida['fuentes_excluidas'],
                'perfil_instrumental' => $salida['perfil_instrumental'],
                'preparacion_conservacion' => ['contrato' => $instantanea->contenido['contrato'], 'huella' => $instantanea->huella,
                    'persistido' => false, 'release_recuperable' => false],
                'persistido' => false, 'revision_profesional' => 'PENDIENTE', 'capturas' => $capturas];
        });
    }
}
