---
title: Changelog documental de RememberMind
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-27
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

## 2026-10-27 — Revisión del trabajo acumulado para commit y push

- [Informe de revisión](sistema/REVISION_PUBLICACION_2026_10_27.md): conserva las
  entregas de Gerencia, acceso, correo, reportes e integración experta; mantiene
  secretos, datos persistentes y artefactos locales fuera de la publicación.
- Portada documental con inventario vigente de 106 tablas de negocio;
  prueba responsive de Gerencia alineada con su composición actual y fixture
  temporal de Medicación corregido sin alterar la regla clínica.
- Frontend final 276 correctos y 4 omitidos; build correcto. Fallos de la suite
  general visibles en el informe; la publicación no acredita producción.

## 2026-10-27 — Altas y sustituciones concurrentes de Gerencia

- [Informe41](especificacion-funcional/vistas/gerencia/41-ALTAS-Y-SUSTITUCIONES-CONCURRENTES.md): P03/P17 corregidos y probados. Alta repite unicidad y autorización bajo coordinador; sustitución compara original capturado al abrir, conserva historia y rechaza formulario obsoleto antes de crear archivos.
- Nueve carreras reales PostgreSQL con dos procesos bloqueados;62/446 SQLite y52/425 PostgreSQL, dos formularios Livewire, recorrido real en dos pestañas y cinco anchos, buildPASS. Sin migraciones, permisos operativos nuevos, cambios de datos institucionales o envíos reales. Inventario106 de negocio conservado. Sin staging, commit, push ni despliegue.

## 2026-10-27 — Gerencia35/37 instaladas y prórroga por expediente

- [Decisión específica](base-de-datos/DECISION_GERENCIA_DOCUMENTACION_HISTORIA_2026_10_27.md) tras «autorizo»: seis relaciones y ocho columnas nullable instaladas en PostgreSQL local,106 de negocio +16 técnicas; respaldo previo y datos originales conservados.
- P19 conciliado mediante concesión única de la misma alta, lectura de procedencia y plazo compartido sin copiar autorías ni ocultar contradicciones; sin cambios al DDL revisado.
- [Informe40](especificacion-funcional/vistas/gerencia/40-INSTALACION-AUTORIZADA-Y-PRORROGA-EXPEDIENTE.md):30/229 SQLite,36/265 PostgreSQL, instalación atómica3/22 y3/24, navegador58 combinaciones sin desbordamiento y buildPASS. Verificación posterior de90 restricciones y cuatro triggers. Mantiene pendientes de aceptación del rol y separa validación clínica. Sin staging, commit, push ni despliegue.

## 2026-10-26 — Regresiones de aceptación de Gerencia

- [Informe39](especificacion-funcional/vistas/gerencia/39-ACEPTACION-REGRESIONES-Y-CASOS.md): entrega puntual observada tarde sin mora retrospectiva, fotografía reconocida sin renombrados, sustitución con cadena/tipo/disco/autoría conservados y revisión laboral contextual.
- Control de edición obsoleta en Galería mediante el original ya protegido; teclado/foco y errores asociados de Contactos; nuevas carreras de horarios y negativos de rollback/revocación/archivos.
- [Inventario de212 casos](../output/auditoria-integral-20261025/gerencia-casos-aceptacion.csv) generado desde nueve contratos con script reproducible. No declara aceptación total ni instala35/37; discrepancia prórroga por alta/destinatario documentada antes de instalación.

## 2026-10-26 — Contactos, DA01 e historia de Planificación de Gerencia

- Familiares/contactos reutiliza la entrada Social: identidad original separada, dos presentaciones, vínculos sin autorizaciones por defecto y acceso FAMILIAR mediante enlace seguro aprobado. Mantenimiento de correo/estado/reemisión separado del vínculo; cuatro permisos existentes17B aplicados al rol GERENTE.
- DA01 adoptada: desactivación bloqueada con asignaciones o planes activos. Guardias previas, jornadas cerradas, cancelaciones y autoría histórica se preservan; calendario de Gerencia usa asignaciones reales con filtro de Enfermería.
- [Propuesta37](especificacion-funcional/vistas/gerencia/37-PROPUESTA-HISTORIA-VINCULOS-ASIGNACIONES.md): dos relaciones nominales con cadenas secuenciales e inmutabilidad, probadas fuera de la base institucional. Inventario permanece100;35/37 instalados producirían106, sujetos a aprobación específica.
- [Informe38](especificacion-funcional/vistas/gerencia/38-INTEGRACION-CONTACTOS-PLANIFICACION-Y-ACEPTACION.md) enlaza pruebas, revisiones y capturas; sustituye pendientes históricos solo dentro del alcance demostrado. Sin commit, push ni despliegue.

