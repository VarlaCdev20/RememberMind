# Datos ficticios para desarrollo local

La BDD Operativa V2.1 conserva exactamente 70 tablas. `DatabaseSeeder` instala únicamente los roles y permisos canónicos en local y producción; en pruebas también prepara las cuentas que requieren las pruebas existentes.

Para llenar las **70 tablas operativas** con al menos 20 casos ficticios coherentes en PostgreSQL, ejecutar explícitamente:

```bash
php artisan db:seed
php artisan db:seed --class=LocalSampleDataSeeder
```

`LocalSampleDataSeeder` solo permite los entornos `local` y `testing`. Puede ejecutarse más de una vez sin duplicar los casos. Crea 20 solicitudes con sus valoraciones de enfermería y formaliza cada ingreso mediante `FormalizarAdmision`; los residentes, las camas ocupadas, los vínculos familiares, los historiales y los consentimientos proceden del flujo transaccional de la aplicación. Las demás tablas reciben al menos 20 registros ficticios vinculados por sus FK reales. Algunas tablas auxiliares pueden tener más de 20 filas porque cada caso requiere dos áreas, turnos o jornadas. El seeder verifica al final que cada tabla operativa tenga al menos 20 registros y revierte toda la carga si encuentra un error.

Los identificadores de estos casos usan el prefijo `FIC_` y las descripciones indican expresamente que son datos ficticios. Las contraseñas de sus cuentas se generan de forma aleatoria y no se imprimen. El archivo documental de ejemplo se guarda en `storage/app/private/fixtures-local/resumen.txt` (según el disco local configurado).

`AdminSeeder` permanece disponible para las pruebas existentes y, en local, solo se habilita con `SEED_SAMPLE_ACCOUNTS=true`. Nunca debe usarse esta carga para atención o documentación clínica real.
