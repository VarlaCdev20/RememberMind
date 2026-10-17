# Organización del Dashboard de Enfermería

Fecha: 2026-10-17. Alcance: refactor de presentación, conservando el diseño,
los datos, el orden de lectura y el comportamiento operativo existentes.

## Antes y después

La entrada `dashboard-turno.blade.php` reunía once secciones, preparación de
variables y tres scripts. Pasa de **523 a 55 líneas físicas**. El código extraído
continúa disponible; el número de líneas no se usa como límite artificial.

La cabecera y el divisor ya eran componentes canónicos y permanecen en la
composición principal. Se reutilizan metric-card, card, section-header,
priority-alert, schedule-item, patient-turn-row, skeleton y empty-state.
No se creó otra familia de componentes ni se modificaron CSS o tokens.

## Responsabilidades

Los once parciales pertenecen al módulo Cuidados, bajo
`Presentacion/Vistas/livewire/cuidados/dashboard`:

- indicadores;
- alertas;
- agenda;
- tareas;
- controles;
- conducta;
- incidentes;
- residentes;
- seguimiento;
- actividad;
- ubicaciones.

La entrada conserva las variables comunes y cada parcial conserva sus límites
HTML. Los includes usan el namespace ya registrado `cuidados::`. No añaden
wrappers, rutas ni alias. Los estados de carga, vacío, falta de contexto y acceso
mantienen las condiciones previas; la actualización de turno sigue cada 60 s.

`resources/frontend/scripts/modules/nursing-dashboard.js` reúne los tres
controladores existentes: incidentes, seguimiento/actividad y efecto de luz.
Se incorpora por Vite y solo monta sus controladores al encontrar el Dashboard.
Conserva guards, eventos de tema, navegación y hooks de morph. Ante JSON ilegible,
retira la gráfica anterior y registra el fallo, evitando datos desactualizados.

## Preservación de trabajo concurrente

El checkout principal contiene otros cambios de distribución y gráficas.
La extracción se aplicó por separado sobre cada versión: su orden, wrappers
y detalles de gráficos permanecen en sus propios parciales. Los commits de
`UNIFICACION` contienen únicamente esta tarea, no esos cambios ajenos.

No se modificaron backend, permisos, reglas clínicas, consultas, persistencia,
esquema o datos operativos. No se añadieron dependencias o referencias V1.

## Verificación y límites

- Baseline inicial: 25 pruebas, un fallo previo por buscar una clase CSS antigua
  del icono de notificaciones. Se sustituyó por la comprobación de su nombre
  accesible, manteniendo la exigencia de una sola campana. Baseline corregido:
  25 pruebas y 503 aserciones.
- Tras la separación de Blade y tras extraer JavaScript: **75 pruebas PHP,
  778 aserciones**. Cobertura: integración visual, componentes, arquitectura,
  complementos, alcance del turno, lectura fuera de jornada y negativos.
- **Cinco pruebas JavaScript**: ausencia de montaje en otras pantallas,
  disponibilidad del DOM, instancias/listeners únicos, cambios de datos/tema,
  limpieza al salir y retirada de datos ilegibles.
- Cuatro estados Blade sintéticos equivalentes antes/después: en turno,
  fuera de turno, sin asignación y gráficas pobladas. Se excluyen whitespace,
  comentarios y metadatos/identificadores aleatorios de las fixtures.
- QA independiente: **PASS de preservación estructural**. Matriz de 80 casos
  (cuatro estados, cinco anchos, dos temas, antes/después), sin errores JS,
  desbordamiento horizontal, literales técnicos inválidos o gráficas sin montar.
  Las tres gráficas usan Chart.js real y permanecen montadas tras morph.
- Builds correctos en worktree y checkout principal. Sigue pendiente dividir
  la carga del bundle general: 513.72 kB minificado en esta rama; no se elevó
  el umbral de advertencia de 500 kB.

Los exports usan datos sintéticos. El caso de gráficas añade payloads agregados
solo en una subclase privada de QA; no son registros guardados ni nuevas reglas.
El transporte Livewire se sustituye en el navegador y no se cargan los iconos o
la fotografía externos. Esta revisión no equivale a E2E contra servidor,
integración PostgreSQL o aceptación clínica integral. No se incluyen fixtures,
capturas ni scripts temporales de QA en el commit.

## Comandos principales

```sh
php vendor/bin/phpunit --filter="DashboardEnfermeriaIntegracionVisualTest|NursingDashboard|FrontendArchitectureTest|DashboardComplementosTest|MiTurnoServiceTest|MiTurnoFueraDeJornadaTest"
node --test tests/Frontend/nursing-dashboard.test.js
npm run build
git diff --check
```

## Commits por cambio

- `2a57e200`: corregir el contrato accesible de notificaciones en la prueba previa.
- `ea9def88`: separar las once secciones Blade y adaptar la lectura de parciales
  en las pruebas, manteniendo sus aserciones.
- Extracción de scripts, pruebas de ciclo de vida y esta documentación: commit
  independiente de cierre. No se realiza push ni se modifica la rama main.
