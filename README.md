# RememberMind

Sistema de gestión clínica y administrativa para una residencia geriátrica,
construido con Laravel 13, Livewire 4, Jetstream y PostgreSQL.

## Requisitos

- PHP 8.3 o superior
- Composer 2
- Node.js y npm
- PostgreSQL para desarrollo y producción

## Instalación local

```bash
composer install
npm install
php artisan migrate --seed
composer dev
```

## Verificación

```bash
composer test
npm run build
node --test tests/Frontend/documentos-adulto.test.js
```

## Documentación

- [Índice de documentación](docs/README.md)
- [Arquitectura y estructura de carpetas](docs/arquitectura/README.md)
- [BDD Operativa V2.1](docs/base-de-datos/README.md)
- [Sistema experto](docs/sistema-experto/README.md)

La BDD V2.1 contiene 70 tablas operativas. Las tablas técnicas de Laravel,
Jetstream, Sanctum y Spatie se mantienen fuera de ese conteo.
