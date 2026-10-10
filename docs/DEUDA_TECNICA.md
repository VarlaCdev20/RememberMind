---
title: "Deuda técnica de RememberMind"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-06
owner: RememberMind
source_of_truth: false
verified_against_commit: 8e9e20325519c5da5a6b25f6fb26568cad78efe1
verification_scope: STATIC_REPOSITORY_REVIEW
runtime_verified: false
runtime_evidence_status: PARTIAL
runtime_evidence_scope: FASE_3_AND_4_AUTOMATED_TESTS_ISOLATED_POSTGRESQL
supersedes: []
related_docs: []
related_modules: []
---

# Deuda técnica

Las descripciones originales de problema/implementación/evidencia registran hallazgos de Fases 1–2. Las actualizaciones Fases 3–4 y Estado tienen prioridad para conocer correcciones y verificación actuales; no se borran los hallazgos históricos. Referencias de línea antiguas se relocalizan por símbolo. Evidencia runtime acotada en TRAZABILIDAD; no certificación global.

**BUG:** discrepancia concreta de contrato e implementación; reproducción/impacto indicado aparte. **DEUDA:** riesgo o mantenimiento con evidencia. **DECISIÓN PENDIENTE:** [registro institucional](DECISIONES_PENDIENTES.md). **MEJORA FUTURA:** [roadmap](ROADMAP.md). No duplicar como deuda una idea sin señal real.

## TECH-001

**Actualización:** Fase 3 lote A: proyección familiar de identidad y visitas propias; clínica, PDF y documentos del residente no publicados se bloquean. Vínculo/contacto/cuenta activos y permiso explícito. Pruebas y alcance en TRAZABILIDAD.md; DEC-OPEN-006 permanece OPEN.

**Área:** Familiar / reportes / documentos

**Tipo:** BUG

**Problema:** Entradas de detalle/reporte usan view del residente y cargan/serializan clínica; esa autorización por vínculo no determina contenido compartible.

**Evidencia:** ResidentePolicy:21–33; ResidenteController::show:29–32 carga atenciones.notas/contactos/prescripciones y JSON completo; ReporteV2Controller:15–18; vistas pages/residentes/show y reportes/residente. Dashboard familiar limita datos en RoleDashboardDataService:275–321.

**Impacto:** ALTO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** Dashboard limita consulta; no se confirmó mitigación de rutas directas. Exposición runtime no reproducida en esta fase.

**Solución deseada:** Reautorizar recurso/contenido y filtrar consultas/serialización/reportes conforme contrato vigente.

**No solucionar mediante:** Conceder permiso clínico amplio, ocultar botón o asumir que vínculo publica expediente.

**Dependencias:** Contrato vigente; DEC-OPEN-006 solo si se busca publicar más contenido.

**Tests necesarios:** Familia A/B, tipo de contenido permitido/no publicado, JSON/HTML/export/download directo y ausencia de información sensible.

**Estado:** VERIFIED_RUNTIME

## TECH-002

**Área:** Alertas

**Tipo:** DEUDA

**Problema:** Creación/eventos difieren entre controladores y servicio; no hay trayectoria equivalente acreditada para todas las entradas.

**Evidencia:** AlertaController:48 crea CREADA y :90 CAMBIO_ESTADO; AlertasService:26–45 crea manual sin evento y :98–108 registra otros eventos; AlertasFlujoTest:51–58 espera cuatro eventos de recorrido.

**Impacto:** ALTO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** Existen caminos con eventos; no afirmar ausencia global de historial.

**Solución deseada:** Unificar contrato de origen/evento/transición en capa responsable y probar cada entrada autorizada.

**No solucionar mediante:** Borrar historial, agregar catálogo/campos no aprobados o usar notificación/log como evento.

**Dependencias:** Contrato por operación; decisiones de estados si falta significado.

**Tests necesarios:** Preview cero efectos, creación + evento exigido, transición + historia, rollback y reintentos conforme contrato.

**Estado:** VERIFIED_RUNTIME

