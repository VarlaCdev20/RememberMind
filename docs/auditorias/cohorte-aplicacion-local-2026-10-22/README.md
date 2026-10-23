# Acta: 30 residentes de entrenamiento visibles en la aplicación

Fecha: 22/10/2026. [Implementación, autorización y recorrido](../../sistema-experto/COHORTE_VISIBLE_APLICACION_2026_10_22.md).

## Resultado local efectivo

La carga fue ejecutada sobre PostgreSQL local `remembermind_dev`, no solamente en una base desechable:

| Comprobación | Resultado |
|---|---|
| Residentes antes | 80 |
| Residentes agregados | 30, identificados como entrenamiento |
| Residentes después | 110 |
| Perfiles calculados | 3 PET-01, 3 PET-02, 3 PET-03, 3 PET-04 |
| Otros casos | 6 sin patrón y 12 contrastes de requisitos/abstención |
| Discrepancias con el manifiesto | 0 |
| Filas originales, reglas, versiones, permisos y migraciones | Huellas y cantidades preservadas en 55 tablas verificadas durante la transacción |
| Esquema | Sin modificación; ninguna migración instalada |
| Respaldo | `pg_dump` privado previo, formato custom, 6.583.606 bytes; hash y referencia en el informe privado |
| Repetición desde otro proceso | 0 residentes agregados, mismos 30 casos, 0 discrepancias |

Las huellas, respaldo y datos de auditoría se conservaron en `storage/app/private/auditorias-locales/cohorte30/`, excluido de Git. No se publican los datos originales de los 80 residentes anteriores. El [informe de resultados locales](resultados-locales.json) contiene exclusivamente la cohorte generada, sus rutas, estados y cantidades generales.

La verificación de sólo lectura no vuelve a sembrar ni demuestra que la historia anterior deba permanecer inmutable para siempre: sus nuevos registros operativos legítimos pueden continuar. La comprobación de preservación corresponde a la transacción de carga y queda registrada en `carga.json`. La salida CLI distingue `NO_APLICA_SOLO_VERIFICACION` de un fallo de preservación.

## Accesos comprobados por código y pruebas

