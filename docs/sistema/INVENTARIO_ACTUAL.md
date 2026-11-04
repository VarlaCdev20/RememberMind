---
title: "Inventario actual de implementación"
status: CURRENT
version: "1.0"
last_reviewed: 2026-11-04
owner: RememberMind
source_of_truth: false
source_of_truth_scope: [observed_repository_inventory]
verified_against_commit: 57285c7e3988cf5df86cdbd24f03e6a9650807bf
verification_scope: CURRENT_TREE_DOCUMENTATION_AND_REGISTERED_ROUTES
runtime_verified: false
---

# Inventario actual de implementación

**Corte documental: 04/11/2026.** Base `57285c7e` y árbol local con trabajo previo de Gerencia, avisos y Reportes. Recuentos de archivos físicos, seeder y `php artisan route:list --json`; no se consultaron pacientes, cuentas o tablas operativas. Archivos, rutas, permisos efectivos y procesos aceptados son medidas distintas.

## Recuentos del árbol

| Elemento | Cantidad |
|---|---:|
| Módulos Laravel | 12 |
| Roles definidos en el seeder | 10 |
| Archivos de modelos Eloquent | 112 |
| Plantillas Blade modulares | 398 |
| Plantillas Blade compartidas | 195 |
| Plantillas Blade totales | 593 |
| Archivos PHP productivos del motor | 106 |
| Definiciones de ruta registradas | 325 |
| Rutas con acción modular | 291 |
| Otras rutas registradas | 34 |

Las rutas `GET|HEAD` cuentan como una definición. La atribución al módulo usa el namespace de la acción; las otras incluyen framework, autenticación, closures y endpoints compartidos. Un archivo Eloquent puede representar una identidad de un bloque consolidado: no inferir tablas a partir de modelos. Las plantillas incluyen parciales y componentes; las clases Livewire también incluyen funcionalidades reutilizadas.

## Doce propietarios

| Módulo | Responsabilidad | PHP sin Blade | Clases Livewire | Blade | Rutas por acción | Entrada |
|---|---|---:|---:|---:|---:|---|
| Admisiones | Preadmisión, revisión y admisión formal con cama | 13 | 4 | 10 | 7 | [Contrato](../../app/Modulos/Admisiones/README.md) |
| Alertas | Incidentes/alertas, atención, eventos y continuidad | 33 | 8 | 23 | 9 | [Contrato](../../app/Modulos/Alertas/README.md) |
| ApoyoDecision | Adquisición, conocimiento, inferencia, explicación y consulta experta | 101 | 6 | 37 | 7 | [Contrato](../../app/Modulos/ApoyoDecision/README.md) |
| Clinica | Atenciones, antecedentes, notas, estudios e información clínica | 65 | 11 | 71 | 54 | [Contrato](../../app/Modulos/Clinica/README.md) |
| Cuidados | Registros diarios, turnos, cuidados y pase de turno | 43 | 11 | 68 | 22 | [Contrato](../../app/Modulos/Cuidados/README.md) |
| Identidad | Acceso, cuentas, roles, personal y documentación de identidad | 63 | 7 | 53 | 29 | [Contrato](../../app/Modulos/Identidad/README.md) |
| Institucion | Estructura residencial, personal, jornadas, asignaciones y planificación | 46 | 8 | 33 | 36 | [Contrato](../../app/Modulos/Institucion/README.md) |
| Medicacion | Catálogo, prescripción, horarios y administración/omisión | 21 | 4 | 15 | 15 | [Contrato](../../app/Modulos/Medicacion/README.md) |
| Reportes | Consulta y exportaciones sujetas a autorización | 59 | 5 | 33 | 38 | [Contrato](../../app/Modulos/Reportes/README.md) |
| Residentes | Identidad residencial, expediente, alojamiento y contactos | 22 | 3 | 29 | 28 | [Contrato](../../app/Modulos/Residentes/README.md) |
| Social | Actividades, participación, visitas y apoyo | 22 | 5 | 7 | 17 | [Contrato](../../app/Modulos/Social/README.md) |
| Valoraciones | Capturas profesionales, aplicaciones, respuestas y contexto de sesión | 44 | 16 | 19 | 29 | [Contrato](../../app/Modulos/Valoraciones/README.md) |

