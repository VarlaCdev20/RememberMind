---
title: "Interfaz médica de resultados del sistema experto"
status: CURRENT
version: "1.2"
last_reviewed: 2026-10-08
owner: RememberMind
source_of_truth: false
verified_against_commit: null
verification_scope: READ_ONLY_EXISTING_RECORDS_AND_DOCUMENTARY_KNOWLEDGE_LOAD
runtime_verified: true
clinical_runtime_verified: false
documentary_knowledge_loaded: true
documentary_knowledge_load_approved: true
clinical_inference_activated: false
related_docs:
  - IMPLEMENTACION_V1.md
---

# Interfaz de resultados para Medicina y Enfermería

Implementación sobre el árbol de trabajo sin commit. Las suites usan datos
artificiales en bases aisladas; la consulta adicional de `remembermind_dev`
usa sus registros sintéticos existentes. Ninguna certifica conocimiento clínico.
La referencia visual es la imagen de resultados aportada por la propietaria.

## Acceso y navegación

Ruta real: `/admin/medico/residente/{residente}/evaluacion-cognitiva/resultados-experto`.
Nombre: `admin.medico.residente.resultados-experto`.

Entradas: ficha médica, botón **Resultados cognitivos**; carpeta **Cognitivo**
del expediente, **Consultar resultados del sistema experto**. Se conserva el
sidebar compartido. La consulta administrativa `/admin/sistema-experto` continúa
siendo una superficie distinta.

La Policy requiere sesión autenticada, cuenta activa, rol médico o
Superadministrador real, permisos explícitos `residentes.ver`,
`controles_cognitivos.ver` y `valoracion_medica.ver`, autorización del residente
y personal activo para el médico. Familiares y previsualización de roles se
rechazan. Se vuelve a autorizar cada interacción y consulta de fuente.
La fuente instrumental exige además `aplicaciones_instrumento.ver`.
No se añaden permisos ni concesiones a roles.

### Consulta de Enfermería

La propietaria autorizó incorporar esta consulta a Enfermería el 2026-10-08.
Ruta: `/admin/enfermeria/pacientes/{residente}/resultados-experto`, nombre
`admin.enfermeria.pacientes.resultados-experto`. Entradas: resumen del residente
en **Mis residentes**, selector de formularios en **Consulta clínica → Sistema
experto** y cabecera de la ficha. El retorno conserva el residente seleccionado.

Se reutilizan el componente y los lectores existentes, con el layout de
Enfermería. La Policy admite el rol real `ENFERMEROS`, personal activo y los
permisos existentes `residentes.ver`, `controles_cognitivos.ver` y
`enfermeria.ver_ficha_paciente`. El residente debe pertenecer al alcance de
asignaciones activas de la jornada de hoy, usando el mismo filtro de lectura
del directorio. Otro rol adicional con lectura global no amplía este alcance;
la vía médica conserva su autorización independiente. Cada interacción vuelve
a comprobar la asignación y los permisos; la URL directa también los exige.

Enfermería consulta perfil, evidencias, fundamento e historial. No se muestran
los controles de revisión profesional. No se concede `valoracion_medica.ver`
ni acceso instrumental sin su permiso específico. Permanecen pendientes la
publicación clínica institucional y el contrato de revisión profesional.

## Flujo de consulta

1. Contexto del residente y de la evaluación, versión original y solicitante
   cuando está registrado.
2. Selección de los cinco criterios cognitivos, sin convertir inactividad o
   ausencia en normalidad o en no evaluabilidad.
3. Resultado e interpretación limitados al alcance examinado, cuando el contrato
   de publicación y la integridad de los registros lo permiten.
4. Tabla de evidencias en escritorio, presentación por registro en móvil.
   Admisibilidad, representación, rol y participación se conservan explícitos.
5. Consulta autorizada del **dato fuente actual**, diferenciada del dato original
   usado durante la inferencia. No se exponen respuestas instrumentales.
6. Fundamento desplegable en la misma pantalla: fuente → mapeo de variable →
   evidencia → variable/valor → condición → regla → consecuencia registrada.
7. Historial paginado con criterio, evaluabilidad y estado de resultado verificado
   para cada evaluación. No promedia instrumentos, recalcula ni atribuye progresión.
8. Revisión profesional visible, con cinco acciones y observación deshabilitadas.
   La interfaz explica por qué no pueden registrarse todavía.

## Contratos y límites

Fuentes: O.R.I.O.N. 4.16/4.19 y D-123/D-137 para persistencia;
D-122/D-125/D-131/D-133/D-143/D-144 para evaluabilidad, resultado y soporte.
El encargo de interfaz de la propietaria define la composición y el lenguaje.