**Actualización Fase 3:** Creación manual Panel/Service y detección automática agregan CREACION; HTTP conserva CREADA/CAMBIO_ESTADO. Mutaciones de Panel y HTTP delegan estados/eventos al Service; actor real, fecha y estados en la misma transacción. Evento previo inalterado y rollback de creación/cambio probados. Lectura preventiva no persiste. Gate PostgreSQL 24 tests / 109 aserciones PASS (38.614 s); test visual de timeline preexistente sigue fuera de este gate y se conserva en suite completa. Catálogo y cierre crítico directo existentes preservados. Evidencia en TRAZABILIDAD.

## TECH-003

**Área:** Instrumentos / cognición / legacy

**Tipo:** DEUDA

**Problema:** Interpretación MOCA/MMSE con umbrales y máximo fallback embebida en controlador heredado; aprobación metodológica/derechos no localizada.

**Evidencia:** AdultoMayorEvaluacionController:43 usa puntaje_maximo ?? 30; :54–79 interpreta códigos; routes/web.php:399–402 conserva entrada. InstrumentoController valida estructura y puntaje en servidor.

**Impacto:** ALTO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** Flujo V2 estructurado existe; ello no valida umbrales/licencias del heredado.

**Solución deseada:** Reconstruir contrato/fuentes y retirar clasificación/fallback legacy en tarea posterior compatible con decisiones aprobadas.

**No solucionar mediante:** Inventar nuevos umbrales, declarar motor experto entregado o copiar reactivos protegidos.

**Dependencias:** DEC-OPEN-005; contrato por instrumento/versión.

**Tests necesarios:** Coherencia instrumento/pregunta/opción, ausencia de defaults inventados, atribución, versión y casos aprobados con datos sintéticos.

**Estado:** OPEN

## TECH-004

**Actualización:** Fase 3 lote A: veto explícito fuera de local/testing y cuenta ACTIVO; caso production con flag true denegado. Excepción documentada local/testing permanece limitada al contexto propio.

**Área:** Excepción clínica Superadmin

**Tipo:** DEUDA

**Problema:** Límite local/testing está en valor predeterminado del flag; servicio leído no añade veto directo por entorno si el flag se fuerza true.

**Evidencia:** PERMISOS_TEMPORALES_SUPERADMIN:5; config/remembermind.php:5–7; AccesoClinicoTemporalService:41–52. Configuración desplegada no revisada.

**Impacto:** ALTO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** Default false fuera de local/testing; no prueba estado de producción.

**Solución deseada:** Verificar guardia efectiva por entorno y retirada conforme documento temporal, sin expandir competencia.

**No solucionar mediante:** Convertir excepción en permiso clínico permanente o asumir producción comprometida sin evidencia.

**Dependencias:** Contrato de excepción vigente y revisión de matriz final.

**Tests necesarios:** Local/testing con flag/contexto válidos; preview/flag off; entorno de producción con flag true según contrato denegado.

**Estado:** VERIFIED_RUNTIME

## TECH-005

**Área:** Persistencia / pruebas BDD

**Tipo:** DEUDA

**Problema:** Cobertura física y de concurrencia no se demuestra por lectura Eloquent/SQLite. Preadmisión→0..1 admisión no está garantizada por UNIQUE en migración inicial.

**Evidencia:** Diccionario cardinalidad de admisiones; create_admisiones_table:13,22 FK nullable sin UNIQUE; FormalizarAdmision:28–33 protege en Action; IntegridadFisicaBddV2Test y BddOperativaV2Test definidos; baseline matriz concurrencia PARCIAL.

**Impacto:** ALTO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** Action transaccional/bloqueos existentes; no equivalen a toda ruta SQL ni carrera ejecutada.

**Solución deseada:** Acreditar garantías por capa/motor y proponer estructura faltante solo con aprobación; diseñar pruebas reales PostgreSQL.

**No solucionar mediante:** Agregar UNIQUE/índices silenciosamente, fingir concurrencia secuencial o resetear DB importante.

