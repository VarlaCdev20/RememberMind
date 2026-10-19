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

/**
 * TÍTULO: Base de integración de repositorios O.R.I.O.N.
 * CAPA: Infrastructure - Repositories (pruebas de integración).
 * ¿PARA QUÉ SIRVE? Ejecuta Eloquent y mappers reales sobre migraciones vigentes.
 * ¿QUÉ INFORMACIÓN RECIBE? SQLite :memory: o PostgreSQL dedicado del perfil de pruebas.
 * ¿QUÉ HACE? Prepara fixtures sintéticas y capacidades de extracción limitadas.
 * ¿QUÉ DEVUELVE? Un entorno aislado con PK/FK reales.
 * ¿QUÉ NO DEBE HACER? No simula el almacenamiento ni activa inferencia o servicios asistenciales.
 */
abstract class RepositoriosTestCase extends TestCase
{
    use RefreshDatabase;

    /** Prepara la base solo después de las guardas de Tests\\TestCase; sin parámetros ni retorno. */
    protected function setUp(): void
    {
        parent::setUp();
        EscenarioRepositorios::preparar();
    }

    /** @return string Conexión explícita del perfil seguro de pruebas; sin parámetros ni excepciones. */
    protected function conexionPruebas(): string
    {
        return DB::getDefaultConnection();
    }

    /** @return ReglaExpertaEloquentRepository Repositorio real con tipo documental explícito; sin parámetros ni excepciones. */
    protected function reglas(): ReglaExpertaEloquentRepository
    {
        return new ReglaExpertaEloquentRepository(new MapeadorReglas('RESULTADO'), $this->conexionPruebas());
    }

    /**
     * Inyecta extracción sintética limitada al campo físico memoria_reciente.
     *
     * @param  Closure|null  $extraer  Colaborador alternativo explícito para negativos.
     * @return EvidenciaEloquentRepository Repositorio real, sin simular sus consultas.
     */
    protected function evidencias(?Closure $extraer = null): EvidenciaEloquentRepository
    {
        return new EvidenciaEloquentRepository(new MapeadorEvidencias(new DateTimeZone('America/La_Paz')),
            $extraer ?? self::extraccion(), new DateTimeZone('America/La_Paz'), $this->conexionPruebas());
    }

    /**
     * Entrega extracción de fixture por lote usando modelos y consultas reales.
     *
     * @param  string  $tipo  Destino técnico, sin significado clínico.
     * @return Closure Colaborador limitado a controles cognitivos sintéticos.
     *
     * @throws \RuntimeException Si falta el original solicitado.
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

    /** @return DateTimeImmutable Corte inclusivo fijo de los escenarios; sin parámetros ni excepciones esperadas. */
    protected static function corte(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-10-17T12:00:00-04:00');
    }

    /**
     * Comprueba que la operación no produjo consultas de escritura.
     *
     * @return void Aserciones sobre el log capturado; sin parámetros ni excepciones esperadas.
     */
    protected function exigirSoloLecturas(): void
    {
        foreach (DB::connection($this->conexionPruebas())->getQueryLog() as $consulta) {
            self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|replace|alter|drop|create)\b/i', $consulta['query']);
        }
    }
}
