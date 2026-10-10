---
title: "Estado actual de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-08
owner: RememberMind
source_of_truth: true
verified_against_commit: null
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
runtime_evidence_status: PARTIAL
runtime_evidence_scope: FASE_3_AND_4_AND_EXPERT_V1_SCOPED_VERIFICATIONS
automated_global_gate_status: FAIL
supersedes: []
related_docs: ["README.md","ROADMAP.md","DEUDA_TECNICA.md","DECISIONES_PENDIENTES.md"]
related_modules: []
---

# Estado actual de RememberMind

## Resumen ejecutivo

RememberMind es un sistema residencial geriátrico con seguimiento clínico, cognitivo, funcional e interdisciplinario. Hay implementación Laravel de admisión, residentes/camas, identidad, cuidados, medicación, alertas, documentación, consultas y reportes, con tests asociados.

Fases 1–2 describen el commit base y cambios previos sin commit mediante revisión estática. Fase 3 ejecutó migraciones/seed, pruebas PostgreSQL aisladas y build; evidencia acotada en [TRAZABILIDAD](TRAZABILIDAD.md). Baseline inicial: 613 tests, 14 fallos, 1 error y 13 omitidos; build PASS. Lote A acredita casos dirigidos de acceso familiar, competencia, pase y errores; B acredita TECH-011 (14/59); C eventos (24/109); D continuidad (46/203). E acredita rollback9puntos y carrera de cama PostgreSQL con dos procesos/locks reales; correcciones acotadas 12/126 PASS. Suite final: 670 tests / 12.068 aserciones, 0 errores, 15 fallos y 13 omitidos; conserva 14 fallos previos y una expectativa Familiar alineada y revalidada después (22/91 PASS). Cohorte núcleo 70/374 PASS. Build y reset/seed final PASS; 71 tablas operativas instaladas en la base aislada. Gate global FAIL; no repetir el resultado acotado como PASS global. `runtime_verified: false` expresa ausencia de certificación global; no invalida esos resultados acotados. No se acredita QA visual ni producción. Ningún área se declara globalmente ESTABLE.

Fase 4 posterior: PHP oficial 8.3.33 habilitado con autorización «habilita php», sin alterar política de seguridad. Baseline repetida antes de cambiar código: 670 tests, 12.082 aserciones, 14 fallos, 0 errores, 13 omitidos. Se clasificaron y repararon esos catorce fallos PHP y dos JS con parches acotados. Suite global final: 686 tests, 12.219 aserciones, 0 fallos, 0 errores y los mismos 13 omitidos justificados; 900,178 s reloj. npm test 30/30, build y migrate:fresh --seed aislado 71/71 PASS. Las seis cohortes F3 se reejecutaron dentro de la suite final sin fallos ni omisiones. **Gate global automatizado F4: PASS**; el FAIL histórico F3 se conserva. Esto no certifica operación clínica integral, producción ni cierre de TECH. runtime_verified=false expresa ese límite de certificación, no ausencia de ejecución de suites. Pint mantiene siete archivos con fixers preexistentes, sin hallazgos de formato nuevos. Inventario individual de skips y evidencia en [TRAZABILIDAD](TRAZABILIDAD.md).

Verificación posterior del 2026-10-08 para sistema experto V1: unitarias 66/1973, feature SQLite 23/1275, feature PostgreSQL desechable 23/1414 e inventario operativo 19/223 PASS; build PASS. Se instalaron con autorización 23 tablas expertas vacías en `remembermind_dev`, preservando las 71 operativas y sus datos. La suite global de ese árbol de trabajo terminó con 888 tests, 18.077 aserciones, cinco fallos, un error y 15 omitidos. El error de una fixture experta fue corregido y su batería específica revalidada en ambos motores; los cinco fallos restantes corresponden a expectativas de vistas, copy, decimales y redirección ajenas al experto. **Gate global actual: FAIL**; el PASS histórico de Fase 4 no certifica el árbol actual. [Implementación y límites](sistema-experto/IMPLEMENTACION_V1.md). No hay conocimiento clínico cargado ni activado.

Versión del producto: no se encontraron tags locales; una release global no fue verificada. El contrato BDD operativo vigente es V2.1 congelado con extensión V2.2 aprobada; las 23 tablas expertas aprobadas se inventarían separadamente. No equiparar esas versiones a una release de todo RememberMind.

## Stack verificado

Versiones bloqueadas en archivos del repositorio; las ejecuciones aisladas de Fases 3–4 acreditan PHP y los comandos descritos en TRAZABILIDAD, no todos los componentes en producción:

