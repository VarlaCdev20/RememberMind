---
title: "Movilidad V2 — resultado de implementación y verificación"
status: ACCEPTED
version: "1.0"
last_reviewed: 2026-10-10
owner: RememberMind
source_of_truth: false
verification_scope: MOVILIDAD_V2_WORKTREE_TESTS_AND_VERIFIED_RUNTIME
runtime_verified: true
---

# MOVILIDAD V2 — RESULTADO

**ACCEPTED / VERIFIED_RUNTIME** para Enfermería → Mis residentes → Movilidad y sus escritores genéricos auditados. Rama `FORMULARIOS`, base `7a6758e8`, árbol de trabajo sin commit. No acepta otros módulos ni certifica WCAG o producción. Los cambios previos del árbol no pertenecen a este lote.

## Schema

- Table: `registros_movilidad`.
- New tables: **NONE**; inventario operacional conservado en 71, tablas expertas contadas aparte.
- Added columns: `motivo_registro`, `actividad_realizada`, `distancia_metros`, `dolor_movilidad`, `mareo`, `disnea`, `debilidad`, `cambio_habitual`, `tolerancia_movilidad`.
- Removed columns: **NONE**. Otras tablas, PK/FK, índices y relaciones modificadas: **NONE**.
- Autoridad: [decisión aprobada](../base-de-datos/DECISION_MOVILIDAD_V2.md); [auditoría CURRENT](../base-de-datos/AUDITORIA_FORMULARIO_MOVILIDAD.md).

## Existing fields preserved

`marcha`, `traslado`, `tipo_apoyo`, `dispositivo`, `equilibrio`, `fatiga`, `riesgo_caida`, `observacion`: **PASS**. Contexto, fecha y estado conservados. Historia anterior sin backfill ni sobrescritura; dispositivo histórico libre presentado literalmente.

## Contract drift

| Contrato | Resultado |
| --- | --- |
| device | **FIXED**: validado y persistido; NINGUNO distinto de NULL |
| generic registrar path | **CANONICAL**: `registrar` y `RegistrosEnfermeria` delegan a `registrarMovilidad` |
| Escritura genérica residual de Dolor | Retirada exclusivamente de la rama MOVILIDAD; otros flujos intactos |

## Migration

| Gate | Resultado |
| --- | --- |
| SQLite migrate / down / up | PASS |
| PostgreSQL migrate / down / up | PASS |
| PostgreSQL fresh seed | PASS, base desechable dedicada |
| Rollback preserva campos anteriores, fila, FK, índices e inventario | PASS |

Migración `2026_10_10_000100_extend_registros_movilidad_v2.php`: exactamente nueve columnas nullable. Aplicada por su ruta explícita en la base aislada de QA y, tras confirmar nombre/motor/entorno local, en `remembermind_dev`. Fresh/seed y rollback únicamente en bases desechables `remembermind_movilidad_fresh_20261010` y `remembermind_experto_test_20261010_movilidad`. La base local del propietario no fue reiniciada.

## Service

| Gate | Resultado |
| --- | --- |
| validation | PASS: whitelist, catálogos, marcha obligatoria, observación trim/5000 |
| server-owned context | PASS: residente contextual, profesional autenticado, jornada activa, now, VIGENTE; rechazo de claves falsificadas |
| tri-state | PASS: true / false / NULL conservados |
| distance | PASS: decimal(7,2), 0–99999.99, máximo dos decimales, solo actividades de caminar |
| device | PASS: persistencia y NINGUNO / NULL diferenciados |

Cuenta y personal activos, competencia vigente, permiso, Policy, asignación y jornada se mantienen. Se verifica denegación a residentes fuera de alcance y roles sin competencia; la excepción temporal CURRENT de Superadministración no se modifica. No se añade interpretación, puntuación, tendencia funcional, recomendación ni alerta automática. El enlace a incidentes utiliza la ruta existente y no crea un incidente al guardar movilidad.

## UI

Reason, activity, capacity, tolerance, symptoms, history y success: **PASS**.

