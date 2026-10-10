---
title: "Dolor V2 — resultado y evidencia de aceptación"
status: CURRENT
version: "2.2"
last_reviewed: 2026-10-09
owner: RememberMind
source_of_truth: false
verification_scope: VERIFIED_RUNTIME
acceptance_status: ACCEPTED
runtime_verified: true
related_docs: [../base-de-datos/DECISION_DOLOR_V2.md, ../base-de-datos/AUDITORIA_FORMULARIO_DOLOR.md]
related_modules: [Enfermería]
---

# DOLOR V2 — RESULTADO

## Alcance

Enfermería → Mis residentes → Dolor, inicial y reevaluación. Base `7a6758e8`,
rama `FORMULARIOS`, cambios ajenos previos conservados. No certifica una
release global. Sin stage, commit ni push.

## Schema y normalización

| Criterio | Resultado |
|---|---|
| cod_valoracion_origen | ADDED, string(20) nullable |
| frecuencia | ADDED, string(40) nullable |
| factores_alivio | ADDED, text nullable |
| Self-FK | PASS: existencia, RESTRICT y mismo residente por FK compuesta |
| Índices | PASS: origen, clave candidata del par; reutiliza índice de historia |
| Rollback/reapply | PASS SQLite y PostgreSQL aislado; preserva filas e índices/triggers anteriores |
| 3FN | PASS en el alcance ampliado: atributos de una valoración, raíz relacionada sin duplicarla |

[Decisión estructural y límite del rollback](../base-de-datos/DECISION_DOLOR_V2.md).

## Valoración inicial, reevaluación e historia

| Criterio | Resultado |
|---|---|
| Inicial | PASS, EVA vacía al abrir; origen/respuesta NULL |
| Reevaluación | PASS, nueva fila apuntando a raíz inicial autorizada VIGENTE no futura |
| Historia preservada | PASS, episodio sintético 8→5→3 en tres filas; inicial intacta |
| Respuesta | PASS, texto opcional únicamente en reevaluación, nunca EVA posterior |
| Fecha/hora | Server controlled YES, editable NO; Locked y now() al guardar |
| EVA | PASS, entero obligatorio 0–10, cero válido; presentación aprobada verde/amarillo/rojo, descrita en la actualización UX inferior |
| Mapa corporal | PASS, frontal/posterior, múltiple, chips/manual/max120; persistencia adicional NONE |
| Localización/duración | PASS, max120; duración positiva + unidad en string80 existente |
| Frecuencia/desencadenante/factores alivio | PASS, max40/trim/opcionales; frecuencia ofrece atajos editables, sin catálogo clínico nuevo |
| Intervención | PASS, medida efectuada, sin prescripción |
| Resumen descriptivo | PASS, EVA/anterior/diferencia matemática/características, sin inferencia clínica |
| Continuidad | PASS, registrar/evolución/reevaluar, contexto y descarte protegidos |
| Autoría/autorización | PASS, sesión activa, permiso, turno, asignación, Policy y personal autenticado |

Se eliminó el guardado antiguo alternativo de Mis residentes. Manipular el
estado fuera de captura da 403 y cero filas. La preparación/guardado de
reevaluación exige también permiso de lectura. No se crean alertas por EVA,
catálogos clínicos, recomendaciones ni permisos.

## Gráfica popup

| Criterio | Resultado / evidencia |
|---|---|
| Open/close | PASS, manual popover, captura preservada |
| Move | PASS, mouse x860/y110→x830/y140; teclado probado |
| Resize | PASS, mouse560→530px; teclado y límites del viewport |
| Reset | PASS, botón/Home, desktop560×680, compacto altura280 |
| Escape/foco | PASS, cierra solo popup y devuelve foco a Ver evolución |
| Preview/tooltip | PASS, hueco/discontinuo; tooltip sigue EVA y se limpia al vaciar |
| Episode history | PASS, 8→5→3, respuestas por reevaluación, sin preview duplicada tras guardar |
| 0/1/1+preview | PASS JS/backend y navegador real: vacío sin gráfica, un punto sin tendencia, histórico + preview diferenciados |
| Historial largo | PASS, siete guardadas o seis+preview; X temporal, Y0–10 y DTO plano |

