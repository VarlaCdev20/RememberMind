# Arquitectura actual de RememberMind

RememberMind conserva una arquitectura MVC de Laravel 13 con Jetstream y Livewire. La refactorización de la BDD V2 no introduce DDD, repositorios ni capas arquitectónicas adicionales.

## Ubicación de cada responsabilidad

- `app/Models`: modelos Eloquent de las 69 tablas operativas. `Residente` es la entidad central y usa `cod_residente`.
- `app/Http/Controllers`: flujos HTTP por área funcional: admisiones, residentes, clínica, cuidados, medicación, instrumentos, valoraciones, actividades, alertas, documentos y reportes.
- `app/Livewire`: interfaces reactivas que complementan los controladores sin sustituir MVC.
- `app/Actions`: operaciones transaccionales específicas, incluida la admisión formal.
- `app/Policies`: autorización del recurso y alcance por residente.
- `app/Services`: procesos de aplicación que no pertenecen a una sola petición HTTP.
- `resources/views`: vistas Blade y plantillas de reportes.
- `routes/web.php`: rutas autenticadas, permisos y enlaces a Controller/Policy.
- `database/migrations`: definición congelada de BDD V2 y tablas técnicas.
- `database/seeders`: únicamente `DatabaseSeeder`, `RolesAndPermissionsSeeder` y `AdminSeeder`.
- `tests`: pruebas integrales portables entre PostgreSQL y SQLite.

## Reglas estructurales vigentes

- El flujo de alta es preadmisión, revisión, admisión formal, residente y cama.
- No existe creación directa de residentes.
- Los contactos se vinculan mediante `residentes_contactos`.
- La prescripción médica está separada de la administración de enfermería.
- Los documentos administrativos y clínicos permanecen separados.
- Los nueve roles vigentes se administran con Spatie Permission.

La fuente de verdad está en [`docs/base-de-datos`](docs/base-de-datos/README.md). El inventario de migración está en [`docs/base-de-datos/INVENTARIO_MIGRACION_APLICACION.md`](docs/base-de-datos/INVENTARIO_MIGRACION_APLICACION.md).