`config/sistema_experto.php` mantiene **vacío** el registro `lectura_clinica`.
El estado ACTIVO de una versión no acredita aprobación institucional. Una futura
habilitación de publicación requiere una decisión explícita y contratos por
versión histórica: criterios, estados de ejecución/inferencia, roles y estados
de participación utilizables y componentes específicos de memoria aprobados.
Este registro no activa el motor. Los contratos `SINTETICA_*` existen únicamente
en la fixture y en el servidor QA aislado; no son catálogos institucionales.

COG-MEM conserva cero resultados para EV-CM-0/1 y exige resultado compatible
para EV-CM-2. Se comprueban dominio exacto, versiones, condiciones/soportes,
participación y evidencia instrumental específica. MIXTOS requiere las dos
categorías opuestas y conjuntos efectivos de soporte diferentes, agrupando
transitivamente el mismo episodio clínico. No se admite una consecuencia mixta
directa ni evidencia contextual como soporte de un resultado.

La traza SQL no conserva valor original, mapeo exacto del valor, corte/fecha
original ni las comprobaciones originales del contexto y componente. La pantalla
declara esas ausencias. No reconstruye datos históricos desde respuestas actuales.
Un registro incompatible suprime la conclusión y presenta un aviso de integridad.
Los cuatro criterios adicionales tienen acceso de consulta y estado independiente;
sus compuertas y resultados requieren formalización específica antes de publicación.

No existe contrato de persistencia de revisión profesional. Las decisiones y
observaciones no se simulan con notas, auditoría ni estado de sesión.
No se crean diagnósticos, recomendaciones terapéuticas, órdenes o alertas.

## Archivos e integración

- `app/Policies/EvaluacionExpertaPolicy.php`: autorización contextual de lectura.
- `app/Backend/Modulos/SistemaExperto/Servicios/LecturaResultadosExperto.php`:
  lectura, integridad e historial, con relaciones cargadas en grupos.
- `app/Backend/Modulos/SistemaExperto/Servicios/LecturaFuenteEvidencia.php`:
  fuentes y campos explícitamente permitidos del mismo residente.
- `app/Backend/Modulos/SistemaExperto/Presentacion/LenguajeResultadosExperto.php`:
  etiquetas y plantillas de presentación.
- `app/Frontend/Livewire/Medico/Clinica/ResultadosExpertoResidente.php`:
  selección y consulta; identificadores bloqueados contra manipulación.
- `resources/views/livewire/medico/resultados-experto-residente.blade.php` y
  tres parciales: evidencias, fundamento e historial.
- `resources/frontend/styles/design-system/patterns/expert-results.css`:
  composición y selección con tokens compartidos, temas y movimiento reducido.
- `routes/web.php`, cabecera de la ficha y carpeta cognitiva: navegación real.
- `tests/Feature/SistemaExperto/ResultadosExpertoResidenteTest.php` y fixture
  en `tests/Support/SistemaExperto/FixtureLecturaExperta.php`.

La carpeta cognitiva ahora presenta aplicaciones y valores guardados. Se retiró
el promedio de instrumentos heterogéneos, la tendencia inferida entre dos
puntajes y el fallback de datos ausentes a NORMAL, directamente relacionados
con esta entrada de resultados.

## Verificación

Las pruebas comprueban rutas y navegación reales, cinco criterios, autorización,
cuenta/personal, permisos revocados, preview, IDOR, estados vacíos, publicación
pendiente, resultados permitidos, contexto, dominios, independencia transitiva,
componentes no utilizables, fuente actual, historial y paginación.
Se comparan huellas de las 23 tablas expertas, notas, alertas y auditoría antes
y después de consultar. Las operaciones protegidas no modifican esas tablas.

Comandos de esta entrega:

```powershell
php artisan test --compact --filter=ResultadosExpertoResidenteTest
php vendor/phpunit/phpunit/phpunit --filter=ResultadosExpertoResidenteTest
php artisan test --compact --filter='ConocimientoPanelTest|EjecucionExpertaTest|MedicoFichaUnificadaTest'
npm run build
```

PHP utilizado: 8.3.30. SQLite `:memory:` y PostgreSQL dedicado
`remembermind_experto_test_20261008_0de3e225`. El segundo comando usa extensiones
PDO PostgreSQL y variables dirigidas exclusivamente a esa base desechable.
La BDD institucional no se migra ni se siembra en esta entrega.

Extensión de Enfermería: **26 pruebas / 178 aserciones PASS en SQLite**;
después de añadir el caso de rol adicional, su prueba específica pasa con
**8 aserciones**. La versión final pasa en **PostgreSQL con 26 pruebas / 179
aserciones**. Regresión de ficha médica, botones y supervisión de Enfermería:
**16 pruebas / 80 aserciones PASS**. **Build PASS**. Se prueban consulta
asignada, entradas reales, retorno, publicación pendiente, residente ajeno,
roles adicionales, asignación revocada, jornada cerrada, personal inactivo y
permiso revocado. El rol de Enfermería permanece sin `valoracion_medica.ver`.

