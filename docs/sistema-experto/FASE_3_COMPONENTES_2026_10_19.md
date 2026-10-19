# Fase 3 — Objetivo activo: completar los 19 componentes

Solicitud de la propietaria, 19/10/2026: verificar los ocho componentes con
cálculo, resolver los once restantes con métodos y referencias aplicables y
definir su interpretación. Criterio de cierre: cada componente comprometido
tiene cálculo documentado y pruebas independientes. Este archivo conserva
el objetivo y su seguimiento para continuar el trabajo entre sesiones.

El resultado final perseguido es O.R.I.O.N. integrado: mediciones originales,
cinco dominios, reglas versionadas, patrón candidato o abstención sustentada,
informe comprensible, conservación exacta y revisiones independientes de
Medicina y Psicología. Esta fase se limita a los contratos instrumentales;
no activa Petersen clínico ni sustituye las fases de integración y conservación.

## Fuente de verdad y corte inicial

- Autoridad: solicitud actual y fase 3 del
  [plan integral](PLAN_CIERRE_INTEGRAL_2026_10_19.md).
- Base documental: [métodos V2](METODOS_Y_CAMPOS_INVESTIGADOS_V2_2026_10_18.md)
  y [matriz de consolidación](CONSOLIDACION_PUNTO_1_2026_10_19.md).
- Motor: `RegistroMetodosInstrumentales`, `NormalizadorHvltBolivia2015`,
  `NormalizadorBateriaBolivia2015`, `CatalogoCapturaCognitiva` y
  `PerfilInstrumentalCognitivo`, bajo `motor-experto`.
- Pruebas iniciales: `NormalizacionInstrumentalInvestigadaTest`,
  `NormalizacionBateriaBolivia2015Test`, `PerfilInstrumentalInstitucionalTest`.
- Base: siete perfiles, once medidas normativas, ocho componentes.
- Pendientes: MEM-S03/S04, ATE-S01/S02, EJE-S03/S04/S05,
  LEN-S03/S04/S05 y VIS-S01.
- Estado: EN CURSO. No hay aprobación clínica integral ni fecha de cierre
  atribuida por este registro. Se preservan fuera del alcance los cambios
  concurrentes de adquisición/fase 2, Residentes, Valoraciones, Institución,
  provider, permisos/migraciones y `work/`.

## Invariantes de aceptación

1. Identidad exacta de instrumento, edición, forma, idioma y método.
2. Fuente original verificable, población y límites documentados.
3. Datos originales preservados; faltante, cero y falso diferenciados.
4. Ejemplos de cálculo independientes de la implementación.
5. Normas incompatibles, interrupciones y contradicciones no producen normalidad.
6. No copiar reactivos ni tablas de manuales sin derechos de uso.
7. Sin migraciones, cambios de permisos ni datos de residentes.
8. Una medida normalizada no es un diagnóstico ni una clasificación automática A/P.
9. Un método no aplicable o solo bruto no se cuenta como normalizador completado.

## Seguimiento

- [x] Objetivo y criterio de cierre guardados en el repositorio.
- [x] Inventario inicial y consumidores localizados.
- [x] Ocho componentes existentes contrastados con las fuentes y ejemplos independientes.
- [ ] Once componentes pendientes resueltos con método aplicable.
- [x] Alcance interpretativo de cada componente documentado con límites explícitos.
- [ ] Pruebas y evidencia final consolidadas.

## Investigación profesional entregada

[Informe de los 19 componentes](INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md):
para qué sirve cada uno, uso conceptual, motivo de selección, operación,
interpretación, población, fuentes y requisitos concretos pendientes.
La petición posterior de la propietaria autoriza investigar y documentar lo
faltante; no aporta manuales ni convierte una población extranjera en compatible.

Se añade reconocimiento HVLT-R como cálculo descriptivo, no como noveno
componente normalizado. El registro tiene siete perfiles normativos y un
complemento descriptivo; conserva once medidas normativas en ocho componentes.
Los once componentes pendientes de normalización no se marcan completados.

## Verificación del incremento

Ejecutado el 19/10/2026:

```powershell
php artisan test --compact --filter='InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest' --colors=never
```

PASS: **83 pruebas / 845 aserciones**. Los casos nuevos contienen diez z
calculados con aritmética decimal independiente y un intervalo Stroop; cubren
los ocho componentes, la discrepancia BTA, tipos, signo, dirección, cero/faltante,
reconocimiento negativo y conservación sin resultado clínico.

```powershell
php artisan test --compact --filter='InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest' --colors=never
```

