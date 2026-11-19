---
title: "Estado comprobado de incorporación del sistema experto"
status: SUPERSEDED_IN_PART
version: "1.0"
last_reviewed: 2026-11-04
owner: ApoyoDecision
source_of_truth: false
runtime_verified: true
verification_scope: [read_only_catalogs, registered_routes, code_and_contract_review, targeted_sqlite_regression]
---

# Dictamen

**Sucesión posterior en el mismo día:** la propietaria autorizó el alcance de conservación del resultado y revisiones. Se implementó, probó e instaló; véase [el cierre vigente](CIERRE_RECORRIDO_INSTITUCIONAL_2026_11_04.md). El texto que sigue documenta el corte anterior a esa autorización y no mantiene su rechazo como bloqueo actual. La conservación íntegra del DTO para replay sigue siendo una capacidad distinta pendiente.

El sistema experto está incorporado parcialmente a RememberMind. No es un
programa paralelo: utiliza el motor PHP existente y las pantallas de
ApoyoDecision, Valoraciones, Medicina, Psicología y Enfermería. **El recorrido
completo con resultado conjunto guardado, historial idéntico y revisiones
posteriores de ese resultado todavía no está terminado.**

La propietaria pide incorporación total. El criterio de cierre es que cada
función esté accesible por el rol autorizado, con fuentes SQL originales,
operación backend, resultado o abstención, conservación real y recuperación.
Un ensayo, un script aislado o un DTO en memoria no satisfacen ese cierre.

“Local” en entregas anteriores identifica el entorno en que se comprobó o
instaló algo. No identifica por sí solo una función desconectada del producto.
Los tres catálogos iniciales están disponibles a los formularios normales de
la aplicación configurada. Publicar otra instalación es una operación distinta;
esta revisión no prueba despliegue, CI remoto ni disponibilidad de otro servidor.

## Fuentes vigentes y alcance observado

- [Orden de desarrollo técnico primero](ESTADO_FUNCIONAL_Y_ORDEN_DESARROLLO_2026_10_30.md): se desarrolla y prueba sin pedir estudios, firmas o avales externos.
- [Contratos institucionales A–D](PROPUESTA_CONTRATO_INSTITUCIONAL_FASE_4_2026_10_19.md), [E–H](PROPUESTA_PETERSEN_INSTITUCIONAL_FASE_5_2026_10_19.md) y [C01–C05](CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md).
- [Activación inicial del04/11](ENTREGA_ACTIVACION_PERFILES_INICIALES_2026_11_04.md).
- [Propuesta de conservación del31/10](../base-de-datos/PROPUESTA_CONSERVACION_RESULTADO_INSTITUCIONAL_2026_10_31.md): rechazada en ese alcance para mantener relaciones y permisos actuales.
- [Decisión sin nuevas tablas](../base-de-datos/DECISION_SIN_NUEVAS_TABLAS_2026_10_27.md).
- Código local observado: rama `feature/apoyo-decision-informe-neurocognitivo`, HEAD `cf8ec4eb`, con trabajo simultáneo existente preservado.

El inventario se obtuvo con `scripts/inventariar-experto.php` en una transacción
PostgreSQL de solo lectura. Consulta catálogos/configuración y metadatos de
estructura, sin leer expedientes o fuentes clínicas de residentes.

## Incorporación real por parte

