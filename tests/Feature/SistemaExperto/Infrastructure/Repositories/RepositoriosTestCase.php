<?php

declare(strict_types=1);

namespace Tests\Feature\SistemaExperto\Infrastructure\Repositories;

use App\Models\ControlCognitivo;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorEvidencias;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Mapeadores\MapeadorReglas;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use Closure;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Ejecuta Eloquent y mappers reales sobre migraciones vigentes. */
abstract class RepositoriosTestCase extends TestCase
{
    use RefreshDatabase;

    /** Prepara la base solo después de las guardas de Tests\\TestCase */
    protected function setUp(): void
    {
        parent::setUp();
        EscenarioRepositorios::preparar();
    }

    /** @return string */
    protected function conexionPruebas(): string
    {
        return DB::getDefaultConnection();
    }

    /** @return ReglaExpertaEloquentRepository */
    protected function reglas(): ReglaExpertaEloquentRepository
    {
        return new ReglaExpertaEloquentRepository(new MapeadorReglas('RESULTADO'), $this->conexionPruebas());
    }

    /**
     * Inyecta extracción sintética limitada al campo físico memoria_reciente.
     * @param Closure|null $extraer
     * @return EvidenciaEloquentRepository
     */
    protected function evidencias(?Closure $extraer = null): EvidenciaEloquentRepository
    {
        return new EvidenciaEloquentRepository(new MapeadorEvidencias(new DateTimeZone('America/La_Paz')),
            $extraer ?? self::extraccion(), new DateTimeZone('America/La_Paz'), $this->conexionPruebas());
    }

    /**
     * Entrega extracción de fixture por lote usando modelos y consultas reales.
     * @param string $tipo
     * @return Closure
     * @throws \RuntimeException
     */
    protected static function extraccion(string $tipo = 'EVIDENCIA_COGNITIVA'): Closure
    {
        return static function (Collection $filas, DateTimeImmutable $corte, string $conexion) use ($tipo): array {
            $registros = ControlCognitivo::on($conexion)->where('cod_residente', 'SINTETICO-RES')
                ->whereIn('cod_control_cognitivo', $filas->pluck('cod_registro_fuente')->all())->get()->keyBy('cod_control_cognitivo');
            $respuesta = [];
            foreach ($filas as $fila) {
                $origen = $registros[$fila->getRawOriginal('cod_registro_fuente')]
                    ?? throw new \RuntimeException('Origen sintético no disponible.');
                $respuesta[$fila->getRawOriginal('cod_evidencia_evaluacion')] = [
                    'origen' => $origen, 'valor' => $origen->getRawOriginal('memoria_reciente'), 'tipo' => $tipo,
                ];
            }

            return $respuesta;
        };
    }

    /** @return DateTimeImmutable */
    protected static function corte(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-10-17T12:00:00-04:00');
    }

    /**
     * Comprueba que la operación no produjo consultas de escritura.
     * @return void
     */
    protected function exigirSoloLecturas(): void
    {
        foreach (DB::connection($this->conexionPruebas())->getQueryLog() as $consulta) {
            self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|replace|alter|drop|create)\b/i', $consulta['query']);
        }
    }
}
