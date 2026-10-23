# Superadministración: exploración conectada del sistema experto

## Fuentes y alcance

Continuación solicitada el 22/10/2026 en la rama de la interfaz existente,
`feature/apoyo-decision-informe-neurocognitivo`. Se conservan archivos ajenos.
AGENTS raíz/app/resources/tests, contrato visual, baseline y consolidación de
bloques expertos del 22/10 gobiernan el cambio. Las proyecciones del paquete
conservan sus nombres de contrato aunque algunas relaciones estén consolidadas
físicamente. No hay cambio de esquema, permisos ni inferencia.

SOURCE OF TRUTH: ConocimientoPanel, ExplorarConocimientoExperto,
ConsultarConocimientoAdministrativo, Policies VersionModeloExperto y
EvaluacionExperta; CapturasInstrumentalesResidente y LecturaResultadosExperto.
La nueva entrada de residentes reutiliza esas autorizaciones existentes, no
habilita la entrada profesional ni concede escritura clínica.

## UX FLOW y referencia

Entrada: superadministración activa → resumen → conocimiento o residentes.
Conocimiento: seleccionar procedencia/versión → buscar categoría o mapa →
seleccionar registro → leer campos y seguir referencias explícitas → volver al
paso anterior. Residentes: buscar → seleccionar → fuentes originales y resultados
conservados, con fecha, autor y alcance → inspeccionar vínculos del expediente.
Errores y falta de permiso se distinguen de ausencia de registros.

REFERENCE ANALYSIS: las imágenes 1/3 repiten el explorador con filtros superiores,
lienzo de aproximadamente 70% y detalle de 30%, con datos relacionados debajo.
La imagen 2 propone resumen, versiones y actividad. La 4 presenta directorio a
la izquierda y residente seleccionado a la derecha. Se conservan esas jerarquías,
el shell institucional, tokens y tipografías locales; móvil apila los paneles.
No se trasladan nombres, diagnósticos, porcentajes, aprobaciones ni métricas
ilustrativas. Las fotografías decorativas no se recortan de las referencias.

Los enlaces se construyen por identidad exacta dentro de la misma instantánea.
No se enlazan fuentes clínicas con reglas por similitud de nombre. El recorrido
local no ejecuta el motor, no escribe historia ni simula consultas de BDD.

Skills: module-delivery, source-of-truth, ui-review, ux-flow-architect,
reference-fidelity, design-system, data-visualization-ux,
responsive-accessibility, Laravel best practices, system-testing; QA y
release-check al verificar. Captura clínica y dirección artística nueva no
aplican. No se necesita generación de imágenes ni nuevas dependencias.

## Checkpoint: pausa solicitada por el propietario

Trabajo pausado el 22/10/2026 por solicitud explícita para recargar contexto.
No se declara terminado ni aceptado; no hay commit de esta continuación.

Implementado hasta la pausa:

- Ficha de conocimiento con referencias exactas, recorrido de vuelta, campos
  originales y mapa compartido; entrada de residentes de lectura en Superadmin.
- Cabecera y navegación compactas, procedencia/versión, acceso al detalle desde
  red y gobierno de reglas; búsqueda de reglas y enlaces de consulta conectada.
- Medicina reorganizada en Conexiones / Resumen y análisis / Fuentes originales.
  Identidad única y textos repetidos reducidos.
- Causa del mapa vacío verificada en consola: `template x-for` dentro de SVG
  carece de `content`; nuevo `x-ui.expert-graph-canvas` repite SVG completos desde
  plantillas HTML. Se comprobó visualmente que aparecen los nodos en 8011.
- Paginación local de seis fuentes en mapas numerosos, sin retirar la lista
  completa; cambio de página y recorrido por vínculo probados en JavaScript.
- Tras instrucción explícita del propietario sobre accesos, se aplicó el seeder
  aditivo `PermisosConsultaFuentesExpertasSeeder` en la instalación local. Repara
  lecturas de Medicina y lecturas `.ver` de Superadmin; preserva permisos previos,
  registra Activitylog y no concede firma clínica. El detalle médico pasó de 403
  a renderizar fuentes, conexiones y observaciones en el navegador.
- Superadmin autorizado a la entrada de residentes bajo la Policy de lectura;
  no se habilitan por rol las operaciones de captura/revisión clínica.

