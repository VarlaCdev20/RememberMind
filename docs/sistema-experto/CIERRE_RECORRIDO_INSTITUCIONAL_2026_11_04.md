---
title: Resultado institucional conservado e integración de revisiones
status: CURRENT
last_reviewed: 2026-11-04
owner: ApoyoDecision
source_of_truth: false
runtime_verified: true
verification_scope: [sqlite, postgresql, concurrency, browser, installed_schema]
---

# Alcance de esta entrega

Se incorpora a las pantallas normales de RememberMind el recorrido **captura original → análisis → guardado del resultado → lectura SQL verificada → historial → revisión independiente de Medicina/Psicología**. Se reutilizan los dos evaluadores y los formularios existentes. No se modifica la interpretación clínica, no se introduce otro motor y no se fabrican mediciones.

La autorización específica es [la decisión del04/11](../base-de-datos/DECISION_RESULTADO_INSTITUCIONAL_2026_11_04.md). Sucede al rechazo del31/10 únicamente para las19 columnas, cinco FK/CHECK/índices y el permiso descritos allí.

**Límite explícito:** se conserva `RESULTADO-INSTITUCIONAL-1`, no el DTO completo `EVALUACION-INSTITUCIONAL-1`. El resultado SQL acredita `persistido=true` solo tras recuperar y verificar su huella. El DTO completo continúa declarando `persistido=false`; no se anuncia replay completo del conocimiento, adquisición y ejecutable.

## Recorrido visible

1. En la captura original, guardar muestra una confirmación legible y permite **Continuar al análisis** del mismo residente. Una captura pendiente debe guardarse o cancelarse antes de continuar. Guardar la fuente no crea una evaluación automática.
2. En las vistas de resultados de Medicina/Psicología aparece **Evaluar y guardar resultado**, si cuenta, competencia, permisos y residente lo permiten. La operación obtiene de nuevo las fuentes en el backend; no guarda un resultado remitido por el navegador.
3. Tras confirmar la transacción, se abre la evaluación recuperada desde SQL. Su dirección conserva la selección mediante `resultadoGuardado`. Recargar conserva esa selección y no ejecuta otra evaluación.
4. La pantalla histórica presenta resultado grande, cinco dominios, requisitos, explicación original, hechos y reglas/condiciones conservados. Sustituye la consulta actual mientras está seleccionada, evitando mezclar resultados de fechas diferentes.
5. **Volver a la consulta actual** permite otra ejecución. Las solicitudes repetidas mantienen el UUID y recuperan la misma evaluación; volver a la consulta permite iniciar una nueva solicitud.
6. El historial se pagina de diez en diez, sin descartar evaluaciones antiguas por un límite fijo. Abrir una evaluación comprueba otra vez residente, acceso e integridad.
7. Medicina y Psicología pueden registrar su revisión en cualquier orden. Cada disciplina conserva su cadena, autor, fecha y justificación. No se altera el resultado al confirmar o discrepar. Enfermería consulta dentro de su asignación; no obtiene ejecución ni firma clínica por leer este apartado.

## Conservación y recuperación

| Elemento | Destino existente | Garantía de este contrato |
|---|---|---|
| Resultado, dominio/requisito, patrón nullable y explicación | evaluaciones_expertas,16 columnas nuevas | Estados emitidos; C02 derivado; negación e insuficiencia distintas. |
| Actor, profesional, versiones, UUID, corte e identidad técnica | Cabecera y campos de conservación existentes/ampliados | Autor de sesión, idempotencia y fechas tipadas. |
| Valores originales de aplicación, respuestas y juicios | hechos_instantanea_experta,tres referencias nuevas | FK excluyentes, valores tipados, NULL, autor, fecha y orden. |
| Evaluación de criterios y trazas | evaluacion_criterios y bloque de traza consolidado | Solo nodos reales de las versiones ejecutadas; Petersen usa su criterio real. |
| Reglas/condiciones | evaluaciones_reglas y evaluaciones_condiciones_regla | Estados realmente evaluados; no se deducen de la regla vigente al leer. |
| Evidencia instrumental mapeada y soportes de condición | evidencias_evaluacion, evidencias_criterio_evaluacion, evidencias_soporte_condicion | Solo mapeos/registros auténticos que entrega el motor; no se simulan para insertar juicios. |
| Revisiones | revisiones_evaluacion_experta | Dos cadenas independientes sobre la evaluación conservada. |