## 2026-10-26 — Renovación de Gerencia y extensión de Personal aprobada

- Integración de Inicio, Personal, Áreas/equipo, Planificación/cobertura, Residentes, Alertas, Reportes PDF/CSV y Galería con componentes compartidos y alcances de lectura/escritura diferenciados. Correcciones H02/H03 en horarios; se preserva H04.
- Diseño aprobado de ocho columnas y cuatro relaciones, implementado exclusivamente en bases desechables: solicitud, membresía, entrega privada, revisión/anulación, prórroga y movimiento laboral. Cambio inicial obligatorio, recuperación por enlace y avisos derivados de fuentes propias; sin autosuspensión por vencimiento ni nueva auditoría corporativa.
- [Informe 36](especificacion-funcional/vistas/gerencia/36-RENOVACION-GERENCIA-IMPLEMENTACION-Y-PRUEBAS.md) conserva comandos, evidencia SQL/navegador/build, capturas y pendientes. El inventario instalado sigue en 100; el destino sería 104 después de una instalación autorizada. No se declara ejecutada toda la aceptación del rol ni resuelto Familiares/contactos. Sin commit, push o despliegue.

## 2026-10-26 — Implementación del acceso de Gerencia a Ocupación H04

- Corregida la ruta compartida: consulta directa mediante controlador/mapa existentes, sin redirección al dashboard administrativo. Filtros/vistas/paginación/detalle conservan la ruta permitida; enlaces administrativos se ofrecen solo con acceso al destino.
- Seis regresiones nuevas y expectativas antiguas de redirect actualizadas. Suite focalizada: 56 pruebas PHP, 361 aserciones; dos JS y build correctos. SQLite desechable, sin prueba PostgreSQL o QA visual con sesión en este hito.
- Informe 34 y matrices actualizados; se preserva auditoría histórica y trabajo simultáneo. Sin nuevas tablas, permisos, datos, commit, push o despliegue.

## 2026-10-26 — Galería e imágenes; matriz de cobertura de Gerencia

- Decisión 27B y operación de Galería aprobada el 17/10 detalladas: doce datos, doce controles, carga/edición, parcial, duplicados, privacidad y archivo/reactivación; 24 casos especificados, no ejecutados.
- Dos imágenes generadas de espacios ficticios sin personas, copiadas al proyecto. No se cargaron a Galería ni se presentaron como fotos del centro; diferencias de botones/shell respecto a tokens quedan anotadas.
- Matriz de fichas principales del rol con 212 escenarios especificados, evidencia de código/histórica, límites de imágenes y pendientes de implementación, autorización, estructura y QA. Sin cambios de aplicación, datos, permisos, BDD, commit o push en esta tarea.

## 2026-10-26 — Reportes de Gerencia e imágenes

- Decisión 26B/C desarrollada en cuatro fichas: indicadores agregados, preparación PDF/CSV, doce datos/contextos, diez controles y 24 casos especificados, no ejecutados; 188 acumulados del rol.
- Dos imágenes con datos ficticios y referencias de componentes: vista general y preparación de descarga. Se corrigió etiqueta de gráfico para evitar doble conteo entre abiertas/reconocidas; no son pantalla implementada ni PDF emitido.
- Documentadas brechas de stock/período, personal/cuentas, payload clínico, CSV nominal frente a agregado y coherencia consulta/descarga. Sin cambios de aplicación, permisos, BDD, datos, comunicaciones, commit o push en esta tarea.

## 2026-10-26 — Alertas de Gerencia e imágenes