PASS SQLite: **110 pruebas / 1216 aserciones**. Incluye integración con captura,
preparación y restricciones de acceso existentes. Durante el trabajo concurrente
hubo una primera ejecución con una fixture sin `cod_aplicacion`; el trabajo de
adquisición corrigió esa fixture y la ejecución posterior pasó sin relajar el
extractor. Ese arreglo no forma parte de los archivos propios de esta entrega.

```powershell
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest'
```

PASS PostgreSQL desechable: **110 pruebas / 1216 aserciones**. El script creó y
detuvo su propio clúster temporal; no usó la base operativa. Evidencia local:
`remembermind-pg-173156d88a4840d09408a6702add20cc.junit.xml` y archivo `.log` en
el directorio temporal del sistema.

Pint y `php -l`: PASS para los seis archivos PHP propios. Revisión de cambios y
espacios: PASS. Sin referencias ejecutables V1, operaciones de persistencia,
dependencias nuevas o cortes clínicos en los dos componentes nuevos. La revisión
técnica propia no se presenta como gate experto independiente. No existe
validación clínica ni dictamen independiente de un profesional aportado por estas
pruebas. No se modifica frontend; build y QA visual no aplican a este incremento.

## Archivos propios del incremento

- `motor-experto/src/motor_experto/dominio/instrumentos/CalculadorReconocimientoHvlt.php` (nuevo).
- `motor-experto/src/motor_experto/dominio/instrumentos/ExplicacionMatematicaInstrumental.php` (nuevo).
- `motor-experto/src/motor_experto/dominio/instrumentos/RegistroMetodosInstrumentales.php`.
- `motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorHvltBolivia2015.php`.
- `motor-experto/src/motor_experto/dominio/instrumentos/CatalogoCapturaCognitiva.php`.
- `tests/Unit/InterpretacionInstrumentalFaseTresTest.php` (nuevo).
- `docs/sistema-experto/INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md` (nuevo).
- Este seguimiento, el índice `README.md` del experto y el plan integral.

Sin cambios propios en BDD, permisos, historias de residentes o reglas expertas.
No se han ejecutado motores clínicos sobre residentes ni generado mediciones.

## Ampliación ante la solicitud de cierre

