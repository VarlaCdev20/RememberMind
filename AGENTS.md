# RememberMind

## Stack
- PHP 8.3
- Laravel 13
- Livewire 4
- Jetstream
- Sanctum
- PostgreSQL
- Tailwind CSS 3
- Spatie Permission
- Spatie Activitylog

## Base de datos
- baseline vigente: BDD Operativa V2.1 (exactamente 70 tablas operativas congeladas)
- entidad central: residentes
- consultar siempre docs/base-de-datos/ antes de cualquier análisis o ajuste
- BDD congelada: prohibición estricta de modificaciones estructurales sin aprobación previa
- usar cod_<entidad>
- mantener FK normalizadas
- no usar JSON/EAV para reemplazar el modelo clínico

## Seguridad
- nunca hardcodear contraseñas
- nunca crear usuarios/residentes/personal automáticamente como fallback
- nunca seleccionar el primer registro disponible para completar una FK
- rechazar datos incompletos mediante validación

## Desarrollo
composer install
npm ci
php artisan migrate
composer test
npm run build

## Testing
- SQLite :memory: para pruebas rápidas cuando esté configurado
- PostgreSQL para validación de integración/CI

## Convenciones Críticas
- La PK canónica de usuarios en V2 es `cod_usuario` (string), nunca `cod_usu` ni autoincrement.
- La entidad central es `residentes` con clave `cod_residente` (string), nunca `cod_am`.
- `usuario`, `personal`, `contacto`, `postulante` y `residente` son conceptos distintos.
- Frontend reside en `app/Frontend/Livewire/` y Backend modular en `app/Backend/Modulos/`. No usar `app/Services/` ni `app/Livewire/`.
- Autenticación mediante `correo` y estado `ACTIVO`. Registro público deshabilitado.
