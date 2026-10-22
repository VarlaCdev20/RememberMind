# Publicación e integración de Alertas

## Autorización y destino

El 22/10/2026 el propietario autorizó expresamente commit, push e integración de este trabajo en la rama oficial `feature/apoyo-decision-informe-neurocognitivo`. Esta instrucción sustituye la restricción de Git vigente durante los cortes locales anteriores; sus documentos describen el estado de aquel momento.

Origen: `feature/alertas-operacion-roles`. Destino confirmado después de consultar el remoto: `feature/apoyo-decision-informe-neurocognitivo`, que contiene avances posteriores al punto común. Se conserva el historial de ambas ramas sin force push ni rebase del trabajo compartido. No se integra a `develop` ni a `main` en esta operación.

## Contenido

Se publica la entrega acumulada de Alertas: flujos por especialidad, formularios con autoría y bloqueos, búsqueda al escribir, vínculos profesionales verificables, consulta del origen, mapas ajustables, lenguaje visible, integración en navegación/expediente/campana y pruebas. Incluye la corrección de la relación asignación → jornada → turno de Personal institucional que bloqueaba la revisión solicitada.

El seeder oficial contiene la concesión de `alertas.ver` a Nutrición, Fisioterapia y Pedagogía, ya aprobada por el propietario en la sesión. Publicar el archivo no ejecuta el seeder ni modifica la base de quien descarga la rama. Esta operación de Git no migra, siembra ni modifica datos institucionales.

Se excluyen `output/alertas-20261021/`, logs, capturas, volcados, credenciales y datos institucionales. Los artefactos locales de comprobación permanecen disponibles en el equipo.

## Verificación para publicar

- Sintaxis PHP correcta en los 35 archivos PHP inicialmente preparados y en los tres archivos de pruebas adicionales corregidos (sin contar Blade).
- Pint y diff sin errores en el alcance revisado.
- Build de la entrega: 111 módulos, 9,40 s; PASS.
- Pruebas específicas recientes de Alertas y PostgreSQL desechable: resultados detallados en los documentos de vínculos y mapas de esta carpeta.
- Selección ampliada frontend: 236 pruebas; 233 aprobadas, 1 fallida y 2 omitidas por sus condiciones anteriores. El fallo está en `shell-visual-consistency.test.js`: el campo enfocado produce `rgb(231, 227, 223)` frente a `rgb(231, 226, 222)` esperado. La prueba y las reglas globales de input/foco ya existen sin cambios en `HEAD`; el trabajo de Alertas no redefine el campo del fixture global. Se conserva como discrepancia ajena al alcance y no se declara la suite completa aprobada.
- La verificación de producto en navegador sigue pendiente: las pruebas de fixtures aislados no sustituyen la sesión institucional ni acreditan las nuevas composiciones de Alertas en cinco anchos y ambos temas.

SQLite ampliado: 121 casos seleccionados; 110 aprobados en la ejecución inicial, diez expectativas anteriores fallidas y una prueba de concurrencia PostgreSQL omitida por requerir ese motor. Se actualizaron únicamente las expectativas/fixtures: nombres visibles, selección explícita de todas las especialidades al probar el ámbito completo, relación profesional del caso, campos consultables pero deshabilitados, permisos revocados explícitamente para los negativos y consulta periódica visible de 60 segundos. Los diez casos corregidos pasaron al repetirse: 10 pruebas / 200 aserciones. Resultado por cortes: 120 casos aprobados y uno pendiente de su runner PostgreSQL, sin fallo de Alertas sin resolver. Evidencias: `publicacion-sqlite.xml` y `publicacion-corregidas.xml`.

La verificación posterior al merge se conserva por separado en los artefactos locales de integración y en el reporte de entrega. La publicación de código no equivale a aceptación clínica ni despliegue.