- [Listado filtrado en Psicología](http://127.0.0.1:8000/admin/psicologia/evaluaciones-asignadas?buscar=entrenamiento).
- [Listado filtrado de resultados](http://127.0.0.1:8000/admin/sistema-experto/residentes?buscar=entrenamiento).
- [Entrenamiento 01: posible DCL amnésico de dominio único](http://127.0.0.1:8000/admin/psicologia/residente/COD_RES_000081/resultados-experto).
- [Entrenamiento 04: posible DCL amnésico multidominio](http://127.0.0.1:8000/admin/psicologia/residente/COD_RES_000084/resultados-experto).
- [Entrenamiento 07: posible DCL no amnésico de dominio único](http://127.0.0.1:8000/admin/psicologia/residente/COD_RES_000087/resultados-experto).
- [Entrenamiento 10: posible DCL no amnésico multidominio](http://127.0.0.1:8000/admin/psicologia/residente/COD_RES_000090/resultados-experto).

Se mantiene el inicio de sesión ordinario y los permisos existentes. No se crea un enlace que salte la autenticación. El menú vigente del dashboard de Psicología lleva a «Mis residentes»; las fuentes se consultan desde la ficha y los resultados. No se atribuyen los registros de entrenamiento al profesional real conectado.

## Pruebas SQLite y PostgreSQL

```powershell
$env:RM_COHORTE30_INFORME = Join-Path $PWD 'docs/auditorias/cohorte-aplicacion-local-2026-10-22/sqlite-resultados.json'
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter 'CohorteInferencia30Test|ManifiestoCognitivo30Test|MisResidentesPsicologiaTest|FichaPsicologicaResidenteTest|NavegacionSistemaExpertoTest|CapturaInstrumentalInstitucionalTest' --log-junit docs/auditorias/cohorte-aplicacion-local-2026-10-22/sqlite.junit.xml

$env:RM_COHORTE30_INFORME = Join-Path $PWD 'docs/auditorias/cohorte-aplicacion-local-2026-10-22/postgres-resultados.json'
./scripts/probar-postgres.ps1 -Filtro 'CohorteInferencia30Test|ManifiestoCognitivo30Test|MisResidentesPsicologiaTest|FichaPsicologicaResidenteTest|NavegacionSistemaExpertoTest|CapturaInstrumentalInstitucionalTest' -Informe "$PWD/docs/auditorias/cohorte-aplicacion-local-2026-10-22/postgres.junit.xml" -Log "$PWD/docs/auditorias/cohorte-aplicacion-local-2026-10-22/postgres.log"
```

La primera ejecución ampliada tuvo **74 pruebas y un fallo** en cada motor: una assertion del dashboard todavía exigía el botón retirado «Abrir sistema experto». Los seis casos del seeder/manifiesto ya pasaban, incluidos la lectura local sin configuración heredada y la preservación de gobierno.

Se corrigió únicamente esa expectativa para comprobar el enlace vigente «Mis residentes» y su ruta. Se añadieron las comprobaciones del filtro inicial por URL. No se relajó ninguna Policy, requisito clínico ni restricción de integridad.

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter NavegacionSistemaExpertoTest --log-junit docs/auditorias/cohorte-aplicacion-local-2026-10-22/sqlite-navegacion.junit.xml
./scripts/probar-postgres.ps1 -Filtro NavegacionSistemaExpertoTest -Informe "$PWD/docs/auditorias/cohorte-aplicacion-local-2026-10-22/postgres-navegacion.junit.xml" -Log "$PWD/docs/auditorias/cohorte-aplicacion-local-2026-10-22/postgres-navegacion.log"
```

La repetición de navegación pasó **4 pruebas y 36 aserciones** por motor. Se conservaron las evidencias iniciales con su fallo y las nuevas ejecuciones satisfactorias. La siguiente consolidación combina las 70 pruebas restantes de la ejecución inicial con las 4 de navegación repetidas; **no representa una nueva ejecución completa de las 74**:

| Motor | Pruebas únicas comprobadas | Aserciones consolidadas | Fallos/errores finales | Evidencias |
|---|---:|---:|---|---|
| SQLite | 74 | 3.380 | 0 / 0 | [Inicial](sqlite.junit.xml), [navegación corregida](sqlite-navegacion.junit.xml) |
| PostgreSQL | 74 | 3.379 | 0 / 0 | [Inicial](postgres.junit.xml), [navegación corregida](postgres-navegacion.junit.xml), [log](postgres-navegacion.log) |

Comparación entre motores:

```powershell
php scripts/comparar-cohorte30.php docs/auditorias/cohorte-aplicacion-local-2026-10-22/sqlite-resultados.json docs/auditorias/cohorte-aplicacion-local-2026-10-22/postgres-resultados.json
```

Resultado: PASS, treinta casos semánticamente equivalentes, mismos estados, motivos y cantidades SQL. [SQLite](sqlite-resultados.json) y [PostgreSQL](postgres-resultados.json). El replay comprobado continúa siendo en memoria; no se afirma conservación SQL conjunta del DTO.

## Carga y verificación en la instalación local

```powershell
php scripts/cargar-cohorte-local.php remembermind_dev
php scripts/cargar-cohorte-local.php remembermind_dev --verificar
```

La primera operación agregó treinta residentes y confirmó la preservación. Las verificaciones posteriores recuperaron y evaluaron los treinta casos con configuración instrumental inicial vacía, en procesos separados, sin duplicarlos. El coordinador y las Policies reales procesaron las fuentes SQL; no se insertaron resultados esperados para obtener coincidencias.

## Frontend, revisión y pendientes

`npm run build`: PASS, Vite 8.3.1, 108 módulos, 4,46 s. Pint pasó en los siete archivos PHP revisados del alcance; `php -l scripts/cargar-cohorte-local.php` pasó. El diff revisado no presentó errores de espacios finales en los archivos del alcance.

La automatización del navegador fue rechazada por la configuración de Work. No se declara QA visual ni captura de pantalla realizada. La apertura del listado mediante Codex devolvió `queued`; se entregan también enlaces directos. Livewire comprueba presentación y acciones, no percepción visual.

Sin modificaciones de estructura, reinicios de BDD, alteración de roles/permisos existentes, aprobación clínica fabricada o vinculación de revisiones a un DTO transitorio. La persistencia conjunta sigue pendiente. Sin `git add`, commit, push ni despliegue.
