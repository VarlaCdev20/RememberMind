---
title: "Renovación de Gerencia: código, integración y verificación"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: TARGETED_RUNTIME_SQLITE_POSTGRES_BROWSER_AND_BUILD
runtime_verified: true
---

# Implementación de Gerencia — 26/10/2026

**Instalación posterior — 27/10/2026:** el [informe40](40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md) acredita autorización, instalación local de35/37 y conciliación P19. Los estados pendientes que siguen conservan este corte histórico.

**Corte posterior:** el [informe 38](38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md) añade Familiares/contactos, decisión de credenciales, DA01 y correcciones de historia/concurrencia. Este informe 36 conserva sus resultados y pendientes históricos; no sustituye la evidencia posterior ni acredita instalación de los diseños 35/37.

Este corte sustituye únicamente las afirmaciones de implementación pendiente que resuelve expresamente. Las fichas 01–33 conservan sus decisiones y sus resultados documentales históricos. **No está cerrado todo el rol ni se han ejecutado los 212 escenarios especificados.**

Rama conservada: `feature/apoyo-decision-informe-neurocognitivo`; HEAD `6d7b8c6d6bc03eecd2ee35add04677f2778272cd`. Trabajo integrado en el árbol local compartido, sin staging, commit, push, cambio de rama o despliegue. Se preservaron cambios simultáneos de experto, acceso, correo y documentación. No se atribuyen a esta entrega.

## 1. Aprobación y estructura

La propietaria respondió **«aprobar diseño»** a la [propuesta 35](35-PROPUESTA-ESTRUCTURAL-PERSONAL.md). Se implementaron sus ocho columnas nullable y cuatro relaciones en bases desechables. No se modificó `remembermind_dev` ni se instalaron migraciones institucionales.

| Inventario | Negocio | Técnicas | Estado |
|---|---:|---:|---|
| Baseline instalado documentado | 100 | 16 | Sin modificar; no se recontó mediante acceso a la base institucional |
| Transición aprobada de Personal | +4 | 0 | Ensayada con SQL real; sin retiro de relaciones |
| Destino después de instalación | 104 | 16 | No equivale al estado instalado actual |

DDL ejecutable: [database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php](../../../../database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php). Está **fuera de `database/migrations`**: un migrate ordinario no lo ejecuta inadvertidamente. La [detección de capacidad](../../../../app/Modulos/Identidad/Aplicacion/Consultas/CapacidadDocumentalPersonal.php) impide fingir escrituras cuando falta la extensión y la pantalla explica su estado.

Las cuatro entidades representan petición, destinatario histórico, entrega y movimiento laboral. Las membresías son filas propias; no se almacenan como JSON/EAV. PK/FK restrictivas, UUID de idempotencia, unicidades, grupos de campos nullable y prórroga exacta de siete días se validaron en ambos motores. SQLite usa triggers donde su ALTER no admite los CHECK de PostgreSQL. No se rellenan autores o fechas históricas desconocidas.

Reversión: solo se retira automáticamente una extensión sin hechos nuevos. Si alguna fila o columna nueva contiene información, `down()` se detiene. Retirar una extensión poblada requerirá transición de conservación explícita; no se elimina su historia para completar un rollback.

## 2. Recorridos implementados

