# Observaciones variadas para probar la captura

22/10/2026. Se crearon **20 observaciones inventadas**, con fechas del 01/09 al 20/10/2026 y veinte horas distintas. Son un escenario separado del expediente de Elena, sin identificadores institucionales, firmas ni nombres de profesionales reales.

- [Lectura de los veinte ejemplos](OBSERVACIONES_SINTETICAS.md).
- [CSV de inspección](observaciones-sinteticas.csv): todas las filas llevan `sintetico=TRUE`; `NO_OBSERVADO` se distingue de `FALSE`. No es un importador a la base asistencial.
- [Prueba SQLite](sqlite.junit.xml) y [prueba PostgreSQL](postgres.junit.xml).

El fixture está en `tests/Support/ObservacionesVariadasSinteticas.php`. El test `tests/Feature/ObservacionesVariadasSinteticasTest.php` crea un residente ficticio, un profesional ficticio y jornadas propias por fecha. Usa `RegistrarExpedienteClinico::crearAtencion` y `RegistrarObservacionCognitiva::ejecutar`; la fecha se controla mediante el reloj de pruebas, sin añadir un campo de fecha manipulable a la captura ordinaria.

La consulta real `ConsultarHallazgosObservacionales::instantaneaCompleta` adquiere los veinte controles y produce **60 decisiones**. Se verificaron cuatro episodios afirmativos de repetición de preguntas y cinco de olvido de indicaciones. Los negativos explícitos y los indicadores no observados conservan estados distintos. La consulta no escribe resultados, diagnósticos ni valoraciones de Petersen. No acredita recurrencia, progresión ni clasificación de DCL.

Resultados: **1 prueba / 638 aserciones en SQLite** y **1 prueba / 638 aserciones en PostgreSQL 18 desechable**, sin fallos, errores ni omisiones. Se comprobó la exportación: veinte filas, veinte instantes distintos y marca sintética en todas ellas. Pint PASS. No se modificaron interfaz, estructura, permisos ni registros de la base institucional; no aplica build a este cambio de fixtures y pruebas.

Reproducción desde la raíz:

```powershell
php vendor/phpunit/phpunit/phpunit --filter ObservacionesVariadasSinteticasTest --do-not-cache-result --colors=never --log-junit docs/auditorias/observaciones-sinteticas-2026-10-22/sqlite.junit.xml
./scripts/probar-postgres.ps1 -Filtro ObservacionesVariadasSinteticasTest -Informe "$PWD/docs/auditorias/observaciones-sinteticas-2026-10-22/postgres.junit.xml"
php -r 'require "vendor/autoload.php"; Tests\Support\ObservacionesVariadasSinteticas::exportar("docs/auditorias/observaciones-sinteticas-2026-10-22");'
```

No se ejecutaron commit, push, despliegues ni cargas en expedientes reales. Una observación real nueva requiere la descripción del hecho observado, su momento y autor original; este dataset no reemplaza esos datos.
