# Integración versionada del laboratorio experto

Fecha: 19/10/2026. Alcance autorizado: incorporar el trabajo local del laboratorio y sus dependencias como un conjunto coherente y comprobarlo desde una copia limpia. Base: `9695a640f826dc62988660c59c6aa9b35e2bb9e1`. No cambia el alcance clínico ni cierra las fases 2–8 del plan institucional.

## Contenido integrado

- Motor experimental de cinco dominios, quince reglas G09, puente Petersen, comparaciones longitudinales y generadores de explicación; escenarios sintéticos y controles G08/G09.
- Editor declarativo de conocimiento, exportación del paquete y validación de códigos efectivos únicos. El refactor de admisibilidad COG-MEM conserva sus restricciones en el dominio.
- Adaptador de fuentes de ensayo, gestión técnica de revisiones SQL, archivo privado cifrado, consulta/reproducción y comandos explícitos.
- Sesión web del laboratorio, explorador de reglas/red, vistas modulares y hoja CSS, con registro de dependencias en el provider. Se incorporan los comandos y binding nuevos; se dejan fuera los cambios de formato ajenos del provider.
- Pruebas unitarias, de integración e interfaz, fixtures sintéticos, scripts puros, manuales y actas previas del laboratorio.
- CI: descubrimiento explícito de las catorce clases del laboratorio en SQLite y cuatro de integración PostgreSQL. El control `scripts/verificar-pruebas-laboratorio.php` falla cuando una clase esperada no existe o no tiene pruebas; se comprobó el negativo retirando una clase del listado. El workflow puro incluye también la independencia de ORION.
- Bloqueos previos detectados en la copia limpia: la prueba de arquitectura confundía todo `</a>` con un cierre inválido; ahora comprueba emparejamiento y ausencia de anidamiento. Dos consultas del resumen cognitivo accedían directamente a `route()`/`auth()` desde Application; se incorporan sus adaptaciones a `EnlacesAplicacion` y `ContextoEjecucion`, ya existentes, sin cambiar permisos, consultas ni reglas clínicas.

Los documentos del 17/18 describen sus incrementos locales. La [consolidación del punto 1](CONSOLIDACION_PUNTO_1_2026_10_19.md) y su CSV conservan el estado previo a esta integración; sus hashes no se reescriben para aparentar que aquellos archivos ya estaban integrados.

## Corrección detectada durante la revisión

El ejecutor declarativo G09 podía recibir un resultado fuente generado para otro residente, evaluación, corte o versión y reconstruir sus evidencias bajo el contexto nuevo. El recorrido integral generaba fuente y contexto juntos, pero el helper público no protegía su contrato.

Se añadió `contexto_origen` explícito en el resultado fuente, incluso sin evidencias. Antes de ejecutar conocimiento declarativo o cotejarlo con un contexto original se comprueba identidad, versión fuente y corte exacto mediante la guarda compartida `validarResultadoFuente`, y se contrasta nuevamente el resultado completo con su productor original. Se rechazan trazas alteradas de evidencia, regla, fecha, episodio o condiciones. La revisión del paquete declarativo permanece separada de la versión fuente G09. Los cortes horarios conservan su instante. El cotejo abstracto sin contexto sigue identificándose con `contexto_original=false`.

Regresión añadida: seis contextos ajenos, cinco trazas alteradas, fuente vacía y positivo con corte horario y otra revisión; once negativos adicionales verifican el cotejo. Ambas correcciones pasaron la revisión independiente: 24 pruebas/25 aserciones, 97 casos G09 y 164 aserciones del cotejo D-130. La ampliación compartida se verificó también en la suite relacionada de 381 pruebas/4354 aserciones; la aceptación del candidato final se registra abajo. Es evidencia técnica, no evaluación de validez clínica.

## Límites conservados

- Laboratorio web: cuenta activa de Superadministración, permiso de auditoría, sin previsualización y únicamente local/testing; casos cerrados con identidad sintética y seis ejecuciones temporales por actor/sesión.
- Archivo durable y gestor experimental: testing, consola y BDD desechable expresamente reconocida. El archivo usa rutas acotadas, cifrado autenticado, exclusión y publicación temporal; no sobrescribe otra entrada bajo la misma identidad.
- La autoría técnica se deriva de la sesión; no se exponen operaciones de archivo/gestión experimental por HTTP. Los comandos leen entradas declarativas, no ejecutan PHP/SQL del documento.
- No se agregan tablas, columnas, permisos, dependencias ni datos clínicos. El inventario aprobado se mantiene en 106 tablas de negocio. No se ejecutan migraciones en la instalación operativa.
- El perfil experimental y su archivo no sustituyen conservación instrumental institucional, interpretación de los once componentes pendientes ni derivación original de C01–C05. No habilitan Petersen sobre residentes por el hecho de incorporarse a Git.
- Horarios de Institución, su prueba, formato previo del provider y `work/` se preservan fuera de este commit. Las dos adaptaciones de consultas y la prueba de enlaces inicialmente aisladas se incorporan tras reproducir sus fallos previos de arquitectura en el candidato y verificar su corrección.
- No se introducen dependencias V1. El escaneo encontró el nombre/alias previo `AdultoMayorFormModal` en el provider compartido, fuera de los hunks modificados y del flujo experto; su limpieza corresponde al módulo de Residentes.