| Recorrido | Implementación e integración | Límite |
|---|---|---|
| Inicio | Tres ámbitos equilibrados; agregados reales de personal, camas y alertas, enlaces directos y pendientes compactos | No mide asistencia ni valida suficiencia de plantilla |
| Personal | Tabla/tarjetas, búsqueda, acceso y trabajo separados; alta de cuatro pasos, ficha, documentos, horarios y alcance de siete roles laborales | Familiar pertenece a otro recorrido; no se concede administración global de cuentas |
| Acceso | Suspensión/reactivación con estado esperado, recuperación por broker existente, cambio inicial obligatorio cuando existe la extensión | Enviar al servicio de correo no prueba recepción humana; pruebas sin envíos externos |
| Documentación | Solicitud expresa por rol, personas seleccionadas, plazo, entrega privada, revisión, corrección, anulación y única prórroga | No hay autosuspensión por vencimiento ni obligación de todos los opcionales |
| Trabajo | Cambio de estado con fecha/motivo/autor, transacción y secuencia; aviso interno a Administración si quedan asignaciones activas | No se borran asignaciones; aviso no equivale a acuse de revisión |
| Áreas y equipo | Entrada única, listado y ficha con asignaciones para la fecha elegida | No se inventa jefatura permanente; decisión DA01 no resuelta |
| Planificación | Semana inicial, mes/día, filtros compactos, turnos nocturnos con fecha final real y formulario de asignación existente | Planilla secundaria de Enfermería conserva flujo/diseño anteriores y deuda de historia |
| Cobertura | Consulta propia por fecha/área/turno con personas asignadas | No equivale a presencia, carga suficiente o déficit |
| Residentes | Tarjetas/tabla y ficha administrativa sin PK visibles; fuentes condicionadas al permiso | No habilita lectura clínica por ser Gerencia |
| Ocupación | Corrección H04 conservada: mapa y detalle en la ruta autorizada | Historia/estados completos tienen brechas propias |
| Alertas | Bandeja de supervisión compacta, filtros, tabla y drawer existentes | Sin atención clínica por implicación; cada origen mantiene autorización |
| Reportes | Agregados, preparación temporal y PDF/CSV sobre las mismas filas; revisión, caducidad, actor y reautorización | Preparación en sesión no es cierre histórico SQL; no exporta clínica nominal |
| Galería | Entrada directa, limpieza de filtros, detalle de descripción y confirmación de archivo/reactivación | Reutiliza almacenamiento privado existente; no afirma fotografías nuevas del centro |

La presentación reutiliza componentes Blade, tokens, modales y tablas del Design System. No imprime códigos o PK como contenido de negocio. La autorización se aplica también en Actions y consultas, no solo al ocultar controles.

## 3. Trazabilidad del cambio

| Requisito | Archivo responsable | Prueba principal |
|---|---|---|
| Tres ámbitos sin datos clínicos nominales | [ConsultarIndicadoresGerencia](../../../../app/Modulos/Reportes/Aplicacion/Consultas/ConsultarIndicadoresGerencia.php) | `GerenciaDashboardTest`, `ReportesGerenciaRenovacionTest` |
| PDF/CSV idénticos y caducidad | [PrepararReporteGerencia](../../../../app/Modulos/Reportes/Aplicacion/Acciones/PrepararReporteGerencia.php) y controlador existente | `ReportesGerenciaRenovacionTest` |
| Alcance de Gerencia y alta atómica | [AutorizarGestionPersonal](../../../../app/Modulos/Identidad/Aplicacion/Acciones/AutorizarGestionPersonal.php), `GuardarPersonalInstitucional` y formulario existente | `GerenciaPersonalRenovacionTest`, `ContratoPersonalInstitucionalTest` |
| Suspender acceso no cambia trabajo | `CambiarEstadoPersonal` | `GerenciaPersonalRenovacionTest` |
| Recuperación segura y fallos de transporte | [EnviarRecuperacionAccesoPersonal](../../../../app/Modulos/Identidad/Aplicacion/Acciones/EnviarRecuperacionAccesoPersonal.php) | `GerenciaPersonalRenovacionTest`, `RecuperacionContrasenaFlujoTest` |
| Cambio inicial no eludible | [ExigirCambioInicialPassword](../../../../app/Modulos/Identidad/Presentacion/Http/Middleware/ExigirCambioInicialPassword.php), controlador y Fortify | `PasswordInicialPersonalTest` y navegador |
| Solicitud/membresía/prórroga | [GestionarSolicitudesDocumentales](../../../../app/Modulos/Identidad/Aplicacion/Acciones/GestionarSolicitudesDocumentales.php) y cuatro modelos nuevos | `GerenciaSolicitudesDocumentalesTest`, concurrencia PostgreSQL |
| Entrega privada, idempotencia y compensación | [RegistrarArchivoSolicitudDocumental](../../../../app/Modulos/Identidad/Aplicacion/Acciones/RegistrarArchivoSolicitudDocumental.php) | `GerenciaSolicitudesDocumentalesTest` y navegador |
| Lectura privada sin IDOR | [DocumentoLaboralPolicy](../../../../app/Modulos/Identidad/Presentacion/Policies/DocumentoLaboralPolicy.php), `ArchivoDocumentalPersonalController` | `GerenciaSolicitudesDocumentalesTest` |
| Transición laboral con historia | [RegistrarMovimientoLaboral](../../../../app/Modulos/Identidad/Aplicacion/Acciones/RegistrarMovimientoLaboral.php) | `GerenciaSolicitudesDocumentalesTest`, concurrencia PostgreSQL |
| Avisos propios sin permiso clínico | `ConsultarDocumentacionPersonal`, `ConsultarCambiosLaboralesPendientes`, campana existente | `GerenciaSolicitudesDocumentalesTest`, pruebas de campana |
| Horarios con permiso y solapamiento real | `GestionarHorariosPersonal`, `PersonalInstitucionalHorarios` | `GerenciaPlanificacionSeguridadTest`, `PersonalInstitucionalHorariosTest` |
| Calendario y cobertura reales | `TurnosAsignacionesPanel`, [ConsultarCoberturaEquipo](../../../../app/Modulos/Institucion/Aplicacion/Consultas/ConsultarCoberturaEquipo.php) | `GerenciaPlanificacionSeguridadTest` y navegador |
| Lectura administrativa del residente | Controladores de Residentes y vistas `paginas/gerencia` | `GerenciaConsultaResidentesTest` |
| Equipo por asignación/contexto | `AreasInstitucionalesPanel`, `GestionarAreasInstitucionales` | `GerenciaAreasEquipoTest`, `AreasInstitucionalesV2Test` |
| SQL/reversión/NULL desconocido | Transición propuesta | `GerenciaDocumentacionEstructuraTest` |