Familia visual clínica compartida; campos A–E, selecciones con icono, borde reforzado y tipografía marcada, colores semánticos, contexto compacto y fecha/hora no editables. Cambio de actividad incompatible limpia la distancia. Limpiar conserva residente e historia; salir con cambios pide confirmación sin justificativo. Durante guardado se bloquean acciones incompatibles. Resultado real con fecha, profesional y valores persistidos; registro posterior independiente.

Ajuste visual solicitado posteriormente por la propietaria el 10/10: retirar check en las opciones y diferenciar selección con menta, azul clínico, lavanda y arena mediante tokens existentes. Las opciones no informadas permanecen neutrales; amarillo y rojo conservan los estados ya definidos, incluso en hover. No cambia catálogos, validación ni persistencia. Verificado en navegador en 1440/1280/1024/768/390 px, sin overflow y con navegación por flechas; 2 pruebas existentes / 29 assertions y build PASS tras el ajuste. La suite full indicada abajo corresponde al cierre previo a este cambio exclusivamente visual. Evidencia: `movilidad-colores-sin-check.png`, `movilidad-colores-390.jpg`, `movilidad-color-tests.txt`, `movilidad-color-build.txt` bajo `storage/app/qa/`.

Ajuste posterior de relieve, corregido según petición de la propietaria: fondo de color uniforme en toda la superficie seleccionada, sin degradado ni iluminación radial; volumen mediante borde y sombra suave alrededor. Se retira la base inferior de 4 px. Al presionar, sombra interior y desplazamiento de 2 px, conservando el fondo sólido. Las opciones no informadas siguen planas. Movimiento reducido conserva el relieve estático y desactiva transformaciones/transiciones. Evidencia actual: captura `movilidad-color-entero.png` y log `movilidad-color-entero-build.txt` en la misma carpeta de QA.

Historial plano de eventos reales, vacío correcto, filtros Todos/Deambulación/Transferencias/Otros, grupos por fecha, detalle desplegable y carga progresiva de 40. Popup común movible/redimensionable, acotado al viewport. Sin gráfica numérica ni captura ficticia. Conteos de jornada factual: registros, deambulación y fatiga documentada; este último incluye SIN_FATIGA y excluye NULL, como establece la decisión CURRENT.

Fallo backend real inducido mediante bloqueo transaccional de PostgreSQL con timeout, únicamente en la base sintética: formulario abierto, datos conservados, aviso seguro visible y enfocado, ningún éxito falso. Tras liberar el bloqueo, reintento persistido y resultado real. Prueba SQLite adicional con trigger controlado confirma ausencia de fila ante fallo. Sin mecanismo de fallo permanente en producción.

## Responsive

| CSS viewport exacto | Resultado |
| --- | --- |
| 1440 × 900 | PASS, dos columnas, lateral 371 px |
| 1280 × 900 | PASS, dos columnas |
| 1024 × 900 | PASS, dos columnas |
| 768 × 600 | PASS, una columna y scroll interno |
| 390 × 600 | PASS, una columna, footer completo y popup usable |

Anchuras CSS comprobadas mediante `innerWidth`, sin sustituir 1280 por zoom. Sin overflow horizontal; contexto/footer permanecen utilizables y el contenido largo tiene scroll. Capturas completas preservadas pese a la escala interna del navegador de Codex, usando el tamaño físico de captura en desktop y captura nativa en tablet/móvil.

## Accessibility

Keyboard, focus, fieldset/legend, aria y reduced-motion: **PASS**.

Flechas cambian selección y conservan foco; radiogroup con roving tabindex, checked, required, invalid y ayudas asociadas. Shift+Tab desde primer control y Tab desde último envuelven dentro del modal. Escape cierra historial y devuelve foco a su disparador. Error de validación y de persistencia enfocados. Targets principales de 44 px. `prefers-reduced-motion` verificado en runtime real: true, sin transformación de hover ni transición decorativa; captura y popup usables. Contrastes de texto de selección sobre sus superficies observadas: cuidado 4.80:1, advertencia 5.50:1, peligro 5.58:1; comprobación localizada, no certificación global.

## Regression

Signos, Dolor, Ingesta, Hidratación y Eliminación: **PASS** en suite PHP completa y pruebas JS existentes. Sin cambios específicos en sus formularios, schema o reglas durante este lote.

## Tests