| Componente | Versión en repo | Fuente |
|---|---|---|
| PHP | requisito ^8.3; platform 8.3.0 | composer.json; F3 PHP 8.4.24 histórico, F4 PHP 8.3.33 habilitado |
| Laravel | 13.33.0 | composer.lock |
| Jetstream / Sanctum | 5.5.3 / 4.3.3 | composer.lock |
| Livewire | 4.4.6 | composer.lock |
| Permission / Activitylog | 7.4.2 / 4.12.3 | composer.lock |
| PHPUnit | 12.5.36 | composer.lock |
| DomPDF / Laravel PDF / Excel | 3.1.2 / 2.13.1 / 3.1.70 | composer.lock |
| Tailwind / Vite | 3.4.19 / 8.3.1 | package-lock.json |
| Chart.js / GSAP / AOS | 4.5.1 / 3.15.0 / 2.3.4 | package-lock.json |
| Alpine | Integrada por Livewire; versión independiente no acreditada por package-lock | No copiar versión desde guía antigua |
| Node | >=22.12.0 para satisfacer el lock completo, incluido Puppeteer 25.12.0 | engines en package-lock.json; runtime aislado Node v26.7.0 |
| PostgreSQL / SQLite | PostgreSQL integrado/staging/producción; SQLite :memory: pruebas rápidas | baseline técnico y phpunit.xml |

No se habilita MySQL/MariaDB por aparecer en configuración Laravel. CI configura PostgreSQL 18; su presencia no demuestra ejecución exitosa ni versión desplegada.

## Base de Datos