La propietaria solicitó resolver las brechas con fuentes médicas válidas y cerrar
la fase. Se amplió la investigación con la guía DETeCD-ADRD, NICE NG97, ITC y
fuentes originales NEURONORMA, UAB y WAB castellana. El
[informe actualizado](INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md#ampliación-solicitada-fuentes-médicas-y-neuropsicológicas-para-el-cierre)
identifica qué resuelve cada fuente y qué no acredita.

Implementación adicional: `CalculadorLenguajeWabCastellano2000`, repetición y habla
espontánea conforme a la tabla original de la edición castellana 2000, página
impresa 18. Conversión de repetición por diez; suma de contenido y fluidez. Se
reutilizan registro, extractor y perfil; no se intercambian WAB y WAB-R ni se
introducen cocientes de afasia o diagnósticos. La explicación descriptiva ahora
identifica la operación de cada medida; no describe lenguaje como reconocimiento.

Cobertura actual: **siete perfiles normativos más dos descriptivos**. Ocho
componentes con once medidas normativas; tres componentes adicionales con
aritmética descriptiva. Continúan once componentes sin normalización aplicable
verificada. No se cambia el criterio de aceptación para convertir un complemento
bruto en normalizador completado.

Verificación adicional SQLite:

```powershell
php artisan test --compact --filter='LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest' --colors=never
```

PASS: **151 pruebas / 1498 aserciones** en el árbol compartido del momento.
Incluye 28 casos nuevos WAB: aritmética independiente, límites, NULL/cero,
tipos, ediciones incompatibles, condiciones ausentes, parcialidad, extracción
tipada, fuentes compartidas y cobertura que no se infla a diecinueve.
Pint y sintaxis de los archivos nuevos: PASS.

Mismo filtro en PostgreSQL desechable mediante `scripts/probar-postgres.ps1`:
**151 pruebas / 1497 aserciones, PASS**. Se conserva el conteo propio de cada
motor; no se oculta su diferencia de una aserción. Evidencia local:
`remembermind-pg-dcd9d136e3524b0bbb1d5ce142f67fbb.junit.xml` y `.log`, en el
directorio temporal. El clúster propio se detuvo y eliminó al finalizar.
Build/QA visual no aplican: no hay cambios propios en Presentation o frontend.

Archivos adicionales propios: calculador WAB y su prueba
`tests/Unit/LenguajeWabCastellanoInvestigadoTest.php`, registro, explicación,
catálogo y los dos documentos de esta fase. Se conservan los demás cambios
concurrentes fuera del incremento.

**Dictamen:** investigación ampliada y aritmética verificable entregadas;
cierre funcional de la fase 3 todavía no acreditado. No existe una guía médica
que por sí sola proporcione las once conversiones ausentes. Tampoco se
presenta el dictamen técnico propio como validación clínica independiente.

## Ampliación: manuales investigados para mayores de 60

La propietaria confirmó que las fuentes deben obtenerse mediante investigación
profunda en internet, para población **mayor de 60 años**, y pidió documentarlo
todo. Se entrega el [manual técnico geriátrico](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md),
con nueve grupos de fuentes, acceso efectivo, localizadores, edades, escolaridad,
versiones, fórmulas candidatas, ejemplos independientes y huellas de cinco
originales. Incluye el acceso comercial legítimo a los manuales WAIS identificados;
no afirma que se hayan consultado íntegros.

Hallazgos que cambian el siguiente trabajo: SABIEX publica métodos de longitud
inversa, letras/números y JLO, con límites de selección de la muestra; pFCSRT+IR
2025 publica una varianza residual dependiente del predicho. Se documentan
contradicciones en la autocorrección del Token chileno y entre la tabla de
FCSRT-Del y la celda C16 de la calculadora pública de SABIEX. No se transfieren
normas entre palabras e imágenes ni se copia una fórmula conflictiva a producción.

Implementado `FCSRT-PALABRAS-NEURONORMA-BRUTO-1`, únicamente descriptivo:
adicionales con claves, total de aprendizaje y total diferido, sin duplicar
aciertos libres. Integra registro, extractor, perfil y explicación ya existentes.
Catálogo `CAPTURA-COGNITIVA-5-20261019`; el enlace WAIS sustituye la antigua ruta
que actualmente devuelve portada. No modifica configuración institucional,
permisos, estructura, reglas ni datos. No ejecuta Petersen o G09.

Cobertura actual: **siete perfiles normativos y tres descriptivos**. Ocho
componentes normativos con once medidas; cuatro componentes adicionales con
cálculo descriptivo. Continúan once normalizaciones sin método aplicable adoptado
y verificado. El compromiso de los diecinueve permanece abierto.

Verificación del incremento:

```powershell
php artisan test --compact --filter='RecuerdoConClavesFcsrtInvestigadoTest|LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest' --colors=never
```

PASS: **94 pruebas / 507 aserciones**.

```powershell
php artisan test --compact --filter='RecuerdoConClavesFcsrtInvestigadoTest|LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest' --colors=never
```

PASS SQLite: **178 pruebas / 1624 aserciones**, duración 66.61 s. Veintisiete
casos FCSRT nuevos cubren aritmética independiente, cero/ausencia, tipos, pares
contradictorios, condiciones, versiones, demora y procedencia hasta el perfil;
este conserva ausencia de normalización y de resultado clínico.

```powershell
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'RecuerdoConClavesFcsrtInvestigadoTest|LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest'
```

PASS PostgreSQL desechable: **178 pruebas / 1623 aserciones**, duración 84.186 s.
Evidencia local: `remembermind-pg-604fb7dfaab04da79d28cb9a42dd30e9.junit.xml` y
`.log`, en el directorio temporal. El script detuvo y eliminó su clúster; no usó
la base operativa. Se conserva la diferencia de una aserción entre motores.

Se mantiene la separación entre pruebas puras de FCSRT y pruebas de integración
existentes de captura/preparación y acceso. No se afirma que se haya configurado
o administrado una nueva prueba en residentes. Sintaxis y Pint: PASS; build/QA
visual no aplican al no cambiar Presentation/frontend. La revisión técnica propia
no sustituye un gate experto independiente o validación clínica.

Auditoría documental: PASS, diecinueve componentes únicos, nueve grupos de fuentes
y cincuenta enlaces locales existentes en los cuatro documentos revisados.
Se verificaron huellas, fórmulas XML y las dos páginas PDF críticas; las fuentes
de acceso parcial se identifican como tales. Revisión de diff y espacios: PASS.

Archivos propios: `CalculadorRecuerdoConClavesFcsrt.php` y
`RecuerdoConClavesFcsrtInvestigadoTest.php` nuevos; registro, catálogo y explicación
instrumentales; manual nuevo, informe de investigación, índice y este seguimiento.
Los cambios concurrentes de Valoraciones, Institución, provider, fase 2 y `work/`
se mantienen fuera del incremento.

## Normalizaciones implementadas tras la solicitud expresa

Solicitud posterior: «realiza las normalizaciones». Se implementan cinco
ecuaciones de referencia: dígitos inversos, letras/números, JLO, FCSRT de
palabras/aprendizaje total y pFCSRT+IR de imágenes/recuerdo libre. Se añade un
selector conjunto para las dos tareas WAIS-III, sin sumar sus puntuaciones ni
necesitar dos perfiles para una misma aplicación. El
[contrato completo](NORMALIZACIONES_REFERENCIA_2026_10_19.md) documenta fórmulas,
entradas, condiciones y límites.

La cobertura pasa a **once componentes con algoritmo normativo programado**,
incluyendo las referencias españolas; no se afirma que las once referencias
estén adoptadas o validadas para residentes bolivianos. Continúan ocho sin
conversión completa: MEM-S04, ATE-S01/S02, EJE-S04/S05 y LEN-S03/S04/S05.
El método conflictivo FCSRT-Del no se registra. Las nuevas referencias conservan
identidad y contexto explícitos, no se seleccionan automáticamente para residentes.

Hay siete perfiles de la base previa, seis selectores de referencia (cinco
ecuaciones) y tres familias descriptivas. Catálogo versión 6, con clave separada
`normalizadores_referencia_disponibles`. Los ejemplos calculan z sin redondeo
previo; el modelo de imágenes usa su varianza dependiente del predicho.

Prueba específica:

```powershell
php artisan test --compact --filter='NormalizacionReferenciasEspanaTest|referencias_espanolas_se_integran_desde_sql' --colors=never
```

PASS: **32 pruebas / 898 aserciones**. Incluye las cinco ecuaciones, contexto
común y validez individual del perfil conjunto, tipado, fuentes, estado técnico,
ausencia de clasificación y una prueba SQL de seis aplicaciones sintéticas.

Suite relacionada:

```powershell
php artisan test --compact --filter='NormalizacionReferenciasEspanaTest|RecuerdoConClavesFcsrtInvestigadoTest|LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest' --colors=never
```

PASS SQLite: **210 pruebas / 2522 aserciones**, 68.87 s.

```powershell
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'NormalizacionReferenciasEspanaTest|RecuerdoConClavesFcsrtInvestigadoTest|LenguajeWabCastellanoInvestigadoTest|InterpretacionInstrumentalFaseTresTest|NormalizacionInstrumentalInvestigadaTest|NormalizacionBateriaBolivia2015Test|PerfilInstrumentalInstitucionalTest|CapturaInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest'
```

PASS PostgreSQL desechable: **210 pruebas / 2521 aserciones**, 91.205 s.
Evidencia temporal `remembermind-pg-6f41560d8f6e4844948090986e19cba7.junit.xml`
y `.log`; clúster propio detenido y eliminado. No se ejecutó contra la base
operativa. Pint, sintaxis y revisión de espacios: PASS. Los resultados de pruebas
no sustituyen revisión experta independiente ni validación clínica.

Integración: se reutilizan registro, extractor, captura autorizada SQL, perfil y
explicación. No hay nuevas tablas, permisos, reglas, datos reales o cambios de
configuración institucional. No se ejecuta Petersen/G09 sobre residentes. Sin
cambio frontend: build/QA visual no aplican.

Archivos propios de este incremento:

- `motor-experto/src/motor_experto/dominio/instrumentos/NormalizadorReferenciasEspana.php` (nuevo).
- `motor-experto/src/motor_experto/dominio/instrumentos/RegistroMetodosInstrumentales.php`.
- `motor-experto/src/motor_experto/dominio/instrumentos/CatalogoCapturaCognitiva.php`.
- `tests/Unit/NormalizacionReferenciasEspanaTest.php` (nuevo).
- `tests/Feature/CapturaInstrumentalInstitucionalTest.php`.
- `docs/sistema-experto/NORMALIZACIONES_REFERENCIA_2026_10_19.md` (nuevo).
- `docs/sistema-experto/MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md`.
- `docs/sistema-experto/INVESTIGACION_PROFESIONAL_19_COMPONENTES_2026_10_19.md`.
- `docs/sistema-experto/README.md`.
- Este seguimiento.

Se excluyen cambios concurrentes de reglas, compiladores, motor experimental,
Institución, provider y sus pruebas. Dictamen: normalizaciones documentadas e
implementadas dentro de su referencia; **fase 3 aún no cerrada** por los ocho
algoritmos pendientes y la pertinencia institucional que falta acreditar.

Auditoría final documental: PASS, cincuenta y siete enlaces locales existentes
en los cinco documentos de este incremento. Revisión propia del código: sin
dependencias V1, consultas SQL ni llamadas a clasificación en el nuevo normalizador.