Los archivos se cuentan dentro de `app/Modulos/{propietario}`; Livewire en `Presentacion/Livewire`, Blade en `Presentacion/Vistas`. Todos conservan `Dominio`, `Aplicacion`, `Infraestructura` y `Presentacion`. Las dependencias compartidas pertenecen a `app/Compartido`; el motor puro vive en `motor-experto`.

## Roles configurados

Fuente: [RolesAndPermissionsSeeder](../../database/seeders/RolesAndPermissionsSeeder.php), lista `rolesActivos`. No es una consulta de cuentas ni una concesión nueva de permisos.

- `SUPERADMINISTRADOR`
- `GERENTE`
- `ADMINISTRADOR`
- `ENFERMEROS`
- `MEDICO GENERAL/GERIATRA`
- `PSICOLOGO/A`
- `PEDAGOGO`
- `NUTRICIONISTA`
- `FISIOTERAPEUTA`
- `FAMILIAR`

La [matriz de autorización](../seguridad/MATRIZ_ROLES_PERMISOS.md) y las Policies/casos de uso aplicables determinan qué entrada permite cada operación. Gerencia y Administración no ganan escritura clínica por rol; Enfermería no prescribe; Familiar no ve un expediente completo por estar vinculado.

## Versiones bloqueadas

Fuentes: [composer.lock](../../composer.lock) y [package-lock.json](../../package-lock.json). Son versiones resueltas, no resultados de instalación o de un audit de vulnerabilidades ejecutado hoy.

| Paquete | Lock |
|---|---|
| `laravel/framework` | `v13.33.0` |
| `laravel/jetstream` | `v5.5.3` |
| `laravel/sanctum` | `v4.3.3` |
| `livewire/livewire` | `v4.4.6` |
| `spatie/laravel-permission` | `7.4.2` |
| `spatie/laravel-activitylog` | `4.12.3` |
| `phpunit/phpunit` | `12.5.36` |
| `tailwindcss` | `3.4.19` |
| `vite` | `8.3.1` |
| `puppeteer` | `25.12.0` |

PHP declarado `^8.3`; Node mínimo compatible con el lock completo `>=22.12.0`. Alpine lo proporciona Livewire; no hay un paquete npm `alpinejs` independiente en este lock. La aplicación utiliza Laravel, Jetstream, Sanctum, Livewire y Spatie; no se introduce otra arquitectura por actualizar esta tabla.

## BDD: inventario documentado, no contado en esta revisión

**106 de negocio (81 operativas + 25 expertas) +16 técnicas =122 físicas**, según decisiones e instalaciones registradas. El núcleo original sigue documentado como70; los catálogos instrumentales y las extensiones de contexto no añaden tablas. [Índice y decisiones BDD](../base-de-datos/README.md).

No se alteran PK/FK, permisos, catálogos ni datos desde este inventario. La propuesta rechazada de conservación conjunta no se incorpora como estructura aprobada.

## Motor e integración

Seis componentes principales: `conocimiento`, `hechos`, `inferencia`, `explicacion`, `instrumentos`, `conservacion`. Tipos/puertos auxiliares: `dominio` y `aplicacion`. Sin envoltorio `src/motor_experto`. [Entradas por componente y reglas](../../motor-experto/README.md).

24 reglas fuente: 17 cognitivas, 4 Petersen y 3 observacionales. 26 métodos instrumentales: 23 por componentes y 3 iniciales descriptivos. La consulta administrativa utiliza versiones/reglas registradas; los conteos de filas pueden ser diferentes. MoCA 8.1, RUDAS-PE 2020 y FAQ B7S están activados localmente para captura/puntuación. La conservación conjunta institucional sigue pendiente; no confundir replay técnico con resultado guardado.

## Cómo comprobar y mantener

```bash
php artisan route:list --json
php scripts/verificar-documentacion.php --check
php scripts/verificar-matriz-trazabilidad-dcl.php --check
```

El comprobador documental contrasta enlaces/anclas locales, estos recuentos/locks, los tokens de color y el contenido de los inventarios Blade. No ejecuta seeders, migraciones, pruebas clínicas ni envíos. Si cambia el código, actualizar esta tabla y los inventarios afectados antes de volver a comprobar. [Estado actual](../ESTADO_ACTUAL.md) · [catálogo documental](../INDICE_DOCUMENTAL.md).
