ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_estado VARCHAR(7);
ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_huella CHAR(64);
ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_juicios BIGINT;
ALTER TABLE evaluaciones_expertas ADD COLUMN membresias_petersen_soportes BIGINT;
CREATE TABLE juicios_petersen_adquiridos (
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
            );
CREATE TABLE soportes_petersen_adquiridos (
                cod_soporte_adquirido VARCHAR(20) PRIMARY KEY,
                cod_juicio_adquirido VARCHAR(20) NOT NULL,
                cod_soporte_petersen VARCHAR(20) NOT NULL,
                orden_membresia BIGINT NOT NULL CHECK(orden_membresia>=0),
                UNIQUE(cod_juicio_adquirido,cod_soporte_petersen),
                UNIQUE(cod_juicio_adquirido,orden_membresia),
                FOREIGN KEY(cod_juicio_adquirido) REFERENCES juicios_petersen_adquiridos(cod_juicio_adquirido) ON DELETE RESTRICT,
                FOREIGN KEY(cod_soporte_petersen) REFERENCES soportes_valoracion_petersen(cod_soporte_petersen) ON DELETE RESTRICT
            );
CREATE INDEX mp_juicio_original_i ON juicios_petersen_adquiridos(cod_valoracion_petersen);
CREATE INDEX mp_soporte_original_i ON soportes_petersen_adquiridos(cod_soporte_petersen);
CREATE FUNCTION mp_proteger_hija() RETURNS trigger LANGUAGE plpgsql AS $$
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
                END $$;
CREATE TRIGGER juicios_petersen_adquiridos_mp_historia BEFORE INSERT OR UPDATE OR DELETE ON juicios_petersen_adquiridos FOR EACH ROW EXECUTE FUNCTION mp_proteger_hija();
CREATE TRIGGER soportes_petersen_adquiridos_mp_historia BEFORE INSERT OR UPDATE OR DELETE ON soportes_petersen_adquiridos FOR EACH ROW EXECUTE FUNCTION mp_proteger_hija();
CREATE FUNCTION mp_proteger_cabecera() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF (OLD.membresias_petersen_estado='CERRADA' AND (
                        NEW.membresias_petersen_estado IS DISTINCT FROM OLD.membresias_petersen_estado OR NEW.membresias_petersen_huella IS DISTINCT FROM OLD.membresias_petersen_huella
                        OR NEW.membresias_petersen_juicios IS DISTINCT FROM OLD.membresias_petersen_juicios OR NEW.membresias_petersen_soportes IS DISTINCT FROM OLD.membresias_petersen_soportes
                        OR NEW.cod_residente IS DISTINCT FROM OLD.cod_residente OR NEW.cod_evaluacion_experta IS DISTINCT FROM OLD.cod_evaluacion_experta))
                        OR (NEW.membresias_petersen_estado IS NOT NULL AND NEW.membresias_petersen_estado NOT IN ('ABIERTA','CERRADA')) OR (OLD.membresias_petersen_estado IS NULL AND NEW.membresias_petersen_estado IS NOT NULL AND NEW.membresias_petersen_estado<>'ABIERTA') OR (OLD.membresias_petersen_estado='ABIERTA' AND (NEW.membresias_petersen_estado IS NULL OR NEW.membresias_petersen_estado<>'CERRADA')) OR ((NEW.membresias_petersen_estado IS NULL AND (NEW.membresias_petersen_huella IS NOT NULL OR NEW.membresias_petersen_juicios IS NOT NULL OR NEW.membresias_petersen_soportes IS NOT NULL))
            OR (NEW.membresias_petersen_estado IS NOT NULL AND (NEW.membresias_petersen_huella IS NULL OR length(NEW.membresias_petersen_huella)<>64
                OR NEW.membresias_petersen_juicios IS NULL OR NEW.membresias_petersen_juicios<0 OR NEW.membresias_petersen_soportes IS NULL OR NEW.membresias_petersen_soportes<0))) OR (NEW.membresias_petersen_estado='CERRADA' AND (
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
            OR EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j WHERE j.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND (j.orden_procedencia>=NEW.membresias_petersen_juicios OR j.orden_membresia>=NEW.membresias_petersen_juicios))))
                        THEN RAISE EXCEPTION 'Cierre de membresías incompatible' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END $$;
CREATE TRIGGER mp_guardar_cabecera BEFORE UPDATE ON evaluaciones_expertas FOR EACH ROW EXECUTE FUNCTION mp_proteger_cabecera();
CREATE FUNCTION mp_guardar_identidad() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF TG_OP='DELETE' THEN
                        IF OLD.membresias_petersen_estado IS NOT NULL THEN RAISE EXCEPTION 'No se elimina una adquisición' USING ERRCODE='55000'; END IF;
                        RETURN OLD;
                    END IF;
                    IF NEW.membresias_petersen_estado IS NOT NULL OR NEW.membresias_petersen_huella IS NOT NULL
                        OR NEW.membresias_petersen_juicios IS NOT NULL OR NEW.membresias_petersen_soportes IS NOT NULL
                        THEN RAISE EXCEPTION 'La adquisición comienza en una evaluación existente' USING ERRCODE='23514'; END IF;
                    RETURN NEW;
                END $$;
CREATE TRIGGER mp_guardar_identidad BEFORE INSERT OR DELETE ON evaluaciones_expertas FOR EACH ROW EXECUTE FUNCTION mp_guardar_identidad();
CREATE FUNCTION mp_exigir_cierre() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    IF EXISTS(SELECT 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta AND membresias_petersen_estado='ABIERTA')
                        THEN RAISE EXCEPTION 'No se confirma una adquisición parcial' USING ERRCODE='23514'; END IF;
                    RETURN NULL;
                END $$;
CREATE CONSTRAINT TRIGGER mp_exigir_cierre AFTER UPDATE ON evaluaciones_expertas DEFERRABLE INITIALLY DEFERRED FOR EACH ROW EXECUTE FUNCTION mp_exigir_cierre();
CREATE FUNCTION mp_sin_truncate() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'No se truncan membresías' USING ERRCODE='55000'; END $$;
CREATE TRIGGER juicios_petersen_adquiridos_mp_truncate BEFORE TRUNCATE ON juicios_petersen_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION mp_sin_truncate();
CREATE TRIGGER soportes_petersen_adquiridos_mp_truncate BEFORE TRUNCATE ON soportes_petersen_adquiridos FOR EACH STATEMENT EXECUTE FUNCTION mp_sin_truncate();
