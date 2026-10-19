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
