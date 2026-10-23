# Evidencia y límites de la auditoría

Fecha: 21/10/2026. Corte base `8eee364ed32c079703b95c0eb63204dcb5512cbe`
con trabajo local sin confirmar. No equivale al árbol del commit por sí solo.
Se guardaron diff, status y huellas SHA-256 de 2.130 archivos de aplicación,
motor, UI, configuración, rutas, base, scripts y pruebas en almacenamiento
privado. Los informes y diagramas no forman parte de ese código ejecutable.

Durante el cierre apareció en el checkout compartido `be8cd580`, que añade
únicamente `docs/sistema-experto/CONTRATO_FUNCIONAL_PETERSEN_C01_C05_2026_10_21.md`.
Se leyó e incorporó como fuente documental adicional. Ese commit no modifica
código ni tablas. No es un commit creado por esta auditoría.

En verificaciones intermedias las 2.130 huellas coincidían. Al cierre cambiaron
cinco archivos por trabajo simultáneo ajeno a esta auditoría:

- `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/CapturaPetersenPanel.php`.
- `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto-residente.blade.php`.
- `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/perfil-instrumental.blade.php`.
- `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/petersen-institucional.blade.php`.
- `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/petersen/captura-original.blade.php`.

Sus fechas de escritura comprenden 11:19–11:26; el JUnit general terminó a
11:25:41. Por ello, la suite general es una ejecución observada en un árbol
con modificaciones simultáneas, **no un certificado de un único snapshot
inmutable ni del último checkout**. Se conserva su resultado literal y se
separa una comprobación posterior dirigida. El cambio inspeccionado añade
cobertura/etiquetas de captura y presentación; no elimina la entrada A/P/I
por dominio ni convierte los requisitos en derivaciones desde brutos.

## Ejecuciones propias

| Ejecución | Resultado al cierre de la auditoría | Qué acredita |
|---|---|---|
| Suite PHP general, SQLite `:memory:` y almacenamiento propio | **3473 tests / 63498 assertions / 9 fallos / 48 omitidos / 0 errores** | 3416 casos aprobados; FAIL global. Ejecución completa, 02:43:24.914; detalle abajo |
| Selección posterior de captura/informes | **63 tests / 608 assertions / 2 fallos / 0 omitidos / 0 errores** | 61 aprobadas; ambos fallos de presentación persisten tras los cambios simultáneos. No reemplaza la suite global |
| PostgreSQL desechable, selección dirigida | **169 tests / 1695 assertions / 0 errores / 0 fallos / 0 omitidos** | Contratos elegidos y listados abajo; no toda la aplicación |
| Frontend `npm test` | **228 totales / 226 PASS / 2 omitidas / 0 fallos** | Pruebas del repositorio, algunas con presentación sintética; no QA manual de roles |
| `npm run build` | **PASS**, Vite 8.3.1 | Compilación |
| Arquitectura aislada | **23 tests / 6133 assertions / 3 fallos** | Dos conteos desfasados y una discrepancia de expectativa visual |
| Reproducción HTTP nueva | **5 tests / 32 assertions** | Tres agregados exponen recurso sin su permiso, PDF de Enfermería sin asignación y excepción de relación con asignación |
| Reproducción pura de dosis | **4 tests / 30 assertions** | Validación de cadenas ambiguas y parsers reales/alternativos; sin persistir órdenes |
| Gate técnico inactivo / dashboard | Casos con objetos sintéticos, solo lectura | Bypass Gate interno y consultas sin permisos en helper; no ataque HTTP ni datos reales expuestos |
| Integridad local agregada | 0 cruces de los tipos comprobados; 1 residente sin admisión | Estado actual de relaciones consultadas; no todas las invariantes ni calidad clínica |
| Entrega documental | 170 enlaces locales existentes; 4 PNG inspeccionados; 120 filas del inventario; sintaxis JS válida e IDs únicos del HTML | Comprobación de archivos/estructura del informe; no sustituye prueba de interacción en navegador |

Los conjuntos se solapan; no se suman como si fueran casos independientes de
validación clínica. Un test de reproducción que pasa demuestra el defecto que
afirma; no prueba que ya esté corregido.

## Comandos y artefactos