**Dependencias:** DEC-OPEN-001/002 según campo; entorno PostgreSQL desechable; cualquier cambio congelado aprobado.

**Tests necesarios:** SQL directo relevante, dos conexiones/procesos con sincronización, fallo intermedio/rollback y garantía instrument/pregunta/contacto/residente por capa.

**Estado:** PARTIAL

**Actualización Fase 3:** VERIFIED_RUNTIME acotado a rollback de admisión (9 puntos), carrera de cama con dos procesos/PIDs reales y locks simultáneos, y gates existentes de integridad física. Una admisión gana y otra se rechaza sin huérfanos. No se cambia esquema. El TECH completo queda PARTIAL: otras carreras/garantías SQL directas y catálogos conflictivos no quedan certificados por esta prueba. Prueba de contacto ajeno acredita Model, no FK compuesta nueva. Evidencia y correcciones de fixtures en TRAZABILIDAD.

## TECH-006

**Área:** Documentación / trazabilidad

**Tipo:** DEUDA

**Problema:** Matriz/afirmaciones antiguas del baseline confunden implementación, catálogo y evidencia ejecutada.

**Evidencia:** Baseline matriz menciona test anterior de 70 y lock pendiente pese a Action con lock; §29 administración mezcla estado con resultado; diccionario resumen de relaciones de preadmisión atribuye profesional a usuarios frente a autoría dual en valoraciones_enfermeria_preadmision. CODEX_SETUP anterior ofrecía scripts/setup-codex.ps1, ausente del árbol inspeccionado; guía corregida sin crear el script.

**Impacto:** MEDIO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** Portal/estado distinguen autoridad y código; avisos de conflicto sin alterar reglas rectoras.

**Solución deseada:** Actualizar matriz por capa y motor y resolver texto normativo sensible mediante decisión correspondiente.

**No solucionar mediante:** Cambiar clínica/estructura para acomodar documento o convertir código en catálogo aprobado.

**Dependencias:** DEC-OPEN-001/002; evidencia de pruebas futuras.

**Tests necesarios:** Verificación documental de símbolos/rutas; resultados de suites solo si ejecutados con entorno/fecha.

**Estado:** OPEN

## TECH-007

**Área:** Documentos legales

**Tipo:** DEUDA

**Problema:** Términos/privacidad son plantillas sin contenido institucional.

**Evidencia:** resources/markdown/terms.md y policy.md:3 dicen Edit this file...; no se revisó publicación/despliegue.

**Impacto:** MEDIO potencial; alcance observado, no incidente desplegado confirmado.

**Workaround actual:** No verificado si están publicados; no afirmar cobertura legal por archivos existentes.

**Solución deseada:** Definir contenido institucional por responsable competente y revisar publicación en tarea correspondiente.

**No solucionar mediante:** Generar obligaciones legales desde plantilla o declarar cumplimiento por presencia de archivos.

**Dependencias:** Responsable institucional/legal y alcance real de publicación.

**Tests necesarios:** Contenido aprobado, rutas/lectores y exposición reales cuando corresponda; no ejecución legal simulada.

**Estado:** OPEN

## Evidencia adicional de Fase 2 sobre deuda existente

**TECH-002 — IMPLEMENTATION GAP HIGH:** AlertasPanel::guardarAlerta crea directamente sin evento inicial; AlertasService::crear usa transaction/lock y tampoco registra evento de creación. AlertaController::store crea CREADA; ServicioDecisionAlertaClinica crea CREACION. AlertasFlujoTest espera cuatro eventos de asignación/intervención/seguimiento/cierre, no el inicial. Mantener un único TECH para esta divergencia; no resolver catálogos sin DEC-OPEN-002.

**TECH-005 — TEST GAP HIGH:** se localizaron índices/garantías específicas en harden_v2_data_integrity, UNIQUE de participantes y resultados/respuestas, además de guardias Eloquent. No se ejecutaron instalación ni carreras PostgreSQL. Tests secuenciales de repetición de pase no acreditan concurrencia. Admisión tiene happy path definido; fallo intermedio inducido de admisión no localizado en el mapeo de Fase 2.

