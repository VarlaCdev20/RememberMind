# Corrección de CI — PR #16 (17/10/2026)

Ámbito: rama `feature/iadl-c03-captura-20261017`, base `develop`. No se fusiona ni aplica la extensión propuesta a una instalación institucional.

## Causa reproducida

CI 39059698836: PostgreSQL SQLSTATE 42830 al crear la self-FK IADL antes de instalar su PK; SQLite: contrato de 296 rutas frente a 298, inventario experto anclado en 71 frente a 72 y rollback que suponía únicamente tres migraciones posteriores.

## Corrección

- Crear la autorreferencia en un segundo Schema::table, tras completar la PK. Se conservan las 17 columnas, cinco FKs, nullabilidad, índices y restricciones de la propuesta IADL.
- El inventario operacional de esta rama es 70 V2.1 + objetivos_signos_vitales + controles_funcionales_iadl = 72; las 23 tablas expertas y las técnicas siguen separadas. Esto no aprueba el esquema para producción ni integra extensiones de otras ramas.
- Actualizar el contrato ordenado únicamente con las dos rutas IADL y sus middleware; preservar las 296 anteriores.
- Calcular las migraciones posteriores al bloque experto para revertir en orden; cotejar columnas, índices y relaciones operacionales antes/después de retirar las 23 expertas y tras reinstalar todo.
- Probar PK/self-FK física y rechazo de rectificación huérfana; recuperar con savepoint la transacción tras el rechazo de FK en PostgreSQL.
- Congelar el reloj de los ensayos IADL: la rectificación debe referirse al mismo momento, incluso cerca del cambio de minuto.
- Añadir los contratos IADL a PostgreSQL en la CI general, con evidencia JUnit.

## Verificación local

- Error PostgreSQL original reproducido en cluster PostgreSQL 18 desechable con usuario restringido; SQLite reprodujo los tres fallos del contrato.
- SQLite: FolderOrganizationTest + PersistenciaExpertaTest + ControlesFuncionalesIadlTest + inventario BddOperativaV2Test: 28 pruebas, 4516 aserciones, PASS.
- PostgreSQL 18: ControlesFuncionalesIadlTest + PersistenciaExpertaTest: 23 pruebas, 1343 aserciones, PASS. La comprobación posterior refuerza conservación del esquema durante rollback y reloj fijo.
- npm run build: PASS. PHP lint de los archivos modificados y git diff --check: PASS.
- Revisión de persistencia limitada al cambio: esquema propuesto preservado; no se cambian reglas clínicas, permisos ni semántica del motor. Sin nuevo legacy ejecutable.

La CI completa se ejecutará de nuevo con la actualización del PR. Su resultado debe leerse sobre el nuevo SHA; estos ensayos focales no acreditan por sí solos la suite general. El PR permanece en borrador y sin autorización de despliegue.

Recomprobación final PostgreSQL 18 (rollback reforzado, rectificación y self-FK): 3 pruebas, 68 aserciones, PASS.
