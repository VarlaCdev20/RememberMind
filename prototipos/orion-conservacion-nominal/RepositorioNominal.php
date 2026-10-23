<?php

declare(strict_types=1);

namespace OrionNominal;

use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea;
use MotorExperto\soporte\conservacion\InstantaneaEvaluacionInstitucional;
use OrionFase02\Artefactos;
use OrionFase02\EntradasCompuestas;
use OrionFase02\GuardiaDesechable;
use PDO;
use Throwable;

/** Repositorio exclusivo del ensayo desechable; no se enlaza al contenedor institucional. */
final class RepositorioNominal
{
    private ProyeccionNominal $proyeccion;

    private array $mapa;

    public function __construct(private PDO $db, ?array $mapa = null)
    {
        self::verificarDestino($db);
        $this->mapa = $mapa ?? ContratoNominal::mapa();
        $this->proyeccion = new ProyeccionNominal($db, $this->mapa);
    }

    public static function verificarDestino(PDO $db): void
    {
        if ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $filas = $db->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC);
            if ($filas === [] || $filas[0]['name'] !== 'main' || array_filter($filas,
                static fn ($fila) => ! in_array($fila['name'], ['main', 'temp'], true) || $fila['file'] !== '') !== []) {
                throw new DomainException('El ensayo sólo admite SQLite en memoria.');
            }
        } elseif ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $nombre = (string) getenv('DB_DATABASE');
            if (getenv('RM_TEST_PROFILE') !== 'postgres'
                || ! preg_match('/^remembermind_test_[a-f0-9]{8}_testing$/D', $nombre)
                || getenv('DB_USERNAME') !== $nombre.'_runner'
                || ! in_array(getenv('DB_HOST'), ['127.0.0.1', 'localhost'], true)
                || getenv('DB_URL')) {
                throw new DomainException('El candidato requiere el runner PostgreSQL desechable propio.');
            }
            GuardiaDesechable::verificar($db);
        } else {
            throw new DomainException('El ensayo requiere un destino desechable comprobado.');
        }
    }

    public function registrarRelease(array $release): void
    {
        $datos = $release;
        $datos['huella_release'] = $datos['huella_manifiesto'];
        unset($datos['huella_manifiesto']);
        $columnas = ['huella_release', 'cod_release_motor', 'huella_zip', 'huella_adaptador', 'revision_base',
            'php_version', 'cantidad_archivos', 'huella_src', 'huella_runtime_zip', 'huella_php_ejecutable'];
        if (array_diff(array_keys($datos), $columnas) || array_diff($columnas, array_keys($datos))) {
            throw new DomainException('El release no corresponde al diccionario técnico nominal.');
        }
        $q = $this->db->prepare('INSERT INTO releases_nominales('.implode(',', $columnas).') VALUES ('.implode(',', array_fill(0, count($columnas), '?')).')');
        $q->execute(array_map(static fn ($c) => $datos[$c], $columnas));
    }

    public function guardar(string $codigo, string $solicitud, InstantaneaEvaluacionInstitucional $dto,
        array $fuente, ?callable $antesDeCerrar = null): bool
    {
        InstantaneaEvaluacionInstitucional::verificar($dto->contenido, $dto->huella);
        if (! preg_match('/^[A-Za-z0-9_-]{1,20}$/D', $codigo) || ! preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/D', $solicitud)
            || $dto->contenido['release'] === null || $this->db->inTransaction()) {
            throw new DomainException('Identidad, release o transacción de conservación incompatible.');
        }
        $entrada = ($this->mapa['contenido_completo'] ?? false) ? ContenidoCompleto::proyectar($dto->contenido)
            : EntradasCompuestas::proyectar($dto, $fuente);
        $huella = CodecInstantanea::huella(C::ordenar($entrada));
        $contexto = $dto->contenido['instrumental']['contenido']['contexto'];
        if ($codigo !== $contexto['evaluacion']) {
            throw new DomainException('La identidad SQL debe coincidir con la evaluación original.');
        }
        $filas = $this->proyeccion->proyectar($entrada, $codigo);
        $this->db->beginTransaction();
        try {
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                // Una solicitud aún sin fila también tiene exclusión mutua entre conexiones.
                $q = $this->db->prepare('SELECT pg_advisory_xact_lock(hashtextextended(?,0))');
                $q->execute([$solicitud]);
            }
            $q = $this->db->prepare('SELECT * FROM evaluaciones_nominales WHERE solicitud_uuid=? OR cod_evaluacion_experta=?');
            $q->execute([$solicitud, $codigo]);
            $previas = $q->fetchAll(PDO::FETCH_ASSOC);
            if ($previas !== []) {
                $previa = count($previas) === 1 ? $previas[0] : [];
                if (($previa['cod_evaluacion_experta'] ?? null) !== $codigo || $previa['solicitud_uuid'] !== $solicitud
                    || $previa['cod_residente'] !== $contexto['residente'] || $previa['cod_usuario'] !== $contexto['usuario_ejecucion']
                    || $previa['huella_compuesta'] !== $dto->huella || $previa['estado'] !== 'CERRADA'
                    || C::ordenar($this->proyeccion->recuperar($codigo)) !== C::ordenar($entrada)) {
                    throw new DomainException('La solicitud ya conserva otra identidad o contenido.');
                }
                $this->db->commit();

                return false;
            }
            $this->db->prepare("INSERT INTO evaluaciones_nominales(cod_evaluacion_experta,solicitud_uuid,cod_residente,cod_usuario,
                huella_entrada,huella_compuesta,huella_release,contrato,estado) VALUES (?,?,?,?,?,?,?,?,'ABIERTA')")
                ->execute([$codigo, $solicitud, $contexto['residente'], $contexto['usuario_ejecucion'], $huella,
                    $dto->huella, $dto->contenido['release']['huella'], $this->mapa['contrato_sql'] ?? ContratoNominal::VERSION]);
            $this->proyeccion->insertar($filas);
            if ($antesDeCerrar !== null) {
                $antesDeCerrar($this->db);
            }
            if (C::ordenar($this->proyeccion->recuperar($codigo)) !== C::ordenar($entrada)) {
                throw new DomainException('El escritor y lector nominales no conservan exactamente las entradas.');
            }
            $this->db->prepare("UPDATE evaluaciones_nominales SET estado='CERRADA' WHERE cod_evaluacion_experta=?")->execute([$codigo]);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Recupera datos originales desde SQL; no ejecuta reglas ni consulta fuentes clínicas actuales. */
    public function recuperarEntradas(string $codigo): array
    {
        if ($this->db->inTransaction()) {
            throw new DomainException('La lectura histórica necesita su propia transacción consistente.');
        }
        $this->db->beginTransaction();
        try {
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                $this->db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            }
            $q = $this->db->prepare("SELECT * FROM evaluaciones_nominales WHERE cod_evaluacion_experta=? AND estado='CERRADA'");
            $q->execute([$codigo]);
            $meta = $q->fetch(PDO::FETCH_ASSOC) ?: throw new DomainException('Evaluación nominal cerrada no disponible.');
            if ($meta['contrato'] !== ($this->mapa['contrato_sql'] ?? ContratoNominal::VERSION)) {
                throw new DomainException('La evaluación pertenece a otro contrato SQL.');
            }
            $entrada = $this->proyeccion->recuperar($codigo);
            if (! hash_equals($meta['huella_entrada'], CodecInstantanea::huella(C::ordenar($entrada)))) {
                throw new DomainException('Las entradas nominales no superan su comprobación de integridad.');
            }
            $q = $this->db->prepare('SELECT * FROM releases_nominales WHERE huella_release=?');
            $q->execute([$meta['huella_release']]);
            $release = $q->fetch(PDO::FETCH_ASSOC) ?: throw new DomainException('Release histórico no disponible.');
            $release['huella_manifiesto'] = $release['huella_release'];
            $release['cantidad_archivos'] = (int) $release['cantidad_archivos'];
            unset($release['huella_release']);
            $this->db->commit();

            return ['metadatos' => $meta, 'entradas' => $entrada, 'release' => $release];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /** Lectura de todas las fuentes y salidas almacenadas. No extrae medidas, interpreta ni reproduce. */
    public function recuperarCompleta(string $codigo): InstantaneaEvaluacionInstitucional
    {
        if (($this->mapa['contenido_completo'] ?? false) !== true) {
            throw new DomainException('El contrato de entradas no conserva la salida independiente.');
        }
        $recuperada = $this->recuperarEntradas($codigo);
        $contenido = ContenidoCompleto::reconstruir($recuperada['entradas']);
        $contexto = $contenido['instrumental']['contenido']['contexto'];
        if ($contexto['evaluacion'] !== $codigo || $contexto['residente'] !== $recuperada['metadatos']['cod_residente']
            || $contexto['usuario_ejecucion'] !== $recuperada['metadatos']['cod_usuario']
            || ($contenido['release']['huella'] ?? null) !== $recuperada['metadatos']['huella_release']) {
            throw new DomainException('El contenido histórico no corresponde a su identidad SQL.');
        }

        return InstantaneaEvaluacionInstitucional::verificarIntegridad($contenido, $recuperada['metadatos']['huella_compuesta']);
    }

    /** Replay separado con PHP, motor y adaptador realmente retenidos por el ensayo F02. */
    public function reproducir(string $codigo, Artefactos $artefactos, string $destino): array
    {
        $recuperada = $this->recuperarEntradas($codigo);
        $entradas = ($this->mapa['contenido_completo'] ?? false)
            ? ContenidoCompleto::entradasReplay(ContenidoCompleto::reconstruir($recuperada['entradas'])) : $recuperada['entradas'];
        $replay = $artefactos->reproducir($entradas, $recuperada['release'], 'primario', $destino);
        if (! hash_equals($recuperada['metadatos']['huella_compuesta'], $replay['huella'])) {
            throw new DomainException('El replay nominal no reproduce la huella histórica.');
        }

        return $replay;
    }
}