**TECH-006 — CONFLICT MEDIUM:** DecisionAdmisionModal opera sobre residente existente con DECISION_ADMISION/PENDIENTE_ASIGNACION/DERIVADO, distinto del ingreso canónico; no presentar esa entrada como sustituto de FormalizarAdmision. Cama::scopeDisponibles exige DISPONIBLE; FormalizarAdmision admite ACTIVA. Valores físicos y condición institucional permanecen en DEC-OPEN-001, sin cambiar estructura o catálogo.

## TECH-008

**Área:** Continuidad / pase / medicación / cuidado / alertas

**Tipo:** BUG — CONTINUITY GAP HIGH

**Contrato:** hechos asistenciales y pendientes relevantes deben conservar significado y estar disponibles para el siguiente lector autorizado.

**Implementación observada:** PaseTurnoService::obtenerContextoClinicoResidente filtra administraciones por estado ADMINISTRADA o PENDIENTE/OMITIDA, mientras RegistrarAdministracionMedicacionService escribe estado REGISTRADA y resultado ADMINISTRADA/OMITIDA. AgendaMedicacionService deriva pendientes desde horarios sin administración; PaseTurnoService::pendientes no incorpora esa agenda de medicación. CuidadoController::ejecutar admite EJECUTADA; el pase busca REALIZADA. El pase omite alertas RECONOCIDA/ASIGNADA, que MiTurnoService sí incluye entre activas.

**Impacto/riesgo:** proyección incompleta o semánticamente incorrecta de hechos/pendientes; no incidente runtime confirmado. No implica pérdida de registros fuente.

**Archivos:** [PaseTurnoService](../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php), [administración](../app/Backend/Modulos/Medicacion/Servicios/RegistrarAdministracionMedicacionService.php), [agenda](../app/Backend/Modulos/Medicacion/Servicios/AgendaMedicacionService.php), [CuidadoController](../app/Http/Controllers/Cuidados/CuidadoController.php), [MiTurno](../app/Backend/Modulos/Enfermeria/Servicios/MiTurnoService.php).

**Tests:** PaseTurnoReconstruidoTest protege ciclo/receptor; equivalencia de esas proyecciones clínicas NOT_FOUND en el mapeo de Fase 2. No suite ejecutada.

**Acción futura:** contrastar writers/lectores por campo/fecha/jornada y corregir proyecciones conforme contrato existente; comprobar hechos y pendientes derivados. No crear tabla de tareas, expediente paralelo ni normalizar resultados a estados por conveniencia.

**Dependencias:** catálogo aplicable DEC-OPEN-002 si requiere decisión; correcciones técnicas compatibles pueden investigarse sin decisión nueva.

**Estado:** VERIFIED_RUNTIME

**Actualización Fase 3:** PaseTurnoService lee resultado ADMINISTRADA/OMITIDA sobre estado REGISTRADA; agenda deriva PENDIENTE/PROXIMA/VENCIDA sin fabricar omisiones ni copiar fuentes. Omisiones explícitas mantienen motivo y referencia; cuidados REALIZADA/EJECUTADA y pendientes previos, alertas RECONOCIDA/ASIGNADA, incidentes/heridas conservan sus fuentes. Gate PostgreSQL PaseTurnoReconstruidoTest + AgendaMedicacionTest + MiTurnoServiceTest: 46 tests / 203 aserciones PASS, 13.310 s. Proyección acotada, no tabla universal ni decisión PRN/reintentos. Evidencia en TRAZABILIDAD.

## TECH-009

**Actualización:** Fase 3 lote A: permisos backend de pase, jornada saliente exacta, residente/turno vigentes, recepción con cuenta/personal activos y editar; anulación propia transaccional conserva observación de recepción. Action de admisión y revisión de preadmisión reautorizan. Contexto laboral HTTP valida entidades activas y jornada operable. Gate PostgreSQL y negativos ejecutados en TRAZABILIDAD.md; integración de alertas verificada en lote C. Los resultados finales F3 y la reejecución F4 se registran en TRAZABILIDAD. TECH completo PARTIAL por entradas/casos no ejecutados exhaustivamente.