- Decisión 25A detallada en cuatro fichas: bandeja única, doce datos, diez controles, detalle/historia/origen y 24 escenarios especificados, no ejecutados; 164 acumulados del rol.
- Dos propuestas visuales generadas y refinadas con datos ficticios, copiadas al proyecto y vinculadas a componentes/tokens reales. No son capturas, implementación ni aprobación visual definitiva; diferencias de estado/color generadas quedan anotadas.
- Supervisión de lectura separada de intervención, notificaciones y pendientes documentales; diferencias de autorización, ámbitos de métricas y fuentes identificadas. Sin cambios de aplicación, permisos, BDD, datos, comunicaciones, commit o push en esta tarea.

## 2026-10-26 — Residentes y ocupación de Gerencia

- Decisiones 29B y 24A/B detalladas: directorio en tarjetas/tabla y ocupación en mapa/tabla, veinte datos nominales, acciones y fuentes; sin códigos técnicos visibles ni nueva escritura clínica/operativa por implicación.
- Veinticuatro escenarios nuevos especificados, no ejecutados; 140 acumulados. Se localizan consultas, componentes y pruebas existentes; se documentan H04, selección técnica del responsable e historia de alojamiento con límites/origen por verificar.
- La propietaria pide refinar el diseño de las propuestas reutilizando componentes y tokens reales. No se declara aprobación visual definitiva. Sin cambios de aplicación, BDD, datos, permisos, commit o push en esta tarea.

## 2026-10-26 — Planificación, cobertura e imágenes de Gerencia

- Decisiones 21A/22A/23A desarrolladas: semana con revisión/confirmación, avisos internos y tabla de cobertura con detalle; 28 escenarios especificados, no ejecutados.
- Dos propuestas visuales generadas con datos ficticios y copiadas al proyecto, con detalle de cada zona, control, color/tipografía y comportamiento. Son imágenes de referencia pendientes de aprobación, no capturas o implementación.
- Se corrige el mapeo documental de Cobertura al catálogo de turnos y se registran brechas de autorización, vigencia, fecha final, grupos serializados y solapamiento nocturno. Sin código de aplicación, BDD, permisos, comunicaciones, commit o push en esta tarea.

## 2026-10-26 — Especificación de Áreas y equipo de Gerencia

- Entrada única 20A desarrollada con diez campos, once acciones, formulario y detalle de equipo; veinte escenarios de aceptación especificados, no ejecutados.
- Fuentes de asignación por jornada, responsable derivado, métricas y exportación agregada contrastadas con código. Se identifican estados ACTIVO/ACTIVA, lecturas sin corte temporal, divergencias de exportaciones y política de desactivación por precisar.
- Índice y ficha del rol enlazados; 88 escenarios documentales acumulados. Sin cambios de aplicación, BDD, permisos, datos, comunicaciones, commit o push en esta tarea.

## 2026-10-26 — Familiares y contactos de Gerencia

- Dos presentaciones sobre contactos/vínculos únicos, 21 campos, 12 acciones y formularios separados para identidad, relación y cuenta; reutiliza decisiones 17B/18A/19B-A.
- Documentada separación de parentesco, responsabilidad, emergencia, información y salida; defaults favorables de código no se adoptan como autorizaciones institucionales. Hallazgos estáticos de entradas paralelas, límites de parentesco e historia.
- 24 escenarios de aceptación nuevos especificados, no ejecutados. Índice y fichas de roles conciliados; sin cambios de aplicación, BDD, permisos, datos, comunicaciones, commit o push en esta tarea.

## 2026-10-25 — R01–R03 adoptadas y solicitud documental por rol

- La propietaria elige R01A añadiendo petición documental por rol, R02A y R03A. Mínimo inmediato obligatorio; demás respaldos opcionales por defecto, con solicitudes expresas de Gerencia.
- Nueva especificación de solicitud: ocho campos, selección y confirmación de destinatarios, autoría, fecha límite, vínculo con entregas y ocho escenarios adicionales. No es una columna genérica ni un catálogo instalado.
- Anulación laboral y baja/reactivación laboral con motivo/fecha/historia conciliadas en fichas, baseline y autorización. Cuenta separada y continuidad de asignaciones con Administración; sin borrado de pasado.
- 44 escenarios especificados, no ejecutados. Implementación, representación SQL y pruebas pendientes; sin cambios de aplicación, BDD, permisos, comunicaciones, commit o push en esta tarea.