Sin null/NaN/undefined/Infinity/[object Object] visibles en los estados
observados. No se infiere tendencia con un punto.

## Responsive

| Objetivo | CSS observado | Resultado |
|---|---|---|
| 1440 | 1440 | PASS, panel1160, dos columnas |
| 1280 | 1280 × 900 | PASS, medida innerWidth/innerHeight exacta; sin overflow, popup dentro del viewport y captura conservada |
| 1024 | 1024 | PASS, panel992, dos columnas |
| 768 | 768 | PASS, panel736, una columna |
| 390 | 390 | PASS, panel358, una columna, mapa frontal/posterior alternable |

Sin overflow horizontal. Popup dentro del viewport: 560×680 desktop,
560×280 a 768 y 374×280 a 390. Captura conservada al cambiar tamaño.

## Accessibility

PASS en comprobaciones acotadas: labels asociados, aria-invalid y errores,
radio EVA, Enter/Espacio en mapa/puntos, aria-pressed, foco visible, Escape y
retorno de foco. Campos/acciones principales de 44px; zonas anatómicas pequeñas
tienen alternativa textual. Reduced motion PASS en navegador real después de
desactivar animaciones de Windows: matchMedia reduce=true, sin animación decorativa,
EVA, mapa por teclado y popup operativos. No es auditoría WCAG integral.

## Tests

| Gate | Evidencia |
|---|---|
| Migration SQLite/PG | PASS, up/down, índices, self-FK, cruce y RESTRICT |
| Dolor + integraciones SQLite | PASS, 37 pruebas /1.420 aserciones |
| Dolor final PostgreSQL | PASS, 27 pruebas /193 aserciones |
| Authorization | PASS, negativos de origen/residente/permisos/competencia/contexto |
| Signos regression | PASS, 35 pruebas /184 aserciones; presentación de Signos intacta |
| Seeder histórico | PASS, 3 pruebas /35 aserciones, idempotencia/guardas |
| PHP full | PASS: 940 pruebas, 15 omitidas por condiciones del proyecto, cero fallos; 19.052 aserciones, 956,97s |
| JS | PASS, 104 pruebas, cero fallos |
| Build | PASS, Vite8.3.1 |
| PostgreSQL fresh --seed | PASS, solo remembermind_experto_test_20261009_dolorv2 |

Logs anteriores conservados en storage/logs/dolor-v2-*. El cierre vigente usa
`dolor-cierre-php-completo.log`, `dolor-cierre-targeted.log` (27/194),
`dolor-cierre-signos.log` (35/184), `dolor-cierre-js.log` (104/104),
`dolor-cierre-build.log` y `dolor-cierre-pg-fresh.log`.
La ejecución PHP completa actual sustituye el FAIL previo de cinco casos y las
ejecuciones interrumpidas durante la preparación de QA; no se suman sus resultados.

### Fallos globales resueltos — fuera del flujo Dolor

| Ubicación | Causa / clasificación / corrección |
|---|---|
| FiltrosDisenoUnificadoTest:43 | BUG_PRODUCT: wrapper local omitía componente canónico; usa x-ui.filter-bar manteniendo campos/bindings |
| MiTurnoServiceTest:593 | CONTRACT_DRIFT: esperaba título compacto sustituido por bienvenida compartida aprobada; verifica institución y contexto del turno, conserva residente y no-mutación |
| MisPacientesRedisenadaTest:2046 | FORMAT_DRIFT: modelo no normalizaba cantidad_ml decimal(8,2), SQLite devolvía entero; cast decimal:2, sin cambiar expectativa ni persistencia |
| PaseTurnoReconstruidoTest:71 | FORMAT_DRIFT: mismo mapping decimal compartido; cast anterior conserva 250.00 ml en pase |
| RolesVistasPermisosMatrixTest:89 | EXPECTED_REDIRECT: contrato administrativo vigente sustituye directorio antiguo; prueba exige redirect al explorador y 200 en destino, mantiene denegación de Usuarios |