**Área:** Fronteras de autorización por entrada

**Tipo:** DEUDA — AUTHORIZATION GAP HIGH

**Contrato:** cuenta activa + permiso de acción + regla + relación/contexto/competencia deben proteger toda mutación sensible. Acceder al panel para leer no concede escribir.

**Implementación observada:** PaseTurnoPanel::mount comprueba lectura; guardarBorrador/confirmarEntrega delegan sin permiso de mutación explícito. PaseTurnoService::procesarPase comprueba asignación de residente/personal sin acotarla a jornada saliente y no comprueba permiso de escritura. confirmarRecepcion comprueba receptor y ciclo, pero no revalida cuenta ACTIVO/permiso; anularPase no valida autorización, scope ni estado de origen. generar sí usa TurnoEnfermeriaService y permiso crear, pero crea GENERADO directamente; HTTP registrarPase usa AutorizacionClinicaService y crea EMITIDO con jornadas input, sin la misma resolución del Service. PreadmisionesPanel revisión comprueba rol, a diferencia del permiso específico HTTP; FormalizarAdmision depende de su llamador para autorización.

**Frontera adicional:** AdultoMayorSignosVitalesController::anular actualiza estado y activity tras comprobar residente del signo, sin Service/contexto clínico visible. No se localizó una ruta activa para ese método en web.php; se reporta frontera de clase, no explotación desplegada. User::checkPermissionTo tiene veto clínico adicional y no debe ignorarse al evaluar accesibilidad. Rectificación/Request expediente usa permiso crear además del middleware editar; no equivalencia automática con Action primaria.

**Contexto laboral HTTP:** InstitucionalController::abrirJornada valida exists turno/fecha; asignarPersonal valida exists personal/área sin revalidar estado activo del personal ni vigencia/estado de jornada. Panel de plaza, ARJ HTTP y servicios clínicos tienen controles adicionales; no atribuirlos a esos métodos. [InstitucionalController](../app/Http/Controllers/Identidad/InstitucionalController.php). Negativos de contexto por esta entrada NOT_FOUND en mapeo.

**Impacto/riesgo:** controles distintos pueden permitir contexto no válido o denegar una operación legítima según entrada. Alcance externo exacto y reproducción runtime NOT VERIFIED. No se afirma que todas las mutaciones estén desprotegidas.

**Archivos:** [PaseTurnoPanel](../app/Frontend/Livewire/Enfermeria/Cuidados/PaseTurnoPanel.php), [Service](../app/Backend/Modulos/Enfermeria/Servicios/PaseTurnoService.php), [CuidadoController](../app/Http/Controllers/Cuidados/CuidadoController.php), [preadmisiones](../app/Frontend/Livewire/Admisiones/PreadmisionesPanel.php), [signos](../app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php), [User](../app/Models/User.php), [rutas](../routes/web.php).

**Tests:** PaseTurnoReconstruidoTest cubre emisor/receptor/ciclo/repetición secuencial; negativos por permiso revocado/cuenta inactiva/jornada específica/anulación no autorizada NOT_FOUND en mapeo. CasosPreadmisionTest cubre lectura/creación, no equivalencia completa de revisión. Tests definidos, no ejecutados.

**Acción futura:** localizar llamadores efectivos y revalidar cada operación en su frontera backend reutilizable; negativos por entrada, cuenta, permiso y contexto. No resolver con ocultar botones ni conceder profesión/permiso más amplio. Diferenciar cambio técnico de decisión institucional.

**Dependencias:** contratos de Fase 2, excepción temporal TECH-004; ninguna DEC nueva por permiso faltante.

**Estado:** PARTIAL

## TECH-010