```powershell
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --log-junit RUTA_PRIVADA/suite-sqlite.xml
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'RolesBaselineCongeladoTest|RolesVistasPermisosMatrixTest|AccesoDashboardPorRolTest|RolesClinicosV2Test|ContratoSeguridadCuentaTest|Fase3AdmisionAtomicidadTest|OcupacionCamaIntegrityTest|RevisionValoracionExpertaTest|PermisosLecturaIadlPetersenTest|CapturaInstrumentalFormularioTest|CapturaInstrumentalInstitucionalTest|PreparacionCompuestaCoherenteTest|ExploradorOrionTest'
npm test
npm run build
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never tests/Feature/Architecture
php artisan app:auditar-base-datos-remember-mind --contar-filas
```

`RUTA_PRIVADA` representa la carpeta de evidencia, no un comando listo para
copiar sin sustituirla. Para SQLite, `LARAVEL_STORAGE_PATH` apunta al storage
privado propio de la ejecución y DB es `:memory:` por `phpunit.xml`. El runner
PostgreSQL crea base/usuario/cluster exclusivos en loopback y verifica su
identidad antes del reset. Terminó y retiró su cluster; no utilizó
`remembermind_dev` como base de tests.

Artefactos locales ignorados por Git:

- `storage/app/private/auditoria-integral-20261021-3447ee72f5eb46c48f08b2211171aae0/`: log y JUnit de la suite general.
- `storage/app/private/auditoria-postgres-20261021.{xml,log}`.
- `storage/app/private/auditoria-arquitectura-20261021.{xml,log}`.
- `storage/app/private/auditoria-frontend-20261021.log` y `auditoria-build-20261021.log`.
- `storage/app/private/auditoria-20261021/`: arneses sintéticos de reproducción y JUnit.
- `storage/app/private/auditoria-integridad-local-20261021.{php,json}`: consulta agregada READ ONLY.
- `storage/app/private/auditoria-corte-20261021.json`, `auditoria-diff-20261021.patch` y `auditoria-status-20261021.txt`.

Los arneses privados no se convierten en regresiones permanentes del producto
durante este gate. La fase implementadora debe incorporarlas con expectativas
de denegación/fidelidad correctas. Los informes no adjuntan contraseñas, datos
personales, documentos clínicos, dumps o la salida completa de registros.

## Fallos de contratos localizados

Los nueve fallos observados del conjunto general son los tres de arquitectura
y los seis adicionales listados aquí. La selección de arquitectura reproduce
los mismos tres: no son doce defectos independientes.

| Test | Observado | Clasificación inicial |
|---|---|---|
| FolderOrganizationTest::test_el_contrato_ordenado_de_rutas_permanece_identico | Espera 305 rutas; hay 306 | Snapshot/contrato por conciliar. El número no demuestra ruta defectuosa ni autoriza actualizar el esperado sin revisar la entrada nueva |
| FrontendArchitectureTest::test_livewire_resuelve_vistas_del_modulo_propietario | Espera 77 componentes; hay 78 | Conteo fijo desfasado; requiere comprobar registro/propiedad antes de actualizar |
| FrontendArchitectureTest::test_paleta_oficial_se_centraliza_y_las_series_no_inventan_alertas | Espera `--rm-palette-page: #D8D2CD`; el código contiene `#F3EEE8` | Contradicción de token/test/documentos; cotejar la última dirección aprobada. No cambiar paleta ni debilitar test en una auditoría |
| InformeNeurocognitivoTest::test_pantalla_medica_no_expone_version_ids_reglas_ni_auditoria, línea 111 | El texto renderizado contiene `INFORME-LECTURA-01` en fecha/versión; el test prohíbe ese identificador | Contrato de presentación por conciliar: versión legible y trazabilidad clínica frente a identificadores técnicos. La aserción se detiene en ese token; no demuestra acceso a toda la auditoría ni a reglas reservadas |
| MisPacientesRedisenadaTest::test_ficha_rapida_muestra_acciones_en_footer_y_un_solo_drawer, línea 799 | Falta el literal `x-trap.noscroll="show"` | El modal actual usa `x-trap.inert.noscroll`. La diferencia textual no demuestra pérdida de foco. Revisar contrato de interacción y después actualizar la prueba pertinente |
| MisPacientesRedisenadaTest::test_modal_de_registro_declara_medidas_responsivas_y_foco_accesible, línea 1096 | Mismo literal de trap esperado, distinto del actual | Mismo contrato compartido; no inferir que todas las aserciones posteriores pasaron ni que se verificó teclado en navegador |
| OverlayComponentsTest::test_modal_usa_dialogo_y_no_cierra_por_fondo_por_defecto, línea 18 | Mismo literal de trap esperado | Conciliar prueba semántica de modalidad, inert, foco y cierre; no quitar protecciones para satisfacer una cadena antigua |
| OverlayComponentsTest::test_drawer_conserva_foco_y_cierre_explicito, línea 30 | Espera el trap anterior; el drawer usa `x-trap.inert.noscroll="show && (!docked || compact)"` | Diferencia de contrato para modos docked/compact. La operación real requiere QA; esta aserción de texto no la certifica ni demuestra por sí sola un defecto funcional |
| RedisenoEnfermeriaIntegralTest::test_informe_cognitivo_panel_lateral_muestra_diagnostico_medico_solo_si_autorizado_y_cuidados_pendientes, línea 540 | No aparece el diagnóstico sintético esperado en la vista inicial | La vista actual organiza diagnósticos/cuidados en la consulta por secciones (`consulta.blade.php`, `ConsultarContinuidadAsistencialExperta`). Revisar recorrido y expectativas; no declarar ausente toda la capacidad ni llamar a esto una exposición sin permiso |