El lector reconstruye un contenido canónico tipado desde las tablas expertas, con orden explícito, y comprueba su SHA-256. No usa mediciones clínicas actuales para completar valores históricos. Las denominaciones de criterios/reglas se consultan aparte en los catálogos referenciados y tienen orden explícito; no forman parte del contenido sellado ni se presentan como una copia íntegra del conocimiento original.

La escritura está en una transacción; un fallo en trazas revierte la cabecera y sus hijos. El cierre inicial solo reemplaza la huella provisional dentro de esa misma transacción. Los modelos/API ordinarios impiden sobrescritura; una corrupción SQL directa se detecta antes de mostrar o revisar el resultado. Esto no se presenta como un almacenamiento WORM que impida toda intervención del propietario de la BDD.

No se conservan íntegros todos los modelos internos, catálogos, colecciones, membresías de adquisición y release ejecutable del DTO completo. No se promete reconstruir esos elementos desde la narrativa. El objetivo de esta autorización es recuperar el resultado emitido, sus hechos, decisiones y explicación, manteniendo separada la reproducción completa del motor.

## Instalación efectiva

- Base configurada de esta estación: remembermind_dev, PostgreSQL. No se desplegó otro servidor.
- Se instaló la extensión explícita `2026_11_04_000001_conservar_resultado_institucional`, registrada en migrations y fuera del descubrimiento automático.
- Inventario comprobado: **106 tablas de negocio +16 técnicas =122 físicas**, sin tablas nuevas.
- Se compararon todas las columnas anteriores de las8 evaluaciones y809 hechos existentes: contenido y cantidades idénticos después del DDL. Los campos nuevos de esas filas quedaron NULL.
- Cinco FK nuevas RESTRICT e indexadas, comprobadas mediante metadatos después de instalar.
- Respaldo PostgreSQL en almacenamiento privado; tamaño, SHA-256 y catálogo de restauración comprobados. Su ruta está en la evidencia privada de instalación; no contiene una exportación pública de los registros.
- La reversión se rechaza si hay resultados o referencias nuevos poblados. En base desechable se comprobó aplicar/revertir preservando historia previa y sin crear tablas.

La verificación posterior detectó que el listener general de creación de permisos concedía el permiso nuevo a Superadministración. Se corrigió su exclusión y el seeder específico; se retiró exclusivamente esa concesión y se comprobó que quedan **Medicina y Psicología**. El caso de creación tardía y el reintento tienen regresión dedicada. No se cambiaron los restantes permisos ni cuentas.

Evidencias: `output/resultado-institucional-20261104-instalacion.json` y `output/resultado-institucional-20261104-verificacion.json`. Son metadatos, conteos y huellas; el respaldo clínico permanece privado.

## Pruebas y comandos

Las pruebas usan registros sintéticos en bases desechables. No se reinició ni sembró la base institucional.

```powershell
./scripts/probar-sqlite.ps1 -Filtro 'ResultadoInstitucionalConservadoTest|test_caso_completo_se_clasifica_desde_mediciones_y_soportes_originales|EjecucionConsultaCognitivaTest|CircuitoInstrumentalInicialTest|GobiernoPetersenInstitucionalTest|PetersenInstitucionalTest|test_continuacion_' -Informe output/resultado-institucional-20261104-sqlite-final.xml -Log output/resultado-institucional-20261104-sqlite-final.txt

./scripts/probar-postgres.ps1 -Filtro 'ResultadoInstitucionalConservadoTest|test_caso_completo_se_clasifica_desde_mediciones_y_soportes_originales' -Informe output/resultado-institucional-20261104-postgres-final.xml -Log output/resultado-institucional-20261104-postgres-final.txt

$env:RM_EXPERTO_E2E='1'
$env:RM_EXPERTO_FASE='conservacion-institucional'
node --test tests/Frontend/experto-workspace-e2e.test.js

npm run build
```