QA de esta integración: **30 estados** en cinco anchos y dos temas, con
resultado sintético, área sin evaluación y publicación deshabilitada; selección
de historial/criterio, entrada desde resumen, retorno e IDOR mediante
HTTP/Livewire real. Sin errores JavaScript, overflow horizontal, literales
inválidos ni controles activos menores de 44 px en la superficie de resultados.
Huellas de las 23 tablas expertas, notas, alertas y auditoría intactas tras QA.
Los artefactos `nursing-*` se conservan en el mismo directorio temporal aislado.
Esto acredita integración de lectura con datos artificiales; no certifica
conocimiento clínico ni habilita su publicación. El defecto heredado de textos
verticales en el sidebar contraído continúa fuera del cambio de integración.

QA: HTTP/Livewire real con dos residentes sintéticos admitidos formalmente en
SQLite temporal aislada. Se revisan 1440, 1280, 1024, 768 y 390 px en claro/oscuro,
selección, carga, teclado, targets, reflow, evidencias, fundamento y revisión.
Los artefactos se guardan fuera del repositorio, en
`%TEMP%/remembermind-medical-results-qa/`.

Resultados automatizados: **22 pruebas / 141 aserciones PASS en SQLite**, y
**22 pruebas / 141 aserciones PASS en PostgreSQL**. Regresión relacionada:
**19 pruebas / 153 aserciones PASS** (panel técnico, ejecución conservada y ficha
médica). **Build PASS** con Vite 8. Las revisiones independientes de seguridad
y contratos de lectura no dejaron hallazgos bloqueantes.

QA final: **70 combinaciones** de siete estados, cinco anchos y dos temas;
**130 interacciones** con carga visible. Además, capturas del vacío global,
publicación pendiente, tabla de evidencias, fundamento abierto y revisión.
Sin errores JavaScript, desbordamientos, literales prohibidos ni objetivos
interactivos menores de 44 px. Contraste medido en 100 conjuntos de pantalla
mediante colores renderizados por canvas, sin fallos AA de texto habilitado.
El fundamento conserva su apertura al consultar una fuente. Reflow equivalente
a 200% comprobado a 720 CSS px y escala 2; no se controló el zoom nativo del navegador.
La huella de las tablas expertas, notas, alertas y auditoría de QA permanece igual.
Dictamen independiente: **PASS para la superficie nueva**. No certifica el shell
global ni una prueba manual con lector de pantalla. La estrechez del sidebar y
sus etiquetas verticales observadas son heredadas de la estructura compartida;
sus archivos no se modificaron en esta entrega. Se registra como pendiente ajeno
al módulo. El contraste mínimo habilitado medido es 4.78:1.

La lectura anterior a la carga documental de `remembermind_dev` confirmó 23 tablas expertas sin filas y
32 residentes previos. La nueva consulta no cargó conocimiento ni datos de prueba
en esa base. El servidor QA y sus artefactos son independientes del servidor habitual.

No se ejecutó la suite global completa en esta entrega; las evidencias anteriores
de `IMPLEMENTACION_V1.md` mantienen su alcance propio.

El trabajo no introduce dependencia nueva, esquema, seed clínico, reglas
institucionales, compatibilidad V1 ni escritura clínica. Commit/push: no realizados.

## Datos existentes y carga documental autorizada — 08/10/2026

La propietaria indicó usar los datos existentes y autorizó expresamente la carga
del conocimiento. Se incorporó la instantánea O.R.I.O.N. `ORION-V1-20261008`:
**55 conceptos y 29 relaciones** entre conceptos identificados. La red conserva
las cinco áreas cognitivas, bloques y elementos transversales, definiciones,
estados documentales, filas fuente y límites. El concepto BL-LON aparece dos
veces en la fuente: se conserva una identidad con la descripción posterior y
ambas procedencias. D-123 normaliza el predicado candidato de subcomponente;
su texto original queda conservado en la observación de la relación.

Las otras 107 relaciones documentales incluyen campos operativos o proposiciones
sin identidad de nodo definida: no se convierten automáticamente en nodos ni en
reglas. La carga no incluye mapeos de `CONSERVADA`/`ALTERACION_LEVE`, componentes
instrumentales ni condiciones/consecuencias clínicas ausentes de la fuente.
La autorización de carga no proporciona esas definiciones faltantes.

El comando `sistema-experto:cargar-orion {autor}` exige una cuenta identificada,
activa y de Superadministración con `auditoria.ver`. Inserta en transacción;
un reintento idéntico no duplica filas ni auditoría, y una versión divergente
se rechaza sin sobrescribirla. Se registra un evento con Activitylog, sin datos
clínicos. La versión permanece INACTIVA y sin vigencia: carga documental y
activación de inferencia son operaciones distintas. No se modificó el registro
`lectura_clinica` ni la compuerta de persistencia técnica.