Verificado antes de la pausa:

- Regresión PHP: LaboratorioExpertoFrontendTest, NavegacionSistemaExpertoTest y
  ConexionesResidenteVisibleTest: 35 pruebas, 320 aserciones correctas. Fue anterior
  a los últimos cambios de gobierno y navegación; revisar lo afectado al retomar.
- DatosConocimientoVisibleTest y ConsultaConectadaSuperadminTest: 9 pruebas,
  32 aserciones correctas en SQLite. Comprueban vínculo exacto, aislamiento,
  recorrido, ausencia de escrituras por lectura y reparación aditiva/idempotente.
- Frontend de recorrido/proyección: 11 pruebas correctas, incluida paginación.
- Build Vite correcto antes de la última paginación JS; repetir al retomar.
- Se lanzó filtro PHPUnit `GobiernoReglas` seguido de `npm run build` en la sesión
  de terminal 4629. Se solicitó interrumpirla al recibir la pausa; comprobar su
  resultado antes de volver a ejecutar.

Sesiones operativas conservadas: Superadmin en 8010; Medicina en 8011 con cookie
independiente `remembermind_medico_8011`. El servidor médico está asociado a la
sesión de terminal 92267. Psicología de 8000 no se cerró. Las credenciales
existentes se utilizaron sin imprimirlas ni cambiarlas. No se crearon cuentas.

Pendientes al retomar: terminar revisión de distribución y casos límite de los
mapas (selección/retorno entre páginas); comprobar interfaz Superadmin y residente
con datos, consola sin errores, teclado y los cinco anchos en ambos temas;
actualizar evidencia/dictamen, revisar diff y seguridad, build final y commit
local solo cuando la entrega esté verificada. Mantener los artefactos `output/`
preexistentes fuera del commit. No hay cambios de esquema ni push.

## Continuación y revisión final — 22/10/2026

Se retomó el checkpoint en la misma rama. La sesión existente de Superadmin
funcionaba en 8010; se recuperó el servidor de Medicina en 8011 con su cookie
independiente. Se utilizaron las cuentas existentes, sin cambiar credenciales,
ejecutar seeders, migraciones ni modificar registros clínicos.

### Correcciones de la continuación

- `residentGraph`: selección y retorno recuperan la página de la fuente y
  redistribuyen el mapa antes de enfocarla. Regresión con 15 fuentes y tres
  páginas; no se modifica el DTO recibido.
- Lienzo compartido: una sola expresión de estilo controla posición y
  visibilidad. Antes, `:style` sustituía el `display` de `x-show`, dejando nodos
  de otras páginas visibles para el teclado. Se comprobó que la primera página
  contiene siete nodos y la última cuatro, conservando los 16 originales.
- Se cerró el objeto JavaScript de `:class` en las aristas. La consola había
  mostrado `Unexpected token ')'` al abrir la ficha. Nueva regresión de sintaxis
  con y sin recorrido; las aperturas posteriores no registran ese error.
- Los nodos de fuentes muestran su fecha y la incluyen en su nombre accesible.
  Estado, profesional y versión permanecen en el inspector original.
- La cabecera y la identidad del residente declaran dirección horizontal para
  evitar heredar la columna de `rm-card`. Los selectores `rm-select` reciben el
  mismo ajuste de interlineado que `rm-input`.
- Las filas de fuentes usan `grid-auto-rows: max-content`: el límite de altura
  mantiene scroll sin comprimir fechas y autoría fuera de las tarjetas.

### Verificación reproducible