Resultados de la regresión principal: **SQLite108 pruebas/572 aserciones**, **PostgreSQL19/170**, satisfactorios. La corrección del orden de las denominaciones después de archivar reglas se repitió en SQLite: **4/88**, satisfactoria. Las ampliaciones finales de permisos e historial tienen informes separados; no se suman como si fueran casos únicos ajenos a esas suites.

Regresiones finales: exclusividad/creación tardía del permiso y formulario de guardado/revisión, **3 pruebas/27 aserciones en cada motor** (`resultado-institucional-20261104-permiso-{sqlite,postgres}.{xml,txt}`); paginación sin pérdida y revisión por URL, **2/20 en cada motor** (`resultado-institucional-20261104-historial-{sqlite,postgres}.{xml,txt}`). Todos satisfactorios. El navegador de cierre pasó de nuevo, con ambas revisiones y recarga (`resultado-institucional-20261104-e2e-cierre.txt`).

El runner PostgreSQL inicia un clúster propio en loopback, con usuario sin privilegios globales, y lo detiene/limpia al terminar. Se corrigió la espera de PowerShell para esperar solo pg_ctl y no el árbol del servidor. La carrera de dos procesos obtiene la misma evaluación y huella con una sola cabecera y seis criterios.

La prueba de navegador recorre guardar, URL histórica, recarga, revisión médica, revisión psicológica y conservación de ambas. Capturas y matriz en `output/experto-conservacion-institucional-*`:1440,1280,1024,768 y390px, sin desbordamiento horizontal ni errores JavaScript. El build es satisfactorio; mantiene el aviso previo sobre tamaño de bundles. Estas comprobaciones no acreditan una auditoría completa de WCAG ni validación clínica.

Comprobación posterior del código:21 archivos PHP sin errores de sintaxis y `git diff --check` satisfactorio en el alcance. Build final en `output/resultado-institucional-20261104-build-cierre.txt`. Las revisiones de seguridad y BDD se cerraron para este alcance después de corregir la concesión automática del permiso: no son una certificación independiente de todos los módulos. Los estados de Git y el diff estadístico del árbol compartido se conservaron en `output/resultado-institucional-20261104-git-status.txt` y `output/resultado-institucional-20261104-git-diff-stat.txt`; incluyen cambios previos ajenos y no se usaron para preparar un commit.

## Matriz de aceptación

| Requisito | Archivo principal | Prueba/evidencia |
|---|---|---|
| Captura → análisis del mismo residente | CapturaPetersenPanel; captura-original.blade.php | CapturaPetersenOriginalTest,continuación médica/psicológica y captura pendiente. |
| Adquisición original y ejecución real | GuardarResultadoInstitucional; PrepararEvaluacionInstitucional | Cuatro casos completos de CapturaPetersenOriginalTest y abstención con información ausente. |
| Persistencia atómica y recuperación tipada | ConservarResultadoInstitucional; ResultadoInstitucionalSQL | ResultadoInstitucionalConservadoTest,igualdad de contenido/huella,sin lectura de medidas actuales y rollback. |
| UUID y carrera | GuardarResultadoInstitucional | Reintento y dos procesos PostgreSQL,una cabecera. |
| Fuentes originales y valores distintos | HechoInstantaneaExperta; extensión DDL | NULL,booleanos,negación original y exclusión por falta de soportes. |
| Historial sin recorte y selección estable | ConsultarResultadoInstitucional; ResultadosExpertoResidente | Paginación,recuperación por URL y navegador tras recarga. |
| Revisiones separadas | ValoracionRevisable; RevisarValoracionExperta | Dos decisiones independientes,en ambos órdenes,sin cambio del resultado. |
| Competencia y permiso específico | EvaluacionExpertaPolicy; seeder específico; AppServiceProvider | Falta de permiso,personal inactivo,Superadministración y creación tardía. |
| Integridad e IDOR | ConsultarResultadoInstitucional | Otro residente denegado; corrupción produce error y no admite revisión. |
| Esquema y conservación previa | Extensión explícita e instalador | FK RESTRICT,reversión poblada,inventario122 y hashes anteriores iguales. |