Los nombres de prueba de la matriz se localizan en `tests/Feature`. Las vistas renovadas se ubican bajo `Presentacion/Vistas` de Identidad, Institucion, Alertas, Residentes y Reportes. El estilo compartido está en [management-workspace.css](../../../../resources/frontend/styles/design-system/patterns/management-workspace.css). No se crea un módulo Gerencia paralelo.

## 4. Verificación ejecutada

Solo se emplearon datos sintéticos. SQLite `:memory:`, SQLite de archivo temporal del navegador y clúster PostgreSQL temporal con usuario dedicado. No se utilizaron datos del expediente institucional ni servicios externos de visualización o correo.

Comandos y registros:

```powershell
php artisan test --filter='Gerencia|ReportesGerencia|ContratoPersonalInstitucionalTest|ContratoDocumentacionUsuarioTest|ContratoSeguridadCuentaTest|PersonalInstitucionalHorariosTest|PersonalInstitucionalResponsiveTest|AreasInstitucionalesV2Test|GaleriaInstitucionalTest|AlertasExperienciaVisualTest|RolesVistasPermisosMatrixTest|AuthenticationTest|PasswordInicialPersonalTest|UpdatePasswordTest|PasswordResetTest|RecuperacionContrasenaFlujoTest' --exclude-group=postgresql --log-junit=output/auditoria-integral-20261025/gerencia-renovacion-aceptacion.xml
php artisan test --filter='GerenciaSolicitudesDocumentalesTest|GerenciaPersonalRenovacionTest|Campana' --log-junit=output/auditoria-integral-20261025/gerencia-avisos-acceso.xml
php artisan test --filter='GerenciaSolicitudesDocumentalesTest|GerenciaPersonalRenovacionTest|ContratoDocumentacionUsuarioTest|GerenciaDocumentacionEstructuraTest' --log-junit=output/auditoria-integral-20261025/gerencia-detalle-documental.xml
./scripts/probar-postgres.ps1 -Filtro 'GerenciaDocumentacionEstructuraTest|GerenciaSolicitudesDocumentalesTest|GerenciaDocumentacionConcurrenciaPostgresTest|PasswordInicialPersonalTest' -Informe output/auditoria-integral-20261025/gerencia-documentacion-postgres.xml -Log output/auditoria-integral-20261025/gerencia-documentacion-postgres.txt
$env:RM_GERENCIA_QA_AMPLIACION='1'
node --test tests/Frontend/gerencia-renovacion-e2e.test.js
Remove-Item Env:RM_GERENCIA_QA_AMPLIACION
npm run build
git diff --check
```