| Parte | Estado comprobado | Límite que impide declarar incorporación total |
|---|---|---|
| Captura de originales y contexto | Formularios y escritores existentes con autor, residente, fecha, atención y procedencia | La presencia de campos no prueba que un expediente particular los tenga completos |
| MoCA8.1 español, RUDAS-PE2020 y FAQ B7S2008 | Los tres catálogos están activos y cotejados;35 campos de captura y puntuación en conjunto | Sus perfiles son descriptivos: no producen automáticamente cinco estados de dominio, C02 o C03 |
| Conocimiento y red | Motor PHP existente, cinco dominios/19 componentes y versiones institucionales | Conocimiento definido no equivale a una medida enlazada y aplicable en un caso |
| Mapeos interpretativos | Existen métodos y mecanismo de vínculos por identidad/versiones/preguntas | Configuración interpretativa operativa vacía y contrato cognitivo incorporado sin vínculos instrumentales |
| Inferencia cognitiva | Consulta y botón de análisis usan el motor real; admite abstención | Sin vínculo y método aplicables no se acredita una dificultad instrumental |
| Petersen | Deriva C02 y evalúa C01/C03/C04/C05 con juicios y soportes originales | Requiere datos admisibles; sus cuatro patrones no se obtienen repartiendo un total global |
| Explicación | Construida desde la adquisición autorizada de la consulta | No es recuperación de una evaluación conjunta anterior conservada |
| Historia observacional | Circuito de guardar, recuperar y revisión por ambas disciplinas existente | No sustituye el resultado conjunto instrumental/Petersen |
| Conservación conjunta | DTO `EVALUACION-INSTITUCIONAL-1` preparado en memoria | `persistido=false`; falta escritor/lector SQL institucional íntegro |
| Revisiones del resultado conjunto | Se preservan tabla, servicios y límites de revisión existentes | No pueden enlazarse al resultado conjunto transitorio como si estuviera guardado |
| Acceso por roles | Rutas y Policies registradas en la aplicación | Enfermería consulta su alcance; no se amplía ejecución o firma por mostrar un botón |

## Inventario efectivo del04/11

- 32 reglas en SQL:17 cognitivas,4 Petersen,3 observacionales y8 del paquete
  histórico de simulación. Estas últimas no son reglas institucionales nuevas;
  el circuito operativo excluye simulaciones.
- 205 nodos semánticos y130 relaciones en los catálogos almacenados.
- 26 métodos registrados según el circuito existente:23 por componentes y3
  iniciales descriptivos. No todos los métodos registrados tienen un perfil
  operativo vinculado.
- Los tres perfiles iniciales informan `CAPTURA_Y_PUNTUACION_ACTIVAS`.
- `perfiles_instrumentales`, `perfiles_interpretativos`,
  `contratos_conocimiento` y `lectura_clinica` configurados:0 entradas.

Los ceros de configuración no significan ausencia de todo el motor: los
contratos institucionales se resuelven también mediante código y linaje, y los
perfiles iniciales se resuelven desde el catálogo exacto. Sin embargo,
`ContratosConocimiento::porVersion()` deja explícitamente vacíos los vínculos
instrumentales del contrato cognitivo incorporado; no conecta mapeos TEST.

Las21 reglas cognitivas/Petersen figuran ACTIVO con revisión PENDIENTE.
No se presenta esa revisión pendiente como falta de un aval externo para
desarrollar: el código vigente no excluye una regla solo por PENDIENTE;
comprueba actividad, rechazo, dependencias, condiciones y contrato aplicables.
Las revisiones profesionales posteriores de un resultado son otro proceso.

## Puntos concretos que deben cerrarse

1. **Medición → componente:** implementar solo las conexiones exactas
   compatibles con el método y sus mediciones originales. Conservar abstención
   en los componentes que carezcan de sustento; no inventar baremos ni convertir
   el total de MoCA/RUDAS en cinco áreas.
2. **Resultado conjunto → SQL:** definir y demostrar conservación íntegra de
   datos adquiridos, membresías, exclusiones, conocimiento, configuración,
   salida y trazas. Recuperación exacta y replay son propiedades separadas.
   El DTO actual no acredita ninguna escritura institucional conjunta.
3. **SQL → historial → revisiones:** conectar la misma evaluación conservada
   a ambas revisiones, sin recalcularla al abrir ni modificar la salida al
   registrar desacuerdo. La revisión observacional existente no cierra esto.
4. **Aceptación del recorrido completo:** verificar captura, inferencia,
   guardado, recuperación y revisión desde las vistas autorizadas, con
   aislamiento de residentes, datos faltantes, contradicciones, errores,
   idempotencia y concurrencia en PostgreSQL.

