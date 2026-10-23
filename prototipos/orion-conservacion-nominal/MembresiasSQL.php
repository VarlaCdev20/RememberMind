<?php

declare(strict_types=1);

namespace OrionNominal;

use DateTimeImmutable;
use DateTimeZone;
use DomainException;
use MotorExperto\soporte\conservacion\CapturaInstrumentalCanonica as C;
use MotorExperto\soporte\conservacion\CodecInstantanea as Codec;
use MotorExperto\soporte\conservacion\InstantaneaPetersen;
use MotorExperto\soporte\conservacion\MembresiasOriginalesPetersen as M;
use PDO;
use Throwable;

/** Submodelo de dos relaciones en ensayo. No es el repositorio del DTO compuesto ni una migración institucional. */
final class MembresiasSQL
{
    public function __construct(private PDO $db, private DateTimeZone $zonaOriginal)
    {
        RepositorioNominal::verificarDestino($db);
    }

    public function instalar(): void
    {
        $this->db->exec(self::ddl($this->db->getAttribute(PDO::ATTR_DRIVER_NAME)));
    }

    public static function ddl(string $motor): string
    {
        if (! in_array($motor, ['sqlite', 'pgsql'], true)) {
            throw new DomainException('Motor desechable no verificado.');
        }
        $sql = [
            'ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_estado VARCHAR(7)',
            'ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_huella CHAR(64)',
            'ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_juicios BIGINT',
            'ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_soportes BIGINT',
            'CREATE TABLE juicios_petersen_adquiridos (
                cod_juicio_adquirido VARCHAR(20) PRIMARY KEY,
                cod_evaluacion_experta VARCHAR(20) NOT NULL,
                cod_valoracion_petersen VARCHAR(20) NOT NULL,
                fecha_hora_iso TEXT NOT NULL,
                registrado_en_iso TEXT NOT NULL,
                orden_procedencia BIGINT NOT NULL CHECK(orden_procedencia>=0),
                orden_membresia BIGINT NOT NULL CHECK(orden_membresia>=0),
                cantidad_soportes BIGINT NOT NULL CHECK(cantidad_soportes>=0),
                UNIQUE(cod_evaluacion_experta,cod_valoracion_petersen),
                UNIQUE(cod_evaluacion_experta,orden_procedencia),
                UNIQUE(cod_evaluacion_experta,orden_membresia),
                FOREIGN KEY(cod_evaluacion_experta) REFERENCES evaluaciones_expertas(cod_evaluacion_experta) ON DELETE RESTRICT,
                FOREIGN KEY(cod_valoracion_petersen) REFERENCES valoraciones_petersen_originales(cod_valoracion_petersen) ON DELETE RESTRICT
            )',
            'CREATE TABLE soportes_petersen_adquiridos (
                cod_soporte_adquirido VARCHAR(20) PRIMARY KEY,
                cod_juicio_adquirido VARCHAR(20) NOT NULL,
                cod_soporte_petersen VARCHAR(20) NOT NULL,
                orden_membresia BIGINT NOT NULL CHECK(orden_membresia>=0),
                UNIQUE(cod_juicio_adquirido,cod_soporte_petersen),
                UNIQUE(cod_juicio_adquirido,orden_membresia),
                FOREIGN KEY(cod_juicio_adquirido) REFERENCES juicios_petersen_adquiridos(cod_juicio_adquirido) ON DELETE RESTRICT,
                FOREIGN KEY(cod_soporte_petersen) REFERENCES soportes_valoracion_petersen(cod_soporte_petersen) ON DELETE RESTRICT
            )',
            'CREATE INDEX mp_juicio_original_i ON juicios_petersen_adquiridos(cod_valoracion_petersen)',
            'CREATE INDEX mp_soporte_original_i ON soportes_petersen_adquiridos(cod_soporte_petersen)',
        ];
        $incompleta = "(NEW.membresias_petersen_estado='CERRADA' AND (
            NEW.membresias_petersen_huella IS NULL OR length(NEW.membresias_petersen_huella)<>64
            OR NEW.membresias_petersen_juicios IS NULL OR NEW.membresias_petersen_juicios<0
            OR NEW.membresias_petersen_soportes IS NULL OR NEW.membresias_petersen_soportes<0
            OR (SELECT count(*) FROM juicios_petersen_adquiridos j WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>NEW.membresias_petersen_juicios
            OR (SELECT count(*) FROM soportes_petersen_adquiridos s JOIN juicios_petersen_adquiridos j ON j.cod_juicio_adquirido=s.cod_juicio_adquirido
                WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta)<>NEW.membresias_petersen_soportes
            OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j JOIN valoraciones_petersen_originales v ON v.cod_valoracion_petersen=j.cod_valoracion_petersen
                WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND v.cod_residente<>NEW.cod_residente)
            OR EXISTS(SELECT 1 FROM soportes_petersen_adquiridos s JOIN juicios_petersen_adquiridos j ON j.cod_juicio_adquirido=s.cod_juicio_adquirido
                JOIN soportes_valoracion_petersen o ON o.cod_soporte_petersen=s.cod_soporte_petersen
                WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND j.cod_valoracion_petersen<>o.cod_valoracion_petersen)
            OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (
                (SELECT count(*) FROM soportes_petersen_adquiridos s WHERE s.cod_juicio_adquirido=j.cod_juicio_adquirido)<>j.cantidad_soportes
                OR EXISTS(SELECT 1 FROM soportes_petersen_adquiridos s WHERE s.cod_juicio_adquirido=j.cod_juicio_adquirido AND s.orden_membresia>=j.cantidad_soportes)))
            OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (j.orden_procedencia>=NEW.membresias_petersen_juicios OR j.orden_membresia>=NEW.membresias_petersen_juicios))))";
        $tablas = ['juicios_petersen_adquiridos', 'soportes_petersen_adquiridos'];
        $transicion = "(OLD.membresias_petersen_estado IS NULL AND NEW.membresias_petersen_estado IS NOT NULL AND NEW.membresias_petersen_estado<>'ABIERTA')"
            ." OR (OLD.membresias_petersen_estado='ABIERTA' AND (NEW.membresias_petersen_estado IS NULL OR NEW.membresias_petersen_estado<>'CERRADA'))";
        $bloque = '((NEW.membresias_petersen_estado IS NULL AND (NEW.membresias_petersen_huella IS NOT NULL OR NEW.membresias_petersen_juicios IS NOT NULL OR NEW.membresias_petersen_soportes IS NOT NULL))
            OR (NEW.membresias_petersen_estado IS NOT NULL AND (NEW.membresias_petersen_huella IS NULL OR length(NEW.membresias_petersen_huella)<>64
                OR NEW.membresias_petersen_juicios IS NULL OR NEW.membresias_petersen_juicios<0 OR NEW.membresias_petersen_soportes IS NULL OR NEW.membresias_petersen_soportes<0)))';
        if ($motor === 'sqlite') {
            foreach (['INSERT', 'UPDATE'] as $evento) {
                $sql[] = 'CREATE TRIGGER mp_mismo_residente_'.strtolower($evento)." BEFORE $evento ON juicios_petersen_adquiridos
                    WHEN NOT EXISTS(SELECT 1 FROM evaluaciones_expertas e JOIN valoraciones_petersen_originales v ON v.cod_residente=e.cod_residente
                        WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND v.cod_valoracion_petersen=NEW.cod_valoracion_petersen)
                        OR EXISTS(SELECT 1 FROM soportes_petersen_adquiridos s JOIN soportes_valoracion_petersen o ON o.cod_soporte_petersen=s.cod_soporte_petersen
                            WHERE s.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND o.cod_valoracion_petersen<>NEW.cod_valoracion_petersen)
                    BEGIN SELECT RAISE(ABORT,'Juicio de otro residente'); END";
                $sql[] = 'CREATE TRIGGER mp_mismo_juicio_'.strtolower($evento)." BEFORE $evento ON soportes_petersen_adquiridos
                    WHEN NOT EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j JOIN soportes_valoracion_petersen s ON s.cod_valoracion_petersen=j.cod_valoracion_petersen
                        WHERE j.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND s.cod_soporte_petersen=NEW.cod_soporte_petersen)
                    BEGIN SELECT RAISE(ABORT,'Soporte de otro juicio'); END";
            }
            foreach ($tablas as $tabla) {
                foreach (['INSERT' => 'NEW', 'UPDATE' => 'OLD', 'DELETE' => 'OLD'] as $evento => $ref) {
                    $id = $tabla === 'juicios_petersen_adquiridos' ? "$ref.cod_evaluacion_experta"
                        : "(SELECT cod_evaluacion_experta FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=$ref.cod_juicio_adquirido)";
                    $nuevo = $tabla === 'juicios_petersen_adquiridos' ? 'NEW.cod_evaluacion_experta'
                        : '(SELECT cod_evaluacion_experta FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=NEW.cod_juicio_adquirido)';
                    $extra = $evento === 'UPDATE' ? " OR (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=$nuevo) IS NOT 'ABIERTA'" : '';
                    $sql[] = "CREATE TRIGGER {$tabla}_mp_".strtolower($evento)." BEFORE $evento ON $tabla
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=$id) IS NOT 'ABIERTA'$extra
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END";
                }
            }
            $sql[] = "CREATE TRIGGER mp_guardar_cabecera BEFORE UPDATE ON evaluaciones_expertas
                WHEN (OLD.membresias_petersen_estado='CERRADA' AND (
                    NEW.membresias_petersen_estado IS NOT OLD.membresias_petersen_estado OR NEW.membresias_petersen_huella IS NOT OLD.membresias_petersen_huella
                    OR NEW.membresias_petersen_juicios IS NOT OLD.membresias_petersen_juicios OR NEW.membresias_petersen_soportes IS NOT OLD.membresias_petersen_soportes
                    OR NEW.cod_residente IS NOT OLD.cod_residente OR NEW.cod_evaluacion_experta IS NOT OLD.cod_evaluacion_experta))
                    OR (NEW.membresias_petersen_estado IS NOT NULL AND NEW.membresias_petersen_estado NOT IN ('ABIERTA','CERRADA')) OR $transicion OR $bloque OR $incompleta
                BEGIN SELECT RAISE(ABORT,'Cierre de membresías incompatible'); END";
            $sql[] = "CREATE TRIGGER mp_sin_borrado BEFORE DELETE ON evaluaciones_expertas WHEN OLD.membresias_petersen_estado IS NOT NULL
                BEGIN SELECT RAISE(ABORT,'No se elimina una adquisición'); END";
            $sql[] = "CREATE TRIGGER mp_nueva_cabecera BEFORE INSERT ON evaluaciones_expertas
                WHEN NEW.membresias_petersen_estado IS NOT NULL OR NEW.membresias_petersen_huella IS NOT NULL
                    OR NEW.membresias_petersen_juicios IS NOT NULL OR NEW.membresias_petersen_soportes IS NOT NULL
                    OR EXISTS(SELECT 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta AND membresias_petersen_estado IS NOT NULL)
                BEGIN SELECT RAISE(ABORT,'La adquisición comienza en una evaluación existente'); END";
        } else {
            $sql[] = "CREATE FUNCTION mp_proteger_hija() RETURNS trigger LANGUAGE plpgsql AS \$\$
                DECLARE codigo TEXT; anterior TEXT; actual TEXT;
                BEGIN
                    IF TG_TABLE_NAME='juicios_petersen_adquiridos' THEN
                        PERFORM pg_advisory_xact_lock(hashtextextended(CASE WHEN TG_OP='DELETE' THEN OLD.cod_juicio_adquirido ELSE NEW.cod_juicio_adquirido END,7));
                        codigo:=CASE WHEN TG_OP='DELETE' THEN OLD.cod_evaluacion_experta ELSE NEW.cod_evaluacion_experta END;
                        IF TG_OP='UPDATE' THEN anterior:=OLD.cod_evaluacion_experta; END IF;
                    ELSE
                        -- La PK del padre puede existir todavía sólo en la transacción que lo está cerrando.
                        -- Se bloquea por su identidad, antes de buscar una fila aún no visible ni comprobar la FK.
                        PERFORM pg_advisory_xact_lock(hashtextextended(CASE WHEN TG_OP='DELETE' THEN OLD.cod_juicio_adquirido ELSE NEW.cod_juicio_adquirido END,7));
                        SELECT cod_evaluacion_experta INTO codigo FROM juicios_petersen_adquiridos
                            WHERE cod_juicio_adquirido=CASE WHEN TG_OP='DELETE' THEN OLD.cod_juicio_adquirido ELSE NEW.cod_juicio_adquirido END;
                        IF TG_OP='UPDATE' THEN
                            SELECT cod_evaluacion_experta INTO anterior FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=OLD.cod_juicio_adquirido;
                        END IF;
                        IF TG_OP<>'DELETE' AND NOT EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j JOIN soportes_valoracion_petersen s
                            ON s.cod_valoracion_petersen=j.cod_valoracion_petersen WHERE j.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND s.cod_soporte_petersen=NEW.cod_soporte_petersen)
                            THEN RAISE EXCEPTION 'Soporte de otro juicio' USING ERRCODE='23503'; END IF;
                    END IF;
                    SELECT membresias_petersen_estado INTO actual FROM evaluaciones_expertas WHERE cod_evaluacion_experta=codigo FOR UPDATE;
                    IF actual IS DISTINCT FROM 'ABIERTA' THEN RAISE EXCEPTION 'Membresía fuera de adquisición abierta' USING ERRCODE='55000'; END IF;
                    IF TG_TABLE_NAME='juicios_petersen_adquiridos' AND TG_OP<>'DELETE' THEN
                        IF NOT EXISTS(SELECT 1 FROM evaluaciones_expertas e JOIN valoraciones_petersen_originales v ON v.cod_residente=e.cod_residente
                            WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND v.cod_valoracion_petersen=NEW.cod_valoracion_petersen)
                            OR EXISTS(SELECT 1 FROM soportes_petersen_adquiridos s JOIN soportes_valoracion_petersen o ON o.cod_soporte_petersen=s.cod_soporte_petersen
                                WHERE s.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND o.cod_valoracion_petersen<>NEW.cod_valoracion_petersen)
                            THEN RAISE EXCEPTION 'Juicio de otro residente o soporte incompatible' USING ERRCODE='23514'; END IF;
                    END IF;
                    IF TG_OP='UPDATE' AND anterior<>codigo THEN
                        SELECT membresias_petersen_estado INTO actual FROM evaluaciones_expertas WHERE cod_evaluacion_experta=anterior FOR UPDATE;
                        IF actual IS DISTINCT FROM 'ABIERTA' THEN RAISE EXCEPTION 'Membresía original cerrada' USING ERRCODE='55000'; END IF;
                    END IF;
                    IF TG_OP='DELETE' THEN RETURN OLD; END IF; RETURN NEW;
                END \$\$";
            foreach ($tablas as $tabla) {
                $sql[] = "CREATE TRIGGER {$tabla}_mp_historia BEFORE INSERT OR UPDATE OR DELETE ON $tabla FOR EACH ROW EXECUTE FUNCTION mp_proteger_hija()";
            }
            $sql[] = "CREATE FUNCTION mp_proteger_cabecera() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN
                    IF (OLD.membresias_petersen_estado='CERRADA' AND (
                        NEW.membresias_petersen_estado IS DISTINCT FROM OLD.membresias_petersen_estado OR NEW.membresias_petersen_huella IS DISTINCT FROM OLD.membresias_petersen_huella
                        OR NEW.membresias_petersen_juicios IS DISTINCT FROM OLD.membresias_petersen_juicios OR NEW.membresias_petersen_soportes IS DISTINCT FROM OLD.membresias_petersen_soportes
                        OR NEW.cod_residente IS DISTINCT FROM OLD.cod_residente OR NEW.cod_evaluacion_experta IS DISTINCT FROM OLD.cod_evaluacion_experta))
                        OR (NEW.membresias_petersen_estado IS NOT NULL AND NEW.membresias_petersen_estado NOT IN ('ABIERTA','CERRADA')) OR $transicion OR $bloque OR $incompleta
                        THEN RAISE EXCEPTION 'Cierre de membresías incompatible' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END \$\$";
            $sql[] = 'CREATE TRIGGER mp_guardar_cabecera BEFORE UPDATE ON evaluaciones_expertas FOR EACH ROW EXECUTE FUNCTION mp_proteger_cabecera()';
            $sql[] = "CREATE FUNCTION mp_guardar_identidad() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN
                    IF TG_OP='DELETE' THEN
                        IF OLD.membresias_petersen_estado IS NOT NULL THEN RAISE EXCEPTION 'No se elimina una adquisición' USING ERRCODE='55000'; END IF;
                        RETURN OLD;
                    END IF;
                    IF NEW.membresias_petersen_estado IS NOT NULL OR NEW.membresias_petersen_huella IS NOT NULL
                        OR NEW.membresias_petersen_juicios IS NOT NULL OR NEW.membresias_petersen_soportes IS NOT NULL
                        THEN RAISE EXCEPTION 'La adquisición comienza en una evaluación existente' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END \$\$";
            $sql[] = 'CREATE TRIGGER mp_guardar_identidad BEFORE INSERT OR DELETE ON evaluaciones_expertas FOR EACH ROW EXECUTE FUNCTION mp_guardar_identidad()';
            $sql[] = "CREATE FUNCTION mp_exigir_cierre() RETURNS trigger LANGUAGE plpgsql AS \$\$
                BEGIN
                    IF EXISTS(SELECT 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta AND membresias_petersen_estado='ABIERTA')
                        THEN RAISE EXCEPTION 'No se confirma una adquisición parcial' USING ERRCODE='23514'; END IF;
                    RETURN NULL;
                END \$\$";
            $sql[] = 'CREATE CONSTRAINT TRIGGER mp_exigir_cierre AFTER UPDATE ON evaluaciones_expertas DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION mp_exigir_cierre()';
            $sql[] = "CREATE FUNCTION mp_sin_truncate() RETURNS trigger LANGUAGE plpgsql AS \$\$ BEGIN RAISE EXCEPTION 'No se truncan membresías' USING ERRCODE='55000'; END \$\$";
            foreach ($tablas as $tabla) {
                $sql[] = "CREATE TRIGGER {$tabla}_mp_truncate BEFORE TRUNCATE ON $tabla FOR EACH STATEMENT EXECUTE FUNCTION mp_sin_truncate()";
            }
        }

        return implode(";\n", $sql).";\n";
    }

    /** Retira únicamente el ensayo desechable, incluso poblado; nunca es un rollback institucional. */
    public function retirarEnsayo(): void
    {
        $pgsql = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
        $this->db->exec('DROP TABLE soportes_petersen_adquiridos; DROP TABLE juicios_petersen_adquiridos');
        foreach (['mp_guardar_cabecera', ...($pgsql ? ['mp_guardar_identidad', 'mp_exigir_cierre'] : ['mp_sin_borrado', 'mp_nueva_cabecera'])] as $trigger) {
            $this->db->exec('DROP TRIGGER '.$trigger.($pgsql ? ' ON evaluaciones_expertas' : ''));
        }
        if ($pgsql) {
            foreach (['mp_proteger_hija', 'mp_proteger_cabecera', 'mp_guardar_identidad', 'mp_exigir_cierre', 'mp_sin_truncate'] as $funcion) {
                $this->db->exec('DROP FUNCTION '.$funcion.'()');
            }
        }
        foreach (['estado', 'huella', 'juicios', 'soportes'] as $campo) {
            $this->db->exec('ALTER TABLE evaluaciones_expertas DROP COLUMN membresias_petersen_'.$campo);
        }
    }

    public static function proyectar(InstantaneaPetersen $dto): array
    {
        if ($dto->contenido['contrato'] !== InstantaneaPetersen::CONTRATO_MEMBRESIAS) {
            throw new DomainException('La V1 no contiene los vínculos originales completos.');
        }

        return array_intersect_key($dto->contenido, array_flip(['procedencia_valoraciones', 'membresias_soportes', 'soportes_originales']));
    }

    /** Sólo conserva este submodelo; no modifica el estado del resultado experto ni permite revisiones. */
    public function guardar(string $evaluacion, InstantaneaPetersen $dto, ?callable $antesDeCerrar = null): bool
    {
        InstantaneaPetersen::verificar($dto->contenido, $dto->huella);
        $p = self::proyectar($dto);
        $hash = Codec::huella(C::ordenar($p));
        if ($this->db->inTransaction()) {
            throw new DomainException('La adquisición de membresías necesita su propia transacción.');
        }
        $this->db->beginTransaction();
        try {
            $bloqueo = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql' ? ' FOR UPDATE' : '';
            $q = $this->db->prepare('SELECT * FROM evaluaciones_expertas WHERE cod_evaluacion_experta=?'.$bloqueo);
            $q->execute([$evaluacion]);
            $cab = $q->fetch(PDO::FETCH_ASSOC) ?: throw new DomainException('No existe la evaluación propietaria.');
            if ($cab['cod_residente'] !== $dto->contenido['contexto']['residente']) {
                throw new DomainException('La evaluación pertenece a otro residente.');
            }
            if ($cab['membresias_petersen_estado'] === 'CERRADA') {
                if (! hash_equals($cab['membresias_petersen_huella'], $hash) || $this->leerDentro($evaluacion) !== C::ordenar($p)) {
                    throw new DomainException('La evaluación conserva otra membresía histórica.');
                }
                $this->db->commit();

                return false;
            }
            if ($cab['membresias_petersen_estado'] !== null) {
                throw new DomainException('Adquisición parcial fuera de una transacción propia.');
            }
            $this->db->prepare("UPDATE evaluaciones_expertas SET membresias_petersen_estado='ABIERTA',membresias_petersen_huella=?,
                membresias_petersen_juicios=?,membresias_petersen_soportes=? WHERE cod_evaluacion_experta=?")
                ->execute([$hash, count($p['procedencia_valoraciones']), count($p['soportes_originales']), $evaluacion]);
            $membresias = [];
            foreach ($p['membresias_soportes'] as $orden => $m) {
                $membresias[$m['valoracion']] = ['orden' => $orden, 'soportes' => $m['soportes']];
            }
            $juiciosAdquiridos = [];
            foreach ($p['procedencia_valoraciones'] as $orden => $original) {
                $q = $this->db->prepare('SELECT '.implode(',', array_keys($original))
                    .' FROM valoraciones_petersen_originales WHERE cod_valoracion_petersen=?');
                $q->execute([$original['cod_valoracion_petersen']]);
                $actual = $q->fetch(PDO::FETCH_ASSOC);
                if ($actual === false || C::ordenar(array_diff_key($actual, array_flip(['fecha_hora', 'registrado_en'])))
                    !== C::ordenar(array_diff_key($original, array_flip(['fecha_hora', 'registrado_en'])))) {
                    throw new DomainException('La procedencia adquirida difiere de su juicio original inmutable.');
                }
                foreach (['fecha_hora', 'registrado_en'] as $fecha) {
                    $sql = new DateTimeImmutable($actual[$fecha], $this->zonaOriginal);
                    $capturada = new DateTimeImmutable($original[$fecha]);
                    if ($sql->format('U.u') !== $capturada->format('U.u')) {
                        throw new DomainException('El instante adquirido difiere de la fecha original inmutable.');
                    }
                }
                $fila = ['cod_juicio_adquirido' => bin2hex(random_bytes(10)), 'cod_evaluacion_experta' => $evaluacion,
                    'cod_valoracion_petersen' => $original['cod_valoracion_petersen'],
                    'fecha_hora_iso' => $original['fecha_hora'], 'registrado_en_iso' => $original['registrado_en']];
                $m = $membresias[$original['cod_valoracion_petersen']];
                $juiciosAdquiridos[$original['cod_valoracion_petersen']] = $fila['cod_juicio_adquirido'];
                $this->insertar('juicios_petersen_adquiridos', $fila + ['orden_procedencia' => $orden,
                    'orden_membresia' => $m['orden'], 'cantidad_soportes' => count($m['soportes'])]);
            }
            foreach ($p['soportes_originales'] as $s) {
                $q = $this->db->prepare('SELECT '.implode(',', M::CAMPOS).' FROM soportes_valoracion_petersen WHERE cod_soporte_petersen=?');
                $q->execute([$s['cod_soporte_petersen']]);
                $actual = $q->fetch(PDO::FETCH_ASSOC);
                if ($actual !== false) {
                    $actual['orden'] = (int) $actual['orden'];
                }
                if ($actual === false || C::ordenar($actual) !== C::ordenar($s)) {
                    throw new DomainException('El soporte adquirido difiere de su vínculo original inmutable.');
                }
                $posicion = array_search($s['cod_soporte_petersen'], $membresias[$s['cod_valoracion_petersen']]['soportes'], true);
                $this->insertar('soportes_petersen_adquiridos', ['cod_soporte_adquirido' => bin2hex(random_bytes(10)),
                    'cod_juicio_adquirido' => $juiciosAdquiridos[$s['cod_valoracion_petersen']],
                    'cod_soporte_petersen' => $s['cod_soporte_petersen'], 'orden_membresia' => $posicion]);
            }
            if ($antesDeCerrar !== null) {
                $antesDeCerrar($this->db);
            }
            if ($this->leerDentro($evaluacion) !== C::ordenar($p)) {
                throw new DomainException('La lectura SQL de membresías no coincide con la captura original.');
            }
            $this->db->prepare("UPDATE evaluaciones_expertas SET membresias_petersen_estado='CERRADA' WHERE cod_evaluacion_experta=?")->execute([$evaluacion]);
            $this->db->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function recuperar(string $evaluacion): array
    {
        if ($this->db->inTransaction()) {
            throw new DomainException('La lectura histórica necesita su propia transacción.');
        }
        $this->db->beginTransaction();
        try {
            if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
                $this->db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
            }
            $q = $this->db->prepare("SELECT membresias_petersen_huella FROM evaluaciones_expertas WHERE cod_evaluacion_experta=? AND membresias_petersen_estado='CERRADA'");
            $q->execute([$evaluacion]);
            $huella = $q->fetchColumn();
            if (! is_string($huella)) {
                throw new DomainException('La evaluación no conserva membresías cerradas.');
            }
            $p = $this->leerDentro($evaluacion);
            if (! hash_equals($huella, Codec::huella($p))) {
                throw new DomainException('Las membresías no superan la verificación de integridad.');
            }
            $this->db->commit();

            return $p;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function leerDentro(string $evaluacion): array
    {
        $q = $this->db->prepare('SELECT j.*,v.cod_residente,v.cod_personal,v.cod_usuario_registro,v.elemento,v.tipo_registro,
            v.cod_valoracion_anterior,v.cod_documento_clinico,v.localizador_fuente FROM juicios_petersen_adquiridos j
            JOIN valoraciones_petersen_originales v ON v.cod_valoracion_petersen=j.cod_valoracion_petersen
            WHERE j.cod_evaluacion_experta=? ORDER BY j.orden_procedencia');
        $q->execute([$evaluacion]);
        $juicios = $q->fetchAll(PDO::FETCH_ASSOC);
        $procedencia = $membresias = [];
        foreach ($juicios as $fila) {
            $p = array_intersect_key($fila, array_flip(['cod_valoracion_petersen', 'cod_residente', 'cod_personal', 'cod_usuario_registro',
                'elemento', 'tipo_registro', 'cod_valoracion_anterior', 'cod_documento_clinico', 'localizador_fuente']));
            $procedencia[] = $p + ['fecha_hora' => $fila['fecha_hora_iso'], 'registrado_en' => $fila['registrado_en_iso']];
            $membresias[(int) $fila['orden_membresia']] = ['valoracion' => $fila['cod_valoracion_petersen'], 'soportes' => []];
        }
        ksort($membresias, SORT_NUMERIC);
        $q = $this->db->prepare('SELECT s.*,j.cod_valoracion_petersen AS propietario_adquirido,m.orden_membresia
            FROM soportes_petersen_adquiridos m JOIN juicios_petersen_adquiridos j ON j.cod_juicio_adquirido=m.cod_juicio_adquirido
            JOIN soportes_valoracion_petersen s ON s.cod_soporte_petersen=m.cod_soporte_petersen
            AND s.cod_valoracion_petersen=j.cod_valoracion_petersen WHERE j.cod_evaluacion_experta=? ORDER BY j.orden_membresia,m.orden_membresia');
        $q->execute([$evaluacion]);
        $soportes = [];
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $fila['orden'] = (int) $fila['orden'];
            $soportes[] = array_intersect_key($fila, array_flip(M::CAMPOS));
            foreach ($membresias as &$m) {
                if ($m['valoracion'] === $fila['propietario_adquirido']) {
                    $m['soportes'][(int) $fila['orden_membresia']] = $fila['cod_soporte_petersen'];
                }
            }
            unset($m);
        }
        foreach ($membresias as &$m) {
            ksort($m['soportes'], SORT_NUMERIC);
            $m['soportes'] = array_values($m['soportes']);
        }
        unset($m);

        return C::ordenar(['procedencia_valoraciones' => $procedencia, 'membresias_soportes' => array_values($membresias), 'soportes_originales' => $soportes]);
    }

    private function insertar(string $tabla, array $fila): void
    {
        $q = $this->db->prepare('INSERT INTO '.$tabla.'('.implode(',', array_keys($fila)).') VALUES ('.implode(',', array_fill(0, count($fila), '?')).')');
        $q->execute(array_values($fila));
    }
}
