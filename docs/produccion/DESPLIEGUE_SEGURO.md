# Despliegue seguro de RememberMind

## Condiciones obligatorias

- PHP 8.3 y PostgreSQL, según el stack soportado.
- HTTPS válido y `APP_DEBUG=false`.
- Secretos inyectados por el entorno; nunca versionados.
- Base de datos y almacenamiento privado con backups cifrados.
- `SEED_SAMPLE_ACCOUNTS=false`. Los usuarios se crean únicamente mediante el flujo institucional autorizado.
- Worker de colas supervisado y tareas programadas ejecutadas cada minuto.

## Liberación

```bash
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan optimize
php artisan storage:link
```

No ejecutar `migrate:fresh`, `db:wipe` ni `db:seed` en producción. La BDD V2.1 permanece congelada en 70 tablas operativas.

## Verificación posterior

1. Consultar `/up` y confirmar respuesta satisfactoria.
2. Confirmar que `APP_ENV=production` y `APP_DEBUG=false`.
3. Probar autenticación, permisos por rol, expediente, seguimiento, medicación y descarga privada.
4. Confirmar que el worker de colas y el scheduler están activos.
5. Ejecutar un backup, restaurarlo en un entorno aislado y registrar el resultado del simulacro.

## Reversión

Conservar el artefacto anterior y un backup previo a cada despliegue. La reversión de código no debe ejecutar migraciones destructivas. Toda restauración de datos debe realizarse sobre una instancia aislada antes de afectar producción.
