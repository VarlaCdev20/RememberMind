---
title: "Antecedentes sintéticos de Enfermería y estado del conocimiento experto"
status: CURRENT
last_reviewed: 2026-10-17
source_of_truth: false
clinical_inference_activated: false
---

# Datos cargados y alcance comprobado

> Actualización posterior: el estado inicial de cero reglas y cero evaluaciones
> descrito aquí fue superado por la [integración de simulación local](SIMULACION_LOCAL_2026_10_17.md).
> Se conservan ambos informes para distinguir la carga de antecedentes de las
> referencias artificiales independientes utilizadas después para probar PET.

La propietaria confirmó expresamente que los residentes son ficticios y la base
es de desarrollo. Se ejecutó la carga en `remembermind_dev`, PostgreSQL local,
sin cambios de esquema, cuentas, permisos, asignaciones actuales ni pantallas.
Los cinco residentes ya estaban admitidos y asignados al enfermero explícito
`COD_PER_000003`; no se crearon personas como sustitución.

Lote `SIM-SE-20261017-V1`: **60 días, del 18/08 al 16/10/2026**. Todos los registros
del lote llevan una marca legible de simulación. Las autorías corresponden a
profesionales ficticios existentes y activos: Enfermería `COD_PER_000003`,
Medicina `COD_PER_000008` y Fisioterapia `COD_PER_000006`. Cada nota y valoración
funcional referencia una atención del mismo residente y profesional.

| Residente | Perfil del escenario | Caídas del lote | Lectura real del resumen a 90 días |
|---|---|---:|---|
| Elena Vargas Mamani · COD_RES_000028 | Olvidos y preguntas repetidas; otras capacidades observadas conservadas | 1 | Señal preliminar |
| Hugo Romero Torrico · COD_RES_000007 | Dificultad persistente de memoria/atención y noches con sueño interrumpido | 2 | Señal preliminar |
| Mercedes Choque Rivera · COD_RES_000030 | Mayor afectación descriptiva, apoyos físicos y caídas repetidas | 5 | Señal preliminar |
| Gloria Sánchez Condori · COD_RES_000010 | Comparación sin dificultades registradas durante las oportunidades simuladas | 0 | Observación documentada |
| Teresa Aguilar Rojas · COD_RES_000019 | Comparación con controles alternados incompletos | 0 | Observación documentada |

La comprobación llamó a `ConsultarResumenCognitivoResidente::consultar` con
la cuenta de Enfermería `COD_USU_000003`, conservando las Policies y el alcance
vigentes. Después de rectificar las coincidencias de hora, ninguno de los cinco
presentó discordancias simultáneas. No se ha verificado visualmente el diseño.

## Volumen y conservación

| Tabla | Registros iniciales del lote |
|---|---:|
| atenciones | 82: 57 médicas y 25 de Fisioterapia |
| notas_clinicas | 57 |
| valoraciones_funcionales | 25 |
| controles_cognitivos | 300 |
| signos_vitales | 600 |
| registros_sueno | 300 |
| registros_ingesta | 900 |
| registros_hidratacion | 1200 |
| registros_eliminacion | 600 |
| registros_movilidad | 300 |
| registros_conductuales | 300 |
| valoraciones_dolor | 300 |
| incidentes | 8 |
| **Total clínico inicial** | **4972** |

Se reutilizó el turno existente de guardia continua 24H `COD_TUR_000002` y se
incorporaron **58 jornadas históricas simuladas** que faltaban; dos ya existían.
No se crearon turnos ni se cambiaron jornadas existentes. Las filas de ingesta,
hidratación y eliminación requieren jornada según el esquema aprobado y
referencian la fecha correcta. La auditoría identifica su origen ficticio.

La primera carga usó las 09:40 para controles y coincidió con observaciones
anteriores. Se registraron **36 rectificaciones de hora**: el original del lote
conserva sus valores, fecha y texto, y cambia a `RECTIFICADO`; el sucesor está a
las 09:45, referencia el original en su texto y tiene un evento Activitylog que
enlaza ambas identidades. No es otra corroboración independiente. Ningún control
anterior ajeno al lote se modificó o eliminó.

En total se añadieron **5008 filas clínicas**, incluidas esas 36 sucesoras, más
58 jornadas. Permanecen 300 controles vigentes del escenario y 36 originales
rectificados, además de la historia previa. El reintento final verificó los
4972 originales y sus rectificaciones con **cero inserciones o rectificaciones
nuevas**. Si cambia el contenido o falta una fila, la carga falla sin repararla
silenciosamente. El fallo intermedio revierte también los correlativos.

Hay un respaldo anterior en almacenamiento privado:
`storage/app/private/dev-backups/antes-escenarios-cognitivos-20261017-dc0f4f08.dump`
(1.600.221 bytes). No se probó una restauración del respaldo.

## Reglas habilitadas y bloqueadas

Estado consultado directamente en la base después de cargar los datos:

