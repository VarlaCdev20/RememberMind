---
title: Changelog documental de RememberMind
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
supersedes: []
related_docs: [README.md, GOBERNANZA_DOCUMENTAL.md]
related_modules: []
---

# Changelog documental

Registro de decisiones de documentación; no changelog de commits ni prueba de funcionalidades. Este registro se inicia en Fase 1; no inventa una historia de entregas anteriores.

## 2026-10-06

### Registro posterior — 09/10/2026: Eliminación V2

- Extensión aprobada: trece columnas nullable en la tabla existente registros_eliminacion; cantidad/caracteristica preservadas sin conversión automática y sin nuevas tablas.
- Formulario estructurado por tipo, escritor único autorizado y timeline longitudinal; sin interpretación clínica ni alerta automática.
- [Resultado y límites reales de verificación](frontend/FORMULARIO_ELIMINACION_V2_RESULTADO.md). Aceptación del lote verificada en runtime; no se altera documentación histórica para atribuirle campos nuevos.
- Sin stage, commit ni push; Movilidad fuera de este lote.

### Registro posterior — 09/10/2026: Dolor V2

- Decisión aprobada: origen, frecuencia y factores de alivio en valoraciones_dolor; self-FK con mismo residente, sin nueva tabla.
- Contrato actual: inicial/reevaluación con nuevas filas, fecha/autor del servidor, mapa sin persistencia paralela, popup longitudinal y presentación numérica coral suave.
- Evidencia y límites: [resultado Dolor V2](frontend/FORMULARIO_DOLOR_V2_RESULTADO.md). Se conserva el antecedente del patrón inicial como HISTORICAL; no modifica los documentos históricos de BDD.
- Sin stage, commit ni push por instrucción expresa; no certificar el lote mientras falten gates de aceptación.

### Fase 2 — arquitectura, dominio, seguridad y contratos

- Added: 16 documentos: arquitectura/ARQUITECTURA_VIGENTE; sistema/FLUJOS_GERIATRICOS, ESTADOS_Y_CICLOS_DE_VIDA, INVARIANTES_NEGOCIO, CONTINUIDAD_ASISTENCIAL; seguridad/MODELO_AUTORIZACION, MATRIZ_ROLES_PERMISOS; modulos/_PLANTILLA_CONTRATO_MODULO y siete contratos README; TRAZABILIDAD con cobertura estática.
- Changed: sistema/MAPA_DOMINIO_FUNCIONAL consolidado en 22 dominios; portal solo enlaces/routing de entrega; arquitectura/README enlaza descripción ampliada. ESTADO_ACTUAL/ROADMAP reflejan encargo de Fase 2, sin marcar producto estable; DEUDA_TECNICA amplía TECH-002/005/006 y añade TECH-008..011.
- Decisiones: ninguna nueva; DEC-OPEN-001..006 conserva significado. Baseline/decisiones aprobadas no se reescriben ni se reclasifica historia como contrato.
- Verificación: commit base + árbol sucio; runtime_verified=false. Sin migraciones, seed, suites, build ni BDD real; sin cambios funcionales, commit o push. [Trazabilidad](TRAZABILIDAD.md) registra comprobaciones locales y estado de revisión independiente.
- Revisión: se corrigieron orden de evaluación antes de persistir signos, diagrama de retorno Policy, transaction real de creación en AlertasService y localizador obtenerContextoClinicoResidente. Una primera revisión fue interrumpida por cuota; la revisión independiente final completó lectura/contraste y devolvió PASS documental estático, sin certificar runtime.
- Ajustes finales revalidados: receptor de pase opcional según entrada, normalización de fecha/hora por actor, validación laboral HTTP distinta de panel/ARJ y mínimo de mediciones de signos frente a peso/dolor no persistidos (TECH-011).

### Added

- Auditoría de 91 documentos previos, clasificación/autoridad y evidencia local.
- ESTADO_ACTUAL, ROADMAP, GLOSARIO_DOMINIO con 30 términos, DECISIONES_PENDIENTES y DEUDA_TECNICA.
- Convención de metadatos y estados oficiales en GOBERNANZA_DOCUMENTAL.
- README de carpetas históricas y archivo íntegro de solicitud de stack de sistema.
- Verificación documental y revisión independiente de cierre.

### Changed

- docs/README es puerta de entrada por área/lector; distingue fuentes, evidencia, planes e historia.
- README raíz corrige inventario BDD, límite Gerente/Administrador, requisito Node del lock y aprobación global no acreditada.
- CODEX_SETUP enlaza catálogo/selectores actuales, sin perpetuar pack de cinco skills.
- Índice BDD muestra cadena aprobada y reemplaza cuatro enlaces rotos por migraciones presentes.
- Metadatos/avisos de alcance en 39 documentos importantes, sin alterar contratos clínicos/estructurales.
- Guía UX recupera su contenido previo: se extrae solicitud completa, verificada contra contenido original.
- Mapa de fuentes y guía del stack sistema reflejan extracción ya realizada.

### Deprecated

- Arquitectura frontend anterior como norma de rutas/visual: consultar arquitectura canónica, resources/AGENTS y contrato visual/DS vigentes.
- Afirmaciones anteriores de README sobre 70 como inventario total actual, Administrador RRHH, Node genérico y suite “100%” sin evidencia de ejecución.

### Historical

