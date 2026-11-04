# Datos sintéticos para desarrollo local

> Alcance de inventario: las cifras de esta decisión describen su fecha. El [inventario vigente](README.md) incorpora las extensiones posteriores:106 tablas de negocio+16 técnicas. La decisión conserva exclusivamente sus columnas, relaciones y reglas autorizadas.

`DatabaseSeeder` instala los roles y permisos canónicos; no carga expedientes de muestra en la aplicación local ni en producción. En pruebas prepara también las cuentas requeridas por los tests existentes.

La carga optativa vigente es `Historical2026Seeder`: **30 residentes con nombres, nacimientos e historiales distintos**, admisión formal, camas y asignaciones por turno. Respeta las **71 tablas operativas de V2.1 + V2.2**, sin modificar el esquema.

Consultar [Carga sintética histórica 2026](SEED_HISTORICO_2026.md) para autorizaciones, fuentes farmacéuticas, ejecución protegida, credenciales privadas y evidencia de verificación. Solo admite `local/testing` y exige identificar explícitamente la base autorizada mediante `HISTORICAL2026_ALLOW_DATABASE`.

Se retiraron los generadores anteriores de muestras y vista previa para impedir que vuelvan a crear residentes repetidos o catálogos ficticios. No se necesita una carga adicional para vincular residentes: el nuevo dataset incluye sus asignaciones y contactos.

`AdminSeeder` sigue disponible para las pruebas existentes y, en local, requiere `SEED_SAMPLE_ACCOUNTS=true`. Ninguna carga sintética debe utilizarse para atención o documentación clínica real. No ejecutar `migrate:fresh` sobre una base con expedientes que deban conservarse.