Fuentes CURRENT: contrato visual y petición aprobada de bienvenida compartida;
componente canónico filter-bar; diccionario BDD sección43 cantidad_ml decimal(8,2);
docs/administrador/04-HABITACIONES-Y-CAMAS.md (URL antigua redirige al explorador).
Pruebas dirigidas: 6/40 más hidratación sucesiva 1/9, todas PASS; PHP completo PASS.
No se debilitaron permisos, Policies, reglas ni expectativas clínicas.
Cambios de este cierre: cuatro archivos ajenos a Dolor; Dolor modified **NO**,
Signos modified **NO**, schema changes **NONE**.

## Docs updated

DECISION_DOLOR_V2, auditoría Dolor, sección 27 del diccionario CURRENT, baseline/
índice BDD, seed histórico, este resultado, ESTADO_ACTUAL, TRAZABILIDAD,
CHANGELOG e índice general. El informe del lote anterior conserva su cuerpo
como HISTORICAL. No se reescribe el diccionario histórico69.

## BDD table count / otros cambios

**UNCHANGED: 71 operativas + 23 expertas aprobadas.** PostgreSQL aislado: 109 tablas
físicas, incluidas 15 técnicas. Other schema changes: **NONE** fuera de las tres
columnas y relación/índices aprobados. Other forms modified: **NONE** en este
lote. Se retiró el fallback antiguo de Dolor en Mis residentes; el módulo
RegistrosEnfermeria fuera de alcance conserva su flujo.

## Gaps / decisiones aún abiertas

- Tipo_dolor: sin vocabulario/catálogo aprobado, permanece NULL.
- Impacto funcional: fuera de las tres columnas autorizadas.
- Cierre de aceptación: **ACCEPTED / VERIFIED_RUNTIME**, gates requeridos verdes.
- Alcance: aceptación del flujo Dolor V2; no certificación WCAG ni de producción.

## Caveman review

**PASS en revisión acotada del código tras las correcciones**, sin certificar
el gate global ni constituir revisión independiente.

Revisión acotada: detectó el guardado alternativo, corregido en una fase de
implementación y verificado con rechazo sin filas. Migración conserva triggers;
FK compuesta asegura residente; dominio revalida autorización/procedencia.
Sin otro hallazgo bloqueante observado. No es revisión independiente.
Caveman Cloud evidence **UNAVAILABLE**: no herramientas/CLI accesibles;
no se inventan costes, trazas o ahorro.

## Visual QA

**PASS** en el alcance requerido. Se conserva QA previa vigente y se completan
los cinco casos restantes en navegador real, con PostgreSQL desechable
remembermind_experto_test_20261009_dolorv2 y cuenta/residentes sintéticos.
Las fixtures 0/1 se admitieron mediante FormalizarAdmision, sin borrar historia.

| Caso restante | Resultado y evidencia |
|---|---|
| 0 history | PASS: cero filas, empty state, cero puntos/gráfica ficticia |
| 1 history | PASS: una fila, un punto EVA4, mensaje explícito sin tendencia inferida |
| Persistence error | PASS: LOCK TABLE SHARE temporal permite lecturas y bloquea INSERT; statement_timeout 2000ms produce SQLSTATE57014 real. Formulario abierto, EVA6/localización/frecuencia conservados, error seguro, sin éxito falso ni SQL visible; conteo permanece1 |
| Reduced motion runtime | PASS: preferencia real de Windows, matchMedia=true; sin animación decorativa, EVA/mapa/popup usables |
| 1280 exact | PASS: innerWidth1280/innerHeight900, sin overflow; popup x700/y110/560×680; datos conservados |

El bloqueo se liberó mediante rollback de su transacción, sin DDL ni mutación de
datos. Servidor QA separado en puerto8002, cookie independiente y APP_DEBUG=false;
remembermind_dev intacta. Sin null/NaN/undefined/Infinity/[object Object] visibles.
Matriz reproducible ignorada: storage/app/qa/dolor/closure-runtime-evidence.json.
Capturas del cierre: cierre-zero-1280.jpg, cierre-one-1280.jpg,
cierre-1280-exact.jpg, cierre-reduced-motion.jpg, cierre-persistence-error.jpg.

