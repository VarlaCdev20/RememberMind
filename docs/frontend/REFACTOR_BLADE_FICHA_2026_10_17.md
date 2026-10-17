# Organización de Blade en la ficha clínica

Fecha: 2026-10-17. Alcance: estructura de presentación de la ficha compartida,
sus formularios, gráficas y estilos de cronología. No es una revisión clínica
ni una limpieza de todas las plantillas del repositorio.

## Por qué eran extensas

Las pestañas mezclaban preparación de datos, cabeceras, resúmenes, filtros,
listados, detalles, formularios y código Chart.js/Alpine. `modales.blade.php`
reunía once ventanas; Signos incluía aproximadamente 780 líneas JavaScript.
Los comentarios y el espaciado no eran la causa principal.

500 líneas es una señal para revisar responsabilidades, no un límite que obligue
a comprimir HTML o partir un formulario a mitad. Se extrajeron secciones
funcionales completas, preservando las variables y el componente Livewire dueño.

## Estructura resultante

Las entradas mantienen sus nombres lógicos y las rutas públicas existentes.
Las secciones nuevas usan el namespace registrado `clinica::`, sin ampliar el
buscador de vistas ni agregar alias de compatibilidad.

| Entrada Blade | Antes | Después | Responsabilidades extraídas |
|---|---:|---:|---|
| modales | 1199 | 15 | Selector, siete formularios, exportación, expediente, familia |
| tab-resumen | 791 | 148 | Información clínica, estado/cuidados, tendencias/continuidad |
| tab-signos | 1987 | 159 | Cabecera, gráfica, métricas, historial, rangos, detalle |
| tab-medicacion | 1129 | 346 | Resumen operativo, listado, detalle |
| tab-estudios | 775 | 222 | Cabecera, métricas, filtros, resultados |
| tab-eventos | 984 | 141 | Cabecera, resumen, filtros, cronología, detalle |
| tab-documentos | 949 | 209 | Cabecera, resumen, filtros, listado, detalle |

Conteos de líneas físicas, incluidos comentarios y espaciado. El código extraído
sigue existiendo: reducir la entrada no implica eliminar funciones ni que la
complejidad clínica haya desaparecido. Las nuevas secciones mantienen sus
contenedores HTML equilibrados; la entrada es dueña de las filas compartidas.

## Componentes y recursos compartidos

- Los siete formularios (signos, medicación, tarea, evolución, incidente, atención
  y cierre de alerta) usan `x-ui.modal-livewire` y la clase existente
  `rm-clinical-form-modal`: cabecera, cierre, foco, scroll y pie comunes.
- Se conservaron los nombres de estado, métodos, validaciones y bindings de los
  campos. El cambio no amplía permisos ni modifica escritores del backend.
- Selector de registros, exportación y ventanas laterales se aislaron en sus
  propios parciales. Su geometría especial todavía no se ha sustituido por los
  componentes estándar; no se declara una unificación completa de esas ventanas.
- El CSS de la cronología se encuentra en
  `resources/frontend/styles/design-system/patterns/clinical-timeline.css`,
  importado por el Design System. No se añadió una paleta.
- `ficha-eventos.js` y `ficha-estudios.js` contienen sus factories de interacción.
  Signos separa estado/interacción, construcción de la gráfica y series en
  `ficha-signos.js`, `ficha-signos-grafica.js` y `ficha-signos-series.js`.
- Los scripts se incorporan mediante Vite. Signos registra la factory tanto si
  Alpine está disponible al cargar el bundle como si se inicia después, sin
  registrar dos veces el mismo componente.
- Fallar al guardar preferencias locales ya no anuncia éxito. El mensaje aclara
  que son preferencias del navegador; no confirma un cambio clínico del servidor.

## Preservación y límites

El checkout principal tenía cambios de otras tareas. Se aplicó la misma
transformación sobre cada versión, conservando su contenido y su paleta. El
commit se prepara únicamente en el worktree de `UNIFICACION`.

Se preservan en este refactor los cálculos existentes y los contratos de datos.
La interpretación duplicada de signos, los rangos locales, las descargas de
informes incompletas y otras observaciones del análisis previo requieren una
reparación funcional separada. No se presentan como resueltas por mover archivos.
Tampoco se reorganizaron aquí perfil, autenticación ni los demás módulos.

## Verificación

- Baseline: 17 pruebas funcionales, 97 aserciones, antes de extraer.
- Después: las mismas pruebas y arquitectura, 31 pruebas y 504 aserciones.
- Exportación de diez pestañas y siete estados de formulario con datos sintéticos:
  17 comprobaciones. Las diez pestañas conservan el HTML visible normalizado,
  descontando identidades sintéticas aleatorias y recursos trasladados.
- Cuatro pruebas JavaScript: orden de carga de Alpine, registro único, interacción
  con Livewire/aislamiento de estado y fallo de almacenamiento local.
- Los contratos de arquitectura ahora recorren los parciales; se conservan las
  aserciones de componentes, tokens y contenido, sin agregar texto oculto para
  satisfacerlas.
- Build Vite en worktree y checkout principal. Advertencia de chunk mayor de
  500 kB pendiente de optimización por carga; no se ocultó ni se elevó el límite.
- La revisión visual usa exports Blade sintéticos con Alpine y Chart.js reales;
  el transporte Livewire se sustituye solo en el fixture, nunca en la aplicación.
  Persistencia y autorización se verifican mediante pruebas PHP, no con capturas.
- Matriz de navegador: 510 casos (17 estados, cinco anchos, dos temas y tres
  variantes: antes, después y CSS del checkout principal). Cero errores
  JavaScript, desbordamientos horizontales o valores técnicos inválidos visibles.
  Las gráficas visibles de signos, estudios y eventos tienen instancias Chart.js.
- Comprobación adicional de teclado: los siete formularios en 1440 y 390 px
  mantienen el foco dentro de la ventana y Escape solicita el método de cierre
  correspondiente. Se usa el plugin de foco incluido en Livewire. El cierre del
  servidor no se simula como persistencia ni se deduce de la captura.
- La aceptación visual final la realizó el implementador. El intento de revisión
  independiente no pudo completarse por disponibilidad del agente; no se declara
  un dictamen independiente. Esta evidencia cubre presentación e interacción en
  fixtures, no una sesión completa contra PostgreSQL ni una validación clínica.

Comandos reproducibles para los checks incorporados al repositorio:

```sh
php vendor/bin/phpunit --filter="MedicoFichaUnificadaTest|FichaPacienteBotonesTest|FichaSeguimientoDatosRealesTest|DocumentacionFichaTest|FrontendArchitectureTest"
node --test tests/Frontend/ficha-modulos.test.js
npm run build
```

Los exports y capturas de QA son artefactos temporales sintéticos y no forman
parte del código de producción ni del commit.

No se modificaron DB, rutas públicas, reglas clínicas, permisos ni datos reales.
