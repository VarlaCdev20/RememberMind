---
title: "O.R.I.O.N. — Fuentes profesionales, población 60+ y puente instrumental"
status: CURRENT
last_reviewed: 2026-10-19
source_of_truth: false
verification_scope: ENGINEERING_AND_TARGETED_RESEARCH
---

# Fuentes profesionales y aplicación técnica en mayores de 60

Fecha de investigación: 19/10/2026, America/La_Paz. Continúa la [fase 4](FASE_4_RED_Y_REGLAS_2026_10_19.md) y el [manual instrumental investigado](MANUAL_INVESTIGADO_MAYORES_60_2026_10_19.md). Es documentación técnica de evidencia médica; no es una firma profesional ni el manual oficial de un instrumento.

La petición de trabajar con mayores de 60 se implementa como cohorte de **60 años cumplidos o más (60+)**, usando la edad al aplicar la prueba. El rango de la norma de cada método se comprueba por separado. Una aplicación de cuando la persona tenía 59 años no se reclasifica como geriátrica porque hoy tenga 70. La falta de nacimiento o una referencia sin rango no se sustituye por una edad estimada.

## 1. Investigación profesional y decisiones de ingeniería

Se consultaron primero el código y los documentos del proyecto. Después se contrastaron fuentes oficiales y originales, mediante búsquedas dirigidas por autor/institución, población, evaluación multidominio, idioma y edad. No se presenta como revisión sistemática ni como adopción institucional automática.