- **Baseline vigente:** núcleo congelado BDD Operativa V2.1, más extensión aprobada V2.2 de objetivos individuales.
- **Versión:** V2.1 + V2.2 en el alcance aprobado; no se reemplaza el núcleo.
- **Número actual de tablas operativas declarado:** 71, excluyendo técnicas Laravel/Jetstream/Sanctum/Spatie y migrations.
- **Documento fuente:** [índice y secuencia BDD](base-de-datos/README.md), [baseline](base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario núcleo](base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [decisión V2.2](base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md).
- **Evidencia de código:** 69 creaciones individuales del núcleo inicial + valoraciones normalizadas + objetivos; 71 nombres operativos únicos.
- **Evidencia de tests:** BddOperativaV2Test define assertCount(71); ejecutado en PostgreSQL desechable en Fase 3. Resultado del gate final en TRAZABILIDAD.
- **BDD física instalada y concurrencia PostgreSQL:** migración/seed y carrera real de la Action de admisión ejecutadas en base desechable; no certificación de producción ni de otras operaciones concurrentes.
- **Histórico:** diccionario V2.0 de 69 tablas. Su nombre/fecha no gobierna trabajo nuevo.

La discrepancia de cifras anteriores queda conciliada por decisiones aprobadas. Persisten conflictos específicos de estados/catálogos en [decisiones](DECISIONES_PENDIENTES.md); no se corrigen reglas sensibles por inferencia.

## Estado por área

Leyenda: ✅ ESTABLE (exige evidencia suficiente vigente); 🟡 EN DESARROLLO; 🟠 PARCIAL; 🔵 DISEÑO; 🔴 BLOQUEADO para alcance concreto; ⚪ PENDIENTE. Clasificación conservadora por capa; Fase 2 solo mapeó tests. Fases 3–4 ejecutan los alcances descritos en TRAZABILIDAD; PASS de suites no declara estabilidad integral del producto.

| Área | Estado | Evidencia presente | Límite |
|---|---|---|---|
| Institucional | 🟠 PARCIAL | Identidad, roles/Policies, jornadas/asignaciones; RolesBaselineCongeladoTest | No aprobación global de todos los procesos |
| Administración | 🟠 PARCIAL | CentroCoordinacionService, UI/rutas y DashboardAdministracionIntegracionVisualTest | Operación diaria; RRHH Gerente |
| Residentes | 🟠 PARCIAL | Residente, controlador, relaciones; BddOperativaV2Test | Lectura Familiar restringida en Fase 3; otras entradas/legacy no quedan certificadas globalmente |
| Admisión | 🟠 PARCIAL | FormalizarAdmision, revisión/preadmisión, CasosPreadmisionTest | Rollback/carrera de admisión acreditados Fase 3; estados físicos siguen DEC-OPEN-001 |
| Camas | 🟠 PARCIAL | Locks e índices/Model de ocupación; OcupacionCamaIntegrityTest | Carrera de misma cama y rollback admisión acreditados Fase 3; otras operaciones no certificadas |
| Enfermería | 🟡 EN DESARROLLO | TurnoEnfermeriaService/PaseTurnoPanel y tests; cambios previos sin commit | Validación integral/QA pendientes |
| Médico | 🟠 PARCIAL | Prescripción/administración, atenciones, objetivos y tests | No certificación clínica o end-to-end actual |
| Psicología | 🟠 PARCIAL | DashboardPsicologo, valoración transversal, instrumentos, tests | Metodología/licencias de escalas no acreditadas |
| Nutrición | 🟠 PARCIAL | RoleDashboardDataService y valoración transversal | No módulo independiente completo demostrado |
| Fisioterapia | 🟠 PARCIAL | Dashboard/valoración transversal | Continuidad y QA integral no demostradas |
| Pedagogía | 🟠 PARCIAL | Dashboard/valoración transversal | Toda planificación/ejecución no acreditada |
| Familiar | 🟠 PARCIAL | Dashboard por vínculo y ResidentePolicy; RoleDashboardDataTest | Fase 3 bloquea clínica/documentos no publicados y acredita identidad/visitas propias; DEC-OPEN-006 sigue OPEN |
| Alertas | 🟠 PARCIAL | AlertaController, AlertasService y tests de ciclo | Fase 3 centraliza creación/mutación con eventos atómicos; no certifica todos los orígenes concurrentes |
| Reportes | 🟠 PARCIAL | Rutas, servicios, exports y ReporteV2Controller; F4 corrige cierre Blade y verifica tres exports de áreas | Permiso/cuenta/error/auditoría ejercitados; bytes/formato Excel y entrega real no acreditados por mocks |
| UX/Design System | 🟡 EN DESARROLLO | Tokens/componentes, contrato visual y stack UX; F4 shell/sidebar y equivalencia computada de filtros | No QA visual/funcional integral de todas las pantallas ni rediseño |
| Sistema experto | 🟠 TÉCNICO SIN ACTIVACIÓN | [Implementación V1](sistema-experto/IMPLEMENTACION_V1.md): 23 tablas, núcleo COG-MEM, trazabilidad y consulta administrativa; pruebas sintéticas SQLite/PostgreSQL | Conocimiento institucional vacío; contratos de contenido, validación profesional y activación clínica pendientes |
| Producción | ⚪ PENDIENTE de verificación | Guía de despliegue/reversión y configuración CI | Despliegue, HTTPS, backups, colas y restauración NO VERIFICADOS |

Mapas y localizadores: [dominio funcional](sistema/MAPA_DOMINIO_FUNCIONAL.md), [arquitectura](arquitectura/README.md), [roles](arquitectura/REMEMBERMIND_ROLES_BASELINE_CONGELADO.md). Estos son enlaces de descubrimiento; no certificación de completitud.

## Flujo institucional

Preadmisión → revisión → APROBADA/RECHAZADA → admisión formal con cama → residente admitido. Aprobación por sí sola no crea residente. Fuente: AGENTS y baseline, con Action FormalizarAdmision como evidencia de implementación. No introducir CRUD directo para sortear ese límite.

## Problemas conocidos

[TECH-001..TECH-011](DEUDA_TECNICA.md) conserva hallazgos históricos y estados actuales: 001/002/004/008/011 VERIFIED_RUNTIME acotado; 005/009/010 PARTIAL; 003/006/007 OPEN. Suite global y límites de prueba en TRAZABILIDAD.

## Decisiones abiertas

[DEC-OPEN-001..006](DECISIONES_PENDIENTES.md): estados institucionales, catálogos, etiquetas clínicas, PRN/reintentos, método/derechos/validación experta y publicación clínica a familiares. La separación contador BDD histórico/actual está resuelta; no abrir otra aprobación sobre la extensión existente.

## Trabajo actual

09/10/2026 — Dolor V2 incorpora la extensión aprobada de tres columnas,
inicial/reevaluación y popup longitudinal de Mis residentes. Inventario de 71
operativas sin cambios; los resultados exactos se mantienen en
[informe Dolor V2](frontend/FORMULARIO_DOLOR_V2_RESULTADO.md), incluida la QA
pendiente. Dolor dirigido y regresión Signos PASS; JS104/build PASS.
PHP global:935 PASS,15 omitidas,5 fallos previos (19.028 aserciones), gate FAIL.
No equivale a una release global; sin commit.

Histórico: Fase 2 documental entregada con PASS de revisión estática independiente. Existen [arquitectura contrastada](arquitectura/ARQUITECTURA_VIGENTE.md), siete contratos núcleo y [trazabilidad/cobertura estática](TRAZABILIDAD.md): 22 dominios, siete flujos, 20 invariantes y 22 métodos de test mapeados. Esto no acredita ejecución ni completitud del producto. El alcance y evidencia del PASS documental están en TRAZABILIDAD; runtime_verified=false.

Fase 3 implementó hardening A–E y obtuvo evidencia runtime aislada: mínimo de signos, eventos de alerta, proyección de continuidad, controles contextuales y respuestas públicas de error en entradas afectadas. Fase 4 completa la estabilización global automatizada autorizada, con baseline, clasificación causal, pruebas dirigidas/de módulo y cuatro gates finales PASS. [Deuda](DEUDA_TECNICA.md) mantiene VERIFIED_RUNTIME acotado, PARTIAL y OPEN; no certifica el sistema completo. Fase 2 no modificó código funcional; Fases 3–4 sí, con evidencia registrada en TRAZABILIDAD.

Se conservan cambios previos de limpieza legacy/áreas/cuidados/reportes/tests sin commit. Las skills existentes orientan investigación; su presencia o validación de instrucciones no prueba producto.

## Próximo objetivo

Fase 4 termina con PASS de los cuatro gates automatizados y preservación del hardening F3. Continuar con un lote aprobado y acotado de deuda pendiente, comenzando por fronteras de excepción/autorización no ejercitadas y garantías que hoy son solo de aplicación. TECH-005/009/010 siguen PARTIAL; TECH-003/006/007 y DEC-OPEN-001..006 siguen OPEN. Ningún PASS concede autorización para decisiones clínicas/BDD ni publicación Familiar. No se inicia ese lote en esta tarea. [Roadmap](ROADMAP.md) mantiene ideas y tareas futuras separadas.
