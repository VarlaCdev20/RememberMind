# Fase 6: preparación compuesta y reproducción

Fecha: 20/10/2026. Estado: incremento técnico de preparación en memoria.
**La conservación institucional completa sigue abierta.** No se ha añadido
estructura ni historia clínica a `remembermind_dev` y no se ha ejecutado DDL.
El inventario aprobado continúa en **108 = 75 operativas + 33 expertas**.

## Implementación e integración

`PrepararEvaluacionInstitucional::preparar()` coordina las consultas existentes
de lectura cognitiva y Petersen. La captura instrumental V3 se lee una vez y
se comparte como objeto tipado; ambas ramas deben corresponder al mismo
residente, usuario, corte con microsegundos y versión instrumental. La familia
de gobierno Petersen conserva su propia versión, sin inventar una versión
común para dos paquetes de conocimiento distintos.

`InstantaneaPetersen` conserva juicios admitidos, procedencia de todos los
juicios adquiridos, membresías ordenadas de sus soportes (incluidos juicios
descartados), exclusiones de adquisición, gobierno observado y constancias
documentales. El original inmutable sigue siendo el origen de la justificación
y sus comprobaciones; la propuesta relacional reutiliza esas filas mediante
FK. El DTO preparado no constituye una copia persistida de ese original.

`InstantaneaEvaluacionInstitucional` reúne ambas preparaciones, verifica sus
huellas y vuelve a ejecutar ambos motores. Una aplicación usada por Petersen
debe contener exactamente la misma captura canónica que en V3. No acepta
cruces de residente, actor, corte o versión ni mediciones discrepantes aunque
se recalculen las huellas externas.

La consulta ordinaria conserva su resultado público. El objeto instrumental
completo se expone únicamente mediante el método backend `preparar()`; no se
añade al estado de Livewire, una descarga de historia ni un nuevo endpoint.

## Seguridad, documentos y límites

- Preparar vuelve a cargar cuenta/residente y aplica las Policies vigentes.
  Compartir una captura anterior no conserva permisos revocados: se revalidan
  los cinco permisos de sus fuentes y la autorización contextual actual.
  IADL mantiene su Policy antes de la consulta.
- Las constancias documentales guardan referencia, papel, metadata observada,
  hash y resultado de la comprobación, sin ruta privada ni bytes. Un documento
  con bytes válidos pero fecha futura no pasa a evidencia admitida. La columna
  SQL documental exige fecha; la defensa de lectura ante ausencia no modifica
  esa restricción ni requiere fabricar una fila inválida para probarla.
- El replay reproduce las entradas autorizadas que fueron preparadas. No
  vuelve a comprobar permisos históricos, los bytes documentales ni la
  adquisición SQL. Los soportes no disponibles permanecen como exclusiones;
  no se adquiere contenido clínico ajeno para explicarlas.
- Las transacciones de lectura actuales no acreditan una instantánea global
  atómica bajo PostgreSQL READ COMMITTED. El futuro guardado deberá fijar un
  aislamiento coherente y bloquear/sellar las versiones conforme al diseño.

## Release y reproducción

La preparación compuesta captura cantidad y huella del conjunto de archivos
`motor-experto/src`. `vincularRelease()` verifica el ZIP real y exige que su
conjunto/bytes corresponda tanto al ejecutor actual como a la identidad
capturada al preparar. Un ZIP internamente íntegro de otro ejecutor no puede
sustituirlo. La verificación histórica del ZIP mantiene su independencia del
checkout actual.

El ZIP contiene software, lock y red conceptual; no contiene DTO clínico,
configuración local, paquetes SQL ni `.env`. Una prueba sintética extrae el
release en un temporal propio y ejecuta el contrato compuesto en otro proceso
PHP con autoload solo desde ese release. Los temporales sintéticos se eliminan
tras comprobar que pertenecen al directorio creado por la prueba.

**Vincular no equivale a retener.** `persistido=false` y
`release_retenido=false` permanecen explícitos. No hay todavía operación de
guardar/recuperar institucional, identidad idempotente persistida, respaldo
operativo comprobado o historial completo conectado a la interfaz. Los
contratos instrumentales V1–V3 y las guardas del almacén experimental siguen
vigentes.

