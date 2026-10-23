# Publicación integrada del sistema experto y cohorte local

22/10/2026. Solicitud expresa de la propietaria: «SUBE EL COOMIT Y AS PUSH», seguida de «CONTINUA».

## Alcance seleccionado

Se integra la cohorte local optativa de treinta residentes ficticios, sus nombres naturales, captura y recuperación de fuentes, consultas instrumentales/Petersen existentes, vistas de resultados y navegación de Psicología, junto con sus dependencias de conservación ya autorizadas. El motor existente se conserva. La consolidación de cuatro bloques expertos se incluye como dependencia instalada previamente y documentada; no se ejecutaron migraciones ni cargas sobre bases institucionales durante esta publicación.

La procedencia sintética permanece explícita. No se publican respaldos, archivos privados de storage, .env, credenciales ni registros clínicos de residentes anteriores. Los ensayos alternativos de conservación conjunta y el trabajo simultáneo ajeno no se incorporan al paquete.

## Integración Git

La rama `feature/apoyo-decision-informe-neurocognitivo` tenía un commit local y seis remotos sin integrar. Se preparó una copia Git aislada que conserva ambos historiales mediante merge, sin reset, stash, rebase ni push forzado. El árbol original se mantiene con sus modificaciones y archivos sin seguimiento.

Se resolvieron los solapamientos del dashboard y el menú de Psicología conservando la bandeja contextual de alertas del remoto. Las expectativas de navegación se actualizaron para las rutas de alertas por especialidad existentes; Familiar conserva únicamente su acceso de inicio.

## Verificación del contenido preparado

| Entorno | Casos distintos comprobados | Aserciones | Fallos pendientes |
|---|---:|---:|---:|
| SQLite `:memory:` y unitarias sin BDD | 217 | 3499 | 0 |
| PostgreSQL desechable | 131 | 3165 | 0 |
| Frontend con Puppeteer | 10 | No contabilizadas como PHPUnit | 0 |

Los totales backend consolidan el último resultado de cada caso de varias ejecuciones y reejecuciones; **no representan una única suite completa ejecutada desde cero**. Se conservan los JUnit originales, incluidos fallos de integración ya corregidos, y [el consolidado por caso](../auditorias/publicacion-integrada-2026-10-22/resultados-consolidados.json). Dos nombres antiguos de pruebas de menú se excluyen del conteo porque fueron renombrados para describir el acceso a alertas de especialidad.

Comandos backend sobre la copia aislada, sin `.env` institucional:

```text
php vendor/phpunit/phpunit/phpunit --do-not-cache-result --colors=never --filter 'CohorteInferencia30Test|ManifiestoCognitivo30Test|MisResidentesPsicologiaTest|FichaPsicologicaResidenteTest|NavegacionSistemaExpertoTest|PsychologyDashboardDataTest|ConsolidacionBloquesExpertosTest|ConocimientoCognitivoInstitucionalTest|GobiernoReglasExpertasTest'
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/probar-postgres.ps1 -Filtro 'CohorteInferencia30Test|ManifiestoCognitivo30Test|MisResidentesPsicologiaTest|FichaPsicologicaResidenteTest|NavegacionSistemaExpertoTest|PsychologyDashboardDataTest|ConsolidacionBloquesExpertosTest|ConocimientoCognitivoInstitucionalTest|GobiernoReglasExpertasTest'
```

Reejecuciones focalizadas: navegación, menú y dashboard; unitarias de instantánea institucional/instrumental, membresías, fecha visible y presentación; repetición de los dos casos de menú corregidos. Los nombres de las ejecuciones y el último resultado de cada caso están en el consolidado y sus JUnit.

```text
node --test tests/Frontend/orion-explorador.test.js tests/Frontend/cognitive-radial.test.js tests/Frontend/cognitive-responsive.test.js tests/Frontend/psychology-dashboard-responsive.test.js tests/Frontend/psychology-results-responsive.test.js
npm run build
```

Frontend: 10/10, sin omisiones, después de incorporar estilos compartidos y reconstruir el CSS. Build: Vite 8.3.1, 112 módulos, satisfactorio. Sintaxis: `php -l` en 131 archivos PHP seleccionados, sin errores. `git diff --cached --check` satisfactorio. Revisión de selección sin rutas privadas ni patrones de claves privadas/tokens de servicio.

## Límites que se conservan

- La conservación conjunta SQL del DTO completo continúa pendiente; `persistido=false` no se sustituye por una afirmación de guardado.
- La cohorte comprueba lógica y recorridos; no acredita validación clínica ni aprobación profesional de los métodos.
- No se guardan revisiones sobre resultados transitorios.
- La prueba frontend utiliza pantallas/estructuras sintéticas; no se afirma inspección interactiva de la sesión clínica del navegador.
- El trabajo no implica despliegue, instalación de esquema ni envío de datos clínicos a GitHub.
