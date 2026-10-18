# Integración local de O.R.I.O.N. — 17/10/2026

Alcance autorizado por la propietaria: completar el recorrido técnico de los
cinco residentes ficticios de `remembermind_dev`, conservar las salidas y
consultarlas desde la pantalla existente. La confirmación de datos ficticios y
la orden «procede y termina todo de forma correcta» corresponden a esta tarea.

## Contrato y fuentes

- Esquema: decisiones aprobadas IADL y conservación V2 de nueve tablas;
  no se incorpora la propuesta histórica de dieciséis.
- Conocimiento nuevo: `SIM-ORION-LOCAL-1`, separado de las dos versiones
  documentales anteriores. Las anteriores conservan su estado y seis mapeos
  pendientes; esta integración no los aprueba ni activa.
- Antecedentes: lote `SIM-SE-20261017-V1`, incluidos sus sucesores trazables.
- Referencias experimentales: CAS-01/02/08/10/20 del oráculo 03A en
  `tests/Unit/SistemaExperto/Domain/Services/Dcl/Fixtures/05_Casos_Sinteticos.csv`.
  Son declaraciones artificiales de cinco dominios y C01–C05, independientes
  de los controles, olvidos y caídas. No son exámenes aplicados al residente.
- No se introducen baremos, pesos, probabilidades, gravedad, etiología ni
  umbrales clínicos. La salida PET es un patrón candidato del caso sintético.

## Recorrido conectado

La consola compone `ExtraerHechosUseCase`, `PrepararMemoriaTrabajoUseCase`,
`EjecutarInferenciaUseCase`, `ClasificarDclUseCase` y `GenerarInformeUseCase`
mediante `AnalizarCasoExperimentalUseCase`. Usa los repositorios Eloquent y
mappers existentes; Domain y Application siguen sin Illuminate.

`ResultadoInferenciaDTO` transporta las decisiones estructuradas originales.
La conservación reutiliza las tablas aprobadas: memoria tipada, soportes,
cinco resultados, funcionalidad IADL, cinco requisitos, candidato/abstenciones,
reglas aplicadas y traza de cada decisión descriptiva. La huella también
contrasta la definición de la regla, sus condiciones y consecuencias.

`LecturaResultadosExperto` consulta el puerto `LecturaSimulacionExperta`;
el lector local reconstruye SQL y verifica la instantánea. No consulta las
fuentes actuales, no ejecuta el motor y no escribe desde render. Si falla la
integridad de cualquier área, clasificación o traza, retira toda la salida.
La pantalla conserva su diseño y añade aviso, requisitos, valores utilizados
y decisiones guardadas. Nunca marca estas áreas como evaluadas clínicamente.

## Reglas habilitadas exclusivamente para simulación

| Regla | Coincidencia literal | Salida descriptiva |
|---|---|---|
| SIM-MEM-01 | memoria_reciente = ALTERACION_LEVE | DIFICULTAD_DESCRIPTIVA |
| SIM-MEM-02 | memoria_reciente = ALTERADA | DIFICULTAD_DESCRIPTIVA |
| SIM-MEM-03 | repite_preguntas = true | REPETICION_REGISTRADA |
| SIM-MEM-04 | olvida_indicaciones = true | OLVIDO_REGISTRADO |
| SIM-ATE-01 | atencion = DISMINUIDA | DIFICULTAD_DESCRIPTIVA |
| SIM-ATE-02 | atencion = FLUCTUANTE | DIFICULTAD_DESCRIPTIVA |
| SIM-LEN-01 | lenguaje = ALTERADO | DIFICULTAD_DESCRIPTIVA |
| SIM-LEN-02 | comprension = ALTERADA | DIFICULTAD_DESCRIPTIVA |

Todas son comparaciones exactas. `NULL` permanece desconocido; `false` no se
convierte en ausencia de dificultad. RAW conserva el tipo y la representación
de intercambio booleana se declara como SIM_TRUE/SIM_FALSE. Las dos formas
comparten episodio y no constituyen corroboraciones independientes.

No hay reglas descriptivas nuevas de función ejecutiva o visuoespacial:
faltan contratos aprobados de sus fuentes. Sus estados de prueba provienen
solo de las referencias 03A, identificadas como artificiales en pantalla.
La publicación clínica `sistema_experto.lectura_clinica` permanece vacía.

## Casos y resultados esperados del oráculo

Orden: memoria, atención, ejecutivo, lenguaje, visuoespacial.

| Residente ficticio | Caso | Referencias | Salida experimental |
|---|---|---|---|
| Elena Vargas Mamani | CAS-01 | A/P/P/P/P; C01–C05 acreditados | PET-01, amnésico de dominio único |
| Hugo Romero Torrico | CAS-02 | A/A/P/P/P; C01–C05 acreditados | PET-02, amnésico multidominio |
| Mercedes Choque Rivera | CAS-08 | A/A/A/A/A; C01–C05 acreditados | PET-02, amnésico multidominio |
| Gloria Sánchez Condori | CAS-10 | P/P/P/P/P; C02 no acreditado | Sin patrón clasificable por requisito |
| Teresa Aguilar Rojas | CAS-20 | A/A/P/P/P; C03 pendiente | Abstención por información insuficiente |

Mercedes tiene más dominios alterados **en su referencia artificial**; el
contrato no ofrece una categoría «DCL grave». Los controles, por sí solos,
no producen estas salidas. Sin las cinco referencias 03A, los cinco dominios
quedan U y no se emite PET, como prueba la regresión de integración.