La carga se ejecutó realmente en `remembermind_dev`. Las huellas de las 79
tablas preexistentes contrastadas permanecieron iguales; estas incluyen datos
operativos y tablas técnicas estables. Se excluyeron de esa comparación las
tablas expertas y las tablas técnicas volátiles/auditoría. No se ejecutaron
migraciones ni resets. Las evaluaciones expertas siguen en **0**.

La pantalla muestra la definición documental del área seleccionada y, para
memoria sin evaluación experta, los últimos cinco controles cognitivos VIGENTES
con fecha no futura, profesional, valores y observación guardados. No infiere
preservación a partir de FALSE ni convierte una observación en conclusión.
Se comprobó la consulta backend para los cinco residentes asignados a Rosa:
Mercedes Choque Rivera, Gloria Sánchez Condori, Hugo Romero Torrico, Elena Vargas
Mamani y Teresa Aguilar Rojas. Todos presentan cinco controles y la versión
documental, sin resultados inventados. Estos registros proceden de la carga de
desarrollo autorizada; no se certifican como evaluaciones clínicas auténticas.

Pruebas de esta ampliación: carga, procedencia, separación del criterio,
idempotencia, versión divergente, rol/cuenta/permiso y lectura sin inferencia.
Resultado final: **32 pruebas / 329 aserciones PASS en SQLite y PostgreSQL**
dedicado, **build PASS**. La vista principal en el puerto 8000 se verificó con
la sesión de Enfermería y los registros existentes: cambio de área, procedencia
y despliegue de controles anteriores. Sin errores JavaScript ni desbordamiento
horizontal en el ancho real del navegador (846 px). No se repitió aquí la matriz
completa de anchos/temas de la entrega anterior.
La consulta mantiene los límites de Enfermería y no modifica fuentes, alertas,
notas ni historial experto. Los artefactos de contraste PostgreSQL se guardan
en `%TEMP%/remembermind-medical-results-qa/orion-main-*.json`; la captura de la
vista principal es `orion-existing-records.png` en ese mismo directorio.

## Caso sintético completo autorizado — 2026-10-08

La propietaria eligió un caso completo en **base de pruebas separada**. Se
generaron tres respuestas artificiales de un instrumento técnico propio, sin
reactivos protegidos ni datos aplicados a Elena Vargas Mamani. El recorrido usa
adaptación, compuerta EV-CM-2, motor y persistencia normalizada existentes; no
inserta una conclusión prefabricada. RULE_A cumple y RULE_B no cumple: el valor
técnico es DIFICULTAD_EVIDENCIADA, sin significado diagnóstico acreditado.

La vista de Enfermería muestra resultado, interpretación técnica, fuente con
las tres respuestas, reglas/condiciones/soportes, versión, autor y fechas. El
informe de QA conserva entradas, corte, método y verificaciones de esta ejecución
como **artefacto de prueba**, separado de la instantánea clínica institucional.
Las otras cuatro áreas continúan sin evaluación: no se formalizan por analogía.
La revisión profesional no se simula ni se firma desde Codex.

La lectura técnica exige APP_ENV=testing, bandera explícita, SQLite en memoria
o PostgreSQL con nombre desechable, VER_TEST y estados/origen técnicos. Conserva
Policy, asignación y permisos de fuente. El permiso instrumental adicional se
concedió únicamente a la cuenta artificial de la base QA, no a Rosa en desarrollo.
El informe además exige coincidencia de evaluación, residente y versión.

Caso reproducible: `Tests\Support\SistemaExperto\CasoCompletoSintetico::crear()`
sobre una base de pruebas vacía. Instancia visual en puerto **8018**, PostgreSQL
`remembermind_experto_test_20261008_199ad3a4dd`. Artefactos y arranque local en
`%TEMP%/remembermind-expert-complete-qa/`; credenciales privadas fuera del Git.
Las 102 tablas estables contrastadas de remembermind_dev no cambiaron; se
excluyeron sesiones, cache, cola y auditoría volátiles. No hubo reset principal.

Verificación final: **132 pruebas / 3727 aserciones en SQLite** y **132 pruebas /
3866 aserciones en PostgreSQL**, PASS técnico. Revisión independiente de lectura
PASS. Vista clara comprobada a 1440, 1281, 1024, 768 y 390 CSS px: sin desbordes,
literales inválidos ni errores JavaScript. No certifica validación clínica.
Se retiró del recorrido una simulación que atribuía hallazgos y una profesional
ficticia al residente seleccionado. Sin commit ni push.