**Actualización:** Fase 3 lote A: errores inesperados de entradas relacionadas de admisión, residentes, signos, pase y medicación se reportan y usan mensajes públicos seguros. Validación y HttpException esperados se conservan. Inyección runtime de fallo en signos HTTP pasa y conserva reporte técnico/mensaje seguro/sin persistencia. Revisión read-only no localizó getMessage inesperado público en las entradas del núcleo auditadas; TECH completo PARTIAL, no se inyectó fallo en cada una.

**Área:** Mensajes de errores inesperados

**Tipo:** BUG — IMPLEMENTATION GAP MEDIUM

**Contrato:** errores inesperados se reportan internamente y muestran una respuesta segura, sin SQL/stack/internos.

**Implementación observada:** AdultoMayorSignosVitalesController::store/update y DecisionAdmisionModal::guardar capturan Exception y pasan getMessage() al mensaje público. No toda excepción es validación de negocio. MisPacientes distingue ValidationException/QueryException y usa report + mensaje seguro; no es el mismo comportamiento de las entradas heredadas.

**Impacto/riesgo:** mensajes de excepción podrían revelar detalles internos. No se reprodujo error ni exposición real en producción.

**Archivos:** [Controller signos](../app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php), [modal decisión](../app/Frontend/Livewire/Admisiones/DecisionAdmisionModal.php), [MisPacientes](../app/Frontend/Livewire/Enfermeria/Cuidados/MisPacientes.php).

**Tests:** respuesta segura ante fallo SQL/inesperado de esas entradas NOT_FOUND en mapeo; sin ejecución.

**Acción futura:** distinguir validación esperada y fallo inesperado, reportar este último y responder seguro. No vaciar catch ni fingir éxito. No cambiar regla de negocio para evitar error.

**Estado:** PARTIAL

## TECH-011

**Área:** Validación y persistencia de signos en entrada expediente

**Tipo:** BUG — IMPLEMENTATION GAP HIGH

**Contrato:** el registro de signos debe contener mediciones de las variables de signos persistidas; peso y dolor tienen entidades clínicas propias. La Action primaria exige al menos una de las siete mediciones.

**Implementación observada:** StoreSignosVitalesRequest::withValidator y SignosVitalesService::validarYNormalizar incluyen peso/dolor entre los valores que satisfacen el mínimo. registrar/rectificar solo persisten las siete variables de signos; SignoVital no impone mínimo de medición. Peso o dolor aislados pueden atravesar la validación y crear una fila con las siete variables null. La garantía de mínimo de la Action primaria no se aplica a ese camino.

**Impacto/riesgo:** registro longitudinal sin medición de signos y falsa señal de un evento documentado. Evidencia estática; no petición runtime ejecutada ni incidente real confirmado. Problema de contenido/validación distinto de TECH-009 (autorización) y TECH-010 (mensajes de error).

**Archivos:** [Request](../app/Http/Requests/Clinica/StoreSignosVitalesRequest.php), [Service](../app/Backend/Modulos/Clinica/Servicios/SignosVitalesService.php), [Action primaria](../app/Backend/Modulos/Clinica/Acciones/RegistrarSignosVitalesAction.php), [Model](../app/Models/SignoVital.php).

**Tests:** caso negativo de peso/dolor aislado en expediente y rectificación, verificando ausencia de signo vacío, NOT_FOUND en el mapeo de Fase 2; no se ejecutaron suites. Preview/Action tienen cobertura definida, pero no acreditan esta entrada.

**Acción futura:** contrastar validación con las variables que se guardan y separar registros de dolor/antropometría según sus contratos; conservar historia existente y reglas aprobadas. No añadir columnas a signos ni fabricar valores para pasar validación.

**Estado:** VERIFIED_RUNTIME

**Actualización Fase 3:** Mínimo corregido sobre las siete variables persistibles en Request y Service. Seis negativos RED de peso/dolor pasaron tras el parche; gate PostgreSQL 14 tests / 59 aserciones PASS. Conserva nullable, PA completa y registros anteriores; fallo de evento crítico revierte signo/alerta/evento. Evidencia acotada en TRAZABILIDAD, sin certificación global ni cambios de estructura.

## Localizadores históricos de Fase 1