## IADL y límites de carga

Se preparan 160 observaciones ficticias: ocho actividades, cuatro fechas y
cinco residentes. Teresa conserva falta de oportunidad y nivel previo
desconocido; no se rellena independencia. Las otras observaciones y su
revisión funcional son fixtures explícitos. BADL no sustituye IADL.

La cohorte exige admisión anterior a todo el período, asignación actual y
personal de Enfermería competente y activo. Una fuente IADL alterada o con
sucesora provoca rechazo; la instantánea histórica sigue siendo consultable.
La transacción exterior incluye paquete, IADL, cinco ejecuciones y trazas.
Una falla tardía revierte el lote; repetir el mismo corte no consume códigos
ni sobrescribe resultados. Cambiar entradas requiere una nueva ejecución,
sin modificar la historia anterior.

## Activación y reproducción

Solo entorno local, PostgreSQL en loopback y base exacta `remembermind_dev`,
con ambas claves explícitas de `.env`:

```dotenv
SISTEMA_EXPERTO_SIMULACION_LOCAL=true
SISTEMA_EXPERTO_SIMULACION_BASE=remembermind_dev
```

El valor por defecto es cerrado. Producción se rechaza incluso con el flag.
Los repositorios sin esta capacidad mantienen la restricción previa a testing
y bases desechables. El alias SINTETICO-* solo existe en Domain: la cabecera
SQL conserva la FK `COD_RES_*` del residente original, sin crear duplicados.

```powershell
php artisan experto:simular-local --autor=COD_USU_000001 --enfermero=COD_PER_000003 --corte=2026-10-17T12:00:00-04:00
```

Los responsables son explícitos y verificados, sin fallback a la primera fila.
La consulta usa las Policies vigentes y revalida cuenta, permisos, asignación
y preview en cada interacción. No se crean permisos ni rutas de escritura,
no hay ejecución automática ni cambios en eventos clínicos.

## Aislamiento posterior de la pantalla operativa

La instrucción posterior de la propietaria retira SIM de la consulta habitual.
La evidencia siguiente corresponde a la entrega anterior; el laboratorio y sus
instantáneas se conservan, pero ya no se incorporan al lector normal.
Véase [consulta operativa sin simulaciones](CONSULTA_OPERATIVA_SIN_SIMULACIONES_2026_10_17.md).

## Evidencia de entrega

El respaldo privado anterior a la incorporación se conserva en
`storage/app/private/dev-backups/antes-simulacion-experta-20261017-212502-32aba626.dump`
(1.678.015 bytes; SHA-256
`eccc92d12ce64fc6efc1f935f35b9b817dc9c0242d77a349214030b5f2a9c19b`).

Aplicadas a `remembermind_dev` exclusivamente las migraciones
`2026_10_17_190000_create_controles_funcionales_iadl_table` y
`2026_10_17_200000_incorporate_conservacion_experta_v2`. Sin reset ni ejecución
de otras migraciones pendientes.

Carga confirmada: 160 IADL, cinco evaluaciones, quince trazas y cuarenta
decisiones de reglas. Las cinco salidas coinciden con la tabla de casos.
El reintento produjo cero evaluaciones nuevas y conservó las secuencias.
La lectura con la cuenta real de Enfermería recuperó íntegros los cinco casos.
Evidencia privada: `storage/app/private/dev-backups/verificacion-simulacion-local-20261017.json`.

| Verificación | Resultado |
|---|---|
| SQLite: experto, infraestructura, informe, G09, resumen y arquitectura | 777 pruebas / 5587 aserciones; 776 PASS y un fallo previo ajeno en Clínica |
| Simulación incluida en esa suite | 13 pruebas / 94 aserciones PASS |
| Cierre final de simulación con todos los ajustes | 14 pruebas / 97 aserciones PASS |
| Regresión adicional de zona temporal corrupta | 1 prueba / 3 aserciones PASS en SQLite y PostgreSQL |
| PostgreSQL desechable: simulación, conservación, IADL y flujo | 55 pruebas / 310 aserciones PASS |
| Revisión independiente de cinco negativos expertos | 5 pruebas / 28 aserciones PASS |
| Build Vite | PASS |
| Lectura local y reintento sobre datos cargados | PASS |
| Revisión visual de navegador | No ejecutada: uso del navegador deshabilitado en Work |

Informes JUnit y logs: TEMP `orion-integracion.sqlite.*`,
`orion-integracion.pg.*`, `orion-fecha.pg.*` y
`orion-cierre.sqlite.*`, `orion-integracion.build.log`. Los dos servidores PostgreSQL de las pruebas
terminadas se detuvieron y sus runners retiraron sus propios clusters.
Un intento anterior de arranque quedó detenido; su carpeta temporal se
conserva porque el control automático rechazó su eliminación por política.

El fallo ajeno de `ApplicationBoundaryTest` identifica `route()` en
`Clinica/Aplicacion/Consultas/ConsultarResumenCognitivoResidente.php:88` y
`auth()` en `ResumenSeguimientoResidente.php:21–22`. Son cambios preexistentes
de Clínica; esta integración no los modifica ni reduce el gate para ocultarlos.

Los gates independientes de persistencia, seguridad y experto no encontraron
P1/P2 pendientes en el alcance implementado. Son verificaciones técnicas;
no acreditan validación clínica, métricas poblacionales ni concurrencia real
entre dos procesos. No se ejecutaron operaciones de escritura Git ni push
por la restricción expresa de los reglamentos de esta tarea.