PHP 8.4.13 de la terminal no habilitaba SQLite. Se cargaron sus extensiones solo
en cada proceso de pruebas; no se editó `php.ini` ni `.env`:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit <archivo>
node --test tests/Frontend/expert-resident-workspace.test.js tests/Frontend/orion-explorador.test.js
npm run build
```

| Pruebas PHP ejecutadas individualmente | Pruebas | Aserciones |
|---|---:|---:|
| ConsultaConectadaSuperadminTest | 5 | 22 |
| DatosConocimientoVisibleTest | 4 | 10 |
| ConexionesResidenteVisibleTest | 6 | 21 |
| LaboratorioExpertoFrontendTest | 25 | 263 |
| NavegacionSistemaExpertoTest | 4 | 38 |
| GobiernoReglasExpertasTest | 27 | 110 |
| **Total SQLite en memoria** | **71** | **464** |

Frontend relacionado: **13 correctas, cero fallos y cero omitidas**. Pint
`--test` pasó en los diez archivos PHP de la entrega. Build Vite pasó; conserva
la advertencia de tamaño de chunks superior a 500 kB. `git diff --check` sin
errores de whitespace.

También se ejecutó `npm test`. La suite general encontró un fallo ajeno a los
selectores modificados: `shell-visual-consistency.test.js:43` espera el fondo
global `rgb(231, 226, 222)` y recibe `rgb(231, 227, 223)`. No se cambiaron los
tokens globales ni se debilitó esa prueba. La suite general no se declara verde.
No se ejecutó integración PostgreSQL ni concurrencia en esta continuación;
SQLite no acredita esos comportamientos.

### QA de la superficie retomada

Evidencia local, excluida del commit:
`output/superadmin-continuacion-20261022/matriz.json` y capturas JPEG adyacentes.
La matriz reúne **30 combinaciones**: ficha de conocimiento, directorio/fuentes
de Superadmin y consulta médica, en 1440/1280/1024/768/390 px y ambos temas.
El navegador redondeó 1024 a 1025 CSS px; se registra la medida efectiva.
Se descartaron de la matriz las capturas cuyo zoom producía un ancho diferente.

| Criterio | Resultado y evidencia |
|---|---|
| Layout y reflow | Sin scroll horizontal de página ni literales `null`, `NaN`, `undefined`, `Infinity` o `[object Object]` en las 30 combinaciones válidas. |
| Campos y procedencia | Ficha con todos los campos, relaciones exactas, apertura de una referencia y retorno; fuente instrumental con fecha, autor, versión y ausencias explícitas. |
| Paginación | Recorrido del conjunto hacia la fuente del 02/10/2026 lleva a página 3 de 3; retorno/selección entre páginas cubiertos también por JS. |
| Teclado y foco | Selección de nodo con Enter; apertura/cierre de ficha mediante teclado y Escape; foco devuelto a `Ver detalle de SUB-ATE-S01`. |
| Estados | Búsqueda sin coincidencias y recuperación; carga Livewire visible; falta de evaluación histórica diferenciada de falta de fuentes; controles de página extremos deshabilitados. |
| Consola | Ficha y Medicina abiertas después de la corrección: cero advertencias/errores. El directorio conservaba el error histórico de las 19:20, anterior a su recarga; no se atribuye al código final. |
| Movimiento reducido | Emulación de `prefers-reduced-motion: reduce`: animación del bloque médico de 0.00001 s. |
| Contraste localizado | Colores computados: texto/metadata sobre fuente seleccionada clara, 5.83:1 y 4.51:1; texto principal del inspector oscuro, 14.91:1. No es una auditoría de todos los pares del sistema. |
| Datos de evidencia | Residente y aplicaciones sintéticas La Paz ya existentes; paquete de conocimiento identificado como experimental. No se fabricaron resultados para llenar gráficos. |

Dictamen funcional/visual del alcance anterior: **PASS**, con la limitación de
que no constituye certificación global de accesibilidad, fidelidad píxel a píxel
contra imágenes históricas ni aprobación clínica del motor. No se provocaron
errores de infraestructura contra la instalación operativa; su recuperación se
revisa en código y las denegaciones se prueban en SQLite aislado.

### Seguridad, integración y cierre

Revisión de lectura, separada de las correcciones: las entradas conservan cuenta
activa, Policy, permisos y contexto del residente. Categorías arbitrarias,
registros ajenos, familiar y cuenta inactiva se rechazan en las pruebas. Consultar
no crea evaluaciones ni firma clínica. No hay mutación, cambio de esquema o
inferencia nueva en la continuación. La reparación aditiva del seeder pertenece
al checkpoint autorizado previo; no se volvió a aplicar.

Sin dependencias nuevas ni legado V1 añadido en el ámbito cambiado. No hay push.
El único seguimiento técnico fuera del alcance es conciliar el token global con
la expectativa de la prueba general de color, mediante su propia revisión visual.
