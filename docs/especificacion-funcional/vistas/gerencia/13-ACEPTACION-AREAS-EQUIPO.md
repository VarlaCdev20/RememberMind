---
title: "Gerencia: aceptación de Áreas y equipo"
status: DRAFT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: ACCEPTANCE_SPECIFICATION_WITH_STATIC_TEST_LOCALIZATION
runtime_verified: false
---

# Aceptación de Áreas y equipo

## 1. Alcance y evidencia existente

Contrato: [Áreas y equipo](12-AREAS-Y-EQUIPO.md), campos AR01–AR10 y acciones AA01–AA11. **Los escenarios AE01–AE20 se especificaron sin ejecución en el corte documental original.** DA01 fue adoptada posteriormente por la propietaria el 26/10/2026; la evidencia nueva se registra en el informe 38. DA02 sigue delimitando la relación permanente no aprobada; las pruebas no adoptan decisiones institucionales.

Pruebas localizadas, no reejecutadas:

- **AT1:** [AreasInstitucionalesV2Test](../../../../tests/Feature/AreasInstitucionalesV2Test.php): datos de área, responsable derivado, exportaciones y errores. Los escenarios actuales con asignaciones activas no demuestran temporalidad completa o nueva exportación agregada de Gerencia.
- **AT2:** [AsignacionesIntegrityTest](../../../../tests/Feature/AsignacionesIntegrityTest.php) y [PersonalInstitucionalAsignacionesTest](../../../../tests/Feature/PersonalInstitucionalAsignacionesTest.php): relaciones y asignaciones; contrastar fixtures/alcance.
- **AT3:** [PersonalInstitucionalHorariosTest](../../../../tests/Feature/PersonalInstitucionalHorariosTest.php) y [AsignacionesJornadaPlanificadaTest](../../../../tests/Feature/AsignacionesJornadaPlanificadaTest.php): continuidad temporal y permisos del proceso propietario, sin aprobación de nuevos límites de guardia.
- **AT4:** [RolesVistasPermisosMatrixTest](../../../../tests/Feature/RolesVistasPermisosMatrixTest.php): entradas por rol; no acredita todos los payloads tras revocación.

## 2. Requisito → caso → evidencia futura

| Caso | Campo/acción | Resultado requerido | Prueba base / trabajo posterior |
|---|---|---|---|
| AE01 | AA01, 20A | Gerencia tiene una sola entrada «Áreas y equipo» al módulo existente, sin perder acceso o duplicar área | AT4 y nueva navegación |
| AE02 | AR01/AR03, AA01 | Búsqueda y filtros muestran fuentes autorizadas; cero coincidencias distinto de fuente fallida o restringida | AT1; ampliar estados |
| AE03 | AR01–AR03, AA02/AA04 | Alta válida guarda solo datos propios del área y autoría auditada; no crea personal/asignaciones por defecto | AT1; ampliar Gerencia |
| AE04 | AR01, AA04 | Normalización, duplicados, espacios y carrera de nombre no crean áreas equivalentes inválidas; mensaje seguro | AT1; PostgreSQL desechable y validación normalizada |
| AE05 | AA03/AA04 | Editar nombre/descripcion conserva PK, relaciones y datos de atenciones/planes; no reatribuye historia | Crear comparación antes/después |
| AE06 | AA04, permisos | Cuenta inactiva, permiso revocado, target manipulado o modo Ver como rol no guarda ni audita éxito | AT4; ampliar HTTP/Livewire |
| AE07 | AA04, atomicidad | Fallo de auditoría/guardado no deja operación reportada como completa; reintento no duplica alta | Crear inyección de fallo |
| AE08 | AA05 | Detalle reautoriza cada fuente, incluso después de abrir panel; no carga directorio de usuarios sin alcance | AT1/AT4; crear revocación/payload |
| AE09 | AR05–AR09, AA06 | Equipo muestra asignación, persona y contexto reales; misma persona repetida se cuenta una vez por universo definido | AT1/AT2; ampliar contextos |
| AE10 | AR07/AR10 | Abrir personal exige su permiso; cuentas suspendidas, personal inactivo y responsable ambiguo no se muestran como disponibilidad actual normal | AT1; crear negativos/múltiples responsables |
| AE11 | AA07 | Acceso a planificación desde área conserva filtro/contexto cuando procede, sin conceder escritura por solo lectura | AT3/AT4; integrar retorno |
| AE12 | AR04–AR06, AA08 | Asignaciones de días previos/futuros, jornadas cerradas y turnos nocturnos se interpretan con contrato temporal, no solo estado de fila | AT3; ampliar fuente de resumen |
| AE13 | Métricas | Global único no duplica personas multiarea; registros por fecha_asignacion no se presentan como plantilla o asistencia mensual | Crear prueba de conteos y semántica |
| AE14 | AR03/AA09 | Desactivación bloqueada con asignaciones o planes activos según DA01 adoptada; sin borrado ni transiciones implícitas | `GerenciaAreasEquipoTest::test_da01_bloquea_desactivar_con_asignaciones_y_permite_despues_de_cerrarlas`; evidencia en informe 38 |
| AE15 | AR03 | ACTIVO/ACTIVA e INACTIVO/INACTIVA de entradas actuales no rompen asignación, filtros o historia; sin migrar catálogo sin aprobación | AT1/AT2; regresión de consumidores |
| AE16 | AA10, 26B/C | PDF y CSV de Gerencia contienen agregados del mismo universo/período; sin filas nominales, teléfonos/correos o expedientes implícitos | AT1 como base; nuevas salidas acotadas |
| AE17 | AA10 | Sin permiso, error de exportador o área inexistente: no descarga ni auditoría de éxito falsa; mensaje sin SQL o rutas internas | AT1; repetir entrada real de Gerencia |
| AE18 | AR10/consulta | Falta de responsable o asignaciones se explica con período; no seleccionar primer registro histórico o usuario global para rellenar | AT1; nuevo caso ausencia/ambigüedad |
| AE19 | AA11/formulario | Cancelar, volver, error y confirmar conservan contexto; no guardado parcial o pérdida silenciosa de captura | Crear integración/frontend |
| AE20 | UI completa | Teclado, foco, labels, modales, gráficos con tabla alternativa si existen, loading y reflow en 1440/1280/1024/768/390 | QA visual y build pendientes al implementar |

## 3. Ejecución y cierre

Los escenarios nuevos requieren pruebas de los consumidores reales; no basta con reutilizar una suite verde que conserva métricas equivocadas. Usar SQLite desechable para lógica y PostgreSQL aislado para carreras/transacciones. No ejecutar migraciones o tests destructivos en base institucional.

Comandos candidatos para implementación posterior, **no ejecutados aquí**:

```bash
php artisan test --filter=AreasInstitucionalesV2Test
php artisan test --filter=AsignacionesIntegrityTest
php artisan test --filter=PersonalInstitucionalAsignacionesTest
php artisan test --filter=AsignacionesJornadaPlanificadaTest
```

Antes de ejecutar, leer tests/AGENTS.md y comprobar conexión. Registrar comandos, entorno, resultados/aserciones y límites. Build y navegador al implementar UI; un control documental de enlaces no acredita esas pruebas.

**Corte de especificación original:** no acreditaba ejecución de los veinte casos. **Actualización posterior:** DA01 adoptada y probada en el flujo descrito en el informe 38. Siguen separados la aceptación completa de la matriz, el soporte permanente DA02 si se solicita y el alcance concreto del QA ejecutado.