[ResidentePolicy](../app/Policies/ResidentePolicy.php), [ResidenteController](../app/Http/Controllers/Residentes/ResidenteController.php), [ReporteV2Controller](../app/Http/Controllers/Reportes/ReporteV2Controller.php), [AlertasService](../app/Backend/Modulos/Alertas/Servicios/AlertasService.php), [AlertaController](../app/Http/Controllers/Alertas/AlertaController.php), [controlador evaluación heredado](../app/Http/Controllers/Valoraciones/AdultoMayorEvaluacionController.php), [Action admisión](../app/Backend/Modulos/Admisiones/Acciones/FormalizarAdmision.php), [baseline](base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md).

Rutas/contadores obsoletos del README, índice BDD y CODEX_SETUP se corrigieron documentalmente en Fase 1; no permanecen como bugs de producto. Fase 2 amplía evidencia de TECH existentes y añade TECH-008..011. No se solucionó código funcional ni se creó nueva regla. [Contratos y cobertura](TRAZABILIDAD.md) permiten localizar alcance/test por módulo.

## Evidencia adicional de Fase 4

**TECH-005 — PARTIAL:** PostgreSQL 18.4 aislado, PHP 8.3.33: IntegridadFisicaBddV2Test + BddOperativaV2Test + OcupacionCamaIntegrityTest, 26 tests / 241 aserciones PASS. Se añaden dos pruebas SQL directo, 2/6 PASS: segunda cama activa del mismo residente rechazada por uq_ocupacion_residente_activa y administración con residente ajeno rechazada por fk_administracion_prescripcion_residente. La administración coherente sí se inserta; el intento rechazado no deja fila. Los savepoints preservan la transacción del test tras el error PostgreSQL. La clase física también pasa SQLite :memory: (5 tests / 13 aserciones), sin certificar locking PostgreSQL mediante SQLite. Esto acredita restricciones físicas existentes, sin modificar ninguna migración. Contacto/consentimiento e instrumento/pregunta siguen acreditados por Model en sus pruebas: no equivalen a FK compuesta ni a SQL directo protegido. Otras carreras, reintentos PRN y toda entrada SQL no quedan certificados. No se cierra TECH-005.

**TECH-009 — PARTIAL:** AreasInstitucionalesV2Test ejecuta las tres entradas Livewire Excel general, Excel de área y CSV general: cuenta sin permiso y cuenta desactivada después de montar el panel son denegadas, no invocan exportador ni registran auditoría de éxito. Se conserva el permiso areas.reportes y la comprobación vigente de ACTIVO; no se añade permiso ni bypass. La clase completa pasa 18 tests / 60 aserciones. No acredita equivalencia universal Controller/Livewire/Action ni autorización contextual de otros módulos.

**TECH-010 — PARTIAL:** esas tres exportaciones reportan el Throwable inesperado y despachan un mensaje público exacto seguro. Tres inyecciones de excepción SQL/ruta/credencial sintéticas pasan; el error esperado de área inexistente da mensaje recuperable y no se reporta como fallo técnico. La auditoría de generación se escribe después de que el exportador retorna: una generación fallida no registra éxito. Las tres respuestas positivas conservan descarga/auditoría/actor. Se simula el límite de exportación; estos tests no validan bytes ni formato del Excel real, ni entrega efectiva al cliente. La búsqueda getMessage/Throwable/Exception y salidas públicas de app/ continúa mostrando consumidores fuera del núcleo ejercitado (usuarios/personal, documentos y valoraciones, entre otros). No se sustituyeron mensajes de dominio/validación por errores genéricos; no se afirma seguridad global de todas las excepciones.

TECH-003/006/007 permanecen OPEN. TECH-006 es deuda documental/catálogos, no un identificador de paleta CSS. DEC-OPEN-001..006 permanecen OPEN y sus documentos no cambian. Los fallos de filtros/topbar/sidebar se resuelven solo dentro del inventario activo F4; no se declara una renovación UX ni limpieza global de estilos.

### Clasificación de fronteras de excepción revisadas

