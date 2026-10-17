# Estructura del directorio de residentes de Enfermería

Fecha: 2026-10-17. Alcance: presentación de Pacientes de Enfermería, conservando
el diseño aprobado y el comportamiento del componente `MisPacientes`.

## Diagnóstico y decisión

El directorio ya reutilizaba `resident-filter-toolbar`, `resident-card`,
`resident-summary-drawer` y `quick-register-modal`. No se crearon equivalentes
ni otro sistema de modales, tarjetas, botones o filtros.

La vista principal mezclaba resultados, carga, errores, estados vacíos, resumen,
configuración del registro, identidad del residente, contenido y acciones del
modal. Tenía 378 líneas; la entrada queda en 34 líneas de composición. No se
eliminaron funciones para alcanzar un límite de líneas.

## Organización

Los parciales pertenecen a `Cuidados/Presentacion/Vistas/livewire/cuidados/directorio`:

| Parcial | Responsabilidad |
|---|---|
| `cabecera` | Contexto de página y acceso a cuidados programados |
| `resultados` | Carga, error, vacío, tarjetas/listado y paginación |
| `resumen` | Drawer del residente y acciones autorizadas |
| `registro` | Variables de presentación, modal, icono y slots |
| `registro-contexto` | Identidad del residente, profesional, jornada y alertas |
| `registro-contenido` | Selección, captura, revisión, descarte y resultado |
| `registro-acciones` | Acciones correspondientes al estado del registro |

Se usa el namespace existente `cuidados::`; no se añadieron alias, rutas,
dependencias ni buscadores de vistas. La variable `cuidadoActual` sigue en la
composición principal porque sirve a cabecera y resumen. Las variables del
registro quedan dentro de su parcial y solo alimentan sus hijos.

Los slots se declaran en su componente dueño. Los eventos y el scope Alpine
permanecen en el mismo elemento raíz. Se conservan los bindings, métodos,
condiciones, claves de morph, permisos y enlaces. No se modificaron CSS,
JavaScript, reglas clínicas, backend, base de datos ni datos operativos.

## Integración y preservación de trabajo concurrente

El checkout principal contenía cambios de otras tareas. La extracción se aplicó
independientemente a cada versión, conservando los cambios previos del principal,
incluidos sus filtros y aperturas específicas de registro. El commit propio se
prepara en el worktree de `UNIFICACION`; no incorpora esos cambios ajenos.

## Verificación

- Baseline previo: 17 pruebas y 197 aserciones, centradas en navegación, filtros,
  búsqueda, estados vacíos, resumen y permisos.
- Suite completa `MisPacientesRedisenadaTest`: **164 pruebas, 1719 aserciones**.
  Incluye registro, validación, autoría, restricciones y negativos de acceso.
- Exports Blade sintéticos antes/después: 15 estados equivalentes. Se excluyen
  comentarios, whitespace y metadatos aleatorios Livewire, además del código
  aleatorio `SGN_*` generado por la factory de signos; lecturas, autoría visible,
  texto, atributos y bindings se comparan. El profesional sintético es determinista.
- Revisión independiente de fuente expandida: mismo contenido salvo whitespace
  y traslado del bloque de variables sin consumidores fuera del registro.
- QA independiente: **PASS acotado a preservación estructural**. Matriz de 150
  casos (15 estados, cinco anchos y dos temas), sin errores JavaScript,
  desbordamiento horizontal ni literales técnicos inválidos visibles.
- Los 100 montajes con overlay conservan foco y contención de teclado. El menú
  de tarjetas abre, cierra con Escape y devuelve el foco: diez de diez casos.
- Build Vite correcto en worktree y checkout principal. Permanece la advertencia
  previa de bundle JavaScript mayor de 500 kB; esta tarea no modifica ese bundle.

Comandos principales:

```sh
php vendor/bin/phpunit tests/Feature/MisPacientesRedisenadaTest.php
npm run build
git diff --check
```

Las capturas son fixtures sintéticas con Alpine real y transporte Livewire
sustituido. La carga de iconos Phosphor del CDN no está incluida en ese harness;
no se cambió en producción. La matriz no representa E2E contra servidor ni
validación clínica o integración PostgreSQL. Medicación sin dosis pendiente
redirige al módulo correspondiente; no se simula una prescripción para abrirla.

No se añadieron dependencias V1. El backend, los formularios internos, sus gráficas
y las demás pantallas mantienen su alcance previo. Dashboard y optimización de
carga de recursos pertenecen a las siguientes tareas propuestas.