Capturas ignoradas en `storage/app/qa/dolor/`: `v2-final-form.jpg`,
`v2-inicial-registrada.jpg`, `v2-1440.jpg`, `v2-1280.jpg`, `v2-1024.jpg`,
`v2-768.jpg`, `v2-390.jpg`, `v2-popup-mobile.jpg`, `v2-final-popup.jpg`. Las tres filas 8→5→3 de QA
pertenecen a un residente sintético; no se borró/sobrescribió historia previa.

## Actualización UX solicitada después del cierre — 09/10/2026

La propietaria solicita desplegables, recuperar colores EVA aprobados, reforzar
feedback del mapa y mejorar composición. Esta actualización sustituye la
presentación coral uniforme anterior; conserva el contrato de persistencia V2.

- Rangos recuperados de la aprobación documentada en FORMULARIO_DOLOR_PATTERN_V1:
  vacío neutro; 0 verde suave; 1–3 verde; 4–6 amarillo; 7–10 rojo.
  Fuente única: `config/enfermeria.php`, enviada al cliente. Son intensidad
  descriptiva; no crean diagnósticos, alertas ni clasificación persistida.
- `clinical-capture-select`: duración/unidad/frecuencia, opciones de captura y
  «Otro valor…». Conserva números exactos/decimales, unidades libres y frecuencia
  libre max40; sincroniza Livewire y dirty state. No introduce un catálogo BDD.
- Seis opciones EVA por fila en escritorio, cinco en móvil; mapa mayor,
  zona/chips con color actual y feedback breve; fallback sin animación.
- Comparación junto al mapa; resumen sin repetir EVA/diferencia.
- Popup manual conserva geometría, teclado, resize/reset y datos de captura
  mediante los controles existentes de Signos; Signos no se modifica.

### Verificación de esta actualización

| Gate | Resultado actual |
|---|---|
| PHP dirigido | PASS: 55 pruebas, 353 assertions; `dolor-ux-php.log` |
| JavaScript | PASS: 105 pruebas; rangos desde config, selectores/dirty, historia y popup; `dolor-ux-js.log` |
| Build | PASS: Vite; `dolor-ux-build.log` |
| Runtime rangos | PASS: 0,3,4,6,7,10 y regreso a2; texto y color coherentes |
| Desplegables | PASS: opciones y duración12.5/frecuencia libre; dato conservado al cambiar modo |
| Validación backend | PASS: duración12.5 sin unidad rechazada; error local, EVA4/localización/frecuencia permanecen |
| Mapa y teclado | PASS: Enter selecciona hombro izquierdo, ambos lados anatómicos/chips se sincronizan |
| Popup escritorio | PASS: 1280×900 CSS exactos, formulario x16/w672 y popup x700/w560; sin solapamiento |
| Popup móvil | PASS: 390×844, popup compacto dentro del viewport; scroll interno |
| Responsive | PASS: 1440/1280/1024/768/390 CSS px; sin overflow, EVA targets mínimo44×44 |
| Literales inválidos | PASS: sin null/NaN/undefined/Infinity/[object Object] visibles |
| Motion | Runtime normal revisado; fallback reduced-motion comprobado por prueba de stylesheet. No se repitió emulación runtime reducida en este lote |

Las suites PHP completas y fresh/seed superiores pertenecen al cierre previo;
no se vuelven a atribuir como nuevas ejecuciones en esta actualización visual.
Sin cambios de backend, permisos, reglas clínicas, Signos ni schema. La prueba
de validación no insertó valoraciones; se descartó toda captura de QA.
Capturas sintéticas ignoradas: `storage/app/qa/dolor/ux-1440.jpg`,
`ux-1024.jpg`, `ux-768.jpg`, `ux-390.jpg`, `ux-popup-1280.jpg`,
`ux-popup-390.jpg`. Los viewports 1280/1024 se midieron exactos en DOM.

## Commit

**NONE.** Sin stage, commit ni push, por instrucción explícita de este cierre.