La búsqueda sistemática sobre app/ cubrió getMessage, Throwable/Exception, flash/dispatch/toast y response/JsonResponse; su salida temporal contiene 720 coincidencias, no 720 fallos certificados. Otra búsqueda de tipos esperados contiene 183 coincidencias. La lectura de las salidas con getMessage diferencia estas fronteras; no constituye ejecución de todas las entradas:

| Tipo | Fuente contrastada | Tratamiento observado / límite |
|---|---|---|
| DOMAIN EXCEPTION | [ContextoClinicoService](../app/Backend/Modulos/Clinica/Servicios/ContextoClinicoService.php), [ActividadesPanel](../app/Frontend/Livewire/Administracion/Actividades/ActividadesPanel.php), [ParticipacionPanel](../app/Frontend/Livewire/Administracion/Actividades/ParticipacionPanel.php) | LogicException con mensajes definidos de contexto laboral se traduce a validación recuperable; se conserva |
| VALIDATION EXCEPTION | [RegistroSignosVitalesModal](../app/Frontend/Livewire/Compartido/Clinica/RegistroSignosVitalesModal.php), [PersonalInstitucionalForm](../app/Frontend/Livewire/Administracion/Identidad/PersonalInstitucionalForm.php) | Se conservan errores por campo/mensajes accionables; no se sustituyen por fallo técnico genérico |
| AUTHORIZATION EXCEPTION | [AutorizacionClinicaService](../app/Backend/Modulos/Clinica/Servicios/AutorizacionClinicaService.php), [controlador de signos](../app/Http/Controllers/Clinica/AdultoMayorSignosVitalesController.php) | Contexto produce 403 con mensaje definido; el controlador propaga HttpException y ValidationException, reportando solo el inesperado |
| DOMAIN EXCEPTION esperada | [AreasInstitucionalesPanel](../app/Frontend/Livewire/Superadministrador/Identidad/AreasInstitucionalesPanel.php) | Área inexistente recibe mensaje seguro recuperable; comprobado por test, sin report técnico ni auditoría de éxito |
| UNEXPECTED EXCEPTION | Mismo panel, tres exportaciones Excel/CSV | Report + mensaje público seguro y ausencia de auditoría de éxito; comprobado con inyección sintética |
| Frontera amplia que puede incluir UNEXPECTED EXCEPTION | [UsuariosPanel](../app/Frontend/Livewire/Administracion/Identidad/UsuariosPanel.php), [UsuarioFichaPanel](../app/Frontend/Livewire/Administracion/Identidad/UsuarioFichaPanel.php), [ValoracionInicialModal](../app/Frontend/Livewire/Compartido/Valoraciones/ValoracionInicialModal.php), [IncidentesPanel](../app/Frontend/Livewire/Enfermeria/Cuidados/IncidentesPanel.php), [UsuarioController](../app/Http/Controllers/Identidad/UsuarioController.php) | Persisten salidas públicas de getMessage en exports/subida/guardado. Riesgo pendiente; las capturas generales no garantizan que el mensaje sea de dominio |
| Frontera amplia convertida a validación | [StoreAdministracionMedicacionRequest](../app/Http/Requests/Medicacion/StoreAdministracionMedicacionRequest.php) | Captura Throwable al autorizar y añade getMessage al validator; no se certifica separación entre denegación esperada y fallo técnico |
| UNEXPECTED EXCEPTION con diagnóstico interno | [ReportExportService](../app/Backend/Modulos/Reportes/Servicios/ReportExportService.php), [EnviarFichaUsuarioJob](../app/Jobs/EnviarFichaUsuarioJob.php), [AreaReporteController](../app/Http/Controllers/Reportes/AreaReporteController.php) | getMessage se usa en log/fallback; las respuestas públicas inspeccionadas son seguras. Esto no acredita minimización global de logs ni operación real de correo/PDF |

Resultados globales y límites posteriores en [TRAZABILIDAD](TRAZABILIDAD.md). PASS de una prueba acotada no cierra ningún TECH completo ni certifica producción.