## Diseño relacional propuesto

El [diccionario de conservación compuesta](PROPUESTA_CONSERVACION_COMPUESTA_FASE_6_2026_10_20.md)
propone 25 relaciones expertas nuevas, siete columnas en dos tablas existentes,
protecciones SQL de definiciones y cierre de colecciones. El inventario
candidato sería 133, pero **no está aprobado ni instalado**. Evita JSON/EAV,
archivos clínicos y duplicar salidas calculadas: conserva entradas, versión y
release para regenerar la explicación y contrastar su huella.

La aprobación del 17/10 para nueve relaciones experimentales y la del 19/10
para captura original no autorizan esta ampliación. La decisión estructural,
la implementación de guardar/recuperar, sus pruebas de concurrencia y la
retención efectiva son pasos posteriores. No se modifica el baseline vigente
como si la propuesta ya hubiese sido aceptada.

## Verificación del incremento

Selección relacionada ejecutada con datos sintéticos:

```powershell
php vendor/phpunit/phpunit/phpunit --filter 'InstantaneaEvaluacionInstitucionalTest|PetersenInstitucionalTest|CapturaPetersenOriginalTest|GobiernoPetersenInstitucionalTest|ConocimientoCognitivoInstitucionalTest|InstantaneaInstrumentalCompletaTest|ReleaseMotorInstrumentalTest|CapturaInstrumentalCanonicaTest|ReproduccionFuenteNominalTest' --log-junit storage/app/private/fase6-sqlite.xml
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'InstantaneaEvaluacionInstitucionalTest|PetersenInstitucionalTest|CapturaPetersenOriginalTest|GobiernoPetersenInstitucionalTest|ConocimientoCognitivoInstitucionalTest|InstantaneaInstrumentalCompletaTest|ReleaseMotorInstrumentalTest|CapturaInstrumentalCanonicaTest|ReproduccionFuenteNominalTest' -Informe storage/app/private/fase6-postgres.xml -Log storage/app/private/fase6-postgres.log
```

- SQLite: **175 pruebas / 961 aserciones, PASS**.
- PostgreSQL desechable: **175 / 961, PASS**. El cluster propio se detuvo y
  retiró al finalizar; no se reutilizó la base de desarrollo.
- Corrección final del contrato de manifiesto y cierre de membresías: selección
  posterior SQLite `InstantaneaEvaluacionInstitucionalTest`, **21 / 58, PASS**;
  selección posterior PostgreSQL compuesto, cuatro patrones SQL, documento de
  cabecera y cinco revocaciones de permisos, **31 / 145, PASS**. Evidencias
  `fase6-contrato-final.xml` y `fase6-postgres-final.xml` en storage privado.
  Son ejecuciones con solapamiento, no totales acumulables.
- Revisión independiente: selección de núcleo/compuesto/captura SQL,
  **98 / 407, PASS**, y negativo posterior de manifiesto V1 **1 / 2, PASS**.
  Dictamen final: PASS acotado de preparación/replay y diseño estático; no
  certifica persistencia, sellado concurrente o retención todavía inexistentes.
- `php motor-experto/tests/independencia.php` y
  `php motor-experto/tests/orion_independencia.php`: PASS tras el cambio final.
- Pint sobre los doce archivos PHP propios: PASS; `git diff --check`: PASS.

La primera ejecución amplia falló en un caso nuevo que intentaba insertar
NULL en la fecha documental NOT NULL. Se corrigió el caso, manteniendo la
restricción y las comprobaciones de documento futuro/alterado. Las ejecuciones
finales anteriores son posteriores a esa corrección.

Los resultados técnicos no acreditan adopción clínica ni precisión diagnóstica.
No hay cambios de frontend: build y QA visual nuevos no aplican a este
incremento backend. La revisión del ámbito modificado no detecta dependencias
V1, depuración ni sustitutos de persistencia. No se realiza push.
