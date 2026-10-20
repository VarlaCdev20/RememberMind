# Cierre de fases 6–8: contrato vigente y avance verificable

Fecha: 20/10/2026. Estado: **en curso**. Este documento distingue preparación,
conservación, interfaz y aceptación. No declara validación clínica.

## Fuente de verdad y decisión estructural

Rigen el baseline V2 con sus extensiones expresamente aprobadas, los contratos
institucionales A–D y E–H, la captura original de fase 2 y la reducción aprobada
del 20/10. Esta última dejó **104 tablas de negocio (75 operativas y 29
expertas)**, preservando historia. La propuesta de 25 tablas de conservación
compuesta fue rechazada. Su diccionario no autoriza migraciones ni sirve como
esquema de reserva. El objetivo vigente es recuperar y recalcular íntegramente
una evaluación histórica con una estructura menor, sin JSON/EAV ni archivos de
datos clínicos.

Código relevante: `PrepararEvaluacionInstitucional` y los DTO
`InstantaneaInstrumental`, `InstantaneaPetersen` e
`InstantaneaEvaluacionInstitucional` verifican el contrato compuesto **en
memoria**. `ResultadosExpertoResidente` consulta las fuentes y resultados
disponibles y conserva revisiones de Medicina y Psicología para los alcances ya
representables. Los tests existentes prueban esas capacidades, no el guardado
compuesto institucional.

## Incremento seguro realizado

`AlmacenReleasesMotor` retiene una copia privada del ZIP del motor, por huella,
sin sobrescribirla. Verifica el ejecutor antes de copiar, los bytes después de
copiar y los bytes retenidos al recuperar. La lectura de una huella ausente
falla sin crear un sustituto. `ReleaseMotorInstrumental` rechaza rutas fuera
del conjunto permitido para `ARTEFACTO-MOTOR-2`, manifiestos ilegibles y
huellas inválidas. El ZIP contiene software y red documental, **no datos de
residentes**. Esta capacidad aún no está enlazada a una evaluación guardada;
`persistido=false` y `release_retenido=false` siguen describiendo fielmente
los DTO preparados.

## Brecha relacional exacta de fase 6

La cabecera `evaluaciones_expertas` identifica una versión cognitiva; la
evaluación compuesta contiene además un gobierno Petersen independiente. La
captura V3 consume conjuntos separados de instrumentos, preguntas incluso sin
respuesta, opciones, aplicaciones, respuestas, perfiles de captura,
interpretaciones y vínculos declarativos. Petersen consume juicios y soportes
originales, fuentes contextuales mutables, exclusiones y constancias
documentales. El replay requiere además el estado de revisión de cada regla
**observado al corte**, la edad/contexto que se usaron y el release retenido.
Esos conjuntos no caben en las columnas aprobadas de las 104 tablas sin perder
contenido, mezclar granos o reutilizar campos experimentales con otro
significado. Una FK al original o una huella sola no reconstruye una fila
mutable ni impide inserciones posteriores en sus colecciones.

El siguiente diccionario reducido debe probar, conjunto por conjunto, qué
originales pueden sellarse y referenciarse, qué proyecciones mutables deben
copiarse de forma escalar tipada, qué relaciones existentes admiten el mismo
grano y cuáles requieren una relación nueva. Debe fijar PK/FK, nulabilidad,
checks, cierre de colecciones, transición y reversión poblada en SQLite y
PostgreSQL. Solo después de demostrar un `guardar → recuperar → replay` igual al
DTO canónico puede presentarse una ampliación estructural **con número y
columnas exactos** a la propietaria. Hasta aprobarla, no se crean migraciones
para este guardado.

## Estado de fases 7 y 8

La entrada por residente, la captura original, las observaciones, el cálculo
actual de cinco dominios/Petersen, el gobierno de reglas, los historiales
representables y las dos cadenas de revisión ya tienen rutas e interfaz. El
informe compuesto histórico y su revisión no deben publicarse como
persistidos antes de cerrar fase 6. La aceptación pendiente exige casos
sintéticos completos, incompletos y contradictorios con oráculo independiente;
alteración de originales/release, reintento idempotente, revocación de permiso,
concurrencia PostgreSQL, navegación y QA visual de los cinco tamaños. Una suite
verde no demuestra sensibilidad, especificidad ni validez diagnóstica.

## Evidencia de este incremento

- `AlmacenReleasesMotorTest`, `ReleaseMotorInstrumentalTest`,
  `InstantaneaEvaluacionInstitucionalTest` e
  `InstantaneaInstrumentalCompletaTest`: PASS en PHPUnit sin base de datos,
  con artefactos sintéticos.
- `motor-experto/tests/independencia.php` y
  `motor-experto/tests/orion_independencia.php`: PASS.
- Regresión relacionada de instantáneas, Petersen, conocimiento y revisiones:
  `php artisan test --filter='InstantaneaEvaluacionInstitucionalTest|PetersenInstitucionalTest|CapturaPetersenOriginalTest|GobiernoPetersenInstitucionalTest|ConocimientoCognitivoInstitucionalTest|InstantaneaInstrumentalCompletaTest|ReleaseMotorInstrumentalTest|AlmacenReleasesMotorTest|RevisionValoracionExpertaTest|RevisionObservacionalExpertaTest' --quiet`: PASS.
- `vendor/bin/pint --test` para los cuatro PHP modificados y `git diff --check`:
  PASS. No corresponde build frontend porque este incremento no cambia la UI.

No hubo DDL, escritura clínica, cambio de permisos ni publicación de una
evaluación institucional nueva.