Los logs homónimos `.txt` y JUnit `.xml` están en [output/auditoria-integral-20261025](../../../../output/auditoria-integral-20261025). Los recuentos de diferentes grupos se solapan y no se suman como pruebas únicas.

| Verificación | Resultado real de este corte | Evidencia |
|---|---|---|
| Backend relacionado, SQLite | **162 pruebas, 1125 aserciones; PASS** | `gerencia-renovacion-aceptacion.xml/.txt` |
| Avisos, acceso y negativos de campana | **30 pruebas, 208 aserciones; PASS** | `gerencia-avisos-acceso.xml/.txt`; grupo parcialmente incluido en el anterior |
| Detalle documental, permisos y última regresión de Personal | **32 pruebas, 211 aserciones; PASS** | `gerencia-detalle-documental.xml/.txt`; posterior al ajuste de permisos por lote |
| Extensión/concurrencia PostgreSQL | **19 pruebas, 142 aserciones; PASS** | `gerencia-documentacion-postgres.xml/.txt`; clúster temporal detenido y retirado |
| Inventario/reversión SQLite | **4 pruebas, 23 aserciones; PASS** | `gerencia-inventario.txt`; también incluidas en los grupos pertinentes |
| Navegador real | **Un recorrido integrado satisfactorio**: diez vistas × cinco anchos, tres capturas oscuras y operaciones descritas abajo | `gerencia-ampliacion-e2e.txt` y matriz JSON; no son 53 pruebas clínicas |
| Compilación frontend | **PASS**, con advertencia de bundle existente mayor de 500 kB | `gerencia-build-final.txt` |
| Documentación | **4 documentos, 112 enlaces locales, cero destinos faltantes** | `gerencia-enlaces.json` |
| Revisión de whitespace | **git diff --check: 0** | `gerencia-diff-check.txt`; avisos CRLF/LF no son errores de whitespace |

No se ejecutó la suite completa del repositorio. La suite amplia inicial detectó una expectativa visual antigua en `AlertasFlujosPorRolTest`; se corrigió al nuevo título sin retirar las verificaciones de ámbito, filtros y datos. La repetición amplia posterior pasó.

La última revisión del detalle eliminó consultas repetidas de permisos por archivo: `DocumentoLaboralPolicy::permisosDocumentos` prepara la autorización por lote; Blade solo presenta esos permisos. Un caso con doce entregas históricas verifica un máximo de diez consultas de autorización y rechaza una referencia que mezcle contexto de residente. Se repitieron los 32 casos pertinentes, PostgreSQL y el recorrido funcional de navegador en modo rápido, separado de la matriz completa de tamaños. Los negativos de API/Action siguen vigentes al abrir el archivo o revisar, aunque se manipule un identificador.

PostgreSQL verifica tres carreras reales de dos procesos: una petición idempotente conserva una sola membresía; dos prórrogas producen una confirmación y un rechazo; dos cambios laborales conservan una sola transición. No se afirma que esto pruebe concurrencia de todos los módulos.

El navegador arranca Laravel con un router de pruebas no registrado en `public`, token aleatorio y base temporal controlada. Recorre diez vistas en 1440, 1280, 1024, 768 y 390 px; tres capturas oscuras adicionales, tabla/tarjetas, búsqueda vacía, validación, cancelación con cambios, drawer, calendario mes/día, apertura de asignación, preparación CSV, cambio inicial, solicitud, PDF privado y revisión. Verifica desbordamiento de página y errores JavaScript. **No es certificación WCAG completa ni ejecución de todos los botones y 212 casos.**