Los 48 omitidos aparecen sin motivo individual en este JUnit. No se presentan
como aprobados ni se afirma que todos hayan quedado cubiertos por la selección
PostgreSQL. Su identificación y cobertura pendiente deben acompañar el cierre
de la fase implementadora. La evidencia completa está en el log/JUnit privado;
no se publica el HTML completo de los fixtures.

## Comprobación posterior por cambios simultáneos

Selección: `CapturaPetersenOriginalTest|InformeNeurocognitivoTest|RedisenoEnfermeriaIntegralTest`,
con SQLite y almacenamiento propios. Resultado: **63 pruebas / 608 aserciones /
2 fallos / 0 errores / 0 omitidos**, en 02:36.156. Persisten
`InformeNeurocognitivoTest`, línea 111 (código de versión visible), y
`RedisenoEnfermeriaIntegralTest`, línea 540 (diagnóstico esperado en la vista
inicial). Los demás 61 casos aprobaron; no se cambió producto ni tests para
lograrlo. Una mejora de presentación no cierra la dependencia de juicios
intermedios ni el objetivo de automatización.
Artefactos privados: `storage/app/private/auditoria-seguimiento-20261021/seguimiento.{xml,log}`.
Esta comprobación no reemplaza una suite completa sobre un checkout estabilizado.

No confundir estos fallos con las exposiciones/dosis encontradas por nuevos
casos: una suite de regresión puede pasar sin cubrir un defecto no previsto.

## QA y aceptación no acreditadas

Se intentó inventariar el navegador mediante la herramienta disponible.
Respondió **“Local browser use is disabled in Work settings”**. No se eludió
esa configuración ni se utilizaron capturas antiguas como evidencia actual.
Los cuatro gráficos de este informe son diagramas propios, no capturas del
sistema. Las pruebas frontend renderizadas cubren sus fixtures, no login,
navegación, permisos, POST y estados de todos los roles en la aplicación.

La matriz visual manual queda **FAIL por evidencia incompleta**, diferenciada
de defectos observados: 1440/1280/1024/768/390 px, claro/oscuro, teclado/foco,
contraste, 200% zoom, movimiento reducido, carga/vacío/error/éxito y contenido
por rol. No se inventa un fallo visual sin verlo.

No se acreditan: producción, exposición efectiva de URLs públicas en el
despliegue, cumplimiento legal, apropiación clínica de tratamientos, licencias,
desempeño diagnóstico, restauración completa de respaldos ni concurrencia de
todos los procesos. La lectura de documentación no sustituye esas pruebas.

El dictamen **NO LISTO / FAIL integral** se mantiene aunque una selección
técnica termine verde. La auditoría termina con hallazgos, evidencia y plan;
la reparación y aceptación institucional son etapas posteriores.
