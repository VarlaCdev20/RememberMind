---
title: "Resultado de migración de la aplicación a BDD V2"
status: HISTORICAL
version: "V2.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: []
related_modules: []
---

> HISTORICAL — DO NOT USE AS CURRENT SOURCE OF TRUTH. Resultado de etapa previa, no verificación global vigente.

# Resultado de migración de la aplicación a BDD V2

Fecha: 18/09/2026
Rama: `REFAC_BDD`


> [!NOTE]
> **NOTA HISTÓRICA:**
> Este documento registra la evidencia de ejecución técnica correspondiente a la **migración V2.0** (69 tablas operativas).
> Posteriormente se aprobó formalmente la **BDD Operativa V2.1**, la cual incorpora la tabla transaccional número 70 (`valoraciones_enfermeria_preadmision`) y su correspondiente modelo de autoría clínica.
> La fuente de verdad vigente se encuentra en [Baseline congelado V2.1 (70 tablas)](REMEMBERMIND_BDD_BASELINE_CONGELADO.md) y [Diccionario de las 70 tablas](REMEMBERMIND_BDD_70_TABLAS.md).

## Resumen de archivos

- Inventario inicial: 278 archivos ejecutables o de interfaz con referencias legacy, 33 documentos históricos y 9 scripts temporales.
- Aplicación, configuración y pruebas migradas o creadas: 42 archivos.
- Documentación modificada o incorporada: 8 archivos.
- Archivos legacy, huérfanos, duplicados o fuera de alcance eliminados: 451.
- Migraciones V2 modificadas: 2, exclusivamente para garantizar que PostgreSQL cree la PK antes de las FK autorreferentes de `documentos` y `notas_clinicas`. No cambió ninguna tabla, columna, PK, FK, relación ni cardinalidad del diseño congelado.

Los archivos eliminados no tenían rutas activas en la aplicación V2 o dependían exclusivamente de modelos/tablas eliminados. Su funcionalidad vigente fue sustituida por controladores, rutas, componentes y vistas que trabajan directamente con las entidades V2.

## Módulos terminados

1. Usuarios, personal, áreas, turnos, jornadas y asignaciones.
2. Preadmisión, revisión, admisión formal, residentes, contactos, habitaciones, camas, documentos y consentimientos.
3. Expediente clínico, atenciones, notas, antecedentes, diagnósticos, alergias, signos vitales, dolor, antropometría, estudios, resultados, informes, documentos clínicos, derivaciones, incidentes e indicaciones.
4. Asignaciones por jornada, registros diarios de enfermería, heridas, curaciones, pases de turno, planes, intervenciones, programaciones y ejecuciones.
5. Medicamentos, prescripciones, horarios y administraciones.
6. Instrumentos, preguntas, opciones, aplicaciones y respuestas.
7. Psicología, nutrición, fisioterapia/valoración funcional y pedagogía.
8. Actividades, participantes, visitas, alertas y eventos.
9. Dashboard, búsqueda de residentes, PDF individual y exportación CSV.

## Autorización

- Las rutas activas combinan autenticación, permisos y Policy de residente.
- La identidad de personal se obtiene del usuario autenticado en los registros clínicos.
- Se validan las pertenencias atención–residente, estudio–residente, componente–tipo de estudio, instrumento–pregunta–opción y actividad–participante.
- FAMILIAR se limita a residentes vinculados y autorizados mediante `residentes_contactos`.
- SUPERADMINISTRADOR mantiene lectura total sin recibir competencia clínica de escritura.
- Enfermería administra medicación, pero no prescribe.

## Verificación ejecutada

- `php artisan optimize:clear`: correcto.
- `php artisan migrate:fresh --seed --force`: correcto sobre la base PostgreSQL real `remembermind_dev`; migraciones V2 y técnicas ejecutadas, con roles/permisos y administrador inicial.
- PostgreSQL real: 84 tablas públicas, correspondientes a 69 tablas operativas V2 y 15 tablas técnicas.
- Datos mínimos reales: 9 roles activos y 1 usuario administrador inicial.
- Tablas legacy `adulto_mayor` y `voluntarios`: inexistentes en la base real después de la migración.
- Respaldo previo recuperable: `storage/app/backups/remembermind_dev_legacy_before_v2_20260918_162107.dump` (formato personalizado de PostgreSQL).
- `php artisan route:list --except-vendor`: correcto, 58 rutas de aplicación.
- `php artisan route:cache`: correcto.
- `php artisan test`: 19 pruebas aprobadas, 211 aserciones.
- `npm run build`: correcto.
- `git diff --check`: correcto; solo avisos informativos de normalización CRLF/LF.
- Sintaxis PHP: correcta.
- Imports de modelos: todos resuelven.

## Búsqueda legacy final

- Aplicación ejecutable: 0 coincidencias para `AdultoMayor`, `adulto_mayor`, `adultos_mayores`, `cod_am`, voluntariado y `valoraciones_enfermeria`.
- Nombres de archivos activos: 0 coincidencias legacy.
- Pruebas: permanecen cuatro menciones negativas que verifican que tablas, columnas y rol eliminados no existan.
- Documentación histórica: se conserva separada y señalada como no vigente.
- Una observación histórica permanece en la migración técnica de Activitylog; no se modificó porque las migraciones están congeladas y el comentario no afecta la ejecución.

## Problemas que requerirían cambiar la BDD congelada

No se detectó ningún problema que requiera modificar tablas, columnas, PK, FK, relaciones o cardinalidades.

Durante la ejecución real se detectó una incompatibilidad de orden DDL específica de PostgreSQL: Laravel emitía las FK autorreferentes antes de registrar la PK de la misma tabla. Se corrigió únicamente el orden de los comandos en las dos migraciones afectadas; el esquema resultante continúa siendo exactamente el congelado.

No se realizó commit ni push.
