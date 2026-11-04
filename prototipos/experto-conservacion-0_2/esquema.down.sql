-- Sólo rollback vacío; nunca destruye historia para permitir un down().
DO $$ BEGIN IF EXISTS(SELECT 1 FROM orion_f02.evaluaciones_compuestas) THEN RAISE EXCEPTION 'Rollback bloqueado: conservar y exportar historia' USING ERRCODE='55000'; END IF; END $$;
DROP SCHEMA orion_f02 CASCADE;