## 2026-10-25 — Especificación detallada de Personal y documentos de Gerencia

- Nuevas fichas de operaciones y documentación laboral: 23 campos/destinos, 16 acciones y 36 escenarios de aceptación con pruebas históricas localizadas, no ejecutadas.
- Comparación de tres catálogos y brechas de nombres de fotografía, datos de carga, fecha de alta, plazo/prórroga y cambio inicial obligatorio; no se usan columnas ajenas o texto libre como solución estructural.
- Tres decisiones residuales reunidas: documentos adicionales requeridos, facultad de anulación y baja laboral. Se conservan decisiones 1–29. Índice y enlaces integrados; sin cambios de aplicación, permisos, BDD, commit o push en esta tarea.

## 2026-10-25 — Respuestas conjuntas de Gerencia 15–29

- La propietaria adopta `15A, 16B, 17B, 18A, 19B Y A, 20A, 21A, 22A, 23A, 24A Y B, 25A, 26B Y C, 27B, 28A, 29B`. Cuestionario y matriz conservan respuesta literal y límites.
- Se concilian baseline, autorización, fichas de Gerencia/Familiar, Inicio y alta: contraseña temporal con cambio inicial, recuperación por enlace, vínculos familiares, cuenta Familiar activa, avisos internos y PDF/CSV agregados. Las vistas dobles comparten datos.
- Adopción funcional; implementación y pruebas pendientes. Solo documentación en esta tarea; sin migraciones, permisos instalados, comunicaciones, commit o push.

## 2026-10-25 — Cuestionario consolidado de Gerencia

- Por solicitud de la propietaria, las preguntas pendientes se presentan juntas: 15–29, con opciones detalladas de acceso, familiares, organización, planificación, consulta y reportes.
- Se conservan las decisiones 1–14; ninguna opción nueva se adopta por defecto. Documento de propuestas, sin permisos, comunicaciones, cambios de aplicación o BDD ejecutados.

## 2026-10-25 — Cuenta laboral activa al finalizar el alta

- La propietaria elige A: nueva cuenta ACTIVA después de completar correctamente la operación conjunta; sin activación manual adicional ni validación documental automática.
- Se concilian ficha, plan, baseline y aceptación. La Action ya asigna ACTIVO, pero el circuito nuevo y las credenciales iniciales siguen pendientes de integración/verificación. Sin cambios de aplicación, permisos instalados o BDD.

## 2026-10-25 — Mínimo documental inmediato del alta

- La propietaria ratifica A: cédula, fotografía y CV al alta; título/certificado para las seis profesiones y matrícula para Medicina. Los demás requeridos que admitan plazo pueden entregarse posteriormente; opcionales sin deuda documental.
- Matriz y aceptación documentadas en Alta de trabajador y conciliadas en Personal, plan y baseline. La entrega no equivale a validación; sin cambios de aplicación o BDD. Estado inicial de la cuenta por decidir.

## 2026-10-25 — Alta de trabajador en cuatro pasos

- La propietaria elige A: identidad/contacto, información laboral, cuenta y documentación; avance/retroceso y guardado final conjunto.
- Nueva ficha detallada con campos observados, botones, estados y aceptación pendiente. Se identifican siete pasos y comunicaciones de 48 horas en el código actual, además de mínimo documental inmediato por conciliar. Sin cambios de aplicación o BDD.

## 2026-10-25 — Recordatorios internos de documentación

- La propietaria elige A: avisar al trabajador y Gerencia dentro del sistema dos días antes y al vencer, cuando falten entregas; también para el plazo prorrogado.
- Flujo consolidado en la ficha de Personal, baseline y plan de Gerencia: carga compartida, revisión gerencial, siete días iniciales y única prórroga de siete. Sin avisos enviados, cambios de aplicación, permisos o BDD; implementación y pruebas pendientes.

