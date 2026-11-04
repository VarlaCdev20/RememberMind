# Fuentes y criterio de construcción

Consulta realizada el 20 de octubre de 2026. No se descargaron registros clínicos identificables ni se atribuyeron las historias a hospitales o profesionales reales.

## Contrato del proyecto

Fuentes vigentes para esta rama: [baseline congelado](../../../docs/base-de-datos/REMEMBERMIND_BDD_BASELINE_CONGELADO.md), [diccionario físico](../../../docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md), [extensión de objetivos de signos](../../../docs/base-de-datos/DECISION_OBJETIVOS_SIGNOS_VITALES_V2_2.md), `database/AGENTS.md`, acción `FormalizarAdmision`, Models y migraciones de `origin/develop` en `155a1516`. `Historical2026Seeder` se consultó como antecedente técnico de mapeos, sin ejecutarlo ni modificarlo.

La base instalada puede contener extensiones posteriores a este corte de `develop`. La cohorte usa las columnas del contrato disponible en la rama y no intenta retirar, migrar o rellenar esas extensiones. No se equipara el inventario documental del núcleo con el inventario físico instalado. El conocimiento y la persistencia del experto quedan fuera de la carga.

## Investigación externa

| Fuente primaria | Qué aporta | Aplicación y límite |
|---|---|---|
| [GAMLP: POA 2024, descripción de macrodistritos](https://sim.lapaz.bo/poa/2024/index.html) | Nombres de zonas de Centro y Sur, y organización municipal | Zonas reales. No aporta domicilios particulares ni distribuciones de pacientes |
| [GAMLP: mapa oficial de límites](https://sitservicios.lapaz.bo/geoestadistico2023/images/cuadro/limites-macrodistritales-distritales.pdf) | Macrodistritos urbanos y rurales | Se usan zonas del municipio de La Paz, sin confundirlo con El Alto |
| [GAMLP: contexto territorial de Max Paredes](https://sitservicios.lapaz.bo/cartillas/otraspublicaciones/Pandillas_19_1.pdf) | Villa Victoria, Munaypata y La Portada | Solo geografía; no se toman datos personales ni sociales del estudio |
| [Ministerio de Salud: HEARTS, 17/05/2025](https://minsalud.gob.bo/8633-seis-departamentos-en-bolivia-implementan-la-iniciativa-hearts-para-mejorar-la-prevencion-y-tratamiento-de-la-hipertension) | Contexto boliviano de seguimiento de hipertensión | Justifica incluir historia y seguimiento cardiovascular, sin copiar prevalencias ni imponer una pauta |
| [Ministerio de Salud: adultos mayores, 25/08/2026](https://minsalud.gob.bo/9405-prevencion-de-hipertension-y-diabetes-en-adultos-mayores) | Hipertensión, diabetes y necesidades de seguimiento en personas mayores | Diversidad de antecedentes; no se interpreta frecuencia de consultas como prevalencia poblacional |
| [OMS: envejecimiento y salud](https://www.who.int/news-room/fact-sheets/detail/ageing-and-health) | Heterogeneidad funcional y coexistencia de condiciones en la vejez | Diferencias entre autonomía, movilidad, estado nutricional, sueño y apoyos |
| [OMS: demencia](https://www.who.int/news-room/fact-sheets/detail/dementia) | Relación del contexto cognitivo con edad, salud vascular, sentidos y cuidados | Se generan observaciones y antecedentes; no se precargan diagnósticos objetivo ni resultados del motor |
| [Investigación original en residentes de altura de La Paz](https://pmc.ncbi.nlm.nih.gov/articles/PMC7542971/) | Contexto de SpO2 a 3.700–4.000 metros | Justifica registrar residencia habitual y aire ambiente. La muestra es de adultos jóvenes: sus cifras no se convierten en rangos geriátricos ni umbrales institucionales |
| [AGEMED: actualización LINAME, 07/09/2026](https://www.agemed.gob.bo/circulares/2026/Actualizacion%20LINAME%20PRECIOS%20al%207-09-2026.xlsx) | Presentaciones del catálogo existente investigado del proyecto | Se reutiliza `MedicamentosInvestigadosSeeder`; una presentación no determina dosis, indicación ni existencia histórica de una marca |

No se copian porcentajes epidemiológicos al generador. El tamaño, el reparto de perfiles, las fechas y las variaciones deterministas son decisiones de construcción de una cohorte sintética para comprobar el sistema. Las referencias posteriores a un hecho de 2025 se usan para documentar el contexto en la fecha de generación, sin atribuirlas retroactivamente al profesional del caso.

La hoja oficial de AGEMED se descargó y contrastó en esta ejecución: SHA-256 `cb428b190445b492cdd128686f5eab49455ec47ec5f4be47b4410677f285327c`. Se confirmaron las presentaciones C0902 (losartán 50 mg), A1007 (metformina 500 mg), H0303 (levotiroxina 0,05 mg) y N0208 (paracetamol 500 mg). El original consultado queda fuera de Git; no se incorporan precios a las historias. La comprobación acredita la presentación en la lista consultada, no la validez de una prescripción para un paciente real ni la vigencia retroactiva de esa edición.

## Coherencia aplicada

Cada residente conserva una identidad y admisión únicas, un contacto responsable, cama propia y registros posteriores al ingreso. Los padecimientos conocidos tienen autor médico; la observación de Enfermería no se convierte en diagnóstico cognitivo. Una prescripción, sus horarios y administraciones corresponden al mismo residente. Cada respuesta instrumental pertenece a una pregunta/opción del instrumento aplicado. Los resultados de estudio corresponden al tipo y componente correctos.

Las trayectorias describen cambios con continuidad: apoyo para marcha y seguimiento funcional, reducción y recuperación gradual de peso, periodos de sueño interrumpido, evolución de lesión y curaciones, y seguimiento tras caída. Se usan valores clínicos ilustrativos compatibles entre sí, sin considerarlos una recomendación de tratamiento ni una distribución medida de La Paz.

Las identidades y series completas son distintas. Se permiten repeticiones clínicamente normales de cifras, tipos de diagnóstico, categorías, parentescos y nombres de zonas: prohibirlas produciría datos artificialmente inverosímiles. Los identificadores y las combinaciones de residente/fecha/tipo de registro evitan duplicaciones.
