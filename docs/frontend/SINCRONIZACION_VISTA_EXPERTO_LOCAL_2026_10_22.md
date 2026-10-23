# Vista general del sistema experto: sincronización local

22/10/2026. Incidencia comunicada: «no veo cambios en la vista del sistema experto».

## Causa comprobada

La entrega anterior actualizó el informe específico de Enfermería y el acceso desde Psicología. Las vistas generales de resultados, listado y conocimiento seguían en la presentación anterior. El commit remoto `c512e760` estaba disponible en una copia aislada; el pull de la carpeta principal se había detenido para preservar sus modificaciones locales.

Se comprobó que el servidor activo del puerto 8000 usa `C:/Users/CARLAENCINAS/Desktop/RememberMind` y que el Vite del puerto 5173 también sirve ese repositorio. El problema no se resolvía únicamente reconstruyendo CSS: faltaba incorporar los archivos de presentación publicados.

## Corrección aplicada

Se aplicó la diferencia de presentación `f855ccdf → c512e760` sobre quince archivos concretos, después de respaldar sus versiones locales en un directorio temporal privado y comprobar `git apply --check`. No se actualizó toda la rama ni se descartaron cambios simultáneos. Se conservaron la distribución nueva de Enfermería y los botones añadidos a Psicología.

| Superficie | Resultado visible |
|---|---|
| `/admin/sistema-experto/residentes` | «Resultados del sistema experto», tarjetas/tabla adaptables y «Ver resultados» como acción principal; ficha psicológica como acción secundaria. |
| Informes de Medicina/Psicología | Navegación a residentes, área seleccionada, evidencias y fuentes, e historial cuando existe evaluación conservada. |
| Áreas cognitivas | Selección de las cinco áreas y modal de detalle. Se conservan nombres y estados humanos; no se muestran los códigos COG como etiqueta clínica. |
| `/admin/sistema-experto` | Entrada de gobierno del conocimiento, catálogo con detalle y red semántica navegable con filtros e inspector, conforme al acceso técnico vigente. |

## Archivos incorporados de la publicación

- `app/Modulos/ApoyoDecision/Aplicacion/DTOs/ProyectarRedConocimiento.php`.
- `app/Modulos/ApoyoDecision/Presentacion/Livewire/Paginas/ConocimientoPanel.php`.
- Vistas `livewire/experto/residentes.blade.php`, `livewire/medico/resultados-experto-residente.blade.php`, parciales `consulta.blade.php` y `dominios.blade.php` del módulo ApoyoDecision.
- Vistas `conocimiento-panel.blade.php`, `explorador.blade.php`, `gobierno.blade.php` y `red-semantica.blade.php` de `livewire/superadministrador/sistema-experto`.
- `resources/frontend/scripts/modules/orion-explorador.js`.
- `resources/frontend/styles/design-system/patterns/expert-laboratory.css`.
- Pruebas `ProyectarRedConocimientoTest.php`, `LaboratorioExpertoFrontendTest.php` y `orion-explorador.test.js`.

Se ajustaron además los textos de aceptación de `InformeCognitivoTest` a las etiquetas publicadas y se comprobó el encabezado nuevo del listado en `NavegacionSistemaExpertoTest`.

## Comprobación

Filtro PHP ejecutado en SQLite `:memory:` y PostgreSQL desechable:

```text
NavegacionSistemaExpertoTest|test_resultados_de_memoria_conservan_codigo_y_alcance|test_ruta_real_sin_ejecutar_automaticamente_ni_publicar_metricas|test_red_filtra_sin_fabricar_nodos_y_catalogo_abre_detalle|test_resumen_abre_catalogos_y_red_con_estado_controlado|ProyectarRedConocimientoTest
```

Comandos:

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter '<filtro anterior>'
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro '<filtro anterior>'
node --test tests/Frontend/orion-explorador.test.js tests/Frontend/cognitive-responsive.test.js tests/Frontend/psychology-results-responsive.test.js
npm run build
php artisan view:clear
```

- SQLite: **16 pruebas, 207 aserciones, sin fallos**.
- PostgreSQL: **16 pruebas, 207 aserciones, sin fallos**.
- Frontend: **9 pruebas, sin fallos ni omisiones**.
- Build: **108 módulos**, satisfactorio en 5,98 segundos.
- Sintaxis de los dos PHP de aplicación incorporados y diff check del alcance: correctos.
- HTTP local: `/login` responde 200 y referencia el Vite activo; la URL de estilos del explorador responde 200 y contiene los estilos nuevos.

La primera corrida de PHP falló en tres variantes por esperar la etiqueta anterior «Cobertura de las cinco áreas cognitivas» en Medicina. Se incorporó la expectativa publicada «Disponibilidad por dominio / Áreas cognitivas» y se repitió el filtro completo en ambos motores, sin fallos. No se modificó la inferencia para ajustar una prueba.

Los JUnit y logs se conservan bajo `storage/logs/experto-vista-publicada-*`. La comprobación de rutas se realiza con fixtures sintéticos, sin tomar una sesión institucional ni crear accesos de respaldo. No se afirma inspección visual de la sesión abierta del navegador de Work.

No hubo cambios de BDD, permisos, reglas clínicas, datos de residentes, commit, push ni despliegue. La conservación conjunta SQL y la validación clínica mantienen sus pendientes anteriores.
