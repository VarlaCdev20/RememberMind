# Resultados de Enfermería y acceso desde Psicología

22/10/2026. Solicitud: «la vista del sistema experto actualízala en enfermería y añade el botón en psicología».

## Alcance y recorrido

Se integra en el árbol local la presentación de Enfermería publicada en `c512e760`, adaptando el resumen a la solicitud de resultado destacado. Se preservan los cambios simultáneos; esto no equivale a actualizar íntegramente la rama mediante pull.

- **Enfermería:** Mis residentes → ficha del residente → Evaluación cognitiva y cuidados. Contexto del residente, resultado destacado, fuentes y fundamento, registros cognitivos y continuidad de cuidados. La información extensa de las áreas y su gráfico permanece accesible mediante el desplegable «Áreas cognitivas, resultado y fundamento disponibles».
- **Psicología:** Inicio → «Abrir sistema experto» → seleccionar residente → resultados. Se añade también «Sistema experto» al menú lateral. La ficha psicológica conserva su acceso individual al informe.

No se modifican el motor, las conclusiones, los diagnósticos, el esquema ni los permisos. No se transforman observaciones en mediciones ni se afirma que una consulta esté conservada si no lo está.

## Presentación y estados

La tarjeta principal ocupa una fila completa. Su título utiliza el tamaño de página y la tipografía del Design System. Los registros y cuidados se muestran en dos columnas; en móvil pasan a una columna. La navegación Resultado/Registros/Cuidados, los estados textuales y el contexto del residente permanecen disponibles. No se presentan códigos internos como identidad visual.

El resumen diferencia falta de resultado, registros no disponibles y falta de permiso sobre cuidados. La ausencia de cuidados pendientes no se interpreta como ausencia de necesidades. Las advertencias técnicas instrumentales permanecen visibles fuera del detalle plegado. No se generan nuevas recomendaciones clínicas.

Los botones reutilizan acciones existentes: significado, fuentes, consulta del cuidado, ficha y agenda. El despliegue nativo del detalle permite acceso por teclado; no se incorpora un modal ni un dashboard alternativos.

El botón del inicio de Psicología requiere rol profesional, personal activo, permiso de lectura de residentes y ausencia de previsualización de rol. El menú reutiliza la visibilidad de navegación. Las rutas y Policies del backend siguen siendo la autoridad: un enlace visible no amplía el acceso.

## Archivos

| Archivo | Cambio |
|---|---|
| `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/enfermeria.blade.php` | Resumen operativo y detalle progresivo; advertencia técnica visible. |
| `app/Modulos/ApoyoDecision/Presentacion/Vistas/livewire/medico/resultados-experto/enfermeria-resumen.blade.php` | Resultado destacado, registros y cuidados con sus accesos existentes. |
| `resources/frontend/styles/design-system/patterns/expert-results.css` | Estilos de lectura publicados, distribución adaptable y título del resultado con tokens existentes. |
| `app/Modulos/Valoraciones/Presentacion/Vistas/livewire/valoraciones/dashboard-psicologo.blade.php` | Botón de acceso desde Inicio de Psicología. |
| `app/Modulos/Identidad/Presentacion/Navegacion/SidebarService.php` | Acceso lateral para Psicología activa y autorizada. |
| `tests/Feature/NavegacionSistemaExpertoTest.php` | Recorrido y denegaciones por inactividad y permiso revocado. |
| `tests/Feature/InformeCognitivoTest.php` | Renderizado de Enfermería, resumen y revocación contextual. |

## Pruebas ejecutadas

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter 'test_enfermeria_asignada_consulta_informe_y_pierde_acceso_al_revocar_asignacion|NavegacionSistemaExpertoTest|PsychologyDashboardDataTest'
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'test_enfermeria_asignada_consulta_informe_y_pierde_acceso_al_revocar_asignacion|NavegacionSistemaExpertoTest|PsychologyDashboardDataTest'
node --test tests/Frontend/cognitive-responsive.test.js tests/Frontend/psychology-dashboard-responsive.test.js tests/Frontend/psychology-results-responsive.test.js
npm run build
```

- SQLite `:memory:`: **9 pruebas, 94 aserciones, 0 fallos**.
- PostgreSQL desechable: **9 pruebas, 94 aserciones, 0 fallos**.
- Frontend: **6 pruebas, 0 fallos u omisiones**.
- Build: Vite 8.3.1, **108 módulos**, satisfactorio en 11,42 segundos.
- `git diff --check` del alcance y sintaxis de `SidebarService`: correctos.

La primera ejecución detectó una expectativa anterior: cerrar una jornada bloqueaba la lectura. El servicio de turnos actual permite lectura propia fuera de turno, incluidas jornadas cerradas de hoy. La prueba ahora comprueba que esa lectura sigue disponible y que **anular la asignación retira el acceso**. No se cambió el servicio ni la Policy para obtener el resultado. Las pruebas conservan la denegación por permiso revocado y profesional inactivo.

## Verificación de la vista

Se exportó el HTML Blade real generado por el test de Enfermería, usando exclusivamente el residente y la evaluación sintéticos de PHPUnit. Con el CSS construido y Chrome headless se comprobaron **1440, 1280, 1024, 768 y 390 px**, en claro y oscuro: diez combinaciones sin desbordamiento horizontal, resultado a ancho completo, título legible y controles de al menos 44 px. Se verificó también el detalle abierto y cerrado y se inspeccionaron capturas de escritorio y móvil.

Esta evidencia cubre el contenido de la vista en aislamiento. No equivale a inspección de la sesión clínica abierta ni a una auditoría completa de accesibilidad. El navegador interactivo de Work devuelve «Local browser use is disabled in Work settings»; no se afirma un recorrido visual de esa sesión. Las acciones del componente y la autorización se comprobaron mediante Livewire y HTTP.

Los JUnit y logs de esta ejecución permanecen bajo `storage/logs/`; el HTML, las capturas y la matriz de renderizado se mantienen en un directorio temporal de QA, sin registros institucionales. No se copian credenciales, sesiones operativas ni la base institucional.

Sin migraciones, cargas de datos, push ni despliegue en esta tarea. La conservación conjunta SQL y la validación clínica conservan sus pendientes previos.
