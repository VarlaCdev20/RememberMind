---
title: "RememberMind — Baseline técnico y entorno de ejecución"
status: CURRENT
version: "2.1"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: true
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> Versiones declaradas compatibles; versiones concretas del lock y runtime no verificado se separan en [ESTADO_ACTUAL](../ESTADO_ACTUAL.md).

# RememberMind — Baseline técnico y entorno de ejecución

**Versión:** 2.1

**Estado:** VIGENTE

**Documento complementario:** [REMEMBERMIND_BDD_BASELINE_CONGELADO.md](../base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md)

## 1. Stack tecnológico oficial

- PHP 8.3+
- Laravel Framework 13.x
- Jetstream 5.5+
- Sanctum 4.x
- Livewire 4.3+
- Tailwind CSS 3.x
- Spatie Laravel Permission 7.3+
- Spatie Laravel Activitylog 4.12+

## 2. Motores de base de datos

### PostgreSQL

Es el único motor soportado para desarrollo integrado, staging y producción. En PostgreSQL se validan los tipos físicos, claves foráneas, índices y restricciones `CHECK` de la BDD Operativa V2.1.

### SQLite

Se utiliza exclusivamente con `:memory:` para pruebas automatizadas rápidas. Las migraciones emulan mediante triggers las restricciones necesarias para que esas pruebas mantengan reglas equivalentes.

MySQL y MariaDB no forman parte del baseline soportado. La presencia de configuración genérica de Laravel para esos motores no implica compatibilidad de RememberMind.

## 3. Ciclo de desarrollo

### Instalación

```bash
composer install
npm ci
```

### Compilación frontend

```bash
npm run build
npm run dev
```

### Migraciones

```bash
php artisan migrate
```

### Pruebas automatizadas

```bash
composer test
php artisan test tests/Feature/BddOperativaV2Test.php
php artisan test tests/Feature/IntegracionAdaptadoresV2Test.php
php artisan test tests/Feature/ValoracionEnfermeriaAutoriaTest.php
```

## 4. Comandos destructivos

`php artisan migrate:fresh`, `php artisan migrate:reset` y `php artisan db:wipe` solamente pueden ejecutarse contra bases desechables de pruebas o desarrollo con datos sintéticos.

Está prohibido ejecutarlos contra una base que contenga información histórica, clínica o institucional que deba conservarse.