## Archivos del alcance

Rutas desde la raíz del repositorio:

- `app/Modulos/ApoyoDecision/Aplicacion/Contratos/ResultadoInstitucionalConservado.php`: contrato de resultado distinto del DTO completo.
- `app/Modulos/ApoyoDecision/Aplicacion/Acciones/GuardarResultadoInstitucional.php`: operación autorizada,UUID,transacción,cierre y comprobación.
- `app/Modulos/ApoyoDecision/Aplicacion/Consultas/ConsultarResultadoInstitucional.php`: lectura autorizada e historial paginado.
- `app/Modulos/ApoyoDecision/Infraestructura/Persistencia/ConservarResultadoInstitucional.php`: proyección nominal de resultados,hechos y trazas.
- `app/Modulos/ApoyoDecision/Infraestructura/Persistencia/ResultadoInstitucionalSQL.php`: lectura canónica e integridad sin inferencia.
- `app/Modulos/ApoyoDecision/Aplicacion/Consultas/ValoracionRevisable.php`: admisión del contrato guardado a revisiones existentes.
- `app/Modulos/ApoyoDecision/Presentacion/Policies/EvaluacionExpertaPolicy.php`: competencia y permiso explícito de ejecución.
- `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ResultadosExpertoResidente.php`: guardar,seleccionar historial,paginar y preparar revisión.
- `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/CapturaPetersenPanel.php`: continuación autorizada,confirmación sin códigos y limpieza de confirmación anterior.
- Vistas en `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/`: historia-institucional,resultado-institucional y revisión existente; conexión en resultados-experto-residente.blade.php y captura-original.blade.php.
- `app/Modulos/ApoyoDecision/Presentacion/Soporte/ResultadoInstitucionalVisible.php`: lenguaje de pantalla,sin modificar originales.
- `app/Models/EvaluacionExperta.php` y `HechoInstantaneaExperta.php`: relaciones originales nuevas y separación del lector D137 anterior.
- `app/Providers/AppServiceProvider.php`: exclusión del permiso nuevo en la concesión automática a Superadministración.
- `database/propuestas/experto/2026_11_04_000001_conservar_resultado_institucional.php`: exactamente19 columnas,cinco FK/índices y CHECK.
- `database/seeders/PermisoEjecucionInstitucionalSeeder.php` y RolesAndPermissionsSeeder: concesión exclusiva e idempotente del permiso adoptado.
- `scripts/instalar-resultado-institucional.php`: instalación explícita con respaldo y comparación; `scripts/probar-postgres.ps1`: runner desechable corregido.
- `tests/Feature/ResultadoInstitucionalConservadoTest.php`,CapturaPetersenOriginalTest,`tests/Support/resultado_institucional_worker.php`,ServidorExpertoSintetico y `tests/Frontend/experto-workspace-e2e.test.js`: pruebas e integración.

## Límites y estado del producto

**Implementado y probado:** resultado conjunto conservado,consulta histórica,paginación,traza del contrato,revisiones independientes,autorización e instalación sin nuevas tablas. Los cinco dominios y Petersen siguen ejecutándose con el motor existente. La insuficiencia sigue produciendo abstención; no se fuerza un perfil.

**Pendiente de mediciones originales cuando falten en un expediente:** tareas,respuestas,condiciones,interpretaciones de dominio y soportes funcionales/diferenciales suficientes. Los registros observacionales no se convierten en instrumentos por habilitar el guardado.

**Fuera de esta autorización:** conservar y reproducir íntegramente todos los modelos,membresías,configuración y ejecutor del DTO completo. No queda resuelto ni se oculta bajo una bandera true.

**Evaluación posterior en uso real:** no se presenta como realizada mediante pruebas sintéticas ni se inventan firmas. Conforme a la orden del30/10,no se exige un aval externo para desarrollar o ejecutar este recorrido técnico autorizado.

No se ejecutaron commit,push,cambio de rama ni despliegue. Se preservó el árbol compartido; los cambios previos de Gerencia,Cuidados,Alertas,Reportes y otras tareas no se incorporaron a una operación Git.
