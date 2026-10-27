---
title: "Gerencia: corrección del acceso a Ocupación H04"
status: CURRENT
version: "1.0"
last_reviewed: 2026-10-26
owner: RememberMind
source_of_truth: false
verification_scope: TARGETED_LARAVEL_HTTP_SQLITE_AND_FRONTEND_UNIT_TESTS
runtime_verified: true
---

# Acceso a Ocupación: H04 corregido

## 1. Alcance y reproducción

La [auditoría del 25/10](../../../auditorias/2026-10-25/INFORME_AUDITORIA_INTEGRAL.md) reprodujo «Ocupación» de Gerencia como 302 → 403: `admin.habitaciones.index` redirigía a `admin.administracion.habitaciones`, cuyo grupo exige `admisiones.ver_dashboard`. Gerencia ya tiene `habitaciones.ver`; esa lectura no requiere abrir el dashboard administrativo.

Antes de corregir, las nuevas pruebas reprodujeron el 403 en el recorrido publicado y el 302 en filtros/detalle de la ruta de entrada. Una prueba adicional con cama ocupada detectó un enlace «Ver residente y alojamiento» hacia otra pantalla administrativa no autorizada. Las reproducciones utilizan datos sintéticos y SQLite `:memory:`, no residentes operativos.

## 2. Implementación

| Archivo | Cambio concreto |
|---|---|
| [Rutas de Institucion](../../../../app/Modulos/Institucion/Presentacion/Rutas/web.php) | La ruta existente de habitaciones resuelve directamente con OperacionController y módulo fijo habitaciones; elimina el redirect defectuoso, sin nueva ruta o grant |
| [OperacionController](../../../../app/Modulos/Institucion/Presentacion/Http/Controllers/OperacionController.php) | Reutiliza MapaHabitacionesService, fija la ruta del explorador según entrada y presenta consulta institucional como Ocupación |
| [Vista compartida](../../../../app/Modulos/Institucion/Presentacion/Vistas/paginas/admin/administracion/habitaciones.blade.php) | Formularios, filtros, cambios de vista, detalle y retorno conservan el destino autorizado; contexto institucional legible e Historial administrativo solo si puede acceder |
| [Detalle de cama](../../../../app/Modulos/Institucion/Presentacion/Vistas/paginas/admin/administracion/partials/detalle-cama.blade.php) | No ofrece enlace a ficha administrativa cuando ese destino no está autorizado; conserva lectura permitida del mapa |
| [GerenciaOcupacionAccesoTest](../../../../tests/Feature/GerenciaOcupacionAccesoTest.php) | Seis regresiones: acceso publicado sin grants adicionales; filtros/vistas/paginación/detalle; sin permiso; identidad autorizada/revocada; cuenta inactiva; Familiar con permiso aislado |
| [EspaciosResidencialesTest](../../../../tests/Feature/EspaciosResidencialesTest.php), [RolesVistasPermisosMatrixTest](../../../../tests/Feature/RolesVistasPermisosMatrixTest.php) | Sustituyen la expectativa antigua de redirección por consulta directa; mantienen pruebas separadas de inventario y contexto administrativo |

La entrada compartida muestra el mapa; el inventario de mantenimiento continúa en la ruta administrativa ya existente con su autorización. No se crea un mapa, motor, Policy o dashboard paralelo. No se cambia esquema, catálogo, permisos ni datos institucionales.

## 3. Fronteras de seguridad comprobadas

- Sesión y middleware de lectura permanecen; controlador y servicio requieren cuenta activa y excluyen Familiar del mapa institucional.
- Gerencia sigue sin `admisiones.ver_dashboard`, `habitaciones.crear` o `ocupaciones_cama.gestionar`. La ruta administrativa continúa devolviendo 403 a ese rol.
- No se ofrecen crear habitación, añadir cama, elegir residente o preparar ingreso sin autorización. La ausencia de botones complementa los controles backend existentes, no los reemplaza.
- La identidad de ocupante depende de lectura de residentes y Policy. Al retirar ese permiso, el payload de detalle tiene ocupante/residente NULL e historial vacío; los registros conservados no cambian.
- El enlace de Historial y la ficha administrativa dependen de acceso a sus rutas. No se concede acceso a toda Administración para resolver el enlace de Ocupación.
- Los filtros se validan con el controlador existente; `modulo` lo fija la ruta y la consulta no registra traslados, admisiones o nuevos residentes.

Revisión acotada del flujo y pruebas negativas satisfactorias; no es auditoría de seguridad global ni validación clínica. No se amplían permisos de otras fuentes del expediente.

## 4. Pruebas ejecutadas y build

```text
php vendor/phpunit/phpunit/phpunit --filter="GerenciaOcupacionAccesoTest|MapaHabitacionesTest|EspaciosResidencialesTest|RolesVistasPermisosMatrixTest|GerenciaDashboardTest|SidebarServiceTest" --log-junit output/auditoria-integral-20261025/gerencia-ocupacion-h04-junit.xml
```

**PASS: 56 pruebas, 361 aserciones, cero fallos/errores/omitidas; 73,17 s.** PHP 8.3.33, PHPUnit 12.5.36, configuración de pruebas SQLite `:memory:` con guardas de base desechable. JUnit conservado en la ruta del comando. No se ejecutó PostgreSQL: no cambió persistencia, SQL o bloqueo; estas pruebas no acreditan concurrencia PostgreSQL.

```text
node --test tests/Frontend/habitaciones-interactivas.test.js
npm.cmd run build
```

**PASS: 2 pruebas JavaScript, sin omitidas. Build Vite 8.3.1 correcto.** El build advierte sobre chunks mayores de 500 kB; no se cambió bundling o dependencias para ocultarlo. No son pruebas de navegador ni contraste/responsive del recorrido completo.

`php -l` correcto en controlador, registro de rutas y nueva clase de pruebas. `git diff --check` sin errores en los archivos del cambio. No se ejecutaron comandos de migración o limpieza de datos, ni se reinició el servidor institucional.

## 5. Trazabilidad y límites del cierre

| Requisito/caso | Evidencia de esta entrega | Pendiente |
|---|---|---|
| H04 / RO11 | Menú a ruta real, respuesta 200 sin redirect administrativo; permisos administrativos siguen denegados | Comprobación visual con sesión de Gerencia |
| RO12 | Mapa/tabla, filtros y paginación conservan URL autorizada y fuente existente | Recorrido táctil/teclado completo en los cinco anchos |
| RO17 | Retirada de identidad/historial del payload sin lectura de residentes | Auditoría de todas las fuentes históricas fuera del alcance de esta corrección |
| RO19 | Sin mutaciones gerenciales/grants nuevos; suite de inventario y permisos conserva negativos | No se cambió el caso de uso de traslado |
| Conservación de trabajo | Diff propio acotado y resto del árbol compartido preservado | Integración Git general de los otros cambios no forma parte del hito |

H04 queda **implementado y verificado técnicamente en HTTP/pruebas**. No cierra todos los casos de Residentes/ocupación, el historial íntegro, las diferencias de estados, la auditoría completa, H02/H03 de planificación o el diseño entero de Gerencia. No hay captura visual nueva, commit, push o despliegue en esta entrega. Las [fichas 18–20](19-OCUPACION-HABITACIONES-CAMAS.md) conservan el contexto de su tarea documental previa y se actualizan con este avance puntual.
