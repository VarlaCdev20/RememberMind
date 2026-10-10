---
title: "RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-08
owner: RememberMind
source_of_truth: false
verified_against_commit: null
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

# RememberMind

Sistema web para una residencia geriátrica: operación institucional y seguimiento clínico, cognitivo, funcional e interdisciplinario.

## Documentación y estado

Entrada oficial: [RememberMind Documentation](docs/README.md).

- [Estado real del repositorio](docs/ESTADO_ACTUAL.md).
- [Roadmap: trabajo, propuestas e ideas](docs/ROADMAP.md).
- [Glosario](docs/GLOSARIO_DOMINIO.md), [decisiones pendientes](docs/DECISIONES_PENDIENTES.md), [deuda evidenciada](docs/DEUDA_TECNICA.md).
- [Arquitectura vigente](docs/arquitectura/README.md), [BDD y extensiones aprobadas](docs/base-de-datos/README.md).
- [Gobernanza/changelog documental](docs/CHANGELOG.md).

Hay código y tests en varias áreas; esta revisión documental no ejecutó suites/build ni verificó producción. No se afirma completitud o porcentaje global de aprobación.

## Dominio y flujo institucional

Postulante ≠ residente ≠ usuario ≠ personal ≠ contacto.

Preadmisión → revisión → APROBADA/RECHAZADA → admisión formal con cama → residente admitido. Aprobación no crea residente; formalización es transaccional. Fuente: [AGENTS](AGENTS.md) y [baseline](docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md); valores físicos divergentes están en DEC-OPEN-001.

Áreas: institucional/alojamiento, clínica, cuidado continuo, medicación, trabajo interdisciplinario, actividades/visitas, documentación, alertas, reportes, seguridad/auditoría, UX/testing. El [sistema experto](docs/sistema-experto/README.md) tiene núcleo técnico, esquema y revisión administrativa implementados; su conocimiento institucional y activación clínica siguen pendientes.

## Stack y requisitos

Versiones bloqueadas y motores: [estado actual](docs/ESTADO_ACTUAL.md) y [baseline técnico](docs/arquitectura/REMEMBERMIND_BASELINE_TECNICO.md).

- PHP ^8.3 y extensiones de la instalación; runtime no comprobado aquí.
- Laravel 13.33.0, Livewire 4.4.6, Tailwind 3.4.19 y Vite 8.3.1 según locks.
- Node >=22.12.0 para satisfacer el lock completo, incluido Puppeteer; Composer según entorno.
- PostgreSQL para desarrollo integrado/staging/producción; SQLite :memory: para tests rápidos. MySQL/MariaDB fuera del baseline.

## Preparar desarrollo

```bash
composer install
npm ci
```

En instalación nueva, crear .env desde el ejemplo solo si no existe y configurar conexión/secretos del entorno. No sobrescribir .env existente. Generar clave únicamente para la instalación nueva; no regenerarla en un sistema con datos cifrados.

```bash
php artisan key:generate
php artisan migrate
```

Antes de migrate confirmar DB destino y contrato aprobado. Seeders sintéticos solo con carga explícita según [guía local](docs/base-de-datos/SEEDERS_LOCALES.md); sin seed automático contra datos reales. migrate:fresh --seed solo en DB confirmada desechable cuando la verificación lo necesita.

## Ejecución, pruebas y build

```bash
composer dev
composer test
php artisan test --filter=BddOperativaV2Test
npm test
npm run build
```

Comandos disponibles, no ejecutados por Fase 1. PostgreSQL para garantías físicas/concurrencia; QA visual independiente del build. Producción tiene [procedimiento propio](docs/produccion/DESPLIEGUE_SEGURO.md).

## Roles y seguridad

Fuente: [roles congelados](docs/arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md).

- Superadministrador: acceso técnico/supervisión; no escritura clínica automática por rol.
- Gerente: dirección, personal y planificación.
- Administrador: operación diaria; no gestión de cuentas/RRHH ni clínica por rol.
- Medicina/geriatría: competencias clínicas y prescripción autorizadas.
- Enfermería: administración/documentación de cuidado; no prescribir.
- Psicología, Nutrición, Fisioterapia y Pedagogía: ámbito profesional permitido.
- Familiar: solo información autorizada de residentes vinculados; ver residente no publica expediente completo.

Cuenta ACTIVO autenticada por correo + permiso + Policy + estado/regla + vínculo/scope/competencia. Registro público deshabilitado; archivos clínicos privados y descargas reautorizadas. Las deudas conocidas no se ocultan con un resumen de seguridad.

## Agentes

[CODEX_SETUP](CODEX_SETUP.md), [stack sistema](docs/sistema/STACK_SKILLS_SISTEMA.md) y [stack UX](docs/frontend/STACK_SKILLS_UX_UI.md). Instrucciones por alcance; historia y propuestas no gobiernan implementación.