La propuesta del31/10 fue rechazada y además delimitaba conservación del
resultado, sin prometer todo el DTO arbitrario. No se debe instalar ni
anunciarla como solución íntegra por esta solicitud general. El objetivo de
incorporación total debe resolverse preservando la decisión estructural
vigente o mediante una nueva decisión específica sobre un diseño demostrado.
Este informe no solicita de nuevo firmas, estudios o avales externos.

## Archivos que determinan el estado

| Responsabilidad | Archivo |
|---|---|
| Instrumentos activos y selectores | [PerfilesInstrumentalesIniciales](../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/PerfilesInstrumentalesIniciales.php) |
| Captura y puntuación | [CircuitoInstrumentalInicial](../../motor-experto/instrumentos/CircuitoInstrumentalInicial.php) |
| Contratos y vínculos operativos | [ContratosConocimiento](../../app/Modulos/ApoyoDecision/Aplicacion/Servicios/ContratosConocimiento.php) y [configuración](../../config/sistema_experto.php) |
| Adquisición/inferencia cognitiva | [ConsultarConocimientoCognitivo](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarConocimientoCognitivo.php) |
| Petersen | [ConsultarPetersenInstitucional](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarPetersenInstitucional.php) y [evaluador PHP](../../motor-experto/inferencia/EvaluarPetersenInstitucional.php) |
| Preparación conjunta | [PrepararEvaluacionInstitucional](../../app/Modulos/ApoyoDecision/Aplicacion/Consultas/PrepararEvaluacionInstitucional.php) |
| Consulta actual y nueva petición | [ResultadosExpertoResidente](../../app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php) |
| Rutas normales y autorización | [Registro de rutas](../../app/Modulos/ApoyoDecision/Presentacion/Rutas/web.php) y [EvaluacionExpertaPolicy](../../app/Modulos/ApoyoDecision/Presentacion/Policies/EvaluacionExpertaPolicy.php) |

`ResultadosExpertoResidente::hydrate()` retira el estado de un análisis
transitorio cuando comienza otra petición. Es una limitación explícita,
no un mecanismo de conservación. Cambiar su etiqueta a «guardado» o el booleano
`persistido` sin escribir/leer SQL sería una confirmación falsa.

## Verificación de esta revisión

Se generaron inventario de solo lectura y listado de rutas registrados:
`output/estado-integracion-experto-20261104.json` y
`output/estado-integracion-experto-20261104-rutas.json`.
Las rutas del experto para Medicina, Psicología y Enfermería, captura Petersen,
reglas y directorio se registran en la aplicación normal. Un listado de rutas
no acredita su recorrido visual completo ni un despliegue remoto.

La repetición dirigida de lógica e integración se registra en
`output/estado-integracion-experto-20261104-sqlite.xml/.txt`.
**90 pruebas/447 aserciones correctas**, sin fallos, errores ni omisiones en
el filtro ejecutado. Cubre puntuación/captura inicial, consulta cognitiva,
Petersen y su gobierno, y el ciclo observacional conservado. Incluye los cuatro
patrones con entradas sintéticas y los casos de insuficiencia; no convierte
esa prueba lógica en conservación conjunta institucional.

```powershell
php scripts/inventariar-experto.php
php artisan route:list --json
./scripts/probar-sqlite.ps1 -Filtro 'CircuitoInstrumentalInicialTest|EjecucionConsultaCognitivaTest|PetersenInstitucionalTest|CicloObservacionalConservadoTest' -Informe output/estado-integracion-experto-20261104-sqlite.xml -Log output/estado-integracion-experto-20261104-sqlite.txt
```

Las pruebas usan SQLite en memoria y almacenamiento temporal aislado. No se
repitió PostgreSQL ni la revisión visual en este encargo; los informes
anteriores conservan su evidencia fechada. El inventario PostgreSQL actual
es de solo lectura y no se presenta como prueba de inferencia o concurrencia.
Build no repetido: no se modificó código de producto, plantillas o assets.
Se comprobaron18 enlaces locales del informe y el diff documental.

Esta revisión no modifica código funcional, permisos, relaciones, catálogos,
datos clínicos ni configuración secreta; tampoco realiza commit, push o
despliegue. No usa subagentes. No presenta el informe documental como una
implementación nueva ni como culminación del sistema.