- architecture-audit y refactorizacion-total conservan rutas; no gobiernan implementación.
- Auditorías V1, diccionario V2.0, inventario/resultados de migración inicial y snapshots/plan de UX marcados como historia.
- Solicitud de sistema antes mezclada en guía UX se preserva en docs/historico; no es protocolo clínico.

### Conflicts resolved

- Cantidad/versión BDD: 69 V2.0 histórica →70 núcleo V2.1 → +1 objetivos V2.2 aprobado =71 actuales declaradas; código y test definido concuerdan. Sin verificar DB física.
- Autoridad de carpetas frontend y límites RRHH: se aplica instrucción/baseline superior, no propuesta o README inferior.
- Mezcla solicitud/guía UX y rutas inexistentes del índice BDD.

### Conflicts still open

- DEC-OPEN-001 estados institucionales; DEC-OPEN-002 catálogos; DEC-OPEN-003 etiquetas clínicas; DEC-OPEN-004 PRN/reintentos; DEC-OPEN-005 metodología/derechos del sistema experto; DEC-OPEN-006 publicación clínica familiar. Permanecen abiertos.
- TECH-001..007 registran riesgos/deuda con evidencia y límites de reproducción.

### Revisión quirúrgica final

- Se explicita verificación estática y ausencia de certificación runtime en las 55 cabeceras con `verified_against_commit`.
- El portal delimita su autoridad a gobernanza documental/routing y describe las 71 tablas como evidencia estática. Se distingue el índice experto CURRENT de sus diseños PROPOSED.
- Referencias DEC-OPEN-001..006 explicitadas en orden canónico; decisiones conservadas. Enlaces y caveman-review documental repetidos, con resultado PASS.

No código funcional, BDD, permisos, reglas clínicas, UX, migraciones o configuración modificados. Sin suites/build/migración/despliegue ni commit. El resultado de revisión y la comprobación de preservación se registran en [VERIFICACION_DOCUMENTAL_FASE_1](VERIFICACION_DOCUMENTAL_FASE_1.md).


## 2026-10-06 — Fase 4: precheck de estabilización

- Autorización de Fase 4 registrada en ESTADO_ACTUAL y TRAZABILIDAD, preservando resultados acotados de Fase 3.
- Baseline PHP intentado sin editar código: bloqueado antes de ejecutar tests por Windows Control de aplicaciones / mbstring. Runtime alternativo PHP 8.5.9 incompatible con dependencia instalada; no se cambia lockfile ni política.
- npm test: 30 tests, 2 fallos; sidebar reprodujo timeout cinco veces. Build PASS; sin reparaciones todavía ni clasificación causal definitiva de fallos JS.
- Conexión testing exclusiva e inventario 71 presentes confirmados; sin nuevo reset/seed. Solo tres documentos existentes actualizados con evidencia de precheck. Fase 4 incompleta, instalación/runtime pendiente; sin staging/commit/push.

## 2026-10-06 — Fase 4: estabilización global automatizada

- PHP: instalación oficial 8.3.33 y PATH de usuario con autorización «habilita php»; extensiones nativas y composer check-platform-reqs PASS. Se conserva configuración requerida, .env y política de seguridad. PHP 8.4.25 continúa bloqueado por Windows; 8.5 no satisface PhpSpreadsheet instalado.
- Baseline real antes de editar código: 670 tests / 12.082 aserciones, 14 fallos, 0 errores, 13 omitidos; JS 28/30. Los 16 fallos se clasificaron por contrato y causa antes de reparar.
- Changed: cierre Blade de reporte, contenedor canónico de gráfica, presentación de la orden real de medicación sin datos de ejemplo, extracción exacta de 54 HEX a tokens existentes y especificidad de visibilidad de iconos del tema. Sidebar funcional/animaciones sin cambios; fixture resuelve @js antes de iniciar Alpine y detecta errores. Expectativas obsoletas/fixtures se corrigen conservando invariantes y negativos.
- Seguridad: tres exports Livewire de áreas conservan areas.reportes/cuenta ACTIVO; reportan errores inesperados seguros y registran auditoría de generación solo después del retorno del exportador. 13 nuevos casos de autorización/error/éxito, 1 ausencia de alergia y 2 SQL directo. Mocks de exportación no certifican formato Excel ni entrega efectiva.
- Verificación final: PHP 686 tests / 12.219 aserciones, 0 fallos, 0 errores, 13 omitidos justificados; 900,178 s reloj. npm test 30/30, build PASS (sin warning PLUGIN_TIMINGS en el run final); sidebar 5/5. Reset con seed posterior a suite en PostgreSQL descartable PASS, 71/71 tablas. Seis cohortes F3 reejecutadas dentro de la suite completa PASS; Pint conserva siete archivos con fixers previos, sin nuevos hallazgos de formato.
- Gate global automatizado F4 PASS; no certificación clínica/producción. TECH-005/009/010 PARTIAL, 003/006/007 OPEN, DEC-OPEN-001..006 intactas. Cuatro documentos existentes actualizados; sin nuevos documentos, estructura BDD, permisos/reglas nuevos, staging, commit ni push. Inventarios y límites en [TRAZABILIDAD](TRAZABILIDAD.md).
- Revisión independiente caveman-review PASS acotado, sin hallazgos accionables pendientes; evidencia local respaldada, Caveman Cloud NOT VERIFIED. Comprobación final de 955 enlaces locales en 109 Markdown: 0 rotos; preservación de 1321 rutas, 24 archivos modificados y .env intacto.
