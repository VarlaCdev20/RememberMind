# Cohorte cognitiva de investigación: seeder y retroalimentación verificable

**Actualización posterior del 22/10:** la propietaria autorizó que los treinta casos aparezcan en la aplicación local. La [integración local](COHORTE_VISIBLE_APLICACION_2026_10_22.md) añade una carga optativa con respaldo y bindings SQL entre peticiones. Los apartados de ejecución siguientes documentan la entrega inicial en bases desechables; no deben interpretarse como prohibición de la carga local específicamente autorizada después.

Fecha de revisión: 22/10/2026. Versión bibliográfica: `INVESTIGACION-COGNITIVA-30-20261022-V1`. Entradas de ingeniería: `COHORTE-INFERENCIA-30-V1`.

## Alcance entregado

La cohorte existente se trasladó de `tests/Support/CohorteInferencia30.php` a `database/seeders/CohorteCognitivaInvestigacionSeeder.php`. Es un seeder optativo, reutilizado por la aceptación de extremo a extremo. No existe una segunda implementación de carga ni un segundo motor.

Los registros generados pertenecen a treinta residentes de entrenamiento identificados como sintéticos. Incluyen admisión formal, contacto, cama, autoría, antecedentes, diagnósticos contextuales, caídas, seguimiento fechado, capturas instrumentales técnicas, respuestas, IADL y juicios originales de ensayo. Se conserva NULL cuando corresponde. No se insertan resultados DCL esperados en las fuentes: el motor calcula el perfil o la abstención.

La última indicación de la propietaria autoriza datos para entrenamiento. No convierte una historia generada en una medición efectivamente realizada. Esta entrega conserva la procedencia explícita y no añade registros inventados al expediente existente de Elena ni atribuye evaluaciones a profesionales reales.

No hay cambios de estructura, FK, permisos institucionales ni reglas. El seeder no se añadió a `DatabaseSeeder`. No se ejecutó carga sobre `remembermind_dev`. Las pruebas cargan SQL en SQLite `:memory:` y en un cluster PostgreSQL desechable con base y usuario propios.

## Investigación: qué se encontró y qué se utiliza

Los artículos normativos fundamentan la identidad, población y cálculo de métodos ya implementados. **Sus muestras no se importaron**. Las puntuaciones del seeder son elecciones de ingeniería para cubrir caminos del programa: no son muestras extraídas, estimaciones de prevalencia ni historiales de participantes de esos estudios.