## 2026-10-25 — Prórroga documental única de siete días

- La propietaria elige A: Gerencia puede conceder una sola prórroga de siete días calendario adicionales al vencimiento original del alta, conservando motivo, autor y fechas. No admite renovaciones sucesivas ni suspensión automática.
- Baseline, plan y ficha de Personal actualizados. Canal y anticipación de avisos pendientes; sin cambios de aplicación, permisos o BDD.

## 2026-10-25 — Carga documental compartida y revisión de Gerencia

- La propietaria elige C: el trabajador sube sus documentos propios y Gerencia puede cargar los recibidos; Gerencia realiza la revisión.
- Se registra titular, autoría/fecha de carga y revisión diferenciadas. Baseline, ficha del rol, plan y vista conciliados; implementación y pruebas pendientes, sin cambios de aplicación, permisos instalados o BDD.

## 2026-10-25 — Seguimiento gerencial de documentación vencida

- La propietaria elige avisar a Gerencia y señalar documentación vencida al superar el plazo de entrega, sin suspensión automática de acceso.
- Gerencia decide el seguimiento, prórroga o suspensión manual dentro de su alcance aprobado. Se documenta conservación del vencimiento y de las prórrogas; canales, límites y responsables de carga/revisión pendientes. Sin cambios de aplicación o BDD.

## 2026-10-25 — Plazo documental de siete días

- La propietaria fija siete días calendario desde la fecha del alta para completar la entrega documental, incluidos fines de semana y feriados. Se distingue entrega de validación posterior.
- Se actualizan baseline, plan de Gerencia y ficha de Personal. Efectos del vencimiento, avisos y prórrogas pendientes; sin cambios de aplicación o BDD.

## 2026-10-25 — Alta con documentación pendiente y plazo

- La propietaria adopta la opción A condicionada a un plazo para completar documentos. Se documentan documentos pendientes y fecha límite, diferenciando entrega de validación.
- Duración, cómputo y efectos del vencimiento pendientes; no se presume suspensión automática. Decisión conciliada en baseline, plan y ficha de Personal; sin cambios de aplicación o BDD.

## 2026-10-25 — Cuenta obligatoria en las altas laborales de Gerencia

- La propietaria elige la opción B: ficha laboral y cuenta vinculada en una operación atómica, con correo y rol autorizado; sin modalidad de alta sin cuenta inicial en ese flujo.
- Se concilió la decisión en baseline funcional, ficha del rol, plan y vista de Personal. No modifica la relación general personal/usuario ni acredita competencia clínica. Implementación y pruebas pendientes; sin cambios de aplicación, permisos o BDD.

## 2026-10-25 — Navegación de altas de Gerencia

- La propietaria elige dos apartados: «Personal» y «Familiares y contactos», cada uno con listado y formulario propios, reutilizando identidades y vínculos existentes.
- Decisión registrada en el plan de Gerencia y la ficha de Personal. La posibilidad de registrar un trabajador sin cuenta inicial queda por definir; sin cambios de aplicación, permisos o BDD.

## 2026-10-25 — Mantenimiento de cuentas por Gerencia

- La propietaria autoriza editar datos y activar/suspender acceso de las cuentas de los ocho roles destino ya definidos. No incluye cuentas de Gerencia/Superadministración, cambio de roles, catálogo de permisos ni eliminación.
- Se registra la distinción entre suspensión de cuenta y estado laboral. Se continúa con la organización documental de formularios de trabajadores/familiares; sin implementación o modificación de permisos instalados.

## 2026-10-25 — Alta de cuentas por Gerencia

- Decisión expresa: Gerencia crea cuentas de Administración, los seis roles profesionales y Familiar; no crea Gerencia ni Superadministración. Superadministración mantiene el alta de los diez roles.
- Se concilió el alcance en el baseline funcional y las fichas de Gerencia. Mantenimiento posterior de cuentas pendiente de definir por operación; sin cambios de código, permisos instalados o BDD.

## 2026-10-25 — Catálogo y funciones de los diez roles