| Elemento | Estado local | Motivo o condición |
|---|---|---|
| Resumen descriptivo de controles | Disponible para el alcance de Enfermería | Lee respuestas originales; repetición/olvido produce «Señal preliminar», sin diagnóstico |
| Detección de respuestas distintas del mismo indicador y momento | Disponible | Conserva discordancia; no elige arbitrariamente una respuesta |
| Reglas clínicas expertas publicadas | **0** | `reglas_expertas` está vacía; no existe una regla concreta habilitada que enumerar |
| ORION-V1-20261008 | INACTIVO | Carga documental de conceptos/relaciones, sin activación ni reglas completas |
| MEM-INV-20261008 | INACTIVO | Propuesta observacional; mapeos pendientes de validación y sin integración instrumental autorizada |
| Mapeos de valores fuente | 6 INACTIVOS, aprobación PENDIENTE | No convierten automáticamente las observaciones en conocimiento aceptado |
| Relaciones semánticas / nodos | 29 / 58 INACTIVOS | Su existencia documental no autoriza inferencia ni participación |
| Publicación en resultados-experto | Bloqueada | `config/sistema_experto.php:lectura_clinica` está vacío; hay 0 evaluaciones guardadas |
| G09 / reglas declarativas | Implementación técnica disponible en escenarios aislados | No se cargaron las reglas experimentales en esta base ni se habilitó ejecución desde la pantalla |
| PET-01..PET-04 | Disponibles exclusivamente para simulaciones con entradas acreditadas | No se derivan de cantidad de controles, caídas o simples alertas; requieren cinco dominios y C01–C05 |
| IADL | Adaptador descriptivo implementado, fuente ausente en esta base | `controles_funcionales_iadl` no está migrada aquí; la valoración funcional básica no sustituye esa fuente ni acredita C03 |

Los patrones experimentales son PET-01 amnésico de dominio único, PET-02 amnésico
multidominio, PET-03 no amnésico de dominio único y PET-04 no amnésico multidominio.
Las pruebas verifican esos patrones en casos sintéticos independientes. No se
ha asignado ninguno a estos cinco residentes ni se ha marcado un caso como
«DCL grave»: esa conclusión no existe en la salida del clasificador actual.

Las invariantes R1–R10 siguen aplicándose en el motor experimental:

| Invariante | Comportamiento |
|---|---|
| R1 | NULL/ausencia no se transforman en normalidad ni en false/cero |
| R2 | Evaluabilidad no acredita alteración |
| R3 | Reutilizar un episodio o instrumento no aporta corroboración independiente |
| R4 | Se exige coherencia de caso, residente, episodio, versión y fecha |
| R5 | La limitación física/institucional o falta de oportunidad IADL no demuestra deterioro cognitivo |
| R6 | Los cinco dominios se mantienen separados, sin índice global inventado |
| R7 | Petersen se abstiene cuando faltan dominios o requisitos generales interpretables |
| R8 | Un hallazgo descriptivo G09 no acredita por sí solo un dominio A/P |
| R9 | PET es patrón candidato sintético; no diagnóstico clínico |
| R10 | El evaluador recibe conocimiento versionado, sin dispersar reglas clínicas en la UI |

## Reproducción y pruebas

Seeder optativo: [EscenariosCognitivosDesarrolloSeeder](../../database/seeders/EscenariosCognitivosDesarrolloSeeder.php).
No se invoca desde DatabaseSeeder. Requiere entorno local/testing, destino
exacto, los tres profesionales y las dos áreas explícitos, turno 24H existente,
admisiones previas al período y asignación actual de los cinco residentes.

Para repetir en el mismo destino confirmado, definir solo en ese proceso:

```powershell
$env:ESCENARIOS_EXPERTO_ALLOW_DATABASE = 'remembermind_dev'
$env:ESCENARIOS_EXPERTO_ENFERMERO = 'COD_PER_000003'
$env:ESCENARIOS_EXPERTO_MEDICO = 'COD_PER_000008'
$env:ESCENARIOS_EXPERTO_FISIOTERAPEUTA = 'COD_PER_000006'
$env:ESCENARIOS_EXPERTO_AREA_MEDICA = 'COD_ARE_000006'
$env:ESCENARIOS_EXPERTO_AREA_FUNCIONAL = 'COD_ARE_000005'
$env:ESCENARIOS_EXPERTO_TURNO_24H = 'COD_TUR_000002'
php artisan db:seed --class=EscenariosCognitivosDesarrolloSeeder
```

Los identificadores anteriores son los del destino inspeccionado, no defaults
ni búsquedas de la primera fila. En otra base deben seleccionarse explícitamente
sus profesionales/áreas/turno válidos. El seeder los valida antes de la carga.

Pruebas rápidas: **198 casos / 2384 aserciones, PASS**, incluyendo siete casos
de carga, Domain Services, Use Cases y flujo SQL experimental. Evidencia en
TEMP: `remembermind-escenarios-cognitivos.sqlite.xml` y su log.

PostgreSQL desechable: **7 casos / 957 aserciones, PASS**, con el runner
`scripts/probar-postgres.ps1`. Informe final en TEMP:
`remembermind-escenarios-cognitivos.pg.xml` y su log. El servidor temporal se
detuvo y su directorio se retiró al finalizar.

Verifica conservación de historia, NULL/false/cero, coherencia de residente y
autor, fechas de jornada, idempotencia, bloqueo de producción/destino ajeno,
competencia, alcance, rollback y rectificaciones trazables. No se presentan
estos tests como validación clínica ni como diseño de pantallas terminado.

No se cambiaron reglas clínicas, permisos ni contratos de publicación, ni se
ejecutaron migraciones. Build/QA visual no aplican al cambio de datos. No se
hicieron operaciones de escritura Git ni push.
