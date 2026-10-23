<?php

declare(strict_types=1);

namespace OrionFase02;

use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use PDO;

final class RepositorioCompuesto
{
    private ProyeccionTipada $proyeccion;

    public function __construct(private PDO $pdo)
    {
        GuardiaDesechable::verificar($pdo);
        $this->proyeccion = new ProyeccionTipada($pdo, require __DIR__.'/mapa-cerrado.php');
    }

    /** Retención ya verificada antes de abrir transacción; ninguna salida se guarda como entrada. */
    public function guardar(string $codigo, InstantaneaEvaluacionInstitucional $dto, array $release, array $fuente, ?callable $antesDeCerrar = null): bool
    {
        if ($this->pdo->inTransaction()) {
            throw new DomainException('El ensayo controla su transacción de adquisición completa.');
        }
        InstantaneaEvaluacionInstitucional::verificar($dto->contenido, $dto->huella);
        if (! preg_match('/^[A-Za-z0-9_-]{1,20}$/D', $codigo) || $dto->contenido['release']['huella'] !== $release['huella_manifiesto']
            || $dto->contenido['ejecutor']['huella_src'] !== $release['huella_src']) {
            throw new DomainException('Identidad de evaluación o ejecutor incompatible.');
        }
        $entrada = EntradasCompuestas::proyectar($dto, $fuente);
        $filas = $this->proyeccion->proyectar($entrada, $codigo); // Rechazar campos ajenos antes de cualquier INSERT.
        $c = $dto->contenido['instrumental']['contenido']['contexto'];
        $this->pdo->beginTransaction();
        try {
            $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $consulta = $this->pdo->prepare('SELECT * FROM orion_f02.evaluaciones_compuestas WHERE cod_evaluacion_experta=? OR id_evaluacion_dominio=? FOR UPDATE');
            $consulta->execute([$codigo, $c['evaluacion']]);
            $previa = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($previa) {
                if ($previa['cod_evaluacion_experta'] !== $codigo || $previa['huella_compuesta'] !== $dto->huella || $previa['estado'] !== 'CERRADA') {
                    throw new DomainException('La evaluación ya existe con otra identidad o contenido.');
                }
                if (C::ordenar($this->proyeccion->recuperar($codigo)) !== C::ordenar($entrada)) {
                    throw new DomainException('El contenido retenido diverge en un reintento.');
                }
                $this->pdo->commit();

                return false;
            }
            $s = $this->pdo->prepare('INSERT INTO orion_f02.evaluaciones_compuestas (cod_evaluacion_experta,id_evaluacion_dominio,id_evaluacion_petersen,cod_residente,cod_usuario_ejecucion,cod_release_motor,huella_compuesta,huella_instrumental,huella_petersen,contrato_proyeccion) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $s->execute([$codigo, $c['evaluacion'], $dto->contenido['petersen']['contenido']['contexto']['evaluacion'], $c['residente'], $c['usuario_ejecucion'], $release['cod_release_motor'], $dto->huella,
                $dto->contenido['instrumental']['huella'], $dto->contenido['petersen']['huella'], 'PROYECCION-COMPUESTA-F02-1']);
            $this->proyeccion->insertar($filas);
            $this->pdo->prepare("INSERT INTO orion_f02.anclajes_condiciones_adquiridas(cod_fila,cod_evaluacion_experta,rama,cod_regla_experta,cod_condicion_regla)
                SELECT substr(md5(cod_fila),1,20),cod_evaluacion_experta,coleccion,cod_regla_experta_texto,cod_condicion_regla_texto
                FROM orion_f02.copia_condiciones_regla_experta WHERE cod_evaluacion_experta=?")->execute([$codigo]);
            if (C::ordenar($this->proyeccion->recuperar($codigo)) !== C::ordenar($entrada)) {
                throw new DomainException('La proyección no recupera exactamente las entradas.');
            }
            if ($antesDeCerrar !== null) {
                $antesDeCerrar($this->pdo);
            }
            $this->pdo->prepare("UPDATE orion_f02.evaluaciones_compuestas SET estado='CERRADA' WHERE cod_evaluacion_experta=?")->execute([$codigo]);
            if (C::ordenar($this->proyeccion->recuperar($codigo)) !== C::ordenar($entrada)) {
                throw new DomainException('La proyección fue alterada durante el cierre.');
            }
            $this->pdo->commit();

            return true;
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function recuperar(string $codigo): array
    {
        $s = $this->pdo->prepare("SELECT e.*,r.* FROM orion_f02.evaluaciones_compuestas e JOIN orion_f02.releases_motor r USING(cod_release_motor) WHERE e.cod_evaluacion_experta=? AND e.estado='CERRADA'");
        $s->execute([$codigo]);
        $meta = $s->fetch(PDO::FETCH_ASSOC) ?: throw new DomainException('Evaluación completa no disponible.');

        return ['metadatos' => $meta, 'entradas' => $this->proyeccion->recuperar($codigo)];
    }
}