## Copia limpia y evidencia

Se exportó exclusivamente el árbol preparado en el índice mediante `git write-tree` y `git archive`, a un directorio temporal nuevo. Como `.gitattributes` excluye `.github` de los archivos de distribución, se repusieron los dos workflows desde sus blobs del índice. No se copiaron `.env`, datos, caches ni dependencias de la instalación de trabajo. Se instalaron dependencias con `composer install --no-scripts --no-interaction --prefer-dist --no-progress` y `npm ci` desde los locks; se omitió la descarga de navegador durante esa instalación. La copia no incluye los cambios independientes sin stage.

Árbol del candidato tras las tres reparaciones de arquitectura: `532b08e660d6768c8c8d09055d2e3d4a84f62560`. Se compararon los 95 archivos preparados de código, configuración, CI, fixtures y locks contra los bytes de la copia, usando las identidades de blob: todos coinciden. La documentación final de resultados se añade después de las corridas sin alterar el código aceptado.

Una comprobación de preadmisión utiliza `git grep`. Se dotó a la copia de metadatos Git aislados y del índice exacto del candidato, y se repitió esa prueba para que su búsqueda fuera real; no se utilizó su salida vacía ante falta de repositorio como evidencia de éxito.

| Comprobación en la copia limpia | Resultado |
| --- | --- |
| Regresión final del laboratorio, captura, informes, gobierno, arquitectura y resúmenes | **700 pruebas / 8017 aserciones; 0 errores, fallos u omisiones**; 6 min 48 s |
| PostgreSQL: laboratorio e integraciones observacionales/gobierno | **128 pruebas / 729 aserciones**, sin errores, fallos u omisiones; 4 min 14 s |
| PostgreSQL después de la guarda compartida final: adquisición, archivo, conocimiento, consola y contexto declarativo | **52 pruebas / 171 aserciones**, sin errores, fallos u omisiones; 43 s |
| Scripts puros enumerados en el workflow experimental | **13 PASS**, incluidas independencia, quince reglas, cinco dominios y patrones/abstenciones Petersen |
| Descubrimiento de pruebas y control negativo | **14 clases SQLite y 4 PostgreSQL**; quitar una clase produce salida 1 |
| Sintaxis PHP del conjunto preparado | **79 archivos PASS** |
| Dependencias y frontend desde los locks | **composer install, npm ci y npm run build PASS**; Vite completó el build en 3,28 s |
| Identidad del candidato y documentación | **95 archivos coincidentes; 24 documentos sin enlaces locales inexistentes** |

Los XML y logs quedan en `storage/app/private/integracion-laboratorio-20261019/`, fuera de Git. Huellas SHA-256:

- `limpio-sqlite-alcance-final.xml`: `d0c5b38930c8873a69c59829bef008e7da110d583548293a286d7b4dae2e666f`.
- `limpio-postgres-inicial.xml`: `429aabbc339f2dab8c396bb15ea51512090d3a5d9d71d3fa4bf90b354404b7e8`.
- `limpio-postgres-final.xml`: `b5b3aa6328a103cff75278911fdbb4f255d9c3ab67a634d5cb6864dc2a7a043b`.

Ambos clústeres PostgreSQL fueron desechables y sus apagados se comprobaron. La última corrida usó además almacenamiento Laravel temporal separado. La corrida de 128 precede la ampliación compartida final; la de 52 y la regresión SQLite de 700 comprueban esa ampliación. No se atribuye la corrida anterior a código posterior.

La suite general de la aplicación se detuvo; no se declara aprobada ni se utiliza como evidencia de aceptación. Mostró dos fallos de arquitectura preexistentes antes de las reparaciones descritas arriba. Sus repeticiones ya corregidas aprobaron: 14 pruebas/431 aserciones de arquitectura frontend y 68 pruebas/1493 aserciones de límites, contratos y resúmenes afectados. Una corrida anterior detenida para aislar almacenamiento temporal tampoco cuenta como aprobada. El cierre utiliza el grupo completo del laboratorio y sus integraciones cercanas, conforme a la selección por módulo de `tests/AGENTS.md`.

