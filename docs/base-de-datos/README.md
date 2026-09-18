# Base de datos de RememberMind

Esta carpeta contiene la documentación canónica de la **BDD Operativa V2**.

## Fuente de verdad

Los documentos deben leerse en este orden:

1. [Baseline congelado](REMEMBERMIND_BDD_BASELINE_CONGELADO.md): fija alcance, decisiones y reglas que no pueden alterarse sin aprobación.
2. [Diccionario de las 69 tablas](REMEMBERMIND_BDD_69_TABLAS.md): define tablas, atributos, tipos, claves, restricciones y relaciones.

Ante cualquier diferencia con documentos anteriores de `docs/architecture-audit` o `docs/refactorizacion-total`, prevalecen estos dos archivos.

## Reglas de mantenimiento

- No cambiar nombres de tablas, columnas, PK, FK, nulabilidad, índices o cardinalidades sin aprobación explícita.
- Las ampliaciones explicativas deben documentarse fuera de los dos archivos congelados.
- Las tablas técnicas de Laravel, Jetstream, Sanctum y Spatie no forman parte de las 69 tablas operativas.
- `residentes` es la entidad maestra y solo puede crearse mediante la admisión formal.
- Spatie Activitylog continúa siendo la auditoría técnica; no se debe crear una auditoría paralela.
- El sistema experto está fuera de esta fase.

## Implementación Laravel

La estructura operativa está distribuida en cuatro migraciones:

- [`2026_09_18_000100_create_bdd_v2_core_tables.php`](../../database/migrations/2026_09_18_000100_create_bdd_v2_core_tables.php)
- [`2026_09_18_000200_create_bdd_v2_clinical_tables.php`](../../database/migrations/2026_09_18_000200_create_bdd_v2_clinical_tables.php)
- [`2026_09_18_000300_create_bdd_v2_care_medication_instrument_tables.php`](../../database/migrations/2026_09_18_000300_create_bdd_v2_care_medication_instrument_tables.php)
- [`2026_09_18_000400_create_bdd_v2_professional_social_alert_tables.php`](../../database/migrations/2026_09_18_000400_create_bdd_v2_professional_social_alert_tables.php)

El flujo transaccional de admisión se implementa en [`FormalizarAdmision.php`](../../app/Actions/Admisiones/FormalizarAdmision.php). La verificación automatizada del baseline se encuentra en [`BddOperativaV2Test.php`](../../tests/Feature/BddOperativaV2Test.php).

## Migración de la capa de aplicación

- [Inventario inicial y clasificación](INVENTARIO_MIGRACION_APLICACION.md)
- [Resultado de la migración a BDD V2](RESULTADO_MIGRACION_APLICACION_V2.md)

## Verificación

Para validar una instalación limpia:

```bash
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan test
```

`migrate:fresh` solo debe ejecutarse en un entorno local o de pruebas con datos descartables. Nunca debe utilizarse contra producción.
