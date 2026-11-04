# Reducción de JavaScript compartido

Fecha: 2026-10-17. Alcance: eliminar carga sin consumidores de la entrada Vite
compartida. No se modifica el diseño, el backend ni el contrato de las gráficas.

## Causa comprobada

`resources/frontend/scripts/app.js` importaba GSAP y ScrollTrigger, registraba
el plugin y publicaba ambos en `window`. La búsqueda en los scripts, vistas,
módulos, rutas y pruebas no encontró consumidores ejecutables fuera de esas
importaciones. Los efectos activos usan AOS y código nativo de puntero,
requestAnimationFrame, scroll e IntersectionObserver.

Se eliminaron únicamente esas importaciones, su registro y sus dos asignaciones
globales. Chart.js, RMCharts, AOS, formularios, tema, navegación y sus órdenes de
carga permanecen iguales. No fue necesario introducir carga asíncrona ni otro
protocolo de inicialización para resolver el tamaño observado.

GSAP sigue en package.json/package-lock.json: esta tarea retira su uso en la
entrada de ejecución, no modifica dependencias del proyecto ni el lock.

## Medición comparable

Builds de producción realizados en el mismo worktree con Vite 8.3.1, las mismas
dependencias y configuración. Bytes del archivo JS de entrada, sin mapas:

| Métrica | Antes | Después | Reducción |
|---|---:|---:|---:|
| JavaScript minificado | 513724 B | 400017 B | 113707 B, 22,13 % |
| gzip medido con Node zlib | 173307 B | 129244 B | 44063 B, 25,42 % |

La advertencia Vite de chunk mayor de 500 kB desaparece sin elevar su umbral.
Los CSS emitidos conservan los mismos hashes. En el checkout principal, que
incluye módulos de otros trabajos, el JS compilado queda en 429,46 kB y tampoco
genera esa advertencia. Las cifras entre checkouts no son una comparación de
rendimiento: contienen fuentes distintas. No se afirma una mejora de latencia
de producción, solo reducción de bytes compilados y descargables.

## Verificación

- Build de producción correcto antes/después y en el checkout principal.
- Dieciséis pruebas focalizadas antes; diecisiete después al incluir la prueba
  de navegador del efecto de luz. Sin fallos ni pruebas omitidas.
- Dieciséis montajes de navegador con los bundles de producción completos:
  antes/después, desktop/móvil, dashboard con las tres gráficas pobladas,
  directorio, ficha de signos y contenido sintético de bienvenida con AOS.
  Cero errores JS, desbordamientos o gráficas sin inicializar; se repite morph
  y navegación. Axios, AOS y el feedback compartido siguen disponibles.
- Las fixtures usan datos sintéticos. El transporte Livewire se sustituye;
  esto no equivale a E2E contra servidor, pruebas clínicas o PostgreSQL.
- No se repitieron suites PHP: no cambia PHP, Blade, permisos o persistencia.
  No se añadieron tests que solo replicaran la eliminación de importaciones.

Comandos principales:

```sh
npm run build
node --test tests/Frontend/nursing-dashboard.test.js tests/Frontend/ficha-modulos.test.js tests/Frontend/chart-design-system.test.js tests/Frontend/chart-interactions.test.js tests/Frontend/cursor-glow.test.js
git diff --check
```

## Integración y límites

La misma eliminación puntual se aplicó al checkout principal preservando sus
importaciones preexistentes. El commit en UNIFICACION contiene únicamente la
entrada compartida y esta evidencia. No se realiza push ni se modifica main.

La carga selectiva de Chart.js o de formularios por pantalla puede estudiarse
separadamente con mediciones y verificación de orden de Alpine/Livewire. No se
declara implementada aquí ni se introduce ese riesgo después de resolver la
advertencia y verificar el cambio acotado.
