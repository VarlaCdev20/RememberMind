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
CREATE TRIGGER mp_mismo_residente_insert BEFORE INSERT ON juicios_petersen_adquiridos
                    WHEN NOT EXISTS(SELECT 1 FROM evaluaciones_expertas e JOIN valoraciones_petersen_originales v ON v.cod_residente=e.cod_residente
                        WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND v.cod_valoracion_petersen=NEW.cod_valoracion_petersen)
                        OR EXISTS(SELECT 1 FROM soportes_petersen_adquiridos s JOIN soportes_valoracion_petersen o ON o.cod_soporte_petersen=s.cod_soporte_petersen
                            WHERE s.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND o.cod_valoracion_petersen<>NEW.cod_valoracion_petersen)
                    BEGIN SELECT RAISE(ABORT,'Juicio de otro residente'); END;
CREATE TRIGGER mp_mismo_juicio_insert BEFORE INSERT ON soportes_petersen_adquiridos
                    WHEN NOT EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j JOIN soportes_valoracion_petersen s ON s.cod_valoracion_petersen=j.cod_valoracion_petersen
                        WHERE j.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND s.cod_soporte_petersen=NEW.cod_soporte_petersen)
                    BEGIN SELECT RAISE(ABORT,'Soporte de otro juicio'); END;
CREATE TRIGGER mp_mismo_residente_update BEFORE UPDATE ON juicios_petersen_adquiridos
                    WHEN NOT EXISTS(SELECT 1 FROM evaluaciones_expertas e JOIN valoraciones_petersen_originales v ON v.cod_residente=e.cod_residente
                        WHERE e.cod_evaluacion_experta=NEW.cod_evaluacion_experta AND v.cod_valoracion_petersen=NEW.cod_valoracion_petersen)
                        OR EXISTS(SELECT 1 FROM soportes_petersen_adquiridos s JOIN soportes_valoracion_petersen o ON o.cod_soporte_petersen=s.cod_soporte_petersen
                            WHERE s.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND o.cod_valoracion_petersen<>NEW.cod_valoracion_petersen)
                    BEGIN SELECT RAISE(ABORT,'Juicio de otro residente'); END;
CREATE TRIGGER mp_mismo_juicio_update BEFORE UPDATE ON soportes_petersen_adquiridos
                    WHEN NOT EXISTS(SELECT 1 FROM juicios_petersen_adquiridos j JOIN soportes_valoracion_petersen s ON s.cod_valoracion_petersen=j.cod_valoracion_petersen
                        WHERE j.cod_juicio_adquirido=NEW.cod_juicio_adquirido AND s.cod_soporte_petersen=NEW.cod_soporte_petersen)
                    BEGIN SELECT RAISE(ABORT,'Soporte de otro juicio'); END;
CREATE TRIGGER juicios_petersen_adquiridos_mp_insert BEFORE INSERT ON juicios_petersen_adquiridos
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta) IS NOT 'ABIERTA'
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END;
CREATE TRIGGER juicios_petersen_adquiridos_mp_update BEFORE UPDATE ON juicios_petersen_adquiridos
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta) IS NOT 'ABIERTA' OR (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta) IS NOT 'ABIERTA'
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END;
CREATE TRIGGER juicios_petersen_adquiridos_mp_delete BEFORE DELETE ON juicios_petersen_adquiridos
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=OLD.cod_evaluacion_experta) IS NOT 'ABIERTA'
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END;
CREATE TRIGGER soportes_petersen_adquiridos_mp_insert BEFORE INSERT ON soportes_petersen_adquiridos
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=(SELECT cod_evaluacion_experta FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=NEW.cod_juicio_adquirido)) IS NOT 'ABIERTA'
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END;
CREATE TRIGGER soportes_petersen_adquiridos_mp_update BEFORE UPDATE ON soportes_petersen_adquiridos
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=(SELECT cod_evaluacion_experta FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=OLD.cod_juicio_adquirido)) IS NOT 'ABIERTA' OR (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=(SELECT cod_evaluacion_experta FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=NEW.cod_juicio_adquirido)) IS NOT 'ABIERTA'
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END;
CREATE TRIGGER soportes_petersen_adquiridos_mp_delete BEFORE DELETE ON soportes_petersen_adquiridos
                        WHEN (SELECT membresias_petersen_estado FROM evaluaciones_expertas WHERE cod_evaluacion_experta=(SELECT cod_evaluacion_experta FROM juicios_petersen_adquiridos WHERE cod_juicio_adquirido=OLD.cod_juicio_adquirido)) IS NOT 'ABIERTA'
                        BEGIN SELECT RAISE(ABORT,'Membresía fuera de adquisición abierta'); END;
CREATE TRIGGER mp_guardar_cabecera BEFORE UPDATE ON evaluaciones_expertas
                WHEN (OLD.membresias_petersen_estado='CERRADA' AND (
                    NEW.membresias_petersen_estado IS NOT OLD.membresias_petersen_estado OR NEW.membresias_petersen_huella IS NOT OLD.membresias_petersen_huella
                    OR NEW.membresias_petersen_juicios IS NOT OLD.membresias_petersen_juicios OR NEW.membresias_petersen_soportes IS NOT OLD.membresias_petersen_soportes
                    OR NEW.cod_residente IS NOT OLD.cod_residente OR NEW.cod_evaluacion_experta IS NOT OLD.cod_evaluacion_experta))
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
                BEGIN SELECT RAISE(ABORT,'Cierre de membresías incompatible'); END;
CREATE TRIGGER mp_sin_borrado BEFORE DELETE ON evaluaciones_expertas WHEN OLD.membresias_petersen_estado IS NOT NULL
                BEGIN SELECT RAISE(ABORT,'No se elimina una adquisición'); END;
CREATE TRIGGER mp_nueva_cabecera BEFORE INSERT ON evaluaciones_expertas
                WHEN NEW.membresias_petersen_estado IS NOT NULL OR NEW.membresias_petersen_huella IS NOT NULL
                    OR NEW.membresias_petersen_juicios IS NOT NULL OR NEW.membresias_petersen_soportes IS NOT NULL
                    OR EXISTS(SELECT 1 FROM evaluaciones_expertas WHERE cod_evaluacion_experta=NEW.cod_evaluacion_experta AND membresias_petersen_estado IS NOT NULL)
                BEGIN SELECT RAISE(ABORT,'La adquisición comienza en una evaluación existente'); END;
