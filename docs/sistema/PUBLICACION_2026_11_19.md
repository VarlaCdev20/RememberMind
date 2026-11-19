# Publicación del trabajo acumulado — 19/11/2026

## Alcance

Publicación expresamente solicitada de todos los cambios de código pendientes
en la copia principal del proyecto, junto con los commits locales anteriores.
Incluye resultados institucionales, avisos y rotación de planificación, reportes
de Enfermería, componentes y estilos compartidos, seeders, pruebas,
documentación y entregables UML editables.

Se excluyen credenciales, configuración local, almacenamiento persistente,
volcados, correos de ensayo, cachés, copias intermedias y diagnósticos generados.
Los archivos excluidos se conservan localmente. Esta tarea de publicación no
ejecutó migraciones ni seeders sobre la base de uso.

## Verificación de este corte

- `composer validate --strict --no-check-publish`: correcto.
- Sintaxis PHP de los 125 archivos candidatos: correcta.
- `npm run build`: correcto. Persiste el aviso de un paquete de JavaScript
  mayor de 500 kB; no impide la compilación.
- Los dos modelos UML editables se pudieron leer como JSON válido.
- `git diff --cached --check`: correcto después de quitar un espacio final
  en un generador de diagramas.
- Inspección de credenciales en archivos candidatos y commits locales: sin
  coincidencias de secretos de la configuración local. Las contraseñas literales
  detectadas corresponden a cuentas exclusivamente sintéticas de pruebas.
- Verificación dirigida de ocho clases: resultados institucionales, avisos
  compartidos, estructura de los avisos, rotación, seguridad de planificación,
  horarios, reportes de Enfermería y roles. Se combinaron la ejecución inicial
  y las repeticiones de las clases corregidas, conservando el último resultado
  de cada caso:
  - PostgreSQL: 105 casos aprobados, 829 aserciones, sin errores ni omisiones.
  - SQLite: 100 aprobados, cinco omitidos por requerir concurrencia PostgreSQL,
    783 aserciones, sin errores ni fallos.

Se corrigieron expectativas antiguas de inventario, el armado de datos de
medicación que aún escribía el residente derivado y el título anterior del
inicio de Gerencia. La prueba de la extensión de avisos compara las tablas
antes y después, además de conservar las comprobaciones de datos y restricciones.

## Corrección de permisos

La comprobación encontró que `ActualizarPermisosRol` podía volver a conceder
`evaluaciones_expertas.ejecutar_institucional` al guardar Superadministración,
aunque el seeder y el proveedor ya lo excluían. Se añadió a los permisos
reservados de esa acción, siguiendo la
[decisión aprobada del 04/11](../base-de-datos/DECISION_RESULTADO_INSTITUCIONAL_2026_11_04.md).
Las pruebas comprueban la denegación sin cambios persistidos para los ocho roles
no competentes, el guardado válido para Medicina/Psicología y la conservación
de la exclusión al guardar el rol Superadministración. Pasaron en ambos motores.

## Interfaz

La ejecución inicial de `npm test -- --test-concurrency=1` registró 314 casos:
287 aprobados, 9 fallidos, 2 cancelados y 16 omitidos por sus condiciones
preexistentes. Se repitieron por separado los recorridos afectados:

- Gerencia: aprobado en cinco anchos. Se corrigió la prueba para buscar al
  trabajador antes de seleccionarlo, conforme al buscador vigente.
- Reporte de Enfermería: aprobado, con filtros, vista previa, PDF y cinco anchos.
- Recuperación de contraseña: aprobada en cinco anchos.
- Red de apoyo: continúa fallando al esperar `#familia-contacto-formulario`.

Permanecen por corregir los dobles de las funciones compartidas `rmConfirmar`
y `rmConfirmarSalida` en `livewire-access-feedback.test.js` y
`wound-evidence.test.js`, y la expectativa de paleta de
`shell-visual-consistency.test.js`. No se omitieron pruebas para obtener un
resultado favorable.

## Suite general y pendientes

`php vendor/phpunit/phpunit/phpunit --stop-on-failure` terminó con 1035 casos,
16703 aserciones, un error, un fallo y cuatro omitidos. La ejecución se detuvo
en `ReferenciaF02SqliteDesechableTest`: el prototipo utiliza la ubicación antigua
de `ContextoEjecucion` y fija inventarios históricos de tablas que ya no
representan las migraciones actuales. El resto de la suite general no se ejecutó
en este intento.

Este corte publica el trabajo en una rama de desarrollo y conserva los
pendientes anteriores; no acredita una CI completa aprobada ni integra cambios
en `develop` o `main`.