| Fuente primaria u oficial | Localizador y alcance | Aplicación concreta |
|---|---|---|
| [OMS, Ageing and health, 1/10/2025](https://www.who.int/news-room/fact-sheets/detail/ageing-and-health) | Overview / Ageing explained / Challenges. Usa 60+ en sus indicadores; describe heterogeneidad del envejecimiento | Identificar cohorte 60+; la edad no equivale a deterioro, dependencia o una puntuación clínica |
| [NIA, Assessing Cognitive Impairment in Older Patients](https://www.nia.nih.gov/health/health-care-professionals-information/assessing-cognitive-impairment-older-patients) | Guía oficial para evaluación de adultos mayores; cambios cognitivos, funcionales, antecedentes y seguimiento | Conservar fuente, antecedentes y contexto; los signos observados motivan evaluación y no sustituyen las mediciones |
| [Alzheimer’s Association, DETeCD-ADRD, Atri et al., DOI 10.1002/alz.14333](https://pmc.ncbi.nlm.nih.gov/articles/PMC12173843/) | Recomendaciones de evaluación clínica; publicación electrónica 23/12/2024. No es un baremo de diecinueve tareas | Separar evaluación cognitiva, funcional y etiológica. No convertir una consecuencia declarativa en diagnóstico por cambiar su etiqueta |
| [NIA-AA, Albert et al., 2011, DOI 10.1016/j.jalz.2011.03.008](https://pmc.ncbi.nlm.nih.gov/articles/PMC3312027/) | §2.1, §2.2 y §2.2.4: dominios, independencia, comparación demográfica y límites en edades avanzadas | Conservar cinco dominios; edad/escolaridad/cultura son relevantes; no extrapolar normas a 90+ sin referencia. La orientación de 1–1,5 DE descrita por el documento no es un corte diagnóstico universal |
| [Arango-Lasprilla et al., HVLT-R, 2015, DOI 10.3233/NRE-151286](https://journals.sagepub.com/doi/10.3233/NRE-151286) | Estudio normativo latinoamericano, tablas A2/A13 para Bolivia; comparación de recuerdo total/diferido | Reutilizar el método existente solo para su identidad y entradas; no aplicar normas de recuerdo a reconocimiento ni completar ensayos faltantes |

Consulta: los textos OMS, NIA, el original NIA-AA y el original HVLT-R se pudieron consultar. DETeCD-ADRD tuvo contenido disponible en el buscador y una lectura previa del texto; reintentos posteriores de PMC devolvieron reCAPTCHA. El portal OMS de ICOPE 2.ª edición se identificó, pero la lectura directa falló; por ello no se extraen nuevos parámetros del manual ICOPE en esta entrega. Los bloqueos de lectura no se eludieron.

Las referencias instrumentales adicionales ya investigadas en la fase 3 se conservan en su [capítulo específico](REFERENCIAS_GERIATRICAS_ADICIONALES_2026_10_19.md), con fuente, calidad, muestra, tablas y acceso. No se transfieren sus normas a otro instrumento o país por compartir idioma.

## 2. Qué se aplica al programa

- `AplicabilidadGeriatrica` devuelve edad en la aplicación, pertenencia a 60+, rango de la referencia y causa de incompatibilidad. Es una comprobación de alcance etario; exige además verificar identidad, educación, idioma y condiciones. No emite una interpretación clínica.
- `CapturasInstrumentalesResidente` calcula la edad desde el nacimiento conservado y la fecha original, incluso cuando falta un perfil numérico. Conserva la procedencia de ese cálculo.
- `ExtractorCapturaInstrumental` adjunta esa comprobación a la captura y mantiene íntegros el bruto, el método y la normalización ya implementada.
- `PerfilInstrumentalCognitivo` resume la cobertura de comparaciones para 60+, sin contar dos veces un componente con varias aplicaciones o medidas.
- `ConstructorMemoriaInstrumental` transforma respuestas originales identificadas en hechos del motor. Requiere edad conocida de 60+ para este puente; una exclusión se explica y no borra la aplicación histórica.
- `ConsultarConocimientoCognitivo` une autorización, versión SQL exacta, contrato del servidor, captura original, memoria, reglas y traza. Se conecta a `ConsultarPerfilInstrumental`, ya usado por la pantalla de resultados. No persiste una consulta como si fuera una evaluación firmada.
- `ContratosConocimiento` unifica el contrato usado por gobierno y ejecución; una sucesora hereda su contrato por linaje. No hay selección de predicados desde el formulario.

## 3. Métricas de cobertura 60+

Son indicadores de disponibilidad de datos, no estimaciones del rendimiento clínico del sistema.

| Campo | Definición / denominador |
|---|---|
| `aplicaciones_examinadas` | Aplicaciones accesibles del residente, no posteriores al corte y fuera de los lotes técnicos excluidos por el contrato existente |
| `aplicaciones_60_o_mas` | De las anteriores, aplicaciones con edad original ≥60 |
| `aplicaciones_menores_60` | Edad original conocida <60; siguen en la historia |
| `aplicaciones_edad_desconocida` | No se puede acreditar edad original; no se asigna al grupo de 60+ |
| `componentes_con_comparacion` | Componentes distintos con al menos una medición `NORMALIZADO` en aplicaciones de 60+; varias medidas del mismo componente cuentan una vez |
| `componentes_del_perfil` | Diecinueve componentes del catálogo vigente |
| `porcentaje_cobertura_comparacion` | 100 × componentes con comparación / 19. No es porcentaje de normalidad, exactitud o DCL |

Ejemplo de prueba: dos aplicaciones aportan MEM-S01/MEM-S02; una aplicación a los 59 años aporta EJE-S03 y otra no tiene edad. Resultado: dos aplicaciones 60+, una menor, una desconocida, dos componentes con comparación y **10,53% de cobertura**. No se declara preservación de los otros diecisiete.

Las comprobaciones de aplicabilidad incluyen fronteras 59/60, 79/80, 95/96, edad desconocida y la celda 65 ambigua de la referencia API ya documentada. La compatibilidad etaria no compensa una escolaridad, cultura, idioma o administración incompatibles. Los calculadores de cada método conservan sus validaciones propias y la ausencia de adopción institucional donde corresponda.

## 4. Contrato para conectar la versión real

Este contrato pertenece al servidor. No se acepta un arreglo de hechos, un autor o un baremo desde parámetros de la interfaz.

1. `sistema_experto.version_cognitiva`: PK de la versión real, ACTIVA, con vigencia no posterior al corte y sin retiro. No puede ser una versión de laboratorio.
2. `sistema_experto.contratos_conocimiento[PK]`: relaciones exactas con tipos/modalidades, tipo de regla y contratos de extracción de esa versión o de su raíz.
3. `vinculos_instrumentales`: cada clave es una PK real de `mapeos_variables_fuente`; contiene `cod_instrumento`, `codigo_instrumento`, `version_instrumento`, `cod_pregunta` y `campo`.
4. La fuente declara explícitamente `aplicaciones_instrumento` y sus columnas originales `cod_aplicacion`, `cod_residente`, `fecha_hora` y `cod_personal`. La extracción requiere `SELECTOR_COMPONENTE`, con `clave_selector` igual a la PK de la pregunta. Los campos admitidos por este puente son `valor_logico` y `valor_texto`, comparados con mapeos exactos. No interpreta una nota libre ni transforma puntuaciones numéricas en predicados mediante umbrales implícitos.
5. Instrumento, versión, pregunta, respuesta, residente, aplicador y fecha proceden de las filas originales. Una respuesta ausente produce hecho no disponible; una combinación ambigua se rechaza. Los documentos vinculados se verifican mediante la captura existente.
6. El backend identifica una aplicación como una unidad de soporte compartida. Verifica nuevamente los hechos con el mapeador del motor. No suma condiciones verdaderas como episodios distintos.
7. Las consecuencias se devuelven con reglas, condiciones, fuentes, fecha de corte y versión. Son candidatas declarativas pendientes de revisión; no reemplazan las compuertas ni derivan A/P o Petersen.

El registro operativo queda sin rellenar hasta cotejar los IDs y las preguntas de la versión real. La bibliografía no permite averiguar qué edición se aplicó ni qué pregunta local se respondió. No se copian los IDs ni los literales sintéticos de las pruebas a la configuración operativa. El software informa `SIN_VERSION_COGNITIVA_VINCULADA` o `SIN_VINCULOS_ORIGINALES_DEL_CONOCIMIENTO` cuando corresponde.

La selección es de una revisión exacta, sin seguimiento automático de la última versión. Las relaciones y los contratos técnicos pueden heredarse por linaje; los vínculos a PK de mapeo deben corresponder a la revisión seleccionada. Al publicar una sucesora se deben cotejar y registrar sus nuevas PK en el servidor: un vínculo ancestral no se reescribe por semejanza ni se acepta silenciosamente. Una versión retirada o una referencia inexistente se rechazan.

Inventario local comprobado mediante consultas de solo lectura el 19/10/2026, sin consultar datos identificativos de residentes:

| Versión del catálogo | Estado | Reglas conservadas | Consecuencia para esta conexión |
|---|---|---:|---|
| `MEM-INV-20261008` | INACTIVO | 0 | Investigación, no paquete ejecutable |
| `ORION-V1-20261008` | INACTIVO | 0 | Cinco criterios; relaciones de pertenencia/asociación, sin contrato habilitante ni reglas |
| `SIM-ORION-LOCAL-1` | ACTIVO | 8 | Laboratorio; se excluye de la consulta institucional |

No existe una versión activa no experimental que esta investigación pueda seleccionar legítimamente. Se necesita conservar una versión institucional con sus reglas declaradas y vincular sus preguntas originales; activar el rótulo de la versión vacía no completa esos contenidos. La autoría de creación deberá provenir de Medicina/Psicología en el gestor existente, no de elegir automáticamente una cuenta del sistema. Este inventario no se presenta como un nuevo guardado o una activación clínica.

## 5. Conservación e historia: brecha delimitada

La consulta devuelve `persistido=false`; no modifica resultados históricos. La revisión del esquema y de los escritores existentes identificó estas diferencias:

| Pieza existente | Reutilización segura | Diferencia que impide fingir un guardado completo |
|---|---|---|
| Cabecera, reglas y trazas D-137 | Residente, versión y relaciones de condiciones/soportes | No conservan por sí solos todas las capturas originales, preguntas sin respuesta y el contexto de extracción |
| `hechos_instantanea_experta` / contrato OBS | Escalares, fecha y autor bajo el contrato observacional aprobado | Su escritor no representa el nuevo perfil declarativo completo ni sus componentes y metadatos instrumentales |
| `AlmacenInstantaneas` | Conservación ORION-SNAP-2 para su contrato y clases originales | Recibe otra `MemoriaTrabajo` y contratos experimentales; no se quitan sus guardas para guardar este resultado como clínico |
| `ArchivoEjecucionesExperimentales` | Evidencia privada cifrada del laboratorio | No es archivo institucional; no se usa como depósito JSON de historias reales |

No se añadieron tablas, columnas, catálogos o permisos. Se conserva el inventario de 106 tablas de negocio y la 4FN. La conservación institucional íntegra sigue en fase 6, con la obligación de reutilizar el esquema y presentar un diccionario concreto si aparece un cambio estructural necesario. Un archivo técnico o un hash no reemplazan los originales ni la representación relacional.

## 6. Checklist y trazabilidad

- [x] Investigar fuentes médicas/primarias y registrar el alcance que sustentan.
- [x] Comprobación explícita de cohorte 60+ y rango etario de cada referencia.
- [x] Métrica de cobertura por componente con denominador 19 y edades desconocidas separadas.
- [x] Puente SQL autorizado hacia memoria y reglas con identidad exacta de preguntas.
- [x] Cuenta, Policy, permisos fuente, corte, autor original y exclusión de aplicaciones incompletas.
- [x] Reutilizar el contrato de gobierno en la ejecución; herencia por linaje.
- [x] Evitar promociones de generadores, resultados esperados o normas a datos reales.
- [x] Verificar integración y negativos en SQLite y PostgreSQL desechables, independencia del motor, formato, sintaxis y build.
- [ ] Cotejar y registrar los IDs/ediciones/preguntas de la versión operativa y sus originales. Dependencia de adquisición, no de una firma profesional previa para desarrollar.
- [ ] Cerrar conservación institucional íntegra y su reproducción desde BDD en la fase 6.
- [x] Revisión independiente específica y corrección posterior de los dos P2 de trazabilidad. [Acta, fuentes verificadas y preparación reproducible](CIERRE_TRAZABILIDAD_Y_REPRODUCCION_2026_10_19.md).

## 7. Verificación reproducible

Candidato aislado desde `add60ab3`, incorporando únicamente los archivos propios de este incremento, sin `.env`. El código PHP productivo comprobado coincide por SHA-256 con el del workspace. No incluye los cambios simultáneos de horarios ni del proveedor modular.

| Comprobación | Resultado |
|---|---|
| Selección relacionada, SQLite `:memory:` | **218 pruebas / 6822 aserciones**, sin errores, fallos u omisiones |
| Misma selección, PostgreSQL desechable | **218 pruebas / 6821 aserciones**, sin errores, fallos u omisiones; clúster detenido y eliminado por el script guardado |
| Prueba adicional de literal textual y admisibilidad de Memoria, en cada motor | **1 prueba / 4 aserciones**, PASS en SQLite y PostgreSQL desechable; sin cambiar código productivo |
| Total de casos distintos comprobados por motor | **219**; 6826 aserciones SQLite / 6825 PostgreSQL |
| `php motor-experto/tests/independencia.php` | PASS: cinco casos técnicos sin Laravel/Eloquent |
| `php motor-experto/tests/orion_independencia.php` | PASS: dominio y huellas sin Laravel, Composer o SQL |
| Pint, sintaxis de PHP propio y enlaces locales de las actas | PASS |
| `npm run build` del candidato | PASS, 3,88 s; sin cambios de interfaz en este incremento |

La selección principal se ejecutó con este filtro en ambos motores:

```powershell
$filtro = 'ConocimientoCognitivoInstitucionalTest|AplicabilidadGeriatricaTest|GobiernoReglasExpertasTest|CapturaInstrumentalInstitucionalTest|PerfilInstrumentalInstitucionalTest|PreparacionInstrumentalInvestigadaTest|CapturaInstrumentalFormularioTest|PerfilCognitivoDeclarativoTest|IntegracionRedCognitivaTest|ApplicationBoundaryTest|FolderOrganizationTest|CierreComponentesFaseTresTest'
php vendor/phpunit/phpunit/phpunit --filter $filtro --log-junit storage/app/private/fase4b-verificado-sqlite.xml
./scripts/probar-postgres.ps1 -Filtro $filtro -Informe storage/app/private/fase4b-verificado-pg.xml -Log storage/app/private/fase4b-verificado-pg.log
```

La prueba adicional, incorporada después de descubrir la selección principal, se ejecutó con `--filter test_memoria_usa_literal_textual_original_y_su_admisibilidad_especifica` y el mismo filtro en `probar-postgres.ps1`. En una reproducción con el archivo final, el filtro principal ya incluye sus **219** casos. Los XML y logs originales se conservaron en el almacenamiento privado del candidato; no se incorporan datos de residentes al repositorio.

Las negativas comprueban cuenta inactiva, Familiar, permisos fuente retirados, cruce de residente, corte futuro, aplicaciones anuladas, edad 59/60, NULL, fuente sin columna de autor y respuesta con dos campos originales incompatibles. La salida distingue ausencia de versión, ausencia de vínculos y rechazo de versión inactiva. El recorrido positivo conserva regla, condiciones, aplicación, autor y unidad de soporte; consultar no crea evaluaciones históricas.

Una corrida previa detectó metadatos incompletos en la fixture de fuente. Se completó la declaración explícita de sus columnas y se conservó la validación de procedencia; los resultados de la tabla corresponden a la repetición corregida. No se debilitó esa validación para obtener verde.

La revisión estática del implementador está realizada. Los revisores independientes disponibles terminaron por límite de uso sin cerrar el gate de este incremento; queda pendiente y no se informa PASS independiente. No se modificó UI, no se acredita QA visual ni se usan estas pruebas sintéticas para afirmar sensibilidad, especificidad, exactitud diagnóstica o validación profesional.

**Actualización posterior del 19/10/2026:** se completó la revisión independiente en el siguiente incremento; detectó dos P2 de captura/procedencia, corregidos y reauditados. El [acta de cierre técnico](CIERRE_TRAZABILIDAD_Y_REPRODUCCION_2026_10_19.md) sustituye únicamente ese pendiente, documenta el DTO/release implementado y conserva abiertos la vinculación institucional y el guardado relacional completo. Los resultados históricos de pruebas anteriores no se cambian.