Comandos de aceptación:

```powershell
php vendor/phpunit/phpunit/phpunit --list-tests-xml storage/app/private/integracion/descubrimiento.xml
php scripts/verificar-pruebas-laboratorio.php storage/app/private/integracion/descubrimiento.xml sqlite
php scripts/verificar-pruebas-laboratorio.php storage/app/private/integracion/descubrimiento.xml postgres
$grupos = @(
    'AdquisicionPerfilExperimentalTest|ArchivoPerfilExperimentalTest|GestionConocimientoExperimentalTest|ConsolaPerfilExperimentalTest|LaboratorioExpertoFrontendTest|LaboratorioPerfilInterfazTest',
    'CatalogoPerfilCincoDominiosTest|ComparadorLongitudinalExperimentalTest|ContratoG09ExperimentalTest|ControlesTareaG09ExperimentalTest|EditorPaqueteExperimentalTest|PerfilExperimentalTest|ReglasDeclarativasPerfilExperimentalTest|ResultadoPerfilExperimentalTest',
    'PresentacionG09AisladaTest|ApplicationBoundaryTest|PresentationBoundaryTest|FrontendArchitectureTest|SucesionObservacionalTest|HallazgosObservacionalesTest|GobiernoReglasExpertasTest',
    'CapturaInstrumentalFormularioTest|CapturaInstrumentalInstitucionalTest|PerfilInstrumentalInstitucionalTest|ResumenCognitivoResidenteTest|EstadoEvolucionResidenteTest|EvolucionComparadaResidenteTest|FichaResumenClinicoTest|DesacoplamientoContextoTest|ConsultasPorCapaTest|InformeNeurocognitivoTest|RevisionObservacionalExpertaTest|CodecIdentidadHistoricaTest'
)
php vendor/phpunit/phpunit/phpunit --fail-on-empty-test-suite --filter ($grupos -join '|') --log-junit storage/app/private/integracion/sqlite-alcance-final.xml
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'AdquisicionPerfilExperimentalTest|ArchivoPerfilExperimentalTest|GestionConocimientoExperimentalTest|ConsolaPerfilExperimentalTest|LaboratorioExpertoFrontendTest|LaboratorioPerfilInterfazTest|SucesionObservacionalTest|GobiernoReglasExpertasTest|GobiernoReglasConcurrenciaPostgresTest|HallazgosObservacionalesTest' -Informe storage/app/private/integracion/postgres.xml -Log storage/app/private/integracion/postgres.log
pwsh -NoProfile -File scripts/probar-postgres.ps1 -Filtro 'AdquisicionPerfilExperimentalTest|ArchivoPerfilExperimentalTest|GestionConocimientoExperimentalTest|ConsolaPerfilExperimentalTest|ReglasDeclarativasPerfilExperimentalTest' -Informe storage/app/private/integracion/postgres-final.xml -Log storage/app/private/integracion/postgres-final.log
npm run build
```

También se ejecutan los trece scripts puros de `motor-experto/tests` enumerados en el workflow experimental, incluidos los dos de independencia. Las pruebas son sintéticas. Sus grupos y corridas se solapan; no se suman como una sola cantidad de casos distintos.

Seguridad independiente: sin hallazgos P1/P2 en Actions, archivo, comandos, sesión y panel; cuatro negativos web/cuenta/permiso/entorno aprobados. Se realizaron además pruebas de archivo, idempotencia, corrupción, historia y límites en los grupos de integración indicados arriba.

QA visual: el intento de navegador devolvió `Local browser use is disabled in Work settings`. No hay PASS visual/responsive ni se utiliza un navegador alternativo para eludir esa configuración. Livewire y build acreditan sus respectivas capas; los cinco viewports y teclado siguen pendientes de inspección real.

## Entrega

- [x] Conjunto local y dependencias incorporados de forma coherente, preservando cambios independientes.
- [x] Dos defectos de identidad/traza G09 corregidos y revisados independientemente.
- [x] Bloqueos previos de arquitectura corregidos y cubiertos por la regresión final.
- [x] Copia limpia con dependencias propias, identidad de fuentes comprobada, pruebas y build aprobados.
- [x] CI ampliado, evidencia privada y límites de aceptación documentados.
- [ ] QA visual en cinco viewports y teclado: pendiente por bloqueo del navegador en Work.
- [ ] CI remoto del PR: pendiente de publicación autorizada.

El código del índice final coincide con el candidato aceptado; los resultados y esta checklist completan su documentación. Entrega mediante commit local, sin push ni despliegue. Continúan los pendientes institucionales de las fases 2–8 del plan; este cierre no inventa mediciones ni activa motores experimentales sobre residentes.
