-- Aprobada e instalada el 26/10/2026. DDL de referencia; usar la migración registrada.
-- PostgreSQL / SQLite compatibles con la migración propuesta.
ALTER TABLE usuarios ADD COLUMN remember_token VARCHAR(100) NULL;

-- Reversión: desactivar primero AUTH_REMEMBER_ENABLED y cerrar sesiones.
-- ALTER TABLE usuarios DROP COLUMN remember_token;