- Contraste del baseline de roles y `$rolesActivos` sin ejecutar seed ni modificar permisos.
- [Catálogo funcional](especificacion-funcional/roles/README.md): tres roles institucionales, seis profesionales y Familiar; diez fichas con funciones, límites, módulos, continuidad y participación cognitiva.
- Los ejemplos de permisos se identifican como código observado; no equivalen a autorización efectiva o prueba ejecutada.


## 2026-10-25 — Espacio de especificación funcional detallada

- Nueva carpeta [especificacion-funcional](especificacion-funcional/README.md), con doce entradas de módulo, diez perfiles de rol, inventario Blade y plantillas de módulo/vista/rol/componente.
- La estructura está preparada; las fichas detalladas permanecen en elaboración. No crea permisos, reglas clínicas ni cambios BDD.


## 2026-10-25 — Conciliación y limpieza funcional

- Inventario actual coherente en instrucciones y entradas BDD: 100 de negocio y 16 técnicas; máximo de diseño 110.
- Estado y portal actualizados a la auditoría del 25/10; evidencia anterior archivada con sus cortes.
- Doce responsabilidades de módulo y un índice funcional, sin duplicar diccionarios o redefinir reglas.
- Fuentes expertas adoptadas y pendientes separados; DEC-OPEN-005 acotada; restricciones de fases anteriores retiradas de la lectura vigente.
- Enlaces locales revisados; rutas trasladadas conciliadas y artefactos ausentes indicados sin fabricarlos.
- [Acta de cambios, verificaciones y límites](auditorias/2026-10-25/LIMPIEZA_DOCUMENTAL.md). Solo Markdown; sin producto, BDD, commit, push ni despliegue en esta tarea.

## 2026-10-20 — Historiales con Superadministrador

- [Análisis de acceso y conexiones](sistema/HISTORIALES_SUPERADMINISTRADOR_2026_10_20.md): 73 fichas abiertas, inventario privado por residente y límites de las proyecciones clínicas.
- Correcciones de fechas documentales V2 y observación institucional; pruebas dirigidas SQLite/PostgreSQL y build ejecutados. La completitud del visor longitudinal conserva hallazgos pendientes.
- Información individual conservada en almacenamiento privado, excluida del repositorio; sin modificación de historias, cuentas, permisos ni resultados expertos.

## 2026-10-11 — Desacoplamiento posterior a la preparación

- [Informe por etapas](arquitectura/DESACOPLAMIENTO_2026_10_11.md): contexto autenticado, sesión de previsualización, archivos, correo, PDF, enlaces y adaptadores expertos detrás de contratos.
- Consultas de ficha y paneles de alertas centralizadas, con preparación después de hidratación Livewire y autorización compartida de pacientes.
- Gate AST y regresiones de identidad/petición, alcance, carga diferida y almacenamiento fallido. Las excepciones Laravel/Eloquent y los límites de atomicidad de archivos permanecen explícitos.

## 2026-10-11 — Preparación para continuar desarrollo

- [Entrega vigente](sistema/PREPARACION_DESARROLLO_2026_10_11.md) con seis etapas, commits locales, comandos seguros y limitaciones abiertas.
- Estado actual actualizado con suite SQLite completa (1.079/31.452, 15 omisiones), cohorte PostgreSQL (143/2.489), frontend (151) y build; evidencia separada de historia, producción y validación clínica.
- README e índices apuntan al repositorio canónico y a los límites de arquitectura comprobados. Instalación general sin migraciones ni regeneración de claves automáticas.

## 2026-10-10 — Auditoría posterior a la migración híbrida

- Organización vigente actualizada a doce propietarios y cuatro capas; retiradas las instrucciones canónicas Backend/Frontend.
- Índices de arquitectura, agentes y sistema experto apuntan a las ubicaciones reales. La evidencia histórica conserva su alcance y los documentos anteriores permanecen en Git.
- [Auditoría y limpieza](arquitectura/AUDITORIA_LIMPIEZA_2026_10_10.md) documenta archivos retirados, comprobaciones y deuda abierta; el inventario de migración distingue los cuatro PHP retirados posteriormente.
- La evidencia del servicio de incidentes sin consumidores se identifica como histórica; no se atribuyen sus reglas a los casos activos.

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