La matriz final conserva 53 combinaciones de vista/tamaño/tema, sin desbordamiento de página. El diagnóstico final registra cero errores JavaScript, cero respuestas HTTP fallidas y cero errores del servidor de ensayo. El modo rápido guarda sus capturas/matriz en `depuracion-rapida` para no sustituir la matriz completa.

Defectos corregidos al ejercitar el navegador: raíz condicional de Livewire que impedía abrir el alta (`Snapshot missing`), configuración del disco temporal de cargas en el servidor de QA y sincronización del cambio de contraseña con la sesión Jetstream. La segunda incidencia afecta exclusivamente al servidor de pruebas. Un test esperaba el título anterior de Alertas; se actualizó esa expectativa conservando los asserts de filtros y datos.

## 5. Evidencia visual de la aplicación

Son capturas reales de Laravel con datos sintéticos, diferentes de las propuestas generadas anteriores:

- [Inicio 1440](../../../../output/auditoria-integral-20261025/gerencia-renovada/inicio-1440.png).
- [Planificación 1440](../../../../output/auditoria-integral-20261025/gerencia-renovada/planificacion-1440.png) y [asignación 390](../../../../output/auditoria-integral-20261025/gerencia-renovada/planificacion-asignar-390.png).
- [Personal en tarjetas 390](../../../../output/auditoria-integral-20261025/gerencia-renovada/personal-tarjetas-390.png) y [validación del formulario](../../../../output/auditoria-integral-20261025/gerencia-renovada/personal-validacion-390.png).
- [Entrega documental revisada 390](../../../../output/auditoria-integral-20261025/gerencia-renovada/documentacion-entrega-validada-390.png).
- [Matriz de tamaños/estados](../../../../output/auditoria-integral-20261025/gerencia-renovada/matriz.json) y [diagnóstico JS/HTTP](../../../../output/auditoria-integral-20261025/gerencia-renovada/diagnostico.json).

## 6. Pendientes que impiden declarar todo Gerencia terminado

1. **Instalación institucional:** la extensión solo se instaló en bases desechables. Verificar destino, respaldo y ventana antes de una instalación expresamente autorizada; actualmente la interfaz institucional indica que no está disponible.
2. **Familiares/contactos:** falta integrar y aceptar las fichas 09–11 completas, incluido el alcance de cuentas Familiar. Las operaciones nuevas de Personal aquí cubren siete roles laborales; no fingen cubrir el octavo con este directorio.
3. **Archivos anteriores:** se conservaron rutas antiguas; no se migraron indiscriminadamente archivos de disco público a privado. El checklist compartido histórico usa FOTO frente a FOTO_DOC del alta: falta conciliar consumidores sin renombrar retrospectivamente documentos.
4. **Planificación histórica:** el agrupado legado de horarios en observación JSON no se presentó como relación normalizada nueva. La corrección de permisos y solapamiento no completa conservación de todas las modificaciones/finalizaciones ni el rediseño de la planilla secundaria de Enfermería.
5. **Áreas/ocupación:** DA01 y otras decisiones de estados/historia siguen delimitadas en sus contratos. No se aprobó una desactivación automática por esta entrega.
6. **Avisos:** son consultas deduplicadas de fuentes persistidas, no un registro ficticio de mensaje entregado, leído o tarea resuelta. No se creó otra tabla de notificaciones/auditoría ni se enviaron nuevos avisos documentales por correo.
7. **Alta de cuentas por otras entradas:** el cambio inicial está integrado al alta de trabajador renovada. No se afirma que todas las entradas antiguas de creación de usuarios de Superadministración hayan adoptado este contrato.
8. **Aceptación completa:** faltan el mapeo y ejecución integral de los 212 casos, revisión independiente de seguridad/BDD y accesibilidad completa. Build conserva la advertencia de tamaño del bundle; no se corrigió mediante un cambio global fuera de alcance.

No se modificaron reglas clínicas, perfiles de deterioro cognitivo ni el motor experto como parte de esta renovación administrativa.