| Identificador | Publicación primaria verificada | Uso y límite |
|---|---|---|
| PETERSEN-2004 | Petersen RC. *Mild cognitive impairment as a diagnostic entity*. Journal of Internal Medicine 256:183–194. [DOI 10.1111/j.1365-2796.2004.01388.x](https://onlinelibrary.wiley.com/doi/full/10.1111/j.1365-2796.2004.01388.x) | Fundamento conceptual de los cuatro perfiles. No convierte una puntuación aislada en diagnóstico. |
| NIA-AA-2011 | Albert MS et al. *The diagnosis of mild cognitive impairment due to Alzheimer’s disease: Recommendations…*. Alzheimer's & Dementia 7:270–279. [DOI 10.1016/j.jalz.2011.03.008](https://pmc.ncbi.nlm.nih.gov/articles/PMC3312027/) | Cambio, evidencia, independencia relativa y evaluación clínica. No se utiliza para atribuir etiología Alzheimer a los casos. |
| HVLT-2015 | Arango-Lasprilla JC et al. *Hopkins Verbal Learning Test–Revised: Normative data for the Latin American Spanish speaking adult population*. [DOI 10.3233/NRE-151286](https://journals.sagepub.com/doi/10.3233/NRE-151286) | Referencia del método existente de memoria, selección BO/forma 5/español. No se reproducen listas de palabras. |
| BTA-2015 | Rivera D et al. *Brief Test of Attention: Normative data for the Latin American Spanish speaking adult population*. [DOI 10.3233/NRE-151283](https://journals.sagepub.com/doi/10.3233/NRE-151283) | Método existente de atención, formas N/L y contexto boliviano. No se incluyen estímulos de aplicación. |
| TMT-2015 | Arango-Lasprilla JC et al. *Trail Making Test: Normative data for the Latin American Spanish speaking adult population*. [DOI 10.3233/NRE-151284](https://journals.sagepub.com/doi/10.3233/NRE-151284) | Método existente de tiempo en parte B. Sin traslado automático de baremos entre países o escolaridades. |
| BNT-2015 | Olabarrieta-Landa L et al. *Standard form of the Boston Naming Test: Normative data for the Latin American Spanish speaking adult population*. [DOI 10.3233/NRE-151278](https://pubmed.ncbi.nlm.nih.gov/26639925/) | Método existente de denominación; el estudio incluye Bolivia. No se distribuyen láminas. |
| ROCF-2015 | Rivera D et al. *Rey–Osterrieth Complex Figure – copy and immediate recall: Normative data for the Latin American Spanish speaking adult population*. [DOI 10.3233/NRE-151285](https://journals.sagepub.com/doi/10.3233/NRE-151285) | Se utiliza el método de copia existente. No se incorpora la figura protegida ni se equipara copia con todas las capacidades visuoespaciales. |

El alcance normativo detallado, fórmulas y condiciones siguen en `DICCIONARIO_ADQUISICION_23_METODOS_2026_10_19.md` y en los normalizadores. Esta tarea no altera coeficientes, umbrales ni su revisión profesional pendiente. La captura técnica contiene nombres de entradas y valores, no un formulario distribuible de esas pruebas. Acceder a un artículo no concede por sí mismo licencia de aplicación o incorporación de sus instrumentos.

Se revisaron también dos datasets reales mediante sus fuentes oficiales, sin solicitar acceso, aceptar acuerdos ni descargar filas individuales:

| Dataset | Fuente revisada | Decisión para esta entrega |
|---|---|---|
| ADNI | [Data Use Agreement y anexo sobre herramientas de IA](https://adni.loni.usc.edu/wp-content/themes/adni_2023/documents/ADNI_Data_Use_Agreement.pdf) | Uso sujeto a acceso/DUA, restricciones de redistribución y garantías sobre recursos de IA. No importado ni utilizado como si sus participantes fueran residentes de RememberMind. |
| OASIS | [Condiciones oficiales, julio de 2025](https://bpb-us-e2.wpmucdn.com/sites.wustl.edu/dist/6/4383/files/2025/07/Data-Use-Agreement_July2025.pdf) | Solicitud/DUA y condiciones según proyecto. No importado. Una etiqueta de demencia o CDR no completa los nueve juicios originales y soportes exigidos por el contrato local. |

La bibliografía general no identifica qué prueba recibió Elena. Para su inferencia con fuentes reales siguen siendo necesarios sus protocolos e interpretaciones originales, no los datos de otro participante.

## Población y composición

Treinta casos de 61–89 años, con 4, 8 o 12 años de escolaridad. País normativo BO, idioma español y lectoescritura son entradas explícitas del ensayo, no datos deducidos del nombre de una persona. Las condiciones auditivas, visuales, motoras y de administración compatibles son supuestos identificados del caso. No se extrapolan estos perfiles a cualquier persona mayor de 60 años.

| Casos | Propósito | Salida esperada del contrato |
|---|---|---|
| S01–S03 | Memoria alterada y resto esperado | Posible DCL amnésico de dominio único |
| S04–S06 | Memoria y otro dominio alterados | Posible DCL amnésico multidominio |
| S07–S09 | Un dominio no amnésico alterado | Posible DCL no amnésico de dominio único |
| S10–S12 | Varios dominios no amnésicos alterados | Posible DCL no amnésico multidominio |
| S13–S18 | Cinco dominios P | Sin patrón de DCL; C02 negativo |
| S19–S23 | Requisito negativo o impedimento diferencial | Sin patrón o abstención, según causa |
| S24–S28 | Falta de medición, interpretabilidad, contexto, corroboración u oportunidad IADL | Abstención por insuficiencia, conservando los dominios utilizables |
| S29 | Juicios de memoria A y P concurrentes | Contradicción y abstención |
| S30 | C05 no revisa el conjunto ampliado | Memoria sustentada; calidad insuficiente |

Los quince casos con caída y los seis antecedentes contextuales se distribuyen sin utilizarlos como determinantes del perfil. Una caída no acredita DCL. Los juicios originales A/P/I son entradas **sintéticas explícitas**: no se pretende que la normalización numérica genere por sí sola esos juicios clínicos. C02 y el patrón final sí se derivan mediante el motor real.

Las cinco capturas metodológicas ejercitan seis subcomponentes existentes. No se afirma cobertura instrumental completa de los 19 subcomponentes ni validación clínica de los 23 métodos del catálogo.

La matriz nominal completa, con edades, escolaridad, variante, cinco estados esperados y fuentes, está en [matriz-casos.csv](../auditorias/cohorte-investigacion-2026-10-22/matriz-casos.csv). El [manifiesto versionado](../auditorias/cohorte-investigacion-2026-10-22/manifiesto.json) incluye referencias, métodos y resultados esperados externos a las tablas clínicas.

## Carga, atomicidad y repetición

`CohorteCognitivaInvestigacionSeeder::run()` exige entorno `testing`, destino técnico permitido y confirmación del nombre exacto mediante `RM_COHORTE30_DESTINO`. SQLite se admite sólo en memoria. PostgreSQL exige el nombre aleatorio y usuario exclusivo creados por el runner, en loopback. `--force` no desactiva estas comprobaciones.

La carga completa usa una transacción. Si falla una respuesta, se revierten admisiones, fuentes, soportes, auditoría y correlativos. Al detectar una cohorte existente se rechaza la repetición; no se sobrescribe historia ni se generan duplicados. Las cuentas y profesionales son ficticios, con correos `example.invalid`. No se proporcionan credenciales institucionales.

Se comprueba también el reintento tras un rollback con el mismo objeto del seeder: se vacían los selectores e identidades del intento fallido antes de cargar de nuevo.

La carga usa las Actions vigentes de revisión, admisión, expediente, aplicación instrumental y juicio original. No modifica el estado clínico de los paquetes investigados. Los perfiles instrumentales del ensayo se configuran **sólo en memoria del proceso**. Ejecutar exclusivamente `db:seed` no autoriza ni configura automáticamente el proceso web posterior: la reproducción completa proporcionada es la aceptación que carga y evalúa en el mismo proceso.

## Verificación y retroalimentación

`ManifiestoCognitivo30::comparar()` contrasta los treinta identificadores, perfil, estado, C02 y los cinco dominios. Detecta casos desconocidos/repetidos, ejecuciones ausentes, claves omitidas —incluido un resultado NULL esperado— y discrepancias de dominio aun si el código final coincide.

El informe exportado incluye fuentes bibliográficas, resultados calculados, motivos, huellas y discrepancias. La comparación no escribe historia, modifica reglas, ajusta pesos ni crea aprobaciones. Sirve como regresión y cobertura; no es entrenamiento estadístico ni una medida de sensibilidad, especificidad o precisión clínica.

El replay comprobado es del DTO en memoria. La conservación conjunta SQL sigue pendiente y no se declara resuelta por esta cohorte. `persistido=false` permanece intacto; no se habilitan revisiones sobre resultados transitorios.

## Comandos reproducibles

Exportar la definición no consulta bases de datos:

```powershell
php scripts/exportar-manifiesto-cohorte.php docs/auditorias/cohorte-investigacion-2026-10-22
```

La suite SQLite fuerza `testing` y `:memory:` desde `phpunit.xml`. Su aceptación invoca realmente `db:seed --class=Database\Seeders\CohorteCognitivaInvestigacionSeeder`, con confirmación exacta y esquema desechable:

```powershell
$env:RM_COHORTE30_INFORME = Join-Path $PWD 'docs/auditorias/cohorte-investigacion-2026-10-22/sqlite-resultados.json'
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter 'CohorteInferencia30Test|ManifiestoCognitivo30Test|ObservacionesVariadasSinteticasTest' --log-junit docs/auditorias/cohorte-investigacion-2026-10-22/sqlite.junit.xml
```

El runner PostgreSQL crea y elimina su cluster propio; no reinicia ni conecta `remembermind_dev`:

```powershell
$env:RM_COHORTE30_INFORME = Join-Path $PWD 'docs/auditorias/cohorte-investigacion-2026-10-22/postgres-resultados.json'
./scripts/probar-postgres.ps1 -Filtro 'CohorteInferencia30Test|ManifiestoCognitivo30Test|ObservacionesVariadasSinteticasTest' -Informe "$PWD/docs/auditorias/cohorte-investigacion-2026-10-22/postgres.junit.xml" -Log "$PWD/docs/auditorias/cohorte-investigacion-2026-10-22/postgres.log"
php scripts/comparar-cohorte30.php docs/auditorias/cohorte-investigacion-2026-10-22/sqlite-resultados.json docs/auditorias/cohorte-investigacion-2026-10-22/postgres-resultados.json
Remove-Item Env:RM_COHORTE30_INFORME
```

No ejecutar `migrate:fresh` ni este seeder contra la conexión ordinaria del aplicativo. Las pruebas de rechazo comprueban el bloqueo antes de cualquier escritura clínica.

## Archivos e integración

| Requisito | Archivo | Evidencia |
|---|---|---|
| Carga reutilizable, procedencia y guardas | `database/seeders/CohorteCognitivaInvestigacionSeeder.php` | Entrada Artisan en aceptación, rechazo de destino, duplicación y rollback |
| Referencias y oráculo separado | `database/seeders/Datos/ManifiestoCognitivo30.php` | Metadatos versionados y siete publicaciones primarias |
| Inferencia desde SQL y ambas ramas | `tests/Feature/CohorteInferencia30Test.php` | Treinta casos, cuatro perfiles, abstenciones, fuente ajena, Livewire, consulta sin escrituras |
| Retroalimentación adversa | `tests/Unit/ManifiestoCognitivo30Test.php` | Alteración de dominio, salida ausente, duplicación y caso omitido detectados |
| Matriz portable | `scripts/exportar-manifiesto-cohorte.php` | CSV y manifiesto sin acceso a BDD |
| Paridad entre motores | `scripts/comparar-cohorte30.php` | Mismas cantidades, estados, motivos y manifiesto; huellas verificadas por ejecución |

Build y QA visual no aplican a esta modificación: no cambia Blade, CSS ni JavaScript. Las vistas reales se comprueban mediante Livewire dentro de la aceptación; eso no sustituye una inspección visual. No se hicieron commits, push ni despliegues.

## Estado final y límites

Los resultados de esta ejecución se registran en el [acta de evidencias](../auditorias/cohorte-investigacion-2026-10-22/README.md). Implementación y aceptación técnica deben leerse por separado de validación clínica. No hay casos profesionales revisados ni firmas nuevas. La cohorte no valida el comportamiento en una población real, no garantiza ausencia de errores y no autoriza decisiones asistenciales automáticas.