| Gate | Resultado |
| --- | --- |
| PHP full | PASS: 984 passed, 15 skipped, 19745 assertions; 1323.05 s |
| SQLite Movilidad + migración | PASS: 14 passed, 188 assertions |
| Arquitectura / Movilidad / rollback experto corregidos | PASS: 29 passed, 514 assertions |
| PostgreSQL Movilidad + migración | PASS: 13 passed, 175 assertions; 1 skip exclusivo de trigger SQLite, sustituido por fallo real de runtime PostgreSQL |
| PostgreSQL rollback experto en su base autorizada | PASS: 1 passed, 57 assertions |
| Error de persistencia tras ajuste de foco | PASS: 1 passed, 13 assertions |
| JS | PASS: 146/146, incluidos siete de Movilidad |
| Build | PASS: Vite 8.3.1 |
| PHP lint y diff check del alcance | PASS |

Dos fallos introducidos detectados y corregidos antes del full final: **BUG_PRODUCT** al importar Model desde un componente UI (ahora opciones DTO desde Livewire); **CONTRACT_DRIFT** en rollback experto que esperaba dos migraciones posteriores y ahora son tres (se comprueba explícitamente desaparición y reinstalación de distancia). La primera ejecución PostgreSQL del rollback experto rechazó una base cuyo nombre no cumple su guard de seguridad; se respetó el guard y se verificó en una base dedicada admitida, sin cambiar la restricción.

Evidencia local ignorada por Git, exclusivamente sintética, bajo `storage/app/qa/`: `movilidad-php-full-final.txt`, `movilidad-pg-acceptance.txt`, `movilidad-expert-rollback-pg.txt`, `movilidad-js-final.txt`, `movilidad-build-final.txt`, capturas `movilidad-1440-final.png`, `movilidad-1280-final.png`, `movilidad-1024-final.png`, `movilidad-768-final.jpg`, `movilidad-390-complete.jpg`, `movilidad-390-popup.jpg`, `movilidad-persistence-error-final.png` y `movilidad-success-final.png`.

## Verificación posterior: validación integral y «Otro»

Petición de la propietaria, 10/10/2026: comprobar el formulario y desplegar descripción al seleccionar Otro. Motivo y dispositivo incorporan un input condicional obligatorio de hasta 500 caracteres. El backend exige texto no vacío tras trim, rechaza detalles incompatibles con la opción elegida y limita a 5000 caracteres la narrativa combinada. Conserva las descripciones identificadas en `observacion`, junto al texto libre, sin columnas nuevas ni alteración de historia anterior. El consumidor genérico de movilidad aplica el mismo escritor y admite la descripción de otro dispositivo.

Verificación de este lote: **17 pruebas PHP de Movilidad / 201 assertions PASS**, **147 pruebas JS PASS**, **build PASS**, PHP lint y diff check PASS. Incluye marcha obligatoria, catálogos, distancia/rango/decimales, triestados, permisos/contexto/autoría, datos conservados ante validación o fallo real de persistencia, precisión de Otro, rechazo de exceso sin truncar, limpieza y consumidor genérico. La primera ampliación del test de limpieza omitía solicitar confirmación: se corrigió la secuencia de prueba para cumplir el guard existente; no se debilitó el guard.

Runtime sintético: campos desplegados, errores individuales sin guardar, datos conservados tras corregir Otro mientras falta marcha, detalle eliminado al cambiar de opción y limpieza confirmada que conserva residente e historial. Revisión visual desktop 1280 CSS y móvil 389 CSS, sin overflow; input móvil de aproximadamente 44 px. Sin guardar nuevos registros de QA desde navegador. Evidencia en `storage/app/qa/`: `movilidad-validacion-php.txt`, `movilidad-validacion-js.txt`, `movilidad-validacion-build.txt`, `movilidad-otro-desplegable.png`, `movilidad-otro-mobile.png`. La suite PHP full y la integración PostgreSQL de las secciones anteriores corresponden al cierre previo, no se repitieron en este lote localizado.

## Commit

Cierre inicial sin commit. Por solicitud posterior de la propietaria, incorporado al lote `FORMULARIOS BONITOS`; las otras áreas se guardan en commits separados por tema. Heridas/Curaciones no iniciadas.
