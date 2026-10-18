# Valoración operativa sin simulaciones

Instrucción de la propietaria del 17/10/2026: retirar las simulaciones de la pantalla habitual del sistema experto. Esta decisión sustituye la integración visual SIM descrita en `SIMULACION_LOCAL_2026_10_17.md`.

## Comportamiento

**Cabecera común posterior:** por solicitud de la propietaria se utiliza
«Evaluación neurocognitiva» para todos los residentes y roles consumidores
de la vista compartida. Se retiran la franja de consulta de Enfermería y la
banda de procedencia de la cabecera. La procedencia de demostración permanece
como nota al pie, sin cambiar resultados ni permisos. Pruebas de memoria,
ausencia y ruta local: 5 PASS / 86 aserciones; Pint y render local PASS.

**Excepción posterior autorizada para verificación visual:** la propietaria solicitó
mostrar la valoración y el fundamento de Elena en la ruta habitual y retirar
la palabra «ficticia» del texto explicativo. Si el laboratorio está habilitado
en local/testing, no existe evaluación operativa y el lector de simulación
recupera una instantánea del residente autorizado, la pantalla muestra esa
instantánea por defecto, sin necesitar parámetros. Se conserva un único aviso
visible de procedencia. Por solicitud posterior de retirar «Simulación», la
etiqueta es «Entorno de pruebas · Resultado de demostración · Sin uso
asistencial». La advertencia original se conserva y verifica en el contrato
interno; no se cambia el resultado ni se convierte en diagnóstico clínico.
La regresión de ruta y los tests de presentación pasan: 13 pruebas / 116
aserciones en SQLite para este ajuste de texto.
Los parámetros antiguos `?simulacion=1` y `?simulacion=0` ya no seleccionan
modos: ambas direcciones muestran el resultado disponible. No hay botón
«Salir de la vista previa». Una evaluación operativa existente conserva
prioridad; el historial conserva la selección exacta y el alcance autorizado.
No cambia el alcance del lector operativo ni incorpora SIM a la ficha clínica.
Solo funciona con la capacidad local ya habilitada, la base autorizada y la
cohorte ficticia verificada por `LectorSimulacionLocal`. Producción permanece
cerrada. Se consulta la instantánea íntegra; no se ejecuta inferencia ni se
crean datos. La Policy, asignación y cuenta se revalidan en cada interacción.
No admite firmas profesionales ni sustituye referencias artificiales por fuentes
operativas. Una instantánea corrupta retira la narrativa completa.

Elena: `/admin/enfermeria/pacientes/COD_RES_000028/resultados-experto`.
La evaluación conservada presenta el patrón amnésico de dominio único del caso
ficticio, fecha, solicitante y estado «Resultado conservado». El texto principal
indica «Se identifican dificultades en memoria» y «patrón candidato de DCL
amnésico de dominio único». «¿Por qué?» conserva fundamentos por función,
autonomía cotidiana y revisión diferencial, junto con los cinco requisitos
considerados, su estado, fecha de corte y soportes. No se cambian los objetos de
resultado, requisitos ni referencias de la instantánea.

Cambios acotados a `ResultadosExpertoResidente.php`,
`PresentarInformeCognitivo.php`, `PresentarPatronExperimental.php`, la vista
principal y su parcial `informe-narrativo.blade.php`, regresiones en
`SimulacionLocalExpertoTest.php` y `PresentacionNeurocognitivaTest.php`, y este
documento. Sin cambios de BDD, datos,
permisos, reglas, umbrales ni Git. Build y Pint PASS. Verificación final sobre
el comportamiento sin selector: 11 pruebas / 105 aserciones PASS en SQLite y
PostgreSQL desechable (lectura, casos guardados, historial, autorización,
capacidad revocada, producción, integridad y error seguro/reintento).
Presentación y reintento: 13 pruebas / 98 aserciones PASS en SQLite.
Las corridas previas detectaron una lectura SQL fuera del manejo seguro del
render y una expectativa de selector obsoleta; la integración final conserva
la lectura protegida y el estado de revisión derivado en Application. No se
cambiaron Policies. QA del Blade exportado de Elena: diez
combinaciones (1440, 1280, 1024, 768 y 390 px; claro/oscuro), cinco áreas,
resultado, fundamento y aviso único, sin desbordamiento.
Capturas/JSON locales en TEMP `remembermind-preview-elena`. La revisión usa
HTML real exportado y CSS compilado; no acredita interacción con la sesión
del navegador del usuario.

