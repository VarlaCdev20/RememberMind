# Fase 01 — cotejo documental completo y matriz VT

Fecha: 18/10/2026. Responsable de implementación/verificación: Codex, ingeniería de software. No se registran firmas ni dictámenes profesionales.

## Acceso y versiones consultadas

La propietaria inició sesión en Google y confirmó el acceso. EXP-01 y G09-EXP se exportaron desde esa sesión y se leyeron completos. G06/G07/G08 se recuperaron como DOCX originales y se extrajo su texto, sin ejecutar contenido. No fue necesario modificar permisos, hacer públicos documentos ni cambiar autorizaciones de RememberMind. Los errores 401 de cortes anteriores quedan resueltos para este cotejo.

| Fuente | SHA-256 del artefacto consultado |
|---|---|
| [EXP-01, exportación Markdown](https://docs.google.com/document/d/1jLV4WU1PMvNHk7hnERVCmVCT6QaXtBe2eIyQMKBACPc/edit) | `B7164BEEFA81552B6F5BD6F38D56EC54830DF099A09E2F3AA42A79DD43059172` |
| [G09-EXP, exportación Markdown](https://docs.google.com/document/d/1b4Vf4MrkodLJIUQF_PtQ_7frOe8F4-nnasedktlaMfw/edit) | `6567A253EA7E24C85AE9F88A9FE92F0E493FBA71927E9A5AB167C9FFAA2F0E53` |
| [G06, DOCX](https://drive.google.com/file/d/18H9LFH-ldy2mVW7EfYdDt4lkkRPUDMVa/view) | `2C5B81181E2E42BAB7BFA69EE6CE9D7E5A5F85C69564EB48EE14662A7E194081` |
| [G07, DOCX](https://drive.google.com/file/d/1m5amm5tkfvD17HLMVkTEPKVg3OJkMI9B/view) | `30167FA1FD5B9FA034B7C1579B777490218380019B79A5F4405C6AE309D5FED7` |
| [G08, DOCX](https://drive.google.com/file/d/13IR23ftx8SopS0G1QcUFH6m5RfcX1nXo/view) | `B977BADC0304A9D4F60AE693086442AE3C18C5444AE445E67DF47A1A054842AB` |
| [Anexo A, XLSX de 19 hojas](https://docs.google.com/spreadsheets/d/1lPFoZD6LlIoOkuWsLN3VTjvsgBowBl0mBQekjyOiyVA/edit) | `3630FF46C7E2CCE3C997A929209C50AA59F2CC31D95B883B1244FC622FF80370` |
| [Documento 16, extracción de acta y compuertas](https://docs.google.com/document/d/1uoa0YoAGNUCLpAeLXMtRTqGJXAuOQIupjcwqk4Xnjrs/edit) | `6EB6BF3A7F4D19DD8E12F24644C2810B03A018F67D788B21D1BA4B9E77B9429E` |
| G12-DCL, extracción del protocolo 05 | `BD832DA90A364F7B5CC801AE364DBC0002D3193DEF0D418AA9E57B11DA9332D7` |

Los originales temporales están en `TEMP/browser-use/exports/` y `TEMP/sistema-experto-fase01-fuentes/`. El inventario previo de Maestro, D-123–D-144, contratos y adendas DCL permanece en [auditoría](FASE01_AUDITORIA_Y_EVIDENCIA_2026_10_18.md). Se cotejaron las secciones pertinentes de las matrices, sin afirmar que cada referencia bibliográfica de las 19 hojas fue verificada externamente de nuevo.

## Autoridad y resolución de aparentes conflictos

| Expediente | Contrato aplicado y evidencia |
|---|---|
| EXP-01 | §13 amplía el fixture positivo de §6 al prototipo segregado. Permite ingeniería integral; mantiene la procedencia sintética, independencia asistencial y restricciones de derechos. |
| G06 | Treinta descriptores candidatos para quince subcomponentes. Se conserva bruto/contexto; el laboratorio usa un vocabulario artificial explícito. No convierte esos descriptores en catálogos clínicos aprobados ni interpreta narrativa. |
| G07 | EV-0/1/2 y MOD separados; validez de tarea antes de regla. Memoria conserva D-122/D-131 propio. Invalidar una tarea exige recalcular las restantes, sin penalización ni bloqueo global inventados. |
| G08 | Quince componentes candidatos y cero perfiles clínicos aprobados. Auditoría y localizadores conservan brechas de edición, edad, población, norma y permiso. Los reactivos de pruebas de software son propios y artificiales. |
| G09 | Identificadores y consecuencias exactos; C1/C2/C3 positivos AND, A01–A14 y VT01–15. §13 y sus adendas posteriores permiten cinco dominios y Petersen con referencias sintéticas independientes; no son autorización asistencial. |
| G10 | Documento 16 y hojas 11_Confusores/13_Solapamientos: 33 confusores de ATE/EJE/LEN/VIS, tarea/episodio, advertencia frente a invalidación y corroboración sin soporte multiplicado. No se deducen episodios por ventanas temporales inventadas. |
| G11 | Documento 16 y hojas 1_Fuentes/16_Matriz_Maestra: registro → captura → hecho → condición → regla → consecuencia → explicación, con copias y versiones conservadas. Referencia científica y resultado operacional no equivalen a aprobación clínica. |
| G12 | Protocolo de referencia independiente y casos de prueba. Los 30 casos y 32 configuraciones documentales permanecen intactos; las pruebas técnicas no se presentan como estudio clínico ciego ni métricas de personas reales. |
| G13 | Documento 16 es plantilla sin firmas. Se entrega evidencia técnica para revisión; no se rellena el acta ni se declara cierre profesional por iniciativa del implementador. |

El inventario histórico 71/23 y el carácter entonces propuesto de IADL se resuelven con las extensiones posteriores ya aprobadas en el repositorio: 73 operativas y 33 expertas. No se aplican migraciones en esta fase. El Prompt§14 permite conservar los detalles no representables mediante el archivo experimental; la brecha de conservación institucional se mantiene en la RFC.

## Cambios derivados del cotejo

1. **VT11:** `EjecutorConocimientoG09Experimental` recontaba dos evidencias de una misma tarea/episodio como soportes independientes. Se mantiene cada condición y cada original; `grupos_soporte` agrupa por episodio/tarea y `soportes_efectivos` elige un representante estable. Las consecuencias se consolidan por esa misma unidad incluso entre reglas declarativas. No se infiere independencia clínica entre tareas o dominios.
2. **VT01–15:** nueva batería parametrizada para las quince reglas. Comprueba ausencia, NULL, FALSE sin mapeo, texto desconocido, respuesta obligatoria ausente, edición original incompatible, todos los A01–A14 negativos/desconocidos, cada condición false/NULL/ausente, identidad/corte/versión ajenos, confusor local, duplicidad, dos procesos, contradicción, replay y cadena hasta respuesta original.
3. **Gramática §5:** explicación calculada con instrumento, tarea, edición esperada y original, perfil, norma artificial, idioma, población, fuente, fecha, respuestas y condiciones exactas. Conserva exclusiones, advertencias y declaraciones de confusores. Las rutas declarativas enlazan IDs de D-130 con la evidencia fuente y su captura.
4. **Cobertura G08:** incorporación selectiva de `g08_auditoria_instrumental.php` y `g08_anexos_bolivia.php` del mismo worktree de investigación ya auditado. El workflow ejecuta ahora doce programas independientes; no se fusionó la rama completa.

## Registro ejecutado de VT01–VT15

Suite: `tests/Unit/ContratoG09ExperimentalTest.php`. **315 pruebas / 4.101 aserciones PASS**, en PHP 8.3.33/PHPUnit 12.5.36, SQLite aislada. La ejecución dirigida que incluye además controles, resultado y reglas declarativas terminó **357 / 4.293 PASS**. No sumar suites solapadas.

La [matriz CSV de 225 cruces regla/VT](FASE01_MATRIZ_VT_G09_2026_10_18.csv) se genera desde los casos JUnit ejecutados, con esperado, observado, fixture/método, estado, versión, responsable y commit base. Incluye huellas del código y señala expresamente los cambios sin commit; no atribuye esta implementación al commit base. Varios cruces usan una misma prueba y algunas pruebas ejecutan múltiples mutaciones; 225 cruces no significa 225 ejecuciones adicionales.

| VT | Verificación por cada regla | Método del contrato (prefijo `test_`) |
|---|---|---|
| 01 | Ausente/NULL/texto sin mapeo no crean normalidad; FALSE textual se rechaza por tipo | `vt01_vt03_…`, `vt01_false_…` |
| 02 | Señal sola o tarea sin contexto queda en EV-1 | `vt02_…` |
| 03 | COMPLETA sin respuesta obligatoria no admite la tarea | `vt01_vt03_…` |
| 04 | Edición incompatible y cualquier A01–A14 no acreditado/desconocido impiden la tarea | `vt04_edicion_…`, `vt04_cada_control_…` |
| 05 | Perfil explícito TEST_ONLY coherente produce CUMPLE | `vt05_vt14_vt15_…` |
| 06–08 | C1/C2/C3 individualmente false/NULL/ausente impiden consecuencia | `vt06_vt08_…` |
| 09 | Otro caso/versión/futuro se excluye; lector rechaza aplicación ajena | `vt09_…` |
| 10 | Invalida una tarea; vecinas admisibles conservan su resultado | `vt10_…` |
| 11 | Dos fuentes de misma tarea/episodio: un soporte efectivo; otro episodio: dos | `vt11_…` |
| 12 | Dos procesos conservados sin prioridad arbitraria | `vt12_…` |
| 13 | Contradicción conserva condiciones cumplidas y suspende consecuencias | `vt13_…` |
| 14 | Mismos hechos/conocimiento producen salida y traza idénticas | `vt05_vt14_vt15_…` |
| 15 | Regla/condición/ID original/captura/respuesta y narrativa enlazados | `vt05_vt14_vt15_…` |

Comando reproducible:

```text
php vendor/bin/phpunit tests/Unit/ContratoG09ExperimentalTest.php --log-junit=<destino de evidencia>
```

Los JUnit/logs TEMP `sistema-experto-fase01-vt-g09.sqlite.*` acreditan la ejecución. Las pruebas Feature de archivo/gestor verifican adicionalmente conservación durable, fuente modificada y recuperación histórica. Sus resultados y la regresión global se registran separadamente en la auditoría.
