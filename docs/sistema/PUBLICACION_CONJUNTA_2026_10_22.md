# Publicación conjunta para sincronización del equipo — 22/10/2026

El propietario solicitó reunir los commits y cambios locales en
`feature/apoyo-decision-informe-neurocognitivo` y publicarlos en `origin`.
Esta instrucción sustituye la rama separada de trabajo de Valoraciones para la
entrega conjunta. No se modifica main ni se integra una release.

## Contenido reunido

- Se conserva el historial remoto existente hasta `f855ccdf`.
- Se incorpora el commit `5b3d4a13` de explorador, guías, avatar y captura
  institucional de Valoraciones. Se conserva su autoría e historial sin reescribirlo.
- Se incorporan los cambios locales de presentación de Alertas por especialidad,
  inspector y continuidad; las vistas de lectura por rol, gobierno del conocimiento,
  catálogo y red navegable; sus pruebas y documentación.
- No se incluyen `output/`, capturas temporales, HTML de sesión, logs, JUnit,
  credenciales, `.env`, bases de datos ni recursos privados de la instalación.
- No se ejecutan seeders ni migraciones contra la base instalada. No hay cambios
  de esquema ni ampliación de permisos para esta publicación.

La documentación anterior de cada tarea describe su estado al redactarse.
Este registro consolida la solicitud posterior de publicar conjuntamente;
no transforma las comprobaciones pendientes en aceptación visual o clínica.

## Verificaciones de esta integración

- `git fetch origin --prune`: ejecutado; se comprobó la relación de ancestros
  con la rama destino antes de moverla. No se usa force push.
- `git diff --check`: correcto.
- `node --test tests/Frontend/alertas-trazabilidad.test.js tests/Frontend/orion-explorador.test.js tests/Frontend/valoraciones-explorador.test.js tests/Frontend/controles-institucionales.test.js`:
  30 pruebas correctas, ninguna fallida ni omitida.
- `npm run build`: correcto, 38,48 s; aviso de tamaño de chunks superior a
  500 kB sin fallo de compilación.
- La suite frontend completa secuencial se interrumpió porque no terminaba
  después de emitir resultados parciales. No se declara como aprobada.
- Pint de los PHP modificados: correcto salvo el estilo preexistente de
  `tests/Feature/InformeCognitivoTest.php`; se reprodujeron los mismos cinco
  tipos de correcciones solicitadas sobre su versión de HEAD. No se reforma
  todo el archivo para cambiar una etiqueta de presentación.
- La corrida PHP amplia con filtro de siete suites no terminó y se interrumpió;
  no hay resultado de aceptación de esa corrida.
- `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Unit/ComponerVistaCasoAlertaTest.php tests/Unit/ProyectarRedConocimientoTest.php`:
  8 pruebas correctas, 131 assertions. Comprueba proyección, fuentes, estados,
  rechazo de relaciones inválidas y ausencia de datos inventados.
- `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/AlertasFlujosPorRolTest.php`:
  4 pruebas correctas, 256 assertions.
- `php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/LaboratorioExpertoFrontendTest.php --filter='test_red_filtra_sin_fabricar_nodos_y_catalogo_abre_detalle|test_resumen_abre_catalogos_y_red_con_estado_controlado'`:
  2 pruebas correctas, 13 assertions.
- Total PHP acotado de esta publicación: 14 pruebas, 400 assertions, sin fallos.
  No equivale a una ejecución completa de todas las suites.

La evidencia local se conserva bajo `output/publicacion-*` y se excluye de Git.
Los resultados previos de Valoraciones (PostgreSQL y SQLite) y las limitaciones
visuales están detallados en su informe de implementación.

## Commits de integración

- `5b3d4a13`: Valoraciones, explorador, guías y captura institucional.
- `622c3d83`: representaciones y consulta de Alertas por especialidad.
- `d1fa2fa5`: consulta por rol y explorador del conocimiento.
- El commit documental de esta publicación conserva este registro y los pasos
  para el otro equipo. El resultado del push y el hash remoto se verifican después
  de crearlo; no se declara una publicación por el mero commit local.

## Actualización en el otro equipo

Primero conservar cualquier trabajo local de ese equipo. Con el checkout limpio:

```powershell
git fetch origin
git switch feature/apoyo-decision-informe-neurocognitivo
git pull --ff-only origin feature/apoyo-decision-informe-neurocognitivo
composer install
npm ci
npm run build
php artisan view:clear
```

Si la rama todavía no existe localmente:

```powershell
git fetch origin
git switch --track origin/feature/apoyo-decision-informe-neurocognitivo
```

`fetch` descarga los commits; `pull --ff-only` actualiza el checkout sin crear
una fusión implícita. Si se rechaza por divergencia, conservar los commits locales
y revisar su integración; no usar reset destructivo ni forzar el remoto.

No copiar `.env` de otro equipo ni reiniciar la base para actualizar esta interfaz.
La carga sintética existente, si se solicita por separado, mantiene su runner
guardado y su política de respaldo. No forma parte de estos pasos de actualización.