La corrida ampliada anterior de informe, narrativa, revisiones y simulación terminó con
86 pruebas PASS y esa misma expectativa 403 obsoleta (513 aserciones). Había
arrancado antes de corregir el test; la repetición posterior de los seis casos
de vista previa pasó íntegra en ambos motores. No se repitió toda la corrida
ampliada después de la corrección exclusiva de la expectativa.

- La pantalla se titula «O.R.I.O.N. — Resultado de la evaluación neurocognitiva». Consulta resultados operativos y, en el laboratorio local autorizado, la instantánea conservada cuando no hay evaluación operativa.
- El lector operativo sigue excluyendo pruebas, simulaciones y experimentos. La excepción local utiliza exclusivamente `LectorSimulacionLocal`, verifica la instantánea y mantiene su advertencia de origen.
- Las fuentes y sus cantidades excluyen los lotes `SIM-SE`, `COR-SIM-SE`, `SIM-IADL` y el instrumento reservado de QA `INST_COMPLETO/TECNICA_1`. Una observación NULL conserva su registro ordinario.
- Se conservan las evaluaciones y antecedentes de laboratorio en la base. El lector explícito `LectorSimulacionLocal` permite verificar las instantáneas fuera de la pantalla operativa. No se renombran datos ficticios ni se convierten en valoraciones reales.
- Psicología y Medicina mantienen sus revisiones separadas para las valoraciones habilitadas. Consultar la pantalla no ejecuta inferencia ni guarda resultados.

## Estado local

La base de desarrollo contiene residentes ficticios, según confirmación de la propietaria. El lector operativo de Gloria (`COD_RES_000010`), Mercedes (`COD_RES_000030`) y Elena (`COD_RES_000028`) conserva su alcance sin SIM. La pantalla, con el laboratorio habilitado, puede mostrar las instantáneas por la excepción posterior. El render actual de Elena muestra resultado, fundamento, requisitos y un solo aviso de simulación; no imprime códigos PET ni RAW en la lectura principal. Se comprobó ausencia de escrituras SQL durante la consulta.

El contrato `sistema_experto.lectura_clinica` continúa vacío. La limpieza de la pantalla no acredita instrumentos, baremos, aprobación clínica ni una clasificación DCL operativa. El conocimiento y los soportes pendientes están concretados en `MATRIZ_INTERPRETACION_OPERATIVA_PARA_REVISION.md`.

## Verificación

Regresiones en `InformeCognitivoTest` y `SimulacionLocalExpertoTest`: flags activados, ID manual, historial mixto, fuentes de QA, lotes conocidos, NULL y conservación de filas. Se mantiene la comprobación de los resultados autorizados, revisión profesional y límites de acceso.

| Verificación | Resultado |
|---|---|
| Informe, laboratorio aislado, revisión profesional, resumen y G09 en SQLite | 88 pruebas / 461 aserciones PASS |
| Mismo conjunto en PostgreSQL desechable | 88 pruebas / 461 aserciones PASS |
| Comprobación final de lotes, instrumento QA e historial mixto, en cada motor | 3 pruebas / 15 aserciones PASS |
| Build Vite | PASS |
| Render local de los tres expedientes y ausencia de escrituras SQL | PASS |
| Revisión independiente del aislamiento | Sin P1/P2 pendientes |

Evidencia JUnit y logs en TEMP: `valoracion-final.sqlite.*`, `valoracion-operativa.pg.*`, `valoracion-ultimos.sqlite.*`, `valoracion-ultimos.pg.*`; build en `valoracion-operativa.build.log`. Los runners PostgreSQL detuvieron y retiraron sus propios clusters desechables.

La comprobación de Blade es funcional, sin inspección visual de navegador: su uso está deshabilitado en esta sesión. No se modifican estructura de BDD, permisos, reglas clínicas ni datos locales.

Integración: lector, presentador, fuentes y Blade de la ruta existente. Seguridad: Policies y reautorización conservadas; selección directa de pruebas denegada. Legacy: sin nuevas dependencias V1 en este cambio. Git: sin commit ni push, conforme a la restricción expresa de los reglamentos maestros y preservando los cambios concurrentes. Pendientes: QA visual y conocimiento clínico operativo, según la matriz citada.
